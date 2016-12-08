<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAntiSpam {

	private static function is_spam( $comment ) {
		$url = $email = $author = $body = $comment; // TODO: get values from form
		$options = array(
			'time_check' => 1, 'bbcode_check' => 1,
			'advanced_check' => 1, 'regexp_check' => 1,
			'dnsbl_check' => 1,
		);

		$response = array( 'spam' => false );

		/* Check if logged in */
		if ( is_user_logged_in() ) {
		    return $response;
		}

		/* Honeypot */
		if ( ! empty( $_POST['ab_spam__hidden_field'] ) ) {
			$response['reason'] = 'css';
			return $response;
		}

		$ip = FrmAppHelper::get_ip_address();
		if ( empty( $ip ) ) {
			$response['reason'] = 'empty';
			return $response;
		}

		/* Action time */
		if ( $options['time_check'] && self::_is_shortest_time() ) {
			$response['reason'] = 'time';
			return $response;
		}

		/* BBCode Spam */
		if ( $options['bbcode_check'] && self::_is_bbcode_spam( $body ) ) {
			$response['reason'] = 'bbcode';
			return $response;
		}

		if ( $options['advanced_check'] && self::_is_fake_ip( $ip ) ) {
			$response['reason'] = 'server';
			return $response;
		}

		/* Regexp for Spam */
		if ( $options['regexp_check'] ) {
			$is_spam = self::_is_regexp_spam( array(
				'ip'	 => $ip,
				'host'	 => parse_url( $url, PHP_URL_HOST ),
				'body'	 => $body,
				'email'	 => $email,
				'author' => $author,
			) );
			if ( $is_spam ) {
				$response['reason'] = 'regexp';
				return $response;
			}
		}

		/* DNSBL Spam */
		if ( $options['dnsbl_check'] && self::_is_dnsbl_spam( $ip ) ) {
			$response['reason'] = 'dnsbl';
			return $response;
		}
	}

	/**
	* Check for form submission time
	*
	* @return  boolean    TRUE if the action time is less than 5 seconds
	*/

	private static function _is_shortest_time() {
		$too_short = false;
		$start_time = FrmAppHelper::get_post_param( 'ab_init_time', 0, 'absint' );

		if ( $start_time ) {
			// Compare time values
			$min_seconds = apply_filters( 'frm_spam_time_limit', 5 );
			$total_time = time() - $start_time;
			if ( $total_time < $min_seconds ) {
				$too_short = true;
			}
		}

		return $too_short;
	}

	private static function _is_bbcode_spam( $body ) {
		return (bool) preg_match( '/\[url[=\]].*\[\/url\]/is', $body );
	}

	private static function _is_fake_ip( $client_ip, $client_host = false ) {
		/* Remote Host */
		$host_by_ip = gethostbyaddr( $client_ip );

		/* IPv6 */
		if ( self::_is_ipv6( $client_ip ) ) {
			return $client_ip != $host_by_ip;
		}

		/* IPv4 */
		if ( empty( $client_host ) ) {
			$ip_by_host = gethostbyname( $host_by_ip );

			if ( $ip_by_host === $host_by_ip ) {
				return false;
			}
		} else {
			/* IPv4 / API */
			if ( $host_by_ip === $client_ip ) {
				return true;
			}

			$ip_by_host = gethostbyname( $client_host );
		}

		if ( strpos( $client_ip, self::_cut_ip( $ip_by_host ) ) === false ) {
			return true;
		}

		return false;
	}

	/**
	* Check for an IPv6 address
	*
	* @param   string   $ip  IP to validate
	* @return  boolean       TRUE if IPv6
	*/

	private static function _is_ipv6( $ip ) {
		if ( function_exists('filter_var') ) {
			return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) !== false;
		} else {
			return ! self::_is_ipv4( $ip );
		}
	}

	/**
	 * Check for an IPv4 address
	 *
	 * @param   string   $ip  IP to validate
	 * @return  integer       TRUE if IPv4
	 */
	private static function _is_ipv4( $ip ) {
		if ( function_exists('filter_var') ) {
			return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false;
		} else {
			return preg_match( '/^\d{1,3}(\.\d{1,3}){3,3}$/', $ip );
		}
	}

	private static function _cut_ip( $ip, $cut_end = true ) {
		$separator = ( self::_is_ipv4( $ip ) ? '.' : ':' );
		$part = ( $cut_end ? strrchr( $ip, $separator ) : strstr( $ip, $separator ) );

		return str_replace( $part, '', $ip );
	}

	private static function _is_regexp_spam( $comment ) {
		/* Felder */
		$fields = array(
			'ip',
			'host',
			'body',
			'email',
			'author',
		);

		/* Regexp */
		$patterns = array(
			0 => array(
				'host'	=> '^(www\.)?\d+\w+\.com$',
				'body'	=> '^\w+\s\d+$',
				'email'	=> '@gmail.com$',
			),
			1 => array(
				'body'	=> '\<\!.+?mfunc.+?\>',
			),
		);

		/* Spammy author */
		if ( $quoted_author = preg_quote( $comment['author'], '/' ) ) {
			$patterns[] = array(
				'body' => sprintf( '<a.+?>%s<\/a>$', $quoted_author ),
			);
			$patterns[] = array(
				'body' => sprintf( '%s https?:.+?$', $quoted_author ),
			);
			$patterns[] = array(
				'email'	 => '@gmail.com$',
				'author' => '^[a-z0-9-\.]+\.[a-z]{2,6}$',
				'host'	 => sprintf( '^%s$', $quoted_author ),
			);
		}

		/* Hook */
		$patterns = apply_filters( 'antispam_bee_patterns', $patterns );

		if ( ! $patterns ) {
			return false;
		}

		foreach ( $patterns as $pattern ) {
			$hits = array();

			foreach ( $pattern as $field => $regexp ) {
				$is_empty = ( empty( $field ) || ! in_array( $field, $fields ) || empty( $regexp ) );
				if ( $is_empty ) {
					continue;
				}

				/* Ignore non utf-8 chars */
				$comment[ $field ] = ( function_exists('iconv') ? iconv( 'utf-8', 'utf-8//TRANSLIT', $comment[ $field ] ) : $comment[ $field ] );

				if ( empty( $comment[ $field ] ) ) {
					continue;
				}

				/* Perform regex */
				if ( preg_match( '/' . $regexp . '/isu', $comment[ $field ] ) ) {
					$hits[ $field ] = true;
				}
			}

			if ( count( $hits ) === count( $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	private static function _is_dnsbl_spam( $ip ) {

		$response = wp_safe_remote_request(
			esc_url_raw(
				sprintf( 'http://www.stopforumspam.com/api?ip=%s&f=json', $ip ),
				'http'
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		/* Get JSON */
		$json = wp_remote_retrieve_body( $response );

		$result = json_decode( $json );

		if ( empty( $result->success ) ) {
			return false;
		}

		$status = (bool) $result->ip->appears;
		return $status;
	}
}
