<?php

/**
 * @group edit-shortcodes
 * @group entries
 * @group pro
 * @group entry-editing
 *
 */
class WP_Test_FrmEditingShortcodes extends FrmUnitTest {

	public function setUp() {
		parent::setUp();

		unset( $GLOBALS['frm_update_link_count'] );
	}

	/**
     * tests update field with a simple, single word, no unusual characters
     *
     * @group updateyes
     *
     * @covers FrmProEntriesController::entry_update_field
     *
	 */
	public function test_entry_update_link_with_yes() {
        $this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

		$atts          = array(
			'id'       => $entry_id,
			'field_id' => $field_id,
			'value'    => 'Yes',
		);

		$update_link   = FrmProEntriesController::entry_update_field( $atts );
		$expected_link = $this->generate_expected_link( $atts );

		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $atts['value'] );

	}

	/**
	 * Tests update link when the value has an apostrophe
	 *
	 * @covers FrmProEntriesController::entry_update_field
	 */
	public function test_entry_update_link_with_apostrophe() {
        $this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

		$this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
		$this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );
		$test_value    = "don't know";
		$atts          = array(
			'id'       => $entry_id,
			'field_id' => $field_id,
			'value'    => $test_value,
		);
		$update_link   = FrmProEntriesController::entry_update_field( $atts );
		$expected_link = '<a href="#" onclick="frmUpdateField(';
		$expected_link .= "{$entry_id},{$field_id}";
		$expected_link .= ",'don\'t know','',1);return false;\"";
		$expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
		$expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';
		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

	}

    /**
     * tests update field when update value is the same as the stored value
     *
     * @group updateyes
     *
     * @covers FrmProEntriesController::entry_update_field
     *
     */
    public function test_entry_update_link_with_same_value() {

        $this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

        $test_value    = 'No';
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
        );

        $update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = '';

        $this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value.' when it is the same as the stored value.' );

    }

    /**
     * Tests update link when the value has an ampersand
     *
     * @covers FrmProEntriesController::entry_update_field
     */
    public function test_entry_update_link_with_ampersand() {

        $this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

        $test_value    = "maybe yes & maybe no";
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
        );
        $update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = '<a href="#" onclick="frmUpdateField(';
        $expected_link .= "{$entry_id},{$field_id}";
        $expected_link .= ",'maybe yes &amp; maybe no','',1);return false;\"";
        $expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
        $expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';

        $this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

    }

    /**
     * Tests update link when the value has an HTML tag
     *
     * @covers FrmProEntriesController::entry_update_field
     */
    public function test_entry_update_link_with_html_tag() {

        $this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

        $test_value    = "<strong>Absolutely!</strong>";
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
        );

        $update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = '<a href="#" onclick="frmUpdateField(';
        $expected_link .= "{$entry_id},{$field_id}";
        $expected_link .= ",'&lt;strong&gt;Absolutely!&lt;/strong&gt;','',1);return false;\"";
        $expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
        $expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';

        $this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

    }

    /**
     * tests update field with a simple, single word, no permission to edit
     *
     * @group updateyes
     *
     * @covers FrmProEntriesController::entry_update_field
     *
     */
    public function test_entry_update_link_without_editing_permission() {

        $entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
        $field_id = FrmField::get_id_by_key( 'rsvp' );

        $test_value    = 'Yes';
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
        );

		$update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = '';

		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

    }

	/**
	 * Tests update link with a class added
	 *
	 * @covers FrmProEntriesController::entry_update_field
	 *
	 */
	public function test_entry_update_link_with_class() {

		$this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

		$test_value    = 'Yes';
		$atts          = array(
			'id'       => $entry_id,
			'field_id' => $field_id,
			'value'    => $test_value,
			'class'	   => 'my_custom_class invalid&character',
		);

		$update_link   = FrmProEntriesController::entry_update_field( $atts );
		$expected_link = '<a href="#" onclick="frmUpdateField(';
		$expected_link .= "{$entry_id},{$field_id}";
		$expected_link .= ",'{$test_value}','',1);return false;\"";
		$expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
		$expected_link .= ' class="frm_update_field_link my_custom_class invalid&amp;character" title="Update">Update</a>';

		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );
	}

	/**
	 * Tests two update links
	 *
	 * @covers FrmProEntriesController::entry_update_field
	 *
	 */
	public function test_two_update_links() {

		$this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

		$test_value    = 'Yes';
		$atts          = array(
			'id'       => $entry_id,
			'field_id' => $field_id,
			'value'    => $test_value,
		);
		$update_link   = FrmProEntriesController::entry_update_field( $atts );
		$expected_link = $this->generate_expected_link( $atts );

		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

		// Second link
		$atts['value'] = "Yes, ma'am";

		$update_link   = FrmProEntriesController::entry_update_field( $atts );
		$expected_link = '<a href="#"';
		$expected_link .= " onclick=\"frmUpdateField({$entry_id},{$field_id},'Yes, ma\'am','',2);return false;\"";
		$expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_2\"";
		$expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';

		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $atts['value'] );
	}

	/**
	 * Tests update link with a message added
	 *
	 * @covers FrmProEntriesController::entry_update_field
	 *
	 */
	public function test_entry_update_link_with_quotes_in_message() {
		$this->set_current_user_to_1();

		$entry_id = $this->get_entry_id( 'steve_conference' );
		$field_id = $this->get_field_id( 'rsvp' );

		$test_value    = 'Yes';
		$atts          = array(
			'id'       => $entry_id,
			'field_id' => $field_id,
			'value'    => $test_value,
			'message'  => 'Congratulations! You\'ve done a great job.',
		);

		$update_link   = FrmProEntriesController::entry_update_field( $atts );

		$expected_link = '<a href="#" onclick="frmUpdateField(';
		$expected_link .= "{$entry_id},{$field_id}";
		$expected_link .= ",'{$test_value}','Congratulations! You\'ve done a great job.',1);return false;\"";
		$expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
		$expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';

		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );
	}

	private function get_entry_id( $entry_key ) {
		$entry_id = FrmEntry::get_id_by_key( $entry_key );
		$this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );

		return $entry_id;
	}

	private function get_field_id( $field_key ) {
		$field_id = FrmField::get_id_by_key( $field_key );
		$this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );

		return $field_id;
	}

	private function generate_expected_link( $atts ) {
		$expected_link = '<a href="#" onclick="frmUpdateField(';
		$expected_link .= $atts['id'] . ',' . $atts['field_id'];
		$expected_link .= ",'{$atts['value']}','',1);return false;\"";
		$expected_link .= " id=\"frm_update_field_{$atts['id']}_{$atts['field_id']}_1\"";
		$expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';

		return $expected_link;

	}

}