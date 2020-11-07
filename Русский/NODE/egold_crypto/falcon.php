<?php

// Implementation of Falcon: https://falcon-sign.info/

namespace Falcon;

require_once 'SHA3.php';

require_once 'common.php';
require_once 'random.php';
require_once 'fft.php';
require_once 'ntt.php';
require_once 'ntrugen.php';
require_once 'ffsampling.php';

// ============================================================================

const slack = 1.1;
const smooth = 1.28;


function normalize_tree(&$tree, $sigma)
{
    // Normalize the leaves of a LDL tree (from values ||b_i||**2 to sigma/||b_i||).

	if(count($tree) == 3) {
        normalize_tree($tree[1], $sigma);
		normalize_tree($tree[2], $sigma);
	}
	
	else {
        $tree[0] = $sigma / sqrt($tree[0]->getReal());
		$tree[1] = 0;
	}
}

function check_ntru($f, $g, $F, $G)
{
	// Check that f * G - g * F = 1 mod (x ** n + 1)
	
    $a = to_int(karamul(to_gmp($f), to_gmp($G)));
	$b = to_int(karamul(to_gmp($g), to_gmp($F)));
	
	$c= [];
	for($i=0; $i<count($f); $i++)
    	$c[$i] = $a[$i] - $b[$i];
 
	return ($c[0] == q) and all_zeros(array_slice($c,1));
}

// ----------------------------------------------------------------------------

trait Hashing
{

	public function hash_function($str,$length)
	{
		$shake256 = \SHA3::init(\SHA3::SHAKE256);
		$shake256->absorb($str);
		return bin2hex($shake256->squeeze($length/2));
	}

	public function hash_to_point($message, $salt)
	{
        // Hash a message to a point in Z[x] mod(Phi, q)
        // Inspired by the Parse function from NewHope.

		$n = $this->n;
		$q = $this->q;

		$k = (2 ** 16) / $q;

		$digest = $this->hash_function($salt.$message, max(256,16*$n));
        $hashed = [];
		
		foreach(str_split($digest,4) as $hex) {
			$el = hexdec($hex);
			
			if($el < $k * $q) {
				array_push($hashed, imod($el, $q));
				if(count($hashed)==$n) 
					break;
			}
		}
		
        return $hashed;
	}

}

class Signature
{
	static function unpack($packed)
	{
		$parts= explode(':',$packed);
		if(!$parts || count($parts)!=2)
			return NULL;
	
		$salt= @hex2bin($parts[0]);
		if(!$salt) 
			return NULL;

		$packed_len= strlen($parts[1]);
		if($packed_len < 2+8 || $packed_len % 2 != 0)
			return NULL;

		$bin_sign= @hex2bin($parts[1]);
		if(!$bin_sign) 
			return NULL;
	
		$n= @(1<<unpack('C',$bin_sign)[1]);
		if(!in_array($n, [4,8,16,32,64,128,256,512,1024]))
			return NULL;

		if(($packed_len-2)/4 != $n*2)
			return NULL;
	
		$sign= @unpack('s*',$bin_sign, 1);
		if(!$sign || count($sign) != $n*2) 
			return NULL;
	
		return new Signature($salt, array_chunk($sign, $n));
	}
	
	function __construct($salt, $sign)
	{
		$this->n = count($sign[0]);
		$this->salt = $salt;
		$this->sign = $sign;

		$this->signature_bound= 87067565*$this->n/1024;
	}

	function __toString()
	{
		return $this->pack();
	}

	function pack()
	{
		$packed_sign= 
			pack('C', round(log($this->n, 2)))
			.pack('s*', ...$this->sign[0])
			.pack('s*', ...$this->sign[1]);
	
		return bin2hex($this->salt).':'.bin2hex($packed_sign);
	}
	
}

class PublicKey
{
	use Hashing;

	static function unpack($packed)
	{
		$packed_len= strlen($packed);

		if($packed_len < 2+8 || $packed_len % 2 != 0)
			return NULL;

		$bin= @hex2bin($packed);
	
		$n= @(1<<unpack('C',$bin)[1]);
		if(!in_array($n, [4,8,16,32,64,128,256,512,1024]))
			return NULL;

		if(($packed_len-2)/4 != $n)
			return NULL;
	
		$key= @unpack('s*',$bin, 1);
		if(count($key)!=$n)
			return NULL;
	
		return new PublicKey(array_values($key));
	}

	function __construct($h)
	{
        $this->n = count($h);
        $this->q = q;
		$this->h = $h;

		$this->signature_bound= 87067565*$this->n/1024;
	}

	function __toString()
	{
		return $this->pack();
	}

	function pack()
	{
		$packed= 
			pack('C', round(log($this->n, 2)))
			.pack('s*', ...$this->h);
	
		return bin2hex($packed);
	}

