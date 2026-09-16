<?php

/**
 * @group entries
 */
class test_FrmEntriesListHelper extends FrmUnitTest {

	/**
	 * @covers FrmEntriesListHelper::column_value
	 */
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
	 * The Form column's own value is wrapped in a second, nested row-title
	 * link when it's the row's action column (e.g. Entry Key/ID columns
	 * hidden on the "all forms" Entries screen) -- browsers implicitly close
	 * the outer row-title <a> as soon as they hit that nested one, leaving it
	 * with no accessible name. When acting as the action column, the form
	 * value must be plain text instead.
	 *
	 * @covers FrmEntriesListHelper::column_value
	 */
	public function test_column_value_form_id_as_action_col_has_no_nested_link() {
		FrmAppHelper::set_current_screen_and_hook_suffix();
		wp_set_current_user( 1 );

		$item          = new stdClass();
		$item->form_id = $this->factory->form->create( array( 'name' => 'My Form' ) );

		// Not the action column: existing behavior, links to the form itself.
		$column_value = $this->column_value( $item, 'form_id', false );
		$this->assertStringContainsString( '<a ', $column_value );
		$this->assertStringContainsString( 'My Form', $column_value );

		// As the action column: no nested <a>, plain label only.
		$column_value = $this->column_value( $item, 'form_id', true );
		$this->assertStringNotContainsString( '<a ', $column_value );
		$this->assertSame( 'My Form', $column_value );
	}

	/**
	 * An entry whose form no longer exists (form_id doesn't resolve to a
	 * row) must still render non-empty fallback text as the action column,
	 * not an empty row-title link.
	 *
	 * @covers FrmEntriesListHelper::column_value
	 */
	public function test_column_value_form_id_as_action_col_missing_form_falls_back() {
		FrmAppHelper::set_current_screen_and_hook_suffix();
		wp_set_current_user( 1 );

		$item          = new stdClass();
		$item->form_id = 999999999;

		$column_value = $this->column_value( $item, 'form_id', true );
		$this->assertNotEmpty( $column_value );
		$this->assertStringNotContainsString( '<a ', $column_value );
	}

	/**
	 * Same bug class as the form_id case above, on the Post column shown
	 * when a form has a create-post action: post_edit_link() builds its own
	 * nested <a>, which must be dropped in favor of a plain label when this
	 * column is the row's action column.
	 *
	 * @covers FrmEntriesListHelper::column_value
	 */
	public function test_column_value_post_id_as_action_col_has_no_nested_link() {
		FrmAppHelper::set_current_screen_and_hook_suffix();

		$item          = new stdClass();
		$item->post_id = $this->factory->post->create( array( 'post_title' => 'My Post' ) );

		// Not the action column: existing behavior, links to the post itself.
		$column_value = $this->column_value( $item, 'post_id', false );
		$this->assertStringContainsString( '<a ', $column_value );
		$this->assertStringContainsString( 'My Post', $column_value );

		// As the action column: no nested <a>, plain label only.
		$column_value = $this->column_value( $item, 'post_id', true );
		$this->assertStringNotContainsString( '<a ', $column_value );
		$this->assertSame( 'My Post', $column_value );
	}

	/**
	 * An entry whose linked post no longer exists must still render
	 * non-empty fallback text as the action column, not an empty
	 * row-title link.
	 *
	 * @covers FrmEntriesListHelper::column_value
	 */
	public function test_column_value_post_id_as_action_col_missing_post_falls_back() {
		FrmAppHelper::set_current_screen_and_hook_suffix();

		$item          = new stdClass();
		$item->post_id = 999999999;

		$column_value = $this->column_value( $item, 'post_id', true );
		$this->assertNotEmpty( $column_value );
		$this->assertStringNotContainsString( '<a ', $column_value );
	}

	/**
	 * @param stdClass $item
	 * @param string   $column_name
	 * @param bool     $is_action_col
	 */
	private function column_value( $item, $column_name, $is_action_col = false ) {
		$list_helper = new FrmEntriesListHelper( array() );
		$this->set_private_property( $list_helper, 'column_name', $column_name );
		return $this->run_private_method( array( $list_helper, 'column_value' ), array( $item, $is_action_col ) );
	}
}
