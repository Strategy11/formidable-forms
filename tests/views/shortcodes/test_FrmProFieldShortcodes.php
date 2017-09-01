<?php
/**
 * @group pro
 * @group views
 * @group shortcodes
 * @group field-shortcodes
 */
class test_FrmProFieldShortcodes extends test_FrmFieldShortcodes {

	// TODO: add fields from embedded form and repeating section
	// TODO: post fields

	protected function get_field_value( $field_key ) {
		$field_id = FrmField::get_id_by_key( $field_key );
		$value = $this->test_entry->metas[ $field_id ];

		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		return $value;
	}

	protected function get_single_file_upload_value() {
		$file_field_id = FrmField::get_id_by_key( 'single-file-upload-field' );

		return $this->get_file_url( $this->test_entry->metas[ $file_field_id ], 'thumbnail' );
	}

	protected function get_multi_file_upload_value() {
		$file_field_id = FrmField::get_id_by_key( 'multi-file-upload-field' );
		$file_urls = array();

		foreach ( $this->test_entry->metas[ $file_field_id ] as $media_id ) {
			$file_urls[] = $this->get_file_url( $media_id, 'thumbnail' );
		}

		return implode( ', ', $file_urls );
	}

	protected function get_expected_field_values() {
		return array(
			'text-field'               => 'Jamie',
			'p3eiuk'                   => "<p>Jamie<br />\nRebecca<br />\nWahlin</p>\n",//paragraph
			'uc580i'                   => 'Red, Green',//checkbox
			'radio-button-field'       => 'cookies',
			'dropdown-field'           => 'Ace Ventura',
			'email-field'              => 'jamie@mail.com',
			'website-field'            => 'http://www.jamie.com',
			'd0c84r'                   => '',//recaptcha
			'cb-sep-values'            => 'Option 1, Option 2',
			'pro-fields-divider'       => '',
			'rich-text-field'          => "<p><strong>Bolded text</strong></p>\n",
			'single-file-upload-field' => $this->get_single_file_upload_value(),
			'multi-file-upload-field'  => $this->get_multi_file_upload_value(),
			'number-field'             => '11',
			'n0d580'                   => '1231231234',//phone number
			'time-field'               => '12:30 AM',
			'date-field'               => 'August 16, 2015',
			'zwuclz'                   => 'http://www.test.com',//image url
			'qbrd2o'                   => '5',//scale
			'ex97jv'                   => '',//end section
			'dynamic-country'          => 'United States',
			'dynamic-state'            => 'California, Utah',
			'dynamic-city'             => '',
			'qfn4lg'                   => '',//Dynamic list
			'embed-form-field'         => $this->get_field_value( 'embed-form-field' ),
			'contact-message'          => "<p>test</p>\n",
			'contact-date'             => 'May 21, 2015',
			'contact-name'             => 'Embedded name',
			'hidden-field'             => 'Hidden value',
			'user-id-field'            => 'admin',
			'9r61y8'                   => 'admin',//password
			'khyzws'                   => '',//html
			'tags-field'               => 'Jame',//tags
			'repeating-section'        => $this->get_field_value( 'repeating-section' ),
			'repeating-text'           => 'First, Second, Third',
			'repeating-checkbox'       => 'Option 1, Option 2, Option 1, Option 2, Option 2',
			'repeating-date'           => 'May 27, 2015, May 29, 2015, June 19, 2015',
			'lookup-country'           => 'United States',
			'address-field'            => '123 Main St. #5, Anytown, OR, 12345, United States',
			'credit-card-field'        => 'xxxxxxxxxxxx4242, 03, 2018',
		);
	}

	protected function get_file_url( $id, $size ) {
		$image = wp_get_attachment_image_src( $id, $size, false );

		$is_non_image = ! wp_attachment_is_image( $id );

		if ( $is_non_image ) {
			$url = wp_get_attachment_url( $id );
		} else {
			$url = $image['0'];
		}

		return $url;
	}

	protected function get_form_for_test() {
		return FrmForm::getOne( 'all_field_types' );
	}

	protected function get_entry_for_test() {
		return FrmEntry::getOne( 'jamie_entry_key', true );
	}
}