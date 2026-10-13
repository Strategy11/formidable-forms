describe( 'AJAX form shows the spam message', () => {
	beforeEach( cy.login );

	it( 'shows a spam error in the form instead of submitting the page again', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
		cy.get( '#frm-form-templates-create-form' ).click();
		cy.get( '#text' ).should( 'contain.text', 'Text' ).click();
		cy.get( 'a[href*="/wp-admin/admin.php?page=formidable&frm_action=settings&id="]' ).click();

		cy.get( '#form_id' ).invoke( 'val' ).then( formId => {
			cy.visit( `wp-admin/admin.php?page=formidable&frm_action=settings&id=${ formId }` );
			cy.get( '#frm_form_name' ).type( 'Spam error form' );
			cy.get( '#ajax_submit' ).check();
			cy.get( '#frm_submit_side_top' ).click();
			cy.get( '.frm_updated_message' ).should( 'contain.text', 'Settings Successfully Updated' );

			cy.get( '#frm_form_key' ).invoke( 'val' ).then( formKey => {
				cy.visit( `/wp-admin/admin-ajax.php?action=frm_forms_preview&form=${ formKey }` );

				// Push the submit button below the fold so the summary lands above the viewport.
				cy.get( '.frm_submit' ).then( $submit => {
					$submit[ 0 ].insertAdjacentHTML( 'beforebegin', '<div style="height:2000px"></div>' );
				} );

				// A full page submit would reload the page and drop this marker.
				cy.window().then( win => {
					win.frmNoReload = true;
				} );
				cy.get( 'input.frm_verify' ).invoke( 'val', 'bot' );
				cy.intercept( 'POST', '**/admin-ajax.php' ).as( 'submitForm' );
				cy.get( '.frm_button_submit' ).click();
				cy.wait( '@submitForm' );

				cy.get( '.frm_error_style' ).should( 'contain.text', 'Your entry appears to be spam!' );
				cy.get( '[data-frm-error-summary]' ).should( 'be.visible' ).and( 'have.focus' );
				cy.window().its( 'frmNoReload' ).should( 'be.true' );

				// Keep scrolling to the summary when automatic error focus is disabled.
				cy.get( '.frm-show-form' ).then( $form => {
					const [ form ] = $form;
					const config = JSON.parse( form.dataset.frmErrorConfig );
					config.focusErrorSummary = false;
					config.focusFirstError = false;
					form.dataset.frmErrorConfig = JSON.stringify( config );
					delete form.frmErrorConfigCache;
				} );
				cy.get( '.frm_button_submit' ).focus().click();
				cy.wait( '@submitForm' );
				cy.get( '[data-frm-error-summary]' ).should( 'not.have.focus' ).and( 'contain.text', 'Your entry appears to be spam!' );
				cy.get( '[data-frm-error-summary]' ).should( $summary => {
					const { top } = $summary[ 0 ].getBoundingClientRect();
					expect( top ).to.be.at.least( 0 );
					expect( top ).to.be.lessThan( $summary[ 0 ].ownerDocument.defaultView.innerHeight );
				} );
				cy.window().its( 'frmNoReload' ).should( 'be.true' );
			} );
		} );
	} );
} );
