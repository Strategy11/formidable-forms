<?php
/**
 * @group fields
 */
class test_FrmFieldFormHtml extends FrmUnitTest {
	/**
	 * @covers FrmFieldFormHtml::add_multiple_input_attributes
	 */
	public function test_add_multiple_input_attributes() {
		$form_id             = $this->factory->form->create();
		$form                = FrmForm::getOne( $form_id );
		$multiple_input_html = '<div class="frm_opt_container" aria-labelledby="field_[key]_label" role="group">[input]</div>';

		$valid_field_types = array(
			array(
				'type' => 'radio',
			),
			array(
				'type' => 'checkbox',
			),
			array(
				'type' => 'scale',
			),
			array(
				'type'      => 'data',
				'data_type' => 'radio',
			),
			array(
				'type'      => 'data',
				'data_type' => 'checkbox',
			),
			array(
				'type'      => 'product',
				'data_type' => 'radio',
			),
			array(
				'type'      => 'product',
				'data_type' => 'checkbox',
			),
		);

		foreach ( $valid_field_types as $field_type ) {
			$field_args = array(
				'type'     => $field_type['type'],
				'form_id'  => $form_id,
				'required' => '1',
			);

			if ( in_array( $field_type['type'], array( 'data', 'product' ), true ) ) {
				$data_type = $field_type['data_type'];

				$field_args['field_options'] = array(
					'data_type' => $field_type['data_type'],
				);
			}

			$field        = $this->factory->field->create_and_get( $field_args );
			$field_object = FrmFieldFactory::get_field_type( $field_type['type'], $field );

			// Prepare the necessary constructor arguments
			$atts = array(
				'field_obj' => $field_object,
				'field_id'  => $field->id,
				'form'      => $form,
				'html_id'   => 1,
				'html'      => $multiple_input_html,
			);

			$instance = new FrmFieldFormHtml( $atts );

			$this->run_private_method( array( $instance, 'add_multiple_input_attributes' ) );
			$html = $this->get_private_property( $instance, 'html' );

			// Assert html has the correct attributes
			if ( 'checkbox' !== $field_type['type'] && ( ! isset( $field_type['data_type'] ) || 'checkbox' !== $field_type['data_type'] ) ) {
				$this->assertStringContainsString( 'aria-required="true"', $html );
			}

			$expect_radio_group = 'radio' === $field_type['type'] || 'scale' === $field_type['type'] || ( isset( $data_type ) && 'radio' === $data_type );

			if ( $expect_radio_group ) {
				$this->assertStringContainsString( 'role="radiogroup"', $html );
			}
		}
	}
}
