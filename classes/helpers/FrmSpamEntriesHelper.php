<?php
/**
 * Spam entries helper.
 *
 * Spam entries are saved with the self::SPAM_ENTRY_STATUS status so they can be
 * reviewed in the Spam tab, but they are kept out of everything else by default: entry lists,
 * counts, views, dynamic fields, form actions and so on.
 *
 * @since x.x
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamEntriesHelper {

	/**
	 * "Spam" entry status, saved in the is_draft column.
	 * "2" and "3" are reserved by the abandonment add-on.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const SPAM_ENTRY_STATUS = 4;

	/**
	 * Save the submission as a spam entry and show the normal success response.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SAVE = 'save';

	/**
	 * Reject the submission and show the spam error message.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const BLOCK = 'block';

	/**
	 * The spam source detected for each form submitted in this request.
	 *
	 * @since x.x
	 *
	 * @var array<int, string>
	 */
	private static $flagged_forms = array();

	/**
	 * Get active add-ons that do not support the spam entry status.
	 *
	 * @since x.x
	 *
	 * @return string[] The add-on names that need an update.
	 */
	public static function get_incompatible_addons() {
		$addons       = array(
			'FrmProAppHelper'   => __( 'Formidable Pro', 'formidable' ),
			'FrmViewsAppHelper' => __( 'Formidable Views', 'formidable' ),
		);
		$incompatible = array();

		foreach ( $addons as $class => $name ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			$support = $class . '::SPAM_ENTRIES_SUPPORTED';

			if ( ! defined( $support ) || true !== constant( $support ) ) {
				$incompatible[] = $name;
			}
		}

		return $incompatible;
	}

	/**
	 * Spam entries may only be stored when every active add-on supports them.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function can_store_spam() {
		return ! self::get_incompatible_addons();
	}

	/**
	 * Get the default handling for each spam check.
	 *
	 * Bot checks are rejected by default because bots can send a high volume of submissions.
	 * Content checks are saved by default so a false positive can be recovered from the Spam tab.
	 * This runs while the global settings load, so it must not translate anything.
	 *
	 * @since x.x
	 *
	 * @return array<string, string>
	 */
	public static function get_default_handling() {
		return array(
			'honeypot'            => self::BLOCK,
			'antispam'            => self::BLOCK,
			'no_ip'               => self::BLOCK,
			'akismet'             => self::SAVE,
			'akismet_discard'     => self::BLOCK,
			'denylist'            => self::SAVE,
			'wp_disallowed_words' => self::SAVE,
			'wp_comments'         => self::SAVE,
			'stopforumspam'       => self::SAVE,
		);
	}

	/**
	 * Get every spam check that can flag a submission, keyed by source.
	 * Each source is an array with a label and its default handling.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public static function get_sources() {
		$labels   = array(
			'honeypot'            => __( 'Honeypot', 'formidable' ),
			'antispam'            => __( 'JavaScript anti-spam check', 'formidable' ),
			'no_ip'               => __( 'Missing IP address', 'formidable' ),
			'akismet'             => __( 'Akismet spam', 'formidable' ),
			'akismet_discard'     => __( 'Akismet blatant spam', 'formidable' ),
			'denylist'            => __( 'Denylist', 'formidable' ),
			'wp_disallowed_words' => __( 'WordPress disallowed words', 'formidable' ),
			'wp_comments'         => __( 'WordPress spam comments', 'formidable' ),
			'stopforumspam'       => __( 'StopForumSpam', 'formidable' ),
		);
		$sources  = array();
		$defaults = self::get_default_handling();

		foreach ( $labels as $source => $label ) {
			$sources[ $source ] = array(
				'label'   => $label,
				'default' => $defaults[ $source ],
			);
		}

		/**
		 * Allows adding spam checks that can be saved as spam entries.
		 * A custom check flags a submission with FrmSpamEntriesHelper::maybe_flag_submission().
		 *
		 * @since x.x
		 *
		 * @param array<string, array{label: string, default: string}> $sources
		 */
		$filtered = apply_filters( 'frm_spam_entry_sources', $sources );

		return is_array( $filtered ) ? $filtered : $sources;
	}

	/**
	 * Sanitize the posted spam handling setting.
	 *
	 * @since x.x
	 *
	 * @param mixed $handling Posted values keyed by source.
	 *
	 * @return array<string, string>
	 */
	public static function sanitize_handling( $handling ) {
		/**
		 * @var array<string, string> $sanitized
		 */
		$sanitized = wp_list_pluck( self::get_sources(), 'default' );

		if ( ! is_array( $handling ) ) {
			return $sanitized;
		}

		foreach ( $sanitized as $source => $default ) {
			if ( isset( $handling[ $source ] ) && in_array( $handling[ $source ], array( self::SAVE, self::BLOCK ), true ) ) {
				$sanitized[ $source ] = $handling[ $source ];
			}
		}

		return $sanitized;
	}

	/**
	 * Check if submissions flagged by a source should be saved as spam entries.
	 *
	 * @since x.x
	 *
	 * @param string $source The spam source key.
	 *
	 * @return bool
	 */
	public static function should_save( $source ) {
		if ( ! self::can_store_spam() ) {
			return false;
		}

		$frm_settings = FrmAppHelper::get_settings();
		$handling     = self::sanitize_handling( $frm_settings->spam_handling );
		$should_save  = isset( $handling[ $source ] ) && self::SAVE === $handling[ $source ];

		/**
		 * Allows changing whether a spam submission is saved as a spam entry or rejected.
		 *
		 * @since x.x
		 *
		 * @param bool   $should_save
		 * @param string $source
		 */
		return (bool) apply_filters( 'frm_save_spam_entry', $should_save, $source );
	}

	/**
	 * Flag the current submission of a form as spam so the entry is saved with the spam status.
	 *
	 * @since x.x
	 *
	 * @param int|string $form_id The submitted form ID.
	 * @param string     $source  The spam source key.
	 *
	 * @return bool True when the submission was flagged. False when it should be rejected instead.
	 */
	public static function maybe_flag_submission( $form_id, $source ) {
		if ( ! self::should_save( $source ) ) {
			return false;
		}

		self::$flagged_forms[ (int) $form_id ] = $source;

		return true;
	}

	/**
	 * Get the spam source flagged for the entry being created, if any.
	 * Child entries (repeaters and embedded forms) inherit the flag of their parent form.
	 *
	 * @since x.x
	 *
	 * @param array $values The values for the entry being created.
	 *
	 * @return string The spam source, or an empty string when the entry is not spam.
	 */
	public static function get_flagged_source( $values ) {
		foreach ( array( 'form_id', 'parent_form_id' ) as $key ) {
			$form_id = isset( $values[ $key ] ) ? (int) $values[ $key ] : 0;

			if ( $form_id && isset( self::$flagged_forms[ $form_id ] ) ) {
				return self::$flagged_forms[ $form_id ];
			}
		}

		return '';
	}

	/**
	 * Clear the spam flags for this request.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function reset_flags() {
		self::$flagged_forms = array();
	}

	/**
	 * Get the spam status and its label, to add to the entry statuses.
	 * It is added last, so a spam entry is never labelled as a draft, even when a filter leaves it out.
	 *
	 * @since x.x
	 *
	 * @return array<int, string>
	 */
	public static function get_entry_status() {
		return array( self::SPAM_ENTRY_STATUS => __( 'Spam', 'formidable' ) );
	}

	/**
	 * Get a copy of a spam entry with every value escaped. Other entries are returned as they are.
	 *
	 * @since x.x
	 *
	 * @param object $entry
	 *
	 * @return object
	 */
	public static function get_escaped_entry( $entry ) {
		if ( ! self::is_spam( $entry ) || empty( $entry->metas ) ) {
			return $entry;
		}

		$entry        = clone $entry;
		$entry->metas = self::escape_value( $entry->metas );

		return $entry;
	}

	/**
	 * @since x.x
	 *
	 * @param object|null $entry
	 *
	 * @return bool
	 */
	public static function is_spam( $entry ) {
		return is_object( $entry ) && isset( $entry->is_draft ) && self::SPAM_ENTRY_STATUS === (int) $entry->is_draft;
	}

	/**
	 * Get the label of the spam check that flagged an entry.
	 *
	 * @since x.x
	 *
	 * @param object $entry
	 *
	 * @return string
	 */
	public static function get_source_label( $entry ) {
		$description = $entry->description ?? array();
		FrmAppHelper::unserialize_or_decode( $description );

		if ( ! is_array( $description ) || empty( $description['spam_source'] ) || ! is_string( $description['spam_source'] ) ) {
			return '';
		}

		if ( 'manual' === $description['spam_source'] ) {
			return __( 'Manual review', 'formidable' );
		}

		$sources = self::get_sources();
		$source  = $description['spam_source'];

		return isset( $sources[ $source ]['label'] ) && is_string( $sources[ $source ]['label'] ) ? $sources[ $source ]['label'] : '';
	}

	/**
	 * Exclude spam entries from an frm_items query unless the query already checks the entry status,
	 * or targets specific entries by ID, key or parent.
	 *
	 * @since x.x
	 *
	 * @param array|string $where  The where query.
	 * @param string       $prefix The frm_items table alias, including the dot. An empty string when there is no alias.
	 *
	 * @return array|string
	 */
	public static function exclude_spam( $where, $prefix = 'it.' ) {
		if ( is_string( $where ) && preg_match( '/^' . preg_quote( $prefix, '/' ) . 'form_id=\d+$/', $where ) ) {
			$where = array( $prefix . 'form_id' => (int) substr( $where, strlen( $prefix . 'form_id=' ) ) );
		}

		if ( ! is_array( $where ) || ! self::should_exclude_spam( $where ) ) {
			return $where;
		}

		$where[] = self::get_exclude_spam_where( $prefix );

		return $where;
	}

	/**
	 * Get the where group that excludes spam entries. Entries without a status are kept.
	 *
	 * @since x.x
	 *
	 * @param string $prefix The frm_items table alias, including the dot.
	 *
	 * @return array
	 */
	public static function get_exclude_spam_where( $prefix = 'it.' ) {
		return array(
			'or'                   => 1,
			$prefix . 'is_draft !' => self::SPAM_ENTRY_STATUS,
			$prefix . 'is_draft'   => null,
		);
	}

	/**
	 * @since x.x
	 *
	 * @param array $where
	 *
	 * @return bool
	 */
	private static function should_exclude_spam( $where ) {
		if ( self::where_has_key( $where, array( 'is_draft' ) ) ) {
			return false;
		}

		$targets_entries = self::where_has_key( $where, array( 'id', 'item_key', 'parent_item_id' ), false );

		/**
		 * Allows including spam entries in an entry query that does not check the entry status.
		 *
		 * @since x.x
		 *
		 * @param bool  $exclude Whether to exclude spam entries.
		 * @param array $where   The where query.
		 */
		return (bool) apply_filters( 'frm_exclude_spam_entries', ! $targets_entries, $where );
	}

	/**
	 * Check if a where query includes a column.
	 *
	 * @since x.x
	 *
	 * @param array    $where
	 * @param string[] $columns
	 * @param bool     $nested Whether to check nested where groups.
	 *
	 * @return bool
	 */
	private static function where_has_key( $where, $columns, $nested = true ) {
		foreach ( $where as $key => $value ) {
			if ( is_numeric( $key ) ) {
				if ( $nested && is_array( $value ) && self::where_has_key( $value, $columns ) ) {
					return true;
				}
				continue;
			}

			// Remove the table alias and any operator, so "it.is_draft !" becomes "is_draft".
			$column = explode( ' ', trim( $key ) );
			$column = explode( '.', $column[0] );
			$column = end( $column );

			if ( in_array( $column, $columns, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Escape every submitted value of a spam entry so no HTML from the submission reaches the page.
	 *
	 * @since x.x
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public static function escape_value( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( self::class, 'escape_value' ), $value );
		}

		return is_string( $value ) ? esc_html( $value ) : $value;
	}

	/**
	 * @since x.x
	 *
	 * @param int|string $form_id A form ID, or 0 for all top level forms.
	 *
	 * @return int
	 */
	public static function count( $form_id = 0 ) {
		$where = array( 'it.is_draft' => self::SPAM_ENTRY_STATUS );

		if ( $form_id ) {
			$where['it.form_id'] = (int) $form_id;
		} else {
			$where[] = array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			);
		}

		return (int) FrmEntry::getRecordCount( $where );
	}

	/**
	 * Check whether an entry was manually marked as spam.
	 *
	 * @since x.x
	 *
	 * @param object $entry The entry to check.
	 *
	 * @return bool
	 */
	public static function is_manual_spam( $entry ) {
		$description = $entry->description ?? array();
		FrmAppHelper::unserialize_or_decode( $description );

		return is_array( $description ) && 'manual' === ( $description['spam_source'] ?? '' );
	}

	/**
	 * Move a submitted entry to spam without triggering form actions.
	 *
	 * @since x.x
	 *
	 * @param int $entry_id The entry being moderated.
	 *
	 * @return bool Whether the entry was marked as spam.
	 */
	public static function mark_as_spam( $entry_id ) {
		global $wpdb;

		if ( ! self::can_store_spam() ) {
			return false;
		}

		$entry = FrmEntry::getOne( $entry_id );

		if ( ! $entry || FrmEntriesHelper::SUBMITTED_ENTRY_STATUS !== (int) $entry->is_draft ) {
			return false;
		}

		$description = $entry->description;
		FrmAppHelper::unserialize_or_decode( $description );

		if ( ! is_array( $description ) ) {
			$description = array();
		}

		$description['spam_source'] = 'manual';

		$updated = $wpdb->update(
			$wpdb->prefix . 'frm_items',
			array(
				'is_draft'    => self::SPAM_ENTRY_STATUS,
				'description' => wp_json_encode( $description ),
			),
			array(
				'id'       => $entry_id,
				'is_draft' => FrmEntriesHelper::SUBMITTED_ENTRY_STATUS,
			)
		);

		if ( ! $updated ) {
			return false;
		}

		self::set_status( $entry_id, self::SPAM_ENTRY_STATUS );
		do_action( 'frm_entry_marked_spam', $entry_id );
		return true;
	}

	/**
	 * Change the status of an entry and of its child entries.
	 *
	 * @since x.x
	 *
	 * @param int|string $entry_id
	 * @param int|string $status
	 *
	 * @return void
	 */
	public static function set_status( $entry_id, $status ) {
		global $wpdb;

		if ( self::SPAM_ENTRY_STATUS === (int) $status && ! self::can_store_spam() ) {
			return;
		}

		$entry_id = (int) $entry_id;

		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => $status ), array( 'id' => $entry_id ) );
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => $status ), array( 'parent_item_id' => $entry_id ) );

		FrmEntry::clear_cache();
	}
}
