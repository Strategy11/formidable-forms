<?php

class FrmNotification {
	public function __construct() {
        if ( ! defined('ABSPATH') ) {
            die('You are not allowed to call this page directly.');
        }
        add_action('frm_trigger_email_action', 'FrmNotification::trigger_email', 10, 3);
    }

	public static function trigger_email( $action, $entry, $form ) {
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING  ) {
            return;
        }

        global $wpdb;

        $notification = $action->post_content;
        $email_key = $action->ID;

        // Set the subject
        if ( empty($notification['email_subject']) ) {
            $notification['email_subject'] = sprintf(__( '%1$s Form submitted on %2$s', 'formidable' ), $form->name, '[sitename]');
        }

        $plain_text = $notification['plain_text'] ? true : false;

        //Filter these fields
        $filter_fields = array(
            'email_to', 'cc', 'bcc',
            'reply_to', 'from',
            'email_subject', 'email_message',
        );

        add_filter('frm_plain_text_email', ($plain_text ? '__return_true' : '__return_false'));

        //Get all values in entry in order to get User ID field ID
        $values = FrmEntryMeta::getAll( array( 'it.field_id !' => 0, 'it.item_id' => $entry->id ), ' ORDER BY fi.field_order' );
        $user_id_field = $user_id_key = '';
        foreach ( $values as $value ) {
            if ( $value->field_type == 'user_id' ) {
                $user_id_field = $value->field_id;
                $user_id_key = $value->field_key;
                break;
            }
            unset($value);
        }

        //Filter and prepare the email fields
        foreach ( $filter_fields as $f ) {
            //Don't allow empty From
			if ( $f == 'from' && empty( $notification[ $f ] ) ) {
				$notification[ $f ] = '[admin_email]';
			} else if ( in_array( $f, array( 'email_to', 'cc', 'bcc', 'reply_to', 'from' ) ) ) {
				//Remove brackets
                //Add a space in case there isn't one
				$notification[ $f ] = str_replace( '<', ' ', $notification[ $f ] );
				$notification[ $f ] = str_replace( array( '"', '>' ), '', $notification[ $f ] );

                //Switch userID shortcode to email address
				if ( strpos( $notification[ $f ], '[' . $user_id_field . ']' ) !== false || strpos( $notification[ $f ], '[' . $user_id_key . ']' ) !== false ) {
					$user_data = get_userdata( $entry->metas[ $user_id_field ] );
                    $user_email = $user_data->user_email;
					$notification[ $f ] = str_replace( array( '[' . $user_id_field . ']', '[' . $user_id_key . ']' ), $user_email, $notification[ $f ] );
                }
            }

			$notification[ $f ] = FrmFieldsHelper::basic_replace_shortcodes( $notification[ $f ], $form, $entry );
        }

        //Put recipients, cc, and bcc into an array if they aren't empty
		$to_emails = self::explode_emails( $notification['email_to'] );
		$cc = self::explode_emails( $notification['cc'] );
		$bcc = self::explode_emails( $notification['bcc'] );

        $to_emails = apply_filters('frm_to_email', $to_emails, $values, $form->id, compact('email_key', 'entry', 'form'));

        // Stop now if there aren't any recipients
        if ( empty( $to_emails ) && empty( $cc ) && empty( $bcc ) ) {
            return;
        }

        $to_emails = array_unique( (array) $to_emails );

        $prev_mail_body = $mail_body = $notification['email_message'];
        $mail_body = FrmEntriesHelper::replace_default_message($mail_body, array(
            'id' => $entry->id, 'entry' => $entry, 'plain_text' => $plain_text,
            'user_info' => (isset($notification['inc_user_info']) ? $notification['inc_user_info'] : false),
        ) );

        // Add the user info if it isn't already included
        if ( $notification['inc_user_info'] && $prev_mail_body == $mail_body ) {
            $data = maybe_unserialize($entry->description);
            $mail_body .= "\r\n\r\n" . __( 'User Information', 'formidable' ) ."\r\n";
            $mail_body .= __( 'IP Address', 'formidable' ) . ': '. $entry->ip ."\r\n";
			$mail_body .= __( 'User-Agent (Browser/OS)', 'formidable' ) . ': ' . FrmEntryFormat::get_browser( $data['browser'] ) . "\r\n";
            $mail_body .= __( 'Referrer', 'formidable' ) . ': '. $data['referrer']."\r\n";
        }
        unset($prev_mail_body);

        // Add attachments
        $attachments = apply_filters('frm_notification_attachment', array(), $form, compact('entry', 'email_key') );

        if ( ! empty($notification['email_subject']) ) {
            $notification['email_subject'] = apply_filters('frm_email_subject', $notification['email_subject'], compact('form', 'entry', 'email_key'));
        }

