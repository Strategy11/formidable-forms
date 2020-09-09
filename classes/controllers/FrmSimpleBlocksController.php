<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSimpleBlocksController {

	/**
	 * Enqueue Formidable Simple Blocks' js and CSS for editor in admin.
	 */
	public static function block_editor_assets() {
		$version = FrmAppHelper::plugin_version();

		wp_register_script(
			'formidable-form-selector',
			FrmAppHelper::plugin_url() . '/js/formidable_blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			true
		);

		$icon       = str_replace( 'dashicons-', '', apply_filters( 'frm_icon', 'svg' ) );
		$block_name = FrmAppHelper::get_menu_name();
		if ( $block_name === 'Formidable' ) {
			$block_name = 'Formidable Forms';
		}

		$script_vars = array(
			'forms' => self::get_forms_options(),
			'icon'  => $icon,
			'name'  => $block_name,
			'link'  => FrmAppHelper::admin_upgrade_link( 'block' ),
			'url'   => FrmAppHelper::plugin_url(),
		);

		wp_localize_script( 'formidable-form-selector', 'formidable_form_selector', $script_vars );
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'formidable-form-selector', 'formidable' );
		}

		wp_enqueue_style(
			'formidable_block-editor-css',
			FrmAppHelper::plugin_url() . '/css/frm_blocks.css',
			array( 'wp-edit-blocks' ),
			$version
		);
	}

	/**
	 * Returns a filtered list of form options with the name as label and the id as value, sorted by label
	 *
	 * @return array
	 */
	private static function get_forms_options() {
		$forms = FrmForm::getAll(
			array(
				'is_template' => 0,
				'status'      => 'published',
				array(
					'or'               => 1,
					'parent_form_id'   => null,
					'parent_form_id <' => 1,
				),
			),
			'name'
		);

		return array_map( 'FrmSimpleBlocksController::set_form_options', $forms );
	}

	/**
	 * Returns an array for a form with name as label and id as value
	 *
	 * @param $form
	 *
	 * @return array
	 */
	private static function set_form_options( $form ) {
		return array(
			'label' => $form->name,
			'value' => $form->id,
		);
	}

	/**
	 * Registers simple form block
	 */
	public static function register_simple_form_block() {
		if ( ! is_callable( 'register_block_type' ) ) {
			return;
		}

		if ( is_admin() ) {
			FrmStylesController::enqueue_css( 'register', true );
		}

		register_block_type(
			'formidable/simple-form',
			array(
				'attributes'      => array(
					'formId'      => array(
						'type' => 'string',
					),
					'title'       => array(
						'type' => 'string',
					),
					'description' => array(
						'type' => 'string',
					),
					'minimize'    => array(
						'type' => 'string',
					),
					'className'   => array(
						'type' => 'string',
					),
				),
				'editor_style'    => 'formidable',
				'editor_script'   => 'formidable-form-selector',
				'render_callback' => 'FrmSimpleBlocksController::simple_form_render',

			)
		);
	}

	/**
	 * Renders a form given the specified attributes.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public static function simple_form_render( $attributes ) {
		if ( ! isset( $attributes['formId'] ) ) {
			return '';
		}

		$params       = array_filter( $attributes );
		$params['id'] = $params['formId'];
		unset( $params['formId'] );

		$form = FrmFormsController::get_form_shortcode( $params );

		return str_replace( ' frm_logic_form ', ' ', $form ); // prevent the form from hiding
	}
}
