<?php

/**
 * @group entries
 */
class test_FrmEntryFormatter extends FrmUnitTest {

	private $formatter;

	/**
	 * @covers FrmEntryFormatter::flatten_array
	 */
	public function test_flatten_array() {
		$this->formatter = new FrmEntryFormatter( array() );
		$values          = array( 'Option 1', 'Option 2', 'Option 3' );

		$this->assertEquals( 'Option 1, Option 2, Option 3', $this->flatten_array( $values ) );

		$change_separators = function( $separator ) {
			return '<br/>';
		};
		add_filter( 'frm_entry_array_separator', $change_separators );
		$this->formatter = new FrmEntryFormatter( array() );

		$this->assertEquals( 'Option 1<br/>Option 2<br/>Option 3', $this->flatten_array( $values ) );

		remove_filter( 'frm_entry_array_separator', $change_separators );
	}

	private function flatten_array( $value ) {
		return $this->run_private_method( array( $this->formatter, 'flatten_array' ), array( $value ) );
	}
}
