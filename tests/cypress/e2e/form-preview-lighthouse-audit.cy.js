import { auditOptions, requireAuditBrowser } from '../../lighthouse/options';

describe( 'Lighthouse form preview baseline', {
	retries: 0,
	responseTimeout: 120000,
	pageLoadTimeout: 120000,
	taskTimeout: 120000,
}, () => {
	before( requireAuditBrowser );

	it( 'Audits the Contact Us form preview', () => {
		cy.login();
		cy.ensureContactUsFormExists();
		cy.visit( '/wp-admin/admin.php?page=formidable' );
		cy.contains( '#the-list tr', 'Contact Us' ).find( '.view a' )
			.invoke( 'attr', 'href' ).then( href => {
				expect( new URL( href ).searchParams.get( 'action' ) ).to.equal( 'frm_forms_preview' );
				// End admin view transitions before navigating into the standalone preview.
				cy.document().then( document => {
					document.querySelector( '#wp-view-transitions-admin-inline-css' )?.remove();
				} );
				cy.visit( href );
				cy.get( '.frm_forms form' ).should( 'be.visible' );
				cy.url().then( url => cy.task( 'lighthouse', { url, opts: auditOptions } ) );
			} );
	} );
} );
