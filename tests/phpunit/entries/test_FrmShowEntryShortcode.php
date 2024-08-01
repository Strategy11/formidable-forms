<?php

/**
 * @since 2.04
 *
 * @group shortcodes
 * @group entries
 * @group show-entry-shortcode
 * @group free
 */
class test_FrmShowEntryShortcode extends FrmUnitTest {

	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	protected $include_fields = array();
	protected $exclude_fields = array();
	protected $include_extras = array();

	protected $extra_fields = array( 'html' );
	protected $tr_style     = ' style="background-color:#ffffff;"';
	protected $td_style     = ' style="text-align:left;color:#1D2939;padding:7px 9px;vertical-align:top;border-top:1px solid #cccccc;"';

	protected $is_repeater_child = false;

	public function __construct() {
		parent::__construct();

		$defaults       = $this->get_defaults();
		$this->td_style = str_replace( '#1D2939', $defaults['text_color'], $this->td_style );
		$this->td_style = str_replace( '#cccccc', $defaults['border_color'], $this->td_style );
	}

	/**
	 * Tests no entry or id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_no_entry_or_id_passed() {
		$atts = array(
			'plain_text' => false,
			'user_info'  => false,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = '';

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests no id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_no_id_passed() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => false,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests fake id passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_fake_id_passed() {
		$atts = array(
			'id'         => 'jfie09293jf',
			'plain_text' => false,
			'user_info'  => false,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = '';

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests no entry passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_no_entry_passed() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'         => $entry->id,
			'plain_text' => false,
			'user_info'  => false,
		);

		$content = $this->get_formatted_content( $atts );

		$atts['entry']    = $entry;
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests no meta passed
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_no_meta_passed() {
		$entry = $this->get_test_entry( false );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => false,
		);

		$content = $this->get_formatted_content( $atts );

		$atts['entry']    = FrmEntry::getOne( $entry->id, true );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_basic_default_message_parameters_all_field_types() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => false,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_extras="html"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_extras_included() {
		$entry = $this->get_test_entry( true );

		$this->include_extras = array( 'divider', 'break', 'html' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => false,
			'user_info'      => false,
			'include_extras' => 'section, page, html',
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_specific_field_ids_included() {
		$entry = $this->get_test_entry( true );

		$this->set_included_fields( 'id ' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => false,
			'user_info'      => false,
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_specific_field_keys_included() {
		$entry = $this->get_test_entry( true );

		$this->set_included_fields( 'key' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => false,
			'user_info'      => false,
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_old_fields_parameter() {
		$entry = $this->get_test_entry( true );

		$this->set_included_fields( 'id' );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => false,
			'fields'     => implode( ',', $this->include_fields ),
		);

		$content = $this->get_formatted_content( $atts );

		$pass_atts                   = $atts;
		$pass_atts['include_fields'] = $atts['fields'];
		unset( $pass_atts['fields'] );

		$expected_content = $this->expected_html_content( $pass_atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_old_fields_parameter_single_field() {
		$entry = $this->get_test_entry( true );

		$this->include_fields = $this->get_single_included_field( 'object' );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => false,
			'fields'     => $this->include_fields,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_specific_field_ids_excluded() {
		$entry = $this->get_test_entry( true );

		$this->set_excluded_fields( 'id' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => false,
			'user_info'      => false,
			'exclude_fields' => implode( ',', $this->exclude_fields ),
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message exclude_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_specific_field_keys_excluded() {
		$entry = $this->get_test_entry( true );

		$this->set_excluded_fields( 'key' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => false,
			'user_info'      => false,
			'exclude_fields' => implode( ',', $this->exclude_fields ),
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message include_fields="x,y,z"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_specific_field_ids_included_and_include_extras() {
		$entry = $this->get_test_entry( true );

		$this->set_included_fields( 'id' );
		$this->include_extras = array( 'divider', 'break', 'html' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => false,
			'user_info'      => false,
			'include_extras' => 'section, page, html',
			'include_fields' => implode( ',', $this->include_fields ),
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message font_size, text_color, border_width, border_color, bg_color]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_styling_changes() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'           => $entry->id,
			'entry'        => $entry,
			'plain_text'   => false,
			'user_info'    => false,
			'font_size'    => '20px',
			'text_color'   => '#00ff00',
			'border_width' => '3px',
			'border_color' => '#FF69B4',
			'bg_color'     => '#0000ff',
			'alt_bg_color' => '#0000ff',
		);

		$this->tr_style = str_replace( 'background-color:#ffffff', 'background-color:' . $atts['bg_color'], $this->tr_style );

		$defaults = $this->get_defaults();

		$this->td_style = str_replace( 'color:' . $defaults['text_color'], 'color:' . $atts['text_color'], $this->td_style );
		$this->td_style = str_replace( '1px solid ' . $defaults['border_color'], $atts['border_width'] . ' solid ' . $atts['border_color'], $this->td_style );

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message inline_style=0]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_inline_style_off() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'           => $entry->id,
			'entry'        => $entry,
			'plain_text'   => false,
			'user_info'    => false,
			'inline_style' => false,
		);

		$content = $this->get_formatted_content( $atts );

		$this->td_style   = '';
		$this->tr_style   = '';
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message user_info=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_user_info() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => true,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message plain_text=1]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_plain_text() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => true,
			'user_info'  => false,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_plain_text_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message plain_text=1 include_extras="page,section,html"]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_plain_text_and_include_extras() {
		$entry                = $this->get_test_entry( true );
		$this->include_extras = array( 'divider', 'break', 'html' );

		$atts = array(
			'id'             => $entry->id,
			'entry'          => $entry,
			'plain_text'     => true,
			'user_info'      => false,
			'include_extras' => 'page,section,html',
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->expected_plain_text_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests [default-message direction=rtl]
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_message_with_rtl_direction() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'         => $entry->id,
			'entry'      => $entry,
			'plain_text' => false,
			'user_info'  => false,
			'direction'  => 'rtl',
		);

		$content = $this->get_formatted_content( $atts );

		$this->td_style   = str_replace( 'text-align:left', 'text-align:right', $this->td_style );
		$expected_content = $this->expected_html_content( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests Default HTML for emails
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_html_for_email() {
		$form_id = $this->get_form_id_for_test();

		$atts = array(
			'form_id'       => $form_id,
			'default_email' => true,
			'plain_text'    => false,
		);

		$content          = $this->get_formatted_content( $atts );
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
		$form_id = $this->get_form_id_for_test();

		$atts = array(
			'form_id'       => $form_id,
			'default_email' => true,
			'plain_text'    => true,
		);

		$content          = $this->get_formatted_content( $atts );
		$expected_content = $this->get_expected_default_plain( $atts );

		$this->assertSame( $expected_content, $content );
	}

	/**
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_array_format_for_api() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'            => $entry->id,
			'user_info'     => false,
			'format'        => 'array',
			'include_blank' => true,
		);

		$data_array     = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_array( $entry, $atts );

		$this->compare_array( $expected_array, $data_array );
	}

	/**
	 * Tests the way an API action gets the default HTML
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_default_array_for_api() {
		$form_id = $this->get_form_id_for_test();

		$atts = array(
			'form_id'       => $form_id,
			'user_info'     => false,
			'format'        => 'array',
			'default_email' => true,
		);

		$data_array     = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_default_array( $atts );

		$this->compare_array( $expected_array, $data_array );
	}

	/**
	 * Tests the way an API action gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_api_entry_retrieval() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'            => $entry->id,
			'fields'        => FrmField::get_all_for_form( $entry->form_id, '', 'include' ),
			'user_info'     => false,
			'format'        => 'array',
			'include_blank' => true,
		);

		$data_array     = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_array( $entry, $atts );

		$this->compare_array( $expected_array, $data_array );
	}

	/**
	 * Tests the way Zapier gets entry data
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_array_format_for_zapier() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'            => $entry->id,
			'entry'         => $entry,
			'user_info'     => false,
			'format'        => 'array',
			'include_blank' => true,
		);

		$data_array     = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_array = $this->expected_array( $entry, $atts );

		$this->compare_array( $expected_array, $data_array );
	}

	/**
	 * Tests the json format
	 *
	 * @covers FrmEntriesController::show_entry_shortcode
	 *
	 * @since 2.05
	 */
	public function test_json_format() {
		$entry = $this->get_test_entry( true );

		$atts = array(
			'id'            => $entry->id,
			'user_info'     => false,
			'format'        => 'json',
			'include_blank' => true,
		);

		$actual_json   = FrmEntriesController::show_entry_shortcode( $atts );
		$expected_json = $this->expected_json( $entry, $atts );

		$this->assertSame( $expected_json, $actual_json );
	}

