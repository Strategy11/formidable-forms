export const createNewForm = () => {
    cy.log("Create a blank form");
    cy.contains(".frm_nav_bar .button-primary", "Add New").click();
    cy.get(".frm-form-templates-grid-layout #frm-form-templates-create-form").should("contain", "Create a blank form").click();
    cy.get("#frm_submit_side_top", { timeout: 5000 }).should("contain", "Save").click();
    cy.get("#frm-form-templates-modal").should("exist");
    cy.get(".frm-modal-title").should("contain", "Name your form");
    cy.get("#frm_new_form_name_input").type("Test Form");
    cy.get("#frm-save-form-name-button").should("contain", "Save").click();
    cy.get("a[aria-label='Close']", { timeout: 7000 }).click();
};

export const deleteForm = () => {

    cy.log("Delete Test Form and its duplicate");
    cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
        console.log('Hovered Row:', $row);
        // Find the visible element with class "trash" within the hovered row and click it
        cy.wrap($row).within(() => {
            cy.get('.row-actions .trash .frm-trash-link').should('be.visible').click({ force: true });
        });
        cy.get("div[role='dialog']").should("contain", "Do you want to move this form to the trash?");
        cy.xpath("//a[@id='frm-confirmed-click']").should("contain", "Confirm").click({ force: true });
    })

}