<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Converts a PayPal Standard (Legacy) action from the PayPal add-on into a PayPal Commerce payment action.
 *
 * The PayPal add-on saves its actions with the 'paypal' type. PayPal Commerce actions are always saved
 * with the 'payment' type and a 'paypal' gateway, so any saved 'paypal' action is a legacy action.
 *
 * @since x.x
 */
class FrmPayPalLiteLegacyMigrationHelper {

	/**
	 * The action types used by legacy PayPal actions.
	 *
	 * @var string[]
	 */
	private static $legacy_types = array( 'paypal', 'paypal-legacy' );

	/**
	 * Legacy billing period codes, mapped to the PayPal Commerce interval and the number of days in it.
	 *
	 * @var array<string, array{0: string, 1: int}>
	 */
	private static $periods = array(
		'D' => array( 'day', 1 ),
		'W' => array( 'week', 7 ),
		'M' => array( 'month', 30 ),
		'Y' => array( 'year', 365 ),
	);

	/**
	 * @since x.x
	 *
	 * @param object $form_action The form action to check.
	 *
	 * @return bool
	 */
	public static function is_legacy_action( $form_action ) {
		return is_object( $form_action ) && ! empty( $form_action->post_excerpt ) && in_array( $form_action->post_excerpt, self::$legacy_types, true );
	}

	/**
	 * Get the reason the action cannot be migrated right now.
	 *
	 * @since x.x
	 *
	 * @param int          $form_id         The ID of the form the legacy action belongs to.
	 * @param array|string $legacy_settings The legacy action settings, as an array or the saved JSON.
	 * @param int          $action_id       The ID of the legacy action being converted.
	 *
	 * @return string An empty string when the action can be migrated.
	 */
	public static function get_migration_blocker( $form_id, $legacy_settings = array(), $action_id = 0 ) {
		$connection_error = FrmTransLiteAppHelper::get_gateway_connection_error( 'paypal' );

		if ( $connection_error ) {
			return $connection_error;
		}

		if ( FrmFormAction::form_has_action_type( $form_id, 'payment' ) ) {
			return __( 'This form already has a payment action, and only one is allowed per form. Delete the existing payment action to convert this one.', 'formidable' );
		}

		if ( self::has_other_active_legacy_actions( $form_id, $action_id ) ) {
			return __( 'This form has other active legacy PayPal actions. Only one payment action is allowed per form, so turn the others off to convert this one.', 'formidable' );
		}

		return self::get_after_payment_blocker( self::get_settings_array( $legacy_settings ) );
	}

	/**
	 * Another active legacy action would keep sending entries to PayPal Standard next to the new
	 * payment action, so a single entry could be charged twice.
	 *
	 * @param int $form_id   The form ID.
	 * @param int $action_id The ID of the legacy action being converted.
	 *
	 * @return bool
	 */
	private static function has_other_active_legacy_actions( $form_id, $action_id ) {
		$legacy_actions = FrmFormAction::get_action_for_form( $form_id, 'paypal', array( 'post_status' => 'publish' ) );

		if ( ! is_array( $legacy_actions ) ) {
			return false;
		}

		unset( $legacy_actions[ $action_id ] );

		return (bool) $legacy_actions;
	}

	/**
	 * The payment action only runs After Payment rows with a Pro version that supports them,
	 * so converting without one would silently stop the field updates.
	 *
	 * @param array $legacy_settings The legacy action settings.
	 *
	 * @return string An empty string when the After Payment rows can run after converting.
	 */
	private static function get_after_payment_blocker( $legacy_settings ) {
		if ( ! self::convert_change_fields( $legacy_settings['change_field'] ?? array() ) || self::after_payment_is_supported() ) {
			return '';
		}

		if ( FrmAppHelper::pro_is_installed() ) {
			return __( 'This action changes fields after payment, which needs the latest version of Formidable Pro. Update Formidable Pro to convert this action.', 'formidable' );
		}

		return __( 'This action changes fields after payment, which needs Formidable Pro. Activate Formidable Pro to convert this action.', 'formidable' );
	}

