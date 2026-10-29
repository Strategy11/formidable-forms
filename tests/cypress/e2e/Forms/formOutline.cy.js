describe( 'Form outline', { retries: 0 }, () => {
	let formName;

	const openOutline = () => {
		cy.get( '#frm-builder-outline-tab' ).then( tab => tab[ 0 ].closest( 'li' ).click() );
	};

	const search = query => {
		cy.get( '#frm-builder-outline-search-input' ).clear();
		if ( query ) {
			cy.get( '#frm-builder-outline-search-input' ).type( query );
		}
	};

	const addField = type => {
		cy.get( '#frm_insert_fields_tab' ).then( tab => tab[ 0 ].click() );
		cy.intercept( 'POST', '**/wp-admin/admin-ajax.php*' ).as( `insert-${ type }` );
		cy.get( `li#${ type } a.frm_add_field` ).then( button => button[ 0 ].click() );
		cy.wait( `@insert-${ type }` );
		cy.get( `#frm-show-fields li[data-type="${ type }"]` ).should( 'exist' );
	};

	// Lite has no Page Break or Section fields, so build their builder markup directly.
	const createField = ( doc, fid, type, name ) => {
		const field = doc.createElement( 'li' );
		field.dataset.fid = fid;
		field.dataset.type = type;
		const label = doc.createElement( 'label' );
		label.className = 'frm_primary_label';
		const labelText = doc.createElement( 'span' );
		labelText.className = 'frm_builder_field_name';
		labelText.textContent = name;
		const required = doc.createElement( 'span' );
		required.className = 'frm_required';
		required.textContent = '*';
		label.append( labelText, required );
		field.append( label );
		return field;
	};

	beforeEach( () => {
		// A unique name keeps a form trashed by the previous test from being opened again.
		formName = `Form Outline QA ${ Date.now() }`;
		cy.login();
		cy.viewport( 1280, 900 );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
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
		cy.clearCookie( 'frm_e2e_ajax_builder' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.get( '#the-list' ).then( list => {
			const rows = Array.from( list[ 0 ].querySelectorAll( 'tr' ) );
			const row = rows.find( item => item.querySelector( '.row-title' )?.textContent.trim() === formName );
			row?.querySelector( '.row-actions .trash a' )?.click();
		} );
	} );

	it( 'shows empty states and finds fields by name only', () => {
		openOutline();
		cy.get( '.frm-builder-outline-empty-title' ).should( 'have.text', 'No fields yet' );
		cy.get( '#frm-builder-outline .frm-search' ).should( 'have.class', 'frm_hidden' );

		addField( 'text' );
		addField( 'html' );
		openOutline();
		cy.get( '.frm-builder-outline-empty-title' ).should( 'have.text', 'No pages or sections yet' );

		// Field previews print the label in a different code path, so check both.
		[ false, true ].forEach( usePreviews => {
			if ( usePreviews ) {
				// See tests/mu-plugins/frm-force-ajax-builder.php.
				cy.setCookie( 'frm_e2e_ajax_builder', '1' );
				cy.reload();
				cy.get( '.frm_field_preview_pending' ).should( 'not.exist' );
				openOutline();
			}

			// The required and collapsed markers must not leak into the name.
			search( 'text' );
			cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1 ).and( 'have.text', 'Text' );
			search( 'collapsed' );
			cy.get( '.frm-builder-outline-empty-title' ).should( 'have.text', 'No results' );
			search( 'html' );
			cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1 ).and( 'have.text', 'HTML' );
		} );
	} );

	it( 'groups fields by page, lists sections and caps field results', () => {
		openOutline();
		cy.get( '#frm-show-fields' ).then( list => {
			const doc = list[ 0 ].ownerDocument;
			const fields = [];
			for ( let i = 1; i <= 70; i++ ) {
				if ( i === 31 || i === 51 ) {
					fields.push( createField( doc, `${ 9000 + i }0`, 'break', 'Next' ) );
				}
				if ( i === 35 ) {
					fields.push( createField( doc, '99999', 'divider', 'Contact details' ) );
				}
				fields.push( createField( doc, `${ 9000 + i }`, 'text', `Item ${ i }` ) );
			}
			list[ 0 ].append( ...fields );
		} );

		cy.get( '.frm-builder-outline-page' ).should( 'have.length', 3 );
		cy.get( '.frm-builder-outline-page' ).first().find( '.frm-builder-outline-label' ).should( 'have.text', 'Page 1: Item 1' );
		cy.get( '.frm-builder-outline-page' ).first().find( '.frm-builder-outline-count' ).should( 'have.text', '30' );
		cy.get( '.frm-builder-outline-page' ).eq( 1 ).find( '.frm-builder-outline-count' ).should( 'have.text', '21' );
		cy.get( '.frm-builder-outline-section' ).should( 'have.length', 1 ).and( 'have.text', 'Contact details' );

		search( 'contact' );
		cy.get( '.frm-builder-outline-page' ).should( 'have.length', 1 );
		cy.get( '.frm-builder-outline-section' ).should( 'have.text', 'Contact details' );
		cy.get( '.frm-builder-outline-field' ).should( 'not.exist' );

		search( 'item' );
		cy.get( '.frm-builder-outline-field' ).should( 'have.length', 60 );
		cy.get( '.frm-builder-outline-status' ).should( 'have.text', 'Showing up to 60 matching fields.' );

		search( 'item 42' );
		cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1 ).click();
		cy.get( '#frm-show-fields li[data-fid="9042"]' ).should( 'have.class', 'frm-builder-outline-target' );
		// Jumping to a field keeps the outline open.
		cy.get( '#frm-builder-outline' ).parent().should( 'have.class', 'frm-active' );
	} );
} );
