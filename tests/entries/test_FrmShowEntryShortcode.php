<?php

/**
 * @since 2.03.11
 *
 * @group shortcodes
 * @group entries
 * @group show-entry-shortcode
 *
 */
class test_FrmShowEntryShortcode extends FrmUnitTest {

	// TODO:
	// 1) UML diagram.
	// 2) Assess overall organization.
	// 3) Add PHP docs.
	// 4) Check all TODO items
	// 5) Deprecate necessary functions and hooks

	// TODO: try including a field from inside a repeating section. It's not yet possible to display a single field from inside a repeating section
	// TODO: try including a field from inside an embedded form
	// TODO: write test for each bug that I'm fixing
	// TODO: section with no fields in it
	// TODO: add is_visible for default HTML

	private $text_field_id = '';
	private $tr_style = 'style="background-color:#ffffff;"';
	private $td_style = ' style="text-align:left;color:#555555;padding:7px 9px;vertical-align:top;border-top:1px solid #cccccc;"';

	public function setUp() {
		parent::setUp();

		$this->text_field_id = FrmField::get_id_by_key( 'text-field' );
	}

	/**
	 * Tests no entry or id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
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
	 * @since 2.03.11
	 */
	public function test_no_id_passed() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
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
	 * @since 2.03.11
	 */
	public function test_no_entry_passed() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
	 */
	public function test_no_meta_passed() {
		$entry = FrmEntry::getOne( 'jamie_entry_key' );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
		);

		$content = $this->get_formatted_content( $atts );

