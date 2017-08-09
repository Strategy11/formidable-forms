<?php

/**
 * @since 3.0
 */
class FrmFieldMultiple extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = true;

	protected function input_html() {
		return $this->multiple_input_html();
	}

	protected function field_settings_for_type() {
		return array(
			'default_blank' => false,
		);
	}
}
