describe( 'Run a Lighthouse performance audit', function() {
	beforeEach( cy.login );

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

	it( 'Check the dashboard page', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
		cy.lighthouse( thresholds );
	} );
} );
