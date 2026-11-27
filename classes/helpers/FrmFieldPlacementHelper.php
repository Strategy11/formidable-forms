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
	 * Order builder fields the way the frontend groups them.
	 *
	 * The frontend renders a repeater's rows from the fields of its child form,
	 * whatever their field_order, and renders every parent form field once. The
	 * builder nests a field by where its field_order falls between a divider and
	 * its end_divider. So a child field ordered past the end_divider showed below
	 * the repeater in the builder, and a parent field ordered inside the repeater
	 * looked repeated. Listing each child form right after its repeater, and
	 * parent form fields from inside a repeater right after its end_divider, makes
	 * the builder show what the form renders. Saving the builder then stores the
	 * matching field_order values.
	 *
	 * @since x.x
	 *
	 * @param array $fields Builder field objects sorted by field_order.
	 * @param array $args   The filter arguments. Contains `form`.
	 *
	 * @return array
	 */
	public static function sort_for_builder( $fields, $args = array() ) {
		$form = $args['form'] ?? null;

		if ( ! is_array( $fields ) || ! is_object( $form ) || empty( $form->id ) ) {
			return $fields;
		}

		$form_id     = (int) $form->id;
		$top_level   = array();
		$child_forms = array();

		foreach ( $fields as $field ) {
			if ( ! is_object( $field ) || ! isset( $field->form_id, $field->type ) ) {
				// Leave a list changed by another filter alone.
				return $fields;
			}

			if ( (int) $field->form_id === $form_id ) {
				$top_level[] = $field;
			} else {
				$child_forms[ (int) $field->form_id ][] = $field;
			}
		}

		$sorted        = array();
		$deferred      = array();
		$open_repeater = false;

		foreach ( $top_level as $field ) {
			if ( $open_repeater ) {
				if ( 'end_divider' === $field->type ) {
					$sorted[]      = $field;
					$sorted        = array_merge( $sorted, $deferred );
					$deferred      = array();
					$open_repeater = false;
					continue;
				}

				if ( ! in_array( $field->type, array( 'divider', 'break' ), true ) ) {
					// Rendered once on the frontend, so it is shown after the repeater.
					$deferred[] = $field;
					continue;
				}

				// A section or page break before the end_divider closes the repeater, like the frontend.
				$sorted        = array_merge( $sorted, $deferred );
				$deferred      = array();
				$open_repeater = false;
			}

			$sorted[] = $field;

			if ( ! FrmField::is_repeating_field( $field ) ) {
				continue;
			}

			$child_form_id = (int) FrmField::get_option( $field, 'form_select' );

			if ( isset( $child_forms[ $child_form_id ] ) ) {
				$sorted = array_merge( $sorted, $child_forms[ $child_form_id ] );
				unset( $child_forms[ $child_form_id ] );
			}

			$open_repeater = true;
		}//end foreach

		$sorted = array_merge( $sorted, $deferred );

		if ( ! $child_forms ) {
			return $sorted;
		}

		// No repeater renders these, so they never show on the frontend. Keep them editable, before the submit button.
		$submit = $sorted && FrmSubmitHelper::FIELD_TYPE === end( $sorted )->type ? array_pop( $sorted ) : null;

		foreach ( $child_forms as $unrendered ) {
			$sorted = array_merge( $sorted, $unrendered );
		}

		if ( $submit ) {
			$sorted[] = $submit;
		}

		return $sorted;
	}

	/**
	 * Find the repeater that renders a child form's fields on the frontend.
	 *
	 * @since x.x
	 *
	 * @param int $child_form_id Repeater child form ID.
	 *
	 * @return false|object The repeater field, or false when no repeater uses the form.
	 */
	public static function get_repeater_for_child_form( $child_form_id ) {
		$child_form = $child_form_id ? FrmForm::getOne( $child_form_id ) : false;

		if ( ! $child_form || empty( $child_form->parent_form_id ) ) {
			return false;
		}

		foreach ( FrmField::get_all_types_in_form( $child_form->parent_form_id, 'divider' ) as $divider ) {
			if ( is_object( $divider ) && FrmField::is_repeating_field( $divider ) && (int) FrmField::get_option( $divider, 'form_select' ) === (int) $child_form->id ) {
				return $divider;
			}
		}

		return false;
	}

	/**
	 * Settle where a new field goes, using the rules the frontend renders with.
	 *
	 * The frontend repeats the fields of a repeater's child form and groups the
	 * other fields into sections by field_order. So in_section and form_id are
	 * made to agree with each other before the field is saved: a field placed in
	 * a repeater is stored in its child form, a field created on a child form
	 * gets that repeater as its section, and a field given a field_order inside
	 * a section or repeater joins it. A field with no field_order is put at the
	 * end of its section or repeater rather than at the end of the form.
	 *
	 * @since x.x
	 *
	 * @param array $values    Prepared field values, including form_id and field_options.
	 * @param bool  $has_order True when the caller chose the field_order.
	 *
	 * @return array|WP_Error
	 */
	public static function prepare_new_placement( $values, $has_order ) {
		$form = FrmForm::getOne( $values['form_id'] ?? 0 );

		if ( ! $form ) {
			return $values;
		}

		$top_form_id = $form->parent_form_id ? (int) $form->parent_form_id : (int) $form->id;
		$type        = explode( '|', $values['type'] ?? '' )[0];
		$ends        = self::get_container_ends();

		if ( in_array( $type, $ends, true ) ) {
			// Closing fields only render in the top-level form.
			$values['form_id'] = $top_form_id;

			if ( ! $has_order ) {
				$values['field_order'] = self::get_next_order( $top_form_id );
			}

			return $values;
		}

		$section = self::get_requested_section( $values, $top_form_id, $form );

		if ( is_wp_error( $section ) ) {
			return $section;
		}

		if ( $has_order && (int) $form->id === $top_form_id ) {
			// The frontend groups top-level fields by order, so the order decides the section.
			$at_order = self::get_container_at_order( $top_form_id, (int) $values['field_order'] );

			if ( ! $section ) {
				// A field that cannot go in that section, like a page break, keeps its top-level placement.
				$section = $at_order && self::is_allowed( $type, $top_form_id, (int) $at_order->id ) ? $at_order : false;
			} elseif ( ! FrmField::is_repeating_field( $section ) && ( ! $at_order || (int) $at_order->id !== (int) $section->id ) ) {
				// Repeaters render their child form whatever the order, but a section is only the fields ordered inside it.
				return new WP_Error(
					'frm_field_order_outside_section',
					__( 'field_order is outside the section set in field_options.in_section. Leave field_order out to add the field at the end of the section.', 'formidable' ),
					array( 'status' => 400 )
				);
			}
		}

		$values['field_options']['in_section'] = $section ? (int) $section->id : 0;
		$values['form_id']                     = $top_form_id;

		if ( $section && FrmField::is_repeating_field( $section ) ) {
			$values['form_id'] = (int) FrmField::get_option( $section, 'form_select' );
		} elseif ( ! $section && (int) $form->id !== $top_form_id ) {
			// A child form no repeater points to. Keep the field where it was asked for.
			$values['form_id'] = (int) $form->id;
		}

		if ( ! $has_order ) {
			$values['field_order'] = $section ? self::get_order_at_section_end( $section ) : self::get_next_order( $top_form_id );
		}

		return $values;
	}

	/**
	 * Settle where an updated field goes when the update changes its section.
	 *
	 * Uses the same rules as prepare_new_placement(), so moving a field into a
	 * repeater stores it in the child form, and moving it out stores it in the
	 * top-level form, like dragging it in the builder. Updates that keep the
	 * stored in_section are left alone, even for a legacy field whose form_id
	 * disagrees with it, because callers often resend every stored option and
	 * moving the field would hide its saved entry values.
	 *
	 * @since x.x
	 *
	 * @param stdClass $field      The stored field.
	 * @param array    $field_data Field data to update, with field_options merged over the stored options.
	 * @param array    $input      The update input as sent, before stored options were merged in.
	 *
	 * @return array|WP_Error
	 */
	public static function prepare_update_placement( $field, $field_data, $input ) {
		if ( ! isset( $input['field_options']['in_section'], $field_data['field_options']['in_section'] ) || ! is_numeric( $input['field_options']['in_section'] ) ) {
			return $field_data;
		}

		$section_id = (int) $field_data['field_options']['in_section'];
		$type       = $field_data['type'] ?? $field->type;

		if ( $section_id === (int) FrmField::get_option( $field, 'in_section' ) || self::is_end_type( $type ) ) {
			return $field_data;
		}

		$has_order = isset( $input['field_order'] );
		$values    = array(
			'type'          => $type,
			'form_id'       => $field->form_id,
			'field_order'   => $field_data['field_order'] ?? $field->field_order,
			'field_options' => $field_data['field_options'],
		);

		if ( ! $section_id ) {
			// Moving out of a section or repeater. The top-level form is the destination.
			$form = FrmForm::getOne( $field->form_id );

			if ( $form && $form->parent_form_id ) {
				$values['form_id'] = (int) $form->parent_form_id;
			}
		}

		$values = self::prepare_new_placement( $values, $has_order );

		if ( is_wp_error( $values ) ) {
			return $values;
		}

		$field_data['field_options']['in_section'] = $values['field_options']['in_section'];

		if ( (int) $values['form_id'] !== (int) $field->form_id ) {
			$field_data['form_id'] = (int) $values['form_id'];
		}

		if ( ! $has_order ) {
			$field_data['field_order'] = (int) $values['field_order'];
		}

		return $field_data;
	}

	/**
	 * Get the fields that close a deleted section, and the ones inside it.
	 *
	 * @since x.x
	 *
	 * @param stdClass $container Section or other container field.
	 *
	 * @return array{end: false|object, children: array} The closing field, if any, and the
	 *                                                     top-level fields with in_section set to the container.
	 */
	public static function get_container_parts( $container ) {
		$map      = self::map_containers( (int) $container->form_id );
		$end      = $map['ends'][ (int) $container->id ] ?? false;
		$children = array();

		foreach ( $map['fields'] as $field ) {
			if ( (int) FrmField::get_option( $field, 'in_section' ) === (int) $container->id ) {
				$children[] = $field;
			}
		}

		return array(
			'end'      => is_object( $end ) && self::is_end_type( $end->type ) ? $end : false,
			'children' => $children,
		);
	}

	/**
	 * Remove what a deleted section or repeater leaves behind.
	 *
	 * The builder deletes a section's end field and its fields with it, and Pro
	 * deletes a repeater's child form, but only in the admin. Without this a
	 * deleted section left an end field that closes nothing, fields pointing at
	 * a section that is gone, and an empty child form. Fields of a plain section
	 * are kept and move up to the section's own container, since deleting a
	 * heading should not delete what was under it.
	 *
	 * @since x.x
	 *
	 * @param stdClass $field The deleted container field.
	 * @param array    $parts Its closing field and child fields, from get_container_parts().
	 *
	 * @return void
	 */
	public static function clean_up_deleted_container( $field, $parts ) {
		if ( $parts['end'] ) {
			FrmField::destroy( $parts['end']->id );
		}

		$parent_section = (int) FrmField::get_option( $field, 'in_section' );

		foreach ( $parts['children'] as $child ) {
			$options               = (array) $child->field_options;
			$options['in_section'] = $parent_section;
			FrmField::update( $child->id, array( 'field_options' => $options ) );
		}

		$child_form = FrmField::is_repeating_field( $field ) ? FrmForm::getOne( (int) FrmField::get_option( $field, 'form_select' ) ) : false;

		if ( $child_form && (int) $child_form->parent_form_id === (int) $field->form_id ) {
			FrmForm::destroy( $child_form->id );
		}
	}

	/**
	 * Check whether a field type opens a builder container, like a section.
	 *
	 * @since x.x
	 *
	 * @param string $type Field type.
	 *
	 * @return bool
	 */
	public static function is_container_type( $type ) {
		return isset( self::get_container_ends()[ $type ] );
	}

	/**
	 * Check whether a field type closes a builder container, like an end_divider.
	 *
	 * @since x.x
	 *
	 * @param string $type Field type.
	 *
	 * @return bool
	 */
	public static function is_end_type( $type ) {
		return in_array( $type, self::get_container_ends(), true );
	}

	/**
	 * Get the field type that closes a container type.
	 *
	 * @since x.x
	 *
	 * @param string $type Container field type, like divider.
	 *
	 * @return string The end field type, or an empty string when the type is not a container.
	 */
	public static function get_end_type( $type ) {
		return self::get_container_ends()[ $type ] ?? '';
	}

	/**
	 * Get the section a new field asks for, after checking it can hold fields.
	 *
	 * @param array    $values      Prepared field values.
	 * @param int      $top_form_id The top-level form.
	 * @param stdClass $form        The form the field was created on.
	 *
	 * @return false|object|WP_Error
	 */
	private static function get_requested_section( $values, $top_form_id, $form ) {
		$section_id = (int) ( $values['field_options']['in_section'] ?? 0 );

		if ( ! $section_id ) {
			// A field created on a child form belongs to the repeater that renders it.
			return (int) $form->id === $top_form_id ? false : self::get_repeater_for_child_form( $form->id );
		}

		$section = FrmField::getOne( $section_id );

		if ( ! $section || (int) $section->form_id !== $top_form_id || ! self::is_container_type( $section->type ) ) {
			return new WP_Error(
				'frm_invalid_section',
				__( 'field_options.in_section must be the ID of a section or repeater on the same form.', 'formidable' ),
				array( 'status' => 400 )
			);
		}

		if ( FrmField::is_repeating_field( $section ) ) {
			$child_form = FrmForm::getOne( (int) FrmField::get_option( $section, 'form_select' ) );

			if ( ! $child_form || (int) $child_form->parent_form_id !== $top_form_id ) {
				return new WP_Error(
					'frm_invalid_section',
					__( 'That repeater has no child form, so fields cannot be added to it. Set its field_options.form_select first.', 'formidable' ),
					array( 'status' => 400 )
				);
			}
		}

		return $section;
	}

	/**
	 * Get the section or repeater a field_order falls in, as the frontend groups fields.
	 *
	 * @param int $form_id Top-level form ID.
	 * @param int $order   The field_order. A field already at this order is pushed after it.
	 *
	 * @return false|object
	 */
	private static function get_container_at_order( $form_id, $order ) {
		$map       = self::map_containers( $form_id );
		$container = 0;

		foreach ( $map['fields'] as $field ) {
			if ( (int) $field->field_order >= $order ) {
				break;
			}

			$container = $map['after'][ (int) $field->id ];
		}

		$field = $container ? FrmField::getOne( $container ) : null;

		return is_object( $field ) ? $field : false;
	}

	/**
	 * Get the field_order at the end of a section or repeater, and make room there.
	 *
	 * @param stdClass $section Section or repeater field.
	 *
	 * @return int
	 */
	private static function get_order_at_section_end( $section ) {
		$map   = self::map_containers( (int) $section->form_id );
		$end   = $map['ends'][ (int) $section->id ] ?? false;
		$order = (int) $section->field_order;

		foreach ( $map['fields'] as $field ) {
			if ( $end && (int) $field->field_order >= (int) $end->field_order ) {
				break;
			}

			if ( (int) $field->field_order > $order && $map['after'][ (int) $field->id ] === (int) $section->id ) {
				$order = (int) $field->field_order;
			}
		}

		if ( FrmField::is_repeating_field( $section ) ) {
			foreach ( FrmField::get_all_for_form( (int) FrmField::get_option( $section, 'form_select' ), '', 'exclude', 'exclude' ) as $child ) {
				if ( ! $end || (int) $child->field_order < (int) $end->field_order ) {
					$order = max( $order, (int) $child->field_order );
				}
			}
		}

		if ( ! $end ) {
			// Nothing closes it, so the end of the form is the end of the section.
			return max( $order + 1, self::get_next_order( (int) $section->form_id ) );
		}

		++$order;
		self::shift_orders( (int) $section->form_id, $order );

		return $order;
	}

	/**
	 * Get the field_order after every field of a form and its repeaters.
	 *
	 * @param int $form_id Top-level form ID.
	 *
	 * @return int
	 */
	private static function get_next_order( $form_id ) {
		$max = 0;

		foreach ( FrmField::get_all_for_form( $form_id, '', 'exclude', 'include' ) as $field ) {
			$max = max( $max, (int) $field->field_order );
		}

		return $max + 1;
	}

	/**
	 * Move every field of a form and its repeaters at or after a field_order down by one.
	 *
	 * @param int $form_id Top-level form ID.
	 * @param int $order   The first field_order to move.
	 *
	 * @return void
	 */
	private static function shift_orders( $form_id, $order ) {
		foreach ( FrmField::get_all_for_form( $form_id, '', 'exclude', 'include' ) as $field ) {
			if ( (int) $field->field_order >= $order ) {
				FrmField::update( $field->id, array( 'field_order' => (int) $field->field_order + 1 ) );
			}
		}
	}

	/**
	 * Walk a top-level form's fields and record which container each one leaves open.
	 *
	 * Mirrors the frontend: a container opens at its field and closes at its end
	 * field. A section is also closed by the next section or page break, as
	 * FrmFormsHelper::auto_add_end_section_fields() does.
	 *
	 * @param int $form_id Top-level form ID.
	 *
	 * @return array{fields: array, after: array<int, int>, ends: array} The fields
	 *         in order, the container open after each field, and the field closing each container.
	 */
	private static function map_containers( $form_id ) {
		$ends   = self::get_container_ends();
		$fields = FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' );
		$open   = array();
		$after  = array();
		$closed = array();

		foreach ( $fields as $field ) {
			$top = $open ? end( $open ) : null;

			if ( $top && 'divider' === $top->type && in_array( $field->type, array( 'divider', 'break' ), true ) ) {
				$closed[ (int) $top->id ] = $field;
				array_pop( $open );
			}

			if ( isset( $ends[ $field->type ] ) ) {
				$open[] = $field;
			} elseif ( in_array( $field->type, $ends, true ) ) {
				self::close_container( $open, $closed, $field, $ends );
			}

			$after[ (int) $field->id ] = $open ? (int) end( $open )->id : 0;
		}

		return array(
			'fields' => array_values( $fields ),
			'after'  => $after,
			'ends'   => $closed,
		);
	}

	/**
	 * Close the innermost open container that the end field belongs to.
	 *
	 * @param stdClass[] $open   Open containers, innermost last.
	 * @param stdClass[] $closed The field closing each container, keyed by container ID.
	 * @param stdClass   $end    The end field.
	 * @param string[]   $ends   End field type for each container type.
	 *
	 * @return void
	 */
	private static function close_container( &$open, &$closed, $end, $ends ) {
		for ( $i = count( $open ) - 1; $i >= 0; $i-- ) {
			if ( $ends[ $open[ $i ]->type ] === $end->type ) {
				$closed[ (int) $open[ $i ]->id ] = $end;
				array_splice( $open, $i );
				return;
			}
		}
	}

	/**
	 * Get the end field type for each container type the builder supports.
	 *
	 * @return array<string, string> End field type keyed by container field type.
	 */
	private static function get_container_ends() {
		$ends = array( 'divider' => 'end_divider' );

		if ( ! is_callable( 'FrmProBuilderContainersHelper::get_containers' ) ) {
			return $ends;
		}

		foreach ( FrmProBuilderContainersHelper::get_containers() as $type => $container ) {
			$ends[ $type ] = $container['end'];
		}

		return $ends;
	}
}
