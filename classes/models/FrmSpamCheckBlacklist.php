<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckBlacklist extends FrmSpamCheck {

	const COMPARE_CONTAINS = '';

	const COMPARE_EQUALS = 'equals';

	protected $posted_fields;

	protected $blacklist;

	public function __construct( $values ) {
		parent::__construct( $values );

		$this->posted_fields = FrmField::get_all_for_form( $values['form_id'] );
		$this->blacklist     = $this->get_blacklist_array();
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
		$blacklist_data = array(
			array(
				'file'    => FrmAppHelper::plugin_path() . '/denylist/domain-partial.txt',
				'compare' => self::COMPARE_CONTAINS,
			),
			array(
				'words'      => array(
					'moncler|north face|vuitton|handbag|burberry|outlet|prada|cialis|viagra|maillot|oakley|ralph lauren|ray ban|iphone|プラダ',
				),
				'field_type' => array( 'name' ),
				'is_regex'   => true,
			),
			array(
				'words'      => array(
					'@mail\.ru|@yandex\.',
				),
				'field_type' => array( 'email' ),
				'is_regex'   => true,
			),
		);

		$custom_blacklist = $this->get_words_from_setting( 'blacklist' );
		if ( $custom_blacklist ) {
			$blacklist_data['custom'] = array(
				'words' => $custom_blacklist,
			);
		}

		return apply_filters( 'frm_blacklist_data', $blacklist_data );
	}

	/**
	 * Gets blacklist IP.
	 *
	 * @return array
	 */
	protected function get_blacklist_ip() {
		return apply_filters(
			'frm_blacklist_ip_data',
			array(
				'files'  => array(
					FrmAppHelper::plugin_path() . '/denylist/ip.txt',
				),
				'custom' => array(),
			)
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
		$whitelist = array_map( array( $this, 'convert_to_lowercase' ), $whitelist );

		foreach ( $this->blacklist as $blacklist ) {
			if ( empty( $blacklist['file'] ) && empty( $blacklist['words'] ) ) {
				continue;
			}

			$this->fill_default_blacklist_data( $blacklist );
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
		}//end foreach

		return false;
	}

	private function fill_default_blacklist_data( &$blacklist ) {
		$blacklist = wp_parse_args(
			$blacklist,
			array(
				'file'          => '',
				'words'         => array(),
				'is_regex'      => false,
				'field_type'    => array(),
				'compare'       => self::COMPARE_CONTAINS, // Is ignore if `is_regex` is `true`.
				'extract_value' => '',
			)
		);
	}

	private function get_words_from_setting( $setting_key ) {
		$frm_settings = FrmAppHelper::get_settings();
		$words        = isset( $frm_settings->$setting_key ) ? $frm_settings->$setting_key : '';
		if ( ! $words ) {
			return array();
		}

		return array_filter(
			array_map( 'trim', explode( "\n", $words ) )
		);
	}

	private function single_line_check_values( $line, $args ) {
		$line = $this->convert_to_lowercase( $line );
		// Do not check if this word is in the whitelist.
		if ( ! empty( $args['whitelist'] ) && in_array( $line, $args['whitelist'], true ) ) {
			return false;
		}

		$values_to_check = $this->get_values_to_check( $args );
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

	private function convert_values_to_string( $values ) {
		return FrmAppHelper::maybe_json_encode( $values );
	}

	private function convert_to_lowercase( $str ) {
		return strtolower( $str );
	}

	/**
	 * Get the field IDs to check.
	 *
	 * @param array $blacklist The blacklist data.
	 *
	 * @return array|false Return array of field IDs or false if do not need to check.
	 */
	private function get_field_ids_to_check( $blacklist ) {
		if ( empty( $blacklist['field_type'] ) || ! is_array( $blacklist['field_type'] ) ) {
			return false;
		}

		$field_ids_to_check = array();
		foreach ( $this->posted_fields as $field ) {
			$field_type = FrmField::get_field_type( $field );
			if ( in_array( $field_type, $blacklist['field_type'], true ) ) {
				$field_ids_to_check[] = intval( $field->id );
			}
			unset( $field );
		}

		return $field_ids_to_check;
	}

	private function get_values_to_check( $blacklist ) {
		$field_ids_to_check = $this->get_field_ids_to_check( $blacklist );
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
		if ( in_array( $ip, FrmAntiSpamController::get_allowed_ips(), true ) ) {
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
			$is_spam = $this->read_lines_and_check( $file, array( $this, 'single_line_check_ip' ), compact( 'ip' ) );
			if ( $is_spam ) {
				return true;
			}
		}

		return false;
	}

	private function single_line_check_ip( $line, $args ) {
		$ip = $args['ip'];

		// Maybe IP in line is x.x.x.x/12 format.
		return $ip === $line || 0 === strpos( $ip . '/', $line );
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