		$meta_entry = FrmEntry::getOne( 'jamie_entry_key', true );
		$atts['entry'] = $meta_entry;
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 *
	 * @group basic-show-entry-for-email
	 */
	public function test_basic_default_message_parameters_all_field_types() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * Tests [default-message]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
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
	 * Tests [default-message include_extras="section, page, html"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 *
	 * @group show-entry-shortcode-include-extras
	 */
	public function test_default_message_with_extras_included() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
	 */
	public function test_default_message_with_specific_field_ids_included() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-section' => FrmField::get_id_by_key( 'repeating-section' ),
			'embed-form-field' => FrmField::get_id_by_key( 'embed-form-field' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_fields' => implode( ',', $include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_repeating_field_id_included() {
		$this->markTestSkipped( 'Functionality not added yet.' );
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-text' => FrmField::get_id_by_key( 'repeating-text' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_fields' => implode( ',', $include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_specific_field_keys_included() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => 'text-field',
			'repeating-section' => 'repeating-section',
			'embed-form-field' => 'embed-form-field',
			'user-id-field' => 'user-id-field',
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_fields' => implode( ',', $include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_old_fields_parameter() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => FrmField::getOne( 'text-field' ),
			'repeating-section' => FrmField::getOne( 'repeating-section' ),
			'embed-form-field' => FrmField::getOne( 'embed-form-field' ),
			'user-id-field' => FrmField::getOne( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'fields' => $include_fields,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_old_fields_parameter_single_field() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => FrmField::getOne( 'text-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'fields' => $include_fields,
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_specific_field_ids_excluded() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$exclude_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-section' => FrmField::get_id_by_key( 'repeating-section' ),
			'embed-form-field' => FrmField::get_id_by_key( 'embed-form-field' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'exclude_fields' => implode( ',', $exclude_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_exclude_fields( $atts, $exclude_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_specific_field_keys_excluded() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$exclude_fields = array(
			'text-field' => 'text-field',
			'repeating-section' => 'repeating-section',
			'embed-form-field' => 'embed-form-field',
			'user-id-field' => 'user-id-field',
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'exclude_fields' => implode( ',', $exclude_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_exclude_fields( $atts, $exclude_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_specific_field_ids_included_and_include_extras() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => FrmField::get_id_by_key( 'text-field' ),
			'repeating-section' => FrmField::get_id_by_key( 'repeating-section' ),
			'embed-form-field' => FrmField::get_id_by_key( 'embed-form-field' ),
			'user-id-field' => FrmField::get_id_by_key( 'user-id-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
			'include_fields' => implode( ',', $include_fields ),
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="section"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 *
	 * @group show-entry-shortcode-conditional-section
	 */
	public function test_default_message_with_conditionally_hidden_sections() {
		$entry = FrmEntry::getOne( 'jamie_entry_key' );

		// Update conditional logic field
		FrmEntryMeta::update_entry_meta( $entry->id, $this->text_field_id, null, 'Hide Fields' );

		// Clear all conditionally hidden fields
		$rich_text_field_id = FrmField::get_id_by_key( 'rich-text-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $rich_text_field_id );

		$single_file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $single_file_field_id );

		$multi_file_field_id = FrmField::get_id_by_key( 'multi-file-upload-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $multi_file_field_id );

		$number_field_id = FrmField::get_id_by_key( 'number-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $number_field_id );

		$phone_number_field_id = FrmField::get_id_by_key( 'n0d580' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $phone_number_field_id );

		$time_field_id = FrmField::get_id_by_key( 'time-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $time_field_id );

		$date_field_id = FrmField::get_id_by_key( 'date-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $date_field_id );

		$image_url_field_id = FrmField::get_id_by_key( 'zwuclz' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $image_url_field_id );

		$scale_field_id = FrmField::get_id_by_key( 'qbrd2o' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $scale_field_id );

		$tags_field_id = FrmField::get_id_by_key( 'tags-field' );
		FrmEntryMeta::delete_entry_meta( $entry->id, $tags_field_id );

		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message font_size, text_color, border_width, border_color, bg_color]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_styling_changes() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * Tests [default-message clickable=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
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
	 * Tests [default-message inline_style=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_inline_style_off() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
	 */
	public function test_default_message_with_user_info() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
	 */
	public function test_default_message_with_plain_text() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
	 */
	public function test_default_message_with_plain_text_and_include_extras() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => true,
			'user_info' => false,
			'include_extras' => 'page,section,html',
		);

		$content = $this->get_formatted_content( $atts, 'plain_text' );
		$expected_content = $this->expected_plain_text_content( $atts );

		$this->assertSameStrings( $expected_content, $content );
	}

	/**
	 * Tests [default-message direction=rtl]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_message_with_rtl_direction() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$include_fields = array(
			'text-field' => FrmField::getOne( 'text-field' ),
		);

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'direction' => 'rtl',
			'fields' => $include_fields,
		);

		$content = $this->get_formatted_content( $atts );

		$this->td_style = str_replace( 'text-align:left', 'text-align:right', $this->td_style );
		$expected_content = $this->expected_content_for_include_fields( $atts, $include_fields );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests Default HTML for emails
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 */
	public function test_default_html_for_email() {
		// TODO: add is_visible shortcode for sections and maybe page breaks? Or maybe just use [if x]

		$form_id = FrmForm::getIdByKey( 'all_field_types' );

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
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 *
	 * @group show-entry-array-format
	 */
	public function test_array_format_for_api() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
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
	 * Tests the json format
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 *
	 * @group show-entry-json-format
	 */
	public function test_json_format() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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

	/**
	 * Tests the way an API action gets the default HTML
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.11
	 *
	 * @group show-entry-array-format
	 */
	public function test_default_array_for_api() {
		$form_id = FrmForm::getIdByKey( 'all_field_types' );

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
	 * @since 2.03.11
	 *
	 * @group show-entry-array-format
	 */
	public function test_api_entry_retrieval() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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
	 * @since 2.03.11
	 *
	 * @group show-entry-array-format
	 */
	public function test_array_format_for_zapier() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

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

	private function get_formatted_content( $atts, $type = 'html' ) {
		$content = FrmEntriesController::show_entry_shortcode( $atts );

		return $content;
	}

	private function expected_plain_text_content( $atts ) {
		$content = $this->text_field_plain_text( $atts );
		$content .= $this->paragraph_to_website_plain_text();
		$content .= $this->page_break_plain_text( $atts );
		$content .= $this->pro_fields_divider_plain_text( $atts );
		$content .= $this->dynamic_field_plain_text();
		$content .= $this->embedded_form_plain_text( $atts );
		$content .= $this->user_id_plain_text();
		$content .= $this->html_field_plain_text( $atts );
		$content .= $this->tags_plain_text( $atts );
		$content .= $this->signature_plain_text();
		$content .= $this->repeating_section_header_plain_text( $atts );
		$content .= $this->repeating_field_plain_text();
		$content .= $this->separate_values_checkbox_plain_text();
		$content .= $this->user_info_plain_text( $atts );

		return $content;
	}

	private function expected_html_content( $atts ) {
		$table = $this->table_header( $atts );

		$table .= $this->text_field_html( $atts );
		$table .= $this->paragraph_to_website_html( $atts );
		$table .= $this->page_break_html( $atts );
		$table .= $this->pro_fields_divider_html( $atts );
		$table .= $this->dynamic_field_html();
		$table .= $this->embedded_form_html( $atts );
		$table .= $this->user_id_html();
		$table .= $this->html_field_html( $atts );
		$table .= $this->tags_html( $atts );
		$table .= $this->signature_html();
		$table .= $this->repeating_section_header( $atts );
		$table .= $this->repeating_field_html();
		$table .= $this->separate_values_checkbox_html();
		$table .= $this->user_info_html( $atts );

		$table .= $this->table_footer();

		return $table;
	}

	private function expected_content_for_include_fields( $atts, $include_fields ) {
		$table = $this->table_header( $atts );

		if ( isset( $include_fields['text-field'] ) ) {
			$table .= $this->text_field_html( $atts );
		}

		if ( isset( $include_fields['embed-form-field'] ) ) {
			$table .= $this->embedded_form_html( $atts );
		}

		if ( isset( $include_fields['user-id-field'] ) ) {
			$table .= $this->user_id_html();
		}

		if ( isset( $include_fields['repeating-section'] ) ) {
			$table .= $this->repeating_section_header( $atts );
			$table .= $this->repeating_field_html();
		}

		if ( isset( $include_fields['repeating-text'] ) ) {
			$table .= $this->repeating_text_field_html();
		}

		$table .= $this->user_info_html( $atts );
		$table .= $this->table_footer();

		return $table;
	}

	private function expected_content_for_exclude_fields( $atts, $exclude_fields ) {

		$table = $this->table_header( $atts );

		if ( ! isset( $exclude_fields['text-field'] ) ) {
			$table .= $this->text_field_html( $atts );
		}

		$table .= $this->paragraph_to_website_html( $atts );
		$table .= $this->pro_fields_divider_html( $atts );
		$table .= $this->dynamic_field_html();

		if ( ! isset( $exclude_fields['embed-form-field'] ) ) {
			$table .= $this->embedded_form_html( $atts );
		}

		if ( ! isset( $exclude_fields['user-id-field'] ) ) {
			$table .= $this->user_id_html();
		}

		$table .= $this->tags_html( $atts );
		$table .= $this->signature_html();

		if ( ! isset( $exclude_fields['repeating-section'] ) ) {
			$table .= $this->repeating_section_header( $atts );
			$table .= $this->repeating_field_html();
		}

		$table .= $this->separate_values_checkbox_html();
		$table .= $this->user_info_html( $atts );
		$table .= $this->table_footer();

		return $table;
	}

	private function table_header( $atts ) {
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

	private function text_field_html( $atts ) {
		$html = '<tr ' . $this->tr_style . '>';

		if ( isset( $atts['direction'] ) && $atts['direction'] == 'rtl' ) {
			$html .= '<td' . $this->td_style . '>' . $atts['entry']->metas[ $this->text_field_id ] . '</td>';
			$html .= '<td' . $this->td_style . '>Single Line Text</td>';
		} else {
			$html .= '<td' . $this->td_style . '>Single Line Text</td>';
			$html .= '<td' . $this->td_style . '>' . $atts['entry']->metas[ $this->text_field_id ] . '</td>';
		}

		$html .= '</tr>' . "\r\n";

		return $html;
	}

	private function paragraph_to_website_html( $atts ) {
		$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Paragraph Text</td><td' . $this->td_style . '>';
		$html .= "Jamie\nRebecca\nWahlin</td></tr>\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Checkboxes - colors</td><td' . $this->td_style . '>Red, Green</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Radio Buttons - dessert</td><td' . $this->td_style . '>cookies</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Dropdown</td><td' . $this->td_style . '>Ace Ventura</td></tr>' . "\r\n";

		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Email Address</td><td' . $this->td_style . '>';
			$html .= '<a href="mailto:jamie@mail.com">jamie@mail.com</a></td></tr>' . "\r\n";

			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Website/URL</td><td' . $this->td_style . '>';
			$html .= '<a href="http://www.jamie.com" rel="nofollow">http://www.jamie.com</a></td></tr>' . "\r\n";
		} else {
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Email Address</td><td' . $this->td_style . '>jamie@mail.com</td></tr>' . "\r\n";
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Website/URL</td><td' . $this->td_style . '>http://www.jamie.com</td></tr>' . "\r\n";
		}

		return $html;
	}

	private function page_break_html( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'page' ) !== false ) {
			$html = '<tr ' . $this->tr_style . '><td colspan="2"' . $this->td_style . '><br/><br/></td></tr>' . "\r\n";

		} else {
			$html = '';
		}

		return $html;
	}

	private function pro_fields_divider_html( $atts ) {
		if ( $atts['entry']->metas[ $this->text_field_id ] == 'Hide Fields' ) {
			return '';
		}

		$html = $this->pro_fields_divider_heading( $atts );
		$html .= $this->fields_within_pro_fields_divider( $atts );

		return $html;
	}

	private function pro_fields_divider_heading( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$html = '<tr ' . $this->tr_style . '><td colspan="2"' . $this->td_style . '><h3>Pro Fields</h3></td></tr>' . "\r\n";
		} else {
			$html = '';
		}

		return $html;
	}


	private function fields_within_pro_fields_divider( $atts ) {
		$html = $this->rich_text_html();
		$html .= $this->single_file_upload_html( $atts );
		$html .= $this->multi_file_upload_html( $atts );
		$html .= $this->number_to_scale_field_html( $atts );

		return $html;
	}

	private function rich_text_html() {
		return '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Rich Text</td><td' . $this->td_style . '><strong>Bolded text</strong></td></tr>' . "\r\n";
	}

	private function single_file_upload_html( $atts ) {
		$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		$single_file_url = wp_get_attachment_url( $atts['entry']->metas[ $file_field_id ] );

		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single File Upload</td><td' . $this->td_style . '>';
			$html .= '<a href="' . $single_file_url . '" rel="nofollow">' . $single_file_url . '</a></td></tr>';
			$html .= "\r\n";
		} else {
			$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single File Upload</td><td' . $this->td_style . '>' . $single_file_url . '</td></tr>';
			$html .= "\r\n";
		}

		return $html;
	}

	private function multi_file_upload_html( $atts ) {
		$multi_file_urls = $this->get_multi_file_urls( $atts['entry'] );

		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Multiple File Upload</td><td' . $this->td_style . '>';

			foreach ( $multi_file_urls as $multi_file_url ) {
				$html .= '<a href="' . $multi_file_url . '" rel="nofollow">' . $multi_file_url . '</a><br/><br/>';
			}

			$html = preg_replace('/<br\/><br\/>$/', '', $html);
			$html .= '</td></tr>';
			$html .= "\r\n";
		} else {
			$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Multiple File Upload</td><td' . $this->td_style . '>';

			$formatted_urls = implode( '<br/><br/>', $multi_file_urls );

			$html .= $formatted_urls . '</td></tr>';
			$html .= "\r\n";
		}

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

	private function number_to_scale_field_html( $atts ) {
		$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Number</td><td' . $this->td_style . '>11</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Phone Number</td><td' . $this->td_style . '>1231231234</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Time</td><td' . $this->td_style . '>12:30 AM</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Date</td><td' . $this->td_style . '>August 16, 2015</td></tr>' . "\r\n";

		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Image URL</td><td' . $this->td_style . '>';
			$html .= '<a href="http://www.test.com" rel="nofollow">http://www.test.com</a></td></tr>' . "\r\n";
		} else {
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Image URL</td><td' . $this->td_style . '>http://www.test.com</td></tr>' . "\r\n";
		}

		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Scale</td><td' . $this->td_style . '>5</td></tr>' . "\r\n";

		return $html;
	}

	private function dynamic_field_html() {
		return '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Dynamic Field - level 1</td><td' . $this->td_style . '>United States</td></tr>' . "\r\n";
	}



	private function html_field_html( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'html' ) !== false ) {
			$html = '<tr ' . $this->tr_style . '><td colspan="2"' . $this->td_style . '>Lorem ipsum.</td></tr>' . "\r\n";

		} else {
			$html = '';
		}

		return $html;
	}

	private function repeating_section_header( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$html = '<tr ' . $this->tr_style . '><td colspan="2"' . $this->td_style . '><h3>Repeating Section</h3></td></tr>' . "\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	private function user_id_html() {
		return '<tr ' . $this->tr_style . '><td' . $this->td_style . '>User ID</td><td' . $this->td_style . '>admin</td></tr>' . "\r\n";
	}

	private function tags_html( $atts ) {
		if ( $atts['entry']->metas[ $this->text_field_id ] == 'Hide Fields' ) {
			$html = '';
		} else {
			$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Tags</td><td' . $this->td_style . '>Jame</td></tr>' . "\r\n";

		}

		return $html;
	}

	private function signature_html() {
		return '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Signature</td><td' . $this->td_style . '>398, 150</td></tr>' . "\r\n";
	}

	private function embedded_form_html( $atts ) {
		$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Name</td><td' . $this->td_style . '>Embedded name</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Last</td><td' . $this->td_style . '>test</td></tr>' . "\r\n";

		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Email</td><td' . $this->td_style . '>';
			$html .= '<a href="mailto:test@mail.com">test@mail.com</a></td></tr>' . "\r\n";

		} else {
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Email</td><td' . $this->td_style . '>test@mail.com</td></tr>' . "\r\n";
		}

		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$html .= '<tr ' . $this->tr_style . '><td colspan="2"' . $this->td_style . '><h3>Email Information</h3></td></tr>' . "\r\n";
		}

		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Subject</td><td' . $this->td_style . '>test</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Message</td><td' . $this->td_style . '>test</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Date</td><td' . $this->td_style . '>May 21, 2015</td></tr>' . "\r\n";

		return $html;
	}

	private function repeating_field_html() {
		$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single Line Text</td><td' . $this->td_style . '>First</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Checkboxes</td><td' . $this->td_style . '>Option 1, Option 2</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Date</td><td' . $this->td_style . '>May 27, 2015</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single Line Text</td><td' . $this->td_style . '>Second</td></tr>'. "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Checkboxes</td><td' . $this->td_style . '>Option 1, Option 2</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Date</td><td' . $this->td_style . '>May 29, 2015</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single Line Text</td><td' . $this->td_style . '>Third</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Checkboxes</td><td' . $this->td_style . '>Option 2</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Date</td><td' . $this->td_style . '>June 19, 2015</td></tr>' . "\r\n";

		return $html;
	}

	private function repeating_text_field_html() {
		$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single Line Text</td><td' . $this->td_style . '>First</td></tr>' . "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single Line Text</td><td' . $this->td_style . '>Second</td></tr>'. "\r\n";
		$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Single Line Text</td><td' . $this->td_style . '>Third</td></tr>' . "\r\n";

		return $html;
	}

	private function separate_values_checkbox_html() {
		return '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Checkboxes - separate values</td><td' . $this->td_style . '>Option 1, Option 2</td></tr>' . "\r\n";
	}

	private function user_info_html( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$html = '<tr ' . $this->tr_style . '><td' . $this->td_style . '>IP Address</td><td' . $this->td_style . '>127.0.0.1</td></tr>' . "\r\n";
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>User-Agent (Browser/OS)</td><td' . $this->td_style . '>Mozilla Firefox 37.0 / OS X</td></tr>' . "\r\n";
			$html .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Referrer</td><td' . $this->td_style . '>http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd</td></tr>' . "\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	private function table_footer() {
		return '</tbody></table>';
	}

	private function text_field_plain_text( $atts ) {
		if ( isset( $atts['direction'] ) && $atts['direction'] == 'rtl' ) {
			$content = $atts['entry']->metas[ $this->text_field_id ] . ': Single Line Text';
		} else {
			$content = 'Single Line Text: ' . $atts['entry']->metas[ $this->text_field_id ];
		}

		$content .= "\r\n";

		return $content;
	}

	private function paragraph_to_website_plain_text() {
		$content = "Paragraph Text: Jamie\nRebecca\nWahlin\r\n";
		$content .= "Checkboxes - colors: Red, Green\r\n";
		$content .= "Radio Buttons - dessert: cookies\r\n";
		$content .= "Dropdown: Ace Ventura\r\n";
		$content .= "Email Address: jamie@mail.com\r\n";
		$content .= "Website/URL: http://www.jamie.com\r\n";

		return $content;
	}

	private function pro_fields_divider_plain_text( $atts ) {
		if ( $atts['entry']->metas[ $this->text_field_id ] == 'Hide Fields' ) {
			return '';
		}

		$content = '';
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$content .= "\r\nPro Fields\r\n";
		}

		$content .= "Rich Text: Bolded text\r\n";

		$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		$single_file_url = wp_get_attachment_url( $atts['entry']->metas[ $file_field_id ] );
		$content .= "Single File Upload: " . $single_file_url . "\r\n";

		$multiple_file_urls = $this->get_multi_file_urls( $atts['entry'] );
		$content .= "Multiple File Upload: " . implode( ', ', $multiple_file_urls ) . "\r\n";

		$content .= "Number: 11\r\n";
		$content .= "Phone Number: 1231231234\r\n";
		$content .= "Time: 12:30 AM\r\n";
		$content .= "Date: August 16, 2015\r\n";
		$content .= "Image URL: http://www.test.com\r\n";
		$content .= "Scale: 5\r\n";

		return $content;
	}


	private function page_break_plain_text( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'page' ) !== false ) {
			$html = "\r\n\r\n";

		} else {
			$html = '';
		}

		return $html;
	}

	private function html_field_plain_text( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'html' ) !== false ) {
			$html = "Lorem ipsum.\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	private function dynamic_field_plain_text() {
		return "Dynamic Field - level 1: United States\r\n";
	}

	private function embedded_form_plain_text( $atts ) {
		$content = "Name: Embedded name\r\n";
		$content .= "Last: test\r\n";

		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$content .= "Email: test@mail.com</a>\r\n";

		} else {
			$content .= "Email: test@mail.com\r\n";
		}

		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$content .= "\r\nEmail Information\r\n";
		}

		$content .= "Subject: test\r\n";
		$content .= "Message: test\r\n";
		$content .= "Date: May 21, 2015\r\n";

		return $content;
	}

	private function repeating_section_header_plain_text( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$content = "\r\nRepeating Section\r\n";
		} else {
			$content = '';
		}

		return $content;
	}

	private function user_id_plain_text() {
		return "User ID: admin\r\n";
	}

	private function tags_plain_text( $atts ) {
		if ( $atts['entry']->metas[ $this->text_field_id ] == 'Hide Fields' ) {
			$content = '';
		} else {
			$content = "Tags: Jame\r\n";

		}

		return $content;
	}

	private function signature_plain_text() {
		return "Signature: 398, 150\r\n";
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

	private function separate_values_checkbox_plain_text() {
		$content = "Checkboxes - separate values: Option 1, Option 2\r\n";

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
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Title</td><td' . $this->td_style . '>Jamie\'s Post</td></tr>' . "\r\n";
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Content</td><td' . $this->td_style . '>Hello! My name is Jamie.</td></tr>' . "\r\n";
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Author</td><td' . $this->td_style . '>Jamie Wahlin</td></tr>' . "\r\n";
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>User ID</td><td' . $this->td_style . '>admin</td></tr>' . "\r\n";
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Category</td><td' . $this->td_style . '><a href="http://example.org/?cat=1" title="View all posts filed under Uncategorized">Uncategorized</a></td></tr>' . "\r\n";
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Parent Dynamic Field</td><td' . $this->td_style . '><a href="http://example.org/?cat=1" title="View all posts filed under Uncategorized">Uncategorized</a></td></tr>' . "\r\n";
		$table .= '<tr ' . $this->tr_style . '><td' . $this->td_style . '>Post Status</td><td' . $this->td_style . '>Published</td></tr>' . "\r\n";
		$table .= $this->table_footer();

		return $table;
	}

	private function get_expected_default_html( $atts ) {
		$fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		$html = $this->table_header( $atts );

		$in_repeating_section = 0;
		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'form', 'divider', 'break', 'end_divider', 'password', 'captcha' ) ) ) {
				if ( $field->type == 'divider' ) {

					$html .= '[if ' . $field->id . ']<tr style="[frm-alt-color]">';
					$html .= '<td colspan="2"' . $this->td_style . '>';
					$html .= '<h3>[' . $field->id . ' show=description]</h3>';
					$html .= '</td>';
					$html .= '</tr>[/if ' . $field->id . ']' . "\r\n";;

					if ( FrmField::is_repeating_field( $field ) ) {
						$in_repeating_section = $field->id;
						$html .= '[foreach ' . $field->id . ']';
					}
				} else if ( $in_repeating_section > 0 && $field->type == 'end_divider' ) {
					$html .= '[/foreach ' . $in_repeating_section . ']';
					$in_repeating_section = 0;
				} else if ( $field->type == 'break' ) {
					$html .= '[if ' . $field->id . ']<tr style="[frm-alt-color]">';
					$html .= '<td colspan="2"' . $this->td_style . '><br/><br/></td>';
					$html .= '</tr>[/if ' . $field->id . ']' . "\r\n";;
				}

				continue;
			}

			$html .= '[if ' . $field->id . ']<tr style="[frm-alt-color]">';
			$html .= '<td' . $this->td_style . '>[' . $field->id . ' show=field_label]</td>';

			if ( $field->type == 'data' && $field->field_options['data_type'] == 'data' ) {
				$html .= '<td' . $this->td_style . '>';
				$html .= '[' . $field->field_options['hide_field'][0] . ' show=' . $field->field_options['form_select'] . ']';
				$html .= '</td>';
			} else {
				$html .= '<td' . $this->td_style . '>[' . $field->id . ']</td>';
			}
			$html .= '</tr>[/if ' . $field->id . ']' . "\r\n";;
		}

