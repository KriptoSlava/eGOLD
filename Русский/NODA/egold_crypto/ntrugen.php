<?php

// This file implements the section 3.8.2 of Falcon's documentation

namespace Falcon;

require_once 'common.php';
require_once 'fft.php';
require_once 'ntt.php';

//const q = 12 * 1024 + 1;

// ----------------------------------------------------------------------------

function to_gmp($value)
{
	if(is_a($value, 'GMP'))
		return $value;

	if(is_numeric($value))
		return gmp_init((int)$value);

	if(is_array($value))
	{
		$result= [];
		foreach($value as $el)	
			array_push($result, to_gmp($el));
		
		return $result;
	}
}

function to_int($value)
{
	if(is_a($value, 'GMP'))
		return gmp_intval($value);

	if(is_numeric($value))
		return $value;

	if(is_array($value))
	{
		$result= [];
		foreach($value as $el)	
			array_push($result, to_int($el));
		
		return $result;
	}
}

function gmp_rshift($x, $n)
{
	$d= gmp_init('0');
	gmp_setbit($d,$n);
	return gmp_div($x, $d, \GMP_ROUND_MINUSINF);
}

function gmp_lshift($x, $n)
{
	$d= gmp_init('0');
	gmp_setbit($d,$n);
	return gmp_mul($x, $d);
}

// ----------------------------------------------------------------------------

function karatsuba($a, $b, $n)
{
    // Karatsuba multiplication between polynomials.
    // The coefficients may be either integer or real.

	if($n == 1)
		return [gmp_mul($a[0],$b[0]), 0];
		
    else {
        $n2 = intdiv($n,2);
        $a0 = array_slice($a, 0,$n2);
        $a1 = array_slice($a, $n2);
        $b0 = array_slice($b, 0,$n2);
        $b1 = array_slice($b, $n2);
		
		$ax=[]; $bx=[];
		for($i=0; $i<$n2; $i++) {
			$ax[$i] = gmp_add($a0[$i], $a1[$i]);
			$bx[$i] = gmp_add($b0[$i], $b1[$i]);
		}
		
		$a0b0 = karatsuba($a0, $b0, $n2);
        $a1b1 = karatsuba($a1, $b1, $n2);
        $axbx = karatsuba($ax, $bx, $n2);
		
		for($i=0; $i<$n; $i++)
			$axbx[$i] = gmp_sub($axbx[$i], gmp_add($a0b0[$i], $a1b1[$i]));
			
        $ab = array_fill(0,2*$n,0);
        for($i=0; $i<$n; $i++) {
            $ab[$i] = gmp_add($ab[$i], $a0b0[$i]);
            $ab[$i + $n] = gmp_add($ab[$i + $n], $a1b1[$i]);
			$ab[$i + $n2] = gmp_add($ab[$i + $n2], $axbx[$i]);
		}
		
		return $ab;
	}
}

function karamul($a, $b)
{
    // """Karatsuba multiplication, followed by reduction mod (x ** n + 1)."""
    $n = count($a);

	$ab = karatsuba($a, $b, $n);
	$abr = [];

	for($i=0; $i<$n; $i++)
		$abr[$i] = gmp_sub($ab[$i], $ab[$i + $n]);
	
	return $abr;
}

function galois_conjugate($a)
{
    // Galois conjugate of an element a in Q[x] / (x ** n + 1).
    // Here, the Galois conjugate of a(x) is simply a(-x).

	$result= [];

	for($i=0; $i<count($a); $i++)
		array_push($result, $i&1? gmp_neg($a[$i]): $a[$i]);

	return $result;
}

function field_norm($a)
{
    // Project an element a of Q[x] / (x ** n + 1) onto Q[x] / (x ** (n // 2) + 1).
	// Only works if n is a power-of-two.
	
    $n2 = intdiv(count($a),2);
	
	$ae = [];
	for($i=0; $i<$n2; $i++)	
		$ae[$i]= $a[2 * $i];

	$ao = []; 
	for($i=0; $i<$n2; $i++)	
		$ao[$i]= $a[2 * $i + 1];

    $ae_squared = karamul($ae, $ae);
    $ao_squared = karamul($ao, $ao);

	$res = array_replace([], $ae_squared);
	for($i=0; $i<($n2 - 1); $i++)
		$res[$i + 1] = gmp_sub($res[$i + 1], $ao_squared[$i]);
		
    $res[0] = gmp_add($res[0], $ao_squared[$n2 - 1]);

	return $res;
}

function lift($a)
{
    // Lift an element a of Q[x] / (x ** (n // 2) + 1) up to Q[x] / (x ** n + 1)
    // The lift of a(x) is simply a(x ** 2) seen as an element of Q[x] / (x ** n + 1)

	$n = count($a);
    $res = array_fill(0,2*$n,0);
    for($i=0; $i<$n; $i++)
        $res[2 * $i] = $a[$i];
	
	return $res;
}

function bitsize($a)
{
    $val = gmp_abs($a);
	$res = 0;
	
	for($i=16; $i>=1; $i/=2) {
		$d= 1<<$i;
		while (gmp_cmp($val,$d)>=0) {
			$res += $i;
			$val= gmp_div($val,$d);
		}
	}

	return $res+gmp_intval($val);
}

