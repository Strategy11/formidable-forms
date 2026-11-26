<?php
echo wp_json_encode( $a );
wp_die();
echo json_encode( $b );
die();
echo json_encode( array( 'x' => 1, 'y' => 2 ) );
exit;
echo \wp_json_encode( $c );
exit();
function respond() {
	echo json_encode(
		array(
			'a' => 1,
		)
	);

	wp_die();
}
if ( $d ) :
	echo wp_json_encode( $d );
	wp_die();
endif;
// Followed by other output.
echo json_encode( $e );
echo 'more';
wp_die();
// Concatenated output.
echo json_encode( $f ) . "\n";
wp_die();
// print is not echo.
print wp_json_encode( $g );
wp_die();
// Encoder flags have no faithful rewrite.
echo wp_json_encode( $h, JSON_PRETTY_PRINT );
wp_die();
// Brace-less conditional.
if ( $i ) echo wp_json_encode( $i );
wp_die();
// Different block.
if ( $j ) {
	echo wp_json_encode( $j );
}
wp_die();
// Not directly followed.
echo wp_json_encode( $k );
$l = 1;
wp_die();
// Terminator with a message.
echo wp_json_encode( $m );
wp_die( 'x' );
// Comment between.
echo wp_json_encode( $n );
// Keep this note.
wp_die();
// Other encoder.
echo maybe_json_encode( $o );
wp_die();
// Already wp_send_json with a status.
wp_send_json( $p, 400 );
// Multiple echo arguments.
echo json_encode( $q ), 'x';
wp_die();
// Method call.
echo $obj->json_encode( $r );
wp_die();
// Conditional terminator.
echo wp_json_encode( $s );
if ( $s ) {
	wp_die();
}
// Inline HTML between.
echo wp_json_encode( $t ); ?>
<p>x</p>
<?php wp_die();
// Short echo tag.
?><?= wp_json_encode( $u ); ?><?php
wp_die();
