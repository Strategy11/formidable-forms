<?php

class test_FrmEntryMetaPathHelper extends FrmUnitTest {

	public function test_nested_reads_and_writes_preserve_other_rows() {
		$_POST['item_meta'] = array(
			10 => array(
				0 => array(
					20 => array(
						0 => array( 30 => 'First' ),
						1 => array( 30 => 'Second' ),
					),
				),
			),
			40 => wp_slash( 'A \\ B' ),
		);
		$args               = array( 'item_meta_path' => array( 10, 0, 20, 0 ) );
		$field              = (object) array( 'id' => 30 );
		$this->assertTrue( FrmEntriesHelper::value_is_posted( $field, $args ), 'A field at the nested path should be posted.' );
		$this->assertSame( 'First', FrmEntriesHelper::get_posted_meta( 30, $args ), 'The helper should read the requested nested row.' );
		FrmEntriesHelper::set_posted_value( $field, 'Updated', $args );
		$this->assertSame( 'Updated', $_POST['item_meta'][10][0][20][0][30], 'The requested field should be updated.' );
		$this->assertSame( 'Second', $_POST['item_meta'][10][0][20][1][30], 'Other rows should be preserved.' );
		$this->assertSame( wp_slash( 'A \\ B' ), $_POST['item_meta'][40], 'Unrelated values should retain their existing slashes.' );
	}

	public function test_malformed_paths_are_empty_and_writes_create_arrays() {
		$_POST['item_meta'] = array( 10 => 'malformed' );
		$args               = array( 'item_meta_path' => array( 10, 0, 20, 0 ) );
		$field              = (object) array( 'id' => 30 );
		$this->assertFalse( FrmEntriesHelper::value_is_posted( $field, $args ), 'A malformed parent should not count as a posted field.' );
		$this->assertSame( '', FrmEntriesHelper::get_posted_meta( 30, $args ), 'A malformed parent should return an empty value.' );
		FrmEntriesHelper::set_posted_value( $field, 'Created', $args );
		$this->assertSame( 'Created', $_POST['item_meta'][10][0][20][0][30], 'Writing should initialize missing parent arrays.' );
	}

	public function test_empty_path_retains_existing_parent_arguments() {
		$_POST['item_meta'] = array();
		$args               = array(
			'parent_field_id' => 10,
			'key_pointer'     => 0,
			'item_meta_path'  => array(),
		);
		FrmEntriesHelper::set_posted_value( (object) array( 'id' => 30 ), 'Legacy row', $args );
		$this->assertSame( 'Legacy row', $_POST['item_meta'][10][0][30], 'An empty path should preserve existing repeater arguments.' );
	}
}
