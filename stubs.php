<?php

namespace {
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
	define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
	define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
	define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
	define( 'EMPTY_TRASH_DAYS', 30 );
	define( 'ABSPATH', realpath( __FILE__ . '/../../../../' ) );
	define( 'WP_PLUGIN_DIR', realpath( __FILE__ . '/../' ) );
	define( 'WPINC', 'wp-includes' );
	define( 'OBJECT', 'OBJECT' );
	define( 'OBJECT_K', 'OBJECT_K' );
	define( 'ARRAY_A', 'ARRAY_A' );
	define( 'EP_PAGES', 4096 );
	define( 'EP_PERMALINK', 1 );
	define( 'COOKIEHASH', '' );
	define( 'COOKIE_DOMAIN', false );
	define( 'WP_IMPORTING', false );
	define( 'ICL_PLUGIN_INACTIVE', false );

	class FrmProFormState {
		public static function get_from_request( $key, $default ) {}
	}

	class FrmProEntryShortcodeFormatter extends FrmEntryShortcodeFormatter {
	}
	class FrmProSettings extends FrmSettings {
	}
	class FrmProApplicationsHelper {
		public static function get_custom_applications_capability() {}
		/**
		 * @return string
		 */
		public static function get_required_templates_capability() {
		}
	}
	class FrmProFileImport {
		public static function import_attachment( $val, $field ) {
		}
	}
	class FrmProAppHelper {
		public static function get_settings() {
		}
		public static function convert_date( $date_str, $from_format, $to_format ) {
		}
		/**
		 * @return bool
		 */
		public static function views_is_installed() {
		}
		/**
		 * @return string
		 */
		public static function plugin_path() {
		}
		/**
		 * @return bool
		 */
		public static function use_chosen_js() {
		}
		/**
		 * @param array|string $selected
		 * @param string       $current
		 * @param bool         $echo
		 * @return string
		 */
		public static function selected( $selected, $current, $echo = true ) {
		}
		/**
		 * @return string
		 */
		public static function plugin_url() {
		}
		/**
		 * @return FrmProEddController
		 */
		public static function get_updater() {
		}
	}
	class FrmProEntryMetaHelper {
		public static function get_post_or_meta_value( $entry, $field, $atts = array() ) {
		}
		/**
		 * @param int|object|string $field_id
		 * @param array|string      $value
		 * @param false|int|string  $entry_id
		 * @return array|object|string|null
		 */
		public static function &value_exists( $field_id, $value, $entry_id = false ) {
		}
		public static function get_post_value( $post_id, $post_field, $custom_field, $atts ) {
		}
	}
	class FrmProFormActionsController {
		/**
		 * @param WP_Post  $action
		 * @param stdClass $entry
		 * @return bool
		 */
		public static function action_conditions_met( $action, $entry ) {
		}
	}
	class FrmViewsLayout {
		/**
		 * @param int    $view_id
		 * @param string $listing_layout
		 * @param string $detail_layout
		 */
		public static function maybe_create_layouts_for_view( $view_id, $listing_layout, $detail_layout ) {
		}
		/**
		 * @param int          $view_id
		 * @param false|string $type
		 * @return array|false|object
		 */
		public static function get_layouts_for_view( $view_id, $type = false ) {
		}
	}
	class FrmProDisplaysHelper {
		public static function get_shortcodes( $content, $form_id ) {
		}
	}
	class FrmProAddonsController {
		/**
		 * @param bool $force_type
		 * @return string
		 */
		public static function license_type( $force_type = false ) {
		}
		/**
		 * @return bool|int
		 */
		public static function is_license_expiring() {
		}
		/**
		 * @param string       $plugin
		 * @param array|string $upgrade_link_args
		 * @return void
		 */
		public static function conditional_action_button( $plugin, $upgrade_link_args ) {
		}
		/**
		 * @param array $atts
		 * @return void
		 */
		public static function show_conditional_action_button( $atts ) {
		}
		/**
		 * @return bool
		 */
		public static function admin_banner() {
		}
		/**
		 * @return string
		 */
		public static function get_readable_license_type() {
		}
	}
	class FrmProCurrencyHelper {
		public static function normalize_formatted_numbers( $field, $formatted_value ) {}
	}
	class FrmProDb {
		public static $plug_version;
	}
	class FrmProStylesController extends FrmStylesController {
		/**
		 * @param int $form_id
		 * @return WP_Post
		 */
		public static function get_active_style_for_form( $form_id ) {
		}
		/**
		 * @param stdClass|WP_Post $active_style
		 * @return array<WP_Post>
		 */
		public static function get_styles_for_styler( $active_style ) {
		}
		/**
		 * @return array<string>
		 */
		public static function get_notes_for_styler_preview() {
		}
		/**
		 * @return false|string
		 */
		public static function get_disabled_javascript_features() {
		}
	}
	class FrmProPost {
		/**
		 * @param array $field
		 * @param array $args
		 * @return string
		 */
		public static function get_category_dropdown( $field, $args ) {
		}
	}
	class FrmProEntriesController {
		public static function show_entry_shortcode( $atts ) {
		}
		/**
		 * @param array $atts
		 * @return string
		 */
		public static function entry_delete_link( $atts ) {
		}
		/**
		 * @param string   $method
		 * @param stdClass $form
		 * @param int      $entry_id
		 * @param array    $args
		 */
		public static function confirmation( $method, $form, $form_options, $entry_id, $args = array() ) {
		}
		/**
		 * @param object $form
		 * @return bool
		 */
		public static function is_form_displayed_after_edit( $form ) {
		}
		/**
		 * @param object $entry
		 * @param array  $args
		 * @return void
		 */
		public static function show_front_end_form_with_entry( $entry, $args ) {
		}
	}
	class FrmProFormsHelper {
		public static function &post_type( $form ) {
		}
		/**
		 * @return array
		 */
		public static function get_default_opts() {
		}
	}
	class FrmProEntry {
		/**
		 * @param array|false $values
		 * @param string      $location
		 * @return array
		 */
		public static function mod_other_vals( $values = false, $location = 'front' ) {
		}
	}
	class FrmProEntryFormatter extends FrmEntryFormatter {
	}
	class FrmProEntriesHelper {
		public static function get_search_str( $where_clause, $search_str, $form_id = 0, $fid = '' ) {
		}
		/**
		 * @param object           $field
		 * @param object           $entry
		 * @param array|int|string $field_value
		 * @return void
		 */
		public static function get_dynamic_list_values( $field, $entry, &$field_value ) {
		}

