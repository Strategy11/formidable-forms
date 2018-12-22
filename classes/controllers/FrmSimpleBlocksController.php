<?php

class FrmSimpleBlocksController {

	/**
	 * Enqueue Formidable Simple Blocks' js and CSS for editor in admin.
	 *
	 */
	public static function formidable_block_editor_assets() {
		$version = FrmAppHelper::plugin_version();

		wp_enqueue_script(
			'formidable-form-selector',
			FrmAppHelper::plugin_url() . '/js/formidable_blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			true
		);

		$forms = self::get_filtered_forms();

		$script_vars = array(
			'forms'        => $forms,
			'pro'          => false,
			'views'        => '',
			'show_counts'  => '',
			'view_options' => '',
		);

		$script_vars = apply_filters( 'frm_simple_blocks_script_vars', $script_vars );

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

	private static function get_filtered_forms() {
		$forms = FrmForm::getAll(
			array(
				'is_template' => 0,
				'status'      => 'published',
				array(
					'or'               => 1,
					'parent_form_id'   => null,
					'parent_form_id <' => 1,
				)
			)
		);

		//ddd($forms);
		$filtered_forms = array_map( 'self::set_form_options', $forms );
		usort( $filtered_forms, 'self::label_sort' );

		//ddd($filtered_forms);
		return $filtered_forms;
	}

	private static function set_form_options( $form ) {
		return array(
			'label' => $form->name,
			'value' => $form->id,
		);
//		return  array(
//			$form->name => $form->id,
//		);
	}

	private static function label_sort( $option1, $option2 ) {
		$label_1 = strtoupper( $option1['label'] );
		$label_2 = strtoupper( $option2['label'] );

		if ( $label_1 == $label_2 ) {
			return 0;
		}

		return ( $label_1 < $label_2 ) ? - 1 : 1;
	}

	/**
	 * Registers simple form block
	 *
	 */
	public static function register_simple_form_block() {
		if ( ! is_callable( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'formidable/simple-form',
			array(
				'attributes'      => array(
					'form_id'     => array(
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
				),
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
		if ( ! isset( $attributes['form_id'] ) ) {
			return '';
		}

		$params       = array_filter( $attributes );
		$params['id'] = $params['form_id'];
		unset( $params['form_id'] );

		$form = FrmFormsController::get_form_shortcode( $params );

		ob_start();
		wp_print_styles( 'formidable' );
		$form .= ob_get_contents();
		ob_end_clean();

		return $form;
	}
}
