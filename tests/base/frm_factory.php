<?php

class Form_Factory extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = FrmFormsHelper::setup_new_vars( false );

		// This is a workaround for WP_UnitTest_Factory_For_Thing::generate_args
		// Non-scalar values are not currently allowed in default definitions
		$this->default_generation_definitions['rootline_titles'] = '';
	}

	function create_object( $args ) {
		return FrmForm::create( $args );
	}

	function update_object( $form_id, $fields ) {
		return FrmForm::update( $form_id, $fields );
	}

	function get_id_by_key( $form_key ) {
		return FrmForm::getIdByKey( $form_key );
	}

	function get_object_by_id( $form_id ) {
		return FrmForm::getOne( $form_id );
	}
}

class Field_Factory extends WP_UnitTest_Factory_For_Thing {
	function __construct( $factory = null ) {
		parent::__construct( $factory );

		global $wpdb;
		$this->default_generation_definitions = array(
			'field_key'   => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_fields', 'field_key' ),
			'name'        => new WP_UnitTest_Generator_Sequence( 'Field name %s' ),
			'description' => new WP_UnitTest_Generator_Sequence( 'Field description %s' ),
			'type'        => 'text',
		);

	}

	function create_object( $args ) {
		$field_values = FrmFieldsHelper::setup_new_vars( $args['type'], $args['form_id'] );
        return FrmField::create( $field_values );
	}

	function update_object( $field_id, $values ) {
		return FrmField::update( $field_id, $values );
	}

	function get_object_by_id( $field_id ) {
		return FrmField::getOne( $field_id );
	}

	function generate_entry_array( $form ) {
		$form_id = is_object( $form ) ? $form->id : $form;
		$entry_data = array(
			'form_id'   => $form_id,
			'item_meta' => array(),
		);

		$form_fields = $this->get_fields_from_form( $form_id );
		foreach ( $form_fields as $field ) {
			$entry_data['item_meta'][ $field->id ] = $this->set_field_value( $field );
		}
		return $entry_data;
	}

	/**
	 * Get all fields in a form
	 */
	function get_fields_from_form( $form_id ) {
		return FrmField::get_all_for_form( $form_id );
	}

	/**
	 * When creating an entry, set the correct data formats
	 */
	function set_field_value( $field ) {
		$value = rand_str();
		$field_values = array(
			'email'  => 'admin@example.org',
			'url'    => 'http://test.com',
			'number' => 120,
			'scale'  => 8,
			'date'   => '2015-01-01',
			'time'   => '13:30:00',
			'user_id' => get_current_user_id(),
		);

		if ( isset( $field_values[ $field->type ] ) ) {
			$value = $field_values[ $field->type ];
		}

		return $value;
	}

	function get_id_by_key( $field_key ) {
		return FrmField::get_id_by_key( $field_key );
	}
}

class Entry_Factory extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		global $wpdb;
		$this->default_generation_definitions = array(
			'item_key' => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key' ),
			'name'     => new WP_UnitTest_Generator_Sequence( 'Entry name %s' ),
		);

	}

	function create_object( $args ) {
        $default_values = array(
            'form_id'   => $args['form_id'],
            'item_meta' => array(),
        );
		$args = array_merge( $default_values, $args );
		return FrmEntry::create( $args );
	}

	function update_object( $entry_id, $fields ) {
		return FrmEntry::update( $entry_id, $fields );
	}

	function get_object_by_id( $entry_id ) {
		return FrmEntry::getOne( $entry_id );
	}

	function get_id_by_key( $entry_key ) {
		return FrmEntry::get_id_by_key( $entry_key );
	}
}
