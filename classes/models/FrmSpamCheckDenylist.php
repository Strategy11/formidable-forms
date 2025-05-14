<?php
/**
 * Spam check using denylist
 *
 * @since 6.21
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckDenylist extends FrmSpamCheck {

	const COMPARE_CONTAINS = '';

	const COMPARE_EQUALS = 'equals';

	protected $posted_fields;

	protected $denylist;

	public function __construct( $values ) {
		$this->maybe_add_form_id_to_values( $values );

		parent::__construct( $values );

		$this->denylist = $this->get_denylist_array();
	}

	protected function get_posted_fields() {
		if ( is_null( $this->posted_fields ) ) {
			$this->posted_fields = FrmField::get_all_for_form( $this->values['form_id'] );
		}
		return $this->posted_fields;
	}

	/**
	 * Maybe add form ID to values. In file name validation, only item_meta in $values.
	 *
	 * @param array $values Spam check values.
	 */
	protected function maybe_add_form_id_to_values( &$values ) {
		if ( ! empty( $values['form_id'] ) || empty( $values['item_meta'] ) ) {
			return;
		}

		$field_id = key( $values['item_meta'] );
		$field    = FrmField::getOne( $field_id );
		if ( $field ) {
			$values['form_id'] = $field->form_id;
		}
	}

	protected function is_enabled() {
		$frm_settings = FrmAppHelper::get_settings();
		$is_enabled   = $frm_settings->denylist_check;

		/**
		 * Allows disabling the denylist check.
		 *
		 * @since 6.21
		 *
		 * @param bool  $is_enabled Whether the denylist check is enabled.
		 * @param array $values     The entry values.
		 */
		return apply_filters( 'frm_check_denylist', $is_enabled, $this->values );
	}

	/**
	 * Gets denylist data.
	 * See {@see FrmSpamCheckDenylist::fill_default_denylist_data()} for more details.
	 *
	 * @return array[]
	 */
	protected function get_denylist_array() {
		$denylist_data = array(
			array(
				'file' => FrmAppHelper::plugin_path() . '/denylist/domain-partial.txt',
			),
			array(
				'file'             => FrmAppHelper::plugin_path() . '/denylist/splorp-wp-comment.txt',
				'skip'             => FrmAppHelper::current_user_can( 'frm_create_entries' ),
				'skip_field_types' => array( 'file' ),
			),
			array(
				'words'       => array(
					'moncler|north face|vuitton|handbag|burberry|outlet|prada|cialis|viagra|maillot|oakley|ralph lauren|ray ban|iphone|プラダ',
				),
				'field_types' => array( 'name' ),
				'is_regex'    => true,
			),
			array(
				'words'       => array(
					'@mail\.ru|@yandex\.',
				),
				'field_types' => array( 'email' ),
				'is_regex'    => true,
			),
		);

		$custom_denylist = $this->get_words_from_setting( 'disallowed_words' );
		if ( $custom_denylist ) {
			$denylist_data['custom'] = array(
				'words' => $custom_denylist,
			);
		}

		/**
		 * Allows to modify the denylist data.
		 *
		 * @since 6.21
		 *
		 * @param array[] $denylist_data The denylist data.
		 */
		return apply_filters( 'frm_denylist_data', $denylist_data );
	}

	/**
	 * Gets denylist IP addresses.
	 *
	 * @return array
	 */
	protected function get_denylist_ips() {
		return apply_filters(
			'frm_denylist_ips_data',
			array(
				'files'  => array(
					FrmAppHelper::plugin_path() . '/denylist/ip.txt',
				),
				'custom' => array(),
			)
		);
	}

	/**
	 * Checks spam.
	 *
	 * @return bool
	 */
	public function check() {
		if ( $this->check_ip() ) {
			return true;
		}

		return $this->check_values();
	}

	/**
	 * Checks entry values.
	 *
	 * @return bool
	 */
	protected function check_values() {
		$allowed_words = $this->get_words_from_setting( 'allowed_words' );
		$allowed_words = array_map( array( $this, 'convert_to_lowercase' ), $allowed_words );

		foreach ( $this->denylist as $denylist ) {
			if ( ! empty( $denylist['skip'] ) ) {
				continue;
			}

			if ( empty( $denylist['file'] ) && empty( $denylist['words'] ) ) {
				continue;
			}

			$this->fill_default_denylist_data( $denylist );
			$denylist['allowed_words'] = $allowed_words;

			if ( ! empty( $denylist['words'] ) ) {
				foreach ( $denylist['words'] as $word ) {
					if ( $this->single_line_check_values( $word, $denylist ) ) {
						self::add_spam_keyword_to_option( $word );
						return true;
					}
				}
			} elseif ( file_exists( $denylist['file'] ) ) {
				$is_spam = $this->read_lines_and_check( $denylist['file'], array( $this, 'single_line_check_values' ), $denylist );
				if ( $is_spam ) {
					return true;
				}
			}
		}//end foreach

		return false;
	}

	/**
	 * Fills default denylist data.
	 *
	 * @param array $denylist Denylist.
	 */
	protected function fill_default_denylist_data( &$denylist ) {
		$denylist = wp_parse_args(
			$denylist,
			array(
				'file'             => '',
				'words'            => array(),
				'is_regex'         => false,
				'field_types'      => array(),
				// Add `other` if you want to skip checking Other values of some field types.
				'skip_field_types' => array(),
				// Is ignore if `is_regex` is `true`.
				'compare'          => self::COMPARE_CONTAINS,
				'extract_value'    => '',
				// If this is `true`, this denylist will be skipped.
				'skip'             => false,
			)
		);

		// Some field types should never be checked.
		$denylist['skip_field_types'] = array_merge(
			$denylist['skip_field_types'],
			array( 'password', 'captcha', 'signature', 'checkbox', 'radio', 'select' )
		);
	}

	/**
	 * Gets words from setting.
	 *
	 * @param string $setting_key Setting key.
	 * @return array
	 */
	protected function get_words_from_setting( $setting_key ) {
		$frm_settings = FrmAppHelper::get_settings();
		$words        = isset( $frm_settings->$setting_key ) ? $frm_settings->$setting_key : '';
		if ( ! $words ) {
			return array();
		}

		return array_filter(
			array_map( 'trim', explode( "\n", $words ) )
		);
	}

	/**
	 * Checks the values against each single word.
	 *
	 * @param string $line Single line.
	 * @param array  $args Check args.
	 * @return bool
	 */
	protected function single_line_check_values( $line, $args ) {
		$line = $this->convert_to_lowercase( $line );
		// Do not check if this word is in the allowed words.
		if ( ! empty( $args['allowed_words'] ) && in_array( $line, $args['allowed_words'], true ) ) {
			return false;
		}

		$values_to_check = $this->get_values_to_check( $args );
		if ( ! $values_to_check ) {
			// Nothing needs to be checked.
			return false;
		}

		if ( ! empty( $args['is_regex'] ) ) {
			return preg_match( '/' . trim( $line, '/' ) . '/i', $this->convert_values_to_string( $values_to_check ) );
		}

		if ( self::COMPARE_EQUALS === $args['compare'] ) {
			foreach ( $values_to_check as $value ) {
				$value = $this->convert_to_lowercase( $value );
				if ( $line === $value ) {
					return true;
				}
			}
			return false;
		}

		$values_str = strtolower( $this->convert_values_to_string( $values_to_check ) );
		return strpos( $values_str, $line ) !== false;
	}

	/**
	 * Converts values to string to check.
	 *
	 * @param array $values Values array.
	 * @return string
	 */
	protected function convert_values_to_string( $values ) {
		return FrmAppHelper::maybe_json_encode( $values );
	}

	/**
	 * Converts string to lowercase.
	 *
	 * @param string $str String.
	 * @return string
	 */
	protected function convert_to_lowercase( $str ) {
		return strtolower( $str );
	}

	/**
	 * Get the field IDs to check.
	 *
	 * @param array $denylist The denylist data.
	 *
	 * @return array|false Return array of field IDs or false if do not need to check.
	 */
	protected function get_field_ids_to_check( array $denylist ) {
		$field_types      = isset( $denylist['field_types'] ) && is_array( $denylist['field_types'] ) ? $denylist['field_types'] : array();
		$skip_field_types = isset( $denylist['skip_field_types'] ) && is_array( $denylist['skip_field_types'] ) ? $denylist['skip_field_types'] : array();

		if ( ! $field_types && ! $skip_field_types ) {
			// This will check all fields.
			return false;
		}

		$field_ids_to_check = array();
		foreach ( $this->get_posted_fields() as $field ) {
			$field_type = FrmField::get_field_type( $field );
			if ( in_array( $field_type, $skip_field_types, true ) ) {
				continue;
			}

			if ( $field_types && ! in_array( $field_type, $field_types, true ) ) {
				continue;
			}

			$field_ids_to_check[] = intval( $field->id );
		}

		return $field_ids_to_check;
	}

	/**
	 * Gets values to check.
	 *
	 * @param array $denylist Single denylist data.
	 * @return array|false Return `false` if no values need to check, or return array of values.
	 */
	protected function get_values_to_check( $denylist ) {
		$field_ids_to_check = $this->get_field_ids_to_check( $denylist );
		if ( array() === $field_ids_to_check ) {
			// No values need to check.
			return false;
		}

		$values_to_check = array();
		foreach ( $this->values['item_meta'] as $key => $value ) {
			if ( is_array( $value ) && isset( $value['form'] ) ) {
				// This is a repeater value, loop through sub values.
				unset( $value['form'] );
				unset( $value['row_ids'] );

				foreach ( $value as $sub_key => $sub_value ) {
					if ( $this->should_check_this_field( $sub_key, $field_ids_to_check ) ) {
						$this->add_to_values_to_check( $values_to_check, $sub_value );
					}
				}
			} elseif ( 'other' === $key ) {
				if ( ! in_array( 'other', $denylist['skip_field_types'], true ) ) {
					// This is Other values, loop through this and add sub values.
					foreach ( $value as $sub_value ) {
						$this->add_to_values_to_check( $values_to_check, $sub_value );
					}
				}
			} elseif ( $this->should_check_this_field( $key, $field_ids_to_check ) ) {
				$this->add_to_values_to_check( $values_to_check, $value );
			}
		}//end foreach

		if ( isset( $denylist['extract_value'] ) && is_callable( $denylist['extract_value'] ) ) {
			$values_to_check = call_user_func( $denylist['extract_value'], $values_to_check, $denylist );
		}

		return $values_to_check;
	}

	/**
	 * Checks if should check the value of the given field ID.
	 *
	 * @param int   $field_id           Field ID.
	 * @param int[] $field_ids_to_check Field IDs to check.
	 * @return bool
	 */
	protected function should_check_this_field( $field_id, $field_ids_to_check ) {
		// Should check this field if no field types is specific or this field ID is in the field IDs to check array.
		return false === $field_ids_to_check || in_array( $field_id, $field_ids_to_check, true );
	}

	/**
	 * Adds the value to values to check array.
	 *
	 * @param array $values_to_check Values to check array.
	 * @param mixed $value           The value.
	 */
	protected function add_to_values_to_check( &$values_to_check, $value ) {
		$values_to_check[] = is_array( $value ) ? implode( ' ', $value ) : $value;
	}

	/**
	 * Checks if IP is denied.
	 *
	 * @return bool
	 */
	protected function check_ip() {
		$ip = FrmAppHelper::get_ip_address();
		if ( $this->is_allowed_ip( $ip ) ) {
			return false;
		}

		$denylist_ips = $this->get_denylist_ips();

		if ( ! empty( $denylist_ips['custom'] ) && $this->ip_matches_array( $ip, $denylist_ips['custom'] ) ) {
			return true;
		}

		if ( empty( $denylist_ips['files'] ) || ! is_array( $denylist_ips['files'] ) ) {
			return false;
		}

		foreach ( $denylist_ips['files'] as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$is_spam = $this->read_lines_and_check(
				$file,
				array( $this, 'single_line_check_ip' ),
				compact( 'ip' )
			);

			if ( $is_spam ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Reads lines in file and do the check.
	 *
	 * @param string   $file_path     File path.
	 * @param callable $callback      Check callback.
	 * @param array    $callback_args Callback args.
	 * @return bool
	 */
	protected function read_lines_and_check( $file_path, $callback, $callback_args = array() ) {
		if ( ! is_callable( $callback ) ) {
			return false;
		}

		$fp = @fopen( $file_path, 'r' );
		if ( ! $fp ) {
			return false;
		}

		while ( ( $line = fgets( $fp ) ) !== false ) {
			$line = trim( $line );
			if ( $line === '' ) {
				continue;
			}

			$is_spam = $callback( $line, $callback_args );
			if ( $is_spam ) {
				if ( is_array( $callback ) && isset( $callback[1] ) && 'single_line_check_values' === $callback[1] ) {
					self::add_spam_keyword_to_option( $line );
				}

				fclose( $fp );
				return true;
			}
		}

		fclose( $fp );
		return false;
	}

	/**
	 * Checks if the given IP is allowed.
	 *
	 * @param string $ip IP address.
	 * @return bool
	 */
	protected function is_allowed_ip( $ip ) {
		return $this->ip_matches_array( $ip, FrmAntiSpamController::get_allowed_ips() );
	}

	protected function single_line_check_ip( $line, $args ) {
		return $this->ip_matches( $args['ip'], $line );
	}

	/**
	 * Checks if the given IP address matches the IP address with CIDR format.
	 *
	 * @param string $ip      IP address.
	 * @param string $cidr_ip IP address with CIDR format (x.x.x.x/24).
	 * @return bool
	 */
	protected function ip_matches( $ip, $cidr_ip ) {
		$cidr_parts = explode( '/', $cidr_ip );

		// If the second IP doesn't have CIDR format, just use equals comparison.
		if ( 1 === count( $cidr_parts ) ) {
			return $ip === $cidr_ip;
		}

		if ( 0 === strpos( $ip . '/', $cidr_ip ) ) {
			// 1.1.1.1 and 1.1.1.1/24 matches.
			return true;
		}

		// Validate IP address format - only IPv4 is supported in the CIDR check.
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return false;
		}

		list( $net, $mask ) = explode( '/', $cidr_ip );

		$ip_net  = ip2long( $net );
		$ip_mask = ~( ( 1 << ( 32 - intval( $mask ) ) ) - 1 ); // phpcs:ignore SlevomatCodingStandard.PHP.UselessParentheses.UselessParentheses

		$ip_ip = ip2long( $ip );

		return ( $ip_ip & $ip_mask ) === ( $ip_net & $ip_mask );
	}

	/**
	 * Checks if the given IP matches an IP in the array.
	 *
	 * @param string   $ip       The IP address.
	 * @param string[] $ip_array Array of IP addresses.
	 * @return bool
	 */
	protected function ip_matches_array( $ip, $ip_array ) {
		foreach ( $ip_array as $cidr_ip ) {
			if ( $this->ip_matches( $ip, $cidr_ip ) ) {
				return true;
			}
		}
		return false;
	}

	protected function get_spam_message() {
		return __( 'Your entry appears to be blocked spam!', 'formidable' );
	}

	private function add_spam_keyword_to_option( $keyword ) {
		$transient_name = 'frm_recent_spam_detected';
		$transient      = get_transient( $transient_name );
		if ( ! is_array( $transient ) ) {
			$transient = array();
		}

		if ( in_array( $keyword, $transient, true ) ) {
			return;
		}

		$transient[] = $keyword;
		set_transient( $transient_name, $transient, DAY_IN_SECONDS );
	}
}
