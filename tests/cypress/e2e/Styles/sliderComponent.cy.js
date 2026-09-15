describe( 'Slider style component', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/admin.php?page=formidable-styles' );
		cy.viewport( 1280, 1600 );
	} );

	/**
	 * Locates a single (non multi-value) slider's range input, visible text input, unit select
	 * and hidden "real" input from the id on its hidden input.
	 *
	 * @param {string} hiddenId - The id of the slider's hidden input, e.g. '#frm_fieldset'.
	 * @return {Object} References to the range, text, select and hidden inputs.
	 */
	const getSingleSlider = hiddenId => {
		const wrapper = cy.get( hiddenId ).closest( '.frm-slider-component' );

		return {
			range: wrapper.find( 'input[type="range"]' ),
			text: wrapper.find( '.frm-slider-value input[type="text"]' ),
			select: wrapper.find( '.frm-slider-value select' ),
			hidden: cy.get( hiddenId )
		};
	};

	it( 'Can drag a single slider with the text input, and the change persists after saving', () => {
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
		cy.get( '#frm_submit_side_top' ).click( { force: true } );
		cy.get( '#frm_fieldset' ).should( 'have.value', '12px' );
		cy.get( '#frm_fieldset' ).closest( '.frm-slider-component' ).find( 'input[type="range"]' ).should( 'have.value', '12' );
	} );

	it( 'Rejects an out of range or negative value typed into the text input', () => {
		const slider = getSingleSlider( '#frm_fieldset' );

		// Max value for Border Width is 25 - start from a known, valid value.
		slider.text.clear().type( '10' ).blur();
		slider.hidden.should( 'have.value', '10px' );

		cy.log( 'A value above the max is rejected and the text box resyncs to the current range value' );
		slider.text.clear().type( '999' ).blur();
		slider.text.should( 'have.value', '10' );
		slider.hidden.should( 'have.value', '10px' );

		cy.log( 'A negative value is rejected the same way' );
		slider.text.clear().type( '-5' ).blur();
		slider.text.should( 'have.value', '10' );
		slider.hidden.should( 'have.value', '10px' );
	} );

	it( 'Expands a multi-value slider group and lets an individual slider be adjusted independently', () => {
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
		cy.get( '#frm_submit_side_top' ).click( { force: true } );
		cy.get( '#frm_fieldset_padding' ).invoke( 'val' ).then( value => {
			expect( value.split( ' ' )[ 2 ] ).to.eq( '22px' );
		} );
	} );

	it( 'Switches a slider between the "auto" keyword and a measured unit, disabling the range for "auto"', () => {
		cy.log( 'Open the Buttons section, collapsed by default' );
		cy.get( '#buttons-style button[aria-label="Buttons"]' ).click();
		cy.get( '#frm_style_section_buttons-style' ).should( 'be.visible' );

		const slider = getSingleSlider( '#frm_submit_width' );

		cy.log( 'Force the unit to "auto" and confirm the range is disabled and announces the keyword' );
		slider.select.select( 'auto' );
		slider.range.should( 'be.disabled' );
		slider.range.should( 'have.attr', 'aria-valuetext', 'auto' );
		slider.hidden.should( 'have.value', 'auto' );

		cy.log( 'Switching to a measured unit re-enables the range and drops the "auto" value' );
		slider.select.select( 'px' );
		slider.range.should( 'not.be.disabled' );
		slider.hidden.invoke( 'val' ).should( 'match', /^\d+px$/ );

		cy.log( 'The numeric value survives a save' );
		cy.get( '#frm_submit_side_top' ).click( { force: true } );
		cy.get( '#frm_submit_width' ).invoke( 'val' ).should( 'match', /^\d+px$/ );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( 'input[type="range"]' ).should( 'not.be.disabled' );

		cy.log( 'Switching back to "auto" and saving persists the disabled, unmeasured state too' );
		cy.get( '#buttons-style button[aria-label="Buttons"]' ).click();
		cy.get( '#frm_style_section_buttons-style' ).should( 'be.visible' );
		getSingleSlider( '#frm_submit_width' ).select.select( 'auto' );
		cy.get( '#frm_submit_side_top' ).click( { force: true } );
		cy.get( '#frm_submit_width' ).should( 'have.value', 'auto' );
		cy.get( '#frm_submit_width' ).closest( '.frm-slider-component' ).find( 'input[type="range"]' ).should( 'be.disabled' );
	} );

	it( 'Slider components in the General section have no accessibility violations', () => {
		cy.injectAxe();
		cy.configureAxe( {
			rules: [
				{ id: 'color-contrast', enabled: false }
			]
		} );
		cy.checkA11y( '#general-style .frm-slider-component', null, violations => {
			cy.task( 'table', violations.map( ( { id, impact, description, nodes } ) => ( { id, impact, description, nodes: nodes.length } ) ) );
		} );
	} );
} );
