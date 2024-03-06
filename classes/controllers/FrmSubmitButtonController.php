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
		return FrmField::get_all_types_in_form( $form_id, self::FIELD_TYPE, 1 );
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

	/**
	 * Gets current action (create or update) from the global variable.
	 *
	 * @param int $form_id Form ID.
	 * @return string
	 */
	public static function get_current_action_from_global_var( $form_id ) {
		global $frm_vars;

		if ( isset( $frm_vars['form_params'][ $form_id ]['action'] ) ) {
			return $frm_vars['form_params'][ $form_id ]['action'];
		}

		return 'create';
	}

	public static function copy_submit_field_settings_to_form( $form ) {
		$submit_field = self::get_submit_field( $form->id );
		if ( ! $submit_field ) {
			return $form;
		}

		$form->options['submit_value'] = $submit_field->name;

		return $form;
	}
}
