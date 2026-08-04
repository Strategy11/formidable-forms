<?php

/**
 * @group gated-content
 */
class test_FrmGatedContentAction extends FrmUnitTest {

	// ── get_posts() ───────────────────────────────────────────────────────── //

	/**
	 * Plain published posts are publicly accessible and must not appear in the
	 * selector — no token is needed to view them.
	 *
	 * @covers FrmGatedContentAction::get_posts
	 */
	public function test_get_posts_excludes_plain_published_post() {
		$post    = $this->factory->post->create_and_get( array( 'post_status' => 'publish' ) );
		$grouped = FrmGatedContentAction::get_posts();
		$ids     = array_map( 'intval', array_column( $grouped['post'] ?? array(), 'ID' ) );
		$this->assertNotContains(
			$post->ID,
			$ids,
			'A plain published post must not appear in the gated content item selector.'
		);
	}

	/**
	 * Private posts require a capability check that a token can satisfy — they
	 * must appear under their post-type key so admins can select them.
	 *
	 * @covers FrmGatedContentAction::get_posts
	 */
	public function test_get_posts_includes_private_post() {
		$post    = $this->factory->post->create_and_get( array( 'post_status' => 'private' ) );
		$grouped = FrmGatedContentAction::get_posts();

		$this->assertArrayHasKey( 'post', $grouped );
		$ids = array_map( 'intval', array_column( $grouped['post'], 'ID' ) );
		$this->assertContains(
			$post->ID,
			$ids,
			'A private post must appear in the gated content item selector.'
		);
	}

	/**
	 * Password-protected posts are published but block access via a password
	 * form — a token must be able to bypass that gate, so they must be selectable.
	 *
	 * @covers FrmGatedContentAction::get_posts
	 */
	public function test_get_posts_includes_password_protected_post() {
		$post    = $this->factory->post->create_and_get(
			array(
				'post_status'   => 'publish',
				'post_password' => wp_generate_password( 12 ),
			)
		);
		$grouped = FrmGatedContentAction::get_posts();

		$this->assertArrayHasKey( 'post', $grouped );
		$ids = array_map( 'intval', array_column( $grouped['post'], 'ID' ) );
		$this->assertContains(
			$post->ID,
			$ids,
			'A password-protected post must appear in the gated content item selector.'
		);
	}

	/**
	 * Posts and pages must land under the correct type key and not bleed into
	 * each other's bucket.
	 *
	 * @covers FrmGatedContentAction::get_posts
	 */
	public function test_get_posts_groups_results_by_post_type() {
		$post     = $this->factory->post->create_and_get(
			array(
				'post_type'   => 'post',
				'post_status' => 'private',
			)
		);
		$page     = $this->factory->post->create_and_get(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
			)
		);
		$grouped  = FrmGatedContentAction::get_posts();
		$post_ids = array_map( 'intval', array_column( $grouped['post'] ?? array(), 'ID' ) );
		$page_ids = array_map( 'intval', array_column( $grouped['page'] ?? array(), 'ID' ) );

		$this->assertContains( $post->ID, $post_ids, 'Private post must appear under the "post" key.' );
		$this->assertNotContains( $post->ID, $page_ids, 'Post must not bleed into the "page" bucket.' );
		$this->assertContains( $page->ID, $page_ids, 'Private page must appear under the "page" key.' );
		$this->assertNotContains( $page->ID, $post_ids, 'Page must not bleed into the "post" bucket.' );
	}

	/**
	 * Disabled types (frm_file, frm_pdf) are not registered post types — they
	 * must not appear as keys in the result.
	 *
	 * @covers FrmGatedContentAction::get_posts
	 */
	public function test_get_posts_omits_disabled_types() {
		$grouped = FrmGatedContentAction::get_posts();

		$this->assertArrayNotHasKey( 'frm_file', $grouped );
		$this->assertArrayNotHasKey( 'frm_pdf', $grouped );
	}

	/**
	 * When no enabled post types exist, get_posts() must return an empty array
	 * rather than querying the DB or returning a partial structure.
	 *
	 * @covers FrmGatedContentAction::get_posts
	 */
	public function test_get_posts_returns_empty_when_no_enabled_types() {
		add_filter(
			'frm_gated_content_item_types',
			static function () {
				return array(
					'frm_file' => array(
						'label'    => 'File',
						'disabled' => true,
					),
				);
			}
		);

		$grouped = FrmGatedContentAction::get_posts();

		remove_all_filters( 'frm_gated_content_item_types' );

		$this->assertSame( array(), $grouped, 'get_posts() must return [] when all registered types are disabled.' );
	}
}
