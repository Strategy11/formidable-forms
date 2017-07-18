<?php
if ( empty($values) || ! isset($values['fields']) || empty($values['fields']) ) { ?>
<div class="frm_forms <?php echo FrmFormsHelper::get_form_style_class($form); ?>" id="frm_form_<?php echo esc_attr( $form->id ) ?>_container">
	<div class="frm_error_style"><strong><?php _e( 'Oops!', 'formidable' ) ?></strong> <?php printf( __( 'You did not add any fields to your form. %1$sGo back%2$s and add some.', 'formidable' ), '<a href="' . esc_url( admin_url( '?page=formidable&frm_action=edit&id=' . $form->id ) ) . '">', '</a>' ) ?>
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
<?php echo FrmFormsHelper::replace_shortcodes( $values['before_html'], $form, $title, $description ); ?>
<?php do_action( 'frm_after_title', compact('form') ) ?>
<input type="hidden" name="frm_action" value="<?php echo esc_attr($form_action) ?>" />
<input type="hidden" name="form_id" value="<?php echo esc_attr($form->id) ?>" />
<input type="hidden" name="frm_hide_fields_<?php echo esc_attr( $form->id ) ?>" id="frm_hide_fields_<?php echo esc_attr( $form->id ) ?>" value="<?php echo esc_attr($frm_hide_fields) ?>" />
<input type="hidden" name="form_key" value="<?php echo esc_attr($form->form_key) ?>" />
<input type="hidden" name="item_meta[0]" value="" />
<?php wp_nonce_field( 'frm_submit_entry_nonce', 'frm_submit_entry_' . $form->id ); ?>
<input type="text" class="frm_hidden frm_verify" id="frm_verify_<?php echo esc_attr( $form->id ) ?>" name="frm_verify" value="<?php echo esc_attr( FrmAppHelper::get_param('frm_verify' ) ) ?>" <?php FrmFormsHelper::maybe_hide_inline() ?> />

<?php if ( isset( $id ) ) { ?><input type="hidden" name="id" value="<?php echo esc_attr( $id ) ?>" /><?php }

if ( $values['fields'] ) {
	foreach ( $values['fields'] as $field ) {
		if ( apply_filters( 'frm_show_normal_field_type', true, $field['type'] ) ) {
			echo FrmFieldsHelper::replace_shortcodes( $field['custom_html'], $field, $errors, $form );
		} else {
			do_action( 'frm_show_other_field_type', $field, $form, array( 'action' => $form_action ) );
		}
    	do_action('frm_get_field_scripts', $field, $form, $form->id);
	}
}

$frm_settings = FrmAppHelper::get_settings();
if ( FrmAppHelper::is_admin() ) { ?>
<div class="frm_form_field form-field">
<label class="frm_primary_label"><?php _e( 'Entry Key', 'formidable' ) ?></label>
<input type="text" name="item_key" value="<?php echo esc_attr($values['item_key']) ?>" />
</div>
<?php } else { ?>
<input type="hidden" name="item_key" value="<?php echo esc_attr($values['item_key']) ?>" />
<?php }

do_action('frm_entry_form', $form, $form_action, $errors);

global $frm_vars;
// close open section div
if ( isset( $frm_vars['div'] ) && $frm_vars['div'] ) {
	echo "</div>\n";
	unset( $frm_vars['div'] );
}

// close open collapsible toggle div
if ( isset($frm_vars['collapse_div']) && $frm_vars['collapse_div'] ) {
    echo "</div>\n";
    unset($frm_vars['collapse_div']);
}

echo FrmFormsHelper::replace_shortcodes($values['after_html'], $form);

if ( FrmForm::show_submit( $form ) ) {

	$copy_values = $values;
	unset( $copy_values['fields'] );

	FrmFormsHelper::get_custom_submit( $copy_values['submit_html'], $form, $submit, $form_action, $copy_values );
}
?>
</fieldset>
</div>
<?php
if ( has_action('frm_entries_footer_scripts') ) {
?><script type="text/javascript"><?php
do_action( 'frm_entries_footer_scripts', $values['fields'], $form );
?></script><?php
}
?>
