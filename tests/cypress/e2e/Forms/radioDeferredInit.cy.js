describe( 'Radio settings initialize when their field opens', () => {
	const radioMarkup = id => `
		<div class="radio-test-section">
			<div class="frm-style-component frm-radio-component" data-radio-test="${ id }">
				<input type="radio" name="radio_test_${ id }" id="radio_test_${ id }_first" value="first" checked>
				<label for="radio_test_${ id }_first" style="display:inline-block;width:80px">First</label>
				<input type="radio" name="radio_test_${ id }" id="radio_test_${ id }_second" value="second">
				<label for="radio_test_${ id }_second" style="display:inline-block;width:120px">Second</label>
				<span class="frm-radio-active-tracker"></span>
			</div>
		</div>`;

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

		// Exercise the shared radio component with Lite or Pro, before page initialization.
		cy.intercept( { method: 'GET', pathname: '/wp-admin/admin.php', query: { frm_action: 'edit' } }, request => {
			request.continue( response => {
				response.body = response.body.replace(
					/<div class="frm-single-settings[^\"]*" id="frm-single-settings-(\d+)"[^>]*>/g,
					( openingTag, id ) => openingTag + radioMarkup( id )
				).replace( '</body>', `${ radioMarkup( 'outside' ) }</body>` );
			} );
		} );
		cy.reload();
		cy.get( '.frm-type-text [data-radio-test]' ).should( 'have.length', 1 );
		cy.get( '.frm-type-textarea [data-radio-test]' ).should( 'have.length', 1 );
	} );

	const openSettings = type => {
		cy.get( `li[data-ftype="${ type }"] .frm-field-action-icons` ).invoke( 'css', 'opacity', 1 );
		cy.get( `li[data-ftype="${ type }"] .frm-dropdown-toggle` ).click();
		cy.get( `li[data-ftype="${ type }"] .frm_select_field` ).click();
		// Cypress reads the fixed builder layout inside WordPress's zero-height #wpbody-content as
		// clipped, so ask the browser whether the panel is really shown.
		cy.get( `.frm-type-${ type }` ).should( panel => expect( panel[ 0 ].checkVisibility() ).to.be.true );
	};

	it( 'defers hidden fields, reconnects only the active panel, and keeps other radio controls working', () => {
		cy.get( '.frm-single-settings .frm-radio-active-tracker' ).should( 'not.have.attr', 'style' );
		cy.get( '[data-radio-test="outside"] .frm-radio-active-tracker' ).should( 'have.css', 'width', '80px' );
		cy.window().then( win => {
			const fields = [ ...win.document.querySelectorAll( 'div[id^="frm-single-settings-"]' ) ];
			const event = new win.Event( 'frm_ajax_loaded_field' );
			event.frmFields = fields.map( field => ( { id: field.dataset.fid } ) );
			win.document.dispatchEvent( event );
			const addedEvent = new win.Event( 'frm_added_field' );
			addedEvent.frmField = win.document.querySelector( '#frm-show-fields .form-field' );
			win.document.dispatchEvent( addedEvent );
		} );
		cy.get( '.frm-single-settings .frm-radio-active-tracker' ).should( 'not.have.attr', 'style' );

		openSettings( 'text' );
		cy.get( '.frm-type-text .frm-radio-active-tracker' ).should( 'have.css', 'width', '80px' );
		openSettings( 'textarea' );
		cy.get( '.frm-type-textarea .radio-test-section' ).invoke( 'addClass', 'frm_hidden' );
		cy.get( '.frm-type-textarea [data-radio-test]' ).invoke( 'addClass', 'radio-test-changed' );
		cy.get( '.frm-type-textarea .frm-radio-active-tracker' ).should( 'have.attr', 'style' ).and( 'include', '80px' );
		cy.get( '.frm-type-textarea .radio-test-section' ).invoke( 'removeClass', 'frm_hidden' );
		cy.get( '.frm-type-textarea input[value="second"]' ).check();
		cy.get( '.frm-type-textarea .frm-radio-active-tracker' ).should( 'have.css', 'width', '120px' );

		openSettings( 'text' );
		cy.window().then( win => {
			// onRadioChange measures its wrapper once per call, so a second change listener on a
			// reopened panel would show up as a second measurement.
			const wrapper = win.document.querySelector( '.frm-type-text [data-radio-test]' );
			let changeHandlerCalls = 0;
			wrapper.getClientRects = function() {
				++changeHandlerCalls;
				return win.Element.prototype.getClientRects.call( this );
			};
			const second = win.document.querySelector( '.frm-type-text input[value="second"]' );
			second.checked = true;
			second.dispatchEvent( new win.Event( 'change', { bubbles: true } ) );
			delete wrapper.getClientRects;
			expect( changeHandlerCalls ).to.equal( 1 );
		} );
		cy.get( '.frm-type-text .frm-radio-active-tracker' ).should( 'have.css', 'width', '120px' );
		// The injected control sits under the fixed builder layout, so change it the way the
		// text field's radio is changed above. Its change handler is what is under test.
		cy.get( '[data-radio-test="outside"] input[value="second"]' ).then( radio => {
			radio[ 0 ].checked = true;
			radio[ 0 ].dispatchEvent( new radio[ 0 ].ownerDocument.defaultView.Event( 'change', { bubbles: true } ) );
		} );
		cy.get( '[data-radio-test="outside"] .frm-radio-active-tracker' ).should( 'have.css', 'width', '120px' );
	} );

	afterEach( () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
