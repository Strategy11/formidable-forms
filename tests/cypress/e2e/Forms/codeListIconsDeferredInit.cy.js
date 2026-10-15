describe( 'Field shortcode list defers its icons until the shortcode popup opens', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	it( 'adds the field icons to the code list only once the popup is shown', () => {
		cy.log( 'Create a blank form with two fields' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();
		cy.get( 'li[id="text"] a[title="Text"]' ).should( 'be.visible' ).click();
		cy.get( 'li[id="textarea"] a[title="Paragraph"]' ).should( 'be.visible' ).click();
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Open the Confirmation action in the form settings' );
		cy.get( '.frm_form_nav', { timeout: 5000 } ).should( 'be.visible' );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();
		cy.get( '.frm-category-tabs > :nth-child(2) > a' ).should( 'contain', 'Actions & Notifications' ).click();
		cy.get( '.widget .widget-title', { timeout: 5000 } ).first().should( 'contain', 'Confirmation' ).click();

		cy.log( 'The hidden popup has no icons yet' );
		cy.get( '#frm_adv_info .frm_customize_field_list li[data-frm-icon]' ).should( 'have.length', 2 );
		cy.get( '#frm_adv_info .frm_customize_field_list svg' ).should( 'not.exist' );

		cy.log( 'Opening the popup adds an icon to the id and key link of each field' );
		cy.get( '.frm_on_submit_type_setting > .frm_grid_container > :nth-child(2) > label' ).should( 'contain', 'Redirect to URL' ).click();
		cy.get( '.frm_on_submit_redirect_settings .frm-show-box:visible' ).first().click();
		cy.get( '#frm_adv_info' ).should( 'be.visible' );
		cy.get( '#frm_adv_info .frm_customize_field_list li[data-frm-icon]' ).should( 'not.exist' );
		cy.get( '#frm_adv_info .frm_customize_field_list li' ).each( $item => {
			cy.wrap( $item ).find( '> a.frm_insert_code > svg.frmsvg[aria-hidden="true"] > use' ).should( 'have.length', 2 );
		} );
	} );

	afterEach( () => {
		cy.log( 'Teardown - delete the form' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
