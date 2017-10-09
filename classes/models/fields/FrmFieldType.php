<?php

/**
 * @since 3.0
 */
abstract class FrmFieldType {

	/**
	 * @var object|array|int
	 * @since 3.0
	 */
	protected $field;

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type;

	/**
	 * Does the html for this field label need to include "for"?
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = false;

	/**
	 * Does the field include a input box to type into?
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = true;

	/**
	 * Is the HTML customizable?
	 * @var bool
	 * @since 3.0
	 */
	protected $has_html = true;

	/**
	 * Could this field hold email values?
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = false;

	/**
	 * Does this field show on the page?
	 * @var bool
	 * @since 3.0
	 */
	protected $normal_field = true;

	public function __construct( $field = 0, $type = '' ) {
		$this->field = $field;
		$this->set_type( $type );
	}

	/**
	 * @param $key
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
	 * @param $type string
	 */
	protected function set_type( $type ) {
		if ( empty( $this->type ) ) {
			$this->type = $this->get_field_column('type');
			if ( empty( $this->type ) && ! empty( $type ) ) {
				$this->type = $type;
			}
		}
	}

	/**
	 * @param $column
	 *
	 * @return string|array
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
	 * @param mixed $value
	 */
	public function set_field_column( $column, $value ) {
		if ( is_object( $this->field ) ) {
			$this->field->{$column} = $value;
		} elseif ( is_array( $this->field ) ) {
			$this->field[ $column ] = $value;
		}
	}

	/**
	 *
	 * @return object|array
	 */
	public function get_field() {
		return $this->field;
	}

	/** Field HTML **/

	public function default_html() {
		if ( ! $this->has_html ) {
			return '';
		}

		$input = $this->input_html();
		$for = $this->for_label_html();

		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
    <label $for class="frm_primary_label">[field_name]
        <span class="frm_required">[required_label]</span>
    </label>
    $input
    [if description]<div class="frm_description">[description]</div>[/if description]
    [if error]<div class="frm_error">[error]</div>[/if error]
</div>
DEFAULT_HTML;

		return $default_html;
	}

	protected function input_html() {
		return '[input]';
	}

	protected function multiple_input_html() {
		return '<div class="frm_opt_container">[input]</div>';
	}

	private function for_label_html() {
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
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );
		$include_file = $this->include_form_builder_file();

