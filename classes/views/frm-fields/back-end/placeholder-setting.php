<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( $field['type'] !== 'textarea' ) {
	?>
	<input type="text" name="<?php echo esc_attr( $default_name ); ?>" value="<?php echo esc_attr( $default_value ); ?>" id="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>" class="default-value-field" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="value" data-sep="<?php echo esc_attr( $this->displayed_field_type( $field ) ? ',' : '' ); ?>" />
	<?php
} else {
	?>
	<textarea name="<?php echo esc_attr( $default_name ); ?>" class="default-value-field" id="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>" rows="3" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>"><?php
		echo FrmAppHelper::esc_textarea( $default_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?></textarea>
	<?php
}
