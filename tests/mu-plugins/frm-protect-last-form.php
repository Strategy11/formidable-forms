<?php
/**
 * Never let the e2e test suite permanently delete the last remaining form.
 *
 * Forms/deleteForms.cy.js can empty the forms list right before
 * admin-html-validation.cy.js runs in the same shard - a shard-order flake,
 * not a real product constraint. Blocking FrmForm::destroy() here, only in
 * this test-only mu-plugin, keeps real users free to delete their only form
 * while the suite can never hit 0 forms.
 *
 * Counts every non-template top-level form regardless of status, not just
 * non-trashed ones (unlike FrmForm::get_forms_count()) - the UI only ever
 * permanently deletes an already-trashed form, so a non-trashed-only count
 * would see every trashed form as "the only one left" and block Empty Trash
 * entirely whenever exactly one other (non-trashed) form exists, breaking
 * deleteForms.cy.js's own passing "(0)" trash-count assertion.
 *
 * @package Formidable
 */

require_once __DIR__ . '/../frm-last-form-guard-callback.php';

add_filter( 'frm_before_destroy_form', frm_last_form_guard_callback() );
