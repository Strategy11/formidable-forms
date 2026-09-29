describe( 'Builder selects share options until their settings open', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.openForm();
		cy.viewport( 1280, 720 );
		cy.get( 'li[id="text"] a[title="Text"]' ).click();
		cy.get( '.frm-type-text', { timeout: 10000 } ).should( 'exist' );
		cy.get( '.frm-tabs-navs #frm_insert_fields_tab' ).click();
		cy.get( 'li[id="textarea"] a[title="Paragraph"]' ).click();
		cy.get( '.frm-type-textarea', { timeout: 10000 } ).should( 'exist' );
		cy.reload();
	} );

	const openSettings = type => {
		cy.get( `li[data-ftype="${ type }"] .frm-field-action-icons` ).invoke( 'css', 'opacity', 1 );
		cy.get( `li[data-ftype="${ type }"] .frm-dropdown-toggle` ).click();
		cy.get( `li[data-ftype="${ type }"] .frm_select_field` ).click();
		// Cypress reads the fixed builder layout inside WordPress's zero-height #wpbody-content as
		// clipped, so ask the browser whether the panel is really shown.
		cy.get( `.frm-type-${ type }` ).should( panel => expect( panel[ 0 ].checkVisibility() ).to.be.true );
	};

	it( 'preserves submitted values, option order, and selections across repeated panel openings', () => {
		cy.get( '.frm-single-settings select[data-frm-options]' ).should( 'have.length.greaterThan', 1 ).each( select => {
			cy.wrap( select ).find( 'option' ).should( 'have.length.at.most', 1 );
		} );
		cy.get( '.frm-single-settings select[data-frm-options]' ).then( selects => {
			const values = [ ...selects ].map( select => ( {
				id: select.id,
				name: select.name,
				values: [ ...select.selectedOptions ].map( option => option.value )
			} ) );
			cy.wrap( values ).as( 'selectedValues' );
		} );

		openSettings( 'text' );
		cy.get( '.frm-type-text select[data-frm-options]' ).should( 'not.exist' );
		cy.get( '.frm-type-textarea select[data-frm-options]' ).should( 'exist' );
		cy.get( '.frm-type-text select[id^="field_options_type_"] option' ).should( 'have.length.greaterThan', 1 );
		cy.get( '@selectedValues' ).then( values => {
			cy.window().then( win => {
				// An opened panel's settings move into #new_fields, the form the builder serializes on save.
				const data = new win.FormData( win.document.getElementById( 'new_fields' ) );
				const textPanel = win.document.querySelector( '.frm-single-settings.frm-type-text' );
				values.forEach( setting => {
					const select = win.document.getElementById( setting.id );
					expect( [ ...select.selectedOptions ].map( option => option.value ) ).to.deep.equal( setting.values );
					if ( textPanel.contains( select ) ) {
						expect( data.getAll( setting.name ) ).to.deep.equal( setting.values );
					}
				} );
			} );
		} );

		cy.get( '.frm-type-text select[id^="field_options_autocomplete_"]' ).then( select => {
			cy.wrap( [ ...select[ 0 ].options ].map( option => option.value ) ).as( 'optionOrder' );
		} );
		cy.get( '.frm-type-text h3[aria-label="Collapsible Advanced Settings"]' ).click();
		// Cypress reads the fixed builder layout inside WordPress's zero-height #wpbody-content as
		// clipped, so ask the browser whether the select is really shown, then choose like a user would.
		cy.get( '.frm-type-text select[id^="field_options_autocomplete_"]' )
			.scrollIntoView()
			.should( select => expect( select[ 0 ].checkVisibility() ).to.be.true )
			.then( select => {
				select[ 0 ].focus();
				select[ 0 ].value = 'off';
				select[ 0 ].dispatchEvent( new select[ 0 ].ownerDocument.defaultView.Event( 'change', { bubbles: true } ) );
			} );
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
