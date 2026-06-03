describe( 'Updating global settings', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should update the custom success message', () => {
		cy.log( 'Open Message defaults page' );
		cy.get( '.frm-category-tabs > :nth-child(2) > a' ).should( 'contain', 'Message Defaults' ).click();
		cy.get( '#messages_settings > :nth-child(9)' ).should( 'exist' );
		cy.get( ':nth-child(9) > .frm_left_label' ).should( 'contain', 'Success Message' );
		cy.get( '#messages_settings input[type="text"]' ).eq( 4 ).clear();
		cy.get( '#messages_settings input[type="text"]' ).eq( 4 ).type( '[form_name] is submitted successfully!' );
		cy.get( '#frm-publishing > .button-primary' ).click();

		cy.log( 'Create a form and check update success message' );
		cy.xpath( "//a[normalize-space()='Forms (Lite)']", { timeout: 5000 } ).click();

		cy.log( 'Create a blank form' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();

		cy.log( `Create a text field` );
		cy.get( `li[id="text"] a[title="Text"]` ).click( { force: true } );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();

		cy.log( 'Verify updated success message' );
		cy.get( '.frm_message' ).should( 'contain', 'Test Form is submitted successfully!' );

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( -2 );

		cy.log( 'Click on Preview - In Theme' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(2) > a' ).should( 'contain', 'In Theme' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();

		cy.log( 'Verify updated success message' );
		cy.get( '.frm_message' ).should( 'contain', 'Test Form is submitted successfully!' );
		cy.go( -2 );
	} );
	afterEach( () => {
		cy.log( 'Teardown - Save the form and delete it' );
		cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click( { force: true } );
		cy.deleteForm();
	} );
} );
