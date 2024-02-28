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
}
