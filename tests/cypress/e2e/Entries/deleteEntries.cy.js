describe( 'Entries submitted from a form', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should be verified and deleted', () => {
		cy.log( 'Create a blank form' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();

		cy.log( `Add some fields` );
		cy.get( 'li[id="text"] a[title="Text"]' ).click( { force: true } );
		cy.get( 'li[id="name"] a[title="Name"]' ).click( { force: true } );
		cy.get( 'li[id="checkbox"] a[title="Checkboxes"]' ).click( { force: true } );
		cy.get( 'li[id="email"] a[title="Email"]' ).click( { force: true } );
		cy.get( 'li[id="phone"] a[title="Phone"]' ).click( { force: true } );

		cy.log( 'Update form' );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Fill in and submit form' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).first().type( 'Entry test' );
		cy.get( '[id^="field_"][id$="_first"]' ).type( 'Name' );
		cy.get( '[id^="field_"][id$="_last"]' ).type( 'Surname' );
		cy.get( '[id^="frm_checkbox_"]' ).eq( 0 ).click();
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 5 ).type( 'test@test.com' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 6 ).type( '+1111111111' );
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();
		cy.go( -2 );
		cy.get( '.frm_form_nav > :nth-child(4) > a' ).should( 'contain', 'Entries' ).click();
		cy.get( '.wrap > h2' ).should( 'contain', 'Form Entries' );
		cy.get( '[data-colname="Text"]' ).contains( 'Entry test' ).parent().find( '.row-title' ).click();

		cy.log( 'Verify entry information' );
		cy.get( '.hndle > :nth-child(1)' ).should( 'contain', 'Entry' );
		cy.get( ':nth-child(1) > th' ).should( 'contain', 'Text' );
		cy.get( ':nth-child(1) > td' ).should( 'contain', 'Entry test' );
		cy.get( ':nth-child(2) > th' ).should( 'contain', 'Name' );
		cy.get( ':nth-child(2) > td' ).should( 'contain', 'Name Surname' );
		cy.get( ':nth-child(3) > th' ).should( 'contain', 'Checkboxes' );
		cy.get( ':nth-child(3) > td' ).should( 'contain', 'Option 1' );
		cy.get( ':nth-child(4) > th' ).should( 'contain', 'Email' );
		cy.get( ':nth-child(4) > td' ).should( 'contain', 'test@test.com' );
		cy.get( ':nth-child(5) > th' ).should( 'contain', 'Phone' );
		cy.get( ':nth-child(5) > td' ).should( 'contain', '+1111111111' );

		cy.log( 'Delete entry' );
		cy.go( 'back' );
		cy.contains( '#the-list tr', 'Entry test' ).trigger( 'mouseover' ).then( $row => {
			cy.wrap( $row ).within( () => {
				cy.get( '.row-actions .delete .submitdelete' ).should( 'be.visible' ).click( { force: true } );
			} );
		} );
		cy.contains( '.frm-confirm-msg', 'Permanently delete this entry?' );
		cy.get( '#frm-confirmed-click' ).should( 'contain', 'Confirm' ).click();

		cy.log( 'Teardown - Close and delete form' );
		cy.get( '.frm_form_nav > :nth-child(1) > a' ).should( 'contain', 'Build' ).click();
		cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click( { force: true } );
		cy.deleteForm();
	} );
} );