	protected function get_test_entry( $include_meta ) {
		$new_entry = array(
			'form_id'     => FrmForm::get_id_by_key( 'free_field_types' ),
			'item_key'    => 'free_entry_key',
			'description' => array(
				'browser'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0',
				'referrer' => 'http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd',
			),
			'created_at'  => '2015-05-12 19:30:23',
			'item_meta'   => $this->expected_free_meta(),
		);

		$entry_id = $this->factory->entry->create_object( $new_entry );

		return FrmEntry::getOne( $entry_id, $include_meta );
	}

	private function expected_free_meta() {
		return array(
			FrmField::get_id_by_key( 'free-text-field' )   => 'Test Testerson',
			FrmField::get_id_by_key( 'free-paragraph-field' ) => "Test\r\nMiddle\r\nTesterson",
			FrmField::get_id_by_key( 'free-checkboxes' )   => array( 'Red', 'Green' ),
			FrmField::get_id_by_key( 'free-radio-button-field' ) => 'cookies',
			FrmField::get_id_by_key( 'free-dropdown-field' ) => 'Ace Ventura',
			FrmField::get_id_by_key( 'free-email-field' )  => 'jamie@mail.com',
			FrmField::get_id_by_key( 'free-website-field' ) => 'http://www.jamie.com',
			FrmField::get_id_by_key( 'free-number-field' ) => '11',
			FrmField::get_id_by_key( 'free-phone-field' )  => '1231231234',
			FrmField::get_id_by_key( 'free-hidden-field' ) => '',
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
		$table .= $this->one_cell_table_row( 'free-html-field', $atts );

		$table .= $this->user_info_rows( $atts );

		$table .= $this->table_footer();

		return $table;
	}

	protected function expected_plain_text_content( $atts ) {
		$content  = $this->label_and_value_plain_text_row( 'free-text-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-paragraph-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-checkboxes', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-radio-button-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-dropdown-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-email-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-website-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-number-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-phone-field', $atts );
		$content .= $this->label_and_value_plain_text_row( 'free-hidden-field', $atts );
		$content .= $this->single_value_plain_text_row( 'free-html-field', $atts );

		$content .= $this->user_info_plain_text_rows( $atts );

		return $content;
	}

	protected function table_header( $atts ) {
		if ( isset( $atts['plain_text'] ) && $atts['plain_text'] ) {
			return '';
		}

		$header = '<table cellspacing="0" ';

		if ( ! isset( $atts['inline_style'] ) || $atts['inline_style'] == true ) {
			$defaults     = $this->get_defaults();
			$atts         = array_merge( $defaults, $atts );
			$font_size    = $atts['font_size'];
			$border_width = isset( $atts['border_width'] ) ? $atts['border_width'] : $atts['field_border_width'];
			$border_color = $atts['border_color'];

			$header .= ' style="font-size:' . $font_size . ';line-height:135%;';
			$header .= 'border-bottom:' . $border_width . ' solid ' . $border_color . ';"';
		}

		$header .= '><tbody>' . "\r\n";

		return $header;
	}

	protected function get_defaults() {
		$frm_style = new FrmStyle();
		$defaults  = $frm_style->get_defaults();
		FrmStylesHelper::prepare_color_output( $defaults );
		return $defaults;
	}

	protected function table_footer() {
		return '</tbody></table>';
	}

	protected function two_cell_table_row( $field_key, $atts ) {
		$field       = FrmField::getOne( $field_key );
		$field_value = $this->get_field_html_value( $atts['entry'], $field, $atts );

		if ( ! $this->is_field_included( $atts, $field, $field_value ) ) {
			return '';
		}

		return $this->two_cell_table_row_for_value( $field->name, $field_value, $atts );
	}

	protected function two_cell_table_row_for_value( $label, $field_value, $atts ) {
		$html = '<tr' . $this->tr_style;
		if ( $this->is_repeater_child ) {
			$html .= ' class="frm-child-row"';
		}
		$html .= '>';

		$label       = '<th' . $this->td_style . '>' . wp_kses_post( $label ) . '</th>';
		$field_value = '<td' . $this->td_style . '>' . wp_kses_post( $field_value ) . '</td>';

		if ( isset( $atts['direction'] ) && $atts['direction'] === 'rtl' ) {
			$html .= $field_value;
			$html .= $label;
		} else {
			$html .= $label;
			$html .= $field_value;
		}

		$html .= '</tr>' . "\r\n";

		return $html;
	}

	protected function one_cell_table_row( $field_key, $atts ) {
		$field       = FrmField::getOne( $field_key );
		$field_value = $this->get_field_html_value( $atts['entry'], $field, $atts );

		if ( ! $this->is_field_included( $atts, $field, $field_value ) ) {
			return '';
		}

		$html  = '<tr' . $this->tr_style . '>';
		$html .= '<td colspan="2"' . $this->td_style . '>' . $field_value . '</td>';
		$html .= '</tr>' . "\r\n";

		return $html;
	}

	protected function label_and_value_plain_text_row( $field_key, $atts ) {
		$field       = FrmField::getOne( $field_key );
		$field_value = $this->get_field_plain_text_value( $atts['entry'], $field, $atts );

		if ( ! $this->is_field_included( $atts, $field, $field_value ) ) {
			return '';
		}

		if ( isset( $atts['direction'] ) && $atts['direction'] === 'rtl' ) {
			$content = $field_value . ': ' . $field->name;
		} else {
			$content = $field->name . ': ' . $field_value;
		}

		$content .= "\r\n";

		return $content;
	}

	protected function single_value_plain_text_row( $field_key, $atts ) {
		$field       = FrmField::getOne( $field_key );
		$field_value = $this->get_field_plain_text_value( $atts['entry'], $field, $atts );

		if ( ! $this->is_field_included( $atts, $field, $field_value ) ) {
			return '';
		}

		$content = $field_value . "\r\n";

		return $content;
	}

	/**
	 * @param array $atts
	 * @param stdClass $field
	 * @param mixed $field_value
	 *
	 * @return bool
	 */
	protected function is_field_included( $atts, $field, $field_value ) {
		$include = true;

		if ( in_array( $field->type, $this->extra_fields, true ) ) {
			$include = in_array( $field->type, $this->include_extras, true );
		}

		if ( $include === true ) {
			if ( ! empty( $this->include_fields ) ) {
				$include = $this->is_self_or_parent_in_array( $field->field_key, $this->include_fields );
			} elseif ( ! empty( $this->exclude_fields ) ) {
				$include = ! $this->is_self_or_parent_in_array( $field->field_key, $this->exclude_fields );
			}
		}

		if ( FrmAppHelper::is_empty_value( $field_value, '' ) ) {
			if ( ! isset( $atts['include_blank'] ) || $atts['include_blank'] == false ) {
				$include = false;
			}
		}

		return $include;
	}

	protected function is_self_or_parent_in_array( $field_key, $array ) {
		return in_array( $field_key, array_keys( $array ), true );
	}

	protected function user_info_rows( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$html  = '<tr' . $this->tr_style . '><th' . $this->td_style . '>IP Address</th><td' . $this->td_style . '>127.0.0.1</td></tr>' . "\r\n";
			$html .= '<tr' . $this->tr_style . '><th' . $this->td_style . '>User-Agent (Browser/OS)</th><td' . $this->td_style . '>Mozilla Firefox 37.0 / OS X</td></tr>' . "\r\n";
			$html .= '<tr' . $this->tr_style . '><th' . $this->td_style . '>Referrer</th><td' . $this->td_style . '>' . wp_kses_post( 'http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd' ) . '</td></tr>' . "\r\n";
		} else {
			$html = '';
		}

		return $html;
	}

	protected function user_info_plain_text_rows( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] == true ) {
			$content  = "IP Address: 127.0.0.1\r\n";
			$content .= "User-Agent (Browser/OS): Mozilla Firefox 37.0 / OS X\r\n";
			$content .= "Referrer: http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd\r\n";
		} else {
			$content = '';
		}

