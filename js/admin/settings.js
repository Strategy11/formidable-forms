( function() {
	'use strict';

	const globalVars = {
		sendTestEmailModal: null
	};

	function addEventListeners() {
		document.addEventListener( 'change', handleChangeEvent );
		document.addEventListener( 'keydown', handleKeyDownEvent );
		document.addEventListener( 'click', handleClickEvent );
	}

	function handleChangeEvent( e ) {
		if ( 'INPUT' === e.target.nodeName && 'checkbox' === e.target.type && e.target.parentNode.classList.contains( 'frm_toggle_block' ) ) {
			handleToggleChangeEvent( e );
		}

		if ( 'frm_currency' === e.target.id ) {
			syncCurrencyOptions( e.target );
		}
	}

	function handleKeyDownEvent( e ) {
		switch ( e.key ) { // eslint-disable-line sonarjs/no-small-switch
			case ' ':
				handleSpaceDownEvent( e );
				break;
		}
	}

	function handleToggleChangeEvent( e ) {
		e.target.nextElementSibling.setAttribute( 'aria-checked', e.target.checked ? 'true' : 'false' );
	}

	function handleClickEvent( e ) {
		if ( 'BUTTON' === e.target.nodeName && 'choose' === e.target.dataset.action && e.target.closest( '.frm-email-style' ) ) {
			handleClickChooseEmailStyle( e );
			return;
		}

		if ( 'frm-send-test-email' === e.target.id ) {
			showSendTestEmailModal( e );
			return;
		}

		if ( 'frm-send-test-email-btn' === e.target.id ) {
			handleClickSendTestEmailBtn( e );
		}
	}

	function handleClickChooseEmailStyle( e ) {
		const styleEls = document.querySelectorAll( '.frm-email-style' );
		styleEls.forEach( el => {
			el.classList.remove( 'frm-email-style--selected' );
		} );

		const styleEl = e.target.closest( '.frm-email-style' );
		styleEl.classList.add( 'frm-email-style--selected' );

		const styleKey = styleEl.dataset.styleKey;
		document.getElementById( 'frm-email-style-value' ).value = styleKey;
	}

	function showSendTestEmailModal( e ) {
		if ( ! globalVars.sendTestEmailModal ) {
			globalVars.sendTestEmailModal = frmAdminBuild.initModal( '#frm-send-test-email-modal', '400px' );
		}

		globalVars.sendTestEmailModal.dialog( 'open' );
	}

	function handleClickSendTestEmailBtn( e ) {
		const emailInput = document.getElementById( 'frm-test-email-address' );
		const resultEl = document.getElementById( 'frm-send-test-email-result' );

		const showResult = ( msg, success ) => {
			resultEl.textContent = msg;
			resultEl.classList.add( success ? 'frm_updated_message' : 'frm_error_style' );
		};

		resultEl.textContent = '';
		resultEl.classList.remove( 'frm_error_style', 'frm_updated_message' );

		if ( ! emailInput.value ) {
			showResult( 'Empty email address' );
			return;
		}

		const data = new FormData();
		data.append( 'emails_str', emailInput.value );
		frmDom.ajax.doJsonPost( 'send_test_email', data ).then( response => {
			showResult( response, true );
		} ).catch( error => {
			showResult( error );
		} );
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
