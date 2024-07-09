describe("Submitting a form", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
    });

    it("should redirect you to a specific URL", () => {
        cy.log("Create a blank form");
        cy.contains(".frm_nav_bar .button-primary", "Add New").click();
        cy.get(".frm-form-templates-grid-layout #frm-form-templates-create-form").should("contain", "Create a blank form").click();
        cy.get("#frm_submit_side_top", { timeout: 5000 }).should("contain", "Save").click();
        cy.get("#frm_new_form_name_input").type("Test Form");
        cy.get("#frm-save-form-name-button").should("contain", "Save").click();

        cy.log(`Create a text field`);
        cy.get(`li[id="text"] a[title="Text"]`).click({ force: true });

        cy.log("Update form");
        cy.get('#frm_submit_side_top').should("contain", "Update").click();

        cy.log("Go to Settings tab and enable the option to show the title and description in the form preview");
        cy.get(".frm_form_nav", { timeout: 5000 }).should("be.visible");
        cy.xpath("//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]").should("contain", "Settings").click();

        cy.log("Click on the confirmation action and add the redirect URL");
        cy.get('.frm-category-tabs > :nth-child(2) > a').should("contain", "Actions & Notifications").click();
        cy.get('.widget .widget-title', { timeout: 5000 }).first().should("contain", "Confirmation").click();
        cy.get('.frm_on_submit_type_setting > .frm_grid_container > :nth-child(2) > label').should("contain", "Redirect to URL").click();
        cy.get('.frm_on_submit_redirect_settings > .frm_has_shortcodes > label').should("contain", "Redirect URL");
        cy.get('[id^="success_url_"]').should("exist").type("http://formidablecypress.local");

        cy.log("Update form");
        cy.get('#frm_submit_side_top').should("contain", "Update").click();

        cy.get("#frm-previewDrop", { timeout: 5000 }).should("contain", "Preview").click();
        cy.get('.preview > .frm-dropdown-menu > :nth-child(1) > a').should("contain", "On Blank Page").invoke('removeAttr', 'target').click();
        cy.get("button[type='submit']").should("contain", "Submit").click();

        cy.log("Verify URL redirect after submitting form");
        cy.url().should('eq', 'http://formidablecypress.local/');
        cy.go(-2);

    });
    
    afterEach(() => {

        cy.log("Teardown - Save the form and delete it");
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click({ force: true });
        cy.deleteForm();

    });
});