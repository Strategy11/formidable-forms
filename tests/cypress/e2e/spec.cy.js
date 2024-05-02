
const login = () => {
    cy.session('admin', () => {
        cy.login();
        cy.visit( '/wp-admin' );
        cy.url().should('include', 'wp-admin');
        cy.get( 'h1' ).should('contain.text', 'Dashboard');
    });
};

describe( 'Run some basic Formidale tests', function() {
    before( login );

    it('Can visit forms list and navigate to form templates page', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable' );
        cy.get( 'h1' ).should('contain.text', 'Forms');
        const submitButton = cy.get( '#frm-publishing a.frm-button-primary' );
        submitButton.should( 'contain.text', 'Add New' );
        submitButton.click();

        cy.url().should('include', 'admin.php?page=formidable-form-templates');
    });

});

