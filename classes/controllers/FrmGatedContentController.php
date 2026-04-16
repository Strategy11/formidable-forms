<?php
/**
 * Gated Content Controller
 *
 * @package Formidable
 *
 * @since x.x
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedContentController {

	/**
	 * Register action hooks.
	 *
	 * @since x.x
	 *
	 */
	public function __construct() {
		add_action( 'frm_trigger_gated_content_action', 'FrmGatedContentController::trigger', 10, 4 );
	}

	/**
	 * Generate a gated content token when a form action fires.
	 *
	 * Runs at form action priority 8 — before On Submit (9) and Send Email (10) —
	 * so the raw token is already in FrmGatedTokenHelper::$tokens when those
	 * actions process [frm_gated_content] shortcodes on the same request.
	 *
	 * @since x.x
	 *
	 * @param object $action Form action post object (post_excerpt = 'gated_content').
	 * @param object $entry  Submitted form entry object.
	 * @param object $form   Form object.
	 * @param string $event  Trigger event ('create', 'update', or 'import').
	 * @return void
	 */
	public static function trigger( $action, $entry, $form, $event ) {
		$user_id = get_current_user_id() ?: null;
		FrmGatedTokenHelper::generate( $action->ID, $entry->id, $user_id );
	}
}
