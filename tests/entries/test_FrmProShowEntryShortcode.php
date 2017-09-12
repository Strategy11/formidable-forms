<?php

/**
 * @since 2.05
 *
 * @group shortcodes
 * @group entries
 * @group show-entry-shortcode
 * @group pro
 *
 */
include( 'test_FrmShowEntryShortcode.php' );
class test_FrmProShowEntryShortcode extends test_FrmShowEntryShortcode {

	// TODO: try including a field from inside a repeating section. It's not yet possible to display a single field from inside a repeating section
	// TODO: try including a field from inside an embedded form
	// TODO: section with no fields in it
	// TODO: add is_visible for for sections and maybe page breaks in default HTML or just if [if x]
	// TODO: what about conditional page breaks?
	// TODO: figure out if this was important: $filter_value = ( ! isset( $atts['filter'] ) || $atts['filter'] !== false );
	// TODO: ***add test for value of 0 and include_blank=false***, using jamie_entry_key_2
	// TODO: add test for section with no values in it, plain text
	// TODO: unit test for frm_email_value hook

	protected $extra_fields = array( 'html', 'divider', 'break' );

	/**
	 * Tests [default-message]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 *
	 * @group show-entry-for-post-entry-email
	 */
	public function test_basic_default_message_parameters_create_post_form() {
		$entry = FrmEntry::getOne( 'post-entry-1', true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content_for_post_entry( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 */
	public function test_default_message_with_repeating_field_id_included() {
		$this->markTestSkipped( 'Functionality not added yet.' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$this->include_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-text' => FrmField::get_id_by_key( 'repeating-text' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content = $this->get_formatted_content( $atts );

		$expected_content = $this->table_header( $atts );
		$expected_content .= $this->text_field_html( $atts );
		$expected_content .= $this->user_id_html( $atts );
		$expected_content .= $this->repeating_field_html( $atts );
		$expected_content .= $this->table_footer();

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04.02
	 */
	public function test_default_message_with_repeating_field_id_excluded() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$this->exclude_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-text' => FrmField::get_id_by_key( 'repeating-text' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'exclude_fields' => implode( ',', $this->exclude_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="section"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 *
	 * @group show-entry-shortcode-conditional-section
	 */
	public function test_default_message_with_conditionally_hidden_sections() {
		$this->hide_and_clear_section();
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$this->include_extras = array( 'divider' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
		);

		$content = $this->get_formatted_content( $atts );

		$expected_content = $this->table_header( $atts );

		$expected_content .= $this->text_field_html( $atts );
		$expected_content .= $this->paragraph_to_website_html( $atts );
		$expected_content .= $this->dynamic_country_html( $atts );
		$expected_content .= $this->dynamic_state_html( $atts );
		$expected_content .= $this->embedded_form_html( $atts );
		$expected_content .= $this->hidden_field_html( $atts );
		$expected_content .= $this->user_id_html( $atts );
		$expected_content .= $this->signature_html( $atts );
		$expected_content .= $this->repeating_section_header( $atts );
		$expected_content .= $this->repeating_field_html( $atts );
		$expected_content .= $this->lookup_to_address_html( $atts );
		$expected_content .= $this->user_info_html( $atts );

		$expected_content .= $this->table_footer();

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="section"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 *
	 * @group show-entry-shortcode-conditional-section
	 */
	public function test_default_message_with_conditionally_hidden_sections_and_include_fields() {
		$this->hide_and_clear_section();
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$this->include_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-section' => FrmField::get_id_by_key( 'repeating-section' ),
			'embed-form-field' => FrmField::get_id_by_key( 'embed-form-field' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
			'pro-fields-divider' => FrmField::get_id_by_key( 'pro-fields-divider' ),
		);

		$this->include_extras = array( 'divider' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content = $this->get_formatted_content( $atts );

		$expected_content = $this->table_header( $atts );
		$expected_content .= $this->text_field_html( $atts );
		$expected_content .= $this->embedded_form_html( $atts );
		$expected_content .= $this->user_id_html( $atts );
		$expected_content .= $this->repeating_section_header( $atts );
		$expected_content .= $this->repeating_field_html( $atts );
		$expected_content .= $this->table_footer();

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message plain_text=1 include_extras="section"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04.02
	 *
	 * @group show-entry-shortcode-conditional-section
	 */
	public function test_plain_text_content_with_conditionally_hidden_sections() {
		$this->hide_and_clear_section();
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$this->include_extras = array( 'divider' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => true,
			'user_info' => false,
			'include_extras' => 'section'
		);

		$content = $this->get_formatted_content( $atts );

		$expected_content = $this->text_field_plain_text( $atts );
		$expected_content .= $this->paragraph_to_website_plain_text();
		$expected_content .= $this->dynamic_country_plain_text( $atts );
		$expected_content .= $this->dynamic_state_plain_text( $atts );
		$expected_content .= $this->embedded_form_plain_text( $atts );
		$expected_content .= $this->hidden_field_plain_text( $atts );
		$expected_content .= $this->user_id_plain_text( $atts );
		$expected_content .= $this->signature_plain_text( $atts );
		$expected_content .= $this->repeating_section_header_plain_text( $atts );
		$expected_content .= $this->repeating_field_plain_text();
		$expected_content .= $this->lookup_to_address_plain_text( $atts );
		$expected_content .= $this->user_info_plain_text( $atts );


		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="section"]
	 * This tests the situation where an entry was submitted and a repeating section was left blank
	 * The repeating section may have child entries, but those entries have no values
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 */
	public function test_default_message_with_no_values_in_repeating_section() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );
		$repeating_section = FrmField::get_id_by_key( 'repeating-section' );

		foreach ( $entry->metas[ $repeating_section ] as $child_id ) {
			// Delete all meta with an item_id of $entry->id
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . 'frm_item_metas', array( 'item_id' => $child_id ) );
		}

		$this->include_extras = array( 'divider' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
		);

		$content = $this->get_formatted_content( $atts );

		$expected_content = $this->table_header( $atts );

		$expected_content .= $this->text_field_html( $atts );
		$expected_content .= $this->paragraph_to_website_html( $atts );
		$expected_content .= $this->page_break_html( $atts );
		$expected_content .= $this->pro_fields_divider_html( $atts );
		$expected_content .= $this->dynamic_country_html( $atts );
		$expected_content .= $this->dynamic_state_html( $atts );
		$expected_content .= $this->embedded_form_html( $atts );
		$expected_content .= $this->hidden_field_html( $atts );
		$expected_content .= $this->user_id_html( $atts );
		$expected_content .= $this->html_field_html( $atts );
		$expected_content .= $this->tags_html( $atts );
		$expected_content .= $this->signature_html( $atts );
		$expected_content .= $this->lookup_to_address_html( $atts );
		$expected_content .= $this->user_info_html( $atts );

		$expected_content .= $this->table_footer();

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="section" include_blank="1"]
	 * This tests the situation where an entry was submitted and a repeating section was left blank
	 * The repeating section may have child entries, but those entries have no values
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 */
	public function test_default_message_with_no_values_in_repeating_section_include_blank() {
		$this->markTestSkipped( 'Make this pass for second beta' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );
		$repeating_section = FrmField::get_id_by_key( 'repeating-section' );

		foreach ( $entry->metas[ $repeating_section ] as $child_id ) {
			// Delete all meta with an item_id of $entry->id
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . 'frm_item_metas', array( 'item_id' => $child_id ) );
		}

		$this->include_extras = array( 'divider' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
			'include_blank' => true,
		);

		$content = $this->get_formatted_content( $atts );

		$expected_content = $this->table_header( $atts );

		$expected_content .= $this->text_field_html( $atts );
		$expected_content .= $this->paragraph_to_website_html( $atts );
		$expected_content .= $this->page_break_html( $atts );
		$expected_content .= $this->pro_fields_divider_html( $atts );
		$expected_content .= $this->dynamic_country_html( $atts );
		$expected_content .= $this->dynamic_state_html( $atts );
		$expected_content .= $this->embedded_form_html( $atts );
		$expected_content .= $this->hidden_field_html( $atts );
		$expected_content .= $this->user_id_html( $atts );
		$expected_content .= $this->html_field_html( $atts );
		$expected_content .= $this->tags_html( $atts );
		$expected_content .= $this->signature_html( $atts );
		$expected_content .= $this->repeating_section_header( $atts );
		//$expected_content .= $this->repeating_field_html( $atts );
		$expected_content .= $this->lookup_to_address_html( $atts );
		$expected_content .= $this->user_info_html( $atts );

		$expected_content .= $this->table_footer();

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests array for API
	 * This tests the situation where an entry was submitted and a repeating section was left blank
	 * The repeating section may have child entries, but those entries have no values
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 */
	public function test_array_with_no_values_in_repeating_section() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$repeating_section = FrmField::get_id_by_key( 'repeating-section' );

		foreach ( $entry->metas[ $repeating_section ] as $child_id ) {
			// Delete all meta with an item_id of $entry->id
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . 'frm_item_metas', array( 'item_id' => $child_id ) );
		}

		$atts = array(
			'id' => $entry->id,
			'format' => 'array',
			'user_info' => false,
			'include_blank' => true,
		);

		$data_array = FrmEntriesController::show_entry_shortcode( $atts );

		$atts['is_repeat_empty'] = true;
		$expected_array = $this->expected_array( $entry, $atts );

		$this->assertSame( $expected_array, $data_array );
	}

	/**
	 * Tests [default-message clickable=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 */
	public function test_default_message_with_clickable() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'clickable' => 1,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 *
	 * @group show-entry-array-format
	 */
	public function test_array_format_for_api_post_entry() {
		$entry = FrmEntry::getOne( 'post-entry-1', true );

		$atts = array(
			'id' => $entry->id,
			'user_info' => false,
			'format' => 'array',
			'include_blank' => true,
		);

		$data_array = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_post_array( $entry, $atts );

		$this->assertSame( $expected_array, $data_array );
	}

	/**
	 * Hide section and clear values in it
	 */
	private function hide_and_clear_section() {
		$entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		// Update conditional logic field
		$text_field_id = FrmField::get_id_by_key( 'text-field' );
		FrmEntryMeta::update_entry_meta( $entry_id, $text_field_id, null, 'Hide Fields' );

		// Clear all conditionally hidden fields
		$rich_text_field_id = FrmField::get_id_by_key( 'rich-text-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $rich_text_field_id );

		$single_file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $single_file_field_id );

		$multi_file_field_id = FrmField::get_id_by_key( 'multi-file-upload-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $multi_file_field_id );

		$number_field_id = FrmField::get_id_by_key( 'number-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $number_field_id );

		$phone_number_field_id = FrmField::get_id_by_key( 'phone-number' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $phone_number_field_id );

		$time_field_id = FrmField::get_id_by_key( 'time-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $time_field_id );

		$date_field_id = FrmField::get_id_by_key( 'date-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $date_field_id );

		$image_url_field_id = FrmField::get_id_by_key( 'image-url' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $image_url_field_id );

		$scale_field_id = FrmField::get_id_by_key( 'scale-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $scale_field_id );

		$tags_field_id = FrmField::get_id_by_key( 'tags-field' );
		FrmEntryMeta::delete_entry_meta( $entry_id, $tags_field_id );
	}

	protected function expected_plain_text_content( $atts ) {
		$content = $this->text_field_plain_text( $atts );
		$content .= $this->paragraph_to_website_plain_text();
		$content .= $this->page_break_plain_text( $atts );
		$content .= $this->pro_fields_divider_plain_text( $atts );
		$content .= $this->dynamic_country_plain_text( $atts );
		$content .= $this->dynamic_state_plain_text( $atts );
		$content .= $this->embedded_form_plain_text( $atts );
		$content .= $this->hidden_field_plain_text( $atts );
		$content .= $this->user_id_plain_text( $atts );
		$content .= $this->html_field_plain_text( $atts );
		$content .= $this->tags_plain_text( $atts );
		$content .= $this->signature_plain_text( $atts );
		$content .= $this->repeating_section_header_plain_text( $atts );
		$content .= $this->repeating_field_plain_text();
		$content .= $this->lookup_to_address_plain_text( $atts );
		$content .= $this->user_info_plain_text( $atts );

		return $content;
	}

	protected function expected_html_content( $atts ) {
		$table = $this->table_header( $atts );

		$table .= $this->text_field_html( $atts );
		$table .= $this->paragraph_to_website_html( $atts );
		$table .= $this->page_break_html( $atts );
		$table .= $this->pro_fields_divider_html( $atts );
		$table .= $this->dynamic_country_html( $atts );
		$table .= $this->dynamic_state_html( $atts );
		// TODO: Dynamic field list?
		$table .= $this->embedded_form_html( $atts );
		$table .= $this->hidden_field_html( $atts );
		$table .= $this->user_id_html( $atts );
		$table .= $this->html_field_html( $atts );
		$table .= $this->tags_html( $atts );
		$table .= $this->signature_html( $atts );
		$table .= $this->repeating_section_header( $atts );
		$table .= $this->repeating_field_html( $atts );
		$table .= $this->lookup_to_address_html( $atts );
		$table .= $this->user_info_html( $atts );

		$table .= $this->table_footer();

		return $table;
	}

	protected function text_field_html( $atts ) {
		return $this->two_cell_table_row( 'text-field', $atts );
	}

	private function paragraph_to_website_html( $atts ) {
		$table = $this->two_cell_table_row( 'paragraph-field', $atts );
		$table .= $this->two_cell_table_row( 'checkbox-colors', $atts );
		$table .= $this->two_cell_table_row( 'radio-button-field', $atts );
		$table .= $this->two_cell_table_row( 'dropdown-field', $atts );
		$table .= $this->two_cell_table_row( 'email-field', $atts );
		$table .= $this->two_cell_table_row( 'website-field', $atts );

		return $table;
	}

	private function page_break_html( $atts ) {
		return $this->one_cell_table_row( 'page-break', $atts );
	}

	private function hidden_field_html( $atts ) {
		return $this->two_cell_table_row( 'hidden-field', $atts );
	}

	private function user_id_html( $atts ) {
		return $this->two_cell_table_row( 'user-id-field', $atts );
	}

	private function lookup_to_address_html( $atts ) {
		$table = $this->two_cell_table_row( 'lookup-country', $atts );
		$table .= $this->two_cell_table_row( 'cb-sep-values', $atts );
		$table .= $this->two_cell_table_row( 'address-field', $atts );

		return $table;
	}

	private function pro_fields_divider_html( $atts ) {
		$html = $this->pro_fields_divider_heading( $atts );
		$html .= $this->fields_within_pro_fields_divider( $atts );

		return $html;
	}

	private function pro_fields_divider_heading( $atts ) {
		return $this->one_cell_table_row( 'pro-fields-divider', $atts );
	}

	private function fields_within_pro_fields_divider( $atts ) {
		$html = $this->two_cell_table_row( 'rich-text-field', $atts );
		$html .= $this->two_cell_table_row( 'single-file-upload-field', $atts );
		$html .= $this->two_cell_table_row( 'multi-file-upload-field', $atts );
		$html .= $this->two_cell_table_row( 'number-field', $atts );
		$html .= $this->two_cell_table_row( 'phone-number', $atts );
		$html .= $this->two_cell_table_row( 'time-field', $atts );
		$html .= $this->two_cell_table_row( 'date-field', $atts );
		$html .= $this->two_cell_table_row( 'image-url', $atts );
		$html .= $this->two_cell_table_row( 'scale-field', $atts );

		return $html;
	}

	private function get_multi_file_urls( $entry ) {
		$file_field_id = FrmField::get_id_by_key( 'multi-file-upload-field' );

		$multi_file_urls = array();
		foreach ( $entry->metas[ $file_field_id ] as $file_id ) {
			$multi_file_urls[] = wp_get_attachment_url( $file_id );
		}

		return $multi_file_urls;
	}

	private function dynamic_country_html( $atts ) {
		return $this->two_cell_table_row( 'dynamic-country', $atts );
	}

	private function dynamic_state_html( $atts ) {
		return $this->two_cell_table_row( 'dynamic-state', $atts );
	}

	private function html_field_html( $atts ) {
		return $this->one_cell_table_row( 'html-field', $atts );
	}

	private function repeating_section_header( $atts ) {
		return $this->one_cell_table_row( 'repeating-section', $atts );
	}

	private function tags_html( $atts ) {
		return $this->two_cell_table_row( 'tags-field', $atts );
	}

	private function signature_html( $atts ) {
		return $this->two_cell_table_row( 'signature-field', $atts );
	}

	private function embedded_form_html( $atts ) {
		$content = '';
		foreach ( $this->fields_in_embedded_form() as $field_key ) {

			if ( $field_key === 'email-information-section' ) {
				$content .= $this->one_cell_table_row( $field_key, $atts );
			} else {
				$content .= $this->two_cell_table_row( $field_key, $atts );
			}
		}

		return $content;
	}

	private function repeating_field_html( $atts ) {
		$html = '';

		if ( isset( $this->exclude_fields['repeating-section'] ) || ( ! empty( $this->include_fields ) && ! isset( $this->include_fields['repeating-section'] ) ) ) {
			return $html;
		}

		if ( ! isset( $this->exclude_fields['repeating-text'] ) ) {
			$html .= $this->two_cell_table_row_for_value( 'Single Line Text', 'First', $atts );
		}
		$html .= $this->two_cell_table_row_for_value( 'Checkboxes', 'Option 1, Option 2', $atts );
		$html .= $this->two_cell_table_row_for_value( 'Date', 'May 27, 2015', $atts );

		if ( ! isset( $this->exclude_fields['repeating-text'] ) ) {
			$html .= $this->two_cell_table_row_for_value( 'Single Line Text', 'Second', $atts );
		}
		$html .= $this->two_cell_table_row_for_value( 'Checkboxes', 'Option 1, Option 2', $atts );
		$html .= $this->two_cell_table_row_for_value( 'Date', 'May 29, 2015', $atts );

		if ( ! isset( $this->exclude_fields['repeating-text'] ) ) {
			$html .= $this->two_cell_table_row_for_value( 'Single Line Text', 'Third', $atts );
		}
		$html .= $this->two_cell_table_row_for_value( 'Checkboxes', 'Option 2', $atts );
		$html .= $this->two_cell_table_row_for_value( 'Date', 'June 19, 2015', $atts );

		return $html;
	}

	/**
	 * @param stdClass $entry
	 * @param stdClass $field
	 * @param array $atts
	 *
	 * @return mixed|string
	 */
	protected function get_field_html_value( $entry, $field, $atts ) {

		switch ( $field->field_key) {
			case 'paragraph-field':
				$value = "Jamie<br/>Rebecca<br/>Wahlin";
				break;

			case 'pro-fields-divider':
				$value = '<h3>Pro Fields</h3>';
				break;

			case 'email-information-section':
				$value = '<h3>Email Information</h3>';
				break;

			case 'rich-text-field':
				$value = '<strong>Bolded text</strong>';
				break;

			case 'page-break':
				$value = '<br/><br/>';
				break;

			case 'repeating-section':
				$value = '<h3>Repeating Section</h3>';
				break;

			case 'address-field':
				$value = '123 Main St. #5 <br/>Anytown, OR <br/>12345 <br/>United States';
				break;

			case 'single-file-upload-field':
				$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
				$single_file_url = wp_get_attachment_url( $atts['entry']->metas[ $file_field_id ] );

				if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
					$value = '<a href="' . $single_file_url . '" rel="nofollow">' . $single_file_url . '</a>';
				} else {
					$value = $single_file_url;
				}
			break;

			case 'multi-file-upload-field':
				$multi_file_urls = $this->get_multi_file_urls( $atts['entry'] );

				if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {

					$value = '';
					foreach ( $multi_file_urls as $multi_file_url ) {
						$value .= '<a href="' . $multi_file_url . '" rel="nofollow">' . $multi_file_url . '</a><br/><br/>';
					}

					$value = preg_replace('/<br\/><br\/>$/', '', $value );
				} else {
					$value = implode( '<br/><br/>', $multi_file_urls );
				}
				break;


			default:
				$value = parent::get_field_html_value( $entry, $field, $atts );
		}

		return $value;

	}

	protected function get_field_value( $entry, $field, $atts ) {
		switch ( $field->field_key) {
			case 'html-field':
				$value = "Lorem ipsum.";
				break;

			case 'email-field':
				if ( isset( $atts[ 'clickable' ] ) && $atts[ 'clickable' ] ) {

					$value = '<a href="mailto:jamie@mail.com">jamie@mail.com</a>';
				} else {
					$value = 'jamie@mail.com';
				}
				break;

			case 'website-field':
				if ( isset( $atts[ 'clickable' ] ) && $atts[ 'clickable' ] ) {
					$value = '<a href="http://www.jamie.com" rel="nofollow">http://www.jamie.com</a>';
				} else {
					$value = 'http://www.jamie.com';
				}
				break;

			case 'tags-field':
				$value = 'Jame';
				break;

			case 'cb-sep-values':
				$value = 'Option 1, Option 2';
				break;

			case 'user-id-field':
				$value = 'admin';
				break;

			case 'dynamic-country':
				$value = 'United States';
				break;

			case 'dynamic-state':
				$value = 'California, Utah';
				break;

			case 'time-field':
				$value = '12:30 AM';
				break;

			case 'date-field':
				$value = 'August 16, 2015';
				break;

			case 'image-url':
				if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
					$value = '<a href="http://www.test.com" rel="nofollow">http://www.test.com</a>';
				} else {
					$value = 'http://www.test.com';
				}
				break;

			case 'contact-name':
				$value = 'Embedded name';
				break;

			case 'contact-last-name':
				$value = 'test';
				break;

			case 'contact-email':
				if ( isset( $atts[ 'clickable' ] ) && $atts[ 'clickable' ] ) {
					$value = '<a href="mailto:test@mail.com">test@mail.com</a>';
				} else {
					$value = 'test@mail.com';
				}
				break;

			case 'contact-subject':
				$value = 'test';
				break;

			case 'contact-message':
				$value = 'test';
				break;

			case 'contact-date':
				$value = 'May 21, 2015';
				break;

			case 'signature-field':
				$value = '398, 150';
				break;

			default:
				$value = parent::get_field_value( $entry, $field, $atts );
		}

		return $value;
	}

	/**
	 * @param stdClass $entry
	 * @param stdClass $field
	 * @param array $atts
	 *
	 * @return mixed|string
	 */
	protected function get_field_plain_text_value( $entry, $field, $atts ) {

		switch ( $field->field_key) {
			case 'page-break':
				$value = "\r\n";
				break;

			case 'html-field':
				$value = "Lorem ipsum.";
				break;

			case 'repeating-section':
				$value = "\r\nRepeating Section";
				break;

			case 'email-information-section':
				$value = "\r\nEmail Information";
				break;

			case 'address-field':
				$value = '123 Main St. #5 Anytown, OR 12345 United States';
				break;

			default:
				$value = parent::get_field_plain_text_value( $entry, $field, $atts );
		}

		return $value;
	}

	private function user_info_html( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$html = '<tr' . $this->tr_style . '><td' . $this->td_style . '>IP Address</td><td' . $this->td_style . '>127.0.0.1</td></tr>' . "\r\n";
			$html .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>User-Agent (Browser/OS)</td><td' . $this->td_style . '>Mozilla Firefox 37.0 / OS X</td></tr>' . "\r\n";
			$html .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Referrer</td><td' . $this->td_style . '>http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd</td></tr>' . "\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	private function text_field_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'text-field', $atts );
	}

	private function paragraph_to_website_plain_text() {
		$content = "Paragraph Text: Jamie\r\nRebecca\r\nWahlin\r\n";
		$content .= "Checkboxes - colors: Red, Green\r\n";
		$content .= "Radio Buttons - dessert: cookies\r\n";
		$content .= "Dropdown: Ace Ventura\r\n";
		$content .= "Email Address: jamie@mail.com\r\n";
		$content .= "Website/URL: http://www.jamie.com\r\n";

		return $content;
	}

	private function pro_fields_divider_plain_text( $atts ) {
		$content = '';
		if ( in_array( 'divider', $this->include_extras ) ) {
			$content .= "\r\nPro Fields\r\n";
		}

		$content .= "Rich Text: Bolded text\r\n";

		$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		$single_file_url = wp_get_attachment_url( $atts['entry']->metas[ $file_field_id ] );
		$content .= "Single File Upload: " . $single_file_url . "\r\n";

		$multiple_file_urls = $this->get_multi_file_urls( $atts['entry'] );
		$content .= "Multiple File Upload: " . implode( ', ', $multiple_file_urls ) . "\r\n";

		$content .= $this->label_and_value_plain_text_row( 'number-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'phone-number', $atts );
		$content .= $this->label_and_value_plain_text_row( 'time-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'date-field', $atts );
		$content .= "Image URL: http://www.test.com\r\n";
		$content .= "Scale: 5\r\n";

		return $content;
	}


	private function page_break_plain_text( $atts ) {
		return $this->single_value_plain_text_row( 'page-break', $atts );
	}

	private function html_field_plain_text( $atts ) {
		return $this->single_value_plain_text_row( 'html-field', $atts );
	}

	private function dynamic_country_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'dynamic-country', $atts );
	}

	private function dynamic_state_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'dynamic-state', $atts );
	}

	private function embedded_form_plain_text( $atts ) {
		$content = '';
		foreach ( $this->fields_in_embedded_form() as $field_key ) {

			if ( $field_key === 'email-information-section' ) {
				$content .= $this->single_value_plain_text_row( $field_key, $atts );
			} else {
				$content .= $this->label_and_value_plain_text_row( $field_key, $atts );
			}
		}

		return $content;
	}

	private function repeating_section_header_plain_text( $atts ) {
		return $this->single_value_plain_text_row( 'repeating-section', $atts );
	}

	private function hidden_field_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'hidden-field', $atts );
	}

	private function user_id_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'user-id-field', $atts );
	}

