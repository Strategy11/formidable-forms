describe( 'CSS Layout Classes token input defers initialization until its settings panel opens', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
		// TEMP diagnostic for the recurring beforeEach race - dump real DOM/URL state instead of
		// guessing further. Remove once the actual cause is confirmed.
		cy.location( 'href' ).then( href => cy.task( 'log', `DIAG href=${ href }` ) );
		cy.document().then( doc => cy.task( 'log', `DIAG readyState=${ doc.readyState } title=${ doc.title }` ) );
		cy.get( 'body' ).then( $body => {
			cy.task( 'log', `DIAG current_page count=${ $body.find( '.current_page' ).length } frm_submit_side_top count=${ $body.find( '#frm_submit_side_top' ).length } frm_loading_form=${ $body.find( '.frm_loading_form' ).length } builder_page=${ $body.find( '#frm_builder_page' ).length }` );
		} );
		// Same readiness check cy.openForm() uses before interacting with the builder - guards
		// against a race right after createNewForm()'s modal close, seen when this spec runs
		// immediately after another spec that leaves the forms list mid-transition.
		cy.get( '.current_page' ).should( 'contain', 'Build' );
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
} );
