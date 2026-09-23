<?php
/**
 * Shared by tests/mu-plugins/frm-protect-last-form.php (the e2e mu-plugin) and
 * tests/phpunit/forms/test_FrmForm.php (the PHPUnit test) so the guard logic
 * only exists in one place.
 *
 * @return callable
 */
function frm_last_form_guard_callback() {
	return function ( $allow_destroy ) {
		if ( ! $allow_destroy ) {
			return $allow_destroy;
		}

		$total_forms = FrmDb::get_count(
			'frm_forms',
			array(
				array(
					'or'               => 1,
					'parent_form_id'   => null,
					'parent_form_id <' => 1,
				),
				'is_template' => 0,
			)
		);

		return $total_forms > 1;
	};
}
