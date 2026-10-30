<?php

/**
 * @group usage
 */
class test_FrmUsage extends FrmUnitTest {

	/**
	 * Fields must not be objects, because clean_before_send() replaces objects with a placeholder.
	 */
	public function test_snapshot_fields_are_arrays() {
		$snapshot = ( new FrmUsage() )->snapshot();

		$this->assertNotEmpty( $snapshot['fields'] );

		foreach ( $snapshot['fields'] as $field ) {
			$this->assertIsArray( $field );
			$this->assertArrayHasKey( 'type', $field );
			$this->assertIsArray( $field['field_options'] );
		}
	}

	public function test_cleaned_snapshot_keeps_field_data() {
		$usage    = new FrmUsage();
		$snapshot = $usage->snapshot();
		$clean    = new ReflectionMethod( $usage, 'clean_before_send' );
		$clean->setAccessible( true );
		$clean->invokeArgs( $usage, array( &$snapshot ) );

		$this->assertNotEmpty( $snapshot['fields'] );
		$this->assertNotContains( '{{object}}', $snapshot['fields'] );
		$this->assertArrayHasKey( 'type', reset( $snapshot['fields'] ) );
	}
}
