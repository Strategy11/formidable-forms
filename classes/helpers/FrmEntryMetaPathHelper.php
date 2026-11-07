<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Access field values inside embedded forms and repeater rows.
 *
 * @since x.x
 */
class FrmEntryMetaPathHelper {

	/**
	 * Read the row containing fields at a nested submission path.
	 *
	 * @since x.x
	 *
	 * @param array $path Keys locating the row inside item_meta.
	 *
	 * @return array
	 */
	public static function get_posted_row( $path ) {
		$row = FrmAppHelper::get_simple_request(
			array(
				'type'     => 'post',
				'param'    => 'item_meta',
				'path'     => $path,
				'default'  => array(),
				'sanitize' => '',
			)
		);
		return is_array( $row ) ? $row : array();
	}

	/**
	 * Write a validated value without copying unrelated submitted rows.
	 *
	 * @since x.x
	 *
	 * @param array|null $posted   The submission, passed by reference.
	 * @param int|string $field_id The field being validated.
	 * @param mixed      $value    The validated field value.
	 * @param array      $args     The parent field, row pointer and nested path.
	 *
	 * @return void
	 */
	public static function set_posted_value( &$posted, $field_id, $value, $args ) {
		$path = ! empty( $args['item_meta_path'] ) ? $args['item_meta_path'] : array( $args['parent_field_id'], $args['key_pointer'] );
		$path = array_merge( array( 'item_meta' ), $path, array( $field_id ) );
		$row  = &$posted;

		foreach ( $path as $key ) {
			if ( ! is_array( $row ) ) {
				$row = array();
			}

			if ( ! isset( $row[ $key ] ) ) {
				$row[ $key ] = array();
			}

			$row = &$row[ $key ];
		}

		$row = $value;
	}
}
