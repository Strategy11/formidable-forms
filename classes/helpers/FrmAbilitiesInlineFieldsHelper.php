<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Creates the inline fields of a create-form ability call, keeping sections intact.
 *
 * The inline list has no field IDs yet, so a field cannot name its section.
 * The list order says it instead, the same way the frontend reads field_order:
 * a field after a divider and before its end_divider is inside that section.
 * Each such field gets the section as in_section, which stores a repeater's
 * fields in its child form. A section still open when the next section or a
 * page break starts, or at the end of the list, gets its end_divider. An end
 * field with no open section is skipped, since it would close nothing.
 *
 * @since x.x
 */
class FrmAbilitiesInlineFieldsHelper {

	/**
	 * Containers created so far that have not been closed, innermost last.
	 *
	 * @var object[]
	 */
	private $open = array();

	/**
	 * The new form.
	 *
	 * @var stdClass
	 */
	private $form;

	/**
	 * @since x.x
	 *
	 * @param stdClass $form The new form.
	 */
	public function __construct( $form ) {
		$this->form = $form;
	}

	/**
	 * Create the inline fields in order.
	 *
	 * @since x.x
	 *
	 * @param array $fields Inline field input, in order.
	 *
	 * @return true|WP_Error
	 */
	public function create( $fields ) {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$field = FrmAbilitiesFieldsController::normalize_field_type( $field );
			$field = $this->place_in_open_sections( $field );

			if ( is_wp_error( $field ) ) {
				return $field;
			}

			if ( ! $field ) {
				// An end field with nothing open to close.
				continue;
			}

			$created = $this->create_field( $field );

			if ( is_wp_error( $created ) ) {
				return $created;
			}

			if ( $created && FrmFieldPlacementHelper::is_container_type( $created->type ) ) {
				$this->open[] = $created;
			}
		}//end foreach

		return $this->close_containers( 0 );
	}

	/**
	 * Close the sections a field is outside of, and give it the open section otherwise.
	 *
	 * @param array $field Inline field input.
	 *
	 * @return array|false|WP_Error The field to create, false to skip an end field that closes nothing.
	 */
	private function place_in_open_sections( $field ) {
		$type = explode( '|', (string) ( $field['type'] ?? '' ) )[0];
		$top  = $this->open ? end( $this->open ) : null;

		if ( $top && 'divider' === $top->type && in_array( $type, array( 'divider', 'break' ), true ) ) {
			// The next section or page break closes a section, as on the frontend.
			$closed = $this->close_containers( count( $this->open ) - 1 );

			if ( is_wp_error( $closed ) ) {
				return $closed;
			}
		}

		if ( FrmFieldPlacementHelper::is_end_type( $type ) ) {
			return $this->close_for_end_field( $field, $type );
		}

		if ( ! $this->open ) {
			return $field;
		}

		if ( ! isset( $field['field_options']['in_section'] ) ) {
			$field['field_options']               = isset( $field['field_options'] ) && is_array( $field['field_options'] ) ? $field['field_options'] : array();
			$field['field_options']['in_section'] = (int) end( $this->open )->id;

			return $field;
		}

		// A field that names its own section is outside every open section nested deeper than that one.
		$closed = $this->close_containers( $this->count_open_through( (int) $field['field_options']['in_section'] ) );

		return is_wp_error( $closed ) ? $closed : $field;
	}

	/**
	 * Close what an end field closes, so it is created as the last of them.
	 *
	 * @param array  $field Inline end field input.
	 * @param string $type  The end field type.
	 *
	 * @return array|false|WP_Error The end field, or false when it closes nothing.
	 */
	private function close_for_end_field( $field, $type ) {
		for ( $i = count( $this->open ) - 1; $i >= 0; $i-- ) {
			if ( FrmFieldPlacementHelper::get_end_type( $this->open[ $i ]->type ) !== $type ) {
				continue;
			}

			// Close anything still open inside it first, so each container gets its own end field.
			$closed = $this->close_containers( $i + 1 );

			if ( is_wp_error( $closed ) ) {
				return $closed;
			}

			array_pop( $this->open );

			return $field;
		}

		return false;
	}

	/**
	 * Count the open containers up to and including the one a field names as its section.
	 *
	 * @param int $section_id The field's in_section.
	 *
	 * @return int How many containers stay open, 0 when the section is not open.
	 */
	private function count_open_through( $section_id ) {
		foreach ( $this->open as $index => $container ) {
			if ( (int) $container->id === $section_id ) {
				return $index + 1;
			}
		}

		return 0;
	}

	/**
	 * Create the end fields for open containers, innermost first.
	 *
	 * @param int $keep How many of the outermost containers stay open.
	 *
	 * @return true|WP_Error
	 */
	private function close_containers( $keep ) {
		for ( $i = count( $this->open ); $i > $keep; $i-- ) {
			$container = array_pop( $this->open );
			$created   = $this->create_field( array( 'type' => FrmFieldPlacementHelper::get_end_type( $container->type ) ) );

			if ( is_wp_error( $created ) ) {
				return $created;
			}
		}

		return true;
	}

	/**
	 * Create one inline field through create-field's pipeline.
	 *
	 * Sharing it gives inline fields the same separate-value detection,
	 * repeater child form, placement, and validation as create-field.
	 *
	 * @param array $field Inline field input.
	 *
	 * @return false|object|WP_Error The created field, false if it could not be saved.
	 */
	private function create_field( $field ) {
		$prepared = FrmAbilitiesFieldsController::prepare_new_field( $field, $this->form );

		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$field_id = FrmField::create( $prepared );
		$created  = $field_id ? FrmField::getOne( $field_id ) : null;

		return is_object( $created ) ? $created : false;
	}
}
