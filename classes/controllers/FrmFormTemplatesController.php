<?php
/**
 * Form Templates Controller class.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

/**
 * Copyright (C) 2010 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmFormTemplatesController.
 * Handles the Form Templates page in the admin area.
 *
 * @since x.x
 */
class FrmFormTemplatesController {

	/**
	 * The slug of the Form Templates page.
	 *
	 * @var string PAGE_SLUG Unique identifier for the "Form Templates" page.
	 */
	const PAGE_SLUG = 'formidable-form-templates';

	/**
	 * The script handle.
	 *
	 * @var string SCRIPT_HANDLE Unique handle for the admin script.
	 */
	const SCRIPT_HANDLE = 'sherv-challenge-admin';

	/**
	 * The required user capability to view form templates.
	 *
	 * @var string REQUIRED_CAPABILITY Required capability to access the view form templates.
	 */
	const REQUIRED_CAPABILITY = 'frm_view_forms';

	/**
	 * The keys of the featured templates.
	 *
	 * Contains the unique keys for the templates that are considered "featured":
	 * "Contact Us", "User Registration", "Create WordPress Post", "Credit Card Payment", "Survey", and "Quiz".
	 *
	 * @var array FEATURED_TEMPLATES_KEYS Unique keys for the featured templates.
	 */
	const FEATURED_TEMPLATES_KEYS = array( 20872734, 20874748, 20882522, 20874739, 20908981, 28109851 );

	/**
	 * Instance of the Form Template API handler.
	 *
	 * @var FrmFormTemplateApi $form_template_api Form Template API handler.
	 */
	private static $form_template_api;

	/**
	 * Templates fetched from the API.
	 *
	 * @var array $templates Templates information from API.
	 */
	private static $templates = array();

	/**
	 * Featured templates.
	 *
	 * @var array $featured_templates Associative array with the featured templates' information.
	 */
	private static $featured_templates = array();

	/**
	 * Templates fetched from the published form by user.
	 *
	 * @var array $custom_templates Templates information from published form.
	 */
	private static $custom_templates = array();

	/**
	 * Categories for organizing templates.
	 *
	 * @var array $categories Categories for organizing templates.
	 */
	private static $categories = array();

	/**
	 * Status of API request, true if expired.
	 *
	 * @var bool $is_expired Whether the API request is expired or not.
	 */
	private static $is_expired = false;

	/**
	 * The type of license received from the API.
	 *
	 * @var string $license_type License type received from the API.
	 */
	private static $license_type = '';

