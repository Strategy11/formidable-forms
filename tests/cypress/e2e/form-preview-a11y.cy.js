describe( 'Run some accessibility tests', function() {
	const configureAxeWithBaselineIgnoredRuleset = () => {
		cy.configureAxe( {
			rules: [
				{ id: 'color-contrast', enabled: false },
				{ id: 'label-title-only', enabled: false },
				{ id: 'label', enabled: false },
				{ id: 'landmark-one-main', enabled: false },
				{ id: 'page-has-heading-one', enabled: false },
				{ id: 'region', enabled: false }
			]
		} );
	};

	it( 'Check the form list has valid HTML', () => {
		cy.login();
		cy.ensureContactUsFormExists();
		cy.visit( '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=contact-form' );
		cy.injectAxe();
		configureAxeWithBaselineIgnoredRuleset();
		cy.checkA11y( null, null, violations => {
			// cy.task output doesn't reach the GitHub Actions log, so build the same
			// summary into the assertion message below, which does.
			const summary = violations
				.map( ( { id, impact, description, nodes } ) => `${ id } (${ impact }): ${ description } - ${ nodes.length } node(s)` )
				.join( '\n' );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
		cy.checkIbmAccessibility( 'form-preview' );
	} );
} );
