<?php

/**
 * Behavior tests for the form abilities: create/read/update/delete through
 * the registered abilities, permission boundaries, and the validation
 * FrmAbilitiesFormsController enforces before writing.
 */
class test_FrmAbilitiesFormsController extends FrmUnitTest {

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
	public function test_a_form_round_trips_through_create_get_update_delete() {
		$created = $this->execute(
			'create-form',
			array(
				'name'        => 'Ability Created Form',
				'description' => 'Made through create-form.',
			)
		);

		$this->assertNotWPError( $created, 'create-form should succeed with just a name.' );
		$this->assertSame( 'Ability Created Form', $created['name'] );
		$this->assertSame( 'published', $created['status'], 'A form with no status sent should default to published.' );
		$this->assertNotEmpty( $created['form_key'], 'A form key should be derived from the name.' );

		$fetched = $this->execute( 'get-form', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $fetched );
		$this->assertSame( $created['id'], $fetched['id'] );

		$fetched_by_key = $this->execute( 'get-form', array( 'id' => $created['form_key'] ) );
		$this->assertNotWPError( $fetched_by_key, 'get-form should also resolve by form_key.' );
		$this->assertSame( $created['id'], $fetched_by_key['id'] );

		$updated = $this->execute(
			'update-form',
			array(
				'id'     => $created['id'],
				'name'   => 'Renamed Form',
				'status' => 'draft',
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( 'Renamed Form', $updated['name'] );
		$this->assertSame( 'draft', $updated['status'] );
		$this->assertSame( 'Made through create-form.', $updated['description'], 'A field left out of the update should keep its value.' );

		$deleted = $this->execute( 'delete-form', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $deleted );
		$this->assertSame( $created['id'], $deleted['id'] );

		$after = $this->execute( 'get-form', array( 'id' => $created['id'] ) );
		$this->assertWPError( $after, 'A deleted form should no longer be found.' );
		$this->assertSame( 404, $after->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_create_form_requires_a_name() {
		$ability = wp_get_ability( 'formidable-forms/create-form' );
		$result  = $ability->execute( array() );

		$this->assertWPError( $result, 'create-form should refuse an input with no name, through the ability schema\'s own required check.' );
	}

	/**
	 * The ability's own schema already enums status to published/draft/trash,
	 * so a value outside that set never reaches the controller through the
	 * ability layer. Called directly, to cover FrmAbilitiesFormsController::execute_update_form()'s
	 * own status check rather than the schema validation in front of it.
	 *
	 * @return void
	 */
	public function test_update_form_rejects_an_invalid_status() {
		$form   = $this->factory->form->create_and_get();
		$result = FrmAbilitiesFormsController::execute_update_form(
			array(
				'id'     => $form->id,
				'status' => 'not-a-real-status',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 400, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_update_form_requires_at_least_one_field_to_change() {
		$form   = $this->factory->form->create_and_get();
		$result = $this->execute( 'update-form', array( 'id' => $form->id ) );

		$this->assertWPError( $result, 'update-form with nothing but an id should be refused rather than a silent no-op.' );
		$this->assertSame( 400, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * Options are stored nested under $form['options'], but
	 * FrmFormsHelper::setup_new_vars() flattens them onto the top level, so
	 * create-form has to re-nest submitted options or they are silently
	 * dropped and every option falls back to its default.
	 *
	 * @return void
	 */
	public function test_create_form_applies_submitted_options() {
		$created = $this->execute(
			'create-form',
			array(
				'name'    => 'Form With Options',
				'options' => array( 'submit_value' => 'Send it' ),
			)
		);

		$this->assertNotWPError( $created );

		$form = FrmForm::getOne( $created['id'] );
		$this->assertSame( 'Send it', $form->options['submit_value'] ?? null, 'A submitted option should be applied, not dropped to its default.' );
	}

	/**
	 * A partial options update has to merge over what is stored, not replace
	 * it outright, or every option left out of the request would reset to
	 * its default and the form would lose its assigned style.
	 *
	 * @return void
	 */
	public function test_update_form_merges_options_rather_than_replacing_them() {
		$created = $this->execute(
			'create-form',
			array(
				'name'    => 'Form With Two Options',
				'options' => array(
					'submit_value' => 'Send it',
					'success_msg'  => 'Thanks!',
				),
			)
		);
		$this->assertNotWPError( $created );

		$updated = $this->execute(
			'update-form',
			array(
				'id'      => $created['id'],
				'options' => array( 'submit_value' => 'Go' ),
			)
		);

		$this->assertNotWPError( $updated );
		$form = FrmForm::getOne( $created['id'] );
		$this->assertSame( 'Go', $form->options['submit_value'] ?? null, 'The submitted option should be applied.' );
		$this->assertSame( 'Thanks!', $form->options['success_msg'] ?? null, 'An option left out of the update should keep its stored value.' );
	}

	/**
	 * Fields sent inline with create-form go through the same pipeline as
	 * create-field, so a form is never created half configured.
	 *
	 * @return void
	 */
	public function test_create_form_creates_inline_fields() {
		$created = $this->execute(
			'create-form',
			array(
				'name'   => 'Form With Fields',
				'fields' => array(
					array(
						'type' => 'text',
						'name' => 'Full Name',
					),
					array(
						'type' => 'email',
						'name' => 'Email Address',
					),
				),
			)
		);

		$this->assertNotWPError( $created );

		$fields = FrmField::get_all_for_form( $created['id'] );
		$this->assertCount( 2, $fields, 'Both inline fields should have been created.' );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_and_edit_and_delete_forms_capabilities() {
		$cases = array(
			'list-forms'  => array( 'frm_view_forms', array() ),
			'get-form'    => array( 'frm_view_forms', array( 'id' => $this->factory->form->create() ) ),
			'create-form' => array( 'frm_edit_forms', array( 'name' => 'X' ) ),
			'update-form' => array(
				'frm_edit_forms',
				array(
					'id'   => $this->factory->form->create(),
					'name' => 'X',
				),
			),
			'delete-form' => array( 'frm_delete_forms', array( 'id' => $this->factory->form->create() ) ),
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
