<?php

/**
 * @since 3.0
 *
 * @group shortcodes
 * @group entries
 * @group show-entry-shortcode-free
 * @group free
 * TODO: DRY
 *
 */
class test_FrmShowEntryShortcode extends FrmUnitTest {

	protected $include_fields = array();
	protected $exclude_fields = array();
	protected $tr_style = ' style="background-color:#ffffff;"';
	protected $td_style = ' style="text-align:left;color:#555555;padding:7px 9px;vertical-align:top;border-top:1px solid #cccccc;"';

	/**
	 * Tests no entry or id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_no_entry_or_id_passed() {
		$atts = array(
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = '';

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests no id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_no_id_passed() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests fake id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_fake_id_passed() {
		$atts = array(
			'id' => 'jfie09293jf',
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = '';

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests no entry passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_no_entry_passed() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );

		$atts['entry'] = $entry;
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests no meta passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_no_meta_passed() {
		$entry = $this->get_test_entry( false );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );

		$atts['entry'] = FrmEntry::getOne( $entry->id, true );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_basic_default_message_parameters_all_field_types() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="html"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_extras_included() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section, page, html',
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_specific_field_ids_included() {
		$entry = $this->get_test_entry( true );

		$this->include_fields = $this->get_included_fields( 'id ');

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_specific_field_keys_included() {
		$entry = $this->get_test_entry( true );

		$this->include_fields = $this->get_included_fields( 'key' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_old_fields_parameter() {
		$entry = $this->get_test_entry( true );

		$this->include_fields = $this->get_included_fields( 'object' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'fields' => $this->include_fields,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_old_fields_parameter_single_field() {
		$entry = $this->get_test_entry( true );

		$this->include_fields = $this->get_single_included_field( 'object' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'fields' => $this->include_fields,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_specific_field_ids_excluded() {
		$entry = $this->get_test_entry( true );

		$this->exclude_fields = $this->get_excluded_fields( 'id' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'exclude_fields' => implode( ',', $this->exclude_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_exclude_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_specific_field_keys_excluded() {
		$entry = $this->get_test_entry( true );

		$this->exclude_fields = $this->get_excluded_fields( 'key' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'exclude_fields' => implode( ',', $this->exclude_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_exclude_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_specific_field_ids_included_and_include_extras() {
		$entry = $this->get_test_entry( true );

		$this->include_fields = $this->get_included_fields( 'id' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section, page, html',
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message font_size, text_color, border_width, border_color, bg_color]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_styling_changes() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'font_size' => '20px',
			'text_color' => '#00ff00',
			'border_width' => '3px',
			'border_color' => '#FF69B4',
			'bg_color' => '#0000ff',
			'alt_bg_color' => '#0000ff',
		);

		$this->tr_style = str_replace( 'background-color:#ffffff', 'background-color:' . $atts['bg_color'], $this->tr_style );
		$this->td_style = str_replace( '#555555', $atts['text_color'], $this->td_style );
		$this->td_style = str_replace( '1px solid #cccccc', $atts['border_width'] . ' solid ' . $atts['border_color'], $this->td_style );

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message inline_style=0]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_inline_style_off() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'inline_style' => false,
		);

		$content = $this->get_formatted_content( $atts );

		$this->td_style = '';
		$this->tr_style = '';
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message user_info=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_user_info() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => true,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message plain_text=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_plain_text() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => true,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_plain_text_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message plain_text=1 include_extras="page,section,html"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_plain_text_and_include_extras() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => true,
			'user_info' => false,
			'include_extras' => 'page,section,html',
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_plain_text_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message direction=rtl]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_message_with_rtl_direction() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'direction' => 'rtl',
		);

		$content = $this->get_formatted_content( $atts );

		$this->td_style = str_replace( 'text-align:left', 'text-align:right', $this->td_style );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests Default HTML for emails
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_html_for_email() {
		$form_id = $this->get_form_id_for_test();

		$atts = array(
			'form_id' => $form_id,
			'default_email' => true,
			'plain_text' => false,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->get_expected_default_html( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests Default HTML for emails
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.04
	 */
	public function test_default_plain_for_email() {
		$form_id = FrmForm::getIdByKey( 'all_field_types' );

		$atts = array(
			'form_id' => $form_id,
			'default_email' => true,
			'plain_text' => true,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->get_expected_default_plain( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_array_format_for_api() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'user_info' => false,
			'format' => 'array',
			'include_blank' => true,
		);

		$data_array = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_array( $entry, $atts );

		$this->assertSame( $expected_array, $data_array );
	}

	/**
	 * Tests the way an API action gets the default HTML
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_default_array_for_api() {
		$form_id = $this->get_form_id_for_test();

		$atts = array(
			'form_id' => $form_id,
			'user_info' => false,
			'format' => 'array',
			'default_email' => true,
		);

		$data_array = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_default_array( $atts );

		$this->assertSame( $expected_array, $data_array );
	}

	/**
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_api_entry_retrieval() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'fields' => FrmField::get_all_for_form( $entry->form_id, '', 'include' ),
			'user_info' => false,
			'format' => 'array',
			'include_blank' => true,
		);

		$data_array = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_array( $entry, $atts );

		$this->assertSame( $expected_array, $data_array );
	}

	/**
	 * Tests the way Zapier gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_array_format_for_zapier() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'user_info' => false,
			'format' => 'array',
			'include_blank' => true,
		);

		$data_array = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_array( $entry, $atts );

		$this->assertSame( $expected_array, $data_array );
	}


	/**
	 * Tests the json format
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 3.0
	 */
	public function test_json_format() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id' => $entry->id,
			'user_info' => false,
			'format' => 'json',
			'include_blank' => true,
		);

