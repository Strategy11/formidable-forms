<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmDbDeprecated
 *
 * @since 2.03.05
 * @deprecated 2.03.05
 * @codeCoverageIgnore
 */
class FrmDbDeprecated {

	var $fields;
	var $forms;
	var $entries;
	var $entry_metas;

	public function __construct() {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}

		global $wpdb;
		$this->fields      = $wpdb->prefix . 'frm_fields';
		$this->forms       = $wpdb->prefix . 'frm_forms';
		$this->entries     = $wpdb->prefix . 'frm_items';
		$this->entry_metas = $wpdb->prefix . 'frm_item_metas';
	}
}
