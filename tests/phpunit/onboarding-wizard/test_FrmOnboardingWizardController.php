<?php

/**
 * @group onboarding-wizard
 */
class test_FrmOnboardingWizardController extends FrmUnitTest {
	private $controller;

	public function setUp(): void {
		parent::setUp();

		// Create admin, editor, and subscriber users.
		$this->create_users();

		// Assign the FrmOnboardingWizardController class to a property for easier reference in tests.
		$this->controller = 'FrmOnboardingWizardController';
	}

	/**
	 * @covers FrmOnboardingWizardController::do_admin_redirects
	 */
	public function test_do_admin_redirects() {
		// Set the initial condition by setting the expected transient.
		set_transient( $this->controller::TRANSIENT_NAME, $this->controller::TRANSIENT_VALUE, 60 );

		// Case 1: Ensure no redirect action is taken when already on the Onboarding Wizard page.
		$_GET['page'] = $this->controller::PAGE_SLUG;
		$this->controller::do_admin_redirects();
		$this->assertNotEquals( 'no', get_transient( $this->controller::TRANSIENT_NAME ), 'No change to the transient is expected when already on the Onboarding Wizard page.' );
		// Reset for the next case.
		unset( $_GET['page'] );

		// Case 2: Ensure no redirect action is taken when onboarding has been previously skipped.
		update_option( $this->controller::ONBOARDING_SKIPPED_OPTION, true );
		$this->controller::do_admin_redirects();
		$this->assertNotEquals( 'no', get_transient( $this->controller::TRANSIENT_NAME ), 'No change to the transient is expected when onboarding has been skipped.' );
		// Reset for the next case.
		delete_option( $this->controller::ONBOARDING_SKIPPED_OPTION );

		// Case 3: Validate the redirect logic when conditions are met for showing the onboarding.
		add_filter( 'wp_redirect', '__return_false' ); // Bypasses redirect and exit for uninterrupted PHPUnit execution.
		$_GET['page'] = 'formidable';
		$this->controller::do_admin_redirects();
		$this->assertEquals( 'no', get_transient( $this->controller::TRANSIENT_NAME ), 'Transient should be set to "no" to indicate a redirect to the Onboarding Wizard is expected.' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		// Reset for the next case.
		unset( $_GET['page'] );

		// Clean up.
		delete_transient( $this->controller::TRANSIENT_NAME );
	}

	/**
	 * @covers FrmOnboardingWizardController::menu
	 */
	public function test_menu() {
		// Simulate the is_onboarding_wizard_page method as true.
		$this->set_admin_screen();
		$_GET['page'] = $this->controller::PAGE_SLUG;

		$this->controller::maybe_load_page();

		$this->assertEquals( 99, has_action( 'admin_menu', $this->controller . '::menu' ) );
	}

	/**
	 * @covers FrmOnboardingWizardController::remove_menu
	 */
	public function test_remove_menu() {
		global $submenu;

		// Set up the initial submenu state.
		$submenu['formidable'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			'Onboarding Wizard',
			$this->controller::REQUIRED_CAPABILITY,
			$this->controller::PAGE_SLUG,
			'Onboarding Wizard',
		);

		// Ensure the submenu is added correctly.
		$this->assertNotEmpty( $submenu['formidable'], 'The submenu should be populated before removal.' );

		$this->controller::remove_menu();

		// Check the submenu has been removed
		$this->assertArrayHasKey( 'formidable', $submenu, 'The formidable key should still exist in $submenu.' );
		$this->assertEmpty( $submenu['formidable'], 'The formidable submenu should be empty after removal.' );
	}

	/**
	 * @covers FrmFormTemplatesController::enqueue_assets
	 */
	public function test_enqueue_assets() {
		// Case 1: Not on the Onboarding Wizard page.
		$this->set_admin_screen();
		$_GET['page'] = 'some-other-page';
		$this->assertFalse(
			has_action( 'admin_enqueue_scripts', $this->controller . '::enqueue_assets' ),
			'The enqueue_assets method should be hooked to admin_enqueue_scripts with priority 15.'
		);

		// Case 2: On the Onboarding Wizard page.
		$_GET['page'] = $this->controller::PAGE_SLUG;
		$this->controller::enqueue_assets();
		// Assert that the specific scripts and styles for the Onboarding Wizard page are enqueued.
		$this->assertTrue( wp_script_is( $this->controller::SCRIPT_HANDLE, 'enqueued' ), 'Script should be enqueued on the Onboarding Wizard page.' );
		$this->assertTrue( wp_style_is( $this->controller::SCRIPT_HANDLE, 'enqueued' ), 'Style should be enqueued on the Onboarding Wizard page.' );
	}
}
