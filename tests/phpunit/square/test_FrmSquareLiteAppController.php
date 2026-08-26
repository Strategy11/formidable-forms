<?php

/**
 * @group square
 */
class test_FrmSquareLiteAppController extends FrmUnitTest {

	/**
	 * The $_POST data in place before a test replaced it.
	 *
	 * @var array
	 */
	private $original_post = array();

	public function setUp(): void {
		parent::setUp();
		$this->original_post = $_POST;
	}

	public function tearDown(): void {
		$_POST = $this->original_post;
		parent::tearDown();
	}

	/**
	 * Square's verifyBuyer expects the amount as a decimal string in the currency's
	 * major units ("20.00" for twenty pounds). The Payments API charge wants the
	 * smallest denomination ("2000"). Sending the charge value to verifyBuyer made
	 * Square's 3DS challenge sheet show a shopper GBP 2,000.00 for a GBP 20.00 payment,
	 * which only surfaced in live, where SCA regions actually render that sheet.
	 *
	 * @covers FrmSquareLiteAppController::get_amount_value_for_verification
	 */
	public function test_get_amount_value_for_verification_returns_major_units() {
		$this->markTestIncomplete( 'Waiting on the fix in fix_square_amount_issues. On master the verification amount is still the smallest denomination, and the false entry has no ip for shortcode replacement to read.' );

		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'hidden',
			)
		);

		$action             = $this->create_square_action( $form_id, '[' . $field_id . ']', 'gbp' );
		$_POST['item_meta'] = array( $field_id => '£20.00' );
		$filter             = $this->add_field_shortcode_filter( $field_id );
		$amount             = $this->get_amount_value_for_verification( $action );
		remove_filter( 'frm_content', $filter, 10 );

		$this->assertSame( '20.00', $amount );
		$this->assertNotSame( '2000', $amount, 'The verification amount must not use the smallest denomination.' );
	}

	/**
	 * An amount typed straight into the action setting is already in major units, but it
	 * may still carry a currency symbol and a thousands separator that Square will reject.
	 *
	 * @covers FrmSquareLiteAppController::get_amount_value_for_verification
	 */
	public function test_get_amount_value_for_verification_normalizes_a_literal_amount() {
		$this->markTestIncomplete( 'Waiting on the fix in fix_square_amount_issues. On master a literal amount is passed through with its symbol and separator.' );

		$form_id = $this->factory->form->create();
		$action  = $this->create_square_action( $form_id, '£1,234.50', 'gbp' );

		$this->assertSame( '1234.50', $this->get_amount_value_for_verification( $action ) );
	}

	/**
	 * The charge and the verification amounts are deliberately in different units.
	 * Pin the charge side too, so the two never get collapsed onto one value.
	 *
	 * @covers FrmSquareLiteActionsController::prepare_amount
	 */
	public function test_charge_amount_uses_the_smallest_denomination() {
		$atts = array( 'currency' => 'gbp' );

		$this->assertSame( '2000', FrmSquareLiteActionsController::prepare_amount( '£20.00', $atts ) );
		$this->assertSame( '20.00', FrmTransLiteActionsController::prepare_amount( '£20.00', $atts ) );
	}

	/**
	 * A currency with no fractional unit uses the same value on both paths, which is why
	 * this was only ever reachable in currencies like GBP.
	 *
	 * @covers FrmSquareLiteActionsController::prepare_amount
	 */
	public function test_zero_decimal_currency_amount_matches_on_both_paths() {
		$atts = array( 'currency' => 'jpy' );

		$this->assertSame( '10', FrmSquareLiteActionsController::prepare_amount( '10', $atts ) );
		$this->assertSame( '10', FrmTransLiteActionsController::prepare_amount( '10', $atts ) );
	}

	/**
	 * An amount arrives as whatever the field or the action setting produced, so both
	 * separator conventions have to survive the trip. The charge and the verification
	 * values are asserted together to keep the pair of units visible in one place.
	 *
	 * @dataProvider amount_format_provider
	 *
	 * @covers FrmSquareLiteActionsController::prepare_amount
	 * @covers FrmTransLiteActionsController::prepare_amount
	 *
	 * @param string $currency        The three letter currency code.
	 * @param string $amount          The amount as it reaches the payment action.
	 * @param string $expected_major  The major unit string that Square verifyBuyer needs.
	 * @param string $expected_charge The smallest denomination that the Payments API needs.
	 *
	 * @return void
	 */
	public function test_amount_formats( $currency, $amount, $expected_major, $expected_charge ) {
		$atts    = array( 'currency' => $currency );
		$message = $currency . ' amount ' . $amount;

		$this->assertSame( $expected_major, FrmTransLiteActionsController::prepare_amount( $amount, $atts ), $message );
		$this->assertSame( $expected_charge, FrmSquareLiteActionsController::prepare_amount( $amount, $atts ), $message );
	}

	/**
	 * @return void
	 */
	public function amount_format_provider(): \Iterator {
		// Comma thousands with a dot decimal, the GBP and USD convention.
		yield 'GBP with no separators' => array( 'gbp', '20', '20.00', '2000' );
		yield 'GBP with a symbol' => array( 'gbp', '£20.00', '20.00', '2000' );
		yield 'GBP under a pound' => array( 'gbp', '0.99', '0.99', '099' );
		yield 'GBP with a thousands comma' => array( 'gbp', '£1,234.50', '1234.50', '123450' );
		yield 'GBP thousands and no decimal' => array( 'gbp', '£1,234', '1234.00', '123400' );
		yield 'GBP with several thousands groups' => array( 'gbp', '£12,345,678.90', '12345678.90', '1234567890' );
		yield 'USD with a thousands comma' => array( 'usd', '$1,234.50', '1234.50', '123450' );

		// Dot thousands with a comma decimal, the EUR and BRL convention.
		yield 'EUR with a comma decimal' => array( 'eur', '€20,00', '20.00', '2000' );
		yield 'EUR with a thousands dot' => array( 'eur', '€1.234,50', '1234.50', '123450' );
		yield 'EUR with several thousands groups' => array( 'eur', '€12.345.678,90', '12345678.90', '1234567890' );
		yield 'EUR with a single decimal digit' => array( 'eur', '1234,5', '1234.50', '123450' );
		yield 'BRL with a thousands dot' => array( 'brl', 'R$1.234,50', '1234.50', '123450' );

		// A dot in a comma decimal currency is ambiguous. One or two trailing digits are
		// read as a decimal, three are read as thousands. See maybe_use_decimal.
		yield 'EUR dot with two digits is a decimal' => array( 'eur', '€20.00', '20.00', '2000' );
		yield 'EUR dot with one digit is a decimal' => array( 'eur', '€1.5', '1.50', '150' );
		yield 'EUR dot with three digits is thousands' => array( 'eur', '€1.234', '1234.00', '123400' );
		yield 'EUR repeated dots are thousands' => array( 'eur', '€1.234.567', '1234567.00', '123456700' );

		// The mirror image. A comma in a dot decimal currency is always thousands, so
		// a shopper typing a European style amount into a GBP form is read as 123 pounds.
		yield 'GBP comma is never a decimal' => array( 'gbp', '1,23', '123.00', '12300' );

		// A currency with no fractional unit keeps the two paths identical, and rounds.
		yield 'JPY with a thousands comma' => array( 'jpy', '1,234', '1234', '1234' );
		yield 'JPY rounds away a decimal' => array( 'jpy', '1234.56', '1235', '1235' );

		// A shortcode that nothing resolved must not reach the gateway as a field id.
		yield 'an unresolved shortcode' => array( 'gbp', '[334]', '0.00', '000' );
		yield 'an empty amount' => array( 'gbp', '', '0.00', '000' );
	}

	/**
	 * CZK and SEK separate thousands with a space, and get_amount_from_string matches on
	 * [0-9,.] only. A space splits "1 234,50" into two matches and the last one wins, so
	 * the amount silently loses its leading group and the shopper is undercharged.
	 * This is shared code, so Stripe and PayPal drop the same digits.
	 *
	 * @covers FrmTransLiteActionsController::prepare_amount
	 *
	 * @return void
	 */
	public function test_space_thousand_separator_keeps_the_leading_group() {
		$this->markTestIncomplete( 'Known bug: a space thousands separator drops every group but the last.' );

		$atts = array( 'currency' => 'czk' );

		$this->assertSame( '1234.50', FrmTransLiteActionsController::prepare_amount( '1 234,50', $atts ) );
		$this->assertSame( '123450', FrmSquareLiteActionsController::prepare_amount( '1 234,50', $atts ) );
	}

	/**
	 * The same currency parses correctly once the space is gone, which isolates the
	 * separator as the cause rather than anything to do with the comma decimal.
	 *
	 * @covers FrmTransLiteActionsController::prepare_amount
	 *
	 * @return void
	 */
	public function test_comma_decimal_without_a_thousands_space_is_correct() {
		$atts = array( 'currency' => 'czk' );

		$this->assertSame( '1234.50', FrmTransLiteActionsController::prepare_amount( '1234,50', $atts ) );
		$this->assertSame( '123450', FrmSquareLiteActionsController::prepare_amount( '1234,50', $atts ) );
	}

	/**
	 * @param int    $form_id
	 * @param string $amount   The amount setting, either a literal or a field shortcode.
	 * @param string $currency The three letter currency code.
	 *
	 * @return object
	 */
	private function create_square_action( $form_id, $amount, $currency ) {
		$this->factory->post->create(
			array(
				// wp_insert_post expects slashed data. Without this the backslash in a
				// \u00a3 escape is stripped and the amount decodes as u00a31,234.50.
				'post_content' => wp_slash(
					wp_json_encode(
						array(
							'gateway'  => array( 'square' ),
							'type'     => 'single',
							'amount'   => $amount,
							'currency' => $currency,
						)
					)
				),
				'menu_order'   => $form_id,
				'post_type'    => 'frm_form_actions',
				'post_status'  => 'publish',
				'post_excerpt' => 'payment',
			)
		);

		$actions = FrmTransLiteActionsController::get_actions_for_form( $form_id );

		return reset( $actions );
	}

	/**
	 * Pro replaces field shortcodes on the frm_content filter and Lite has no equivalent,
	 * so stand in for it here to get a resolved amount through to prepare_amount.
	 *
	 * @param int $field_id The field the amount shortcode points at.
	 *
	 * @return callable The filter that was added, to pass back to remove_filter.
	 */
	private function add_field_shortcode_filter( $field_id ) {
		$filter = function ( $value, $form, $entry ) use ( $field_id ) {
			return str_replace( '[' . $field_id . ']', $entry->metas[ $field_id ], $value );
		};

		add_filter( 'frm_content', $filter, 10, 3 );

		return $filter;
	}

	/**
	 * @param object $action
	 *
	 * @return string
	 */
	private function get_amount_value_for_verification( $action ) {
		return $this->run_private_method( array( 'FrmSquareLiteAppController', 'get_amount_value_for_verification' ), array( $action ) );
	}
}
