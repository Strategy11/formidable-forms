<?php

/**
 * @since 3.0
 */
class FrmFieldType {

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

	public function __get( $key ) {
		$value = '';
		if ( property_exists( $this, $key ) ) {
			$value = $this->{$key};
		}
		return $value;
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

	private function default_field_settings() {
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
}
