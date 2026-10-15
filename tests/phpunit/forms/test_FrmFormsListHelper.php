<?php

/**
 * @group forms
 *
 * @covers FrmFormsListHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'forms' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFormsListHelper::class )]
class test_FrmFormsListHelper extends FrmUnitTest {

	/**
	 * @dataProvider date_search_terms
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'date_search_terms' )]
	public function test_prepare_items_skips_impossible_date_searches( $term, $date_search ) {
		global $wpdb;

		$this->set_current_user_to_1();
		$form          = $this->factory->form->create_and_get(
			array(
				'name'       => 'multi test',
				'created_at' => '2026-09-29 12:30:00',
			)
		);
		$request       = $_REQUEST;
		$_REQUEST['s'] = $term;
		$queries       = array();
		$capture_query = static function ( $query ) use ( &$queries ) {
			$queries[] = $query;
			return $query;
		};
		add_filter( 'query', $capture_query );

		try {
			$list_helper = new FrmFormsListHelper( array( 'params' => FrmForm::get_admin_params( $form->id ) ) );
			$list_helper->prepare_items();
			$this->assertSame( '', $wpdb->last_error, 'Form searches should run without datetime errors.' );
			$this->assertContains(
				(string) $form->id,
				array_map( 'strval', wp_list_pluck( $list_helper->items, 'id' ) ),
				'Text and partial-date searches should still find the form.'
			);
			$this->assertSame(
				$date_search,
				str_contains( implode( '\n', $queries ), 'CAST(created_at as CHAR)' ),
				'Only terms that can occur in a datetime should search the date column.'
			);
		} finally {
			remove_filter( 'query', $capture_query );
			$_REQUEST = $request;
		}
	}

	public static function date_search_terms() {
		yield 'text' => array( 'multi', false );
		yield 'partial date' => array( '2026-09', true );
		yield 'partial time' => array( '12:30', true );
	}

	public function test_get_posts_contain_form() {
		$form = $this->factory->form->create_and_get();

		$post_with_form = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'Before [formidable id=' . $form->id . '] after',
			)
		);

		$post_without_form = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'No form embedded here',
			)
		);

		$list_helper = new FrmFormsListHelper(
			array( 'params' => FrmForm::get_admin_params( $form->id ) )
		);

		$posts    = $this->run_private_method( array( $list_helper, 'get_posts_contain_form' ), array( $form ) );
		$post_ids = array_map( 'intval', wp_list_pluck( $posts, 'ID' ) );

		$this->assertContains( $post_with_form, $post_ids );
		$this->assertNotContains( $post_without_form, $post_ids );
	}
}
