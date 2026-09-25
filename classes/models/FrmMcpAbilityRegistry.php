<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Knows which plugin provides every Formidable ability, including the ones this site cannot see.
 *
 * The abilities are registered by whichever plugin owns the feature: Formidable
 * owns forms, fields, entries, styles, and form actions, Pro owns entry writes,
 * style writes, stats, and applications, Views owns views and view layouts, and
 * the smaller add-ons own theirs. A site without one of those plugins therefore
 * has no record of the abilities it would have registered, and a client asking
 * for one of them can only be told that the name does not exist.
 *
 * This is that record. It lives in Formidable because Formidable is the one
 * plugin guaranteed to be present, and it deliberately lists abilities that
 * cannot be found in the registry on this site: naming the add-on is the whole
 * point, and an absent add-on cannot name itself.
 *
 * Nothing here reads a license key or reports a file path. The answers are
 * shown to whoever is driving the MCP server, which is not necessarily an
 * administrator.
 *
 * @since x.x
 */
class FrmMcpAbilityRegistry {

	/**
	 * The plugin that provides the ability is not on this site at all.
	 *
	 * @var string
	 */
	const STATUS_NOT_INSTALLED = 'not-installed';

	/**
	 * The plugin is installed but not activated.
	 *
	 * @var string
	 */
	const STATUS_INACTIVE = 'inactive';

	/**
	 * The plugin is active but does not claim the domain the ability belongs to.
	 *
	 * @var string
	 */
	const STATUS_OUTDATED = 'outdated';

	/**
	 * The plugin is active and current, but its license does not entitle it to run.
	 *
	 * @var string
	 */
	const STATUS_EXPIRED = 'expired';

	/**
	 * The plugin looks able to register the ability and still did not.
	 *
	 * @var string
	 */
	const STATUS_UNAVAILABLE = 'unavailable';

	/**
	 * No plugin in the family claims the name.
	 *
	 * @var string
	 */
	const STATUS_UNKNOWN = 'unknown';

	/**
	 * Memoized providers().
	 *
	 * @var array|null
	 */
	private static $providers;

	/**
	 * Map each provider to the plugin it lives in and the abilities it registers.
	 *
	 * The abilities are grouped by the ability domain they belong to, which is
	 * the same domain name the provider announces on the frm_ability_domains
	 * filter. That is what lets an unreachable ability be told apart from an
	 * add-on that is too old to have the feature at all.
	 *
	 * The defaults cover every add-on that registers a Formidable ability today.
	 * They are hard-coded on purpose: an add-on that is not installed cannot
	 * declare anything, so the plugin that is always present has to hold the
	 * list. Add-ons that ship after this can still add themselves on the filter,
	 * which is what keeps this from having to be edited forever.
	 *
	 * @since x.x
	 *
	 * @return array Provider keys mapped to a title, plugin folder, and ability names grouped by domain.
	 */
	public static function providers() {
		if ( null !== self::$providers ) {
			return self::$providers;
		}

		$providers = array(
			'pro'     => array(
				'title'     => 'Formidable Forms Pro',
				'folder'    => 'formidable-pro',
				'abilities' => array(
					'entry-writes' => array(
						'formidable-forms/create-entry',
						'formidable-forms/update-entry',
					),
					'styles-pro'   => array(
						'formidable-forms/create-style',
						'formidable-forms/delete-style',
						'formidable-forms/assign-style-to-form',
					),
					'stats'        => array(
						'formidable-forms/get-stats',
					),
					'applications' => array(
						'formidable-forms/list-applications',
						'formidable-forms/get-application',
						'formidable-forms/create-application',
						'formidable-forms/delete-application',
						'formidable-forms/list-application-items',
						'formidable-forms/add-item-to-application',
						'formidable-forms/remove-item-from-application',
					),
				),
			),
			'views'   => array(
				'title'     => 'Formidable Views',
				'folder'    => 'formidable-views',
				'abilities' => array(
					'views'        => array(
						'formidable-forms/list-views',
						'formidable-forms/get-view',
						'formidable-forms/create-view',
						'formidable-forms/update-view',
						'formidable-forms/delete-view',
					),
					'view-layouts' => array(
						'formidable-forms/list-view-layouts',
						'formidable-forms/get-view-layout',
						'formidable-forms/create-view-layout',
						'formidable-forms/update-view-layout',
						'formidable-forms/delete-view-layout',
					),
				),
			),
			'coupons' => array(
				'title'     => 'Formidable Coupons',
				'folder'    => 'formidable-coupons',
				'abilities' => array(
					'coupons' => array(
						'formidable-forms/list-coupons',
						'formidable-forms/get-coupon',
						'formidable-forms/create-coupon',
						'formidable-forms/update-coupon',
						'formidable-forms/delete-coupon',
					),
				),
			),
			'landing' => array(
				'title'     => 'Formidable Landing Pages',
				'folder'    => 'formidable-landing',
				'abilities' => array(
					'landing-pages' => array(
						'formidable-forms/list-landing-pages',
						'formidable-forms/get-landing-page',
						'formidable-forms/save-landing-page',
						'formidable-forms/update-landing-page',
						'formidable-forms/delete-landing-page',
					),
				),
			),
		);

		/**
		 * Filter the abilities Formidable knows about but may not be able to see registered.
		 *
		 * An add-on adds one entry keyed by a short provider name, holding its
		 * display title, its plugin folder, and the ability names it owns grouped
		 * by the domain names it announces on frm_ability_domains. Registering
		 * the same names in the Abilities API is still what makes them work:
		 * this list is only consulted when the name is missing.
		 *
		 * @since x.x
		 *
		 * @param array $providers Provider keys mapped to a title, plugin folder, and ability names grouped by domain.
		 */
		self::$providers = (array) apply_filters( 'frm_mcp_ability_providers', $providers );

		return self::$providers;
	}

