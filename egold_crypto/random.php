<?php

namespace Falcon;

require_once 'vendor/autoload.php';

use lastguest\Murmur;

class SystemRandom
{

	function uniform($min, $max)
	{
		return $min+(float)random_int(0,0xFFFFFFFF)/0xFFFFFFFF*($max-$min);
	}
	
	function gauss($mu, $sigma)
	{
		$x = (float)random_int(0,0xFFFFFFFF)/0xFFFFFFFF;
		$y = (float)random_int(0,0xFFFFFFFF)/0xFFFFFFFF;

		return sqrt(-2*log($x))*cos(2*pi()*$y)*$sigma + $mu;
	}
}

class SeqRandom
{
	function __construct($seed)
	{
		$this->next= 0;
		$this->seed= $seed;
		$this->seed_len= strlen($seed);
		$this->prev= Murmur::hash3_int(substr($this->seed, -4, 4));
	}

	function getNext()
	{
		if($this->next > $this->seed_len-4) {
			echo "Falcon.Random: seed sequence is too short!\n";
			$this->seed= substr($this->seed, -1) . substr($this->seed, 0,$this->seed_len-1);
			$this->next= 0;
		}
		
		$hash32= Murmur::hash3_int(substr($this->seed, $this->next, 4).strval($this->prev));
		$this->next+= 2;
		$this->prev= $hash32;
		
		return $hash32;
	}

	function uniform($min, $max)
	{
		return $min+$this->getNext()/0xFFFFFFFF*($max-$min);
	}

	function gauss($mu, $sigma)
	{
		$x = $this->getNext()/0xFFFFFFFF;
		$y = $this->getNext()/0xFFFFFFFF;
		
		return sqrt(-2*log($x))*cos(2*pi()*$y)*$sigma + $mu;
	}
}


