<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Finds and caches the posts that embed a form, for the Embeds column on the forms list.
 *
 * Locating an embed means searching post_content for a shortcode that can sit anywhere in the
 * content, so the LIKE patterns are unanchored and no index applies. Two things keep that
 * affordable: one query serves every form on a list page, and the cache is only invalidated
 * when a save actually changes which forms a post embeds.
 *
 * @since x.x
 */
class FrmFormEmbedsHelper {

	/**
	 * The transient name that stores data for which posts a form is embedded in.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const TRANSIENT_NAME = 'frm_posts_contain_form';

	/**
	 * Embed posts keyed by form ID, read from the transient once per request.
	 *
	 * @since x.x
	 *
	 * @var array|null
	 */
	private static $cached_posts;

	/**
	 * Every post that embeds any Formidable form, queried once per request.
	 *
	 * @since x.x
	 *
	 * @var array|null
	 */
	private static $candidate_posts;

	/**
	 * Reads the embed posts cache, hitting the transient only once per request.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public static function get_cached_posts() {
		if ( null === self::$cached_posts ) {
			$cached_posts       = get_transient( self::TRANSIENT_NAME );
			self::$cached_posts = is_array( $cached_posts ) ? $cached_posts : array();
		}

		return self::$cached_posts;
	}

	/**
	 * Saves the embed posts cache.
	 *
	 * @since x.x
	 *
	 * @param array $cached_posts Embed posts keyed by form ID.
	 *
	 * @return void
	 */
	public static function save_cached_posts( $cached_posts ) {
		self::$cached_posts = $cached_posts;
		set_transient( self::TRANSIENT_NAME, $cached_posts, DAY_IN_SECONDS );
	}

	/**
	 * Matches the candidate posts against the search strings of several forms at once.
	 *
	 * @since x.x
	 *
	 * @param array $search_map Search strings keyed by form ID.
	 *
	 * @return array Posts keyed by form ID.
	 */
	public static function match_candidate_posts( $search_map ) {
		$matched = array_fill_keys( array_keys( $search_map ), array() );

		foreach ( self::get_candidate_posts() as $candidate ) {
			foreach ( $search_map as $form_id => $search_strings ) {
				foreach ( $search_strings as $search_string ) {
					if ( ! str_contains( $candidate->post_content, $search_string ) ) {
						continue;
					}

					$matched[ $form_id ][] = (object) array(
						'ID'         => $candidate->ID,
						'post_title' => $candidate->post_title,
						'post_name'  => $candidate->post_name,
					);
					break;
				}
			}
		}

		return $matched;
	}

	/**
	 * Adds the links and fallback titles the Embeds dropdown expects.
	 *
	 * @since x.x
	 *
	 * @param array $posts Posts that embed a form.
	 *
	 * @return array
	 */
	public static function prepare_posts( $posts ) {
		foreach ( $posts as $post ) {
			if ( ! property_exists( $post, 'permalink' ) ) {
				$post->permalink = get_permalink( $post->ID );
			}

			if ( ! property_exists( $post, 'edit_link' ) ) {
				$post->edit_link = get_edit_post_link( $post->ID );
			}

			// Ensure post_name is not null or the string "null"
			if ( ! isset( $post->post_name ) ) {
				$post->post_name = '';
			}

			// Ensure post_title is not null or the string "null"
			if ( ! isset( $post->post_title ) ) {
				$post->post_title = '';
			}

			if ( '' === $post->post_title ) {
				$post->post_title = __( '(no title)', 'formidable' );
			}
		}//end foreach

		return $posts;
	}

	/**
	 * Gets the substrings that mark a post as embedding some Formidable form.
	 *
	 * Every string returned by FrmFormsListHelper::get_search_strings_for_form() has to contain
	 * at least one of these, because they are what narrows wp_posts to a candidate set in one
	 * query rather than one query per form.
	 *
	 * @since x.x
	 *
	 * @return string[]
	 */
	private static function get_needles() {
		/**
		 * @since x.x
		 *
		 * @param string[] $needles
		 */
		$needles = apply_filters(
			'frm_embed_post_needles',
			array(
				'[formidable ',
				'wp:formidable/simple-form',
			)
		);

		$strings = array();

		foreach ( (array) $needles as $needle ) {
			if ( is_string( $needle ) && '' !== $needle ) {
				$strings[] = $needle;
			}
		}

		return $strings;
	}

