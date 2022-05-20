<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$replacement = FrmAppHelper::plugin_path() . '/classes/views/frm-fields/front-end/dropdown-field.php';
_deprecated_file( esc_html( basename( __FILE__ ) ), '5.0.13', esc_html( $replacement ) );

if ( ! isset( $read_only ) ) {
	$read_only = FrmField::get_option( $field, 'read_only' );
}

if ( ! isset( $html_id ) ) {
	$html_id = isset( $field['html_id'] ) ? $field['html_id'] : FrmFieldsHelper::get_html_id( $field );
}

require $replacement;
