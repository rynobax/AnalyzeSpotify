<?php
function generate_n_distinct_colors($num_colors){
	$colors = array();
	for($i = 0; $i < 360; $i += 360 / $num_colors) {
	    $hue = $i;
	    $saturation = 90 + lcg_value() * 10;
	    $lightness = 50 + lcg_value() * 10;

	    $rgb = ColorHSLToRGB($hue, $saturation, $lightness);
	    array_push($colors, $rgb);
	}
	return $colors;
}

function ColorHSLToRGB($h, $s, $l){
	//http://stackoverflow.com/questions/20423641/php-function-to-convert-hsl-to-rgb-or-hex
		$h /= 360;
	    $s /=100;
	    $l /=100;

        $r = $l;
        $g = $l;
        $b = $l;
        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
        if ($v > 0){
              $m;
              $sv;
              $sextant;
              $fract;
              $vsf;
              $mid1;
              $mid2;

              $m = $l + $l - $v;
              $sv = ($v - $m ) / $v;
              $h *= 6.0;
              $sextant = floor($h);
              $fract = $h - $sextant;
              $vsf = $v * $sv * $fract;
              $mid1 = $m + $vsf;
              $mid2 = $v - $vsf;

              switch ($sextant)
              {
                    case 0:
                          $r = $v;
                          $g = $mid1;
                          $b = $m;
                          break;
                    case 1:
                          $r = $mid2;
                          $g = $v;
                          $b = $m;
                          break;
                    case 2:
                          $r = $m;
                          $g = $v;
                          $b = $mid1;
                          break;
                    case 3:
                          $r = $m;
                          $g = $mid2;
                          $b = $v;
                          break;
                    case 4:
                          $r = $mid1;
                          $g = $m;
                          $b = $v;
                          break;
                    case 5:
                          $r = $v;
                          $g = $m;
                          $b = $mid2;
                          break;
              }
        }
        return array('r' => round($r * 255.0), 'g' => round($g * 255.0), 'b' => round($b * 255.0));
}
?>