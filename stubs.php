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

	class FrmProEntryShortcodeFormatter extends FrmEntryShortcodeFormatter {
	}
	class FrmProSettings extends FrmSettings {
	}
	class FrmProApplicationsHelper {
		public static function get_custom_applications_capability() {}
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
	}
	class FrmProEntryMetaHelper {
		public static function get_post_or_meta_value( $entry, $field, $atts = array() ) {
		}
		/**
		 * @param object|string|int $field_id
		 * @param array|string      $value
		 * @param string|int|false  $entry_id
		 * @return array|null|string|object
		 */
		public static function &value_exists( $field_id, $value, $entry_id = false ) {
		}
		public static function get_post_value( $post_id, $post_field, $custom_field, $atts ) {
		}
	}
	class FrmProFormActionsController {
	}
	class FrmViewsLayout {
	}
	class FrmProDisplaysHelper {
		public static function get_shortcodes( $content, $form_id ) {
		}
	}
	class FrmProAddonsController {
	}
	class FrmProDb {
		public static $plug_version;
	}
	class FrmProStylesController extends FrmStylesController {
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
	}
	class FrmProFormsHelper {
		public static function &post_type( $form ) {
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
		 * @param string|array|int $field_value
		 * @return void
		 */
		public static function get_dynamic_list_values( $field, $entry, &$field_value ) {
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
		 * @param object|array $field
		 * @return bool
		 */
		public static function &is_field_visible_to_user( $field ) {
		}
		public static function add_default_field_settings( $settings, $atts ) {
		}
	}
	class FrmViewsAppHelper {
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
	}
	class FrmProDashboardHelper {
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
