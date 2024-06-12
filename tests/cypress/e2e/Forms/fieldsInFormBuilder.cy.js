describe("Fields in the form builder", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.createNewForm();
        cy.viewport(1280, 720);
    });

    const createAndDuplicateField = (fieldId, fieldType) => {
        cy.log(`Create a ${fieldType} field and duplicate it`);
        cy.get(`li[id="${fieldId}"] a[title="${fieldType}"]`).click();
        cy.get(`li[data-ftype="${fieldId}"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`,{ timeout: 10000 }).click({ force: true });
        cy.get(`li[data-ftype="${fieldId}"] .frm_clone_field > span`).should("contain", "Duplicate").click({ force: true });

        cy.get(`li[data-type="${fieldId}"]`).should('have.length', 2);
        const originalField = cy.get(`li[data-type="${fieldId}"]:first`);
        const duplicateField = cy.get(`li[data-type="${fieldId}"]:last`);
        return { originalField, duplicateField };
    };

    const removeField = (field) => {
        field.within(() => {
            cy.get('.frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use').click({ force: true });
            cy.get('.frm-dropdown-menu > :nth-child(1) > .frm_delete_field').should("contain", "Delete").click({ force: true });
        });
        cy.get('.postbox a[id="frm-confirmed-click"]').contains("Confirm").should('be.visible').click({ force: true });
        cy.get(`li[data-type="${field}"]`).should('not.exist');
    };

    it("should create, duplicate a field from each type and delete them", () => {
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
            cy.wrap($row).within(() => {
                cy.get('.column-name .row-title').should('exist').and('be.visible').then(($elem) => {
                    cy.wrap($elem).click({ force: true });
                });
            });
        });

        cy.get('h1 > .frm_bstooltip').should("contain", "Test Form");
        cy.get('.current_page').should("contain", "Build");
        cy.get('.frm_field_list > #frm-nav-tabs > .frm-tabs > #frm_insert_fields_tab').should("contain", "Add Fields");

        cy.log("Create and duplicate fields for each type");
        const fieldsToDelete = [
            createAndDuplicateField("text", "Text"),
            createAndDuplicateField("textarea", "Paragraph"),
            createAndDuplicateField("checkbox", "Checkboxes"),
            createAndDuplicateField("radio", "Radio Buttons"),
            createAndDuplicateField("select", "Dropdown"),
            createAndDuplicateField("email", "Email"),
            createAndDuplicateField("url", "Website/URL"),
            createAndDuplicateField("number", "Number"),
            createAndDuplicateField("name", "Name"),
            createAndDuplicateField("phone", "Phone"),
            createAndDuplicateField("html", "HTML"),
            createAndDuplicateField("hidden", "Hidden"),
            createAndDuplicateField("user_id", "User ID"),
            createAndDuplicateField("captcha", "Captcha"),
            createAndDuplicateField("credit_card", "Payment")
        ];

        cy.log("Sequentially delete each field along with its duplicate");
        fieldsToDelete.forEach(fields => {
            removeField(fields.originalField);
            removeField(fields.duplicateField);
        });

        cy.log("Teardown-Delete form");
        cy.get("a[aria-label='Close']").click({ force: true });
        cy.deleteForm();
    });
});
