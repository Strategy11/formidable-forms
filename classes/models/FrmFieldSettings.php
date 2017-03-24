<?php

/**
 * @since 2.03.05
 */
class FrmFieldSettings {

	/**
	 * @var array
	 * @since 2.03.05
	 */
	protected $field_options = array();

	/**
	 * FrmFieldSettings constructor.
	 *
	 * @param array $field_options
	 */
	public function  __construct( $field_options ) {
		$this->field_options = $field_options;
	}

	/*
	 * Check if a field has any field_options from database
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	final function has_field_options() {
		return ! empty( $this->field_options );
	}

}