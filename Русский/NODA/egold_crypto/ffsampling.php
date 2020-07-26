<?php

// This file contains important algorithms for Falcon.

// - the Fast Fourier orthogonalization (in coefficient and FFT representation)
// - the Fast Fourier nearest plane (in coefficient and FFT representation)
// - the Fast Fourier sampling (only in FFT)

namespace Falcon;

require_once 'common.php';
require_once 'fft.php';
require_once 'ntt.php';
require_once 'sampler.php';

function gram($B)
{
    // Compute the Gram matrix of B.

    // Args:
    //     B: a matrix

	// Format: coefficient

    $nrows = count($B);
    $ncols = count($B[0]);
	$deg = count($B[0][0]);
	
	for( $i=0; $i<$nrows; $i++) {
		$G[$i]=[];

		for ( $j=0; $j<$nrows; $j++) {
			$G[$i][$j]= array_fill(0,$deg,0);

            for( $k=0; $k<$ncols; $k++)
				$G[$i][$j] = add($G[$i][$j], mul($B[$i][$k], adj($B[$j][$k])));
		}
	}

    return $G;
}

function ldl($G)
{
    // Compute the LDL decomposition of G.

    // Args:
    //     G: a Gram matrix

    // Format: coefficient

    // Corresponds to algorithm 8 (LDL) of Falcon's documentation,
    // except it's in polynomial representation.
	// 
	
	$dim = count($G);
    $deg = count($G[0][0]);
	
	$L= build_array([$dim,$dim,$deg],0); 
	$D= build_array([$dim,$dim,$deg],0); 
	
	for( $i=0; $i<$dim; $i++ ) {
        $L[$i][$i][0] = 1;
        $D[$i][$i] = $G[$i][$i];
	
		for( $j=0; $j<$i; $j++)	{
			$L[$i][$j] = $G[$i][$j];
			
            for( $k=0; $k<$j; $k++) 
				$L[$i][$j] = sub($L[$i][$j], mul(mul($L[$i][$k], adj($L[$j][$k])), $D[$k][$k]));
				
            $L[$i][$j] = div($L[$i][$j], $D[$j][$j]);
			$D[$i][$i] = sub($D[$i][$i], mul(mul($L[$i][$j], adj($L[$i][$j])), $D[$j][$j]));
		}
	}

	return [$L, $D];
}


function ldl_fft($G)
{
    // Compute the LDL decomposition of G.

    // Args:
    //     G: a Gram matrix

    // Format: FFT

    // Corresponds to algorithm 8 (LDL) of Falcon's documentation.

	$dim = count($G);
	$deg = count($G[0]);
	
	$L= build_array([$dim,$dim,$deg],0); 
	$D= build_array([$dim,$dim,$deg],0); 

    for($i=0; $i<$dim; $i++) {
        $L[$i][$i] = array_fill(0,$deg,1);
		$D[$i][$i] = $G[$i][$i];
		
        for($j=0; $j<$i; $j++) {
			$L[$i][$j] = $G[$i][$j];
			
            for($k=0; $k<$j; $k++)
				$L[$i][$j] = sub_fft($L[$i][$j], mul_fft(mul_fft($L[$i][$k], adj_fft($L[$j][$k])), $D[$k][$k]));
				
            $L[$i][$j] = div_fft($L[$i][$j], $D[$j][$j]);
			$D[$i][$i] = sub_fft($D[$i][$i], mul_fft(mul_fft($L[$i][$j], adj_fft($L[$i][$j])), $D[$j][$j]));
		}
	}

    return [$L, $D];
}

function ffldl($G)
{
    // Compute the ffLDL decomposition tree of G.

    // Args:
    //     G: a Gram matrix

    // Format: coefficient

    // Corresponds to algorithm 9 (ffLDL) of Falcon's documentation,
    // except it's in polynomial representation.

	$n = count($G[0][0]);

	// Coefficients of L, D are elements of R[x]/(x^n - x^(n/2) + 1), in coefficient representation
    list($L, $D) = ldl($G);

	if ($n > 2) {
        // A bisection is done on elements of a 2*2 diagonal matrix.
        list($d00,$d01) = split($D[0][0]);
        list($d10,$d11) = split($D[1][1]);
        $G0 = [[$d00, $d01], [adj($d01), $d00]];
        $G1 = [[$d10, $d11], [adj($d11), $d10]];
		
		return [$L[1][0], ffldl($G0), ffldl($G1)];
	}

    else if ($n == 2) {
        # Bottom of the recursion.
        $D[0][0][1] = 0;
		$D[1][1][1] = 0;
		
		return [$L[1][0], $D[0][0], $D[1][1]];
	}

	return [];
}


