describe( 'Entries submitted from a form', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should not be stored in the entry list', () => {
		cy.log( 'Create a blank form' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();

		cy.log( `Create a text field` );
		cy.get( `li[id="text"] a[title="Text"]` ).click( { force: true } );

		cy.log( 'Update form' );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( "Go to Settings tab and enable the 'Do not store entries submitted from this form' option" );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();
		cy.get( ':nth-child(8) > .frm_inline_block' ).should( 'contain', 'Do not store entries submitted from this form' );
		cy.get( '#no_save' ).check();
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Submit form and verify entry is not stored' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '[id^="field_"]:not(.frm_verify)' ).filter( 'input, textarea' ).type( 'Entry is not stored' );
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();
		cy.go( -2 );
		cy.get( '.frm_form_nav > :nth-child(4) > a' ).should( 'contain', 'Entries' ).click();
		cy.get( '.wrap > h2' ).should( 'contain', 'Form Entries' );

		cy.log( 'Check that the tip info text matches one of the expected messages' );
		cy.get( '.frm-tip-info' ).then( $el => {
			const text = $el.text().trim();

			expect( text ).to.be.oneOf( [
				'Want to search submitted entries?',
				'Make your site dynamic. Enable front-end editing.',
				'Edit form entries anytime with entry management.',
				'Want to import entries into your forms?',
				'Turn entries into dynamic content — no code needed.'
			] );
		} );

		cy.log( 'Check that the upgrade text matches one of the expected messages' );
		cy.get( '.frm-tip-cta' ).then( $el => {
			const text = $el.text().trim();
			expect( text ).to.be.oneOf( [
				'Upgrade to Pro.',
				'Get 60% Off Pro!'
			] );
		} );

		cy.get( 'h3' ).should( 'contain', 'This form is not set to save any entries.' );

		cy.get( '.frmcenter.frm_no_entries_form.frm_placeholder_block' )
			.should( 'exist' )
			.within( () => {
				cy.get( 'h3' ).should( 'contain.text', 'This form is not set to save any entries.' );

				cy.get( 'p' ).should( 'contain.text', 'If you would like to save entries in this form, go to the' );

				cy.get( 'a' )
					.should( 'have.attr', 'href' )
					.and( 'match', /page=formidable&frm_action=settings&id=\d+/ );
			} );

		cy.get( '.frm_form_nav > :nth-child(1) > a' ).should( 'contain', 'Build' ).click();
		cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click( { force: true } );
		cy.log( 'Verify that entries are not allowed from the forms list' );
		cy.get( 'td[data-colname="Entries"] svg[data-original-title="Saving entries is disabled for this form"]' ).should( 'exist' );
	} );

	it( 'should be stored and validated in the entry list', () => {
		cy.log( 'Create a blank form' );
		cy.contains( '.frm_nav_bar .button-primary', 'Add New' ).click();
		cy.get( '.frm-list-grid-layout #frm-form-templates-create-form' ).should( 'contain', 'Create a blank form' ).click();
		cy.get( '#frm_submit_side_top', { timeout: 5000 } ).should( 'contain', 'Save' ).click();
		cy.get( '#frm_new_form_name_input' ).type( 'Test Form' );
		cy.get( '#frm-save-form-name-button' ).should( 'contain', 'Save' ).click();

		cy.log( `Create a text field` );
		cy.get( `li[id="text"] a[title="Text"]` ).click( { force: true } );

		cy.log( 'Update form' );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Submit form and verify entry is stored' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();
		cy.get( '[id^="field_"]:not(.frm_verify)' ).filter( 'input, textarea' ).type( 'Entry is stored' );
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();
		cy.go( -2 );
		cy.get( '.frm_form_nav > :nth-child(4) > a' ).should( 'contain', 'Entries' ).click();
		cy.get( '.wrap > h2' ).should( 'contain', 'Form Entries' );
		cy.get( '.displaying-num' ).should( 'contain', '1 item' );

		cy.log( 'Verify column names in the entries forms page' );
		cy.get( 'th[id$="_id"] > a' ).should( 'contain', 'ID' );
		cy.get( 'th[id$="_item_key"] > a' ).should( 'contain', 'Entry Key' );
		cy.get( 'th' ).contains( 'a', 'Text' ).should( 'contain', 'Text' );
		cy.get( 'th[id$="_is_draft"] > a' ).should( 'contain', 'Entry Status' );
		cy.get( 'th[id$="_created_at"] > a' ).should( 'contain', 'Entry creation date' );
		cy.get( 'th[id$="_updated_at"] > a' ).should( 'contain', 'Entry update date' );
		cy.get( 'th[id$="_ip"] > a' ).should( 'contain', 'IP' );

		cy.get( 'a.row-title' ).should( 'exist' );
		cy.get( 'td[data-colname="Entry Key"]' ).invoke( 'text' ).should( 'match', /^[a-zA-Z0-9]+$/ );
		cy.get( "#the-list td[data-colname='Text']" ).should( 'contain', 'Entry is stored' );
		cy.get( '.frm-meta-tag' ).should( 'contain', 'Submitted' );
		cy.get( 'td[data-colname="Entry creation date"] abbr' )
			.invoke( 'text' )
			.should( 'match', /^[A-Za-z]+ \d{1,2}, \d{4} at \d{1,2}:\d{2} (am|pm)$/ );

		cy.get( 'td[data-colname="Entry update date"] abbr' )
			.invoke( 'text' )
			.should( 'match', /^[A-Za-z]+ \d{1,2}, \d{4} at \d{1,2}:\d{2} (am|pm)$/ );

		cy.get( 'td[data-colname="Entry creation date"] abbr' ).invoke( 'text' ).then( creationDate => {
			cy.get( 'td[data-colname="Entry update date"] abbr' ).invoke( 'text' ).should( 'equal', creationDate );
		} );
		cy.get( 'td[data-colname="IP"]' ).should( 'exist' );

		cy.log( 'Click on View' );
		cy.get( 'tr div.row-actions span.view a', { timeout: 5000 } )
			.should( 'contain', 'View' )
			.click( { force: true } );

		cy.url().should( 'include', 'frm_action=show&id=' );

		cy.get( '.hndle > :nth-child(1)' ).should( 'contain', 'Entry' );
		cy.get( '.frm-odd > th' ).should( 'contain', 'Text' );
		cy.get( '.frm-odd > td' ).should( 'contain', 'Entry is stored' );

		cy.get( '#frm-entry-show-empty-fields' ).should( 'contain', 'Show empty fields' );

		cy.log( 'Check for entry actions elements' );
		cy.get( '.frm_no_print > h3' ).should( 'contain', 'Entry Actions' );
		cy.get( '.frm_no_print > .inside > :nth-child(1)' ).should( 'contain', 'Delete Entry' );
		cy.get( '.frm_no_print > .inside > :nth-child(2)' ).should( 'contain', 'Print Entry' );
		cy.get( '.frm_no_print > .inside > :nth-child(3)' ).should( 'contain', 'Resend Emails' );
		cy.get( '.frm_no_print > .inside > :nth-child(4)' ).should( 'contain', 'Download as PDF' );
		cy.get( '.inside > :nth-child(5)' ).should( 'contain', 'Edit Entry' );

		cy.log( 'Verify for entry details' );
		cy.get( ':nth-child(2) > h3' ).should( 'contain', 'Entry Details' );
		cy.get( '#timestamp' )
			.invoke( 'text' )
			.should( 'match', /Submitted: [A-Za-z]{3} \d{1,2}, \d{4} at \d{1,2}:\d{2} (am|pm)/ );
		cy.get( ':nth-child(2) > .inside > :nth-child(2)' ).should( 'contain', 'Entry ID:' );
		cy.log( 'Extract and trim the value after "Entry Key:' );
		cy.get( ':nth-child(2) > .inside > :nth-child(3)' )
			.invoke( 'text' )
			.then( text => {
				const keyValue = text.split( ':' )[ 1 ].trim();
				expect( keyValue ).to.match( /^[a-zA-Z0-9]+$/ );
			} );

		cy.log( 'Verify for user information' );
		cy.get( ':nth-child(3) > h3' ).should( 'contain', 'User Information' );
		cy.get( ':nth-child(3) > .inside > :nth-child(1)' ).should( 'contain', 'Created by: admin' );
		cy.get( ':nth-child(3) > .inside > :nth-child(2)' ).should( 'contain', 'IP Address:' );
		cy.get( ':nth-child(3) > .inside > :nth-child(3)' ).should( 'contain', 'Browser/OS:' );

		cy.log( 'Delete entry' );
		cy.get( 'a[href*="frm_action=destroy"] span.frm_link_label' ).should( 'contain', 'Delete Entry' ).click();
		cy.get( '.frm-confirm-msg' ).should( 'contain', 'Permanently delete this entry?' );
		cy.get( '.frm-flex-box > .button-secondary' ).should( 'contain', 'Cancel' ).click();
		cy.get( 'a[href*="frm_action=destroy"] span.frm_link_label' ).should( 'contain', 'Delete Entry' ).click();
		cy.get( '#frm-confirmed-click' ).should( 'contain', 'Confirm' ).click();
		cy.log( 'Verify that entry has been deleted' );
		cy.get( '.frm_updated_message' ).should( 'contain', 'Entry was successfully deleted' );
		cy.get( '.frm_no_entries_header' ).should( 'contain', 'No Entries for form: Test Form' );

		cy.get( '.frm_form_nav > :nth-child(1) > a' ).should( 'contain', 'Build' ).click();
		cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click( { force: true } );
	} );

	afterEach( () => {
		cy.log( 'Teardown - Delete form' );
		cy.deleteForm();
	} );
} );
