<?php

/**
 * @since 2.03.04
 */
class FrmEmail {

	private $email_key = '';
	private $to = array();
	private $cc = array();
	private $bcc = array();
	private $from = '';
	private $reply_to = '';
	private $subject = '';
	private $message = '';
	private $attachments = array();

	private $is_plain_text = false;
	private $is_single_recipient = false;
	private $include_user_info = false;

	private $charset = '';
	private $content_type = 'text/html';

	private $settings = array();
	private $entry;
	private $form;

	/**
	 * FrmEmail constructor
	 *
	 * @param object $action
	 * @param object $entry
	 * @param object $form
	 */
	public function __construct( $action, $entry, $form ) {
		$this->set_email_key( $action );
		$this->entry    = $entry;
		$this->form     = $form;
		$this->settings = $action->post_content;

		$user_id_args = self::get_user_id_args( $form->id );
		$this->set_to( $user_id_args );
		$this->set_cc( $user_id_args );
		$this->set_bcc( $user_id_args );

		if ( ! $this->has_recipients() ) {
			return;
		}

		$this->set_from( $user_id_args );
		$this->set_reply_to( $user_id_args );

		$this->set_include_user_info();
		$this->set_is_plain_text();
		$this->set_is_single_recipient( $action );

		$this->set_charset();
		$this->set_content_type();

		$this->set_subject();
		$this->set_message();
		$this->set_attachments();
	}

	/**
	 * Set the email key property
	 *
	 * @since 2.03.04
	 *
	 * @param object $action
	 */
	private function set_email_key( $action ) {
		$this->email_key = $action->ID;
	}

	/**
	 * Set the to addresses
	 *
	 * @since 2.03.04
	 *
	 * @param array $user_id_args
	 */
	private function set_to( $user_id_args ) {
		$to = $this->prepare_email_setting( $this->settings['email_to'], $user_id_args );
		$to = $this->explode_emails( $to );

		$where = array(
			'it.field_id !' => 0,
			'it.item_id'    => $this->entry->id,
		);
		$values = FrmEntryMeta::getAll( $where, ' ORDER BY fi.field_order' );
		$args   = array(
			'email_key' => $this->email_key,
			'entry'     => $this->entry,
			'form'      => $this->form,
		);
		$to     = apply_filters( 'frm_to_email', $to, $values, $this->form->id, $args );

		$this->to = array_unique( (array) $to );

		if ( empty( $this->to ) ) {
			return;
		}

		$this->handle_phone_numbers();

		$this->to = $this->format_recipients( $this->to );
	}

	/**
	 * Set the CC addresses
	 *
	 * @since 2.03.04
	 *
	 * @param array $user_id_args
	 */
	private function set_cc( $user_id_args ) {
		$this->cc = $this->prepare_additional_recipients( $this->settings['cc'], $user_id_args );
	}

	/**
	 * Set the BCC addresses
	 *
	 * @since 2.03.04
	 *
	 * @param array $user_id_args
	 */
	private function set_bcc( $user_id_args ) {
		$this->bcc = $this->prepare_additional_recipients( $this->settings['bcc'], $user_id_args );
	}

	/**
	 * Prepare CC and BCC recipients
	 *
	 * @since 2.03.04
	 *
	 * @param string $recipients
	 * @param array $user_id_args
	 *
	 * @return array
	 */
	private function prepare_additional_recipients( $recipients, $user_id_args ) {
		$recipients = $this->prepare_email_setting( $recipients, $user_id_args );
		$recipients = $this->explode_emails( $recipients );

		$recipients = array_unique( (array) $recipients );
		$recipients = $this->format_recipients( $recipients );

		return $recipients;
	}

	/**
	 * Set the From addresses
	 *
	 * @since 2.03.04
	 *
	 * @param array $user_id_args
	 */
	private function set_from( $user_id_args ) {
		if ( empty( $this->settings['from'] ) ) {
			$from = get_option( 'admin_email' );
		} else {
			$from = $this->prepare_email_setting( $this->settings['from'], $user_id_args );
		}

		$this->from = $this->format_from( $from );
	}

	/**
	 * Set the Reply To addresses
	 *
	 * @since 2.03.04
	 *
	 * @param array $user_id_args
	 */
	private function set_reply_to( $user_id_args ) {
		$this->reply_to = trim( $this->settings['reply_to'] );

		if ( empty( $this->reply_to ) ) {
			$this->reply_to = $this->from;
		} else {
			$this->reply_to = $this->prepare_email_setting( $this->settings['reply_to'], $user_id_args );
			$this->reply_to = $this->format_reply_to( $this->reply_to );
		}
	}

