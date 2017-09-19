<?php

/**
 * @since 3.0
 */
class FrmFieldHidden extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'hidden';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_html = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;
}
