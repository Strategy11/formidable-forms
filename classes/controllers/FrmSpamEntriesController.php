<?php
/**
 * Spam entries controller.
 *
 * @since x.x
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamEntriesController {

	/**
	 * The GET param and value used for the Spam tab of the entries list.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const TAB_PARAM = 'entry_status';

	/**
	 * @since x.x
	 *
	 * @var string
	 */
	const TAB_VALUE = 'spam';

	/**
	 * Form action types that need data from the original browser session, so they cannot run later.
	 *
	 * @since x.x
	 *
	 * @var string[]
	 */
	const ACTIONS_THAT_CANNOT_RUN_LATER = array( 'payment', 'stripe', 'square', 'paypal', 'authnet_aim', 'on_submit' );

	/**
	 * The form actions selected in the "Not spam" modal while they are being triggered.
	 *
	 * @since x.x
	 *
	 * @var int[]
	 */
	private static $selected_action_ids = array();

	/**
	 * Check if the entries list is showing the Spam tab.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_spam_tab() {
		return self::TAB_VALUE === FrmAppHelper::simple_get( self::TAB_PARAM, 'sanitize_title' );
	}

	/**
	 * Get the URL of an entries list tab.
	 *
	 * @since x.x
	 *
	 * @param bool       $spam    True for the Spam tab.
	 * @param int|string $form_id The form being filtered, if any.
	 *
	 * @return string
	 */
	public static function get_tab_url( $spam, $form_id = 0 ) {
		$args = array( 'page' => 'formidable-entries' );

		if ( $form_id ) {
			$args['form'] = (int) $form_id;
		}

		if ( $spam ) {
			$args[ self::TAB_PARAM ] = self::TAB_VALUE;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Show the Entries and Spam tabs above the entries list.
	 *
	 * @since x.x
	 *
	 * @param false|object $form The form being filtered, if any.
	 *
	 * @return void
	 */
	public static function show_tabs( $form ) {
		$form_id    = $form ? $form->id : 0;
		$active_tab = self::is_spam_tab() ? 'spam' : 'entries';
		$spam_count = FrmSpamEntriesHelper::count( $form_id );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/tabs.php';
	}

	/**
	 * Spam entries can be viewed, marked as not spam or deleted. They cannot be edited or duplicated.
	 *
	 * @since x.x
	 *
	 * @param array  $actions Row action links keyed by action.
	 * @param object $item    The entry in the row.
	 *
	 * @return array
	 */
	public static function row_actions( $actions, $item ) {
		if ( ! FrmSpamEntriesHelper::is_spam( $item ) ) {
			if ( self::can_mark_as_spam( $item ) ) {
				$actions['mark_spam'] = '<a href="' . esc_url( self::get_mark_spam_url( $item ) ) . '">' . esc_html__( 'Mark as spam', 'formidable' ) . '</a>';
			}

			return $actions;
		}

		unset( $actions['edit'], $actions['duplicate'] );

		if ( ! self::current_user_can_moderate() ) {
			return $actions;
		}

		$not_spam = array(
			'not_spam' => '<a href="' . esc_url( self::get_not_spam_url( $item ) ) . '">' . esc_html__( 'Not spam', 'formidable' ) . '</a>',
		);

		// Show "Not spam" right after "View".
		return array_slice( $actions, 0, 1, true ) + $not_spam + array_slice( $actions, 1, null, true );
	}

	/**
	 * Remove the edit, duplicate and resend links from the sidebar of a spam entry, and add "Not spam".
	 *
	 * @since x.x
	 *
	 * @param array $actions Sidebar action links keyed by action.
	 * @param array $args    Includes `id` and `entry`.
	 *
	 * @return array
	 */
	public static function sidebar_actions( $actions, $args ) {
		$entry = $args['entry'] ?? null;

		if ( ! FrmSpamEntriesHelper::is_spam( $entry ) ) {
			if ( self::can_mark_as_spam( $entry ) ) {
				$actions['frm_mark_spam'] = array(
					'url'   => self::get_mark_spam_url( $entry ),
					'label' => __( 'Mark as spam', 'formidable' ),
					'icon'  => 'frmfont frm_alert_icon',
				);
			}

			return $actions;
		}

		unset( $actions['frm_edit'], $actions['frm_duplicate'], $actions['frm_resend'] );

		if ( ! self::current_user_can_moderate() ) {
			return $actions;
		}

		$not_spam = array(
			'frm_not_spam' => array(
				'url'   => self::get_not_spam_url( $entry ),
				'label' => __( 'Mark as Not Spam', 'formidable' ),
				'class' => 'frm-open-not-spam-modal',
				'icon'  => 'frmfont frm_checkmark_icon',
			),
		);

		return $not_spam + $actions;
	}

	/**
	 * Show a notice and the "Not spam" modal at the top of a spam entry.
	 *
	 * @since x.x
	 *
	 * @param array $args Includes `id` and `form`.
	 *
	 * @return void
	 */
	public static function show_spam_notice( $args ) {
		$entry = FrmEntry::getOne( $args['id'] );

		if ( ! FrmSpamEntriesHelper::is_spam( $entry ) ) {
			return;
		}

		$form            = $args['form'];
		$source_label    = FrmSpamEntriesHelper::get_source_label( $entry );
		$can_moderate    = self::current_user_can_moderate();
		$manual_spam     = FrmSpamEntriesHelper::is_manual_spam( $entry );
		$pending_actions = $can_moderate && ! $manual_spam ? self::get_pending_actions( $form ) : array();
		$open_modal      = $can_moderate && FrmAppHelper::simple_get( 'not_spam', 'absint' );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/spam-notice.php';
	}

	/**
	 * Mark a submitted entry as spam after checking moderation permission and the nonce.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function mark_spam() {
		$entry_id = FrmAppHelper::simple_get( 'id', 'absint' );
		$nonce    = FrmAppHelper::simple_get( '_wpnonce', 'sanitize_text_field' );

		if ( ! self::current_user_can_moderate() || ! wp_verify_nonce( $nonce, 'frm_mark_spam_' . $entry_id ) ) {
			FrmAppController::show_error_modal(
				array(
					'title'      => __( 'Verification failed', 'formidable' ),
					'body'       => __( 'Unable to verify your request. Please reload the page and try again.', 'formidable' ),
					'cancel_url' => self::get_tab_url( false ),
				)
			);
			return;
		}

		$marked  = FrmSpamEntriesHelper::mark_as_spam( $entry_id );
		$message = $marked ? __( 'The entry was marked as spam.', 'formidable' ) : __( 'The entry could not be marked as spam.', 'formidable' );
		FrmEntriesController::show( $entry_id, $message );
	}

	/**
	 * @since x.x
	 *
	 * @param object|null $entry The entry being moderated.
	 *
	 * @return bool
	 */
	private static function can_mark_as_spam( $entry ) {
		if ( ! is_object( $entry ) || ! isset( $entry->is_draft ) || FrmEntriesHelper::SUBMITTED_ENTRY_STATUS !== (int) $entry->is_draft ) {
			return false;
		}

		return self::current_user_can_moderate() && FrmSpamEntriesHelper::can_store_spam();
	}

	/**
	 * @since x.x
	 *
	 * @param object $entry The entry being moderated.
	 *
	 * @return string
	 */
	private static function get_mark_spam_url( $entry ) {
		$url = add_query_arg(
			array(
				'page'       => 'formidable-entries',
				'frm_action' => 'mark_spam',
				'id'         => $entry->id,
			),
			admin_url( 'admin.php' )
		);

		return wp_nonce_url( $url, 'frm_mark_spam_' . $entry->id );
	}

	/**
	 * Get the form actions that would have run when the entry was created.
	 *
	 * @since x.x
	 *
	 * @param object $form
	 *
	 * @return array The action posts, keyed by ID.
	 */
	public static function get_pending_actions( $form ) {
		$pending = array();

		foreach ( FrmFormAction::get_action_for_form( $form->id ) as $action ) {
			$events = $action->post_content['event'] ?? array();

			if ( ! in_array( 'create', (array) $events, true ) || in_array( $action->post_excerpt, self::ACTIONS_THAT_CANNOT_RUN_LATER, true ) ) {
				continue;
			}

			$pending[ $action->ID ] = $action;
		}

		/**
		 * Allows changing the form actions that can run when an entry is marked as not spam.
		 *
		 * @since x.x
		 *
		 * @param WP_Post[] $pending Actions keyed by ID.
		 * @param object    $form
		 */
		$filtered = apply_filters( 'frm_not_spam_pending_actions', $pending, $form );

		return is_array( $filtered ) ? $filtered : $pending;
	}

	/**
	 * Move a spam entry back to the entries list, and run the form actions that were selected.
	 * Triggered by the form in the "Not spam" modal.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function not_spam() {
		$entry_id = FrmAppHelper::get_post_param( 'id', 0, 'absint' );
		$error    = self::get_not_spam_error();

		if ( $error ) {
			FrmAppController::show_error_modal(
				array(
					'title'      => __( 'Verification failed', 'formidable' ),
					'body'       => $error,
					'cancel_url' => admin_url( 'admin.php?page=formidable-entries' ),
				)
			);
			return;
		}

		$entry = FrmEntry::getOne( $entry_id );

		if ( ! FrmSpamEntriesHelper::is_spam( $entry ) ) {
			FrmEntriesController::show( $entry_id );
			return;
		}

		if ( ! FrmSpamEntriesHelper::try_set_status( $entry_id, FrmEntriesHelper::SUBMITTED_ENTRY_STATUS ) ) {
			FrmAppController::show_error_modal(
				array(
					'title'      => __( 'Unable to restore entry', 'formidable' ),
					'body'       => __( 'The entry could not be marked as not spam. Please try again.', 'formidable' ),
					'cancel_url' => admin_url( 'admin.php?page=formidable-entries' ),
				)
			);
			return;
		}

		$action_ids = FrmSpamEntriesHelper::is_manual_spam( $entry ) ? array() : self::get_selected_action_ids( $entry->form_id );

		if ( $action_ids ) {
			self::trigger_selected_actions( $entry_id, $action_ids );
		}

		/**
		 * Fires after a spam entry is marked as not spam.
		 *
		 * @since x.x
		 *
		 * @param int   $entry_id
		 * @param int[] $action_ids The form actions that were triggered.
		 */
		do_action( 'frm_entry_marked_not_spam', $entry_id, $action_ids );

		FrmEntriesController::show( $entry_id, __( 'The entry was marked as not spam.', 'formidable' ) );
	}

	/**
	 * @since x.x
	 *
	 * @return string The error message, or an empty string when the request is allowed.
	 */
	private static function get_not_spam_error() {
		if ( ! self::current_user_can_moderate() ) {
			return FrmAppHelper::get_settings()->admin_permission;
		}

		$nonce = FrmAppHelper::get_post_param( 'frm_not_spam_nonce', '', 'sanitize_text_field' );

		if ( ! wp_verify_nonce( $nonce, 'frm_not_spam' ) ) {
			return __( 'Unable to verify your request. Please reload the page and try again.', 'formidable' );
		}

		return '';
	}

	/**
	 * Get the posted action IDs that can run for the entry's form.
	 *
	 * @since x.x
	 *
	 * @param int|string $form_id
	 *
	 * @return int[]
	 */
	private static function get_selected_action_ids( $form_id ) {
		$posted = array();

		// The nonce is checked in get_not_spam_error() before this runs.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['frm_not_spam_actions'] ) && is_array( $_POST['frm_not_spam_actions'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$posted = array_map( 'absint', wp_unslash( $_POST['frm_not_spam_actions'] ) );
		}

		$form = FrmForm::getOne( $form_id );

		if ( ! $posted || ! $form ) {
			return array();
		}

		return array_values( array_intersect( $posted, array_keys( self::get_pending_actions( $form ) ) ) );
	}

	/**
	 * Trigger the create actions for an entry, limited to the selected actions.
	 * Conditional logic still applies.
	 *
	 * @since x.x
	 *
	 * @param int   $entry_id
	 * @param int[] $action_ids
	 *
	 * @return void
	 */
	private static function trigger_selected_actions( $entry_id, $action_ids ) {
		$entry = FrmEntry::getOne( $entry_id, true );

		if ( ! $entry ) {
			return;
		}

		self::$selected_action_ids = $action_ids;

		add_filter( 'frm_skip_form_action', self::class . '::skip_unselected_action', 99, 2 );
		FrmFormActionsController::trigger_actions( 'create', $entry->form_id, $entry );
		remove_filter( 'frm_skip_form_action', self::class . '::skip_unselected_action', 99 );

		self::$selected_action_ids = array();
	}

	/**
	 * Skip every form action that was not selected in the "Not spam" modal.
	 *
	 * @since x.x
	 *
	 * @param bool  $skip Whether the action is already being skipped.
	 * @param array $args Includes `action`, `entry`, `form` and `event`.
	 *
	 * @return bool
	 */
	public static function skip_unselected_action( $skip, $args ) {
		return $skip || ! in_array( (int) $args['action']->ID, self::$selected_action_ids, true );
	}

	/**
	 * @since x.x
	 *
	 * @param object $entry
	 *
	 * @return string
	 */
	private static function get_not_spam_url( $entry ) {
		return add_query_arg(
			array(
				'page'       => 'formidable-entries',
				'frm_action' => 'show',
				'id'         => (int) $entry->id,
				'not_spam'   => 1,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Deciding whether an entry is spam is moderation, so it uses the same permission as deleting entries.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function current_user_can_moderate() {
		return FrmAppHelper::current_user_can( 'frm_delete_entries' );
	}
}
