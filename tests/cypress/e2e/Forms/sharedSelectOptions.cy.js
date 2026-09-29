describe( 'Builder selects share options until their settings open', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.openForm();
		cy.viewport( 1280, 720 );
		cy.get( 'li[id="text"] a[title="Text"]' ).click();
		cy.get( '.frm-type-text' ).should( 'exist' );
		cy.get( '#frm_insert_fields_tab' ).click();
		cy.get( 'li[id="textarea"] a[title="Paragraph"]' ).click();
		cy.get( '.frm-type-textarea' ).should( 'exist' );
		cy.reload();
	} );

	const openSettings = type => {
		cy.get( `li[data-ftype="${ type }"] .frm-field-action-icons` ).invoke( 'css', 'opacity', 1 );
		cy.get( `li[data-ftype="${ type }"] .frm-dropdown-toggle` ).click();
		cy.get( `li[data-ftype="${ type }"] .frm_select_field` ).click();
		cy.get( `.frm-type-${ type }` ).should( 'be.visible' );
	};

	it( 'preserves submitted values, option order, and selections across repeated panel openings', () => {
		cy.get( '.frm-single-settings select[data-frm-options]' ).should( 'have.length.greaterThan', 1 ).each( select => {
			cy.wrap( select ).find( 'option' ).should( 'have.length.at.most', 1 );
		} );
		cy.window().then( win => {
			const form = win.document.getElementById( 'frm_js_build_form' );
			const data = new win.FormData( form );
			const values = [ ...form.querySelectorAll( 'select[data-frm-options]' ) ].map( select => ( {
				name: select.name,
				values: data.getAll( select.name )
			} ) );
			cy.wrap( values ).as( 'submittedValues' );
		} );

		openSettings( 'text' );
		cy.get( '.frm-type-text select[data-frm-options]' ).should( 'not.exist' );
		cy.get( '.frm-type-textarea select[data-frm-options]' ).should( 'exist' );
		cy.get( '.frm-type-text select[id^="field_options_type_"] option' ).should( 'have.length.greaterThan', 1 );
		cy.get( '@submittedValues' ).then( values => {
			cy.window().then( win => {
				const data = new win.FormData( win.document.getElementById( 'frm_js_build_form' ) );
				values.forEach( setting => expect( data.getAll( setting.name ) ).to.deep.equal( setting.values ) );
			} );
		} );

		cy.get( '.frm-type-text select[id^="field_options_autocomplete_"]' ).then( select => {
			cy.wrap( [ ...select[ 0 ].options ].map( option => option.value ) ).as( 'optionOrder' );
		} );
		cy.get( '.frm-type-text h3[aria-label="Collapsible Advanced Settings"]' ).click();
		cy.get( '.frm-type-text select[id^="field_options_autocomplete_"]' ).should( 'be.visible' ).select( 'off' );
		openSettings( 'textarea' );
		openSettings( 'text' );
		cy.get( '.frm-type-text select[id^="field_options_autocomplete_"]' ).should( 'have.value', 'off' );
		cy.get( '@optionOrder' ).then( values => {
			cy.get( '.frm-type-text select[id^="field_options_autocomplete_"]' ).then( select => {
				expect( [ ...select[ 0 ].options ].map( option => option.value ) ).to.deep.equal( values );
			} );
		} );
	} );

	afterEach( () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