		if ( ! empty( $include_file ) ) {
			$this->include_on_form_builder( $name, $field );
		} else {
			echo $this->builder_text_field();
		}
	}

	/**
	 * Define parameters and include the field on form builder
	 *
	 * @since 3.0
	 *
	 * @param string $name
	 * @param array $field
	 */
	protected function include_on_form_builder( $name, $field ) {
		$field_name = $this->html_name( $name );
		$html_id = $this->html_id();
		$display = $this->display_field_settings();
		include( $this->include_form_builder_file() );
	}

	/**
	 * @return string The file path to include on the form builder
	 */
	protected function include_form_builder_file() {
		return '';
	}

	protected function builder_text_field() {
		return '<input type="text" name="' . esc_attr( $this->html_name() ) . '" id="' . esc_attr( $this->html_id() ) . '" value="' . esc_attr( $this->get_field_column('default_value') ) . '" class="dyn_default_value" />';
	}

	protected function html_name( $name = '' ) {
		$prefix = empty( $name ) ? 'item_meta' : $name;
		return $prefix . '[' . $this->get_field_column('id') . ']';
	}

	protected function html_id( $plus = '' ) {
		return apply_filters( 'frm_field_get_html_id', 'field_' . $this->get_field_column('field_key') . $plus, $this->field );
    }

	public function display_field_settings() {
		$default_settings = $this->default_field_settings();
		$field_type_settings = $this->field_settings_for_type();
		return array_merge( $default_settings, $field_type_settings );
	}

	protected function default_field_settings() {
		return array(
			'type'         => $this->type,
			'required'     => true,
			'unique'       => false,
			'read_only'    => false,
			'description'  => true,
			'options'      => true,
			'label_position' => true,
			'invalid'      => false,
			'size'         => false,
			'clear_on_focus' => false,
			'default_blank' => true,
			'css'          => true,
			'conf_field'   => false,
			'max'          => true,
			'range'        => false,
			'captcha_size' => false,
			'format'       => false,
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
			'default_blank'  => false,
			'required'       => false,
			'description'    => false,
			'label_position' => false,
		);
	}

	public function form_builder_classes( $display_type ) {
		$classes = 'form-field edit_form_item frm_field_box frm_top_container frm_not_divider edit_field_type_' . $display_type;
		return $this->alter_builder_classes( $classes );
	}

	protected function alter_builder_classes( $classes ) {
		return $classes;
	}

	/** New field **/

	public function get_new_field_defaults() {
		$frm_settings = FrmAppHelper::get_settings();
		$field = array(
			'name'          => $this->get_new_field_name(),
			'description'   => '',
			'type'          => $this->type,
			'options'       => '',
			'default_value' => '',
			'required'      => false,
			'blank'         => $frm_settings->blank_msg,
			'unique_msg'    => $this->default_unique_msg(),
			'invalid'       => $this->default_invalid_msg(),
			'field_options' => $this->get_default_field_options(),
		);

		$field_options = $this->new_field_settings();
		return array_merge( $field, $field_options );
	}

	protected function default_unique_msg() {
		if ( is_object( $this->field ) && ! FrmField::is_option_true( $this->field, 'unique' ) ) {
			$message = '';
		} else {
			$frm_settings = FrmAppHelper::get_settings();
			$message = $frm_settings->unique_msg;
		}

		return $message;
	}

	protected function default_invalid_msg() {
		$field_name = $this->get_field_column('name');
		if ( $field_name == '' ) {
			$invalid = __( 'This field is invalid', 'formidable' );
		} else {
			$invalid = sprintf( __( '%s is invalid', 'formidable' ), $field_name );
		}

		return $invalid;
	}

	protected function get_new_field_name() {
		$name = __( 'Untitled', 'formidable' );

		$fields = FrmField::field_selection();
		$fields = array_merge( $fields, FrmField::pro_field_selection() );

		if ( isset( $fields[ $this->type ] ) ) {
			$name = is_array( $fields[ $this->type ] ) ? $fields[ $this->type ]['name'] : $fields[ $this->type ];
		}

		return $name;
	}

	protected function new_field_settings() {
		return array();
	}

	public function get_default_field_options() {
		$opts = array(
			'size'    => '',
			'max'     => '',
			'label'   => '',
			'blank'   => '',
			'required_indicator' => '*',
			'invalid' => '',
			'separate_value' => 0,
			'clear_on_focus' => 0,
			'default_blank' => 0,
			'classes' => '',
			'custom_html' => '',
			'minnum'  => 1,
			'maxnum'  => 10,
			'step'    => 1,
			'format'  => '',
		);
		$field_opts = $this->extra_field_opts();
		$opts = array_merge( $opts, $field_opts );

		$opts = apply_filters( 'frm_default_field_options', $opts, array( 'field' => $this->field, 'type' => $this->type ) );

		if ( $this->field ) {
			if ( has_filter( 'frm_default_field_opts' ) || has_filter( 'frm_default_' . $this->type . '_field_opts' ) ) {
				$values = FrmFieldsHelper::field_object_to_array( $this->field );
				$opts = apply_filters( 'frm_default_field_opts', $opts, $values, $this->field );
				$opts = apply_filters( 'frm_default_' . $this->type . '_field_opts', $opts, $values, $this->field );
			}
		}

		return $opts;
	}

	protected function extra_field_opts() {
		return array();
	}

	/** Show on front-end **/
	/**
	 * @param array $args ($field, $errors, $form, $form_action)
	 */
	public function show_field( $args ) {
		if ( apply_filters( 'frm_show_normal_field_type', $this->normal_field, $this->type ) ) {
			echo $this->prepare_field_html( $args );
		} else {
			do_action( 'frm_show_other_field_type', $this->field, $args['form'], array( 'action' => $args['form_action'] ) );
		}
		$this->get_field_scripts_hook( $args );
	}

	protected function get_field_scripts_hook( $args ) {
		$form_id = isset( $args['parent_field_id'] ) && $args['parent_field_id'] ? $args['parent_field_id'] : $args['form']->id;
		do_action( 'frm_get_field_scripts', $this->field, $args['form'], $form_id );
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function prepare_field_html( $args ) {
		$args = $this->fill_display_field_values( $args );

		if ( $this->has_html ) {
			$html = $this->field['custom_html'];
			$html = apply_filters( 'frm_before_replace_shortcodes', $html, $this->field, $args['errors'], $args['form'] );

			$args['errors'] = is_array( $args['errors'] ) ? $args['errors'] : array();
			$args['field_obj'] = $this;
			$this->field['label'] = FrmFieldsHelper::label_position( $this->field['label'], $this->field, $args['form'] );

			$html_shortcode = new FrmFieldFormHtml( $args );
			$html = $html_shortcode->get_html();
		} else {
			$html = $this->include_front_field_input( $args, array() );
		}
		return $html;
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

	protected function include_front_form_file() {
		return '';
	}

	protected function include_on_front_form( $args, $shortcode_atts ) {
		global $frm_vars;

		$field = $this->field;
		$html_id = $args['html_id'];
		$field_name = $args['field_name'];
		$read_only = FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin();
		unset( $args['form'] ); // lighten up on memory usage

		ob_start();
		include( $this->include_front_form_file() );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$field_type = $this->html5_input_type();
		$input_html = $this->get_field_input_html_hook( $this->field );

		return '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $args['html_id'] ) . '" name="' . esc_attr( $args['field_name'] ) . '" value="' . esc_attr( $this->field['value'] ) . '" ' . $input_html . '/>';
	}

	protected function html5_input_type() {
		$frm_settings = FrmAppHelper::get_settings();
		return $frm_settings->use_html ? $this->type : 'text';
	}

	protected function load_field_scripts( $args ) {
		// Override me
	}

	protected function fill_display_field_values( $args = array() ) {
		$defaults = array(
			'field_name'    => 'item_meta[' . $this->get_field_column('id') . ']',
			'field_id'      => $this->get_field_column('id'),
			'field_plus_id' => '',
			'section_id'    => '',
		);
		$args = wp_parse_args( $args, $defaults );
		$args['html_id'] = $this->html_id( $args['field_plus_id'] );

		if ( FrmField::is_multiple_select( $this->field ) ) {
			$args['field_name'] .= '[]';
		}

		return $args;
	}

	protected function get_field_input_html_hook( $field ) {
		ob_start();
		do_action( 'frm_field_input_html', $field );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	/** Prepare value for display **/

	/**
	 *
	 * @param string|array $value
	 * @param array $atts
	 *
	 * @return string
	 */
	public function get_display_value( $value, $atts = array() ) {
		$this->fill_default_atts( $atts );
		$value = $this->prepare_display_value( $value, $atts );

		if ( is_array( $value ) ) {
			if ( isset( $atts['show'] ) && $atts['show'] && isset( $value[ $atts['show'] ] ) ) {
				$value = $value[ $atts['show'] ];
			} elseif ( ! isset( $atts['return_array'] ) || ! $atts['return_array'] ) {
				$sep = isset( $atts['sep'] ) ? $atts['sep'] : ', ';
				$value = implode( $sep, $value );
			}
		}
		return $value;
	}

	protected function fill_default_atts( &$atts ) {
		$defaults = array(
			'sep' => ', ',
		);
		$atts = wp_parse_args( $atts, $defaults );
	}

	protected function prepare_display_value( $value, $atts ) {
		return $value;
	}

	/** Importing **/

	/**
	 * @param $value
	 * @param array $atts
	 *
	 * @return mixed
	 */
	public function get_import_value( $value, $atts = array() ) {
		return $this->prepare_import_value( $value, $atts );
	}

	/**
	 * @param $value
	 * @param $atts
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
	 * @param $value
	 * @param $atts
	 * @internal param array $meta_value
	 * @internal param object $field
	 * @internal param array $saved_entries
	 *
	 * @return array $new_value
	 */
	protected function get_new_child_ids( $value, $atts ) {
		$saved_entries = $atts['ids'];
		$new_value = array();
		foreach ( (array) $value as $old_child_id ) {
			if ( isset( $saved_entries[ $old_child_id ] ) ) {
				$new_value[] = $saved_entries[ $old_child_id ];
			}
		}

		return $new_value;
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	protected function get_multi_opts_for_import( $value ) {

		if ( ! $this->field || empty( $value ) || in_array( $value, (array) $this->field->options ) ) {
			return $value;
		}

		$checked = is_array( $value ) ? $value : maybe_unserialize( $value );

		if ( ! is_array( $checked ) ) {
			$checked = explode( ',', $checked );
		}

		if ( ! empty( $checked ) && count( $checked ) > 1 ) {
			$value = array_map( 'trim', $checked );
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $defaults
	 */
	protected function fill_values( &$value, $defaults ) {
		if ( empty( $value ) ) {
			$value = $defaults;
		} else {
			$value = array_merge( $defaults, (array) $value );
		}
	}
}
