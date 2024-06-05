describe("Duplicating fields in the form builder", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.createNewForm();
        cy.viewport(1280, 720);
    });

    const createAndDuplicateField = (fieldId, fieldType) => {
        cy.log(`Create a ${fieldType} field and duplicate it`);
        cy.get(`li[id="${fieldId}"] a[title="${fieldType}"]`).click();
        cy.get(`li[data-ftype="${fieldId}"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 7000 }).click();
        cy.get(`li[data-ftype="${fieldId}"] .frm_clone_field > span`).should("contain", "Duplicate").click();
        cy.get(`li[data-type="${fieldId}"]`, { timeout: 7000 }).should("be.visible").should("have.length", 2);
    };

    it("should duplicate a field from each type", () => {
        // Click on the created form
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
            cy.wrap($row).within(() => {
                cy.get('.column-name .row-title').should('exist').and('be.visible').then(($elem) => {
                    cy.wrap($elem).click({ force: true });
                });
            });
        });    
        // Assertions to verify form and field creation
        cy.get('h1 > .frm_bstooltip', { timeout: 10000 }).should("contain", "Test Form")
        cy.get('.current_page').should("contain", "Build");
        cy.get('.frm_field_list > #frm-nav-tabs > .frm-tabs > #frm_insert_fields_tab').should("contain", "Add Fields");
    
        // Create and duplicate fields for each type
        createAndDuplicateField("text", "Text");
        createAndDuplicateField("textarea", "Paragraph");
        createAndDuplicateField("checkbox", "Checkboxes");
        createAndDuplicateField("radio", "Radio Buttons");
        createAndDuplicateField("select", "Dropdown");
        createAndDuplicateField("email", "Email");
        createAndDuplicateField("url", "Website/URL");
        createAndDuplicateField("number", "Number");
        createAndDuplicateField("name", "Name");
        createAndDuplicateField("phone", "Phone");
        createAndDuplicateField("html", "HTML");
        createAndDuplicateField("hidden", "Hidden");
        createAndDuplicateField("user_id", "User ID");
        createAndDuplicateField("captcha", "Captcha");
        createAndDuplicateField("credit_card", "Payment");
    
        // Save the form and teardown
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click();
        cy.deleteForm();
    });
});
