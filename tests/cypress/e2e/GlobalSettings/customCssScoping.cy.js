/**
 * Tests that Formidable's global custom CSS is:
 *   - Applied unscoped on the frontend (affects the whole page as authored)
 *   - Scoped to .frm_forms in the admin area (does not bleed into WordPress UI)
 *
 * The mechanism: the admin style editor loads the CSS endpoint with ?frm_admin=1,
 * which wraps the custom CSS in @scope (.frm_forms) { ... }.  The frontend loads
 * the same endpoint without that parameter and receives the CSS unscoped.
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

		// Navigate to the Custom CSS tab
		cy.get( '.frm-category-tabs' ).contains( 'a', 'Custom CSS' ).click();

		cy.get( '#frm_custom_css_box' ).clear().type( TEST_CSS, { delay: 0 } );
		cy.get( '#frm-publishing .button-primary' ).click();
		cy.get( '.frm_updated_message, .notice-success' ).should( 'exist' );
	} );

	after( () => {
		cy.log( 'Teardown - clear the custom CSS' );
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable-settings' );
		cy.get( '.frm-category-tabs' ).contains( 'a', 'Custom CSS' ).click();
		cy.get( '#frm_custom_css_box' ).clear();
		cy.get( '#frm-publishing .button-primary' ).click();
	} );

	context( 'CSS endpoint output', () => {
		it( 'serves custom CSS unscoped on the frontend (no frm_admin param)', () => {
			cy.request( '/wp-admin/admin-ajax.php?action=frmpro_css' ).then( ( response ) => {
				expect( response.status ).to.eq( 200 );
				expect( response.headers[ 'content-type' ] ).to.include( 'text/css' );

				// The CSS rules should be present verbatim — not wrapped in @scope
				expect( response.body ).to.include( 'h1 { font-size: 100px !important; }' );
				expect( response.body ).not.to.include( '@scope' );
			} );
		} );

		it( 'serves custom CSS scoped to .frm_forms in the admin area (frm_admin=1)', () => {
			cy.request( '/wp-admin/admin-ajax.php?action=frmpro_css&frm_admin=1' ).then( ( response ) => {
				expect( response.status ).to.eq( 200 );
				expect( response.headers[ 'content-type' ] ).to.include( 'text/css' );

				// The CSS should be wrapped in @scope (.frm_forms) { ... }
				expect( response.body ).to.match( /@scope\s*\(\.frm_forms\)\s*\{/ );
				expect( response.body ).to.include( 'h1 { font-size: 100px !important; }' );
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

			cy.get( '#frm_form_key' ).invoke( 'val' ).then( ( formKey ) => {
				// The blank-page preview renders the form on a plain page
				cy.visit( `/wp-admin/admin-ajax.php?action=frm_forms_preview&form=${ formKey }` );

				cy.log( 'Verify the page has an h1 outside .frm_forms and the custom CSS affects it' );
				cy.get( 'body' ).then( ( $body ) => {
					// The preview page title h1 exists outside .frm_forms
					const $h1OutsideForm = $body.find( 'h1' ).not( '.frm_forms h1' );
					if ( $h1OutsideForm.length ) {
						cy.wrap( $h1OutsideForm.first() )
							.invoke( 'css', 'font-size' )
							.should( 'eq', '100px' );
					} else {
						cy.log( 'No h1 outside .frm_forms on this page — skipping outer-scope check' );
					}
				} );

				cy.log( 'Verify the h1 inside .frm_forms is also affected on the frontend' );
				cy.get( 'body' ).then( ( $body ) => {
					const $h1InsideForm = $body.find( '.frm_forms h1' );
					if ( $h1InsideForm.length ) {
						cy.wrap( $h1InsideForm.first() )
							.invoke( 'css', 'font-size' )
							.should( 'eq', '100px' );
					}
				} );
			} );

			cy.log( 'Teardown - delete the test form' );
			cy.visit( '/wp-admin/admin.php?page=formidable' );
			cy.deleteForm();
		} );

		it( 'does not apply custom CSS to h1 elements outside .frm_forms in the admin style editor', () => {
			cy.visit( '/wp-admin/admin.php?page=formidable-styles' );

			cy.log( 'Wait for the style editor CSS (loaded with frm_admin=1) to apply' );
			// The style editor stylesheet is loaded with frm_admin=1 so custom CSS is @scope'd
			cy.get( '#frm-styles-container, .frm_style_preview', { timeout: 5000 } ).should( 'exist' );

			cy.log( 'h1 elements in the WordPress admin UI (outside .frm_forms) should not be affected' );
			cy.get( 'h1' ).not( '.frm_forms h1' ).first()
				.invoke( 'css', 'font-size' )
				.should( 'not.eq', '100px' );

			cy.log( 'h1 elements inside .frm_forms (the style preview) should be affected' );
			cy.get( 'body' ).then( ( $body ) => {
				const $h1InsideForm = $body.find( '.frm_forms h1' );
				if ( $h1InsideForm.length ) {
					cy.wrap( $h1InsideForm.first() )
						.invoke( 'css', 'font-size' )
						.should( 'eq', '100px' );
				} else {
					cy.log( 'No h1 inside .frm_forms on this page — skipping inner-scope check' );
				}
			} );
		} );
	} );
} );
