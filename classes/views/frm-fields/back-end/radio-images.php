<?php
/**
 * View file for image options of Radio or Checkbox field.
 *
 * @package Formidable
 *
 * @var array $args Arguments. Contains `field`, `display` and `values`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmFieldsHelper::show_radio_display_format( $args['field'] );

include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/upsell/separate-values.php';
