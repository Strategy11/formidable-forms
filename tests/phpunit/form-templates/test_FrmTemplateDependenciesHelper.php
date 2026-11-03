<?php

/**
 * @group form-templates
 *
 * @covers FrmTemplateDependenciesHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'form-templates' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmTemplateDependenciesHelper::class )]
class test_FrmTemplateDependenciesHelper extends FrmAjaxUnitTest {

	private $template_url = 'https://s3.amazonaws.com/fp.strategy11.com/form-templates/dependency-test.xml';

	public function setUp(): void {
		parent::setUp();
		remove_filter( 'pre_option_active_plugins', 'wp_tests_options' );
		$this->set_as_user_role( 'administrator' );
		FrmAppHelper::maybe_add_permissions();
		wp_set_current_user( 0 );
		wp_set_current_user( $this->user_id );
		$this->cache_templates( array( 'used_addons' => array( 'Quiz Maker' ) ) );
		$api = new FrmFormApi();
		update_option(
			$api->get_cache_key(),
			array(
				'timeout' => time() + HOUR_IN_SECONDS,
				'value'   => wp_json_encode(
					array(
						20815759 => array(
							'title'  => 'Quiz Maker',
							'slug'   => 'quiz-maker',
							'plugin' => 'formidable/formidable.php',
						),
					)
				),
			)
		);
	}

	/**
	 * Cache a controlled template catalog without external requests.
	 *
	 * @param array $requirements The template dependency properties.
	 *
	 * @return void
	 */
	private function cache_templates( $requirements ) {
		$api = new FrmFormTemplateApi();
		update_option(
			$api->get_cache_key(),
			array(
				'timeout' => time() + HOUR_IN_SECONDS,
				'value'   => wp_json_encode(
					array(
						array_merge(
							array(
								'id'  => 123,
								'url' => $this->template_url,
							),
							$requirements
						),
					)
				),
			)
		);
	}

	public function test_used_addons_are_combined_with_template_corrections() {
		$result = FrmTemplateDependenciesHelper::get_required_addons(
			array(
				'id'          => 28150471,
				'used_addons' => array( 'Quiz Maker' ),
			)
		);
		$this->assertContains( 'Quiz Maker', $result, 'The API add-on list should be preserved.' );
		$this->assertContains( 'signature', $result, 'Known missing signature requirements should be added.' );
		$this->assertContains( 'geo', $result, 'Known missing geolocation requirements should be added.' );
	}

	public function test_legacy_overrides_preserve_multiple_dependencies() {
		$result = FrmTemplateDependenciesHelper::get_required_addons( array( 'id' => 28150471 ) );
		$this->assertContains( 'signature', $result, 'Membership should require its signature field.' );
		$this->assertContains( 'geo', $result, 'Membership should require its address autocomplete.' );
	}

	public function test_ordinary_date_templates_do_not_require_dates() {
		$result = FrmTemplateDependenciesHelper::get_required_addons( array( 'id' => 28224368 ) );
		$this->assertSame( array(), $result, 'An ordinary date field should not require the Dates add-on.' );
		$result = FrmTemplateDependenciesHelper::get_required_addons( array( 'id' => 28220800 ) );
		$this->assertContains( 'dates', $result, 'The Volunteer Hours dynamic maximum date should require Dates.' );
	}

	public function test_legacy_payment_gateways_and_malformed_metadata() {
		$result = FrmTemplateDependenciesHelper::get_required_addons(
			array( 'used_addons' => array( 'Stripe', 'Square', 'PayPal Commerce', 'PayPal Standard', array() ) )
		);
		$this->assertSame( array( 'PayPal Standard' ), $result, 'Only the legacy PayPal gateway should require a separate add-on.' );
		$this->assertSame( array(), FrmTemplateDependenciesHelper::get_required_addons( array() ), 'Missing metadata should be supported.' );
	}

	public function test_active_dependencies_are_recognized_without_activation_permission() {
		update_option( 'active_plugins', array( 'formidable/formidable.php' ) );
		$this->set_as_user_role( 'editor' );
		$this->assertSame( array(), FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url ), 'Active state should not depend on activation permissions.' );
	}

	public function test_inactive_dependencies_require_activation_and_support_resigned_urls() {
		update_option( 'active_plugins', array() );
		$missing = FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url . '?Expires=123&Signature=changed' );
		$this->assertCount( 1, $missing, 'A changed download signature should not bypass dependency checks.' );
		$this->assertSame( 'frm_activate_addon', $missing[0]['action'], 'An installed inactive dependency should offer activation.' );
	}

	public function test_user_without_permissions_cannot_activate_dependencies() {
		update_option( 'active_plugins', array() );
		$this->set_as_user_role( 'editor' );
		$missing = FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url );
		$this->assertCount( 1, $missing, 'The inactive dependency should remain visible to an editor.' );
		$this->assertSame( '', $missing[0]['action'], 'An editor must not receive an activation action.' );
	}

	public function test_unknown_dependencies_block_import_with_manual_instructions() {
		$this->cache_templates( array( 'used_addons' => array( 'Unknown add-on' ) ) );
		$missing = FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url );
		$this->assertCount( 1, $missing, 'Unknown dependencies should not be silently ignored.' );
		$this->assertSame( 'Unknown add-on', $missing[0]['name'], 'The unknown add-on label should be preserved.' );
		$this->assertSame( '', $missing[0]['action'], 'Unknown dependencies should require manual resolution.' );
	}

	public function test_import_stops_before_downloading_or_creating_forms() {
		update_option( 'active_plugins', array() );
		$_POST    = array(
			'nonce' => wp_create_nonce( 'frm_ajax' ),
			'xml'   => $this->template_url,
		);
		$before   = (array) FrmForm::get_count();
		$response = json_decode( $this->trigger_action( 'frm_install_template' ), true );
		$this->assertArrayHasKey( 'missing_addons', $response, 'Import should return the dependency step.' );
		$this->assertArrayNotHasKey( 'redirect', $response, 'Import should not redirect into a broken form.' );
		$this->assertSame( $before, (array) FrmForm::get_count(), 'No forms should be created before dependencies are active.' );
	}
	public function test_missing_dependency_offers_installation_from_the_license_catalog() {
		$api = new FrmFormApi();
		update_option(
			$api->get_cache_key(),
			array(
				'timeout' => time() + HOUR_IN_SECONDS,
				'value'   => wp_json_encode(
					array(
						20815759 => array(
							'title'  => 'Quiz Maker',
							'slug'   => 'quiz-maker',
							'plugin' => 'template-test-missing/template-test-missing.php',
							'url'    => 'https://example.org/quiz.zip',
						),
					)
				),
			)
		);
		$missing = FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url );
		$this->assertSame( 'frm_install_addon', $missing[0]['action'], 'An available missing dependency should offer installation.' );
		$this->assertSame( 'https://example.org/quiz.zip', $missing[0]['plugin'], 'Installation should use the licensed catalog URL.' );
	}

	public function test_dates_override_resolves_the_datepicker_options_catalog_name() {
		update_option( 'active_plugins', array() );
		$this->cache_templates( array( 'used_addons' => array( 'dates' ) ) );
		$api = new FrmFormApi();
		update_option(
			$api->get_cache_key(),
			array(
				'timeout' => time() + HOUR_IN_SECONDS,
				'value'   => wp_json_encode(
					array(
						20247260 => array(
							'title'  => 'Datepicker Options',
							'slug'   => 'datepicker-options',
							'plugin' => 'formidable/formidable.php',
						),
					)
				),
			)
		);
		$missing = FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url );
		$this->assertSame( 'Datepicker Options', $missing[0]['name'], 'The Dates slug should resolve to the catalog display name.' );
		$this->assertSame( 'frm_activate_addon', $missing[0]['action'], 'The resolved Dates add-on should offer activation.' );
	}

	public function test_matching_slug_and_name_do_not_duplicate_the_same_dependency() {
		update_option( 'active_plugins', array() );
		$this->cache_templates( array( 'used_addons' => array( 'Quiz Maker', 'quiz-maker' ) ) );
		$missing = FrmTemplateDependenciesHelper::get_missing_addons( $this->template_url );
		$this->assertCount( 1, $missing, 'Aliases for the same plugin should produce one dependency row.' );
	}

	public function test_import_continues_after_dependencies_are_activated() {
		update_option( 'active_plugins', array( 'formidable/formidable.php' ) );
		add_filter( 'pre_http_request', array( $this, 'mock_template_download' ), 10, 3 );
		$_POST    = array(
			'nonce' => wp_create_nonce( 'frm_ajax' ),
			'xml'   => $this->template_url,
		);
		$response = json_decode( $this->trigger_action( 'frm_install_template' ), true );
		$this->assertArrayHasKey( 'redirect', $response, 'An activated dependency should allow the template to import.' );
		$this->assertArrayNotHasKey( 'missing_addons', $response, 'Active dependencies should not show another dependency step.' );
	}

	/**
	 * Return a minimal XML template for the import continuation test.
	 *
	 * @param mixed  $response The HTTP preemption value.
	 * @param array  $args The request options.
	 * @param string $url The requested download URL.
	 *
	 * @return mixed The mock response or the original preemption value.
	 */
	public function mock_template_download( $response, $args, $url ) {
		if ( $url !== $this->template_url ) {
			return $response;
		}

		return array(
			'response' => array( 'code' => 200 ),
			'body'     => '<channel><form><id>9999</id><form_key>template-dependency-test</form_key><name>Dependency test</name><options>{}</options></form></channel>',
		);
	}
}
