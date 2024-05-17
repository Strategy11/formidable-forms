describe("Duplicating a form from the form list page", () => {
    beforeEach(cy.login);


    it("should create a duplicate form", () => {
        cy.visit('/wp-admin/admin.php?page=formidable');

        cy.log("Create a blank form");
        cy.get(".frm_nav_bar .button-primary").should("contain", "Add New").click();
        cy.get(".frm-form-templates-grid-layout #frm-form-templates-create-form").should("contain", "Create a blank form").click();
        cy.get("#frm_submit_side_top").should("contain", "Save").click();
        cy.get("#frm-form-templates-modal").should("exist")
            .get(".frm-modal-title").should("contain", "Name your form")
            .get("#frm_new_form_name_input").type("Test Form")
            .get("#frm-save-form-name-button").should("contain", "Save").click();
        cy.get("a[aria-label='Close']", { timeout: 5000 }).as('btn').click();

        cy.log("Duplicate the newly created form");
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
            console.log('Hovered Row:', $row);

            // Wait a moment to ensure the hover effect has time to display the duplicate element
            cy.wait(500);

            // Find the visible element with class "duplicate" within the hovered row and click it
            cy.wrap($row).within(() => {
                cy.get('.row-actions .duplicate .frm-trash-link').should('be.visible').then(($duplicate) => {
                    console.log('Duplicate Element:', $duplicate);
                    cy.wrap($duplicate).click({ force: true });
                });
            });

            cy.get("a[aria-label='Close']", { timeout: 5000 }).as('btn').click();

            // Locate rows containing the text "Test Form" and count them
            cy.get('#the-list tr:contains("Test Form")').then($rows => {
                // Assert that there are exactly 2 rows containing "Test Form"
                expect($rows.length).to.equal(2);
            });

            cy.log("Teardown");
            cy.log("Delete Test Form and it's duplicate");

            cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
                console.log('Hovered Row:', $row);

                // Wait a moment to ensure the hover effect has time to display the trash element
                cy.wait(500);

                // Find the visible element with class "trash" within the hovered row and click it
                cy.wrap($row).within(() => {
                    cy.get('.row-actions .trash .frm-trash-link').should('be.visible').then(($duplicate) => {
                        console.log('Duplicate Element:', $duplicate);
                        cy.wrap($duplicate).click({ force: true });
                    });
                });
                cy.get("div[role='dialog']").should("contain", "Do you want to move this form to the trash?");

                cy.xpath("//a[@id='frm-confirmed-click']").should("contain", "Confirm").click({ force: true });


            });

            cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
                console.log('Hovered Row:', $row);
                cy.wait(500);
                cy.wrap($row).within(() => {
                    cy.get('.row-actions .trash .frm-trash-link').should('be.visible').then(($duplicate) => {
                        console.log('Duplicate Element:', $duplicate);
                        cy.wrap($duplicate).click({ force: true });
                    });
                });
                cy.get("div[role='dialog']").should("contain", "Do you want to move this form to the trash?");
                cy.xpath("//a[@id='frm-confirmed-click']").should("contain", "Confirm").click({ force: true });


            });



        });

    })
})