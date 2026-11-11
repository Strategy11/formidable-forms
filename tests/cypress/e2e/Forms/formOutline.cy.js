describe( 'Form outline', { retries: 0 }, () => {
	let formName;

	const openOutline = () => {
		cy.get( '#frm-builder-outline-toggle' ).then( toggle => {
			if ( ! toggle.closest( 'li' ).hasClass( 'frm-active' ) ) {
				toggle[ 0 ].click();
			}
		} );
		cy.get( '#frm-builder-outline-toggle' ).closest( 'li' ).should( 'have.class', 'frm-active' );
		cy.get( '#frm-builder-outline' ).should( 'exist' );
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

	// Lite can't add a Page Break, so add one to the builder markup to split the form into pages.
	const addPageBreak = () => {
		cy.get( '#frm-show-fields' ).then( list => {
			list[ 0 ].append( createField( list[ 0 ].ownerDocument, '90001', 'break', 'Next' ) );
		} );
	};

	it( 'renders only in its tab and finds preview labels', () => {
		cy.get( '.frm-builder-outline-list' ).should( 'be.empty' );

		addField( 'text' );
		addField( 'html' );
		cy.get( '.frm-builder-outline-list' ).should( 'be.empty' );
		addPageBreak();
		openOutline();

		// Field previews print the label in a different code path, so check both.
		[ false, true ].forEach( usePreviews => {
			if ( usePreviews ) {
				// See tests/mu-plugins/frm-force-ajax-builder.php.
				cy.setCookie( 'frm_e2e_ajax_builder', '1' );
				cy.reload();
				cy.get( '.frm_field_preview_pending' ).should( 'not.exist' );
				addPageBreak();
				openOutline();
			}

			// The required and collapsed markers must not leak into the name.
			search( 'text' );
			cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1 ).and( 'have.text', 'Text' );
			search( 'collapsed' );
			cy.get( '.frm-builder-outline-empty-title' ).should( 'have.text', 'No results for “collapsed”' );
			search( 'html' );
			cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1 ).and( 'have.text', 'HTML' );
		} );
	} );

	it( 'groups fields by page, lists sections and caps field results', () => {
		cy.get( '#frm-show-fields' ).then( list => {
			const doc = list[ 0 ].ownerDocument;
			const fields = [];
			const section = createField( doc, '99999', 'divider', 'Contact details' );
			const sectionFields = doc.createElement( 'ul' );
			sectionFields.className = 'start_divider';
			sectionFields.append(
				createField( doc, '9035', 'text', 'Item 35' ),
				createField( doc, '99998', 'end_divider', '' )
			);
			section.append( sectionFields );
			for ( let i = 1; i <= 70; i++ ) {
				if ( i === 31 || i === 51 ) {
					fields.push( createField( doc, `${ 9000 + i }0`, 'break', 'Next' ) );
				}
				if ( i === 35 ) {
					fields.push( section );
					continue;
				}
				fields.push( createField( doc, `${ 9000 + i }`, 'text', `Item ${ i }` ) );
			}
			const fragment = doc.createDocumentFragment();
			fragment.append( ...fields );
			list[ 0 ].append( fragment );
		} );
		openOutline();

		cy.get( '.frm-builder-outline-page' ).should( 'have.length', 3 );
		cy.get( '.frm-builder-outline-page' ).first().find( '.frm-builder-outline-label' ).should( 'have.text', 'Page 1: Item 1' );
		// Counts leave out page breaks and sections.
		cy.get( '.frm-builder-outline-page' ).first().find( '.frm-builder-outline-meta' ).should( 'have.text', '30 fields' );
		cy.get( '.frm-builder-outline-page' ).eq( 1 ).find( '.frm-builder-outline-meta' ).should( 'have.text', '20 fields' );
		cy.get( '.frm-builder-outline-section' ).should( 'have.length', 1 );
		cy.get( '.frm-builder-outline-section .frm-builder-outline-label' ).should( 'have.text', 'Contact details' );
		cy.get( '.frm-builder-outline-section .frm-builder-outline-meta' ).should( 'have.text', '1 field' );
		cy.get( '.frm-builder-outline-section' ).parent().find( '.frm-builder-outline-field' ).should( 'have.length', 1 ).and( 'have.text', 'Item 35' );

		search( '99998' );
		cy.get( '.frm-builder-outline-field' ).should( 'not.exist' );
		cy.get( '.frm-builder-outline-empty-title' ).should( 'have.text', 'No results for “99998”' );

		search( 'contact' );
		cy.get( '.frm-builder-outline-page' ).should( 'have.length', 1 );
		cy.get( '.frm-builder-outline-section .frm-builder-outline-label' ).should( 'have.text', 'Contact details' );
		cy.get( '.frm-builder-outline-field' ).should( 'not.exist' );

		search( 'item' );
		cy.get( '.frm-builder-outline-field' ).should( 'have.length', 60 );
		cy.get( '.frm-builder-outline-status' ).should( 'have.text', 'Showing up to 60 matching fields.' );

		search( 'item 42' );
		cy.get( '#frm-builder-outline-search-input' ).type( '{enter}' );
		cy.get( '#frm-show-fields li[data-fid="9042"]' ).should( 'have.class', 'frm-builder-outline-target' );
		cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1 ).click();
		cy.get( '#frm-show-fields li[data-fid="9042"]' ).should( 'have.class', 'frm-builder-outline-target' );
		// Jumping to a field keeps the outline open.
		cy.get( '#frm-builder-outline-toggle' ).closest( 'li' ).should( 'have.class', 'frm-active' );

		// The outline is a sidebar tab, so returning to Add Fields closes it.
		cy.get( '#frm_insert_fields_tab' ).then( tab => tab[ 0 ].click() );
		cy.get( '#frm_insert_fields_tab' ).closest( 'li' ).should( 'have.class', 'frm-active' );
		cy.get( '#frm-builder-outline-toggle' ).closest( 'li' ).should( 'not.have.class', 'frm-active' );
	} );

	it( 'tracks edits only while open and preserves results when scrolling', () => {
		cy.get( '#frm-show-fields' ).then( list => {
			const doc = list[ 0 ].ownerDocument;
			const fragment = doc.createDocumentFragment();
			const fields = [];
			for ( let index = 0; index < 1000; index++ ) {
				fields.push( createField( doc, `${ 90000 + index }`, 'text', `Large field ${ index }` ) );
			}
			fragment.append( ...fields );
			list[ 0 ].append( fragment );
		} );
		cy.get( '.frm-builder-outline-list' ).should( 'be.empty' );
		openOutline();
		cy.get( '.frm-builder-outline-field' ).should( 'have.length', 1000 );
		cy.get( '.frm-builder-outline-field' ).first().then( button => {
			cy.get( '#post-body-content' ).scrollTo( 'bottom' );
			cy.get( '.frm-builder-outline-field' ).first().should( current => {
				expect( current[ 0 ] ).to.equal( button[ 0 ] );
			} );
		} );
		cy.get( '#frm-show-fields li[data-fid="90000"] .frm_builder_field_name' ).then( label => {
			label[ 0 ].textContent = 'Changed while open';
		} );
		cy.get( '.frm-builder-outline-field' ).first().should( 'have.text', 'Changed while open' );
		cy.get( '#frm_insert_fields_tab' ).then( tab => tab[ 0 ].click() );
		cy.get( '#frm-show-fields li[data-fid="90000"] .frm_builder_field_name' ).then( label => {
			label[ 0 ].textContent = 'Changed while closed';
		} );
		openOutline();
		cy.get( '.frm-builder-outline-field' ).first().should( 'have.text', 'Changed while closed' );
	} );
} );
