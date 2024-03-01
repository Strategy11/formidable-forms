<?php
/**
 * Submit button controller
 *
 * @since x.x
 * @package Formidable
 */

class FrmSubmitButtonController {

	const FIELD_TYPE = 'submit';

	public static function get_submit_field( $form_id ) {
		$fields = FrmField::get_all_types_in_form( $form_id, self::FIELD_TYPE, 1 );
		if ( ! $fields ) {
			return false;
		}

		return reset( $fields );
	}

	/**
	 * Checks if there is submit button field on the current page.
	 *
	 * @param array $values Prepared form values.
	 * @return bool
	 */
	public static function has_submit_field_on_current_page( $values ) {
		if ( empty( $values['fields'] ) ) {
			return false;
		}

		foreach ( $values['fields'] as $field ) {
			if ( self::FIELD_TYPE === $field['type'] ) {
				return true;
			}
		}

		return false;
	}
}
