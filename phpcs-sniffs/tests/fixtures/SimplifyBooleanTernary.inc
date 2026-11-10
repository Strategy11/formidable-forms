<?php
$a = $x === 1 ? true : false;
$b = $x && $y ? false : true;
$c = $x ? true : false;
$d = $x === 1 ? true : true;
$e = $x === 1 ? true : false || $fallback;
$f = $guard ? ( $x === 1 ? true : $other ) : false;
$g = $x === 1 ? true : ( false || $fallback );
$h = $x === 1 ? /* keep */ true : false;
