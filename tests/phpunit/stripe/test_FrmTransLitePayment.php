<?php

/**
 * @group stripe
 *
 * @covers FrmTransLitePayment
 */
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmTransLitePayment::class )]
class test_FrmTransLitePayment extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		( new FrmTransLiteDb() )->upgrade();
	}

	public function tearDown(): void {
		remove_all_actions( 'frm_after_create_payment' );
		parent::tearDown();
	}

	/**
	 * @covers FrmTransLitePayment::create
	 */
	public function test_create_fires_after_create_payment() {
		$calls = array();
		add_action(
			'frm_after_create_payment',
			function ( $payment_id, $values ) use ( &$calls ) {
				$calls[] = compact( 'payment_id', 'values' );
			},
			10,
			2
		);

		$values     = $this->get_payment_values();
		$payment_id = ( new FrmTransLitePayment() )->create( $values );

		$this->assertGreaterThan( 0, $payment_id );
		$this->assertCount( 1, $calls );
		$this->assertSame( $payment_id, $calls[0]['payment_id'] );
		$this->assertSame( $values, $calls[0]['values'], 'The values passed to create() should be passed to the hook unchanged.' );
	}

	/**
	 * @covers FrmTransLitePayment::create
	 */
	public function test_create_does_not_fire_after_failed_insert() {
		$fired = false;
		add_action(
			'frm_after_create_payment',
			function () use ( &$fired ) {
				$fired = true;
			}
		);

		// An empty query makes wpdb skip the insert and reset insert_id to 0.
		$block_payment_insert = function ( $query ) {
			return str_starts_with( $query, 'INSERT INTO' ) && str_contains( $query, 'frm_payments' ) ? '' : $query;
		};
		add_filter( 'query', $block_payment_insert );
		$payment_id = ( new FrmTransLitePayment() )->create( $this->get_payment_values() );
		remove_filter( 'query', $block_payment_insert );

		$this->assertSame( 0, $payment_id );
		$this->assertFalse( $fired, 'The hook should not fire when no payment was stored.' );
	}

	/**
	 * @return array
	 */
	private function get_payment_values() {
		return array(
			'receipt_id' => 'pi_test',
			'item_id'    => 1,
			'action_id'  => 1,
			'amount'     => '10.00',
			'status'     => 'complete',
			'paysys'     => 'stripe',
			'test'       => 1,
		);
	}
}
