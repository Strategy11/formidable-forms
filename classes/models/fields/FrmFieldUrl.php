<?php

/**
 * @since 3.0
 */
class FrmFieldUrl extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'url';
	protected $display_type = 'text';

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
		);
	}

	protected function get_field_name() {
		return __( 'Website', 'formidable' );
	}
}
