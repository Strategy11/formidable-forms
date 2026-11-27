<?php
if ( $x === '' || ! $x ) {
	$a = true;
}
if ( ! $x || $x === 0 ) {
	$b = true;
}
if ( $x == false || ! $x ) {
	$c = true;
}
if ( ! $x || $x == null ) {
	$d = true;
}
if ( ! $x || $x === null ) {
	$e = true;
}
if ( $x === false || ! $x ) {
	$f = true;
}
if ( ! $x || $x == 0 ) {
	$g = true;
}
if ( ! $x || $x == 000 ) {
	$h = true;
}
if ( $x == 0.0 || ! $x ) {
	$i = true;
}
if ( ! $x || $x == '0' ) {
	$j = true;
}
if ( $x == '' || ! $x ) {
	$k = true;
}
if ( ! $x || $x === '0.00' ) {
	$l = true;
}
if ( ! $x || $y === '' ) {
	$m = true;
}
if ( ! $x || $x === '' || $y ) {
	$n = true;
}
// Yoda comparisons are not recognised; pin them as not flagged.
if ( ! $x || null == $x ) {
	$o = true;
}
if ( ! $x || 0 == $x ) {
	$p = true;
}
if ( false == $x || ! $x ) {
	$q = true;
}
