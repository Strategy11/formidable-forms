describe( 'Run some accessibility tests', function() {
	beforeEach( cy.login );

	const logViolations = violations => {
		cy.task(
			'log',
			`${ violations.length } accessibility violation${
				violations.length === 1 ? '' : 's'
			} ${ violations.length === 1 ? 'was' : 'were' } detected`
		);

		// pluck specific keys to keep the table readable
		const violationData = violations.map(
			( { id, impact, description, nodes } ) => ( {
				id,
				impact,
				description,
				nodes: nodes.length
			} )
		);

		cy.task( 'table', violationData );
	};

	const configureAxeWithIgnoredRuleset = rules => {
		cy.configureAxe( { rules } );
	};

	const baselineRules = [
		{ id: 'color-contrast', enabled: false },
		{ id: 'aria-allowed-role', enabled: false },
		{ id: 'link-name', enabled: false },
		{ id: 'link-in-text-block', enabled: false },
		{ id: 'region', enabled: false },
	];

	it( 'Check the dashboard page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
		cy.injectAxe();
		cy.get( 'body' ).then( $body => {
			if ( $body.find( '.frm-welcome-tour-modal a.dismiss' ).length ) {
				cy.get( '.frm-welcome-tour-modal a.dismiss' ).click( { force: true } );
				cy.log( 'Welcome tour dismissed' );
			}
		} );
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the form list is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'empty-table-header', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the entries page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-entries' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'empty-table-header', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the styles page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			{ id: 'aria-allowed-role', enabled: false },
			{ id: 'link-name', enabled: false },
			{ id: 'label', enabled: false },
			{ id: 'label-title-only', enabled: false },
			{ id: 'heading-order', enabled: false },
			{ id: 'empty-heading', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the applications page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-applications' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'image-alt', enabled: false },
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the form templates page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
		cy.injectAxe();
		cy.get( 'body' ).then( $body => {
			if ( $body.find( '.frm-checklist span.frm-text-grey-400' ).length ) {
				cy.get( '.frm-checklist span.frm-text-grey-400' ).click( { force: true } );
				cy.log( 'Checklist dismissed' );
			}
		} );
		configureAxeWithIgnoredRuleset( [
			{ id: 'color-contrast', enabled: false },
			{ id: 'aria-allowed-role', enabled: false },
			{ id: 'link-name', enabled: false },
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the import/export page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-import' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the global settings page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the Add-Ons page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-addons' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the SMTP page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-smtp' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'landmark-unique', enabled: false },
			{ id: 'landmark-complementary-is-top-level', enabled: false }
		] );
		cy.checkA11y( null, null, logViolations );
	} );

	it( 'Check the list of deleted forms is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable&form_type=trash' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules
		] );
		cy.checkA11y( null, null, logViolations );
	} );
} );
