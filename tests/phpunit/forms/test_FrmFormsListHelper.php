<?php

/**
 * @group forms
 */
class test_FrmFormsListHelper extends FrmUnitTest {

	/**
	 * @covers FrmFormsListHelper::get_posts_contain_form
	 */
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

	/**
	 * @covers FrmFormsListHelper::print_column_headers
	 */
	public function test_checkbox_column_header_is_th() {
		$this->set_user_by_role( 'administrator' );
		$this->set_admin_screen( 'admin.php?page=formidable' );
		FrmFormsController::maybe_load_listing_hooks();

		// Prime WP core's per-screen `get_column_headers()` cache the same way admin-header.php's
		// Screen Options rendering does in a real page load, before the list table's own instance
		// filter is registered - otherwise that instance filter (a back-compat no-op) runs second
		// and wipes the controller's real columns back to an empty array.
		get_column_headers( 'toplevel_page_formidable' );

		$list_helper = new FrmFormsListHelper(
			array(
				'params' => FrmForm::get_admin_params( 0 ),
				'screen' => 'toplevel_page_formidable',
			)
		);
		$list_helper->prepare_items();

		ob_start();
		$list_helper->display();
		$html = ob_get_clean();

		$thead = substr( $html, 0, strpos( $html, '</thead>' ) );

		$this->assertMatchesRegularExpression(
			'/<th[^>]*scope="col"[^>]*id=[\'"]cb[\'"][^>]*>/',
			$thead,
			'The cb column header cell must be a real <th scope="col">, not a <td>, for IBM table_headers_exists.'
		);
	}
}
