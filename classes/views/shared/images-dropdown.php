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
	$component_options[] = array(
		'label' => $option['text'],
		'value' => $key,
	);
}

new FrmTextToggleStyleComponent(
	$args['name'],
	$args['selected'] ?? 0,
	array(
		'classname'       => $args['classes'],
		'options'         => $component_options,
		'input-classname' => $args['input_attrs']['class'] ?? '',
		'data-fid'        => $args['input_attrs']['data-fid'] ?? '',
	)
);
