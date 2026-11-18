<?php

/**
 * @group fields
 *
 * @covers FrmEntryValidate
 * @covers FrmFieldEmail
 * @covers FrmFieldNumber
 * @covers FrmFieldPhone
 * @covers FrmFieldType
 * @covers FrmFieldUrl
 */
#[\PHPUnit\Framework\Attributes\Group( 'fields' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEntryValidate::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldEmail::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldNumber::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldPhone::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldType::class )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldUrl::class )]
class test_FrmFieldValidate extends FrmUnitTest {

	protected $form;

	public function setUp(): void {
		parent::setUp();
		$this->create_validation_form();
	}

	protected function create_validation_form() {
		$this->form  = $this->factory->form->create_and_get();
		$field_types = $this->get_all_fields();

		foreach ( $field_types as $field_type ) {
			$this->factory->field->create(
				array(
					'type'      => $field_type,
					'form_id'   => $this->form->id,
					'field_key' => $this->get_field_key( $field_type ),
				)
			);
		}
	}

	protected function get_all_fields() {
		$fields  = array_keys( FrmField::field_selection() );
		$exclude = array( 'html' );
		return array_diff( $fields, $exclude );
	}

	public function test_not_required_fields() {
		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => array(),
			'action'    => 'create',
		);

		$errors       = FrmEntryValidate::validate( $_POST );
		$error_fields = array();

		if ( $errors ) {
			$error_field_ids = array_keys( $errors );

			foreach ( $error_field_ids as $error_field ) {
				$field_type     = FrmField::get_type( str_replace( 'field', '', $error_field ) );
				$error_fields[] = $field_type ? $field_type : $error_field;
			}
		}

