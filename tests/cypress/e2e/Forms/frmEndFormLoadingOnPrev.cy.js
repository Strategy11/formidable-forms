describe( 'Form loading state is cleared when going to the previous page', () => {
	beforeEach( cy.login );

	it( 'fires frmEndFormLoading and removes frm_loading_prev', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
		cy.get( '#frm-form-templates-create-form' ).click();
		cy.get( '#text' ).should( 'contain.text', 'Text' ).click();
		cy.get( 'a[href*="/wp-admin/admin.php?page=formidable&frm_action=settings&id="]' ).click();

		cy.get( '#form_id' ).invoke( 'val' ).then( formId => {
			cy.visit( `wp-admin/admin.php?page=formidable&frm_action=settings&id=${ formId }` );

			cy.get( '#frm_form_key' ).invoke( 'val' ).then( formKey => {
				cy.visit( `/wp-admin/admin-ajax.php?action=frm_forms_preview&form=${ formKey }` );

				cy.window().then( win => {
					const form = win.document.querySelector( 'form.frm-show-form' );
					let ended = 0;
					win.jQuery( win.document ).on( 'frmEndFormLoading', () => ended++ );

					form.classList.add( 'frm_loading_prev' );
					win.frmFrontForm.removeSubmitLoading( win.jQuery( form ) );

					expect( ended ).to.equal( 1 );
					expect( form.classList.contains( 'frm_loading_prev' ) ).to.equal( false );
				} );
			} );
		} );
	} );
} );
