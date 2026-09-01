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
	 * Every post ID that appears in the cache, flattened once per request.
	 *
	 * @since x.x
	 *
	 * @var array|null
	 */
	private static $cached_post_ids;

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
		self::$cached_posts    = $cached_posts;
		self::$cached_post_ids = null;
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

		// A post that has only just been created cannot be in the cache yet, so its own content
		// is the only thing worth checking. Anything heavier here gets paid once per row by a
		// bulk insert or an import.
		if ( self::content_has_embed( $post->post_content ) ) {
			self::clear();
		}
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

		$embedded_before = self::content_has_embed( $post_before->post_content );
		$embedded_after  = self::content_has_embed( $post_after->post_content );

		if ( $embedded_before !== $embedded_after ) {
			// A form embed was either added or removed.
			self::clear();
			return;
		}

		if ( $embedded_after ) {
			// Both versions embed a form, so compare which ones. A status change matters too,
			// since it can move the post in or out of the embeds query.
			$signature_changed = self::get_embed_signature( $post_before->post_content ) !== self::get_embed_signature( $post_after->post_content );

			if ( $signature_changed || $post_before->post_status !== $post_after->post_status ) {
				self::clear();
			}

			return;
		}

		if ( $post_before->post_content === $post_after->post_content && $post_before->post_status === $post_after->post_status ) {
			// Nothing that could affect the embeds list changed.
			return;
		}

		// Neither version embeds a form in its own content, so this post can only matter if the
		// frm_get_posts_contain_form filter is what put it in the cache.
		self::clear_if_cached( $post_id );
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

		if ( self::content_has_embed( $post->post_content ) ) {
			self::clear();
			return;
		}

		self::clear_if_cached( $post_id );
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
	 * Checks whether post content embeds a Formidable form.
	 *
	 * Deliberately two string searches and nothing more. This runs on every post save on the
	 * site, so it is the gate that keeps the regex and the cache lookup off the hot path.
	 *
	 * @since x.x
	 *
	 * @param string $content Post content.
	 *
	 * @return bool
	 */
	private static function content_has_embed( $content ) {
		if ( ! is_string( $content ) ) {
			return false;
		}

		return str_contains( $content, '[formidable ' ) || str_contains( $content, '<!-- wp:formidable/simple-form ' );
	}

	/**
	 * Clears the cache if the post is one of the posts it lists.
	 *
	 * @since x.x
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	private static function clear_if_cached( $post_id ) {
		if ( isset( self::get_cached_post_ids()[ intval( $post_id ) ] ) ) {
			self::clear();
		}
	}

	/**
	 * Flattens the cache into a lookup of the post IDs it lists.
	 *
	 * Built once per request, so a bulk operation reads the cache once and then answers each
	 * post with an array lookup instead of walking every form's post list every time.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_cached_post_ids() {
		if ( null !== self::$cached_post_ids ) {
			return self::$cached_post_ids;
		}

		self::$cached_post_ids = array();

		foreach ( self::get_cached_posts() as $posts ) {
			if ( ! is_array( $posts ) ) {
				continue;
			}

			foreach ( $posts as $post_data ) {
				if ( isset( $post_data->ID ) ) {
					self::$cached_post_ids[ intval( $post_data->ID ) ] = true;
				}
			}
		}

		return self::$cached_post_ids;
	}

	/**
	 * Clears the embed posts cache.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function clear() {
		self::$cached_post_ids = array();

		if ( array() === self::get_cached_posts() ) {
			// Nothing left to delete. Without this, a bulk insert of pages that embed a form
			// would run a delete query once per page.
			//
			// Reading the memoized copy rather than the transient is safe because
			// save_cached_posts() is the only thing that writes this key, and it refreshes the
			// memo. Pro and Landing delete the key on activation, which can only leave the memo
			// stale in the harmless direction: one redundant delete.
			return;
		}

		self::$cached_posts = array();

		delete_transient( self::TRANSIENT_NAME );
	}
}
