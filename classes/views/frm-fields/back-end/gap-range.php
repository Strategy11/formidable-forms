<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_form_field frm-gap-range">
	<label for="frm_format_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Set the gap range the field validation should allow.', 'formidable' ); ?>">
		<?php esc_html_e( 'Gap Range', 'formidable' ); ?>
	</label>
	<div class="frm_grid_container">
		<p class="frm6">
			<label for="frm_min_gap_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_form_field frm-gap-min">
				<?php esc_html_e( 'Min Gap', 'formidable' ); ?>
			</label>
			<input id="frm_min_gap_<?php echo esc_attr( $field['field_key'] ); ?>" type="text" name="field_options[mingap_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['mingap'] ); ?>" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="min" />
		</p>
		<p class="frm6">
			<label for="frm_max_gap_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_last frm_form_field">
				<?php esc_html_e( 'Max Gap', 'formidable' ); ?>
			</label>
			<input id="frm_max_gap_<?php echo esc_attr( $field['field_key'] ); ?>" type="text" name="field_options[maxgap_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['maxgap'] ); ?>" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="max" />
		</p>
	</div>
</div>
