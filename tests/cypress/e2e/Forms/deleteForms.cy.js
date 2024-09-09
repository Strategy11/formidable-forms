describe("Deleting forms", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.viewport(1280, 720);
    });

    it("should create multiple forms and bulk delete them", () => {  
        
        cy.emptyTrash();

        cy.log("Create 5 new forms");
        for (let i = 0; i < 5; i++) {
            cy.createNewForm();
        }

        cy.log("Bulk delete all 5 new forms");
        cy.get('tr').filter((index, element) => {
            return Cypress.$(element).text().includes('Test Form');
        }).each(($row) => {
            cy.wrap($row).find('.check-column input[type="checkbox"]').check();
        });

        cy.log("Click on Bulk Actions and select the Move to Trash option")
        cy.get('#bulk-action-selector-top').select("Move to Trash");
        cy.get('#doaction').should("contain", "Apply").click();
        cy.get('.frm_updated_message').should("contain", "forms moved to the Trash.");

        cy.log("Restore deleted forms and the redelete them");
        cy.get('.subsubsub > .trash > a').should("contain", "Trash").click();
        cy.get('#cb-select-all-1').click();
        cy.get('#bulk-action-selector-top').should("contain", "Bulk Actions").select('bulk_untrash');
        cy.get('#doaction').should("contain", "Apply").click();
        cy.get('.trash > a').should('contain.text', 'Trash')
            .find('.count').should('contain.text', '(0)');
        
        cy.get('.colspanchange > p').should("contain", "No forms found in the trash.");
        cy.get('.colspanchange > p > a').should("contain", "See all forms").click();
        cy.log("Bulk delete permanently deleted forms");
        cy.get('#bulk-action-selector-top').select("Move to Trash");
        cy.get('#doaction').should("contain", "Apply").click();
        cy.get('.trash > a').should('contain.text', 'Trash');
        cy.get('#cb-select-all-1').click();
        cy.get('#bulk-action-selector-top').should("contain", "Bulk Actions").select('bulk_trash');
        cy.get('#doaction').should("contain", "Apply").click();

        cy.log("Permanently delete 2 forms using the Delete Permanetly option")
        cy.get('.subsubsub > .trash > a').should("contain", "Trash").click();
        cy.get('.trash > a').should('contain.text', 'Trash')
            .find('.count').should('contain.text', '(5)');
        cy.get('[id^="cb-item-action-"]').eq(0).click();
        cy.get('[id^="cb-item-action-"]').eq(1).click();
        cy.get('#bulk-action-selector-top').should("contain", "Bulk Actions").select('bulk_delete');
        cy.get('#doaction').should("contain", "Apply").click();
        cy.get('.frm-confirm-msg').should("contain", "ALL selected forms and their entries will be permanently deleted. Want to proceed?")
        cy.get('.button-secondary').should("contain", "Cancel").click();
        cy.get('#doaction').should("contain", "Apply").click();
        cy.get('#frm-confirmed-click').should("contain", "Confirm").click();
        cy.get('.trash > a').should('contain.text', 'Trash')
            .find('.count').should('contain.text', '(3)');
        cy.get('.published > a').should('contain.text', 'My Forms')
            .find('.count').should('contain.text', '(0)');

        cy.log("Empty trash");
        cy.get('#delete_all').should("contain", "Empty Trash").click();
        cy.get('.trash > a').should('contain.text', 'Trash')
            .find('.count').should('contain.text', '(0)');
        cy.get('.published > a').should('contain.text', 'My Forms')
            .find('.count').should('contain.text', '(0)');
        cy.get('.colspanchange > p').should("contain", "No forms found in the trash.");
    });
});
