describe("Add-Ons page", () => {
    beforeEach(() => {
        cy.login();
        cy.visit('/wp-admin/admin.php?page=formidable-addons');
        cy.viewport(1280, 720);
    });

    it("should validate all add-on cards", () => {

        cy.get('#frm_top_bar').should("contain", "Formidable Add-Ons");
        cy.get('#frm-connect-btns > .button-primary').should("contain", "Connect an Account");
        cy.get('#frm-connect-btns > .button-secondary').should("contain", "Get Formidable Now");
        cy.get('#frm-publishing > .button').should("contain","Upgrade");
        cy.get('#addon-search-input').should("exist");

        cy.log("Formidable Forms Pro card");
        cy.get('.plugin-card-pro > .plugin-card-top').should("exist");
        cy.get('.plugin-card-pro > .plugin-card-top > h2').should("contain", "Formidable Forms Pro");
        cy.get('.plugin-card-pro > .plugin-card-top > p').should("contain", "Create calculators, surveys, smart forms, and data-driven applications. Build directories, real estate listings, job boards, and much more.")
        cy.get('.plugin-card-pro > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-pro > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Digital Signatures card");
        cy.contains('.frm-card', 'Digital Signatures')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-163248 > .plugin-card-top > :nth-child(2)').should("contain", "Add an electronic signature to your WordPress form. The visitor may write their signature with a trackpad/mouse or type it.")
        cy.get('.plugin-card-163248 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-163248 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("PayPal Standard card");
        cy.contains('.frm-card', 'PayPal Standard')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-163257 > .plugin-card-top > :nth-child(2)').should("contain", "Collect instant payments and recurring payments to automate your online business. Calculate a total and send customers on to PayPal.")
        cy.get('.plugin-card-163257 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-163257 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Formidable API card");
        cy.contains('.frm-card', 'Formidable API')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-168072 > .plugin-card-top > :nth-child(2)').should("contain", "Add a full forms API for forms, form fields, views, and entries. Then send submissions to other sites with REST APIs.")
        cy.get('.plugin-card-168072 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-168072 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Twilio WordPress SMS card");
        cy.contains('.frm-card', 'Twilio WordPress SMS')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-168460 > .plugin-card-top > :nth-child(2)').should("contain", "Allow users to text their votes for polls created by Formidable Forms, or send SMS notifications when entries are submitted or updated.")
        cy.get('.plugin-card-168460 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-168460 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Bootstrap card");
        cy.contains('.frm-card', 'Bootstrap')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-168463 > .plugin-card-top > :nth-child(2)').should("contain", "Instantly add Bootstrap styling to all your Formidable forms.")
        cy.get('.plugin-card-168463 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-168463 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("AWeber card");
        cy.contains('.frm-card', 'AWeber')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-168468 > .plugin-card-top > :nth-child(2)').should("contain", "AWeber is a powerful email marketing service. Subscribe contacts to an AWeber mailing list when they submit your WordPress contact forms.")
        cy.get('.plugin-card-168468 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-168468 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("WP Multilingual card");
        cy.contains('.frm-card', 'WP Multilingual')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-169998 > .plugin-card-top > :nth-child(2)').should("contain", "Translate your forms into multiple languages using the Formidable-integrated WPML plugin.")
        cy.get('.plugin-card-169998 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-169998 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Locations card");
        cy.contains('.frm-card', 'Locations')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-170641 > .plugin-card-top > :nth-child(2)').should("contain", "Populate fields with Countries, States/Provinces, U.S. Counties, and U.S. Cities. This data can then be used in dependent Data from Entries fields.")
        cy.get('.plugin-card-170641 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-170641 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Zapier card");
        cy.contains('.frm-card', 'Zapier')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-170645 > .plugin-card-top > :nth-child(2)').should("contain", "Connect with hundreds of applications through Zapier. Automatically insert a Google spreadsheet row, tweet, or upload to Dropbox.")
        cy.get('.plugin-card-170645 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-170645 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("User Flow card");
        cy.contains('.frm-card', 'User Flow')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-170649 > .plugin-card-top > :nth-child(2)').should("contain", "Track the pages a user visits and the time spent on each page prior to submitting a form.")
        cy.get('.plugin-card-170649 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-170649 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Mailchimp card");
        cy.contains('.frm-card', 'Mailchimp')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-170655 > .plugin-card-top > :nth-child(2)').should("contain", "Get on the path to more leads in minutes. Add and update leads in a Mailchimp mailing list when a form is submitted.")
        cy.get('.plugin-card-170655 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-170655 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("User Registration card");
        cy.contains('.frm-card', 'User Registration')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-173984 > .plugin-card-top > :nth-child(2)').should("contain", "Give new users access to your site quickly and painlessly. Plus edit profiles and login from the front end.")
        cy.get('.plugin-card-173984 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-173984 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("WooCommerce card");
        cy.contains('.frm-card', 'WooCommerce')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Elite')
            });
        cy.get('.plugin-card-174006 > .plugin-card-top > :nth-child(2)').should("contain", "Are your WooCommerce product forms too basic? Add custom fields to a product form and collect more data when it is added to the cart.")
        cy.get('.plugin-card-174006 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-174006 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Highrise card");
        cy.contains('.frm-card', 'Highrise')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-180495 > .plugin-card-top > :nth-child(2)').should("contain", "Capture leads in your WordPress contact forms, and save them in your Highrise CRM account too.")
        cy.get('.plugin-card-180495 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-180495 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Bootstrap Modal card");
        cy.contains('.frm-card', 'Bootstrap Modal')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-185013 > .plugin-card-top > :nth-child(2)').should("contain", "Open forms, views, other shortcodes, or sections of content in a Bootstrap popup.")
        cy.get('.plugin-card-185013 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-185013 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Polylang card");
        cy.contains('.frm-card', 'Polylang')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-209561 > .plugin-card-top > :nth-child(2)').should("contain", "Create bilingual or multilingual forms with help from Polylang.")
        cy.get('.plugin-card-209561 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-209561 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Stripe card");
        cy.contains('.frm-card', 'Stripe')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-310430 > .plugin-card-top > :nth-child(2)').should("contain", "Any Formidable forms on your site can accept credit card payments without users ever leaving your site.")
        cy.get('.plugin-card-310430 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-310430 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Form Action Automation card");
        cy.contains('.frm-card', 'Form Action Automation')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Elite')
            });
        cy.get('.plugin-card-326042 > .plugin-card-top > :nth-child(2)').should("contain", "Schedule email notifications, SMS messages, and API actions.")
        cy.get('.plugin-card-326042 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-326042 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Authorize.net AIM card");
        cy.contains('.frm-card', 'Authorize.net AIM')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Elite')
            });
        cy.get('.plugin-card-337527 > .plugin-card-top > :nth-child(2)').should("contain", "Accept one-time payments directly on your site, using Authorize.net AIM.")
        cy.get('.plugin-card-337527 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-337527 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Logs card");
        cy.contains('.frm-card', 'Logs')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Basic')
            });
        cy.get('.plugin-card-11927748 > .plugin-card-top > :nth-child(2)').should("contain", "See your API requests along with their responses from add-ons including Zapier, Formidable API Webhooks, Salesforce and more.")
        cy.get('.plugin-card-11927748 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-11927748 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Datepicker Options card");
        cy.contains('.frm-card', 'Datepicker Options')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-20247260 > .plugin-card-top > :nth-child(2)').should("contain", "Add more options to date fields in your forms for so only the dates you choose can be chosen.")
        cy.get('.plugin-card-20247260 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20247260 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Salesforce card");
        cy.get('.frm-card.plugin-card-20266559')
            .within(() => {
                cy.get('.plugin-card-top h2')
                    .should('contain', 'Salesforce');
                cy.get('.frm_plan_required')
                    .should('contain.text', 'License plan required')
                    .and('contain.text', 'Elite');
                cy.get('.plugin-card-bottom .addon-status')
                    .should('contain.text', 'Status: Not Installed');
                cy.get('.plugin-card-bottom .install-now')
                    .should('contain.text', 'Upgrade Now');
                cy.get('.plugin-card-top > :nth-child(2)')
                    .should("contain", "Add new contacts and leads into your Salesforce CRM directly from the WordPress forms on your site.")
            });

        cy.log("MailPoet Newsletters card");
        cy.contains('.frm-card', 'MailPoet Newsletters')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-20781560 > .plugin-card-top > :nth-child(2)').should("contain", "Send WordPress newsletters from your own site with MailPoet. And use Formidable to for your newsletter signup forms.")
        cy.get('.plugin-card-20781560 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20781560 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("ActiveCampaign card");
        cy.contains('.frm-card', 'ActiveCampaign')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Elite')
            });
        cy.get('.plugin-card-20790298 > .plugin-card-top > :nth-child(2)').should("contain", "Add contacts to any ActiveCampaign list from your WordPress forms.")
        cy.get('.plugin-card-20790298 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20790298 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("HubSpot card");
        cy.contains('.frm-card', 'HubSpot')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Elite')
            });
        cy.get('.plugin-card-20811871 > .plugin-card-top > :nth-child(2)').should("contain", "HubSpot is a complete CRM platform with tools for increased leads, accelerated sales, or streamlined customer service.")
        cy.get('.plugin-card-20811871 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20811871 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("GetResponse card");
        cy.contains('.frm-card', 'GetResponse')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-20813244 > .plugin-card-top > :nth-child(2)').should("contain", "Collect leads in WordPress forms and automatically add them in GetResponse. Then trigger automatic emails and other GetResponse marketing automations.")
        cy.get('.plugin-card-20813244 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20813244 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Quiz Maker card");
        cy.contains('.frm-card', 'Quiz Maker')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-20815759 > .plugin-card-top > :nth-child(2)').should("contain", "Turn your forms into automated quizzes. Add questions and submit the quiz key. Then all the grading is done for you.")
        cy.get('.plugin-card-20815759 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20815759 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Constant Contact card");
        cy.contains('.frm-card', 'Constant Contact')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-20826884 > .plugin-card-top > :nth-child(2)').should("contain", "Setup WordPress forms to create leads automatically in Constant Contact. Just select a list and match up form fields.")
        cy.get('.plugin-card-20826884 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20826884 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Campaign Monitor card");
        cy.contains('.frm-card', 'Campaign Monitor')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-20891694 > .plugin-card-top > :nth-child(2)').should("contain", "Save time by automatically sending leads from WordPress forms to Campaign Monitor.")
        cy.get('.plugin-card-20891694 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20891694 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Export View to CSV card");
        cy.contains('.frm-card', 'Export View to CSV')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-20897348 > .plugin-card-top > :nth-child(2)').should("contain", "Easily create custom CSV files and allow users to export their data from the front-end of your site.")
        cy.get('.plugin-card-20897348 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-20897348 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Legacy Views card");
        cy.contains('.frm-card', 'Legacy Views');
        cy.get('.plugin-card-28027505 > .plugin-card-top > :nth-child(2)').should("contain", "Add the power of views to your Formidable Forms to display your form submissions in listings, tables, calendars, and more.")
        cy.get('.plugin-card-28027505 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28027505 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Visual Views card");
        cy.contains('.frm-card', 'Visual Views')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-28058856 > .plugin-card-top > :nth-child(2)').should("contain", "Create WordPress web apps to display your form submissions in grids, tables, calendars, and more.")
        cy.get('.plugin-card-28058856 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28058856 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Surveys and Polls card");
        cy.contains('.frm-card', 'Surveys and Polls')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28067256 > .plugin-card-top > :nth-child(2)').should("contain", "Transform your WordPress site into a data collection machine with our user-friendly survey form builder.")
        cy.get('.plugin-card-28067256 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28067256 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Landing Pages card");
        cy.contains('.frm-card', 'Landing Pages')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-28074303 > .plugin-card-top > :nth-child(2)').should("contain", "Create beautiful landing pages fast and rake in new leads.")
        cy.get('.plugin-card-28074303 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28074303 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Conversational Forms card");
        cy.contains('.frm-card', 'Conversational Forms')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28100793 > .plugin-card-top > :nth-child(2)').should("contain", "Ask one question at a time to humanize forms and boost their conversion rates.")
        cy.get('.plugin-card-28100793 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28100793 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Geolocation card");
        cy.contains('.frm-card', 'Geolocation')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28118399 > .plugin-card-top > :nth-child(2)').should("contain", "Get more accurate data and make forms faster to complete with address autocomplete.")
        cy.get('.plugin-card-28118399 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28118399 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("PDFs card");
        cy.contains('.frm-card', 'PDFs')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28136428 > .plugin-card-top > :nth-child(2)').should("contain", "Create PDFs from form entries automatically. Email them or let visitors download PDFs from your site.")
        cy.get('.plugin-card-28136428 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28136428 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Google Sheets card");
        cy.contains('.frm-card', 'Google Sheets')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28149579 > .plugin-card-top > :nth-child(2)').should("contain", "Send form entries to a Google spreadsheet as a backup or for extra processing.")
        cy.get('.plugin-card-28149579 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28149579 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("ACF Forms card");
        cy.contains('.frm-card', 'ACF Forms')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28158728 > .plugin-card-top > :nth-child(2)').should("contain", "Sync custom fields between Formidable and Advanced Custom Fields or ACF Pro.")
        cy.get('.plugin-card-28158728 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28158728 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("AI Forms card");
        cy.contains('.frm-card', 'AI Forms')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28189169 > .plugin-card-top > :nth-child(2)').should("contain", "Get back your time by autogenerating a response from ChatGPT and inserting it into a field.")
        cy.get('.plugin-card-28189169 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28189169 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Form Abandonment card");
        cy.contains('.frm-card', 'Form Abandonment')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Business')
            });
        cy.get('.plugin-card-28217763 > .plugin-card-top > :nth-child(2)').should("contain", "Capture form data before it's submitted to save more leads and optimize forms. Plus, auto save drafts and allow logged out editing.")
        cy.get('.plugin-card-28217763 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28217763 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("Charts card");
        cy.contains('.frm-card', 'Charts')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-28248560 > .plugin-card-top > :nth-child(2)').should("contain", "Transform form data into insightful graphs with ease.")
        cy.get('.plugin-card-28248560 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28248560 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

        cy.log("ConvertKit card");
        cy.contains('.frm-card', 'ConvertKitNEW')
            .find('.frm_plan_required')
            .invoke('text')
            .then((text) => {
                const trimmedText = text.replace(/\s+/g, ' ').trim();
                expect(trimmedText).to.include('License plan required: Plus')
            });
        cy.get('.plugin-card-28286367 > .plugin-card-top > :nth-child(2)').should("contain", 'Bring automation into your email marketing plan for the power to say "welcome" to your subscribers the moment they opt-in to your list.')
        cy.get('.plugin-card-28286367 > .plugin-card-bottom > .addon-status').should("contain", "Status: Not Installed");
        cy.get('.plugin-card-28286367 > .plugin-card-bottom > .install-now').should("contain", "Upgrade Now");

    });
});
