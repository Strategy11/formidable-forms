<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckDenylist extends FrmSpamCheck {

	const COMPARE_CONTAINS = '';

	const COMPARE_EQUALS = 'equals';

	protected $posted_fields;

	protected $denylist;

	public function __construct( $values ) {
		parent::__construct( $values );

		$this->posted_fields = FrmField::get_all_for_form( $values['form_id'] );
		$this->denylist      = $this->get_denylist_array();
	}

	protected function is_enabled() {
		/**
		 * Allows to disable the denylist check.
		 *
		 * @since x.x
		 *
		 * @param bool  $is_enabled Whether the denylist check is enabled.
		 * @param array $values     The entry values.
		 */
		return apply_filters( 'frm_check_denylist', true, $this->values );
	}

	/**
	 * Gets denylist data.
	 *
	 * @return array[]
	 */
	protected function get_denylist_array() {
		$denylist_data = array(
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

		$custom_denylist = $this->get_words_from_setting( 'denylist' );
		if ( $custom_denylist ) {
			$denylist_data['custom'] = array(
				'words' => $custom_denylist,
			);
		}

		/**
		 * Allows to modify the denylist data.
		 *
		 * @since x.x
		 *
		 * @param array[] $denylist_data The denylist data
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

	public function check() {
		if ( $this->check_ip() ) {
			return true;
		}

		return $this->check_values();
	}

	private function check_values() {
		$allowed_words = $this->get_words_from_setting( 'allowed_words' );
		$allowed_words = array_map( array( $this, 'convert_to_lowercase' ), $allowed_words );

		foreach ( $this->denylist as $denylist ) {
			if ( empty( $denylist['file'] ) && empty( $denylist['words'] ) ) {
				continue;
			}

			$this->fill_default_denylist_data( $denylist );
			$denylist['allowed_words'] = $denylist;

			if ( ! empty( $denylist['words'] ) ) {
				foreach ( $denylist['words'] as $word ) {
					if ( $this->single_line_check_values( $word, $denylist ) ) {
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

	private function fill_default_denylist_data( &$denylist ) {
		$denylist = wp_parse_args(
			$denylist,
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
		// Do not check if this word is in the allowed words.
		if ( ! empty( $args['allowed_words'] ) && in_array( $line, $args['allowed_words'], true ) ) {
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
	 * @param array $denylist The denylist data.
	 *
	 * @return array|false Return array of field IDs or false if do not need to check.
	 */
	private function get_field_ids_to_check( array $denylist ) {
		if ( empty( $denylist['field_type'] ) || ! is_array( $denylist['field_type'] ) ) {
			return false;
		}

		$field_ids_to_check = array();
		foreach ( $this->posted_fields as $field ) {
			$field_type = FrmField::get_field_type( $field );
			if ( in_array( $field_type, $denylist['field_type'], true ) ) {
				$field_ids_to_check[] = intval( $field->id );
			}
			unset( $field );
		}

		return $field_ids_to_check;
	}

	private function get_values_to_check( $denylist ) {
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
					if ( false === $field_ids_to_check || in_array( $sub_key, $field_ids_to_check, true ) ) {
						continue;
					}
					$values_to_check[] = is_array( $sub_value ) ? implode( ' ', $sub_value ) : $sub_value;
				}
			} elseif ( false === $field_ids_to_check || in_array( $key, $field_ids_to_check, true ) ) {
				$values_to_check[] = is_array( $value ) ? implode( ' ', $value ) : $value;
			}
		}

		if ( isset( $denylist['extract_value'] ) && is_callable( $denylist['extract_value'] ) ) {
			$values_to_check = call_user_func( $denylist['extract_value'], $values_to_check, $denylist );
		}

		return $values_to_check;
	}

	private function check_ip() {
		$ip = FrmAppHelper::get_ip_address();
		if ( in_array( $ip, FrmAntiSpamController::get_allowed_ips(), true ) ) {
			return false;
		}

		$denylist_ips = $this->get_denylist_ips();

		if ( ! empty( $denylist_ips['custom'] ) && is_array( $denylist_ips['custom'] ) && in_array( $ip, $denylist_ips['custom'], true ) ) {
			return true;
		}

		if ( empty( $denylist_ips['files'] ) || ! is_array( $denylist_ips['files'] ) ) {
			return false;
		}

		foreach ( $denylist_ips['files'] as $file ) {
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
