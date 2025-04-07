<?php

class FrmBlacklistSpamCheck extends FrmValidate {

	private $values;

	public function set_values( $values ) {
		$this->values = $values;
	}

	/**
	 * @return string
	 */
	protected function get_option_key() {
		return 'blacklist';
	}

	private function get_blacklist_ip() {
		return array(
			FrmAppHelper::plugin_path() . '/blacklist/toxic_ip_cidr.txt'
		);
	}

	private function get_blacklist() {
		return array(
			'domain' => FrmAppHelper::plugin_path() . '/blacklist/toxic_domain_partial.txt',
		);
	}

	/**
	 * @return bool Return `true` if not spam, `false` if spam.
	 */
	public function validate() {
		if ( ! $this->validate_ip() ) {
			return false;
		}

		$blacklist = $this->get_blacklist();
		$values_str = FrmAppHelper::maybe_json_encode( $this->values );

		foreach ( $blacklist as $key => $value ) {
			if ( is_string( $value ) ) {
				if ( file_exists( $value ) ) {
					$fp = @fopen( $value, 'r' );
					if ( $fp ) {
						while ( ( $line = fgets( $fp ) ) !== false ) {
							$line = trim( $line );
							if ( strpos( $values_str, $line ) !== false ) {
								fclose( $fp );
								return false;
							}
						}
						fclose( $fp );
					}
				}
			} elseif ( is_array( $value ) ) {
				foreach ( $value as $line ) {
					if ( strpos( $values_str, $line ) !== false ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	private function validate_ip() {
		$ip = FrmAppHelper::get_ip_address();
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
}
