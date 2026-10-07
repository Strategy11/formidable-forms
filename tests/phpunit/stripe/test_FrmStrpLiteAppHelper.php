<?php

/**
 * @group stripe
 *
 * @covers FrmStrpLiteAppHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStrpLiteAppHelper::class )]
class test_FrmStrpLiteAppHelper extends FrmUnitTest {

	public function tearDown(): void {
		remove_all_filters( 'frm_strp_active_mode' );
		remove_all_filters( 'frm_strp_lookup_modes' );
		parent::tearDown();
	}

	/**
	 * @covers FrmStrpLiteAppHelper::active_mode
	 */
	public function test_active_mode() {
		$configured_mode = FrmStrpLiteAppHelper::get_settings()->settings->test_mode ? 'test' : 'live';
		$other_mode      = 'test' === $configured_mode ? 'live' : 'test';

		$this->assertSame( $configured_mode, FrmStrpLiteAppHelper::active_mode() );

		add_filter(
			'frm_strp_active_mode',
			function () use ( $other_mode ) {
				return $other_mode;
			}
		);
		$this->assertSame( $other_mode, FrmStrpLiteAppHelper::active_mode(), 'A filtered mode should be used.' );

		remove_all_filters( 'frm_strp_active_mode' );
		add_filter(
			'frm_strp_active_mode',
			function () {
				return 'sandbox';
			}
		);
		$this->assertSame( $configured_mode, FrmStrpLiteAppHelper::active_mode(), 'An invalid filtered mode should fall back to the configured mode.' );
	}

	/**
	 * @covers FrmStrpLiteAppHelper::get_event_lookup_modes
	 */
	public function test_get_event_lookup_modes() {
		add_filter(
			'frm_strp_active_mode',
			function () {
				return 'live';
			}
		);

		$this->assertSame( array( 'live' ), FrmStrpLiteAppHelper::get_event_lookup_modes() );

		$this->assert_lookup_modes_for_filter( array( 'live', 'test' ), array( 'live', 'test' ), 'Both modes should be polled when the filter adds one.' );
		$this->assert_lookup_modes_for_filter(
			array( 'test', 'bogus', 'test', 'live' ),
			array( 'test', 'live' ),
			'Invalid and duplicate modes should be dropped, keeping the filter order.'
		);
		$this->assert_lookup_modes_for_filter( array(), array( 'live' ), 'An empty list should fall back to the active mode.' );
		$this->assert_lookup_modes_for_filter( 'test', array( 'live' ), 'A non-array value should fall back to the active mode.' );
	}

	/**
	 * @param mixed  $filtered The value the lookup modes filter returns.
	 * @param array  $expected The expected modes.
	 * @param string $message  The assertion message.
	 *
	 * @return void
	 */
	private function assert_lookup_modes_for_filter( $filtered, $expected, $message ) {
		$callback = function () use ( $filtered ) {
			return $filtered;
		};

		add_filter( 'frm_strp_lookup_modes', $callback );
		$this->assertSame( $expected, FrmStrpLiteAppHelper::get_event_lookup_modes(), $message );
		remove_filter( 'frm_strp_lookup_modes', $callback );
	}
}
