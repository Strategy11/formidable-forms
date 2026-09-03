<?php

class test_FrmSpamCheckDenylist extends FrmUnitTest {

	private $text_field_id;

	private $email_field_id;

	private $email_field_id2;

	private $name_field_id;

	private $spam_check;

	private $custom_denylist_data;

	private $default_values;

	public function setUp(): void {
		parent::setUp();

		$form_id = $this->factory->form->create(
			array(
				'form_key' => 'test_form_spam',
			)
		);

		$fields              = FrmField::getAll( array( 'form_id' => $form_id ) );
		$this->text_field_id = $fields[0]->id;

		$this->email_field_id = $this->factory->field->create(
			array(
				'type'    => 'email',
				'form_id' => $form_id,
			)
		);

		$this->name_field_id = $this->factory->field->create(
			array(
				'type'    => 'name',
				'form_id' => $form_id,
			)
		);

		$this->email_field_id2 = $this->factory->field->create(
			array(
				'type'    => 'email',
				'form_id' => $form_id,
			)
		);

		$this->default_values = array(
			'form_id'   => $form_id,
			'item_meta' => array(
				$this->email_field_id  => 'test@gmail.com',
				$this->text_field_id   => 'this text contains test@domain.com',
				$this->name_field_id   => array(
					'first' => 'WordPress',
					'last'  => 'Plugin',
				),
				$this->email_field_id2 => 'john@doe.com',
			),
		);

		$this->spam_check = new FrmSpamCheckDenylist( $this->default_values );

		$this->custom_denylist_data = array(
			'denylist_with_all_fields'      => array(
				'words' => array( 'spamword' ),
			),
			'denylist_with_name_text_email' => array(
				'words'       => array( 'spamword' ),
				'field_types' => array( 'text', 'email', 'name' ),
			),
			'denylist_with_name'            => array(
				'words'       => array( 'spamword' ),
				'field_types' => array( 'name' ),
			),
			'denylist_with_email'           => array(
				'words'       => array( 'spamword' ),
				'field_types' => array( 'email' ),
			),
			'denylist_with_extract_email'   => array(
				'words'         => array( 'spamword' ),
				'field_types'   => array(),
				'extract_value' => array( 'FrmAntiSpamController', 'extract_emails_from_values' ),
			),
		);
	}

	/**
	 * @param array $denylist_data
	 */
	private function set_denylist_data( $denylist_data ) {
		$this->set_private_property( $this->spam_check, 'denylist', $denylist_data );
	}

