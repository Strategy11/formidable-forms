( function() {
	'use strict';

	const globalVars = {
		sendTestEmailModal: null,
		mcpConnectionTimer: null
	};

	function addEventListeners() {
		document.addEventListener( 'change', handleChangeEvent );
		document.addEventListener( 'keydown', handleKeyDownEvent );
		document.addEventListener( 'click', handleClickEvent );
		document.addEventListener( 'input', handleInputEvent );
	}

	function handleInputEvent( e ) {
		if ( 'frm-settings-search' === e.target.id ) {
			searchSettings( e.target.value );
		}
	}

	/**
	 * Wrap occurrences of the query inside an element with <mark>.
	 *
	 * Clears marks from a previous search first. Only text nodes are touched,
	 * so icons and tooltips inside the element are left alone.
	 *
	 * @param {Element|null} element Element to highlight.
	 * @param {string}       query   Lowercase search term, empty to only clear.
	 * @return {void}
	 */
	function highlightText( element, query ) {
		if ( ! element ) {
			return;
		}

		const marks = element.querySelectorAll( 'mark.frm-search-highlight' );
		marks.forEach( mark => mark.replaceWith( document.createTextNode( mark.textContent ) ) );
		if ( marks.length ) {
			element.normalize();
		}

		if ( ! query ) {
			return;
		}

		const walker = document.createTreeWalker( element, NodeFilter.SHOW_TEXT );
		const nodes = [];
		while ( walker.nextNode() ) {
			if ( walker.currentNode.textContent.toLowerCase().includes( query ) ) {
				nodes.push( walker.currentNode );
			}
		}

		nodes.forEach( node => {
			const text = node.textContent;
			const lower = text.toLowerCase();
			const fragment = document.createDocumentFragment();
			let position = 0;
			let index = lower.indexOf( query );

			while ( index !== -1 ) {
				fragment.append( text.slice( position, index ) );
				const mark = document.createElement( 'mark' );
				mark.className = 'frm-search-highlight';
				mark.textContent = text.slice( index, index + query.length );
				fragment.append( mark );
				position = index + query.length;
				index = lower.indexOf( query, position );
			}

			fragment.append( text.slice( position ) );
			node.replaceWith( fragment );
		} );
	}

	/**
	 * Filter the settings tabs and rows by the search term.
	 *
	 * Tabs without a match are hidden, and the first matching tab is opened
	 * when the active tab has no match.
	 *
	 * @param {string} term Search term.
	 * @return {void}
	 */
	function searchSettings( term ) {
		const query = term.trim().toLowerCase();
		const tabs = document.querySelectorAll( '.frm-form-setting-tabs > li' );
		let firstMatch = null;
		let activeMatches = false;

		tabs.forEach( tab => {
			const link = tab.querySelector( 'a' );
			const panel = document.querySelector( link?.getAttribute( 'href' ) || 'x' );
			if ( ! panel ) {
				return;
			}

			const rows = Array.from( panel.querySelectorAll( '.frm_grid_container, h3, .frm_license_box' ) ).filter( row => ! row.parentElement.closest( '.frm_license_box' ) );
			highlightText( tab.querySelector( 'span' ), query );
			const tabNameMatches = tab.textContent.toLowerCase().includes( query );
			let matches = ! query || tabNameMatches;

			rows.forEach( row => {
				const label = row.querySelector( 'label' ) || row;
				const rowMatches = ! query || tabNameMatches || label.textContent.toLowerCase().includes( query );
				row.classList.toggle( 'frm_hidden', ! rowMatches );
				highlightText( row.matches( 'h3, .frm_license_box' ) ? row : row.querySelector( 'label' ), query );
				matches = matches || rowMatches;
			} );

			tab.classList.toggle( 'frm_hidden', ! matches );
			if ( matches && ! firstMatch ) {
				firstMatch = link;
			}
			if ( matches && tab.classList.contains( 'active' ) ) {
				activeMatches = true;
			}
		} );

		document.getElementById( 'frm-settings-search-empty' ).classList.toggle( 'frm_hidden', !! firstMatch );
		if ( firstMatch && ! activeMatches ) {
			firstMatch.click();
		}
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
		const copyButton = e.target.closest( '.js-frm-mcp-copy' );
		if ( copyButton ) {
			copyMcpInstruction( copyButton );
			return;
		}

		const downloadButton = e.target.closest( '.js-frm-mcp-download-env' );
		if ( downloadButton ) {
			// The download does not reload the page, so the buttons and label are updated here.
			completeMcpStep( 'env' );
			const connected = document.getElementById( 'frm_mcp_step_connect' )?.classList.contains( 'frm-mcp-step-complete' );
			document.querySelectorAll( '.js-frm-mcp-download-env, .js-frm-mcp-copy' ).forEach( button => {
				const isPrimary = button.classList.contains( 'js-frm-mcp-copy' ) && ! connected;
				button.classList.toggle( 'frm-button-primary', isPrimary );
				button.classList.toggle( 'frm-button-secondary', ! isPrimary );
			} );
			downloadButton.querySelector( '.js-frm-mcp-download-label' ).textContent = downloadButton.dataset.newLabel;
			waitForMcpConnection();
			return;
		}

		if ( 'BUTTON' === e.target.nodeName && 'choose' === e.target.dataset.action && e.target.closest( '.frm-email-style' ) ) {
			handleClickChooseEmailStyle( e );
			return;
		}

		if ( 'frm-send-test-email' === e.target.id ) {
			showSendTestEmailModal();
			return;
		}

		if ( 'frm-send-test-email-btn' === e.target.id ) {
			handleClickSendTestEmailBtn();
		}
	}

	/**
	 * Copy an MCP setup instruction and confirm the result on its button.
	 *
	 * @since 7.0
	 *
	 * @param {HTMLButtonElement} button Copy button.
	 * @return {void}
	 */
	async function copyMcpInstruction( button ) {
		const text = button.dataset.frmCopy;
		let copied = false;

		if ( navigator.clipboard?.writeText ) {
			try {
				await navigator.clipboard.writeText( text );
				copied = true;
			} catch ( error ) {
				// Use the fallback when clipboard access is denied.
			}
		}

		if ( ! copied ) {
			const input = frmDom.tag( 'textarea' );
			input.value = text;
			input.style.cssText = 'position:fixed;opacity:0;';
			button.after( input );
			input.select();
			copied = document.execCommand( 'copy' );
			input.remove();
		}

		if ( ! copied ) {
			return;
		}

		// Swap the visible label so the confirmation shows on the button itself.
		const label = button.querySelector( '.js-frm-mcp-copy-label' );
		const originalLabel = label.textContent;
		const icon = button.querySelector( 'use' );
		label.textContent = button.dataset.copiedLabel;
		icon.setAttribute( 'href', '#frm_checkmark_icon' );
		button.classList.add( 'frm-mcp-copied' );
		setTimeout( () => {
			label.textContent = originalLabel;
			icon.setAttribute( 'href', '#frm-copy-icon' );
			button.classList.remove( 'frm-mcp-copied' );
		}, 1600 );
	}

	/**
	 * Show the setup prompt for the checked assistant.
	 *
	 * Reads the checked radio rather than the event, so the prompt always matches
	 * what is selected, however the selection happened.
	 *
	 * @since 7.0
	 *
	 * @return {void}
	 */
	function syncMcpClient() {
		const client = document.querySelector( 'input[name="frm_mcp_client_view"]:checked' )?.value;

		[ 'claude', 'codex' ].forEach( name => {
			document.getElementById( `frm_mcp_prompt_client_${ name }` ).classList.toggle( 'frm_hidden', name !== client );
		} );
	}

	/**
	 * Check off an MCP setup step.
	 *
	 * @since 7.0
	 *
	 * @param {string} stepKey Step key, the suffix of the step's frm_mcp_step_ id.
	 * @return {void}
	 */
	function completeMcpStep( stepKey ) {
		const step = document.getElementById( `frm_mcp_step_${ stepKey }` );
		if ( ! step ) {
			return;
		}

		step.classList.add( 'frm-mcp-step-complete' );
		step.querySelector( '.js-frm-mcp-step-state' ).textContent = document.getElementById( 'frm_mcp_steps' ).dataset.completeLabel;
	}

	/**
	 * Poll until an assistant uses a downloaded env file, then check off the last MCP step.
	 *
	 * Stops after ten minutes, and skips checks while the tab is hidden.
	 *
	 * @since 7.0
	 *
	 * @return {void}
	 */
	function waitForMcpConnection() {
		if ( globalVars.mcpConnectionTimer ) {
			return;
		}

		const interval = 5000;
		let checksLeft = 120;

		globalVars.mcpConnectionTimer = setInterval( () => {
			if ( document.hidden ) {
				return;
			}

			checksLeft--;
			if ( checksLeft < 0 ) {
				clearInterval( globalVars.mcpConnectionTimer );
				return;
			}

			frmDom.ajax.doJsonPost( 'mcp_connection_status', new FormData() ).then( response => {
				document.getElementById( 'frm_mcp_connection_status' ).textContent = response.message;

				if ( response.connected ) {
					clearInterval( globalVars.mcpConnectionTimer );
					completeMcpStep( 'connect' );
					// Setup is done, so nothing on the page stays primary.
					document.querySelectorAll( '.js-frm-mcp-copy' ).forEach( button => {
						button.classList.replace( 'frm-button-primary', 'frm-button-secondary' );
					} );
				}
			} ).catch( () => {
				// A failed check is retried on the next tick.
			} );
		}, interval );
	}

	function handleClickChooseEmailStyle( e ) {
		const styleEls = document.querySelectorAll( '.frm-email-style' );
		styleEls.forEach( el => {
			el.classList.remove( 'frm-email-style--selected' );
		} );

		const styleEl = e.target.closest( '.frm-email-style' );
		styleEl.classList.add( 'frm-email-style--selected' );
		// The Choose button is hidden once its style is selected, so keep focus on the Selected button that replaces it.
		styleEl.querySelector( '[data-action="selected"]' ).focus();

		const { styleKey } = styleEl.dataset;
		document.getElementById( 'frm-email-style-value' ).value = styleKey;
	}

	function showSendTestEmailModal() {
		if ( ! globalVars.sendTestEmailModal ) {
			globalVars.sendTestEmailModal = frmAdminBuild.initModal( '#frm-send-test-email-modal', '400px' );
		}

		globalVars.sendTestEmailModal.dialog( 'open' );
	}

	function handleClickSendTestEmailBtn() {
		const emailInput = document.getElementById( 'frm-test-email-address' );
		const resultEl = document.getElementById( 'frm-send-test-email-result' );

		const showResult = ( msg, success, invalid = false ) => {
			resultEl.textContent = msg;
			// A failed send is not always caused by the address, so only flag the field when it is known to be wrong.
			if ( invalid ) {
				emailInput.setAttribute( 'aria-invalid', 'true' );
			}
			emailInput.setAttribute( 'aria-describedby', success ? 'frm-test-email-help' : 'frm-test-email-help frm-send-test-email-result' );
			resultEl.classList.add( success ? 'frm_updated_message' : 'frm_error_style' );
		};

		resultEl.textContent = '';
		emailInput.removeAttribute( 'aria-invalid' );
		emailInput.setAttribute( 'aria-describedby', 'frm-test-email-help' );
		resultEl.classList.remove( 'frm_error_style', 'frm_updated_message' );

		if ( ! emailInput.value ) {
			showResult( wp.i18n.__( 'Empty email address', 'formidable' ), false, true );
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

	if ( document.getElementById( 'frm_mcp_steps' )?.dataset.waiting ) {
		waitForMcpConnection();
	}

	// Replay the staggered reveal each time the server is switched on.
	document.getElementById( 'frm_mcp' )?.addEventListener( 'change', e => {
		const options = document.querySelector( '.frm_mcp_options' );
		if ( ! options || ! e.target.checked ) {
			return;
		}

		options.classList.remove( 'frm-mcp-reveal' );
		options.getBoundingClientRect(); // Reflow, so the animation starts again.
		options.classList.add( 'frm-mcp-reveal' );
	} );

	// Listen on the group so the prompt follows the selection. pageshow also
	// covers a page restored from the back/forward cache.
	const mcpClientGroup = document.getElementById( 'frm-mcp-client-claude' )?.parentNode;
	if ( mcpClientGroup ) {
		mcpClientGroup.addEventListener( 'change', syncMcpClient );
		window.addEventListener( 'pageshow', syncMcpClient );
		syncMcpClient();
	}
}() );
