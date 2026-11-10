<?php
/** Verify consistent page and View choices without changing stored IDs. */
class test_FrmPostDropdownLabels extends FrmUnitTest {
	/** @return void */
	public function test_titles_and_ids() {
		foreach ( array( '', '   ', 'Saved Listings', '0', 'A & B <script>alert(1)</script>' ) as $title ) {
			$post     = (object) array(
				'ID'         => 123,
				'post_title' => $title,
			);
			$expected = '' === trim( $title ) ? FrmFormsHelper::get_no_title_text() : $title;
			$this->assertSame( $expected . ' (ID: 123)', FrmAppHelper::get_post_dropdown_label( $post ) );
			$this->assertSame( $title, $post->post_title );
		}
	}

	/** @return void */
	public function test_truncated_title_keeps_id() {
		$post  = (object) array(
			'ID'         => 456,
			'post_title' => str_repeat( 'Long title ', 20 ),
		);
		$label = FrmAppHelper::get_post_dropdown_label( $post, 30 );
		$this->assertStringContainsString( '(ID: 456)', $label );
		$this->assertLessThan( strlen( $post->post_title ), strlen( $label ) );
	}

	/** @return void */
	public function test_page_dropdown_preserves_selection_and_escapes_titles() {
		$id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'A & B',
			)
		);
		add_filter( 'list_pages', array( 'FrmAppHelper', 'page_dropdown_label' ), 10, 2 );
		$html = wp_dropdown_pages(
			array(
				'echo'     => 0,
				'selected' => $id,
				'name'     => 'destination',
				'id'       => 'destination-id',
			)
		);
		$this->assertStringContainsString( 'value="' . $id . '" selected="selected"', $html );
		$this->assertStringContainsString( 'A &amp; B (ID: ' . $id . ')', $html );
		$this->assertStringContainsString( "id='destination-id'", $html );
		remove_filter( 'list_pages', array( 'FrmAppHelper', 'page_dropdown_label' ), 10 );
		$this->assertFalse( has_filter( 'list_pages', array( 'FrmAppHelper', 'page_dropdown_label' ) ) );
	}

	/** @return void */
	public function test_shared_page_dropdown_uses_no_title() {
		$id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => '',
			)
		);
		ob_start();
		FrmAppHelper::wp_pages_dropdown(
			array(
				'field_name' => 'destination',
				'page_id'    => $id,
			)
		);
		$html = ob_get_clean();
		$this->assertStringContainsString( '(no title) (ID: ' . $id . ')', $html );
		$this->assertStringContainsString( 'value="' . $id . '"', $html );
	}

	/** @return void */
	public function test_gated_content_autocomplete_keeps_values() {
		$source = json_decode(
			FrmGatedContentAction::get_posts_autocomplete_source(
				array(
					(object) array(
						'ID'         => 123,
						'post_title' => '',
					),
				)
			),
			true
		);
		$this->assertSame(
			array(
				array(
					'value' => '123',
					'label' => '(no title) (ID: 123)',
				),
			),
			$source
		);
	}
}
