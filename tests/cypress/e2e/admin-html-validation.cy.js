describe( 'Run some HTML validation', function() {
	beforeEach( cy.login );

	// cypress-html-validate's htmlvalidate() command asserts internally (chai `assert.equal` on
	// the error count, see node_modules/cypress-html-validate/dist/commands.js) - it's a real
	// assertion the static analyzer can't see across the plugin boundary, not a missing check.
	// eslint-disable-next-line sonarjs/assertions-in-tests
	it( 'Check the form list has valid HTML', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.get( '#wpbody-content' ).htmlvalidate( {
			rules: {
				'prefer-button': 'off',
				'prefer-native-element': 'off',
				'wcag/h30': 'off',
				'valid-id': 'off',
				'aria-label-misuse': 'off',
				'no-missing-references': 'off',
				'heading-level': 'off'
			},
		} );
	} );

	// Same as above - htmlvalidate() asserts internally, invisible to static analysis.
	// eslint-disable-next-line sonarjs/assertions-in-tests
	it( 'Check the global settings page has valid HTML', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.get( '#wpbody-content' ).htmlvalidate( {
			rules: {
				'element-permitted-content': 'off',
				'valid-id': 'off',
				'prefer-button': 'off',
				'wcag/h30': 'off',
				'aria-label-misuse': 'off',
				'no-redundant-role': 'off'
			},
		} );
	} );
} );
