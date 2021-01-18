<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.03.05
 * @deprecated 2.03.05
 * @codeCoverageIgnore
 */
class FrmEntryDeprecated {

	/**
	 * @deprecated 2.03.05
	 */
	public static function create( $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::create' );

		return FrmEntry::create( $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function is_duplicate( $new_values, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::is_duplicate' );

		return FrmEntry::is_duplicate( $new_values, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function duplicate( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::duplicate' );

		return FrmEntry::duplicate( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update( $id, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::update' );

		return FrmEntry::update( $id, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function destroy( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::destroy' );

		return FrmEntry::destroy( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update_form( $id, $value, $form_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::update_form' );

		return FrmEntry::update_form( $id, $value, $form_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function clear_cache() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::clear_cache' );

		FrmEntry::clear_cache();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_new_entry_name( $values, $default = '' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::get_new_entry_name' );

		return FrmEntry::get_new_entry_name( $values, $default );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function maybe_get_entry( &$entry ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::maybe_get_entry' );

		FrmEntry::maybe_get_entry( $entry );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getOne( $id, $meta = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::getOne' );

		return FrmEntry::getOne( $id, $meta );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_meta( $entry ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::get_meta' );

		return FrmEntry::get_meta( $entry );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function exists( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::exists' );

		return FrmEntry::exists( $id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getAll( $where, $order_by = '', $limit = '', $meta = false, $inc_form = true ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::getAll' );

		return FrmEntry::getAll( $where, $order_by, $limit, $meta, $inc_form );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getRecordCount( $where = '' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::getRecordCount' );

		return FrmEntry::getRecordCount( $where );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getPageCount( $p_size, $where = '' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::getPageCount' );

		return FrmEntry::getPageCount( $p_size, $where );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function sanitize_entry_post( &$values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::sanitize_entry_post' );

		FrmEntry::sanitize_entry_post( $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function create_entry_from_xml( $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::create_entry_from_xml' );

		return FrmEntry::create_entry_from_xml( $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update_entry_from_xml( $id, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::update_entry_from_xml ' );

		return FrmEntry::update_entry_from_xml( $id, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_id_by_key( $key ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntry::get_id_by_key' );

		return FrmEntry::get_id_by_key( $key );
	}
}
