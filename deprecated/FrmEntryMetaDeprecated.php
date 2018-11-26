<?php

/**
 * @since 2.03.05
 * @deprecated 2.03.05
 * @codeCoverageIgnore
 */
class FrmEntryMetaDeprecated {

	/**
	 * @deprecated 2.03.05
	 */
	public static function add_entry_meta( $entry_id, $field_id, $meta_key = null, $meta_value ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::add_entry_meta' );

		return FrmEntryMeta::add_entry_meta( $entry_id, $field_id, $meta_key, $meta_value );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update_entry_meta( $entry_id, $field_id, $meta_key = null, $meta_value ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::update_entry_meta' );

		return FrmEntryMeta::update_entry_meta( $entry_id, $field_id, $meta_key, $meta_value );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function update_entry_metas( $entry_id, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::update_entry_metas' );

		return FrmEntryMeta::update_entry_metas( $entry_id, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function duplicate_entry_metas( $old_id, $new_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::duplicate_entry_metas' );
		FrmEntryMeta::duplicate_entry_metas( $old_id, $new_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function delete_entry_meta( $entry_id, $field_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::delete_entry_meta' );

		return FrmEntryMeta::delete_entry_meta( $entry_id, $field_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function clear_cache() {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::clear_cache' );
		FrmEntryMeta::clear_cache();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_meta_value( $entry, $field_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::get_meta_value' );

		return FrmEntryMeta::get_meta_value( $entry, $field_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_entry_meta_by_field( $entry_id, $field_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::get_entry_meta_by_field' );

		return FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_entry_metas_for_field( $field_id, $order = '', $limit = '', $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::get_entry_metas_for_field' );

		return FrmEntryMeta::get_entry_metas_for_field( $field_id, $order, $limit, $args );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_entry_meta_info( $entry_id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::get_entry_meta_info' );

		return FrmEntryMeta::get_entry_meta_info( $entry_id );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getAll( $where = array(), $order_by = '', $limit = '', $stripslashes = false ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::getAll' );

		return FrmEntryMeta::getAll( $where, $order_by, $limit, $stripslashes );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function getEntryIds( $where = array(), $order_by = '', $limit = '', $unique = true, $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::getEntryIds' );

		return FrmEntryMeta::getEntryIds( $where, $order_by, $limit, $unique, $args );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function search_entry_metas( $search, $field_id = '', $operator ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmEntryMeta::search_entry_metas' );

		return FrmEntryMeta::search_entry_metas( $search, $field_id, $operator );
	}
}
