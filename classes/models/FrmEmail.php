<?php

/**
 * @since 2.03.03
 */
class FrmEmail {

	// TODO: initial rewrite
	// TODO: write basic unit tests

	private $email_key = '';
	private $to = array();
	private $cc = array();
	private $bcc = array();
	private $from = '';// TODO: should multiple be allowed?
	private $reply_to = '';// TODO: should multiple be allowed?
	private $subject = '';
	private $message = '';
	private $attachments = array();

	private $is_plain_text = false;
	private $is_single_recipient = false;
	private $include_user_info = false;

	private $settings = array();
	private $entry;
	private $form;

	/**
	 * FrmEmail constructor.
	 *
	 * @param object $action
	 * @param object $entry
	 * @param object $form
	 */
	function __construct( $action, $entry, $form ) {
		$this->set_email_key( $action );
		$this->entry = $entry;
		$this->form = $form;
		$this->settings = $action->post_content;

		$user_id_args = self::get_user_id_args( $form->id );
		$this->set_to( $user_id_args );
		$this->set_cc( $user_id_args );
		$this->set_bcc( $user_id_args );

		// Stop now if there aren't any recipients
		if ( ! $this->has_recipients() ) {
			return;
		}

		$this->set_from( $user_id_args );
		$this->set_reply_to( $user_id_args );

		$this->set_include_user_info();
		$this->set_is_plain_text();
		$this->set_is_single_recipient( $action );

		$this->set_subject();
		$this->set_message();
		$this->set_attachments();
	}

	private function set_email_key( $action ) {
		$this->email_key = $action->ID;
	}

	private function get_email_key() {
		return $this->email_key;
	}

	private function set_to( $user_id_args ) {
		$to = $this->prepare_setting( $this->settings['email_to'], $user_id_args );
		$to = $this->explode_emails( $to );

		$values = FrmEntryMeta::getAll( array( 'it.field_id !' => 0, 'it.item_id' => $this->entry->id ), ' ORDER BY fi.field_order' );
		$args = array(
			'email_key' => $this->email_key,
			'entry' => $this->entry,
			'form' => $this->form,
		);
		$to = apply_filters( 'frm_to_email', $to, $values, $this->form->id, $args );

		$this->to = array_unique( (array) $to );

		if ( empty( $this->to ) ) {
			return;
		}

		// check for a phone number
		foreach ( $this->to as $key => $recipient ) {
			if ( $recipient != '[admin_email]' && ! is_email( $recipient ) ) {
				$recipient = explode(' ', $recipient);

				//If to_email has name <test@mail.com> format
				if ( is_email( end( $recipient ) ) ) {
					continue;
				}

				do_action('frm_send_to_not_email', array(
					'e'         => $recipient,
					'subject'   => $this->subject,
					'mail_body' => $this->message,
					'reply_to'  => $this->reply_to,
					'from'      => $this->from,
					'plain_text' => $this->is_plain_text,
					'attachments' => $this->attachments,
					'form'      => $this->form,
					'email_key' => $key,
				) );

				unset( $this->to[ $key ] );
			}
		}
	}

	public function get_to() {
		return $this->to;
	}

	private function set_cc( $user_id_args ) {
		$cc = $this->prepare_setting( $this->settings['cc'], $user_id_args );
		$cc = $this->explode_emails( $cc );

		$this->cc = array_unique( (array) $cc );
	}

	public function get_cc() {
		return $this->cc;
	}

	private function set_bcc( $user_id_args ) {
		$bcc = $this->prepare_setting( $this->settings['bcc'], $user_id_args );
		$bcc = $this->explode_emails( $bcc );

		$this->bcc = array_unique( (array) $bcc );
	}

	public function get_bcc() {
		return $this->bcc;
	}

	private function set_from( $user_id_args ) {
		if ( empty( $this->settings['from'] ) ) {
			$from = '[admin_email]';
			$from = FrmFieldsHelper::basic_replace_shortcodes( $from, $this->form, $this->entry );
		} else {
			$from = $this->prepare_setting( $this->settings['from'], $user_id_args );
		}

		$this->from = $from;
	}

	public function get_from() {
		return $this->from;
	}

	private function set_reply_to( $user_id_args ) {
		$this->reply_to = $this->prepare_setting( $this->settings['reply_to'], $user_id_args );
	}

	public function get_reply_to() {
		return $this->reply_to;
	}

	private function set_subject() {
		if ( empty( $this->settings['email_subject'] ) ) {
			$this->subject = sprintf(__( '%1$s Form submitted on %2$s', 'formidable' ), $this->form->name, '[sitename]');
		} else {
			$this->subject = $this->settings['email_subject'];
		}

		$this->subject = FrmFieldsHelper::basic_replace_shortcodes( $this->subject, $this->form, $this->entry );

		// TODO: function for these args?
		$args = array(
			'form' => $this->form,
			'entry' => $this->entry,
			'email_key' => $this->email_key,
		);
		$this->subject = apply_filters( 'frm_email_subject', $this->subject, $args );
	}

