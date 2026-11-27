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
		return (bool) has_filter( 'frm_ability_new_field_placement' );
	}
}
