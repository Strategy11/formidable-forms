<?php

/**
 * @group entries
 */
class test_FrmEntriesHelper extends FrmUnitTest {

	/**
	 * @covers FrmEntriesHelper::display_value
	 */
	public function test_display_value() {
		$value         = serialize( array( 'Option 1', 'Option 2' ) );
		$field         = $this->factory->field->create_and_get(
			array(
				'form_id' => $this->factory->form->create(),
				'type'    => 'checkbox',
			)
		);
		$atts          = array();
		$display_value = FrmEntriesHelper::display_value( $value, $field, $atts );
		$this->assertIsString( $display_value );
		$this->assertEquals( 'Option 1, Option 2', $display_value );
	}
}
