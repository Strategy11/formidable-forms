<?php

/**
 * @group fields
 */
class test_FrmFieldGridHelper extends FrmUnitTest {

	private $form_id;

	private $helper;

	private $section_helper;

	/**
	 * @covers FrmFieldGridHelper::get_size_of_class
	 */
	public function test_get_size_of_class() {
		$this->assertSame( 1, $this->get_size_of_class( 'frm1' ) );
		$this->assertSame( 6, $this->get_size_of_class( 'frm6' ) );
		$this->assertSame( 8, $this->get_size_of_class( 'frm8' ) );
		$this->assertSame( 10, $this->get_size_of_class( 'frm10' ) );
		$this->assertSame( 12, $this->get_size_of_class( 'frm12' ) );
		$this->assertSame( 2, $this->get_size_of_class( 'frm_sixth' ) );
		$this->assertSame( 3, $this->get_size_of_class( 'frm_fourth' ) );
		$this->assertSame( 4, $this->get_size_of_class( 'frm_third' ) );
		$this->assertSame( 6, $this->get_size_of_class( 'frm_half' ) );
		$this->assertSame( 12, $this->get_size_of_class( 'frm_full' ) );
	}

	/**
	 * @param string $class
	 */
	private function get_size_of_class( $class ) {
		return $this->run_private_method( array( 'FrmFieldGridHelper', 'get_size_of_class' ), array( $class ) );
	}

	public function test_basic_grouping() {
		$this->form_id       = $this->factory->form->create();
		$half_width_field    = $this->create_field_with_classes( 'text', 'frm_half' );
		$quarter_width_field = $this->create_field_with_classes( 'text', 'frm_fourth' );

		// prevent any html from rendering during the unit test (the grid helper adds wrappers around fields).
		ob_start();

		$this->helper = new FrmFieldGridHelper();
		$this->helper->set_field( $half_width_field );

		$this->sync_current_field_once( 6 );
		$this->sync_current_field_once( 0, 'The list should automatically close once two frm_half elements are added together.' );

		$this->helper->set_field( $quarter_width_field );
		$this->sync_current_field_once( 3 );

		$this->helper->set_field( $half_width_field );
		$this->sync_current_field_once( 9 );

		$this->helper->set_field( $quarter_width_field );
		$this->sync_current_field_once( 0 );

		ob_end_clean();
	}

	/**
	 * @param false|int $assert_size
	 * @param string $assert_message
	 */
	private function sync_current_field_once( $assert_size = false, $assert_message = '' ) {
		$this->helper->maybe_begin_field_wrapper();
		$this->helper->sync_list_size();

		if ( false !== $assert_size ) {
			$this->assert_current_list_size( $assert_size, $assert_message );
		}
	}

	private function assert_current_list_size( $expected, $message = '' ) {
		$this->assertSame( $expected, $this->get_private_property( $this->helper, 'current_list_size' ), $message );
	}

	/**
	 * @param string $type
	 * @param string $classes
	 */
	private function create_field_with_classes( $type, $classes = '' ) {
		return $this->factory->field->create_and_get(
			array(
				'form_id'       => $this->form_id,
				'type'          => $type,
				'field_options' => array(
					'classes' => $classes,
				),
			)
		);
	}

