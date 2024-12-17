describe("Form Templates page", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable-form-templates');
        cy.viewport(1280, 720);
    });

    it("should validate page content", () => {

        cy.get('h1').should("contain", "Form Templates");
        cy.get('#template-search-input').should("exist");

        cy.log("Validate template categories");
        cy.get('li[data-category="favorites"]').within(() => {
            cy.get('.frm-page-skeleton-cat-text').should("have.text", "Favorites");
            cy.get('.frm-page-skeleton-cat-count').should("have.text", "0");
        });

        cy.get('li[data-category="custom"]').within(() => {
            cy.get('.frm-page-skeleton-cat-text').should("have.text", "Custom");
            cy.get('.frm-page-skeleton-cat-count').should("have.text", "0");
        });

        cy.get('li[data-category="available-templates"]').within(() => {
            cy.get('.frm-page-skeleton-cat-text').should("have.text", "Available Templates");
            cy.get('.frm-page-skeleton-cat-count').should("have.text", "0");
        });

        cy.get('li[data-category="all-items"]').within(() => {
            cy.get('.frm-page-skeleton-cat-text').should("have.text", "All Templates");
        });

        cy.get('li[data-category="free-templates"]').within(() => {
            cy.get('.frm-page-skeleton-cat-text').should("have.text", "Free Templates");
        });

        cy.log("Check the items on the All Templates page");
        cy.log("Contact Us Template");
        cy.get('[frm-search-text="contact us"] .frm-form-templates-item-title-text')
            .should('contain', 'Contact Us');
        cy.get('[frm-search-text="contact us"] .frm-form-templates-item-description')
            .should('contain.text', 'A basic contact form that for any WordPress website.');
        cy.get('[frm-search-text="contact us"] span.frm-category-icon svg use')
            .should('have.attr', 'xlink:href', '#frm_align_right_icon');

        cy.log("Stripe Payment Template");
        cy.get('[frm-search-text="stripe payment"] .frm-form-templates-item-title-text')
            .should('contain', 'Stripe Payment');
        cy.get('[frm-search-text="stripe payment"] .frm-form-templates-item-description')
            .should('contain.text', 'Effortlessly gather payment information from customers using our secure Stripe payment form. Simplify the payment process and ensure a seamless transaction experience.');
        cy.get('[frm-search-text="stripe payment"] span.frm-category-icon svg use')
            .should('have.attr', 'xlink:href', '#frm_credit_card_icon');

        cy.log("User Registration Template");
        cy.get('[frm-search-text="user registration"] .frm-form-templates-item-title-text')
            .should('contain', 'User Registration');
        cy.get('[frm-search-text="user registration"] .frm-form-templates-item-description')
            .should('contain.text', 'Let users register on the front-end of your site and set their username, email, password, name, and avatar.');
        cy.get('[frm-search-text="user registration"] span.frm-category-icon svg use')
            .should('have.attr', 'xlink:href', '#frm_register_icon');

        cy.log("Create WordPress Post Template");
        cy.get('[frm-search-text="create wordpress post"] .frm-form-templates-item-title-text')
            .should('contain', 'Create WordPress Post');
        cy.get('[frm-search-text="create wordpress post"] .frm-form-templates-item-description')
            .should('contain.text', 'Allow users to create WordPress posts from the front-end of your site with the Create WordPress Post form template.');
        cy.get('[frm-search-text="create wordpress post"] span.frm-category-icon svg use')
            .should('have.attr', 'xlink:href', '#frm_wordpress_icon');

        cy.log("Survey Template");
        cy.get('[frm-search-text="survey"] .frm-form-templates-item-title-text')
            .should('contain', 'Survey');
        cy.get('[frm-search-text="survey"] .frm-form-templates-item-description')
            .should('contain.text', 'Collect feedback from your customers, employees, or other members of your community using an online survey form.');
        cy.get('[frm-search-text="survey"] span.frm-category-icon svg use')
            .should('have.attr', 'xlink:href', '#frm_smile_icon');

        cy.log("Quiz Template");
        cy.get('[frm-search-text="quiz"] .frm-form-templates-item-title-text')
            .should('contain', 'Quiz');
        cy.get('[frm-search-text="quiz"] .frm-form-templates-item-description')
            .should('contain.text', 'This multiple-choice quiz template is a great example of basic quiz scoring.');
        cy.get('[frm-search-text="quiz"] span.frm-category-icon svg use')
            .should('have.attr', 'xlink:href', '#frm_percent_icon');

        cy.log("Car payment calculator Template");
        cy.get('[frm-search-text="car payment calculator"] .frm-form-templates-item-title-text')
            .should('contain', 'Car Payment Calculator');
        cy.get('[frm-search-text="car payment calculator"] .frm-form-templates-item-description')
            .should('contain.text', 'Calculate monthly payments with an easy auto loan calculator.');

        cy.log("Edit User Profile Template");
        cy.get('[frm-search-text="edit user profile"] .frm-form-templates-item-title-text')
            .should('contain', 'Edit User Profile');
        cy.get('[frm-search-text="edit user profile"] .frm-form-templates-item-description')
            .should('contain.text', 'The basics from the regular WordPress profile page including first and last name, password and email, avatar, website, and bio.');

        cy.log("Edit User Profile Template");
        cy.get('[frm-search-text="edit user profile"] .frm-form-templates-item-title-text')
            .should('contain', 'Edit User Profile');
        cy.get('[frm-search-text="edit user profile"] .frm-form-templates-item-description')
            .should('contain.text', 'The basics from the regular WordPress profile page including first and last name, password and email, avatar, website, and bio.');

        cy.log("Credit card payment Template");
        cy.get('[frm-search-text="credit card payment"] .frm-form-templates-item-title-text')
            .should('contain', 'Credit Card Payment');
        cy.get('[frm-search-text="credit card payment"] .frm-form-templates-item-description')
            .should('contain.text', 'Use with either the Stripe or Authorize.net add-ons to securely run payments while keeping users on your site.');

        cy.log("User Information Template");
        cy.get('[frm-search-text="user information"] .frm-form-templates-item-title-text')
            .should('contain', 'User Information');
        cy.get('[frm-search-text="user information"] .frm-form-templates-item-description')
            .should('contain.text', 'Get a WordPress contact form with more user information including website and address.');

        cy.log("Travel Booking Template");
        cy.get('[frm-search-text="travel booking"] .frm-form-templates-item-title-text')
            .should('contain', 'Travel Booking');
        cy.get('[frm-search-text="travel booking"] .frm-form-templates-item-description')
            .should('contain.text', 'Setting up a travel website? Plan-ahead ride service? Allow your users to easily reserve travel services.');

        cy.log("Job Application Template");
        cy.get('[frm-search-text="job application"] .frm-form-templates-item-title-text')
            .should('contain', 'Job Application');
        cy.get('[frm-search-text="job application"] .frm-form-templates-item-description')
            .should('contain.text', 'Streamline your hiring process by collecting employment applications online and get more applications.');

        cy.log("Support Ticket Template");
        cy.get('[frm-search-text="support ticket"] .frm-form-templates-item-title-text')
            .should('contain', 'Support Ticket');
        cy.get('[frm-search-text="support ticket"] .frm-form-templates-item-description')
            .should('contain.text', 'Looking for a support ticket form template? This form helps you manage customer support requests with ease! Users submit requests & create tickets all at once.');

        cy.log("Sponsor Donations Template");
        cy.get('[frm-search-text="sponsor donation"] .frm-form-templates-item-title-text')
            .should('contain', 'Sponsor Donation');
        cy.get('[frm-search-text="sponsor donation"] .frm-form-templates-item-description')
            .should('contain.text', 'Use this online form to handle your event sponsorship and donation applications.');

        cy.log("FAQ Template");
        cy.get('[frm-search-text="faq"] .frm-form-templates-item-title-text')
            .should('contain', 'FAQ');
        cy.get('[frm-search-text="faq"] .frm-form-templates-item-description')
            .should('contain.text', 'Find information quickly and easily provide answers to the most frequently asked questions.');

        cy.log("Entry Template");
        cy.get('[frm-search-text="entry"] .frm-form-templates-item-title-text')
            .should('contain', 'Entry');
        cy.get('[frm-search-text="entry"] .frm-form-templates-item-description')
            .should('contain.text', 'Collect entries for contests, competitions, or events, and let users upload their entry.');

        cy.log("Poll Template");
        cy.get('[frm-search-text="poll"] .frm-form-templates-item-title-text')
            .should('contain', 'Poll');
        cy.get('[frm-search-text="poll"] .frm-form-templates-item-description')
            .should('contain.text', 'A simple way to take a poll of likes, dislikes, or favorites.');

        cy.log("Grade Book Template");
        cy.get('[frm-search-text="grade book"] .frm-form-templates-item-title-text')
            .should('contain', 'Grade Book');
        cy.get('[frm-search-text="grade book"] .frm-form-templates-item-description')
            .should('contain.text', 'Keep track of grades all in one place.');

        cy.log("Release Template");
        cy.get('[frm-search-text="release"] .frm-form-templates-item-title-text')
            .should('contain', 'Release');
        cy.get('[frm-search-text="release"] .frm-form-templates-item-description')
            .should('contain.text', 'A simple standard release form template.');

    });

    it("search for valid and invalid templates", () => {

        cy.log("Search for valid templates");
        cy.get('#template-search-input').type("Contact Us");
        cy.get('#frm-form-templates-page-title-text').should("contain", "Search Result");
        cy.get('#frm-form-templates-list > .frm-form-templates-featured-item.frm-search-result').should("contain", "Contact Us");
        cy.get('#template-search-input').clear().type("Esthetician Consent");
        cy.get('.frm-search-result').should("contain", "Esthetician Consent");
        cy.get('#template-search-input').clear().type("Payment");
        cy.get('[data-id="20874733"] > .frm-form-templates-item-body > .frm-form-templates-item-title > .frm-form-templates-item-title-text > .frm-form-template-name').should("contain", "Payment");
        cy.get('[data-id="20874739"] > .frm-form-templates-item-body > .frm-form-templates-item-title > .frm-form-templates-item-title-text > .frm-form-template-name').should("contain", "Payment");

        cy.log("Search for non-valid templates");
        cy.get('#template-search-input').clear().type("Non Valid Template");
        cy.get('.frmcenter > .frm-page-skeleton-title').should("contain", "No templates found");
        cy.get('.frm-page-skeleton-text').should("contain", "Sorry, we didn't find any templates that match your criteria.");
        cy.get('#frm-page-skeleton-empty-state > .button').should("contain", "Start from Scratch").click();
        cy.get('#frm-form-templates-page-title-text').should("contain", "All Templates");

        cy.log("Search for application templates");
        cy.get('#template-search-input').clear().type("Business");
        cy.get('[frm-search-text="small business loan application"]').should('exist')
            .within(() => {
                cy.get('span.frm-form-template-name')
                    .should('contain.text', 'Small Business Loan Application');
                cy.get('p.frm-form-templates-item-description')
                    .should('contain.text', 'A complete loan application for small businesses and startups. Allow business owners to easily apply for loans on your site.');
            });

        cy.get('[frm-search-text="business inquiry"]').should('exist')
            .within(() => {
                cy.get('span.frm-form-template-name')
                    .should('contain.text', 'Business Inquiry');
                cy.get('p.frm-form-templates-item-description')
                    .should('contain.text', 'Obtain contact information from potential clients interested in your business.');
            });

        cy.get('#frm-form-templates-applications > .frm-mb-sm').should("contain", "Application Templates");
        cy.get('li[data-frm-search-text="business directory"]').should('exist')
            .within(() => {
                cy.get('div.frm-form-templates-item-icon img')
                    .should('have.attr', 'src')
                    .and('include', 'business-directory.png');

                cy.get('span.frm-meta-tag.frm-orange-tag')
                    .should('have.text', 'Ready Made Solution');

                cy.get('h3.frm-text-sm.frm-font-medium')
                    .should('have.text', 'Business Directory');

                cy.get('a.frm-text-xs.frm-font-semibold')
                    .should('have.text', 'See all applications')
                    .and('have.attr', 'href')
                    .and('include', '/wp-admin/admin.php?page=formidable-applications');
            });

        cy.get('li[data-frm-search-text="business directory"]').within(() => {
            cy.get('a.frm-text-xs.frm-font-semibold')
                .should('have.text', 'See all applications')
                .click();
        });

        cy.url().should('include', 'page=formidable-applications');
        cy.go('back');

        cy.get('#template-search-input').clear().type("Business");
        cy.get('[frm-search-text="business inquiry"]').should('exist')
            .within(() => {
                cy.get('span.frm-form-template-name')
                    .should('contain.text', 'Business Inquiry');
                cy.get('p.frm-form-templates-item-description')
                    .should('contain.text', 'Obtain contact information from potential clients interested in your business.');
            });

        cy.get('#frm-form-templates-applications > .frm-mb-sm').should("contain", "Application Templates");
        cy.get('li[data-frm-search-text="business hours"]').should('exist')
            .within(() => {
                cy.get('div.frm-form-templates-item-icon img')
                    .should('have.attr', 'src')
                    .and('include', 'business-hours.png');

                cy.get('span.frm-meta-tag.frm-orange-tag')
                    .should('have.text', 'Ready Made Solution');

                cy.get('h3.frm-text-sm.frm-font-medium')
                    .should('have.text', 'Business Hours');

                cy.get('a.frm-text-xs.frm-font-semibold')
                    .should('have.text', 'See all applications')
                    .and('have.attr', 'href')
                    .and('include', '/wp-admin/admin.php?page=formidable-applications');
            });

        cy.get('li[data-frm-search-text="business hours"]').within(() => {
            cy.get('a.frm-text-xs.frm-font-semibold')
                .should('have.text', 'See all applications')
                .click();
        });

        cy.url().should('include', 'page=formidable-applications');

    });

    it("add templates as favorites, view demo and use templates", () => {

        cy.log("Add contact us template as favorite");
        cy.get('li[frm-search-text="contact us"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-form-templates-item-favorite-button')
            .click({ force: true });

        cy.get('[data-category="favorites"] > .frm-page-skeleton-cat-count').should("contain.text", '1');
        cy.get('[data-category="favorites"]').click();
        cy.get('#frm-form-templates-list > .frm-form-templates-favorite-item').should("contain", "Contact Us");

        cy.log("Remove contact us template from favorites");
        cy.get('#frm-form-templates-list > .frm-form-templates-favorite-item > .frm-form-templates-item-body > .frm-form-templates-item-title > .frm-flex-box > .frm-form-templates-item-favorite-button > .frmsvg > use').click();
        cy.get('.frmcenter > .frm-page-skeleton-title').should("contain", "No favorites");

        cy.get('[data-category="all-items"]').should("contain", "All Templates").click();

        cy.log("View demo of the contact us template");
        cy.get('li[frm-search-text="contact us"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-button-secondary')
            .invoke('removeAttr', 'target')
            .should("contain", "View Demo")
            .click({ force: true });

        cy.origin('https://formidableforms.com', () => {
            cy.get('h1.margin30').should('have.text', 'Contact Us Form Template');
            cy.get('h2.aligncenter').should('contain', "What's in the Contact Us Form Template Demo");
        });

        cy.visit('/wp-admin/admin.php?page=formidable-form-templates');

        cy.get('li[frm-search-text="contact us"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-form-templates-use-template-button')
            .should("contain", "Use Template");

        cy.log("Try to use free templates");
        cy.get('[data-category="free-templates"]').should("contain", "Free Templates").click();
        cy.get('#frm-form-templates-page-title-text').should("contain", "Free Templates");
        cy.get('li[frm-search-text="contact us"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-form-templates-use-template-button')
            .should('contain', 'Use Template')
            .click({ force: true });

        cy.get('#frm-leave-email-modal').should('be.visible');
        cy.get('#frm-leave-email-modal > .frm_modal_top > .frm-modal-title > h2').should('contain', 'Get 20+ Free Form Templates');
        cy.get('#frm-leave-email-modal p')
            .should('contain.text', "Just add your email address and we'll send you a code for free form templates!");

        cy.get('a#frm-get-code-button').should('contain.text', 'Get Code');
        cy.get('a.frm-modal-close').should('contain.text', 'Close');

        cy.get('#frm-leave-email-modal > .frm_modal_footer > .button-secondary').click();

        cy.get('li[frm-search-text="contact us"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-form-templates-use-template-button')
            .should('contain', 'Use Template')
            .click({ force: true });
        cy.get('a#frm-get-code-button').click();
        cy.get('#frm-code-from-email-modal .frm-modal-title h2')
            .should('contain.text', 'Check Your Inbox');

        cy.get('#frm-code-from-email-modal p')
            .should('contain.text', 'Enter the code that we sent to your email address.');

        cy.get('a#frm-code-modal-back-button')
            .should('contain.text', 'Back')
            .and('have.attr', 'role', 'button').click({force:true});

        cy.get('a#frm-get-code-button').click({force:true});

        cy.get('a#frm-confirm-email-address')
            .should('contain.text', 'Save Code')
            .and('have.attr', 'role', 'button')
            .and('have.class', 'button-primary')
            .click({force:true});
        cy.get('#frm_code_from_email_error > [frm-error="empty"]').should("contain", "Verification code is empty");

        cy.get('input#frm_code_from_email')
            .should('have.attr', 'type', 'text')
            .should('have.attr', 'placeholder', 'Code from email')
            .type("Invalid code");
        cy.get('a#frm-confirm-email-address')
            .should('contain.text', 'Save Code').click();
        cy.get('#frm_code_from_email_error > [frm-error="custom"]').should('contain.text', "Sorry, that's not the right code.");
        cy.get('#frm-change-email-address').should('contain.text', "Change email address").click();
        cy.get('a#frm-get-code-button').click();
        cy.get('#frm-resend-code').should('contain.text', "Resend code");

        cy.get('a#frm-code-modal-back-button')
            .should('contain.text', 'Back')
            .and('have.attr', 'role', 'button').click({force:true});
        cy.get('#frm-leave-email-modal > .frm_modal_footer > .button-secondary').click();

        cy.get('[data-category="all-items"]').click();

        cy.log("Try to use templates which require upgrade");
        cy.get('[frm-search-text="user registration"]')
            .first()
            .trigger('mouseover')
            .find('.frm-form-templates-use-template-button')
            .should("contain", "Use Template")
            .click({force: true});

        cy.get('#frm-form-upgrade-modal > .frm_modal_top > .frm-modal-title > h2').should("contain", "User Registration is a PRO Template");
        cy.get('#frm-form-upgrade-modal > .inside > :nth-child(1)').should("contain", "The User Registration is not available on your plan. Please upgrade to unlock this and more awesome templates.");
        cy.get('#frm-form-upgrade-modal > .frm_modal_footer > .button-secondary').should("contain", "Close");
        cy.get('#frm-upgrade-modal-link').should("contain", "Upgrade to PRO").invoke('removeAttr', 'target').click();

        cy.origin('https://formidableforms.com', () => {
            cy.url().should('include', 'https://formidableforms.com');
        });

    });

    it("create a new custom template and delete it", () => {

        cy.get('#frm-form-templates-create-form').should("contain", "Create a blank form").click({force: true});
        cy.get('#frm_submit_side_top').should("contain", "Save").click({force: true});

        cy.log("Ensure the modal for for saving form is visible");
        cy.get('#frm-form-templates-modal').should('be.visible');
        cy.get('a.frm-modal-close.dismiss')
            .should('have.attr', 'title', 'Close')
            .should('be.visible');
        cy.get('.frm-modal-title > h2')
            .should('contain', 'Name your form');
        cy.get('#frm-name-your-form-modal p')
            .should('contain', 'Before we save this form, do you want to name it first?');
        cy.get('label[for="frm_new_form_name_input"]')
            .should('contain', 'Form Name (Optional)');
        cy.get('input#frm_new_form_name_input')
            .should('have.attr', 'placeholder', 'Enter your form name');
        cy.get('a#frm-cancel-rename-form-button')
            .should('contain.text', 'Cancel');

        cy.get('#frm_new_form_name_input').type("Form Template Test");
        cy.get('#frm-save-form-name-button').should("contain", "Save").click();
        cy.get('a[aria-label="Close"] svg').click();

        cy.get('#toplevel_page_formidable > .wp-submenu > :nth-child(8) > a').should("contain", "Form Templates").click();
        cy.get('[data-category="custom"]').click();

        cy.log("Validate that there are no custom templates yet");
        cy.get('.frmcenter > .frm-page-skeleton-title').should("contain", "You currently have no templates.");
        cy.get('.frm-page-skeleton-text').should("contain", "You haven't created any form templates. Begin now to simplify your workflow and save time.");
        cy.get('#frm-page-skeleton-empty-state > .button').should("contain", "Create Template").click();
        cy.get('#frm-create-template-modal > .frm_modal_footer > .button-secondary').should("contain", "Cancel").click();

        cy.log("Create a new template");
        cy.get('#frm-page-skeleton-empty-state > .button').should("contain", "Create Template").click({force: true});
        cy.get('#frm-create-template-modal > .frm_modal_top > .frm-modal-title > h2').should("contain", "Create New Template");
        cy.get('.inside > :nth-child(1) > label').should("contain", "Select form for a new template");
        cy.get('#frm-create-template-modal-forms-select').select("Form Template Test");
        cy.get(':nth-child(3) > label').should("contain", "Description");
        cy.get('#frm_create_template_description').type("Test description");
        cy.get('#frm-create-template-button').should("contain", "Create Template").click({force: true});
        cy.get('a[aria-label="Close"] svg').click();
        cy.get('.row-title').should("contain", "Form Template Test Template");

        cy.get('#toplevel_page_formidable > .wp-submenu > :nth-child(8) > a').should("contain", "Form Templates").click();
        cy.get('[data-category="custom"]').click();

        cy.log("Validate creation of the form template");
        cy.get('#frm-form-templates-custom-list > .frm-card-item > .frm-form-templates-item-body > .frm-form-templates-item-title > .frm-form-templates-item-title-text > .frm-form-template-name').should("contain", "Form Template Test Template");
        cy.get('#frm-form-templates-custom-list > .frm-card-item > .frm-form-templates-item-body > .frm-form-templates-item-content > .frm-form-templates-item-description').should("contain", "Test description");

        cy.log("Edit template");
        cy.get('li[frm-search-text="form template test template"]')
            .trigger('mouseover')
            .find('.frm-button-secondary')
            .should("contain", "Edit")
            .click({ force: true });

        cy.get('a[aria-label="Close"] svg').click();

        cy.get('#toplevel_page_formidable > .wp-submenu > :nth-child(8) > a').should("contain", "Form Templates").click();
        cy.get('[data-category="custom"]').click();

        cy.log("Click on the use template button");
        cy.get('li[frm-search-text="form template test template"]')
            .trigger('mouseover')
            .find('.frm-button-primary')
            .should("contain", "Use Template")
            .click({ force: true });

        cy.get('a[aria-label="Close"] svg').click();

        cy.get('#toplevel_page_formidable > .wp-submenu > :nth-child(8) > a').should("contain", "Form Templates").click();
        cy.get('[data-category="custom"]').click();

        cy.log("Delete template");
        cy.get('li[frm-search-text="form template test template"]')
            .trigger('mouseover')
            .find('.frm-form-templates-custom-item-trash-button')
            .click({ force: true });
        cy.get('.cta-inside > .frm-flex-box > .button-secondary').should("contain", "Cancel").click();
        cy.get('li[frm-search-text="form template test template"]')
            .trigger('mouseover')
            .find('.frm-form-templates-custom-item-trash-button')
            .click({ force: true });
        cy.get('.frm-confirm-msg').should("contain", "Do you want to move this form template to the trash?");
        cy.get('#frm-confirmed-click').should("contain", "Confirm").click();

        cy.log("Delete forms");
        cy.get('#cb-select-all-1').click();
        cy.get('#bulk-action-selector-top').select('Move to Trash');
        cy.get('#doaction').should("contain", "Apply").click();
    });
});