		$html .= $this->table_footer();

		return $html;
	}

	private function expected_array( $entry, $atts ) {

		// Single file upload field
		$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );
		$single_file_url = wp_get_attachment_url( $entry->metas[ $file_field_id ] );

		// Multi file upload field
		$multi_file_field_id = FrmField::get_id_by_key( 'multi-file-upload-field' );
		$multi_file_urls = $this->get_multi_file_urls( $entry );

		// Dynamic Country
		$where = array( 'meta_value' => 'United States', 'field_id' => FrmField::get_id_by_key( '2atiqt' ) );
		$dynamic_country_id = FrmDb::get_var( 'frm_item_metas', $where, 'item_id' );

		// TODO: do I need field label?


		$expected = array(
			'text-field' => 'Jamie',
			'p3eiuk' => "Jamie\nRebecca\nWahlin",
    		'uc580i' => array ( 'Red', 'Green' ),
			'radio-button-field' => 'cookies',
			'dropdown-field' => 'Ace Ventura',
			'email-field' => 'jamie@mail.com',
			'website-field' => 'http://www.jamie.com',
			'rich-text-field' => 'Bolded text',
			'rich-text-field-value' => '<strong>Bolded text</strong>',
			'single-file-upload-field' => $single_file_url,
			'single-file-upload-field-value' => $entry->metas[ $file_field_id ],
			'multi-file-upload-field' => $multi_file_urls,// TODO: check purpose of extra space in FrmProEntryMetaHelper
			'multi-file-upload-field-value' => $entry->metas[ $multi_file_field_id ],
			'number-field' => '11',
			'n0d580' => '1231231234',
			'time-field' => '12:30 AM',
			'time-field-value' => '00:30',
			'date-field' => 'August 16, 2015',
			'date-field-value' => '2015-08-16',
			'zwuclz' => 'http://www.test.com',
			'qbrd2o' => '5',
			'dynamic-country' => 'United States',
			'dynamic-country-value' => $dynamic_country_id,
			'dynamic-state' => '',
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
			'hidden-field' => '',
			'user-id-field' => 'admin',
			'user-id-field-value' => '1',
			'tags-field' => 'Jame',
			'ggo4ez' => array(
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
			'lookup-country' => '',
			'cb-sep-values' => array( 'Option 1', 'Option 2' ),
			'cb-sep-values-value' => array( 'Red', 'Orange' ),
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
			// TODO: pick up on displayed value for categories. Should categories by in array?
			'parent-dynamic-taxonomy' => 'Uncategorized',
			'parent-dynamic-taxonomy-value' => array( 1 ),
			'child-dynamic-taxonomy' => '',
			'grandchild-dynamic-taxonomy' => '',
			'post-status-dropdown' => 'Published',
			'post-status-dropdown-value' => 'publish',
		);

		return $expected;
	}

	private function expected_json( $entry, $atts ) {
		$array = $this->expected_array( $entry, $atts );
		return json_encode( $array );
	}

	private function expected_default_array( $atts ) {
		$fields = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );

		$expected = array();

		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'form', 'divider', 'break', 'end_divider', 'password', 'captcha' ) ) ) {
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

	private function assertSameStrings( $expected, $actual ) {
		$message = array();

		if (
			$expected !== $actual
			&& preg_replace('/(\r\n|\n\r|\r)/', "\n", $expected) === preg_replace('/(\r\n|\n\r|\r)/', "\n", $actual)
		) {
			$message[] = ' #Warning: Strings contain different line endings! Debug using remaping ["\r" => "R", "\n" => "N", "\t" => "T"]:';
			$message[] = ' -'.str_replace(array("\r", "\n", "\t"), array('R', 'N', 'T'), $expected);
			$message[] = ''.str_replace(array("\r", "\n", "\t"), array('R', 'N', 'T'), $actual);
		}

		$this->assertSame($expected, $actual, implode("\n", $message));
	}

}