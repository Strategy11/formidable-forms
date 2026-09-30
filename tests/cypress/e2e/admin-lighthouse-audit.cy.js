import { auditOptions, requireAuditBrowser } from '../../lighthouse/options';

describe( 'Lighthouse dashboard baseline', { retries: 0, responseTimeout: 120000, pageLoadTimeout: 120000, taskTimeout: 120000 }, () => {
	before( requireAuditBrowser );

	it( 'Audits the Formidable dashboard', () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable-dashboard' );
		cy.get( '#frm_top_bar' ).should( 'be.visible' );
		cy.url().then( url => cy.task( 'lighthouse', { url, opts: auditOptions } ) );
	} );
} );
