<?php

/**
 * @group csv
 *
 * @covers FrmCSVExportHelper
 * @covers FrmEntriesListHelper
 * @covers FrmEntry::get_sorted_ids
 */
class test_FrmCSVExportSort extends FrmUnitTest {

	public function test_descending_export_spans_multiple_batches() {
		$form       = $this->factory->form->create_and_get();
		$ids        = $this->factory->entry->create_many( 25, $this->factory->field->generate_entry_array( $form ) );
		$sort       = FrmEntriesListHelper::get_sort_query( 'id', 'DESC' );
		$sorted_ids = FrmEntry::get_sorted_ids( array( 'form_id' => $form->id ), $sort );

		$this->assertSame( array_reverse( $ids ), $this->exported_ids( $form, $sorted_ids ), 'Descending order should survive every CSV batch.' );
	}

	public function test_numeric_field_export_uses_numeric_order() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'number',
			)
		);
		$ids      = array();

		foreach ( array( '10', '2', '30' ) as $value ) {
			$ids[] = $this->factory->entry->create(
				array(
					'form_id'   => $form->id,
					'item_meta' => array( $field_id => $value ),
				)
			);
		}

		$sort       = FrmEntriesListHelper::get_sort_query( 'meta_' . $field_id, 'ASC' );
		$sorted_ids = FrmEntry::get_sorted_ids( array( 'form_id' => $form->id ), $sort );
		$this->assertSame( array( $ids[1], $ids[0], $ids[2] ), $this->exported_ids( $form, $sorted_ids ), 'Numeric sorting should place 2 before 10.' );
	}

	public function test_text_field_export_uses_field_order() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
			)
		);
		$ids      = array();

		foreach ( array( 'Pear', 'Apple', 'Orange' ) as $value ) {
			$ids[] = $this->factory->entry->create(
				array(
					'form_id'   => $form->id,
					'item_meta' => array( $field_id => $value ),
				)
			);
		}

		$sort       = FrmEntriesListHelper::get_sort_query( 'meta_' . $field_id, 'DESC' );
		$sorted_ids = FrmEntry::get_sorted_ids( array( 'form_id' => $form->id ), $sort );
		$this->assertSame( array( $ids[0], $ids[2], $ids[1] ), $this->exported_ids( $form, $sorted_ids ), 'Text sorting should follow the selected direction.' );
	}

	public function test_export_preserves_the_supplied_id_sequence() {
		$form = $this->factory->form->create_and_get();
		$ids  = $this->factory->entry->create_many( 25, $this->factory->field->generate_entry_array( $form ) );
		$ids  = array_merge( array_slice( $ids, 12 ), array_slice( $ids, 0, 12 ) );

		$this->assertSame( $ids, $this->exported_ids( $form, $ids ), 'CSV batches should preserve arbitrary caller order.' );
	}

	/**
	 * @covers FrmXMLController::csv
	 *
	 * @runInSeparateProcess
	 *
	 * @preserveGlobalState disabled
	 */
	public function test_controller_reads_the_requested_sort() {
		$this->set_current_user_to_1();
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'number',
			)
		);
		$ids      = array();

		foreach ( array( '10', '2', '30' ) as $value ) {
			$ids[] = $this->factory->entry->create(
				array(
					'form_id'   => $form->id,
					'item_meta' => array( $field_id => $value ),
				)
			);
		}

		$get      = $_GET;
		$request  = $_REQUEST;
		$_GET     = array(
			'form'    => $form->id,
			'orderby' => 'meta_' . $field_id,
			'order'   => 'DESC',
		);
		$_REQUEST = $_GET;
		$handler  = static function () {
			return static function () {
				throw new RuntimeException( 'CSV complete.' );
			};
		};
		add_filter( 'wp_die_handler', $handler );
		ob_start();

		try {
			FrmXMLController::csv();
		} catch ( RuntimeException $exception ) {
			$this->assertSame( 'CSV complete.', $exception->getMessage(), 'The CSV controller should finish through wp_die.' );
		} finally {
			$csv = ob_get_clean();
			remove_filter( 'wp_die_handler', $handler );
			$_GET     = $get;
			$_REQUEST = $request;
		}

		$lines     = preg_split( '/\r\n|\n|\r/', trim( $csv ) );
		$headings  = str_getcsv( array_shift( $lines ), ',', '"', '' );
		$id_column = array_search( 'ID', $headings, true );
		$exported  = array();

		foreach ( $lines as $line ) {
			$row        = str_getcsv( $line, ',', '"', '' );
			$exported[] = (int) $row[ $id_column ];
		}

		$this->assertSame( array( $ids[2], $ids[0], $ids[1] ), $exported, 'The controller should read both sort parameters from the URL.' );
	}

	/**
	 * @param object $form Form being exported.
	 * @param array  $ids  Ordered entry IDs.
	 *
	 * @return array
	 */
	private function exported_ids( $form, $ids ) {
		$path = FrmCSVExportHelper::generate_csv(
			array(
				'mode'      => 'file',
				'form'      => $form,
				'entry_ids' => $ids,
				'form_cols' => FrmField::get_all_for_form( $form->id, '', 'include' ),
			)
		);
		WP_Filesystem();
		global $wp_filesystem;
		$lines     = preg_split( '/\r\n|\n|\r/', trim( $wp_filesystem->get_contents( $path ) ) );
		$headings  = str_getcsv( array_shift( $lines ), ',', '"', '' );
		$id_column = array_search( 'ID', $headings, true );
		$rows      = array();

		foreach ( $lines as $line ) {
			$row    = str_getcsv( $line, ',', '"', '' );
			$rows[] = (int) $row[ $id_column ];
		}

		wp_delete_file( $path );
		return $rows;
	}
}
