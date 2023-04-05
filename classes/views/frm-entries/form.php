<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( empty( $values ) || ! isset( $values['fields'] ) || empty( $values['fields'] ) ) { ?>
<div class="frm_forms <?php echo esc_attr( FrmFormsHelper::get_form_style_class( $form ) ); ?>" id="frm_form_<?php echo esc_attr( $form->id ); ?>_container">
	<div class="frm_error_style">
		<strong><?php esc_html_e( 'Oops!', 'formidable' ); ?></strong>
	<?php
	printf(
		/* translators: %1$s: HTML open link, %2$s: HTML close link */
		esc_html__( 'You did not add any fields to your form. %1$sGo back%2$s and add some.', 'formidable' ),
		'<a href="' . esc_url( FrmForm::get_edit_link( $form->id ) ) . '">',
		'</a>'
	);
	?>
	</div>
</div>
	<?php
	return;
}

global $frm_vars;
FrmFormsController::maybe_load_css( $form, $values['custom_style'], $frm_vars['load_css'] );

// Get conditionally hidden fields
$frm_hide_fields = FrmAppHelper::get_post_param( 'frm_hide_fields_' . $form->id, '', 'sanitize_text_field' );

?>
<div class="frm_form_fields <?php echo esc_attr( apply_filters( 'frm_form_fields_class', '', $values ) ); ?>">
<fieldset>
<?php
/**
 * @since 5.5.1
 */
do_action( 'frm_before_title', compact( 'form' ) );
echo FrmAppHelper::maybe_kses( FrmFormsHelper::replace_shortcodes( $values['before_html'], $form, $title, $description ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<div <?php echo wp_strip_all_tags( apply_filters( 'frm_fields_container_class', 'class="frm_fields_container"' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php do_action( 'frm_after_title', compact( 'form' ) ); ?>
<input type="hidden" name="frm_action" value="<?php echo esc_attr( $form_action ); ?>" />
<input type="hidden" name="form_id" value="<?php echo esc_attr( $form->id ); ?>" />
<input type="hidden" name="frm_hide_fields_<?php echo esc_attr( $form->id ); ?>" id="frm_hide_fields_<?php echo esc_attr( $form->id ); ?>" value="<?php echo esc_attr( $frm_hide_fields ); ?>" />
<input type="hidden" name="form_key" value="<?php echo esc_attr( $form->form_key ); ?>" />
<input type="hidden" name="item_meta[0]" value="" />
<?php wp_nonce_field( 'frm_submit_entry_nonce', 'frm_submit_entry_' . $form->id ); ?>
<?php if ( isset( $id ) ) { ?>
<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
<?php } ?>
<?php
if ( $values['fields'] ) {
	/**
	 * Allows modifying the list of fields in the frontend form.
	 *
	 * @since 5.0.04
	 *
	 * @param array $fields Array of fields.
	 * @param array $args   The arguments. Contains `form`.
	 */
	$fields_to_show = apply_filters( 'frm_fields_in_form', $values['fields'], compact( 'form' ) );
	FrmFieldsHelper::show_fields( $fields_to_show, $errors, $form, $form_action );
}

$frm_settings = FrmAppHelper::get_settings();
if ( FrmAppHelper::is_admin() ) {
	?>
	<div class="frm_form_field form-field">
	<label class="frm_primary_label"><?php esc_html_e( 'Entry Key', 'formidable' ); ?></label>
	<input type="text" name="item_key" value="<?php echo esc_attr( $values['item_key'] ); ?>" />
	</div>
	<?php
} else {
	?>
	<input type="hidden" name="item_key" value="<?php echo esc_attr( $values['item_key'] ); ?>" />
	<?php
	FrmHoneypot::maybe_render_field( $form->id );
	FrmFormState::maybe_render_state_field( $form );
}

do_action( 'frm_entry_form', $form, $form_action, $errors );

global $frm_vars;
// close open section div
if ( isset( $frm_vars['div'] ) && $frm_vars['div'] ) {
	echo "</div>\n";
	unset( $frm_vars['div'] );
}

// close open collapsible toggle div
if ( isset( $frm_vars['collapse_div'] ) && $frm_vars['collapse_div'] ) {
	echo "</div>\n";
	unset( $frm_vars['collapse_div'] );
}

echo FrmAppHelper::maybe_kses( FrmFormsHelper::replace_shortcodes( $values['after_html'], $form ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( FrmForm::show_submit( $form ) ) {
	/**
	 * @since 5.5.1
	 */
	do_action( 'frm_before_submit_btn', compact( 'form' ) );

	$copy_values = $values;
	unset( $copy_values['fields'] );

	if ( isset( $form->options['form_class'] ) && strpos( $form->options['form_class'], 'frm_inline_success' ) !== false ) {
		ob_start();
		ob_implicit_flush( false );
		FrmFormsHelper::get_custom_submit( $copy_values['submit_html'], $form, $submit, $form_action, $copy_values );
		$clip = ob_get_clean();

		ob_start();
		ob_implicit_flush( false );
		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php';
		$message = ob_get_clean();

		echo preg_replace( '~\<\/div\>(?!.*\<\/div\>)~', $message . '</div>', $clip ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		FrmFormsHelper::get_custom_submit( $copy_values['submit_html'], $form, $submit, $form_action, $copy_values );
	}

	/**
	 * @since 5.5.1
	 */
	do_action( 'frm_after_submit_btn', compact( 'form' ) );
}
?>
</div>
</fieldset>
</div>
<?php if ( has_action( 'frm_entries_footer_scripts' ) ) { ?>
<script type="text/javascript"><?php do_action( 'frm_entries_footer_scripts', $values['fields'], $form ); ?></script>
<?php } ?>
