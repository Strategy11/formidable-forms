<?php
/**
 * Text input for the default value setting.
 *
 * @package Formidable
 *
 * @var FrmFieldType     $this          Field type handler that included this template.
 * @var array            $field         Field data including 'id' and 'field_key'.
 * @var string           $default_name  HTML name attribute for the input.
 * @var int|float|string $default_value Current default value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<input type="text" name="<?php echo esc_attr( $default_name ); ?>" value="<?php echo esc_attr( $default_value ); ?>" id="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>" class="default-value-field" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="value" data-sep="<?php echo esc_attr( $this->displayed_field_type( $field ) ? ',' : '' ); ?>" />
