<?php
/**
 * Never let the e2e test suite permanently delete the last remaining form.
 *
 * Forms/deleteForms.cy.js can empty the forms list right before
 * admin-html-validation.cy.js runs in the same shard (formidable-forms#3416,
 * PR #3425) - a shard-order flake, not a real product constraint. Blocking
 * FrmForm::destroy() here, only in this test-only mu-plugin, keeps real users
 * free to delete their only form while the suite can never hit 0 forms.
 *
 * @package Formidable
 */

add_filter(
	'frm_before_destroy_form',
	function ( $allow_destroy ) {
		return $allow_destroy && FrmForm::get_forms_count() > 1;
	}
);
