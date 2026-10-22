<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Manages the entries CSV column picker and each user's saved preferences.
 *
 * @since x.x
 */
class FrmCSVExportController {

	/**
	 * Loads styles only on the entries page.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		if ( 'formidable-entries' !== FrmAppHelper::get_param( 'page' ) || ! FrmAppHelper::pro_is_installed() ) {
			return;
		}
		wp_enqueue_style( 'frm-csv-export', FrmAppHelper::plugin_url() . '/css/admin/csv-export.css', array(), FrmAppHelper::plugin_version() );
	}

	/**
	 * Returns the available columns and the current user's selection.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function options() {
		$form    = self::get_form();
		$columns = FrmCSVExportHelper::get_column_options( $form );
		$options = array();

		foreach ( $columns as $value => $label ) {
			$options[] = array(
				'value' => (string) $value,
				'label' => $label,
			);
		}

		$saved = get_user_meta( get_current_user_id(), 'frm_csv_columns_' . $form->id, true );
		wp_send_json_success(
			array(
				'columns'  => $options,
				'selected' => is_array( $saved ) ? self::validate_columns( $saved, $columns ) : array_map( 'strval', array_keys( $columns ) ),
				'saved'    => is_array( $saved ),
			)
		);
	}

	/**
	 * Saves a selection for the current user and form.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function save_preferences() {
		$form      = self::get_form();
		$available = FrmCSVExportHelper::get_column_options( $form );
		$columns   = self::validate_columns( FrmAppHelper::get_post_param( 'columns', array() ), $available );

		if ( ! $columns ) {
			wp_send_json_error( __( 'Select at least one column to export.', 'formidable' ), 400 );
		}

		update_user_meta( get_current_user_id(), 'frm_csv_columns_' . $form->id, $columns );
		wp_send_json_success();
	}

	/**
	 * Checks the request before accessing column preferences.
	 *
	 * @since x.x
	 *
	 * @return object
	 */
	private static function get_form() {
		FrmAppHelper::permission_check( 'frm_view_entries' );
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$form = FrmForm::getOne( FrmAppHelper::get_post_param( 'form', 0, 'absint' ) );

		if ( ! $form ) {
			wp_send_json_error( __( 'Form not found.', 'formidable' ), 404 );
		}

		return $form;
	}

	/**
	 * Keeps valid, unique columns in the user's chosen order.
	 *
	 * @since x.x
	 *
	 * @param mixed $columns Requested column keys.
	 * @param array $available Available keys and labels.
	 *
	 * @return array
	 */
	private static function validate_columns( $columns, $available ) {
		if ( ! is_array( $columns ) ) {
			return array();
		}

		$valid = array();

		foreach ( $columns as $column ) {
			if ( ! is_scalar( $column ) || ! array_key_exists( (string) $column, $available ) ) {
				continue;
			}

			$valid[ (string) $column ] = (string) $column;
		}

		return array_values( $valid );
	}
}
