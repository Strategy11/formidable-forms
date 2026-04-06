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
		cy.window().then( win => {
			const editor = win.frm_codemirror_box_wp_editor.codemirror;
			editor.setValue( TEST_CSS );
			editor.save();
		} );
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
		cy.window().then( win => {
			const editor = win.frm_codemirror_box_wp_editor.codemirror;
			editor.setValue( '' );
			editor.save();
		} );
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
		it( 'applies custom CSS to elements on a frontend page', () => {
			// Load the CSS endpoint as a stylesheet in a blank document and verify it applies.
			cy.request( '/wp-admin/admin-ajax.php?action=frmpro_css' ).then( response => {
				const css = response.body;

				cy.document().then( doc => {
					const style = doc.createElement( 'style' );
					style.textContent = css;
					doc.head.append( style );

					const container = doc.createElement( 'div' );
					container.innerHTML = '<h1 id="frm-css-scope-test">Test</h1>';
					doc.body.append( container );
				} );

				cy.log( 'Unscoped CSS must affect an h1 on the page' );
				cy.get( '#frm-css-scope-test' )
					.invoke( 'css', 'font-size' )
					.should( 'eq', '100px' );
			} );
		} );

		it( 'does not apply scoped custom CSS to elements outside .frm_forms', () => {
			// Load the scoped CSS endpoint as a stylesheet and verify scoping works.
			cy.request( '/wp-admin/admin-ajax.php?action=frmpro_css&frm_scope_custom_css=1' ).then( response => {
				const css = response.body;

				cy.document().then( doc => {
					const style = doc.createElement( 'style' );
					style.textContent = css;
					doc.head.append( style );

					// h1 outside .frm_forms should NOT be affected.
					const outerH1 = doc.createElement( 'h1' );
					outerH1.id = 'frm-css-scope-outer';
					doc.body.append( outerH1 );

					// h1 inside .frm_forms should be affected.
					const wrapper = doc.createElement( 'div' );
					wrapper.className = 'frm_forms';
					const innerH1 = doc.createElement( 'h1' );
					innerH1.id = 'frm-css-scope-inner';
					wrapper.append( innerH1 );
					doc.body.append( wrapper );
				} );

				cy.log( 'h1 outside .frm_forms must NOT be affected by scoped CSS' );
				cy.get( '#frm-css-scope-outer' )
					.invoke( 'css', 'font-size' )
					.should( 'not.eq', '100px' );

				cy.log( 'h1 inside .frm_forms must be affected by scoped CSS' );
				cy.get( '#frm-css-scope-inner' )
					.invoke( 'css', 'font-size' )
					.should( 'eq', '100px' );
			} );
		} );
	} );
} );
