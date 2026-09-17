describe( 'Run a Lighthouse performance audit', function() {
	// Permissive on purpose - this run establishes the baseline. The
	// `lighthouse` task in cypress.config.js logs the raw category scores to
	// the CI log; ratchet these thresholds up to that baseline once it's
	// measured.
	const thresholds = {
		performance: 0,
		accessibility: 0,
		'best-practices': 0,
		seo: 0
	};

	it( 'Check the front-end form preview page', () => {
		cy.login();
		cy.visit( '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=contact-form' );
		cy.lighthouse( thresholds );
	} );
} );
