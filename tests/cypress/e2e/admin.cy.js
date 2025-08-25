describe( 'Run some basic Formidale tests', function() {
	beforeEach( cy.login );

	it( 'Can visit forms list and navigate to form templates page', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.get( 'h1' ).should( 'contain.text', 'Forms' );
		cy.get( '#frm-publishing a.frm-button-primary' ).should( 'contain.text', 'Add New' ).click();

		cy.url().should( 'include', 'admin.php?page=formidable-form-templates' );
		cy.get( 'h1' ).should( 'contain.text', 'Form Templates' );
	} );

	it( 'Can create a new form, visit settings, preview the form and submit it.', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
		cy.get( '#frm-form-templates-create-form' ).click();
		cy.url().should( 'include', 'wp-admin/admin.php?page=formidable&frm_action=edit&id=' );

		// Confirm we can add a text field, and add one.
		// This is to make sure our form has a field so it can be submitted later.
		cy.get( '#text' ).should( 'contain.text', 'Text' ).click();

		// Check if we can access form settings for the new form after clicking on the settings tab.
		cy.get( 'a[href*="/wp-admin/admin.php?page=formidable&frm_action=settings&id="]' ).click();
		cy.get( 'h2' ).should( 'contain.text', 'General Form Settings' );

		// Load the settings page fresh so it doesn't try to prompt for a form name on save.
		cy.get( '#form_id' ).invoke( 'val' ).then( formId => {
			cy.visit( 'wp-admin/admin.php?page=formidable&frm_action=settings&id=' + formId );

			// Update the form settings. Give the form a name.
			cy.get( '#frm_form_name' ).type( 'My form' );
			cy.get( '#frm_submit_side_top' ).click();
			cy.get( '.frm_updated_message' ).should( 'contain.text', 'Settings Successfully Updated' );

			// Load the form preview and check if there is a submit button.
			// Submit the form and expect a success message.
			cy.get( '#frm_form_key' ).invoke( 'val' ).then( formKey => {
				cy.visit( '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=' + formKey );
				cy.get( '.frm_button_submit' ).should( 'contain.text', 'Submit' ).click();
				cy.get( '.frm_message' ).should( 'contain.text', 'Your responses were successfully submitted. Thank you!' );
			} );
		} );
	} );

	it( 'Can access global settings', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.get( 'h1' ).should( 'contain.text', 'Settings' );
		cy.get( 'h2' ).should( 'contain.text', 'General Settings' );
	} );

	it( 'Can access style settings', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
		cy.get( '#general-style h3' ).should( 'contain.text', 'General' );
		cy.get( '#frm_submit_side_top' ).should( 'contain.text', 'Update' );
	} );

	it( 'Can access import/export', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-import' );
		cy.get( 'h1' ).should( 'contain.text', 'Import/Export' );
	} );

	it( 'Can access applications', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-applications' );
		cy.get( 'h1' ).should( 'contain.text', 'Applications' );
	} );

	it( 'Can access addons', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-addons' );
		cy.get( 'h1' ).should( 'contain.text', 'Formidable Add-Ons' );
	} );

	it( 'Can access entries', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-entries' );
		cy.get( 'h1' ).should( 'contain.text', 'Form Entries' );
	} );

	it( 'Can access dashboard', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
		cy.get( 'h1' ).should( 'contain.text', 'Dashboard' );
		cy.get( 'h2' ).should( 'contain.text', 'Latest Entries' );
	} );
} );
