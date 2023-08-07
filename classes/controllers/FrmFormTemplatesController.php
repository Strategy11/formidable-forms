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
	 * Categories for organizing templates.
	 *
	 * @var array $categories Categories for organizing templates.
	 */
	private static $categories = array();

	/**
	 * Templates organized by categories.
	 *
	 * @var array $categorized_templates Templates organized by category.
	 */
	private static $categorized_templates = array();

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
	 * Renders the Form Templates page.
	 *
	 * This method prepares the required data and includes the view for displaying
	 * the Form Templates page in the WordPress admin area.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render() {
		// Retrieve the form templates data.
		self::get_form_templates_data();

		// Check license expiration status.
		$expiring = FrmAddonsController::is_license_expiring();
		$expired  = FrmFormsController::expired();

		// Define paths and additional variables.
		$categories            = self::get_categories();
		$categorized_templates = self::get_categorized_templates();
		$all_templates         = self::get_templates();
		$blocks_to_render      = array();
		$upgrade_link          = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'new-template',
				'content' => 'upgrade',
			)
		);
		$renew_link            = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'new-template',
				'content' => 'renew',
			)
		);
		$view_path             = FrmAppHelper::plugin_path() . '/classes/views/form-templates/';

		// The following variable gets the current WP user to determine
		// a default value for a field in `_leave-email.php`.
		$user = wp_get_current_user();

		// Check if the pro version is not installed and the user
		// does not have free access, then render 'email' and 'code' blocks.
		if ( ! FrmAppHelper::pro_is_installed() && ! self::$form_template_api->has_free_access() ) {
			array_push( $blocks_to_render, 'email', 'code' );
		}

		// If the license type is not 'elite', add 'upgrade' block to render.
		if ( 'elite' !== FrmAddonsController::license_type() ) {
			$blocks_to_render[] = 'upgrade';
		}

		// Check the license expiration status and add appropriate blocks to render.
		if ( $expired ) {
			$blocks_to_render[] = 'renew';
			$modal_class        = 'frm-expired';
		} elseif ( $expiring ) {
			$modal_class = 'frm-expiring';
		}

		// Include svg images.
		FrmAppHelper::include_svg();

		// Include the view file for rendering the page.
		include $view_path . 'index.php';
	}

	/**
	 * Fetches form template data from API.
	 * Organizes the data and handles any API errors.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function get_form_templates_data() {
		// Initialize form template API handler.
		self::$form_template_api = new FrmFormTemplateApi();

		// Get templates from the API.
		self::$templates = self::$form_template_api->get_api_info();

		// Handle any errors returned from the API.
		if ( isset( self::$templates['error'] ) ) {
			self::handle_api_errors();
		}

		// Organize the templates by category.
		self::group_templates_by_category();

		// Call synchronize global variables.
		self::synchronize_global_variables();
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
		// Extract error message and modify the `utm_medium`.
		$error = self::$templates['error']['message'];
		$error = str_replace( 'utm_medium=addons', 'utm_medium=form-templates', $error );

		// Determine if request expired and set the license type.
		self::$is_expired = 'expired' === self::$templates['error']['code'];
		self::$license_type = isset( self::$templates['error']['type'] ) ? self::$templates['error']['type'] : '';

		// Remove error from the templates.
		unset( self::$templates['error'] );
	}

	/**
	 * Organizes the templates by category.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function group_templates_by_category() {
		// Extract custom templates.
		$custom_templates = array();
		self::add_user_templates( $custom_templates );

		// Organize templates by their categories.
		foreach ( self::$templates as $template ) {
			// Skip the template if the categories are not set.
			if ( ! isset( $template['categories'] ) ) {
				continue;
			}

			// Iterate through each category and organize the templates accordingly.
			foreach ( $template['categories'] as $category ) {
				if ( ! isset( self::$categorized_templates[ $category ] ) ) {
					self::$categorized_templates[ $category ] = array();
				}

				self::$categorized_templates[ $category ][] = $template;
			}

			// Unset the current template to release memory.
			unset( $template );
		}

		// Get all category keys.
		self::$categories = array_keys( self::$categorized_templates );
		// Remove certain categories from the final list.
		self::$categories = array_diff( self::$categories, FrmFormsHelper::ignore_template_categories() );
		// Remove redundant categories.
		$redundant_cats = array( 'PayPal', 'Stripe', 'Twilio' );
		self::$categories = array_diff( self::$categories, $redundant_cats );
		// Sort the categories.
		sort( self::$categories );

		// Add 'All Templates' Category.
		$all_templates_cat_text = __( 'All Templates', 'formidable' );
		self::$categories       = array_merge( array( $all_templates_cat_text ), self::$categories );

		// Add 'Custom' Category.
		$custom_cat_text  = __( 'Custom', 'formidable' );
		self::$categories = array_merge( array( $custom_cat_text ), self::$categories );
		self::$categorized_templates[ $custom_cat_text ] = $custom_templates;

		// Add 'Favorites' Category.
		$favorites_cat_text = __( 'Favorites', 'formidable' );
		self::$categories   = array_merge( array( $favorites_cat_text ), self::$categories );
		self::$categorized_templates[ $favorites_cat_text ] = $custom_templates;
	}

	/**
	 * Adds user-defined templates.
	 *
	 * @since x.x
	 *
	 * @param array &$templates The templates array to update.
	 * @return void
	 */
	private static function add_user_templates( &$templates ) {
		// Retrieve user-defined templates.
		$user_templates = FrmForm::getAll(
			array(
				'is_template'      => 1,
				'default_template' => 0,
			),
			'name'
		);

		// Add user templates to the array.
		foreach ( $user_templates as $template ) {
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

			// Add each template to the templates array.
			array_unshift( $templates, $template );
			unset( $template );
		}
	}

	/**
	 * Synchronizes global variables.
	 * This method synchronizes global variables with the current state of the class.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function synchronize_global_variables() {
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
	 * Accessor for `$templates`.
	 *
	 * @since x.x
	 *
	 * @return array The templates.
	 */
	public static function get_templates() {
		return self::$templates;
	}

	/**
	 * Accessor for `$categories`.
	 *
	 * @since x.x
	 *
	 * @return array The categories.
	 */
	public static function get_categories() {
		return self::$categories;
	}

	/**
	 * Accessor for `$categorized_templates`.
	 *
	 * @since x.x
	 *
	 * @return array The templates, organized by category.
	 */
	public static function get_categorized_templates() {
		return self::$categorized_templates;
	}

	/**
	 * Accessor for `$license_type`.
	 *
	 * @since x.x
	 *
	 * @return string The license type.
	 */
	public static function get_license_type() {
		return self::$license_type;
	}

	/**
	 * Accessor for `$is_expired`.
	 *
	 * @since x.x
	 *
	 * @return bool True if the API request was expired, false otherwise.
	 */
	public static function is_expired() {
		return self::$is_expired;
	}
}
