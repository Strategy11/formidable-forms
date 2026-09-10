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
	 * Transient holding the latest skill release read from the GitHub API.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SKILL_RELEASE_TRANSIENT = 'frm_mcp_skill_release';

	/**
	 * User meta holding the time and version of the last skill download.
	 *
	 * The name is Lite's own rather than the API add-on's frm_api_skill_download,
	 * so a site that has both never has one of them reading the other's record.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SKILL_DOWNLOAD_META = 'frm_mcp_skill_download';

	/**
	 * Value of the action query arg that sends a skill download through this site.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SKILL_DOWNLOAD_ACTION = 'frm_mcp_download_skill';

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_filter( 'frm_add_settings_section', 'FrmMcpSettingsController::add_settings_section' );
		add_action( 'frm_store_settings', 'FrmMcpController::reset' );
		add_action( 'admin_post_' . self::SKILL_DOWNLOAD_ACTION, 'FrmMcpSettingsController::download_skill' );
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
			// The same cloud the API section uses, in both its real and placeholder
			// form, since the two tabs are two faces of the same feature.
			// frm_bolt_icon was here before and is not in images/icons.svg, so the
			// tab rendered with no glyph at all.
			'icon'     => 'frmfont frm_cloud_icon',
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
		$blocked_reason = $mcp_enabled ? FrmMcpCompat::unsupported_reason() : '';
		$is_inherited   = self::inherited_from_api_addon();
		$skill_url      = self::get_skill_download_url();
		$skill_release  = self::get_skill_release();
		$skill_download = self::get_skill_download();
		$skill_is_stale = self::skill_update_available( $skill_release, $skill_download );

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

	/**
	 * Get the download link for the Formidable skill, the instructions an AI
	 * assistant loads so it knows how to drive the MCP server.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_skill_url() {
		return 'https://github.com/strategy11/formidable-mcp-skill/releases/latest/download/formidable-mcp-skill.zip';
	}

	/**
	 * Get the link the Download Skill button points at.
	 *
	 * The download goes through this site rather than straight to GitHub, so the
	 * version each user last took can be recorded and compared against the
	 * current release. The handler redirects on to the GitHub asset.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_skill_download_url() {
		$url = admin_url( 'admin-post.php?action=' . self::SKILL_DOWNLOAD_ACTION );
		return wp_nonce_url( $url, self::SKILL_DOWNLOAD_ACTION );
	}

	/**
	 * Record the download and send the browser on to the skill on GitHub.
	 *
	 * @since x.x
	 * @see action hook admin_post_frm_mcp_download_skill
	 *
	 * @return void
	 */
	public static function download_skill() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_admin_referer( self::SKILL_DOWNLOAD_ACTION );

		// Read past the cache. The button always sends the browser to the current
		// release, so a cached version could record something older than the file
		// the user is about to get, and then claim an update they already have.
		$release = self::get_skill_release( true );

		update_user_meta(
			get_current_user_id(),
			self::SKILL_DOWNLOAD_META,
			array(
				'time'    => time(),
				'version' => $release ? $release['version'] : '',
			)
		);

		// The skill is hosted on GitHub, so that host has to be allowed before
		// the safe redirect will send the browser off site.
		add_filter( 'allowed_redirect_hosts', 'FrmMcpSettingsController::allow_skill_redirect_host' );
		wp_safe_redirect( self::get_skill_url() );
		exit;
	}

	/**
	 * Allow the skill download host as a redirect target.
	 *
	 * @since x.x
	 * @see filter hook allowed_redirect_hosts
	 *
	 * @param array $hosts Hosts a safe redirect may send the browser to.
	 *
	 * @return array
	 */
	public static function allow_skill_redirect_host( $hosts ) {
		$host = wp_parse_url( self::get_skill_url(), PHP_URL_HOST );

		if ( $host ) {
			$hosts[] = $host;
		}

		return $hosts;
	}

	/**
	 * Get the time and version of the current user's last skill download.
	 *
	 * @since x.x
	 *
	 * @return array|false Time and version of the last download, or false when this user has never downloaded it.
	 */
	private static function get_skill_download() {
		$download = get_user_meta( get_current_user_id(), self::SKILL_DOWNLOAD_META, true );

		if ( ! is_array( $download ) || empty( $download['time'] ) ) {
			return false;
		}

		return $download;
	}

	/**
	 * Check whether a release has come out since this user last downloaded the skill.
	 *
	 * The two versions are compared for equality rather than order, so a tag
	 * that does not read as a version number still reports the change.
	 *
	 * @since x.x
	 *
	 * @param array|false $release  The current release, from get_skill_release().
	 * @param array|false $download The user's last download, from get_skill_download().
	 *
	 * @return bool
	 */
	private static function skill_update_available( $release, $download ) {
		if ( ! $release || ! $download || empty( $download['version'] ) ) {
			// Nothing to compare against until both versions are known.
			return false;
		}

		return $release['version'] !== $download['version'];
	}

	/**
	 * Format a skill release or download date as a short relative phrase.
	 *
	 * These dates read inside a sentence beside the download button, where a
	 * full date makes the line long enough to wrap. Today is named rather than
	 * counted, since '4 hours ago' is more precision than the line needs.
	 *
	 * @since x.x
	 *
	 * @param int|string $date A timestamp, or the date string the GitHub API returned.
	 *
	 * @return string A phrase like 'today' or '3 days ago', or an empty string when the date cannot be read.
	 */
	public static function relative_skill_date( $date ) {
		$timestamp = is_numeric( $date ) ? (int) $date : strtotime( $date );

		if ( ! $timestamp ) {
			return '';
		}

		$now = time();

		if ( $timestamp > $now ) {
			// A release published moments ago can still read as the future once
			// the site time zone is applied, and 'in 2 hours' would be wrong.
			$timestamp = $now;
		}

		if ( wp_date( 'Y-m-d', $timestamp ) === wp_date( 'Y-m-d', $now ) ) {
			return __( 'today', 'formidable' );
		}

		/* translators: %s: Human readable time difference, like "3 days". */
		$phrase = sprintf( __( '%s ago', 'formidable' ), human_time_diff( $timestamp, $now ) );

		return $phrase ? $phrase : '';
	}

	/**
	 * Get the latest published release of the skill from the GitHub API.
	 *
	 * The result is cached, since the settings page would otherwise call GitHub
	 * on every load. An hour keeps the version on screen close to the real one
	 * while staying far inside the 60 unauthenticated requests an hour per IP
	 * that GitHub allows, and a failure is cached for a shorter time so an
	 * outage does not send a request on every load either.
	 *
	 * @since x.x
	 *
	 * @param bool $refresh Whether to skip the cache and read the release from GitHub.
	 *
	 * @return array|false Version, publish date, and release page URL, or false when the release cannot be read.
	 */
	private static function get_skill_release( $refresh = false ) {
		$cached = get_transient( self::SKILL_RELEASE_TRANSIENT );
		// An empty array is the cached failure, and false is nothing cached at all.
		$cached = is_array( $cached ) ? $cached : null;

		if ( ! $refresh && null !== $cached ) {
			return array() === $cached ? false : $cached;
		}

		$release = self::fetch_skill_release();

		if ( ! $release ) {
			if ( $cached ) {
				// A refresh that failed keeps the release already cached rather
				// than dropping the version that is on screen.
				return $cached;
			}

			set_transient( self::SKILL_RELEASE_TRANSIENT, array(), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		set_transient( self::SKILL_RELEASE_TRANSIENT, $release, HOUR_IN_SECONDS );

		return $release;
	}

	/**
	 * Read the latest release from the GitHub API.
	 *
	 * The endpoint is public and skips drafts and prereleases, so a tagged
	 * release candidate does not replace the last stable one.
	 *
	 * @since x.x
	 *
	 * @return array|false Version, publish date, and release page URL, or false when the release cannot be read.
	 */
	private static function fetch_skill_release() {
		$response = wp_remote_get(
			'https://api.github.com/repos/Strategy11/formidable-mcp-skill/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);

		$body = array();

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
		}

		if ( ! is_array( $body ) || empty( $body['tag_name'] ) ) {
			return false;
		}

		return array(
			// published_at is when the release went out. created_at is older, since it dates the tagged commit.
			'published' => $body['published_at'] ?? '',
			'url'       => $body['html_url'] ?? '',
			'version'   => $body['tag_name'],
		);
	}
}