		$actual_json = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_json = $this->expected_json( $entry, $atts );

		$this->assertSame( $expected_json, $actual_json );
	}


	protected function get_test_entry( $include_meta ) {
		$new_entry = array(
			'form_id'  => FrmForm::getIdByKey( 'free_field_types' ),
			'item_key' => 'jamie_entry_key',
			'description' => array(
				'browser' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0',
				'referrer' => 'http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd',
			),
			'created_at'  => '2015-05-12 19:30:23',
			'item_meta' => $this->expected_free_meta(),
		);

		$entry_id = $this->factory->entry->create_object( $new_entry );

		return FrmEntry::getOne( $entry_id, $include_meta );
	}

	private function expected_free_meta() {
		return array(
			FrmField::get_id_by_key( 'free-text-field' ) => 'Test Testerson',
			FrmField::get_id_by_key( 'free-paragraph-field' ) => "Test\r\nMiddle\r\nTesterson",
			FrmField::get_id_by_key( 'free-checkboxes' ) => array ( 'Red', 'Green' ),
			FrmField::get_id_by_key( 'free-radio-button-field' ) => 'cookies',
			FrmField::get_id_by_key( 'free-dropdown-field' ) => 'Ace Ventura',
			FrmField::get_id_by_key( 'free-email-field' ) => 'jamie@mail.com',
			FrmField::get_id_by_key( 'free-website-field' ) => 'http://www.jamie.com',
			FrmField::get_id_by_key( 'free-number-field' ) => '11',
			FrmField::get_id_by_key( 'free-phone-field' ) => '1231231234',
			FrmField::get_id_by_key( 'free-hidden-field' ) => '',
			//FrmField::get_id_by_key( 'free-user-id-field' ) => '1',
		);
	}

	protected function get_formatted_content( $atts ) {
		return FrmEntriesController::show_entry_shortcode( $atts );
	}

	protected function expected_html_content( $atts ) {
		$table = $this->table_header( $atts );

		$table .= $this->two_cell_table_row( 'free-text-field', $atts );
		$table .= $this->two_cell_table_row( 'free-paragraph-field', $atts );
		$table .= $this->two_cell_table_row( 'free-checkboxes', $atts );
		$table .= $this->two_cell_table_row( 'free-radio-button-field', $atts );
		$table .= $this->two_cell_table_row( 'free-dropdown-field', $atts );
		$table .= $this->two_cell_table_row( 'free-email-field', $atts );
		$table .= $this->two_cell_table_row( 'free-website-field', $atts );
		$table .= $this->two_cell_table_row( 'free-number-field', $atts );
		$table .= $this->two_cell_table_row( 'free-phone-field', $atts );
		$table .= $this->two_cell_table_row( 'free-hidden-field', $atts );
		//$table .= $this->standard_field_html( 'free-user-id-field', $atts );
		$table .= $this->html_field_row( $atts );

		$table .= $this->user_info_rows( $atts );

		$table .= $this->table_footer();

		return $table;
	}

	protected function expected_plain_text_content( $atts ) {
		$content = $this->standard_plain_text_row( 'free-text-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-paragraph-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-checkboxes', $atts );
		$content .= $this->standard_plain_text_row( 'free-radio-button-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-dropdown-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-email-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-website-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-number-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-phone-field', $atts );
		$content .= $this->standard_plain_text_row( 'free-hidden-field', $atts );
		$content .= $this->html_field_plain_text_row( $atts );

		$content .= $this->user_info_plain_text_rows( $atts );

		return $content;
	}

	protected function table_header( $atts ) {
		if ( isset( $atts['plain_text'] ) && $atts['plain_text'] ) {
			return '';
		}

		$font_size = isset( $atts['font_size'] ) ? $atts['font_size'] : '14px';
		$border_width = isset( $atts['border_width'] ) ? $atts['border_width'] : '1px';
		$border_color = isset( $atts['border_color'] ) ? $atts['border_color'] : '#cccccc';

		$header = '<table cellspacing="0"';

		if ( ! isset( $atts['inline_style'] ) || $atts['inline_style'] == true ) {
			$header .= ' style="font-size:' . $font_size . ';line-height:135%;';
			$header .= 'border-bottom:' . $border_width . ' solid ' . $border_color . ';"';
		}

		$header .= '><tbody>' . "\r\n";

		return $header;
	}

	protected function table_footer() {
		return '</tbody></table>';
	}

	protected function two_cell_table_row( $field_key, $atts ) {
		$field = FrmField::getOne( $field_key );
		$field_value = $this->get_field_value( $atts['entry'], $field->id, 'html' );

		if ( ! $this->is_field_included( $atts, $field_key, $field_value ) ) {
			return '';
		}

		$html = '<tr' . $this->tr_style . '>';

		if ( isset( $atts['direction'] ) && $atts['direction'] == 'rtl' ) {
			$first = $field_value;
			$second = $field->name;
		} else {
			$first = $field->name;
			$second = $field_value;
		}

		$html .= '<td' . $this->td_style . '>' . $first . '</td>';
		$html .= '<td' . $this->td_style . '>' . $second . '</td>';

		$html .= '</tr>' . "\r\n";

		return $html;
	}

	protected function standard_plain_text_row( $field_key, $atts ) {
		$field = FrmField::getOne( $field_key );
		$field_value = $this->get_field_value( $atts['entry'], $field->id, 'plain_text' );

		if ( ! $this->is_field_included( $atts, $field_key, $field_value ) ) {
			return '';
		}

		if ( isset( $atts['direction'] ) && $atts['direction'] == 'rtl' ) {
			$content = $field_value . ': ' . $field->name;
		} else {
			$content = $field->name .': ' . $field_value;
		}

		$content .= "\r\n";

		return $content;
	}

	protected function is_field_included( $atts, $field_key, $field_value ) {
		$include = true;

		if ( ! empty( $this->include_fields ) && ! in_array( $field_key, array_keys( $this->include_fields ) ) ) {
			$include = false;
		} else if ( ! empty( $this->exclude_fields ) && in_array( $field_key, array_keys( $this->exclude_fields ) ) ) {
			$include = false;
		} else if ( FrmAppHelper::is_empty_value( $field_value, '' ) ) {
			if ( ! isset( $atts['include_blank'] ) || $atts['include_blank'] == false ) {
				$include = false;
			}
		}

		return $include;
	}

	protected function html_field_row( $atts ) {
		$field_value = 'Lorem ipsum.';
		if ( ! $this->is_field_included( $atts, 'free-html-field', $field_value ) ) {
			return '';
		}

		$html = '';

		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'html' ) !== false ) {
			$html .= '<tr' . $this->tr_style . '><td colspan="2"' . $this->td_style . '>' . $field_value . '</td></tr>' . "\r\n";
		}

		return $html;
	}

	protected function html_field_plain_text_row( $atts ) {
		$field_value = 'Lorem ipsum.';
		if ( ! $this->is_field_included( $atts, 'free-html-field', $field_value ) ) {
			return '';
		}

		$html = '';

		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'html' ) !== false ) {
			$html = "Lorem ipsum.\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	protected function user_info_rows( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$html = '<tr' . $this->tr_style . '><td' . $this->td_style . '>IP Address</td><td' . $this->td_style . '>127.0.0.1</td></tr>' . "\r\n";
			$html .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>User-Agent (Browser/OS)</td><td' . $this->td_style . '>Mozilla Firefox 37.0 / OS X</td></tr>' . "\r\n";
			$html .= '<tr' . $this->tr_style . '><td' . $this->td_style . '>Referrer</td><td' . $this->td_style . '>http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd</td></tr>' . "\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	protected function user_info_plain_text_rows( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$content = "IP Address: 127.0.0.1\r\n";
			$content .= "User-Agent (Browser/OS): Mozilla Firefox 37.0 / OS X\r\n";
			$content .= "Referrer: http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd\r\n";
		} else {
			$content = '';
		}

		return $content;
	}

	protected function get_field_value( $entry, $field_id, $type ) {
		$field_value = isset( $entry->metas[ $field_id ] ) ? $entry->metas[ $field_id ] : '';

		if ( is_array( $field_value ) ) {
			$field_value = implode( ', ', $field_value );
		}

		if ( $type === 'html' ) {
			$field_value = str_replace( array( "\r\n", "\n" ), '<br/>', $field_value );
		}

		return $field_value;
	}

	protected function get_included_fields( $type ) {
		$include_fields = array(
			'free-text-field' => 'free-text-field',
			'free-checkboxes' => 'free-checkboxes',
			//'free-user-id-field' => FrmField::get_id_by_key( 'free-user-id-field' ),
		);

		$this->convert_field_array( $type, $include_fields );

		return $include_fields;
	}

	protected function get_excluded_fields( $type ) {
		return $this->get_included_fields( $type );
	}

	protected function get_single_included_field( $type ) {
		$include_fields = array(
			'free-text-field' => 'free-text-field',
		);

		$this->convert_field_array( $type, $include_fields );

		return $include_fields;
	}

	protected function convert_field_array( $type, &$include_fields ) {
		if ( $type === 'id' ) {
			$this->convert_field_keys_to_ids( $include_fields );
		} else if ( $type === 'object' ) {
			$this->convert_field_keys_to_objects( $include_fields );
		}
	}

	protected function convert_field_keys_to_ids( &$array ) {
		foreach ( $array as $key => $field_key ) {
			$array[ $key ] = FrmField::get_id_by_key( $field_key );
		}
	}

	protected function convert_field_keys_to_objects( &$array ) {
		foreach ( $array as $key => $field_key ) {
			$array[ $key ] = FrmField::getOne( $field_key );
		}
	}

	protected function expected_content_for_include_fields( $atts ) {
		return $this->expected_html_content( $atts );
	}

	protected function expected_content_for_exclude_fields( $atts ) {
		return $this->expected_html_content( $atts );
	}

	protected function get_expected_default_html( $atts ) {
		$fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		if ( $type === 'html' ) {
			$content = $this->table_header( $atts );
		}

		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'captcha' ) ) ) {
				continue;
			}

			$html .= '[if ' . $field->id . ']<tr style="[frm-alt-color]">';
			$html .= '<td' . $this->td_style . '>[' . $field->id . ' show=field_label]</td>';
			$html .= '<td' . $this->td_style . '>[' . $field->id . ']</td>';
			$html .= '</tr>' . "\r\n" . '[/if ' . $field->id . ']' . "\r\n";;
		}

	private function table_row_end_tags( $type ) {
		$html = '';

		if ( $type === 'html' ) {
			$html .= '</td></tr>';
		}

		return $html;
	}

	private function after_table_row_tags( $type ) {
		$html = '';

		if ( $type === 'html' ) {
			$html .= "\r\n";
		}

		return $html;
	}

	protected function expected_default_array( $atts ) {
		$fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		$expected = array();

		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'captcha' ) ) ) {
				continue;
			}

			$expected[ $field->id ] = array(
				'label' => '[' . $field->id . ' show=field_label]',
				'val' => '[' . $field->id . ']',
				'type' => $field->type,
			);
		}

		return $expected;
	}

	protected function expected_array( $entry, $atts ) {
		$expected = array(
			'free-text-field' => 'Test Testerson',
			'free-paragraph-field' => "Test\r\nMiddle\r\nTesterson",
			'free-checkboxes' => array ( 'Red', 'Green' ),
			'free-radio-button-field' => 'cookies',
			'free-dropdown-field' => 'Ace Ventura',
			'free-email-field' => 'jamie@mail.com',
			'free-website-field' => 'http://www.jamie.com',
			'free-number-field' => '11',
			'free-phone-field' => '1231231234',
			'free-hidden-field' => '',
			'free-user-id-field' => '',
		);

		if ( ! isset( $atts['include_blank'] ) || $atts['include_blank'] == false ) {
			foreach ( $expected as $field_key => $value ) {
				if ( $value == '' || empty( $value ) ) {
					unset( $expected[ $field_key ] );
				}
			}
		}

		return $expected;
	}

	protected function expected_json( $entry, $atts ) {
		$array = $this->expected_array( $entry, $atts );
		return json_encode( $array );
	}

	protected function get_form_id_for_test() {
		return FrmForm::getIdByKey( 'free_field_types' );
	}


}