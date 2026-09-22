describe( 'Style builder labels focus their visible/interactive control', () => {
	beforeEach( () => {
		cy.login();
		cy.viewport( 1280, 1600 );
	} );

	it( 'Clicking a color picker\'s own label focuses the visible swatch button, not the hidden text input', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );

		// "Primary" in Quick Settings - the label wraps a color picker whose original text
		// input gets hidden by wpColorPicker() in favor of a `.wp-color-result` button.
		// style.js repoints the label's `for` to `{id}_visible` once wpColorPicker() inits,
		// which happens before this query runs - so the selector must target the post-init id.
		cy.get( 'label[for="frm_style_qsettings_submit_bg_color_visible"]' ).click();
		cy.get( '#frm_style_qsettings_submit_bg_color' ).should( 'not.be.visible' );
		cy.focused().should( 'have.class', 'wp-color-result' );
	} );

	it( 'Clicking a single-value slider\'s own label focuses the visible number input, not the hidden real input', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#general-style' ).should( 'have.class', 'open' );

		// "Border Width" lives directly in the General section, open by default.
		cy.get( 'label[for="frm_fieldset-value"]' ).click();
		cy.get( '#frm_fieldset' ).should( 'not.be.visible' );
		cy.focused().should( 'have.id', 'frm_fieldset-value' );
	} );
} );
