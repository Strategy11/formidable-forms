( function() {

	function addEventListeners() {
		document.addEventListener( 'change', handleChangeEvent );
		document.addEventListener( 'keydown', handleKeyDownEvent );
	}

	function handleChangeEvent( e ) {
		if ( 'INPUT' === e.target.nodeName && 'checkbox' === e.target.type && e.target.parentNode.classList.contains( 'frm_toggle_block' ) ) {
			handleToggleChangeEvent( e );
		}

		if ( 'frm_currency' === e.target.id) {
			syncCurrencyOptions( e.target );
		}

		if ( 'frm_datepicker_library' === e.target.id ) {
			toggleDatepickerJqueryRangeSupportNoteOnChange( e );
		}

	}

	function handleKeyDownEvent( e ) {
		switch ( e.key ) {
			case ' ':
				handleSpaceDownEvent( e );
				break;
		}
	}

	function handleToggleChangeEvent( e ) {
		e.target.nextElementSibling.setAttribute( 'aria-checked', e.target.checked ? 'true' : 'false' );
	}

	/**
	 * Updates the currency formatting options based on the selected currency.
	 *
	 * @param {HTMLSelectElement} currencySelect The currency select element.
	 */
	function syncCurrencyOptions( currencySelect ) {
		const currency = frmSettings.currencies[ currencySelect.value ];

		document.getElementById( 'frm_thousand_separator' ).value = currency.thousand_separator;
		document.getElementById( 'frm_decimal_separator' ).value = currency.decimal_separator;
		document.getElementById( 'frm_decimals' ).value = currency.decimals;
	}

	function handleSpaceDownEvent( e ) {
		if ( e.target.classList.contains( 'frm_toggle' ) ) {
			e.preventDefault(); // Prevent automatic browser scroll when space is pressed.
			e.target.click();
		}
	}

	/**
	 * Toggle the jQuery range support note based on the datepicker library selection.
	 * @param {Event} event
	 * @return {void}
	 */
	function toggleDatepickerJqueryRangeSupportNoteOnChange( event ) {
		const datepickerLibrary      = event.target.value;
		const jqueryRangeSupportNote = document.getElementById( 'frm_datepicker_jquery_range_support_note' );

		if ( 'jquery' === datepickerLibrary ) {
			jqueryRangeSupportNote.classList.remove( 'frm_hidden' );
			return;
		}

		jqueryRangeSupportNote.classList.add( 'frm_hidden' );
	}

	addEventListeners();
}() );
