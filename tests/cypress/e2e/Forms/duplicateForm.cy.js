describe( 'Duplicating a form from the form list page', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should create a duplicate form', () => {
		cy.createNewForm();

		cy.log( 'Duplicate the newly created form' );
		cy.contains( '#the-list tr', 'Test Form' ).trigger( 'mouseover' ).then( $row => {
			console.log( 'Hovered Row:', $row );

			cy.log( 'Find the visible element with class duplicate within the hovered row and click it' );
			cy.wrap( $row ).within( () => {
				// WP core only reveals row-actions on a real CSS `:hover` (`.row-actions` is
				// `position: relative; left: -9999em` until `tr:hover`) - make it actionable the
				// way the real hover would, then click normally. Chained in one continuous command
				// so there's no window between the reset and the click for a re-render to undo it.
				cy.get( '.row-actions' )
					.invoke( 'css', 'position', 'static' )
					.find( '.duplicate .frm-trash-link' )
					.should( 'be.visible' )
					.click();
			} );

			cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click();

			cy.log( 'Locate rows containing the text - Test Form and count them' );
			cy.get( '#the-list tr:contains("Test Form")' ).then( $rows => {
				expect( $rows ).to.have.lengthOf( 2 );
			} );
		} );
	} );

	// A separate afterEach (rather than teardown steps at the end of the `it` block) so cleanup
	// still runs even if the assertion above throws - otherwise a failed run leaves both the
	// original and the duplicate "Test Form" behind for every later spec that assumes a clean
	// list (see the cross-spec "Test Form" leakage this caused).
	afterEach( () => {
		cy.log( 'Teardown - Move every Test Form row to the trash, how many are left doesn\'t matter' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.get( 'body' ).then( $body => {
			const testFormRows = $body.find( '#the-list tr' ).filter( ( _, element ) => Cypress.$( element ).text().includes( 'Test Form' ) );

			if ( testFormRows.length === 0 ) {
				return;
			}

			cy.wrap( testFormRows ).each( $row => {
				cy.wrap( $row ).find( '.check-column input[type="checkbox"]' ).check();
			} );
			cy.get( '#bulk-action-selector-top' ).select( 'Move to Trash' );
			cy.get( '#doaction' ).should( 'contain', 'Apply' ).click();
		} );
	} );
} );
