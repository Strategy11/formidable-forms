<?php

/**
 * @since 3.0
 */
abstract class FrmFieldType {

	/**
	 * @var object
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

	public function __construct( $field = 0, $type = '' ) {
		$this->field = $field;
		$this->set_type( $type );
	}

	public function __get( $key ) {
		$value = '';
		if ( property_exists( $this, $key ) ) {
			$value = $this->{$key};
		}
		return $value;
	}

	private function set_type( $type ) {
		if ( empty( $this->type ) ) {
			$this->type = $this->get_field_column('type');
			if ( empty( $this->type ) && ! empty( $type ) ) {
				$this->type = $type;
			}
		}
	}

	protected function get_field_column( $column ) {
		$field_val = '';
		if ( is_object( $this->field ) ) {
			$field_val = $this->field->{$column};
		}
		return $field_val;
	}

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
			'unique_msg'    => $frm_settings->unique_msg,
			'invalid'       => __( 'This field is invalid', 'formidable' ),
			'field_options' => $this->get_default_field_options(),
		);

		$field_options = $this->new_field_settings();
		return array_merge( $field, $field_options );
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
			if ( has_filter( 'frm_default_field_opts' ) || has_filter( 'frm_default_'. $this->type .'_field_opts' ) ) {
				$values = FrmFieldsHelper::field_object_to_array( $field );
				$opts = apply_filters( 'frm_default_field_opts', $opts, $values, $this->field );
				$opts = apply_filters( 'frm_default_' . $this->type . '_field_opts', $opts, $values, $this->field );
			}
		}

		return $opts;
	}

	protected function extra_field_opts() {
		return array();
	}

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

	public function get_import_value( $value, $atts = array() ) {
		return $this->prepare_import_value( $value, $atts );
	}

	protected function prepare_import_value( $value, $atts ) {
		return $value;
	}

	/**
	* Get the new child IDs for a repeating field's or embedded form's meta_value
	*
	* @since 2.0.16
	* @param array $meta_value
	* @param object $field
	* @param array $saved_entries
	* @return array $meta_value
	*/
	protected function get_new_child_ids( $value, $atts ) {
		$saved_entried = $atts['ids'];
		$new_value = array();
		foreach ( (array) $value as $old_child_id ) {
			if ( isset( $saved_entries[ $old_child_id ] ) ) {
				$new_value[] = $saved_entries[ $old_child_id ];
			}
		}

		return $new_value;
	}

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

	protected function run_wpautop( $atts, &$value ) {
		$autop = isset( $atts['wpautop'] ) ? $atts['wpautop'] : true;
		if ( apply_filters( 'frm_use_wpautop', $autop ) ) {
			if ( is_array( $value ) ) {
				$value = implode( "\n", $value );
			}
			$value = wpautop( $value );
		}
	}

	protected function fill_values( &$value, $defaults ) {
		if ( empty( $value ) ) {
			$value = $defaults;
		} else {
			$value = array_merge( $defaults, (array) $value );
		}
	}
}
