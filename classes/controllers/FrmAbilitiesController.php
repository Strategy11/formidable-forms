<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable abilities for the WordPress Abilities API.
 *
 * Abilities let AI assistants and automation tools discover and run Formidable
 * operations, over the MCP server this plugin registers or over wp-abilities/v1.
 * Each domain (forms, fields, entries, styles, form actions) lives in its own
 * FrmAbilities*Controller. This class registers the shared category, delegates
 * to the domain controllers, and owns the gate every plugin in the family
 * checks before registering anything of its own.
 *
 * Pro and Views register the domains that belong to their features, and the API
 * add-on registers whatever is left over on a site where those plugins are too
 * old to have them. All three ask owns() first, which is what keeps two plugins
 * from claiming one ability name.
 *
 * @since x.x
 */
class FrmAbilitiesController {

	/**
	 * Ability category every Formidable ability is registered under.
	 *
	 * @var string
	 */
	const CATEGORY = 'formidable-forms';

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			// The Abilities API is part of WordPress 7.0. Without it there is
			// nothing to register against, on any version of any of these plugins.
			return;
		}

		add_action( 'wp_abilities_api_categories_init', 'FrmAbilitiesController::register_categories' );
		add_action( 'wp_abilities_api_init', 'FrmAbilitiesController::register_abilities' );
	}

	/**
	 * Check whether the Formidable family may register abilities at all.
	 *
	 * Pro, Views, and the API add-on all consult this before registering their
	 * own domains, so the MCP setting is one switch for the whole AI surface
	 * rather than one per plugin.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_active() {
		if ( FrmMcpController::api_addon_owns_mcp() ) {
			// An API add-on that predates the move registers the whole surface
			// itself, including the domains that now belong to Pro and Views.
			return false;
		}

		return FrmMcpController::is_enabled();
	}

	/**
	 * Map each ability domain to the class that owns it.
	 *
	 * Formidable owns the domains its own features cover. Pro and Views add
	 * theirs on the frm_ability_domains filter, which is also how the API add-on
	 * finds out whether a domain has an owner on this site or whether it should
	 * keep serving that domain itself.
	 *
	 * @since x.x
	 *
	 * @return array Domain names mapped to the controller class that registers them.
	 */
	public static function domains() {
		$domains = array(
			'forms'        => 'FrmAbilitiesFormsController',
			'fields'       => 'FrmAbilitiesFieldsController',
			'entries'      => 'FrmAbilitiesEntriesController',
			'styles'       => 'FrmAbilitiesStylesController',
			'form-actions' => 'FrmAbilitiesFormActionsController',
		);

		/**
		 * Filter the ability domains and the classes that own them.
		 *
		 * Pro adds entry-writes, styles-pro, stats, and applications. Views adds
		 * views and view-layouts. A plugin that adds a domain here is stating
		 * that it registers every ability in it, and the API add-on stops
		 * registering that domain in response.
		 *
		 * @since x.x
		 *
		 * @param array<string, string> $domains Domain names mapped to the controller class that registers them.
		 */
		return (array) apply_filters( 'frm_ability_domains', $domains );
	}

	/**
	 * Check whether one ability domain has an owner on this site.
	 *
	 * @since x.x
	 *
	 * @param string $domain Domain name, such as forms or view-layouts.
	 *
	 * @return bool
	 */
	public static function owns( $domain ) {
		if ( ! self::is_active() ) {
			return false;
		}

		$domains = self::domains();

		return isset( $domains[ $domain ] ) && class_exists( $domains[ $domain ] );
	}

	/**
	 * Register the shared ability category.
	 *
	 * @since x.x
	 * @see action hook wp_abilities_api_categories_init
	 *
	 * @return void
	 */
	public static function register_categories() {
		if ( ! self::is_active() ) {
			return;
		}

		// Pro, Views, and the API add-on all register into this category, so
		// whichever of them runs first would otherwise register it twice. The
		// registry is asked directly because wp_get_ability_category() is a
		// getter, not a check: it raises _doing_it_wrong for a category that is
		// not there, which is the normal answer here.
		if ( self::category_is_registered() ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Formidable Forms', 'formidable' ),
				'description' => __( 'Abilities for managing Formidable forms and entries.', 'formidable' ),
			)
		);
	}

	/**
	 * Check whether the shared category has already been registered.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function category_is_registered() {
		if ( ! class_exists( 'WP_Ability_Categories_Registry' ) ) {
			return false;
		}

		$registry = WP_Ability_Categories_Registry::get_instance();

		return $registry && $registry->is_registered( self::CATEGORY );
	}

	/**
	 * Register every ability Formidable itself owns.
	 *
	 * @since x.x
	 * @see action hook wp_abilities_api_init
	 *
	 * @return void
	 */
	public static function register_abilities() {
		if ( ! self::is_active() ) {
			return;
		}

		FrmAbilitiesFormsController::register_abilities();
		FrmAbilitiesFieldsController::register_abilities();
		FrmAbilitiesEntriesController::register_abilities();
		FrmAbilitiesStylesController::register_abilities();
		FrmAbilitiesFormActionsController::register_abilities();
	}
}
