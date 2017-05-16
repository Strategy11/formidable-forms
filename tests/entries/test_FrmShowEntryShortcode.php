<?php

/**
 * @since 2.03.08
 *
 * @group shortcodes
 * @group entries
 * @group show-entry-shortcode
 *
 */
class test_FrmShowEntryShortcode extends FrmUnitTest {

	private $text_field_id = '';
	private $tr_style = 'style="background-color:#ffffff;"';
	private $td_style = 'style="text-align:left;color:#555555;padding:7px 9px;vertical-align:top;border-top:1px solid #cccccc;"';

	public function setUp() {
		parent::setUp();

		$this->text_field_id = FrmField::get_id_by_key( 'text-field' );
	}

	// TODO: test post fields

	/**
	 * Tests [default-message]
	 *
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
	 *
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
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
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
	 * @covers FrmEntryFormat::show_entry
	 *
	 * @since 2.03.08
	 *
	 * @group show-entry-shortcode-conditional-section
	 */
	public function test_default_message_with_conditionally_hidden_sections() {
		$entry = FrmEntry::getOne( 'jamie_entry_key', true );

		$entry->metas[ $this->text_field_id ] = 'Hide Fields';

		// Clear tags field value since it is conditionally hidden
		$tags_field_id = FrmField::get_id_by_key( 'tags-field' );
		$entry->metas[ $tags_field_id ] = '';

		// TODO: maybe clear all values in section as well

		$atts = array(
			'id' => $entry->id,
			'entry' => $entry,
			'plain_text' => false,
			'user_info' => false,
			'include_extras' => 'section',
		);

		$content = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		// TODO: maybe run more specific tests so errors are more helpful?
		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntryFormat::show_entry
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.03.08
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
		$expected_array = $this->expected_array( $atts );

		$this->assertSame( $expected_array, $data_array );
	}

	private function get_formatted_content( $atts ) {
		$content = FrmEntryFormat::show_entry( $atts );
		$content = preg_replace( "/\r|\n/", "", $content );

		return $content;
	}

	private function expected_html_content( $atts ) {
		$table = $this->table_header();

		$table .= $this->text_field_html( $atts );
		$table .= $this->paragraph_to_website_html();
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

		$table .= $this->table_footer();

		$table = preg_replace( "/\r|\n/", "", $table );

		return $table;
	}

	private function expected_content_for_include_fields( $atts, $include_fields ) {
		$table = $this->table_header();

		if ( isset( $include_fields['text-field'] ) ) {
			$table .= $this->text_field_html( $atts );
		}

		if ( isset( $include_fields['repeating-section'] ) ) {
			$table .= $this->repeating_section_header( $atts );
			$table .= $this->repeating_field_html();
		}

		if ( isset( $include_fields['embed-form-field'] ) ) {
			$table .= $this->embedded_form_html( $atts );
		}

		if ( isset( $include_fields['user-id-field'] ) ) {
			$table .= $this->user_id_html();
		}

		$table .= $this->table_footer();

		$table = preg_replace( "/\r|\n/", "", $table );

		return $table;
	}

	private function expected_content_for_exclude_fields( $atts, $exclude_fields ) {

		$table = $this->table_header();

		if ( ! isset( $exclude_fields['text-field'] ) ) {
			$table .= $this->text_field_html( $atts );
		}

		$table .= $this->paragraph_to_website_html();
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

		$table .= $this->table_footer();

		$table = preg_replace( "/\r|\n/", "", $table );

		return $table;
	}

	private function table_header() {
		return '<table cellspacing="0"  style="font-size:14px;line-height:135%; border-bottom:1px solid #cccccc;"><tbody>';
	}

	private function text_field_html( $atts ) {
		$html = '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Single Line Text</td><td  ' . $this->td_style . '>';
		$html .= $atts['entry']->metas[ $this->text_field_id ];
		$html .= '</td></tr>';

		return $html;
	}

