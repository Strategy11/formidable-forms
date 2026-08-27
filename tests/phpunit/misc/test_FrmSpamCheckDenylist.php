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
	 * @param array $denylist_data Array of denylists.
	 *
	 * @return bool
	 */
	private function check_values_with( $denylist_data ) {
		$this->set_denylist_data( $denylist_data );
		return $this->run_private_method( array( $this->spam_check, 'check_values' ) );
	}

	/**
	 * Every line of a denylist file has to be compared, not just the first one,
	 * and blank lines have to be skipped. A blank line that reaches the compare
	 * would match every submission, so a file holding one is the assertion.
	 */
	public function test_check_values_checks_every_line_of_a_file() {
		$denylist = array(
			'file' => __DIR__ . '/denylist-multiple-words.txt',
		);

		// The only word in the file that is in the values, `plugin`, is on the last line.
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		// None of the words are in the email values, and the blank line must not match either.
		$denylist['field_types'] = array( 'email' );
		$this->assertFalse( $this->check_values_with( array( $denylist ) ) );
	}

	/**
	 * An allowed word skips its own line only. A later line still has to be able
	 * to flag the submission.
	 */
	public function test_check_values_skips_allowed_words_line_by_line() {
		$denylist = array(
			'file' => __DIR__ . '/denylist-multiple-words.txt',
		);

		FrmAppHelper::get_settings()->update_setting( 'allowed_words', 'notinvalues', 'sanitize_textarea_field' );
		$this->assertTrue( $this->check_values_with( array( $denylist ) ) );

		// Allowing the word that actually matches leaves nothing to flag.
		FrmAppHelper::get_settings()->update_setting( 'allowed_words', "notinvalues\nplugin", 'sanitize_textarea_field' );
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
		$this->assertTrue( $this->check_values_with( array( array( 'file' => __DIR__ . '/denylist-multiple-words.txt' ) ) ) );
		$this->assertContains( 'plugin', (array) get_transient( $transient_name ) );

		delete_transient( $transient_name );
	}
}
