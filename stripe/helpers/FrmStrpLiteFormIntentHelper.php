<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Identify Stripe Lite intents and delay them until the payment field is visible.
 *
 * @since x.x
 */
class FrmStrpLiteFormIntentHelper {
	/**
	 * @var string
	 */
	const PAYMENT_TYPE = 'payment';
	/**
	 * @var string
	 */
	const SETUP_TYPE = 'setup';
	/**
	 * @var string
	 */
	const SETUP_PREFIX = 'seti_';

	/**
	 * @var array Payment fields cached by form id for the current request.
	 */
	private static $payment_fields_cache = array();

	/**
	 * Wait until the page containing the payment field has been reached.
	 *
	 * @since x.x
	 *
	 * @param WP_Post $action The Stripe payment action.
	 *
	 * @return bool
	 */
	public static function requires_intent_on_load( $action ) {
		return self::payment_field_on_current_page( $action->menu_order );
	}

	/**
	 * Check if the payment field for a form is on the page currently being shown.
	 *
	 * A multi-page form renders every page's fields on every page load or page turn, and only the
	 * page currently being shown is not hidden. Waiting for the payment field's own page keeps a one
	 * time payment from creating (and possibly abandoning) an intent before that page is ever seen.
	 *
	 * Payment fields are cached per form id for the current request.
	 *
	 * @since x.x
	 *
	 * @param int|string $form_id
	 *
	 * @return bool
	 */
	private static function payment_field_on_current_page( $form_id ) {
		if ( ! is_callable( 'FrmProFieldsHelper::field_on_current_page' ) ) {
			// Multi-page forms are a Pro feature, so every field is on the only page.
			return true;
		}

		if ( ! isset( self::$payment_fields_cache[ $form_id ] ) ) {
			self::$payment_fields_cache[ $form_id ] = FrmField::get_all_types_in_form( $form_id, 'credit_card' );
		}

		$payment_fields = self::$payment_fields_cache[ $form_id ];

		if ( ! $payment_fields ) {
			// There is no payment field to wait for, e.g. an action with a hard coded amount.
			return true;
		}

		foreach ( $payment_fields as $payment_field ) {
			if ( FrmProFieldsHelper::field_on_current_page( $payment_field ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Pull the intent id out of a client secret.
	 *
	 * Passing an id that is already an intent id returns it unchanged.
	 *
	 * @since x.x
	 *
	 * @param string $client_secret
	 *
	 * @return string
	 */
	public static function get_intent_id( $client_secret ) {
		return explode( '_secret_', $client_secret )[0];
	}

	/**
	 * Get the type of intent an id refers to.
	 *
	 * @since x.x
	 *
	 * @param string $intent_id An intent id or a client secret.
	 *
	 * @return string Either self::PAYMENT_TYPE or self::SETUP_TYPE.
	 */
	public static function get_intent_type_for_id( $intent_id ) {
		return str_starts_with( $intent_id, self::SETUP_PREFIX ) ? self::SETUP_TYPE : self::PAYMENT_TYPE;
	}

	/**
	 * Flatten the posted client secrets into a plain list.
	 *
	 * Repeated inputs sharing one name arrive nested one level deeper once the AJAX request has been
	 * expanded back out, so both shapes have to be read.
	 *
	 * @since x.x
	 *
	 * @param array|string $client_secrets
	 *
	 * @return string[]
	 */
	public static function flatten_client_secrets( $client_secrets ) {
		$flat = array();

		foreach ( (array) $client_secrets as $client_secret ) {
			if ( is_array( $client_secret ) ) {
				$flat = array_merge( $flat, self::flatten_client_secrets( $client_secret ) );
				continue;
			}

			if ( is_string( $client_secret ) && '' !== $client_secret ) {
				$flat[] = $client_secret;
			}
		}

		return $flat;
	}

	/**
	 * Find the client secret in the posted data that belongs to an action.
	 *
	 * Match the intent type required by the action, including nested posted values.
	 *
	 * @since x.x
	 *
	 * @param array   $client_secrets Posted client secrets, possibly nested one level deeper.
	 * @param WP_Post $action
	 *
	 * @return false|string
	 */
	public static function find_posted_client_secret( $client_secrets, $action ) {
		$needed = 'recurring' === $action->post_content['type'] ? self::SETUP_TYPE : self::PAYMENT_TYPE;

		foreach ( self::flatten_client_secrets( $client_secrets ) as $client_secret ) {
			if ( self::get_intent_type_for_id( $client_secret ) === $needed ) {
				return $client_secret;
			}
		}

		return false;
	}
}
