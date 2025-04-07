describe( 'Run some HTML validation', function() {
    it('Check the form list has valid HTML', () => {
        cy.login();
        cy.visit( '/wp-admin/admin-ajax.php?action=frm_forms_preview&form=contact-form' );
        cy.get('#form_contact-form').htmlvalidate();
    });
});
