<?php

/**
 * @group entries
 */
class test_FrmPersonalData extends FrmUnitTest {

	public $factory;
	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	public function setUp(): void {
		parent::setUp();
		$this->create_users();
	}

	/**
	 * @covers FrmPersonalData::get_user_entries
	 */
	public function test_get_user_entries() {
		$form       = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$this->factory->entry->create_object( $entry_data ); // create a non-matching entry

		$email    = 'notauser@mail.com';
		$expected = $this->create_entries_for_email( $entry_data, $email );
		$this->get_and_compare_entries( $expected, $email );

		$email    = 'subscriber@mail.com';
		$expected = $this->create_entries_for_user( $entry_data, $email );
		$this->get_and_compare_entries( $expected, $email );
	}

	private function get_and_compare_entries( $expected, $email ) {
		$data_class = new FrmPersonalData();
		$entries    = $this->run_private_method( array( $data_class, 'get_user_entries' ), array( $email ) );
		$this->assertSame( asort( $expected ), asort( $entries ) );
	}

	private function create_entries_for_user( $entry_data, $email ) {
		$user = get_user_by( 'email', $email );
		$this->assertNotEmpty( $user );

		$user_id_field = $this->factory->field->get_id_by_key( 'contact-user-id' );
		$email_field   = $this->factory->field->get_id_by_key( 'contact-email' );

		$entry_ids = $this->create_entries_for_email( $entry_data, $email );

		// with user id and matching email
		$entry_data['item_meta'][ $email_field ]   = $email;
		$entry_data['item_meta'][ $user_id_field ] = $user->ID;
		$entry_data['frm_user_id']                 = $user->ID;
		$entry_ids[]                               = (string) $this->factory->entry->create_object( $entry_data );

		// with user id by different email
		$entry_data['item_meta'][ $email_field ] = 'something@different.com';
		$entry_ids[]                             = (string) $this->factory->entry->create_object( $entry_data );

		return $entry_ids;
	}

	/**
	 * @param mixed $entry_data
	 * @param mixed $email
	 *
	 * @return string[]
	 */
	private function create_entries_for_email( $entry_data, $email ) {
		$email_field = $this->factory->field->get_id_by_key( 'contact-email' );

		// similar email should not match
		$entry_data['item_meta'][ $email_field ] = '2nd' . $email;
		$this->factory->entry->create_object( $entry_data );

		// with email but no user ID
		$entry_data['item_meta'][ $email_field ] = $email;
		$entry_ids                               = array();
		$entry_ids[]                             = (string) $this->factory->entry->create_object( $entry_data );

		return $entry_ids;
	}
}
