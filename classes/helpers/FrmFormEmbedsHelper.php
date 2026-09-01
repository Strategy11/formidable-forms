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
	 * Expanded affected form IDs, keyed by the embedded form IDs they came from.
	 *
	 * @since x.x
	 *
	 * @var array<string,array<int>>
	 */
	private static $affected_form_ids = array();

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
	 * Reduces posts to the fields the Embeds column actually renders, before they are cached.
	 *
	 * The frm_get_posts_contain_form filter is free to return whole WP_Post objects, and the
	 * Landing Pages add-on does. Caching those stores post_content and every other column, and
	 * because the column JSON encodes this straight into a data-posts attribute it also puts
	 * full post content, drafts included, into the admin page markup.
	 *
	 * @since x.x
	 *
	 * @param array $posts Posts that embed a form.
	 *
	 * @return \stdClass[]
	 */
	public static function slim_posts( $posts ) {
		/**
		 * Filters the post fields kept in the embeds cache.
		 *
		 * Anything rendered by the Embeds dropdown has to be listed here to survive caching.
		 *
		 * @since x.x
		 *
		 * @param string[] $fields Property names to keep.
		 */
		$fields = apply_filters(
			'frm_embed_post_cached_fields',
			array( 'ID', 'post_title', 'post_name', 'title_contains_html', 'permalink', 'edit_link' )
		);

		$slim = array();

		foreach ( $posts as $post ) {
			if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
				continue;
			}

			$kept = array();

			foreach ( $fields as $field ) {
				if ( property_exists( $post, $field ) ) {
					$kept[ $field ] = $post->$field;
				}
			}

			$slim[] = (object) $kept;
		}

		return $slim;
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
		$prepared = array();

		foreach ( $posts as $post ) {
			// Copied so the derived values never end up back in the cached original.
			$display = clone $post;

			if ( ! property_exists( $display, 'permalink' ) ) {
				$display->permalink = get_permalink( $display->ID );
			}

			if ( ! property_exists( $display, 'edit_link' ) ) {
				$display->edit_link = get_edit_post_link( $display->ID );
			}

			// Ensure post_name is not null or the string "null"
			if ( ! isset( $display->post_name ) ) {
				$display->post_name = '';
			}

			// Ensure post_title is not null or the string "null"
			if ( ! isset( $display->post_title ) ) {
				$display->post_title = '';
			}

			if ( '' === $display->post_title ) {
				$display->post_title = __( '(no title)', 'formidable' );
			}

			$prepared[] = $display;
		}//end foreach

		return $prepared;
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
		if ( ! self::content_has_embed( $post->post_content ) ) {
			return;
		}

		self::clear_for_forms( self::get_affected_form_ids( self::get_embedded_form_ids( $post->post_content ) ) );
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

		$before_ids     = self::get_embedded_form_ids( $post_before->post_content );
		$after_ids      = self::get_embedded_form_ids( $post_after->post_content );
		$status_changed = $post_before->post_status !== $post_after->post_status;
		$embeds_changed = $before_ids !== $after_ids;

		// The dropdown lists the title and slug, so renaming a post makes the cached copy wrong.
		$label_changed = $post_before->post_title !== $post_after->post_title || $post_before->post_name !== $post_after->post_name;

		$form_ids = array();

		if ( $embeds_changed || $status_changed ) {
			// The forms this post embeds changed, or a status change moved the post in or out
			// of the embeds query.
			$form_ids = self::get_affected_form_ids( array_merge( $before_ids, $after_ids ) );
		}

		$filter_added = ! $before_ids && ! $after_ids && $post_before->post_content !== $post_after->post_content;

		if ( $label_changed || $status_changed || $filter_added ) {
			// A post the frm_get_posts_contain_form filter added is listed even though its own
			// content does not say so, so fall back to whichever rows actually list it.
			$form_ids = array_merge( $form_ids, self::get_cached_form_ids_for_post( $post_id ) );
		}

		self::clear_for_forms( $form_ids );
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

		$form_ids = self::get_affected_form_ids( self::get_embedded_form_ids( $post->post_content ) );

		self::clear_for_forms( array_merge( $form_ids, self::get_cached_form_ids_for_post( $post_id ) ) );
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
	 * Checks whether post content embeds a Formidable form.
	 *
	 * Deliberately two string searches and nothing more. This runs on every post save on the
	 * site, so it is the gate that keeps the parsing off the hot path.
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
	 * Extracts the IDs of the forms embedded in post content.
	 *
	 * Only id= shortcodes and the simple-form block are recognised, matching what
	 * FrmFormsListHelper::get_base_search_strings_for_form() looks for. A key= shortcode is
	 * deliberately not matched, because the Embeds column cannot find it either.
	 *
	 * @since x.x
	 *
	 * @param string $content Post content.
	 *
	 * @return array Sorted, unique form IDs.
	 */
	private static function get_embedded_form_ids( $content ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return array();
		}

		$ids     = array();
		$matches = array();

		// [formidable id=5], [formidable key="contact-form"], and every quoting of both.
		preg_match_all( '/\[formidable\b[^\]]*\b(?:id|key)=["\']?([A-Za-z0-9_\-]+)/', $content, $matches );

		if ( $matches[1] ) {
			$ids = $matches[1];
		}

		$matches = array();

		// <!-- wp:formidable/simple-form {"formId":"5" ... -->.
		preg_match_all( '/wp:formidable\/simple-form\s*\{[^}]*"formId":"?(\d+)/', $content, $matches );

		if ( $matches[1] ) {
			$ids = array_merge( $ids, $matches[1] );
		}

		$form_ids = array();

		foreach ( $ids as $id ) {
			// A key in either attribute resolves to the same form the shortcode would render.
			$form_ids[] = is_numeric( $id ) ? intval( $id ) : FrmForm::get_id_by_key( $id );
		}

		$form_ids = array_filter( $form_ids );

		$form_ids = array_unique( $form_ids );
		sort( $form_ids );

		return $form_ids;
	}

	/**
	 * Expands the forms embedded in a post to every form whose cached list they affect.
	 *
	 * A form's embeds list can be matched by a shortcode for a different form. Pro's nested
	 * forms are the case that matters: FrmProFormsListHelper::get_search_strings_for_form()
	 * makes form G's list match [formidable id=P] whenever form P embeds form G, so a change to
	 * a post embedding P has to invalidate G too.
	 *
	 * @since x.x
	 *
	 * @param array $form_ids Form IDs embedded in the post content.
	 *
	 * @return array
	 */
	private static function get_affected_form_ids( $form_ids ) {
		if ( ! $form_ids ) {
			return array();
		}

		$ids = array();

		foreach ( $form_ids as $form_id ) {
			$ids[] = intval( $form_id );
		}

		$form_ids = array_unique( $ids );
		sort( $form_ids );

		$key = implode( ',', $form_ids );

		if ( isset( self::$affected_form_ids[ $key ] ) ) {
			// Listeners hit the database to work this out, so a bulk import of pages embedding
			// the same form must not pay for it once per page.
			return self::$affected_form_ids[ $key ];
		}

		/**
		 * Filters the forms whose cached embeds list is affected by a post embedding $form_ids.
		 *
		 * Anything that widens get_search_strings_for_form() has to widen this to match, or the
		 * forms it added will keep a stale count.
		 *
		 * @since x.x
		 *
		 * @param array $affected_form_ids Form IDs whose cached lists are affected.
		 * @param array $form_ids          Form IDs embedded in the post content.
		 */
		$affected = apply_filters( 'frm_form_ids_affected_by_embed', $form_ids, $form_ids );

		if ( ! is_array( $affected ) ) {
			$affected = $form_ids;
		}

		$expanded = array();

		foreach ( $affected as $form_id ) {
			$expanded[] = intval( $form_id );
		}

		$expanded = array_values( array_unique( $expanded ) );

		self::$affected_form_ids[ $key ] = $expanded;

		return $expanded;
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
	 * Gets the cached forms that currently list a post.
	 *
	 * @since x.x
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array
	 */
	private static function get_cached_form_ids_for_post( $post_id ) {
		$post_id = intval( $post_id );

		if ( ! isset( self::get_cached_post_ids()[ $post_id ] ) ) {
			// Answers the overwhelming majority of saves without walking the cache.
			return array();
		}

		$form_ids = array();

		foreach ( self::get_cached_posts() as $form_id => $posts ) {
			if ( ! is_array( $posts ) ) {
				continue;
			}

			foreach ( $posts as $post_data ) {
				if ( isset( $post_data->ID ) && intval( $post_data->ID ) === $post_id ) {
					$form_ids[] = intval( $form_id );
					break;
				}
			}
		}

		return $form_ids;
	}

	/**
	 * Drops the given forms from the cache and keeps everything else.
	 *
	 * At 100k posts a rebuild costs seconds, so keeping the forms that did not change is worth
	 * far more than the cost of writing the map back.
	 *
	 * @since x.x
	 *
	 * @param array $form_ids Form IDs to drop.
	 *
	 * @return void
	 */
	private static function clear_for_forms( $form_ids ) {
		if ( ! $form_ids ) {
			return;
		}

		if ( ! self::can_target_forms() ) {
			self::clear();
			return;
		}

		$cached_posts = self::get_cached_posts();

		if ( array() === $cached_posts ) {
			return;
		}

		$dropped = false;

		foreach ( $form_ids as $form_id ) {
			if ( ! array_key_exists( $form_id, $cached_posts ) ) {
				continue;
			}

			unset( $cached_posts[ $form_id ] );
			$dropped = true;
		}

		if ( ! $dropped ) {
			// None of the affected forms are cached, so every cached count is still accurate.
			return;
		}

		if ( array() === $cached_posts ) {
			self::clear();
			return;
		}

		self::save_cached_posts( $cached_posts );
	}

	/**
	 * Checks whether the affected forms can be worked out precisely.
	 *
	 * Pro widens get_search_strings_for_form() for nested forms. A Pro old enough not to hook
	 * frm_form_ids_affected_by_embed cannot tell us which extra forms a post reaches, so on
	 * those installs the whole cache is cleared rather than risk leaving a stale count.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function can_target_forms() {
		if ( has_filter( 'frm_form_ids_affected_by_embed' ) ) {
			return true;
		}

		return ! FrmAppHelper::pro_is_installed();
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