	public function get_subject() {
		return $this->subject;
	}

	private function set_message() {
		$this->message = FrmFieldsHelper::basic_replace_shortcodes( $this->settings['email_message'], $this->form, $this->entry );

		$prev_mail_body = $this->message;
		$pass_entry = clone $this->entry; // make a copy to prevent changes by reference
		$mail_body = FrmEntriesHelper::replace_default_message( $prev_mail_body, array(
			'id' => $this->entry->id, 'entry' => $pass_entry, 'plain_text' => $this->is_plain_text,
			'user_info' => $this->include_user_info,
		) );

		// Add the user info if it isn't already included
		if ( $this->include_user_info && $prev_mail_body == $mail_body ) {
			$data = maybe_unserialize( $this->entry->description );
			$mail_body .= "\r\n\r\n" . __( 'User Information', 'formidable' ) . "\r\n";
			$mail_body .= __( 'IP Address', 'formidable' ) . ': ' . $this->entry->ip . "\r\n";
			$mail_body .= __( 'User-Agent (Browser/OS)', 'formidable' ) . ': ' . FrmEntryFormat::get_browser( $data['browser'] ) . "\r\n";
			$mail_body .= __( 'Referrer', 'formidable' ) . ': ' . $data['referrer'] . "\r\n";
		}

		$this->message = $mail_body;
	}

	public function get_message() {
		return $this->message;
	}

	private function set_attachments() {
		$args = array(
			'entry' => $this->entry,
			'email_key' => $this->email_key,
		);

		$this->attachments = apply_filters( 'frm_notification_attachment', array(), $this->form, $args );
	}

	public function get_attachments() {
		return $this->attachments;
	}

	private function set_is_plain_text() {
		if ( $this->settings['plain_text'] ) {
			$this->is_plain_text = true;
		}

		// TODO: is this necessary?
		add_filter( 'frm_plain_text_email', ( $this->is_plain_text ? '__return_true' : '__return_false' ) );

	}

	public function get_is_plain_text() {
		return $this->is_plain_text;
	}

	private function set_include_user_info() {
		if ( isset( $this->settings['inc_user_info'] ) ) {
			$this->include_user_info = $this->settings['inc_user_info'];
		}
	}

	private function set_is_single_recipient( $action ) {
		$args = array(
			'form' => $this->form,
			'entry' => $this->entry,
			'action' => $action,
		);
		/**
		 * Send a separate email for email address in the "to" section
		 * @since 2.2.13
		 */
		$this->is_single_recipient = apply_filters( 'frm_send_separate_emails', false, compact( 'action', 'entry', 'form' ) );
	}

	public function get_is_single_recipient() {
		return $this->is_single_recipient;
	}

	public function has_recipients() {
		if ( empty( $this->to ) && empty( $this->cc ) && empty( $this->bcc ) ) {
			return false;
		} else {
			return true;
		}
	}

	private function get_user_id_args( $form_id ) {
		$user_id_args = array(
			'field_id' => '',
			'field_key' => '',
		);

		$user_id_args['field_id'] = FrmEmailHelper::get_user_id_field_for_form( $form_id );
		if ( $user_id_args['field_id'] ) {
			$user_id_args['field_key'] = FrmField::get_key_by_id( $user_id_args['field_id'] );
		}

		return $user_id_args;
	}

	private function prepare_setting( $value,$user_id_args ) {
		$value = FrmFieldsHelper::basic_replace_shortcodes( $value, $this->form, $this->entry );

		// Remove brackets and add a space in case there isn't one
		$value = str_replace( '<', ' ', $value );
		$value = str_replace( array( '"', '>' ), '', $value );

		// Switch userID shortcode to email address
		if ( strpos( $value, '[' . $user_id_args['field_id'] . ']' ) !== false || strpos( $value, '[' . $user_id_args['field_key'] . ']' ) !== false ) {
			$user_data = get_userdata( $this->entry->metas[ $user_id_args['field_id'] ] );
			$value = str_replace( array( '[' . $user_id_args['field_id'] . ']', '[' . $user_id_args['field_key'] . ']' ), $user_data->user_email, $value );
		}

		return $value;
	}

	/**
	 * Extract the emails from cc and bcc. Allow separation by , or ;.
	 * Trim the emails here as well
	 *
	 * @since 2.03.03
	 */
	private function explode_emails( $emails ) {
		$emails = ( ! empty( $emails ) ? preg_split( '/(,|;)/', $emails ) : '' );
		if ( is_array( $emails ) ) {
			$emails = array_map( 'trim', $emails );
		} else {
			$emails = trim( $emails );
		}
		return $emails;
	}

}