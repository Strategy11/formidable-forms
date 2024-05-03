describe( 'Run some basic Formidale tests', function() {
    it('Can visit forms list and navigate to form templates page', () => {
        cy.login();
        cy.visit( '/wp-admin/admin.php?page=formidable' );
        cy.get( 'h1' ).should('contain.text', 'Forms');
        const submitButton = cy.get( '#frm-publishing a.frm-button-primary' );
        submitButton.should( 'contain.text', 'Add New' );
        submitButton.click();

        cy.url().should('include', 'admin.php?page=formidable-form-templates');
        cy.get( 'h1' ).should( 'contain.text', 'Form Templates' );
    });

    it('Can create a new form', () => {
        cy.login();
        cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
        cy.get( '#frm-form-templates-create-form' ).click();
        cy.url().should('include', 'wp-admin/admin.php?page=formidable&frm_action=edit&id=' );
        cy.get( '#text' ).should( 'contain.text', 'Text' );

        // Check if we can access form settings for the new form after clicking on the settings tab.
        cy.get( 'a[href*="/wp-admin/admin.php?page=formidable&frm_action=settings&id="]' ).click();
        cy.get( 'h2' ).should( 'contain.text', 'General Form Settings' );
    });

    it('Can access global settings', () => {
        cy.login();
        cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
        cy.get( 'h1' ).should( 'contain.text', 'Settings' );
        cy.get( 'h2' ).should( 'contain.text', 'General Settings' );
    });

    it('Can access style settings', () => {
        cy.login();
        cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
        cy.get( '#general-style h3' ).should( 'contain.text', 'General' );
        cy.get( '#frm_submit_side_top' ).should( 'contain.text', 'Update' );
    });

    it('Can access import/export', () => {
        cy.login();
        cy.visit( '/wp-admin/admin.php?page=formidable-import' );
        cy.get( 'h1' ).should( 'contain.text', 'Import/Export' );
    });

    it('Can access applications', () => {
        cy.login();
        cy.visit( '/wp-admin/admin.php?page=formidable-applications' );
        cy.get( 'h1' ).should( 'contain.text', 'Applications' );
    });
});
