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
<p class="frm_form_field frm6">
	<label class="frm-h-stack-xs frm_show_upgrade" for="prepend_<?php echo absint( $field['id'] ); ?>">
		<span><?php esc_html_e( 'Before Input', 'formidable' ); ?></span>
		<?php
		FrmAppHelper::tooltip_icon(
			__( 'A value entered here will show directly before the input box in the form.', 'formidable' ),
			array(
				'data-placement' => 'right',
				'class'          => 'frm-flex',
			)
		);
		?>
	</label>

	<input type="text" readonly name="field_options[prepend_<?php echo absint( $field['id'] ); ?>]" id="prepend_<?php echo absint( $field['id'] ); ?>" aria-invalid="false"  data-upgrade="<?php esc_attr_e( 'Before and after contents', 'formidable' ); ?>"/>
</p>

<p class="frm_form_field frm6">
	<label for="append_<?php echo absint( $field['id'] ); ?>" class="frm_show_upgrade">
		<?php esc_html_e( 'After Input', 'formidable' ); ?>
	</label>

	<input type="text" readonly name="field_options[append_<?php echo absint( $field['id'] ); ?>]" id="append_<?php echo absint( $field['id'] ); ?>" data-upgrade="<?php esc_attr_e( 'Before and after contents', 'formidable' ); ?>"/>
</p>
