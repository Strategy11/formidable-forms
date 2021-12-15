<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

_deprecated_file( esc_html( basename( __FILE__ ) ), '3.0', null, 'FrmFieldType::field_input' );

$field_obj = FrmFieldFactory::get_field_type( $field['type'], $field );
echo $field_obj->include_front_field_input( compact( 'errors', 'form', 'html_id', 'field_name' ), $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
