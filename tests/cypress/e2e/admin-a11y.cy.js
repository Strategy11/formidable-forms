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
				nodes: nodes.length,
				targets: nodes.map( node => node.target.join( ' ' ) ).join( '; ' )
			} )
		);

		cy.task( 'table', violationData );

		// cy.task output doesn't reach the GitHub Actions log, so build the same
		// summary into the assertion message below, which does.
		return violationData
			.map( ( { id, impact, description, nodes, targets } ) => `${ id } (${ impact }): ${ description } - ${ nodes } node(s): ${ targets }` )
			.join( '\n' );
	};

	const configureAxeWithIgnoredRuleset = rules => {
		cy.configureAxe( { rules } );
	};

	const baselineRules = [
		{ id: 'link-in-text-block', enabled: false },
		{ id: 'region', enabled: false },
		{ id: 'color-contrast', enabled: false },
	];

	// #wpadminbar is WordPress core markup Formidable doesn't own or render (e.g. its
	// <li role="group"> items fail aria-allowed-role); exclude it so these tests only
	// assert on Formidable's own admin pages.
	const excludeAdminBar = { exclude: [ [ '#wpadminbar' ] ] };

	it( 'Check the dashboard page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
		cy.injectAxe();
		cy.get( 'body' ).then( $body => {
			if ( $body.find( '.frm-welcome-tour-modal a.dismiss' ).length ) {
				cy.get( '.frm-welcome-tour-modal a.dismiss' ).should( 'be.visible' ).click();
				cy.log( 'Welcome tour dismissed' );
			}
		} );
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-dashboard' );
	} );

	it( 'Check the form list is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'empty-table-header', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-form-list' );
	} );

	it( 'Check the entries page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-entries' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'empty-table-header', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-entries' );
	} );

	it( 'Check the styles page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			{ id: 'label', enabled: false },
			{ id: 'label-title-only', enabled: false },
			{ id: 'heading-order', enabled: false },
			{ id: 'empty-heading', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-styles' );
	} );

	it( 'Check the applications page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-applications' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false },
			{ id: 'image-alt', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-applications' );
	} );

	it( 'Check the form templates page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
		cy.injectAxe();
		cy.get( 'body' ).then( $body => {
			if ( $body.find( '.frm-checklist span.frm-text-grey-400' ).length ) {
				cy.get( '.frm-checklist span.frm-text-grey-400' ).should( 'be.visible' ).click();
				cy.log( 'Checklist dismissed' );
			}
		} );
		configureAxeWithIgnoredRuleset( [
			{ id: 'heading-order', enabled: false },
			{ id: 'color-contrast', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-form-templates' );
	} );

	it( 'Check the import/export page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-import' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-import' );
	} );

	it( 'Check the global settings page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-settings' );
	} );

	it( 'Check the Add-Ons page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-addons' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'heading-order', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-addons' );
	} );

	it( 'Check the SMTP page is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-smtp' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules,
			{ id: 'landmark-unique', enabled: false },
			{ id: 'landmark-complementary-is-top-level', enabled: false }
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-smtp' );
	} );

	it( 'Check the list of deleted forms is accessible', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable&form_type=trash' );
		cy.injectAxe();
		configureAxeWithIgnoredRuleset( [
			...baselineRules
		] );
		cy.checkA11y( excludeAdminBar, null, violations => {
			const summary = logViolations( violations );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'formidable-trash' );
	} );
} );