	/**
	 * Queries once for every post or page that embeds any Formidable form.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_candidate_posts() {
		if ( null !== self::$candidate_posts ) {
			return self::$candidate_posts;
		}

		global $wpdb;

		$needles = self::get_needles();

		if ( ! $needles ) {
			self::$candidate_posts = array();
			return self::$candidate_posts;
		}

		$like_where = implode( ' OR ', array_fill( 0, count( $needles ), 'post_content LIKE %s' ) );
		$args       = array( $wpdb->posts, 'post', 'page', 'auto-draft', 'trash' );

		foreach ( $needles as $needle ) {
			$args[] = '%' . $wpdb->esc_like( $needle ) . '%';
		}

		$sql = 'SELECT ID, post_title, post_name, post_content FROM %i'
		. ' WHERE post_type IN ( %s, %s )'
		. ' AND post_status NOT IN ( %s, %s )'
		. ' AND ( ' . $like_where . ' )';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $like_where is built from a placeholder count, not from input.
		$posts = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );

		self::$candidate_posts = is_array( $posts ) ? $posts : array();

		return self::$candidate_posts;
	}

	/**
	 * Maybe clear the cache when a post is inserted.
	 *
	 * Updates go through maybe_clear_on_update(), which can compare the content before and
	 * after the change.
	 *
	 * @since x.x
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  True when an existing post was updated rather than created.
	 *
	 * @return void
	 */
	public static function maybe_clear_on_insert( $post_id, $post, $update = false ) {
		if ( $update || ! self::post_can_embed_form( $post ) ) {
			return;
		}

		self::clear_if_relevant( $post_id, $post->post_content );
	}

	/**
	 * Maybe clear the cache when a post is updated.
	 *
	 * @since x.x
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post_after  Post object after the update.
	 * @param WP_Post $post_before Post object before the update.
	 *
	 * @return void
	 */
	public static function maybe_clear_on_update( $post_id, $post_after, $post_before ) {
		if ( ! self::post_can_embed_form( $post_after ) && ! self::post_can_embed_form( $post_before ) ) {
			return;
		}

		$signature_changed = self::get_embed_signature( $post_before->post_content ) !== self::get_embed_signature( $post_after->post_content );

		if ( ! $signature_changed && $post_before->post_status === $post_after->post_status ) {
			// The forms embedded in this post did not change, so the cached counts still hold.
			return;
		}

		self::clear_if_relevant( $post_id, $post_after->post_content . $post_before->post_content );
	}

	/**
	 * Maybe clear the cache when a post is trashed, untrashed or deleted.
	 *
	 * @since x.x
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post|null $post    Post object, when the hook provides one.
	 *
	 * @return void
	 */
	public static function maybe_clear_for_post( $post_id, $post = null ) {
		if ( ! is_object( $post ) ) {
			$post = get_post( $post_id );
		}

		if ( ! is_object( $post ) || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		self::clear_if_relevant( $post_id, $post->post_content );
	}

	/**
	 * Checks if a post is one the embeds query would look at.
	 *
	 * Revisions, autosaves and auto-drafts all fire wp_insert_post, and an active site creates
	 * them constantly. Letting those clear the cache would keep it permanently cold.
	 *
	 * @since x.x
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return bool
	 */
	private static function post_can_embed_form( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return false;
		}

		return 'auto-draft' !== $post->post_status;
	}

	/**
	 * Extracts the Formidable embed markup from post content so two revisions can be compared.
	 *
	 * @since x.x
	 *
	 * @param string $content Post content.
	 *
	 * @return string
	 */
	private static function get_embed_signature( $content ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return '';
		}

		$matches = array();
		preg_match_all( '/\[formidable\b[^\]]*\]|<!--\s*wp:formidable\/simple-form[^>]*-->/', $content, $matches );

		if ( ! $matches[0] ) {
			return '';
		}

		sort( $matches[0] );

		return implode( '|', $matches[0] );
	}

	/**
	 * Clears the cache, but only when the post could affect it.
	 *
	 * @since x.x
	 *
	 * @param int    $post_id Post ID.
	 * @param string $content Post content to test for an embed.
	 *
	 * @return void
	 */
	private static function clear_if_relevant( $post_id, $content ) {
		if ( str_contains( $content, '[formidable ' ) || str_contains( $content, '<!-- wp:formidable/simple-form ' ) ) {
			// This post embeds a form, so the cached counts may be stale.
			self::clear();
			return;
		}

		$cached_posts = get_transient( self::TRANSIENT_NAME );

		if ( ! is_array( $cached_posts ) ) {
			return;
		}

		// If the new post data of a cached post doesn't contain the Formidable forms, clear the cache.
		foreach ( $cached_posts as $posts ) {
			if ( ! is_array( $posts ) ) {
				continue;
			}

			foreach ( $posts as $post_data ) {
				if ( intval( $post_data->ID ) === intval( $post_id ) ) {
					// This post contained a form shortcode before the change, so clear the cache.
					self::clear();
					return;
				}
			}
		}
	}

	/**
	 * Clears the embed posts cache.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function clear() {
		self::$cached_posts = null;
		delete_transient( self::TRANSIENT_NAME );
	}
}
