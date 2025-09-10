describe( 'Fields in the form builder', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.createNewForm();
		cy.viewport( 1280, 720 );
	} );

	it( 'should create, duplicate a field from each type and delete them', () => {
		const createAndDuplicateField = ( fieldId, fieldType ) => {
			cy.log( `Create a ${ fieldType } field and duplicate it` );
			cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).click();
			cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 } ).click( { force: true } );
			cy.get( `li[data-ftype="${ fieldId }"] .frm_clone_field > span` ).should( 'contain', 'Duplicate' ).click( { force: true } );

			cy.get( `li[data-type="${ fieldId }"]` ).should( 'have.length', 2 );
			const originalField = cy.get( `li[data-type="${ fieldId }"]:first` );
			const duplicateField = cy.get( `li[data-type="${ fieldId }"]:last` );
			return { originalField, duplicateField };
		};

		const removeField = field => {
			field.within( () => {
				cy.get( '.frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use' ).click( { force: true } );
				cy.get( '.frm-dropdown-menu > :nth-child(1) > .frm_delete_field' ).should( 'contain', 'Delete' ).click( { force: true } );
			} );
			cy.get( '.postbox a[id="frm-confirmed-click"]' ).contains( 'Confirm' ).should( 'be.visible' ).click( { force: true } );
			cy.get( `li[data-type="${ field }"]` ).should( 'not.exist' );
		};

		cy.contains( '#the-list tr', 'Test Form' ).trigger( 'mouseover' ).then( $row => {
			cy.wrap( $row ).within( () => {
				cy.get( '.column-name .row-title' ).should( 'exist' ).and( 'be.visible' ).then( $elem => {
					cy.wrap( $elem ).click( { force: true } );
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
		const createField = ( fieldId, fieldType ) => {
			cy.log( `Create a ${ fieldType } field` );
			cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).click( { force: true } );
		};

		const renameField = ( fieldId, fieldType, fieldValue ) => {
			cy.log( `Rename a ${ fieldType } field` );
			cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 } ).click( { force: true } );
			cy.get( `li[data-ftype="${ fieldId }"] .frm_select_field > span` ).should( 'contain', 'Field Settings' ).click( { force: true } );
			cy.get( `div[id^="frm-single-settings-"] input[value="${ fieldValue }"]`, { timeout: 10000 } ).should( 'be.visible' ).clear( { force: true } ).type( `${ fieldType } Updated`, { force: true } );
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

		const createField = ( fieldId, fieldType ) => {
			cy.log( `Create a ${ fieldType } field` );
			cy.get( `li[id="${ fieldId }"] a[title="${ fieldType }"]` ).click( { force: true } );
		};

		const requiredField = ( fieldId, fieldType ) => {
			cy.log( `Set ${ fieldType } field as require` );
			cy.get( `li[data-ftype="${ fieldId }"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 } ).click( { force: true } );
			cy.get( `li[data-ftype="${ fieldId }"] .frm_select_field > span` ).should( 'contain', 'Field Settings' ).click( { force: true } );
			cy.get( 'input.frm_req_field[type="checkbox"]' ).check( { force: true } );
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
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click( { force: true } );

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
	} );

	it( 'should validate forms with javascript setting', () => {
		cy.openForm();
		cy.log( `Create a text field and set it as required` );
		cy.get( `li[id="text"] a[title="Text"]` ).click( { force: true } );
		cy.get( `li[data-ftype="text"] [id^="field_"][id$="_inner_container"] > .frm-field-action-icons > .dropdown > .frm_bstooltip > .frmsvg > use`, { timeout: 10000 } ).click( { force: true } );
		cy.get( `li[data-ftype="text"] .frm_select_field > span` ).should( 'contain', 'Field Settings' ).click( { force: true } );
		cy.get( '.frm_field_list div[id^="frm-single-settings-"] .frm_grid_container .frm-hide-empty input[type="checkbox"]', { timeout: 10000 } ).check( { force: true } );

		cy.log( 'Create a phone and email field' );
		cy.get( `li[id="email"] a[title="Email"]` ).click( { force: true } );
		cy.get( `li[id="phone"] a[title="Phone"]` ).click( { force: true } );

		cy.log( 'Update form' );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click( { force: true } );

		cy.log( "Enabling the 'Validate this form with javascript' setting" );
		cy.xpath( "//ul[@class='frm_form_nav']//a[contains(text(),'Settings')]" ).should( 'contain', 'Settings' ).click();
		cy.get( ':nth-child(3) > td > .frm_inline_block', { timeout: 5000 } ).should( 'contain', 'Validate this form with javascript' );
		cy.get( '#js_validate' ).click( { force: true } );
		cy.get( '#frm_submit_side_top' ).should( 'contain', 'Update' ).click( { force: true } );

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
	} );

	afterEach( () => {
		cy.log( 'Teardown - Save the form and delete it' );
		cy.get( "a[aria-label='Close']", { timeout: 5000 } ).click( { force: true } );
		cy.deleteForm();
	} );
} );
