<?php

/**
 * @since 2.03.11
 */
class FrmEntryFactory {

	/**
	 * Create an entry format instance
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 *
	 * @return FrmEntryFormat|FrmProEntryFormat
	 */
	public static function entry_format_instance( $atts ) {
		$entry_format = apply_filters( 'frm_entry_format_instance', null, $atts );

		if ( ! is_object( $entry_format ) ) {
			$entry_format = new FrmEntryFormat( $atts );
		}

		return $entry_format;
	}

	/**
	 * Create an HTML generator instance
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