function reduce($f, $g, $F, $G)
{
    // Reduce (F, G) relatively to (f, g)

    // This is done via Babai's reduction
    // (F, G) <-- (F, G) - k * (f, g), where k = round((F f* + G g*) / (f f* + g g*))
	// Corresponds to algorithm 7 (Reduce) of Falcon's documentation
	
	$n = count($f);
    $fg_bitlen = max(53, bitsize(min($f)), bitsize(max($f)), bitsize(min($g)), bitsize(max($g)));

	$f_adjust = [];
	for($i=0; $i<$n; $i++)
		$f_adjust[$i]= gmp_intval(gmp_rshift($f[$i], ($fg_bitlen - 53)));

	$g_adjust = [];
	for($i=0; $i<$n; $i++)
		$g_adjust[$i]= gmp_intval(gmp_rshift($g[$i], ($fg_bitlen - 53)));
		
    $fa_fft = fft($f_adjust);
	$ga_fft = fft($g_adjust);
	
	$iter= 0;

    while(1) {
		
        $FG_bitlen = max(53, bitsize(min($F)), bitsize(max($F)), bitsize(min($G)), bitsize(max($G)));
		
		if($FG_bitlen < $fg_bitlen) break;

		$F_adjust = [];
		for($i=0; $i<$n; $i++)
			$F_adjust[$i]= gmp_intval(gmp_rshift($F[$i], ($FG_bitlen - 53)));
	
		$G_adjust = [];
		for($i=0; $i<$n; $i++)
			$G_adjust[$i] = gmp_intval(gmp_rshift($G[$i], ($FG_bitlen - 53)));

		$Fa_fft = fft($F_adjust);
        $Ga_fft = fft($G_adjust);

        $den_fft = add_fft(mul_fft($fa_fft, adj_fft($fa_fft)), mul_fft($ga_fft, adj_fft($ga_fft)));
        $num_fft = add_fft(mul_fft($Fa_fft, adj_fft($fa_fft)), mul_fft($Ga_fft, adj_fft($ga_fft)));
		$k_fft = div_fft($num_fft, $den_fft);

		$k = ifft($k_fft);
		for($i=0; $i<count($k); $i++) 
			$k[$i]= (int)round($k[$i]);

		if(all_zeros($k)) break;

		$k= to_gmp($k);

        $fk = karamul($f, $k);
		$gk = karamul($g, $k);
		
		for($i=0; $i<$n; $i++) {
            $F[$i] = gmp_sub($F[$i], gmp_lshift($fk[$i], ($FG_bitlen - $fg_bitlen)));
			$G[$i] = gmp_sub($G[$i], gmp_lshift($gk[$i], ($FG_bitlen - $fg_bitlen)));
		}
	}

    return [$F, $G];
}

function ntru_solve($f, $g)
{
    // Solve the NTRU equation for f and g.
    // Corresponds to algorithm 6 (NTRUSolve) of Falcon's documentation.

	$n = count($f);
	
	if($n == 1) {
        $f0 = $f[0];
        $g0 = $g[0];
		//list($d,$u,$v) = xgcd($f0, $g0);
		$gcd= gmp_gcdext($f[0], $g[0]);
		$d= gmp_intval($gcd['g']);
		
        if($d != 1)
			throw new \Exception("ntru_solve: value error d=${d}\n");
        else
			return [[gmp_mul($gcd['t'], -q)], [gmp_mul($gcd['s'], q)]];
	}

    else {
        $fp = field_norm($f);
        $gp = field_norm($g);

		list($Fp,$Gp) = ntru_solve($fp, $gp);

		$F = karamul(lift($Fp), galois_conjugate($g));
        $G = karamul(lift($Gp), galois_conjugate($f));

		list($F,$G) = reduce($f, $g, $F, $G);
		
		return [$F, $G];
	}
}

function gs_norm($f, $g, $q) 
{
    // Compute the squared Gram-Schmidt norm of the NTRU matrix generated by f, g

    // This matrix is [[g, - f], [G, - F]]
    // This algorithm is equivalent to line 9 of algorithm 5 (NTRUGen)

	$sqnorm_fg = sqnorm([$f, $g]);
    $ffgg = add(mul($f, adj($f)), mul($g, adj($g)));
    $Ft = div(adj($g), $ffgg);
    $Gt = div(adj($f), $ffgg);
    $_sqnorm_FG = ($q ** 2) * sqnorm([$Ft, $Gt]);
	
	return max($sqnorm_fg, $_sqnorm_FG);
}

function ntru_gen($n, $rng)
{
    // Implement the algorithm 5 (NTRUGen) of Falcon's documentation.

    // At the end of the function, polynomials f, g, F, G in Z[x]/(x ** n + 1)
    // are output, which verify f * G - g * F = 1 mod (x ** n + 1).

	while(1) {
		$sigma = 1.17 * sqrt(q / (2. * $n));
		
		$f = [];
		for($i=0; $i<$n; $i++)
			$f[$i]= round($rng->gauss(0, $sigma));

		$g = [];
		for($i=0; $i<$n; $i++)
			$g[$i]= round($rng->gauss(0, $sigma));
		
        try {
			
            if (gs_norm($f, $g, q) < (1.17 ** 2) * q) {
				
				list($F,$G) = ntru_solve(to_gmp($f), to_gmp($g));

				$F= to_int($F);
				$G= to_int($G);

				return [$f, $g, $F, $G];
			}
		}

		// # If f is not invertible, a ZeroDivisionError is raised
		// # If the NTRU equation cannot be solved, a ValueError is raised
		// # In both cases, we start again

		catch (\DivisionByZeroError $e) {
			//echo 'Division by zero';
			continue;
		}

        catch (\Exception $e) {
			//echo $e->getMessage();
			continue;
		}
	}
}
