describe( 'Deferred settings without Pro', { retries: 0 }, () => {
	const formName = 'Deferred Lite QA';

	beforeEach( () => {
		cy.login();
		cy.viewport( 1280, 900 );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		// Wait for creation before looking for the builder's Save button.
		cy.intercept( 'POST', '**/wp-admin/admin-ajax.php*' ).as( 'createBlankForm' );
		cy.get( '#frm-form-templates-create-form' ).then( button => button[ 0 ].click() );
		cy.wait( '@createBlankForm', { timeout: 30000 } );
		cy.get( '#frm_submit_side_top', { timeout: 30000 } ).then( button => button[ 0 ].click() );
		cy.get( '#frm_new_form_name_input' ).type( formName );
		cy.intercept( 'POST', '**/wp-admin/admin.php*' ).as( 'saveFixture' );
		cy.get( '#frm-save-form-name-button' ).then( button => button[ 0 ].click() );
		cy.wait( '@saveFixture' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.contains( '#the-list tr', formName ).find( 'a.row-title' ).click();
	} );

	afterEach( () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.get( '#the-list' ).then( list => {
			const rows = Array.from( list[ 0 ].querySelectorAll( 'tr' ) );
			const row = rows.find( item => item.querySelector( '.row-title' )?.textContent.trim() === formName );
			row?.querySelector( '.row-actions .trash a' )?.click();
		} );
	} );

	it( 'initializes each inserted panel once and preserves edits made to its metadata', () => {
		cy.url().then( builderUrl => {
			cy.visit( builderUrl.replace( 'frm_action=edit', 'frm_action=settings' ) );
			cy.get( '#ajax_load' ).then( inputs => {
				if ( ! inputs[ 0 ].checked ) {
					inputs[ 0 ].click();
				}
			} );
			cy.intercept( 'POST', '**/wp-admin/admin.php*' ).as( 'saveAjaxBuilder' );
			cy.get( '#frm_submit_side_top' ).then( button => button[ 0 ].click() );
			cy.wait( '@saveAjaxBuilder' );
			cy.visit( builderUrl );
		} );
		cy.get( 'li#text a.frm_add_field' ).then( button => button[ 0 ].click() );
		cy.get( '#frm-show-fields li[data-ftype="text"]' ).invoke( 'attr', 'data-fid' ).then( id => {
			cy.reload();
			cy.get( '.frm_field_preview_pending' ).should( 'not.exist' );
			cy.get( `#frm-single-settings-${ id }` ).should( 'not.exist' );
			const initialized = [];
			cy.window().then( win => {
				win.wp.hooks.addAction( 'frm_field_settings_loaded', 'qa-deferred-settings', settings => initialized.push( settings ) );
				win.document.getElementById( `frm_classes_${ id }` ).value = 'frm6 qa-late-class';
			} );
			cy.get( `#frm_field_id_${ id }` ).then( field => field[ 0 ].click() );
			cy.get( `#frm-single-settings-${ id }` ).should( 'not.have.class', 'frm_hidden' ).then( settings => {
				expect( initialized ).to.have.length( 1 );
				expect( initialized[ 0 ] ).to.equal( settings[ 0 ] );
				expect( settings[ 0 ].querySelectorAll( '.frm-hide-empty' ) ).to.satisfy( wrappers =>
					Array.from( wrappers ).every( wrapper => wrapper.textContent.trim() !== '' )
				);
			} );
			cy.get( `#frm_classes_${ id }` ).should( 'have.value', 'frm6 qa-late-class' );
			cy.get( '#frm_insert_fields_tab' ).then( tab => tab[ 0 ].click() );
			cy.get( `#frm_field_id_${ id }` ).then( field => field[ 0 ].click() );
			cy.then( () => expect( initialized ).to.have.length( 1 ) );
		} );
	} );
} );
