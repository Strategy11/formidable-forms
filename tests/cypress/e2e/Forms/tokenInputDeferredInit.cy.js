describe( 'CSS Layout Classes token input defers initialization until its settings panel opens', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		// createNewForm() ends back on the forms list (its naming modal's own save triggers an
		// async publish that lands there, confirmed via a throwaway diagnostic dump against real
		// CI - deleteForms.cy.js/duplicateForm.cy.js/searchFunctionality.cy.js all rely on this
		// same end state). openForm() is the real, deliberate navigation into the builder for the
		// form it just created - not a race to win against that redirect.
		cy.openForm();
		cy.viewport( 1280, 720 );
	} );

	// Same selectors/interaction sequence as fieldsInFormBuilder.cy.js's createField/renameField
	// helpers - reused here rather than invented fresh, since those are already confirmed to work
	// against this same builder UI.
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

	it( 'initializes a field\'s CSS Layout Classes token input only once that field\'s settings panel is shown, not for every field on builder load', () => {
		createField( 'text', 'Text' );
		createField( 'textarea', 'Paragraph' );

		// frm_added_field's own listener already initializes a just-added field's token input
		// immediately - the issue deliberately leaves that listener as-is. Re-enter the builder via
		// a fresh page load so both fields are pre-existing on load instead of just-added this
		// session, which is the actual scenario the deferred-init fix targets.
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.openForm();
		cy.get( 'li[data-ftype="text"]' ).should( 'exist' );
		cy.get( 'li[data-ftype="textarea"]' ).should( 'exist' );

		cy.log( 'Neither field\'s CSS Layout Classes input is tokenized before any settings panel has been opened' );
		cy.get( '.frm-token-container' ).should( 'not.exist' );

		openFieldSettings( 'text' );
		cy.get( 'div[id^="frm-single-settings-"]:visible', { timeout: 10000 } ).find( '.frm-token-container' ).should( 'exist' );
		cy.log( 'Only the opened field\'s token input is initialized - the other field\'s is still untouched' );
		cy.get( '.frm-token-container' ).should( 'have.length', 1 );

		openFieldSettings( 'textarea' );
		cy.get( 'div[id^="frm-single-settings-"]:visible', { timeout: 10000 } ).find( '.frm-token-container' ).should( 'exist' );
		cy.get( '.frm-token-container' ).should( 'have.length', 2 );
	} );

	afterEach( () => {
		// Navigate to the list directly rather than relying on the builder's own "Close" link - same
		// hardening as fieldsInFormBuilder.cy.js's afterEach (formidable-forms#3400: a leaked "Test
		// Form" here previously broke an unrelated downstream spec in the same CI shard once bin-
		// packing put it next to another spec that searches by that exact name).
		cy.log( 'Teardown - delete the form' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