	/**
	 * Add Form Templates menu item to sidebar and define index page.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function menu() {
		$label = __( 'Form Templates', 'formidable' );

		add_submenu_page(
			'formidable',
			'Formidable | ' . $label,
			$label,
			self::REQUIRED_CAPABILITY,
			self::PAGE_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Renders the Form Templates page in the WordPress admin area.
	 *
	 * Sets up template data, fetches relevant information, determines which blocks to render,
	 * and includes the view file for displaying the Form Templates page.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render() {
		// Initialize form templates data.
		self::set_form_templates_data();

		// Get current user.
		$user = wp_get_current_user();

		// Retrieve various template types and categories.
		$templates          = self::get_templates();
		$featured_templates = self::get_featured_templates();
		$custom_templates   = self::get_custom_templates();
		$categories         = self::get_categories();

		// Define view path.
		$view_path = FrmAppHelper::plugin_path() . '/classes/views/form-templates/';

		// License information and upgrade/renewal links.
		$expired      = FrmFormsController::expired();
		$upgrade_link = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'new-template',
				'content' => 'upgrade',
			)
		);
		$renew_link   = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'new-template',
				'content' => 'renew',
			)
		);

		// Determine which blocks to render based on license status.
		$blocks_to_render = array();
		if ( ! FrmAppHelper::pro_is_installed() && ! self::$form_template_api->has_free_access() ) {
			array_push( $blocks_to_render, 'email', 'code' );
		}
		if ( 'elite' !== FrmAddonsController::license_type() ) {
			$blocks_to_render[] = 'upgrade';
		}
		if ( $expired ) {
			$blocks_to_render[] = 'renew';
		}

		// Include SVG images for icons.
		FrmAppHelper::include_svg();

		// Render the view.
		include $view_path . 'index.php';
	}

	/**
	 * Initializes and organizes form template data by performing the following actions:
	 * - Instantiates the Form Template API class
	 * - Retrieves and sets templates, including featured ones
	 * - Organizes and categorizes templates
	 * - Formats custom templates
	 * - Updates global variables to reflect the current state
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function set_form_templates_data() {
		// Instantiate the Form Template API class.
		self::$form_template_api = new FrmFormTemplateApi();

		// Retrieve and set the templates.
		self::retrieve_and_set_templates();

		// Fetch and format custom templates.
		self::fetch_and_format_custom_templates();

		// Assign featured templates.
		self::assign_featured_templates();

		// Organize and set categories.
		self::organize_and_set_categories();

		// Update global variables to synchronize with the current class state.
		self::update_global_variables();
	}

	/**
	 * Retrieve and set templates.
	 *
	 * Gets the templates from the API and assigns them to the class property.
	 * Also handles any errors returned from the API.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function retrieve_and_set_templates() {
		self::$templates = self::$form_template_api->get_api_info();

		// Handle any errors returned from the API.
		self::handle_api_errors();
	}

	/**
	 * Assign featured templates.
	 *
	 * Iterates through FEATURED_TEMPLATES_KEYS and adds matching templates to
	 * the `featured_templates` class property.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function assign_featured_templates() {
		foreach ( self::FEATURED_TEMPLATES_KEYS as $key ) {
			if ( isset( self::$templates[ $key ] ) ) {
				self::$featured_templates[] = self::$templates[ $key ];
			}
		}
	}

	/**
	 * Organize and set categories.
	 *
	 * Iterates through templates to organize categories, performs filtering, sorting,
	 * and adds special categories.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function organize_and_set_categories() {
		// Iterate through templates to assign categories.
		foreach ( self::$templates as $key => $template ) {
			// Skip the template if the categories are not set.
			if ( ! isset( $template['categories'] ) ) {
				unset( self::$templates[ $key ] );

				continue;
			}

			// Increment the count for each category.
			foreach ( $template['categories'] as $category ) {
				if ( ! isset( self::$categories[ $category ] ) ) {
					self::$categories[ $category ] = 0;
				}

				self::$categories[ $category ]++;
			}
		}

		// Filter out certain and redundant categories.
		// 'PayPal', 'Stripe', and 'Twilio' are included elsewhere and should be ignored in this context.
		$redundant_cats = array_merge( array( 'PayPal', 'Stripe', 'Twilio' ), FrmFormsHelper::ignore_template_categories() );
		foreach ( $redundant_cats as $redundant_cat ) {
			unset( self::$categories[ $redundant_cat ] );
		}

		// Sort the categories by keys alphabetically.
		ksort( self::$categories );

		// Add special categories.
		self::$categories = array_merge(
			array(
				__( 'Favorites', 'formidable' )     => 5,
				__( 'Custom', 'formidable' )        => count( self::$custom_templates ),
				__( 'All Templates', 'formidable' ) => count( self::$templates ),
			),
			self::$categories
		);
	}

	/**
	 * Fetch and format custom templates.
	 *
	 * Retrieves the custom templates, formats them, and assigns them to the class property.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function fetch_and_format_custom_templates() {
		// Get all published forms.
		self::$custom_templates = FrmForm::get_published_forms();

		foreach ( self::$custom_templates as $template ) {
			$template = array(
				'id'          => $template->id,
				'name'        => $template->name,
				'key'         => $template->form_key,
				'description' => $template->description,
				'url'         => wp_nonce_url( admin_url( 'admin.php?page=formidable&frm_action=duplicate&id=' . absint( $template->id ) ) ),
				'released'    => $template->created_at,
				'installed'   => 1,
				'custom'      => true,
			);

			// Add the formatted custom template to the list.
			array_unshift( self::$custom_templates, $template );
		}
	}

	/**
	 * Handles any errors from the API request.
	 * Sets the `$is_expired` and `$license_type` properties.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function handle_api_errors() {
		if ( ! isset( self::$templates['error'] ) ) {
			return;
		}

		// Extract error message and modify the `utm_medium`.
		$error = self::$templates['error']['message'];
		$error = str_replace( 'utm_medium=addons', 'utm_medium=form-templates', $error );

		// Determine if request expired and set the license type.
		self::$is_expired   = 'expired' === self::$templates['error']['code'];
		self::$license_type = isset( self::$templates['error']['type'] ) ? self::$templates['error']['type'] : '';

		// Remove error from the templates.
		unset( self::$templates['error'] );
	}

	/**
	 * Updates global variables with the current state of the class.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function update_global_variables() {
		global $frm_templates;
		global $frm_expired;
		global $frm_license_type;

		$frm_templates    = self::get_templates();
		$frm_expired      = self::is_expired();
		$frm_license_type = self::get_license_type();
	}

	/**
	 * Enqueues "Form Templates" scripts and styles.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$plugin_url      = FrmAppHelper::plugin_url();
		$version         = FrmAppHelper::plugin_version();
		$js_dependencies = array(
			'wp-i18n',
			'wp-hooks', // This prevents a console error "wp.hooks is undefined" in WP versions older than 5.7.
			'formidable_dom',
		);

		// Enqueue styles that needed.
		wp_enqueue_style( 'formidable-admin' );
		wp_enqueue_style( 'formidable-grids' );

		// Register and enqueue "Form Templates" style.
		wp_register_style( self::SCRIPT_HANDLE, $plugin_url . '/css/admin/form-templates.css', array(), $version );
		wp_enqueue_style( self::SCRIPT_HANDLE );

		// Register and enqueue "Form Templates" script.
		// wp_register_script( self::SCRIPT_HANDLE, $plugin_url . '/js/admin/form-templates.js', $js_dependencies, $version, true );
		// wp_localize_script( self::SCRIPT_HANDLE, 'frmFormTemplatesVars', self::get_js_variables() );
		// wp_enqueue_script( self::SCRIPT_HANDLE );

		/**
		 * Fires after "Form Templates" enqueue assets.
		 *
		 * @since x.x
		 */
		do_action( 'frm_form_templates_enqueue_assets' );
	}

	/**
	 * Get "Form Templates" JS variables as an array.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_js_variables() {
		$js_variables = array(
			'proUpgradeUrl' => FrmAppHelper::admin_upgrade_link( 'form-templates' ),
		);

		/**
		 * Filters `js_variables` passed to the "Form Templates".
		 *
		 * @since x.x
		 *
		 * @param array $js_variables Array of js_variables passed to "Form Templates".
		 */
		return apply_filters( 'frm_form_templates_js_variables', $js_variables );
	}

	/**
	 * Dequeue scripts and styles on "Form Templates".
	 * Avoid extra scripts loading on "Form Templates" page that aren't needed.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function dequeue_scripts() {
		if ( self::PAGE_SLUG === FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) {
			wp_dequeue_script( 'frm-surveys-admin' );
			wp_dequeue_script( 'frm-quizzes-form-action' );
		}
	}

	/**
	 * Get the list of templates.
	 *
	 * @since x.x
	 *
	 * @return array A list of templates.
	 */
	public static function get_templates() {
		return self::$templates;
	}

	/**
	 * Get the list of featured templates.
	 *
	 * @since x.x
	 *
	 * @return array A list of featured templates.
	 */
	public static function get_featured_templates() {
		return self::$featured_templates;
	}

	/**
	 * Get the list of categories.
	 *
	 * @since x.x
	 *
	 * @return array A list of categories.
	 */
	public static function get_categories() {
		return self::$categories;
	}

	/**
	 * Get the list of custom templates.
	 *
	 * @since x.x
	 *
	 * @return array A list of custom templates.
	 */
	public static function get_custom_templates() {
		return self::$custom_templates;
	}

	/**
	 * Get the license type.
	 *
	 * @since x.x
	 *
	 * @return string The license type.
	 */
	public static function get_license_type() {
		return self::$license_type;
	}

	/**
	 * Checks if the API request was expired.
	 *
	 * @since x.x
	 *
	 * @return bool True if the API request was expired, false otherwise.
	 */
	public static function is_expired() {
		return self::$is_expired;
	}

}
