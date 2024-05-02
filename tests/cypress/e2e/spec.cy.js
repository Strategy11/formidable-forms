
const login = name => {
    cy.session( name, () => {
        cy.login();
        cy.visit( '/wp-admin' );
        cy.url().should('include', 'wp-admin');
        cy.get( 'h1' ).should('contain.text', 'Dashboard');
        cy.visit( '/wp-admin/plugins.php' );
        cy.get( 'h1' ).should('contain.text', 'Plugins');
        cy.activatePlugin( 'formidable' );
    });
};

describe( 'Configure WordPress', function() {
    before(() => {
        login( 'admin' );
    });

    it('Can visit forms list and navigate to form templates page', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable' );
        cy.get( 'h1' ).should('contain.text', 'Forms');
        const submitButton = cy.get( '#frm-publishing a.frm-button-primary' );
        submitButton.should( 'contain.text', 'Add New' );
        submitButton.click();

        cy.url().should('include', 'admin.php?page=formidable-form-templates');
    });

});

