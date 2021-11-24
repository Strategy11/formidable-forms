<?php

/**
 * @group fields
 */
class test_FrmFieldCombo extends FrmUnitTest {

	protected function get_combo_field_without_sub_field_options() {
		$combo_field = new FrmFieldCombo();

		$this->run_private_method(
			array( $combo_field, 'register_sub_fields' ),
			array(
				array(
					'first_child'  => array(
						'type'    => 'text',
						'label'   => 'First child',
						'options' => array(),
					),
					'second_child' => array(
						'type'    => 'text',
						'label'   => 'Second child',
						'options' => array(),
					),
					'third_child'  => array(
						'type'    => 'text',
						'label'   => 'Third child',
						'options' => array(),
					),
					'forth_child'  => array(
						'type'    => 'text',
						'label'   => 'Forth child',
						'options' => array(),
					),
				),
			)
		);

		return $combo_field;
	}

	protected function get_combo_field_with_sub_field_options() {
		$combo_field = new FrmFieldCombo();

		$this->run_private_method(
			array( $combo_field, 'register_sub_fields' ),
			array(
				array(
					'name'  => array(
						'type'    => 'text',
						'label'   => 'Name',
						'options' => array(
							'desc',
						),
					),
					'email' => array(
						'type'    => 'email',
						'label'   => 'Email',
						'options' => array(
							'placeholder',
							'default_value',
						),
					),
					'dob'   => array(
						'type'    => 'date',
						'label'   => 'Date of Birth',
						'options' => array(
							'default_value',
							'desc',
							array(
								'name' => 'custom_opt',
								'type' => 'text',
							),
						),
					),
				),
			)
		);

		return $combo_field;
	}

	public function test_register_sub_fields() {
		$combo_field = new FrmFieldCombo();

		$this->assertEquals( $this->get_private_property( $combo_field, 'sub_fields' ), array() );

		$this->run_private_method(
			array( $combo_field, 'register_sub_fields' ),
			array(
				array(
					'first'  => 'first label',
					'second' => 'second label',
				),
			)
		);

		$this->assertEquals(
			$this->get_private_property( $combo_field, 'sub_fields' ),
			array(
				'first'  => array(
					'name'            => 'first',
					'label'           => 'first label',
					'type'            => 'text',
					'classes'         => '',
					'wrapper_classes' => '',
					'optional'        => false,
					'options'         => array(
						'default_value',
						'placeholder',
						'desc',
					),
					'atts'            => array(),
				),
				'second' => array(
					'name'            => 'second',
					'label'           => 'second label',
					'type'            => 'text',
					'classes'         => '',
					'wrapper_classes' => '',
					'optional'        => false,
					'options'         => array(
						'default_value',
						'placeholder',
						'desc',
					),
					'atts'            => array(),
				),
			)
		);

		$this->set_private_property( $combo_field, 'sub_fields', array() );

		$this->run_private_method(
			array( $combo_field, 'register_sub_fields' ),
			array(
				array(
					'first'  => array(),
					'second' => 'second label',
					'third'  => array(
						'name'    => 'another name',
						'options' => array(),
					),
					'forth'  => true,
				),
			)
		);

		$this->assertEquals(
			$this->get_private_property( $combo_field, 'sub_fields' ),
			array(
				'second' => array(
					'name'            => 'second',
					'label'           => 'second label',
					'type'            => 'text',
					'classes'         => '',
					'wrapper_classes' => '',
					'optional'        => false,
					'options'         => array(
						'default_value',
						'placeholder',
						'desc',
					),
					'atts'            => array(),
				),
				'third'  => array(
					'name'            => 'third',
					'label'           => '',
					'type'            => 'text',
					'classes'         => '',
					'wrapper_classes' => '',
					'optional'        => false,
					'options'         => array(),
					'atts'            => array(),
				),
			)
		);
	}

	public function test_extra_field_opts() {
		$combo_field = $this->get_combo_field_without_sub_field_options();

		$this->assertEquals( $this->run_private_method( array( $combo_field, 'extra_field_opts' ) ), array() );

		$combo_field = $this->get_combo_field_with_sub_field_options();

		$this->assertEquals(
			$this->run_private_method( array( $combo_field, 'extra_field_opts' ) ),
			array(
				'name_desc'         => '',
				'email_placeholder' => '',
				'dob_desc'          => '',
				'dob_custom_opt'    => '',
			)
		);
	}

	public function test_get_default_value() {
		$combo_field = $this->get_combo_field_without_sub_field_options();

		$this->assertEquals(
			$this->run_private_method( array( $combo_field, 'get_default_value' ) ),
			array(
				'first_child'  => '',
				'second_child' => '',
				'third_child'  => '',
				'forth_child'  => '',
			)
		);

		$combo_field = $this->get_combo_field_with_sub_field_options();

		$this->assertEquals(
			$this->run_private_method( array( $combo_field, 'get_default_value' ) ),
			array(
				'name'  => '',
				'email' => '',
				'dob'   => '',
			)
		);
	}

	/**
	 * @covers FrmFieldCombo::print_input_atts
	 */
	public function test_print_input_atts() {
		$combo_field = new FrmFieldCombo();

		$field = (array) $this->factory->field->create_and_get(
			array(
				'type'    => 'text',
				'form_id' => 1,
			)
		);

		$field['placeholder'] = array(
			'first'  => 'First placeholder',
			'second' => 'Second placeholder',
			'third'  => 'Third placeholder',
		);

		$sub_field = array(
			'name'    => 'first',
			'label'   => 'First',
			'type'    => 'text',
			'atts'    => array(
				'maxlength' => 10,
				'data-attr' => 'custom-attr',
			),
			'classes' => 'frm-custom-class',
			'options' => array(
				'placeholder',
			),
		);

		FrmHooksController::load_form_hooks();

		ob_start();
		$this->run_private_method(
			array( $combo_field, 'print_input_atts' ),
			array( compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, ' placeholder="First placeholder" class="frm-custom-class"  maxlength="10" data-attr="custom-attr" ' );

		$sub_field = array(
			'name'     => 'second',
			'label'    => 'Second',
			'type'     => 'text',
			'classes'  => array(
				'frm-class1',
				'frm-class2',
			),
			'optional' => true,
			'options'  => array(),
		);

		ob_start();
		$this->run_private_method(
			array( $combo_field, 'print_input_atts' ),
			array( compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, ' class="frm-class1 frm-class2 frm_optional"  ' );

		$sub_field = array(
			'name'    => 'forth',
			'label'   => 'Forth',
			'type'    => 'text',
			'options' => array(),
		);

		ob_start();
		$this->run_private_method(
			array( $combo_field, 'print_input_atts' ),
			array( compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, '   ' );
	}

	public function test_get_export_headings() {
		$combo_field = $this->get_combo_field_without_sub_field_options();
		$field       = (array) $this->factory->field->create_and_get(
			array(
				'type'    => 'name',
				'form_id' => 1,
			)
		);

		$field_id   = $field['id'];
		$field_name = $field['name'];
		$field_key  = $field['field_key'];

		$this->set_private_property( $combo_field, 'field', $field );

		$this->assertEquals(
			array(
				$field_id . '_first_child'  => $field_name . ' (' . $field_key . ') - First child',
				$field_id . '_second_child' => $field_name . ' (' . $field_key . ') - Second child',
				$field_id . '_third_child'  => $field_name . ' (' . $field_key . ') - Third child',
				$field_id . '_forth_child'  => $field_name . ' (' . $field_key . ') - Forth child',
			),
			$combo_field->get_export_headings()
		);
	}
}
