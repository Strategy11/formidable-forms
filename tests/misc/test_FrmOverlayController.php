<?php

class test_FrmOverlayController extends FrmUnitTest {

	private $time_mock;

	public function setUp(): void {
		parent::setUp();
		$this->time_mock = $this->getMockBuilder( 'FrmOverlayController' )
			->setMethods( array( 'time', 'strtotime', 'date' ) )
			->getMock();
	}

	public function test_open_overlay() {

		$test_data                                   = array(
			'buttons'    => array(
				array(
					'label'  => __( 'Learn More', 'formidable' ),
					'target' => '_blank',
					'url'    => FrmAppHelper::admin_upgrade_link( 'nulled-full', 'formidable-forms-pro-nulled/' ),
				),
				array(
					'label'  => __( 'Get 50% off!', 'formidable' ),
					'target' => '_blank',
					'url'    => FrmAppHelper::admin_upgrade_link( 'nulled-full' ),
				),
			),
			'copy'       => 'Overlay copy test',
			'heading'    => 'Overlay Heading Test',
			'hero_image' => FrmAppHelper::plugin_url() . '/images/overlay/lock.svg',
		);
		$recurring_execution_interval                = '1 week';
		$overlay_controller                          = new FrmOverlayController();
		$overlay_recurring_execution_controller_mock = $this->getMockBuilder( 'FrmOverlayController' )
			->setMethods( array( 'get_time' ) )
			->setConstructorArgs(
				array(
					array(
						'config-option-name'  => 'unit-test-recurring-exec-1week',
						'execution-frequency' => $recurring_execution_interval,
					),
				)
			)->getMock();

		$this->assertNotFalse( $overlay_controller->open_overlay( $test_data ), 'The overlay controller doesn\'t open' );
		$this->assertNotFalse( $overlay_controller->open_overlay( $test_data ), 'The overlay controller should always open when no recurring execution is specified' );

		$get_time_mock = $overlay_recurring_execution_controller_mock->method( 'get_time' );
		$get_time_mock->willReturn( time() );
		$this->assertNotFalse( $overlay_recurring_execution_controller_mock->open_overlay( $test_data ), 'The overlay controller doesn\'t open - recurring execution' );

		$get_time_mock->willReturn( strtotime( '+3 day', time() ) );
		$this->assertFalse( $overlay_recurring_execution_controller_mock->open_overlay( $test_data ), 'The overlay controller should not execute before the recurring time interval has passed.' );

		$get_time_mock->willReturn( strtotime( $recurring_execution_interval, time() ) );
		$this->assertNotFalse( $overlay_recurring_execution_controller_mock->open_overlay( $test_data ), 'The overlay controller doesn\'t open after the recurring time interval has passed.' );
	}
}
