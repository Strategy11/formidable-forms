<?php

/**
 * @group stripe
 */
class test_FrmTransLiteSubscription extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		( new FrmTransLiteDb() )->upgrade();
	}

	/**
	 * Creates a subscription row and returns its id.
	 *
	 * @param array $values Values to override the defaults.
	 *
	 * @return int
	 */
	private function create_subscription( $values = array() ) {
		$subscription = new FrmTransLiteSubscription();
		$defaults     = array(
			'sub_id'         => 'sub_test',
			'item_id'        => 1,
			'action_id'      => 1,
			'amount'         => 10.00,
			'first_amount'   => 10.00,
			'interval_count' => 1,
			'time_interval'  => 'month',
			'fail_count'     => 0,
			'end_count'      => 9999,
			'next_bill_date' => gmdate( 'Y-m-d', strtotime( '-2 days' ) ),
			'status'         => 'active',
			'paysys'         => 'stripe',
			'created_at'     => gmdate( 'Y-m-d H:i:s' ),
		);

		return $subscription->create( array_merge( $defaults, $values ) );
	}

	/**
	 * @covers FrmTransLiteSubscription::get_overdue_subscriptions
	 */
	public function test_get_overdue_subscriptions() {
		$overdue_active_id = $this->create_subscription();
		$overdue_cancel_id = $this->create_subscription( array( 'status' => 'future_cancel' ) );
		$failed_id         = $this->create_subscription( array( 'fail_count' => 3 ) );
		$future_id         = $this->create_subscription( array( 'next_bill_date' => gmdate( 'Y-m-d', strtotime( '+2 days' ) ) ) );
		$canceled_id       = $this->create_subscription( array( 'status' => 'canceled' ) );

		$this->assertGreaterThan( 0, $overdue_active_id );

		$subscription = new FrmTransLiteSubscription();
		$overdue_ids  = array_map( 'intval', wp_list_pluck( $subscription->get_overdue_subscriptions(), 'id' ) );

		$this->assertContains( $overdue_active_id, $overdue_ids );
		$this->assertContains( $overdue_cancel_id, $overdue_ids );
		$this->assertNotContains( $failed_id, $overdue_ids );
		$this->assertNotContains( $future_id, $overdue_ids );
		$this->assertNotContains( $canceled_id, $overdue_ids );
	}

	/**
	 * @covers FrmTransLiteDb::get_one
	 * @covers FrmTransLiteDb::get_one_by
	 * @covers FrmTransLiteDb::get_all_by
	 * @covers FrmTransLiteDb::get_count
	 * @covers FrmTransLiteDb::update
	 * @covers FrmTransLiteDb::destroy
	 */
	public function test_subscription_crud() {
		$subscription = new FrmTransLiteSubscription();
		$id           = $this->create_subscription(
			array(
				'sub_id'  => 'sub_crud',
				'item_id' => 42,
			)
		);

		$this->assertGreaterThan( 0, $id );

		$row = $subscription->get_one( $id );
		$this->assertSame( 'sub_crud', $row->sub_id );
		$this->assertSame( 'active', $row->status );

		$row = $subscription->get_one_by( 'sub_crud', 'sub_id' );
		$this->assertEquals( $id, $row->id );

		$rows = $subscription->get_all_by( 42, 'item_id' );
		$this->assertCount( 1, $rows );
		$this->assertEquals( $id, $rows[0]->id );

		$this->assertGreaterThanOrEqual( 1, (int) $subscription->get_count() );

		$subscription->update( $id, array( 'status' => 'canceled' ) );
		$this->assertSame( 'canceled', $subscription->get_one( $id )->status );

		$this->set_user_by_role( 'administrator' );
		$subscription->destroy( $id );
		$this->assertNull( $subscription->get_one( $id ) );
	}

	/**
	 * @covers FrmTransLiteDb::get_all_for_user
	 */
	public function test_get_all_for_user() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$form         = $this->factory->form->create_and_get();
		$entry        = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$sub_id       = $this->create_subscription( array( 'item_id' => $entry->id ) );
		$subscription = new FrmTransLiteSubscription();
		$rows         = $subscription->get_all_for_user( $entry->user_id );

		$this->assertContains( $sub_id, array_map( 'intval', wp_list_pluck( $rows, 'id' ) ) );
	}
}
