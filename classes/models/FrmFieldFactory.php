<?php

/**
 * @since 2.03.05
 */
class FrmFieldFactory {

	/**
	 * Create an instance of an FrmFieldValueSelector object
	 *
	 * @since 2.03.05
	 *
	 * @param int $field_id
	 * @param array $args
	 *
	 * @return FrmFieldValueSelector
	 */
	public static function create_field_value_selector( $field_id, $args ) {
		$selector = null;

		if ( $field_id > 0 ) {
			$selector = apply_filters( 'frm_create_field_value_selector', $selector, $field_id, $args );
		}

		if ( ! is_object( $selector ) ) {
			$selector = new FrmFieldValueSelector( $field_id, $args );
		}

		return $selector;
	}

}