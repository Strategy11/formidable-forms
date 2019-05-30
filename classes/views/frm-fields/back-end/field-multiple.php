<?php

_deprecated_file( esc_html( basename( __FILE__ ) ), '4.0', null );

if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} else {
	$read_only  = $field['read_only'];
	include( dirname( __FILE__ ) . '/' . $field['type'] . '-field.php' );
}
