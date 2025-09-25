<?php
/**
 * Images dropdown option view
 *
 * @since 5.0.04
 * @package Formidable
 *
 * @var array $args The arguments of images_dropdown() method.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$component_options = array();
foreach ( $args['options'] as $key => $option ) {
	$option['key'] = $key;
	$image_details = FrmAppHelper::get_images_dropdown_atts( $option, $args );

	$component_options[] = array(
		'label'        => $option['text'],
		'value'        => $key,
		'classes'      => $image_details['classes'],
		'custom_attrs' => $image_details['custom_attrs'],
	);
}

new FrmTextToggleStyleComponent(
	$args['name'],
	$args['selected'] ?? 0,
	array(
		'classname'       => $args['classes'],
		'options'         => $component_options,
		'input_attrs_str' => $input_attrs_str,
	)
);
