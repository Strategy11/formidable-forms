<?php

class FrmUnitTestFactory extends WP_UnitTest_Factory {

	/**
	 * @var Form_Factory|null
	 */
	public $form;

	/**
	 * @var Field_Factory|null
	 */
	public $field;

	/**
	 * @var Entry_Factory|null
	 */
	public $entry;
}

class Form_Factory extends WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$defaults = FrmFormsHelper::setup_new_vars( false );
		if ( isset( $defaults['submit_conditions'] ) ) {
			// the array default is causing errors with unit test code
			unset( $defaults['submit_conditions'] );
		}
		$this->default_generation_definitions = $defaults;

		// This is a workaround for WP_UnitTest_Factory_For_Thing::generate_args
		// Non-scalar values are not currently allowed in default definitions
		$this->default_generation_definitions['rootline_titles']    = '';
		$this->default_generation_definitions['protect_files_role'] = '';
	}

	public function create_object( $args ) {
		$form = FrmForm::create( $args );

		$field_values = FrmFieldsHelper::setup_new_vars( 'text', $form );
		if ( isset( $args['field_options'] ) ) {
			$field_values = array_merge( $field_values, $args['field_options'] );
		}
		FrmField::create( $field_values );

		return $form;
	}

	public function update_object( $form_id, $fields ) {
		return FrmForm::update( $form_id, $fields );
	}

	public function get_id_by_key( $form_key ) {
		return FrmForm::get_id_by_key( $form_key );
	}

	public function get_object_by_id( $form_id ) {
		return FrmForm::getOne( $form_id );
	}
}

class Field_Factory extends WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		global $wpdb;
		$this->default_generation_definitions = array(
			'field_key'   => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_fields', 'field_key' ),
			'name'        => new WP_UnitTest_Generator_Sequence( 'Field name %s' ),
			'description' => new WP_UnitTest_Generator_Sequence( 'Field description %s' ),
			'type'        => 'text',
		);
	}

	public function create_object( $args ) {
		$field_values = FrmFieldsHelper::setup_new_vars( $args['type'], $args['form_id'] );
		unset( $args['type'], $args['form_id'] );

		if ( isset( $args['field_options'] ) ) {
			$field_values['field_options'] = array_merge( $field_values['field_options'], $args['field_options'] );
			unset( $args['field_options'] );
		}

		if ( ! isset( $args['options'] ) ) {
			$field_values['options'] = array();
		}

		$field_values = array_merge( $field_values, $args );
		return FrmField::create( $field_values );
	}

	public function update_object( $field_id, $values ) {
		return FrmField::update( $field_id, $values );
	}

	public function get_object_by_id( $field_id ) {
		return FrmField::getOne( $field_id );
	}

	public function generate_entry_array( $form ) {
		$form_id    = is_object( $form ) ? $form->id : $form;
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
	public function get_fields_from_form( $form_id ) {
		return FrmField::get_all_for_form( $form_id );
	}

	/**
	 * When creating an entry, set the correct data formats
	 */
	public function set_field_value( $field ) {
		$value        = rand_str();
		$field_values = array(
			'email'    => 'admin@example.org',
			'url'      => 'http://sometest.com',
			'number'   => 120,
			'scale'    => 8,
			'date'     => '2015-01-01',
			'time'     => '13:30:00',
			'user_id'  => get_current_user_id(),
			'phone'    => '222-222-2222',
			'html'     => '',
			'quantity' => 2,
		);

		if ( isset( $field_values[ $field->type ] ) ) {
			$value = $field_values[ $field->type ];
		}

		return $value;
	}

	public function get_id_by_key( $field_key ) {
		return FrmField::get_id_by_key( $field_key );
	}
}

class Entry_Factory extends WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		global $wpdb;
		$this->default_generation_definitions = array(
			'item_key' => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key' ),
			'name'     => new WP_UnitTest_Generator_Sequence( 'Entry name %s' ),
		);
	}

	public function create_object( $args ) {
		$default_values = array(
			'form_id'   => $args['form_id'],
			'item_meta' => array(),
		);
		$args           = array_merge( $default_values, $args );
		return FrmEntry::create( $args );
	}

	public function update_object( $entry_id, $fields ) {
		return FrmEntry::update( $entry_id, $fields );
	}

	public function get_object_by_id( $entry_id ) {
		return FrmEntry::getOne( $entry_id );
	}

	public function get_id_by_key( $entry_key ) {
		return FrmEntry::get_id_by_key( $entry_key );
	}
}
