<?php
wp_send_json( $a );
wp_die();
wp_send_json_success( $b );
die();
wp_send_json_error( $c, 400 );
exit;
\wp_send_json( $d );
exit();
WP_SEND_JSON_ERROR( $e );
die;
if ( $f ) :
	wp_send_json( $f );
	wp_die();
endif;
switch ( $g ) {
	case 1:
		wp_send_json( $g );
		wp_die();
}
function send_one() {
	wp_send_json_success(
		array( 'x' => 1 )
	);

	wp_die();
}
// A different function before the terminator.
wp_send_json_foo( $h );
wp_die();
echo 'done';
wp_die();
// Not directly after.
wp_send_json( $j );
$k = 1;
wp_die();
// In a different block.
if ( $l ) {
	wp_send_json( $l );
}
wp_die();
// Brace-less conditional.
if ( $m ) wp_send_json( $m );
wp_die();
// Terminator with arguments.
wp_send_json( $n );
wp_die( 'Done' );
// Comment between.
wp_send_json( $o );
// Keep this note.
wp_die();
// Method, static and namespaced calls.
$obj->wp_send_json( $p );
wp_die();
Helper::wp_send_json( $q );
die();
Foo\wp_send_json( $r );
exit;
// Return value used.
$s = wp_send_json( $s );
wp_die();
// Conditional terminator.
wp_send_json( $t );
if ( $t ) {
	wp_die();
}
// Declaration.
function wp_send_json_local() {}
// Inline HTML between.
wp_send_json( $u ); ?>
<p>x</p>
<?php wp_die();
// Exit with a status.
wp_send_json( $v );
exit( 1 );