		return $content;
	}

	protected function get_field_html_value( $entry, $field, $atts ) {
		$field_value = $this->get_field_value( $entry, $field, $atts );
		$field_value = str_replace( array( "\r\n", "\n" ), '<br/>', $field_value );

		return $field_value;
	}

	protected function get_field_plain_text_value( $entry, $field, $atts ) {
		$field_value = $this->get_field_value( $entry, $field, $atts );

		return $field_value;
	}

	/**
	 * @param stdClass $entry
	 * @param stdClass $field
	 *
	 * @return mixed|string
	 */
	protected function get_field_value( $entry, $field, $atts ) {

		if ( $field->field_key === 'free-html-field' ) {
			$field_value = 'Lorem ipsum.';
		} else {
			$field_value = isset( $entry->metas[ $field->id ] ) ? $entry->metas[ $field->id ] : '';

			if ( is_array( $field_value ) ) {
				$field_value = implode( ', ', $field_value );
			}
		}

		return $field_value;
	}

	protected function set_included_fields( $type ) {
		$this->include_fields = $this->set_field_array( $type );
	}

	protected function set_excluded_fields( $type ) {
		$this->exclude_fields = $this->set_field_array( $type );
	}

	protected function set_field_array( $type ) {
		$fields = array(
			'free-text-field' => 'free-text-field',
			'free-checkboxes' => 'free-checkboxes',
		);

		$this->convert_field_array( $type, $fields );
		return $fields;
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
		} elseif ( $type === 'object' ) {
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

	/**
	 * Get the expected default HTML shortcodes
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	protected function get_expected_default_html( $atts ) {
		return $this->get_expected_default_shortcodes( 'html', $atts );
	}

	/**
	 * Get the expected default plain text shortcodes
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	protected function get_expected_default_plain( $atts ) {
		return $this->get_expected_default_shortcodes( 'plain', $atts );
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
			$content .= $this->table_header( $atts );
		}

		foreach ( $fields as $field ) {

			if ( in_array( $field->type, array( 'html', 'captcha' ) ) ) {
				continue;
			}

			$content .= '[if ' . $field->id . ']';
			$content .= $this->table_row_start_tags( $type, $field );
			$content .= '[' . $field->id . ' show=field_label]';
			$content .= $this->cell_separator( $type );
			$content .= '[' . $field->id . ']';
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
			$html = '<tr style="[frm-alt-color]"><th' . $this->td_style . '>';
		} else {
			$html = '';
		}

		return $html;
	}

	protected function cell_separator( $type ) {
		if ( $type === 'html' ) {
			$html = '</th><td' . $this->td_style . '>';
		} else {
			$html = ': ';
		}

		return $html;
	}

	protected function table_row_end_tags( $type ) {
		if ( $type === 'html' ) {
			$html = '</td></tr>';
		} else {
			$html = '';
		}

		return $html;
	}

	protected function after_table_row_tags( $type ) {
		if ( $type === 'html' ) {
			$html = "\r\n";
		} else {
			$html = '';
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
				'val'   => '[' . $field->id . ']',
				'type'  => $field->type,
			);
		}

		return $expected;
	}

	protected function expected_array( $entry, $atts ) {
		$expected = array(
			'free-text-field'         => 'Test Testerson',
			'free-paragraph-field'    => "Test\r\nMiddle\r\nTesterson",
			'free-checkboxes'         => array( 'Red', 'Green' ),
			'free-radio-button-field' => 'cookies',
			'free-dropdown-field'     => 'Ace Ventura',
			'free-email-field'        => 'jamie@mail.com',
			'free-website-field'      => 'http://www.jamie.com',
			'free-number-field'       => '11',
			'free-phone-field'        => '1231231234',
			'free-hidden-field'       => '',
			'free-user-id-field'      => '',
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
		return FrmForm::get_id_by_key( 'free_field_types' );
	}

	protected function compare_array( $expected, $actual ) {
		if ( $expected !== $actual ) {
			foreach ( $expected as $k => $v ) {
				if ( isset( $actual[ $k ] ) && $v === $actual[ $k ] ) {
					unset( $actual[ $k ], $expected[ $k ] );
				}
			}
		}

		$this->assertSame( $expected, $actual );
	}
}
