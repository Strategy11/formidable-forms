<?php

/**
 * @group form-templates
 */
class test_FrmFormTemplatesController extends FrmUnitTest {
	private $controller;

	public function setUp(): void {
		parent::setUp();

		// Create admin, editor, and subscriber users.
		$this->create_users();

		// Assign the FrmFormTemplatesController class to a property for easier reference in tests.
		$this->controller = 'FrmFormTemplatesController';
	}

	/**
	 * @covers FrmFormTemplatesController::menu
	 */
	public function test_menu() {
		$this->assertEquals( 14, has_action( 'admin_menu', $this->controller . '::menu' ) );
	}

	/**
	 * @covers FrmFormTemplatesController::render
	 */
	public function test_render() {
		// Prepare the necessary environment and data for the test.
		$this->run_private_method( array( $this->controller, 'init_template_resources' ) );
		$this->controller::set_form_templates_data();

		// Assertions for verifying that necessary variables are set by the render method.
		$this->assertNotEmpty( $this->controller::get_view_path(), 'View path should be set.' );
		$this->assertNotEmpty( $this->controller::get_upgrade_link(), 'Upgrade link should be set.' );
		$this->assertNotEmpty( $this->controller::get_renew_link(), 'Renew link should be set.' );
		$this->assertIsString( $this->controller::get_license_type(), 'License type should be a string.' );
		$this->assertNotEmpty( FrmAppHelper::admin_upgrade_link( 'form-templates' ), 'Pricing link should be set.' );
		$this->assertIsBool( $this->controller::is_expired(), 'Expired status should be a boolean.' );

		// Assertions for checking if template data is set correctly.
		$this->assertIsArray( $this->controller::get_templates(), 'Templates should be an array.' );
		$this->assertIsArray( $this->controller::get_favorite_templates(), 'Favorite templates should be an array.' );
		$this->assertIsArray( $this->controller::get_featured_templates(), 'Featured templates should be an array.' );
		$this->assertIsArray( $this->controller::get_custom_templates(), 'Custom templates should be an array.' );
		$this->assertIsArray( $this->controller::get_categories(), 'Categories should be an array.' );

		// Call the render method and capture the output.
		ob_start();
		$this->controller::render();
		$output = ob_get_clean();

		// Assertions for verifying specific elements in the rendered output.
		$this->assertStringContainsString( 'id="frm-form-templates-page"', $output, 'The output does not contain the page ID.' );
		$this->assertStringContainsString( 'id="frm-new-template"', $output, 'The hidden form (frm-new-template) is missing from the output.' );
		$this->assertStringContainsString( 'id="frm-page-skeleton-sidebar"', $output, 'The sidebar (frm-page-skeleton-sidebar) is missing from the output.' );
		$this->assertStringContainsString( 'id="post-body-content"', $output, 'The post body content (post-body-content) is missing from the output.' );
	}

	/**
	 * @covers FrmFormTemplatesController::render_modal
	 */
	public function test_render_modal() {
		// Simulate the is_templates_page method as true.
		$this->set_admin_screen();
		$_GET['page'] = $this->controller::PAGE_SLUG;

		// Prepare the necessary environment and data for the test.
		$this->run_private_method( array( $this->controller, 'init_template_resources' ) );
		$this->controller::set_form_templates_data();

		ob_start();
		$this->controller::render_modal();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="frm-create-template-modal"', $output );

		// Case: Free license, not expired.
		$this->set_private_property( $this->controller, 'is_expired', false );

		// Assertions for free license.
		$this->assertStringContainsString( 'id="frm-leave-email-modal"', $output );
		$this->assertStringContainsString( 'id="frm-form-upgrade-modal"', $output );
		$this->assertStringNotContainsString( 'id="frm-renew-modal"', $output );

		// Case: Expired account.
		$this->set_private_property( $this->controller, 'is_expired', true );

		ob_start();
		$this->controller::render_modal();
		$output = ob_get_clean();

		// Assertions for expired account.
		$this->assertStringContainsString( 'id="frm-renew-modal"', $output );

		// Case: Not on form templates page.
		$_GET['page'] = 'other-page';
		ob_start();
		$this->controller::render_modal();
		$output = ob_get_clean();

		// Assertions for being on a different page.
		$this->assertEmpty( $output );
	}

	/**
	 * @covers FrmFormTemplatesController::init_favorite_templates
	 */
	public function test_init_favorite_templates() {
		// Define test data for favorite templates.
		$test_favorites = array(
			array(
				'default' => array( 20872734 ),
				'custom'  => array( 51 ),
			),
			array(
				'custom' => array( 51 ),
			),
		);

		$default = array(
			'default' => array(),
			'custom'  => array(),
		);

		foreach ( $test_favorites as $test_favorite ) {
			// Update the option to include test favorite templates.
			update_option( $this->controller::FAVORITE_TEMPLATES_OPTION, $test_favorites );

			// Initialize favorite templates.
			$this->run_private_method( array( $this->controller, 'init_favorite_templates' ) );
			$favorites = $this->controller::get_favorite_templates();

			// Verify the favorite templates are correctly initialized.
			$this->assertIsArray( $favorites, 'Favorite templates should be an array.' );
			$this->assertTrue( isset( $favorites['default'] ), 'Missing default in favorites.' );

			$expected = array_merge( $test_favorite, $default );
			$this->assertEquals( $expected, $favorites, 'Favorite templates should match the example data.' );
		}
	}

