<?php

class FrmSpamCheckBlacklist extends FrmSpamCheck {

	const COMPARE_CONTAINS = '';
	const COMPARE_EQUALS = 'equals';
	const COMPARE_DOMAIN = 'domain';

	const EXTRACT_NO = '';

	const EXTRACT_SINGLE = 'single';

	const EXTRACT_EMAIL = 'email';

	protected $blacklist;

	public function __construct( $values, $posted_fields ) {
		parent::__construct( $values, $posted_fields );

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
				'extract_value' => self::EXTRACT_EMAIL,
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
				$is_spam = $this->read_lines_and_check( $blacklist['file'], array( $this, 'single_line_check_values'), $blacklist );
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

		$values_to_check = FrmAppHelper::maybe_json_encode( $this->values );
		return strpos( $values_to_check, $line ) !== false; // TODO: check compare and extract value.
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
