<?php

/**
 * @since 3.0
 */
class FrmFieldDefault extends FrmFieldType {

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
}
