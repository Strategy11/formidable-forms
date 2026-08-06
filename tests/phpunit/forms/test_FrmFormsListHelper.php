<?php

/**
 * @group forms
 */
class test_FrmFormsListHelper extends FrmUnitTest {

	/**
	 * @covers FrmFormsListHelper::query_posts_contain_form
	 */
	public function test_query_posts_contain_form() {
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

		$posts    = $this->run_private_method( array( $list_helper, 'query_posts_contain_form' ), array( $form ) );
		$post_ids = array_map( 'intval', wp_list_pluck( $posts, 'ID' ) );

		$this->assertContains( $post_with_form, $post_ids );
		$this->assertNotContains( $post_without_form, $post_ids );
	}
}
