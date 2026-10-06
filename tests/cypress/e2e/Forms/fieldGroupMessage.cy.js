describe( 'Field group multiselect message', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
	} );

	const createField = ( fieldId, fieldType ) => {
		cy.get( '.frm-tabs-navs #frm_insert_fields_tab' ).click();
		cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).should( 'be.visible' ).click();
	};

	it( 'should stay dismissed for the rest of the page load once closed', () => {
		cy.openForm();
		createField( 'text', 'Text' );
		createField( 'email', 'Email' );
		cy.get( '#frm-show-fields li[data-type="text"]' ).should( 'have.length', 1 );
		cy.get( '#frm-show-fields li[data-type="email"]' ).should( 'have.length', 1 );

		// Clicking a field group with 2+ rows on the form shows the "hold Shift" message.
		cy.get( '#frm-show-fields li[data-type="text"]' ).click();
		cy.get( '#frm-field-group-message' ).should( 'be.visible' ).and( 'not.have.class', 'frm_hidden' );

		cy.get( '#frm-field-group-message-dismiss' ).click();
		cy.get( '#frm-field-group-message' ).should( 'have.class', 'frm_hidden' );

		// A later field group click must not bring it back for the rest of this page load.
		cy.get( '#frm-show-fields li[data-type="email"]' ).click();
		cy.get( '#frm-field-group-message' ).should( 'have.class', 'frm_hidden' );
	} );
} );
