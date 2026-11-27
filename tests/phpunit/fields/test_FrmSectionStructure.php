<?php

/**
 * The builder must group section and repeater fields the way the frontend renders them.
 *
 * The frontend repeats the fields of a repeater's child form whatever their
 * field_order, and renders parent form fields once. These cover the layouts
 * where field_order, form_id and in_section disagree.
 *
 * @covers FrmFieldPlacementHelper
 */
class test_FrmSectionStructure extends FrmUnitTest {

	/**
	 * @var int
	 */
	private $form_id;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->form_id = $this->create_empty_form();
	}

	/**
	 * @return void
	 */
	public function test_builder_order_is_unchanged_without_repeaters() {
		$ids = array(
			$this->add_field( 'text', 1 ),
			$this->add_field( 'divider', 2 ),
			$this->add_field( 'text', 3 ),
			$this->add_field( 'end_divider', 4 ),
			$this->add_field( 'text', 5 ),
		);

		$this->assertSame( $ids, $this->get_builder_order(), 'A form with no repeater must keep its stored order.' );
	}

	/**
	 * @return void
	 */
	public function test_builder_shows_child_fields_ordered_after_the_end_divider_inside_the_repeater() {
		$child    = $this->create_child_form();
		$before   = $this->add_field( 'text', 1 );
		$repeater = $this->add_repeater( 2, $child );
		$end      = $this->add_field( 'end_divider', 3 );
		// Same orders as a real broken form: the children sort after the end_divider.
		$first  = $this->add_field( 'text', 4, array( 'in_section' => $repeater ), $child );
		$second = $this->add_field( 'text', 5, array( 'in_section' => $repeater ), $child );

		$this->assertSame( array( $before, $repeater, $first, $second, $end ), $this->get_builder_order() );
	}

	/**
	 * @return void
	 */
	public function test_builder_shows_child_fields_ordered_before_the_divider_inside_the_repeater() {
		$child    = $this->create_child_form();
		$orphaned = $this->add_field( 'text', 1, array(), $child );
		$top      = $this->add_field( 'text', 5 );
		$repeater = $this->add_repeater( 10, $child );
		$end      = $this->add_field( 'end_divider', 11 );

		$this->assertSame( array( $top, $repeater, $orphaned, $end ), $this->get_builder_order() );
	}

	/**
	 * @return void
	 */
	public function test_builder_moves_parent_fields_inside_a_repeater_after_it() {
		$child     = $this->create_child_form();
		$repeater  = $this->add_repeater( 1, $child );
		$in_child  = $this->add_field( 'text', 2, array( 'in_section' => $repeater ), $child );
		$in_parent = $this->add_field( 'text', 3, array( 'in_section' => $repeater ) );
		$end       = $this->add_field( 'end_divider', 4 );
		$after     = $this->add_field( 'text', 5 );

		$this->assertSame(
			array( $repeater, $in_child, $end, $in_parent, $after ),
			$this->get_builder_order(),
			'A parent form field is rendered once on the frontend, so the builder must not show it as repeated.'
		);
	}

	/**
	 * @return void
	 */
	public function test_builder_closes_a_repeater_at_a_section_or_page_break_without_an_end_divider() {
		$child     = $this->create_child_form();
		$repeater  = $this->add_repeater( 1, $child );
		$in_parent = $this->add_field( 'text', 2 );
		$break     = $this->add_field( 'break', 3 );
		$after     = $this->add_field( 'text', 4 );
		$in_child  = $this->add_field( 'text', 9, array( 'in_section' => $repeater ), $child );

		$this->assertSame( array( $repeater, $in_child, $in_parent, $break, $after ), $this->get_builder_order() );
	}

	/**
	 * @return void
	 */
	public function test_builder_keeps_each_child_form_with_its_own_repeater() {
		$child_a    = $this->create_child_form();
		$child_b    = $this->create_child_form();
		$repeater_a = $this->add_repeater( 1, $child_a );
		$end_a      = $this->add_field( 'end_divider', 2 );
		$repeater_b = $this->add_repeater( 3, $child_b );
		$end_b      = $this->add_field( 'end_divider', 4 );
		// Swapped orders: each child field sits in the other repeater's window.
		$in_b = $this->add_field( 'text', 2, array( 'in_section' => $repeater_b ), $child_b );
		$in_a = $this->add_field( 'text', 4, array( 'in_section' => $repeater_a ), $child_a );

		$this->assertSame( array( $repeater_a, $in_a, $end_a, $repeater_b, $in_b, $end_b ), $this->get_builder_order() );
	}

	/**
	 * @return void
	 */
	public function test_builder_keeps_fields_of_an_unused_child_form_before_the_submit_button() {
		$unused  = $this->create_child_form();
		$top     = $this->add_field( 'text', 1 );
		$missing = $this->add_repeater( 2, 999999 );
		$orphan  = $this->add_field( 'text', 3, array(), $unused );
		$end     = $this->add_field( 'end_divider', 4 );
		$submit  = $this->add_field( 'submit', 5 );

		$this->assertSame(
			array( $top, $missing, $end, $orphan, $submit ),
			$this->get_builder_order(),
			'Fields no repeater renders stay editable, and a repeater with a missing child form keeps its place.'
		);
	}

	/**
	 * Other builder filters must see the list in the order the builder renders it.
	 *
	 * @return void
	 */
	public function test_builder_sort_runs_before_other_builder_filters() {
		$this->assertSame( 5, has_filter( 'frm_fields_in_form_builder', 'FrmFieldPlacementHelper::sort_for_builder' ) );
	}

	/**
	 * @return void
	 */
	public function test_builder_ignores_a_list_changed_into_another_shape() {
		$fields = array( 'not a field' );
		$this->assertSame( $fields, FrmFieldPlacementHelper::sort_for_builder( $fields, array( 'form' => FrmForm::getOne( $this->form_id ) ) ) );
		$this->assertSame( $fields, FrmFieldPlacementHelper::sort_for_builder( $fields, array() ) );
	}

	/**
	 * @return void
	 */
	public function test_repeater_for_child_form_is_found_from_form_select() {
		$child    = $this->create_child_form();
		$repeater = $this->add_repeater( 1, $child );

		$this->assertSame( $repeater, (int) FrmFieldPlacementHelper::get_repeater_for_child_form( $child )->id );
		$this->assertFalse( FrmFieldPlacementHelper::get_repeater_for_child_form( $this->create_child_form() ), 'A child form no repeater uses has no repeater.' );
		$this->assertFalse( FrmFieldPlacementHelper::get_repeater_for_child_form( $this->form_id ), 'A top-level form is not a child form.' );
		$this->assertFalse( FrmFieldPlacementHelper::get_repeater_for_child_form( 0 ) );
	}

	/**
	 * @return void
	 */
	public function test_container_parts_find_the_end_field_and_children() {
		$section = $this->add_field( 'divider', 1 );
		$inside  = $this->add_field( 'text', 2, array( 'in_section' => $section ) );
		$end     = $this->add_field( 'end_divider', 3 );
		$this->add_field( 'text', 4 );

		$parts = FrmFieldPlacementHelper::get_container_parts( FrmField::getOne( $section ) );

		$this->assertSame( $end, (int) $parts['end']->id );
		$this->assertSame( array( $inside ), array_map( 'intval', wp_list_pluck( $parts['children'], 'id' ) ) );
	}

	/**
	 * A section closed by the next section has no end field to delete.
	 *
	 * @return void
	 */
	public function test_container_parts_skip_an_implicit_close() {
		$section = $this->add_field( 'divider', 1 );
		$this->add_field( 'divider', 2 );

		$this->assertFalse( FrmFieldPlacementHelper::get_container_parts( FrmField::getOne( $section ) )['end'] );
	}

	/**
	 * @return int[]
	 */
	private function get_builder_order() {
		$fields = FrmField::get_all_for_form( $this->form_id );
		/** This filter is documented in classes/controllers/FrmFormsController.php */
		$fields = apply_filters( 'frm_fields_in_form_builder', $fields, array( 'form' => FrmForm::getOne( $this->form_id ) ) );

		return array_map( 'intval', wp_list_pluck( $fields, 'id' ) );
	}

	/**
	 * @return int
	 */
	private function create_child_form() {
		return $this->create_empty_form( $this->form_id );
	}

	/**
	 * The form factory adds a text field to every form, which these tests must not have.
	 *
	 * @param int $parent_form_id Parent form, 0 for a top-level form.
	 *
	 * @return int
	 */
	private function create_empty_form( $parent_form_id = 0 ) {
		return (int) FrmForm::create(
			array(
				'form_key'       => '',
				'name'           => 'Section structure',
				'description'    => '',
				'parent_form_id' => $parent_form_id,
			)
		);
	}

	/**
	 * @param int $order         Field order.
	 * @param int $child_form_id Child form for form_select.
	 *
	 * @return int
	 */
	private function add_repeater( $order, $child_form_id ) {
		return $this->add_field(
			'divider',
			$order,
			array(
				'repeat'      => 1,
				'form_select' => $child_form_id,
			)
		);
	}

	/**
	 * Store a field exactly as given, the way legacy data and imports can hold it.
	 *
	 * @param string   $type    Field type.
	 * @param int      $order   Field order.
	 * @param array    $options Field options to merge over the defaults.
	 * @param int|null $form_id Form, the test form by default.
	 *
	 * @return int
	 */
	private function add_field( $type, $order, $options = array(), $form_id = null ) {
		$values                  = FrmFieldsHelper::setup_new_vars( $type, $form_id ?? $this->form_id );
		$values['field_order']   = $order;
		$values['field_options'] = array_merge( $values['field_options'], $options );
		$field_id                = FrmField::create( $values );

		$this->assertNotFalse( $field_id, 'The ' . $type . ' test field should be created.' );

		return (int) $field_id;
	}
}
