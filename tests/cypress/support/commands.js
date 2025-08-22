// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

Cypress.Commands.add("createNewForm", () => {
    cy.log("Create a blank form");
    cy.contains(".frm_nav_bar .button-primary", "Add New").click();
    cy.get(".frm-list-grid-layout #frm-form-templates-create-form").should("contain", "Create a blank form").click();
    cy.get("#frm_submit_side_top", { timeout: 5000 }).should("contain", "Save").click();
    cy.get("#frm-form-templates-modal").should("exist");
    cy.get(".frm-modal-title").should("contain", "Name your form");
    cy.get("#frm_new_form_name_input").type("Test Form");
    cy.get("#frm-save-form-name-button").should("contain", "Save").click();
    cy.get("a[aria-label='Close']", { timeout: 7000 }).click();
})
Cypress.Commands.add("deleteForm", () => {
    cy.log("Delete Form");
    cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
        console.log('Hovered Row:', $row);
        cy.wrap($row).within(() => {
            cy.get('.row-actions .trash .frm-trash-link').should('be.visible').click({ force: true });
        });
        cy.get("body").then(($body) => {
            if ($body.find("div[role='dialog']").length) {
                cy.get("div[role='dialog']").should("be.visible").and("contain.text", "Do you want to move this form to the trash?");
                cy.xpath("//a[@id='frm-confirmed-click']").should("contain.text", "Confirm").click({ force: true });
            } else {
                cy.log("Dialog not found");
            }
        });
    });
});

Cypress.Commands.add("openForm", () => {
    cy.log("Click on the created form");
    cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
        cy.wrap($row).within(() => {
            cy.get('.column-name .row-title').should('exist').and('be.visible').then(($elem) => {
                console.log('Element is:', $elem);
                cy.wrap($elem).click({ force: true });
            });
        });
    });

    cy.get('h1 > .frm_bstooltip').should("contain", "Test Form");
    cy.get('.current_page').should("contain", "Build");
    cy.xpath("//li[@class='frm-active']//a[@id='frm_insert_fields_tab']").should("contain", "Add Fields");

});

Cypress.Commands.add("getCurrentFormattedDate", () => {
    const currentDate = new Date();
    const formattedDate = currentDate.toISOString().split('T')[0].replace(/-/g, '/');
    return formattedDate;
});

Cypress.Commands.add("emptyTrash", () => {
cy.log("Precondition - Clear trash if there are deleted forms in the list");
cy.get('.subsubsub > .trash > a').then(($trashLink) => {
    if ($trashLink.text().includes("Trash")) {
        cy.wrap($trashLink).click();
        cy.get('body').then($body => {
            if ($body.find('#delete_all').length > 0) {
                cy.get('#delete_all').should("contain", "Empty Trash").click();
            } else {
                cy.log('No forms available to delete.');
            }
        });
    } else {
        cy.log('No forms in the Trash.');
    }
});
});
