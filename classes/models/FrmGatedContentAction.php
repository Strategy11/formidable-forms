<?php
/**
 * Gated Content form action
 *
 * @package Formidable
 *
 * @since x.x
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedContentAction extends FrmFormAction {

	/**
	 * @var string
	 *
	 * @since x.x
	 *
	 */
	public static $slug = 'gated_content';

	/**
	 * Set up action options and register with parent constructor.
	 *
	 * @since x.x
	 *
	 */
	public function __construct() {
		$action_ops = array(
			'classes'  => 'frmfont frm_lock_icon',
			'active'   => true,
			'event'    => array( 'create', 'update', 'payment-success' ),
			'limit'    => 99,
			'priority' => 8,
			'color'    => 'rgb(99, 102, 241)',
			'keywords' => __( 'gated, content, payment, access, token, restrict, download', 'formidable' ),
		);
		$action_ops = apply_filters( 'frm_' . self::$slug . '_control_settings', $action_ops );

		parent::__construct( self::$slug, self::get_name(), $action_ops );
	}

	/**
	 * Get the action display name.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Gated Content', 'formidable' );
	}

	/**
	 * Get the available gated content item types.
	 *
	 * Each entry is an associative array with:
	 * - label    (string) Display label shown in the type dropdown.
	 * - disabled (bool)   Whether the option is selectable. Default false.
	 * - pro      (bool)   Whether the type requires Pro. Default false.
	 *
	 * Pro and PDF plugins remove the `disabled` flag for their types by hooking
	 * `frm_gated_content_item_types`.
	 *
	 * @since x.x
	 *
	 * @return array<string, array>
	 */
	public static function get_types() {
		$types = array(
			'page'    => array(
				'label'    => __( 'WordPress page', 'formidable' ),
				'disabled' => false,
				'pro'      => false,
			),
			'frm_file' => array(
				'label'    => __( 'Formidable file', 'formidable' ),
				'disabled' => true,
				'pro'      => true,
			),
			'frm_pdf'  => array(
				'label'    => __( 'Formidable PDF file', 'formidable' ),
				'disabled' => true,
				'pro'      => true,
			),
		);

		/**
		 * Filter the available gated content item types.
		 *
		 * Use this to register new types or enable Pro types that are greyed out by default.
		 *
		 * @since x.x
		 *
		 * @param array<string, array> $types Associative array of type slug => type config.
		 */
		/** @var array<string, array> $types */
		$types = apply_filters( 'frm_gated_content_item_types', $types );
		return $types;
	}

	/**
	 * Render the action settings form.
	 *
	 * @since x.x
	 *
	 * @param object $instance Form action post object.
	 * @param array  $args     Contains `form`, `action_key`, `values`.
	 */
	public function form( $instance, $args = array() ) {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/_gated_content_settings.php';
		return '';
	}

	/**
	 * Default settings for a new gated content action.
	 *
	 * - items:          Array of item objects, each with 'type' and 'id' keys.
	 *                   One token unlocks all items in this action.
	 *                   Pro adds 'frm_file' and 'frm_pdf' types.
	 * - show_form_page: Page ID to redirect visitors who access protected content without a
	 *                   valid token. Typically a page containing the purchase form. Null = no redirect.
	 * - expired_hours:  Hours until access token expires. Null = never expires.
	 *                   Set via Pro only; stored here for shared validation logic.
	 * - event:          Form events that trigger token generation.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'type'           => 'page',
			'items'          => array(),
			'show_form_page' => null,
			'expired_hours'  => null,
			'event'          => array( 'create' ),
		);
	}

	/**
	 * Sanitize and validate settings on save.
	 *
	 * @since x.x
	 *
	 * @param array $new_instance New settings submitted via form().
	 * @param array $old_instance Previous saved settings.
	 * @return array Sanitized settings to save. Return false to abort save.
	 */
	public function update( $new_instance, $old_instance ) {
		$post_content = $new_instance['post_content'];

		// Sanitize items — each item is an array with 'type' and 'id' keys.
		$raw_items       = isset( $post_content['items'] ) ? (array) $post_content['items'] : array();
		$sanitized_items = array();

		foreach ( $raw_items as $raw_item ) {
			if ( ! is_array( $raw_item ) ) {
				continue;
			}

			$item = array(
				'type' => isset( $raw_item['type'] ) ? sanitize_key( $raw_item['type'] ) : 'page',
				'id'   => isset( $raw_item['id'] ) ? absint( $raw_item['id'] ) : 0,
			);

			/**
			 * Filter a sanitized gated content item before it is saved.
			 *
			 * Pro and PDF plugins use this to sanitize their own type-specific fields
			 * and merge them into the item array.
			 *
			 * @since x.x
			 *
			 * @param array $item     Sanitized item data (keys: type, id).
			 * @param array $raw_item Raw submitted item data.
			 */
			$item = apply_filters( 'frm_gated_content_sanitize_item', $item, $raw_item );

			// Skip items with no ID selected (user left the select at the empty default).
			if ( empty( $item['id'] ) ) {
				continue;
			}

			$sanitized_items[] = $item;
		}

		$post_content['items'] = $sanitized_items;

		// Sanitize show_form_page — positive int or null.
		$post_content['show_form_page'] = ! empty( $post_content['show_form_page'] )
			? absint( $post_content['show_form_page'] )
			: null;

		// Sanitize expired_hours — positive int or null (Pro may set this).
		$post_content['expired_hours'] = ! empty( $post_content['expired_hours'] )
			? absint( $post_content['expired_hours'] )
			: null;

		$new_instance['post_content'] = $post_content;

		return $new_instance;
	}
}
