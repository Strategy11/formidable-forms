describe("Entries submitted from a form", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.viewport(1280, 720);
    });

    it("should not be stored", () => {

        cy.log("Create a blank form");
        cy.contains(".frm_nav_bar .button-primary", "Add New").click();
        cy.get(".frm-form-templates-grid-layout #frm-form-templates-create-form").should("contain", "Create a blank form").click();
        cy.get("#frm_submit_side_top", { timeout: 5000 }).should("contain", "Save").click();
        cy.get("#frm_new_form_name_input").type("Test Form");
        cy.get("#frm-save-form-name-button").should("contain", "Save").click();

        cy.log(`Create a text field`);
        cy.get(`li[id="text"] a[title="Text"]`).click({ force: true });

        cy.log("Update form");
        cy.get('#frm_submit_side_top').should("contain", "Update").click();

        cy.log("Submit form and verify entry is stored");
        cy.get("#frm-previewDrop",{timeout:5000}).should("contain", "Preview").click();
        cy.get('.preview > .frm-dropdown-menu > :nth-child(1) > a').should("contain", "On Blank Page").invoke('removeAttr', 'target').click();
        cy.get('[id^="field_"]').filter('input, textarea').type("Entry is stored");
        cy.get("button[type='submit']").should("contain", "Submit").click();   
        cy.go(-2);
        cy.get('.frm_form_nav > :nth-child(4) > a').should("contain","Entries").click();
        cy.get('.wrap > h2').should("contain","Form Entries");
        cy.get("#the-list td[data-colname='Text']").should("contain","Entry is stored");
        cy.get('.frm-meta-tag',{timeout:5000}).should("contain","Submitted");

        cy.log("Go to Settings tab and enable the 'Do not store entries submitted from this form' option");
        cy.xpath("//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]").should("contain","Settings").click();
        cy.get(':nth-child(8) > .frm_inline_block').should("contain","Do not store entries submitted from this form");
        cy.get('#no_save').check();
        cy.get('#frm_submit_side_top').should("contain", "Update").click();

        cy.log("Submit another form and verify entry is not stored");
        cy.get("#frm-previewDrop",{timeout:5000}).should("contain", "Preview").click();
        cy.get('.preview > .frm-dropdown-menu > :nth-child(1) > a').should("contain", "On Blank Page").invoke('removeAttr', 'target').click();
        cy.get('[id^="field_"]').filter('input, textarea').type("Entry is not stored");
        cy.get("button[type='submit']").should("contain", "Submit").click();   
        cy.go(-2);
        cy.get('.frm_form_nav > :nth-child(4) > a').should("contain","Entries").click();
        cy.get('.wrap > h2').should("contain","Form Entries");
        cy.get("#the-list td[data-colname='Text']").should("not.contain","Entry is not stored");
        cy.get('.displaying-num').should("contain", "1 item");

        cy.log("Teardown - Close and delete form");
        cy.get('.frm_form_nav > :nth-child(1) > a').should("contain","Build").click();
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click({ force: true });    
        cy.deleteForm(); 
    });

});
