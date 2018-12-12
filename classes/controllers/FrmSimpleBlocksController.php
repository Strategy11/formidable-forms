<?php

class FrmSimpleBlocksController {

	/**
	 * Enqueue Formidable Simple Blocks' js for editor in admin.
	 *
	 */
	public static function formidable_block_editor_js() {
		$version = FrmAppHelper::plugin_version();

		wp_enqueue_script(
			'formidable_simple-block-js',
			FrmAppHelper::plugin_url() . '/js/blocks.build.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			true
		);

		$pro = is_callable( 'FrmProDisplay::getAll' );

		$views = $pro ? FrmProDisplay::getAll() : '';

		wp_localize_script(
			'formidable_simple-block-js',
			'formidable_simple_script_vars',
			array(
				'forms'        => FrmForm::getAll(),
				'pro'          => $pro,
				'views'        => $views,
				'show_counts'  => $pro && $views ? FrmProDisplaysHelper::get_show_counts() : '',
				'view_options' => $pro && $views ? FrmProDisplaysHelper::get_frm_options_for_views() : '',
			)
		);
	}

	/**
	 * Enqueue Formidable Simple Blocks' CSS for editor in admin.
	 *
	 */
	public static function formidable_block_editor_css() {
		$version = FrmAppHelper::plugin_version();

		wp_enqueue_style(
			'formidable_block-editor-css',
			FrmAppHelper::plugin_url() . '/css/blocks.editor.build.css',
			array( 'wp-edit-blocks' ),
			$version
		);
	}

	/**
	 * Registers simple form and View blocks
	 *
	 */
	public static function register_guten_blocks() {
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
				'editor_script'   => 'formidable_simple-block-js',
				'render_callback' => 'FrmSimpleBlocksController::simple_form_render',

			)
		);

		register_block_type(
			'formidable/simple-view',
			array(
				'attributes'      => array(
					'view_id'           => array(
						'type' => 'string',
					),
					'filter'            => array(
						'type' => 'string',
					),
					'limit'             => array(
						'type' => 'string',
					),
					'use_default_limit' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'editor_script'   => 'formidable_simple-block-js',
				'render_callback' => 'FrmSimpleBlocksController::simple_view_render',
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

	/**
	 * Renders a View given the specified attributes.  Shows up to 20 entries if no limit set for list Views.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public static function simple_view_render( $attributes ) {
		if ( ! isset( $attributes['view_id'] ) ) {
			return '';
		}

		$params = array_filter( $attributes );

		$params['id'] = $params['view_id'];
		unset( $params['view_id'] );

		if ( isset( $params['use_default_limit'] ) && ( $params['use_default_limit'] ) ) {
			$params['limit'] = 20;
		}
		unset( $params['use_default_limit'] );

		$view = FrmProDisplaysController::get_shortcode( $params );

		$view_type = get_post_meta( $params['id'], 'frm_show_count', true );

		if ( $view_type === 'calendar' ) {
			ob_start();
			wp_print_styles( 'formidable' );
			$view .= ob_get_contents();
			ob_end_clean();
		}

		return $view;
	}
}
