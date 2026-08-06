<?php

/**
 * @group fields
 */
class test_FrmField extends FrmUnitTest {

	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	public function test_create() {
		$form_id     = $this->factory->form->get_id_by_key( 'contact-db12' );
		$field_types = array_merge( FrmField::field_selection(), FrmField::pro_field_selection() );

		foreach ( $field_types as $field_type => $field_info ) {
			$field_id = $this->factory->field->create(
				array(
					'type'    => $field_type,
					'form_id' => $form_id,
				)
			);
			$this->assertIsNumeric( $field_id );
			$this->assertGreaterThan( 0, $field_id );
		}
	}

	/**
	 * @covers FrmField::getAll
	 */
	public function test_getAll() {
		$forms = array(
			$this->contact_form_key    => $this->contact_form_field_count,
			$this->all_fields_form_key => $this->all_field_types_count - $this->contact_form_field_count - 3,
		);

		foreach ( $forms as $form_key => $expected_count ) {
			$form_id = $this->factory->form->get_id_by_key( $form_key );
			$fields  = FrmField::getAll( array( 'fi.form_id' => (int) $form_id ) );
			$this->assertNotEmpty( $fields );
			$this->assertCount( $expected_count, $fields, 'An incorrect number of fields are retrieved with FrmField::getAll.' );
		}
	}

	/**
	 * @covers FrmField::get_all_for_form
	 */
	public function test_get_all_for_form() {
		$forms = array(
			'basic_test'         => array(
				'form_key' => $this->contact_form_key,
				'count'    => $this->contact_form_field_count,
			),
			'no_repeat_or_embed' => array(
				'form_key' => $this->all_fields_form_key,
				'count'    => $this->all_field_types_count - $this->contact_form_field_count - 3,
			),
		);

		foreach ( $forms as $test => $args ) {
			$form_id = FrmForm::get_id_by_key( $args['form_key'] );

			if ( $test === 'no_repeat_or_embed' ) {
				$fields = FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' );
			} else {
				$fields = FrmField::get_all_for_form( $form_id );
			}

			$this->assertNotEmpty( $fields );
			$this->assertCount( $args['count'], $fields, 'An incorrect number of fields are retrieved with FrmField::get_all_for_form for ' . $test . '.' );
		}
	}

	/**
	 * @covers FrmField::destroy
	 */
	public function test_destroy() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
			)
		);

		$entry_data = $this->factory->field->generate_entry_array( $form );

		$entry_data['item_meta'][ $field_id ] = 'Meta for deleted field';

		$entry_id = $this->factory->entry->create( $entry_data );

		$this->assertSame( 'Meta for deleted field', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ) );

		FrmField::destroy( $field_id );

		$this->assertNull( FrmField::getOne( $field_id ) );

		global $wpdb;
		$meta_value = $wpdb->get_var(
			$wpdb->prepare( 'SELECT meta_value FROM %i WHERE item_id = %d AND field_id = %d', $wpdb->prefix . 'frm_item_metas', $entry_id, $field_id )
		);
		$this->assertNull( $meta_value );
	}
}
