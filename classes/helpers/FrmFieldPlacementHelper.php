<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Shared placement rules for the builder and programmatic field writes.
 *
 * @since x.x
 */
class FrmFieldPlacementHelper {

	/**
	 * Check placement using stored values for columns omitted by an update.
	 *
	 * @since x.x
	 *
	 * @param int   $id     Field ID being updated.
	 * @param array $values New field columns and options.
	 *
	 * @return bool
	 */
	public static function is_allowed_update( $id, $values ) {
		if ( ! isset( $values['type'] ) && ! isset( $values['form_id'] ) && ! isset( $values['field_options'] ) ) {
			return true;
		}

		$field = FrmField::getOne( $id );

		if ( ! $field ) {
			return true;
		}

		$type       = $values['type'] ?? $field->type;
		$form_id    = (int) ( $values['form_id'] ?? $field->form_id );
		$options    = $values['field_options'] ?? $field->field_options;
		$section_id = (int) ( $options['in_section'] ?? 0 );
		return self::is_allowed( $type, $form_id, $section_id );
	}

	/**
	 * Check the destination before writing a field or running creation hooks.
	 *
	 * @since x.x
	 *
	 * @param string $type       Field type, including palette variants.
	 * @param int    $form_id    Destination form ID.
	 * @param int    $section_id Destination container field ID.
	 *
	 * @return bool
	 */
	public static function is_allowed( $type, $form_id, $section_id = 0 ) {
		$type           = explode( '|', $type )[0];
		$section        = $section_id ? FrmField::getOne( $section_id ) : false;
		$parent_form_id = $form_id ? FrmDb::get_var( 'frm_forms', array( 'id' => $form_id ), 'parent_form_id' ) : 0;

		if ( $section ) {
			if ( in_array( $type, array( 'break', 'submit', 'form', 'tabs', 'accordion' ), true ) ) {
				return false;
			}

			if ( 'divider' === $section->type && 'divider' === $type ) {
				return false;
			}
		}

		$is_repeater = $parent_form_id || ( $section && FrmField::is_repeating_field( $section ) );

		if ( ! $is_repeater ) {
			return true;
		}

		return FrmFieldFactory::get_field_type( $type )->allows_in_repeater();
	}
}
