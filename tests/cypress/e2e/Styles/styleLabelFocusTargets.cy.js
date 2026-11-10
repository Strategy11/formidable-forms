describe( 'Style builder labels focus their visible/interactive control', () => {
	// The Styles page is slow to render on a cold CI run, longer than the 4s default.
	const STYLES_PAGE_TIMEOUT = 10000;

	beforeEach( () => {
		cy.login();
		cy.viewport( 1280, 1600 );
	} );

	it( 'Clicking a color picker\'s own label focuses the visible swatch button, not the hidden text input', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );

		// The original input lives inside the closed picker. Its label targets the swatch.
		cy.get( '#frm_style_qsettings_submit_bg_color', { timeout: STYLES_PAGE_TIMEOUT } ).should( 'not.be.visible' );
		cy.get( 'label[for="frm_style_qsettings_submit_bg_color_visible"]' ).click();
		cy.focused().should( 'have.class', 'frm-block-color-toggle' );
	} );

	it( 'Color pickers remain visible outside the scrolling settings panel', () => {
		cy.viewport( 1280, 720 );
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( 'button[aria-label="Form Messages"]', { timeout: STYLES_PAGE_TIMEOUT } ).click();
		cy.get( '#frm_success_bg_color_visible' ).scrollIntoView().click();
		cy.get( '#frm_success_bg_color_picker' ).should( 'be.visible' ).then( panel => {
			const [ element ] = panel;
			const bounds = element.getBoundingClientRect();
			const doc = element.ownerDocument;
			const toolbarBottom = doc.getElementById( 'wpadminbar' ).getBoundingClientRect().bottom;
			expect( bounds.top ).to.be.at.least( toolbarBottom );
			expect( bounds.bottom ).to.be.at.most( doc.defaultView.innerHeight );
			expect( element.contains( doc.elementFromPoint( bounds.left + 20, bounds.top + 20 ) ) ).to.equal( true );
			expect( element.contains( doc.elementFromPoint( bounds.left + 20, bounds.bottom - 20 ) ) ).to.equal( true );
		} );
		cy.get( '#frm_success_bg_color_picker' ).find( 'select, [role="combobox"][aria-label="Color format"]' ).then( control => {
			if ( control[ 0 ].tagName === 'SELECT' ) {
				cy.wrap( control ).select( 'RGB' ).should( 'have.value', 'rgb' );
				return;
			}
			cy.wrap( control ).click();
			cy.get( '#frm_success_bg_color_picker' ).should( 'not.have.attr', 'hidden' );
			cy.get( '[role="listbox"]' ).should( 'be.visible' ).contains( '[role="option"]', 'RGB' ).click();
			cy.wrap( control ).should( 'contain.text', 'RGB' );
		} );
		cy.get( '#frm_success_bg_color_picker' ).should( 'be.visible' );
		cy.get( '#frm_success_bg_color' ).then( input => {
			expect( input[ 0 ].form.id ).to.equal( 'frm_styling_form' );
		} );
		cy.get( '#frm_success_bg_color_picker .frm-block-color-clear' ).focus().type( '{esc}' );
		cy.get( '#frm_success_bg_color_picker' ).should( 'not.exist' );
		cy.focused().should( 'have.id', 'frm_success_bg_color_visible' );
		cy.get( '#frm_success_bg_color_visible' ).click();
		cy.get( 'body' ).trigger( 'pointerdown', { eventConstructor: 'PointerEvent' } );
		cy.get( '#frm_success_bg_color_picker' ).should( 'not.exist' );
	} );

	it( 'Clicking a single-value slider\'s own label focuses the visible number input, not the hidden real input', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#general-style', { timeout: STYLES_PAGE_TIMEOUT } ).should( 'have.class', 'open' );

		// "Border Width" lives directly in the General section, open by default.
		cy.get( 'label[for="frm_fieldset-value"]' ).click();
		cy.get( '#frm_fieldset' ).should( 'not.be.visible' );
		cy.focused().should( 'have.id', 'frm_fieldset-value' );
	} );

	it( 'A slider label outside Width/Height also drops its focus target once its unit is cleared to "Not set"', () => {
		cy.intercept( 'POST', '**/admin-ajax.php', req => {
			if ( req.body?.includes( 'action=frm_change_styling' ) ) {
				req.alias = 'changeStyling';
			}
		} );

		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#general-style', { timeout: STYLES_PAGE_TIMEOUT } ).should( 'have.class', 'open' );

		cy.log( '"Border Width" (frm_fieldset) ships with a measured default, so the label starts wired up' );
		cy.get( '[data-slider-label-for="frm_fieldset-value"]' ).should( 'have.attr', 'for', 'frm_fieldset-value' );

		cy.log( 'Clearing the unit to "Not set" disables the value input and drops the label\'s focus target' );
		cy.get( '#frm_fieldset' ).closest( '.frm-slider-component' ).find( '.frm-slider-value select' ).select( '' );
		cy.wait( '@changeStyling', { timeout: 10000 } );
		cy.get( '#frm_fieldset-value' ).should( 'be.disabled' );
		cy.get( '[data-slider-label-for="frm_fieldset-value"]' ).should( 'not.have.attr', 'for' );

		cy.log( 'Choosing a measured unit again re-associates the label with the now-enabled input' );
		cy.get( '#frm_fieldset' ).closest( '.frm-slider-component' ).find( '.frm-slider-value select' ).select( 'px' );
		cy.wait( '@changeStyling', { timeout: 10000 } );
		cy.get( '[data-slider-label-for="frm_fieldset-value"]' ).should( 'have.attr', 'for', 'frm_fieldset-value' );
		cy.get( '[data-slider-label-for="frm_fieldset-value"]' ).click();
		cy.focused().should( 'have.id', 'frm_fieldset-value' );
	} );

	it( 'A "Width"/"Height" label targets nothing while its unit defaults to "auto", and gets a working focus target once a measured unit is chosen', () => {
		cy.intercept( 'POST', '**/admin-ajax.php', req => {
			if ( req.body?.includes( 'action=frm_change_styling' ) ) {
				req.alias = 'changeStyling';
			}
		} );

		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#buttons-style button[aria-label="Buttons"]', { timeout: STYLES_PAGE_TIMEOUT } ).click();
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
	it( 'Background labels focus their color swatch', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( 'label[for="frm_fieldset_bg_color_visible"]', { timeout: STYLES_PAGE_TIMEOUT } ).click();
		cy.focused().should( 'have.id', 'frm_fieldset_bg_color_visible' );
	} );

	it( 'Radio headings focus the selected option without changing it, and toggle headings focus the switch', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#general-style .frm-align-component label[for="frm_form_align-right"]', { timeout: STYLES_PAGE_TIMEOUT } ).click();
		cy.contains( '#general-style .frm-style-item-heading', 'Alignment' ).click();
		cy.focused().prev().should( 'have.value', 'right' ).and( 'be.checked' );
		cy.get( '#general-style .frm-align-component input[value="right"]' ).should( 'be.checked' );
		cy.get( 'label[for="frm_important_style"]' ).click();
		cy.focused().should( 'have.attr', 'role', 'switch' ).and( 'have.attr', 'aria-label', 'Override Theme' );
	} );

	it( 'Required Indicator Weight focuses its own dropdown', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#field-labels-style button[aria-label="Field Labels"]', { timeout: STYLES_PAGE_TIMEOUT } ).click();
		cy.get( 'label[for="frm_weight"]' ).click();
		cy.focused().should( 'have.id', 'frm_weight' );
		cy.contains( '#field-labels-style .frm-tabs-navs li', 'Required Indicator' ).click();
		cy.get( 'label[for="frm_required_weight"]' ).click();
		cy.focused().should( 'have.id', 'frm_required_weight' );
		cy.get( '[id="frm_required_weight"]' ).should( 'have.length', 1 );
	} );
} );
