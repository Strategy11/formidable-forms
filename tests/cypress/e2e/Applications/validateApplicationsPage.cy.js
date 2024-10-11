describe("Applications page", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable-applications');
        cy.viewport(1280, 720);
    });

    it("should validate application templates", () => {

        cy.get('#frm_top_bar').should('contain', "Applications");
        cy.get('#frm-publishing > .button').should("contain", "Upgrade");
        cy.get('.frm-h2 > span').should('contain', 'My Applications');
        cy.get('#frm_custom_applications_placeholder > :nth-child(1) > img').should('exist');
        cy.get(':nth-child(2) > h3').should('contain', 'Improve your workflow with applications');
        cy.get('#frm_custom_applications_placeholder > :nth-child(2) > div').should('contain', 'Applications help to organize your workspace by combining forms, Views, and pages into a full solution.');
        cy.get('#frm_custom_applications_placeholder > :nth-child(2) > .button').should('contain', 'Upgrade to Pro').invoke('removeAttr', 'target').click();

        cy.origin('https://formidableforms.com', () => {
            cy.get('h1').should(($h1) => {
                const text = $h1.text();
                expect(text).to.satisfy((t) =>
                    t.includes('The Only WordPress Form Maker & Application Builder Plugin') ||
                    t.includes('Upgrade Today to Unlock the Full Power of Formidable Forms')
                );
            });
        });

        cy.visit('/wp-admin/admin.php?page=formidable-applications');

        cy.log("Applications Template validations");
        cy.get(':nth-child(3) > .frm-h2').should('contain', "Application Templates");
        cy.get('#frm_application_category_filter > .current').should('contain', "All Items");

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Business Directory')
        .within(() => {
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Business Directory Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/business-directory.png');
            cy.get('h3 .frm-inner-text').should('contain.text', 'Business Directory').click();
        });

        cy.get('#frm_view_application_modal')
            .should('be.visible')
            .within(() => {
                cy.get('.frm-modal-title').should('contain.text', 'Business Directory');
                cy.get('.frm_warning_style span')
                    .should('contain.text', 'Access to this application requires the Elite plan.');
                cy.get('.frm-application-image-wrapper img')
                    .should('have.attr', 'src')
                    .and('include', '/plugins/formidable-forms/images/applications/placeholder.png');
                cy.get('.frm-application-modal-details .frm-application-modal-label')
                    .should('contain.text', 'Description');
                cy.get('.frm-application-modal-details div')
                    .should('contain.text', 'Collect paid business listings, accept user reviews, and let visitors contact a business.');
                cy.get('.frm_modal_footer a.button-secondary')
                    .should('contain.text', 'Learn More')
                    .and('have.attr', 'href')
                    .and('include', 'https://formidableforms.com/view-templates/business-directory-template?utm_source=WordPress&utm_medium=applications&utm_campaign=liteplugin&utm_content=upgrade');
                cy.get('.frm_modal_footer a.button-primary')
                    .should('contain.text', 'Upgrade Now')
                    .invoke('removeAttr', 'target')
                    .click();
            });
        cy.origin('https://formidableforms.com', () => {
            cy.get('h1').should('have.text', 'Business Directory Template');
            cy.get('p.margin30').within(() => {
                cy.contains('This application template is included with the').should('be.visible');
                cy.contains('Elite plan.').should('be.visible');
            });
        });

        cy.visit('/wp-admin/admin.php?page=formidable-applications');

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Business Hours')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Business Hours');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Business Hours Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/business-hours.png');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Certificate')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Certificate');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Certificate Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/certificate.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Charity Tracker')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Charity Tracker');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Charity Tracker Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/charity-tracker.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Contract Agreement')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Contract Agreement');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Contract Agreement Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/contract-agreement.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'FAQ')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'FAQ');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'FAQ Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/faq-template-wordpress.png');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Freelance Invoice Generator')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Freelance Invoice Generator');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Freelance Invoice Generator Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/freelance-invoice-generator.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Invoice PDF')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Invoice PDF');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Invoice PDF Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/invoice-pdf.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Letter of Recommendation')
        .within(() => {
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Letter of Recommendation Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/letter-of-recommendation.webp');
            cy.get('h3 .frm-inner-text').should('contain.text', 'Letter of Recommendation').click();
        });

        cy.get('#frm_view_application_modal')
            .should('be.visible')
            .within(() => {
                cy.get('.frm-modal-title').should('contain.text', 'Letter of Recommendation');
                cy.get('.frm_warning_style span')
                    .should('contain.text', 'Access to this application requires the Business plan');
                cy.get('.frm-application-image-wrapper img')
                    .should('have.attr', 'src')
                    .and('include', '/plugins/formidable-forms/images/applications/placeholder.png');
                cy.get('.frm-application-modal-details .frm-application-modal-label')
                    .should('contain.text', 'Description');
                cy.get('.frm-application-modal-details div')
                    .should('contain.text', "Find your dream job with a professional letter of recommendation. Often written by the job applicant's most recent supervisor, this letter highlights the applicant's abilities, traits, and qualities relevant to the job position for which they are applying.");
                cy.get('.frm_modal_footer a.button-secondary')
                    .should('contain.text', 'Learn More')
                    .and('have.attr', 'href')
                    .and('include', 'https://formidableforms.com/view-templates/letter-of-recommendation-template?utm_source=WordPress&utm_medium=applications&utm_campaign=liteplugin&utm_content=upgrade');
                cy.get('.frm_modal_footer a.button-primary')
                    .should('contain.text', 'Upgrade Now')
                    .invoke('removeAttr', 'target')
                    .click();
            });
        cy.origin('https://formidableforms.com', () => {
            cy.get('h1').should('have.text', 'Letter of Recommendation Template');
            cy.get('p.margin30').within(() => {
                cy.contains('This application template is included with the').should('be.visible');
                cy.contains('Business plan.').should('be.visible');
            });
        });

        cy.visit('/wp-admin/admin.php?page=formidable-applications');

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Link in Bio Instagram Page')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Link in Bio Instagram Page');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Link in Bio Instagram Page Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/link-in-bio-instagram.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Member Directory')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Member Directory');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Member Directory Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/member-directory.webp');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Product Review and Purchase')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Product Review and Purchase');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Product Review and Purchase Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/product-review.png');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Real Estate Listing')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Real Estate Listing');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Real Estate Listing Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/real-estate-listings.png');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Restaurant Menu')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Restaurant Menu');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Restaurant Menu Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/restaurant-menu.png');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Team Directory')
        .within(() => {
            cy.get('h3 .frm-inner-text').should('contain.text', 'Team Directory');
            cy.get('.button.frm-button-secondary.frm-button-sm')
                .should('contain.text', 'Learn More')
                .and('have.attr', 'aria-description', 'Team Directory Template');
            cy.get('.frm-application-card-image-wrapper img')
                .should('have.attr', 'src')
                .and('include', '/plugins/formidable-forms/images/applications/thumbnails/team-directory.png');
        });

        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card', 'Testimonials')
        .within(() => {
          cy.get('.button.frm-button-secondary.frm-button-sm')
            .should('contain.text', 'Learn More')
            .and('have.attr', 'aria-description', 'Testimonials Template');
          cy.get('.frm-application-card-image-wrapper img')
            .should('have.attr', 'src')
            .and('include', '/plugins/formidable-forms/images/applications/thumbnails/testimonials.webp');
          cy.get('h3 .frm-inner-text').should('contain.text', 'Testimonials').click();
        });
      

        cy.get('#frm_view_application_modal')
            .should('be.visible')
            .within(() => {
                cy.get('.frm-modal-title').should('contain.text', 'Testimonials');
                cy.get('.frm_warning_style span')
                    .should('contain.text', 'Access to this application requires the Business plan');
                cy.get('.frm-application-image-wrapper img')
                    .should('have.attr', 'src')
                    .and('include', '/plugins/formidable-forms/images/applications/placeholder.png');
                cy.get('.frm-application-modal-details .frm-application-modal-label')
                    .should('contain.text', 'Description');
                cy.get('.frm-application-modal-details div')
                    .should('contain.text', "Collect testimonials in the form, and choose between three layouts before publishing.");
                cy.get('.frm_modal_footer a.button-secondary')
                    .should('contain.text', 'Learn More')
                    .and('have.attr', 'href')
                    .and('include', 'https://formidableforms.com/view-templates/testimonials-template?utm_source=WordPress&utm_medium=applications&utm_campaign=liteplugin&utm_content=upgrade');
                cy.get('.frm_modal_footer a.button-primary')
                    .should('contain.text', 'Upgrade Now')
                    .invoke('removeAttr', 'target')
                    .click();
            });

        cy.origin('https://formidableforms.com', () => {
            cy.get('h1').should('have.text', 'Testimonials Template');
            cy.get('p.margin30').within(() => {
                cy.contains('This application template is included with the').should('be.visible');
                cy.contains('Business plan.').should('be.visible');
            });
        });
    });

    it("should search for application templates", () => {

        cy.log("Search for valid application templates");
        cy.get('#frm-application-search').type("Business Hours");
        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card.frm-search-result', 'Business Hours').should('exist');
        cy.get('#frm-application-search').clear().type("menu");
        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card.frm-search-result', 'Restaurant Menu').should('exist');
        cy.get('#frm-application-search').clear().type("business");
        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card.frm-search-result', 'Business Directory')
        .within(() => {
          cy.get('.button.frm-button-secondary.frm-button-sm')
            .should('contain.text', 'Learn More')
            .and('have.attr', 'aria-description', 'Business Directory Template');
          cy.get('.frm-application-card-image-wrapper img')
            .should('have.attr', 'src')
            .and('include', '/plugins/formidable-forms/images/applications/thumbnails/business-directory.png');
        });
        cy.get('#frm_application_templates_grid')
        .contains('.frm-application-template-card.frm-search-result', 'Business Hours')
        .within(() => {
          cy.get('.button.frm-button-secondary.frm-button-sm')
            .should('contain.text', 'Learn More')
            .and('have.attr', 'aria-description', 'Business Hours Template');
          cy.get('.frm-application-card-image-wrapper img')
            .should('have.attr', 'src')
            .and('include', '/plugins/formidable-forms/images/applications/thumbnails/business-hours.png');
        });
        cy.log("Search for non-valid application templates");
        cy.get('#frm-application-search').clear().type("Application does not exist");
        cy.get('#frm_application_templates_grid').should('contain', 'No application templates match your search query.');

    });
});