	/**
	 * Create a PayPal Commerce payment action from a legacy PayPal action, then turn the legacy action off.
	 *
	 * @since x.x
	 *
	 * @param int $action_id The ID of the legacy PayPal action.
	 *
	 * @return array|WP_Error The new action ID and notes about settings that could not be copied exactly.
	 */
	public static function migrate( $action_id ) {
		$legacy_action = get_post( $action_id );

		if ( ! $legacy_action || FrmFormActionsController::$action_post_type !== $legacy_action->post_type || ! self::is_legacy_action( $legacy_action ) ) {
			return new WP_Error( 'frm_paypal_invalid_action', __( 'That legacy PayPal action could not be found.', 'formidable' ) );
		}

		$form_id         = $legacy_action->menu_order;
		$legacy_settings = self::get_settings_array( $legacy_action->post_content );
		$blocker         = self::get_migration_blocker( $form_id, $legacy_settings, $legacy_action->ID );

		if ( $blocker ) {
			return new WP_Error( 'frm_paypal_migration_blocked', $blocker );
		}

		$payment_control = FrmFormActionsController::get_form_actions( 'payment' );

		if ( ! is_object( $payment_control ) ) {
			return new WP_Error( 'frm_paypal_no_payment_action', __( 'The payment action is not available.', 'formidable' ) );
		}

		$notes      = array();
		$new_action = $payment_control->prepare_new( $form_id );

		$new_action->post_title   = $legacy_action->post_title;
		$new_action->post_content = array_merge( $new_action->post_content, self::convert_settings( $legacy_settings, $form_id, $notes ) );

		$action_array             = (array) $new_action;
		$new_action->post_content = apply_filters( 'frm_before_save_action', $new_action->post_content, $action_array, $action_array, array(), $payment_control );
		$new_action->post_content = apply_filters( 'frm_before_save_payment_action', $new_action->post_content, $action_array, $action_array, array(), $payment_control );

		$new_action_id = $payment_control->save_settings( $new_action );

		if ( ! $new_action_id || is_wp_error( $new_action_id ) ) {
			return new WP_Error( 'frm_paypal_migration_failed', __( 'The PayPal Commerce action could not be created. The legacy action was not changed.', 'formidable' ) );
		}

		FrmFormAction::clear_cache();
		$disabled = wp_update_post(
			array(
				'ID'          => $legacy_action->ID,
				'post_status' => 'draft',
			)
		);

		if ( ! $disabled || is_wp_error( $disabled ) ) {
			// Never leave both actions on. Each would try to collect the payment.
			wp_delete_post( $new_action_id, true );
			FrmFormAction::clear_cache();

			return new WP_Error( 'frm_paypal_migration_failed', __( 'The legacy action could not be turned off, so the PayPal Commerce action was not created.', 'formidable' ) );
		}

		if ( ! empty( $legacy_settings['paypal_stop_email'] ) ) {
			$email_note = self::hold_emails_until_payment( $form_id, $new_action->post_content['conditions'] ?? array() );

			if ( $email_note ) {
				$notes[] = $email_note;
			}
		}

		$retriggered = self::add_payment_triggers( $form_id );

		if ( $retriggered ) {
			$notes[] = sprintf(
				/* translators: %d: The number of form actions that were changed. */
				_n(
					'%d action that ran after a legacy PayPal payment now also runs after a PayPal Commerce payment.',
					'%d actions that ran after a legacy PayPal payment now also run after a PayPal Commerce payment.',
					$retriggered,
					'formidable'
				),
				$retriggered
			);
		}

		FrmFormAction::clear_cache();

		return array(
			'action_id' => $new_action_id,
			'notes'     => $notes,
		);
	}

