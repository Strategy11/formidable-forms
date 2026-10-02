export const auditOptions = {
	onlyCategories: [ 'performance', 'accessibility', 'best-practices', 'seo' ],
	disableStorageReset: true,
	formFactor: 'desktop',
	screenEmulation: {
		mobile: false,
		width: 1350,
		height: 940,
		deviceScaleFactor: 1,
		disabled: false,
	},
	output: [ 'html', 'json' ],
};

/**
 * Require a Chrome browser for Lighthouse remote debugging.
 */
export function requireAuditBrowser() {
	expect( [ 'Chrome', 'Chromium', 'Canary' ] ).to.include( Cypress.browser.displayName );
}
