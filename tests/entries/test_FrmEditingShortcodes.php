<?php

/**
 * @group editshortcodes
 * @group entries
 * @group pro
 * @group entry-editing
 *
 */
class WP_Test_FrmEditingShortcodes extends FrmUnitTest {

	public function setUp() {
		parent::setUp();

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

		$entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
		$field_id = FrmField::get_id_by_key( 'rsvp' );
		$this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
		$this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );

		$test_value    = 'Yes';
		$atts          = array(
			'id'       => $entry_id,
			'field_id' => $field_id,
			'value'    => $test_value,
		);
		$update_link   = FrmProEntriesController::entry_update_field( $atts );
		$expected_link = '<a href="#" onclick="frmUpdateField(';
		$expected_link .= "{$entry_id},{$field_id}";
		$expected_link .= ",'{$test_value}','',1);return false;\"";
		$expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
		$expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';
		$this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

	}

	/**
	 * Tests update link when the value has an apostrophe
	 *
	 * @covers FrmProEntriesController::entry_update_field
	 */
	public function test_entry_update_link_with_apostrophe() {

        $this->set_current_user_to_1();

		$entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
		$field_id = FrmField::get_id_by_key( 'rsvp' );
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

        $entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
        $field_id = FrmField::get_id_by_key( 'rsvp' );
        $this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
        $this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );

        $test_value    = 'No';
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
        );
        $update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = null;
        $this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value.' when it is the same as the stored value.' );

    }

    /**
     * Tests update link when the value has an ampersand
     *
     * @covers FrmProEntriesController::entry_update_field
     */
    public function test_entry_update_link_with_ampersand() {

        $this->set_current_user_to_1();

        $entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
        $field_id = FrmField::get_id_by_key( 'rsvp' );
        $this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
        $this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );
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

        $entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
        $field_id = FrmField::get_id_by_key( 'rsvp' );
        $this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
        $this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );
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
        $this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
        $this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );

        $test_value    = 'Yes';
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
        );
        $update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = null;
        $this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value );

    }

    /**
     * tests update field with a simple, single word, no permission to edit, but allow is set to "everyone"
     *
     * @group updateyes
     *
     * @covers FrmProEntriesController::entry_update_field
     *
     */
    public function test_entry_update_link_with_allow_everyone() {


        $entry_id = FrmEntry::get_id_by_key( 'steve_conference' );
        $field_id = FrmField::get_id_by_key( 'rsvp' );
        $this->assertTrue( is_numeric( $entry_id ), 'Entry ID for steve conference is not valid.' );
        $this->assertTrue( is_numeric( $field_id ), 'Field ID for rsvp is not valid.' );

        $test_value    = 'Yes';
        $atts          = array(
            'id'       => $entry_id,
            'field_id' => $field_id,
            'value'    => $test_value,
            'allow'    => 'everyone',
        );
        $update_link   = FrmProEntriesController::entry_update_field( $atts );
        $expected_link = '<a href="#" onclick="frmUpdateField(';
        $expected_link .= "{$entry_id},{$field_id}";
        $expected_link .= ",'{$test_value}','',1);return false;\"";
        $expected_link .= " id=\"frm_update_field_{$entry_id}_{$field_id}_1\"";
        $expected_link .= ' class="frm_update_field_link " title="Update">Update</a>';
        $this->assertSame( $expected_link, $update_link, 'Update link is not equal to expected link for ' . $test_value .' with allow set to everyone.');

    }

}