	/**
	 * Set the is_plain_text property
	 * This should be set before the message
	 *
	 * @since 2.03.04
	 */
	private function set_is_plain_text() {
		if ( $this->settings['plain_text'] ) {
			$this->is_plain_text = true;
		}
	}

	/**
	 * Set the include_user_info property
	 * This should be set before the message
	 *
	 * @since 2.03.04
	 */
	private function set_include_user_info() {
		if ( isset( $this->settings['inc_user_info'] ) ) {
			$this->include_user_info = $this->settings['inc_user_info'];
		}
	}

	/**
	 * Set the is_single_recipient property
	 *
	 * @since 2.03.04
	 *
	 * @param $action
	 */
	private function set_is_single_recipient( $action ) {
		$args = array(
			'form'   => $this->form,
			'entry'  => $this->entry,
			'action' => $action,
		);

		/**
		 * Send a separate email for email address in the "to" section
		 *
		 * @since 2.2.13
		 */
		$this->is_single_recipient = apply_filters( 'frm_send_separate_emails', false, $args );
	}

	/**
	 * Set the charset
	 *
	 * @since 2.03.04
	 */
	private function set_charset() {
		$this->charset = get_option( 'blog_charset' );
	}

	/**
	 * Set the content type
	 *
	 * @since 2.03.04
	 */
	private function set_content_type() {
		if ( $this->is_plain_text ) {
			$this->content_type = 'text/plain';
		}
	}

	/**
	 * Set the subject
	 *
	 * @since 2.03.04
	 */
	private function set_subject() {
		if ( empty( $this->settings['email_subject'] ) ) {
			$this->subject = sprintf( __( '%1$s Form submitted on %2$s', 'formidable' ), $this->form->name, '[sitename]' );
		} else {
			$this->subject = $this->settings['email_subject'];
		}

		$this->subject = FrmFieldsHelper::basic_replace_shortcodes( $this->subject, $this->form, $this->entry );

		$args          = array(
			'form'      => $this->form,
			'entry'     => $this->entry,
			'email_key' => $this->email_key,
		);
		$this->subject = apply_filters( 'frm_email_subject', $this->subject, $args );

		$this->subject = wp_specialchars_decode( strip_tags( stripslashes( $this->subject ) ), ENT_QUOTES );
	}

