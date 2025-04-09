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
			'custom' => $this->get_custom_words( 'blacklist' ),
		);
	}

	private function get_custom_words( $setting_key ) {
		$frm_settings = FrmAppHelper::get_settings();
		$custom_words = isset( $frm_settings->$setting_key ) ? $frm_settings->$setting_key : '';
		if ( ! $custom_words ) {
			return array();
		}

		return explode( "\n", $custom_words );
	}

	/**
	 * @return bool Return `true` if not spam, `false` if spam.
	 */
	public function validate() {
		if ( ! apply_filters( 'frm_check_blacklist', true, $this->values ) ) {
			return true;
		}

		if ( $this->contains_wp_disallowed_words() ) {
			return false;
		}

		if ( ! $this->validate_ip() ) {
			return false;
		}

		$blacklist  = $this->get_blacklist();
		$whitelist  = $this->get_custom_words( 'whitelist' );
		$values_str = FrmAppHelper::maybe_json_encode( $this->values );

		foreach ( $blacklist as $key => $value ) {
			if ( is_string( $value ) ) {
				if ( file_exists( $value ) ) {
					$fp = @fopen( $value, 'r' );
					if ( $fp ) {
						while ( ( $line = fgets( $fp ) ) !== false ) {
							$line = trim( $line );

							// Do not check if this word is in the whitelist.
							if ( in_array( $line, $whitelist, true ) ) {
								continue;
							}

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
					// Do not check if this word is in the whitelist.
					if ( in_array( $line, $whitelist, true ) ) {
						continue;
					}

					if ( strpos( $values_str, $line ) !== false ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	private function contains_wp_disallowed_words() {
		$mod_keys = trim( $this->get_disallowed_words() );
		if ( empty( $mod_keys ) ) {
			return false;
		}

		$values  = $this->values;
		$content = FrmEntriesHelper::entry_array_to_string( $values );

		FrmEntryValidate::prepare_values_for_spam_check( $values );
		$ip         = FrmAppHelper::get_ip_address();
		$user_agent = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$user_info  = FrmEntryValidate::get_spam_check_user_info( $values );

		return $this->do_check_wp_disallowed_words( $user_info['comment_author'], $user_info['comment_author_email'], $user_info['comment_author_url'], $content, $ip, $user_agent );
	}

	/**
	 * For WP 5.5 compatibility.
	 *
	 * @since 4.06.02
	 */
	private function do_check_wp_disallowed_words( $author, $email, $url, $content, $ip, $user_agent ) {
		if ( function_exists( 'wp_check_comment_disallowed_list' ) ) {
			return wp_check_comment_disallowed_list( $author, $email, $url, $content, $ip, $user_agent );
		}
		// phpcs:ignore WordPress.WP.DeprecatedFunctions.wp_blacklist_checkFound
		return wp_blacklist_check( $author, $email, $url, $content, $ip, $user_agent );
	}



	/**
	 * For WP 5.5 compatibility.
	 *
	 * @since 4.06.02
	 */
	private function get_disallowed_words() {
		$keys = get_option( 'disallowed_keys' );
		if ( false === $keys ) {
			// Fallback for WP < 5.5.
			// phpcs:ignore WordPress.WP.DeprecatedParameterValues.Found
			$keys = get_option( 'blacklist_keys' );
		}
		return $keys;
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