	/**
	 * Forget the memoized providers.
	 *
	 * Needed by tests, which move the filter within one process.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function reset() {
		self::$providers = null;
	}

	/**
	 * Check whether an ability name is in Formidable's namespace.
	 *
	 * The Formidable MCP server reaches every ability registered on the site,
	 * so a name arriving from a client can belong to WooCommerce or any other
	 * plugin. This is the test for whether a name is Formidable's business at
	 * all, and it works on a name that is not registered, which is the case
	 * this class exists to answer for.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Full ability name, including its namespace.
	 *
	 * @return bool
	 */
	public static function owns( $ability_name ) {
		if ( ! is_string( $ability_name ) || '' === $ability_name ) {
			return false;
		}

		return str_starts_with( $ability_name, FrmAbilitiesController::CATEGORY . '/' );
	}

	/**
	 * Find the provider that owns one ability name.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Full ability name, including the formidable-forms prefix.
	 *
	 * @return array|false The provider entry, or false when no provider claims the name.
	 */
	public static function provider_for( $ability_name ) {
		$found = self::locate( $ability_name );

		if ( ! $found || ! is_array( $found['provider'] ) ) {
			return false;
		}

		return $found['provider'];
	}

	/**
	 * Find the provider and the ability domain that own one ability name.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Full ability name, including the formidable-forms prefix.
	 *
	 * @return array|false The provider entry and its domain name, or false when no provider claims the name.
	 */
	private static function locate( $ability_name ) {
		foreach ( self::providers() as $provider ) {
			if ( ! is_array( $provider ) || empty( $provider['abilities'] ) ) {
				continue;
			}

			foreach ( (array) $provider['abilities'] as $domain => $names ) {
				if ( in_array( $ability_name, (array) $names, true ) ) {
					return array(
						'provider' => $provider,
						'domain'   => $domain,
					);
				}
			}
		}

		return false;
	}

	/**
	 * Work out why one ability name cannot be reached on this site.
	 *
	 * Only meaningful for a name the Abilities API does not have. A registered
	 * ability is answered by the Abilities API itself and never gets here.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Full ability name, including the formidable-forms prefix.
	 *
	 * @return string One of the STATUS_ constants.
	 */
	public static function status( $ability_name ) {
		$found = self::locate( $ability_name );

		if ( ! $found ) {
			return self::STATUS_UNKNOWN;
		}

		$folder    = isset( $found['provider']['folder'] ) ? (string) $found['provider']['folder'] : '';
		$basenames = '' === $folder ? array() : self::installed_basenames( $folder );

		if ( ! $basenames ) {
			return self::STATUS_NOT_INSTALLED;
		}

		if ( ! self::any_active( $basenames ) ) {
			return self::STATUS_INACTIVE;
		}

		if ( ! self::domain_is_claimed( $found['domain'] ) ) {
			// The plugin is running but never announced the domain, so this copy
			// of it is older than the feature the ability belongs to.
			return self::STATUS_OUTDATED;
		}

		if ( self::license_is_expired() ) {
			return self::STATUS_EXPIRED;
		}

		return self::STATUS_UNAVAILABLE;
	}

	/**
	 * Check whether a plugin has announced that it owns one ability domain.
	 *
	 * Pro and Views add their domains on the frm_ability_domains filter whatever
	 * the license says, and only stop registering the abilities themselves, so
	 * the claim is the signal for "the feature is here" and the missing ability
	 * is the signal for "something stopped it".
	 *
	 * @since x.x
	 *
	 * @param string $domain Domain name, such as views or applications.
	 *
	 * @return bool
	 */
	private static function domain_is_claimed( $domain ) {
		$domains = FrmAbilitiesController::domains();
		return isset( $domains[ $domain ] );
	}

	/**
	 * Build the error a client gets for an ability it cannot reach.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Full ability name, including the formidable-forms prefix.
	 * @param string $status       One of the STATUS_ constants, from status().
	 *
	 * @return WP_Error
	 */
	public static function error( $ability_name, $status ) {
		$provider = self::provider_for( $ability_name );
		$title    = $provider && ! empty( $provider['title'] ) ? (string) $provider['title'] : '';

		return new WP_Error(
			self::error_code( $status ),
			self::message( $status, $title, $ability_name ),
			array( 'status' => self::STATUS_UNKNOWN === $status ? 404 : 403 )
		);
	}

