<?php
// Forces the welcome-tour checklist off in the e2e test environment.
//
// On a fresh install the checklist renders expanded on its 'create-form'
// step and its header overlays the form builder, covering
// #frm-save-form-name-button so `cy.click()` fails actionability.
//
// The suite used to get away with this by accident: Cypress runs root-level
// specs first, so admin-a11y.cy.js was always spec 1 of 18 and clicked the
// checklist away, and because FrmWelcomeTourController stores its state in
// the site-wide `frm-welcome-tour` option, every later spec inherited a
// dismissed tour. Sharding removes that guarantee - whichever spec runs
// first on a given shard now meets a virgin site - so the state has to be
// set deterministically instead of inherited from a neighbour.
//
// Filtering rather than seeding the option means save_checklist() can't
// write over it mid-run. `active_step_key` is required alongside
// `dismissed`: get_usage_data() reads it whenever `dismissed` is truthy and
// would otherwise emit an undefined-index warning into debug.log.
add_filter(
	'pre_option_frm-welcome-tour',
	function () {
		return array(
			'completed_steps' => array(),
			'active_step_key' => 'create-form',
			'dismissed'       => true,
		);
	}
);
