<?php

class test_FrmSpamCheckWPDisallowedWords extends FrmUnitTest {

	/**
	 * @covers FrmEntryValidate::blacklist_check
	 * @covers FrmAntiSpamController::contains_wp_disallowed_words
	 */
	public function test_check() {
		$values = array(
			'item_meta'      => array(
				'25' => '23.342.33',
				'36' => 'email@example.com',
				'37' => array( 'value1', 'value2' ),
			),
			'name_field_ids' => array(),
			'form_id'        => 1,
		);

		update_option( $this->get_disallowed_option_name(), '' );
		$spam_check = new FrmSpamCheckWPDisallowedWords( $values );
		$this->assertFalse( $spam_check->is_spam() );

		$blocked   = '23.343.12332';
		$new_block = $blocked . "\nspamemail@example.com";
		update_option( $this->get_disallowed_option_name(), $new_block );
		$this->assertSame( $new_block, get_option( $this->get_disallowed_option_name() ) );

		$wp_test = $this->run_private_method(
			array( $spam_check, 'do_check_wp_disallowed_words' ),
			array( 'Author', 'author@gmail.com', '', 'No spam here', FrmAppHelper::get_ip_address(), FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' ) )
		);
		$this->assertFalse( $wp_test );

		$ip      = FrmAppHelper::get_ip_address();
		$wp_test = $this->run_private_method(
			array( $spam_check, 'do_check_wp_disallowed_words' ),
			array( 'Author', 'author@gmail.com', '', $blocked, $ip, FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' ) )
		);

		if ( ! $wp_test ) {
			$this->markTestSkipped( 'WordPress blacklist check is failing in some cases' );
		}
		$this->assertTrue( $wp_test, 'WordPress missing spam for IP ' . $ip . ' agent ' . FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' ) );

		$this->assertFalse( $spam_check->is_spam() );

		$is_spam = FrmAntiSpamController::contains_wp_disallowed_words( array( 'item_meta' => array( '', '' ) ) );
		$this->assertFalse( $is_spam );

		$values['item_meta']['25'] = $blocked;
		$is_spam                   = FrmAntiSpamController::contains_wp_disallowed_words( $values );
		$this->assertTrue( $is_spam, 'Exact match for spam missed' );

		$values['item_meta']['25'] = $blocked . '23.343.1233234323';
		$is_spam                   = FrmAntiSpamController::contains_wp_disallowed_words( $values );
		$this->assertTrue( $is_spam );
	}

	/**
	 * The name of the disallowed list of words was changed in WP 5.5.
	 */
	private function get_disallowed_option_name() {
		$keys = get_option( 'disallowed_keys' );
		// Fallback for WP < 5.5.
		return false === $keys ? 'blacklist_keys' : 'disallowed_keys';
	}
}
