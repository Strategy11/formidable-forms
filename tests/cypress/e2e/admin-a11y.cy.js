describe( 'Run some accessibility tests', function() {
    beforeEach( cy.login );

    const configureAxeWithBaselineIgnoredRuleset = () => {
        cy.configureAxe({
            rules: [
                { id: 'color-contrast', enabled: false },
                { id: 'aria-required-parent', enabled: false },
                { id: 'aria-required-children', enabled: false },
                { id: 'has-visible-text', enabled: false },
                { id: 'listitem', enabled: false },
                { id: 'link-in-text-block', enabled: false },
                { id: 'link-name', enabled: false },
                { id: 'empty-table-header', enabled: false },
                { id: 'heading-order', enabled: false },
                { id: 'no-dup-id', enabled: false },
                { id: 'aria-allowed-role', enabled: false },
                {id: 'label-title-only', enabled:false },
                {id: 'label', enabled:false },
                {id: 'landmark-unique', enabled:false },
                {id: 'image-alt', enabled:false }
            ]
        });
    };

    it('Check the dashboard page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the form list is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the entries page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-entries' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the views page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-entries' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the styles page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the applications page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-applications' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the form templates page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the payments page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-settings&t=stripe_settings' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the import/export page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the global settings page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the Add-Ons page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-addons' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the upgrade page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-pro-upgrade' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the SMTP page is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-smtp' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the form creation is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable-form-templates&return_page=forms' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

    it('Check the list of deleted forms is accessible', () => {
        cy.visit( '/wp-admin/admin.php?page=formidable&form_type=trash' );
        cy.injectAxe();
        configureAxeWithBaselineIgnoredRuleset();
        cy.checkA11y();
    });

});
