<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Describes simple field previews as data so the form builder can draw them in the browser.
 *
 * Only the values a preview shows are printed with the page. The settings panels, which are most
 * of the cost of rendering a field, are requested separately once the previews are on screen.
 *
 * @since x.x
 */
class FrmBuilderPreviewHelper {

	/**
	 * Field types with a preview the browser can draw, mapped to the field classes that render
	 * them. A field type class replaced by an add-on is drawn on the server, since its preview
	 * may differ from the one described here.
	 *
	 * @var array<string,string[]>
	 */
	private static $field_classes = array(
		'text'     => array( 'FrmFieldText', 'FrmProFieldText' ),
		'email'    => array( 'FrmFieldEmail', 'FrmProFieldEmail' ),
		'url'      => array( 'FrmFieldUrl', 'FrmProFieldUrl' ),
		'number'   => array( 'FrmFieldNumber', 'FrmProFieldNumber' ),
		'phone'    => array( 'FrmFieldPhone', 'FrmProFieldPhone' ),
		'textarea' => array( 'FrmFieldTextarea', 'FrmProFieldTextarea' ),
		'hidden'   => array( 'FrmFieldHidden', 'FrmProFieldHidden' ),
		'html'     => array( 'FrmFieldHTML', 'FrmProFieldHTML' ),
	);

	/**
	 * Field types previewed as a single line text input.
	 *
	 * @var string[]
	 */
	private static $text_input_types = array( 'text', 'email', 'url', 'number', 'phone' );

	/**
	 * Check whether a field preview can be drawn in the browser.
	 *
	 * This runs before the field settings are prepared, so it only looks at the saved field.
	 *
	 * @since x.x
	 *
	 * @param object       $field     The field being added to the form builder.
	 * @param FrmFieldType $field_obj The field type object for the field.
	 * @param array        $display   The field display settings.
	 *
	 * @return bool
	 */
	public static function supports( $field, $field_obj, $display ) {
		$type = $field->type;

		/**
		 * Filters the exact field classes whose previews the browser can draw.
		 *
		 * Register a matching renderer with the frm_builder_preview_inputs JavaScript filter.
		 *
		 * @since x.x
		 *
		 * @param array<string,string[]> $field_classes Supported classes keyed by field type.
		 */
		$field_classes = apply_filters( 'frm_builder_preview_field_classes', self::$field_classes );

		if ( ! isset( $field_classes[ $type ] ) || ! in_array( get_class( $field_obj ), $field_classes[ $type ], true ) ) {
			return false;
		}

		if ( ! empty( $display['conf_field'] ) && ! is_callable( 'FrmProFieldsController::add_confirmation_field_preview' ) ) {
			// Older Pro versions print the confirmation preview from the add_field.php view.
			return false;
		}

		if ( in_array( $type, self::$text_input_types, true ) && self::has_input_group( $field ) ) {
			return false;
		}

		if ( is_array( $field->default_value ) || is_array( FrmField::get_option( $field, 'placeholder' ) ) ) {
			return false;
		}

		/**
		 * Filter whether a field preview in the form builder is drawn in the browser.
		 *
		 * Return false for a field whose preview is changed on the server, so it keeps
		 * the preview rendered by PHP.
		 *
		 * @since x.x
		 *
		 * @param bool   $supported Whether the preview can be drawn in the browser.
		 * @param object $field     The field being added to the form builder.
		 */
		return (bool) apply_filters( 'frm_builder_preview_in_browser', true, $field );
	}

