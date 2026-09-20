describe( 'Fields in the form builder', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
	} );

	// Shared by the tests below - the sidebar's "Add Fields" tab is only one of two tabs
	// (the other, "Field Options", takes over after opening a field's settings), so a field
	// link here is only genuinely visible once that tab is active again - not a render-timing
	// race (a longer timeout never resolves it if "Field Options" is still showing).
	const createField = ( fieldId, fieldType ) => {
		cy.log( `Create a ${ fieldType } field` );
		// Plain #frm_insert_fields_tab is ambiguous - a second, hidden (mobile-dropdown) element
		// shares the same id, and a bare id selector can resolve to that one instead. Scope to the
		// real sidebar tab list (.frm-tabs-navs) to avoid it.
		cy.get( '.frm-tabs-navs #frm_insert_fields_tab' ).click();
		cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).should( 'be.visible' ).click();
	};

	it( 'should create, duplicate a field from each type and delete them', () => {
		const createAndDuplicateField = ( fieldId, fieldType ) => {
			cy.log( `Create a ${ fieldType } field and duplicate it` );
			cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).click();
			// .frm-field-action-icons is also .frm-show-hover (opacity: 0 by default; see
			// resources/scss/admin/components/sorting/_sorting-display.scss), only revealed on a
			// real CSS :hover of the field row or when the field is .selected - it's still genuinely
			// clickable underneath, so reveal it the same way the row-actions helpers in commands.js
			// do, instead of forcing through the opacity check.
			// A bare .should('be.visible') can time out here - #wpbody-content intermittently
			// measures 1280x0 (formidable-forms#3399), same shape as the #js_validate race below.
			// .scrollIntoView() first reliably clears it.
			cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons`, { timeout: 10000 } )
				.invoke( 'css', 'opacity', 1 )
				.find( '.dropdown > .frm_bstooltip > .frmsvg > use' )
				.first()
				.scrollIntoView()
				.should( 'be.visible' )
				.click();
			// The dropdown menu opens via a Bootstrap JS toggle (a real click, not hover-gated), so
			// once it's open the item is genuinely clickable - wait for it to be visible instead of
			// forcing through the open transition.
			cy.get( `li[data-ftype="${ fieldId }"] .frm_clone_field > span` ).should( 'be.visible' ).and( 'contain', 'Duplicate' ).click();

			cy.get( `li[data-type="${ fieldId }"]` ).should( 'have.length', 2 );
			const originalField = cy.get( `li[data-type="${ fieldId }"]:first` );
			const duplicateField = cy.get( `li[data-type="${ fieldId }"]:last` );
			return { originalField, duplicateField };
		};

		const removeField = field => {
			field.within( () => {
				// Same .frm-show-hover opacity gate as the toggle above - reveal it first.
				cy.get( '.frm-field-action-icons' )
					.invoke( 'css', 'opacity', 1 )
					.find( '.dropdown .frm-hover-icon .frmsvg' )
					.first()
					.should( 'be.visible' )
					.click();

				// The menu is open via the click above (not hover-gated), so wait for the item to
				// be visible instead of forcing through the open transition.
				cy.get( '.frm-dropdown-menu .frm_delete_field' )
					.should( 'be.visible' )
					.and( 'contain', 'Delete' )
					.click();
			} );

			// Plain cy.get() by id (an id is unique) rather than cy.get().contains() - the latter
			// can resolve to a narrower descendant node than the clickable link itself, which is
			// what forced force here. Plain cy.get() on this id works unforced elsewhere in the
			// suite.
			cy.get( '#frm-confirmed-click' )
				.should( 'be.visible' )
				.and( 'contain', 'Confirm' )
				.click();

			cy.get( `li[data-type="${ field }"]` ).should( 'not.exist' );
		};

		cy.contains( '#the-list tr', 'Test Form' ).trigger( 'mouseover' ).then( $row => {
			cy.wrap( $row ).within( () => {
				cy.get( '.column-name .row-title' ).should( 'exist' ).and( 'be.visible' ).then( $elem => {
					// Plain click - verified via document.elementFromPoint() that the link is the
					// topmost element at its own coordinates, not covered by anything. No force needed.
					cy.wrap( $elem ).click();
				} );
			} );
		} );

		cy.get( 'h1 > .frm_bstooltip' ).should( 'contain', 'Test Form' );
		cy.get( '.current_page' ).should( 'contain', 'Build' );

		cy.xpath( "//li[@class='frm-active']//a[@id='frm_insert_fields_tab']" ).should( 'contain', 'Add Fields' );

		cy.log( 'Create and duplicate fields for each type' );
		const fieldsToDelete = [
			createAndDuplicateField( 'text', 'Text' ),
			createAndDuplicateField( 'textarea', 'Paragraph' ),
			createAndDuplicateField( 'checkbox', 'Checkboxes' ),
			createAndDuplicateField( 'radio', 'Radio Buttons' ),
			createAndDuplicateField( 'select', 'Dropdown' ),
			createAndDuplicateField( 'email', 'Email' ),
			createAndDuplicateField( 'url', 'Website/URL' ),
			createAndDuplicateField( 'number', 'Number' ),
			createAndDuplicateField( 'name', 'Name' ),
			createAndDuplicateField( 'phone', 'Phone' ),
			createAndDuplicateField( 'html', 'HTML' ),
			createAndDuplicateField( 'hidden', 'Hidden' ),
			createAndDuplicateField( 'user_id', 'User ID' ),
			createAndDuplicateField( 'captcha', 'Captcha' ),
			createAndDuplicateField( 'credit_card', 'Payment' )
		];

		cy.log( 'Sequentially delete each field along with its duplicate' );
		fieldsToDelete.forEach( fields => {
			removeField( fields.originalField );
			removeField( fields.duplicateField );
		} );
	} );

	it( 'should rename a field from each type', () => {
		const renameField = ( fieldId, fieldType, fieldValue ) => {
			cy.log( `Rename a ${ fieldType } field` );
			// See the .frm-show-hover opacity note on the field-row "more options" toggle above.
			cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons`, { timeout: 10000 } )
				.invoke( 'css', 'opacity', 1 )
				.find( '.dropdown > .frm_bstooltip > .frmsvg > use' )
				.first()
				.should( 'be.visible' )
				.click();
			cy.get( `li[data-ftype="${ fieldId }"] .frm_select_field > span` ).should( 'be.visible' ).and( 'contain', 'Field Settings' ).click();
			// The settings panel opens via a bounded jQuery slideDown() (admin.js) - wait for the
			// input to be visible (Cypress retries until it has settled into the flow), then interact
			// normally.
			cy.get( `div[id^="frm-single-settings-"] input[value="${ fieldValue }"]`, { timeout: 10000 } ).should( 'be.visible' ).clear().type( `${ fieldType } Updated` );
		};

		cy.openForm();

		const fieldsToProcess = [
			{ fieldId: 'text', fieldType: 'Text', fieldValue: 'Text' },
			{ fieldId: 'textarea', fieldType: 'Paragraph', fieldValue: 'Paragraph' },
			{ fieldId: 'checkbox', fieldType: 'Checkboxes', fieldValue: 'Checkboxes' },
			{ fieldId: 'radio', fieldType: 'Radio Buttons', fieldValue: 'Radio Buttons' },
			{ fieldId: 'select', fieldType: 'Dropdown', fieldValue: 'Dropdown' },
			{ fieldId: 'email', fieldType: 'Email', fieldValue: 'Email' },
			{ fieldId: 'url', fieldType: 'Website/URL', fieldValue: 'Website/URL' },
			{ fieldId: 'number', fieldType: 'Number', fieldValue: 'Number' },
			{ fieldId: 'name', fieldType: 'Name', fieldValue: 'Name' },
			{ fieldId: 'phone', fieldType: 'Phone', fieldValue: 'Phone' },
			{ fieldId: 'html', fieldType: 'HTML', fieldValue: 'HTML' },
			{ fieldId: 'hidden', fieldType: 'Hidden', fieldValue: 'Hidden' },
			{ fieldId: 'user_id', fieldType: 'User ID', fieldValue: 'User ID' },
			{ fieldId: 'captcha', fieldType: 'Captcha', fieldValue: 'Captcha' },
			{ fieldId: 'credit_card', fieldType: 'Payment', fieldValue: 'Payment' }
		];

		fieldsToProcess.forEach( field => {
			createField( field.fieldId, field.fieldType );
			renameField( field.fieldId, field.fieldType, field.fieldValue );
		} );
	} );

	it( 'should set fields as required and validate them in frontend', () => {
		const fieldTypes = [ 'Text', 'Paragraph', 'Checkboxes', 'Radio Buttons', 'Dropdown', 'Email', 'Website/URL', 'Number', 'Name', 'Phone' ];

		const requiredField = ( fieldId, fieldType ) => {
			cy.log( `Set ${ fieldType } field as require` );
			// See the .frm-show-hover opacity note on the field-row "more options" toggle above.
			// A bare .should('be.visible') can time out here - #wpbody-content intermittently
			// measures 1280x0 (formidable-forms#3397), same shape as the #js_validate race below.
			// .scrollIntoView() first reliably clears it.
			cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons`, { timeout: 10000 } )
				.invoke( 'css', 'opacity', 1 )
				.find( '.dropdown > .frm_bstooltip > .frmsvg > use' )
				.first()
				.scrollIntoView()
				.should( 'be.visible' )
				.click();
			cy.get( `li[data-ftype="${ fieldId }"] .frm_select_field > span` ).should( 'be.visible' ).and( 'contain', 'Field Settings' ).click();
			// Same slideDown()-driven settings panel as elsewhere in this file - scope by the
			// field's own numeric id (from the field row's data-fid) so this matches only the
			// panel that was just opened, not every previously-opened (now hidden) one, and wait
			// for the slideDown to finish the same way the "rename" test above does.
			cy.get( `li[data-ftype="${ fieldId }"]` ).invoke( 'data', 'fid' ).then( fieldNumericId => {
				cy.get( `#frm-single-settings-${ fieldNumericId } input.frm_req_field[type="checkbox"]`, { timeout: 10000 } ).should( 'be.visible' ).check();
			} );
		};

		cy.openForm();

		const fieldsToSetAsRequired = [
			{ fieldId: 'text', fieldType: 'Text' },
			{ fieldId: 'textarea', fieldType: 'Paragraph' },
			{ fieldId: 'checkbox', fieldType: 'Checkboxes' },
			{ fieldId: 'radio', fieldType: 'Radio Buttons' },
			{ fieldId: 'select', fieldType: 'Dropdown' },
			{ fieldId: 'email', fieldType: 'Email' },
			{ fieldId: 'url', fieldType: 'Website/URL' },
			{ fieldId: 'number', fieldType: 'Number' },
			{ fieldId: 'name', fieldType: 'Name' },
			{ fieldId: 'phone', fieldType: 'Phone' }
		];

		fieldsToSetAsRequired.forEach( field => {
			createField( field.fieldId, field.fieldType );
			requiredField( field.fieldId, field.fieldType );
		} );

		cy.log( 'Update form' );
		// Plain #frm_submit_side_top "Update" click - no force needed here (see the identical
		// unforced click used for this same button elsewhere in the suite, e.g. formsSettings.cy.js).
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Click on Preview - Blank Page' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();

		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();
		cy.log( 'Check on error messages - Blank Page' );
		cy.get( '.frm_error_style' ).should( 'contain', 'There was a problem with your submission. Errors are marked below.' );

		fieldTypes.forEach( fieldType => {
			cy.contains( `[id^="frm_error_field_"]`, `${ fieldType } cannot be blank.` );
		} );

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( -2 );

		cy.log( 'Click on Preview - In Theme' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(2) > a' ).should( 'contain', 'In Theme' ).invoke( 'removeAttr', 'target' ).click();

		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();
		cy.log( 'Check on error messages - In Theme' );
		cy.get( '.frm_error_style' ).should( 'contain', 'There was a problem with your submission. Errors are marked below.' );

		fieldTypes.forEach( fieldType => {
			cy.contains( `[id^="frm_error_field_"]`, `${ fieldType } cannot be blank.` );
		} );

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( -2 );
		// Unlike the first cy.go(-2) above, nothing after this one asserts the builder page
		// actually finished loading before the test ends - the very next thing to run is
		// afterEach's own cy.visit(), which can otherwise race the still-settling history
		// navigation and land back on this edit page instead (formidable-forms#3397: seen
		// as afterEach's own "Test Form" lookup timing out on this page's non-list markup).
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' );
	} );

	it( 'should validate forms with javascript setting', () => {
		cy.openForm();
		cy.log( `Create a text field and set it as required` );
		// Plain, always-visible sidebar link - see the identical unforced click elsewhere in this file.
		cy.get( `li[id="text"] a[title="Text"]` ).should( 'be.visible' ).click();
		// See the .frm-show-hover opacity note on the field-row "more options" toggle elsewhere in
		// this file.
		cy.get( `li[data-ftype="text"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons`, { timeout: 10000 } )
			.invoke( 'css', 'opacity', 1 )
			.find( '.dropdown > .frm_bstooltip > .frmsvg > use' )
			.first()
			.should( 'be.visible' )
			.click();
		cy.get( `li[data-ftype="text"] .frm_select_field > span` ).should( 'be.visible' ).and( 'contain', 'Field Settings' ).click();
		// Same slideDown()-driven settings panel as elsewhere in this file. Target the "Required"
		// checkbox by its own class rather than "first checkbox in the panel" - that generic
		// selector can resolve to the Pro-gated "Unique fields" checkbox instead, which is
		// disabled in a Lite-only environment.
		cy.get( '.frm_field_list div[id^="frm-single-settings-"] .frm_req_field', { timeout: 10000 } ).should( 'be.visible' ).check();

		createField( 'email', 'Email' );
		createField( 'phone', 'Phone' );

		cy.log( 'Update form' );
		// Plain #frm_submit_side_top "Update" click - no force needed, see the note above.
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( "Enabling the 'Validate this form with javascript' setting" );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();
		cy.get( ':nth-child(3) > td > .frm_inline_block', { timeout: 5000 } ).should( 'contain', 'Validate this form with javascript' );
		// A bare .should('be.visible') times out here - #wpbody-content measures 1280x0 even
		// with a 10s timeout, so it doesn't self-resolve. .scrollIntoView() first reliably
		// clears it (verified red/green, 3 runs); exact mechanism unconfirmed.
		cy.get( '#js_validate' ).scrollIntoView().should( 'be.visible' ).click();
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click();

		cy.log( 'Click on Preview - Blank Page' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(1) > a' ).should( 'contain', 'On Blank Page' ).invoke( 'removeAttr', 'target' ).click();

		cy.log( 'Check error messages on real time - Blank Page' );

		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).first().type( 'Test' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 1 ).type( 'Test' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 2 ).type( 'Test' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 0 ).clear();
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 1 ).click();

		cy.get( `[id^="frm_error_field_"]` ).eq( 0 ).should( 'contain', `Text cannot be blank.` );
		cy.get( `[id^="frm_error_field_"]` ).eq( 1 ).should( 'contain', `Email is invalid` );
		cy.get( `[id^="frm_error_field_"]` ).eq( 2 ).should( 'contain', `Phone is invalid` );
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( 'back' );

		cy.log( 'Click on Preview - In Theme' );
		cy.get( '#frm-previewDrop', { timeout: 5000 } ).should( 'contain', 'Preview' ).click();
		cy.get( '.preview > .frm-dropdown-menu > :nth-child(2) > a' ).should( 'contain', 'In Theme' ).invoke( 'removeAttr', 'target' ).click();

		cy.log( 'Check error messages on real time - In Theme' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).first().type( 'Test' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 1 ).type( 'Test@gmail.com' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 2 ).type( '+12312312333' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 0 ).clear();
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 1 ).click();
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();

		cy.get( `[id^="frm_error_field_"]` ).eq( 0 ).should( 'contain', `Text cannot be blank.` );
		cy.get( `[id^="frm_error_field_"]` ).eq( 1 ).should( 'not.exist' );
		cy.get( `[id^="frm_error_field_"]` ).eq( 2 ).should( 'not.exist' );
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 1 ).clear();
		cy.get( '[id^="field_"]' ).filter( 'input, textarea' ).eq( 2 ).clear();
		cy.get( "button[type='submit']" ).should( 'contain', 'Submit' ).click();

		cy.log( 'Navigate back to the formidable form page' );
		cy.go( 'back' );
		// Same settle-before-teardown guard as the required-field test above.
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' );
	} );

	afterEach( () => {
		// Navigate to the list directly rather than clicking the builder's own "Close" link - a
		// test that failed mid-way can leave the builder in a state where that link isn't
		// reachable, which skips deleteForm() too and leaks this test's "Test Form" into whatever
		// spec runs next on the same wp-env (formidable-forms#3400: this leak was the actual cause
		// of an unrelated redirect test failing downstream in the same CI shard, not a product
		// bug - same class of fix as duplicateForm.cy.js's own afterEach hardening).
		cy.log( 'Teardown - delete the form' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
