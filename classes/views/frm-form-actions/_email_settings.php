<p class="frm_has_shortcodes frm_to_row frm_email_row">
	<label for="<?php echo esc_attr( $this->get_field_id( 'email_to' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'email_to' ); ?>>
		<?php esc_html_e( 'To', 'formidable' ); ?>
	</label>
	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'email_to' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['email_to'] ); ?>" class="frm_not_email_to frm_email_blur large-text <?php FrmAppHelper::maybe_add_tooltip( 'email_to', 'open' ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'email_to' ) ); ?>" />
</p>

<p class="frm_bcc_cc_container">
	<a href="javascript:void(0)" class="button frm_email_buttons frm_cc_button <?php echo esc_attr( ! empty( $form_action->post_content['cc'] ) ? 'frm_hidden' : '' ); ?>" data-emailrow="cc">
		<?php esc_html_e( 'CC', 'formidable' ); ?>
	</a>
	<a href="javascript:void(0)" class="button frm_email_buttons frm_bcc_button <?php echo esc_attr( ! empty( $form_action->post_content['bcc'] ) ? 'frm_hidden' : '' ); ?>" data-emailrow="bcc">
		<?php esc_html_e( 'BCC', 'formidable' ); ?>
	</a>
</p>

<p class="frm_has_shortcodes frm_cc_row frm_email_row<?php echo empty( $form_action->post_content['cc'] ) ? ' frm_hidden' : ''; ?>" >
	<label for="<?php echo esc_attr( $this->get_field_id( 'cc' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'cc' ); ?>>
		<?php esc_html_e( 'CC', 'formidable' ); ?>
		<a href="javascript:void(0)" class="frm_icon_font frm_remove_field frm_cancel1_icon" data-emailrow="cc"></a>
	</label>

	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'cc' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['cc'] ); ?>" class="frm_not_email_to large-text <?php FrmAppHelper::maybe_add_tooltip( 'cc', 'open' ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'cc' ) ); ?>" />
</p>

<p class="frm_has_shortcodes frm_bcc_row frm_email_row<?php echo empty( $form_action->post_content['bcc'] ) ? ' frm_hidden' : ''; ?>" >
	<label for="<?php echo esc_attr( $this->get_field_id( 'bcc' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'bcc' ); ?>>
		<?php esc_html_e( 'BCC', 'formidable' ); ?>
		<a href="javascript:void(0)" class="frm_icon_font frm_remove_field frm_cancel1_icon" data-emailrow="bcc"></a>
	</label>

	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'bcc' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['bcc'] ); ?>" class="frm_not_email_to large-text <?php FrmAppHelper::maybe_add_tooltip( 'bcc', 'open' ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'bcc' ) ); ?>" />
</p>

<p class="frm_has_shortcodes frm_from_row frm_email_row">
	<label for="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'from' ); ?>>
		<?php esc_html_e( 'From', 'formidable' ); ?>
	</label>

	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['from'] ); ?>" class="frm_not_email_to frm_email_blur large-text <?php FrmAppHelper::maybe_add_tooltip( 'from', 'open' ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>" />
</p>

<p class="frm_reply_to_container">
	<a href="javascript:void(0)" class="button frm_email_buttons frm_reply_to_button <?php echo ( ! empty( $form_action->post_content['reply_to'] ) ? 'frm_hidden' : '' ); ?>" data-emailrow="reply_to">
		<?php esc_html_e( 'Reply To', 'formidable' ); ?>
	</a>
</p>

<p class="frm_has_shortcodes frm_reply_to_row frm_email_row<?php echo empty( $form_action->post_content['reply_to'] ) ? ' frm_hidden' : ''; ?>">
	<label for="<?php echo esc_attr( $this->get_field_id( 'reply_to' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'reply_to' ); ?>>
		<?php esc_html_e( 'Reply To', 'formidable' ); ?>
		<a href="javascript:void(0)" class="frm_icon_font frm_remove_field frm_cancel1_icon" data-emailrow="reply_to"></a>
	</label>

	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'reply_to' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['reply_to'] ); ?>" class="frm_not_email_to large-text <?php FrmAppHelper::maybe_add_tooltip( 'reply_to', 'open' ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'reply_to' ) ); ?>" />
</p>

<p class="frm_has_shortcodes">
	<label for="<?php echo esc_attr( $this->get_field_id( 'email_subject' ) ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'email_subject', '', $form->name ); ?>>
		<?php esc_html_e( 'Subject', 'formidable' ); ?>
	</label>
	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'email_subject' ) ); ?>" class="frm_not_email_subject large-text <?php FrmAppHelper::maybe_add_tooltip( 'email_subject', 'open', $form->name ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'email_subject' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['email_subject'] ); ?>" />
</p>

<p class="frm_has_shortcodes">
	<label for="<?php echo esc_attr( $this->get_field_id( 'email_message' ) ); ?>">
		<?php esc_html_e( 'Message', 'formidable' ); ?>
	</label>
	<textarea name="<?php echo esc_attr( $this->get_field_name( 'email_message' ) ); ?>" class="frm_not_email_message frm_long_input" id="<?php echo esc_attr( $this->get_field_id( 'email_message' ) ); ?>" cols="50" rows="5"><?php echo FrmAppHelper::esc_textarea( $form_action->post_content['email_message'] ); // WPCS: XSS ok. ?></textarea>
</p>

<label for="<?php echo esc_attr( $this->get_field_id( 'inc_user_info' ) ); ?>">
	<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'inc_user_info' ) ); ?>" class="frm_not_inc_user_info" id="<?php echo esc_attr( $this->get_field_id( 'inc_user_info' ) ); ?>" value="1" <?php checked( $form_action->post_content['inc_user_info'], 1 ); ?> />
	<?php if ( FrmAppHelper::ips_saved() ) { ?>
		<?php esc_html_e( 'Append IP Address, Browser, and Referring URL to message', 'formidable' ); ?>
	<?php } else { ?>
		<?php esc_html_e( 'Append Browser and Referring URL to message', 'formidable' ); ?>
	<?php } ?>
</label>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'plain_text' ) ); ?>">
		<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'plain_text' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'plain_text' ) ); ?>" value="1" <?php checked( $form_action->post_content['plain_text'], 1 ); ?> />
		<?php esc_html_e( 'Send Emails in Plain Text', 'formidable' ); ?>
	</label>
</p>
