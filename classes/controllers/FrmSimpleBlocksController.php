<?php

class FrmSimpleBlocksController {

	/**
	 * Enqueue Formidable Simple Blocks' assets for editor in admin.
	 *
	 */
	public static function formidable_block_editor_assets() {
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
				'show_counts'  => $pro && $views ? self::get_show_counts() : '',
				'view_options' => $pro && $views ? self::get_frm_options_for_views() : '',
			)
		);

		wp_enqueue_style(
			'formidable_block-editor-css',
			FrmAppHelper::plugin_url() . '/css/blocks.editor.build.css',
			array( 'wp-edit-blocks' ),
			$version
		);
	}

	/**
	 * Get the View type (show_count) for each View, e.g. calendar, dynamic
	 *
	 * @return array|object|void|null
	 */
	private static function get_show_counts() {
		$show_counts = self::get_meta_values( 'frm_show_count', 'frm_display' );

		return $show_counts;
	}

	/**
	 * Get the specified meta value for the specified post type
	 *
	 * @param string $key
	 * @param string $post_type
	 *
	 * @return array|object|void|null
	 */
	private static function get_meta_values( $key = '', $post_type = 'frm_display' ) {

		global $wpdb;

		if ( empty( $key ) ) {
			return;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value, pm.meta_key FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE ( pm.meta_key = %s ) AND p.post_type = %s",
				$key,
				$post_type
			),
			OBJECT_K
		);

		return $results;
	}

	/**
	 * Get the options for the site's Views
	 *
	 * @return array|object|void|null
	 */
	private static function get_frm_options_for_views() {

		$views_options = self::get_meta_values( 'frm_options', 'frm_display' );

		foreach ( $views_options as $key => $value ) {
			$views_options[ $key ]->meta_value = unserialize( $value->meta_value );
		}

		return $views_options;
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
				'render_callback' => array( 'FrmSimpleBlocksController', 'simple_form_render' ),
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
				'render_callback' => array( 'FrmSimpleBlocksController', 'simple_view_render' ),
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
		$params = '';
		$params .= self::create_attribute_text( 'id', $attributes['form_id'] );
		$params .= self::create_attribute_text( 'title', $attributes['title'] );
		$params .= self::create_attribute_text( 'description', $attributes['description'] );
		$params .= self::create_attribute_text( 'minimize', $attributes['minimize'] );

		return do_shortcode( '[formidable' . $params . ']' );
	}

	/**
	 * Creates text for an attribute to be used in a shortcode, e.g. id=12, if the attribute has a value.
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	private static function create_attribute_text( $name, $value ) {
		return isset( $value ) ? ' ' . $name . ' =' . $value : '';
	}

	/**
	 * Renders a View given the specified attributes.  Shows up to 20 entries if no limit set for list Views.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public static function simple_view_render( $attributes ) {
		$params = '';
		$params .= self::create_attribute_text( 'id', $attributes['view_id'] );
		$params .= self::create_attribute_text( 'filter', $attributes['filter'] );

		$params .= ( $attributes['use_default_limit'] ) ? ' limit=20' : '';

		return do_shortcode( '[display-frm-data' . $params . ']' );
	}
}
