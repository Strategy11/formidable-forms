describe("Forms page", () => {
    const formidableFormsUpgradeUrl = 'https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=settings-license&utm_campaign=liteplugin&utm_content=lite-banner';
    const origin = Cypress.config('baseUrl');
    const formTitle = "Test Form";

    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.createNewForm(formTitle);
        cy.viewport(1280, 720);
    });

    it("should validate all data in list view", () => {
        cy.log("Validate all header data");
        cy.log("Validate the upgrade link");
        cy.get('.frm-upgrade-bar > a')
            .should('have.text', 'upgrading to PRO').click();
        cy.origin('https://formidableforms.com', () => { 
            cy.get('h1').then(($h1) => {
                const text = $h1.text();
                expect(['The Only WordPress Form Maker & Application Builder Plugin', 'Upgrade Today to Unlock the Full Power of Formidable Forms']).to.include(text);
            });
        });            

        cy.log("Navigate back to the original page");
        cy.visit('/wp-admin/admin.php?page=formidable');

        cy.log("Validate the header logo link");
        cy.get('a.frm-header-logo')
            .should('have.attr', 'href', origin + "/wp-admin/admin.php?page=formidable")
            .click();

        cy.log("Validate the URL after clicking the header logo");
        cy.url().should('eq', origin + "/wp-admin/admin.php?page=formidable");

        cy.log("Validate other header elements");
        cy.get('h1').should("contain", "Forms");
        cy.get('#frm-publishing > .frm-button-secondary').should("contain", "Import");
        cy.get('#frm-publishing > .button-primary').should("contain", "Add New");

        cy.log("Validate all other elements shown in forms page");
        cy.get('.published > .current').should("contain", "My Forms");
        cy.get('.subsubsub > .trash > a').should("contain", "Trash");
        cy.get('#entry-search-input').should('exist');
        cy.get('#search-submit').should("contain", "Search");
        cy.get('#bulk-action-selector-top').should("contain", "Bulk Actions");
        cy.get('#doaction').should("contain", "Apply");

        cy.log("Verify that table view in forms page is in list mode");
        cy.get('#view-switch-list').should("exist").click();

        cy.log("Verify existence of the select all checkbox");
        cy.get('#cb-select-all-1').should("exist");

        cy.log("Verify column names in the forms page");
        cy.get('#name > a').should("contain", "Form Title");
        cy.get('#entries').should("contain", "Entries");
        cy.get('#id > a > :nth-child(1)').should("contain", "ID");
        cy.get('#form_key > a > :nth-child(1)').should("contain", "Key");
        cy.get('#shortcode').should("contain", "Actions");
        cy.get('#created_at > a > :nth-child(1)').should("contain", "Date");

        cy.log("Verify existence of a single row select checkbox");
        cy.get('[id^="cb-item-action-"]').should("exist");

        cy.log("Verify list view data of the created form");
        cy.get('.id').should("exist");
        cy.get(`[id^="item-action-"] > .name > strong > .row-title:contains("${formTitle}")`)
            .parents('[id^="item-action-"]')
            .within(() => {
                cy.get('.entries > a').should("contain", "0");
                cy.get('.name > strong > .row-title').should("contain", formTitle);
                cy.get('.form_key').should("contain", "test-form");
                cy.get('.shortcode > div').should("exist");

                cy.getCurrentFormattedDate().then((formattedDate) => {
                    cy.log('Formatted Date: ', formattedDate);

                    cy.log("Find the element that displays the date and get its text content");
                    cy.get('.created_at > abbr')
                        .invoke('text')
                        .then((dateText) => {
                            const dateMatch = dateText.match(/\d{4}\/\d{2}\/\d{2}/);
                            const displayedDate = dateMatch ? dateMatch[0] : '';
                            expect(displayedDate).to.equal(formattedDate);
                        });
                });
            });
    });

    it("should validate all data in excerpt view", () => {

        cy.log("Edit form to add description");
        cy.contains('#the-list tr', 'Test Form').trigger('mouseover').within(() => {
            cy.get('.row-actions .frm_edit a').should('be.visible').click({ force: true });
        });

        cy.xpath("//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]").should("contain","Settings").click();
        cy.get('#frm_form_description').should("be.visible").type("Lorem Ipsum is simply dummy text of the printing and typesetting industry.");
        cy.get('#frm_submit_side_top').should("contain", "Update").click();
        cy.get("a[aria-label='Close']", { timeout: 5000 }).click({ force: true });    

        cy.log("Verify that table view in forms page is in excerpt mode");
        cy.get('#view-switch-excerpt').should("exist").click();

        cy.log("Verify existence of the select all checkbox");
        cy.get('#cb-select-all-1').should("exist");

        cy.log("Verify column names in the forms page");
        cy.get('#name > a').should("contain", "Form Title");
        cy.get('#entries').should("contain", "Entries");
        cy.get('#id > a > :nth-child(1)').should("contain", "ID");
        cy.get('#form_key > a > :nth-child(1)').should("contain", "Key");
        cy.get('#shortcode').should("contain", "Actions");
        cy.get('#created_at > a > :nth-child(1)').should("contain", "Date");

        cy.log("Verify existence of a single row select checkbox");
        cy.get('[id^="cb-item-action-"]').should("exist");

        cy.log("Verify excerpt view data of the created form");
        cy.get('.id').should("exist");

        cy.get(`[id^="item-action-"] > .name > strong > .row-title:contains("${formTitle}")`)
            .parents('[id^="item-action-"]')
            .within(() => {
                cy.get('.name > strong > .row-title').should("contain", formTitle);
                cy.get('.name').should("contain", "Lorem Ipsum is simply dummy text of the printing...");
                cy.get('.entries > a').should("contain", "0");
                cy.get('.form_key').should("contain", "test-form");
                cy.get('.shortcode > div').should("exist");

                cy.getCurrentFormattedDate().then((formattedDate) => {
                    cy.log('Formatted Date: ', formattedDate);

                    cy.log("Find the element that displays the date and get its title content");
                    cy.get('.created_at > abbr')
                        .invoke('attr', 'title')
                        .then((dateTime) => {
                            const datePart = dateTime.split(' ')[0];
                            expect(datePart).to.equal(formattedDate);
                    

                    cy.log("Check that time exists in the <br> element");
                    /* eslint-disable no-unused-expressions */
                    cy.get('.created_at > abbr')
                        .invoke('html')
                        .then((html) => {
                            expect(html.split('<br>')[1]).to.exist.and.not.be.empty;
                    /* eslint-enable no-unused-expressions */
                        });
                    });
                });
            });
    });

    afterEach(() => {
        cy.deleteForm();
    });
});
