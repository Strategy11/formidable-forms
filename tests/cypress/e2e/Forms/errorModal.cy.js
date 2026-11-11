describe( 'Error modal on the form edit page', () => {
	beforeEach( () => {
		cy.login();
		cy.viewport( 1280, 720 );
	} );

	it( 'should show the error dialog for a missing form', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable&frm_action=edit&id=999999', { failOnStatusCode: false } );

		cy.log( 'The dialog must render outside #wpbody-content so admin page rules do not hide it' );
		cy.get( '#frm_error_modal' )
			.should( 'be.visible' )
			.and( 'have.attr', 'open' );
		cy.get( '#frm_error_modal' ).parent().should( 'match', 'body' );
		cy.get( '#frm_error_modal_title' ).should( 'be.visible' );
		cy.get( '#frm_error_modal .frm_modal_footer a' ).first().should( 'be.visible' );
	} );

	it( 'should keep the dialog open when Escape is pressed', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable&frm_action=edit&id=999999', { failOnStatusCode: false } );

		cy.get( '#frm_error_modal' ).should( 'be.visible' );
		cy.get( 'body' ).type( '{esc}' );
		cy.get( '#frm_error_modal' ).should( 'be.visible' ).and( 'have.attr', 'open' );
	} );
} );
