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
			// measures 1280x0 (formidable-forms#3399), same shape as the #js_validate race in
			// fieldsInFormBuilder-validation.cy.js. .scrollIntoView() first reliably clears it.
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
			// data-type holds the field's type slug (e.g. "text"), shared by the original and its
			// duplicate - not unique enough to prove *this* field is gone. data-fid is the field's
			// own database id, so capture it before deleting to assert against afterward.
			field.invoke( 'attr', 'data-fid' ).then( fid => {
				field.within( () => {
					// Same .frm-show-hover opacity gate as the toggle above - reveal it first.
					// Same #wpbody-content 1280x0 race as createAndDuplicateField above
					// (formidable-forms#3399) - .scrollIntoView() first reliably clears it.
					cy.get( '.frm-field-action-icons' )
						.invoke( 'css', 'opacity', 1 )
						.find( '.dropdown .frm-hover-icon .frmsvg' )
						.first()
						.scrollIntoView()
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

				cy.get( `li[data-fid="${ fid }"]` ).should( 'not.exist' );
			} );
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
