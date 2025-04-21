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
				'regexes'      => array(),
				'field_type'   => array(),
				'compare'      => self::COMPARE_CONTAINS, // Regex won't work if compare is `equals`.
				'extract_value' => self::EXTRACT_EMAIL,
			),
		);
	}

	public function loop_through_blacklist( $callback ) {
		if ( ! is_callable( $callback ) ) {
			return;
		}

		foreach ( $this->blacklist as $blacklist ) {
			$callback( $blacklist );
		}
	}

	protected function single_blacklist_check( $blacklist ) {
		if ( empty( $blacklist['file'] ) && empty( $blacklist['regexes'] ) ) {
			return false; // Not a spam because not have data to check.
		}

		if ( ! empty( $blacklist['regexes'] ) ) {
			foreach ( $blacklist['regexes'] as $regex ) {
				if ( preg_match( $regex, $this->values['email'] ) ) {
					return true;
				}
			}
		}
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
	}



	private function check_ip() {
		$ip = FrmAppHelper::get_ip_address();
		if ( in_array( $ip, FrmAntiSpamController::get_whitelist_ip(), true ) ) {
			return false;
		}

		if ( ! empty( $blacklist_ip['custom'] ) && is_array( $blacklist_ip['custom'] ) && in_array( $ip, $blacklist_ip['custom'], true ) ) {
			return true;
		}

		if ( empty( $blacklist_ip['files'] ) || ! is_array( $blacklist_ip['files'] ) ) {
			return false;
		}

		foreach ( $blacklist_ip['files'] as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;

				$this->read_lines_and_check( $file, array( $this, 'single_line_check_ip' ), array( compact( 'ip' ) ) );
			}
		}

		return false;

		$blacklist_ip = $this->get_blacklist_ip();
		foreach ( $blacklist_ip as $ip_addresses ) {
			if ( is_string( $ip_addresses ) && file_exists( $ip_addresses ) ) {
				$fp = @fopen( $ip_addresses, 'r' );
				if ( $fp ) {
					while ( ( $line = fgets( $fp ) ) !== false ) {
						$line = trim( $line );
						if ( $line === $ip || 0 === strpos( $ip . '/', $line ) ) {
							fclose( $fp );
							return false;
						}
					}
					fclose( $fp );
				}
			} elseif ( is_array( $ip_addresses ) ) {
				foreach ( $ip_addresses as $ip_address ) {
					if ( $ip === $ip_address || 0 === strpos( $ip . '/', $ip_address ) ) {
						return false;
					}
				}
			}
		}

		return true;
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