	/**
	 * Pro wraps an input in a prefix and suffix group, which the browser does not draw.
	 *
	 * @since x.x
	 *
	 * @param object $field The field being added to the form builder.
	 *
	 * @return bool
	 */
	private static function has_input_group( $field ) {
		foreach ( array( 'prepend', 'append', 'is_currency', 'format' ) as $option ) {
			if ( FrmField::get_option( $field, $option ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Record a field preview and print the empty list item it will be drawn into.
	 *
	 * @since x.x
	 *
	 * @param array{field: array, saved_field: object, field_obj: FrmFieldType, display: array, li_classes: string, extra_field_attributes: array} $atts     The field settings from FrmFieldsHelper::setup_edit_vars(), the saved field, its type object, display settings, and list item classes and attributes.
	 * @param object{definitions: array, fields: array}&\stdClass                                                                                  $manifest The previews printed with the form builder.
	 *
	 * @return void
	 */
	public static function add_to_manifest( $atts, $manifest ) {
		$index              = count( $manifest->fields );
		$manifest->fields[] = self::get_preview_data( $atts, $manifest );

		echo '<li data-frm-preview="' . esc_attr( $index ) . '"></li>';
	}

	/**
	 * Print the previews and the text they share for the browser to draw.
	 *
	 * @since x.x
	 *
	 * @param object{definitions: array, fields: array}&\stdClass $manifest The previews collected while the fields were listed.
	 *
	 * @return void
	 */
	public static function print_manifest( $manifest ) {
		if ( ! $manifest->fields ) {
			return;
		}

		$manifest->strings = array(
			'moveField'     => __( 'Move Field', 'formidable' ),
			'moreOptions'   => __( 'More Options', 'formidable' ),
			'toggleOptions' => __( 'Toggle More Options Dropdown', 'formidable' ),
			'collapsed'     => __( '(Collapsed)', 'formidable' ),
			'hiddenNote'    => __( 'Note: This field will not show in the form. Enter the value to be hidden.', 'formidable' ),
			'customHtml'    => __( 'Custom HTML:', 'formidable' ),
			'dropdownAlign' => is_rtl() ? 'dropdown-menu-left' : 'dropdown-menu-right',
		);

		wp_print_inline_script_tag(
			wp_json_encode( $manifest, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ),
			array(
				'id'   => 'frm-field-previews',
				'type' => 'application/json',
			)
		);
	}

	/**
	 * Collect what the browser needs to draw one field preview.
	 *
	 * Text that the server prints as HTML is sent as sanitized HTML. Input values are sent as the
	 * value the browser would read from the escaped attribute, so they match the PHP preview.
	 * Empty values are left out to keep the page small.
	 *
	 * @since x.x
	 *
	 * @param array{field: array, saved_field: object, field_obj: FrmFieldType, display: array, li_classes: string, extra_field_attributes: array} $atts     The prepared field. See add_to_manifest().
	 * @param object{definitions: array, fields: array}&\stdClass                                                                                  $manifest The previews printed with the form builder.
	 *
	 * @return array
	 */
	private static function get_preview_data( $atts, $manifest ) {
		$field     = $atts['field'];
		$field_obj = $atts['field_obj'];
		$display   = $atts['display'];
		$label     = FrmAppHelper::kses( force_balance_tags( $field['name'] ), 'all' );

		$definition = array(
			self::attribute_value( $atts['li_classes'] ),
			(int) $field['form_id'],
			$field['type'],
			$display['type'],
		);

		$data = array(
			'id'         => (int) $field['id'],
			'definition' => self::get_definition_index( $definition, $manifest ),
			'label'      => $label,
			'key'        => self::attribute_value( $field['field_key'] ),
			'order'      => self::attribute_value( $field['field_order'] ),
			'align'      => $field['align'] ?? null,
		);

		$name = self::attribute_value( $field['name'] );

		if ( $label !== $name ) {
			$data['name'] = $name;
		}

		if ( FrmField::is_required( $field ) ) {
			$data['required'] = 1;
		}

		if ( $atts['extra_field_attributes'] ) {
			$data['attributes'] = array_map( array( self::class, 'attribute_value' ), $atts['extra_field_attributes'] );
		}

		self::add_input_data( $data, $field_obj, $field, $atts['saved_field'] );
		/**
		 * Filters the data used to draw a field's inputs in the browser.
		 *
		 * @since x.x
		 *
		 * @param array        $data      Preview data.
		 * @param array        $field     Prepared field settings.
		 * @param FrmFieldType $field_obj Field type object.
		 */
		$data = apply_filters( 'frm_builder_preview_data', $data, $field, $field_obj );

		self::add_hook_html( $data, $field, $display );

		// Keep values that change the preview, including a 0 that is printed as text.
		$data = array_filter(
			$data,
			static function ( $value ) {
				return null !== $value && '' !== $value && array() !== $value;
			}
		);

		if ( '*' !== $field['required_indicator'] ) {
			// An indicator removed on purpose stays empty instead of falling back to the default.
			$data['indicator'] = self::attribute_value( $field['required_indicator'] );
		}

		if ( $display['css'] ) {
			// Layout changes need this input to exist even when no classes are saved yet.
			$data['classes'] = self::attribute_value( $field['classes'] );
		}

		if ( $display['description'] ) {
			// An empty description still gets its container, so it is added after empty values are removed.
			$data['description'] = FrmAppHelper::kses( force_balance_tags( $field['description'] ), 'all' );
		}

		return $data;
	}

	/**
	 * Share the classes, form and type of a list item between the fields that use the same ones.
	 *
	 * @since x.x
	 *
	 * @param array                                               $definition The list item classes, form id, field type and display type.
	 * @param object{definitions: array, fields: array}&\stdClass $manifest   The previews printed with the form builder.
	 *
	 * @return int The position of the definition in the manifest.
	 */
	private static function get_definition_index( $definition, $manifest ) {
		$index = array_search( $definition, $manifest->definitions, true );

		if ( false === $index ) {
			$index                   = count( $manifest->definitions );
			$manifest->definitions[] = $definition;
		}

		return (int) $index;
	}

	/**
	 * Add the values shown in the field input.
	 *
	 * @since x.x
	 *
	 * @param array        $data        The preview data.
	 * @param FrmFieldType $field_obj   The field type object.
	 * @param array        $field       The field settings.
	 * @param object       $saved_field The field as it was read from the database, which the field class previews from.
	 *
	 * @return void
	 */
	private static function add_input_data( &$data, $field_obj, $field, $saved_field ) {
		$type          = $field['type'];
		$default_value = $field_obj->get_field_column( 'default_value' );

		if ( 'html' === $type ) {
			$data['html'] = esc_html( FrmAppHelper::truncate( htmlentities( $field['description'] ), 60 ) );
			return;
		}

		$data['htmlId'] = self::attribute_value( apply_filters( 'frm_field_get_html_id', 'field_' . $field['field_key'], $saved_field ) );

		if ( 'hidden' === $type ) {
			$data['value'] = self::attribute_value( $field_obj->maybe_decode_value( $field['default_value'] ) );
			return;
		}

		$data['placeholder'] = self::attribute_value( FrmField::get_option( $saved_field, 'placeholder' ) );

		if ( 'textarea' === $type ) {
			$data['rows'] = self::attribute_value( FrmField::get_option( $saved_field, 'max' ) );
			$data['text'] = FrmAppHelper::esc_textarea( force_balance_tags( $default_value ) );
			$size         = FrmField::get_option( $saved_field, 'size' );

			if ( $size ) {
				$data['width'] = is_numeric( $size ) ? $size . 'px' : self::attribute_value( $size );
			}

			return;
		}

		$data['value'] = self::attribute_value( $default_value );

		if ( FrmField::get_option( $saved_field, 'read_only' ) ) {
			$data['readOnly'] = 1;
		}
	}

	/**
	 * Add the markup printed by actions inside the field preview.
	 *
	 * @since x.x
	 *
	 * @param array $data    The preview data.
	 * @param array $field   The field settings.
	 * @param array $display The field display settings.
	 *
	 * @return void
	 */
	private static function add_hook_html( &$data, $field, $display ) {
		ob_start();
		FrmSubmitHelper::print_last_row_fields_order_input();

		/** This action is documented in classes/views/frm-forms/add_field.php */
		do_action( 'frm_extra_field_actions', $field['id'] );
		$data['before'] = ob_get_clean();

		if ( has_action( 'frm_builder_after_field_label' ) ) {
			ob_start();

			/** This action is documented in classes/models/fields/FrmFieldType.php */
			do_action( 'frm_builder_after_field_label', $field );
			$data['afterLabel'] = ob_get_clean();
		}

		if ( ! has_action( 'frm_builder_preview_after_field' ) ) {
			return;
		}

		ob_start();

		/** This action is documented in classes/views/frm-forms/add_field.php */
		do_action( 'frm_builder_preview_after_field', compact( 'field', 'display' ) );
		$data['after'] = ob_get_clean();
	}

	/**
	 * Get the value the browser reads from an attribute escaped with esc_attr().
	 *
	 * @since x.x
	 *
	 * @param mixed $value The attribute value.
	 *
	 * @return string
	 */
	private static function attribute_value( $value ) {
		if ( is_array( $value ) ) {
			return '';
		}

		$value = esc_attr( (string) $value );
		$value = str_replace( array( "\r\n", "\r", "\0" ), array( "\n", "\n", "\xEF\xBF\xBD" ), $value );

		if ( class_exists( 'WP_HTML_Decoder' ) ) {
			return WP_HTML_Decoder::decode_attribute( $value );
		}

		// WordPress 6.3 through 6.5 expose attribute decoding through the tag processor.
		$processor = new WP_HTML_Tag_Processor( '<input value="' . $value . '">' );
		$processor->next_tag();
		return (string) $processor->get_attribute( 'value' );
	}
}
