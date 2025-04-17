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
	public static function get_blacklist_ip() {
		// TODO: add the filter.
		return array(
			'files' => array(
				FrmAppHelper::plugin_path() . '/blacklist/ip.txt',
			),
			'custom' => array(),
		);
	}

	public function check() {
		$this->load_blacklist();
	}
}