		$this->assertEmpty( $errors, 'A field was required when it should not have been. ' . implode( ', ', $error_fields ) );
	}

	public function test_format_validation() {
		$test_formats = $this->expected_format_errors();

		foreach ( $test_formats as $test_format ) {
			$field_key = $this->get_field_key( $test_format['type'] );
			$field_id  = FrmField::get_id_by_key( $field_key );
			$errors    = $this->check_single_value( array( $field_id => $test_format['value'] ) );

			if ( $test_format['invalid'] ) {
				$this->assertNotEmpty( $errors, $test_format['type'] . ' value ' . $test_format['value'] . ' passed validation' );
			} else {
				$this->assertEmpty( $errors, $test_format['type'] . ' value ' . $test_format['value'] . ' did not pass validation' );
			}
		}
	}

	/**
	 * @return array
	 */
	protected function expected_format_errors() {
		return array(
			array(
				'type'    => 'number',
				'value'   => 123,
				'invalid' => false,
			),
			array(
				'type'    => 'number',
				'value'   => 'hello',
				'invalid' => true,
			),
			array(
				'type'    => 'number',
				'value'   => '1.234',
				'invalid' => false,
			),
			array(
				'type'    => 'phone',
				'value'   => '232-343-2322',
				'invalid' => false,
			),
			array(
				'type'    => 'phone',
				'value'   => '2323',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => '2323',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => 'http://',
				'invalid' => false,
			),
			array(
				'type'    => 'url',
				'value'   => 'https://ernährung.ch',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => 'https://пример.рф',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => 'https://càphê.vn',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => 'https://a/b.com',
				'invalid' => true,
			),
		);
	}

	public function test_empty_required_fields() {
		$fields = $this->factory->field->get_fields_from_form( $this->form->id );
		$this->set_required_fields( $fields );

		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => array(),
			'action'    => 'create',
		);

		$errors = FrmEntryValidate::validate( $_POST );
		$this->assertNotEmpty( $errors );
		$error_fields = array();

		if ( $errors ) {
			foreach ( $fields as $field ) {
				if ( ! isset( $errors[ 'field' . $field->id ] ) ) {
					$error_fields[] = $field->type;
				}
			}
		}

		$this->assertEmpty( $error_fields, 'A field was not required when it should have been. ' . implode( ', ', $error_fields ) );
	}

	public function test_filled_required_fields() {
		$_POST        = $this->factory->field->generate_entry_array( $this->form );
		$errors       = FrmEntryValidate::validate( $_POST );
		$error_fields = array();

		if ( $errors ) {
			$error_field_ids = array_keys( $errors );

			foreach ( $error_field_ids as $error_field ) {
				$field_type     = FrmField::get_type( str_replace( 'field', '', $error_field ) );
				$error_fields[] = $field_type ? $field_type : $error_field;
			}
		}

		$this->assertEmpty( $error_fields, 'A field was required when it was not empty. ' . implode( ', ', $error_fields ) );
	}

	/**
	 * When a url field is required, http:// should not pass
	 *
	 * @see FrmFieldUrl::validate
	 */
	public function test_url_value() {
		$field = FrmField::getOne( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );

		$this->set_required_field( $field );

		$errors = $this->check_single_value( array( $field->id => 'http://' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'http:// passed required validation ' . print_r( $errors, 1 ) );
	}

	/**
	 * Internationalized domain names are rejected unless the field opts in.
	 *
	 * The allow_intl_domains setting is off by default, so a url field keeps the ASCII-only host
	 * pattern it has always used. The punycode spelling of the same domain still passes.
	 *
	 * @see FrmFieldUrl::validate
	 */
	public function test_url_idn_rejected_by_default() {
		$field = $this->factory->field->get_object_by_id( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );
		$this->assertEmpty( FrmField::get_option( $field, 'allow_intl_domains' ), 'allow_intl_domains should be off by default.' );

		foreach ( array( 'https://ernährung.ch', 'https://пример.рф', 'ernährung.ch' ) as $url ) {
			$errors = $this->check_single_value( array( $field->id => $url ) );
			$this->assertArrayHasKey( 'field' . $field->id, $errors, 'An internationalized domain passed validation without allow_intl_domains: ' . $url );
		}

		$errors = $this->check_single_value( array( $field->id => 'https://xn--ernhrung-2za.ch' ) );
		$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'A punycode domain failed validation.' );
	}

	/**
	 * Internationalized domain names must pass validation when allow_intl_domains is on.
	 *
	 * The host pattern in FrmFieldUrl::get_url_pattern() matches UTF-8 bytes, so non-ASCII hosts are
	 * accepted. These are real registrable domains, since .ch permits accented vowels, and the
	 * punycode spelling of the same domain has always passed, so accepting these adds no new capability.
	 *
	 * @see FrmFieldUrl::validate
	 */
	public function test_url_idn_validation() {
		$field = $this->create_intl_url_field();

		$should_pass = array(
			'https://ernährung.ch',
			'https://münchen.de',
			'https://café.fr',
			'https://пример.рф',
			'https://例え.jp',
			'https://càphê.vn',
			'https://ÄPFEL.DE',
			'https://xn--ernhrung-2za.ch',
			'https://example.com',
			'http://localhost',
			'https://ernährung.ch/über-uns?q=grüße#süß',
			'ernährung.ch',
		);

		foreach ( $should_pass as $url ) {
			$errors = $this->check_single_value( array( $field->id => $url ) );
			$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'A valid url failed validation: ' . $url );
		}

		/**
		 * The last two must fail even though the class allows non-ASCII: the pattern still
		 * requires a dotted host, and a hyphen placed after the byte range would turn it into the
		 * range 0x2E-0x80 and let path and query characters through.
		 */
		$should_fail = array(
			'münchen',
			'https://ä',
			'https://a/b.com',
			'https://a?b.com',
		);

		foreach ( $should_fail as $url ) {
			$errors = $this->check_single_value( array( $field->id => $url ) );
			$this->assertArrayHasKey( 'field' . $field->id, $errors, 'An invalid url passed validation: ' . $url );
		}
	}

	/**
	 * A raw Latin-1 host byte must still validate when allow_intl_domains is on.
	 *
	 * This guards against adding the /u modifier to the host pattern. With /u, preg_match() returns
	 * false on invalid UTF-8, and because the result is negated the value would be reported invalid.
	 *
	 * @see FrmFieldUrl::validate
	 */
	public function test_url_non_utf8_host_byte() {
		$field = $this->create_intl_url_field();
		$url   = "https://ex\xE4mple.com";

		// Without this the assertion below would pass vacuously if the byte were stripped first.
		$this->assertNotEmpty( esc_url_raw( $url ), 'The Latin-1 host byte did not survive sanitizing, so this test proves nothing.' );

		$errors = $this->check_single_value( array( $field->id => $url ) );
		$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'A Latin-1 host byte failed validation, which suggests the /u modifier was added to the host pattern.' );
	}

	/**
	 * The front end input only carries the data-intl-domains flag when the setting is on, since the
	 * javascript validation picks its pattern from that attribute.
	 *
	 * @see FrmFieldUrl::add_extra_html_atts
	 */
	public function test_url_intl_domains_input_attribute() {
		$default_field = FrmFieldFactory::get_field_object( FrmField::getOne( $this->get_field_key( 'url' ) ) );
		$input_html    = '';
		$this->run_private_method( array( $default_field, 'add_extra_html_atts' ), array( array(), &$input_html ) );
		$this->assertStringNotContainsString( 'data-intl-domains', $input_html );

		$intl_field = FrmFieldFactory::get_field_object( $this->create_intl_url_field() );
		$input_html = '';
		$this->run_private_method( array( $intl_field, 'add_extra_html_atts' ), array( array(), &$input_html ) );
		$this->assertStringContainsString( 'data-intl-domains="1"', $input_html );
	}

	/**
	 * The JS copy of the host patterns must stay in step with the PHP ones, and the minified artifact
	 * must be rebuilt whenever the source changes.
	 *
	 * There is no JS engine in this suite, so this asserts the rules on the source rather than
	 * running the regex: both the ASCII-only class and the code unit range are present, the PHP byte
	 * range was not copied across by mistake, and the minified file carries the same classes.
	 *
	 * Deliberately carries no @covers: it reads js/formidable.js and js/formidable.min.js and never
	 * executes FrmFieldUrl::validate, so claiming to cover that method would credit it with
	 * coverage it does not provide.
	 */
	public function test_url_field_js_regex_parity() {
		$source   = FrmAppHelper::plugin_path() . '/js/formidable.js';
		$minified = FrmAppHelper::plugin_path() . '/js/formidable.min.js';

		foreach ( array( $source, $minified ) as $file ) {
			$this->assertFileExists( $file );

			$contents = file_get_contents( $file );
			$name     = basename( $file );

			$this->assertStringContainsString( '[\da-z\.-]', $contents, 'The default ASCII-only host class is missing from ' . $name );
			$this->assertStringContainsString( '[\da-z\u0080-\u{10FFFF}\.-]', $contents, 'The international host class is missing from ' . $name );
			$this->assertStringNotContainsString( '\x80-\xff', $contents, 'The PHP byte range was copied into ' . $name . '. That rejects Cyrillic and CJK hosts.' );
			$this->assertStringContainsString( 'data-intl-domains', $contents, 'The data-intl-domains check is missing from ' . $name );
		}
	}

	/**
	 * @return stdClass
	 */
	private function create_intl_url_field() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'url',
				'form_id'       => $this->form->id,
				'field_options' => array(
					'allow_intl_domains' => 1,
				),
			)
		);
		$this->assertNotEmpty( FrmField::get_option( $field, 'allow_intl_domains' ) );

		return $field;
	}

	public function test_email_value() {
		$field = $this->factory->field->get_object_by_id( $this->get_field_key( 'email' ) );
		$this->assertNotEmpty( $field );
		$this->set_required_field( $field );

		$errors = $this->check_single_value( array( $field->id => 'notemail@' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'Poorly formatted email passed validation ' . print_r( $errors, 1 ) );

		$errors = $this->check_single_value( array( $field->id => '' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'Email email passed required validation ' . print_r( $errors, 1 ) );

		$errors = $this->check_single_value( array( $field->id => 'email@example.com' ) );
		$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'Properly formatted email did not pass validation ' . print_r( $errors, 1 ) );
	}

	public function test_number_validation() {
		$field  = $this->factory->field->get_object_by_id( $this->get_field_key( 'number' ) );
		$errors = $this->check_single_value( array( $field->id => '10.5' ) );
		$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'Number failed validation ' . print_r( $errors, 1 ) );

		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'number',
				'form_id'       => $this->form->id,
				'field_options' => array(
					'minnum' => 0,
					'maxnum' => 20,
				),
			)
		);
		$this->assertSame( 20, $field->field_options['maxnum'] );

		$errors = $this->check_single_value( array( $field->id => '10.5' ) );
		$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'Number failed range validation ' . print_r( $errors, 1 ) );

		$errors = $this->check_single_value( array( $field->id => 'not numeric' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'Number failed numeric validation' );

		$errors = $this->check_single_value( array( $field->id => '25' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'Number failed max range validation' );

		$errors = $this->check_single_value( array( $field->id => '-25' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'Number failed min range validation' );
	}

	protected function set_required_fields( $fields ) {
		foreach ( $fields as $field ) {
			$this->set_required_field( $field );
		}
	}

	protected function set_required_field( $field ) {
		global $wpdb;
		$query_results = $wpdb->update( $wpdb->prefix . 'frm_fields', array( 'required' => 1 ), array( 'id' => $field->id ) );

		if ( ! $query_results ) {
			return;
		}

		wp_cache_delete( $field->id, 'frm_field' );
		FrmField::delete_form_transient( $this->form->id );

		$field = FrmField::getOne( $field->id );
		$this->assertNotEmpty( $field->required );
	}

	/**
	 * @param string $field_type
	 */
	protected function get_field_key( $field_type ) {
		return $field_type . '-form' . $this->form->id;
	}

	/**
	 * @param array $item_meta
	 */
	protected function check_single_value( $item_meta ) {
		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => $item_meta,
			'action'    => 'create',
		);

		return FrmEntryValidate::validate( $_POST );
	}

	public function test_phone_format() {
		$check_formats = array(
			array(
				'field_key' => 'phone_with_default_format',
				'format'    => '',
				'expected'  => $this->run_private_method( array( 'FrmEntryValidate', 'default_phone_format' ), array() ),
			),
			array(
				'field_key' => 'phone_with_format',
				'format'    => '999-999-9999',
				'expected'  => '^\d\d\d-\d\d\d-\d\d\d\d$',
			),
			array(
				'field_key' => 'phone_with_regex',
				'format'    => '^\d{3}-\d{4}$',
				'expected'  => '^\d{3}-\d{4}$', // Leave it alone
			),
		);

		foreach ( $check_formats as $check_it ) {
			$field = $this->factory->field->create_and_get(
				array(
					'type'          => 'phone',
					'form_id'       => $this->form->id,
					'field_key'     => $check_it['field_key'],
					'field_options' => array(
						'format' => $check_it['format'],
					),
				)
			);
			$this->assertSame( $check_it['format'], $field->field_options['format'] );

			$format = FrmEntryValidate::phone_format( $field );
			$this->assertSame( '/' . $check_it['expected'] . '/', $format );
		}
	}

	public function test_create_regular_expression_from_format() {
		$formats = array(
			'(999)999-2323' => '^\(\d\d\d\)\d\d\d-\d\d\d\d$',
			'a9aa2328'      => '^[a-zA-Z]\d[a-zA-Z][a-zA-Z]\d\d\d\d$',
			'****'          => '^\w\w\w\w$',
			'99/23'         => '^\d\d\/\d\d$',
			'99?99'         => '^\d\d(\d\d)?$',
		);

		foreach ( $formats as $start => $expected ) {
			$new_format = $this->run_private_method( array( 'FrmEntryValidate', 'create_regular_expression_from_format' ), array( $start ) );
			$this->assertSame( $expected, $new_format );
		}
	}

	public function test_is_akismet_enabled_for_user() {
		$this->assertEmpty( $this->form->options['akismet'] );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $this->form->id ) );
		$this->assertFalse( $enabled );

		$akismet_for_everyone = $this->factory->form->create_and_get(
			array(
				'options' => array(
					'akismet' => '1',
				),
			)
		);
		$this->assertNotEmpty( $akismet_for_everyone->options['akismet'] );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $akismet_for_everyone->id ) );
		$this->assertTrue( $enabled );

		$akismet_logged = $this->factory->form->create_and_get(
			array(
				'options' => array(
					'akismet' => 'logged',
				),
			)
		);
		$this->assertSame( 'logged', $akismet_logged->options['akismet'] );

		wp_set_current_user( 0 );
		$this->assertFalse( is_user_logged_in() );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $akismet_logged->id ) );
		$this->assertTrue( $enabled, 'Akismet not enabled for logged out users' );

		$this->set_current_user_to_1();
		$this->assertTrue( is_user_logged_in() );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $akismet_logged->id ) );
		$this->assertFalse( $enabled, 'Akismet enabled for logged in users' );
	}
}
