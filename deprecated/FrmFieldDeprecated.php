<?php

/**
 * @since 2.03.05
 */
class FrmFieldDeprecated {

	/**
	 * @deprecated 2.03.05
	 */
	public static function field_selection() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::field_selection' );

		return FrmField::field_selection();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function pro_field_selection() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::pro_field_selection' );

		return FrmField::pro_field_selection();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function create( $values, $return = true ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::create' );

		return FrmField::create( $values, $return );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function duplicate( $old_form_id, $form_id, $copy_keys = false, $blog_id = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::duplicate' );
		FrmField::duplicate( $old_form_id, $form_id, $copy_keys, $blog_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update( $id, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::update' );

		return FrmField::update( $id, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function destroy( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::destroy' );

		return FrmField::destroy( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function delete_form_transient( $form_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::delete_form_transient' );

		return FrmField::delete_form_transient( $form_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function maybe_get_field( &$field ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::maybe_get_field' );
		FrmField::maybe_get_field( $field );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getOne( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::getOne' );

		return FrmField::getOne( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_type( $id, $col = 'type' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_type' );

		return FrmField::get_type( $id, $col );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_all_types_in_form( $form_id, $type, $limit = '', $inc_sub = 'exclude' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_all_types_in_form' );

		return FrmField::get_all_types_in_form( $form_id, $type, $limit, $inc_sub );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_all_for_form( $form_id, $limit = '', $inc_embed = 'exclude', $inc_repeat = 'include' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_all_for_form' );

		return FrmField::get_all_for_form( $form_id, $limit, $inc_embed, $inc_repeat );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function include_sub_fields( &$results, $inc_embed, $type = 'all' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::include_sub_fields' );
		FrmField::include_sub_fields( $results, $inc_embed, $type );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getAll( $where = array(), $order_by = '', $limit = '', $blog_id = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::getAll' );

		return FrmField::getAll( $where, $order_by, $limit, $blog_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_no_save_field( $type ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_no_save_field' );

		return FrmField::is_no_save_field( $type );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function no_save_fields() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::no_save_fields' );

		return FrmField::no_save_fields();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_field_with_multiple_values( $field ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_field_with_multiple_values' );

		return FrmField::is_field_with_multiple_values( $field );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_multiple_select( $field ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_multiple_select' );

		return FrmField::is_multiple_select( $field );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_read_only( $field ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_read_only' );

		return FrmField::is_read_only( $field );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_required( $field ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_required' );

		return FrmField::is_required( $field );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_true( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_true' );

		return FrmField::is_option_true( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_empty( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_empty' );

		return FrmField::is_option_empty( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_true_in_array( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_true_in_array' );

		return FrmField::is_option_true_in_array( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_true_in_object( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_true_in_object' );

		return FrmField::is_option_true_in_object( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_empty_in_array( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_empty_in_array' );

		return FrmField::is_option_empty_in_array( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_empty_in_object( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_empty_in_object' );

		return FrmField::is_option_empty_in_object( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_option_value_in_object( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_option_value_in_object' );

		return FrmField::is_option_value_in_object( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_option( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_option' );

		return FrmField::get_option( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_option_in_array( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_option_in_array' );

		return FrmField::get_option_in_array( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_option_in_object( $field, $option ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_option_in_object' );

		return FrmField::get_option_in_object( $field, $option );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_repeating_field( $field ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::is_repeating_field' );

		return FrmField::is_repeating_field( $field );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_id_by_key( $key ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_id_by_key' );

		return FrmField::get_id_by_key( $key );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_key_by_id( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmField::get_key_by_id' );

		return FrmField::get_key_by_id( $id );
	}
}
