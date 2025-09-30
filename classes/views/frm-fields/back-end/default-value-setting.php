<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p
	class="frm-has-modal frm-default-value-wrapper default-value-section-<?php echo esc_attr( $field['id'] ); ?>"
	<?php $field_obj->echo_field_default_setting_attributes( $field ); ?>
	id="default-value-for-<?php echo esc_attr( $field['id'] ); ?>"
>
	<label class="frm-force-flex frm-gap-xs frm-items-center" for="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>">
		<span><?php esc_html_e( 'Default Value', 'formidable' ); ?></span>
		<?php FrmAppHelper::tooltip_icon( __( 'Pre-fill your field with this value. Users can modify it unless the field is read-only.', 'formidable' ), array( 'class' => 'frm-flex' ) ); ?>
	</label>

	<span class="frm-flex-col frm-with-right-icon">
		<?php
		if ( isset( $display['default_value'] ) && $display['default_value'] ) {
			$default_name  = 'field_options[dyn_default_value_' . $field['id'] . ']';
			$default_value = $field['dyn_default_value'] ?? '';
		} else {
			$default_name  = 'default_value_' . $field['id'];
			$default_value = $field['default_value'];
		}

		$field_obj->default_value_to_string( $default_value );
		$field_obj->show_default_value_field( $field, $default_name, $default_value );
		$field_obj->display_smart_values_modal_trigger_icon( $field );
		?>
	</span>
</p>
