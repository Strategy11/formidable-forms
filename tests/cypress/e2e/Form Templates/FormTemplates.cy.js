describe("Form Templates page", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable-form-templates');
        cy.viewport(1280, 720);
    });

    it("should validate page content", () => {

        cy.get('h1').should("contain", "Form Templates");
        cy.get('#template-search-input').should("exist");

        cy.log("Validate data categories");

        const categories = [
            { category: 'all-templates', text: 'All Templates', count: '319' },
            { category: 'favorites', text: 'Favorites', count: '0' },
            { category: 'custom', text: 'Custom', count: '0' },
            { category: 'available-templates', text: 'Available Templates', count: '33' },
            { category: 'ai', text: 'AI', count: '9' },
            { category: 'application', text: 'Application', count: '12' },
            { category: 'business-operations', text: 'Business Operations', count: '91' },
            { category: 'calculator', text: 'Calculator', count: '57' },
            { category: 'consent', text: 'Consent', count: '2' },
            { category: 'contact', text: 'Contact', count: '14' },
            { category: 'conversational-forms', text: 'Conversational Forms', count: '18' },
            { category: 'customer-service', text: 'Customer Service', count: '18' },
            { category: 'datepicker', text: 'Datepicker', count: '1' },
            { category: 'education', text: 'Education', count: '23' },
            { category: 'entertainment', text: 'Entertainment', count: '1' },
            { category: 'event-planning', text: 'Event Planning', count: '23' },
            { category: 'feedback', text: 'Feedback', count: '21' },
            { category: 'finance', text: 'Finance', count: '15' },
            { category: 'geolocation', text: 'Geolocation', count: '5' },
            { category: 'health-and-wellness', text: 'Health and Wellness', count: '21' },
            { category: 'lead', text: 'Lead', count: '11' },
            { category: 'marketing', text: 'Marketing', count: '12' },
            { category: 'multi-page', text: 'Multi-Page', count: '3' },
            { category: 'nonprofit', text: 'Nonprofit', count: '22' },
            { category: 'order-form', text: 'Order Form', count: '16' },
            { category: 'payment', text: 'Payment', count: '26' },
            { category: 'post', text: 'Post', count: '3' },
            { category: 'quiz', text: 'Quiz', count: '18' },
            { category: 'real-estate', text: 'Real Estate', count: '9' },
            { category: 'registration-and-signup', text: 'Registration and Signup', count: '14' },
            { category: 'repeater-field', text: 'Repeater Field', count: '2' },
            { category: 'signature', text: 'Signature', count: '48' },
            { category: 'survey', text: 'Survey', count: '21' },
            { category: 'user-registration', text: 'User Registration', count: '3' },
            { category: 'woocommerce', text: 'WooCommerce', count: '2' }
        ];

        categories.forEach(({ category, text, count }) => {
            cy.get(`li[data-category="${category}"]`).within(() => {
                cy.get('.frm-form-templates-cat-text').should("have.text", text);

                cy.log("Since number of templates keeps changing assert that it's within ±5 of the expected count"); cy.get('.frm-form-templates-cat-count').invoke('text').then((countText) => {
                    const actualCount = parseInt(countText.trim(), 10);
                    const expectedCount = parseInt(count, 10);

                    if (!isNaN(actualCount) && !isNaN(expectedCount)) {
                        const minCount = expectedCount - 5;
                        const maxCount = expectedCount + 5;

                        expect(actualCount).to.be.within(minCount, maxCount);
                    } else {
                        throw new Error(`Invalid count: Expected (${count}) or actual count (${countText}) is not a number`);
                    }
                });
            });
        });

        cy.log("Check the items on the All Templates page");
        cy.log("Contact Us Template");
        cy.get('[frm-search-text="contact us"] .frm-form-templates-item-title-text')
            .should('contain', 'Contact Us');
        cy.get('[frm-search-text="contact us"] .frm-form-templates-item-description')
            .should('contain.text', 'A basic contact form that for any WordPress website.');

        cy.log("Stripe Payment Template");
        cy.get('[frm-search-text="stripe payment"] .frm-form-templates-item-title-text')
            .should('contain', 'Stripe Payment');
        cy.get('[frm-search-text="stripe payment"] .frm-form-templates-item-description')
            .should('contain.text', 'Effortlessly gather payment information from customers using our secure Stripe payment form. Simplify the payment process and ensure a seamless transaction experience.');

        cy.log("User Registration Template");
        cy.get('[frm-search-text="user registration"] .frm-form-templates-item-title-text')
            .should('contain', 'User Registration');
        cy.get('[frm-search-text="user registration"] .frm-form-templates-item-description')
            .should('contain.text', 'Let users register on the front-end of your site and set their username, email, password, name, and avatar.');

        cy.log("Create WordPress Post Template");
        cy.get('[frm-search-text="create wordpress post"] .frm-form-templates-item-title-text')
            .should('contain', 'Create WordPress Post');
        cy.get('[frm-search-text="create wordpress post"] .frm-form-templates-item-description')
            .should('contain.text', 'Allow users to create WordPress posts from the front-end of your site with the Create WordPress Post form template.');

        cy.log("Survey Template");
        cy.get('[frm-search-text="survey"] .frm-form-templates-item-title-text')
            .should('contain', 'Survey');
        cy.get('[frm-search-text="survey"] .frm-form-templates-item-description')
            .should('contain.text', 'Collect feedback from your customers, employees, or other members of your community using an online survey form.');

        cy.log("Quiz Template");
        cy.get('[frm-search-text="quiz"] .frm-form-templates-item-title-text')
            .should('contain', 'Quiz');
        cy.get('[frm-search-text="quiz"] .frm-form-templates-item-description')
            .should('contain.text', 'This multiple-choice quiz template is a great example of basic quiz scoring.');

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
        cy.get('.frmcenter > .frm-form-templates-title').should("contain", "No templates found");
        cy.get('.frm-form-templates-text').should("contain", "Sorry, we didn't find any templates that match your criteria.");
        cy.get('#frm-form-templates-empty-state > .button').should("contain", "Start from Scratch").click();
        cy.get('#frm-form-templates-page-title-text').should("contain", "All Templates");

    });

    it("add templates as favorites, view demo and use templates", () => {

        cy.log("Add contact us template as favorite");
        cy.get('li[frm-search-text="contact us"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-form-templates-item-favorite-button')
            .click({ force: true });

        cy.get('[data-category="favorites"] > .frm-form-templates-cat-count').should("contain.text", '1');
        cy.get('[data-category="favorites"]').click();
        cy.get('#frm-form-templates-list > .frm-form-templates-favorite-item').should("contain", "Contact Us");

        cy.log("Remove contact us template from favorites");
        cy.get('#frm-form-templates-list > .frm-form-templates-favorite-item > .frm-form-templates-item-body > .frm-form-templates-item-title > .frm-flex-box > .frm-form-templates-item-favorite-button > .frmsvg > use').click();
        cy.get('.frmcenter > .frm-form-templates-title').should("contain", "No favorites");

        cy.get('[data-category="all-templates"]').should("contain", "All Templates").click();

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

        cy.log("Try to use template of a template which requires upgrade")
        cy.get('li[frm-search-text="user registration"]').first()
            .trigger('mouseover', { force: true })
            .find('.frm-form-templates-use-template-button')
            .should("contain", "Use Template")
            .click({ force: true });

        cy.get('#frm-form-upgrade-modal > .frm_modal_top > .frm-modal-title > h2').should("contain", "User Registration is a PRO Template");
        cy.get('#frm-form-upgrade-modal > .inside > :nth-child(1)').should("contain", "The User Registration is not available on your plan. Please upgrade to unlock this and more awesome templates.");
        cy.get('#frm-form-upgrade-modal > .frm_modal_footer > .button-secondary').should("contain", "Close");
        cy.get('#frm-upgrade-modal-link').should("contain", "Upgrade to PRO").invoke('removeAttr', 'target').click();

        cy.origin('https://formidableforms.com', () => {
            cy.get('h1.wp-block-heading').should('have.text', 'Upgrade Today to Unlock the Full Power of Formidable Forms');
        });
    });

    it("create a new custom template and delete it", () => {

        cy.get('#frm-form-templates-create-form').should("contain", "Create a blank form").click();
        cy.get('#frm_submit_side_top').should("contain", "Save").click();
        cy.get('#frm_new_form_name_input').type("Form Template Test");
        cy.get('#frm-save-form-name-button').should("contain", "Save").click();
        cy.get('a[aria-label="Close"] svg').click();

        cy.get('#toplevel_page_formidable > .wp-submenu > :nth-child(8) > a').should("contain", "Form Templates").click();
        cy.get('[data-category="custom"]').click();

        cy.log("Validate that there are no custom templates yet");
        cy.get('.frmcenter > .frm-form-templates-title').should("contain", "You currently have no templates.");
        cy.get('.frm-form-templates-text').should("contain", "You haven't created any form templates. Begin now to simplify your workflow and save time.");
        cy.get('#frm-form-templates-empty-state > .button').should("contain", "Create Template").click();
        cy.get('#frm-create-template-modal > .frm_modal_footer > .button-secondary').should("contain", "Cancel").click();

        cy.log("Create a new template");
        cy.get('#frm-form-templates-empty-state > .button').should("contain", "Create Template").click();
        cy.get('#frm-create-template-modal > .frm_modal_top > .frm-modal-title > h2').should("contain", "Create New Template");
        cy.get('.inside > :nth-child(1) > label').should("contain", "Select form for a new template");
        cy.get('#frm-create-template-modal-forms-select').select("Form Template Test");
        cy.get(':nth-child(3) > label').should("contain", "Description");
        cy.get('#frm_create_template_description').type("Test description");
        cy.get('#frm-create-template-button').should("contain", "Create Template").click();
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