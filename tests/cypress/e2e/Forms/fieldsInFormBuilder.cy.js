describe("Fields in the form builder", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.createNewForm();
        cy.viewport(1280, 720);
    });
    it("should create, duplicate a field from each type and delete them", () => {

        const createAndDuplicateField = (fieldId, fieldType) => {
            cy.log(`Create a ${fieldType} field and duplicate it`);
            cy.get(`li[id="${fieldId}"] a[title="${fieldType}"]`).click();
            cy.get(`li[data-ftype="${fieldId}"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 }).click({ force: true });
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
    });


    it("should rename a field from each type", () => {

        const openForm = () => {
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
            cy.get('.frm_field_list > #frm-nav-tabs > .frm-tabs > #frm_insert_fields_tab').should("contain", "Add Fields");
        };

        const createField = (fieldId, fieldType) => {
            cy.log(`Create a ${fieldType} field`);
            cy.get(`li[id="${fieldId}"] a[title="${fieldType}"]`).click({ force: true });
        };

        const renameField = (fieldId, fieldType, fieldValue) => {
            cy.log(`Rename a ${fieldType} field`);
            cy.get(`li[data-ftype="${fieldId}"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 }).click({ force: true });
            cy.get(`li[data-ftype="${fieldId}"] .frm_select_field > span`).should("contain", "Field Settings").click({ force: true });
            cy.get(`div[id^="frm-single-settings-"] input[value="${fieldValue}"]`, { timeout: 10000 }).should('be.visible').clear({ force: true }).type(`${fieldType} Updated`, { force: true });
        };

        openForm();

        const fieldsToProcess = [
            { fieldId: "text", fieldType: "Text", fieldValue: "Text" },
            { fieldId: "textarea", fieldType: "Paragraph", fieldValue: "Paragraph" },
            { fieldId: "checkbox", fieldType: "Checkboxes", fieldValue: "Checkboxes" },
            { fieldId: "radio", fieldType: "Radio Buttons", fieldValue: "Radio Buttons" },
            { fieldId: "select", fieldType: "Dropdown", fieldValue: "Dropdown" },
            { fieldId: "email", fieldType: "Email", fieldValue: "Email" },
            { fieldId: "url", fieldType: "Website/URL", fieldValue: "Website/URL" },
            { fieldId: "number", fieldType: "Number", fieldValue: "Number" },
            { fieldId: "name", fieldType: "Name", fieldValue: "Name" },
            { fieldId: "phone", fieldType: "Phone", fieldValue: "Phone" },
            { fieldId: "html", fieldType: "HTML", fieldValue: "HTML" },
            { fieldId: "hidden", fieldType: "Hidden", fieldValue: "Hidden" },
            { fieldId: "user_id", fieldType: "User ID", fieldValue: "User ID" },
            { fieldId: "captcha", fieldType: "Captcha", fieldValue: "Captcha" },
            { fieldId: "credit_card", fieldType: "Payment", fieldValue: "Payment" }
        ];

        fieldsToProcess.forEach(field => {
            createField(field.fieldId, field.fieldType);
            renameField(field.fieldId, field.fieldType, field.fieldValue);
        });
    });

    afterEach(() => {

        cy.log("Save the form");
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click({ force: true });

        cy.log("Teardown-Delete Form");
        cy.deleteForm();
    })
});