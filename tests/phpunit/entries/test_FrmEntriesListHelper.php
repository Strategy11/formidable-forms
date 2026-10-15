<?php

/**
 * @group entries
 *
 * @covers FrmEntriesListHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'entries' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEntriesListHelper::class )]
class test_FrmEntriesListHelper extends FrmUnitTest {

	public function test_column_value() {
		FrmAppHelper::set_current_screen_and_hook_suffix();

		$item = new stdClass();

		// This doesn't need to be accurate for this test. This ID doesn't match the database.
		$item->id             = 1;
		$item->name           = 'My entry name';
		$item->description    = array(
			'browser'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:107.0) Gecko/20100101 Firefox/107.0',
			'referrer' => 'http://example.com',
		);
		$item->post_id        = 0;
		$item->form_id        = $this->factory->form->create();
		$description_field_id = $this->factory->field->create(
			array(
				'form_id'   => $item->form_id,
				'field_key' => 'description',
			)
		);
		$item->metas          = array(
			$description_field_id => 'Description field value',
		);

		$column_value = $this->column_value( $item, 'description' );
		$this->assertIsString( $column_value );
		$this->assertSame( 'Description field value', $column_value );

		$column_value = $this->column_value( $item, 'id' );
		$this->assertSame( 1, $column_value );

		$column_value = $this->column_value( $item, 'name' );
		$this->assertSame( 'My entry name', $column_value );
	}

	/**
	 * Reasons support stored metadata, manual moderation, and missing sources.
	 */
	public function test_spam_reason_column() {
		FrmAppHelper::set_current_screen_and_hook_suffix();
		$cases = array(
			array( wp_json_encode( array( 'spam_source' => 'akismet' ) ), 'Akismet spam' ),
			array( array( 'spam_source' => 'akismet_discard' ), 'Akismet blatant spam' ),
			array( array( 'spam_source' => 'denylist' ), 'Denylist' ),
			array( array( 'spam_source' => 'manual' ), 'Manual review' ),
			array( array(), 'Not recorded' ),
			array( array( 'spam_source' => 'unknown' ), 'Not recorded' ),
		);

		foreach ( $cases as $case ) {
			$item = (object) array( 'description' => $case[0] );
			$this->assertSame( $case[1], $this->column_value( $item, 'spam_reason' ) );
		}
	}

	/**
	 * @param stdClass $item
	 * @param string   $column_name
	 */
	private function column_value( $item, $column_name ) {
		$list_helper = new FrmEntriesListHelper( array() );
		$this->set_private_property( $list_helper, 'column_name', $column_name );
		return $this->run_private_method( array( $list_helper, 'column_value' ), array( $item ) );
	}
}
