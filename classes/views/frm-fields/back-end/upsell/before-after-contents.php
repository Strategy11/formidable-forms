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

	<input id="prepend_<?php echo absint( $field['id'] ); ?>" <?php FrmAppHelper::array_to_html_params( $before_after_content_upsell_atts, true ); ?>/>
</p>

<p class="frm_form_field frm6">
	<label for="append_<?php echo absint( $field['id'] ); ?>" class="frm_show_upgrade">
		<?php esc_html_e( 'After Input', 'formidable' ); ?>
	</label>

	<input id="append_<?php echo absint( $field['id'] ); ?>" <?php FrmAppHelper::array_to_html_params( $before_after_content_upsell_atts, true ); ?>/>
</p>
