describe("Duplicating fields in the form builder", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.createNewForm();
        cy.viewport(1280, 720);

    });

    it("should duplicate a field from each type", () => {
        cy.log("Click on the created form")
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').then(($row) => {
            cy.wrap($row).within(() => {
                cy.get('.column-name .row-title').should('exist').and('be.visible').then(($elem) => {
                    console.log('Element is:', $elem);
                    cy.wrap($elem).click({ force: true });
                });
            });
        });

        cy.get('h1 > .frm_bstooltip').should("contain", "Test Form")
        cy.get('.current_page').should("contain", "Build");
        cy.get('.frm_field_list > #frm-nav-tabs > .frm-tabs > #frm_insert_fields_tab').should("contain", "Add Fields");

        cy.log("Create a text field and duplicate it");
        cy.get('li[id="text"] a[title="Text"]').click();
        cy.get('li[data-type="text"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('.frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="text"]').should("have.length", 2);


        cy.log("Create a paragraph field and duplicate it");
        cy.get('li[id="textarea"] a[title="Paragraph"]').click();
        cy.get('li[data-ftype="textarea"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="textarea"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="textarea"]').should("have.length", 2);

        cy.log("Create a checkbox field and duplicate it");
        cy.get('li[id="checkbox"] a[title="Checkboxes"]').click();
        cy.get('li[data-ftype="checkbox"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="checkbox"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="checkbox"]').should("have.length", 2);

        cy.log("Create a radio button field and duplicate it");
        cy.get('li[id="radio"] a[title="Radio Buttons"]').click();
        cy.get('li[data-ftype="radio"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="radio"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="radio"]').should("have.length", 2);

        cy.log("Create a dropdown field and duplicate it");
        cy.get('li[id="select"] a[title="Dropdown"]').click();
        cy.get('li[data-ftype="select"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="select"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="select"]').should("have.length", 2);

        cy.log("Create an email field and duplicate it");
        cy.get('li[id="email"] a[title="Email"]').click();
        cy.get('li[data-ftype="email"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="email"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.wait(1000);
        cy.get('li[data-type="email"]').should("have.length", 2);

        cy.log("Create a Website/URL field and duplicate it");
        cy.get('li[id="url"] a[title="Website/URL"]').click();
        cy.get('li[data-ftype="url"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="url"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="url"]').should("have.length", 2);

        cy.log("Create a Number field and duplicate it");
        cy.get('li[id="number"] a[title="Number"]').click();
        cy.get('li[data-ftype="number"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="number"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="number"]').should("have.length", 2);

        cy.log("Create a Name field and duplicate it");
        cy.get('li[id="name"] a[title="Name"]').click();
        cy.get('li[data-ftype="name"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="name"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.wait(1000);
        cy.get('li[data-type="name"]').should("have.length", 2);

        cy.log("Create a Phone field and duplicate it");
        cy.get('li[id="phone"] a[title="Phone"]').click();
        cy.get('li[data-ftype="phone"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="phone"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="phone"]').should("have.length", 2);

        cy.log("Create a HTML field and duplicate it");
        cy.get('li[id="html"] a[title="HTML"]').click();
        cy.get('li[data-ftype="html"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 6000 }).click()
        cy.get('li[data-ftype="html"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="html"]').should("have.length", 2);

        cy.log("Create a Hidden field and duplicate it");
        cy.get('li[id="hidden"] a[title="Hidden"]').click();
        cy.get('li[data-ftype="hidden"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="hidden"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="hidden"]').should("have.length", 2);

        cy.log("Create an User ID field and duplicate it");
        cy.get('li[id="user_id"] a[title="User ID"]').click();
        cy.get('li[data-ftype="user_id"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="user_id"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="user_id"]').should("have.length", 2);

        cy.log("Create a Captcha field and duplicate it");
        cy.get('li[id="captcha"] a[title="Captcha"]').click();
        cy.get('li[data-ftype="captcha"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 5000 }).click()
        cy.get('li[data-ftype="captcha"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="captcha"]').should("have.length", 2);

        cy.log("Create a Payment field and duplicate it");
        cy.get('li[id="credit_card"] a[title="Payment"]').click();
        cy.get('li[data-ftype="credit_card"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use', { timeout: 7000 }).click()
        cy.get('li[data-ftype="credit_card"] .frm_clone_field > span').should("contain", "Duplicate").click();
        cy.get('li[data-type="credit_card"]').should("have.length", 2);

        cy.log("Save the form");
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click();

        cy.log("Teardown-Delete Form");
        cy.deleteForm();

    });

});

