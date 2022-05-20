<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.05
 */
class FrmInboxController {

	/**
	 * @since 4.05
	 */
	public static function menu() {
		$unread = self::get_notice_count();
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Inbox', 'formidable' ), __( 'Inbox', 'formidable' ) . $unread, 'frm_change_settings', 'formidable-inbox', 'FrmInboxController::inbox' );
	}

	/**
	 * @since 4.05
	 */
	private static function get_notice_count() {
		FrmFormMigratorsHelper::maybe_add_to_inbox();

		$inbox = new FrmInbox();
		return $inbox->unread_html();
	}

	/**
	 * @since 4.06
	 */
	public static function dismiss_all_button( $atts ) {
		if ( empty( $atts['messages'] ) ) {
			return;
		}

		echo '<button class="button-secondary frm-button-secondary" id="frm-dismiss-inbox" type="button">' .
			esc_html__( 'Dismiss All', 'formidable' ) .
			'</button>';
	}

	/**
	 * @since 4.05
	 */
	public static function inbox() {
		FrmAppHelper::include_svg();
		self::add_tracking_request();
		self::add_free_template_message();

		$inbox    = new FrmInbox();
		$messages = $inbox->get_messages( 'filter' );
		$messages = array_reverse( $messages );
		$user     = wp_get_current_user();

		wp_enqueue_script( 'frm-ac', FrmAppHelper::plugin_url() . '/js/ac.js', array(), FrmAppHelper::plugin_version(), true );

		include( FrmAppHelper::plugin_path() . '/classes/views/inbox/list.php' );
	}

	/**
	 * @since 4.05
	 */
	public static function dismiss_message() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		$key = FrmAppHelper::get_param( 'key', '', 'post', 'sanitize_text_field' );
		if ( ! empty( $key ) ) {
			$message = new FrmInbox();
			$message->dismiss( $key );
			if ( $key === 'review' ) {
				$reviews = new FrmReviews();
				$reviews->dismiss_review();
			}
		}

		wp_die();
	}

	/**
	 * @since 4.05
	 */
	private static function add_tracking_request() {
		$settings = FrmAppHelper::get_settings();
		if ( $settings->tracking ) {
			return;
		}

		$link = admin_url( 'admin.php?page=formidable-settings&t=misc_settings' );

		$message = new FrmInbox();
		$message->add_message(
			array(
				'key'     => 'usage',
				'message' => 'Gathering usage data allows us to improve Formidable. Your forms will be considered as we evaluate new features, judge the quality of an update, or determine if an improvement makes sense. You can always visit the <a href="' . esc_url( $link ) . '">Global Settings</a> and choose to stop sharing data. <a href="https://formidableforms.com/knowledgebase/global-settings-overview/#kb-usage-tracking" target="_blank" rel="noopener noreferrer">Read more about what data we collect</a>.',
				'subject' => __( 'Help Formidable improve with usage tracking', 'formidable' ),
				'cta'     => '<a href="#" class="button-secondary frm-button-secondary frm_inbox_dismiss">Dismiss</a> <a href="' . esc_url( $link ) . '" class="button-primary frm-button-primary frm_inbox_dismiss">Activate usage tracking</a>',
				'type'    => 'feedback',
			)
		);
	}

	/**
	 * Adds free template design.
	 *
	 * @since 4.10.03
	 */
	private static function add_free_template_message() {
		if ( FrmAppHelper::pro_is_installed() ) {
			return;
		}

		$api = new FrmFormTemplateApi();
		if ( $api->has_free_access() ) {
			return;
		}

		$link = admin_url( 'admin.php?page=formidable&triggerNewFormModal=1&free-templates=1' );

		$message = new FrmInbox();
		$message->add_message(
			array(
				'key'     => 'free_templates',
				'message' => 'Add your email address to get a code for 10+ free form templates.',
				'subject' => 'Get 10+ Free Form Templates',
				'cta'     => '<a href="#" class="button-secondary frm-button-secondary frm_inbox_dismiss">Dismiss</a> <a href="' . esc_url( $link ) . '" class="button-primary frm-button-primary">Get Now</a>',
				'type'    => 'feedback',
			)
		);
	}
}
