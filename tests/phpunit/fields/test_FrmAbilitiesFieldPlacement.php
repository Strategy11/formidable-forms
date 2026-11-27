<?php

/**
 * Field abilities hand section and repeater placement to Pro through hooks.
 *
 * Sections and repeaters are Pro fields, so Pro decides where a field in one
 * is stored. These cover the hooks it uses, and the earlier placement that is
 * kept when no Pro version hooks in.
 *
 * @covers FrmAbilitiesFieldsController
 * @covers FrmAbilitiesFormsController
 * @covers FrmFieldPlacementHelper
 *
 * @group mcp
 */
class test_FrmAbilitiesFieldPlacement extends FrmUnitTest {

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'wp_get_abilities' ) || ! class_exists( 'WP_Abilities_Registry' ) || ! is_callable( 'FrmMcpController::reset' ) ) {
			$this->markTestSkipped( 'The Abilities API is not available in this WordPress install.' );
		}

		// Start each test as if no Pro version places fields, whether or not Pro is active.
		remove_all_filters( 'frm_ability_new_field_placement' );
		remove_all_filters( 'frm_ability_update_field_placement' );
		remove_all_filters( 'frm_ability_create_inline_fields' );
		remove_all_actions( 'frm_ability_before_delete_field' );
		remove_all_actions( 'frm_ability_after_delete_field' );

		$this->set_current_user_to_1();

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
	 * With a Pro version that does not place fields, a new child form field still goes before the end_divider.
	 *
	 * @return void
	 */
	public function test_create_field_keeps_the_earlier_repeater_placement_without_a_placement_hook() {
		$this->assertFalse( FrmFieldPlacementHelper::has_ability_section_placement() );

		$layout  = $this->create_repeater_layout();
		$created = $this->execute(
			'create-field',
			array(
				'form_id' => $layout['child'],
				'type'    => 'text',
				'name'    => 'Added',
			)
		);

		$this->assertNotWPError( $created );
		$this->assertSame( $layout['child'], (int) $created['form_id'] );
		$this->assertSame( 3, (int) $created['field_order'], 'The field goes right after the last field of the repeater.' );
		$this->assertSame( 4, (int) FrmField::getOne( $layout['end'] )->field_order, 'Fields after it move down.' );
		$this->assertSame( 5, (int) FrmField::getOne( $layout['after'] )->field_order );
	}

	/**
	 * @return void
	 */
	public function test_create_field_stores_the_field_where_the_placement_hook_puts_it() {
		$form    = $this->create_empty_form();
		$other   = (int) FrmForm::create( $this->empty_form_values( $form ) );
		$checked = array();

		add_filter(
			'frm_ability_new_field_placement',
			function ( $prepared, $args ) use ( $other, &$checked ) {
				$checked                 = $args;
				$prepared['form_id']     = $other;
				$prepared['field_order'] = 77;
				return $prepared;
			},
			10,
			2
		);

		$this->assertTrue( FrmFieldPlacementHelper::has_ability_section_placement() );

		$created = $this->execute(
			'create-field',
			array(
				'form_id'     => $form,
				'type'        => 'text',
				'field_order' => 5,
			)
		);

		$this->assertNotWPError( $created );
		$this->assertSame( $other, (int) $created['form_id'], 'The form the hook chose must not be overwritten with the requested form.' );
		$this->assertSame( 77, (int) $created['field_order'] );
		$this->assertSame( $form, (int) $checked['form']->id );
		$this->assertTrue( $checked['has_order'] );
		$this->assertSame( 'text', $checked['field']['type'] );
	}

	/**
	 * @return void
	 */
	public function test_create_field_returns_an_error_from_the_placement_hook() {
		$form = $this->create_empty_form();

		add_filter(
			'frm_ability_new_field_placement',
			function () {
				return new WP_Error( 'frm_invalid_section', 'Not a section.', array( 'status' => 400 ) );
			}
		);

		$result = $this->execute(
			'create-field',
			array(
				'form_id' => $form,
				'type'    => 'text',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'frm_invalid_section', $result->get_error_code() );
		$this->assertEmpty( FrmField::get_all_for_form( $form ), 'A rejected field is not created.' );
	}

	/**
	 * @return void
	 */
	public function test_update_field_saves_what_the_update_hook_returns() {
		$form     = $this->create_empty_form();
		$other    = (int) FrmForm::create( $this->empty_form_values( $form ) );
		$field_id = $this->store_field( 'text', $form, 1 );
		$checked  = array();

		add_filter(
			'frm_ability_update_field_placement',
			function ( $field_data, $args ) use ( $other, &$checked ) {
				$checked                   = $args;
				$field_data['form_id']     = $other;
				$field_data['field_order'] = 55;
				return $field_data;
			},
			10,
			2
		);

		$updated = $this->execute(
			'update-field',
			array(
				'id'   => $field_id,
				'name' => 'Renamed',
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( $field_id, (int) $checked['field']->id );
		$this->assertSame( 'Renamed', $checked['input']['name'] );

		$stored = FrmField::getOne( $field_id );
		$this->assertSame( $other, (int) $stored->form_id );
		$this->assertSame( 55, (int) $stored->field_order );
		$this->assertSame( 'Renamed', $stored->name );
	}

	/**
	 * @return void
	 */
	public function test_update_field_returns_an_error_from_the_update_hook() {
		$field_id = $this->store_field( 'text', $this->create_empty_form(), 1 );

		add_filter(
			'frm_ability_update_field_placement',
			function () {
				return new WP_Error( 'frm_field_order_outside_section', 'Outside.', array( 'status' => 400 ) );
			}
		);

		$result = $this->execute(
			'update-field',
			array(
				'id'   => $field_id,
				'name' => 'Renamed',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'frm_field_order_outside_section', $result->get_error_code() );
		$this->assertNotSame( 'Renamed', FrmField::getOne( $field_id )->name, 'A rejected update is not saved.' );
	}

	/**
	 * An update hook that returns nothing must not break the update.
	 *
	 * @return void
	 */
	public function test_update_field_ignores_a_non_array_from_the_update_hook() {
		$form     = $this->create_empty_form();
		$field_id = $this->store_field( 'text', $form, 1 );

		add_filter( 'frm_ability_update_field_placement', '__return_null' );

		$updated = $this->execute(
			'update-field',
			array(
				'id'   => $field_id,
				'name' => 'Renamed',
			)
		);

		$this->assertNotWPError( $updated );

		$stored = FrmField::getOne( $field_id );
		$this->assertSame( 'Renamed', $stored->name, 'The unfiltered update is saved.' );
		$this->assertSame( $form, (int) $stored->form_id );
	}

	/**
	 * @return void
	 */
	public function test_delete_field_fires_hooks_before_and_after_the_field_is_gone() {
		$field_id = $this->store_field( 'text', $this->create_empty_form(), 1 );
		$calls    = array();

		add_action(
			'frm_ability_before_delete_field',
			function ( $field ) use ( &$calls ) {
				$calls[] = array( 'before', (int) $field->id, (bool) FrmField::getOne( $field->id ) );
			}
		);
		add_action(
			'frm_ability_after_delete_field',
			function ( $field ) use ( &$calls ) {
				$calls[] = array( 'after', (int) $field->id, (bool) FrmField::getOne( $field->id ) );
			}
		);

		$this->assertNotWPError( $this->execute( 'delete-field', array( 'id' => $field_id ) ) );
		$this->assertSame(
			array(
				array( 'before', $field_id, true ),
				array( 'after', $field_id, false ),
			),
			$calls
		);
	}

	/**
	 * @return void
	 */
	public function test_create_form_creates_inline_fields_itself_without_a_hook() {
		$created = $this->execute( 'create-form', $this->inline_form_input() );

		$this->assertNotWPError( $created );
		$this->assertCount( 2, FrmField::get_all_for_form( $created['id'] ) );
	}

	/**
	 * @return void
	 */
	public function test_create_form_skips_inline_fields_a_hook_created() {
		$received = array();

		add_filter(
			'frm_ability_create_inline_fields',
			function ( $created, $fields, $form ) use ( &$received ) {
				$received = array( count( $fields ), (int) $form->id );
				return true;
			},
			10,
			3
		);

		$created = $this->execute( 'create-form', $this->inline_form_input() );

		$this->assertNotWPError( $created );
		$this->assertSame( array( 2, (int) $created['id'] ), $received );
		$this->assertEmpty( FrmField::get_all_for_form( $created['id'] ), 'Formidable must not create the fields a second time.' );
	}

	/**
	 * Only an explicit true means a callback created the fields.
	 *
	 * @return void
	 */
	public function test_create_form_creates_inline_fields_when_the_hook_returns_false() {
		add_filter( 'frm_ability_create_inline_fields', '__return_false' );

		$created = $this->execute( 'create-form', $this->inline_form_input() );

		$this->assertNotWPError( $created );
		$this->assertCount( 2, FrmField::get_all_for_form( $created['id'] ) );
	}

	/**
	 * Inline fields go through the placement hook like create-field, and the form it picks is kept.
	 *
	 * @return void
	 */
	public function test_create_form_stores_inline_fields_where_the_placement_hook_puts_them() {
		$other = $this->create_empty_form();

		add_filter(
			'frm_ability_new_field_placement',
			function ( $prepared ) use ( $other ) {
				$prepared['form_id'] = $other;
				return $prepared;
			}
		);

		$created = $this->execute( 'create-form', $this->inline_form_input() );

		$this->assertNotWPError( $created );
		$this->assertEmpty( FrmField::get_all_for_form( $created['id'] ), 'The requested form must not overwrite the form the hook chose.' );
		$this->assertCount( 2, FrmField::get_all_for_form( $other ) );
	}

	/**
	 * @return void
	 */
	public function test_create_form_returns_an_error_from_the_inline_fields_hook() {
		add_filter(
			'frm_ability_create_inline_fields',
			function () {
				return new WP_Error( 'frm_invalid_section', 'Not a section.', array( 'status' => 400 ) );
			}
		);

		$result = $this->execute( 'create-form', $this->inline_form_input() );

		$this->assertWPError( $result );
		$this->assertSame( 'frm_invalid_section', $result->get_error_code() );
	}

	/**
	 * @return array
	 */
	private function inline_form_input() {
		return array(
			'name'   => 'Inline fields',
			'fields' => array(
				array(
					'type' => 'text',
					'name' => 'First',
				),
				array(
					'type' => 'text',
					'name' => 'Second',
				),
			),
		);
	}

	/**
	 * Build a form with a repeater whose child form has one field, plus a field after it.
	 *
	 * @return array<string, int>
	 */
	private function create_repeater_layout() {
		$form     = $this->create_empty_form();
		$child    = (int) FrmForm::create( $this->empty_form_values( $form ) );
		$repeater = $this->store_field(
			'divider',
			$form,
			1,
			array(
				'repeat'      => 1,
				'form_select' => $child,
			)
		);
		$item     = $this->store_field( 'text', $child, 2, array( 'in_section' => $repeater ) );
		$end      = $this->store_field( 'end_divider', $form, 3 );
		$after    = $this->store_field( 'text', $form, 4 );

		return compact( 'form', 'child', 'repeater', 'item', 'end', 'after' );
	}

	/**
	 * The form factory adds a text field to every form, which these layouts must not have.
	 *
	 * @return int
	 */
	private function create_empty_form() {
		return (int) FrmForm::create( $this->empty_form_values() );
	}

	/**
	 * @param int $parent_form_id Parent form, 0 for a top-level form.
	 *
	 * @return array
	 */
	private function empty_form_values( $parent_form_id = 0 ) {
		return array(
			'form_key'       => '',
			'name'           => 'Placement',
			'description'    => '',
			'parent_form_id' => $parent_form_id,
		);
	}

	/**
	 * Store a field exactly as given, the way legacy data and imports can hold it.
	 *
	 * @param string $type    Field type.
	 * @param int    $form_id Form ID.
	 * @param int    $order   Field order.
	 * @param array  $options Field options to merge over the defaults.
	 *
	 * @return int
	 */
	private function store_field( $type, $form_id, $order, $options = array() ) {
		$values                  = FrmFieldsHelper::setup_new_vars( $type, $form_id );
		$values['field_order']   = $order;
		$values['field_options'] = array_merge( $values['field_options'], $options );
		$field_id                = FrmField::create( $values );

		$this->assertNotFalse( $field_id, 'The ' . $type . ' test field should be created.' );

		return (int) $field_id;
	}
}
