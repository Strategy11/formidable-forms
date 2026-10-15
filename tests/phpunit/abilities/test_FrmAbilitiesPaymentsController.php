<?php

/**
 * Behavior tests for the payment abilities: list/get/delete/refund through the
 * registered abilities, the form filter, and the permission boundaries
 * FrmAbilitiesPaymentsController enforces.
 */
class test_FrmAbilitiesPaymentsController extends FrmUnitTest {

	/**
	 * @var stdClass
	 */
	private $form;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'wp_get_abilities' ) ) {
			$this->markTestSkipped( 'The Abilities API is not available in this WordPress install.' );
		}

		( new FrmTransLiteDb() )->upgrade();

		$this->set_current_user_to_1();
		$this->enable_abilities();
		$this->form = $this->factory->form->create_and_get();
	}

	/**
	 * @return void
	 */
	private function enable_abilities() {
		if ( ! class_exists( 'WP_Abilities_Registry' ) || ! is_callable( 'FrmMcpController::reset' ) ) {
			$this->markTestSkipped( 'This install has no abilities registry to register into.' );
		}

		$frm_settings      = FrmAppHelper::get_settings();
		$frm_settings->mcp = 1;
		$frm_settings->store();
		FrmMcpController::reset();

		WP_Ability_Categories_Registry::get_instance();
		WP_Abilities_Registry::get_instance();

		remove_all_actions( 'wp_abilities_api_categories_init' );
		remove_all_actions( 'wp_abilities_api_init' );

		add_action( 'wp_abilities_api_categories_init', array( 'FrmAbilitiesController', 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( 'FrmAbilitiesController', 'register_abilities' ) );

		do_action( 'wp_abilities_api_categories_init' );
		do_action( 'wp_abilities_api_init' );
	}

	/**
	 * @param string $slug  Ability slug, without the formidable-forms prefix.
	 * @param array  $input Ability input parameters.
	 *
	 * @return mixed
	 */
	private function execute( $slug, $input = array() ) {
		$ability = wp_get_ability( 'formidable-forms/' . $slug );

		$this->assertInstanceOf( \WP_Ability::class, $ability, 'The ' . $slug . ' ability should be registered.' );

		return $ability->execute( $input );
	}

	/**
	 * @param array $overrides
	 *
	 * @return array{id: int, entry: stdClass}
	 */
	private function create_payment( $overrides = array() ) {
		$entry  = $this->factory->entry->create_and_get( array( 'form_id' => $this->form->id ) );
		$values = array_merge(
			array(
				'item_id' => $entry->id,
				'amount'  => '9.99',
				'status'  => 'complete',
				'paysys'  => 'manual',
			),
			$overrides
		);

		$id = ( new FrmTransLitePayment() )->create( $values );

		return array(
			'id'    => $id,
			'entry' => $entry,
		);
	}

	/**
	 * @return void
	 */
	public function test_a_payment_round_trips_through_list_get_delete() {
		$created = $this->create_payment( array( 'status' => 'complete' ) );
		$listed  = $this->execute( 'list-payments', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $created['id'], $listed, 'The created payment should be in the listing.' );
		$this->assertSame( 'complete', $listed[ $created['id'] ]['status'] );

		$fetched = $this->execute( 'get-payment', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $fetched );
		$this->assertSame( $created['id'], $fetched['id'] );
		$this->assertSame( (int) $created['entry']->id, $fetched['item_id'] );

		$deleted = $this->execute( 'delete-payment', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $deleted );
		$this->assertSame( $created['id'], $deleted['id'] );

		$after = $this->execute( 'get-payment', array( 'id' => $created['id'] ) );
		$this->assertWPError( $after, 'A deleted payment should no longer be found.' );
		$this->assertSame( 404, $after->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_list_payments_filters_by_form() {
		$other_form    = $this->factory->form->create_and_get();
		$on_this_form  = $this->create_payment();
		$on_other_form = $this->create_payment(
			array( 'item_id' => $this->factory->entry->create( array( 'form_id' => $other_form->id ) ) )
		);

		$listed = $this->execute( 'list-payments', array( 'form_id' => $this->form->id ) );

		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $on_this_form['id'], $listed );
		$this->assertArrayNotHasKey( $on_other_form['id'], $listed, 'A payment on a different form should not be in a form-filtered listing.' );
	}

	/**
	 * @return void
	 */
	public function test_get_payment_requires_an_id() {
		$ability = wp_get_ability( 'formidable-forms/get-payment' );
		$result  = $ability->execute( array() );

		$this->assertWPError( $result, 'get-payment should refuse a request with no id, through the ability schema\'s own required check.' );
	}

	/**
	 * @return void
	 */
	public function test_get_payment_reports_not_found_for_a_missing_payment() {
		$result = $this->execute( 'get-payment', array( 'id' => 99999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * A payment recorded against an unsupported/manual gateway has nothing to
	 * dispatch a refund to, so refund-payment should fail rather than silently
	 * mark it refunded.
	 *
	 * @return void
	 */
	public function test_refund_payment_fails_for_an_unsupported_gateway() {
		$created = $this->create_payment( array( 'paysys' => 'manual' ) );
		$result  = $this->execute( 'refund-payment', array( 'id' => $created['id'] ) );

		$this->assertWPError( $result, 'refund-payment should fail when the payment gateway is not one that supports refunds.' );

		$after = $this->execute( 'get-payment', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $after );
		$this->assertSame( 'complete', $after['status'], 'A failed refund should leave the stored status unchanged.' );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_entries_edit_entries_and_administrator_capabilities() {
		$payment_for_get    = $this->create_payment();
		$payment_for_refund = $this->create_payment( array( 'paysys' => 'manual' ) );
		$payment_for_delete = $this->create_payment();

		$cases = array(
			'list-payments'  => array( 'frm_view_entries', array( 'form_id' => $this->form->id ) ),
			'get-payment'    => array( 'frm_view_entries', array( 'id' => $payment_for_get['id'] ) ),
			'refund-payment' => array( 'frm_edit_entries', array( 'id' => $payment_for_refund['id'] ) ),
			'delete-payment' => array( 'administrator', array( 'id' => $payment_for_delete['id'] ) ),
		);

		foreach ( $cases as $slug => list( $capability, $input ) ) {
			$subscriber_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
			wp_set_current_user( $subscriber_id );

			$ability = wp_get_ability( 'formidable-forms/' . $slug );
			$denied  = $ability->check_permissions( $input );
			$denied  = is_wp_error( $denied ) ? false : $denied;
			$this->assertFalse( $denied, $slug . ' should refuse a subscriber with no ' . $capability . ' capability.' );

			$subscriber = get_user_by( 'id', $subscriber_id );
			$subscriber->add_cap( $capability );
			wp_set_current_user( 0 );
			wp_set_current_user( $subscriber_id );

			$allowed = $ability->check_permissions( $input );
			$this->assertNotWPError( $allowed, $slug . ' should not error once ' . $capability . ' is granted.' );
			$this->assertTrue( $allowed, $slug . ' should allow a non-admin who holds ' . $capability . '.' );
		}

		$this->set_current_user_to_1();
	}
}