	public function test_with_sections() {
		$this->form_id    = $this->factory->form->create();
		$half_width_field = $this->create_field_with_classes( 'text', 'frm_half' );
		$this->create_field_with_classes( 'text', 'frm_fourth' );
		$half_width_section    = $this->create_field_with_classes( 'divider', 'frm_half' );
		$quarter_width_section = $this->create_field_with_classes( 'divider', 'frm_fourth' );
		$end_divider           = $this->create_field_with_classes( 'end_divider' );

		ob_start();

		$this->helper = new FrmFieldGridHelper();
		$this->helper->set_field( $half_width_section );

		$this->sync_current_field_once( 0 );
		$this->section_helper = $this->get_private_property( $this->helper, 'section_helper' );
		$this->assertInstanceOf( \FrmFieldGridHelper::class, $this->section_helper );
		$this->assert_section_helper_size( 0 );

		$this->helper->set_field( $half_width_field );
		$this->sync_current_field_once();
		$this->assert_section_helper_size( 6 );

		$this->sync_current_field_once();
		$this->assert_section_helper_size( 0 );

		$this->helper->set_field( $end_divider );
		$this->sync_current_field_once( 6 );

		$this->section_helper = $this->get_private_property( $this->helper, 'section_helper' );
		$this->assertEmpty( $this->section_helper );

		$this->helper->set_field( $quarter_width_section );
		$this->sync_current_field_once( 6 );
		$this->section_helper = $this->get_private_property( $this->helper, 'section_helper' );
		$this->assertInstanceOf( \FrmFieldGridHelper::class, $this->section_helper );
		$this->assert_section_helper_size( 0 );

		$this->helper->set_field( $half_width_field );
		$this->sync_current_field_once();
		$this->assert_section_helper_size( 6 );

		$this->helper->set_field( $end_divider );
		$this->sync_current_field_once( 9 );

		ob_end_clean();
	}

	/**
	 * @param int $expected
	 */
	private function assert_section_helper_size( $expected ) {
		$this->assertSame( $expected, $this->get_private_property( $this->section_helper, 'current_list_size' ) );
	}

	public function test_frm_first() {
		$this->form_id              = $this->factory->form->create();
		$half_width_frm_first_field = $this->create_field_with_classes( 'text', 'frm_half frm_first' );

		ob_start();

		$this->helper = new FrmFieldGridHelper();
		$this->helper->set_field( $half_width_frm_first_field );
		$this->sync_current_field_once( 6 );
		$this->sync_current_field_once( 6 );

		ob_end_clean();
	}

	/**
	 * @covers FrmFieldGridHelper::get_size_of_class
	 */
	public function test_get_size_of_class_handles_every_named_class() {
		$this->assertSame( 2, $this->get_size_of_class( 'frm_sixth' ) );
		$this->assertSame( 3, $this->get_size_of_class( 'frm_fourth' ) );
		$this->assertSame( 4, $this->get_size_of_class( 'frm_third' ) );
		$this->assertSame( 6, $this->get_size_of_class( 'frm_half' ) );
		$this->assertSame( 8, $this->get_size_of_class( 'frm_two_thirds' ) );
		$this->assertSame( 9, $this->get_size_of_class( 'frm_three_fourths' ) );
		$this->assertSame( 12, $this->get_size_of_class( 'frm_full' ) );
	}

	/**
	 * Anything the helper cannot read a width from takes up a full row.
	 *
	 * @covers FrmFieldGridHelper::get_size_of_class
	 */
	public function test_get_size_of_class_falls_back_to_full_width() {
		$this->assertSame( 12, $this->get_size_of_class( '' ), 'A field with no layout class should fill the row.' );
		$this->assertSame( 12, $this->get_size_of_class( 'frm_alignright' ), 'A frm class with a non numeric suffix is not a width.' );
		$this->assertSame( 12, $this->get_size_of_class( 'my_custom_class' ) );
		$this->assertSame( 12, $this->get_size_of_class( 'frm' ) );
	}

	/**
	 * @covers FrmFieldGridHelper::get_field_layout_class
	 */
	public function test_get_field_layout_class_reads_the_width_from_the_field_classes() {
		$this->assertSame( 'frm_half', $this->get_layout_class_for( 'frm_half' ) );
		$this->assertSame( 'frm4', $this->get_layout_class_for( 'frm4' ) );
		$this->assertSame( 'frm_half', $this->get_layout_class_for( 'frm_half frm_first' ), 'frm_first sits alongside the width class rather than replacing it.' );
		$this->assertSame( 'frm_third', $this->get_layout_class_for( 'my_custom_class frm_third' ), 'Unrelated classes should be ignored.' );
	}

