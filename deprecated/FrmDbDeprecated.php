<?php

/**
 * Class FrmDbDeprecated
 *
 * @since 2.03.05
 * @deprecated 2.03.05
 * @codeCoverageIgnore
 */
class FrmDbDeprecated {

	var $fields;
	var $forms;
	var $entries;
	var $entry_metas;

	public function __construct() {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}

		global $wpdb;
		$this->fields      = $wpdb->prefix . 'frm_fields';
		$this->forms       = $wpdb->prefix . 'frm_forms';
		$this->entries     = $wpdb->prefix . 'frm_items';
		$this->entry_metas = $wpdb->prefix . 'frm_item_metas';
	}

	/**
	 * @deprecated 2.03.05
	 */
	public function upgrade() {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmMigrate::upgrade' );

		$db = new FrmMigrate();
		$db->upgrade();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public function collation() {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmMigrate::collation' );

		$db = new FrmMigrate();
		return $db->collation();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public function uninstall() {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmMigrate::uninstall' );

		$db = new FrmMigrate();
		$db->uninstall();
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_where_clause_and_values( &$args, $starts_with = ' WHERE ' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_where_clause_and_values( $args, $starts_with )' );
		FrmDb::get_where_clause_and_values( $args, $starts_with );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function parse_where_from_array( $args, $base_where, &$where, &$values ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::parse_where_from_array( $args, $base_where, $where, $values )' );
		FrmDb::parse_where_from_array( $args, $base_where, $where, $values );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_count( $table, $where = array(), $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_count' );

		return FrmDb::get_count( $table, $where, $args );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_var( $table, $where = array(), $field = 'id', $args = array(), $limit = '', $type = 'var' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_var' );

		return FrmDb::get_var( $table, $where, $field, $args, $limit, $type );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_col( $table, $where = array(), $field = 'id', $args = array(), $limit = '' ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_col' );

		return FrmDb::get_col( $table, $where, $field, $args, $limit );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_row( $table, $where = array(), $fields = '*', $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_row' );

		return FrmDb::get_row( $table, $where, $fields, $args );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_results( $table, $where = array(), $fields = '*', $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_results' );

		return FrmDb::get_results( $table, $where, $fields, $args );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function append_where_is( $where_is ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::append_where_is' );

		return FrmDb::append_where_is( $where_is );
	}

	/**
	 * @deprecated 2.03.05
	 */
	public static function get_associative_array_results( $columns, $table, $where ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmDb::get_associative_array_results' );

		return FrmDb::get_associative_array_results( $columns, $table, $where );
	}
}
