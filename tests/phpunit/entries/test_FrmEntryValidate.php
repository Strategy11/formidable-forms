<?php

/**
 * @group entries
 * @group free
 */
class test_FrmEntryValidate extends FrmUnitTest {

	/**
	 * @covers FrmEntryValidate::validate
	 */
	public function test_validate() {
		$add_a_custom_error = function ( $errors ) {
			$errors['custom_error'] = 'Error message';
			return $errors;
		};

		add_filter( 'frm_validate_entry', $add_a_custom_error );

		$values = array(
			'form_id'   => 1,
			'item_meta' => array(),
		);
		$errors = FrmEntryValidate::validate( $values );
		$this->assertIsArray( $errors );

		$this->assertArrayHasKey( 'custom_error', $errors );
		$this->assertEquals( 'Error message', $errors['custom_error'] );

		remove_filter( 'frm_validate_entry', $add_a_custom_error );
	}

	/**
	 * @covers FrmEntryValidate::get_spam_check_user_info
	 */
	public function test_get_spam_check_user_info() {
		$made_up_name_field_id  = 4;
		$made_up_email_field_id = 12;
		$made_up_url_field_id   = 16;
		$test_name              = array(
			'first' => 'Some',
			'last'  => 'Guy',
		);
		$test_email             = 'amadeupemail@email.com';
		$test_url               = 'http://madeupwebsite.com';
		$values                 = array(
			'item_meta'      => array(
				0                       => '',
				$made_up_name_field_id  => $test_name,
				$made_up_email_field_id => $test_email,
				$made_up_url_field_id   => $test_url,
			),
			'name_field_ids' => array(),
		);

		wp_set_current_user( null );
		$this->run_private_method( array( 'FrmEntryValidate', 'prepare_values_for_spam_check' ), array( &$values ) );
		$check = $this->get_spam_check_user_info( $values );
		$this->assertTrue( empty( $check['user_ID'] ) );
		$this->assertTrue( empty( $check['user_id'] ) );
		$this->assertEquals( 'Some Guy', $check['comment_author'] );
		$this->assertEquals( $test_email, $check['comment_author_email'] );
		$this->assertEquals( $test_url, $check['comment_author_url'] );

		// Test "Name" + "Last" field name pattern to build the comment_author
		$form_id       = $this->factory->form->create();
		$first_name_id = $this->factory->field->create(
			array(
				'type'    => 'text',
				'form_id' => $form_id,
				'name'    => 'Name',
			)
		);
		$last_name_id  = $this->factory->field->create(
			array(
				'type'    => 'text',
				'form_id' => $form_id,
				'name'    => 'Last',
			)
		);

		$values           = array(
			'item_meta'      => array(
				0                       => '',
				$first_name_id          => 'John',
				$last_name_id           => 'Doe',
				$made_up_email_field_id => $test_email,
				$made_up_url_field_id   => $test_url,
			),
			'name_field_ids' => array(),
		);
		$_POST['form_id'] = $form_id;
		$this->run_private_method( array( 'FrmEntryValidate', 'prepare_values_for_spam_check' ), array( &$values ) );
		$check = $this->get_spam_check_user_info( $values );
		$this->assertEquals( 'John Doe', $check['comment_author'] );

		// Test with repeater/embedded field.
		$values['item_meta'][ $made_up_name_field_id ]  = array(
			'John Doe',
			'Some Guy',
		);
		$values['item_meta'][ $made_up_email_field_id ] = array(
			'johndoe@gmail.com',
			'someguy@gmail.com',
		);
		$values['item_meta'][ $made_up_url_field_id ]   = array(
			'https://johndoe.com',
			'https://someguy.com',
		);

		$check = $this->get_spam_check_user_info( $values );
		$this->assertEquals( 'John Doe', $check['comment_author'] );
		$this->assertEquals( 'johndoe@gmail.com', $check['comment_author_email'] );
		$this->assertEquals( 'https://johndoe.com', $check['comment_author_url'] );

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

	public function test_get_all_form_ids_and_flatten_meta() {
		$test_values = array(
			'frm_action'         => 'create',
			'form_id'            => 1,
			'frm_hide_fields_1'  => '',
			'form_key'           => 'contact-form',
			'item_meta'          => array(
				0       => null,
				1       => array(
					'first' => 'John',
					'last'  => 'Doe',
				),
				2       => 'Doe',
				3       => 'johndoe@gmail.com',
				4       => 'Test',
				5       => 'This is a test',
				141     => 'Developer',
				'other' => array( 141 => null ),
				155     => null,
				156     => null,
				163     => array(
					'form'    => 17,
					'row_ids' => array(
						0 => 0,
						1 => 1,
					),
					0         => array(
						0   => null,
						162 => 'Option 2',
					),
					1         => array(
						0   => null,
						162 => 'Option 1',
					),
				),
				165     => array(
					'form'    => 11,
					'row_ids' => array( 0 => 0 ),
					0         => array(
						0   => null,
						118 => array(
							'first' => 'John',
							'last'  => 'Doe',
						),
					),
				),
			),
			'frm_submit_entry_1' => '6e70504545',
			'_wp_http_referer'   => '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=contact-form',
			'item_key'           => '8wl00',
			'frm_verify'         => null,
			'frm_state'          => 'gfMW/S4I1MCpqXn7OnjXQHLIibJNuRkLkCYpp7MWM7Y=',
		);

		$form_ids = $this->run_private_method(
			array( 'FrmEntryValidate', 'get_all_form_ids_and_flatten_meta' ),
			array( &$test_values )
		);

		$this->assertEquals( $form_ids, array( 1, 17, 11 ) );
		$this->assertFalse( isset( $test_values['item_meta'][163] ) );
		$this->assertFalse( isset( $test_values['item_meta'][165] ) );
		$this->assertEquals( $test_values['item_meta'][162], array( 'Option 2', 'Option 1' ) );
		$this->assertEquals( $test_values['item_meta'][118], array( 'John Doe' ) );
		$this->assertEquals( $test_values['item_meta'][1], 'John Doe' );
	}

	public function test_skip_adding_values_to_akismet() {
		$form   = $this->factory->form->create_and_get();
		$fields = array();

		// These types are skipped.
		foreach ( array( 'radio', 'checkbox', 'select', 'scale', 'star', 'range', 'toggle' ) as $field_type ) {
			$fields[ $field_type ] = $this->factory->field->create_and_get(
				array(
					'form_id' => $form->id,
					'type'    => $field_type,
				)
			);
		}

		// Radio field, has Other, but no options.
		$fields['radio_2'] = $this->factory->field->create_and_get(
			array(
				'form_id'       => $form->id,
				'type'          => 'radio',
				'field_options' => array( 'other' => '1' ),
			)
		);

		/*
		 * Radio_3: has Other and options, value is one of option. Skip this from values.
		 * Radio_4: has Other and options, value is Other and same as one of options. Skip this from values.
		 * Radio_5: has Other and options, value is Other and different from one of option. Do not skip this.
		 */
		$options = array(
			array(
				'label' => 'Option 1',
				'value' => 'option-1',
			),
			array(
				'label' => 'Option 2',
				'value' => 'option-2',
			),
			array(
				'label' => 'Option 3',
				'value' => 'option-3',
			),
			'other_3' => 'Other',
		);

		foreach ( array( 'radio_3', 'radio_4', 'radio_5' ) as $key ) {
			$fields[ $key ] = $this->factory->field->create_and_get(
				array(
					'form_id'       => $form->id,
					'type'          => 'radio',
					'field_options' => array( 'other' => '1' ),
					'options'       => $options,
				)
			);
		}

		$values = $this->factory->field->generate_entry_array( $form );

		$values['item_meta'][ $fields['radio_3']->id ] = 'option-2';
		$values['item_meta'][ $fields['radio_4']->id ] = 'Other';
		$values['item_meta'][ $fields['radio_5']->id ] = 'Other';
		$values['item_meta']['other']                  = array(
			$fields['radio_4']->id => 'option-3',
			$fields['radio_5']->id => 'another-value',
		);

		$values['form_ids'] = $this->run_private_method(
			array( 'FrmEntryValidate', 'get_all_form_ids_and_flatten_meta' ),
			array( &$values )
		);

		$this->run_private_method(
			array( 'FrmEntryValidate', 'skip_adding_values_to_akismet' ),
			array( &$values )
		);

		// Checkbox field shouldn't be skipped.
		foreach ( array( 'radio', 'radio_2', 'radio_3', 'radio_4', 'checkbox', 'select', 'scale', 'star', 'range', 'toggle' ) as $key ) {
			$this->assertFalse( isset( $values['item_meta'][ $fields[ $key ]->id ] ) );
		}

		$this->assertTrue( isset( $values['item_meta'][ $fields['radio_5']->id ] ) );
	}
}
