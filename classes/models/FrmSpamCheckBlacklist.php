<?php

class FrmSpamCheckBlacklist extends FrmSpamCheck {

	const COMPARE_CONTAINS = '';

	const COMPARE_EQUALS = 'equals';

	protected $blacklist;

	public function __construct( $values ) {
		parent::__construct( $values );

		$this->blacklist = $this->get_blacklist_array();
	}

	protected function is_enabled() {
		return apply_filters( 'frm_check_blacklist', true, $this->values );
	}

	/**
	 * Gets blacklist data.
	 *
	 * @return array[]
	 */
	protected function get_blacklist_array() {
		// TODO: add the filter.
		return array(
			array(
				'file'         => FrmAppHelper::plugin_path() . '/blacklist/domain-partial.txt',
				'words'        => array(),
				'field_type'   => array(),
				'compare'      => self::COMPARE_CONTAINS, // Regex won't work if compare is `equals`.
				'extract_value' => array( 'FrmAntiSpamController', 'extract_emails_from_values' ),
			),
		);
	}

	/**
	 * Gets blacklist IP.
	 *
	 * @return array
	 */
	protected function get_blacklist_ip() {
		// TODO: add the filter.
		return array(
			'files' => array(
				FrmAppHelper::plugin_path() . '/blacklist/ip.txt',
			),
			'custom' => array(),
		);
	}

	public function check() {
		if ( $this->check_ip() ) {
			return true;
		}

		return $this->check_values();
	}

	private function check_values() {
		$whitelist = $this->get_words_from_setting( 'whitelist' );

		foreach ( $this->blacklist as $blacklist ) {
			if ( empty( $blacklist['file'] ) && empty( $blacklist['words'] ) ) {
				continue;
			}

			$blacklist['whitelist'] = $whitelist;

			if ( ! empty( $blacklist['words'] ) ) {
				foreach ( $blacklist['words'] as $word ) {
					if ( $this->single_line_check_values( $word, $blacklist ) ) {
						return true;
					}
				}
			} elseif ( file_exists( $blacklist['file'] ) ) {
				$is_spam = $this->read_lines_and_check( $blacklist['file'], array( $this, 'single_line_check_values' ), $blacklist );
				if ( $is_spam ) {
					return true;
				}
			}
		}

		return false;
	}

	private function get_words_from_setting( $setting_key ) {
		$frm_settings = FrmAppHelper::get_settings();
		$words        = isset( $frm_settings->$setting_key ) ? $frm_settings->$setting_key : '';
		if ( ! $words ) {
			return array();
		}

		return explode( "\n", $words );
	}

	private function single_line_check_values( $line, $args ) {
		// Do not check if this word is in the whitelist.
		if ( ! empty( $args['whitelist'] ) && in_array( $line, $args['whitelist'], true ) ) {
			return false;
		}

		$values_to_check = $this->get_values_to_check( $args );

		if ( self::COMPARE_EQUALS === $args['compare'] ) {
			foreach ( $values_to_check as $value ) {
				if ( $line === $value ) {
					return true;
				}
			}
			return false;
		}

		$values_str = FrmAppHelper::maybe_json_encode( $values_to_check );
		return strpos( $values_str, $line ) !== false;
	}

	/**
	 * Get the field IDs to check.
	 *
	 * @param array $blacklist The blacklist data.
	 *
	 * @return array|false Return array of field IDs or false if do not need to check.
	 */
	private function get_field_ids_to_check( $blacklist ) {
		if ( empty( $blacklist['fields'] ) || ! is_array( $blacklist['fields'] ) ) {
			return false;
		}

		$field_ids_to_check = array();
		foreach ( $this->posted_fields as $field ) {
			$field_type = FrmField::get_field_type( $field->id );
			if ( in_array( $field_type, $blacklist['fields'], true ) ) {
				$field_ids_to_check[] = $field->id;
			}
		}

		return $field_ids_to_check;
	}

	private function get_values_to_check( $blacklist ) {
		$field_ids_to_check = $this->get_field_ids_to_check( $blacklist );
		if ( array() === $field_ids_to_check ) {
			return false; // No values need to check.
		}

		$values_to_check = array();
		foreach ( $this->values['item_meta'] as $key => $value ) {
			if ( is_array( $value ) && isset( $value['form'] ) ) {
				// This is a repeater value, loop through sub values.
				unset( $value['form'] );
				unset( $value['row_ids'] );

				foreach ( $value as $sub_key => $sub_value ) {
					if ( false === $field_ids_to_check || in_array( $sub_key, $field_ids_to_check, true ) ) {
						continue;
					}
					$values_to_check[] = is_array( $sub_value ) ? implode( ' ', $sub_value ) : $sub_value;
				}
			} elseif ( false === $field_ids_to_check || in_array( $key, $field_ids_to_check, true ) ) {
				$values_to_check[] = is_array( $value ) ? implode( ' ', $value ) : $value;
			}
		}

		if ( isset( $blacklist['extract_value'] ) && is_callable( $blacklist['extract_value'] ) ) {
			$values_to_check = call_user_func( $blacklist['extract_value'], $values_to_check, $blacklist );
		}

		return $values_to_check;
	}

	private function check_ip() {
		$ip = FrmAppHelper::get_ip_address();
		if ( in_array( $ip, FrmAntiSpamController::get_whitelist_ip(), true ) ) {
			return false;
		}

		$blacklist_ip = $this->get_blacklist_ip();

		if ( ! empty( $blacklist_ip['custom'] ) && is_array( $blacklist_ip['custom'] ) && in_array( $ip, $blacklist_ip['custom'], true ) ) {
			return true;
		}

		if ( empty( $blacklist_ip['files'] ) || ! is_array( $blacklist_ip['files'] ) ) {
			return false;
		}

		foreach ( $blacklist_ip['files'] as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}
			$is_spam = $this->read_lines_and_check( $file, array( $this, 'single_line_check_ip' ), array( compact( 'ip' ) ) );
			if ( $is_spam ) {
				return true;
			}
		}

		return false;
	}

	private function single_line_check_ip( $line, $args ) {
		$ip = $args['ip'];
		return $ip === $line || 0 === strpos( $ip . '/', $line ); // Maybe IP in line is x.x.x.x/12 format.
	}

	private function read_lines_and_check( $file_path, $callback, $callback_args = array() ) {
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
				fclose( $fp );
				return true;
			}
		}

		fclose( $fp );
		return false;
	}
}
