<?php
/**
 * Submit field class
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.03
 */
class FrmFieldSubmit extends FrmFieldType {

	protected $type = 'submit';

	protected $has_for_label = false;

	public function default_html() {
		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field">
	<h3 class="frm_pos_[label_position]">[field_name]</h3>
	[if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
    [input]
</div>
DEFAULT_HTML;

		return $default_html;
	}

	protected function field_settings_for_type() {
		$settings = array(
			'required'       => false,
			'visibility'     => false,
			'label_position' => true,
			'options'        => true,
			'default'        => false,
		);

		return $settings;
	}

	/**
	 * @param array $args - Includes 'field', 'display', and 'values'
	 */
	public function show_extra_field_choices( $args ) {
		$field = $args['field'];
		// include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/summary-options.php' );

		parent::show_extra_field_choices( $args );
	}

	protected function extra_field_opts() {
		return array_merge(
			parent::extra_field_opts(),
			array(
				'exclude_fields' => '',
				'include_extras' => array(),
				'label'          => 'none',
			)
		);
	}

	protected function include_form_builder_file() {
		// return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/field-summary.php';
	}

	protected function get_excluded_ids() {
		$ids = trim( FrmField::get_option( $this->field, 'exclude_fields' ) );
		if ( ! empty( $ids ) ) {
			$ids = explode( ',', $ids );
			// trim to avoid mismatch - due to empty space - when doing in_array.
			// array_filter to remove empty spaces caused by e.g. trailing comma.
			$ids = array_filter( array_map( 'trim', $ids ) );

			return $ids;
		} else {
			return array();
		}
	}

	public function front_field_input( $args, $shortcode_atts ) {
		var_dump( 'Front field input' );

		return parent::front_field_input( $args, $shortcode_atts );
	}
}