	private function tags_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'tags-field', $atts );
	}

	private function signature_plain_text( $atts ) {
		return $this->label_and_value_plain_text_row( 'signature-field', $atts );
	}

	private function repeating_field_plain_text() {
		$content = "Single Line Text: First\r\n";
		$content .= "Checkboxes: Option 1, Option 2\r\n";
		$content .= "Date: May 27, 2015\r\n";
		$content .= "Single Line Text: Second\r\n";
		$content .= "Checkboxes: Option 1, Option 2\r\n";
		$content .= "Date: May 29, 2015\r\n";
		$content .= "Single Line Text: Third\r\n";
		$content .= "Checkboxes: Option 2\r\n";
		$content .= "Date: June 19, 2015\r\n";

		return $content;
	}

	private function lookup_to_address_plain_text( $atts ) {
		$content = $this->label_and_value_plain_text_row( 'lookup-country', $atts );
		$content .= $this->label_and_value_plain_text_row( 'cb-sep-values', $atts );
		$content .= $this->label_and_value_plain_text_row( 'address-field', $atts );

		return $content;
	}

	private function user_info_plain_text( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$content = "IP Address: 127.0.0.1\r\n";
			$content .= "User-Agent (Browser/OS): Mozilla Firefox 37.0 / OS X\r\n";
			$content .= "Referrer: http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd\r\n";
		} else {
			$content = '';
		}

		return $content;
	}

	private function expected_html_content_for_post_entry( $atts ) {
		$table = $this->table_header( $atts );
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Title</td><td' . $this->td_style . '>Jamie\'s Post</td></tr>' . "\r\n";
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Content</td><td' . $this->td_style . '>Hello! My name is Jamie.</td></tr>' . "\r\n";
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Author</td><td' . $this->td_style . '>Jamie Wahlin</td></tr>' . "\r\n";
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>User ID</td><td' . $this->td_style . '>admin</td></tr>' . "\r\n";
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Category</td><td' . $this->td_style . '><a href="http://example.org/?cat=1" title="View all posts filed under Uncategorized">Uncategorized</a></td></tr>' . "\r\n";
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Parent Dynamic Field</td><td' . $this->td_style . '><a href="http://example.org/?cat=1" title="View all posts filed under Uncategorized">Uncategorized</a></td></tr>' . "\r\n";
		$table .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Post Status</td><td' . $this->td_style . '>Published</td></tr>' . "\r\n";
		$table .= $this->table_footer();

		return $table;
	}

	/**
	 * Get the expected default HTML or plain text shortcodes
	 *
	 * @param string $type
	 * @param array $atts
	 *
	 * @return string
	 */
	protected function get_expected_default_shortcodes( $type, $atts ) {
		$content = '';

		$fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		if ( $type === 'html' ) {
			$content = $this->table_header( $atts );
		}

		$in_repeating_section = 0;
		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'form', 'divider', 'break', 'end_divider', 'password', 'captcha', 'credit_card' ) ) ) {
				if ( $field->type == 'divider' ) {

					$content .= '[if ' . $field->id . ']';
					$content .= $this->table_row_start_tags( $type, $field );

					if ( $type === 'html' ) {
						$content .= '<h3>[' . $field->id . ' show=description]</h3>';
					} else {
						$content .= '[' . $field->id . ' show=description]';
					}

					$content .= $this->table_row_end_tags( $type );
					$content .= "\r\n" . '[/if ' . $field->id . ']';
					$content .= $this->after_table_row_tags( $type );

					if ( FrmField::is_repeating_field( $field ) ) {
						$in_repeating_section = $field->id;
						$content .= '[foreach ' . $field->id . ']';
					}
				} else if ( $in_repeating_section > 0 && $field->type == 'end_divider' ) {
					$content .= '[/foreach ' . $in_repeating_section . ']';
					$in_repeating_section = 0;
				} else if ( $field->type == 'break' ) {
					if ( $type === 'plain' ) {
						continue;
					}

					$content .= '[if ' . $field->id . ']';
					$content .= $this->table_row_start_tags( $type, $field );
					$content .= '<br/><br/>';
					$content .= $this->table_row_end_tags( $type );
					$content .= "\r\n" . '[/if ' . $field->id . ']';
					$content .= $this->after_table_row_tags( $type );
				}

				continue;
			}

			$content .= '[if ' . $field->id . ']';
			$content .= $this->table_row_start_tags( $type, $field );
			$content .= '[' . $field->id . ' show=field_label]';
			$content .= $this->cell_separator( $type );

			if ( $field->type == 'data' && $field->field_options['data_type'] == 'data' ) {
				$content .= '[' . $field->field_options['hide_field'][0] . ' show=' . $field->field_options['form_select'] . ']';
			} else {
				$content .= '[' . $field->id . ']';
			}

			$content .= $this->table_row_end_tags( $type );
			$content .= "\r\n" . '[/if ' . $field->id . ']';
			$content .= $this->after_table_row_tags( $type );
		}

		if ( $type === 'html' ) {
			$content .= $this->table_footer();
		}

		return $content;
	}

	protected function table_row_start_tags( $type, $field ) {
		if ( $type === 'html' ) {
			$html = '<tr style="[frm-alt-color]">';

			if ( $field->type === 'divider' || $field->type === 'break' ) {
				$html .= '<td colspan="2"' . $this->td_style . '>';
			} else {
				$html .= '<td' . $this->td_style . '>';
			}
		} else {
			$html = '';
		}

		return $html;
	}

	protected function expected_array( $entry, $atts ) {

		// Single file upload field
		$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		$single_file_url = wp_get_attachment_url( $entry->metas[ $file_field_id ] );

		// Multi file upload field
		$multi_file_field_id = FrmField::get_id_by_key( 'multi-file-upload-field' );
		$multi_file_urls = $this->get_multi_file_urls( $entry );

		// Dynamic Country
		$where = array( 'meta_value' => 'United States', 'field_id' => FrmField::get_id_by_key( '2atiqt' ) );
		$dynamic_country_id = FrmDb::get_var( 'frm_item_metas', $where, 'item_id' );

		// Dynamic State Field ID
		$dynamic_state_id = FrmField::get_id_by_key( 'dynamic-state' );

		$expected = array(
			'text-field' => 'Jamie',
			'paragraph-field' => "Jamie\r\nRebecca\r\nWahlin",
			'checkbox-colors' => array ( 'Red', 'Green' ),
			'radio-button-field' => 'cookies',
			'dropdown-field' => 'Ace Ventura',
			'email-field' => 'jamie@mail.com',
			'website-field' => 'http://www.jamie.com',
			'rich-text-field' => 'Bolded text',
			'rich-text-field-value' => '<strong>Bolded text</strong>',
			'single-file-upload-field' => $single_file_url,
			'single-file-upload-field-value' => $entry->metas[ $file_field_id ],
			'multi-file-upload-field' => $multi_file_urls,
			'multi-file-upload-field-value' => $entry->metas[ $multi_file_field_id ],
			'number-field' => '11',
			'phone-number' => '1231231234',
			'time-field' => '12:30 AM',
			'time-field-value' => '00:30',
			'date-field' => 'August 16, 2015',
			'date-field-value' => '2015-08-16',
			'image-url' => 'http://www.test.com',
			'scale-field' => '5',
			'dynamic-country' => 'United States',
			'dynamic-country-value' => $dynamic_country_id,
			'dynamic-state' => array ( 'California', 'Utah' ),
			'dynamic-state-value' => $entry->metas[ $dynamic_state_id ],
			'dynamic-city' => '',
			'qfn4lg' => '',
			'contact-name' => 'Embedded name',
			'contact-last-name' => 'test',
			'contact-email' => 'test@mail.com',
			'contact-website' => '',
			'contact-subject' => 'test',
			'contact-message' => 'test',
			'contact-date' => 'May 21, 2015',
			'contact-date-value' => '2015-05-21',
			'contact-user-id' => '',
			'hidden-field' => 'Hidden value',
			'user-id-field' => 'admin',
			'user-id-field-value' => '1',
			'tags-field' => 'Jame',
			'signature-field' => array(
				'width' => '398',
				'height' => '150',
			),
			'repeating-section' => array(
				0 => array(
					'repeating-text' => 'First',
					'repeating-checkbox' => array( 'Option 1', 'Option 2' ),
					'repeating-date' => 'May 27, 2015',
					'repeating-date-value' => '2015-05-27',
				),
				1 => array(
					'repeating-text' => 'Second',
					'repeating-checkbox' => array( 'Option 1', 'Option 2' ),
					'repeating-date' => 'May 29, 2015',
					'repeating-date-value' => '2015-05-29',
				),
				2 => array(
					'repeating-text' => 'Third',
					'repeating-checkbox' => array( 'Option 2' ),
					'repeating-date' => 'June 19, 2015',
					'repeating-date-value' => '2015-06-19',
				),
			),
			'repeating-text' => array( 'First', 'Second', 'Third' ),
			'repeating-checkbox' => array( array( 'Option 1', 'Option 2' ), array( 'Option 1', 'Option 2' ), array( 'Option 2') ),
			'repeating-date' => array( 'May 27, 2015', 'May 29, 2015', 'June 19, 2015' ),
			'repeating-date-value' => array( '2015-05-27', '2015-05-29', '2015-06-19' ),
			'lookup-country' => array( 'United States'),
			'cb-sep-values' => array( 'Option 1', 'Option 2' ),
			'cb-sep-values-value' => array( 'Red', 'Orange' ),
			'address-field' => '123 Main St. #5 Anytown, OR 12345 United States',
			'address-field-value' => array (
				'line1' => '123 Main St. #5',
				'city' => 'Anytown',
				'state' => 'OR',
				'zip' => '12345',
				'country' => 'United States',
    		),
		);

		$this->remove_repeating_fields( $atts, $expected );

		if ( ! isset( $atts['include_blank'] ) || $atts['include_blank'] == false ) {
			foreach ( $expected as $field_key => $value ) {
				if ( $value == '' || empty( $value ) ) {
					unset( $expected[ $field_key ] );
				}
			}
		}

		return $expected;
	}

	private function remove_repeating_fields( $atts, &$expected ) {
		if ( isset( $atts['is_repeat_empty'] ) && $atts['is_repeat_empty'] ) {

			$child_values = array(
				'repeating-text' => '',
				'repeating-checkbox' => '',
				'repeating-date' => '',
			);

			$expected['repeating-section'] = array( $child_values, $child_values, $child_values );
			$expected['repeating-text'] = array( '', '', '' );
			$expected['repeating-checkbox'] = array( '', '', '' );
			$expected['repeating-date'] = array( '', '', '' );
			unset( $expected['repeating-date-value'] );
		}
	}

	private function expected_post_array( $entry, $atts ) {
		$expected = array(
			'yi6yvm' => 'Jamie\'s Post',
			'knzfvv' => 'Hello! My name is Jamie.',
			'8j2k9i' => '',
			'37pxx2' => 'Jamie Wahlin',
			'ml8awj' => 'admin',
			'ml8awj-value' => '1',
			'rs4jgc' => '',
			'izzcad' => 'Uncategorized',
			'izzcad-value' => array( 1 ),
			// TODO: displayed value for categories. Should categories by in array?
			'parent-dynamic-taxonomy' => 'Uncategorized',
			'parent-dynamic-taxonomy-value' => array( 1 ),
			'child-dynamic-taxonomy' => '',
			'grandchild-dynamic-taxonomy' => '',
			'post-status-dropdown' => 'Published',
			'post-status-dropdown-value' => 'publish',
		);

		return $expected;
	}

	protected function expected_default_array( $atts ) {
		$fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		$expected = array();

		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'form', 'divider', 'break', 'end_divider', 'password', 'captcha', 'credit_card' ) ) ) {
				if ( FrmField::is_repeating_field( $field ) ) {
					// TODO: do something different for repeating sections?
				}
				continue;
			}

			if ( $field->type == 'data' && $field->field_options['data_type'] == 'data' ) {

				$expected[ $field->id ] = array(
					'label' => '[' . $field->id . ' show=field_label]',
					'val' => '[' . $field->field_options['hide_field'][0] . ' show=' . $field->field_options['form_select'] . ']',
					'type' => $field->type,
				);

			} else {
				$expected[ $field->id ] = array(
					'label' => '[' . $field->id . ' show=field_label]',
					'val' => '[' . $field->id . ']',
					'type' => $field->type,
				);
			}

		}

		return $expected;
	}

	protected function get_test_entry( $include_meta ) {
		return FrmEntry::getOne( 'jamie_entry_key', $include_meta );
	}

	protected function get_included_fields( $type ) {
		$include_fields = array(
			'text-field' => 'text-field',
			'repeating-section' => 'repeating-section',
			'embed-form-field' => 'embed-form-field',
			'user-id-field' => 'user-id-field',
		);

		$this->convert_field_array( $type, $include_fields );

		return $include_fields;
	}

	protected function get_single_included_field( $type ) {
		$include_fields = array(
			'text-field' => 'text-field',
		);

		$this->convert_field_array( $type, $include_fields );

		return $include_fields;
	}

	protected function get_form_id_for_test() {
		return FrmForm::getIdByKey( 'all_field_types' );
	}

	protected function one_cell_table_row( $field_key, $atts ) {
		$field = FrmField::getOne( $field_key );
		$field_value = $this->get_field_html_value( $atts['entry'], $field, $atts );

		if ( ! $this->is_field_included( $atts, $field, $field_value ) ) {
			return '';
		}

		$html = '<tr' . $this->tr_style . '>';
		$html .= '<td colspan="2"' . $this->td_style . '>' . $field_value . '</td>';
		$html .= '</tr>' . "\r\n";

		return $html;
	}

	protected function is_self_or_parent_in_array( $field_key, $array ) {
		if ( in_array( $field_key, array_keys( $array ) ) ) {
			$in_array = true;
		} else if ( in_array( $field_key, $this->fields_in_pro_divider() ) && in_array( 'pro-fields-divider', array_keys( $array ) ) ) {
			$in_array = true;
		} else if ( in_array( $field_key, $this->fields_in_repeating_section() ) && in_array( 'repeating-section', array_keys( $array ) ) ) {
			$in_array = true;
		} else if ( in_array( $field_key, $this->fields_in_embedded_form() ) && in_array( 'embed-form-field', array_keys( $array ) ) ) {
			$in_array = true;
		} else {
			$in_array = false;
		}

		return $in_array;

	}

	private function fields_in_repeating_section() {
		return array(
			'repeating-text',
			'repeating-checkbox',
			'repeating-date',
		);
	}

	private function fields_in_embedded_form() {
		return array(
			'contact-name',
			'contact-last-name',
			'contact-email',
			'contact-website',
			'email-information-section',
			'contact-subject',
			'contact-message',
			'contact-date',
			'contact-user-id',
		);
	}

	private function fields_in_pro_divider() {
		return array(
			'rich-text-field',
			'single-file-upload-field',
			'multi-file-upload-field',
			'number-field',
			'phone-number',
			'time-field',
			'date-field',
			'image-url',
			'scale-field',
			);
	}
}