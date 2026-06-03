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
		cy.visit( '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=contact-form' );
		cy.injectAxe();
		configureAxeWithBaselineIgnoredRuleset();
		cy.checkA11y();
	} );
} );
