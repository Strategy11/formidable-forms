<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Shares repeated builder option lists while keeping saved selections in the form.
 *
 * @since x.x
 */
class FrmBuilderSelectHelper {

	/**
	 * @var array<string,array>
	 */
	private static $templates = array();

	/**
	 * Render a select, deferring unselected options only when its registry can be delivered.
	 *
	 * @since x.x
	 *
	 * @param array                    $attributes        Select attributes.
	 * @param array                    $options           Option labels keyed by value, in display order.
	 * @param array<int|string>|string $selected          Selected values.
	 * @param array                    $option_attributes Additional option attributes keyed by value.
	 *
	 * @return void
	 */
	public static function render( $attributes, $options, $selected, $option_attributes = array() ) {
		$records = array();

		foreach ( $options as $value => $label ) {
			$records[] = array(
				'value'      => (string) $value,
				'label'      => html_entity_decode( (string) $label, ENT_QUOTES, 'UTF-8' ),
				'attributes' => $option_attributes[ $value ] ?? array(),
			);
		}

		$selected = array_map( 'strval', (array) $selected );

		if ( ! isset( $attributes['multiple'] ) && ! array_intersect( $selected, array_keys( $options ) ) ) {
			// Match the browser's default selection when the saved value is no longer available.
			foreach ( $records as $record ) {
				if ( ! isset( $record['attributes']['disabled'] ) ) {
					$selected = array( $record['value'] );
					break;
				}
			}
		}

		$defer = wp_doing_ajax()
		? 'frm_load_field' === FrmAppHelper::get_post_param( 'action', '', 'sanitize_text_field' )
		: FrmAppHelper::is_form_builder_page( false );

		if ( $defer && $records ) {
			$key                            = md5( wp_json_encode( $records ) );
			self::$templates[ $key ]        = $records;
			$attributes['data-frm-options'] = $key;
		}

		echo '<select';
		FrmAppHelper::array_to_html_params( $attributes, true );
		echo '>';

		foreach ( $records as $record ) {
			$is_selected = in_array( $record['value'], $selected, true );

			if ( $defer && ! $is_selected ) {
				continue;
			}

			$params          = $record['attributes'];
			$params['value'] = $record['value'];
			FrmHtmlHelper::echo_dropdown_option( $options[ $record['value'] ], $is_selected, $params );
		}
		echo '</select>';
	}

	/**
	 * Get option lists collected during this request, including AJAX field batches.
	 *
	 * @since x.x
	 *
	 * @return array<string,array>
	 */
	public static function get_templates() {
		return self::$templates;
	}

	/**
	 * Deliver the initial page's shared options alongside the builder script.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function print_templates() {
		if ( self::$templates ) {
			wp_add_inline_script( 'formidable_admin', 'frm_admin_js.selectOptions = ' . wp_json_encode( self::$templates ) . ';', 'before' );
		}
	}
}