	/**
	 * Map legacy PayPal action settings to PayPal Commerce payment action settings.
	 *
	 * @since x.x
	 *
	 * @param array    $legacy  The legacy action settings.
	 * @param int      $form_id The form ID, used for a product name fallback.
	 * @param string[] $notes   Messages about settings that could not be copied exactly, passed by reference.
	 *
	 * @return array
	 */
	public static function convert_settings( $legacy, $form_id, &$notes ) {
		$item_name = (string) ( $legacy['paypal_item_name'] ?? '' );
		$type      = (string) ( $legacy['paypal_type'] ?? '' );
		$settings  = array(
			'gateway'     => array( 'paypal' ),
			'type'        => '_xclick-subscriptions' === $type ? 'recurring' : 'single',
			'description' => $item_name,
			'amount'      => self::convert_amount( $legacy ),
			'currency'    => strtolower( self::get_legacy_currency( $legacy ) ),
			'event'       => array( 'create' ),
			'conditions'  => self::convert_conditions( $legacy['conditions'] ?? array() ),
		);

		if ( '_donations' === $type ) {
			$notes[] = __( 'The legacy Donation payment type is now a one-time payment. PayPal Commerce does not have a separate donation checkout.', 'formidable' );
		}

		if ( 'recurring' === $settings['type'] ) {
			$settings = array_merge( $settings, self::convert_recurring_settings( $legacy, $item_name, $form_id, $notes ) );
		}

		$change_fields = self::convert_change_fields( $legacy['change_field'] ?? array() );

		if ( $change_fields ) {
			// get_migration_blocker() stops the conversion when nothing can run these rows.
			$settings['change_field'] = $change_fields;
		}

		if ( ! empty( $legacy['business_email'] ) ) {
			$notes[] = sprintf(
				/* translators: %s: The PayPal email address from the legacy action. */
				__( 'Payments now go to the PayPal account connected in Global Settings, not %s.', 'formidable' ),
				$legacy['business_email']
			);
		}

		if ( ! empty( $legacy['return_url'] ) || ! empty( $legacy['cancel_url'] ) ) {
			$notes[] = __( 'Payments now happen on the form page, so the Return URL and Cancel URL are not used. Set a redirect in the confirmation.', 'formidable' );
		}

		return $settings;
	}

	/**
	 * Legacy actions use either a field or a fixed amount. PayPal Commerce uses a single amount setting
	 * that accepts field shortcodes.
	 *
	 * @param array $legacy The legacy action settings.
	 *
	 * @return string
	 */
	private static function convert_amount( $legacy ) {
		if ( ! empty( $legacy['paypal_amount_field'] ) && is_numeric( $legacy['paypal_amount_field'] ) ) {
			return '[' . absint( $legacy['paypal_amount_field'] ) . ']';
		}

		return (string) ( $legacy['paypal_amount'] ?? '' );
	}

	/**
	 * Legacy actions without their own currency use the currency from the PayPal add-on settings,
	 * then USD, like the PayPal add-on. The option is read directly so this also works while the
	 * PayPal add-on is inactive.
	 *
	 * @param array $legacy The legacy action settings.
	 *
	 * @return string
	 */
	private static function get_legacy_currency( $legacy ) {
		if ( ! empty( $legacy['currency'] ) && is_string( $legacy['currency'] ) ) {
			return $legacy['currency'];
		}

		$global_settings = get_option( 'frm_paypal_options' );

		if ( is_object( $global_settings ) && ! empty( $global_settings->currency ) && is_string( $global_settings->currency ) ) {
			return $global_settings->currency;
		}

		return 'USD';
	}

