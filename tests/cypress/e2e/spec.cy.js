const login = name => {
    cy.session( name, () => {
        cy.visit( 'http://devsite.formidableforms.com:8889/wp-login.php' );

        // User login.
        cy.get( '#user_login' ).type( name );

        // User pass.
        cy.get( '#user_pass' ).type( 'password' );

        // WP submit.
        cy.get( '#wp-submit' ).click();

        cy.url().should('include', 'wp-admin');
        cy.get( 'h1' ).should('contain.text', 'Dashboard');
    });
};

describe( 'Configure WordPress', function() {
    before(() => {
        login( 'admin' );
    });

    it('Can visit forms list and navigate to form templates page', () => {
        cy.visit( 'http://devsite.formidableforms.com:8889/wp-admin/admin.php?page=formidable' );
        cy.get( 'h1' ).should('contain.text', 'Forms');
        const submitButton = cy.get( '#frm-publishing a.frm-button-primary' );
        submitButton.should( 'contain.text', 'Add New' );
        submitButton.click();

        cy.url().should('include', 'admin.php?page=formidable-form-templates');
    });

});

