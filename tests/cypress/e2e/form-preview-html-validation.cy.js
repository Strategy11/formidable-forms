describe( 'Run some HTML validation', function() {
	// cypress-html-validate's htmlvalidate() command asserts internally (chai `assert.equal` on
	// the error count, see node_modules/cypress-html-validate/dist/commands.js) - it's a real
	// assertion the static analyzer can't see across the plugin boundary, not a missing check.
	// eslint-disable-next-line sonarjs/assertions-in-tests
	it( 'Check the form list has valid HTML', () => {
		cy.login();
		cy.ensureContactUsFormExists();
		cy.visit( '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=contact-form' );
		cy.get( '#form_contact-form' ).htmlvalidate();
	} );
} );
