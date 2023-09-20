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
 * Copyright (C) 2023 Formidable Forms
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
	 * Option name to store favorite templates.
	 *
	 * @var string FAVORITE_TEMPLATES_OPTION Unique identifier for storing favorite templates.
	 */
	const FAVORITE_TEMPLATES_OPTION = 'frm_favorite_templates';

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
	 * List of user favorite templates.
	 *
	 * @var array $favorite_templates List of templates that the user has marked as favorites.
	 */
	private static $favorite_templates = array();

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
	 * Path to views.
	 *
	 * @var string $view_path Path to form templates views.
	 */
	private static $view_path = '';

	/**
	 * Upgrade URL.
	 *
	 * @var string $upgrade_link URL for upgrading accounts.
	 */
	private static $upgrade_link = '';

	/**
	 * Renew URL.
	 *
	 * @var string $renew_link URL for renewing accounts.
	 */
	private static $renew_link = '';

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
		// Include SVG images for icons.
		FrmAppHelper::include_svg();

		$view_path    = self::get_view_path();
		$upgrade_link = self::get_upgrade_link();
		$license_type = self::get_license_type();
		$pricing      = FrmAppHelper::admin_upgrade_link( 'form-templates' );

		// Get various template types and categories.
		$templates          = self::get_templates();
		$favorite_templates = self::get_favorite_templates();
		$featured_templates = self::get_featured_templates();
		$custom_templates   = self::get_custom_templates();
		$categories         = self::get_categories();

		// Render the view.
		include $view_path . 'index.php';
	}

	/**
	 * Renders a modal component in the WordPress admin area.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render_modal() {
		$view_path  = self::$view_path;
		$view_parts = array();

		// User and license-related variables.
		$user         = wp_get_current_user();
		$expired      = self::is_expired();
		$upgrade_link = self::get_upgrade_link();
		$renew_link   = self::get_renew_link();

		// Add 'leave-email' and 'code-from-email' modals views for users without Pro or free access.
		if ( ! FrmAppHelper::pro_is_installed() && ! self::$form_template_api->has_free_access() ) {
			$view_parts[] = 'modals/leave-email-modal.php';
			$view_parts[] = 'modals/code-from-email-modal.php';
		}

		// Add 'upgrade' modal view for non-elite users.
		if ( 'elite' !== FrmAddonsController::license_type() ) {
			$view_parts[] = 'modals/upgrade-modal.php';
		}

		// Add 'renew-account' modal view for expired users.
		if ( $expired ) {
			$view_parts[] = 'modals/renew-account-modal.php';
		}

		// Render the view.
		include $view_path . 'modal.php';
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
	public static function set_form_templates_data() {
		// Instantiate the Form Template API class.
		self::$form_template_api = new FrmFormTemplateApi();

		// Initialize favorite templates.
		self::init_favorite_templates();

		// Fetch and format custom templates.
		self::fetch_and_format_custom_templates();

		// Retrieve and set the templates.
		self::retrieve_and_set_templates();

		// Organize and set categories.
		self::organize_and_set_categories();

		// Assign featured templates.
		self::assign_featured_templates();

		// Update global variables to synchronize with the current class state.
		self::update_global_variables();

		// Initialize essential resources.
		self::init_template_resources();
	}

	/**
	 * Initialize favorite templates from WordPress options.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function init_favorite_templates() {
		$default_option_structure = array(
			'default' => array(),
			'custom'  => array(),
		);
		self::$favorite_templates = get_option( self::FAVORITE_TEMPLATES_OPTION, $default_option_structure );
	}

	/**
	 * Handle AJAX request to add or remove favorite templates.
	 *
	 * Manages the $favorite_templates by using WordPress options to
	 * add or remove templates from the favorites list.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function ajax_add_or_remove_favorite() {
		// Check permission and nonce
		FrmAppHelper::permission_check( self::REQUIRED_CAPABILITY );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Get posted data
		$template_id        = FrmAppHelper::get_post_param( 'template_id', '', 'absint' );
		$operation          = FrmAppHelper::get_post_param( 'operation', '', 'sanitize_text_field' );
		$is_custom_template = FrmAppHelper::get_post_param( 'is_custom_template', '', 'rest_sanitize_boolean' );

		// Determine the key based on whether it's a custom template or not
		$key = $is_custom_template ? 'custom' : 'default';

		// Perform add or remove operation
		if ( 'add' === $operation ) {
			self::$favorite_templates[ $key ][ $template_id ] = $template_id;
		} elseif ( 'remove' === $operation ) {
			if ( isset( self::$favorite_templates[ $key ][ $template_id ] ) ) {
				unset( self::$favorite_templates[ $key ][ $template_id ] );
			}
		}

		// Update the favorite templates option
		update_option( self::FAVORITE_TEMPLATES_OPTION, self::$favorite_templates );

		// Return the updated list of favorite templates
		wp_send_json_success( self::$favorite_templates );
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
		$published_forms = FrmForm::get_published_forms();

		// Get IDs from the published forms for matching.
		$published_form_ids = array_map(
			function( $form ) {
				return $form->id;
			},
			$published_forms
		);
		// Update custom favorite templates to include only IDs that are also in the published forms.
		self::$favorite_templates['custom'] = array_intersect( self::$favorite_templates['custom'], $published_form_ids );

		foreach ( $published_forms as $template ) {
			$template = array(
				'id'          => $template->id,
				'name'        => $template->name,
				'key'         => $template->form_key,
				'description' => $template->description,
				'link'        => FrmFormsHelper::get_direct_link( $template->form_key ),
				'url'         => wp_nonce_url( admin_url( 'admin.php?page=formidable&frm_action=duplicate&id=' . absint( $template->id ) ) ),
				'released'    => $template->created_at,
				'installed'   => 1,
				'is_custom'   => true,
			);

			// Add the 'is_favorite' key
			$template['is_favorite'] = in_array( $template['id'], self::$favorite_templates['custom'] );

			// Add the formatted custom template to the list.
			array_unshift( self::$custom_templates, $template );
		}
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
		foreach ( self::$templates as $key => &$template ) {
			// Skip the template if the categories are not set.
			if ( ! isset( $template['categories'] ) ) {
				unset( self::$templates[ $key ] );
				continue;
			}

			// Add a new key for category slugs.
			$template['category_slugs'] = array();

			// Increment the count for each category.
			foreach ( $template['categories'] as $category ) {
				$category_slug = sanitize_title( $category );

				// Add the slug to the new array.
				$template['category_slugs'][] = $category_slug;

				if ( ! isset( self::$categories[ $category_slug ] ) ) {
					self::$categories[ $category_slug ] = array(
						'name'  => $category,
						'count' => 0,
					);
				}

				self::$categories[ $category_slug ]['count']++;
			}

			// Add the 'is_favorite' key
			$template['is_favorite'] = in_array( $template['id'], self::$favorite_templates['default'] );
		}
		unset( $template ); // Unset the reference `$template` variable

		// Filter out certain and redundant categories.
		// 'PayPal', 'Stripe', and 'Twilio' are included elsewhere and should be ignored in this context.
		$redundant_cats = array_merge( array( 'PayPal', 'Stripe', 'Twilio' ), FrmFormTemplatesHelper::ignore_template_categories() );
		foreach ( $redundant_cats as $redundant_cat ) {
			$category_slug = sanitize_title( $redundant_cat );
			unset( self::$categories[ $category_slug ] );
		}

		// Sort the categories by keys alphabetically.
		ksort( self::$categories );

		// Add special categories.
		self::$categories = array_merge(
			array(
				'favorites' => array(
					'name'  => __( 'Favorites', 'formidable' ),
					'count' => self::get_favorite_templates_count(),
				),
				'custom' => array(
					'name'  => __( 'Custom', 'formidable' ),
					'count' => count( self::$custom_templates ),
				),
				'all-templates' => array(
					'name'  => __( 'All Templates', 'formidable' ),
					'count' => count( self::$templates ),
				),
			),
			self::$categories
		);
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
				self::$templates[ $key ]['is_featured'] = true;
				self::$featured_templates[] = self::$templates[ $key ];
			}
		}
	}

	/**
	 * Get the total count of favorite templates.
	 *
	 * @since x.x
	 *
	 * @return int
	 */
	public static function get_favorite_templates_count() {
		$custom_count  = count( self::$favorite_templates['custom'] );
		$default_count = count( self::$favorite_templates['default'] );

		return $custom_count + $default_count;
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
	 * Initializes essential resources.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function init_template_resources() {
		self::$view_path = FrmAppHelper::plugin_path() . '/classes/views/form-templates/';

		self::$upgrade_link = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'form-templates',
				'content' => 'upgrade',
			)
		);

		self::$renew_link = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'form-templates',
				'content' => 'renew',
			)
		);
	}

	/**
	 * Adds a Cancel button to the header of the Form Templates page.
	 *
	 * It's hidden by default and will show when the user clicks on 'Create Form' from
	 * another place in Formidable Forms.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function get_header_cancel_button() {
		echo '
		<a class="button frm-button-secondary frm_hidden" href="' . esc_url( admin_url( 'admin.php?page=formidable' ) ) . '" role="button">
			' . esc_html__( 'Cancel', 'formidable' ) . '
		</a>';
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
		global $frm_license_type;

		$frm_templates    = self::get_templates();
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
		if ( ! FrmAppHelper::is_form_templates_page() ) {
			return;
		}

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
		wp_register_script( self::SCRIPT_HANDLE, $plugin_url . '/js/form-templates.js', $js_dependencies, $version, true );
		wp_localize_script( self::SCRIPT_HANDLE, 'frmFormTemplatesVars', self::get_js_variables() );
		wp_enqueue_script( self::SCRIPT_HANDLE );

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
			'FEATURED_TEMPLATES_KEYS' => self::FEATURED_TEMPLATES_KEYS,
			'favoritesCount'          => array(
				'total'   => self::get_favorite_templates_count(),
				'default' => count( self::$favorite_templates['default'] ),
				'custom'  => count( self::$favorite_templates['custom'] ),
			),
			'customCount'             => count( self::$custom_templates ),
			'proUpgradeUrl'           => FrmAppHelper::admin_upgrade_link( 'form-templates' ),
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
	 *
	 * Avoid extra scripts loading on "Form Templates" page that aren't needed.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function dequeue_scripts() {
		if ( ! FrmAppHelper::is_form_templates_page() ) {
			return;
		}

		wp_dequeue_script( 'frm-surveys-admin' );
		wp_dequeue_script( 'frm-quizzes-form-action' );
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
	 * Get the user's favorite form templates.
	 *
	 * @since x.x
	 *
	 * @return array The IDs of the user's favorite form templates.
	 */
	public static function get_favorite_templates() {
		return self::$favorite_templates;
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

	/**
	 * Get the path to form templates views.
	 *
	 * @since x.x
	 *
	 * @return string Path to views.
	 */
	public static function get_view_path() {
		return self::$view_path;
	}

	/**
	 * Get the upgrade link.
	 *
	 * @since x.x
	 *
	 * @return string URL for upgrading accounts.
	 */
	public static function get_upgrade_link() {
		return self::$upgrade_link;
	}

	/**
	 * Get the renewal link.
	 *
	 * @since x.x
	 *
	 * @return string URL for renewing accounts.
	 */
	public static function get_renew_link() {
		return self::$renew_link;
	}
}
