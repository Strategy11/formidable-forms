<?php

/**
 * @since 3.0
 */
class FrmFieldText extends FrmFieldType {

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	/**
	 * @param $type string
	 */
	protected function set_type( $type ) {
		if ( empty( $type ) ) {
			$type = 'text';
		}
		parent::set_type( $type );
	}

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'format'         => true,
		);
	}
}