        // check for a phone number
        foreach ( (array) $to_emails as $email_key => $e ) {
            if ( $e != '[admin_email]' && ! is_email($e) ) {
                $e = explode(' ', $e);

                //If to_email has name <test@mail.com> format
                if ( is_email(end($e)) ) {
                    continue;
                }

                do_action('frm_send_to_not_email', array(
                    'e'         => $e,
                    'subject'   => $notification['email_subject'],
                    'mail_body' => $mail_body,
                    'reply_to'  => $notification['reply_to'],
                    'from'      => $notification['from'],
                    'plain_text' => $plain_text,
                    'attachments' => $attachments,
                    'form'      => $form,
                    'email_key' => $email_key,
                ) );

				unset( $to_emails[ $email_key ] );
            }
        }

        // Send the email now
        $sent_to = self::send_email( array(
            'to_email'      => $to_emails,
            'subject'       => $notification['email_subject'],
            'message'       => $mail_body,
            'from'          => $notification['from'],
            'plain_text'    => $plain_text,
            'reply_to'      => $notification['reply_to'],
            'attachments'   => $attachments,
            'cc'            => $cc,
            'bcc'           => $bcc,
        ) );

        return $sent_to;
    }

	public function entry_created( $entry_id, $form_id ) {
        _deprecated_function( __FUNCTION__, '2.0', 'FrmFormActionsController::trigger_actions("create", '. $form_id .', '. $entry_id .', "email")');
        FrmFormActionsController::trigger_actions('create', $form_id, $entry_id, 'email');
    }

	public function send_notification_email( $to_email, $subject, $message, $from = '', $from_name = '', $plain_text = true, $attachments = array(), $reply_to = '' ) {
        _deprecated_function( __FUNCTION__, '2.0', 'FrmNotification::send_email' );

        return self::send_email(compact(
            'to_email', 'subject', 'message',
            'from', 'from_name', 'plain_text',
            'attachments', 'reply_to'
        ));
    }

	/**
	 * Extract the emails from cc and bcc. Allow separation by , or ;.
	 * Trim the emails here as well
	 *
	 * @since 2.0.1
	 */
	private static function explode_emails( $emails ) {
		$emails = ( ! empty( $emails ) ? preg_split( '/(,|;)/', $emails ) : '' );
		if ( is_array( $emails ) ) {
			$emails = array_map( 'trim', $emails );
		} else {
			$emails = trim( $emails );
		}
		return $emails;
	}

    /**
    * Put To, BCC, CC, Reply To, and From fields in Name <test@mail.com> format
    * Formats that should work: Name, "Name", test@mail.com, <test@mail.com>, Name <test@mail.com>,
    * "Name" <test@mail.com>, Name test@mail.com, "Name" test@mail.com, Name<test@mail.com>, "Name"<test@mail.com>
    * "First Last" <test@mail.com>
    *
    * Things that won't work: First Last (with no email entered)
    * @since 2.0
    * @param array $atts array of email fields, pass by reference
    * @param $admin_email
    */
    private static function format_email_fields( &$atts, $admin_email ) {

        // If from is empty or is set to admin_email, set it now
        $atts['from'] = ( empty($atts['from']) || $atts['from'] == '[admin_email]' ) ? $admin_email : $atts['from'];

        // Filter values in these fields
		$filter_fields = array( 'to_email', 'bcc', 'cc', 'from', 'reply_to' );

        foreach ( $filter_fields as $f ) {
            // If empty, just skip it
			if ( empty( $atts[ $f ] ) ) {
                continue;
            }

            // to_email, cc, and bcc can be an array
			if ( is_array( $atts[ $f ] ) ) {
				foreach ( $atts[ $f ] as $key => $val ) {
                    self::format_single_field( $atts, $f, $val, $key );
                    unset( $key, $val );
                }
                unset($f);
                continue;
            }

			self::format_single_field( $atts, $f, $atts[ $f ] );
        }

        // If reply-to isn't set, make it match the from settings
        if ( empty( $atts['reply_to'] ) ) {
            $atts['reply_to'] = self::get_email_from_formatted_string( $atts['from'] );
        }

        if ( ! is_array($atts['to_email']) && '[admin_email]' == $atts['to_email'] ) {
            $atts['to_email'] = $admin_email;
        }
    }

	private static function get_email_from_formatted_string( $value ) {
		if ( strpos( $value, '<' ) !== false ) {
			preg_match_all( '/\<([^)]+)\>/', $value, $emails );
			$value = $emails[1][0];
		}
		return $value;
	}

    /**
    * Format individual email fields
    *
    * @since 2.0
    * @param array $atts pass by reference
    * @param string $f (to, from, reply_to, etc)
    * @param string $val value saved in field
    * @param int $key if in array, this will be set
    */
    private static function format_single_field( &$atts, $f, $val, $key = false ) {
        $val = trim($val);

        // If just a plain email is used
        if ( is_email($val) ) {
            // add sender's name if not included in $from
            if ( $f == 'from' ) {
				$part_2 = $atts[ $f ];
                $part_1  = $atts['from_name'] ? $atts['from_name'] : wp_specialchars_decode( FrmAppHelper::site_name(), ENT_QUOTES );
            } else {
                return;
            }
        } else {
            $parts = explode(' ', $val);
            $part_2 = end($parts);

            // If inputted correcly, $part_2 should be an email
            if ( is_email( $part_2 ) ) {
                $part_1 = trim( str_replace( $part_2, '', $val ) );
            } else if ( in_array( $f, array( 'from', 'reply_to' ) ) ) {
				// In case someone just puts a name in the From or Reply To field
				$part_1 = $val;
                $part_2 = get_option('admin_email');
            } else {
				// In case someone just puts a name in any other email field
                if ( false !== $key ) {
					unset( $atts[ $f ][ $key ] );
                    return;
                }
				$atts[ $f ] = '';
                return;
            }
        }

		// if sending the email from a yahoo address, change it to the WordPress default
		if ( $f == 'from' && strpos( $part_2, '@yahoo.com' ) ) {
			// Get the site domain and get rid of www.
			$sitename = strtolower( FrmAppHelper::get_server_value( 'SERVER_NAME' ) );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$part_2 = 'wordpress@' . $sitename;
		}

        // Set up formatted value
		$final_val = str_replace( '"', '', $part_1 ) . ' <'. $part_2 .'>';

        // If value is an array
        if ( false !== $key ) {
			$atts[ $f ][ $key ] = $final_val;
            return;
        }
		$atts[ $f ] = $final_val;
    }

	public static function send_email( $atts ) {
        $admin_email = get_option('admin_email');
        $defaults = array(
            'to_email'      => $admin_email,
            'subject'       => '',
            'message'       => '',
            'from'          => $admin_email,
            'from_name'     => '',
            'cc'            => '',
            'bcc'           => '',
            'plain_text'    => true,
            'reply_to'      => $admin_email,
            'attachments'   => array(),
        );
        $atts = wp_parse_args($atts, $defaults);

        // Put To, BCC, CC, Reply To, and From fields in the correct format
        self::format_email_fields( $atts, $admin_email );

        $recipient      = $atts['to_email']; //recipient
        $header         = array();
        $header[]       = 'From: ' . $atts['from'];

        //Allow for cc and bcc arrays
		$array_fields = array( 'CC' => $atts['cc'], 'BCC' => $atts['bcc'] );
		$cc = array( 'CC' => array(), 'BCC' => array() );
        foreach ( $array_fields as $key => $a_field ) {
            if ( empty($a_field) ) {
                continue;
            }

			foreach ( (array) $a_field as $email ) {
				$cc[ $key ][] = $email;
            }
            unset($key, $a_field);
        }
		$cc = array_filter( $cc ); // remove cc and bcc if they are empty

		foreach ( $cc as $k => $v ) {
			$header[] = $k . ': '. implode( ',', $v );
		}

        $content_type   = $atts['plain_text'] ? 'text/plain' : 'text/html';
        $charset        = get_option('blog_charset');

        $header[]       = 'Reply-To: '. $atts['reply_to'];
		$header[]       = 'Content-Type: ' . $content_type . '; charset="' . esc_attr( $charset ) . '"';
        $atts['subject'] = wp_specialchars_decode(strip_tags(stripslashes($atts['subject'])), ENT_QUOTES );

        $message        = do_shortcode($atts['message']);

        if ( $atts['plain_text'] ) {
            //$message    = wordwrap($message, 70, "\r\n"); //in case any lines are longer than 70 chars
            $message    = wp_specialchars_decode(strip_tags($message), ENT_QUOTES );
        } else {
			// remove line breaks in HTML emails to prevent conflicts with Mandrill
        	add_filter( 'mandrill_nl2br', 'FrmNotification::remove_mandrill_br' );
        }
		$message = apply_filters( 'frm_email_message', $message, $atts );

        $header         = apply_filters('frm_email_header', $header, array(
			'to_email' => $atts['to_email'], 'subject' => $atts['subject'],
		) );

        if ( apply_filters('frm_encode_subject', 1, $atts['subject'] ) ) {
            $atts['subject'] = '=?'. $charset .'?B?'. base64_encode($atts['subject']) .'?=';
        }

        remove_filter('wp_mail_from', 'bp_core_email_from_address_filter' );
        remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');

        $sent = wp_mail($recipient, $atts['subject'], $message, $header, $atts['attachments']);
        if ( ! $sent ) {
            $header = 'From: '. $atts['from'] ."\r\n";
            $recipient = implode(',', (array) $recipient);
            $sent = mail($recipient, $atts['subject'], $message, $header);
        }

		// remove the filter now so other emails can still use it
		remove_filter( 'mandrill_nl2br', 'FrmNotification::remove_mandrill_br' );

        do_action('frm_notification', $recipient, $atts['subject'], $message);

        if ( $sent ) {
			$sent_to = array_merge( (array) $atts['to_email'], (array) $atts['cc'], (array) $atts['bcc'] );
            $sent_to = array_filter( $sent_to );
            if ( apply_filters('frm_echo_emails', false) ) {
                $temp = str_replace('<', '&lt;', $sent_to);
				echo ' ' . FrmAppHelper::kses( implode(', ', (array) $temp ) );
            }
            return $sent_to;
        }
    }

	/**
	 * This function should only be fired when Mandrill is sending an HTML email
	 * This will make sure Mandrill doesn't mess with our HTML emails
	 *
	 * @since 2.0
	 */
	public static function remove_mandrill_br() {
		return false;
	}
}