	function verify($message, $signature)
	{
		// Verify a signature

		try {
		
			$salt = $signature->salt;
			$s = $signature->sign;
			
			//1. hashes a message to a point of Z[x] mod (Phi,q)
			$hashed = $this->hash_to_point($message, $salt);
			//2. Computes s0 + s1*h
			$result = add_zq($s[0], mul_zq($s[1], $this->h));
			
			// 3. Verifies that the s0 + s1*h = hashed
			for($i=0; $i<$this->n; $i++)
				if($result[$i] != $hashed[$i])
					return FALSE;

			// 4. Verifies that the norm is small
			$norm_sign = 0;
			foreach($s as $part)
				foreach($part as $el)
					$norm_sign+= $el**2;

			if($norm_sign > $this->signature_bound) {
				echo("The squared norm of the signature is too big: $norm_sign\n");
				return FALSE;
			}
		}

		catch(\Exception $err) {
			return NULL;
		}

        // 5. If the previous steps did not fail, accept
		return TRUE;
	}

}

class SecretKey
{
    // This class contains methods for performing secret key operations (and also public key operations) in Falcon.

    // One can perform:
    // - initializing a secret key for:
    //     - n = 8, 16, 32, 64, 128, 256, 512, 1024,
    //     - phi = x ** n + 1,
    //     - q = 12 * 1024 + 1
    // - finding a preimage t of a point c (both in ( Z[x] mod (Phi,q) )**2 ) such that t*B0 = c
    // - hashing a message to a point of Z[x] mod (Phi,q)
    // - sign a message
	// - verify the signature of a message
	
	static function create($n, $rng=NULL)
	{
		if(!$rng) $rng= new SystemRandom();	
        // New a secret key
		$ntru= ntru_gen($n, $rng);

		return new SecretKey($ntru);
	}

	static function unpack($packed)
	{
		$packed_len= strlen($packed);

		if($packed_len< 2+32 || $packed_len % 2 != 0)
			return NULL;

		$bin= @hex2bin($packed);
		if(!$bin)
			return NULL;
	
		$n= @(1<<unpack('C', $bin)[1]);
		if(!in_array($n, [4,8,16,32,64,128,256,512,1024]))
			return NULL;

		if(($packed_len-2)/4 != $n*4)
			return NULL;
	
		$key= @unpack('s*',$bin, 1);
		if(!$key || count($key) != $n*4)
			return NULL;

		list($f, $g, $F, $G)= array_chunk($key, $n);
		if(!check_ntru($f, $g, $F, $G)) {
			return NULL;
		}
	
		return new SecretKey(array_chunk($key, $n));
	}
	
	// ------------------------------------------------------------------------

	use Hashing;

	function __construct($ntru)
	{
        // Initialize a secret key.
		// Public parameters
		
        $this->n = count($ntru[0]);
        $this->q = q; // max 2^16

		// Private key part 1: NTRU polynomials f, g, F, G verifying fG - gF = q mod Phi
		//$ntru= ntru_gen($n);
		$this->f = $ntru[0];
		$this->g = $ntru[1];
		$this->F = $ntru[2];
		$this->G = $ntru[3];

        // Private key part 2: fft's of f, g, F, G
        $this->f_fft = fft($this->f);
        $this->g_fft = fft($this->g);
        $this->F_fft = fft($this->F);
        $this->G_fft = fft($this->G);

        // Private key part 3: from f, g, F, G, compute the basis B0 of a NTRU lattice as well as its Gram matrix and their fft's
        $this->B0 = [[$this->g, neg($this->f)], [$this->G, neg($this->F)]];
        $this->G0 = gram($this->B0);
		
		$this->B0_fft = [];
		foreach($this->B0 as $row)
			array_push($this->B0_fft, array_map('Falcon\fft', $row));
			
        $this->G0_fft = [];
		foreach($this->G0 as $row)
			array_push($this->G0_fft, array_map('Falcon\fft', $row));

        $this->T_fft = ffldl_fft($this->G0_fft);

        // Private key part 4: compute sigma and signature bound
        $slack = 1.1;
		$smooth = 1.28;
		
        $sq_gs_norm = gs_norm($this->f, $this->g, q);
        $this->sigma = $smooth * sqrt($sq_gs_norm);
		$this->signature_bound = $slack * 2 * $this->n * ($this->sigma**2);
		
		//echo "Signature bound: $this->signature_bound\n";

        // Private key part 5: set leaves of tree to be the standard deviations
        normalize_tree($this->T_fft, $this->sigma);

        // Public key: h such that h*f = g mod (Phi,q)
		$this->h = div_zq($this->g, $this->f);
	}

	function __toString()
	{
		return $this->pack();
	}

	public function pack()
	{
		$packed= 
			pack('C',round(log($this->n, 2)))
			.pack('s*', ...$this->f)
			.pack('s*', ...$this->g)
			.pack('s*', ...$this->F)
			.pack('s*', ...$this->G);
	
		return bin2hex($packed);
	}