	/**
	 * Set the email message
	 *
	 * @since 2.03.04
	 */
	private function set_message() {
		$this->message = FrmFieldsHelper::basic_replace_shortcodes( $this->settings['email_message'], $this->form, $this->entry );

		$prev_mail_body = $this->message;
		$pass_entry     = clone $this->entry; // make a copy to prevent changes by reference
		$mail_body      = FrmEntriesHelper::replace_default_message( $prev_mail_body, array(
			'id'         => $this->entry->id,
			'entry'      => $pass_entry,
			'plain_text' => $this->is_plain_text,
			'user_info'  => $this->include_user_info,
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

		$this->message = do_shortcode( $this->message );

		if ( $this->is_plain_text ) {
			$this->message = wp_specialchars_decode( strip_tags( $this->message ), ENT_QUOTES );
		}

		$this->message = apply_filters( 'frm_email_message', $this->message, $this->package_atts() );
	}

	/**
	 * Set the attachments for an email message
	 *
	 * @since 2.03.04
	 */
	private function set_attachments() {
		$args = array(
			'entry'     => $this->entry,
			'email_key' => $this->email_key,
		);

		$this->attachments = apply_filters( 'frm_notification_attachment', array(), $this->form, $args );
	}

	/**
	 * Check if an email should send
	 *
	 * @since 2.03.04
	 *
	 * @return bool|mixed|void
	 */
	public function should_send() {
		if ( ! $this->has_recipients() ) {
			$send = false;
		} else {

			/**
			 * Stop an email based on the message, subject, recipient,
			 * or any information included in the email header
			 *
			 * @since 2.2.8
			 */
			$send = apply_filters( 'frm_send_email', true, array(
				'message'   => $this->message,
				'subject'   => $this->subject,
				'recipient' => $this->to,
				'header'    => $this->package_header(),
			) );
		}

		return $send;
	}

	/**
	 * Check if an email has any recipients
	 *
	 * @since 2.03.04
	 *
	 * @return bool
	 */
	private function has_recipients() {
		if ( empty( $this->to ) && empty( $this->cc ) && empty( $this->bcc ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Send an email
	 *
	 * @since 2.03.04
	 *
	 * @return bool
	 */
	public function send() {
		$this->remove_buddypress_filters();
		$this->add_mandrill_filter();

		$sent = false;
		if ( count( $this->to ) > 1 && $this->is_single_recipient ) {
			foreach ( $this->to as $recipient ) {
				$sent = $this->send_single( $recipient );
			}
		} else {
			$sent = $this->send_single( $this->to );
		}

		$this->remove_mandrill_filter();

		return $sent;
	}

	/**
	 * Send a single email
	 *
	 * @since 2.03.04
	 *
	 * @param array|string $recipient
	 *
	 * @return bool
	 */
	private function send_single( $recipient ) {
		$header = apply_filters( 'frm_email_header', $this->package_header(), array(
			'to_email' => $recipient,
			'subject'  => $this->subject,
		) );

		$subject = $this->encode_subject( $this->subject );

		$sent = wp_mail( $recipient, $subject, $this->message, $header, $this->attachments );

		if ( ! $sent ) {
			$header    = 'From: ' . $this->from . "\r\n";
			$recipient = implode( ',', (array) $recipient );
			$sent      = mail( $recipient, $subject, $this->message, $header );
		}

		do_action( 'frm_notification', $recipient, $subject, $this->message );

		return $sent;
	}

	/**
	 * Package the email header
	 *
	 * @since 2.03.04
	 *
	 * @return array
	 */
	private function package_header() {
		$header   = array();

		if ( ! empty( $this->cc ) ) {
			$header[] = 'CC: ' . implode( ',', $this->cc );
		}

		if ( ! empty( $this->bcc ) ) {
			$header[] = 'BCC: ' . implode( ',', $this->bcc );
		}

		$header[] = 'From: ' . $this->from;
		$header[] = 'Reply-To: ' . $this->reply_to;
		$header[] = 'Content-Type: ' . $this->content_type . '; charset="' . esc_attr( $this->charset ) . '"';

		return $header;
	}

	/**
	 * Get the userID field ID and key for email settings
	 *
	 * @since 2.03.04
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	private function get_user_id_args( $form_id ) {
		$user_id_args = array(
			'field_id'  => '',
			'field_key' => '',
		);

		$user_id_args['field_id'] = FrmEmailHelper::get_user_id_field_for_form( $form_id );
		if ( $user_id_args['field_id'] ) {
			$user_id_args['field_key'] = FrmField::get_key_by_id( $user_id_args['field_id'] );
		}

		return $user_id_args;
	}

	/**
	 * Prepare the to, cc, bcc, reply_to, and from setting
	 *
	 * @since 2.03.04
	 *
	 * @param string $value
	 * @param array $user_id_args
	 *
	 * @return string
	 */
	private function prepare_email_setting( $value, $user_id_args ) {
		if ( strpos( $value, '[' . $user_id_args['field_id'] . ']' ) !== false ) {
			$value = str_replace( '[' . $user_id_args['field_id'] . ']', '[' . $user_id_args['field_id'] . ' show="user_email"]', $value );
		} else if ( strpos( $value, '[' . $user_id_args['field_key'] . ']' ) !== false ) {
			$value = str_replace( '[' . $user_id_args['field_key'] . ']', '[' . $user_id_args['field_key'] . ' show="user_email"]', $value );
		}

		$value = FrmFieldsHelper::basic_replace_shortcodes( $value, $this->form, $this->entry );

		// Remove brackets and add a space in case there isn't one
		$value = str_replace( '<', ' ', $value );
		$value = str_replace( array( '"', '>' ), '', $value );

		return $value;
	}

	/**
	 * Extract the emails from cc and bcc. Allow separation by , or ;.
	 * Trim the emails here as well
	 *
	 * @since 2.03.04
	 *
	 * @param string $emails
	 * @return array|string $emails
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

	/**
	 * Format the recipients( to, cc, bcc)
	 *
	 * @param array $recipients
	 *
	 * @return array
	 */
	private function format_recipients( $recipients ) {
		if ( empty( $recipients ) ) {
			return $recipients;
		}

		foreach ( $recipients as $key => $val ) {
			$val = trim( $val );

			if ( is_email( $val ) ) {
				// If a plain email is used, no formatting is needed
				continue;
			} else {
				$parts = explode( ' ', $val );
				$email = end( $parts );

				if ( is_email( $email ) ) {
					// If user enters a name and email
					$name = trim( str_replace( $email, '', $val ) );
				} else {
					// If user enters a name without an email
					unset( $recipients[ $key ] );
					continue;
				}
			}

			$recipients[ $key ] = $name . ' <' . $email . '>';
		}

		return $recipients;
	}

	/**
	 * Format the From header
	 *
	 * @param string $from
	 *
	 * @return string
	 */
	private function format_from( $from ) {
		$from = trim( $from );

		if ( is_email( $from ) ) {
			// If a plain email is used, add the site name so "WordPress" doesn't get added
			$from_name  = wp_specialchars_decode( FrmAppHelper::site_name(), ENT_QUOTES );
			$from_email = $from;
		} else {
			list( $from_name, $from_email ) = $this->get_name_and_email_for_sender( $from );
		}

		// if sending the email from a yahoo address, change it to the WordPress default
		if ( strpos( $from_email, '@yahoo.com' ) ) {

			// Get the site domain and get rid of www.
			$sitename = strtolower( FrmAppHelper::get_server_value( 'SERVER_NAME' ) );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$from_email = 'wordpress@' . $sitename;
		}

		$from = $from_name . ' <' . $from_email . '>';

		return $from;
	}

	/**
	 * Format the Reply To property
	 *
	 * @since 2.03.04
	 *
	 * @param string $reply_to
	 *
	 * @return string
	 */
	private function format_reply_to( $reply_to ) {
		$reply_to = trim( $reply_to );

		if ( empty( $reply_to ) ) {
			return $this->from;
		} else if ( is_email( $reply_to ) ) {
			return $reply_to;
		} else {
			list( $name, $email ) = $this->get_name_and_email_for_sender( $reply_to );
		}

		return $name . ' <' . $email . '>';
	}

	/**
	 * Get the name and email for the From or Reply To header
	 *
	 * @since 2.03.04
	 *
	 * @param string $sender
	 *
	 * @return array
	 */
	private function get_name_and_email_for_sender( $sender ) {
		$parts = explode( ' ', $sender );
		$end   = end( $parts );

		if ( is_email( $end ) ) {
			$name = trim( str_replace( $end, '', $sender ) );
		} else {
			// Only a name was entered in the From or Reply To field
			$name = $sender;
			$end  = get_option( 'admin_email' );
		}

		return array( $name, $end );
	}

	/**
	 * Remove phone numbers from To addresses
	 * Send the phone numbers to the frm_send_to_not_email hook
	 *
	 * @since 2.03.04
	 */
	private function handle_phone_numbers() {

		foreach ( $this->to as $key => $recipient ) {
			if ( $recipient != '[admin_email]' && ! is_email( $recipient ) ) {
				$recipient = explode( ' ', $recipient );

				if ( is_email( end( $recipient ) ) ) {
					continue;
				}

				do_action( 'frm_send_to_not_email', array(
					'e'           => $recipient,
					'subject'     => $this->subject,
					'mail_body'   => $this->message,
					'reply_to'    => $this->reply_to,
					'from'        => $this->from,
					'plain_text'  => $this->is_plain_text,
					'attachments' => $this->attachments,
					'form'        => $this->form,
					'email_key'   => $key,
				) );

				// Remove phone number from to addresses
				unset( $this->to[ $key ] );
			}
		}
	}

	/**
	 * Package an array of FrmEmail properties
	 *
	 * @since 2.03.04
	 *
	 * @return array
	 */
	public function package_atts() {
		return array(
			'to_email'    => $this->to,
			'cc'          => $this->cc,
			'bcc'         => $this->bcc,
			'from'        => $this->from,
			'reply_to'    => $this->reply_to,
			'subject'     => $this->subject,
			'message'     => $this->message,
			'attachments' => $this->attachments,
			'plain_text'  => $this->is_plain_text,
		);
	}

	/**
	 * Remove the Buddypress email filters
	 *
	 * @since 2.03.04
	 */
	private function remove_buddypress_filters() {
		remove_filter( 'wp_mail_from', 'bp_core_email_from_address_filter' );
		remove_filter( 'wp_mail_from_name', 'bp_core_email_from_name_filter' );
	}

	/**
	 * Add Mandrill line break filter
	 * Remove line breaks in HTML emails to prevent conflicts with Mandrill
	 *
	 * @since 2.03.04
	 */
	private function add_mandrill_filter() {
		if ( ! $this->is_plain_text ) {
			add_filter( 'mandrill_nl2br', 'FrmEmailHelper::remove_mandrill_br' );
		}
	}

	/**
	 * Remove Mandrill line break filter
	 *
	 * @since 2.03.04
	 */
	private function remove_mandrill_filter() {
		remove_filter( 'mandrill_nl2br', 'FrmEmailHelper::remove_mandrill_br' );
	}

	/**
	 * Encode the email subject
	 *
	 * @param string $subject
	 *
	 * @return string
	 */
	private function encode_subject( $subject ) {
		if ( apply_filters( 'frm_encode_subject', 1, $subject ) ) {
			$subject = '=?' . $this->charset . '?B?' . base64_encode( $subject ) . '?=';
		}

		return $subject;
	}

}