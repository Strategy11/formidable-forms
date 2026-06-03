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
}
