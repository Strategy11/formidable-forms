( function() {

	const { doJsonPost } = frmDom.ajax;

	function addEventListeners() {
		document.addEventListener( 'change', handleChangeEvent );
		document.addEventListener( 'keydown', handleKeyDownEvent );
		document.addEventListener( 'click', handleClickEvent );
	}

	function handleChangeEvent( e ) {
		if ( 'INPUT' === e.target.nodeName && 'checkbox' === e.target.type && e.target.parentNode.classList.contains( 'frm_toggle_block' ) ) {
			handleToggleChangeEvent( e );
		}

		if ( 'frm_currency' === e.target.id) {
			syncCurrencyOptions( e.target );
		}
	}

	function handleKeyDownEvent( e ) {
		switch ( e.key ) {
			case ' ':
				handleSpaceDownEvent( e );
				break;
		}
	}

	function handleClickEvent( e ) {
		if ( e.target.classList.contains( 'frm_dismiss_default_email_message' ) ) {
			e.preventDefault();
			const formData = new FormData();
			formData.append( 'action', 'frm_dismiss_default_email_message' );
			formData.append( 'nonce', frmGlobal.nonce );
			doJsonPost( 'dismiss_default_email_message', formData )
			.then( () => {
				e.target.closest( '.frm_default_email_message' ).remove();
			})
			.catch( error => {
				console.error( error );
			});
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

	addEventListeners();
}() );
