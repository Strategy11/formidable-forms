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
	public function test_page_search_matches_prefilled_label_and_id() {
		$id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Saved Listings',
			)
		);

		foreach ( array( 'Saved Listings', 'Saved Listings (ID: ' . $id . ')', 'Saved', (string) $id ) as $term ) {
			$values = wp_list_pluck( FrmSettingsController::get_page_search_results( $term, 'page' ), 'value' );
			$this->assertContains( (string) $id, array_map( 'strval', $values ), 'Term: ' . $term );
		}

		$results = FrmSettingsController::get_page_search_results( 'Saved Listings', 'page' );
		$this->assertSame( 'Saved Listings (ID: ' . $id . ')', $results[0]['label'] );
		$this->assertSame( array(), FrmSettingsController::get_page_search_results( 'No such page', 'page' ) );
	}

	/** @return void */
	public function test_autocomplete_selected_value_keeps_private_prefix() {
		$id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
				'post_title'  => 'Secret',
			)
		);
		self::factory()->post->create_many(
			51,
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		ob_start();
		FrmAppHelper::maybe_autocomplete_pages_options(
			array(
				'field_name' => 'destination',
				'page_id'    => $id,
			)
		);
		$html = ob_get_clean();
		$this->assertStringContainsString( 'value="Private: Secret (ID: ' . $id . ')"', $html );
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