	/**
	 * @covers FrmFieldGridHelper::get_field_layout_class
	 */
	public function test_get_field_layout_class_returns_an_empty_string_without_a_width() {
		$this->assertSame( '', $this->get_layout_class_for( '' ) );
		$this->assertSame( '', $this->get_layout_class_for( 'my_custom_class' ) );
	}

	/**
	 * A field carrying two width classes resolves in the order the helper lists them, not the order they appear on the field.
	 *
	 * @covers FrmFieldGridHelper::get_field_layout_class
	 */
	public function test_get_field_layout_class_uses_the_helper_class_order() {
		$this->assertSame( 'frm_half', $this->get_layout_class_for( 'frm6 frm_half' ) );
		$this->assertSame( 'frm_half', $this->get_layout_class_for( 'frm_half frm6' ) );
	}

	/**
	 * @covers FrmFieldGridHelper::get_field_layout_class
	 */
	public function test_get_field_layout_class_flags_frm_first() {
		$this->assertTrue( $this->is_frm_first_for( 'frm_half frm_first' ) );
		$this->assertFalse( $this->is_frm_first_for( 'frm_half' ) );
	}

	/**
	 * @param string $classes
	 *
	 * @return string
	 */
	private function get_layout_class_for( $classes ) {
		return $this->build_helper_for_classes( $classes )->get_field_layout_class();
	}

	/**
	 * @param string $classes
	 *
	 * @return bool
	 */
	private function is_frm_first_for( $classes ) {
		$helper = $this->build_helper_for_classes( $classes );
		$helper->get_field_layout_class();

		return $this->get_private_property( $helper, 'is_frm_first' );
	}

	/**
	 * @param string $classes
	 *
	 * @return FrmFieldGridHelper
	 */
	private function build_helper_for_classes( $classes ) {
		$this->form_id = $this->factory->form->create();
		$helper        = new FrmFieldGridHelper();
		$helper->set_field( $this->create_field_with_classes( 'text', $classes ) );

		return $helper;
	}

	public function test_fields_without_a_width_each_take_a_whole_row() {
		$layout = $this->get_grid_layout(
			array(
				$this->text_spec(),
				$this->text_spec(),
			)
		);

		$this->assertSame( '[1][2]', $layout );
	}

	public function test_fields_share_a_row_while_there_is_room() {
		$this->assertSame( '[1,2]', $this->get_grid_layout( array( $this->text_spec( 'frm_half' ), $this->text_spec( 'frm_half' ) ) ) );
		$this->assertSame( '[1,2,3]', $this->get_grid_layout( array( $this->text_spec( 'frm_third' ), $this->text_spec( 'frm_third' ), $this->text_spec( 'frm_third' ) ) ) );
		$this->assertSame(
			'[1,2,3,4,5,6]',
			$this->get_grid_layout(
				array(
					$this->text_spec( 'frm_sixth' ),
					$this->text_spec( 'frm_sixth' ),
					$this->text_spec( 'frm_sixth' ),
					$this->text_spec( 'frm_sixth' ),
					$this->text_spec( 'frm_sixth' ),
					$this->text_spec( 'frm_sixth' ),
				)
			)
		);
	}

	public function test_a_row_closes_once_it_reaches_twelve_columns() {
		$layout = $this->get_grid_layout(
			array(
				$this->text_spec( 'frm_half' ),
				$this->text_spec( 'frm_fourth' ),
				$this->text_spec( 'frm_half' ),
				$this->text_spec( 'frm_fourth' ),
			)
		);

		$this->assertSame( '[1,2][3,4]', $layout, 'A half and a fourth leave room for another half, and the row closes at twelve.' );
	}

	public function test_a_field_too_wide_for_the_row_starts_a_new_one() {
		$layout = $this->get_grid_layout(
			array(
				$this->text_spec( 'frm_two_thirds' ),
				$this->text_spec( 'frm_half' ),
			)
		);

		$this->assertSame( '[1][2]', $layout, 'Eight plus six overflows the twelve column grid.' );
	}

