/**
 * Tests that Formidable's global custom CSS is:
 *   - Applied unscoped on the frontend (affects the whole page as authored)
 *   - Scoped to .frm_forms in the admin area (does not bleed into WordPress UI)
 *
 * The mechanism: the admin style editor loads the CSS endpoint with
 * ?frm_scope_custom_css=1, which wraps the custom CSS in a @scope (.frm_forms)
 * block so it only applies inside form containers. The frontend loads the same
 * endpoint without that parameter and receives the CSS unscoped.
 */

const TEST_CSS = [
	'h1 { font-size: 100px !important; }',
	'h1 { margin-bottom: 50px !important; }',
	'h1 { margin-top: 50px !important; }',
	'h1 { padding-top: 50px !important; }',
	'h1 { padding-bottom: 50px !important; }',
	'h1 { height: 200px !important; }',
].join( '\n' );

describe( 'Custom CSS scoping', () => {
	before( () => {
		cy.login();

		cy.log( 'Save test CSS to Formidable global settings' );
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.get( '.frm-category-tabs' ).contains( 'a', 'Custom CSS' ).click();
		cy.get( '#frm_custom_css_box' )
			.clear()
			.type( TEST_CSS, { delay: 0, parseSpecialCharSequences: false } );
		cy.get( '#frm-publishing .button-primary' ).click();
		cy.get( '.frm_updated_message, .notice-success' ).should( 'exist' );
	} );

	// Re-login before each test: Cypress 13 testIsolation:true clears
	// cookies/storage between tests, so a single before() login is not enough.
	beforeEach( () => {
		cy.login();
	} );

	after( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.get( '.frm-category-tabs' ).contains( 'a', 'Custom CSS' ).click();
		cy.get( '#frm_custom_css_box' ).clear();
		cy.get( '#frm-publishing .button-primary' ).click();
	} );

	context( 'CSS endpoint output', () => {
		it( 'serves custom CSS unscoped on the frontend (no frm_admin param)', () => {
			cy.request( '/wp-admin/admin-ajax.php?action=frmpro_css' ).then( response => {
				expect( response.status ).to.eq( 200 );
				expect( response.headers[ 'content-type' ] ).to.include( 'text/css' );

				// The CSS rules should be present verbatim, not wrapped in @scope
				expect( response.body ).to.include( 'h1 { font-size: 100px !important; }' );
				expect( response.body ).not.to.include( '@scope' );
			} );
		} );

		it( 'serves custom CSS scoped to .frm_forms in the admin area (frm_scope_custom_css=1)', () => {
			cy.request( '/wp-admin/admin-ajax.php?action=frmpro_css&frm_scope_custom_css=1' ).then( response => {
				expect( response.status ).to.eq( 200 );
				expect( response.headers[ 'content-type' ] ).to.include( 'text/css' );

				// Custom CSS should be wrapped in @scope (.frm_forms)
				expect( response.body ).to.include( '@scope (.frm_forms)' );
				expect( response.body ).to.include( 'font-size: 100px !important' );
			} );
		} );
	} );

	context( 'Browser-level style application', () => {
		it( 'applies custom CSS to h1 elements outside .frm_forms on the frontend', () => {
			cy.log( 'Create a form and visit its preview page' );
			cy.visit( '/wp-admin/admin.php?page=formidable-form-templates' );
			cy.get( '#frm-form-templates-create-form' ).click();

			cy.get( '#frm_submit_side_top', { timeout: 5000 } ).click();
			cy.get( '#frm_new_form_name_input' ).type( 'CSS Scoping Test Form' );
			cy.get( '#frm-save-form-name-button' ).click();
			cy.get( "a[aria-label='Close']", { timeout: 7000 } ).click();

			cy.get( '#frm_form_key' ).invoke( 'val' ).then( formKey => {
				cy.visit( `/wp-admin/admin-ajax.php?action=frm_forms_preview&form=${ formKey }` );

				// Inject a stable h1 outside .frm_forms so the assertion always has a target.
				cy.document().then( doc => {
					const h1 = doc.createElement( 'h1' );
					h1.id = 'frm-css-scope-test';
					doc.body.insertBefore( h1, doc.body.firstChild );
				} );

				cy.log( 'Unscoped CSS must affect an h1 outside .frm_forms' );
				cy.get( '#frm-css-scope-test' )
					.invoke( 'css', 'font-size' )
					.should( 'eq', '100px' );
			} );

			cy.log( 'Teardown - delete the test form' );
			cy.visit( '/wp-admin/admin.php?page=formidable' );
			cy.deleteForm();
		} );

		it( 'does not apply custom CSS to h1 elements outside .frm_forms in the admin style editor', () => {
			cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
			cy.get( '#frm-styles-container, .frm_style_preview', { timeout: 5000 } ).should( 'exist' );

			cy.log( 'h1 in the WordPress admin UI (outside .frm_forms) must not be affected' );
			// WP admin always renders a page-title h1 with .wp-heading-inline.
			cy.get( '.wp-heading-inline' ).first()
				.should( 'exist' )
				.invoke( 'css', 'font-size' )
				.should( 'not.eq', '100px' );

			cy.log( 'h1 inside .frm_forms must be affected by the scoped rule' );
			// Inject a stable h1 into the form preview container so the assertion
			// always has a target regardless of the sample form's content.
			cy.get( '.frm_forms' ).first().should( 'exist' ).then( $form => {
				const h1 = $form[ 0 ].ownerDocument.createElement( 'h1' );
				h1.id = 'frm-css-scope-inner-test';
				$form[ 0 ].prepend( h1 );
			} );

			cy.get( '#frm-css-scope-inner-test' )
				.invoke( 'css', 'font-size' )
				.should( 'eq', '100px' );
		} );
	} );
} );
