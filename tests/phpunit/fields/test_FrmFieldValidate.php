<?php

/**
 * @group fields
 */
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

	/**
	 * @covers FrmEntryValidate::validate
	 */
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

	/**
	 * @covers FrmFieldType::validate
	 * @covers FrmFieldNumber::validate
	 * @covers FrmFieldPhone::validate
	 * @covers FrmFieldUrl::validate
	 */
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
				'invalid' => false,
			),
			array(
				'type'    => 'url',
				'value'   => 'https://пример.рф',
				'invalid' => false,
			),
			array(
				'type'    => 'url',
				'value'   => 'https://a/b.com',
				'invalid' => true,
			),
		);
	}

	/**
	 * @covers FrmEntryValidate::validate
	 */
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
	 * @covers FrmFieldUrl::validate
	 */
	public function test_url_value() {
		$field = FrmField::getOne( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );

		$this->set_required_field( $field );

		$errors = $this->check_single_value( array( $field->id => 'http://' ) );
		$this->assertArrayHasKey( 'field' . $field->id, $errors, 'http:// passed required validation ' . print_r( $errors, 1 ) );
	}

	/**
	 * Internationalized domain names must pass validation.
	 *
	 * The host pattern in FrmFieldUrl::validate() matches UTF-8 bytes, so non-ASCII hosts are
	 * accepted. These are real registrable domains - .ch permits accented vowels - and the punycode
	 * spelling of the same domain has always passed, so accepting these adds no new capability.
	 *
	 * @covers FrmFieldUrl::validate
	 */
	public function test_url_idn_validation() {
		$field = $this->factory->field->get_object_by_id( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );

		$should_pass = array(
			'https://ernährung.ch',
			'https://münchen.de',
			'https://café.fr',
			'https://пример.рф',
			'https://例え.jp',
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
		 * The last two must fail even though the class now allows non-ASCII: the pattern still
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
	 * A raw Latin-1 host byte must still validate.
	 *
	 * This guards against adding the /u modifier to the host pattern. With /u, preg_match() returns
	 * false on invalid UTF-8, and because the result is negated the value would be reported invalid.
	 *
	 * @covers FrmFieldUrl::validate
	 */
	public function test_url_non_utf8_host_byte() {
		$field = $this->factory->field->get_object_by_id( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );

		$url = "https://ex\xE4mple.com";

		// Without this the assertion below would pass vacuously if the byte were stripped first.
		$this->assertNotEmpty( esc_url_raw( $url ), 'The Latin-1 host byte did not survive sanitizing, so this test proves nothing.' );

		$errors = $this->check_single_value( array( $field->id => $url ) );
		$this->assertArrayNotHasKey( 'field' . $field->id, $errors, 'A Latin-1 host byte failed validation, which suggests the /u modifier was added to the host pattern.' );
	}

	/**
	 * The JS copy of the host pattern must stay in step with the PHP one, and the minified artifact
	 * must be rebuilt whenever the source changes.
	 *
	 * There is no JS engine in this suite, so this asserts the rules on the source rather than
	 * running the regex: the code unit range is present, the PHP byte range was not copied across by
	 * mistake, the old ASCII-only class is gone, and the minified file carries the same class.
	 *
	 * @covers FrmFieldUrl::validate
	 */
	public function test_url_field_js_regex_parity() {
		$source   = FrmAppHelper::plugin_path() . '/js/formidable.js';
		$minified = FrmAppHelper::plugin_path() . '/js/formidable.min.js';

		foreach ( array( $source, $minified ) as $file ) {
			$this->assertFileExists( $file );

			$contents = file_get_contents( $file );
			$name     = basename( $file );

			$this->assertStringContainsString( '\u0080-\uFFFF', $contents, 'The JS host pattern is missing the code unit range in ' . $name );
			$this->assertStringNotContainsString( '\x80-\xff', $contents, 'The PHP byte range was copied into ' . $name . '. JS matches UTF-16 code units, so that would reject the Cyrillic and CJK hosts the server accepts.' );
			$this->assertStringNotContainsString( '[\da-z\.-]', $contents, 'The old ASCII-only host class is still present in ' . $name );
		}

		// The host class in the source must appear verbatim in the minified artifact.
		$matched = preg_match( '/\[\\\\da-z[^\]]*\]/', file_get_contents( $source ), $matches );
		$this->assertSame( 1, $matched, 'Could not find the url host class in js/formidable.js' );
		$this->assertStringContainsString( $matches[0], file_get_contents( $minified ), 'js/formidable.min.js is stale. Rebuild it so it carries the same url host class as js/formidable.js.' );
	}

	/**
	 * @covers FrmFieldEmail::validate
	 */
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

	/**
	 * @covers FrmFieldNumber::validate
	 */
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

	/**
	 * @covers FrmEntryValidate::phone_format
	 */
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

	/**
	 * @covers FrmEntryValidate::create_regular_expression_from_format
	 */
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

	/**
	 * @covers FrmEntryValidate::is_akismet_enabled_for_user
	 */
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
