<?php

namespace Falcon;

require_once 'common.php';
require_once 'ntt_constants.php';

const i2 = 6145;


// sqr1 is a square root of (-1) mod q (currently, sqr1 = 1479)
$sqr1 = $roots_dict_Zq[2][0];

function split_ntt($f_ntt)
{
    global $roots_dict_Zq, $inv_mod_q;
    $n = count($f_ntt);
    $w = $roots_dict_Zq[$n];

    $f0_ntt = [];
    $f1_ntt = [];

    for($i=0; $i<intdiv($n,2); $i++)
    {
        array_push($f0_ntt, mod_q(i2 * ($f_ntt[$i*2] + $f_ntt[$i*2+1])));
        array_push($f1_ntt, mod_q(i2 * ($f_ntt[$i*2] - $f_ntt[$i*2+1]) * $inv_mod_q[$w[$i*2]]));
    }

    return [$f0_ntt, $f1_ntt];
}

function merge_ntt($f_list_ntt)
{
    global $roots_dict_Zq, $inv_mod_q;

    $f0_ntt = $f_list_ntt[0];
    $f1_ntt = $f_list_ntt[1];
    
    $n = count($f0_ntt);
    $w = $roots_dict_Zq[$n*2];
    $f_ntt = [];
    
    for($i=0; $i<$n; $i++)
    {
        $f_ntt[$i*2] = mod_q($f0_ntt[$i] + $w[$i*2] * $f1_ntt[$i]);
        $f_ntt[$i*2 + 1] = mod_q($f0_ntt[$i] - $w[$i*2] * $f1_ntt[$i]);
    }

    return $f_ntt;
}

function ntt($f)
{
    global $sqr1;
    $n = count($f);

    if ($n > 2) {
        $f_s= split($f);
        $f0_ntt = ntt($f_s[0]);
        $f1_ntt = ntt($f_s[1]);
        
        return merge_ntt([$f0_ntt, $f1_ntt]);
    }
    else if ($n == 2) 
    {
        return [ 
            mod_q($f[0] + $sqr1 * $f[1]), 
            mod_q($f[0] - $sqr1 * $f[1]) 
        ];
    }

    return [];
}

function intt($f_ntt)
{
    global $inv_mod_q;

    $n = count($f_ntt);
    
    if ($n > 2) {
        $f_ntt_s = split_ntt($f_ntt);

        $f0 = intt($f_ntt_s[0]);
        $f1 = intt($f_ntt_s[1]);

        return merge([$f0, $f1]);
    }
    
    else if($n == 2)
        return [ 
            mod_q(i2 * ($f_ntt[0]+$f_ntt[1])), 
            mod_q(i2 * $inv_mod_q[1479] * ($f_ntt[0]-$f_ntt[1])) 
        ];

    return [];
}


function neg_zq($f)
{
    $deg = count($f);
    $r= [];

    for($i=0; $i<$deg; $i++) 
        $r[$i]= mod_q(-$f[$i]);

    return $r;
}

function add_zq($f, $g)
{
    $deg = count($f);
    $r= [];
    
    for($i=0; $i<$deg; $i++) 
        $r[$i]= mod_q($f[$i] + $g[$i]);
    
    return $r;
}

function sub_zq($f, $g)
{
    $deg = count($f);
    $r= [];
    
    for($i=0; $i<$deg; $i++) 
        $r[$i]= mod_q($f[$i] - $g[$i]);
    
    return $r;
}
    
function mul_zq($f, $g)
{
    return intt(mul_ntt(ntt($f), ntt($g)));
}

function div_zq($f, $g)
{
    return intt(div_ntt(ntt($f), ntt($g)));
}

function add_ntt($f_ntt, $g_ntt)
{
    return add_zq($f_ntt, $g_ntt);
}

function sub_ntt($f_ntt, $g_ntt)
{
    return sub_zq($f_ntt, $g_ntt);
}

function mul_ntt($f_ntt, $g_ntt)
{
    $deg = count($f_ntt);
    $r= [];
    
    for($i=0; $i<$deg; $i++) 
        $r[$i]= mod_q($f_ntt[$i] * $g_ntt[$i]);
    
    return $r;
}
    
function div_ntt($f_ntt, $g_ntt)
{
    global $inv_mod_q;

    $deg = count($f_ntt);
    $n_imq= count($inv_mod_q);

    $r= [];
    
    for($i=0; $i<$deg; $i++) 
        if($g_ntt[$i] > 0)
            $r[$i]= mod_q($f_ntt[$i] * $inv_mod_q[$g_ntt[$i]]);
        else if($g_ntt[$i] < 0)
            $r[$i]= mod_q($f_ntt[$i] * $inv_mod_q[$n_imq+$g_ntt[$i]]);
        else
            throw new DivisionByZeroError();
    
    return $r;
}

// This value is the ratio between:
//     - The degree n
//     - The number of complex coefficients of the NTT
// While here this ratio is 1, it is possible to develop a short NTT such that it is 2.

const ntt_ratio = 1;
