describe( 'Run some HTML validation', function() {
	beforeEach( cy.login );

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
