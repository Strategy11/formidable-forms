<?php
/**
 * Gated Content form action
 *
 * @package Formidable
 *
 * @since 6.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedContentAction extends FrmFormAction {

	/**
	 * @var string
	 */
	public static $slug = 'gated_content';

	/**
	 * Set up action options and register with parent constructor.
	 *
	 * Runs at form action priority 8 — before On Submit (9) and Send Email (10) —
	 * so the raw token is already stored when those actions process [frm_gated_content]
	 * shortcodes on the same request or after a payment redirect.
	 */
	public function __construct() {
		$action_ops = array(
			'classes'  => 'frmfont frm_lock_simple',
			'active'   => true,
			'event'    => array( 'create', 'payment-success' ),
			'limit'    => 99,
			'priority' => 8,
			'color'    => '#F59E0B',
			'keywords' => __( 'gated, content, payment, access, token, restrict, download', 'formidable' ),
		);
		$action_ops = apply_filters( 'frm_' . self::$slug . '_control_settings', $action_ops );

		parent::__construct( self::$slug, self::get_name(), $action_ops );
	}

	/**
	 * Get the action display name.
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
	 * @return array<string, array>
	 */
	public static function get_types() {
		$types = array(
			'page'     => array(
				'label'    => __( 'Page', 'formidable' ),
				'disabled' => false,
			),
			'post'     => array(
				'label'    => __( 'Post', 'formidable' ),
				'disabled' => false,
			),
			'frm_file' => array(
				'label'    => __( 'Formidable file (Pro)', 'formidable' ),
				'disabled' => true,
			),
			'frm_pdf'  => array(
				'label'    => __( 'Formidable PDF (PDFs add-on)', 'formidable' ),
				'disabled' => true,
			),
		);

		/**
		 * Filter the available gated content item types.
		 *
		 * Use this to register new types or enable Pro types that are greyed out by default.
		 *
		 * @param array<string, array> $types Associative array of type slug => type config.
		 */
		/** @var array<string, array> */
		return apply_filters( 'frm_gated_content_item_types', $types );
	}

	/**
	 * Render the action settings form.
	 *
	 * @param object $instance Form action post object.
	 * @param array  $args     Contains `form`, `action_key`, `values`.
	 *
	 * @return string
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
	 *                   Pro adds the 'frm_file' type.
	 * - expired_hours:  Hours until access token expires. Null = never expires.
	 *                   Set via Pro only; stored here for shared validation logic.
	 * - event:          Form events that trigger token generation.
	 *
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'type'          => 'post',
			'items'         => array(),
			'expired_hours' => null,
			'event'         => array( 'create' ),
		);
	}

	/**
	 * Get the shortcode reference rows for the action settings UI.
	 *
	 * Returns an array of shortcode row definitions, each with:
	 * - code   (string) The shortcode string to display and copy.
	 * - output (string) Human-readable description of what it outputs.
	 *
	 * The `frm_gated_content_shortcodes` filter allows Pro and add-ons to append
	 * additional rows (e.g. show="expired_time" when expiry is configured).
	 *
	 * @since 6.33
	 *
	 * @param int $action_id Gated content action post ID.
	 *
	 * @return array<int, array{code: string, output: string}>
	 */
	public static function get_shortcodes( $action_id ) {
		$shortcodes = array(
			array(
				'code'   => '[frm_gated_content id="' . absint( $action_id ) . '"]',
				'output' => __( 'Access links for all items', 'formidable' ),
			),
			array(
				'code'   => '[frm_gated_content id="' . absint( $action_id ) . '" item="0"]',
				'output' => __( 'Access link for the first item (0-indexed)', 'formidable' ),
			),
			array(
				'code'   => '[frm_gated_content id="' . absint( $action_id ) . '" item="0" show="url"]',
				'output' => __( 'URL only for the first item (no link tag)', 'formidable' ),
			),
			array(
				'code'   => '[frm_gated_content id="' . absint( $action_id ) . '" show="access_token"]',
				'output' => __( 'Raw access token string', 'formidable' ),
			),
		);

		/**
		 * Filter the shortcode reference rows shown in the gated content action settings UI.
		 *
		 * Each entry must be an array with:
		 * - code   (string) The shortcode string to display and copy.
		 * - output (string) Human-readable description of what it outputs.
		 *
		 * @since 6.33
		 *
		 * @param array<int, array{code: string, output: string}> $shortcodes Shortcode rows.
		 * @param int                                             $action_id  Gated content action post ID.
		 */
		/** @var array<int, array{code: string, output: string}> */
		return (array) apply_filters( 'frm_gated_content_shortcodes', $shortcodes, $action_id );
	}

	/**
	 * Get posts for the "post" item type selector.
	 *
	 * Applies the `frm_gated_content_posts_query` filter so callers can extend
	 * the post types or change any other WP_Query argument, then narrows the
	 * result to posts that actually require a token: private posts and
	 * password-protected posts. Plain published posts are publicly accessible
	 * and should not appear as selectable gated content items.
	 *
	 * @return WP_Post[]
	 */
	/**
	 * Get posts for all post-type-backed gated content item type selectors.
	 *
	 * Runs one query covering all enabled post types derived from get_types(), then
	 * groups the results by type key. Only private and password-protected posts are
	 * included — plain published posts are publicly accessible and not selectable.
	 *
	 * @return array<string, WP_Post[]> Posts keyed by item type slug (e.g. 'page', 'post').
	 */
	public static function get_posts() {
		$post_types = array();

		foreach ( self::get_types() as $type_key => $type_config ) {
			if ( empty( $type_config['disabled'] ) && post_type_exists( $type_key ) ) {
				$post_types[] = $type_key;
			}
		}

		if ( ! $post_types ) {
			return array();
		}

		/**
		 * Filter the get_posts() arguments used to build the gated content item selectors.
		 *
		 * The filtered list is then narrowed to private and password-protected posts only.
		 *
		 * @since 6.33
		 *
		 * @param array $query_args get_posts() argument array.
		 */
		$query_args = (array) apply_filters(
			'frm_gated_content_posts_query',
			array(
				'post_type'              => $post_types,
				'post_status'            => array( 'publish', 'private' ),
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'numberposts'            => -1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$raw_posts = get_posts( $query_args );
		$raw_posts = is_array( $raw_posts ) ? $raw_posts : array();

		// Initialise empty buckets in get_types() order.
		$grouped = array_fill_keys( $post_types, array() );

		foreach ( $raw_posts as $post ) {
			if ( 'private' !== $post->post_status && '' === $post->post_password ) {
				// Skip publicly accessible posts.
				continue;
			}

			if ( isset( $grouped[ $post->post_type ] ) ) {
				$grouped[ $post->post_type ][] = $post;
			}
		}

		/** @var array<string, WP_Post[]> */
		return $grouped;
	}

	/**
	 * Build the JSON-encoded autocomplete source array for the "post" item selector.
	 *
	 * Returns a JSON string suitable for passing as a `data-source` attribute to a
	 * jQuery UI autocomplete widget. Each entry has a `value` (post ID string) and
	 * a `label` (post title).
	 *
	 * @param WP_Post[] $posts Posts returned by get_posts().
	 *
	 * @return string JSON-encoded array, or an empty string on encoding failure.
	 */
	public static function get_posts_autocomplete_source( $posts ) {
		return (string) wp_json_encode(
			array_map(
				static function ( $p ) {
					return array(
						'value' => (string) $p->ID,
						'label' => $p->post_title,
					);
				},
				$posts
			)
		);
	}

	/**
	 * Sanitize and validate settings on save.
	 *
	 * @param array $new_instance New settings submitted via form().
	 * @param array $old_instance Previous saved settings.
	 *
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
				'type' => isset( $raw_item['type'] ) ? sanitize_key( $raw_item['type'] ) : 'post',
				'id'   => isset( $raw_item['id'] ) ? sanitize_text_field( $raw_item['id'] ) : '',
			);

			/**
			 * Filter a sanitized gated content item before it is saved.
			 *
			 * Pro and PDF plugins use this to sanitize their own type-specific fields
			 * and merge them into the item array.
			 *
			 * @param array $item Sanitized item data (keys: type, id).
			 * @param array $args {
			 *
			 *     @type array $raw_item Raw submitted item data.
			 * }
			 */
			$item = apply_filters( 'frm_gated_content_sanitize_item', $item, compact( 'raw_item' ) );

			// Skip items with no ID selected (user left the select at the empty default).
			if ( empty( $item['id'] ) ) {
				continue;
			}

			$sanitized_items[] = $item;
		}//end foreach

		$post_content['items'] = $sanitized_items;

		// Sanitize expired_hours — positive int or null (Pro may set this).
		$post_content['expired_hours'] = ! empty( $post_content['expired_hours'] )
		? absint( $post_content['expired_hours'] )
		: null;

		$new_instance['post_content'] = $post_content;

		return $new_instance;
	}
}
