describe("Duplicating a form from the form list page", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
    });

    it("should create a duplicate form", () => {
        // Create a blank form
        cy.log("Create a blank form");
        cy.contains(".frm_nav_bar .button-primary", "Add New").click();
        cy.get(".frm-form-templates-grid-layout #frm-form-templates-create-form").should("contain", "Create a blank form").click();
        cy.get("#frm_submit_side_top").should("contain", "Save").click();
        cy.get("#frm-form-templates-modal").should("exist");
        cy.get(".frm-modal-title").should("contain", "Name your form");
        cy.get("#frm_new_form_name_input").type("Test Form");
        cy.get("#frm-save-form-name-button").should("contain", "Save").click();
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click();

        // Duplicate the newly created form
        cy.log("Duplicate the newly created form");
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
            console.log('Hovered Row:', $row);
            // Any interaction with $row goes here
            cy.wrap($row).find('.some-button').click();
        
        

            // Find the visible element with class "duplicate" within the hovered row and click it
            cy.wrap($row).within(() => {
                cy.get('.row-actions .duplicate .frm-trash-link').should('be.visible').click({ force: true });
            });

            cy.get("a[aria-label='Close']", { timeout: 5000 }).click();

            // Locate rows containing the text "Test Form" and count them
            cy.get('#the-list tr:contains("Test Form")').then($rows => {
                expect($rows.length).to.equal(2); // Assert that there are exactly 2 rows containing "Test Form"
            });
        });

        // Teardown: Delete Test Form and its duplicate
        cy.log("Teardown");
        cy.log("Delete Test Form and its duplicate");
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
            console.log('Hovered Row:', $row);

            // Find the visible element with class "trash" within the hovered row and click it
            cy.wrap($row).within(() => {
                cy.get('.row-actions .trash .frm-trash-link').should('be.visible').click({ force: true });
            });
            cy.get("div[role='dialog']").should("contain", "Do you want to move this form to the trash?");
            cy.xpath("//a[@id='frm-confirmed-click']").should("contain", "Confirm").click({ force: true });

            // To ensure elements are not chained from previous action's results
            cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
                cy.wrap($row).within(() => {
                    cy.get('.row-actions .trash .frm-trash-link').should('be.visible').click({ force: true });
                });
                cy.get("div[role='dialog']").should("contain", "Do you want to move this form to the trash?");
                cy.xpath("//a[@id='frm-confirmed-click']").should("contain", "Confirm").click({ force: true });
            });
        });
    });
});