	public function test_frm_first_starts_a_new_row_even_when_the_field_fits() {
		$layout = $this->get_grid_layout(
			array(
				$this->text_spec( 'frm_half' ),
				$this->text_spec( 'frm_half frm_first' ),
			)
		);

		$this->assertSame( '[1][2]', $layout, 'frm_first marks a deliberate row start, so the fitting field still breaks away.' );
	}

	public function test_a_section_nests_a_row_for_its_own_fields() {
		$layout = $this->get_grid_layout(
			array(
				$this->section_spec(),
				$this->text_spec(),
				$this->section_end_spec(),
			)
		);

		$this->assertSame( '[1[2]3]', $layout, 'The section and its end marker share the outer row, and the field inside gets a nested row.' );
	}

	public function test_fields_inside_a_section_pack_into_nested_rows() {
		$layout = $this->get_grid_layout(
			array(
				$this->section_spec(),
				$this->text_spec( 'frm_half' ),
				$this->text_spec( 'frm_half' ),
				$this->text_spec( 'frm_half' ),
				$this->section_end_spec(),
			)
		);

		$this->assertSame( '[1[2,3][4]5]', $layout, 'Two halves fill the first nested row, so the third starts another.' );
	}

	public function test_the_outer_row_resumes_after_a_section_closes() {
		$layout = $this->get_grid_layout(
			array(
				$this->section_spec( 'frm_half' ),
				$this->text_spec(),
				$this->section_end_spec(),
				$this->text_spec( 'frm_fourth' ),
			)
		);

		$this->assertSame( '[1[2]3,4]', $layout, 'A half width section leaves six columns, so a fourth still fits beside it.' );
	}

	public function test_a_full_width_section_closes_the_row_behind_it() {
		$layout = $this->get_grid_layout(
			array(
				$this->section_spec(),
				$this->text_spec(),
				$this->section_end_spec(),
				$this->text_spec( 'frm_half' ),
				$this->text_spec( 'frm_half' ),
			)
		);

		$this->assertSame( '[1[2]3][4,5]', $layout );
	}

	public function test_two_sections_can_share_a_row() {
		$layout = $this->get_grid_layout(
			array(
				$this->section_spec( 'frm_first frm6' ),
				$this->text_spec(),
				$this->section_end_spec(),
				$this->section_spec( 'frm6' ),
				$this->text_spec(),
				$this->section_end_spec(),
			)
		);

		$this->assertSame( '[1[2]3,4[5]6]', $layout, 'Two half width sections add up to twelve columns.' );
	}

	/**
	 * The section helper is what wraps the fields that belong to a section, so it has to survive
	 * the wrapper bookkeeping that runs for the section field itself.
	 *
	 * Note that close_field_wrapper() calls maybe_close_section_helper(), so any change that closes
	 * the open row while handling a section field discards the helper set_field() just created.
	 */
	public function test_a_section_keeps_its_helper_after_the_row_bookkeeping_runs() {
		$this->form_id = $this->factory->form->create();
		$narrow_field  = $this->create_field_with_classes( 'text', 'frm4' );
		$section       = $this->create_field_with_classes( 'divider' );

		ob_start();

		$helper = new FrmFieldGridHelper();

		$helper->set_field( $narrow_field );
		$helper->maybe_begin_field_wrapper();
		$helper->sync_list_size();

		$helper->set_field( $section );
		$this->assertInstanceOf(
			\FrmFieldGridHelper::class,
			$this->get_private_property( $helper, 'section_helper' ),
			'set_field should open a section helper for a section field.'
		);

		$helper->maybe_begin_field_wrapper();

		ob_end_clean();

		$this->assertInstanceOf(
			\FrmFieldGridHelper::class,
			$this->get_private_property( $helper, 'section_helper' ),
			'The section helper should still be open once the row bookkeeping has run.'
		);
	}

