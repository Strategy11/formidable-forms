<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * The MCP section of the global settings page.
 *
 * Holds the toggle that turns the Formidable MCP server and the Formidable
 * abilities on, and the summary of who has connected to it.
 *
 * The section is hidden entirely while an API add-on that predates the move
 * still shows its own MCP toggle, so a site never has two toggles for one
 * feature. That add-on's toggle keeps working, and Formidable reads it: see
 * FrmMcpController::is_enabled().
 *
 * @since x.x
 */
class FrmMcpSettingsController {

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_filter( 'frm_add_settings_section', 'FrmMcpSettingsController::add_settings_section' );
		add_action( 'frm_store_settings', 'FrmMcpController::reset' );
	}

	/**
	 * Add the MCP section to the global settings page.
	 *
	 * @since x.x
	 * @see filter hook frm_add_settings_section
	 *
	 * @param array<array> $sections Sections registered for the Global Settings page so far.
	 *
	 * @return array<array>
	 */
	public static function add_settings_section( $sections ) {
		if ( ! self::should_show_section() ) {
			return $sections;
		}

		$sections['mcp'] = array(
			'class'    => 'FrmMcpSettingsController',
			'function' => 'route',
			'name'     => __( 'MCP', 'formidable' ),
			'icon'     => 'frmfont frm_bolt_icon',
		);

		return $sections;
	}

	/**
	 * Check whether Formidable should show the MCP section.
	 *
	 * Hidden while an API add-on that still renders its own MCP toggle is
	 * active, so the site has one toggle rather than two that disagree.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function should_show_section() {
		return ! self::api_addon_shows_toggle();
	}

	/**
	 * Check whether the API add-on renders its own MCP toggle.
	 *
	 * The add-on drops its toggle in the same release that teaches it to defer,
	 * and says so with FrmAPISettingsController::renders_mcp_toggle(). An older
	 * copy has the class but not the method, and always renders one.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function api_addon_shows_toggle() {
		if ( ! class_exists( 'FrmAPISettingsController' ) ) {
			return false;
		}

		if ( ! method_exists( 'FrmAPISettingsController', 'renders_mcp_toggle' ) ) {
			return true;
		}

		return (bool) FrmAPISettingsController::renders_mcp_toggle();
	}

	/**
	 * Render the MCP section of the global settings page.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function route() {
		$mcp_enabled    = FrmMcpController::is_enabled();
		$connections    = FrmMcpCompat::is_usable() ? FrmMcpConnection::get_connections() : null;
		$server_url     = get_rest_url( null, ltrim( FrmMcpConnection::MCP_ROUTE, '/' ) );
		$blocked_reason = $mcp_enabled ? FrmMcpCompat::unsupported_reason() : '';
		$is_inherited   = self::inherited_from_api_addon();

		require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/mcp.php';
	}

	/**
	 * Check whether the toggle is showing a value inherited from the API add-on.
	 *
	 * Worth naming on screen: until the section is saved once, the toggle
	 * reflects the add-on's setting rather than one of its own, and saving here
	 * is what takes it over.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function inherited_from_api_addon() {
		$settings = FrmAppHelper::get_settings();

		if ( isset( $settings->mcp ) && null !== $settings->mcp && '' !== $settings->mcp ) {
			return false;
		}

		return null !== FrmMcpController::api_addon_setting();
	}
}
