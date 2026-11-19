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
	 * @covers FrmEntriesListHelper::get_sort_parameters
	 */
	public function test_saved_sort_preference_is_sanitized() {
		set_current_screen( 'formidable_page_formidable-entries' );
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		update_user_meta(
			$user_id,
			'frm_preferred_list_sort_' . get_current_screen()->id,
			array(
				'orderby' => 'id&form=2',
				'order'   => 'DESC&item_id=1',
			)
		);

		try {
			$sort = FrmEntriesListHelper::get_sort_parameters();
		} finally {
			set_current_screen( 'front' );
		}

		$this->assertSame( 'idform2', $sort['orderby'], 'A saved column should not be able to add URL parameters.' );
		$this->assertSame( 'descitem_id1', $sort['order'], 'A saved direction should not be able to add URL parameters.' );
	}

	/**
	 * @covers FrmEntriesListHelper::get_sort_query
	 */
	public function test_unknown_sort_column_falls_back_to_id() {
		$form = $this->factory->form->create_and_get();
		$ids  = $this->factory->entry->create_many( 3, $this->factory->field->generate_entry_array( $form ) );

		foreach ( array( 'bogus', 'id;select', 'idselect', 'meta', 'meta_5;drop', 'it.id' ) as $orderby ) {
			$sort = FrmEntriesListHelper::get_sort_query( $orderby, 'desc' );
			$this->assertSame( ' ORDER BY id desc', $sort, 'Unknown sort column "' . $orderby . '" should fall back to the entry ID.' );
		}

		$this->assertSame( ' ORDER BY created_at asc', FrmEntriesListHelper::get_sort_query( 'created_at', 'asc' ), 'A known entry column should be kept.' );
		$this->assertSame( ' ORDER BY meta_999999 asc', FrmEntriesListHelper::get_sort_query( 'meta_999999', 'asc' ), 'A field alias should be kept.' );

		global $wpdb;
		$sorted_ids = FrmEntry::get_sorted_ids( array( 'form_id' => $form->id ), FrmEntriesListHelper::get_sort_query( 'bogus', 'desc' ) );
		$this->assertSame( '', $wpdb->last_error, 'An unknown sort column should not cause a database error.' );
		$this->assertSame( array_reverse( $ids ), array_map( 'intval', $sorted_ids ), 'An unknown sort column should export newest first.' );
	}

	/**
	 * @covers FrmEntry::get_sorted_ids
	 */
	public function test_post_field_sort_without_a_column_falls_back_to_id() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id'       => $form->id,
				'type'          => 'text',
				'field_options' => array( 'post_field' => 'post_title' ),
			)
		);
		$ids      = $this->factory->entry->create_many( 3, array( 'form_id' => $form->id ) );
		$filter   = '__return_empty_string';
		add_filter( 'frm_handle_field_column_sort', $filter );

		try {
			global $wpdb;
			$sorted_ids = FrmEntry::get_sorted_ids( array( 'form_id' => $form->id ), FrmEntriesListHelper::get_sort_query( 'meta_' . $field_id, 'desc' ) );
			$this->assertSame( '', $wpdb->last_error, 'A field without a sortable column should not cause a database error.' );
		} finally {
			remove_filter( 'frm_handle_field_column_sort', $filter );
		}

		$this->assertSame( array_reverse( $ids ), array_map( 'intval', $sorted_ids ), 'A field without a sortable column should sort by entry ID.' );
	}

	/**
	 * @covers FrmXMLController::csv
	 *
	 * @runInSeparateProcess
	 *
	 * @preserveGlobalState disabled
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
	public function test_controller_ignores_an_unknown_sort_column() {
		$this->set_current_user_to_1();
		$form = $this->factory->form->create_and_get();
		$ids  = $this->factory->entry->create_many( 3, $this->factory->field->generate_entry_array( $form ) );

		$exported = $this->exported_ids_from_controller(
			array(
				'form'    => $form->id,
				'orderby' => 'id;select',
				'order'   => 'desc',
			)
		);

		$this->assertSame( array_reverse( $ids ), $exported, 'An unknown sort column should export newest first instead of failing.' );
	}

	/**
	 * @covers FrmXMLController::csv
	 *
	 * @runInSeparateProcess
	 *
	 * @preserveGlobalState disabled
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
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

		$exported = $this->exported_ids_from_controller(
			array(
				'form'    => $form->id,
				'orderby' => 'meta_' . $field_id,
				'order'   => 'DESC',
			)
		);

		$this->assertSame( array( $ids[2], $ids[0], $ids[1] ), $exported, 'The controller should read both sort parameters from the URL.' );
	}

	/**
	 * @covers FrmXMLController::csv
	 *
	 * @runInSeparateProcess
	 *
	 * @preserveGlobalState disabled
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
	public function test_controller_applies_items_table_conditions_with_the_sort() {
		$this->set_current_user_to_1();
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'number',
			)
		);
		$ids      = array();

		foreach ( array( '10', '2', '30', '5' ) as $index => $value ) {
			$ids[] = $this->factory->entry->create(
				array(
					'form_id'   => $form->id,
					'item_meta' => array( $field_id => $value ),
					'is_draft'  => 2 === $index ? 1 : 0,
				)
			);
		}

		$excluded = $ids[3];
		$filter   = static function ( $query ) use ( $excluded ) {
			$query['it.is_draft'] = 0;
			$query['it.id !']     = $excluded;
			return $query;
		};
		add_filter( 'frm_csv_where', $filter );

		try {
			$exported = $this->exported_ids_from_controller(
				array(
					'form'    => $form->id,
					'orderby' => 'meta_' . $field_id,
					'order'   => 'DESC',
				)
			);
		} finally {
			remove_filter( 'frm_csv_where', $filter );
		}

		$this->assertSame( array( $ids[0], $ids[1] ), $exported, 'Conditions on the aliased items table should filter the sorted export.' );
	}

	/**
	 * Run the CSV controller with the given URL parameters and return the exported entry IDs in order.
	 *
	 * @param array $params Query string parameters.
	 *
	 * @return array
	 */
	private function exported_ids_from_controller( $params ) {
		$get      = $_GET;
		$request  = $_REQUEST;
		$_GET     = $params;
		$_REQUEST = $params;
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

		return $exported;
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
