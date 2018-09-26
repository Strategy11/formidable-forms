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
	protected $has_for_label = true;

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

	/**
	 * Is this field a lot taller than the submit button?
	 * @var bool
	 * @since 3.0
	 */
	protected $is_tall = false;

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
			$this->type = $this->get_field_column( 'type' );
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
    [if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
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
		} elseif ( $this->has_input ) {
			echo $this->builder_text_field( $name ); // WPCS: XSS ok.
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
		$html_id    = $this->html_id();
		$field['html_name'] = $field_name;
		$field['html_id']   = $html_id;

		$display = $this->display_field_settings();
		include( $this->include_form_builder_file() );
	}

	/**
	 * @return string The file path to include on the form builder
	 */
	protected function include_form_builder_file() {
		return '';
	}

	protected function builder_text_field( $name = '' ) {
		return '<input type="text" name="' . esc_attr( $this->html_name( $name ) ) . '" id="' . esc_attr( $this->html_id() ) . '" value="' . esc_attr( $this->get_field_column( 'default_value' ) ) . '" class="dyn_default_value" />';
	}

	protected function html_name( $name = '' ) {
		$prefix = empty( $name ) ? 'item_meta' : $name;
		return $prefix . '[' . $this->get_field_column( 'id' ) . ']';
	}

	protected function html_id( $plus = '' ) {
		return apply_filters( 'frm_field_get_html_id', 'field_' . $this->get_field_column( 'field_key' ) . $plus, $this->field );
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
			'show_image'   => false,
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

	/**
	 * @since 3.01.01
	 */
	public function show_options( $field, $display, $values ) {
		do_action( 'frm_' . $field['type'] . '_field_options_form', $field, $display, $values );
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
		$field_name = $this->get_field_column( 'name' );
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

		$filter_args = array(
			'field' => $this->field,
			'type'  => $this->type,
		);
		return apply_filters( 'frm_default_field_options', $opts, $filter_args );
	}

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

		return maybe_unserialize( $values['options'] );
	}

	/**
	 * @param array $args ($field, $errors, $form, $form_action)
	 */
	public function show_field( $args ) {
		if ( apply_filters( 'frm_show_normal_field_type', $this->normal_field, $this->type ) ) {
			echo $this->prepare_field_html( $args ); // WPCS: XSS ok.
		} else {
			do_action( 'frm_show_other_field_type', $this->field, $args['form'], array( 'action' => $args['form_action'] ) );
		}
		$this->get_field_scripts_hook( $args );
	}

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
		$args = $this->fill_display_field_values( $args );

		if ( $this->has_html ) {
			$args['html'] = $this->before_replace_html_shortcodes( $args, $this->field['custom_html'] );
			$args['errors'] = is_array( $args['errors'] ) ? $args['errors'] : array();
			$args['field_obj'] = $this;

			$label = FrmFieldsHelper::label_position( $this->field['label'], $this->field, $args['form'] );
			$this->set_field_column( 'label', $label );

			$html_shortcode = new FrmFieldFormHtml( $args );
			$html = $html_shortcode->get_html();
			$html = $this->after_replace_html_shortcodes( $args, $html );
			$html_shortcode->remove_collapse_shortcode( $html );
		} else {
			$html = $this->include_front_field_input( $args, array() );
		}
		return $html;
	}

	/**
	 * @param array $args
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
		$is_radio = FrmField::is_radio( $this->field );
		$is_checkbox = FrmField::is_checkbox( $this->field );
		$align = FrmField::get_option( $this->field, 'align' );

		$class = '';
		if ( ! empty( $align ) && ( $is_radio || $is_checkbox ) ) {
			$class .= ( 'inline' === $align ) ? ' horizontal_radio' : ' vertical_radio';
		}

		return $class;
	}

	public function get_label_class() {
		return ' frm_primary_label';
	}

	/**
	 * Add classes to the input for output
	 *
	 * @since 3.02
	 */
	protected function add_input_class() {
		$input_class = FrmField::get_option( $this->field, 'input_class' );
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

		$field = $this->field;
		$html_id = $args['html_id'];
		$field_name = $args['field_name'];
		$read_only = FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin();
		unset( $args['form'] ); // lighten up on memory usage

		ob_start();
		include( $include_file );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $hidden . $input_html;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$field_type = $this->html5_input_type();
		$input_html = $this->get_field_input_html_hook( $this->field );
		$this->add_aria_description( $args, $input_html );
		$this->add_extra_html_atts( $args, $input_html );

		return '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $args['html_id'] ) . '" name="' . esc_attr( $args['field_name'] ) . '" value="' . esc_attr( $this->field['value'] ) . '" ' . $input_html . '/>';
	}

	protected function html5_input_type() {
		$frm_settings = FrmAppHelper::get_settings();
		return $frm_settings->use_html ? $this->type : 'text';
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
		$hidden = '';
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
		$hidden = '';
		if ( is_array( $selected_value ) ) {
			$args['save_array'] = true;
			foreach ( $selected_value as $selected ) {
				$hidden .= $this->show_single_hidden( $selected, $args );
			}
		} else {
			$args['save_array'] = $this->is_readonly_array();
			$hidden .= $this->show_single_hidden( $selected_value, $args );
		}

		return $hidden;
	}

	protected function show_single_hidden( $selected, $args ) {
		if ( $args['save_array'] ) {
			$args['field_name'] .= '[]';
			$id = '';
		} else {
			$id = ' id="' . esc_attr( $args['html_id'] ) . '"';
		}

		return '<input type="hidden" value="' . esc_attr( $selected ) . '" name="' . esc_attr( $args['field_name'] ) . '" ' . $id . ' />';
	}

	/**
	 * @since 3.0
	 */
	protected function get_select_box( $values ) {
		$options = $this->get_field_column( 'options' );
		$selected = $values['field_value'];

		if ( isset( $values['combo_name'] ) ) {
			$options = $options[ $values['combo_name'] ];
			$selected = ( is_array( $selected ) && isset( $selected[ $values['combo_name'] ] ) ) ? $selected[ $values['combo_name'] ] : '';
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
		$field = isset( $values['field'] ) ? $values['field'] : $this->field;
		$input_html = $this->get_field_input_html_hook( $field );
		$select_atts = $this->get_select_atributes( $values );
		$select = '';
		foreach ( $select_atts as $name => $value ) {
			$select .= $name . '="' . esc_attr( $value ) . '" ';
		}
		return '<select ' . $select . $input_html . '>';
	}

	/**
	 * @since 3.0
	 */
	protected function get_select_atributes( $values ) {
		$readonly = ( FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin() );
		$select_atts = array();
		if ( ! $readonly ) {
			if ( isset( $values['combo_name'] ) ) {
				$values['field_name'] .= '[' . $values['combo_name'] . ']';
				$values['html_id'] .= '_' . $values['combo_name'];
			}

			$select_atts['name'] = $values['field_name'];
			$select_atts['id'] = $values['html_id'];
		}

		return $select_atts;
	}

	protected function load_field_scripts( $args ) {
		// Override me
	}

	protected function fill_display_field_values( $args = array() ) {
		$defaults = array(
			'field_name'    => 'item_meta[' . $this->get_field_column( 'id' ) . ']',
			'field_id'      => $this->get_field_column( 'id' ),
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
		$field['input_class'] = $this->add_input_class();

		ob_start();
		do_action( 'frm_field_input_html', $field );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	/**
	 * Link input to field description for screen readers
	 * @since 3.0
	 */
	protected function add_aria_description( $args, &$input_html ) {
		if ( $this->get_field_column( 'description' ) != '' ) {
			$desc_id = 'frm_desc_' . esc_attr( $args['html_id'] );
			$input_html .= ' aria-describedby="' . esc_attr( $desc_id ) . '"';
		}
	}

	/**
	 * @param array $args
	 * @return array
	 */
	public function validate( $args ) {
		return array();
	}

	public function is_not_unique( $value, $entry_id ) {
		$exists = false;
		if ( FrmAppHelper::pro_is_installed() ) {
			$exists = FrmProEntryMetaHelper::value_exists( $this->get_field_column( 'id' ), $value, $entry_id );
		}
		return $exists;
	}

	public function get_value_to_save( $value, $atts ) {
		return $value;
	}

	/**
	 * Prepare value last thing before saving in the db
	 *
	 * @param string|array $value
	 *
	 * @return string|array|float
	 */
	public function set_value_before_save( $value ) {
		return $value;
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

	/**
	 * @since 3.0
	 *
	 * @param array|string $value
	 * @param array $atts
	 *
	 * @return array|string
	 */
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