	/**
	 * Documents the behaviour reported in https://github.com/Strategy11/formidable-pro/issues/3820.
	 *
	 * A section is measured through section_size rather than active_field_size, and
	 * should_first_close_the_active_field_wrapper() returns early while a section helper
	 * exists, so the width check never runs for the section itself. The narrow field and the
	 * full width section end up in one row even though they add up to sixteen columns.
	 *
	 * The assertion below records what the helper does today. Closing the row before the section
	 * is only half the fix: close_field_wrapper() also drops the section helper, which
	 * test_a_section_keeps_its_helper_after_the_row_bookkeeping_runs covers.
	 */
	public function test_a_section_currently_joins_a_row_it_cannot_fit_in() {
		$layout = $this->get_grid_layout(
			array(
				$this->text_spec( 'frm4' ),
				$this->section_spec(),
				$this->text_spec(),
				$this->section_end_spec(),
			)
		);

		$this->assertSame( '[1,2[3]4]', $layout, 'Four columns plus a full width section overflow the row, but the row is not closed first.' );
	}

	/**
	 * @param string $classes
	 *
	 * @return array<string,string>
	 */
	private function text_spec( $classes = '' ) {
		return array(
			'type'    => 'text',
			'classes' => $classes,
		);
	}

	/**
	 * @param string $classes
	 *
	 * @return array<string,string>
	 */
	private function section_spec( $classes = '' ) {
		return array(
			'type'    => 'divider',
			'classes' => $classes,
		);
	}

	/**
	 * @return array<string,string>
	 */
	private function section_end_spec() {
		return array(
			'type'    => 'end_divider',
			'classes' => '',
		);
	}

	/**
	 * Runs fields through the grid helper the way the form builder does, then describes the rows it produced.
	 *
	 * Each row wrapper shows as a pair of square brackets and each field as its position in
	 * $specs, so '[1,2][3]' means the first two fields shared a row and the third started a new
	 * one. A section's nested row appears inside its parent, as in '[1[2]3]'.
	 *
	 * This describes the wrappers the grid helper itself writes. In the builder a section field
	 * also renders its own container around those wrappers, so the finished markup nests more
	 * deeply than the string here shows. Which fields share a top level row is the same either way.
	 *
	 * @param array<array<string,string>> $specs Field descriptions, each with a 'type' and a 'classes' key.
	 *
	 * @return string
	 */
	private function get_grid_layout( $specs ) {
		$this->form_id = $this->factory->form->create();
		$fields        = array();

		foreach ( $specs as $spec ) {
			$fields[] = $this->create_field_with_classes( $spec['type'], $spec['classes'] );
		}

		$helper   = new FrmFieldGridHelper();
		$position = 0;

		ob_start();

		foreach ( $fields as $field ) {
			++$position;
			$helper->set_field( $field );
			$helper->maybe_begin_field_wrapper();
			// Stands in for the FrmFieldsController::load_single_field call the builder makes here.
			echo '<i>' . intval( $position ) . '</i>';
			$helper->sync_list_size();
		}

		$helper->force_close_field_wrapper();

		return $this->describe_grid( ob_get_clean() );
	}

	/**
	 * @param string $html Markup captured from the grid helper.
	 *
	 * @return string
	 */
	private function describe_grid( $html ) {
		preg_match_all( '/<li class="frm_field_box"><ul[^>]*>|<\/ul><\/li>|<i>(\d+)<\/i>/', $html, $matches, PREG_SET_ORDER );

		$layout        = '';
		$after_a_field = false;

		foreach ( $matches as $match ) {
			if ( '</ul></li>' === $match[0] ) {
				$layout       .= ']';
				$after_a_field = false;
				continue;
			}

			if ( ! isset( $match[1] ) ) {
				$layout       .= '[';
				$after_a_field = false;
				continue;
			}

			if ( $after_a_field ) {
				$layout .= ',';
			}

			$layout       .= $match[1];
			$after_a_field = true;
		}

		return $layout;
	}
}
