describe( 'Slider style component', () => {
	beforeEach( () => {
		cy.login();
		cy.viewport( 1280, 1600 );
		// Every change the tests below make gets committed to the hidden input by a "change"
		// handler that runs immediately - but the style editor also fires an admin-ajax
		// `frm_change_styling` live-preview request on the same change, and re-normalizes the
		// hidden input's value (e.g. adding back the unit) once that request resolves. Firing
		// several slider changes back to back without waiting for that request backs its queue
		// up, so the value/disabled-state assertions that follow can outlast Cypress's default
		// retry window - not because the value is wrong, but because it hasn't settled yet.
		// The action lives in the POST body, not the URL, so match on the body rather than a URL
		// pattern.
		cy.intercept( 'POST', '**/admin-ajax.php', req => {
			if ( req.body?.includes( 'action=frm_change_styling' ) ) {
				req.alias = 'changeStyling';
			}
		} );
	} );

	const SLIDER_TIMEOUT = 10000;
	// Wait for the live-preview sync request to actually round-trip, rather than a fixed delay.
	// Call this after every blur()/select() on a slider.
	const settleSliderChange = () => cy.wait( '@changeStyling', { timeout: SLIDER_TIMEOUT } );

	/**
	 * Locates a single (non multi-value) slider's range input, visible text input, unit select
	 * and hidden "real" input from the id on its hidden input.
	 *
	 * @param {string} hiddenId - The id of the slider's hidden input, e.g. '#frm_fieldset'.
	 * @return {Object} References to the range, text, select and hidden inputs.
	 */
	const getSingleSlider = hiddenId => {
		const wrapper = () => cy.get( hiddenId, { timeout: SLIDER_TIMEOUT } ).closest( '.frm-slider-component' );

		// Functions, not pre-built chains: a chain saved to a variable and asserted on much later
		// (after other commands have queued in between, e.g. a select()/blur() and a settle wait)
		// does not reliably re-run its full cy.get().closest().find() from the current DOM on
		// retry - callers should invoke these at the point of use instead.
		return {
			range: () => wrapper().find( 'input[type="range"]' ),
			text: () => wrapper().find( '.frm-slider-value input[type="text"]' ),
			select: () => wrapper().find( '.frm-slider-value select' ),
			hidden: () => cy.get( hiddenId, { timeout: SLIDER_TIMEOUT } )
		};
	};

	it( 'Can drag a single slider with the text input, and the change persists after saving', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.get( '#general-style' ).should( 'have.class', 'open' );

		// Border Width lives directly in the General section, open by default.
		cy.get( '#frm_fieldset' ).closest( '.frm-slider-component' ).within( () => {
			cy.get( 'input[type="range"]' ).should( 'have.attr', 'aria-label', 'Field value' );

			// Typing a value into the text box should drive the range input and its aria-valuetext.
			cy.get( '.frm-slider-value input[type="text"]' ).clear().type( '12' ).blur();
			cy.get( 'input[type="range"]' ).should( 'have.value', '12' );
			cy.get( 'input[type="range"]' ).should( 'have.attr', 'aria-valuetext', '12px' );
		} );

		// The hidden input is the one that is actually saved with the style.
		cy.get( '#frm_fieldset' ).should( 'have.value', '12px' );

		cy.log( 'Save the style and confirm the value survived the round trip through the server' );
		// #frm_submit_side_top is a .frm-button-primary, whose CSS `:active` scale-transform
		// (see resources/scss/admin/components/button/_button.scss) starts moving the element the
		// instant the click's mousedown lands, which trips Cypress's own animation-stability check
		// (default animationDistanceThreshold: 5px) even though the button is visible, enabled and
		// unobstructed - verified independently via Playwright, which hits the same "not stable"
		// failure on this exact button (see the dev-site skill notes). Raise the threshold instead
		// of forcing, so every other actionability check still runs.
		cy.get( '#frm_submit_side_top' ).click( { animationDistanceThreshold: 100 } );
		cy.get( '#frm_fieldset' ).should( 'have.value', '12px' );
		cy.get( '#frm_fieldset' ).closest( '.frm-slider-component' ).find( 'input[type="range"]' ).should( 'have.value', '12' );
	} );

	it( 'Rejects an out of range or negative value typed into the text input', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		const slider = getSingleSlider( '#frm_fieldset' );

		// Max value for Border Width is 25 - start from a known, valid value.
		slider.text().clear().type( '10' ).blur();
		settleSliderChange();
		slider.hidden().should( 'have.value', '10px' );

		cy.log( 'A value above the max is rejected and the text box resyncs to the current range value' );
		slider.text().clear().type( '999' ).blur();
		settleSliderChange();
		slider.text().should( 'have.value', '10' );
		slider.hidden().should( 'have.value', '10px' );

		cy.log( 'A negative value is rejected the same way' );
		slider.text().clear().type( '-5' ).blur();
		settleSliderChange();
		slider.text().should( 'have.value', '10' );
		// A direct cy.get() (rather than through the slider.hidden() helper) so the assertion is
		// visible to static analysis - functionally identical to the checks above.
		cy.get( '#frm_fieldset', { timeout: SLIDER_TIMEOUT } ).should( 'have.value', '10px' );
	} );

	it( 'Expands a multi-value slider group and lets an individual slider be adjusted independently', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		const wrapper = () => cy.get( '#frm_fieldset_padding' ).parent();

		wrapper().find( '.frm-slider-component[data-type="top"]' ).should( 'have.class', 'frm_hidden' );
		wrapper().find( '.frm-slider-component[data-type="bottom"]' ).should( 'have.class', 'frm_hidden' );

		cy.log( 'Expand the vertical group to reveal the independent Top and Bottom sliders' );
		wrapper().find( '.frm-slider-component[data-type="vertical"] .frmsvg' ).click();
		wrapper().find( '.frm-slider-component[data-type="top"]' ).should( 'not.have.class', 'frm_hidden' );
		wrapper().find( '.frm-slider-component[data-type="bottom"]' ).should( 'not.have.class', 'frm_hidden' );

		cy.log( 'Adjusting the Bottom slider only updates the bottom position of the combined value' );
		wrapper().find( '.frm-slider-component[data-type="bottom"]' ).within( () => {
			cy.get( 'input[type="range"]' ).should( 'have.attr', 'aria-label', 'Bottom value' );
			cy.get( '.frm-slider-value input[type="text"]' ).clear().type( '22' ).blur();
			cy.get( 'input[type="range"]' ).should( 'have.value', '22' );
		} );

		// Padding order is "top right bottom left" - Bottom is the third (index 2) space-separated value.
		cy.get( '#frm_fieldset_padding' ).invoke( 'val' ).then( value => {
			expect( value.split( ' ' )[ 2 ] ).to.eq( '22px' );
		} );

		cy.log( 'Save the style and confirm the combined value survived the round trip through the server' );
		// Same click-triggered :active transform as above - raise the threshold instead of forcing.
		cy.get( '#frm_submit_side_top' ).click( { animationDistanceThreshold: 100 } );
		cy.get( '#frm_fieldset_padding' ).invoke( 'val' ).then( value => {
			expect( value.split( ' ' )[ 2 ] ).to.eq( '22px' );
		} );
	} );

	it( 'Switches a slider between the "auto" keyword and a measured unit, disabling the range for "auto"', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );

		cy.log( 'Open the Buttons section, collapsed by default' );
		cy.get( '#buttons-style button[aria-label="Buttons"]' ).click();
		cy.get( '#frm_style_section_buttons-style' ).should( 'be.visible' );

		const slider = getSingleSlider( '#frm_submit_width' );

		cy.log( 'Force the unit to "auto" and confirm the range and text box are both disabled, with no duplicated "auto" text' );
		slider.select().select( 'auto' );
		// Wait for the wrapper's own disabled-state class before inspecting its children -
		// applying "auto" disables the range/text inputs via the same handler that adds this
		// class, so this is a real signal that handler has run rather than an arbitrary pause.
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).should( 'have.class', 'frm-disabled' );
		slider.range().should( 'be.disabled' );
		slider.range().should( 'have.attr', 'aria-valuetext', 'auto' );
		slider.text().should( 'be.disabled' ).and( 'have.value', '' );
		slider.hidden().should( 'have.value', 'auto' );

		cy.log( 'Switching to a measured unit re-enables both the range and the text box' );
		slider.select().select( 'px' );
		slider.range().should( 'not.be.disabled' );
		slider.text().should( 'not.be.disabled' );
		slider.hidden().invoke( 'val' ).should( 'match', /^\d+px$/ );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).should( 'not.have.class', 'frm-disabled' );

		cy.log( 'The numeric value survives a save' );
		// Same click-triggered :active transform as above - raise the threshold instead of forcing.
		cy.get( '#frm_submit_side_top' ).click( { animationDistanceThreshold: 100 } );
		cy.get( '#frm_submit_width' ).invoke( 'val' ).should( 'match', /^\d+px$/ );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( 'input[type="range"]' ).should( 'not.be.disabled' );

		cy.log( 'Switching back to "auto" and saving persists the disabled, blank, unmeasured state too' );
		cy.get( '#buttons-style button[aria-label="Buttons"]' ).click();
		cy.get( '#frm_style_section_buttons-style' ).should( 'be.visible' );
		getSingleSlider( '#frm_submit_width' ).select().select( 'auto' );
		// Same click-triggered :active transform as above - raise the threshold instead of forcing.
		cy.get( '#frm_submit_side_top' ).click( { animationDistanceThreshold: 100 } );
		cy.get( '#frm_submit_width' ).should( 'have.value', 'auto' );

		cy.log( 'Reloading confirms the disabled, blank state is server-rendered on the very first paint, not just applied live by JS' );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).should( 'have.class', 'frm-disabled' );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( 'input[type="range"]' ).should( 'be.disabled' );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( '.frm-slider-value input[type="text"]' ).should( 'be.disabled' ).and( 'have.value', '' );
	} );

	it( 'Clearing a dependency-updater slider (Quick Settings) propagates the unset value to the real field and persists it', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );

		// Quick Settings sliders have no name of their own - they write into another field elsewhere
		// on the page via a "will-change" propagation, e.g. Corner Radius here targets border_radius.
		const realInput = () => cy.get( 'input[name="frm_style_setting[post_content][border_radius]"]' );
		const quickSettingsSlider = () => cy.get( '[data-will-change*="border_radius"]' );

		cy.log( 'Start from a known, measured value' );
		quickSettingsSlider().within( () => {
			cy.get( 'select' ).select( 'px' );
			cy.get( '.frm-slider-value input[type="text"]' ).clear().type( '8' ).blur();
		} );
		realInput().should( 'have.value', '8px' );

		cy.log( 'Clearing the unit propagates the unset value to the real field, not just this slider\'s own (unsubmitted) hidden input' );
		quickSettingsSlider().find( 'select' ).select( '' );
		realInput().should( 'have.value', '' );
		quickSettingsSlider().find( 'input[type="range"]' ).should( 'be.disabled' );
		quickSettingsSlider().find( '.frm-slider-value input[type="text"]' ).should( 'be.disabled' ).and( 'have.value', '' );

		cy.log( 'The unset state is announced, not silent, while staying visually blank (the option keeps empty text and carries the announcement via aria-label instead)' );
		quickSettingsSlider().find( 'select option:selected' ).should( 'have.text', '' ).and( 'have.attr', 'aria-label', 'Not set' );
		quickSettingsSlider().find( 'input[type="range"]' ).should( 'have.attr', 'aria-valuetext', 'Not set' );

		cy.log( 'The cleared value survives a save, rather than silently reverting to the last numeric value' );
		// Same click-triggered :active transform as above - raise the threshold instead of forcing.
		cy.get( '#frm_submit_side_top' ).click( { animationDistanceThreshold: 100 } );
		realInput().should( 'have.value', '' );
		quickSettingsSlider().should( 'have.class', 'frm-disabled' ).and( 'have.class', 'frm-empty' );

		cy.log( 'The blank-but-announced state is server-rendered on reload too, not only applied live by JS' );
		quickSettingsSlider().find( 'select option:selected' ).should( 'have.text', '' ).and( 'have.attr', 'aria-label', 'Not set' );
		quickSettingsSlider().find( 'input[type="range"]' ).should( 'have.attr', 'aria-valuetext', 'Not set' );

		cy.log( 'Restore a real value so the style is left in a usable state' );
		quickSettingsSlider().within( () => {
			cy.get( 'select' ).select( 'px' );
			cy.get( '.frm-slider-value input[type="text"]' ).clear().type( '8' ).blur();
		} );
		// Same click-triggered :active transform as above - raise the threshold instead of forcing.
		cy.get( '#frm_submit_side_top' ).click( { animationDistanceThreshold: 100 } );
		// A direct cy.get() (rather than through the realInput() helper) so the assertion is
		// visible to static analysis - functionally identical to the checks above.
		cy.get( 'input[name="frm_style_setting[post_content][border_radius]"]' ).should( 'have.value', '8px' );
	} );

	it( 'Slider components in the General section have no accessibility violations', () => {
		cy.visit( '/wp-admin/admin.php?page=formidable-styles&section=advanced-settings' );
		cy.injectAxe();
		cy.configureAxe( {
			rules: [
				{ id: 'color-contrast', enabled: false }
			]
		} );
		cy.checkA11y( '#general-style .frm-slider-component', null, violations => {
			const violationData = violations.map( ( { id, impact, description, nodes } ) => ( { id, impact, description, nodes: nodes.length } ) );
			cy.task( 'table', violationData );

			// cy.task output doesn't reach the GitHub Actions log, so build the same
			// summary into the assertion message below, which does.
			const summary = violationData
				.map( ( { id, impact, description, nodes } ) => `${ id } (${ impact }): ${ description } - ${ nodes } node(s)` )
				.join( '\n' );
			expect( violations, summary ).to.have.lengthOf( 0 );
		} );
	} );
} );
