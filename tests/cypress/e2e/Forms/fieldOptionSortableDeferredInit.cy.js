describe( 'Field option choices list defers jQuery UI sortable init until its settings panel first opens', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		// createNewForm() ends back on the forms list (its naming modal's own save triggers an
		// async publish that lands there) - same end state tokenInputDeferredInit.cy.js relies on.
		cy.openForm();
		cy.viewport( 1280, 720 );
	} );

	// Same selectors/interaction sequence as tokenInputDeferredInit.cy.js's own helpers - reused
	// here rather than invented fresh, since those are already confirmed to work against this
	// same builder UI.
	const createField = ( fieldId, fieldType ) => {
		cy.log( `Create a ${ fieldType } field` );
		cy.get( '.frm-tabs-navs #frm_insert_fields_tab' ).click();
		cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).should( 'be.visible' ).click();
	};

	const openFieldSettings = fieldId => {
		cy.log( `Open settings panel for the ${ fieldId } field` );
		cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons`, { timeout: 10000 } )
			.invoke( 'css', 'opacity', 1 )
			.find( '.dropdown > .frm_bstooltip > .frmsvg > use' )
			.first()
			.scrollIntoView()
			.should( 'be.visible' )
			.click();
		cy.get( `li[data-ftype="${ fieldId }"] .frm_select_field > span` ).should( 'be.visible' ).and( 'contain', 'Field Settings' ).click();
	};

	// The settings-panel container is keyed by the field's own numeric id (data-fid), not its
	// type string - same scoping fieldsInFormBuilder.cy.js's "required" test already relies on,
	// so this resolves the one panel that was just opened rather than any other options field's.
	const settingsPanel = fieldId => cy.get( `li[data-ftype="${ fieldId }"]` ).invoke( 'data', 'fid' ).then( fieldNumericId => cy.get( `#frm-single-settings-${ fieldNumericId }`, { timeout: 10000 } ) );

	it( 'adds ui-sortable to a field\'s own settings panel only once that panel is shown, not for every options field on builder load', () => {
		createField( 'radio', 'Radio Buttons' );
		createField( 'checkbox', 'Checkboxes' );

		// Re-enter the builder via a fresh page load so both fields are pre-existing on load
		// instead of just-added this session - the actual scenario the deferred-init fix targets,
		// same reasoning as tokenInputDeferredInit.cy.js.
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.openForm();
		cy.get( 'li[data-ftype="radio"]' ).should( 'exist' );
		cy.get( 'li[data-ftype="checkbox"]' ).should( 'exist' );

		cy.log( 'Neither field\'s settings panel is sortable-initialized before its panel has been opened' );
		settingsPanel( 'radio' ).should( 'not.have.class', 'ui-sortable' );
		settingsPanel( 'checkbox' ).should( 'not.have.class', 'ui-sortable' );

		openFieldSettings( 'radio' );
		settingsPanel( 'radio' ).should( 'have.class', 'ui-sortable' );
		cy.log( 'Only the opened field\'s panel is initialized - the other field\'s is still untouched' );
		settingsPanel( 'checkbox' ).should( 'not.have.class', 'ui-sortable' );

		openFieldSettings( 'checkbox' );
		settingsPanel( 'checkbox' ).should( 'have.class', 'ui-sortable' );
	} );

	afterEach( () => {
		// Navigate to the list directly rather than relying on the builder's own "Close" link -
		// same hardening as tokenInputDeferredInit.cy.js's afterEach (formidable-forms#3400).
		cy.log( 'Teardown - delete the form' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
