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

}
