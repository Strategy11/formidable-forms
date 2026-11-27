<?php

/**
 * Field abilities must place section and repeater fields the way the frontend renders them.
 *
 * The frontend repeats the fields of a repeater's child form and groups the
 * other fields into sections by field_order, so create-field, update-field,
 * delete-field and create-form keep form_id, in_section and field_order in
 * agreement, including on layouts that were already inconsistent.
 *
 * @covers FrmAbilitiesFieldsController
 * @covers FrmAbilitiesInlineFieldsHelper
 * @covers FrmFieldPlacementHelper
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
	 * A field put in a repeater from the parent form must be stored in the child form,
	 * or the frontend renders it once while the builder shows it in the repeater.
	 *
	 * @return void
	 */
	public function test_create_field_in_a_repeater_is_stored_in_its_child_form() {
		$layout  = $this->create_repeater_layout();
		$created = $this->execute(
			'create-field',
			array(
				'form_id'       => $layout['form'],
				'type'          => 'text',
				'name'          => 'Item',
				'field_options' => array( 'in_section' => $layout['repeater'] ),
			)
		);

		$this->assertNotWPError( $created );
		$this->assertSame( $layout['child'], $created['form_id'], 'The frontend only repeats fields of the child form.' );
		$this->assert_between( $created['id'], $layout['repeater'], $layout['end'] );
	}

	/**
	 * @return void
	 */
	public function test_create_field_on_a_child_form_joins_its_repeater_before_the_end_divider() {
		$layout = $this->create_repeater_layout();
		// A child field already ordered past the end_divider must not pull new fields out with it.
		$this->store_field( 'text', $layout['child'], 50, array( 'in_section' => $layout['repeater'] ) );

		$created = $this->execute(
			'create-field',
			array(
				'form_id' => $layout['child'],
				'type'    => 'text',
				'name'    => 'Added',
			)
		);

		$this->assertNotWPError( $created );
		$this->assertSame( $layout['repeater'], (int) $created['field_options']['in_section'], 'A child form field belongs to the repeater that renders it.' );
		$this->assert_between( $created['id'], $layout['repeater'], $layout['end'] );
		$end_order = (int) FrmField::getOne( $layout['end'] )->field_order;
		$this->assertLessThan( (int) FrmField::getOne( $layout['after'] )->field_order, $end_order, 'Fields after the repeater move down to make room.' );
	}

	/**
	 * @return void
	 */
	public function test_create_field_rejects_an_in_section_that_is_not_a_section_on_the_form() {
		$layout     = $this->create_repeater_layout();
		$other      = $this->create_repeater_layout();
		$missing    = FrmForm::create( $this->empty_form_values( $layout['form'] ) );
		$no_child   = $this->store_field(
			'divider',
			$layout['form'],
			20,
			array(
				'repeat'      => 1,
				'form_select' => $missing,
			)
		);
		$candidates = array(
			'a missing field'                 => 999999,
			'another form section'            => $other['repeater'],
			'a field that is not a container' => $layout['after'],
		);
		FrmForm::destroy( $missing );
		$candidates['a repeater whose child form is gone'] = $no_child;

		foreach ( $candidates as $label => $section_id ) {
			$result = $this->execute(
				'create-field',
				array(
					'form_id'       => $layout['form'],
					'type'          => 'text',
					'field_options' => array( 'in_section' => $section_id ),
				)
			);

			$this->assertWPError( $result, 'in_section set to ' . $label . ' must be rejected.' );
		}
	}

	/**
	 * @return void
	 */
	public function test_create_field_in_a_section_without_an_order_is_added_before_its_end_divider() {
		$form    = $this->create_empty_form();
		$section = $this->store_field( 'divider', $form, 1 );
		$inside  = $this->store_field( 'text', $form, 2, array( 'in_section' => $section ) );
		$end     = $this->store_field( 'end_divider', $form, 3 );
		$after   = $this->store_field( 'text', $form, 4 );

		$created = $this->execute(
			'create-field',
			array(
				'form_id'       => $form,
				'type'          => 'text',
				'field_options' => array( 'in_section' => $section ),
			)
		);

		$this->assertNotWPError( $created );
		$this->assertSame( 3, $created['field_order'], 'The field goes at the end of the section.' );
		$this->assertSame( 2, (int) FrmField::getOne( $inside )->field_order );
		$this->assertSame( 4, (int) FrmField::getOne( $end )->field_order, 'The end_divider moves down so the field stays inside.' );
		$this->assertSame( 5, (int) FrmField::getOne( $after )->field_order );
	}

	/**
	 * The end of a section is after its last field, not its first.
	 *
	 * @return void
	 */
	public function test_create_field_in_a_section_with_several_fields_is_added_after_the_last_one() {
		$form    = $this->create_empty_form();
		$section = $this->store_field( 'divider', $form, 1 );
		$first   = $this->store_field( 'text', $form, 2, array( 'in_section' => $section ) );
		$second  = $this->store_field( 'text', $form, 3, array( 'in_section' => $section ) );
		$last    = $this->store_field( 'text', $form, 4, array( 'in_section' => $section ) );
		$end     = $this->store_field( 'end_divider', $form, 5 );
		$after   = $this->store_field( 'text', $form, 6 );

		$created = $this->execute(
			'create-field',
			array(
				'form_id'       => $form,
				'type'          => 'text',
				'field_options' => array( 'in_section' => $section ),
			)
		);

		$this->assertNotWPError( $created );
		$this->assertSame( 5, $created['field_order'], 'The field goes after the last field of the section.' );
		$this->assertSame( 2, (int) FrmField::getOne( $first )->field_order );
		$this->assertSame( 3, (int) FrmField::getOne( $second )->field_order );
		$this->assertSame( 4, (int) FrmField::getOne( $last )->field_order, 'Fields already in the section keep their order.' );
		$this->assertSame( 6, (int) FrmField::getOne( $end )->field_order, 'The end_divider moves down so the field stays inside.' );
		$this->assertSame( 7, (int) FrmField::getOne( $after )->field_order );
	}

	/**
	 * @return void
	 */
	public function test_create_field_with_an_order_outside_its_section_is_rejected() {
		$form    = $this->create_empty_form();
		$section = $this->store_field( 'divider', $form, 1 );
		$this->store_field( 'end_divider', $form, 2 );
		$this->store_field( 'text', $form, 3 );

		$result = $this->execute(
			'create-field',
			array(
				'form_id'       => $form,
				'type'          => 'text',
				'field_order'   => 4,
				'field_options' => array( 'in_section' => $section ),
			)
		);

		$this->assertWPError( $result, 'The frontend would show the field outside the section it names.' );
	}

	/**
	 * The frontend groups top-level fields by order, so an order inside a section or repeater joins it.
	 *
	 * @return void
	 */
	public function test_create_field_with_an_order_inside_a_section_or_repeater_joins_it() {
		$layout = $this->create_repeater_layout();

		$in_repeater = $this->execute(
			'create-field',
			array(
				'form_id'     => $layout['form'],
				'type'        => 'text',
				'field_order' => (int) FrmField::getOne( $layout['end'] )->field_order,
			)
		);

		$this->assertNotWPError( $in_repeater );
		$this->assertSame( $layout['child'], $in_repeater['form_id'] );
		$this->assertSame( $layout['repeater'], (int) $in_repeater['field_options']['in_section'] );

		$form    = $this->create_empty_form();
		$section = $this->store_field( 'divider', $form, 10 );
		$this->store_field( 'end_divider', $form, 20 );

		$in_section = $this->execute(
			'create-field',
			array(
				'form_id'     => $form,
				'type'        => 'text',
				'field_order' => 15,
			)
		);
		$this->assertNotWPError( $in_section );
		$this->assertSame( $section, (int) $in_section['field_options']['in_section'] );

		// Page breaks are a Pro field type, so check the shared placement step directly.
		$page_break = FrmFieldPlacementHelper::prepare_new_placement(
			array(
				'type'          => 'break',
				'form_id'       => $form,
				'field_order'   => 16,
				'field_options' => array(),
			),
			true
		);
		$this->assertNotWPError( $page_break, 'A type sections cannot hold keeps its old top-level placement.' );
		$this->assertSame( 0, (int) $page_break['field_options']['in_section'] );
	}

	/**
	 * @return void
	 */
	public function test_create_end_divider_on_a_child_form_is_stored_after_every_field() {
		$layout = $this->create_repeater_layout();
		$late   = $this->store_field( 'text', $layout['child'], 70, array( 'in_section' => $layout['repeater'] ) );
		// end_divider is a Pro field type, so check the shared placement step directly.
		$created = FrmFieldPlacementHelper::prepare_new_placement(
			array(
				'type'          => 'end_divider',
				'form_id'       => $layout['child'],
				'field_order'   => 1,
				'field_options' => array(),
			),
			false
		);

		$this->assertNotWPError( $created );
		$this->assertSame( $layout['form'], $created['form_id'], 'End fields only render in the top-level form.' );
		$this->assertGreaterThan( (int) FrmField::getOne( $late )->field_order, $created['field_order'], 'A new end field must not land on a child field order.' );
	}

	/**
	 * @return void
	 */
	public function test_update_field_moves_a_field_into_and_out_of_a_repeater() {
		$layout = $this->create_repeater_layout();

		$moved_in = $this->execute(
			'update-field',
			array(
				'id'            => $layout['after'],
				'field_options' => array( 'in_section' => $layout['repeater'] ),
			)
		);

		$this->assertNotWPError( $moved_in );
		$this->assertSame( $layout['child'], $moved_in['form_id'], 'Moving into a repeater stores the field in the child form, like a builder drag.' );
		$this->assert_between( $layout['after'], $layout['repeater'], $layout['end'] );

		$moved_out = $this->execute(
			'update-field',
			array(
				'id'            => $layout['after'],
				'field_options' => array( 'in_section' => 0 ),
			)
		);

		$this->assertNotWPError( $moved_out );
		$this->assertSame( $layout['form'], $moved_out['form_id'], 'Moving out of a repeater stores the field in the top-level form.' );
		$this->assertGreaterThan( (int) FrmField::getOne( $layout['end'] )->field_order, $moved_out['field_order'], 'With no order given, the field goes after the repeater.' );
	}

	/**
	 * Callers often resend every stored option, so an unchanged in_section must not move a legacy field.
	 *
	 * @return void
	 */
	public function test_update_field_keeps_a_legacy_placement_when_in_section_is_unchanged() {
		$layout = $this->create_repeater_layout();
		$legacy = $this->store_field( 'text', $layout['form'], 2, array( 'in_section' => $layout['repeater'] ) );
		$stored = FrmField::getOne( $legacy );

		$updated = $this->execute(
			'update-field',
			array(
				'id'            => $legacy,
				'name'          => 'Renamed',
				'field_options' => (array) $stored->field_options,
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( $layout['form'], $updated['form_id'], 'Its saved entry values stay with the form they were saved on.' );
		$this->assertSame( (int) $stored->field_order, $updated['field_order'] );
	}

	/**
	 * @return void
	 */
	public function test_delete_section_removes_its_end_and_keeps_its_fields() {
		$form    = $this->create_empty_form();
		$section = $this->store_field( 'divider', $form, 1 );
		$inside  = $this->store_field( 'text', $form, 2, array( 'in_section' => $section ) );
		$end     = $this->store_field( 'end_divider', $form, 3 );

		$this->assertNotWPError( $this->execute( 'delete-field', array( 'id' => $section ) ) );

		$this->assertNull( FrmField::getOne( $end ), 'An end_divider with nothing to close must go with its section.' );
		$this->assertNotNull( FrmField::getOne( $inside ), 'Deleting a heading must not delete the fields under it.' );
		$this->assertSame( 0, (int) FrmField::get_option( FrmField::getOne( $inside ), 'in_section' ), 'The kept field must not point at a deleted section.' );
	}

	/**
	 * @return void
	 */
	public function test_delete_repeater_removes_its_child_form_and_end() {
		$layout = $this->create_repeater_layout();
		$legacy = $this->store_field( 'text', $layout['form'], 2, array( 'in_section' => $layout['repeater'] ) );

		$this->assertNotWPError( $this->execute( 'delete-field', array( 'id' => $layout['repeater'] ) ) );

		$this->assertNull( FrmField::getOne( $layout['end'] ) );
		$this->assertNull( FrmField::getOne( $layout['item'] ), 'The child form fields go with the repeater.' );
		$this->assertEmpty( FrmForm::getOne( $layout['child'] ), 'An empty child form must not be left linked to the parent.' );
		$this->assertSame( 0, (int) FrmField::get_option( FrmField::getOne( $legacy ), 'in_section' ) );
		$this->assertNotNull( FrmField::getOne( $layout['after'] ), 'Fields after the repeater are untouched.' );
	}

	/**
	 * Inline fields cannot name their section by ID, so their order places them, and open sections get closed.
	 *
	 * @return void
	 */
	public function test_create_form_places_inline_fields_in_their_sections() {
		$created = $this->execute(
			'create-form',
			array(
				'name'   => 'Inline sections',
				// Without Pro, end_divider is not an inline type, so every section here is closed automatically.
				'fields' => array(
					array(
						'type' => 'text',
						'name' => 'Before',
					),
					array(
						'type' => 'section',
						'name' => 'First',
					),
					array(
						'type' => 'text',
						'name' => 'In first',
					),
					array(
						'type' => 'section',
						'name' => 'Second',
					),
					array(
						'type' => 'text',
						'name' => 'In second',
					),
					array(
						'type' => 'text',
						'name' => 'Also in second',
					),
					array(
						'type'          => 'text',
						'name'          => 'After',
						'field_options' => array( 'in_section' => 0 ),
					),
				),
			)
		);

		$this->assertNotWPError( $created );

		$fields  = FrmField::get_all_for_form( $created['id'] );
		$names   = array();
		$ids     = array();
		$section = array();

		foreach ( $fields as $field ) {
			$names[]                 = $field->type . ':' . $field->name;
			$ids[ $field->name ]     = (int) $field->id;
			$section[ $field->name ] = (int) FrmField::get_option( $field, 'in_section' );
		}

		$end = FrmFieldsHelper::setup_new_vars( 'end_divider' )['name'];
		$this->assertSame(
			array(
				'text:Before',
				'divider:First',
				'text:In first',
				'end_divider:' . $end,
				'divider:Second',
				'text:In second',
				'text:Also in second',
				'end_divider:' . $end,
				'text:After',
			),
			$names,
			'Every section must be closed exactly once, in order.'
		);
		$this->assertSame( $ids['First'], $section['In first'] );
		$this->assertSame( $ids['Second'], $section['In second'] );
		$this->assertSame( $ids['Second'], $section['Also in second'] );
		$this->assertSame( 0, $section['After'], 'An explicit in_section of 0 takes the field out of the open section.' );
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

	/**
	 * @param int $field_id Field that must sit inside the window.
	 * @param int $start_id Field that opens the window.
	 * @param int $end_id   Field that closes the window.
	 *
	 * @return void
	 */
	private function assert_between( $field_id, $start_id, $end_id ) {
		$order = (int) FrmField::getOne( $field_id )->field_order;

		$this->assertGreaterThan( (int) FrmField::getOne( $start_id )->field_order, $order, 'The field must be ordered after the section opens.' );
		$this->assertLessThan( (int) FrmField::getOne( $end_id )->field_order, $order, 'The field must be ordered before the section closes.' );
	}
}
