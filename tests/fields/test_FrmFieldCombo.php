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
						'label'   => 'First child',
						'options' => array(),
						'type'    => 'text',
					),
					'forth_child'  => array(
						'label'   => 'Forth child',
						'options' => array(),
						'type'    => 'text',
					),
					'second_child' => array(
						'label'   => 'Second child',
						'options' => array(),
						'type'    => 'text',
					),
					'third_child'  => array(
						'label'   => 'Third child',
						'options' => array(),
						'type'    => 'text',
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
					'dob'   => array(
						'label'   => 'Date of Birth',
						'options' => array(
							'default_value',
							'desc',
							array(
								'name' => 'custom_opt',
								'type' => 'text',
							),
						),
						'type'    => 'date',
					),
					'email' => array(
						'label'   => 'Email',
						'options' => array(
							'placeholder',
							'default_value',
						),
						'type'    => 'email',
					),
					'name'  => array(
						'label'   => 'Name',
						'options' => array(
							'desc',
						),
						'type'    => 'text',
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
					'atts'            => array(),
					'classes'         => '',
					'label'           => 'first label',
					'name'            => 'first',
					'optional'        => false,
					'options'         => array(
						'default_value',
						'placeholder',
						'desc',
					),
					'type'            => 'text',
					'wrapper_classes' => '',
				),
				'second' => array(
					'atts'            => array(),
					'classes'         => '',
					'label'           => 'second label',
					'name'            => 'second',
					'optional'        => false,
					'options'         => array(
						'default_value',
						'placeholder',
						'desc',
					),
					'type'            => 'text',
					'wrapper_classes' => '',
				),
			)
		);

		$this->set_private_property( $combo_field, 'sub_fields', array() );

		$this->run_private_method(
			array( $combo_field, 'register_sub_fields' ),
			array(
				array(
					'first'  => array(),
					'forth'  => true,
					'second' => 'second label',
					'third'  => array(
						'name'    => 'another name',
						'options' => array(),
					),
				),
			)
		);

		$this->assertEquals(
			$this->get_private_property( $combo_field, 'sub_fields' ),
			array(
				'second' => array(
					'atts'            => array(),
					'classes'         => '',
					'label'           => 'second label',
					'name'            => 'second',
					'optional'        => false,
					'options'         => array(
						'default_value',
						'placeholder',
						'desc',
					),
					'type'            => 'text',
					'wrapper_classes' => '',
				),
				'third'  => array(
					'atts'            => array(),
					'classes'         => '',
					'label'           => '',
					'name'            => 'third',
					'optional'        => false,
					'options'         => array(),
					'type'            => 'text',
					'wrapper_classes' => '',
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
				'dob_custom_opt'    => '',
				'dob_desc'          => '',
				'email_placeholder' => '',
				'name_desc'         => '',
			)
		);
	}

	public function test_get_default_value() {
		$combo_field = $this->get_combo_field_without_sub_field_options();

		$this->assertEquals(
			$this->run_private_method( array( $combo_field, 'get_default_value' ) ),
			array(
				'first_child'  => '',
				'forth_child'  => '',
				'second_child' => '',
				'third_child'  => '',
			)
		);

		$combo_field = $this->get_combo_field_with_sub_field_options();

		$this->assertEquals(
			$this->run_private_method( array( $combo_field, 'get_default_value' ) ),
			array(
				'dob'   => '',
				'email' => '',
				'name'  => '',
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
				'form_id' => 1,
				'type'    => 'text',
			)
		);

		$field['placeholder'] = array(
			'first'  => 'First placeholder',
			'second' => 'Second placeholder',
			'third'  => 'Third placeholder',
		);

		$sub_field = array(
			'atts'    => array(
				'data-attr' => 'custom-attr',
				'maxlength' => 10,
			),
			'classes' => 'frm-custom-class',
			'label'   => 'First',
			'name'    => 'first',
			'options' => array(
				'placeholder',
			),
			'type'    => 'text',
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
			'classes'  => array(
				'frm-class1',
				'frm-class2',
			),
			'label'    => 'Second',
			'name'     => 'second',
			'optional' => true,
			'options'  => array(),
			'type'     => 'text',
		);

		ob_start();
		$this->run_private_method(
			array( $combo_field, 'print_input_atts' ),
			array( compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, ' class="frm-class1 frm-class2 frm_optional"  ' );

		$sub_field = array(
			'label'   => 'Forth',
			'name'    => 'forth',
			'options' => array(),
			'type'    => 'text',
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
				'form_id' => 1,
				'type'    => 'name',
			)
		);

		$field_id   = $field['id'];
		$field_name = $field['name'];
		$field_key  = $field['field_key'];

		$this->set_private_property( $combo_field, 'field', $field );

		$this->assertEquals(
			array(
				$field_id . '_first_child'  => $field_name . ' (' . $field_key . ') - First child',
				$field_id . '_forth_child'  => $field_name . ' (' . $field_key . ') - Forth child',
				$field_id . '_second_child' => $field_name . ' (' . $field_key . ') - Second child',
				$field_id . '_third_child'  => $field_name . ' (' . $field_key . ') - Third child',
			),
			$combo_field->get_export_headings()
		);
	}
}