	private function paragraph_to_website_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Paragraph Text</td><td  ' . $this->td_style . '>Jamie
Rebecca
Wahlin</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Checkboxes - colors</td><td  ' . $this->td_style . '>Red, Green</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Radio Buttons - dessert</td><td  ' . $this->td_style . '>cookies</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Dropdown</td><td  ' . $this->td_style . '>Ace Ventura</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Email Address</td><td  ' . $this->td_style . '>jamie@mail.com</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Website/URL</td><td  ' . $this->td_style . '>http://www.jamie.com</td></tr>';
	}

	private function page_break_html( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'page' ) !== false ) {
			$html = '<tr  ' . $this->tr_style . '><td colspan="2"  ' . $this->td_style . '><br/><br/></td></tr>';
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
			$html = '<tr  ' . $this->tr_style . '><td colspan="2"  ' . $this->td_style . '><h3>Pro Fields</h3> </td></tr>';
		} else {
			$html = '';
		}

		return $html;
	}

	private function fields_within_pro_fields_divider( $atts ) {
		$html = $this->rich_text_html();
		$html .= $this->single_file_upload_html( $atts );
		$html .= $this->number_to_scale_field_html();

		return $html;
	}

	private function rich_text_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Rich Text</td><td  ' . $this->td_style . '><strong>Bolded text</strong></td></tr>';
	}

	private function single_file_upload_html( $atts ) {
		$file_field_id = FrmField::get_id_by_key( 'mprllc' );
		$single_file_url = wp_get_attachment_url( $atts['entry']->metas[ $file_field_id ] );

		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Single File Upload</td><td  ' . $this->td_style . '>' . $single_file_url . '</td></tr>';
	}

	private function number_to_scale_field_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Number</td><td  ' . $this->td_style . '>11</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Phone Number</td><td  ' . $this->td_style . '>1231231234</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Time</td><td  ' . $this->td_style . '>12:30 AM</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Date</td><td  ' . $this->td_style . '>August 16, 2015</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Image URL</td><td  ' . $this->td_style . '>http://www.test.com</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Scale</td><td  ' . $this->td_style . '>5</td></tr>';
	}

	private function dynamic_field_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Dynamic Field - level 1</td><td  ' . $this->td_style . '>United States</td></tr>';
	}

	private function html_field_html( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'html' ) !== false ) {
			$html = '<tr  ' . $this->tr_style . '><td colspan="2"  ' . $this->td_style . '>Lorem ipsum.</td></tr>';
		} else {
			$html = '';
		}

		return $html;
	}

	private function repeating_section_header( $atts ) {
		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$html = '<tr  ' . $this->tr_style . '><td colspan="2"  ' . $this->td_style . '><h3>Repeating Section</h3> </td></tr>';
		} else {
			$html = '';
		}

		return $html;
	}

	private function user_id_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>User ID</td><td  ' . $this->td_style . '>admin</td></tr>';
	}

	private function tags_html( $atts ) {
		if ( $atts['entry']->metas[ $this->text_field_id ] == 'Hide Fields' ) {
			$html = '';
		} else {
			$html = '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Tags</td><td  ' . $this->td_style . '>Jame</td></tr>';

		}

		return $html;
	}

	private function signature_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Signature</td><td  ' . $this->td_style . '>398, 150</td></tr>';
	}

	private function embedded_form_html( $atts ) {
		$html = '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Name</td><td  ' . $this->td_style . '>Embedded name</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Last</td><td  ' . $this->td_style . '>test</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Email</td><td  ' . $this->td_style . '>test@mail.com</td></tr>';

		if ( isset( $atts['include_extras'] ) && strpos( $atts['include_extras'], 'section' ) !== false ) {
			$html .= '<tr  ' . $this->tr_style . '><td colspan="2"  ' . $this->td_style . '><h3>Email Information</h3> </td></tr>';
		}

		$html .= '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Subject</td><td  ' . $this->td_style . '>test</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Message</td><td  ' . $this->td_style . '>test</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Date</td><td  ' . $this->td_style . '>May 21, 2015</td></tr>';

		return $html;
	}

	private function repeating_field_html() {
		return '<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Single Line Text</td><td  ' . $this->td_style . '>First</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Checkboxes</td><td  ' . $this->td_style . '>Option 1, Option 2</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Date</td><td  ' . $this->td_style . '>May 27, 2015</td></tr>

<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Single Line Text</td><td  ' . $this->td_style . '>Second</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Checkboxes</td><td  ' . $this->td_style . '>Option 1, Option 2</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Date</td><td  ' . $this->td_style . '>May 29, 2015</td></tr>

<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Single Line Text</td><td  ' . $this->td_style . '>Third</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Checkboxes</td><td  ' . $this->td_style . '>Option 2</td></tr>
<tr ' . $this->tr_style . '><td  ' . $this->td_style . '>Date</td><td  ' . $this->td_style . '>June 19, 2015</td></tr>';
	}

	private function table_footer() {
		return '</tbody></table>';
	}

	private function expected_html_content_for_post_entry( $atts ) {
		return '';
	}

	private function expected_array() {
		$expected = array(
			'text-field' => 'Jamie',
			'p3eiuk' => 'Jamie
Rebecca
Wahlin',
    		'uc580i' => array ( 'Red', 'Green' ),
			'radio-button-field' => 'cookies',
			'dropdown-field' => 'Ace Ventura',
			'email-field' => 'jamie@mail.com',
			'website-field' => 'http://www.jamie.com',
			'i79z0s' => 'Bolded text',
			'i79z0s-value' => '<strong>Bolded text</strong>',
			'mprllc' => 'http://example.org/wp-content/uploads/2017/05/global-settings_enter-license1-13379.png',
			'mprllc-value' => '23',
			'72hika' => '',
			'72hika-value' => '25,26,27',
			'msyehy' => '11',
			'n0d580' => '1231231234',
			'time-field' => '12:30 AM',
			'time-field-value' => '00:30',
			'date-field' => 'August 16, 2015',
			'date-field-value' => '2015-08-16',
			'zwuclz' => 'http://www.test.com',
			'qbrd2o' => '5',
			'dynamic-country' => 'United States',
			'dynamic-country-value' => '19',
			'dynamic-state' => '',
			'dynamic-city' => '',
			'qfn4lg' => array(),
			'qfn4lg-value' => array( '' ),
			'embed-form-field' => array(
				'contact-name' => 'Jamie',
				'contact-last-name' => 'Wahlin',
				'contact-email' => 'jamie@test.com',
				'contact-website' => 'http://www.test.com',
				'contact-subject' => 'Jamie\'s subject',
				'contact-message' => 'Jamie\'s message',
				'contact-date' => 'May 20th, 2015',
				'contact-date-value' => '2015-05-20',
				'contact-user-id' => 'admin',
				'contact-user-id-value' => '1',
			),
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
			'lookup-country' => '',
		);

		return $expected;
	}


}