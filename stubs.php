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

	class FrmProEntryShortcodeFormatter extends FrmEntryShortcodeFormatter {
	}
	class FrmProSettings extends FrmSettings {
	}
	class FrmProApplicationsHelper {
		public static function get_required_templates_capability() {}
		public static function get_custom_applications_capability() {}
	}
	class FrmProFileImport {
		public static function import_attachment( $val, $field ) {
		}
	}

	class FrmLog {
		public function __construct() {
		}
		public function add( $values ) {
		}
	}

	class FrmProAppHelper {
		public static function get_settings() {
		}
	}
	class FrmProEntryMetaHelper {
		public static function get_post_or_meta_value( $entry, $field, $atts = array() ) {
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
	}
	class FrmProEntriesController {
		public static function show_entry_shortcode( $atts ) {
		}
	}
	class FrmProFormsHelper {
		public static function &post_type( $form ) {
		}
	}
	class FrmProEntry {
	}
	class FrmProEntryFormatter extends FrmEntryFormatter {
	}
	class FrmProEntriesHelper {
	}
	class FrmViewsAppHelper {
	}
	class Akismet {
	}
	class PHPMailer {
		public function __construct( $exceptions = null ) {
		}
	}
	/**
	 * @return void
	 */
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
	}
}

namespace WPMailSMTP {
	class Options {
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
