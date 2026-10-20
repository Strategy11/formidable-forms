<?php
/**
 * Lets an e2e spec force AJAX loading in the form builder for a short form.
 *
 * The builder only loads with AJAX once a form has more than
 * FrmFormsHelper::AJAX_BUILDER_FIELD_THRESHOLD fields, and specs that cover
 * deferred field settings would otherwise need to build a form that long.
 * Setting the frm_e2e_ajax_builder cookie turns AJAX loading on through the
 * frm_ajax_load_form_builder filter. It only applies while the cookie is set,
 * so every other spec still sees the default behavior.
 *
 * @package Formidable
 */

add_filter(
	'frm_ajax_load_form_builder',
	/**
	 * @param bool $ajax_load True when the form is long enough to load with AJAX.
	 *
	 * @return bool
	 */
	function ( $ajax_load ) {
		return $ajax_load || isset( $_COOKIE['frm_e2e_ajax_builder'] );
	}
);
