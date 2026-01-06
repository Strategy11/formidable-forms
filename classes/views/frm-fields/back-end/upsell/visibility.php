<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since x.x
 *
 * @var array $field
 */
?>
<p class="frm6 frm_form_field frm_show_upgrade">
	<label class="frm-h-stack-xs" id="for_field_options_admin_only_<?php echo absint( $field['id'] ); ?>" for="field_options_admin_only_<?php echo absint( $field['id'] ); ?>">
		<span><?php esc_html_e( 'Visibility', 'formidable' ); ?></span>
		<?php
		FrmAppHelper::tooltip_icon(
			__( 'Determines who can see this field.', 'formidable' ),
			array(
				'data-placement' => 'right',
				'class'          => 'frm-flex',
			)
		);
		?>
	</label>
	<select readonly name="field_options[admin_only_<?php echo absint( $field['id'] ); ?>][]" id="field_options_admin_only_<?php echo absint( $field['id'] ); ?>" class="" data-upgrade="<?php esc_attr_e( 'Visibility options', 'formidable' ); ?>">
		<option value=""><?php esc_html_e( 'Everyone', 'formidable' ); ?></option>
	</select>
</p>
