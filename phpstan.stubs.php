<?php

namespace {
	define( 'ABSPATH', realpath( __FILE__ . '/../../../../' ) );
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