function ffldl_fft($G)
{
    // Compute the ffLDL decomposition tree of G.

    // Args:
    //     G: a Gram matrix

    // Format: FFT

    // Corresponds to algorithm 9 (ffLDL) of Falcon's documentation.

	$n = count($G[0][0]) * fft_ratio;
	list($L,$D) = ldl_fft($G);
	
    # Coefficients of L, D are elements of R[x]/(x^n - x^(n/2) + 1), in FFT representation
    if ($n > 2) {
        # A bisection is done on elements of a 2*2 diagonal matrix.
        list($d00,$d01) = split_fft($D[0][0]);
        list($d10,$d11) = split_fft($D[1][1]);
        $G0 = [[$d00, $d01], [adj_fft($d01), $d00]];
        $G1 = [[$d10, $d11], [adj_fft($d11), $d10]];
		
		return [$L[1][0], ffldl_fft($G0), ffldl_fft($G1)];
	}

    else if ($n == 2)
		return [$L[1][0], $D[0][0], $D[1][1]];
		
	return [];
}

function ffnp($t, $T)
{
    // Compute the ffnp reduction of t, using T as auxilary information.

    // Args:
    //     t: a vector
    //     T: a ldl decomposition tree

    // Format: coefficient

	$n = count($t[0]);
	$z = [0,0];
	
    if ($n > 1) {
        list($l10, $T0, $T1) = $T;
        $z[1] = merge(ffnp(split($t[1]), $T1));
        $t0b = add($t[0], mul(sub($t[1], $z[1]), $l10));
        $z[0] = merge(ffnp(split($t0b), $T0));
	}

    else if ($n == 1) {
        $z[0] = [round($t[0][0])];
        $z[1] = [round($t[1][0])];
	}

	return $z;
}

function ffnp_fft($t, $T)
{
    // Compute the ffnp reduction of t, using T as auxilary information.

    // Args:
    //     t: a vector
    //     T: a ldl decomposition tree

    // Format: FFT

	$n = count(t[0]) * fft_ratio;
	$z = [0, 0];
	
    if ($n > 1) {
        list($l10,$T0,$T1) = $T;
        $z[1] = merge_fft(ffnp_fft(split_fft($t[1]), $T1));
        $t0b = add_fft($t[0], mul_fft(sub_fft($t[1], $z[1]), $l10));
        $z[0] = merge_fft(ffnp_fft(split_fft($t0b), $T0));
	}

    elseif ($n == 1) {
        $z[0] = [round($t[0][0]->getReal())];
		$z[1] = [round($t[1][0]->getReal())];
	}
	
	return $z;
}

function ffsampling_fft($t, $T) 
{
    // Compute the ffsampling of t, using T as auxilary information.
	//
    // Args:
    //     t: a vector
    //     T: a ldl decomposition tree
	//
    // Format: FFT
    // Corresponds to algorithm 11 (ffSampling) of Falcon's documentation.
	//

	$n = count($t[0]) * fft_ratio;
	$z = [0, 0];
	
    if ($n > 1) {
        list($l10,$T0,$T1) = $T;
        $z[1] = merge_fft(ffsampling_fft(split_fft($t[1]), $T1));
        $t0b = add_fft($t[0], mul_fft(sub_fft($t[1], $z[1]), $l10));
        $z[0] = merge_fft(ffsampling_fft(split_fft($t0b), $T0));
	}

    else if ($n == 1) {
        $z[0] = [sampler_z($T[0], $t[0][0]->getReal())];
		$z[1] = [sampler_z($T[0], $t[1][0]->getReal())];
	}
	
	return $z;
}