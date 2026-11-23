describe( 'Search functionality', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should search valid and invalid form title', () => {
		cy.createNewForm();

		cy.log( 'Search the newly created form by using enter' );
		// A search submits a real page navigation, not an AJAX update - the reload can still be
		// settling past the default 4s command timeout when the result count is checked right after.
		cy.get( '#entry-search-input' ).type( 'Test Form {enter}', { timeout: 10000 } );
		cy.get( '.current > .count', { timeout: 10000 } ).should( 'contain', '1' );

		cy.log( 'Search the newly created form by using the submit button' );
		cy.get( '#entry-search-input' ).type( 'Test Form' ).clear();
		cy.get( '#entry-search-input' ).type( 'Test Form' );
		cy.get( '#search-submit' ).click();

		cy.get( '.published > .current', { timeout: 10000 } ).should( 'exist' );
		cy.get( '.current > .count', { timeout: 10000 } ).should( 'contain', '1' );
		cy.get( '.displaying-num' ).should( 'contain', '1' );
		cy.contains( '#the-list tr', 'Test Form' ).should( 'exist' );

		cy.log( 'Search for an invalid form title' );
		cy.get( '#entry-search-input' ).clear().type( 'Invalid Test Form {enter}' );
		cy.get( '.current > .count', { timeout: 10000 } ).should( 'contain', '0' );

		cy.get( '#entry-search-input' ).clear();
		cy.get( '#search-submit' ).click();

		cy.log( 'Teardown - Delete Test Form' );
		cy.deleteForm();
	} );
} );
