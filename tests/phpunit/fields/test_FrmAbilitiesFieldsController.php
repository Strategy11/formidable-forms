<?php

/**
 * Behavior tests for the field abilities: create/list/update/delete through
 * the registered abilities, permission boundaries, and the validation
 * FrmAbilitiesFieldsController enforces before writing.
 */
class test_FrmAbilitiesFieldsController extends FrmUnitTest {

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

		$this->assertNotNull( $ability, 'The ' . $slug . ' ability should be registered.' );

		return $ability->execute( $input );
	}

	/**
	 * @return void
	 */
	public function test_a_field_round_trips_through_create_list_update_delete() {
		$created = $this->execute(
			'create-field',
			array(
				'form_id'  => $this->form->id,
				'type'     => 'text',
				'name'     => 'Full Name',
				'required' => true,
			)
		);

		$this->assertNotWPError( $created, 'create-field should succeed with a form_id and a type.' );
		$this->assertSame( 'Full Name', $created['name'] );
		$this->assertSame( 'text', $created['type'] );
		$this->assertTrue( (bool) $created['required'] );

		$listed = $this->execute( 'list-fields', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $created['field_key'], $listed, 'The created field should be in the listing, keyed by field_key.' );

		$updated = $this->execute(
			'update-field',
			array(
				'id'   => $created['id'],
				'name' => 'Renamed Field',
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( 'Renamed Field', $updated['name'] );

		$deleted = $this->execute( 'delete-field', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $deleted );
		$this->assertSame( $created['id'], $deleted['id'] );

		$after = $this->execute( 'list-fields', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $after );
		$this->assertArrayNotHasKey( $created['field_key'], $after, 'A deleted field should no longer be listed.' );
	}

	/**
	 * @return void
	 */
	public function test_list_fields_requires_a_form_id() {
		$ability = wp_get_ability( 'formidable-forms/list-fields' );
		$result  = $ability->execute( array() );

		$this->assertWPError( $result, 'list-fields should refuse a request with no form_id, through the ability schema\'s own required check.' );
	}

	/**
	 * @return void
	 */
	public function test_create_field_requires_a_type() {
		$ability = wp_get_ability( 'formidable-forms/create-field' );
		$result  = $ability->execute( array( 'form_id' => $this->form->id ) );

		$this->assertWPError( $result, 'create-field should refuse a request with no type, through the ability schema\'s own required check.' );
	}

	/**
	 * @return void
	 */
	public function test_create_field_reports_not_found_for_a_missing_form() {
		$result = $this->execute(
			'create-field',
			array(
				'form_id' => 99999999,
				'type'    => 'text',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_update_field_reports_not_found_for_a_missing_id() {
		$result = $this->execute(
			'update-field',
			array(
				'id'   => 99999999,
				'name' => 'X',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_delete_field_reports_not_found_for_a_missing_id() {
		$result = $this->execute( 'delete-field', array( 'id' => 99999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * Form_id on delete-field is a guard, not a requirement: it only checks
	 * that the field named actually belongs to the form named, so a field
	 * that belongs to a different form should be refused rather than deleted.
	 *
	 * @return void
	 */
	public function test_delete_field_refuses_a_field_that_does_not_belong_to_the_given_form() {
		$field      = $this->factory->field->create_and_get(
			array(
				'form_id' => $this->form->id,
				'type'    => 'text',
			)
		);
		$other_form = $this->factory->form->create_and_get();
		$result     = $this->execute(
			'delete-field',
			array(
				'id'      => $field->id,
				'form_id' => $other_form->id,
			)
		);

		$this->assertWPError( $result, 'A field belonging to a different form than named should be refused.' );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );

		$this->assertNotNull( FrmField::getOne( $field->id ), 'The field should not have been deleted.' );
	}

	/**
	 * The placeholder property is not a real column: FrmField::update() only
	 * writes real columns, so update-field has to fold it into field_options
	 * itself or the value is silently dropped.
	 *
	 * @return void
	 */
	public function test_update_field_stores_the_placeholder_inside_field_options() {
		$field   = $this->factory->field->create_and_get(
			array(
				'form_id' => $this->form->id,
				'type'    => 'text',
			)
		);
		$updated = $this->execute(
			'update-field',
			array(
				'id'          => $field->id,
				'placeholder' => 'Type here',
			)
		);

		$this->assertNotWPError( $updated );

		$stored = FrmField::getOne( $field->id );
		$this->assertSame( 'Type here', $stored->field_options['placeholder'] ?? null, 'The placeholder should be stored inside field_options.' );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_and_edit_and_delete_forms_capabilities() {
		$cases = array(
			'list-fields'  => array( 'frm_view_forms', array( 'form_id' => $this->form->id ) ),
			'create-field' => array(
				'frm_edit_forms',
				array(
					'form_id' => $this->form->id,
					'type'    => 'text',
				),
			),
			'update-field' => array(
				'frm_edit_forms',
				array(
					'id'   => $this->factory->field->create(
						array(
							'form_id' => $this->form->id,
							'type'    => 'text',
						)
					),
					'name' => 'X',
				),
			),
			'delete-field' => array(
				'frm_delete_forms',
				array(
					'id' => $this->factory->field->create(
						array(
							'form_id' => $this->form->id,
							'type'    => 'text',
						)
					),
				),
			),
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
