<?php
/**
 * @group csv
 */
class test_FrmCSVExportHelper extends FrmUnitTest {

	/**
	 * @covers FrnCsvExportHelper::csv_headings
	 */
	public function test_csv_headings() {
		$this->check_php_version( '5.3' );

		$form      = FrmForm::getOne( 'all_field_types' );
		$form_id   = $form->id;
		$form_cols = $this->run_private_method(
			array( 'FrmXMLController', 'get_fields_for_csv_export' ),
			array( $form_id, $form )
		);

		$this->set_private_property( 'FrmCSVExportHelper', 'form_id', $form_id );
		$this->set_private_property( 'FrmCSVExportHelper', 'fields', $form_cols );

		$headings = array();
		$this->run_private_method(
			array( 'FrmCSVExportHelper', 'csv_headings' ),
			array( &$headings ) // parameters go inside this array if any
		);

		$expected = array(
			// default expected
			'created_at' => 'Timestamp',
			'updated_at' => 'Last Updated',
			'user_id'    => 'Created By',
			'updated_by' => 'Updated By',
			'is_draft'   => 'Draft',
			'ip'         => 'IP',
			'id'         => 'ID',
			'item_key'   => 'Key',
		);

		$keys = array_keys( $headings );
		foreach ( $expected as $key => $label ) {
			$this->assertContains( $key, $keys, "{$label} is not present in CSV Headings" );
		}

		// expected for all_field_types form
		$expected = array(
			'Paragraph Text',
			'Checkboxes - colors',
			'Radio Buttons - dessert',
			'Dropdown',
			'Email Address',
			'Website/URL',
			'Rich Text',
			'Single File Upload',
			'Multiple File Upload',
			'Number',
			'Phone Number',
			'Time',
			'Date',
			'Image URL',
			'Scale',
			'Dynamic Field - level 1',
			'Dynamic Field - level 2',
			'Dynamic Field - level 3',
			'Dynamic Field List',
			'Hidden Field',
			'User ID',
			'Password',
			'Tags',
			'Signature',
			'Lookup Field - level 1',
			'Checkboxes - separate values (label)',
			'Checkboxes - separate values',
			'Address - US',
			'Credit Card',
		);

		$labels = array_values( $headings );
		foreach ( $expected as $label ) {
			$this->assertContains( $label, $labels, "{$label} is not present in CSV Headings" );
		}
	}
}