	/**
	 * @covers FrmFormTemplatesController::fetch_and_format_custom_templates
	 */
	public function test_fetch_and_format_custom_templates() {
		// Prepare the necessary environment and data for the test.
		$this->run_private_method( array( $this->controller, 'init_template_resources' ) );
		$this->controller::set_form_templates_data();

		// Get the custom templates.
		$templates = $this->controller::get_custom_templates();
		// Extract 'key' column values from the array of arrays.
		$keys = array_column( $templates, 'key' );

		// Assert that 'contact-db12' is present in the keys array.
		$this->assertContains( 'contact-db12', $keys, 'The custom templates array should contain an element with key "contact-db12".' );
	}

	/**
	 * @covers FrmFormTemplatesController::organize_and_set_categories
	 */
	public function test_organize_and_set_categories() {
		// Set up the testing environment by initializing template data and categorizing them.
		$this->set_private_property( $this->controller, 'custom_templates', array() );
		$this->run_private_method( array( $this->controller, 'init_template_resources' ) );
		$this->controller::set_form_templates_data();
		$this->run_private_method( array( $this->controller, 'organize_and_set_categories' ) );

		// Get the organized categories for validation.
		$categories = $this->controller::get_categories();

		// Ensure the organized categories are structured correctly.
		$this->assertIsArray( $categories, 'Organized categories should be an array.' );
		foreach ( $categories as $slug => $category ) {
			$this->assertIsArray( $category, 'Each category should be an array.' );
			$this->assertArrayHasKey( 'name', $category, "Category '{$slug}' should have a 'name' key." );
			$this->assertArrayHasKey( 'count', $category, "Category '{$slug}' should have a 'count' key." );
		}

		// Define and validate the presence of specific categories.
		$expected_categories = array( 'favorites', 'custom', 'all-items', 'free-templates' );
		if ( 'elite' !== FrmAddonsController::license_type() ) {
			$expected_categories[] = 'available-templates';
		}

		// Check for the existence and verify the count of each expected category.
		foreach ( $expected_categories as $expected_category ) {
			$this->assertArrayHasKey( $expected_category, $categories, "Should contain the '{$expected_category}' category." );

			// Calculate the expected count for each category and validate it.
			if ( isset( $categories[ $expected_category ] ) ) {
				$expected_count = 0;
				switch ( $expected_category ) {
					case 'favorites':
						$expected_count = $this->controller::get_favorite_templates_count();
						break;
					case 'custom':
						$expected_count = count( $this->controller::get_custom_templates() );
						break;
					case 'all-items':
						$expected_count = count( $this->controller::get_templates() );
						break;
					case 'free-templates':
					case 'available-templates':
						$expected_count = 0;
						break;
				}
				$this->assertEquals( $expected_count, $categories[ $expected_category ]['count'], "The '{$expected_category}' category count should match the expected number." );
			}
		}
	}

	/**
	 * @covers FrmFormTemplatesController::append_new_template_to_nav
	 */
	public function test_append_new_template_to_nav() {
		// Mock navigation items.
		$nav_items = array(
			array(
				'link' => 'http://example.com/page1',
			),
			array(
				'link' => 'http://example.com/page2',
			),
		);

		// Case 1: 'new_template' not present in the URL.
		$_GET['new_template'] = null;
		$modified_nav_items   = $this->controller::append_new_template_to_nav( $nav_items, array() );
		// Assert that the links are unchanged.
		foreach ( $modified_nav_items as $index => $item ) {
			$this->assertEquals( $nav_items[ $index ]['link'], $item['link'], "Link should remain unchanged when 'new_template' is not present." );
		}

		// Case 2: 'new_template' is present in the URL.
		$_GET['new_template'] = 'true';
		$modified_nav_items   = $this->controller::append_new_template_to_nav( $nav_items, array() );
		// Assert that 'new_template=true' is appended to each link.
		foreach ( $modified_nav_items as $index => $item ) {
			$expected_link = $nav_items[ $index ]['link'] . '&new_template=true';
			$this->assertEquals( $expected_link, $item['link'], "Link should have 'new_template=true' appended." );
		}
	}

	/**
	 * @covers FrmFormTemplatesController::enqueue_assets
	 */
	public function test_enqueue_assets() {
		global $wp_scripts, $wp_styles;

		// Mock the global WordPress objects for scripts and styles.
		$wp_scripts = new WP_Scripts(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_styles  = new WP_Styles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Case 1: Not on the form templates page.
		$this->set_admin_screen();
		$_GET['page'] = 'some-other-page';
		$this->assertFalse(
			has_action( 'admin_enqueue_scripts', $this->controller . '::enqueue_assets' ),
			'The enqueue_assets method should be hooked to admin_enqueue_scripts with priority 15.'
		);

		// Case 2: On the form templates page.
		$_GET['page'] = $this->controller::PAGE_SLUG;
		$this->run_private_method( array( $this->controller, 'init_template_resources' ) );
		$this->controller::set_form_templates_data();
		$this->controller::enqueue_assets();
		// Assert that the specific scripts and styles for the form templates page are enqueued.
		$this->assertTrue( wp_script_is( $this->controller::SCRIPT_HANDLE, 'enqueued' ), 'Script should be enqueued on the form templates page.' );
		$this->assertTrue( wp_style_is( $this->controller::SCRIPT_HANDLE, 'enqueued' ), 'Style should be enqueued on the form templates page.' );
	}
}
