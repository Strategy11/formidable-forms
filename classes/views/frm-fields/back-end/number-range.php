<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm8 frm_first frm_form_field frm-number-range">
	<label for="frm_format_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_help frm-font-semibold frm-text-grey-600 frm-mb-xs" title="<?php esc_attr_e( 'Set the number range the field validation should allow. Browsers that support the HTML5 number field require a number range to determine the numbers seen when clicking the arrows next to the field.', 'formidable' ); ?>">
		<?php esc_html_e( 'Number Range', 'formidable' ); ?>
	</label>
	<span class="frm_grid_container">
		<span class="frm6 frm_form_field frm-range-min">
			<label for="frm_minnum_<?php echo esc_attr( $field['field_key'] ); ?>">
				<?php esc_html_e( 'Min Value', 'formidable' ); ?>
			</label>
			<input type="text" id="frm_minnum_<?php echo esc_attr( $field['field_key'] ); ?>" name="field_options[minnum_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['minnum'] ); ?>" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="min" />
		</span>
		<span class="frm6 frm_last frm_form_field">
			<label for="frm_maxnum_<?php echo esc_attr( $field['field_key'] ); ?>">
				<?php esc_html_e( 'Max Value', 'formidable' ); ?>
			</label>
			<input type="text" id="frm_maxnum_<?php echo esc_attr( $field['field_key'] ); ?>" name="field_options[maxnum_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['maxnum'] ); ?>" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="max" />
		</span>
	</span>
</p>
<p class="frm4 frm_last frm_form_field frm-step frm-self-end">
	<label for="frm_step_<?php echo esc_attr( $field['field_key'] ); ?>">
		<?php esc_html_e( 'Step', 'formidable' ); ?>
	</label>
	<input type="text" name="field_options[step_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['step'] ); ?>" id="frm_step_<?php echo esc_attr( $field['field_key'] ); ?>" />
</p>
