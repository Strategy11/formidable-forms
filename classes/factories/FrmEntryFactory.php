<?php

/**
 * @since 2.03.11
 */
class FrmEntryFactory {

	/**
	 * Create an instance of the FrmEntryFormatter class
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 *
	 * @return FrmEntryFormatter|FrmProEntryFormatter
	 */
	public static function entry_formatter_instance( $atts ) {
		$entry_formatter = apply_filters( 'frm_entry_formatter_instance', null, $atts );

		if ( ! is_object( $entry_formatter ) ) {
			$entry_formatter = new FrmEntryFormatter( $atts );
		}

		return $entry_formatter;
	}

	/**
	 * Create an intsance of the FrmEntryShortcodeFormatter class
	 *
	 * @since 2.03.11
	 *
	 * @param int|string $form_id
	 * @param string $format
	 *
	 * @return FrmEntryShortcodeFormatter|FrmProEntryShortcodeFormatter
	 */
	public static function entry_shortcode_formatter_instance( $form_id, $format ) {
		$html_generator = apply_filters( 'frm_entry_shortcode_formatter_instance', null, $form_id, $format );

		if ( ! is_object( $html_generator ) ) {
			$html_generator = new FrmEntryShortcodeFormatter( $form_id, $format );
		}

		return $html_generator;
	}

}