<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Exposes setup discovery even while Formidable AI access is disabled.
 *
 * @since 7.1
 */
class FrmAbilitiesSetupController {

	/**
	 * Register the read-only setup ability independently of the operational gate.
	 *
	 * @since 7.1
	 *
	 * @return void
	 */
	public static function register_abilities() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-ai-status',
			array(
				'label'               => __( 'Get Formidable AI Status', 'formidable' ),
				'description'         => __(
					'Check Formidable AI access, even when disabled. Use the settings link to enable access, then rediscover abilities. WPVibe needs no new connection.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				// No properties key. An empty PHP array would serialize as a JSON list instead of an object.
				'input_schema'        => array(
					'type'    => 'object',
					'default' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'enabled'        => array(
							'type'        => 'boolean',
							'description' => __( 'Whether Formidable AI access is enabled.', 'formidable' ),
						),
						'ready'          => array(
							'type'        => 'boolean',
							'description' => __(
								'Whether operational abilities are registered and the MCP adapter is compatible. Individual abilities still require permission.',
								'formidable'
							),
						),
						'blocked_reason' => array(
							'type'        => 'string',
							'description' => __( 'Why AI access is unavailable, or an empty string when ready.', 'formidable' ),
						),
						'can_enable'     => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the current user can change Formidable settings.', 'formidable' ),
						),
						'settings_url'   => array(
							'type'        => 'string',
							'description' => __( 'The settings page containing the AI access toggle.', 'formidable' ),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesSetupController::get_ai_status',
				'permission_callback' => 'FrmAbilitiesSetupController::can_get_ai_status',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Allow users who can view forms to discover setup, like the other read abilities.
	 *
	 * @since 7.1
	 *
	 * @return bool
	 */
	public static function can_get_ai_status() {
		FrmAbilitiesHelper::set_current_user();
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Describe availability without enabling access or booting the MCP adapter.
	 *
	 * @since 7.1
	 *
	 * @return array
	 */
	public static function get_ai_status() {
		// An older API add-on owns its toggle. Its registered operations reflect
		// that toggle even when Lite has a different saved value.
		$enabled = FrmMcpController::api_addon_owns_mcp() ? wp_has_ability( 'formidable-forms/list-forms' ) : FrmMcpController::is_enabled();
		$tab     = FrmMcpSettingsController::should_show_section() ? FrmMcpSettingsController::TAB_ANCHOR : 'api_settings';
		$reason  = '';

		if ( ! $enabled ) {
			$reason = __(
				'Formidable AI access is off. A user who can change Formidable settings must enable it using the settings link. Then discover abilities again.',
				'formidable'
			);
		} elseif ( ! wp_has_ability( 'formidable-forms/list-forms' ) ) {
			$reason = __(
				'Formidable AI access is enabled, but operational abilities are unavailable. Check the settings page and try discovery again.',
				'formidable'
			);
		} else {
			$reason = FrmMcpCompat::unsupported_reason();
		}

		return array(
			'enabled'        => $enabled,
			'ready'          => $enabled && '' === $reason,
			'blocked_reason' => $reason,
			'can_enable'     => current_user_can( 'frm_change_settings' ),
			'settings_url'   => admin_url( 'admin.php?page=formidable-settings&t=' . $tab ),
		);
	}
}
