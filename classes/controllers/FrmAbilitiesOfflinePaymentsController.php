<?php
/**
 * Record offline transactions without calling a payment gateway.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAbilitiesOfflinePaymentsController {
	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		$properties = array(
			'entry_id'  => array(
				'type'    => 'integer',
				'minimum' => 1,
			),
			'amount'    => array(
				'type'    => 'number',
				'minimum' => 0.01,
			),
			'currency'  => array(
				'type'    => 'string',
				'pattern' => '^[A-Z]{3}$',
				'enum'    => array_keys( FrmCurrencyHelper::get_currencies() ),
				'default' => 'USD',
			),
			'reference' => array(
				'type'      => 'string',
				'minLength' => 1,
			),
			'test'      => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'status'    => array(
				'type'    => 'string',
				'enum'    => array( 'pending', 'complete', 'failed', 'refunded' ),
				'default' => 'complete',
			),
		);
		$caps       = array( 'manage_options', 'frm_edit_entries' );
		FrmSolutionAbilitiesHelper::register(
			'record-offline-payment',
			__( 'Record an offline payment without contacting a gateway', 'formidable' ),
			array(
				'properties' => $properties,
				'required'   => array( 'entry_id', 'amount', 'reference' ),
			),
			array( self::class, 'payment' ),
			$caps
		);
		$properties['status']         = array(
			'type'    => 'string',
			'enum'    => array( 'pending', 'active', 'cancelled' ),
			'default' => 'active',
		);
		$properties['interval_count'] = array(
			'type'    => 'integer',
			'minimum' => 1,
			'default' => 1,
		);
		$properties['time_interval']  = array(
			'type'    => 'string',
			'enum'    => array( 'days', 'weeks', 'months', 'years' ),
			'default' => 'months',
		);
		$properties['next_bill_date'] = array(
			'type'   => 'string',
			'format' => 'date',
		);
		FrmSolutionAbilitiesHelper::register(
			'record-offline-subscription',
			__( 'Record an offline subscription without contacting a gateway', 'formidable' ),
			array(
				'properties' => $properties,
				'required'   => array( 'entry_id', 'amount', 'reference' ),
			),
			array( self::class, 'subscription' ),
			$caps
		);
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Offline transaction details.
	 *
	 * @return array|WP_Error
	 */
	public static function payment( $input ) {
		$values = self::values( $input );

		if ( is_wp_error( $values ) ) {
			return $values;
		}

		$values['receipt_id'] = sanitize_text_field( $input['reference'] );
		$model                = new FrmTransLitePayment();
		$id                   = $model->create( $values );
		return $id ? array(
			'id'     => $id,
			'paysys' => 'manual',
		) : new WP_Error( 'frm_offline_payment', __( 'The offline payment could not be recorded.', 'formidable' ) );
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Offline subscription details.
	 *
	 * @return array|WP_Error
	 */
	public static function subscription( $input ) {
		$values = self::values( $input );

		if ( is_wp_error( $values ) ) {
			return $values;
		}

		$values['sub_id']         = sanitize_text_field( $input['reference'] );
		$values['interval_count'] = absint( $input['interval_count'] );
		$values['time_interval']  = $input['time_interval'];
		$values['next_bill_date'] = $input['next_bill_date'] ?? '0000-00-00';
		$model                    = new FrmTransLiteSubscription();
		$id                       = $model->create( $values );
		return $id ? array(
			'id'     => $id,
			'paysys' => 'manual',
		) : new WP_Error( 'frm_offline_subscription', __( 'The offline subscription could not be recorded.', 'formidable' ) );
	}

	/**
	 * @since x.x
	 *
	 * @param array $input Offline record values.
	 *
	 * @return array|WP_Error
	 */
	private static function values( $input ) {
		$entry = FrmEntry::getOne( $input['entry_id'] );

		if ( ! $entry || (int) $entry->is_draft ) {
			return new WP_Error( 'frm_offline_entry', __( 'Choose an existing submitted entry.', 'formidable' ) );
		}

		return array(
			'item_id'    => (int) $entry->id,
			'amount'     => $input['amount'],
			'status'     => $input['status'],
			'paysys'     => 'manual',
			'test'       => ! empty( $input['test'] ) ? 1 : 0,
			'meta_value' => array( 'currency' => $input['currency'] ),
		);
	}
}
