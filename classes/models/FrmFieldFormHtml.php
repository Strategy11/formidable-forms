<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */

class FrmFieldFormHtml {

	private $html;

	private $html_id;

	/**
	 * @var FrmFieldType
	 */
	private $field_obj;

	private $field_id;

	private $form = array();

	private $pass_args = array();

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		$this->_set( 'field_obj', $atts );
		$this->set_field_id( $atts );
		$this->_set( 'form', $atts );
		$this->_set( 'html_id', $atts );
		$this->set_html( $atts );
		$this->set_pass_args( $atts );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $param
	 * @param array $atts
	 */
	private function _set( $param, $atts ) {
		if ( isset( $atts[ $param ] ) ) {
			$this->{$param} = $atts[ $param ];
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	private function set_html( $atts ) {
		$this->set_from_field(
			$atts,
			array(
				'param'   => 'html',
				'default' => 'custom_html',
			)
		);
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	private function set_field_id( $atts ) {
		$this->set_from_field(
			$atts,
			array(
				'param'   => 'field_id',
				'default' => 'id',
			)
		);
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	private function set_pass_args( $atts ) {
		$this->pass_args = $atts;

		$exclude = array( 'field_obj', 'html' );
		foreach ( $exclude as $ex ) {
			if ( isset( $atts[ $ex ] ) ) {
				unset( $this->pass_args[ $ex ] );
			}
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 * @param array $set
	 */
	private function set_from_field( $atts, $set ) {
		if ( isset( $atts[ $set['param'] ] ) ) {
			$this->{$set['param']} = $atts[ $set['param'] ];
		} else {
			$this->{$set['param']} = $this->field_obj->get_field_column( $set['default'] );
		}
	}

	public function get_html() {
		$this->replace_shortcodes_before_input();
		$this->replace_shortcodes_with_atts();
		$this->replace_shortcodes_after_input();

		return $this->html;
	}

	/**
	 * @since 3.0
	 */
	private function replace_shortcodes_before_input() {
		$this->html = apply_filters( 'frm_before_replace_shortcodes', $this->html, $this->field_obj->get_field(), $this->pass_args['errors'], $this->form );

		$this->replace_field_values();

		$this->replace_required_label_shortcode();
		$this->replace_required_class();
		$this->maybe_replace_description_shortcode( false );
		$this->replace_error_shortcode();
		$this->add_class_to_label();
		$this->add_field_div_classes();

		$this->replace_entry_key();
		$this->replace_form_shortcodes();
		$this->process_wp_shortcodes();
		$this->maybe_replace_description_shortcode( true );
	}

	/**
	 * @since 3.0
	 */
	private function replace_field_values() {
		//replace [id]
		$this->html = str_replace( '[id]', $this->field_id, $this->html );

		// set the label for
		$this->html = str_replace( 'field_[key]', $this->html_id, $this->html );

		//replace [key]
		$this->html = str_replace( '[key]', $this->field_obj->get_field_column( 'field_key' ), $this->html );

		//replace [field_name]
		$this->html = str_replace( '[field_name]', $this->field_obj->get_field_column( 'name' ), $this->html );
	}

	/**
	 * @since 3.0
	 */
	private function replace_required_label_shortcode() {
		$required = FrmField::is_required( $this->field_obj->get_field() ) ? $this->field_obj->get_field_column( 'required_indicator' ) : '';
		FrmShortcodeHelper::remove_inline_conditions( ! empty( $required ), 'required_label', $required, $this->html );
	}

	/**
	 * If this is an HTML field, the values are included in the description.
	 * In this case, we don't want to run the wp shortcodes with the description included.
	 *
	 * @since 3.0
	 */
	private function maybe_replace_description_shortcode( $wp_processed = false ) {
		$is_html        = 'html' === $this->field_obj->get_field_column( 'type' );
		$should_replace = ( $is_html && $wp_processed ) || ( ! $is_html && ! $wp_processed );
		if ( $should_replace ) {
			$this->replace_description_shortcode();
		}
	}

	/**
	 * @since 3.0
	 */
	private function replace_description_shortcode() {
		$this->maybe_add_description_id();
		$description = $this->field_obj->get_field_column( 'description' );
		FrmShortcodeHelper::remove_inline_conditions( ( $description && $description != '' ), 'description', $description, $this->html );
	}

	/**
	 * Add an ID to the description for aria-describedby.
	 * This ID was added to the HTML in v3.0.
	 *
	 * @since 3.0
	 */
	private function maybe_add_description_id() {
		$description = $this->field_obj->get_field_column( 'description' );
		if ( $description != '' ) {
			$this->add_element_id( 'description', 'desc' );
		}
	}

	/**
	 * Insert an ID if it doesn't exist.
	 *
	 * @since 3.06.02
	 */
	private function add_element_id( $param, $id ) {
		preg_match_all( '/(\[if\s+' . $param . '\])(.*?)(\[\/if\s+' . $param . '\])/mis', $this->html, $inner_html );
		if ( ! isset( $inner_html[2] ) ) {
			return;
		}

		if ( ! is_string( $inner_html[2] ) && count( $inner_html[2] ) === 1 ) {
			$inner_html[2] = $inner_html[2][0];
		}

		if ( is_string( $inner_html[2] ) ) {
			$has_id = strpos( $inner_html[2], ' id=' );
			if ( ! $has_id ) {
				$id = 'frm_' . $id . '_' . $this->html_id;
				$this->html = str_replace( 'class="frm_' . $param, 'id="' . esc_attr( $id ) . '" class="frm_' . esc_attr( $param ), $this->html );
			}
		}
	}

	/**
	 * @since 3.0
	 */
	private function replace_error_shortcode() {
		$this->maybe_add_error_id();
		$error = isset( $this->pass_args['errors'][ 'field' . $this->field_id ] ) ? $this->pass_args['errors'][ 'field' . $this->field_id ] : false;
		FrmShortcodeHelper::remove_inline_conditions( ! empty( $error ), 'error', $error, $this->html );
	}

	/**
	 * Add an ID to the error message for aria-describedby.
	 * This ID was added to the HTML in v3.06.02.
	 *
	 * @since 3.06.02
	 */
	private function maybe_add_error_id() {
		if ( ! isset( $this->pass_args['errors'][ 'field' . $this->field_id ] ) ) {
			return;
		}

		$this->add_element_id( 'error', 'error' );
	}

	/**
	 * Replace [required_class]
	 *
	 * @since 3.0
	 */
	private function replace_required_class() {
		$required_class = FrmField::is_required( $this->field_obj->get_field() ) ? ' frm_required_field' : '';
		$this->html     = str_replace( '[required_class]', $required_class, $this->html );
	}

	/**
	 * @since 3.0
	 */
	private function replace_form_shortcodes() {
		if ( ! empty( $this->form ) ) {
			$form = (array) $this->form;

			//replace [form_key]
			$this->html = str_replace( '[form_key]', $form['form_key'], $this->html );

			//replace [form_name]
			$this->html = str_replace( '[form_name]', $form['name'], $this->html );
		}
	}

	/**
	 * @since 3.0
	 */
	public function replace_shortcodes_after_input() {
		$this->html .= "\n";

		// Stop html filtering on confirmation field to prevent loop
		if ( $this->field_obj->get_field_column( 'conf_field' ) != 'stop' ) {
			$this->filter_for_more_shortcodes();
		}
	}

	/**
	 * @since 3.0
	 */
	private function filter_for_more_shortcodes() {
		$atts = $this->pass_args;

		//If field is not in repeating section
		if ( empty( $atts['section_id'] ) ) {
			$atts = array(
				'errors' => $this->pass_args['errors'],
				'form'   => $this->form,
			);
		}
		$this->html = apply_filters( 'frm_replace_shortcodes', $this->html, $this->field_obj->get_field(), $atts );
	}

	/**
	 * Remove [collapse_this] if it's still included after all processing
	 *
	 * @since 3.0
	 *
	 * @param string $html
	 */
	public function remove_collapse_shortcode( &$html ) {
		if ( strpos( $html, '[collapse_this]' ) ) {
			$html = str_replace( '[collapse_this]', '', $html );
		}
	}

	/**
	 * @since 3.0
	 */
	private function replace_shortcodes_with_atts() {
		preg_match_all( "/\[(input|deletelink)\b(.*?)(?:(\/))?\]/s", $this->html, $shortcodes, PREG_PATTERN_ORDER );

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$shortcode_atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[2][ $short_key ] );
			$tag            = FrmShortcodeHelper::get_shortcode_tag( $shortcodes, $short_key );

			$replace_with = '';

			if ( $tag == 'deletelink' && FrmAppHelper::pro_is_installed() ) {
				$replace_with = FrmProEntriesController::entry_delete_link( $shortcode_atts );
			} elseif ( $tag == 'input' ) {
				$replace_with = $this->replace_input_shortcode( $shortcode_atts );
			}

			$this->html = str_replace( $shortcodes[0][ $short_key ], $replace_with, $this->html );
		}
	}

	/**
	 * @param array $shortcode_atts
	 *
	 * @return string
	 */
	private function replace_input_shortcode( $shortcode_atts ) {
		$shortcode_atts = $this->prepare_input_shortcode_atts( $shortcode_atts );

		return $this->field_obj->include_front_field_input( $this->pass_args, $shortcode_atts );
	}

	/**
	 * @param array $shortcode_atts
	 *
	 * @return array
	 */
	private function prepare_input_shortcode_atts( $shortcode_atts ) {
		if ( isset( $shortcode_atts['opt'] ) ) {
			$shortcode_atts['opt'] --;
		}

		$field_class = isset( $shortcode_atts['class'] ) ? $shortcode_atts['class'] : '';
		$this->field_obj->set_field_column( 'input_class', $field_class );

		if ( isset( $shortcode_atts['class'] ) ) {
			unset( $shortcode_atts['class'] );
		}

		$shortcode_atts['aria-invalid'] = isset( $this->pass_args['errors'][ 'field' . $this->field_id ] ) ? 'true' : 'false';

		$this->field_obj->set_field_column( 'shortcodes', $shortcode_atts );

		return $shortcode_atts;
	}

	/**
	 * Add the label position class into the HTML
	 * If the label position is inside, add a class to show the label if the field has a value.
	 *
	 * @since 3.0
	 */
	private function add_class_to_label() {
		$label_class = $this->field_obj->get_label_class();
		$this->html  = str_replace( '[label_position]', $label_class, $this->html );
		if ( $this->field_obj->get_field_column( 'label' ) == 'inside' && $this->field_obj->get_field_column( 'value' ) != '' ) {
			$this->html = str_replace( 'frm_primary_label', 'frm_primary_label frm_visible', $this->html );
		}
	}

	/**
	 * Replace [entry_key]
	 *
	 * @since 3.0
	 */
	private function replace_entry_key() {
		$entry_key  = FrmAppHelper::simple_get( 'entry', 'sanitize_title' );
		$this->html = str_replace( '[entry_key]', $entry_key, $this->html );
	}

	/**
	 * Add classes to a field div
	 *
	 * @since 3.0
	 */
	private function add_field_div_classes() {
		$classes = $this->get_field_div_classes();

		if ( $this->field_obj->get_field_column( 'type' ) == 'html' && strpos( $this->html, '[error_class]' ) === false ) {
			// there is no error_class shortcode for HTML fields
			$this->html = str_replace( 'class="frm_form_field', 'class="frm_form_field ' . $classes, $this->html );
		}
		$this->html = str_replace( '[error_class]', $classes, $this->html );
	}

	/**
	 * Get the classes for a field div
	 *
	 * @since 3.0
	 *
	 * @return string $classes
	 */
	private function get_field_div_classes() {
		// Add error class
		$classes = isset( $this->pass_args['errors'][ 'field' . $this->field_id ] ) ? ' frm_blank_field' : '';

		// Add label position class
		$settings = $this->field_obj->display_field_settings();
		if ( isset( $settings['label_position'] ) && $settings['label_position'] ) {
			$classes .= ' frm_' . $this->field_obj->get_field_column( 'label' ) . '_container';
		}

		// Add CSS layout classes
		$extra_classes = $this->field_obj->get_field_column( 'classes' );
		if ( ! empty( $extra_classes ) ) {
			if ( ! strpos( $this->html, 'frm_form_field ' ) ) {
				$classes .= ' frm_form_field';
			}
			$classes .= ' ' . $extra_classes;
		}

		$classes .= $this->field_obj->get_container_class();

		// Get additional classes
		return apply_filters( 'frm_field_div_classes', $classes, $this->field_obj->get_field(), array( 'field_id' => $this->field_id ) );
	}

	/**
	 * This filters shortcodes in the field HTML
	 *
	 * @since 3.0
	 */
	private function process_wp_shortcodes() {
		if ( apply_filters( 'frm_do_html_shortcodes', true ) ) {
			$this->html = do_shortcode( $this->html );
		}
	}
}
