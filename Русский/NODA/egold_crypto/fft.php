<?php

namespace Falcon;

require_once 'vendor/autoload.php';

require_once 'common.php';
require_once 'fft_constants.php';

// ============================================================================

const fft_ratio = 1;

$C_1i= complex(0,1); // 1i
$Cn_1i= complex(0,-1); // -1i
$C05= complex(0.5);	// 0.5

function split_fft($f_fft)
{
	global $C05, $roots_dict;

	$n = count($f_fft); 
	$half_n= intdiv($n,2);
    $w = $roots_dict[$n];

	$f0_fft= []; $f1_fft= [];

	for($i=0; $i<$half_n; $i++)
	{
        $f0_fft[$i] = $C05->multiply(\Complex\add($f_fft[2*$i], $f_fft[2*$i+1]));
		$f1_fft[$i] = $C05->multiply(\Complex\subtract($f_fft[2*$i], $f_fft[2*$i+1]))->multiply($w[2*$i]->conjugate());
	}

    return [$f0_fft, $f1_fft];
}

function merge_fft($f_list)
{
	global $roots_dict;
	
	$f0_fft = $f_list[0];
	$f1_fft = $f_list[1];

	$n= count($f0_fft);
    $w = $roots_dict[$n*2];

	$f_fft= [];

	for($i=0; $i<$n; $i++)
	{
		$wf1_fft= $w[$i*2]->multiply($f1_fft[$i]);
        $f_fft[$i*2]   = $wf1_fft->add($f0_fft[$i]);
		$f_fft[$i*2+1] = $wf1_fft->negative()->add($f0_fft[$i]);
	}

    return $f_fft;
}

function fft($f)
{
	global $C_1i, $Cn_1i;
	$n = count($f);
	
	if ($n > 2) {
        $fs = split($f);
        $f0_fft = fft($fs[0]);
        $f1_fft = fft($fs[1]);
		return merge_fft([$f0_fft, $f1_fft]);
	}

	else if ($n == 2) {
        return [ $C_1i->multiply($f[1])->add($f[0]), $Cn_1i->multiply($f[1])->add($f[0]) ];
	}

    return [];
}

function ifft($f_fft)
{
	$n = count($f_fft);

	if($n > 2) 	{
		$fs = split_fft($f_fft);
		$f0_fft= $fs[0];
		$f1_fft= $fs[1];

        $f0 = ifft($f0_fft);
        $f1 = ifft($f1_fft);

		return merge([$f0, $f1]);
	}
	
	else if ($n == 2) {
        return [$f_fft[0]->getReal(), $f_fft[0]->getImaginary()];
	}
		
    return [];
}

// ----------------------------------------------------------------------------

function neg($f)
{
	$r= [];
	for($i=0; $i<count($f); $i++)
		array_push($r, -$f[$i]);
	
	return $r;
}

function add($f, $g)
{
	$r= [];
	for($i=0; $i<count($f); $i++)
		array_push($r, $f[$i]+$g[$i]);
	
	return $r;
}

function sub($f, $g)
{
	$r= [];
	for($i=0; $i<count($f); $i++)
		array_push($r, $f[$i]-$g[$i]);
	
	return $r;
}

function mul($f, $g)
{
    return ifft(mul_fft(fft($f), fft($g)));
}

function div($f, $g)
{
	return ifft(div_fft(fft($f), fft($g)));
}

function adj($f)
{
	return ifft(adj_fft(fft($f)));
}

function add_fft($f_fft, $g_fft)
{
	$r= [];
	for($i=0; $i<count($f_fft); $i++)
		array_push($r, $f_fft[$i]->add($g_fft[$i]));

	return $r;
}

function sub_fft($f_fft, $g_fft)
{
	$r= [];
	for($i=0; $i<count($f_fft); $i++)
		array_push($r, \Complex\subtract($f_fft[$i], $g_fft[$i]));

	return $r;
}

function mul_fft($f_fft, $g_fft)
{
	$r= [];
	for($i=0; $i<count($f_fft); $i++)
		array_push($r, \Complex\multiply($f_fft[$i], $g_fft[$i]));

	return $r;
}

function div_fft($f_fft, $g_fft)
{
	$r= [];
	for($i=0; $i<count($f_fft); $i++)
		array_push($r, \Complex\divideby($f_fft[$i],$g_fft[$i]));

	return $r;
}

function adj_fft($f_fft)
{
	$r= [];
	for($i=0; $i<count($f_fft); $i++)
		array_push($r, $f_fft[$i]->conjugate());
	
	return $r;
}


