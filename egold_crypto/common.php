<?php

namespace Falcon;

// q is the integer modulus which is used in Falcon
const q = 12 * 1024 + 1;

function complex($r,$i=0)
{
	return new \Complex\Complex([$r,$i]); 
}

function imod($x,$y)
{
    return ($x-floor($x/$y)*$y);    
}

function mod_q($x)
{
    return $x-floor($x/q)*q;    
}

function uniform($min, $max)
{
    return $min+mt_rand()/mt_getrandmax()*($max-$min);
}

function gauss($mu, $sigma)
{
    $x = mt_rand()/mt_getrandmax();
    $y = mt_rand()/mt_getrandmax();
    return sqrt(-2*log($x))*cos(2*pi()*$y)*$sigma + $mu;
}



// ----------------------------------------------------------------------------

function build_array($dim, $fill=0)
{
	if(count($dim)>1)
	{
		$A= [];
		$sdim= array_slice($dim,1);
		for($i=0; $i<$dim[0]; $i++)	$A[$i]= build_array($sdim,$fill);
		return $A;
	}

	else if(count($dim)==1)
		return array_fill(0,$dim[0],$fill);

	return [];
}

function all_zeros($a) 
{
	foreach($a as $i)
		if($i!=0) return 0;
	
	return 1;
}

function split($a)
{
	$a0=[]; $a1=[];

	for($i=0; $i<count($a)-1; $i+=2)
	{
		array_push($a0, $a[$i]);
		array_push($a1, $a[$i+1]);
	}

	return [$a0, $a1];
}

function merge($a)
{
	$a0= $a[0];
	$a1= $a[1];
	$u= [];

	for($i=0; $i<count($a0); $i++)
	{
		array_push($u, $a0[$i]);
		array_push($u, $a1[$i]);
	}

	return $u;
}

// Compute the square euclidean norm of the vector v

function sqnorm($v)
{
	$res = 0;

	foreach($v as $elt)
        foreach($elt as $c)
			$res += $c ** 2;
	
	return $res;
}