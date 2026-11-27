describe( 'Forms page', () => {
	const origin = Cypress.config( 'baseUrl' );
	const formTitle = 'Test Form';

	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.dismissInboxBanners();
		cy.createNewForm( formTitle );
		cy.viewport( 1280, 720 );
	} );

	it( 'should validate all data in list view', () => {
		cy.log( 'Validate all header data' );
		cy.log( 'Validate the upgrade link' );

		cy.get( 'body' ).then( $body => {
			if ( $body.find( '#frm_sale_banner' ).length ) {
				cy.get( '#frm_sale_banner' )
					.should( 'have.attr', 'data-url' )
					.and( 'match', /^https?:\/\// );
				cy.get( '#frm_sale_banner a:not(.dismiss)' ).should( 'be.visible' ).and( 'not.be.empty' );
				return;
			}

			cy.get( '.frm-upgrade-bar .frm-upgrade-bar-inner > a' )
				.should( 'be.visible' )
				.and( 'have.attr', 'href' )
				.and( 'match', /^https?:\/\// );
		} );

		cy.log( 'Navigate back to the original page' );
		cy.visit( '/wp-admin/admin.php?page=formidable' );

		cy.log( 'Validate the header logo link' );
		cy.get( 'a.frm-header-logo' )
			.should( 'have.attr', 'href', `${ origin }/wp-admin/admin.php?page=formidable` )
			.click();

		cy.log( 'Validate the URL after clicking the header logo' );
		cy.url().should( 'eq', `${ origin }/wp-admin/admin.php?page=formidable` );

		cy.log( 'Validate other header elements' );
		cy.get( 'h1' ).should( 'contain', 'Forms' );
		cy.get( '#frm-publishing > .frm-button-secondary' ).should( 'contain', 'Import' );
		cy.get( '#frm-publishing > .button-primary' ).should( 'contain', 'Add New' );

		cy.log( 'Validate all other elements shown in forms page' );
		cy.get( '.published > .current' ).should( 'contain', 'My Forms' );
		cy.get( '.subsubsub > .trash > a' ).should( 'contain', 'Trash' );
		cy.get( '#entry-search-input' ).should( 'exist' );
		cy.get( '#search-submit' ).should( 'contain', 'Search' );
		cy.get( '#bulk-action-selector-top' ).should( 'contain', 'Bulk Actions' );
		cy.get( '#doaction' ).should( 'contain', 'Apply' );

		cy.log( 'Verify existence of the select all checkbox' );
		cy.get( '#cb-select-all-1' ).should( 'exist' );

		cy.log( 'Verify column names in the forms page' );
		cy.get( '#name > a' ).should( 'contain', 'Form Title' );
		cy.get( '#entries' ).should( 'contain', 'Entries' );
		cy.get( '#id > a > :nth-child(1)' ).should( 'contain', 'ID' );
		cy.get( '#form_key > a > :nth-child(1)' ).should( 'contain', 'Key' );
		cy.get( '#created_at > a > :nth-child(1)' ).should( 'contain', 'Date' );

		cy.log( 'Verify existence of a single row select checkbox' );
		cy.get( '[id^="cb-item-action-"]' ).should( 'exist' );

		cy.log( 'Verify list view data of the created form' );
		cy.get( '.id' ).should( 'exist' );
		cy.get( `[id^="item-action-"] > .name > strong > .row-title:contains("${ formTitle }")` )
			.parents( '[id^="item-action-"]' )
			.within( () => {
				cy.get( '.entries > a' ).should( 'contain', '0' );
				cy.get( '.name > strong > .row-title' ).should( 'contain', formTitle );
				cy.get( '.form_key' ).should( 'contain', 'test-form' );

				cy.getCurrentFormattedDate().then( formattedDate => {
					cy.log( 'Formatted Date: ', formattedDate );

					cy.log( 'Find the element that displays the date and get its text content' );
					cy.get( '.created_at > abbr' )
						.invoke( 'text' )
						.then( dateText => {
							const dateMatch = dateText.match( /\d{4}\/\d{2}\/\d{2}/ );
							const displayedDate = dateMatch ? dateMatch[ 0 ] : '';
							expect( displayedDate ).to.equal( formattedDate );
						} );
				} );
			} );
	} );

	afterEach( () => {
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.deleteForm();
	} );
} );