		/**
		 * @param object $entry
		 * @param object $field
		 * @param array  $atts
		 *
		 * @return string
		 */
		public static function prepare_child_display_value( $entry, $field, $atts ) {
		}
	}
	class FrmProFieldsHelper {
		/**
		 * @param array  $args
		 * @param string $value
		 * @return void
		 */
		public static function replace_non_standard_formidable_shortcodes( $args, &$value ) {
		}
		/**
		 * @param array|object $field
		 * @return bool
		 */
		public static function &is_field_visible_to_user( $field ) {
		}
	}
	class FrmViewsAppHelper {
		/**
		 * @return string
		 */
		public static function plugin_version() {
		}
	}
	class FrmProCreditCardsController {
		/**
		 * @param array  $field
		 * @param string $field_name
		 * @param array  $atts
		 * @return void
		 */
		public static function show_in_form( $field, $field_name, $atts ) {
		}
	}
	class FrmProAppController {
		/**
		 * @return bool
		 */
		public static function has_combo_js_file() {
		}
	}
	class Akismet {
		/**
		 * @param string $request
		 * @param string $path
		 * @param string $ip
		 * @return array
		 */
		public static function http_post( $request, $path, $ip = null ) {
		}
		public static function get_user_roles( $user_id ) {
		}
	}
	class PHPMailer {
		public function __construct( $exceptions = null ) {
		}
	}
	class FrmPaymentsController {
		public static $db_opt_name = 'frm_pay_db_version';
		/**
		 * @param array $cols
		 * @return array
		 */
		public static function payment_columns( $cols = array() ) {
		}
	}
	class FrmProDashboardHelper {
		/**
		 * @return bool
		 */
		public static function should_display_videos() {
		}
		/**
		 * @param array $entries_template
		 * @return void
		 */
		public static function get_main_widget( $entries_template ) {
		}
		/**
		 * @param array $entries_template
		 * @return void
		 */
		public static function get_bottom_widget( $entries_template ) {
		}
		/**
		 * @param array $template
		 * @return void
		 */
		public static function load_license_management( $template ) {
		}
	}
	class FrmProEddController extends FrmAddon {
	}
	class FrmProFieldSettings {
	}
	function load_formidable_pro() {
	}
	/**
	 * @return WPMailSMTP\Core
	 */
	function wp_mail_smtp() {
	}
	/**
	 * @return bool
	 */
	function akismet_test_mode() {
	}

	/** WP Optimize plugin */
	class WP_Optimize {
		/**
		 * @return WPO_Page_Cache
		 */
		public function get_page_cache() {
		}
	}
	class WPO_Page_Cache {
		/**
		 * @return bool
		 */
		public function purge() {
		}
	}
	class FrmLog {
		/**
		 * @param array<string> $values values.
		 *
		 * @return void
		 */
		public function add( $values ) {
		}
	}
	/**
	 * @return WP_Optimize
	 */
	function WP_Optimize() {
	}

	/**
	 * Function from W3 Total cache.
	 *
	 * @param array|null $extras Extras.
	 * @return void
	 */
	function w3tc_flush_all( $extras = null ) {
	}
	class FrmTransListsController {
		/**
		 * @param array $columns
		 * @return array
		 */
		public static function payment_columns( $columns = array() ) {
		}
	}
	class FrmProSettingsController {
		/**
		 * @param string $count
		 * @return string
		 */
		public static function inbox_badge( $count ) {}
		/**
		 * @return void
		 */
		public static function add_currency_settings() {}
	}
}

namespace Elementor {
	abstract class Widget_Base {
		public function start_controls_section( $section_id, array $args = array() ) {
		}
		public function add_control( $id, array $args, $options = array() ) {
		}
		public function end_controls_section() {
		}
		public function get_settings_for_display( $setting_key = null ) {
		}
	}

	class Plugin {
		/**
		 * @return Plugin
		 */
		public static function instance() {
		}
	}

	class Controls_Manager {
		const TAB_CONTENT = 'content';
		const SELECT2     = 'select2';
		const SWITCHER    = 'switcher';
	}
}

namespace WPMailSMTP {
	class Options {
	   /**
	    * @return Options
	    */
	   public static function init() {
	   }
		/**
		 * @param string $group
		 * @param string $key
		 * @param bool   $strip_slashes
		 * @return mixed|null
		 */
		public function get( $group, $key, $strip_slashes = true ) {
		}
	}
	class Core {
		/**
		 * @return Providers\Loader
		 */
		public function get_providers() {
		}
	}
}

namespace WPMailSMTP\Providers {
	interface MailerInterface {
		/**
		 * @return bool
		 */
		public function is_mailer_complete();
	}
	abstract class MailerAbstract implements MailerInterface {
	}
	class Loader {
		/**
		 * @param string               $provider  The provider name.
		 * @param MailCatcherInterface $phpmailer The MailCatcher object.
		 *
		 * @return MailerAbstract|null
		 */
		public function get_mailer( $provider, $phpmailer ) {
		}
	}
}
