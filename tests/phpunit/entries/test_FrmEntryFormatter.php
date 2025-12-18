<?php

/**
 * @group entries
 */
class test_FrmEntryFormatter extends FrmUnitTest {

	public $factory;
	private $formatter;

	/**
	 * @covers FrmEntryFormatter::flatten_array
	 */
	public function test_flatten_array() {
		$values = array( 'Option 1', 'Option 2', 'Option 3' );

		$form       = $this->factory->form->create_and_get();
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry_id   = $this->factory->entry->create( $entry_data );
		$atts       = array(
			'id'     => $entry_id,
			'format' => 'text',
		);

		$this->formatter = new FrmEntryFormatter( $atts );
		$this->assertEquals( 'Option 1, Option 2, Option 3', $this->flatten_array( $values ) );

		$atts['array_separator'] = '<br/>';
		$this->formatter         = new FrmEntryFormatter( $atts );
		$this->assertEquals( 'Option 1<br/>Option 2<br/>Option 3', $this->flatten_array( $values ) );
	}

	private function flatten_array( $value ) {
		return $this->run_private_method( array( $this->formatter, 'flatten_array' ), array( $value ) );
	}
}
