describe( 'Submit press while fixing a field error', () => {
	const errorSelector = '[id^="frm_error_field_"]';
	const fieldSelector = '.frm-show-form input[id^="field_"]';

	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
		cy.openForm();
		cy.get( 'li[id="text"] a[title="Text"]' ).should( 'be.visible' ).click();

		// Same field-row "more options" reveal as fieldsInFormBuilder-validation.cy.js.
		cy.get( 'li[data-ftype="text"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons', { timeout: 10000 } )
			.revealFieldActions()
			.find( '.dropdown > .frm_bstooltip > .frmsvg > use' )
			.first()
			.should( 'be.visible' )
			.click();
		cy.get( 'li[data-ftype="text"] .frm_select_field > span' ).should( 'be.visible' ).and( 'contain', 'Field Settings' ).click();
		cy.get( '.frm_field_list div[id^="frm-single-settings-"] .frm_req_field', { timeout: 10000 } ).should( 'be.visible' ).check();
		cy.intercept( 'POST', '**/wp-admin/admin-ajax.php', req => {
			if ( req.body?.includes( 'action=frm_save_form' ) ) {
				req.alias = 'saveForm';
			}
		} );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();
		cy.wait( '@saveForm' );

		// Preview on a blank page, as fieldsInFormBuilder-validation.cy.js does.
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();

		// Submit empty so the required error line sits above the Submit button.
		cy.get( '.frm_button_submit' ).click();
		cy.get( errorSelector ).should( 'exist' );

		// Fix the value, then blur the field while the mouse is down on Submit, as a real click does.
		cy.get( fieldSelector ).first().type( 'Fixed' );
		cy.get( '.frm_button_submit' ).trigger( 'mousedown', { button: 0 } );
		cy.get( fieldSelector ).first().blur();

		// Removing the line now would move Submit out from under the pointer.
		cy.get( errorSelector ).should( 'exist' );
	} );

	it( 'removes the error line after the press ends on the Submit button', () => {
		cy.get( '.frm_button_submit' ).trigger( 'mouseup', { button: 0 } );
		cy.get( errorSelector ).should( 'not.exist' );
	} );

	it( 'removes the error line after a press ends off the Submit button', () => {
		// A press dragged off the button never submits, so the deferred check must still run.
		cy.get( 'body' ).trigger( 'mouseup', { button: 0 } );
		cy.get( errorSelector ).should( 'not.exist' );
	} );

	afterEach( () => {
		// Visit the list directly so a failed run still removes its form, as fieldsInFormBuilder-validation.cy.js does.
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
