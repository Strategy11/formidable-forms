<?php

class test_FrmInbox extends FrmUnitTest {

	private $inbox;

	public function test_add_message() {
		$this->inbox   = new FrmInbox();
		$initial_count = $this->get_message_count();
		$message       = array(
			'key'     => 'sale-20201108',
			'subject' => 'Want a Free 27-inch iMac?',
			'message' => 'Do you write code? Edit videos? Create podcasts? Play online games? Want a free 27” iMac to do it? We want you to have one.',
			'cta'     => 'Win a Free iMac',
			'icon'    => 'frm_price_tags_icon',
			'type'    => 'promo',
		);
		$this->inbox->add_message( $message );
		$this->assert_message_count( $initial_count + 1, 'Message count should go up after a valid message is added.' );

		$invalid_message = $message['message'];
		$this->inbox->add_message( $invalid_message );
		$this->assert_message_count( $initial_count + 1, 'Message count should not go up after an invalid message is added.' );
	}

	private function assert_message_count( $count, $message ) {
		$this->assertEquals( $count, $this->get_message_count(), $message );
	}

	private function get_message_count() {
		return count( $this->inbox->get_messages() );
	}
}
