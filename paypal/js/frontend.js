/**
 * PayPal Payment Integration - Radio-Based Payment Method Selector.
 *
 * Architecture:
 * - A radio group lets the user pick their payment method (Card, PayPal, Venmo, etc.).
 * - Only the selected method's UI is visible at a time.
 * - Card + PayPal are pre-rendered on init (hybrid approach).
 * - Other methods (Venmo, Google Pay, etc.) are lazy-rendered on first selection, then cached.
 * - When Card is selected: card fields + native submit button are shown.
 * - When any button method is selected: the submit button is hidden and only that button is shown.
 */
( function() {
	if ( ! window.frmPayPalVars ) {
		return;
	}

	// ---- State ----

	let thisForm = null;
	let running = 0;
	let cardFieldsInstance = null;
	let cardFieldsValid = false;
	let submitEvent = null;

	/**
	 * Registry of available payment methods.
	 * Populated during init based on SDK eligibility checks.
	 *
	 * @type {Map<string, Object>}
	 */
	const paymentMethods = new Map();

	/** Currently selected payment method key. */
	let selectedMethod = null;

	/** Cached Google Pay config from paypal.Googlepay().config(). */
	let googlePayConfig = null;

	// ---- Constants ----

	/**
	 * Human-readable labels for funding sources.
	 */
	const METHOD_LABELS = {
		card: 'Credit Card',
		paypal: 'PayPal',
		venmo: 'Venmo',
		paylater: 'Pay Later',
		google_pay: 'Google Pay',
		bancontact: 'Bancontact',
		blik: 'BLIK',
		eps: 'EPS',
		p24: 'Przelewy24',
		trustly: 'Trustly',
		satispay: 'Satispay',
		sepa: 'SEPA',
		mybank: 'MyBank',
		ideal: 'iDEAL',
	};

	/**
	 * Maps internal method keys to PayPal FUNDING constants for the Marks API.
	 * Card and Google Pay use local images instead of PayPal Marks.
	 */
	const METHOD_FUNDING_SOURCE = {
		paypal: 'paypal',
		venmo: 'venmo',
		paylater: 'paylater',
		bancontact: 'bancontact',
		blik: 'blik',
		eps: 'eps',
		p24: 'p24',
		trustly: 'trustly',
		satispay: 'satispay',
		sepa: 'sepa',
		mybank: 'mybank',
		ideal: 'ideal',
	};

	/**
	 * Methods that should be pre-rendered on init (hybrid approach).
	 * Everything else is lazy-rendered on first selection.
	 */
	const PRE_RENDER_METHODS = new Set( [ 'card', 'paypal' ] );

	/**
	 * Base request object shared by isReadyToPay and PaymentDataRequest.
	 */
	const googlePayBaseRequest = {
		apiVersion: 2,
		apiVersionMinor: 0
	};

	// ---- Initialization ----

	/**
	 * Main entry point.
	 */
	async function paypalInit() {
		const cardElement = document.querySelector( '.frm-card-element' );
		if ( ! cardElement ) {
			return;
		}

		thisForm = cardElement.closest( 'form' );
		if ( ! thisForm ) {
			return;
		}

		const settings = getPayPalSettings()[ 0 ];
		if ( ! settings ) {
			return;
		}

		const isRecurring = 'single' !== settings.one;
		const { layout } = settings;
		const cardFieldsAreSupported = layout !== 'checkout_only' && 'function' === typeof window.paypal.CardFields && ! isRecurring;
		const buttonsAreEnabled = layout !== 'card_only' && 'function' === typeof window.paypal.Buttons;

		// Clear the card element. We rebuild it entirely.
		cardElement.innerHTML = '';

		// 1. Discover eligible methods and register them.
		await discoverPaymentMethods( {
			cardFieldsAreSupported,
			buttonsAreEnabled,
			isRecurring
		} );

		if ( paymentMethods.size === 0 ) {
			displayPaymentFailure( 'No payment methods available.' );
			return;
		}

		// 2. Build the radio selector UI, then render marks after it's in the DOM.
		const radioGroup = buildRadioGroup();
		cardElement.append( radioGroup );
		renderMarks();

		// 3. Build a container area for each method's UI (buttons / card fields).
		const methodArea = document.createElement( 'div' );
		methodArea.classList.add( 'frm-payment-method-area' );
		cardElement.append( methodArea );

		for ( const [ key, method ] of paymentMethods ) {
			const container = document.createElement( 'div' );
			container.id = `frm-payment-method-${ key }`;
			container.classList.add( 'frm-payment-method-container' );
			container.style.display = 'none';
			container.style.maxWidth = '500px';
			methodArea.append( container );
			method.containerEl = container;
		}

		// 4. Pre-render Card + PayPal (hybrid approach).
		for ( const key of PRE_RENDER_METHODS ) {
			const method = paymentMethods.get( key );
			if ( method && method.eligible ) {
				try {
					await method.render.call( method );
					method.rendered = true;
				} catch ( err ) {
					console.error( `Failed to pre-render payment method: ${ key }`, err );
				}
			}
		}

		// 5. Auto-select the first eligible method.
		const firstKey = paymentMethods.keys().next().value;
		await selectPaymentMethod( firstKey );

		// 6. Attach form submit handler (for card method).
		thisForm.addEventListener( 'submit', handleFormSubmission );

		// 7. Pay Later messages.
		if ( paymentMethods.has( 'paylater' ) ) {
			renderMessages();
			jQuery( document ).on( 'frmFieldChanged', priceChanged );
			checkPriceFieldsOnLoad();
		}
	}

	// ---- Discovery ----

	/**
	 * Discover which payment methods are eligible and register them.
	 *
	 * @param {Object} opts Config flags.
	 */
	async function discoverPaymentMethods( opts ) {
		const { cardFieldsAreSupported, buttonsAreEnabled, isRecurring } = opts;

		// --- Card Fields ---
		if ( cardFieldsAreSupported ) {
			const cardFields = createCardFieldsSDKInstance();
			if ( cardFields && cardFields.isEligible() ) {
				cardFieldsInstance = cardFields;
				registerMethod( 'card', {
					eligible: true,
					render: renderCardFields
				} );
			}
		}

		// --- PayPal button ---
		if ( buttonsAreEnabled ) {
			const paypalBtn = createPayPalButton( paypal.FUNDING.PAYPAL, isRecurring );
			if ( paypalBtn.isEligible() ) {
				registerMethod( 'paypal', {
					eligible: true,
					buttonInstance: paypalBtn,
					render: function() {
						this.buttonInstance.render( `#${ this.containerEl.id }` );
					}
				} );
			}
		}

		// --- Alternative funding sources ---
		if ( buttonsAreEnabled ) {
			const fundingSources = [
				{ key: 'venmo', funding: paypal.FUNDING.VENMO },
				{ key: 'paylater', funding: paypal.FUNDING.PAYLATER },
				{ key: 'bancontact', funding: paypal.FUNDING.BANCONTACT },
				{ key: 'blik', funding: paypal.FUNDING.BLIK },
				{ key: 'eps', funding: paypal.FUNDING.EPS },
				{ key: 'p24', funding: paypal.FUNDING.P24 },
				{ key: 'trustly', funding: paypal.FUNDING.TRUSTLY },
				{ key: 'satispay', funding: paypal.FUNDING.SATISPAY },
				{ key: 'sepa', funding: paypal.FUNDING.SEPA },
				{ key: 'mybank', funding: paypal.FUNDING.MYBANK },
				{ key: 'ideal', funding: paypal.FUNDING.IDEAL },
			];

			for ( const { key, funding } of fundingSources ) {
				const btn = createPayPalButton( funding, isRecurring );
				if ( btn.isEligible() ) {
					registerMethod( key, {
						eligible: true,
						buttonInstance: btn,
						render: function() {
							this.buttonInstance.render( `#${ this.containerEl.id }` );
						}
					} );
				}
			}
		}

		// --- Google Pay ---
		if ( buttonsAreEnabled && ! isRecurring ) {
			const googlePayEligible = await checkGooglePayEligibility();
			if ( googlePayEligible ) {
				registerMethod( 'google_pay', {
					eligible: true,
					render: renderGooglePayButton
				} );
			}
		}
	}

	/**
	 * Register a payment method in the registry.
	 *
	 * @param {string} key    Unique identifier.
	 * @param {Object} config Method configuration.
	 */
	function registerMethod( key, config ) {
		paymentMethods.set( key, {
			key,
			label: METHOD_LABELS[ key ] || key,
			eligible: config.eligible || false,
			rendered: false,
			containerEl: null,
			buttonInstance: config.buttonInstance || null,
			render: config.render || ( () => {} ),
		} );
	}

	// ---- Radio Group UI ----

	/**
	 * Inject the payment method selector CSS into the page (once).
	 */
	function injectStyles() {
		if ( document.getElementById( 'frm-payment-method-styles' ) ) {
			return;
		}

		const style = document.createElement( 'style' );
		style.id = 'frm-payment-method-styles';
		style.textContent = `
			.frm-payment-method-selector {
				display: flex;
				flex-direction: column;
				gap: 0;
				margin-bottom: 16px;
				border: 1px solid #e0e0e0;
				border-radius: 8px;
				overflow: hidden;
				max-width: 500px;
			}
			.frm-payment-method-option {
				display: flex;
				align-items: center;
				gap: 12px;
				padding: 16px;
				cursor: pointer;
				border-bottom: 1px solid #e0e0e0;
				background: #fff;
				transition: background-color 0.15s ease;
				position: relative;
				margin: 0;
			}
			.frm-payment-method-option:last-child {
				border-bottom: none;
			}
			.frm-payment-method-option:hover {
				background-color: #f9f9fb;
			}
			.frm-payment-method-active {
				background-color: #f0f4ff;
			}
			.frm-payment-method-active:hover {
				background-color: #f0f4ff;
			}
			.frm-payment-method-option input[type="radio"] {
				appearance: none !important;
				-webkit-appearance: none !important;
				width: 20px !important;
				height: 20px !important;
				min-width: 20px !important;
				min-height: 20px !important;
				border: 2px solid #c0c0c0 !important;
				border-radius: 50% !important;
				flex-shrink: 0;
				margin: 0 !important;
				padding: 0 !important;
				cursor: pointer;
				transition: border-color 0.15s ease, background 0.15s ease;
				background: #fff !important;
				box-shadow: none !important;
				outline: none !important;
			}
			.frm-payment-method-option input[type="radio"]:checked {
				border-color: #0070ba !important;
				background: radial-gradient(circle, #0070ba 40%, #fff 40%, #fff 100%) !important;
			}
			.frm-payment-method-option input[type="radio"]:focus {
				outline: 2px solid rgba(0, 112, 186, 0.3) !important;
				outline-offset: 2px !important;
			}
			.frm-payment-method-text {
				display: flex;
				flex-direction: column;
				gap: 2px;
				flex: 1;
				min-width: 0;
			}
			.frm-payment-method-label-text {
				font-size: 15px;
				font-weight: 500;
				color: #1a1a2e;
				line-height: 1.3;
			}
			.frm-payment-method-desc {
				font-size: 13px;
				color: #6b7280;
				line-height: 1.3;
			}
			.frm-payment-method-mark {
				flex-shrink: 0;
				display: flex;
				align-items: center;
				gap: 4px;
			}
			.frm-payment-method-mark img,
			.frm-payment-method-mark svg {
				height: 24px;
				width: auto;
			}
			.frm-payment-method-google-pay-icon {
				height: 24px;
			}
			.frm-payment-method-google-pay-icon svg {
				height: 24px;
				width: auto;
			}
			.frm-payment-method-paylater-wrap {
				border-bottom: 1px solid #e0e0e0;
				background: #fff;
				transition: background-color 0.15s ease;
			}
			.frm-payment-method-paylater-wrap:hover {
				background-color: #f9f9fb;
			}
			.frm-payment-method-paylater-wrap.frm-payment-method-active-wrap {
				background-color: #f0f4ff;
			}
			.frm-payment-method-paylater-wrap.frm-payment-method-active-wrap:hover {
				background-color: #f0f4ff;
			}
			.frm-payment-method-paylater-wrap.frm-payment-method-active-wrap .frm-payment-method-paylater-msg {
				background-color: #f0f4ff;
			}
			.frm-payment-method-paylater-wrap:last-child {
				border-bottom: none;
			}
			.frm-payment-method-paylater-wrap .frm-payment-method-option {
				border-bottom: none;
				background: transparent;
			}
			.frm-payment-method-paylater-wrap .frm-payment-method-option:hover {
				background: transparent;
			}
			.frm-payment-method-paylater-msg {
				padding: 0 16px 12px 48px;
				font-size: 13px;
				color: #6b7280;
				background: transparent;
			}
			.frm-payment-method-area {
				margin-top: 16px;
			}
		`;
		document.head.append( style );
	}

	/**
	 * Build the radio button group for payment method selection.
	 * Each option is a card-like row with a radio, label, description, and PayPal Mark logo.
	 *
	 * @return {HTMLElement} The radio group container.
	 */
	function buildRadioGroup() {
		injectStyles();

		const group = document.createElement( 'div' );
		group.classList.add( 'frm-payment-method-selector' );
		group.setAttribute( 'role', 'radiogroup' );
		group.setAttribute( 'aria-label', 'Select payment method' );

		for ( const [ key, method ] of paymentMethods ) {
			const label = document.createElement( 'label' );
			label.classList.add( 'frm-payment-method-option' );
			label.setAttribute( 'for', `frm-payment-method-radio-${ key }` );

			const radio = document.createElement( 'input' );
			radio.type = 'radio';
			radio.name = 'frm_payment_method';
			radio.id = `frm-payment-method-radio-${ key }`;
			radio.value = key;

			radio.addEventListener( 'change', () => selectPaymentMethod( key ) );

			// Text column: label + description.
			const textWrap = document.createElement( 'div' );
			textWrap.classList.add( 'frm-payment-method-text' );

			const labelText = document.createElement( 'span' );
			labelText.classList.add( 'frm-payment-method-label-text' );
			labelText.textContent = method.label;
			textWrap.append( labelText );

			// Mark column: will be populated by renderMarks() after the group is in the DOM.
			const markWrap = document.createElement( 'div' );
			markWrap.classList.add( 'frm-payment-method-mark' );
			markWrap.id = `frm-payment-mark-${ key }`;

			const baseUrl = frmPayPalVars.imagesUrl || '';

			if ( key === 'card' ) {
				const cardBrands = [
					{ file: 'visa.svg', alt: 'Visa' },
					{ file: 'mastercard.svg', alt: 'Mastercard' },
					{ file: 'amex.svg', alt: 'American Express' },
					{ file: 'discover.svg', alt: 'Discover' },
				];
				cardBrands.forEach( function( brand ) {
					const img = document.createElement( 'img' );
					img.src = baseUrl + brand.file;
					img.alt = brand.alt;
					img.height = 24;
					markWrap.append( img );
				} );
			} else if ( key === 'google_pay' ) {
				markWrap.classList.add( 'frm-payment-method-google-pay-icon' );
				const img = document.createElement( 'img' );
				const baseUrl = frmPayPalVars.imagesUrl || '';
				img.src = baseUrl + 'gpay.svg';
				img.alt = 'Google Pay';
				img.height = 24;
				markWrap.append( img );
			}

			label.append( radio );
			label.append( textWrap );
			label.append( markWrap );

			if ( key === 'paylater' ) {
				// Wrap the label and a message container in a div.
				const wrapper = document.createElement( 'div' );
				wrapper.classList.add( 'frm-payment-method-paylater-wrap' );
				wrapper.append( label );

				const msgContainer = document.createElement( 'div' );
				msgContainer.id = 'frm-paylater-message';
				msgContainer.classList.add( 'frm-payment-method-paylater-msg' );
				wrapper.append( msgContainer );

				group.append( wrapper );
			} else {
				group.append( label );
			}
		}

		return group;
	}

	/**
	 * Render PayPal Marks into the radio group containers.
	 * Must be called AFTER the radio group is appended to the DOM,
	 * because the Marks API needs the containers to be in the document.
	 */
	function renderMarks() {
		if ( 'function' !== typeof paypal.Marks ) {
			return;
		}

		for ( const [ key ] of paymentMethods ) {
			const fundingSource = METHOD_FUNDING_SOURCE[ key ];
			if ( ! fundingSource ) {
				continue;
			}

			const markContainerId = `frm-payment-mark-${ key }`;
			const container = document.getElementById( markContainerId );
			if ( ! container ) {
				continue;
			}

			try {
				const mark = paypal.Marks( { fundingSource } );
				if ( mark.isEligible() ) {
					mark.render( `#${ markContainerId }` );
				}
			} catch ( err ) {
				// Mark not available for this source, that's fine.
			}
		}
	}

	// ---- Method Selection ----

	/**
	 * Handle switching to a new payment method.
	 *
	 * 1. Lazy-render if this method hasn't been rendered yet.
	 * 2. Hide all method containers.
	 * 3. Show the selected method's container.
	 * 4. Toggle submit button visibility.
	 *
	 * @param {string} key The payment method key to select.
	 */
	async function selectPaymentMethod( key ) {
		const method = paymentMethods.get( key );
		if ( ! method ) {
			return;
		}

		selectedMethod = key;

		// Update radio checked state.
		const radio = document.getElementById( `frm-payment-method-radio-${ key }` );
		if ( radio && ! radio.checked ) {
			radio.checked = true;
		}

		// Lazy-render if this is the first time selecting a non-pre-rendered method.
		if ( ! method.rendered ) {
			method.containerEl.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible"></span>';
			try {
				await method.render.call( method );
				method.rendered = true;
			} catch ( err ) {
				console.error( `Failed to render payment method: ${ key }`, err );
				method.containerEl.innerHTML = '';
			}
		}

		// Hide all method containers.
		for ( const [ , m ] of paymentMethods ) {
			if ( m.containerEl ) {
				m.containerEl.style.display = 'none';
			}
		}

		// Show the selected one.
		if ( method.containerEl ) {
			method.containerEl.style.display = 'block';
		}

		// Toggle submit button + card fields visibility.
		updateSubmitButtonVisibility( key );

		// Update active class on radio labels.
		document.querySelectorAll( '.frm-payment-method-option' ).forEach( el => {
			el.classList.remove( 'frm-payment-method-active' );
		} );
		document.querySelectorAll( '.frm-payment-method-paylater-wrap' ).forEach( el => {
			el.classList.remove( 'frm-payment-method-active-wrap' );
		} );
		const activeLabel = radio?.closest( '.frm-payment-method-option' );
		if ( activeLabel ) {
			activeLabel.classList.add( 'frm-payment-method-active' );
			const wrapper = activeLabel.closest( '.frm-payment-method-paylater-wrap' );
			if ( wrapper ) {
				wrapper.classList.add( 'frm-payment-method-active-wrap' );
			}
		}
	}

	/**
	 * Show/hide the native submit button based on the selected method.
	 *
	 * - Card: submit button visible (user fills card fields, clicks submit).
	 * - Everything else: submit button hidden (PayPal SDK button handles submission).
	 *
	 * @param {string} key The selected payment method key.
	 */
	function updateSubmitButtonVisibility( key ) {
		const submitButtons = thisForm.querySelectorAll(
			'input[type="submit"], input[type="button"], button[type="submit"]'
		);
		const isCardMethod = key === 'card';

		submitButtons.forEach( btn => {
			if ( btn.classList.contains( 'frm_prev_page' ) ) {
				return;
			}

			if ( isCardMethod ) {
				btn.style.display = '';
				if ( cardFieldsValid ) {
					btn.removeAttribute( 'disabled' );
				} else {
					btn.setAttribute( 'disabled', 'disabled' );
				}
			} else {
				btn.style.display = 'none';
			}
		} );
	}

	// ---- Card Fields ----

	/**
	 * Create the PayPal CardFields SDK instance (without rendering).
	 *
	 * @return {Object|null} The card fields instance.
	 */
	function createCardFieldsSDKInstance() {
		try {
			return window.paypal.CardFields( {
				onApprove,
				onError,
				createOrder,
				style: frmPayPalVars.style,
				inputEvents: {
					onChange: onCardFieldsChange
				}
			} );
		} catch ( err ) {
			console.error( 'Failed to create CardFields instance', err );
			return null;
		}
	}

	/**
	 * Handle card field value changes.
	 *
	 * @param {Object} data The onChange event data.
	 */
	function onCardFieldsChange( data ) {
		cardFieldsValid = data.isFormValid;

		if ( selectedMethod === 'card' ) {
			if ( cardFieldsValid ) {
				enableSubmit();
			} else {
				disableSubmit( thisForm );
			}
		}
	}

	/**
	 * Render the card number / expiry / CVV fields into the method container.
	 */
	function renderCardFields() {
		const method = paymentMethods.get( 'card' );
		if ( ! method || ! cardFieldsInstance ) {
			return;
		}

		const wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'frm-card-fields-wrapper', 'frm_grid_container' );

		const cardNumberWrapper = document.createElement( 'div' );
		cardNumberWrapper.id = 'frm-paypal-card-number';
		cardNumberWrapper.classList.add( 'frm6', 'frm-payment-card-number' );

		const expiryWrapper = document.createElement( 'div' );
		expiryWrapper.id = 'frm-paypal-card-expiry';
		expiryWrapper.classList.add( 'frm3', 'frm-payment-card-expiry' );

		const cvvWrapper = document.createElement( 'div' );
		cvvWrapper.id = 'frm-paypal-card-cvv';
		cvvWrapper.classList.add( 'frm3', 'frm-payment-card-cvv' );

		wrapper.append( cardNumberWrapper, expiryWrapper, cvvWrapper );
		method.containerEl.innerHTML = '';
		method.containerEl.append( wrapper );

		cardFieldsInstance.NumberField().render( '#frm-paypal-card-number' );
		cardFieldsInstance.ExpiryField().render( '#frm-paypal-card-expiry' );
		cardFieldsInstance.CVVField().render( '#frm-paypal-card-cvv' );

		setupCardFieldIframeObservers();
	}

	/**
	 * Watch for PayPal iframe height changes and add 1px to prevent border clipping.
	 */
	function setupCardFieldIframeObservers() {
		const ids = [ 'frm-paypal-card-number', 'frm-paypal-card-expiry', 'frm-paypal-card-cvv' ];
		const wrappers = ids
			.map( id => document.getElementById( id )?.querySelector( 'iframe' )?.parentNode )
			.filter( Boolean );

		if ( ! wrappers.length ) {
			return;
		}

		const observerOptions = { attributes: true, attributeFilter: [ 'style' ] };

		const observerCallback = ( mutationsList, observer ) => {
			observer.disconnect();

			for ( const mutation of mutationsList ) {
				if ( mutation.type !== 'attributes' || mutation.attributeName !== 'style' ) {
					continue;
				}

				const currentHeight = mutation.target.offsetHeight;
				if ( currentHeight > 0 ) {
					mutation.target.style.height = ( currentHeight + 1 ) + 'px';
				}
			}

			wrappers.forEach( w => observer.observe( w, observerOptions ) );
		};

		const observer = new MutationObserver( observerCallback );
		wrappers.forEach( w => observer.observe( w, observerOptions ) );
	}

	// ---- PayPal Button Creation ----

	/**
	 * Create a PayPal Buttons instance for a given funding source (without rendering).
	 *
	 * @param {string}  fundingSource The PayPal FUNDING constant.
	 * @param {boolean} isRecurring   Whether this is a recurring payment.
	 *
	 * @return {Object} The PayPal Buttons instance.
	 */
	function createPayPalButton( fundingSource, isRecurring ) {
		const buttonConfig = {
			fundingSource,
			onApprove,
			onError,
			onCancel,
			style: { ...frmPayPalVars.buttonStyle },
		};

		const supportedColors = [ 'silver', 'black', 'white' ];
		const supportedColorsMap = {
			venmo: [ 'blue' ],
			paylater: [ 'gold', 'blue' ]
		};

		supportedColorsMap[ fundingSource ]?.forEach( color => supportedColors.push( color ) );

		if ( ! supportedColors.includes( buttonConfig.style.color ) ) {
			delete buttonConfig.style.color;
		}

		if ( isRecurring ) {
			buttonConfig.createSubscription = createSubscription;
		} else {
			buttonConfig.createOrder = createOrder;
		}

		return paypal.Buttons( buttonConfig );
	}

	// ---- Google Pay ----

	/**
	 * Check if Google Pay is eligible (without rendering).
	 *
	 * @return {Promise<boolean>}
	 */
	async function checkGooglePayEligibility() {
		if ( 'function' !== typeof paypal.Googlepay ) {
			return false;
		}

		if ( 'undefined' === typeof google || google.payments === undefined ) {
			return false;
		}

		try {
			googlePayConfig = await paypal.Googlepay().config();
			const paymentsClient = getGooglePaymentsClient();

			const readyToPayRequest = Object.assign( {}, googlePayBaseRequest, {
				allowedPaymentMethods: googlePayConfig.allowedPaymentMethods
			} );

			const response = await paymentsClient.isReadyToPay( readyToPayRequest );
			return response.result;
		} catch ( err ) {
			console.error( 'Google Pay eligibility check failed', err );
			return false;
		}
	}

	/**
	 * Render the Google Pay button into its method container.
	 */
	async function renderGooglePayButton() {
		const method = paymentMethods.get( 'google_pay' );
		if ( ! method || ! googlePayConfig ) {
			return;
		}

		const paymentsClient = getGooglePaymentsClient();
		const buttonOptions = Object.assign(
			getGooglePayButtonStyle(),
			{
				onClick: () => onGooglePayButtonClicked( googlePayConfig ),
				allowedPaymentMethods: googlePayConfig.allowedPaymentMethods
			}
		);
		const button = paymentsClient.createButton( buttonOptions );

		const container = method.containerEl;
		container.innerHTML = '';
		container.append( button );
	}

	/**
	 * Get a Google PaymentsClient configured for the current environment.
	 *
	 * @return {google.payments.api.PaymentsClient} The payments client instance.
	 */
	function getGooglePaymentsClient() {
		return new google.payments.api.PaymentsClient( {
			environment: 'TEST',
			paymentDataCallbacks: {
				onPaymentAuthorized
			}
		} );
	}

	/**
	 * Map frmPayPalVars.buttonStyle to Google Pay ButtonOptions.
	 *
	 * @return {Object} Google Pay button style options.
	 */
	function getGooglePayButtonStyle() {
		const style = frmPayPalVars.buttonStyle || {};
		const options = { buttonSizeMode: 'fill' };

		const colorMap = { black: 'black', white: 'white', silver: 'white' };
		if ( style.color && colorMap[ style.color ] ) {
			options.buttonColor = colorMap[ style.color ];
		}

		const typeMap = { pay: 'pay', checkout: 'checkout', buynow: 'buy', donate: 'donate', subscribe: 'subscribe' };
		if ( style.label && typeMap[ style.label ] ) {
			options.buttonType = typeMap[ style.label ];
		}

		if ( style.borderRadius !== undefined ) {
			options.buttonRadius = style.borderRadius;
		}

		return options;
	}

	/**
	 * Handle Google Pay button click.
	 *
	 * @param {Object} config The config from paypal.Googlepay().config().
	 *
	 * @return {Promise<void>}
	 */
	async function onGooglePayButtonClicked( config ) {
		const settings = getPayPalSettings()[ 0 ];
		const currency = ( settings.currency || 'USD' ).toUpperCase();

		const paymentDataRequest = Object.assign( {}, googlePayBaseRequest );
		paymentDataRequest.allowedPaymentMethods = config.allowedPaymentMethods;
		paymentDataRequest.merchantInfo = config.merchantInfo;
		paymentDataRequest.callbackIntents = [ 'PAYMENT_AUTHORIZATION' ];

		paymentDataRequest.transactionInfo = {
			currencyCode: currency,
			totalPriceStatus: 'ESTIMATED',
			totalPrice: '0.00'
		};

		try {
			const amount = await new Promise( ( resolve, reject ) => {
				getPrice( result => {
					if ( result && result.data && result.data.amount ) {
						resolve( result.data.amount );
					} else {
						reject( new Error( 'No amount' ) );
					}
				} );
			} );

			paymentDataRequest.transactionInfo.totalPrice = String( amount );
			paymentDataRequest.transactionInfo.totalPriceStatus = 'FINAL';
		} catch ( e ) {
			// Fall back to ESTIMATED with 0.00 if we can't get the price.
		}

		const paymentsClient = getGooglePaymentsClient();
		paymentsClient.loadPaymentData( paymentDataRequest );
	}

	/**
	 * Callback invoked by Google Pay when the buyer authorizes the payment.
	 *
	 * @param {Object} paymentData The Google Pay PaymentData response object.
	 *
	 * @return {Promise<Object>} Transaction state result for the Google Pay sheet.
	 */
	async function onPaymentAuthorized( paymentData ) {
		try {
			const orderId = await createOrderForGooglePay();

			const confirmOrderResponse = await paypal.Googlepay().confirmOrder( {
				orderId,
				paymentMethodData: paymentData.paymentMethodData
			} );

			if ( confirmOrderResponse.status === 'PAYER_ACTION_REQUIRED' ) {
				await paypal.Googlepay().initiatePayerAction( { orderId } );
			}

			if ( confirmOrderResponse.status === 'APPROVED' || confirmOrderResponse.status === 'PAYER_ACTION_REQUIRED' ) {
				await onApprove( {
					orderID: orderId,
					paymentSource: 'google_pay'
				} );

				return { transactionState: 'SUCCESS' };
			}

			return {
				transactionState: 'ERROR',
				error: {
					intent: 'PAYMENT_AUTHORIZATION',
					message: 'Payment could not be authorized'
				}
			};
		} catch ( err ) {
			return {
				transactionState: 'ERROR',
				error: {
					intent: 'PAYMENT_AUTHORIZATION',
					message: err.message || 'Payment failed'
				}
			};
		}
	}

	// ---- AJAX / Order Creation ----

	/**
	 * Create a PayPal order via AJAX.
	 *
	 * @param {Object} data
	 * @return {Promise<string>} The order ID.
	 */
	async function createOrder( data ) {
		++running;
		thisForm.classList.add( 'frm_loading_form' );

		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_order' );
		formData.append( 'nonce', frmPayPalVars.nonce );
		formData.append( 'payment_source', data.paymentSource );

		formData.delete( 'frm_action' );
		formData.delete( 'form_key' );
		formData.delete( 'item_key' );

		const response = await fetch( frmPayPalVars.ajax, {
			method: 'POST',
			body: formData
		} );

		if ( ! response.ok ) {
			thisForm.classList.remove( 'frm_loading_form' );
			throw new Error( 'Failed to create PayPal order' );
		}

		const orderData = await response.json();

		if ( ! orderData.success || ! orderData.data.orderID ) {
			thisForm.classList.remove( 'frm_loading_form' );
			throw new Error( orderData.data || 'Failed to create PayPal order' );
		}

		return orderData.data.orderID;
	}

	async function createSubscription( data ) {
		thisForm.classList.add( 'frm_loading_form' );

		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_subscription' );
		formData.append( 'nonce', frmPayPalVars.nonce );

		formData.delete( 'frm_action' );
		formData.delete( 'form_key' );
		formData.delete( 'item_key' );

		const response = await fetch( frmPayPalVars.ajax, {
			method: 'POST',
			body: formData
		} );

		if ( ! response.ok ) {
			thisForm.classList.remove( 'frm_loading_form' );
			throw new Error( 'Failed to create PayPal subscription' );
		}

		const orderData = await response.json();

		if ( ! orderData.success || ! orderData.data.subscriptionID ) {
			thisForm.classList.remove( 'frm_loading_form' );

			if ( 'string' === typeof orderData.data ) {
				throw new TypeError( orderData.data );
			}

			throw new Error( 'Failed to create PayPal subscription' );
		}

		return orderData.data.subscriptionID;
	}

	/**
	 * Create a PayPal order specifically for Google Pay.
	 *
	 * @return {Promise<string>} The PayPal order ID.
	 */
	async function createOrderForGooglePay() {
		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_order' );
		formData.append( 'nonce', frmPayPalVars.nonce );
		formData.append( 'payment_source', 'google_pay' );

		formData.delete( 'frm_action' );
		formData.delete( 'form_key' );
		formData.delete( 'item_key' );

		const response = await fetch( frmPayPalVars.ajax, {
			method: 'POST',
			body: formData
		} );

		if ( ! response.ok ) {
			throw new Error( 'Failed to create PayPal order for Google Pay' );
		}

		const orderData = await response.json();

		if ( ! orderData.success || ! orderData.data.orderID ) {
			throw new Error( orderData.data || 'Failed to create PayPal order for Google Pay' );
		}

		return orderData.data.orderID;
	}

	async function createVaultSetupToken() {
		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_vault_setup_token' );
		formData.append( 'nonce', frmPayPalVars.nonce );

		formData.delete( 'frm_action' );
		formData.delete( 'form_key' );
		formData.delete( 'item_key' );

		const response = await fetch( frmPayPalVars.ajax, {
			method: 'POST',
			body: formData
		} );

		if ( ! response.ok ) {
			throw new Error( 'Failed to create PayPal vault setup token' );
		}

		const tokenData = await response.json();

		if ( ! tokenData.success || ! tokenData.data.token ) {
			throw new Error( tokenData.data || 'Failed to create PayPal vault setup token' );
		}

		return tokenData.data.token;
	}

	// ---- Payment Callbacks ----

	/**
	 * Handle approved payment.
	 *
	 * @param {Object} data The approval data containing orderID.
	 */
	async function onApprove( data ) {
		if ( 'NO' === data.liabilityShift || 'UNKNOWN' === data.liabilityShift ) {
			onError( new Error( 'This payment was flagged as possible fraud and has been rejected.' ) );
			return;
		}

		if ( data.orderID ) {
			const orderInput = document.createElement( 'input' );
			orderInput.type = 'hidden';
			orderInput.name = 'paypal_order_id';
			orderInput.value = data.orderID;
			thisForm.append( orderInput );
		}

		if ( data.subscriptionID ) {
			const subscriptionInput = document.createElement( 'input' );
			subscriptionInput.type = 'hidden';
			subscriptionInput.name = 'paypal_subscription_id';
			subscriptionInput.value = data.subscriptionID;
			thisForm.append( subscriptionInput );
		}

		const paymentSourceInput = document.createElement( 'input' );
		paymentSourceInput.type = 'hidden';
		paymentSourceInput.name = 'paypal_payment_source';

		// When onApprove is called for card fields, there is no paymentSource specified.
		paymentSourceInput.value = data.paymentSource || 'card';

		thisForm.append( paymentSourceInput );

		if ( ! submitEvent ) {
			submitEvent = new Event( 'submit', { cancelable: true, bubbles: true } );
			submitEvent.target = thisForm;
		}

		if ( typeof frmFrontForm.submitFormManual === 'function' ) {
			frmFrontForm.submitFormManual( submitEvent, thisForm );
		} else {
			thisForm.submit();
		}
	}

	/**
	 * Handle payment errors.
	 *
	 * @param {Error} err The error object.
	 */
	function onError( err ) {
		running--;
		if ( running === 0 && thisForm ) {
			if ( selectedMethod === 'card' && cardFieldsValid ) {
				enableSubmit();
			} else {
				frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'disable', 0 );
			}
		}
		displayPaymentFailure( err.message || 'Payment failed. Please try again.' );
	}

	function onCancel() {
		thisForm.classList.add( 'frm_loading_form' );
		frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'disable', 0 );
	}

	// ---- Submit Button Helpers ----

	/**
	 * Enable the submit button for the form.
	 */
	function enableSubmit() {
		if ( running > 0 ) {
			return;
		}

		thisForm.classList.add( 'frm_loading_form' );
		frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'enable', 0 );

		const event = new CustomEvent( 'frmPayPalLiteEnableSubmit', {
			detail: { form: thisForm }
		} );
		document.dispatchEvent( event );
	}

	/**
	 * Disable submit button for a target form.
	 *
	 * @param {Element} form
	 * @return {void}
	 */
	function disableSubmit( form ) {
		jQuery( form ).find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).not( '.frm_prev_page' ).attr( 'disabled', 'disabled' );

		const event = new CustomEvent( 'frmPayPalLiteDisableSubmit', {
			detail: { form }
		} );
		document.dispatchEvent( event );
	}

	function hideSubmit( form ) {
		jQuery( form ).find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).not( '.frm_prev_page' ).hide();
	}

	// ---- Error Display ----

	/**
	 * Display an error message in the payment form.
	 *
	 * @param {string} errorMessage
	 * @return {void}
	 */
	function displayPaymentFailure( errorMessage ) {
		if ( ! thisForm ) {
			return;
		}

		const statusContainer = thisForm.querySelector( '.frm-card-errors' );
		if ( statusContainer ) {
			statusContainer.textContent = errorMessage;
			statusContainer.style.display = 'block';
		}
	}

	/**
	 * Clear error messages.
	 */
	function clearErrors() {
		if ( ! thisForm ) {
			return;
		}

		const statusContainer = thisForm.querySelector( '.frm-card-errors' );
		if ( statusContainer ) {
			statusContainer.textContent = '';
			statusContainer.style.display = 'none';
		}
	}

	// ---- Form Submission ----

	/**
	 * Validate the form before submission.
	 *
	 * @param {Element} form
	 * @return {boolean} True if valid.
	 */
	function validateFormSubmit( form ) {
		if ( typeof frmFrontForm.validateFormSubmit !== 'function' ) {
			return true;
		}

		const errors = frmFrontForm.validateFormSubmit( form );
		const keys = Object.keys( errors );

		if ( 1 === keys.length && errors[ keys[ 0 ] ] === '' ) {
			keys.pop();
		}

		return 0 === keys.length;
	}

	/**
	 * Check if the current form action type should trigger payment processing.
	 *
	 * @return {boolean} True if current action type should be processed.
	 */
	function currentActionTypeShouldBeProcessed() {
		const action = jQuery( thisForm ).find( 'input[name="frm_action"]' ).val();

		if ( 'object' !== typeof window.frmProForm || 'function' !== typeof window.frmProForm.currentActionTypeShouldBeProcessed ) {
			return 'create' === action;
		}

		return window.frmProForm.currentActionTypeShouldBeProcessed(
			action,
			{ thisForm }
		);
	}

	/**
	 * Handle form submission. Routes to card submission when card is selected.
	 * For button-based methods (PayPal, Venmo, etc.) the SDK handles submission via onApprove.
	 *
	 * @param {Event} event
	 */
	async function handleFormSubmission( event ) {
		if ( ! currentActionTypeShouldBeProcessed() ) {
			return;
		}

		// Only intercept submission when card is the selected method.
		if ( selectedMethod !== 'card' ) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		submitEvent = event;

		clearErrors();

		thisForm.classList.add( 'frm_js_validate' );
		if ( ! validateFormSubmit( thisForm ) ) {
			return;
		}

		disableSubmit( thisForm );

		const meta = addName( jQuery( thisForm ) );

		const submitArgs = {};

		if ( meta.name ) {
			submitArgs.cardholderName = meta.name;
		}

		/*
		TODO Add the billing address here as well.
		Stripe calls a window.frmProForm.addAddressMeta function.
		That's included in frmstrp.js though, so we need to add a script in Pro for PayPal as well.

		billingAddress: {
			addressLine1: '555 Billing Ave',
			adminArea1: 'NY',
			adminArea2: 'New York',
			postalCode: '10001',
			countryCode: 'US'
		}
		*/

		try {
			await cardFieldsInstance.submit( submitArgs );
		} catch ( err ) {
			running--;
			if ( running === 0 && thisForm ) {
				enableSubmit();
			}
			displayPaymentFailure( err.message || 'Payment failed. Please try again.' );
		}
	}

	// ---- Price / Pay Later ----

	/**
	 * Get PayPal settings from frmPayPalVars.settings.
	 *
	 * @return {Array} Array of PayPal settings.
	 */
	function getPayPalSettings() {
		const paypalSettings = [];
		frmPayPalVars.settings.forEach( function( setting ) {
			if ( setting.gateways.includes( 'paypal' ) ) {
				paypalSettings.push( setting );
			}
		} );
		return paypalSettings;
	}

	/**
	 * Get the field IDs that affect the price.
	 *
	 * @return {Array} Array of field IDs.
	 */
	function getPriceFields() {
		const priceFields = [];
		getPayPalSettings().forEach( function( setting ) {
			if ( -1 !== setting.fields ) {
				setting.fields.forEach( function( field ) {
					if ( isNaN( field ) ) {
						priceFields.push( `field_${ field }` );
					} else {
						priceFields.push( field );
					}
				} );
			}
		} );
		return priceFields;
	}

	/**
	 * Handle price field changes.
	 *
	 * @param {Event}       _       The event object.
	 * @param {HTMLElement} field   The changed field element.
	 * @param {string}      fieldId The changed field ID.
	 */
	function priceChanged( _, field, fieldId ) {
		const price = getPriceFields();
		let run = price.includes( fieldId ) || price.includes( field.id );

		if ( ! run ) {
			for ( let i = 0; i < price.length; i++ ) {
				if ( field.id.indexOf( price[ i ] ) === 0 ) {
					run = true;
					break;
				}
			}
		}

		if ( ! run ) {
			return;
		}

		const form = field.closest ? field.closest( 'form' ) : jQuery( field ).closest( 'form' )[ 0 ];
		if ( ! form ) {
			return;
		}

		getPrice(
			function( result ) {
				updatePayLaterMessage( result.data.amount );
			}
		);
	}

	function getPrice( callback ) {
		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_get_amount' );
		formData.append( 'nonce', frmPayPalVars.nonce );

		formData.delete( 'frm_action' );
		formData.delete( 'form_key' );
		formData.delete( 'item_key' );

		fetch( frmPayPalVars.ajax, {
			method: 'POST',
			body: formData
		} )
			.then( response => response.json() )
			.then( function( result ) {
				if ( result.success && result.data && result.data.amount ) {
					callback( result );
				}
			} )
			.catch( function( err ) {
				console.error( 'Failed to get PayPal amount', err );
			} );
	}

	/**
	 * Re-render the Pay Later message with the current amount.
	 *
	 * @param {number|string} amount
	 *
	 * @return {void}
	 */
	function updatePayLaterMessage( amount ) {
		const banner = document.getElementById( 'frm-paylater-message' );
		if ( banner ) {
			banner.setAttribute( 'data-pp-amount', amount );
		}
	}

	function renderMessages() {
		if ( 'function' !== typeof paypal.Messages ) {
			return;
		}

		const container = document.getElementById( 'frm-paylater-message' );
		if ( ! container ) {
			return;
		}

		getPrice( function( result ) {
			container.setAttribute( 'data-pp-amount', result.data.amount );
		} );

		paypal.Messages( {
			style: {
				layout: 'text',
				logo: { type: 'primary' },
			}
		} ).render( '#frm-paylater-message' );
	}

	/**
	 * Check for price fields on load and trigger an initial price update.
	 */
	function checkPriceFieldsOnLoad() {
		getPriceFields().forEach( function( fieldId ) {
			const fieldContainer = document.getElementById( `frm_field_${ fieldId }_container` );
			if ( ! fieldContainer ) {
				return;
			}

			const input = fieldContainer.querySelector( 'input[name^=item_meta]' );
			if ( input && '' !== input.value ) {
				priceChanged( null, input, fieldId );
			}
		} );
	}

	// ---- Name Fields ----

	function addName( $form ) {
		let i;
		let firstField;
		let lastField;
		let firstFieldContainer;
		let lastFieldContainer;
		let firstNameID = '';
		let lastNameID = '';
		let subFieldEl;

		const cardObject = {};
		const { settings } = frmPayPalVars;

		/**
		 * Gets first, middle or last name from the given field.
		 *
		 * @param {number|HTMLElement} field        Field ID or Field element.
		 * @param {string}             subFieldName Subfield name.
		 * @return {string} Name field value.
		 */
		const getNameFieldValue = function( field, subFieldName ) {
			if ( 'object' !== typeof field ) {
				field = document.getElementById( `frm_field_${ field }_container` );
			}

			if ( ! field || 'object' !== typeof field || 'function' !== typeof field.querySelector ) {
				return '';
			}

			subFieldEl = field.querySelector( `.frm_combo_inputs_container .frm_form_subfield-${ subFieldName } input` );
			if ( ! subFieldEl ) {
				return '';
			}

			return subFieldEl.value;
		};

		for ( i = 0; i < settings.length; i++ ) {
			firstNameID = settings[ i ].first_name;
			lastNameID = settings[ i ].last_name;
		}

		/**
		 * Returns a name field container or element.
		 *
		 * @param {number}      fieldID
		 * @param {string}      type    Either 'container' or 'field'
		 * @param {object|null} $form
		 * @return {HTMLElement|null} Name field container or element.
		 */
		function getNameFieldItem( fieldID, type, $form = null ) {
			const queryForNameFieldIsFound = 'object' === typeof window.frmProForm && 'function' === typeof window.frmProForm.queryForNameField;

			if ( type === 'container' ) {
				return queryForNameFieldIsFound
					? window.frmProForm.queryForNameField( fieldID, 'container' )
					: document.querySelector( `#frm_field_${ fieldID }_container, .frm_field_${ fieldID }_container` );
			}

			return queryForNameFieldIsFound
				? window.frmProForm.queryForNameField( fieldID, 'field', $form[ 0 ] )
				: $form[ 0 ].querySelector( `#frm_field_${ fieldID }_container input, input[name="item_meta[${ fieldID }]"], .frm_field_${ fieldID }_container input` );
		}

		if ( firstNameID !== '' ) {
			firstFieldContainer = getNameFieldItem( firstNameID, 'container' );
			if ( firstFieldContainer && firstFieldContainer.querySelector( '.frm_combo_inputs_container' ) ) {
				cardObject.name = getNameFieldValue( firstFieldContainer, 'first' );
			} else {
				firstField = getNameFieldItem( firstNameID, 'field', $form );
				if ( firstField && firstField.value ) {
					cardObject.name = firstField.value;
				}
			}
		}

		if ( lastNameID !== '' ) {
			lastFieldContainer = getNameFieldItem( lastNameID, 'container' );
			if ( lastFieldContainer && lastFieldContainer.querySelector( '.frm_combo_inputs_container' ) ) {
				cardObject.name = `${ cardObject.name } ${ getNameFieldValue( lastFieldContainer, 'last' ) }`;
			} else {
				lastField = getNameFieldItem( lastNameID, 'field', $form );
				if ( lastField && lastField.value ) {
					cardObject.name = `${ cardObject.name } ${ lastField.value }`;
				}
			}
		}

		return cardObject;
	}

	// ---- Bootstrap ----

	document.addEventListener( 'DOMContentLoaded', async function() {
		if ( window.paypal ) {
			paypalInit();
			return;
		}

		const interval = setInterval(
			function() {
				if ( window.paypal ) {
					paypalInit();
					clearInterval( interval );
				}
			},
			50
		);
	} );

	jQuery( document ).on( 'frmPageChanged', function() {
		paypalInit();
	} );
}() );
