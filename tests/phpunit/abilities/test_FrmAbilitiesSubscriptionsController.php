<?php

/**
 * Behavior tests for the subscription abilities: list/get/delete/cancel
 * through the registered abilities, the form filter, and the permission
 * boundaries FrmAbilitiesSubscriptionsController enforces.
 */
class test_FrmAbilitiesSubscriptionsController extends FrmUnitTest {

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
	private function create_subscription( $overrides = array() ) {
		$entry  = $this->factory->entry->create_and_get( array( 'form_id' => $this->form->id ) );
		$values = array_merge(
			array(
				'item_id' => $entry->id,
				'amount'  => '19.99',
				'status'  => 'active',
				'paysys'  => 'manual',
			),
			$overrides
		);

		$id = ( new FrmTransLiteSubscription() )->create( $values );

		return array(
			'id'    => $id,
			'entry' => $entry,
		);
	}

	/**
	 * @return void
	 */
	public function test_a_subscription_round_trips_through_list_get_delete() {
		$created = $this->create_subscription( array( 'status' => 'active' ) );
		$listed  = $this->execute( 'list-subscriptions', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $created['id'], $listed, 'The created subscription should be in the listing.' );
		$this->assertSame( 'active', $listed[ $created['id'] ]['status'] );

		$fetched = $this->execute( 'get-subscription', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $fetched );
		$this->assertSame( $created['id'], $fetched['id'] );
		$this->assertSame( (int) $created['entry']->id, $fetched['item_id'] );

		$deleted = $this->execute( 'delete-subscription', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $deleted );
		$this->assertSame( $created['id'], $deleted['id'] );

		$after = $this->execute( 'get-subscription', array( 'id' => $created['id'] ) );
		$this->assertWPError( $after, 'A deleted subscription should no longer be found.' );
		$this->assertSame( 404, $after->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_list_subscriptions_filters_by_form() {
		$other_form    = $this->factory->form->create_and_get();
		$on_this_form  = $this->create_subscription();
		$on_other_form = $this->create_subscription(
			array( 'item_id' => $this->factory->entry->create( array( 'form_id' => $other_form->id ) ) )
		);

		$listed = $this->execute( 'list-subscriptions', array( 'form_id' => $this->form->id ) );

		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $on_this_form['id'], $listed );
		$this->assertArrayNotHasKey( $on_other_form['id'], $listed, 'A subscription on a different form should not be in a form-filtered listing.' );
	}

	/**
	 * @return void
	 */
	public function test_get_subscription_requires_an_id() {
		$ability = wp_get_ability( 'formidable-forms/get-subscription' );
		$result  = $ability->execute( array() );

		$this->assertWPError( $result, 'get-subscription should refuse a request with no id, through the ability schema\'s own required check.' );
	}

	/**
	 * @return void
	 */
	public function test_get_subscription_reports_not_found_for_a_missing_subscription() {
		$result = $this->execute( 'get-subscription', array( 'id' => 99999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * A subscription recorded against an unsupported/manual gateway has
	 * nothing to dispatch a cancelation to, so cancel-subscription should fail
	 * rather than silently marking it canceled.
	 *
	 * @return void
	 */
	public function test_cancel_subscription_fails_for_an_unsupported_gateway() {
		$created = $this->create_subscription( array( 'paysys' => 'manual' ) );
		$result  = $this->execute( 'cancel-subscription', array( 'id' => $created['id'] ) );

		$this->assertWPError( $result, 'cancel-subscription should fail when the gateway is not one that supports cancelation.' );

		$after = $this->execute( 'get-subscription', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $after );
		$this->assertSame( 'active', $after['status'], 'A failed cancelation should leave the stored status unchanged.' );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_entries_edit_entries_and_administrator_capabilities() {
		$subscription_for_get    = $this->create_subscription();
		$subscription_for_cancel = $this->create_subscription( array( 'paysys' => 'manual' ) );
		$subscription_for_delete = $this->create_subscription();

		$cases = array(
			'list-subscriptions'  => array( 'frm_view_entries', array( 'form_id' => $this->form->id ) ),
			'get-subscription'    => array( 'frm_view_entries', array( 'id' => $subscription_for_get['id'] ) ),
			'cancel-subscription' => array( 'frm_edit_entries', array( 'id' => $subscription_for_cancel['id'] ) ),
			'delete-subscription' => array( 'administrator', array( 'id' => $subscription_for_delete['id'] ) ),
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
