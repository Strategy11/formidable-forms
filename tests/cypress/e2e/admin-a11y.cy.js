describe('Run some accessibility tests', function() {
    beforeEach(cy.login);

    const configureAxeWithIgnoredRuleset = (rules) => {
        cy.configureAxe({ rules });
    };

    const baselineRules = [
        { id: 'color-contrast', enabled: false },
        { id: 'aria-allowed-role', enabled: false },
        { id: 'link-name', enabled: false },
        { id: 'link-in-text-block', enabled: false }
    ];

    it('Check the dashboard page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-dashboard');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'heading-order', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the form list is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'empty-table-header', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the entries page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-entries');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'empty-table-header', enabled: false },
            { id: 'label', enabled: false },
            { id: 'select-name', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the views page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-entries');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'empty-table-header', enabled: false },
            { id: 'label', enabled: false },
            { id: 'select-name', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the styles page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-styles');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            { id: 'aria-allowed-role', enabled: false },
            { id: 'link-name', enabled: false },
            { id: 'label', enabled: false },
            { id: 'label-title-only', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the applications page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-applications');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'image-alt', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the form templates page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-form-templates');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            { id: 'color-contrast', enabled: false },
            { id: 'aria-allowed-role', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the payments page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-settings&t=stripe_settings');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules
        ]);
        cy.checkA11y();
    });

    it('Check the import/export page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-import');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'heading-order', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the global settings page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-settings');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules
        ]);
        cy.checkA11y();
    });

    it('Check the Add-Ons page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-addons');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules
        ]);
        cy.checkA11y();
    });

    it('Check the upgrade page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-pro-upgrade');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'empty-table-header', enabled: false },
            { id: 'heading-order', enabled: false }

        ]);
        cy.checkA11y();
    });

    it('Check the SMTP page is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-smtp');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules,
            { id: 'landmark-unique', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the form creation is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable-form-templates&return_page=forms');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            { id: 'color-contrast', enabled: false },
            { id: 'aria-allowed-role', enabled: false }
        ]);
        cy.checkA11y();
    });

    it('Check the list of deleted forms is accessible', () => {
        cy.visit('/wp-admin/admin.php?page=formidable&form_type=trash');
        cy.injectAxe();
        configureAxeWithIgnoredRuleset([
            ...baselineRules
        ]);
        cy.checkA11y();
    });
});
