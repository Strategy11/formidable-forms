<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.04
 */
class FrmEntryFactory {

	/**
	 * Create an instance of the FrmEntryFormatter class
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 *
	 * @return FrmEntryFormatter|FrmProEntryFormatter
	 */
	public static function entry_formatter_instance( $atts ) {
		$formatter_class = 'FrmEntryFormatter';

		if ( FrmAppHelper::pro_is_installed() ) {
			$formatter_class = 'FrmProEntryFormatter';
		}

		/**
		 * Allows changing entry formatter class name.
		 *
		 * @since 5.0.16
		 *
		 * @param string $formatter_class Entry formatter class name.
		 * @param array  $atts            See {@see FrmEntriesController::show_entry_shortcode()}.
		 */
		$formatter_class = apply_filters( 'frm_entry_formatter_class', $formatter_class, $atts );

		return new $formatter_class( $atts );
	}

	/**
	 * Create an intsance of the FrmEntryShortcodeFormatter class
	 *
	 * @since 2.04
	 *
	 * @param int|string $form_id
	 * @param array $atts
	 *
	 * @return FrmEntryShortcodeFormatter|FrmProEntryShortcodeFormatter
	 */
	public static function entry_shortcode_formatter_instance( $form_id, $atts ) {
		if ( FrmAppHelper::pro_is_installed() ) {
			$shortcode_formatter = new FrmProEntryShortcodeFormatter( $form_id, $atts );
		} else {
			$shortcode_formatter = new FrmEntryShortcodeFormatter( $form_id, $atts );
		}

		return $shortcode_formatter;
	}
}
