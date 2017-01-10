<div class="frm_forms with_frm_style frm_style_<?php echo esc_attr( $style->post_name ) ?> <?php echo esc_attr( FrmAppHelper::pro_is_installed() ? 'frm_pro_form' : 'frm_lite_form' ) ?>">
<div class="frm-show-form">
<div class="frm_message">
	<strong><?php esc_html_e( 'SAMPLE:', 'formidable' ) ?></strong>
	<?php echo wp_kses_post( $frm_settings->success_msg ); ?>
</div>

<div class="frm_error_style">
	<strong><?php esc_html_e( 'SAMPLE:', 'formidable' ) ?></strong>
	<?php echo wp_kses_post( $frm_settings->invalid_msg ); ?>
</div>

<?php $pos_class = 'frm_pos_container frm_' . ( $style->post_content['position'] == 'none' ? 'top' : ( $style->post_content['position'] == 'no_label' ? 'none' : $style->post_content['position'] ) ) . '_container'; ?>

<div class="frm_form_fields frm_sample_form">
<fieldset>
<h3 class="frm_form_title"><?php _e( 'Form Title', 'formidable' ) ?></h3>
<div class="frm_description"><p><?php _e( 'This is an example form description for styling purposes.', 'formidable' ) ?></p></div>

<div class="frm_form_field frm_first frm_half form-field <?php echo esc_attr( $pos_class ) ?>">
<label class="frm_primary_label"><?php _e( 'Text field', 'formidable' ) ?> <span class="frm_required">*</span></label>
<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ) ?>"/>
<div class="frm_description"><?php _e( 'A field with a description', 'formidable' ) ?></div>
</div>

<div class="frm_form_field form-field frm_half <?php echo esc_attr( $pos_class ) ?>">
	<label for="field_wq7w5e" class="frm_primary_label"><?php _e( 'Drop-down Select', 'formidable' ) ?></label>

	<select name="item_meta[1028]" id="field_wq7w5e" >
		<option value=""> </option>
		<option value=""><?php _e( 'Option 1', 'formidable' ) ?></option>
	</select>
</div>

<div class="frm_form_field form-field frm_first frm_third frm_blank_field <?php echo esc_attr( $pos_class ) ?>">
<label class="frm_primary_label"><?php _e( 'Text field with error', 'formidable' ) ?> <span class="frm_required">*</span></label>
<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ) ?>"/>
<div class="frm_error"><?php echo esc_html( $frm_settings->blank_msg ) ?></div>
</div>

<div class="frm_form_field frm_third form-field frm_focus_field <?php echo esc_attr( $pos_class ) ?>">
<label class="frm_primary_label"><?php _e( 'Text field in active state', 'formidable' ) ?> <span class="frm_required">*</span></label>
<input type="text" value="<?php esc_attr_e( 'Active state will be seen when the field is clicked', 'formidable' ) ?>" />
</div>

<div class="frm_form_field frm_third form-field <?php echo esc_attr( $pos_class ) ?>">
<label class="frm_primary_label"><?php _e( 'Read-only field', 'formidable' ) ?></label>
<input type="text" value="<?php esc_attr_e( 'This field is not editable', 'formidable' ) ?>" disabled="disabled" />
</div>

<div class="frm_form_field form-field frm_first frm_half <?php echo esc_attr( $pos_class ) ?> frm_lite_style">
	<label class="frm_primary_label"><?php _e( 'Text Area', 'formidable' ) ?></label>
	<textarea></textarea>
	<div class="frm_description"><?php _e( 'Another field with a description', 'formidable' ) ?></div>
</div>

<div class="frm_form_field form-field frm_fourth <?php echo esc_attr( $pos_class ) ?> frm_lite_style">
	<label class="frm_primary_label"><?php _e( 'Radio Buttons', 'formidable' ) ?></label>
	<div class="frm_opt_container">
		<div class="frm_radio"><input type="radio" /><label><?php _e( 'Option 1', 'formidable' ) ?></label></div>
		<div class="frm_radio"><input type="radio" /><label><?php _e( 'Option 2', 'formidable' ) ?></label></div>
	</div>
</div>

<div class="frm_form_field form-field frm_fourth <?php echo esc_attr( $pos_class ) ?> frm_lite_style">
	<label class="frm_primary_label"><?php _e( 'Check Boxes', 'formidable' ) ?></label>
	<div class="frm_opt_container">
		<div class="frm_checkbox"><label><input type="checkbox" /><?php _e( 'Option 1', 'formidable' ) ?></label></div>
		<div class="frm_checkbox"><label><input type="checkbox" /><?php _e( 'Option 2', 'formidable' ) ?></label></div>
	</div>
</div>

<?php do_action( 'frm_sample_style_form', compact( 'style', 'pos_class' ) ); ?>

<div class="frm_submit">
<input type="submit" disabled="disabled" class="frm_full_opacity" value="<?php esc_attr_e( 'Submit', 'formidable' ) ?>" />
</div>

</fieldset>
</div>
</div>
</div>
