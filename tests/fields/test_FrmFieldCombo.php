<?php

/**
 * @group fields
 */
class test_FrmFieldCombo extends FrmUnitTest {

	public function test_extra_field_opts() {
		$test_combo = new TestFrmFieldComboWithoutSubFieldOptions();

		$this->assertEquals( $this->run_private_method( array( $test_combo, 'extra_field_opts' ) ), array() );

		$test_combo = new TestFrmFieldComboWithSubFieldOptions();

		$this->assertEquals(
			$this->run_private_method( array( $test_combo, 'extra_field_opts' ) ),
			array(
				'name_desc'         => '',
				'email_placeholder' => '',
				'dob_desc'          => '',
				'dob_custom_opt'    => '',
			)
		);
	}

	public function test_get_default_value() {
		$test_combo = new TestFrmFieldComboWithoutSubFieldOptions();

		$this->assertEquals(
			$this->run_private_method( array( $test_combo, 'get_default_value' ) ),
			array(
				'first_child'  => '',
				'second_child' => '',
				'third_child'  => '',
				'forth_child'  => '',
			)
		);

		$test_combo = new TestFrmFieldComboWithSubFieldOptions();

		$this->assertEquals(
			$this->run_private_method( array( $test_combo, 'get_default_value' ) ),
			array(
				'name'  => '',
				'email' => '',
				'dob'   => '',
			)
		);
	}

	public function test_print_input_atts() {
		$test_combo = $this->getMockForAbstractClass( FrmFieldCombo::class );

		$this->assertTrue( $test_combo instanceof FrmFieldCombo );

		$field = (array) $this->factory->field->create_and_get(
			array(
				'type'    => 'text',
				'form_id' => 1,
			)
		);

		$field['field_options']['first_placeholder']  = 'First placeholder';
		$field['field_options']['second_placeholder'] = 'Second placeholder';
		$field['field_options']['third_placeholder']  = 'Third placeholder';

		$sub_field = array(
			'name'    => 'first',
			'label'   => 'First',
			'type'    => 'text',
			'atts'    => array(
				'maxlength' => 10,
				'data-attr' => 'custom-attr',
			),
			'classes' => 'frm-custom-class',
		);

		ob_start();
		$this->run_private_method(
			array( $test_combo, 'print_input_atts' ),
			array( 'args' => compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, 'placeholder="First placeholder" class="frm-custom-class" maxlength="10" data-attr="custom-attr"' );

		$sub_field = array(
			'name'    => 'second',
			'label'   => 'Second',
			'type'    => 'text',
			'classes' => array(
				'frm-class1',
				'frm-class2',
			),
			'optional' => true,
		);

		ob_start();
		$this->run_private_method(
			array( $test_combo, 'print_input_atts' ),
			array( 'args' => compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, 'placeholder="Second placeholder" class="frm-class1 frm-class2 frm_optional"' );

		$sub_field = array(
			'name'    => 'forth',
			'label'   => 'Forth',
			'type'    => 'text',
		);

		ob_start();
		$this->run_private_method(
			array( $test_combo, 'print_input_atts' ),
			array( 'args' => compact( 'field', 'sub_field' ) )
		);
		$atts = ob_get_clean();

		$this->assertEquals( $atts, '' );
	}
}

class TestFrmFieldComboWithoutSubFieldOptions extends FrmFieldCombo {

	protected function get_sub_fields() {
		return array(
			'first_child'  => array(
				'type'     => 'text',
				'label'    => 'First child',
			),
			'second_child' => array(
				'type'  => 'text',
				'label' => 'Second child',
			),
			'third_child'  => array(
				'type'  => 'text',
				'label' => 'Third child',
			),
			'forth_child'  => array(
				'type'  => 'text',
				'label' => 'Forth child',
			),
		);
	}
}

class TestFrmFieldComboWithSubFieldOptions extends FrmFieldCombo {

	protected function get_sub_fields() {
		return array(
			'name' => array(
				'type' => 'text',
				'label' => 'Name',
				'options' => array(
					'desc',
				),
			),
			'email' => array(
				'type' => 'email',
				'label' => 'Email',
				'options' => array(
					'placeholder',
					'default_value',
				),
			),
			'dob' => array(
				'type' => 'date',
				'label' => 'Date of Birth',
				'options' => array(
					'default_value',
					'desc',
					array(
						'name' => 'custom_opt',
						'type' => 'text',
					),
				),
			),
		);
	}
}
