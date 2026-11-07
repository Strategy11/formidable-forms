<?php

/**
 * @group paypal
 *
 * @covers FrmPayPalLiteActionsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'paypal' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmPayPalLiteActionsController::class )]
class test_FrmPayPalLiteActionsController extends FrmUnitTest {

	/**
	 * @see FrmPayPalLiteActionsController::get_billing_address_type
	 */
	public function test_get_billing_address_type_reads_the_address_field_type() {
		$form_id = $this->factory->form->create();

		foreach ( array( 'us', 'international', 'europe', 'generic' ) as $address_type ) {
			$field_id = $this->factory->field->create(
				array(
					'form_id'       => $form_id,
					'type'          => 'address',
					'field_options' => array( 'address_type' => $address_type ),
				)
			);

			$this->assertSame( $address_type, $this->get_billing_address_type( $field_id ), 'The address type should be read from the mapped field.' );
		}
	}

	/**
	 * @see FrmPayPalLiteActionsController::get_billing_address_type
	 */
	public function test_get_billing_address_type_defaults_to_international() {
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $this->factory->form->create(),
				'type'    => 'address',
			)
		);

		$field_options = FrmField::getOne( $field_id )->field_options;
		unset( $field_options['address_type'] );
		FrmField::update( $field_id, array( 'field_options' => $field_options ) );

		$this->assertSame( 'international', $this->get_billing_address_type( $field_id ), 'An address field with no stored type should be treated as international.' );
	}

	/**
	 * @see FrmPayPalLiteActionsController::get_billing_address_type
	 */
	public function test_get_billing_address_type_is_empty_without_an_address_field() {
		$text_field_id = $this->factory->field->create(
			array(
				'form_id' => $this->factory->form->create(),
				'type'    => 'text',
			)
		);

		$this->assertSame( '', $this->get_billing_address_type( $text_field_id ), 'A non-address field has no address type.' );
		$this->assertSame( '', $this->get_billing_address_type( 0 ), 'An empty field id has no address type.' );
		$this->assertSame( '', $this->get_billing_address_type( $text_field_id + 1000000 ), 'A missing field has no address type.' );
	}

	/**
	 * @see FrmPayPalLiteActionsController::get_country_codes_for_js
	 */
	public function test_get_country_codes_for_js_only_returns_the_map_for_international_and_europe() {
		foreach ( array( 'international', 'europe' ) as $address_type ) {
			$codes = $this->get_country_codes_for_js( array( array( 'addressType' => $address_type ) ) );
			$this->assertSame( FrmAddressesController::get_country_codes(), $codes, 'The map should be localized for ' . $address_type . ' addresses.' );
			$this->assertNotEmpty( $codes );
		}

		$no_map = array(
			'us'         => array( array( 'addressType' => 'us' ) ),
			'generic'    => array( array( 'addressType' => 'generic' ) ),
			'no address' => array( array( 'addressType' => '' ) ),
			'no key'     => array( array( 'address' => '' ) ),
			'no actions' => array(),
		);

		foreach ( $no_map as $name => $action_settings ) {
			$this->assertSame( array(), $this->get_country_codes_for_js( $action_settings ), 'No map is needed for: ' . $name );
		}
	}

	/**
	 * One international action is enough, even when another action maps a US address.
	 *
	 * @see FrmPayPalLiteActionsController::get_country_codes_for_js
	 */
	public function test_get_country_codes_for_js_returns_the_map_when_any_action_needs_it() {
		$action_settings = array(
			array( 'addressType' => 'us' ),
			array( 'addressType' => 'europe' ),
		);

		$this->assertSame( FrmAddressesController::get_country_codes(), $this->get_country_codes_for_js( $action_settings ) );
	}

	/**
	 * @param int|string $field_id
	 *
	 * @return string
	 */
	private function get_billing_address_type( $field_id ) {
		return $this->run_private_method( array( 'FrmPayPalLiteActionsController', 'get_billing_address_type' ), array( $field_id ) );
	}

	/**
	 * @param array $action_settings
	 *
	 * @return array
	 */
	private function get_country_codes_for_js( $action_settings ) {
		return $this->run_private_method( array( 'FrmPayPalLiteActionsController', 'get_country_codes_for_js' ), array( $action_settings ) );
	}
}
