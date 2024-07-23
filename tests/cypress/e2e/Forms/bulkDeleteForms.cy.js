describe("Bulk delete forms from the form list page", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.viewport(1280, 720);

    });

    it("should create multiple forms and bulk delete them", () => {
        cy.log("Create 5 new forms");
        for (let i = 0; i < 5; i++) {
            cy.createNewForm();
        }

        cy.log("Bulk delete all 5 new forms");
        cy.get('tr').filter((element) => {
            return Cypress.$(element).text().includes('Test Form');
        }).each(($row) => {
            cy.wrap($row).find('.check-column input[type="checkbox"]').check();
        });

        cy.log("Click on Bulk Actions and select the Move to Trash option")
        cy.get('#bulk-action-selector-top').select("Move to Trash");
        cy.get('#doaction').should("contain", "Apply").click();

        cy.get('.frm_updated_message').should("contain", "forms moved to the Trash.");



    })
})