	public function getPublicKey()
	{
		return new PublicKey($this->h);
	}
	

	private function get_coord_in_fft($point)
	{
		// Compute t such that t*B0 = c
		
        list($c0,$c1) = $point;
        list(list($a, $b), list($c, $d)) = $this->B0_fft;
		
		$c0_fft = fft($c0);
		$c1_fft = fft($c1);

		$t0_fft = []; 
		$t1_fft = [];
		
		for($i=0; $i<$this->n; $i++) {
			array_push($t0_fft, 
				$c0_fft[$i]->multiply($d[$i])->subtract($c1_fft[$i]->multiply($c[$i]))->divideby($this->q));
			array_push($t1_fft, 
				$c1_fft[$i]->multiply($a[$i])->subtract($c0_fft[$i]->multiply($b[$i]))->divideby($this->q));
		}
		
		return [$t0_fft, $t1_fft];
	}


	private function sample_preimage_fft($point)
	{
		// Sample preimage
		
        $B = $this->B0_fft;
        $c = [$point, array_fill(0,$this->n, 0)];
		$t_fft = $this->get_coord_in_fft($c);
		
		$z_fft = ffsampling_fft($t_fft, $this->T_fft);

		$v0_fft = add_fft(mul_fft($z_fft[0], $B[0][0]), mul_fft($z_fft[1], $B[1][0]));
        $v1_fft = add_fft(mul_fft($z_fft[0], $B[0][1]), mul_fft($z_fft[1], $B[1][1]));
        $v0 = array_map('round', ifft($v0_fft));
        $v1 = array_map('round', ifft($v1_fft));

		return [sub($c[0], $v0), sub($c[1], $v1)];
	}

	public function sign($message, $salt=NULL)
	{
        // Sign a message. Needs hash randomization to be secure
		// 1. The message is hashed into a point of Z[x] mod (Phi,q)

		if($salt==NULL) {
			$rand_salt= '';
			for($i=0; $i<8; $i++)
				$rand_salt= $rand_salt.base_convert(random_int(0,0xFFFFFFFF), 10, 16);
			
			$salt= $rand_salt;
		}

		$hashed = $this->hash_to_point($message, $salt);
		
        // 2. A short pre-image of this point is determined
		
		while(1) {
            $s = $this->sample_preimage_fft($hashed);

			// 3. The norm of the signature is checked

			$norm_sign = 0;
			foreach($s as $part)
				foreach($part as $el)
					$norm_sign+= $el**2;

			if($norm_sign < $this->signature_bound)
                return new Signature($salt, $s);
			// else
            //     trace("redo")
		}
	}

	public function verify($message, $signature)
	{
		// Verify a signature

		try {
		
			$salt = $signature->salt;
			$s = $signature->sign;
			
			//1. hashes a message to a point of Z[x] mod (Phi,q)
			$hashed = $this->hash_to_point($message, $salt);
			//2. Computes s0 + s1*h
			$result = add_zq($s[0], mul_zq($s[1], $this->h));
			
			// 3. Verifies that the s0 + s1*h = hashed
			for($i=0; $i<$this->n; $i++)
				if($result[$i] != $hashed[$i]) {
					echo("The signature does not correspond to the hash!\n");
					$ar= join(', ', $hashed);
					echo "desire: [ $ar ]\n";
					$ar= join(', ', $result);
					echo "result: [ $ar ]\n";

					return FALSE;
				}

			// 4. Verifies that the norm is small
			$norm_sign = 0;
			foreach($s as $part)
				foreach($part as $el)
					$norm_sign+= $el**2;

			if($norm_sign > $this->signature_bound) {
				echo("The squared norm of the signature is too big: $norm_sign\n");
				return FALSE;
			}
		}

		catch(Exception $err) {
			return NULL;
		}

        // 5. If the previous steps did not fail, accept
		return TRUE;
	}
}

function createKeyPair($n, $seed=NULL)
{
	$rng= $seed? new SeqRandom($seed): new SystemRandom();

	$sk= SecretKey::create($n, $rng);
	if(!$sk) return NULL;

	$pk= $sk->getPublicKey();
	if(!$pk) return NULL;

	return [$sk->pack(), $pk->pack()];
}

function createPublicKey($hex_sk)
{
	$sk= SecretKey::unpack($hex_sk);
	if(!$sk) return NULL;

	$pk= $sk->getPublicKey();
	if(!$pk) return NULL;

	return $pk->pack();
}

function sign($packed_sk, $message, $salt=NULL)
{
	$sk= SecretKey::unpack($packed_sk);
	if(!$sk) return NULL;

	return $sk->sign($message, $salt)->pack();
}

function verify($packed_pk, $message, $packed_sign)
{
	$pk= PublicKey::unpack($packed_pk);
	if(!$pk) return NULL;

	$sign= Signature::unpack($packed_sign);
	if(!$sign) return NULL;

	return $pk->verify($message, $sign);
}