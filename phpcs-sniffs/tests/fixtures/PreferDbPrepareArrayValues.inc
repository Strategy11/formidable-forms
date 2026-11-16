<?php
$a = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
$b = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
$c = implode( ', ', array_fill( 1, count( $ids ), '%d' ) );
$d = implode( ', ', $obj->array_fill( 0, count( $ids ), '%d' ) );
$e = implode( ', ', array_fill( 0, count( get_ids() ), '%d' ) );
$f = $obj->implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
$g = Tools::implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
$h = implode( ', ', array_fill( 0, count( $ids ), '%s' ), );
$i = implode( ', ', array_fill( 0, count( $ids ), '%s' ) );
$j = implode( ", ", array_fill( 0, count( $ids ), "%f" ) );
