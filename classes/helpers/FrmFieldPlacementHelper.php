<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Shared placement rules for the builder and programmatic field writes.
 *
 * @since 7.1
 */
class FrmFieldPlacementHelper {

	/**
	 * Check placement using stored values for columns omitted by an update.
	 *
	 * @since 7.1
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

		$type    = $values['type'] ?? $field->type;
		$form_id = (int) ( $values['form_id'] ?? $field->form_id );
		$options = $values['field_options'] ?? $field->field_options;
		// Some callers, like Pro, pass the options already serialized.
		FrmAppHelper::unserialize_or_decode( $options );
		$section_id = is_array( $options ) ? (int) ( $options['in_section'] ?? 0 ) : 0;

		if ( $type === $field->type && $form_id === (int) $field->form_id && $section_id === (int) FrmField::get_option( $field, 'in_section' ) ) {
			// Existing placements stay editable even when the current policy forbids creating them.
			return true;
		}

		return self::is_allowed( $type, $form_id, $section_id );
	}

	/**
	 * Check the destination before writing a field or running creation hooks.
	 *
	 * @since 7.1
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
		$form           = $form_id ? FrmForm::getOne( $form_id ) : false;
		$parent_form_id = $form ? $form->parent_form_id : 0;

		if ( $section ) {
			if ( in_array( $type, array( 'break', 'submit', 'form', 'tabs', 'accordion' ), true ) ) {
				return false;
			}

			if ( 'divider' === $section->type && 'divider' === $type ) {
				$panel_parent = FrmField::getOne( (int) FrmField::get_option( $section, 'in_section' ) );

				if ( ! $panel_parent || ! in_array( $panel_parent->type, array( 'tabs', 'accordion' ), true ) ) {
					return false;
				}
			}
		}

		$is_repeater = $parent_form_id || ( $section && FrmField::is_repeating_field( $section ) );

		if ( ! $is_repeater ) {
			return true;
		}

		return FrmFieldFactory::get_field_type( $type )->allows_in_repeater();
	}

	/**
	 * Check whether Pro places ability-created fields in sections and repeaters.
	 *
	 * When it does not, the abilities keep their earlier placement. Pro also
	 * checks that this method exists before it changes how sections are grouped,
	 * so a mismatched Lite and Pro both keep the earlier behavior.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function has_ability_section_placement() {
		return has_filter( 'frm_ability_new_field_placement' );
	}

	/**
	 * Decide which form, section and field_order a new field gets.
	 *
	 * Sections and repeaters are Pro fields, so Pro places fields in them
	 * through the frm_ability_new_field_placement filter. With a Pro version
	 * that does not, the earlier repeater placement is kept.
	 *
	 * @since x.x
	 *
	 * @param array    $prepared Prepared field data.
	 * @param array    $field    Raw field input.
	 * @param stdClass $form     The form the field is created on.
	 *
	 * @return array|WP_Error
	 */
	public static function place_new_ability_field( $prepared, $field, $form ) {
		if ( ! self::has_ability_section_placement() ) {
			return self::maybe_position_in_repeater( $prepared, $field, $form );
		}

		/**
		 * Filters where a field created by an ability is stored.
		 *
		 * A callback may change form_id, field_order and field_options.in_section,
		 * or return a WP_Error to reject the field.
		 *
		 * @since x.x
		 *
		 * @param array $prepared Prepared field data.
		 * @param array $args     Contains `field`, the input as sent, `form`, the form the field is created on,
		 *                        and `has_order`, true when the caller chose the field_order.
		 */
		$placed = apply_filters(
			'frm_ability_new_field_placement',
			$prepared,
			array(
				'field'     => $field,
				'form'      => $form,
				'has_order' => isset( $field['field_order'] ),
			)
		);

		return is_array( $placed ) || is_wp_error( $placed ) ? $placed : $prepared;
	}

	/**
	 * Slot a new repeater child field after the last field of its section.
	 *
	 * Repeater child fields sort into the parent form's field list purely by
	 * field_order, so an order computed against the child form alone can land
	 * before the repeater's own divider and render outside the section in the
	 * builder. When the caller gives no explicit order, the field goes right
	 * after the last field of its section and the fields that follow shift down.
	 *
	 * @since 7.0
	 * @since x.x Moved here from FrmAbilitiesFieldsController.
	 *
	 * @param array    $prepared Prepared field data.
	 * @param array    $field    Raw field input.
	 * @param stdClass $form     The form the field is created on.
	 *
	 * @return array
	 */
	private static function maybe_position_in_repeater( $prepared, $field, $form ) {
		if ( isset( $field['field_order'] ) || empty( $form->parent_form_id ) ) {
			return $prepared;
		}

		$parent_fields = FrmField::get_all_for_form( $form->parent_form_id, '', 'include' );
		$section_order = 0;

		foreach ( $parent_fields as $parent_field ) {
			$form_select         = isset( $parent_field->field_options['form_select'] ) ? (int) $parent_field->field_options['form_select'] : 0;
			$starts_this_section = 'divider' === $parent_field->type && $form_select === (int) $form->id;
			$in_this_section     = (int) $parent_field->form_id === (int) $form->id;

			if ( $starts_this_section || $in_this_section ) {
				$section_order = max( $section_order, (int) $parent_field->field_order );
			}
		}

		if ( ! $section_order ) {
			return $prepared;
		}

		$prepared['field_order'] = $section_order + 1;

		foreach ( $parent_fields as $parent_field ) {
			if ( (int) $parent_field->field_order > $section_order ) {
				FrmField::update( $parent_field->id, array( 'field_order' => (int) $parent_field->field_order + 1 ) );
			}
		}

		return $prepared;
	}
}
