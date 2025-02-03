<?php
/**
 * @package Formidable
 * @since x.x
 *
 * @var array        $field Field data.
 * @var array        $args  Includes 'field', 'display', and 'values' settings.
 * @var FrmFieldType $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_type = $field['type'];
$format     = FrmField::get_option( $field, 'format' );

FrmHtmlHelper::echo_dropdown_option( __( 'None', 'formidable' ), '' === $format, array( 'value' => 'none' ) );

FrmHtmlHelper::echo_dropdown_option(
	in_array( $field_type, array( 'number', 'range' ), true ) ? __( 'Custom', 'formidable' ) : __( 'Number', 'formidable' ),
	false,
	array(
		'value'        => '',
		'class'        => 'frm_show_upgrade frm_noallow',
		'data-upgrade' => __( 'Format number field', 'formidable' ),
		'data-medium'  => 'format-number-field',
	)
);

if ( 'text' === $field_type ) {
	FrmHtmlHelper::echo_dropdown_option(
		__( 'Custom', 'formidable' ),
		! empty( $format ) && 'currency' !== $format,
		array(
			'value'           => 'custom',
			'data-dependency' => '#frm-field-format-custom-' . $field_id,
		)
	);
}
