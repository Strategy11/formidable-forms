<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
abstract class FrmFieldType {

	/**
	 * @var array|int|object
	 * @since 3.0
	 */
	protected $field;

	/**
	 * @var int
	 * @since 3.0
	 */
	protected $field_id = 0;

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type;

	/**
	 * Does the html for this field label need to include "for"?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = true;

	/**
	 * Does the field include a input box to type into?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = true;

	/**
	 * Is the HTML customizable?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $has_html = true;

	/**
	 * Could this field hold email values?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = false;

	/**
	 * Does this field show on the page?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $normal_field = true;

	/**
	 * Is this field a lot taller than the submit button?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $is_tall = false;

	/**
	 * Does this type support array values (like a checkbox or a name field).
	 *
	 * @var bool
	 * @since 6.2
	 */
	protected $array_allowed = true;

	/**
	 * @var bool|null Whether or not draft fields should be hidden on the front end.
	 */
	private static $should_hide_draft_fields;

	/**
	 * @since 6.10
	 *
	 * @var array|null
	 */
	private static $all_field_types;

	/**
	 * @param array|int|object $field
	 * @param string           $type
	 */
	public function __construct( $field = 0, $type = '' ) {
		$this->field = $field;
		$this->set_type( $type );
		$this->set_field_id();
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function __get( $key ) {
		$value = '';
		if ( property_exists( $this, $key ) ) {
			$value = $this->{$key};
		}

		return $value;
	}

	/**
	 * @param string $type
	 */
	protected function set_type( $type ) {
		if ( empty( $this->type ) ) {
			$this->type = $this->get_field_column( 'type' );
			if ( empty( $this->type ) && ! empty( $type ) ) {
				$this->type = $type;
			}
		}
	}

	/**
	 * @since 4.02
	 */
	protected function set_field_id() {
		if ( empty( $this->field ) ) {
			return;
		}

		if ( is_array( $this->field ) ) {
			$this->field_id = isset( $this->field['id'] ) ? $this->field['id'] : 0;
		} elseif ( is_object( $this->field ) && property_exists( $this->field, 'id' ) ) {
			$this->field_id = $this->field->id;
		} elseif ( is_numeric( $this->field ) ) {
			$this->field_id = $this->field;
		}
	}

	/**
	 * @param string $column
	 *
	 * @return array|string
	 */
	public function get_field_column( $column ) {
		$field_val = '';
		if ( is_object( $this->field ) ) {
			$field_val = $this->field->{$column};
		} elseif ( is_array( $this->field ) && isset( $this->field[ $column ] ) ) {
			$field_val = $this->field[ $column ];
		}

		return $field_val;
	}

	/**
	 * @param string $column
	 * @param mixed  $value
	 */
	public function set_field_column( $column, $value ) {
		if ( is_object( $this->field ) ) {
			$this->field->{$column} = $value;
		} elseif ( is_array( $this->field ) ) {
			$this->field[ $column ] = $value;
		}
	}

	/**
	 * @return array|int|object
	 */
	public function get_field() {
		return $this->field;
	}

	/**
	 * Field HTML
	 */
	public function default_html() {
		if ( ! $this->has_html ) {
			return '';
		}

		$input = $this->input_html();
		$for   = $this->for_label_html();
		$label = $this->primary_label_element();

		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
	<$label $for id="field_[key]_label" class="frm_primary_label">[field_name]
		<span class="frm_required" aria-hidden="true">[required_label]</span>
	</$label>
	$input
	[if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
	[if error]<div class="frm_error" role="alert" id="frm_error_field_[key]">[error]</div>[/if error]
</div>
DEFAULT_HTML;

		return $default_html;
	}

	protected function input_html() {
		return '[input]';
	}

	/**
	 * Creates a template for generating HTML containing multiple input fields enclosed in a div container.
	 *
	 * The placeholders [key] and [input] will be replaced dynamically during runtime.
	 *
	 * @see FrmFieldFormHtml->get_html() for the function handling the dynamic replacement.
	 *
	 * @return string The template HTML string for a div container with multiple input fields. This string is
	 *                prepared for dynamic replacement of the placeholders [key], and [input].
	 */
	protected function multiple_input_html() {
		return '<div class="frm_opt_container" aria-labelledby="field_[key]_label" role="group">[input]</div>';
	}

	protected function primary_label_element() {
		return $this->has_for_label ? 'label' : 'div';
	}

	protected function for_label_html() {
		if ( $this->has_for_label ) {
			$for = 'for="field_[key]"';
		} else {
			$for = '';
		}

		return $for;
	}

	/** Form builder **/

	/**
	 * @param string $name
	 */
	public function show_on_form_builder( $name = '' ) {
		$field        = FrmFieldsHelper::setup_edit_vars( $this->field );
		$include_file = $this->include_form_builder_file();

		if ( ! empty( $include_file ) ) {
			$this->include_on_form_builder( $name, $field );
		} elseif ( $this->has_input ) {
			echo $this->builder_text_field( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Shows field label on form builder.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public function show_label_on_form_builder() {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );
		?>
		<label class="frm_primary_label" id="field_label_<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo FrmAppHelper::kses( force_balance_tags( $field['name'] ), 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="frm_required <?php echo esc_attr( FrmField::is_required( $field ) ? '' : 'frm_hidden' ); ?>">
				<?php echo esc_html( $field['required_indicator'] ); ?>
			</span>
			<span class="frm-sub-label frm-collapsed-label">
				<?php esc_html_e( '(Collapsed)', 'formidable' ); ?>
			</span>
		</label>
		<?php
	}

	/**
	 * Define parameters and include the field on form builder
	 *
	 * @since 3.0
	 *
	 * @param string $name
	 * @param array  $field
	 */
	protected function include_on_form_builder( $name, $field ) {
		$field_name = $this->html_name( $name );
		$html_id    = $this->html_id();
		$read_only  = isset( $field['read_only'] ) ? $field['read_only'] : 0;

		$field['html_name'] = $field_name;
		$field['html_id']   = $html_id;
		FrmAppHelper::unserialize_or_decode( $field['default_value'] );

		$display = $this->display_field_settings();
		include $this->include_form_builder_file();
	}

	/**
	 * @return string The file path to include on the form builder
	 */
	protected function include_form_builder_file() {
		return '';
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function builder_text_field( $name = '' ) {
		$read_only = FrmField::get_option( $this->field, 'read_only' );

		return '<input type="text" name="' . esc_attr( $this->html_name( $name ) ) . '" id="' . esc_attr( $this->html_id() ) . '" value="' . esc_attr( $this->get_field_column( 'default_value' ) ) . '" placeholder="' . esc_attr( FrmField::get_option( $this->field, 'placeholder' ) ) . '" ' . ( $read_only ? ' readonly="readonly" disabled="disabled"' : '' ) . ' />';
	}

	protected function html_name( $name = '' ) {
		$prefix = empty( $name ) ? 'item_meta' : $name;

		return $prefix . '[' . $this->get_field_column( 'id' ) . ']';
	}

	protected function html_id( $plus = '' ) {
		return apply_filters( 'frm_field_get_html_id', 'field_' . $this->get_field_column( 'field_key' ) . $plus, $this->field );
	}

	public function display_field_settings() {
		$default_settings    = $this->default_field_settings();
		$field_type_settings = $this->field_settings_for_type();

		return array_merge( $default_settings, $field_type_settings );
	}

	/**
	 * @return array
	 */
	protected function default_field_settings() {
		return array(
			'type'           => $this->type,
			'label'          => true,
			'required'       => true,
			'unique'         => false,
			'read_only'      => false,
			'description'    => true,
			'options'        => true,
			'label_position' => true,
			'invalid'        => false,
			'size'           => false,
			// Shows the placeholder option.
			'clear_on_focus' => false,
			'css'            => true,
			'conf_field'     => false,
			'max'            => true,
			'range'          => false,
			'captcha_size'   => false,
			'captcha_theme'  => false,
			'format'         => false,
			'show_image'     => false,
			'default'        => true,
		);
	}

	protected function field_settings_for_type() {
		$settings = array();
		if ( ! $this->has_input ) {
			$settings = $this->no_input_settings();
		}

		return $settings;
	}

	private function no_input_settings() {
		return array(
			'required'       => false,
			'description'    => false,
			'label_position' => false,
			'default'        => false,
		);
	}

	/**
	 * Get a list of all field settings that should be translated
	 * on a multilingual site.
	 *
	 * @since 3.06.01
	 */
	public function translatable_strings() {
		return array(
			'name',
			'description',
			'default_value',
			'placeholder',
			'required_indicator',
			'invalid',
			'blank',
			'unique_msg',
		);
	}

	public function form_builder_classes( $display_type ) {
		$classes = 'form-field edit_form_item frm_field_box frm_top_container frm_not_divider edit_field_type_' . $display_type;

		return $this->alter_builder_classes( $classes );
	}

	protected function alter_builder_classes( $classes ) {
		return $classes;
	}

	/**
	 * @since 3.01.01
	 */
	public function show_options( $field, $display, $values ) {
		do_action( 'frm_' . $field['type'] . '_field_options_form', $field, $display, $values );
	}

	/**
	 * @since 4.0
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 * @return void
	 */
	public function show_primary_options( $args ) {
		do_action( 'frm_' . $args['field']['type'] . '_primary_field_options', $args );
	}

	/**
	 * Add and remove choices in a radio, checkbox, dropdown.
	 *
	 * @since 4.02.01
	 *
	 * @param array $args - Includes field, display, and values.
	 */
	public function show_field_choices( $args ) {
		if ( ! $this->has_field_choices( $args['field'] ) ) {
			return;
		}

		$this->field_choices_heading( $args );

		echo '<div class="frm_grid_container frm-collapse-me' . esc_attr( $this->extra_field_choices_class() ) . '">';
		$this->show_priority_field_choices( $args );
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-choices.php';
		$this->show_extra_field_choices( $args );
		echo '</div>';
	}

	/**
	 * @since 4.04
	 */
	public function show_field_options( $args ) {
		if ( ! $this->should_continue_to_field_options( $args ) ) {
			return;
		}

		$has_options = ! empty( $args['field']['options'] );
		$short_name  = FrmAppHelper::truncate( strip_tags( str_replace( '"', '&quot;', $args['field']['name'] ) ), 20 );

		/* translators: %s: Field name */
		$option_title = sprintf( __( '%s Options', 'formidable' ), $short_name );

		$display_format = FrmField::get_option( $args['field'], 'image_options' );

		/**
		 * Allows updating a flag that determines whether Bulk edit option should be visible on page load.
		 *
		 * @since 6.8.4
		 *
		 * @param bool   $should_hide_bulk_edit
		 * @param string $display_format
		 * @param array  $args
		 */
		$should_hide_bulk_edit = apply_filters( 'frm_should_hide_bulk_edit', $display_format === '1', $display_format, $args );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-options.php';
	}

	/**
	 * Allows adding extra html attributes to field default value setting field.
	 *
	 * @since 6.0
	 *
	 * @param array $field
	 *
	 * @return void
	 */
	public function echo_field_default_setting_attributes( $field ) {}

	/**
	 * @param array  $field
	 * @param object $field_obj
	 * @param array  $default_value_types
	 * @param array  $display
	 *
	 * @return void
	 */
	public function show_default_value_setting( $field, $field_obj, $default_value_types, $display ) {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/default-value-setting.php';
	}

	/**
	 * @param array $field
	 *
	 * @return void
	 */
	public function display_smart_values_modal_trigger_icon( $field ) {
		$special_default = ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' ) || $field['type'] === 'data';
		FrmAppHelper::icon_by_class(
			'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
			array(
				'data-open' => $special_default ? 'frm-tax-box-' . $field['id'] : 'frm-smart-values-box',
				'title'     => esc_attr__( 'Toggle Options', 'formidable' ),
			)
		);
	}

	/**
	 * @since 6.0
	 *
	 * @param array  $field
	 * @param string $default_name
	 * @param mixed  $default_value
	 *
	 * @return void
	 */
	public function show_default_value_field( $field, $default_name, $default_value ) {
		if ( $field['type'] === 'rte' ) {
			// This function is overwritten in Pro. This check is for backwards compatibility.
			include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/textarea-default-value-field.php';
			return;
		}

		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/default-value-field.php';
	}

	/**
	 * @since 4.04
	 *
	 * @param array $args
	 * @return bool
	 */
	protected function should_continue_to_field_options( $args ) {
		return in_array( $args['field']['type'], array( 'select', 'radio', 'checkbox' ), true );
	}

	/**
	 * @since 4.04
	 */
	protected function get_bulk_edit_string() {
		return __( 'Bulk Edit Options', 'formidable' );
	}

	/**
	 * @since 4.04
	 */
	protected function get_add_option_string() {
		return __( 'Add Option', 'formidable' );
	}

	/**
	 * @since 4.04
	 */
	protected function show_single_option( $args ) {
		FrmFieldsHelper::show_single_option( $args['field'] );
	}

	/**
	 * @since 4.04
	 */
	protected function extra_field_choices_class() {
		return '';
	}

	/**
	 * Should the section for adding choices show for this field?
	 *
	 * @since 4.02.01
	 */
	protected function has_field_choices( $field ) {
		return ! empty( $this->displayed_field_type( $field ) );
	}

	/**
	 * Get the type of field being displayed for lookups and dynamic fields.
	 *
	 * @since 4.02.01
	 * @return array
	 */
	public function displayed_field_type( $field ) {
		$display_type = array(
			'radio'    => FrmField::is_field_type( $field, 'radio' ),
			'checkbox' => FrmField::is_field_type( $field, 'checkbox' ),
			'select'   => FrmField::is_field_type( $field, 'select' ),
			'lookup'   => FrmField::is_field_type( $field, 'lookup' ),
			'data'     => FrmField::is_field_type( $field, 'data' ),
		);
		return array_filter( $display_type );
	}

	/**
	 * @since 4.02.01
	 *
	 * @param array $args
	 * @return void
	 */
	protected function field_choices_heading( $args ) {
		$all_field_types = self::get_all_field_types();
		?>
		<h3 <?php $this->field_choices_heading_attrs( $args ); ?>>
			<?php
			printf(
				/* translators: %s: Field type */
				esc_html__( '%s Options', 'formidable' ),
				esc_html( $all_field_types[ $args['display']['type'] ]['name'] )
			);
			FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) );
			?>
		</h3>
		<?php
	}

	/**
	 * Store $all_field_types in memory on first call and re-use it to improve the performance of the form builder.
	 *
	 * @since 6.10
	 *
	 * @return array
	 */
	private static function get_all_field_types() {
		if ( ! isset( self::$all_field_types ) ) {
			self::$all_field_types = array_merge( FrmField::pro_field_selection(), FrmField::field_selection() );
		}
		return self::$all_field_types;
	}

	/**
	 * @since 4.04
	 *
	 * @param array $args
	 * @return void
	 */
	protected function field_choices_heading_attrs( $args ) {
		return;
	}

	/**
	 * Show settings above the multiple options settings.
	 *
	 * @since 4.06
	 *
	 * @param array $args
	 * @return void
	 */
	protected function show_priority_field_choices( $args = array() ) {
		return;
	}

	/**
	 * This is called for any fields with set options (radio, checkbox, select, dynamic, lookup).
	 *
	 * @since 4.0
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 * @return void
	 */
	public function show_extra_field_choices( $args ) {
		return;
	}

	/**
	 * This is called right after the default value settings.
	 *
	 * @since 4.0
	 *
	 * @param array $args - Includes 'field', 'display'.
	 * @return void
	 */
	public function show_after_default( $args ) {
		return;
	}

	/**
	 * @since 4.0
	 */
	public function default_value_to_string( &$default_value ) {
		if ( ! is_array( $default_value ) ) {
			return;
		}

		$is_empty = array_filter( $default_value );
		if ( empty( $is_empty ) ) {
			$default_value = '';
		} else {
			$default_value = implode( ',', $default_value );
		}
	}

	/**
	 * @since 4.0
	 * @param array $args Includes 'field', 'display', and 'values'.
	 */
	protected function auto_width_setting( $args ) {
		$use_style = ( ! isset( $args['values']['custom_style'] ) || $args['values']['custom_style'] );
		if ( $use_style ) {
			$field = $args['field'];
			include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/automatic-width.php';
		}
	}

	/**
	 * New field
	 */
	public function get_new_field_defaults() {
		$field        = array(
			'name'          => $this->get_new_field_name(),
			'description'   => '',
			'type'          => $this->type,
			'options'       => '',
			'default_value' => '',
			'required'      => false,
			'field_options' => $this->get_default_field_options(),
		);

		$field_options = $this->new_field_settings();

		return array_merge( $field, $field_options );
	}

	/**
	 * Get the default field name when a field is inserted into a form.
	 *
	 * @return string
	 */
	protected function get_new_field_name() {
		$name       = __( 'Untitled', 'formidable' );
		$fields     = FrmField::field_selection();
		$pro_fields = FrmField::pro_field_selection();

		// As the credit card field is in Lite now, we want the name from the Lite array.
		// The pro key would is still set for backward compatibility.
		unset( $pro_fields['credit_card'] );

		$fields = array_merge( $fields, $pro_fields );

		if ( isset( $fields[ $this->type ] ) ) {
			$name = is_array( $fields[ $this->type ] ) ? $fields[ $this->type ]['name'] : $fields[ $this->type ];
		}

		return $name;
	}

	/**
	 * @return array
	 */
	protected function new_field_settings() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_default_field_options() {
		$opts        = array(
			'size'               => '',
			'max'                => '',
			'label'              => '',
			'blank'              => FrmFieldsHelper::default_blank_msg(),
			'required_indicator' => '*',
			'invalid'            => '',
			'unique_msg'         => '',
			'separate_value'     => 0,
			'clear_on_focus'     => 0,
			'classes'            => '',
			'custom_html'        => '',
			'minnum'             => 1,
			'maxnum'             => 10,
			'step'               => 1,
			'format'             => '',
			'placeholder'        => '',
			'draft'              => 0,
		);
		$field_opts  = $this->extra_field_opts();
		$opts        = array_merge( $opts, $field_opts );
		$filter_args = array(
			'field' => $this->field,
			'type'  => $this->type,
		);

		return apply_filters( 'frm_default_field_options', $opts, $filter_args );
	}

	/**
	 * @return array
	 */
	protected function extra_field_opts() {
		return array();
	}

	/** Show on front-end **/

	/**
	 * @param array $values
	 * @param array $atts
	 *
	 * @return array
	 */
	public function prepare_front_field( $values, $atts ) {
		$values['value'] = $this->prepare_field_value( $values['value'], $atts );

		return $values;
	}

	/**
	 * @since 3.03.03
	 *
	 * @param mixed $value
	 * @param array $atts
	 *
	 * @return mixed
	 */
	public function prepare_field_value( $value, $atts ) {
		return $value;
	}

	/**
	 * @param array $values
	 *
	 * @return array
	 */
	public function get_options( $values ) {
		if ( empty( $values ) ) {
			$values = (array) $this->field;
		}

		FrmAppHelper::unserialize_or_decode( $values['options'] );
		return $values['options'];
	}

	/**
	 * @param array $args {
	 *    Details about the field to show.
	 *
	 *    @type array $field
	 *    @type array $errors
	 *    @type object $form
	 *    @type object $form_action
	 * }
	 *
	 * @return void
	 */
	public function show_field( $args ) {
		if ( apply_filters( 'frm_show_normal_field_type', $this->normal_field, $this->type ) ) {
			echo $this->prepare_field_html( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			do_action( 'frm_show_other_field_type', $this->field, $args['form'], array( 'action' => $args['form_action'] ) );
		}
		$this->get_field_scripts_hook( $args );
	}

	/**
	 * @return void
	 */
	protected function get_field_scripts_hook( $args ) {
		$form_id = isset( $args['parent_form_id'] ) && $args['parent_form_id'] ? $args['parent_form_id'] : $args['form']->id;
		do_action( 'frm_get_field_scripts', $this->field, $args['form'], $form_id );
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function prepare_field_html( $args ) {
		if ( FrmField::get_option( $this->field, 'draft' ) && $this->should_hide_draft_field() ) {
			// A draft field is never shown on the front-end.
			return '';
		}

		$args = $this->fill_display_field_values( $args );
		if ( $this->has_html ) {
			$args['html']      = $this->before_replace_html_shortcodes( $args, FrmAppHelper::maybe_kses( FrmField::get_option( $this->field, 'custom_html' ) ) );
			$args['errors']    = is_array( $args['errors'] ) ? $args['errors'] : array();
			$args['field_obj'] = $this;

			$label = FrmFieldsHelper::label_position( $this->field['label'], $this->field, $args['form'] );
			$this->set_field_column( 'label', $label );

			$html_shortcode = new FrmFieldFormHtml( $args );
			$html           = $html_shortcode->get_html();
			$html           = $this->after_replace_html_shortcodes( $args, $html );
			$html_shortcode->remove_collapse_shortcode( $html );
		} else {
			$html = $this->include_front_field_input( $args, array() );
		}

		return $html;
	}

	/**
	 * A draft field can be previewed on the preview page for a user who can edit forms.
	 *
	 * @since 6.8
	 *
	 * @return bool
	 */
	private function should_hide_draft_field() {
		if ( ! isset( self::$should_hide_draft_fields ) ) {
			self::$should_hide_draft_fields = ! FrmAppHelper::is_preview_page() || ! current_user_can( 'frm_edit_forms' );
		}
		return self::$should_hide_draft_fields;
	}

	/**
	 * @param array  $args
	 * @param string $html
	 *
	 * @return string
	 */
	protected function before_replace_html_shortcodes( $args, $html ) {
		return $html;
	}

	protected function after_replace_html_shortcodes( $args, $html ) {
		return $html;
	}

	public function get_container_class() {
		$is_radio    = FrmField::is_radio( $this->field );
		$is_checkbox = FrmField::is_checkbox( $this->field );
		$align       = FrmField::get_option( $this->field, 'align' );

		$class = '';
		if ( ! empty( $align ) && ( $is_radio || $is_checkbox ) ) {
			self::prepare_align_class( $align );
			$class .= ' ' . $align;
		}

		return $class;
	}

	/**
	 * @since 4.0
	 *
	 * @param string $align
	 * @return void
	 */
	public function prepare_align_class( &$align ) {
		if ( 'inline' === $align ) {
			$align = 'horizontal_radio';
		} elseif ( 'block' === $align ) {
			$align = 'vertical_radio';
		}
	}

	/**
	 * @return string
	 */
	public function get_label_class() {
		return ' frm_primary_label';
	}

	/**
	 * Add classes to the input for output
	 *
	 * @since 3.02
	 *
	 * @return string
	 */
	protected function add_input_class() {
		$input_class   = FrmField::get_option( $this->field, 'input_class' );
		$extra_classes = $this->get_input_class();
		if ( ! empty( $extra_classes ) ) {
			$input_class .= ' ' . $extra_classes;
		}

		if ( is_object( $this->field ) ) {
			$this->field->field_options['input_class'] = $input_class;
		} else {
			$this->field['input_class'] = $input_class;
		}

		return $input_class;
	}

	/**
	 * Add extra classes on front-end input
	 *
	 * @since 3.02
	 *
	 * @return string
	 */
	protected function get_input_class() {
		return '';
	}

	/**
	 * @param array $args
	 * @param array $shortcode_atts
	 *
	 * @return string
	 */
	public function include_front_field_input( $args, $shortcode_atts ) {
		$include_file = $this->include_front_form_file();

		if ( ! empty( $include_file ) ) {
			$input = $this->include_on_front_form( $args, $shortcode_atts );
		} else {
			$input = $this->front_field_input( $args, $shortcode_atts );
		}

		$this->load_field_scripts( $args );

		return $input;
	}

	/**
	 * @return string
	 */
	protected function include_front_form_file() {
		return '';
	}

	protected function include_on_front_form( $args, $shortcode_atts ) {
		global $frm_vars;

		$include_file = $this->include_front_form_file();
		if ( empty( $include_file ) ) {
			return;
		}

		$hidden = $this->maybe_include_hidden_values( $args );

		$field      = $this->field;
		$html_id    = $args['html_id'];
		$field_name = $args['field_name'];
		$read_only  = FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin();
		// Lighten up on memory usage.
		unset( $args['form'] );

		ob_start();
		include $include_file;
		$input_html = ob_get_contents();
		ob_end_clean();

		return $hidden . $input_html;
	}

	/**
	 * @param array $args
	 * @param array $shortcode_atts
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$field_type = $this->html5_input_type();
		$input_html = $this->get_field_input_html_hook( $this->field );
		$this->add_aria_description( $args, $input_html );
		$this->add_extra_html_atts( $args, $input_html );

		return '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $args['html_id'] ) . '" name="' . esc_attr( $args['field_name'] ) . '" value="' . esc_attr( $this->prepare_esc_value() ) . '" ' . $input_html . '/>';
	}

	protected function html5_input_type() {
		$frm_settings = FrmAppHelper::get_settings();

		return $frm_settings->use_html ? $this->type : 'text';
	}

	/**
	 * If the value includes intentional entities, don't lose them.
	 *
	 * @since 4.03.01
	 *
	 * @return string
	 */
	protected function prepare_esc_value() {
		$value = $this->field['value'];
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
		if ( strpos( $value, '&lt;' ) !== false ) {
			$value = htmlentities( $value );
		}
		return $value;
	}

	/**
	 * Add paramters to an input value as an alterntative to
	 * using the frm_field_input_html hook
	 *
	 * @since 3.01.03
	 */
	protected function add_extra_html_atts( $args, &$input_html ) {
		// override from other fields
	}

	/**
	 * @since 3.01.03
	 *
	 * @return void
	 */
	protected function add_min_max( $args, &$input_html ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( ! $frm_settings->use_html ) {
			return;
		}

		$min = FrmField::get_option( $this->field, 'minnum' );
		if ( ! is_numeric( $min ) ) {
			$min = 0;
		}

		$max = FrmField::get_option( $this->field, 'maxnum' );
		if ( ! is_numeric( $max ) ) {
			$max = 9999999;
		}

		$step = FrmField::get_option( $this->field, 'step' );
		if ( ! is_numeric( $step ) && $step !== 'any' ) {
			$step = 1;
		}

		$input_html .= ' min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '"';
	}

	protected function maybe_include_hidden_values( $args ) {
		$hidden       = '';
		$is_read_only = FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin();
		if ( $is_read_only && $this->show_readonly_hidden() ) {
			$hidden = $this->show_hidden_values( $args );
		}

		return $hidden;
	}

	/**
	 * When the field is read only, does it need it include hidden fields?
	 * Checkboxes and dropdowns need this
	 */
	protected function show_readonly_hidden() {
		return false;
	}

	/**
	 * When the field has a single value, should the name include
	 * name[] to indicate an array?
	 */
	protected function is_readonly_array() {
		return false;
	}

	protected function show_hidden_values( $args ) {
		$selected_value = isset( $args['field_value'] ) ? $args['field_value'] : $this->field['value'];
		$hidden         = '';
		if ( is_array( $selected_value ) ) {
			$args['save_array'] = true;
			foreach ( $selected_value as $selected ) {
				$hidden .= $this->show_single_hidden( $selected, $args );
			}
		} else {
			$args['save_array'] = $this->is_readonly_array();
			$hidden            .= $this->show_single_hidden( $selected_value, $args );
		}

		return $hidden;
	}

	protected function show_single_hidden( $selected, $args ) {
		if ( $args['save_array'] ) {
			$args['field_name'] .= '[]';
			$id                  = '';
		} else {
			$id = ' id="' . esc_attr( $args['html_id'] ) . '"';
		}

		return '<input type="hidden" value="' . esc_attr( $selected ) . '" name="' . esc_attr( $args['field_name'] ) . '" ' . $id . ' />';
	}

	/**
	 * @since 3.0
	 */
	protected function get_select_box( $values ) {
		$options  = $this->get_field_column( 'options' );
		$selected = $values['field_value'];

		if ( isset( $values['combo_name'] ) ) {
			$options  = $options[ $values['combo_name'] ];
			$selected = is_array( $selected ) && isset( $selected[ $values['combo_name'] ] ) ? $selected[ $values['combo_name'] ] : '';
		}

		$input = $this->select_tag( $values );

		foreach ( $options as $option ) {
			$input .= '<option value="' . esc_attr( $option ) . '" ' . selected( $selected, $option, false ) . '>';
			$input .= esc_html( $option );
			$input .= '</option>';
		}
		$input .= '</select>';

		return $input;
	}

	/**
	 * @since 3.0
	 */
	protected function select_tag( $values ) {
		$field       = isset( $values['field'] ) ? $values['field'] : $this->field;
		$input_html  = $this->get_field_input_html_hook( $field );
		$select_atts = $this->get_select_atributes( $values );
		$select      = FrmAppHelper::array_to_html_params( $select_atts ) . ' ';

		return '<select' . $select . $input_html . '>';
	}

	/**
	 * @since 3.0
	 */
	protected function get_select_atributes( $values ) {
		$readonly    = ( FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin() );
		$select_atts = array();
		if ( ! $readonly ) {
			if ( isset( $values['combo_name'] ) ) {
				$values['field_name'] .= '[' . $values['combo_name'] . ']';
				$values['html_id']    .= '_' . $values['combo_name'];
			}

			$select_atts['name'] = $values['field_name'];
			$select_atts['id']   = $values['html_id'];
		}

		return $select_atts;
	}

	protected function load_field_scripts( $args ) {
		// Override me
	}

	protected function fill_display_field_values( $args = array() ) {
		$defaults        = array(
			'field_name'    => 'item_meta[' . $this->get_field_column( 'id' ) . ']',
			'field_id'      => $this->get_field_column( 'id' ),
			'field_plus_id' => '',
			'section_id'    => '',
		);
		$args            = wp_parse_args( $args, $defaults );
		$args['html_id'] = $this->html_id( $args['field_plus_id'] );

		if ( FrmField::is_multiple_select( $this->field ) ) {
			$args['field_name'] .= '[]';
		}

		return $args;
	}

	protected function get_field_input_html_hook( $field ) {
		$field['input_class'] = $this->add_input_class();

		ob_start();
		do_action( 'frm_field_input_html', $field );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	/**
	 * Link input to field description for screen readers
	 *
	 * @since 3.0
	 *
	 * @param array  $args
	 * @param string $input_html
	 * @return void
	 */
	protected function add_aria_description( $args, &$input_html ) {
		$aria_describedby_exists = preg_match_all( '/aria-describedby=\"([^\"]*)\"/', $input_html, $matches ) === 1;
		if ( $aria_describedby_exists ) {
			$describedby = preg_split( '/\s+/', esc_attr( trim( $matches[1][0] ) ) );
		} else {
			$describedby = array();
		}

		$error_comes_first = true;

		$custom_error_fields = preg_grep( '/frm_error_field_*/', $describedby );
		$custom_desc_fields  = preg_grep( '/frm_desc_field_*/', $describedby );

		if ( $custom_desc_fields && $custom_error_fields ) {
			reset( $custom_error_fields );
			reset( $custom_desc_fields );
			if ( key( $custom_error_fields ) > key( $custom_desc_fields ) ) {
				$error_comes_first = false;
			}
		}

		if ( isset( $args['errors'][ 'field' . $args['field_id'] ] ) && ! $custom_error_fields ) {
			if ( $error_comes_first ) {
				array_unshift( $describedby, 'frm_error_' . $args['html_id'] );
			} else {
				array_push( $describedby, 'frm_error_' . $args['html_id'] );
			}
		}

		if ( $this->get_field_column( 'description' ) !== '' && ! in_array( 'frm_desc_' . $args['html_id'], $describedby, true ) ) {
			if ( ! $error_comes_first ) {
				array_unshift( $describedby, 'frm_desc_' . $args['html_id'] );
			} else {
				array_push( $describedby, 'frm_desc_' . $args['html_id'] );
			}
		}

		$describedby = implode( ' ', $describedby );

		if ( $aria_describedby_exists ) {
			$input_html = preg_replace( '/aria-describedby=\"[^\"]*\"/', 'aria-describedby="' . $describedby . '"', $input_html );
		} elseif ( $describedby ) {
			$input_html .= ' aria-describedby="' . esc_attr( trim( $describedby ) ) . '"';
		}

		if ( ! $error_comes_first ) {
			$input_html .= ' data-error-first="0"';
		}
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function validate( $args ) {
		return array();
	}

	/**
	 * @since 4.02
	 */
	public function maybe_trim_excess_values( &$value ) {
		// Override in a child class.
	}

	/**
	 * A field is not unique if it has already been passed to this function, or if it exists in meta for this field but another entry id
	 *
	 * @param mixed $value
	 * @param int   $entry_id
	 * @return bool
	 */
	public function is_not_unique( $value, $entry_id ) {
		if ( $this->value_has_already_been_validated_as_unique( $value ) ) {
			return true;
		}

		if ( $this->value_exists_in_meta_for_another_entry( $value, $entry_id ) ) {
			return true;
		}

		$this->value_validated_as_unique( $value );
		return false;
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	private function value_has_already_been_validated_as_unique( $value ) {
		global $frm_validated_unique_values;

		if ( empty( $frm_validated_unique_values ) ) {
			$frm_validated_unique_values = array();
			return false;
		}

		$field_id = $this->get_field_column( 'id' );
		if ( ! array_key_exists( $field_id, $frm_validated_unique_values ) ) {
			$frm_validated_unique_values[ $field_id ] = array();
			return false;
		}

		$already_validated_this_value = in_array( $value, $frm_validated_unique_values[ $field_id ], true );
		return $already_validated_this_value;
	}

	/**
	 * @param mixed $value
	 * @param int   $entry_id
	 * @return bool
	 */
	private function value_exists_in_meta_for_another_entry( $value, $entry_id ) {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return false;
		}
		$field_id = $this->get_field_column( 'id' );
		return FrmProEntryMetaHelper::value_exists( $field_id, $value, $entry_id );
	}

	/**
	 * Track that a value has been flagged as unique so that no other iterations can be for the same value for this field
	 *
	 * @param mixed $value
	 */
	private function value_validated_as_unique( $value ) {
		global $frm_validated_unique_values;
		$field_id                                   = $this->get_field_column( 'id' );
		$frm_validated_unique_values[ $field_id ][] = $value;
	}

	/**
	 * @param array|string $value
	 * @param array        $atts
	 * @return array|string
	 */
	public function get_value_to_save( $value, $atts ) {
		return $value;
	}

	/**
	 * Prepare value last thing before saving in the db
	 *
	 * @param array|string $value
	 *
	 * @return array|float|int|string
	 */
	public function set_value_before_save( $value ) {
		return $value;
	}

	/** Prepare value for display **/

	/**
	 * @param array|string $value
	 * @param array        $atts
	 *
	 * @return string
	 */
	public function get_display_value( $value, $atts = array() ) {
		$this->fill_default_atts( $atts );

		if ( $this->should_strip_most_html_before_preparing_display_value( $atts ) ) {
			FrmAppHelper::sanitize_value( 'FrmAppHelper::strip_most_html', $value );
		}

		$value = $this->prepare_display_value( $value, $atts );

		if ( is_array( $value ) ) {
			if ( isset( $atts['show'] ) && $atts['show'] && isset( $value[ $atts['show'] ] ) ) {
				$value = $value[ $atts['show'] ];
			} elseif ( ! isset( $atts['return_array'] ) || ! $atts['return_array'] ) {
				$sep   = isset( $atts['sep'] ) ? $atts['sep'] : ', ';
				$value = implode( $sep, $value );
			}
		}

		return $value;
	}

	/**
	 * @since 6.7.1
	 *
	 * @param array $atts
	 * @return bool
	 */
	protected function should_strip_most_html_before_preparing_display_value( $atts ) {
		if ( ! empty( $atts['keepjs'] ) ) {
			// Always keep JS if the option is set.
			return false;
		}

		if ( ! empty( $atts['entry'] ) ) {
			$entry = $atts['entry'];
		} elseif ( ! empty( $atts['entry_id'] ) ) {
			$entry = FrmEntry::getOne( $atts['entry_id'] );
		}

		return ! empty( $entry ) && is_object( $entry ) && $this->should_strip_most_html( $entry );
	}

	/**
	 * Only allow medium-risk HTML tags like a and img when an entry is created by or edited by a privileged user.
	 *
	 * @since 6.7.1
	 *
	 * @param stdClass $entry
	 * @return bool
	 */
	protected function should_strip_most_html( $entry ) {
		// In old versions of Pro, updated_by and user_id may both be missing.
		// This is because $entry may be an stdClass created in FrmProSummaryValues::base_entry.
		if ( ! empty( $entry->updated_by ) && $this->user_id_is_privileged( $entry->updated_by ) ) {
			return false;
		}
		if ( ! empty( $entry->user_id ) && $this->user_id_is_privileged( $entry->user_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if a user is allowed to save additional HTML (like a and img tags).
	 * HTML is stripped more strictly for users that are not logged in, or users that
	 * do not have access to editing entries in the back end.
	 *
	 * @since 6.8
	 *
	 * @param int|string $user_id
	 * @return bool
	 */
	private function user_id_is_privileged( $user_id ) {
		return user_can( $user_id, 'administrator' ) || user_can( $user_id, 'frm_edit_entries' );
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	protected function fill_default_atts( &$atts ) {
		$defaults = array(
			'sep' => ', ',
		);
		$atts     = wp_parse_args( $atts, $defaults );
	}

	/**
	 * @since 3.0
	 *
	 * @param array|string $value
	 * @param array        $atts
	 *
	 * @return array|string
	 */
	protected function prepare_display_value( $value, $atts ) {
		return $value;
	}

	/** Importing **/

	/**
	 * @param string $value
	 * @param array  $atts
	 *
	 * @return mixed
	 */
	public function get_import_value( $value, $atts = array() ) {
		return $this->prepare_import_value( $value, $atts );
	}

	/**
	 * @param string $value
	 * @param array  $atts
	 *
	 * @return mixed
	 */
	protected function prepare_import_value( $value, $atts ) {
		return $value;
	}

	/**
	 * Get the new child IDs for a repeating field's or embedded form's meta_value
	 *
	 * @since 3.0
	 *
	 * @param array|string $value
	 * @param array        $atts {
	 *     Details about the field to show.
	 *
	 *     @type array $meta_value
	 *     @type object $field
	 *     @type array $saved_entries
	 * }
	 *
	 * @return array $new_value
	 */
	protected function get_new_child_ids( $value, $atts ) {
		$saved_entries = $atts['ids'];
		$new_value     = array();
		foreach ( (array) $value as $old_child_id ) {
			if ( isset( $saved_entries[ $old_child_id ] ) ) {
				$new_value[] = $saved_entries[ $old_child_id ];
			}
		}

		return $new_value;
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 */
	protected function get_multi_opts_for_import( $value ) {
		if ( ! $this->field || ! $value || in_array( $value, (array) $this->field->options ) ) {
			return $value;
		}

		$checked = $value;
		FrmAppHelper::unserialize_or_decode( $checked );

		if ( ! is_array( $checked ) ) {
			$filtered_checked   = $checked;
			$csv_values_checked = array();

			$options = (array) $this->field->options;
			$options = array_reverse( $options );

			foreach ( $options as $option ) {
				if ( isset( $option['value'] ) && strpos( $filtered_checked, $option['value'] ) !== false ) {
					$csv_values_checked[] = $option['value'];
					$filtered_checked     = str_replace( $option['value'], '', $filtered_checked );
				}
			}

			$csv_values_checked = array_reverse( $csv_values_checked );

			$checked = array_merge( $csv_values_checked, array_filter( explode( ',', $filtered_checked ) ) );
		}

		if ( ! empty( $checked ) && count( $checked ) > 1 ) {
			$value = array_map( 'trim', $checked );
		}

		return $value;
	}

	/**
	 * @param array|string $value
	 * @param array        $defaults
	 */
	protected function fill_values( &$value, $defaults ) {
		if ( empty( $value ) ) {
			$value = $defaults;
		} else {
			$value = array_merge( $defaults, (array) $value );
		}
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_with_html( $value );
	}

	/**
	 * Maybe adjust a field value based on type.
	 * Some types require unserializing an array (including checkbox, name, address, credit_card, select, file, lookup, data, product).
	 * If a type does not require it, $this->array_allowed = false can be set to avoid the unserialize call.
	 *
	 * @since 6.2
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function maybe_decode_value( $value ) {
		if ( $this->has_input && $this->array_allowed ) {
			FrmAppHelper::unserialize_or_decode( $value );
		}
		return $value;
	}

	/**
	 * @deprecated 6.8.3
	 *
	 * @return string
	 */
	protected function default_unique_msg() {
		_deprecated_function( __METHOD__, '6.8.3', 'FrmFieldsHelper::default_unique_msg' );
		$frm_settings = FrmAppHelper::get_settings();
		$message      = $frm_settings->unique_msg;
		return $message;
	}

	/**
	 * @deprecated 6.8.3
	 *
	 * @return string
	 */
	protected function default_invalid_msg() {
		_deprecated_function( __METHOD__, '6.8.3', 'FrmFieldsHelper::default_invalid_msg' );
		return FrmFieldsHelper::default_invalid_msg();
	}
}
