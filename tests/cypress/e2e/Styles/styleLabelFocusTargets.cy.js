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
		// Confirm the original input is hidden *before* interacting with it - clicking the
		// swatch button intentionally reveals it again afterward as WP core's own manual hex
		// entry field (color-picker.js `open()` un-hides `.wp-picker-input-wrap`), so asserting
		// it stays hidden post-click would fail against WP's own by-design behavior.
		cy.get( '#frm_style_qsettings_submit_bg_color' ).should( 'not.be.visible' );
		cy.get( 'label[for="frm_style_qsettings_submit_bg_color_visible"]' ).click();
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

	it( 'A "Width"/"Height" label targets nothing while its unit defaults to "auto", and gets a working focus target once a measured unit is chosen', () => {
		cy.intercept( 'POST', '**/admin-ajax.php', req => {
			if ( req.body?.includes( 'action=frm_change_styling' ) ) {
				req.alias = 'changeStyling';
			}
		} );

		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#buttons-style button[aria-label="Buttons"]' ).click();
		cy.get( '#frm_style_section_buttons-style' ).should( 'be.visible' );

		cy.log( 'Width defaults to "auto" out of the box (FrmStyle.php), rendering the value input disabled' );
		cy.get( '#frm_submit_width' ).should( 'have.value', 'auto' );
		cy.get( '#frm_submit_width-value' ).should( 'be.disabled' );
		cy.get( '[data-slider-label-for="frm_submit_width-value"]' ).should( 'not.have.attr', 'for' );

		cy.log( 'Choosing a measured unit re-associates the label with the now-enabled input' );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( '.frm-slider-value select' ).select( 'px' );
		cy.wait( '@changeStyling', { timeout: 10000 } );
		cy.get( '[data-slider-label-for="frm_submit_width-value"]' ).should( 'have.attr', 'for', 'frm_submit_width-value' );
		cy.get( '[data-slider-label-for="frm_submit_width-value"]' ).click();
		cy.focused().should( 'have.id', 'frm_submit_width-value' );

		cy.log( 'Switching back to "auto" removes the focus target again, live, not just on the next server render' );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( '.frm-slider-value select' ).select( 'auto' );
		cy.wait( '@changeStyling', { timeout: 10000 } );
		cy.get( '[data-slider-label-for="frm_submit_width-value"]' ).should( 'not.have.attr', 'for' );
	} );
} );