	/**
	 * Saved actions hold their settings as JSON. Actions loaded for the form settings page hold an array.
	 *
	 * @param array|string $settings The legacy action settings.
	 *
	 * @return array
	 */
	private static function get_settings_array( $settings ) {
		$settings = FrmAppHelper::maybe_json_decode( $settings );
		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Legacy "After Payment" rows use the same id, status and value keys as the payment action.
	 * The legacy statuses, Completed and Failed, both exist in the payment action, so only
	 * incomplete rows are dropped.
	 *
	 * @param array $change_fields The legacy After Payment rows, keyed by row number.
	 *
	 * @return array<int, array{id: int, status: string, value: string}>
	 */
	private static function convert_change_fields( $change_fields ) {
		if ( ! is_array( $change_fields ) ) {
			return array();
		}

		$rows = array();

		foreach ( $change_fields as $row ) {
			if ( ! is_array( $row ) || empty( $row['id'] ) || ! in_array( $row['status'] ?? '', array( 'complete', 'failed' ), true ) ) {
				continue;
			}

			$rows[] = array(
				'id'     => absint( $row['id'] ),
				'status' => $row['status'],
				'value'  => (string) ( $row['value'] ?? '' ),
			);
		}

		return $rows;
	}

	/**
	 * Pro runs the After Payment rows of the payment action. While the Stripe add-on is active,
	 * its own payment action runs them instead.
	 *
	 * @return bool
	 */
	private static function after_payment_is_supported() {
		return is_callable( 'FrmProTransLiteController::change_fields_after_payment' ) || class_exists( 'FrmTransActionsController' );
	}

	/**
	 * Very old legacy actions store conditions as parallel arrays. Convert them to the current row format.
	 * This mirrors FrmPaymentAction::migrate_values() so it also works while the PayPal add-on is inactive.
	 *
	 * @param array $conditions The legacy conditional logic settings.
	 *
	 * @return array
	 */
	private static function convert_conditions( $conditions ) {
		if ( ! is_array( $conditions ) || empty( $conditions['hide_field'] ) || ! is_array( $conditions['hide_field'] ) ) {
			return is_array( $conditions ) ? $conditions : array();
		}

		$new_conditions = array(
			'send_stop' => $conditions['send_stop'] ?? 'send',
			'any_all'   => $conditions['any_all'] ?? 'any',
		);

		foreach ( $conditions['hide_field'] as $key => $field_id ) {
			$new_conditions[] = array(
				'hide_field'      => $field_id,
				'hide_field_cond' => $conditions['hide_field_cond'][ $key ] ?? '==',
				'hide_opt'        => $conditions['hide_opt'][ $key ] ?? '',
			);
		}

		return $new_conditions;
	}

	/**
	 * @param array    $legacy    The legacy action settings.
	 * @param string   $item_name The legacy item name.
	 * @param int      $form_id   The form ID, used for a product name fallback.
	 * @param string[] $notes     Messages about settings that could not be copied exactly, passed by reference.
	 *
	 * @return array
	 */
	private static function convert_recurring_settings( $legacy, $item_name, $form_id, &$notes ) {
		$period   = self::$periods[ $legacy['repeat_time'] ?? 'M' ] ?? self::$periods['M'];
		$settings = array(
			'interval'             => $period[0],
			'interval_count'       => max( 1, (int) ( $legacy['repeat_num'] ?? 1 ) ),
			'product_name'         => $item_name,
			'product_type'         => 'SERVICE',
			'payment_limit'        => '',
			'trial_interval_count' => 0,
		);

		if ( '' === $settings['product_name'] ) {
			// PayPal Commerce requires a product name for subscriptions.
			$settings['product_name'] = FrmForm::getName( $form_id );
		}

		if ( ! empty( $legacy['trial'] ) ) {
			$trial_period                     = self::$periods[ $legacy['trial_time'] ?? 'M' ] ?? self::$periods['M'];
			$trial_days                       = max( 1, (int) ( $legacy['trial_num'] ?? 1 ) ) * $trial_period[1];
			$settings['trial_interval_count'] = $trial_days;

			if ( 'day' !== $trial_period[0] ) {
				$notes[] = sprintf(
					/* translators: %d: The number of days in the trial period. */
					__( 'PayPal Commerce trials are set in days, so the trial is now %d days.', 'formidable' ),
					$trial_days
				);
			}

			if ( (float) ( $legacy['trial_amount'] ?? 0 ) > 0 ) {
				$notes[] = __( 'PayPal Commerce trials are free, so the legacy trial amount was not copied.', 'formidable' );
			}
		}

		$notes[] = __( 'Existing legacy subscriptions stay on PayPal Standard. Keep the PayPal add-on active so their payments are still recorded.', 'formidable' );

		if ( ! empty( $legacy['retry'] ) ) {
			$notes[] = __( 'The legacy "Retry a failed payment" setting has no PayPal Commerce equivalent. PayPal applies its own retry rules to subscriptions.', 'formidable' );
		}

		return $settings;
	}

	/**
	 * The legacy "hold email notifications" setting only held emails when a payment was collected.
	 * An action with conditional logic can skip the payment, and the Successful Payment trigger never
	 * runs without one, so the emails are only moved when every entry pays.
	 *
	 * @param int   $form_id    The form ID.
	 * @param array $conditions The conditional logic settings of the new payment action.
	 *
	 * @return string A note about what happened to the email actions. Empty when there is nothing to report.
	 */
	private static function hold_emails_until_payment( $form_id, $conditions ) {
		if ( self::has_condition_rows( $conditions ) ) {
			return __( 'This action uses conditional logic, so emails still send when an entry is created. Successful Payment emails only send for paid entries.', 'formidable' );
		}

		$moved = self::move_emails_to_payment_success( $form_id );

		if ( ! $moved ) {
			return '';
		}

		return sprintf(
			/* translators: %d: The number of email actions that were changed. */
			_n(
				'To hold notifications until payment is received, %d email action now sends on a successful payment instead of when the entry is created.',
				'To hold notifications until payment is received, %d email actions now send on a successful payment instead of when the entry is created.',
				$moved,
				'formidable'
			),
			$moved
		);
	}

	/**
	 * @param array $conditions The conditional logic settings, with one array per rule.
	 *
	 * @return bool
	 */
	private static function has_condition_rows( $conditions ) {
		foreach ( $conditions as $condition ) {
			if ( is_array( $condition ) && ! empty( $condition['hide_field'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Actions triggered by the legacy "Successful PayPal payment" and "Failed PayPal payment" events
	 * get the matching payment events too. The legacy events stay, so payments for subscriptions that
	 * started with the legacy action keep running them while the PayPal add-on is active.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return int The number of form actions changed.
	 */
	private static function add_payment_triggers( $form_id ) {
		$event_map = array(
			'paypal'        => 'payment-success',
			'paypal-failed' => 'payment-failed',
		);
		$changed   = 0;

		foreach ( FrmFormAction::get_action_for_form( $form_id ) as $action ) {
			$events     = (array) ( $action->post_content['event'] ?? array() );
			$new_events = $events;

			foreach ( $event_map as $legacy_event => $event ) {
				if ( in_array( $legacy_event, $events, true ) && ! in_array( $event, $events, true ) ) {
					$new_events[] = $event;
				}
			}

			$action_control = FrmFormActionsController::get_form_actions( $action->post_excerpt );

			if ( $new_events === $events || ! is_object( $action_control ) ) {
				continue;
			}

			$action->post_content['event'] = $new_events;
			$action_control->save_settings( $action );
			++$changed;
		}

		return $changed;
	}

	/**
	 * Send held emails with the Successful Payment trigger instead of on entry creation.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return int The number of email actions changed.
	 */
	private static function move_emails_to_payment_success( $form_id ) {
		$email_control = FrmFormActionsController::get_form_actions( 'email' );

		if ( ! is_object( $email_control ) ) {
			return 0;
		}

		$moved = 0;

		foreach ( FrmFormAction::get_action_for_form( $form_id, 'email' ) as $email_action ) {
			$events = (array) ( $email_action->post_content['event'] ?? array() );

			if ( ! in_array( 'create', $events, true ) ) {
				continue;
			}

			$events = array_values( array_diff( $events, array( 'create' ) ) );

			if ( ! in_array( 'payment-success', $events, true ) ) {
				$events[] = 'payment-success';
			}

			$email_action->post_content['event'] = $events;
			$email_control->save_settings( $email_action );
			++$moved;
		}

		return $moved;
	}
}