	/**
	 * Map a status to the error code a client can branch on.
	 *
	 * @since x.x
	 *
	 * @param string $status One of the STATUS_ constants.
	 *
	 * @return string
	 */
	private static function error_code( $status ) {
		$codes = array(
			self::STATUS_NOT_INSTALLED => 'frm_ability_addon_missing',
			self::STATUS_INACTIVE      => 'frm_ability_addon_inactive',
			self::STATUS_OUTDATED      => 'frm_ability_addon_outdated',
			self::STATUS_EXPIRED       => 'frm_ability_license_expired',
			self::STATUS_UNAVAILABLE   => 'frm_ability_unavailable',
		);

		return $codes[ $status ] ?? 'frm_ability_unknown';
	}

	/**
	 * Build the message that tells the caller what to do about it.
	 *
	 * Each message names the plugin and the next step, and none of them mentions
	 * a license key or a path on disk.
	 *
	 * @since x.x
	 *
	 * @param string $status       One of the STATUS_ constants.
	 * @param string $title        Display title of the plugin that provides the ability.
	 * @param string $ability_name Full ability name, including the formidable-forms prefix.
	 *
	 * @return string
	 */
	private static function message( $status, $title, $ability_name ) {
		if ( self::STATUS_UNKNOWN === $status || '' === $title ) {
			return sprintf(
				/* translators: %s: an ability name, such as formidable-forms/create-view */
				__( 'There is no ability named %s on this site. Run the discover abilities tool to see which abilities this site has.', 'formidable' ),
				$ability_name
			);
		}

		return sprintf( self::message_template( $status ), $title, $ability_name );
	}

	/**
	 * Get the untranslated-argument template for one status.
	 *
	 * @since x.x
	 *
	 * @param string $status One of the STATUS_ constants.
	 *
	 * @return string A template taking the plugin title as %1$s and the ability name as %2$s.
	 */
	private static function message_template( $status ) {
		switch ( $status ) {
			case self::STATUS_NOT_INSTALLED:
				/* translators: 1: a plugin name, such as Formidable Views, 2: an ability name */
				return __( 'This action needs %1$s, which is not installed on this site. Install and activate %1$s from Formidable > Add-Ons, then run %2$s again.', 'formidable' );

			case self::STATUS_INACTIVE:
				/* translators: 1: a plugin name, such as Formidable Views, 2: an ability name */
				return __( 'This action needs %1$s, which is installed but not active. Activate %1$s on the Plugins screen, then run %2$s again.', 'formidable' );

			case self::STATUS_OUTDATED:
				/* translators: 1: a plugin name, such as Formidable Views, 2: an ability name */
				return __( 'This action needs a newer version of %1$s. Update %1$s to the latest version, then run %2$s again.', 'formidable' );

			case self::STATUS_EXPIRED:
				/* translators: 1: a plugin name, such as Formidable Views, 2: an ability name */
				return __( '%1$s is active but is not offering %2$s because its license is expired. Renew it in Formidable > Global Settings, then try again.', 'formidable' );

			default:
				/* translators: 1: a plugin name, such as Formidable Views, 2: an ability name */
				return __( '%1$s is active but did not register %2$s on this site. Check that %1$s is up to date and that its license is active, then try again.', 'formidable' );
		}//end switch
	}

	/**
	 * List the installed plugin files that live in one plugin folder.
	 *
	 * Matched on the folder rather than on one folder/file.php, because the main
	 * file has been renamed across versions of some of these add-ons and the
	 * answer here only needs to be "is a copy of it on disk".
	 *
	 * @since x.x
	 *
	 * @param string $folder Plugin folder name, such as formidable-views.
	 *
	 * @return array Plugin basenames in that folder.
	 */
	private static function installed_basenames( $folder ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$found = array();

		foreach ( array_keys( get_plugins() ) as $basename ) {
			if ( $folder === dirname( $basename ) ) {
				$found[] = $basename;
			}
		}

		return $found;
	}

	/**
	 * Check whether any of the given plugin files is active.
	 *
	 * @since x.x
	 *
	 * @param array $basenames Plugin basenames, as returned by installed_basenames().
	 *
	 * @return bool
	 */
	private static function any_active( $basenames ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $basenames as $basename ) {
			if ( is_plugin_active( $basename ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether the license stops the add-ons registering anything.
	 *
	 * There is one license for the whole family, and Pro and Views both stand
	 * down once it is expired and the grace period is over, so one answer covers
	 * both. Pro owns the answer: the grace period is a timestamp the store
	 * supplies rather than a fixed duration, and the methods that read it are
	 * private, so get_license_status() is the only correct source. A site still
	 * inside the grace period reports grace, not expired, and keeps working.
	 *
	 * Only the status word is read. No license key is fetched, compared, or
	 * reported anywhere in this class.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function license_is_expired() {
		if ( is_callable( 'FrmProAddonsController::get_license_status' ) && 'expired' === FrmProAddonsController::get_license_status() ) {
			return true;
		}

		// Pro can also be running unauthorized, which never reaches an expired
		// license status because there is no license to read.
		return ! FrmAppHelper::pro_is_installed();
	}
}
