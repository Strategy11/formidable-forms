describe( 'Updating form settings', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	it( "should 'Show the form title' and 'Show the form description' on the preview form", () => {
		cy.log( 'Create a blank form' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();

		cy.log( `Create a text field` );
		cy.get( `li[id="text"] a[title="Text"]` ).click( { force: true } );

		cy.log( 'Update form' );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Go to Settings tab and enable the option to show the title and description in the form preview' );
		cy.get( '.frm_form_nav', { timeout: 5000 } ).should( 'be.visible' );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();
		cy.get( '#frm_form_description' ).should( 'be.visible' ).type( "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book." );
		cy.get( ':nth-child(4) > .frm_inline_block' ).should( 'contain', 'Show the form title' );
		cy.get( '#show_title' ).check();
		cy.get( ':nth-child(5) > .frm_inline_block' ).should( 'contain', 'Show the form description' );
		cy.get( '#show_description' ).check();
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();

		cy.get( '.frm_form_title' ).should( 'contain', 'Test Form' );
		cy.get( 'p' ).should( 'contain', "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book." );

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( 'back' );

		cy.log( 'Click on Preview - In Theme' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(2) > a' ).should( 'contain', 'In Theme' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '.frm_form_title' ).should( 'contain', 'Test Form' );
		cy.get( 'p' ).should( 'contain', "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book." );

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( 'back' );

		cy.log( 'Go to Settings tab and disable the option to show the title and description in the form preview' );
		cy.get( '.frm_form_nav', { timeout: 5000 } ).should( 'be.visible' );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();
		cy.get( ':nth-child(4) > .frm_inline_block' ).should( 'contain', 'Show the form title' );
		cy.get( '#show_title' ).uncheck();
		cy.get( ':nth-child(5) > .frm_inline_block' ).should( 'contain', 'Show the form description' );
		cy.get( '#show_description' ).uncheck();
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();

		cy.get( '.frm_form_title' ).should( 'not.exist' );
		cy.get( 'p' ).should( 'not.exist' );

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( 'back' );

		cy.log( 'Click on Preview - In Theme' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(2) > a' ).should( 'contain', 'In Theme' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '.frm_form_title' ).should( 'not.exist' );
		cy.get( '.frm_description > p' ).should( 'not.exist' );
		cy.go( 'back' );
		cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click( { force: true } );

		cy.log( 'Teardown - Delete form' );
		cy.deleteForm();
	} );

	it( 'should redirect you to a specific URL after submitting a form', () => {
		const Origin = Cypress.config( 'baseUrl' );

		cy.log( 'Create a blank form' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();

		cy.log( `Create a text field` );
		cy.get( 'li[id="text"] a[title="Text"]', { timeout: 5000 } ).click( { force: true } );

		cy.log( 'Update form' );
		cy.contains( '#frm_submit_side_top', 'Update' ).click( { force: true } );

		cy.log( 'Go to Settings tab' );
		cy.get( '.frm_form_nav', { timeout: 5000 } ).should( 'be.visible' );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();

		cy.log( 'Click on the confirmation action and add the redirect URL' );
		cy.get( '.frm-category-tabs > :nth-child(2) > a' ).should( 'contain', 'Actions & Notifications' ).click();
		cy.get( '.widget .widget-title', { timeout: 5000 } ).first().should( 'contain', 'Confirmation' ).click();
		cy.get( '.frm_on_submit_type_setting > .frm_grid_container > :nth-child(2) > label' ).should( 'contain', 'Redirect to URL' ).click();
		cy.get( '.frm_on_submit_redirect_settings > .frm_has_shortcodes > label' ).should( 'contain', 'Redirect URL' );
		cy.get( '[id^="success_url_"]' ).should( 'exist' ).type( 'https://formidableforms.com/' );

		cy.log( 'Update form' );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '.frm_forms', { timeout: 10000 } )
			.should( 'be.visible' )
			.within( () => {
				cy.get( "button[type='submit'], input[type='submit']" )
					.filter( ':visible' )
					.first()
					.click();
			} );

		cy.log( 'Verify URL redirect after submitting form' );
		cy.origin( 'https://formidableforms.com', () => {
			cy.location( 'href', { timeout: 10000 } ).should( 'include', 'https://formidableforms.com/' );
		} );

		cy.log( 'Navigate back to the formidable form page' );
		cy.visit( Origin + '/wp-admin/admin.php?page=formidable' );
		cy.openForm();

		cy.log( 'Click on Preview - In Theme' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(2) > a' ).should( 'contain', 'In Theme' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '.frm_forms', { timeout: 10000 } )
			.should( 'be.visible' )
			.within( () => {
				cy.get( "button[type='submit'], input[type='submit']" )
					.filter( ':visible' )
					.first()
					.click();
			} );

		cy.log( 'Verify URL redirect after submitting form' );
		cy.origin( 'https://formidableforms.com', () => {
			cy.location( 'href', { timeout: 10000 } ).should( 'include', 'https://formidableforms.com/' );
		} );

		cy.log( 'Navigate back to the formidable form page' );
		cy.visit( Origin + '/wp-admin/admin.php?page=formidable' );

		cy.log( 'Teardown - Delete form' );
		cy.deleteForm();
	} );
} );
