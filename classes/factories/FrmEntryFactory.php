<?php

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
		if ( FrmAppHelper::pro_is_installed() ) {
			$entry_formatter = new FrmProEntryFormatter( $atts );
		} else {
			$entry_formatter = new FrmEntryFormatter( $atts );
		}

		return $entry_formatter;
	}

	/**
	 * Create an intsance of the FrmEntryShortcodeFormatter class
	 *
	 * @since 2.04
	 *
	 * @param int|string $form_id
	 * @param string $format
	 *
	 * @return FrmEntryShortcodeFormatter|FrmProEntryShortcodeFormatter
	 */
	public static function entry_shortcode_formatter_instance( $form_id, $format ) {
		if ( FrmAppHelper::pro_is_installed() ) {
			$shortcode_formatter = new FrmProEntryShortcodeFormatter( $form_id, $format );
		} else {
			$shortcode_formatter = new FrmEntryShortcodeFormatter( $form_id, $format );
		}

		return $shortcode_formatter;
	}
}