	public function test_get_field_ids_to_check() {
		$denylist = $this->custom_denylist_data['denylist_with_all_fields'];

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $denylist )
		);
		$this->assertFalse( $field_ids_to_check );

		$denylist['skip_field_types'] = array( 'email' );
		$field_ids_to_check           = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $denylist )
		);
		$this->assertEquals(
			array(
				$this->text_field_id,
				$this->name_field_id,
			),
			$field_ids_to_check
		);

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name_text_email'] )
		);
		$this->assertEquals(
			array(
				$this->text_field_id,
				$this->email_field_id,
				$this->name_field_id,
				$this->email_field_id2,
			),
			$field_ids_to_check
		);

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name'] )
		);
		$this->assertEquals( array( $this->name_field_id ), $field_ids_to_check );

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_email'] )
		);
		$this->assertEquals( array( $this->email_field_id, $this->email_field_id2 ), $field_ids_to_check );
	}

	public function test_get_values_to_check() {
		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_all_fields'] )
		);
		$this->assertSame(
			array(
				'test@gmail.com',
				'this text contains test@domain.com',
				'WordPress Plugin',
				'john@doe.com',
			),
			$values_to_check
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name_text_email'] )
		);
		$this->assertSame(
			array(
				'test@gmail.com',
				'this text contains test@domain.com',
				'WordPress Plugin',
				'john@doe.com',
			),
			$values_to_check
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name'] )
		);
		$this->assertSame(
			array(
				'WordPress Plugin',
			),
			$values_to_check
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_extract_email'] )
		);
		$this->assertSame(
			array(
				'test@gmail.com',
				'test@domain.com',
				'john@doe.com',
			),
			$values_to_check
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_email'] )
		);
		$this->assertSame(
			array(
				'test@gmail.com',
				'john@doe.com',
			),
			$values_to_check
		);
	}

	public function test_check_values() {
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertFalse( $this->run_private_method( array( $spam_check, 'check_values' ) ) );

		$denylist = $this->custom_denylist_data['denylist_with_all_fields'];
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['words'] = array( '.com' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['compare'] = FrmSpamCheckDenylist::COMPARE_EQUALS;
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['words']   = array( '@' );
		$denylist['compare'] = FrmSpamCheckDenylist::COMPARE_CONTAINS;
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['skip'] = true;
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist          = $this->custom_denylist_data['denylist_with_name'];
		$denylist['words'] = array( '@' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['words'][] = 'plugin';
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['skip_field_types'] = array( 'name' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist          = $this->custom_denylist_data['denylist_with_all_fields'];
		$denylist['words'] = array( 'plugin' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['extract_value'] = array( 'FrmAntiSpamController', 'extract_emails_from_values' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist         = $this->custom_denylist_data['denylist_with_all_fields'];
		$denylist['file'] = __DIR__ . '/denylist-email-contain.txt';
		unset( $denylist['words'] );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$denylist['extract_value'] = array( 'FrmAntiSpamController', 'extract_emails_from_values' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		FrmAppHelper::get_settings()->update_setting( 'allowed_words', "wordpress\nplugin", 'sanitize_textarea_field' );
		unset( $denylist['extract_value'] );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		FrmAppHelper::get_settings()->update_setting( 'disallowed_words', "wordprezz\ndoe.com", 'sanitize_textarea_field' );
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertTrue( $this->run_private_method( array( $spam_check, 'check_values' ) ) );

		// Test with regex.
		$values                                       = $this->default_values;
		$values['item_meta'][ $this->email_field_id ] = 'someone@mail.ru';

		$spam_check = new FrmSpamCheckDenylist( $values );
		$this->assertTrue( $this->run_private_method( array( $spam_check, 'check_values' ) ) );

		$values                                      = $this->default_values;
		$values['item_meta'][ $this->text_field_id ] = 'This text contains someone@yandex.com email';

		$spam_check = new FrmSpamCheckDenylist( $values );
		$this->assertTrue( $this->run_private_method( array( $spam_check, 'check_values' ) ) );

		// Reset.
		FrmAppHelper::get_settings()->update_setting( 'allowed_words', '', 'sanitize_textarea_field' );
		FrmAppHelper::get_settings()->update_setting( 'disallowed_words', '', 'sanitize_textarea_field' );
	}

	public function test_check_ip() {
		$current_ip = $_SERVER['REMOTE_ADDR'];

		// Mock IP address.
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		// Test when IP is blacklisted.
		function frm_test_filter_denylist_ip_data() {
			return array(
				'custom' => array( '192.168.1.1' ),
				'files'  => array(),
			);
		}

		add_filter( 'frm_denylist_ips_data', 'frm_test_filter_denylist_ip_data' );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_ip' ) ) );

		function frm_test_filter_allowed_ips() {
			return array( '192.168.1.1' );
		}
		// Test when IP is whitelisted.
		add_filter( 'frm_allowed_ips', 'frm_test_filter_allowed_ips' );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_ip' ) ) );
		remove_filter( 'frm_allowed_ips', 'frm_test_filter_allowed_ips' );
		remove_filter( 'frm_denylist_ips_data', 'frm_test_filter_denylist_ip_data' );

		// Test IP CIDR format.
		function frm_test_filter_denylist_ip_data_2() {
			return array(
				'custom' => array(),
				'files'  => array( __DIR__ . '/denylist-ip.txt' ),
			);
		}

		add_filter( 'frm_denylist_ips_data', 'frm_test_filter_denylist_ip_data_2' );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_ip' ) ) );
		remove_filter( 'frm_denylist_ips_data', 'frm_test_filter_denylist_ip_data_2' );

		// Reset the IP address.
		$_SERVER['REMOTE_ADDR'] = $current_ip;
	}

	public function test_ip_matches() {
		$this->assertTrue(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.168.1.1', '192.168.1.1' )
			)
		);

		$this->assertFalse(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.168.1.1', '192.168.1.0' )
			)
		);

		$this->assertTrue(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.168.1.0', '192.168.1.0/24' )
			)
		);

		$this->assertTrue(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.168.1.1', '192.168.1.0/24' )
			)
		);

		$this->assertFalse(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.168.2.1', '192.168.1.0/24' )
			)
		);

		$this->assertTrue(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.168.2.1', '192.168.1.0/16' )
			)
		);

		$this->assertFalse(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.1.2.1', '192.168.1.0/16' )
			)
		);

		$this->assertTrue(
			$this->run_private_method(
				array( $this->spam_check, 'ip_matches' ),
				array( '192.1.2.1', '192.168.1.0/8' )
			)
		);
	}

	/**
	 * Runs check_values() against the given denylist data.
	 *
	 * @param array                     $denylist_data Array of denylists.
	 * @param FrmSpamCheckDenylist|null $spam_check    The check to run, or null for the one built in setUp().
	 *
	 * @return bool
	 */
	private function check_values_with( $denylist_data, $spam_check = null ) {
		if ( null === $spam_check ) {
			$spam_check = $this->spam_check;
		}

		$this->set_private_property( $spam_check, 'denylist', $denylist_data );
		return $this->run_private_method( array( $spam_check, 'check_values' ) );
	}

	/**
	 * A denylist file is compared line by line, and blank lines are skipped. A
	 * blank line that reached the compare would match every submission, so a file
	 * holding one that comes back clean is the assertion.
	 */
	public function test_check_values_with_denylist_file() {
		$denylist = array(
			'file' => __DIR__ . '/denylist-email-contain.txt',
		);

		// The file holds `wordpress` and `plugin`, both in the name field value.
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		// Neither word is in the email values, and the blank line must not match.
		$denylist['field_types'] = array( 'email' );
		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );
	}

	/**
	 * An allowed word skips its own line only. A later line still has to be able
	 * to flag the submission.
	 */
	public function test_check_values_skips_allowed_words_line_by_line() {
		$denylist = array(
			'file' => __DIR__ . '/denylist-email-contain.txt',
		);

		// `wordpress` is the first line of the file and `plugin` the last. Allowing
		// the first still has to leave the last able to flag the submission.
		FrmAppHelper::get_settings()->update_setting( 'allowed_words', 'wordpress', 'sanitize_textarea_field' );
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		// Allowing both words leaves nothing to flag.
		FrmAppHelper::get_settings()->update_setting( 'allowed_words', "wordpress\nplugin", 'sanitize_textarea_field' );
		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );

		FrmAppHelper::get_settings()->update_setting( 'allowed_words', '', 'sanitize_textarea_field' );
	}

	/**
	 * Each denylist is checked against the values its own field types select, so a
	 * denylist that finds nothing must not stop the ones after it, and must not
	 * lend its values to them either.
	 */
	public function test_check_values_scopes_values_to_each_denylist() {
		$in_email_only = array(
			'words'       => array( 'doe.com' ),
			'field_types' => array( 'email' ),
		);
		$in_name_only  = array(
			'words'       => array( 'wordpress' ),
			'field_types' => array( 'name' ),
		);

		$this->assertTrue( $this->check_values_with( array( $in_email_only ) ) );
		$this->assertTrue( $this->check_values_with( array( $in_name_only ) ) );

		// Each word is only in the other denylist's fields, so neither can match.
		$misses = array(
			array(
				'words'       => array( 'doe.com' ),
				'field_types' => array( 'name' ),
			),
			array(
				'words'       => array( 'wordpress' ),
				'field_types' => array( 'email' ),
			),
		);
		$this->assertFalse( $this->check_values_with( $misses ) );

		// A denylist that finds nothing must not stop a later one from matching.
		$this->assertTrue( $this->check_values_with( array( $misses[0], $in_email_only ) ) );
		$this->assertTrue( $this->check_values_with( array( $misses[1], $in_name_only ) ) );
	}

	/**
	 * A denylist whose field types select none of the submitted values is not spam,
	 * even when the word is somewhere else in the submission.
	 */
	public function test_check_values_with_nothing_to_check() {
		$denylist = array(
			'words'       => array( 'plugin' ),
			// The test form has text, email and name fields only.
			'field_types' => array( 'url' ),
		);

		$this->assertSame(
			array(),
			$this->run_private_method(
				array( $this->spam_check, 'get_field_ids_to_check' ),
				array( $denylist )
			)
		);

		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );

		// It must not stop a later denylist from checking the values it selects.
		$this->assertTrue(
			$this->check_values_with(
				array(
					$denylist,
					array(
						'words' => array( 'plugin' ),
					),
				)
			)
		);

		// The same word does flag the submission once the name field is included.
		$denylist['field_types'] = array( 'name' );
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );
	}

	/**
	 * The same instance has to answer the same way every time, and has to follow
	 * the denylist it is given rather than one it was given earlier.
	 */
	public function test_check_values_is_repeatable() {
		$matches = array(
			'words' => array( 'plugin' ),
		);
		$misses  = array(
			'words' => array( 'notinvalues' ),
		);

		$this->assertTrue( $this->check_values_with( array( $matches ) ) );
		$this->assertTrue( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$this->assertFalse( $this->check_values_with( array( $misses ) ) );
		$this->assertFalse( $this->run_private_method( array( $this->spam_check, 'check_values' ) ) );

		$this->assertTrue( $this->check_values_with( array( $matches ) ) );
	}

	/**
	 * COMPARE_EQUALS matches a whole value, ignoring case, and never a substring.
	 */
	public function test_check_values_with_equals_compare() {
		$denylist = array(
			'words'   => array( 'WordPress Plugin' ),
			'compare' => FrmSpamCheckDenylist::COMPARE_EQUALS,
		);
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		$denylist['words'] = array( 'wordpress plugin' );
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		// A substring of a value is not an equals match.
		$denylist['words'] = array( 'plugin' );
		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );

		// The same word is a contains match.
		$denylist['compare'] = FrmSpamCheckDenylist::COMPARE_CONTAINS;
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );
	}

	/**
	 * Regex denylists are matched case insensitively against the values.
	 */
	public function test_check_values_with_regex() {
		$denylist = array(
			'words'    => array( 'wordpress\s+plugin' ),
			'is_regex' => true,
		);
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		$denylist['words'] = array( 'wordpress\s+theme' );
		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );

		// Field types narrow a regex denylist the same way they narrow the others.
		$denylist['words']       = array( 'wordpress\s+plugin' );
		$denylist['field_types'] = array( 'email' );
		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );
	}

	/**
	 * Repeater values arrive as a nested array carrying `form` and `row_ids` keys.
	 * The rows are checked and those two keys are not.
	 */
	public function test_get_values_to_check_with_repeater_values() {
		$values = $this->default_values;

		// The key is a section field ID and the sub keys are its row IDs.
		$values['item_meta'][9999] = array(
			'form'    => 4242,
			'row_ids' => array( 'i0' ),
			'i0'      => array( 8888 => 'repeated value' ),
		);

		$spam_check      = new FrmSpamCheckDenylist( $values );
		$values_to_check = $this->run_private_method(
			array( $spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_all_fields'] )
		);

		$this->assertContains( 'repeated value', $values_to_check );
		$this->assertNotContains( 4242, $values_to_check );
		$this->assertNotContains( array( 'i0' ), $values_to_check );
	}

	/**
	 * Other values are checked unless `other` is in the skipped field types.
	 */
	public function test_get_values_to_check_with_other_values() {
		$values                       = $this->default_values;
		$values['item_meta']['other'] = array( 7777 => 'other value' );
		$spam_check                   = new FrmSpamCheckDenylist( $values );

		$denylist        = array(
			'skip_field_types' => array(),
		);
		$values_to_check = $this->run_private_method(
			array( $spam_check, 'get_values_to_check' ),
			array( $denylist )
		);
		$this->assertContains( 'other value', $values_to_check );

		$denylist['skip_field_types'] = array( 'other' );
		$values_to_check              = $this->run_private_method(
			array( $spam_check, 'get_values_to_check' ),
			array( $denylist )
		);
		$this->assertNotContains( 'other value', $values_to_check );
	}

	/**
	 * The word that flagged the submission is recorded, whether it came from the
	 * denylist words or from a line of a denylist file.
	 */
	public function test_matched_word_is_recorded() {
		$transient_name = 'frm_recent_spam_detected';

		delete_transient( $transient_name );
		$this->assertTrue( $this->check_values_with( array( array( 'words' => array( 'plugin' ) ) ) ) );
		$this->assertContains( 'plugin', (array) get_transient( $transient_name ) );

		delete_transient( $transient_name );
		$this->assertTrue( $this->check_values_with( array( array( 'file' => __DIR__ . '/denylist-email-contain.txt' ) ) ) );
		// `wordpress` is the first line of the file that matches.
		$this->assertContains( 'wordpress', (array) get_transient( $transient_name ) );

		delete_transient( $transient_name );
	}

	/**
	 * The values are compared as a JSON string, which escapes forward slashes, and
	 * they are unescaped again before the compare. Without that, no denylist entry
	 * holding a slash could ever match.
	 */
	public function test_check_values_with_slashes_in_denylist_words() {
		$values                                      = $this->default_values;
		$values['item_meta'][ $this->text_field_id ] = 'go to spam/path/here and /joomla/ today';
		$spam_check                                  = new FrmSpamCheckDenylist( $values );

		$this->assertTrue( $this->check_values_with( array( array( 'words' => array( 'spam/path' ) ) ), $spam_check ) );
		$this->assertTrue( $this->check_values_with( array( array( 'words' => array( '/joomla/' ) ) ), $spam_check ) );

		// A slash in the word still has to be part of the value to match.
		$this->assertFalse( $this->check_values_with( array( array( 'words' => array( 'spam/other' ) ) ), $spam_check ) );
	}

	/**
	 * Option fields hold values the site author wrote, and password fields hold
	 * secrets, so fill_default_denylist_data() always adds them to the skipped
	 * field types. A denylisted word in one of them is not spam.
	 */
	public function test_check_values_skips_the_default_skipped_field_types() {
		$form_id  = FrmField::getOne( $this->text_field_id )->form_id;
		$denylist = array(
			array(
				'words' => array( 'zzspamword' ),
			),
		);

		foreach ( array( 'radio', 'checkbox', 'select', 'password' ) as $type ) {
			$field_id   = $this->factory->field->create(
				array(
					'type'    => $type,
					'form_id' => $form_id,
				)
			);
			$spam_check = new FrmSpamCheckDenylist(
				array(
					'form_id'   => $form_id,
					'item_meta' => array( $field_id => 'zzspamword' ),
				)
			);

			$this->assertFalse(
				$this->check_values_with( $denylist, $spam_check ),
				'A denylisted word in a ' . $type . ' field should not be spam.'
			);
		}

		// The same word in a field type that is checked is spam.
		$spam_check = new FrmSpamCheckDenylist(
			array(
				'form_id'   => $form_id,
				'item_meta' => array( $this->text_field_id => 'zzspamword' ),
			)
		);
		$this->assertTrue( $this->check_values_with( $denylist, $spam_check ) );
	}

	/**
	 * The denylist check runs only while its setting is on, and the
	 * frm_check_denylist filter can turn it off on its own.
	 */
	public function test_is_spam_respects_the_denylist_setting() {
		$frm_settings = FrmAppHelper::get_settings();
		$was_enabled  = $frm_settings->denylist_check;

		add_filter( 'frm_denylist_data', array( $this, 'filter_denylist_to_one_word' ) );

		$frm_settings->update_setting( 'denylist_check', 1, 'absint' );
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertNotFalse( $spam_check->is_spam() );

		$frm_settings->update_setting( 'denylist_check', 0, 'absint' );
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertFalse( $spam_check->is_spam() );

		// The filter turns the check off while the setting is still on.
		$frm_settings->update_setting( 'denylist_check', 1, 'absint' );
		add_filter( 'frm_check_denylist', '__return_false' );
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertFalse( $spam_check->is_spam() );
		remove_filter( 'frm_check_denylist', '__return_false' );

		remove_filter( 'frm_denylist_data', array( $this, 'filter_denylist_to_one_word' ) );
		$frm_settings->update_setting( 'denylist_check', $was_enabled, 'absint' );
	}

	/**
	 * Replaces the shipped denylist with a single word that is in the test values,
	 * so the setting is what decides the result rather than the denylist files.
	 *
	 * @return array[]
	 */
	public function filter_denylist_to_one_word() {
		return array(
			array(
				'words' => array( 'plugin' ),
			),
		);
	}

	/**
	 * Text long enough that the values pass MIN_LENGTH_TO_INDEX once encoded.
	 *
	 * @return string
	 */
	private function get_filler_text() {
		return str_repeat( 'ordinary sentence about a kitchen remodel. ', 60 );
	}

	/**
	 * Builds a submission whose values are long enough to be indexed, with the
	 * given text planted in the middle of them.
	 *
	 * @param string $planted Text to plant in the middle of a value.
	 *
	 * @return FrmSpamCheckDenylist
	 */
	private function get_long_submission( $planted = '' ) {
		$filler = $this->get_filler_text();

		// Only the one field, so nothing in the other default values can match.
		return new FrmSpamCheckDenylist(
			array(
				'form_id'   => $this->default_values['form_id'],
				'item_meta' => array(
					$this->text_field_id => $filler . $planted . ' ' . $filler,
				),
			)
		);
	}

	/**
	 * Returns the denylist shape get_values_prefix_index() reads: the comparison
	 * settings and the lowercased values string.
	 *
	 * @param string $values_string_lower The values to index.
	 * @param array  $extra               Denylist settings to override.
	 *
	 * @return array
	 */
	private function get_indexable_denylist( $values_string_lower, $extra = array() ) {
		return array_merge(
			array(
				'is_regex'            => false,
				'compare'             => FrmSpamCheckDenylist::COMPARE_CONTAINS,
				'values_string_lower' => $values_string_lower,
			),
			$extra
		);
	}

	/**
	 * Runs get_values_prefix_index() for the given denylist shape.
	 *
	 * @param array $denylist Denylist data.
	 *
	 * @return array
	 */
	private function get_prefix_index( $denylist ) {
		return $this->run_private_method(
			array( $this->spam_check, 'get_values_prefix_index' ),
			array( $denylist )
		);
	}

	/**
	 * The index holds every PREFIX_LENGTH character window of the values, and only
	 * gets built for values long enough to be worth it.
	 */
	public function test_get_values_prefix_index() {
		$long  = strtolower( wp_json_encode( array( $this->get_filler_text() ) ) );
		$index = $this->get_prefix_index( $this->get_indexable_denylist( $long ) );

		$this->assertNotEmpty( $index );

		// Every window of the values is a key, and nothing else is.
		$this->assertArrayHasKey( 'ordi', $index );
		$this->assertArrayHasKey( 'kitc', $index );
		$this->assertArrayNotHasKey( 'zzzz', $index );

		// Short values are not indexed, so they are compared exactly as before.
		$this->assertSame( array(), $this->get_prefix_index( $this->get_indexable_denylist( 'short values' ) ) );

		// A regex line is a pattern, not text to look for, so it is never indexed.
		$this->assertSame(
			array(),
			$this->get_prefix_index( $this->get_indexable_denylist( $long, array( 'is_regex' => true ) ) )
		);

		// An equals comparison is against whole values, not the joined string.
		$this->assertSame(
			array(),
			$this->get_prefix_index(
				$this->get_indexable_denylist( $long, array( 'compare' => FrmSpamCheckDenylist::COMPARE_EQUALS ) )
			)
		);
	}

	/**
	 * The index only rules lines out, so an indexed submission has to reach the
	 * same verdict as an unindexed one, for words and for file lines.
	 */
	public function test_prefix_index_does_not_change_the_verdict() {
		$long = $this->get_long_submission( 'buy-cheap-widgets.example' );

		$this->assertTrue(
			$this->check_values_with( array( array( 'words' => array( 'buy-cheap-widgets.example' ) ) ), $long )
		);
		$this->assertFalse(
			$this->check_values_with( array( array( 'words' => array( 'zzzz-not-in-the-values' ) ) ), $long )
		);

		// A word that shares its first characters with the values but is not in
		// them survives the index and is then ruled out by the comparison.
		$this->assertFalse(
			$this->check_values_with( array( array( 'words' => array( 'kitchen-remodel-spam.example' ) ) ), $long )
		);
	}

	/**
	 * A match on the last line of a file is still found once the lines before it
	 * have been ruled out by the index.
	 */
	public function test_prefix_index_still_finds_a_match_on_the_last_line() {
		$denylist = array(
			array(
				// The file is `wordpress`, a blank line, then `plugin`.
				'file' => __DIR__ . '/denylist-email-contain.txt',
			),
		);

		// Only the word on the last line is present, so the first line has to be
		// ruled out and the last one still found.
		$long = $this->get_long_submission( 'a plugin for you' );
		$this->assertTrue( $this->check_values_with( $denylist, $long ) );

		// Neither word present, and the blank line must not match.
		$clean = $this->get_long_submission( 'nothing to see' );
		$this->assertFalse( $this->check_values_with( $denylist, $clean ) );
	}

	/**
	 * A regex denylist still matches on a long submission, where the index would
	 * have wrongly ruled the pattern out had it been applied.
	 */
	public function test_regex_denylist_matches_on_an_indexed_length_submission() {
		$long = $this->get_long_submission( 'buy prada handbags' );

		$this->assertTrue(
			$this->check_values_with(
				array(
					array(
						'words'    => array( 'prada\s+handbags' ),
						'is_regex' => true,
					),
				),
				$long
			)
		);
	}

	/**
	 * Lines shorter than the index key cannot be looked up, so they are compared
	 * rather than wrongly ruled out.
	 */
	public function test_lines_shorter_than_the_index_key_are_still_compared() {
		$long = $this->get_long_submission( 'aaa' );

		$this->assertTrue( $this->check_values_with( array( array( 'words' => array( 'aaa' ) ) ), $long ) );
		$this->assertFalse( $this->check_values_with( array( array( 'words' => array( 'zqx' ) ) ), $long ) );
	}
}
