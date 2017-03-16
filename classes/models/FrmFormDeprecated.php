<?php

/**
 * @since 2.03.05
 */
class FrmFormDeprecated {

	/**
	 * @deprecated 2.03.05
	 */
	public static function create( $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::create' );

		return FrmForm::create( $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function duplicate( $id, $template = false, $copy_keys = false, $blog_id = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::duplicate' );

		return FrmForm::duplicate( $id, $template, $copy_keys, $blog_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function after_duplicate( $form_id, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::after_duplicate' );

		return FrmForm::after_duplicate( $form_id, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update( $id, $values, $create_link = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::update' );

		return FrmForm::update( $id, $values, $create_link );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function set_update_options( $new_values, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::set_update_options' );

		return FrmForm::set_update_options( $new_values, $values );
	}


	/**
	 * @deprecated 2.03.05
	 */
	public static function update_fields( $id, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::update_fields' );

		return FrmForm::update_fields( $id, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function set_status( $id, $status ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::set_status' );

		return FrmForm::set_status( $id, $status );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function trash( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::trash' );

		return FrmForm::trash( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function destroy( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::destroy' );

		return FrmForm::destroy( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function scheduled_delete( $delete_timestamp = '' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::scheduled_delete' );

		return FrmForm::scheduled_delete( $delete_timestamp );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getName( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::getName' );

		return FrmForm::getName( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getIdByKey( $key ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::getIdByKey' );

		return FrmForm::getIdByKey( $key );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getKeyById( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::getKeyById' );

		return FrmForm::getKeyById( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function maybe_get_form( &$form ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::maybe_get_form' );
		FrmForm::maybe_get_form( $form );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getOne( $id, $blog_id = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::getOne' );

		return FrmForm::getOne( $id, $blog_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getAll( $where = array(), $order_by = '', $limit = '' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::getAll' );

		return FrmForm::getAll( $where, $order_by, $limit );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_published_forms( $query = array(), $limit = 999, $inc_children = 'exclude' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_published_forms' );

		return FrmForm::get_published_forms( $query, $limit, $inc_children );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_count() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_count' );

		return FrmForm::get_count();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function clear_form_cache() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::clear_form_cache' );
		FrmForm::clear_form_cache();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function validate( $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::validate' );

		return FrmForm::validate( $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_params( $form = null ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_params' );

		return FrmForm::get_params( $form );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function list_page_params() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::list_page_params' );

		return FrmForm::list_page_params();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_admin_params( $form = null ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_admin_params' );

		return FrmForm::get_admin_params( $form );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_current_form_id( $default_form = 'none' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_current_form_id' );

		return FrmForm::get_current_form_id( $default_form );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function maybe_get_current_form( $form_id = 0 ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::maybe_get_current_form' );

		return FrmForm::maybe_get_current_form( $form_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_current_form( $form_id = 0 ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_current_form' );

		return FrmForm::get_current_form( $form_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function set_current_form( $form_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::set_current_form' );

		return FrmForm::set_current_form( $form_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_form_loaded( $form, $this_load, $global_load ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::is_form_loaded' );

		return FrmForm::is_form_loaded( $form, $this_load, $global_load );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function show_submit( $form ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::show_submit' );

		return FrmForm::show_submit( $form );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_option( $atts ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_option' );

		return FrmForm::get_option( $atts );
	}

}