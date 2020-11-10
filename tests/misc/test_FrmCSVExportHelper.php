<?php
/**
 * @group csv
 */
class test_FrmCSVExportHelper extends FrmUnitTest {

	private $form;

	/**
	 * @covers FrmCsvExportHelper::csv_headings
	 */
	public function test_csv_headings() {
		$this->check_php_version( '5.3' );

		$this->set_form( FrmForm::getOne( 'all_field_types' ) );

		$headings = $this->csv_headings();
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
		$keys     = array_keys( $headings );

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

	/**
	 * @covers FrmCsvExportHelper::csv_headings exports the fields in a section for an embedded form
	 */
	public function test_csv_headings_for_embedded_sections() {
		$this->check_php_version( '5.3' );

		$embedded_form = $this->factory->form->create_and_get();
		$section       = $this->factory->field->create_and_get(
			array(
				'form_id' => $embedded_form->id,
				'type'    => 'divider',
				'name'    => 'Section',
			)
		);
		$field_in_section = $this->factory->field->create_and_get(
			array(
				'form_id' => $embedded_form->id,
				'type'    => 'text',
				'name'    => 'Text',
				'field_options' => array(
					'in_section' => $section->id,
				),
			)
		);

		$parent_form = $this->factory->form->create_and_get();
		$embed_field = $this->factory->field->create_and_get(
			array(
				'form_id' => $parent_form->id,
				'type'    => 'embed',
				'field_options' => array(
					'form_select' => $embedded_form->id,
				),
			)
		);

		$this->set_form( $parent_form );

		$headings = $this->csv_headings();
		$expected = array( $field_in_section->name );

		$labels = array_values( $headings );
		foreach ( $expected as $label ) {
			$this->assertContains( $label, $labels, "{$label} is not present in CSV Headings" );
		}
	}

	private function set_form( $form ) {
		$this->form = $form;
		$this->set_private_property( 'FrmCSVExportHelper', 'form_id', $form->id );
		$this->set_form_cols();
	}

	private function set_form_cols() {
		$form_cols = $this->run_private_method(
			array( 'FrmXMLController', 'get_fields_for_csv_export' ),
			array( $this->form->id, $this->form )
		);
		$this->set_private_property( 'FrmCSVExportHelper', 'fields', $form_cols );
	}

	private function csv_headings() {
		$headings = array();
		$this->run_private_method(
			array( 'FrmCSVExportHelper', 'csv_headings' ),
			array( &$headings )
		);
		return $headings;
	}
}
