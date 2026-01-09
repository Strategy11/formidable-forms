<?php
/**
 * Name field
 *
 * @package Formidable
 *
 * @since 4.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFieldName extends FrmFieldCombo {

	/**
	 * Track the first name field ID in forms.
	 *
	 * @var array Array with keys are form ID and values are name field IDs.
	 */
	private static $first_name_field_ids = array();

	/**
	 * Field name.
	 *
	 * @var string
	 *
	 * @since 3.0
	 */
	protected $type = 'name';

	/**
	 * Could this field hold email values?
	 *
	 * @var bool
	 *
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	/**
	 * @param array|int|object $field
	 * @param string           $type
	 */
	public function __construct( $field = 0, $type = '' ) {
		parent::__construct( $field, $type );

		$this->register_sub_fields(
			array(
				'first'  => __( 'First Name', 'formidable' ),
				'middle' => __( 'Middle Name', 'formidable' ),
				'last'   => __( 'Last Name', 'formidable' ),
			)
		);
	}

	/**
	 * Gets processed sub fields.
	 * This should return the list of sub fields after sorting or show/hide based of some options.
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		$name_layout = $this->get_name_layout();
		$names       = explode( '_', $name_layout );
		$col_class   = 'frm' . intval( 12 / count( $names ) );
		$result      = array();

		foreach ( $names as $name ) {
			if ( empty( $this->sub_fields[ $name ] ) ) {
				continue;
			}

			if ( ! isset( $this->sub_fields[ $name ]['wrapper_classes'] ) ) {
				$this->sub_fields[ $name ]['wrapper_classes'] = $col_class;
			} elseif ( is_array( $this->sub_fields[ $name ]['wrapper_classes'] ) ) {
				$this->sub_fields[ $name ]['wrapper_classes'] = implode( ' ', $this->sub_fields[ $name ]['wrapper_classes'] ) . ' ' . $col_class;
			} else {
				$this->sub_fields[ $name ]['wrapper_classes'] .= ' ' . $col_class;
			}

			$result[ $name ] = $this->sub_fields[ $name ];
		}

		return $result;
	}

	/**
	 * Gets name layout option value.
	 *
	 * @return string
	 */
	protected function get_name_layout() {
		$name_layout = FrmField::get_option( $this->field, 'name_layout' );

		if ( ! $name_layout ) {
			$name_layout = 'first_last';
		}

		return $name_layout;
	}

	/**
	 * Gets extra field options.
	 *
	 * @return string[]
	 */
	protected function extra_field_opts() {
		$extra_options = parent::extra_field_opts();

		$extra_options['name_layout'] = 'first_last';

		// Default desc.
		foreach ( $this->sub_fields as $name => $sub_field ) {
			$extra_options[ $name . '_desc' ] = $sub_field['label'];
		}

		return $extra_options;
	}

	/**
	 * Shows primary options.
	 *
	 * @since 4.0
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 *
	 * @return void
	 */
	public function show_primary_options( $args ) {
		$field = (array) $args['field'];
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/name/primary-options.php';

		parent::show_primary_options( $args );
	}

	/**
	 * Prepares the display value.
	 * This also handles the shortcode output. Support [id], [id show=first], [id show=last], [id show=middle].
	 *
	 * @param mixed $value Field value before processing.
	 * @param array $atts  Shortcode attributes.
	 *
	 * @return string      Most of cases, this will return string.
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$name_layout = $this->get_name_layout();

		if ( ! empty( $atts['show'] ) ) {
			return $value[ $atts['show'] ] ?? '';
		}

		$value = wp_parse_args(
			$value,
			array(
				'first'  => '',
				'middle' => '',
				'last'   => '',
			)
		);

		switch ( $name_layout ) {
			case 'last_first':
				$value = $value['last'] . ' ' . $value['first'];
				break;

			case 'first_middle_last':
				$value = $value['first'] . ' ' . $value['middle'] . ' ' . $value['last'];
				break;

			default:
				$value = $value['first'] . ' ' . $value['last'];
		}

		return trim( $value );
	}

	/**
	 * @since 4.0.04
	 *
	 * @param array|string $value
	 *
	 * @return void
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}

	/**
	 * Validate field.
	 *
	 * @param array $args Arguments. Includes `errors`, `value`.
	 *
	 * @return array Errors array.
	 */
	public function validate( $args ) {
		/**
		 * If users fill just HTML tag, it passes the validation but value is empty in the database because of the
		 * sanitization. So we need to sanitize the value before validating.
		 */
		$this->sanitize_value( $args['value'] );
		return parent::validate( $args );
	}

	/**
	 * Loads processed args for field output.
	 *
	 * @param array $args {
	 *     Arguments.
	 *
	 *     @type array  $field          Field array.
	 *     @type string $html_id        HTML ID.
	 *     @type string $field_name     Field name attribute.
	 *     @type array  $shortcode_atts Shortcode attributes.
	 *     @type array  $errors         Field errors.
	 *     @type bool   $remove_names   Remove name attribute or not.
	 * }
	 *
	 * @return void
	 */
	protected function process_args_for_field_output( &$args ) {
		parent::process_args_for_field_output( $args );

		// Show all subfields in form builder then use JS to show or hide them.
		if ( $this->should_print_hidden_sub_fields() && count( $args['sub_fields'] ) !== count( $this->sub_fields ) ) {
			$hidden_fields      = array_diff_key( $this->sub_fields, $args['sub_fields'] );
			$args['sub_fields'] = $this->sub_fields;

			foreach ( $hidden_fields as $name => $hidden_field ) {
				$args['sub_fields'][ $name ]['wrapper_classes'] .= ' frm_hidden';
			}
		}
	}

	/**
	 * Checks if should print hidden subfields and hide them. This is useful to use js to show or hide sub fields.
	 *
	 * @return bool
	 */
	protected function should_print_hidden_sub_fields() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return FrmAppHelper::is_form_builder_page() || FrmAppHelper::doing_ajax() && isset( $_POST['action'] ) && 'frm_insert_field' === $_POST['action'];
	}

	/**
	 * Gets inputs container attributes.
	 *
	 * @return array
	 */
	protected function get_inputs_container_attrs() {
		$attrs = parent::get_inputs_container_attrs();

		$attrs['data-name-layout'] = $this->get_name_layout();
		return $attrs;
	}

	/**
	 * Maybe show a warning if a name field is using a description that is not descriptive enough.
	 * Prior to version 6.16, First and Last were the default values, but this has been updated to
	 * improve accessibility.
	 *
	 * @since 6.16
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function show_after_default( $args ) {
		echo '<div class="frm-mt-xs">';
		parent::show_after_default( $args );
		echo '</div>';

		/**
		 * @var array $field
		 */
		$field = $args['field'];

		$show_warning = false;

		foreach ( $this->sub_fields as $sub_field ) {
			$description = FrmField::get_option( $field, $sub_field['name'] . '_desc' );

			if ( in_array( $description, array( 'First', 'Last' ), true ) ) {
				$show_warning = true;
				break;
			}
		}

		if ( ! $show_warning ) {
			return;
		}
		?>
		<div class="frm_warning_style">
			<?php
			FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon', array( 'style' => 'width:24px' ) );
			echo ' ';
			esc_html_e( 'Subfield descriptions are read by screen readers. Enhance accessibility by using complete labels, like "First Name" instead of "First".', 'formidable' );
			?>
		</div>
		<?php
	}

	/**
	 * Tracks the first name field ID in a form.
	 *
	 * @since 6.26
	 *
	 * @param object[] $fields Array of fields in a form.
	 *
	 * @return void
	 */
	public static function track_first_name_field( $fields ) {
		foreach ( $fields as $field ) {
			if ( 'name' === $field->type ) {
				self::$first_name_field_ids[ $field->form_id ] = $field->id;
				return;
			}
		}
	}

	/**
	 * Gets subfield input attributes.
	 *
	 * @since 6.26
	 *
	 * @param array $sub_field Subfield data.
	 * @param array $args      Field output args. See {@see FrmFieldCombo::load_field_output()}.
	 *
	 * @return array
	 */
	protected function get_sub_field_input_attrs( $sub_field, $args ) {
		$attrs   = parent::get_sub_field_input_attrs( $sub_field, $args );
		$form_id = (int) ( is_array( $args['field'] ) ? $args['field']['form_id'] : $args['field']->form_id );

		if ( ! self::$first_name_field_ids || empty( self::$first_name_field_ids[ $form_id ] ) ) {
			return $attrs;
		}

		$parent_form_id = (int) FrmField::get_option( $args['field'], 'parent_form_id' );

		if ( $form_id !== $parent_form_id ) {
			// Do not add autocomplete attribute to a name field inside repeater.
			return $attrs;
		}

		$field_id = (int) ( is_array( $args['field'] ) ? $args['field']['id'] : $args['field']->id );

		if ( intval( self::$first_name_field_ids[ $form_id ] ) === $field_id ) {
			if ( 'first' === $sub_field['name'] ) {
				$attrs['autocomplete'] = 'given-name';
			} elseif ( 'last' === $sub_field['name'] ) {
				$attrs['autocomplete'] = 'family-name';
			}
		}

		return $attrs;
	}
}
