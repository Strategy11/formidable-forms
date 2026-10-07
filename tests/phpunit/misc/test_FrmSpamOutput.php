<?php

/**
 * @covers FrmSpamEntriesHelper
 * @covers FrmFieldsHelper
 * @covers FrmEntriesHelper
 * @covers FrmFieldValue
 */
class test_FrmSpamOutput extends FrmUnitTest {
	/**
	 * Referrers keep line breaks without rendering submitted markup for normal or spam entries.
	 *
	 * @return void
	 */
	public function test_sidebar_referrer_preserves_line_breaks() {
		$form_id = $this->factory->form->create();
		$id      = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		$entry   = FrmEntry::getOne( $id );
		$data    = array( 'referrer' => "https://example.com/\n<img src=x onerror=alert(1)>\r\nLast line" );

		foreach ( array( 0, 4 ) as $status ) {
			$entry->is_draft = $status;
			ob_start();

			try {
				include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/sidebar-shared.php';
				$html = ob_get_contents();
			} finally {
				ob_end_clean();
			}
			$this->assertStringContainsString( "https://example.com/<br />\n&lt;img src=x onerror=alert(1)&gt;<br />\r\nLast line", $html );
			$this->assertStringNotContainsString( '<img src=x', $html );
		}
	}

	/**
	 * Late display filters must not reintroduce markup into spam values.
	 *
	 * @return void
	 */
	public function test_spam_values_remain_text_after_display_filters() {
		$form  = $this->factory->form->create();
		$field = $this->factory->field->create_and_get(
			array(
				'form_id' => $form,
				'type'    => 'text',
			)
		);
		$id    = $this->factory->entry->create( array( 'form_id' => $form ) );
		FrmEntryMeta::add_entry_meta( $id, $field->id, '', '<b>Submitted</b>' );
		$this->assertTrue( FrmSpamEntriesHelper::mark_as_spam( $id ) );
		$entry   = FrmEntry::getOne( $id, true );
		$payload = '<img src=x onerror=alert(1)><b>Filtered</b>[spam_probe]';
		$filter  = static function () use ( $payload ) {
			return $payload;
		};
		add_filter( 'frm_display_value', $filter );

		try {
			$this->assertSame( esc_html( $payload ), FrmFieldsHelper::get_display_value( 'Submitted', $field, array( 'entry' => $entry ) ) );
			$this->assertSame( esc_html( $payload ), FrmEntriesHelper::prepare_display_value( $entry, $field, array() ) );
			$value = new FrmFieldValue( $field, $entry );
			$value->prepare_displayed_value();
			$this->assertSame( esc_html( $payload ), $value->get_displayed_value() );
		} finally {
			remove_filter( 'frm_display_value', $filter );
		}

		add_filter( 'frm_sanitize_shortcodes', '__return_false' );

		try {
			$value = $payload;
			FrmFieldsHelper::sanitize_embedded_shortcodes( array( 'entry' => $entry ), $value );
			$this->assertSame( str_replace( '[', '&#91;', esc_html( $payload ) ), $value );
		} finally {
			remove_filter( 'frm_sanitize_shortcodes', '__return_false' );
		}
	}

	/**
	 * Global counts and raw conditions must exclude spam while explicit access remains available.
	 *
	 * @return void
	 */
	public function test_default_and_raw_queries_exclude_spam() {
		$form   = $this->factory->form->create();
		$before = (int) FrmEntry::getRecordCount();
		$normal = $this->factory->entry->create( array( 'form_id' => $form ) );
		$draft  = $this->factory->entry->create(
			array(
				'form_id'  => $form,
				'is_draft' => 1,
			)
		);
		$spam   = $this->factory->entry->create( array( 'form_id' => $form ) );
		$this->assertTrue( FrmSpamEntriesHelper::mark_as_spam( $spam ) );
		$this->assertSame( $before + 2, (int) FrmEntry::getRecordCount() );

		foreach ( array(
			array( 'it.form_id' => $form ),
			array(
				'or'         => 1,
				'it.form_id' => $form,
				'it.id'      => $normal,
			),
			array(
				'it.form_id' => $form,
				'it.id >'    => 0,
			),
			array(
				'it.form_id'        => $form,
				'it.parent_item_id' => 0,
			),
			array(
				'it.form_id' => $form,
				array(
					'or'          => 1,
					'it.is_draft' => 0,
					'it.user_id'  => 0,
				),
			),
			'it.form_id = ' . $form,
			'it.form_id = ' . $form . ' AND it.parent_item_id=0',
			'it.form_id = ' . $form . ' AND it.id > 0',
			"'id=4 is_draft=4'='id=4 is_draft=4' AND it.form_id = " . $form,
			'it.form_id = ' . $form . ' OR it.form_id = -1',
		) as $where ) {
			$this->assertSame( 2, (int) FrmEntry::getRecordCount( $where ) );
			$this->assertEqualsCanonicalizing( array( $normal, $draft ), array_values( array_map( 'intval', wp_list_pluck( FrmEntry::getAll( $where ), 'id' ) ) ) );
		}
		$this->assertSame(
			1,
			(int) FrmEntry::getRecordCount(
				array(
					'it.form_id'  => $form,
					'it.is_draft' => 4,
				)
			)
		);
		$this->assertSame( array( $spam ), array_values( array_map( 'intval', wp_list_pluck( FrmEntry::getAll( array( 'it.id' => $spam ) ), 'id' ) ) ) );
	}
}
