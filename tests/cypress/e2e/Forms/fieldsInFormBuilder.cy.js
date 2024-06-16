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

    it("should set fields as required and validate them in frontend", () => {

        const fieldTypes = ['Text','Paragraph','Checkboxes','Radio Buttons','Dropdown','Email','Website/URL','Number','Name','Phone'];

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

        const requiredField = (fieldId, fieldType) => {
            cy.log(`Set ${fieldType} field as require`);
            cy.get(`li[data-ftype="${fieldId}"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 }).click({ force: true });
            cy.get(`li[data-ftype="${fieldId}"] .frm_select_field > span`).should("contain", "Field Settings").click({ force: true });
            cy.get('.frm_field_list div[id^="frm-single-settings-"] .frm_grid_container .frm-hide-empty input[type="checkbox"]', { timeout: 10000 }).check({ force: true });
        };

        openForm();

        const fieldsToSetAsRequired = [
            { fieldId: "text", fieldType: "Text" },
            { fieldId: "textarea", fieldType: "Paragraph" },
            { fieldId: "checkbox", fieldType: "Checkboxes" },
            { fieldId: "radio", fieldType: "Radio Buttons" },
            { fieldId: "select", fieldType: "Dropdown" },
            { fieldId: "email", fieldType: "Email" },
            { fieldId: "url", fieldType: "Website/URL" },
            { fieldId: "number", fieldType: "Number" },
            { fieldId: "name", fieldType: "Name" },
            { fieldId: "phone", fieldType: "Phone" }
        ];

        fieldsToSetAsRequired.forEach(field => {
            createField(field.fieldId, field.fieldType);
            requiredField(field.fieldId, field.fieldType);
        });

            cy.log("Update form");
            cy.get('#frm_submit_side_top').should("contain", "Update").click();

            cy.log("Click on Preview - Blank Page");
            cy.get("#frm-previewDrop",{timeout:5000}).should("contain", "Preview").click();
            cy.get('.preview > .frm-dropdown-menu > :nth-child(1) > a').should("contain", "On Blank Page").invoke('removeAttr', 'target').click();

            cy.get("button[type='submit']").should("contain", "Submit").click();
            cy.log("Check on error messages - Blank Page");
            cy.get('.frm_error_style').should("contain", "There was a problem with your submission. Errors are marked below.");

            fieldTypes.forEach(fieldType => {
                cy.contains(`[id^="frm_error_field_"]`, `${fieldType} cannot be blank.`);
            });

            cy.log("Navigate back to the formidable form page");
            cy.go(-2);

            cy.log("Click on Preview - In Theme");
            cy.get("#frm-previewDrop",{timeout:5000}).should("contain", "Preview").click();
            cy.get('.preview > .frm-dropdown-menu > :nth-child(2) > a').should("contain", "In Theme").invoke('removeAttr', 'target').click();

            cy.get("button[type='submit']").should("contain", "Submit").click();
            cy.log("Check on error messages - In Theme");
            cy.get('.frm_error_style').should("contain", "There was a problem with your submission. Errors are marked below.");

            fieldTypes.forEach(fieldType => {
                cy.contains(`[id^="frm_error_field_"]`, `${fieldType} cannot be blank.`);
            });

            cy.log("Navigate back to the formidable form page");
            cy.go(-2);
        });    
    
    afterEach(() => {

        cy.log("Teardown - Save the form and delete it");
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click({ force: true });    
        cy.deleteForm();        
    });
});
