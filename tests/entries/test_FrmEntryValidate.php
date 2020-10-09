<?php

/**
 * @group entries
 * @group free
 *
 */
class test_FrmEntryValidate extends FrmUnitTest {

	/**
	 * @covers FrmEntryValidate::get_spam_check_user_info
	 */
	public function test_get_spam_check_user_info() {
		$made_up_name_field_id  = 4;
		$made_up_email_field_id = 12;
		$made_up_url_field_id   = 16;
		$test_name              = 'Some Guy';
		$test_email             = 'amadeupemail@email.com';
		$test_url               = 'http://madeupwebsite.com';
		$values                 = array(
			'item_meta' => array(
				0                       => '',
				$made_up_name_field_id  => $test_name,
				$made_up_email_field_id => $test_email,
				$made_up_url_field_id   => $test_url,
			)
		);

		wp_set_current_user( null );
		$check = $this->get_spam_check_user_info( $values );
		$this->assertTrue( empty( $check['user_ID'] ) );
		$this->assertTrue( empty( $check['user_id'] ) );
		$this->assertEquals( $test_name, $check['comment_author'] );
		$this->assertEquals( $test_email, $check['comment_author_email'] );
		$this->assertEquals( $test_url, $check['comment_author_url'] );		

		wp_set_current_user( 1 );
		$user  = wp_get_current_user();
		$check = $this->get_spam_check_user_info( $values );
		$this->assertEquals( $user->ID, $check['user_ID'] );
		$this->assertEquals( $user->ID, $check['user_id'] );
		$this->assertEquals( $user->display_name, $check['comment_author'] );
		$this->assertEquals( $user->user_email, $check['comment_author_email'] );
		$this->assertEquals( $user->user_url, $check['comment_author_url'] );
	}

	private function get_spam_check_user_info( $values ) {
		return $this->run_private_method(
			array( 'FrmEntryValidate', 'get_spam_check_user_info' ),
			array( $values )
		);
	}
}
