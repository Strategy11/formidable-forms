<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.17
 */
class FrmApiHelper {

	/**
	 * Check if an API item matches the current site license target.
	 *
	 * @since 6.17
	 *
	 * @param array $item Inbox or Sale item.
	 * @return bool
	 */
	public static function is_for_user( $item ) {
		if ( ! isset( $item['who'] ) || $item['who'] === 'all' ) {
			return true;
		}
		$who = (array) $item['who'];
		if ( self::is_for_everyone( $who ) ) {
			return true;
		}
		if ( self::is_user_type( $who ) ) {
			return true;
		}
		if ( in_array( 'free_first_30', $who, true ) && self::is_free_first_30() ) {
			return true;
		}
		if ( in_array( 'free_not_first_30', $who, true ) && self::is_free_not_first_30() ) {
			return true;
		}
		return false;
	}

	/**
	 * @since 6.17
	 *
	 * @param array $who
	 * @return bool
	 */
	private static function is_for_everyone( $who ) {
		return in_array( 'all', $who, true );
	}

	/**
	 * @since 6.17
	 *
	 * @param array $who
	 * @return bool
	 */
	private static function is_user_type( $who ) {
		return in_array( self::get_user_type(), $who, true );
	}

	/**
	 * @since 6.17
	 *
	 * @return string
	 */
	private static function get_user_type() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return 'free';
		}

		return FrmAddonsController::license_type();
	}

	/**
	 * Check if user is still using the Lite version only, and within
	 * the first 30 days of activation.
	 *
	 * @since 6.17
	 *
	 * @return bool
	 */
	private static function is_free_first_30() {
		return self::is_free() && self::is_first_30();
	}

	/**
	 * @since 6.17
	 *
	 * @return bool
	 */
	private static function is_first_30() {
		$activation_timestamp = get_option( 'frm_first_activation' );
		if ( false === $activation_timestamp ) {
			// If the option does not exist, assume that it is
			// because the user was active before this option was introduced.
			return false;
		}
		$cutoff = strtotime( '-30 days' );
		return $activation_timestamp > $cutoff;
	}

	/**
	 * @since 6.17
	 *
	 * @return bool
	 */
	private static function is_free_not_first_30() {
		return self::is_free() && ! self::is_first_30();
	}

	/**
	 * Check if the Pro plugin is active. If not, consider the user to be on the free version.
	 *
	 * @since 6.17
	 *
	 * @return bool
	 */
	private static function is_free() {
		return ! FrmAppHelper::pro_is_included();
	}
}
