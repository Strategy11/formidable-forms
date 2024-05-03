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
    });
});
