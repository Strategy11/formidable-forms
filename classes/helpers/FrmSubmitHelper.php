<?php
/**
 * Submit helper
 *
 * @since 6.9
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmSubmitHelper
 */
class FrmSubmitHelper {

	/**
	 * Field type name.
	 *
	 * @var string
	 */
	const FIELD_TYPE = 'submit';

	/**
	 * Default order for submit field.
	 *
	 * @var int
	 */
	const DEFAULT_ORDER = 9999;

	/**
	 * Track the new order of last row fields after a new field is added.
	 *
	 * @var array Keys are field IDs, values are the order.
	 */
	private static $last_row_fields_order = array();

	/**
	 * Gets submit field object.
	 *
	 * @param int $form_id Form ID.
	 * @return object
	 */
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

		return self::has_submit_field_in_list( $values['fields'] );
	}

	/**
	 * Checks if the given fields list contains a submit field.
	 *
	 * @param array $fields Array of fields.
	 * @return bool
	 */
	private static function has_submit_field_in_list( $fields ) {
		foreach ( $fields as $field ) {
			if ( self::FIELD_TYPE === FrmField::get_field_type( $field ) ) {
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

		return $frm_vars['form_params'][ $form_id ]['action'] ?? 'create';
	}

	/**
	 * Gets submit button settings from form option.
	 *
	 * @param object $form Form object.
	 * @return array
	 */
	private static function get_submit_settings_from_form( $form ) {
		return array(
			'edit_text'        => FrmForm::get_option(
				array(
					'form'   => $form,
					'option' => 'edit_value',
				)
			),
			'align'            => FrmForm::get_option(
				array(
					'form'   => $form,
					'option' => 'submit_align',
				)
			),
			'start_over'       => FrmForm::get_option(
				array(
					'form'   => $form,
					'option' => 'start_over',
				)
			),
			'start_over_label' => FrmForm::get_option(
				array(
					'form'   => $form,
					'option' => 'start_over_label',
				)
			),
		);
	}

	/**
	 * Copies submit field settings to form options.
	 *
	 * @param object $form Form object.
	 * @return object
	 */
	public static function copy_submit_field_settings_to_form( $form ) {
		$submit_field = self::get_submit_field( $form->id );
		if ( ! $submit_field ) {
			return $form;
		}

		$form->options['submit_value'] = $submit_field->name;

		return $form;
	}

	/**
	 * Maybe create a submit field for a form.
	 *
	 * @param object $form         Form object.
	 * @param array  $fields       Array of fields.
	 * @param bool   $reset_fields Flag to refresh fields after one is created or updated.
	 */
	public static function maybe_create_submit_field( $form, $fields, &$reset_fields ) {
		if ( self::has_submit_field_in_list( $fields ) ) {
			return;
		}

		$field_data = FrmFieldsHelper::setup_new_vars( self::FIELD_TYPE, $form->id );

		$submit_settings             = self::get_submit_settings_from_form( $form );
		$field_data['field_options'] = $submit_settings + $field_data['field_options'];
		$field_data['name']          = FrmForm::get_option(
			array(
				'form'    => $form,
				'option'  => 'submit_value',
				'default' => __( 'Submit', 'formidable' ),
			)
		);

		if ( FrmField::create( $field_data ) ) {
			$reset_fields = true;
		}
	}

	/**
	 * Removes submit field from the list of fields.
	 *
	 * @param array $fields Array of fields.
	 */
	public static function remove_submit_field_from_list( &$fields ) {
		foreach ( $fields as $key => $field ) {
			if ( self::FIELD_TYPE === FrmField::get_field_type( $field ) ) {
				unset( $fields[ $key ] );
				return;
			}
		}
	}

	/**
	 * Checks if the given fields array only contains the submit field.
	 *
	 * @param array $fields Array of fields.
	 * @return false|object Return the last found submit field, or `false` if there is at least another field.
	 */
	public static function only_contains_submit_field( $fields ) {
		$submit_field = false;
		foreach ( $fields as $field ) {
			if ( self::FIELD_TYPE !== FrmField::get_field_type( $field ) ) {
				return false;
			}
			$submit_field = $field;
		}
		return $submit_field;
	}

	/**
	 * Updates fields in the last row when new field is added.
	 *
	 * @since 6.25.1
	 *
	 * @param int $field_count The current field count.
	 */
	public static function update_last_row_fields_order_when_adding_field( $field_count ) {
		$last_row_field_ids = FrmAppHelper::get_post_param( 'last_row_field_ids', array() );
		if ( ! is_array( $last_row_field_ids ) || empty( $last_row_field_ids ) ) {
			return;
		}
		foreach ( $last_row_field_ids as $index => $last_row_field_id ) {
			$last_row_field_id = absint( $last_row_field_id );
			if ( ! $last_row_field_id ) {
				continue;
			}
			// Plus 2 here because the new field has that plus 1.
			$new_order = $field_count + $index + 2;
			$updated   = FrmField::update(
				$last_row_field_id,
				array( 'field_order' => $new_order )
			);
			if ( false !== $updated ) {
				self::$last_row_fields_order[ $last_row_field_id ] = $new_order;
			}
		}
	}

	/**
	 * Prints the hidden input that contains the last row fields order to be processed in JS after adding new field.
	 *
	 * @since 6.25.1
	 */
	public static function print_last_row_fields_order_input() {
		if ( ! self::$last_row_fields_order ) {
			return;
		}

		printf(
			'<input id="frm-last-row-fields-order" type="hidden" value="%s" />',
			esc_attr( wp_json_encode( self::$last_row_fields_order ) )
		);

		self::$last_row_fields_order = array();
	}
}
