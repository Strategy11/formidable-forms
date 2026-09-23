describe( 'Deleting forms', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	// Select only the "Test Form" rows on whatever list table is currently showing, rather than
	// the header "select all" checkbox - the forms list isn't necessarily empty going into this
	// test (e.g. the "Contact Us" form Formidable creates by default on a fresh install), and a
	// blanket "select all" would sweep that up into this test's own trash/delete/restore cycle.
	const checkTestFormRows = () => {
		cy.get( 'tr' ).filter( ( index, element ) => {
			return Cypress.$( element ).text().includes( 'Test Form' );
		} ).each( $row => {
			cy.wrap( $row ).find( '.check-column input[type="checkbox"]' ).check();
		} );
	};

	it( 'should create multiple forms and bulk delete them', () => {
		cy.emptyTrash();

		cy.log( 'Create a form this test never selects for delete, so the list can never hit 0 forms - a shard-order flake between this spec and the HTML-validation spec, not a real product constraint' );
		cy.createNewForm( 'Keep Alive Form' );

		cy.log( 'Record the published form count so later assertions allow for forms this test did not create, like the default Contact Us form' );
		let baselinePublishedCount = '(0)';
		cy.get( '.published > a .count' ).invoke( 'text' ).then( text => {
			baselinePublishedCount = text;
		} );

		cy.log( 'Create 5 new forms' );
		for ( let i = 0; i < 5; i++ ) {
			cy.createNewForm();
		}

		cy.log( 'Bulk delete all 5 new forms' );
		checkTestFormRows();

		cy.log( 'Click on Bulk Actions and select the Move to Trash option' );
		cy.get( '#bulk-action-selector-top' ).select( 'Move to Trash' );
		cy.get( '#doaction' ).should( 'contain', 'Apply' ).click();
		cy.get( '.frm_updated_message' ).should( 'contain', 'forms moved to the Trash.' );

		cy.log( 'Restore deleted forms and the redelete them' );
		cy.get( '.subsubsub > .trash > a' ).should( 'contain', 'Trash' ).click();
		cy.get( '#cb-select-all-1' ).click();
		cy.get( '#bulk-action-selector-top' ).should( 'contain', 'Bulk Actions' ).select( 'bulk_untrash' );
		cy.get( '#doaction' ).should( 'contain', 'Apply' ).click();
		cy.get( '.trash > a' ).should( 'contain.text', 'Trash' )
			.find( '.count' ).should( 'contain.text', '(0)' );

		cy.get( '.colspanchange > p' ).should( 'contain', 'No forms found in the trash.' );
		cy.get( '.colspanchange > p > a' ).should( 'contain', 'See all forms' ).click();
		cy.log( 'Bulk delete permanently deleted forms' );
		checkTestFormRows();
		cy.get( '#bulk-action-selector-top' ).select( 'bulk_trash' );
		cy.get( '#doaction' ).should( 'contain', 'Apply' ).click();

		cy.log( 'Permanently delete 2 forms using the Delete Permanetly option' );
		cy.get( '.subsubsub > .trash > a' ).should( 'contain', 'Trash' ).click();
		cy.get( '.trash > a' ).should( 'contain.text', 'Trash' )
			.find( '.count' ).should( 'contain.text', '(5)' );
		cy.get( '[id^="cb-item-action-"]' ).eq( 0 ).click();
		cy.get( '[id^="cb-item-action-"]' ).eq( 1 ).click();
		cy.get( '#bulk-action-selector-top' ).should( 'contain', 'Bulk Actions' ).select( 'bulk_delete' );
		cy.get( '#doaction' ).should( 'contain', 'Apply' ).click();
		cy.get( '.frm-confirm-msg' ).should( 'contain', 'ALL selected forms and their entries will be permanently deleted. Want to proceed?' );
		// cy.contains() (not cy.get()) is required here - two confirm banners with their own
		// `a.button-secondary` Cancel link both exist in the DOM at once, so a plain cy.get()
		// matches 2 elements and cy.click() rejects a multi-element subject.
		cy.contains( 'a.button-secondary', 'Cancel' ).should( 'be.visible' ).click();
		cy.get( '#doaction' ).should( 'contain', 'Apply' ).click();
		cy.get( '#frm-confirmed-click' ).should( 'contain', 'Confirm' ).click();
		cy.get( '.trash > a' ).should( 'contain.text', 'Trash' )
			.find( '.count' ).should( 'contain.text', '(3)' );
		cy.get( '.published > a' ).should( 'contain.text', 'My Forms' )
			.find( '.count' ).then( $count => {
				expect( $count.text() ).to.eq( baselinePublishedCount );
			} );

		cy.log( 'Empty trash' );
		cy.get( '#delete_all' ).should( 'contain', 'Empty Trash' ).click();
		cy.get( '.trash > a' ).should( 'contain.text', 'Trash' )
			.find( '.count' ).should( 'contain.text', '(0)' );
		cy.get( '.published > a' ).should( 'contain.text', 'My Forms' )
			.find( '.count' ).then( $count => {
				expect( $count.text() ).to.eq( baselinePublishedCount );
			} );
		cy.get( '.colspanchange > p' ).should( 'contain', 'No forms found in the trash.' );
	} );
} );
