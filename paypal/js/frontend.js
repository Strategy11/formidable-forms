( function() {
	if ( ! window.frmPayPalVars ) {
		return;
	}

	// Track the state of the PayPal card fields
	let cardFieldsValid = false;
	let thisForm = null;
	let running = 0;
	let cardFieldsInstance = null;
	let submitEvent = null;

	// Track the state of each field in the card form
	const cardFields = {
		number: false,
		expiry: false,
		cvv: false
	};

	/**
	 * Initialize PayPal Card Fields (Advanced Card Payments).
	 *
	 * @return {Promise<Object>} The card fields instance.
	 */
	async function initializeCardFields() {
		const cardElement = document.querySelector( '.frm-card-element' );
		if ( ! cardElement ) {
			return null;
		}

		cardElement.classList.add( 'frm_grid_container' );

		// Create the card fields container structure
		// TODO: Make these IDs unique.
		cardElement.innerHTML = `
			<div id="paypal-button-container"></div>
			<div class="separator">OR</div>
			<div class="frm-paypal-card-number frm6" id="frm-paypal-card-number"></div>
			<div class="frm-paypal-card-expiry frm3" id="frm-paypal-card-expiry"></div>
			<div class="frm-paypal-card-cvv frm3" id="frm-paypal-card-cvv"></div>
		`;

		const renderPayPalButton = makeRenderPayPalButton( cardElement );
		const fundingSources = [
			paypal.FUNDING.BANCONTACT,
			paypal.FUNDING.BLIK,
			paypal.FUNDING.EPS,
			paypal.FUNDING.P24,
			paypal.FUNDING.TRUSTLY,
			paypal.FUNDING.SATISPAY, // Appears for Germany (EUR).
			paypal.FUNDING.SEPA, // Appears for Germany (EUR).
		];
		fundingSources.forEach( renderPayPalButton );

		renderMessages( cardElement );

		thisForm = cardElement.closest( 'form' );

		const cardFieldsConfig = {
			createOrder: createOrder,
		//	createSubscription: createSubscription,
		//	createVaultSetupToken: createVaultSetupToken,
			onApprove: onApprove,
			onError: onError,
			style: frmPayPalVars.style,
			inputEvents: {
				onChange: data => {
					cardFieldsValid = data.isFormValid;

					if ( cardFieldsValid ) {
						enableSubmit();
					} else {
						disableSubmit( thisForm );
					}
				}
			}
		};

		disableSubmit( thisForm );

		paypal.Buttons( {
		//	fundingSource: paypal.FUNDING.PAYLATER,
			createOrder: createOrder,
		//	createSubscription: createSubscription,
			onApprove: onApprove,
			onError: onError,
			onCancel: function() {
				thisForm.classList.add( 'frm_loading_form' );
				frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'disable', 0 );
			},
			// https://developer.paypal.com/sdk/js/reference/#style
			style: frmPayPalVars.buttonStyle,
		} ).render( '#paypal-button-container' );

		const cardFields = window.paypal.CardFields( cardFieldsConfig );

		// Check eligibility for card fields
		if ( ! cardFields.isEligible() ) {
			console.warn( 'PayPal Card Fields not eligible for this configuration' );
			return null;
		}

		// Render individual card fields
		cardFields.NumberField().render( '#frm-paypal-card-number' );
		cardFields.ExpiryField().render( '#frm-paypal-card-expiry' );
		cardFields.CVVField().render( '#frm-paypal-card-cvv' );

		return cardFields;
	}

	function renderMessages( cardElement ) {
		if ( 'function' !== typeof paypal.Messages ) {
			return;
		}

		const payLaterBanner = document.createElement( 'div' );
		payLaterBanner.id = 'my-pay-later-banner';
		cardElement.prepend( payLaterBanner );

		let action = false;
		frmPayPalVars.settings.forEach( function( setting ) {
			if ( -1 !== setting.gateways.indexOf( 'paypal' ) ) {
				action = setting;
			}
		} );

		// TODO We can use a value here if the amount is not dynamic.
		// Otherwise we might need to wait until we know an amount
		// and we might need to try refreshing this message when the amount
		// changes.

		paypal.Messages( buildMessagesArgs() ).render( '#my-pay-later-banner' );
	}

	function buildMessagesArgs() {
		return {
			amount: 100.00,
			style: {
				layout: 'text',
				logo: {
					type: 'primary'
				},
			}
		};
	}

	function makeRenderPayPalButton( cardElement ) {
		return function( fundingSource ) {
				const button = paypal.Buttons({
				fundingSource,
				createOrder,
				onApprove,
				onError,
			});

			if ( ! button.isEligible() ) {
				return;
			}

			const containerId = 'frm-paypal-button-' + fundingSource + '-container';
			const container = document.createElement( 'div' );
			container.id = containerId;
			cardElement.prepend( container );

			button.render( '#' + containerId );
		};
	}

	/**
	 * Create a PayPal order via AJAX.
	 *
	 * @return {Promise<string>} The order ID.
	 */
	async function createOrder() {
		thisForm.classList.add( 'frm_loading_form' );

		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_order' );
		formData.append( 'nonce', frmPayPalVars.nonce );

		// Remove a few fields so form validation does not incorrectly trigger.
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

	async function createSubscription() {
		thisForm.classList.add( 'frm_loading_form' );

		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_subscription' );
		formData.append( 'nonce', frmPayPalVars.nonce );

		// Remove a few fields so form validation does not incorrectly trigger.
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

		if ( ! orderData.success || ! orderData.data.subscriptionID ) {
			thisForm.classList.remove( 'frm_loading_form' );

			if ( 'string' === typeof orderData.data ) {
				throw new TypeError( orderData.data );
			}

			throw new Error( 'Failed to create PayPal subscription' );
		}

		return orderData.data.subscriptionID;
	}

	async function createVaultSetupToken() {
		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_vault_setup_token' );
		formData.append( 'nonce', frmPayPalVars.nonce );

		// Remove a few fields so form validation does not incorrectly trigger.
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

	/**
	 * Handle approved payment.
	 *
	 * @param {Object} data The approval data containing orderID.
	 */
	async function onApprove( data ) {
		console.log( 'onApprove', data );
		// Add the order ID to the form
		const orderInput = document.createElement( 'input' );
		orderInput.type = 'hidden';
		orderInput.name = 'paypal_order_id';
		orderInput.value = data.orderID;
		thisForm.append( orderInput );

		// If someone uses the PayPal checkout button, the form submit event doesn't actually get triggered.
		if ( ! submitEvent ) {
			submitEvent = new Event( 'submit', { cancelable: true, bubbles: true } );
			submitEvent.target = thisForm;
		}

		// Submit the form
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
			enableSubmit();
		}
		displayPaymentFailure( err.message || 'Payment failed. Please try again.' );
	}

	/**
	 * Enable the submit button for the form.
	 */
	function enableSubmit() {
		if ( running > 0 ) {
			return;
		}

		thisForm.classList.add( 'frm_loading_form' );
		frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'enable', 0 );

		// Trigger custom event for other scripts to hook into
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

		// Trigger custom event for other scripts to hook into
		const event = new CustomEvent( 'frmPayPalLiteDisableSubmit', {
			detail: { form: form }
		} );
		document.dispatchEvent( event );
	}

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
			// Pop the empty error that gets added by invisible recaptcha.
			keys.pop();
		}

		return 0 === keys.length;
	}

	/**
	 * Handle form submission with card fields.
	 *
	 * @param {Event} event
	 */
	async function handleCardSubmission( event ) {
		event.preventDefault();
		event.stopPropagation();

		submitEvent = event;

		clearErrors();

		// Validate the form first
		thisForm.classList.add( 'frm_js_validate' );
		if ( ! validateFormSubmit( thisForm ) ) {
			return;
		}

		// Increment running counter and disable the submit button
		running++;
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
			// Submit the card fields - this triggers createOrder and onApprove
			// TODO: Stop hard coding the billing address and use actual form data.
			await cardFieldsInstance.submit( submitArgs );
		} catch ( err ) {
			running--;
			if ( running === 0 && thisForm ) {
				enableSubmit();
			}
			displayPaymentFailure( err.message || 'Payment failed. Please try again.' );
		}
	}

	/**
	 * Initialize PayPal integration.
	 */
	async function paypalInit() {
		// Find the form containing the PayPal payment element
		const cardContainer = document.querySelector( '.frm-card-element' );
		if ( ! cardContainer ) {
			return;
		}

		thisForm = cardContainer.closest( 'form' );
		if ( ! thisForm ) {
			return;
		}

		// Initially disable the submit button until PayPal is ready
		disableSubmit( thisForm );

		try {
			cardFieldsInstance = await initializeCardFields();

			if ( ! cardFieldsInstance ) {
				displayPaymentFailure( 'PayPal Card Fields could not be initialized.' );
				return;
			}

			// Add event listener for form submission
			thisForm.addEventListener( 'submit', handleCardSubmission );
		} catch ( e ) {
			console.error( 'Initializing PayPal Card Fields failed', e );
			displayPaymentFailure( 'Failed to initialize payment form.' );
		}
	}

	function addName( $form ) {
		let i,
			firstField,
			lastField,
			firstFieldContainer,
			lastFieldContainer,
			firstNameID = '',
			lastNameID = '',
			subFieldEl;

		const cardObject = {};
		const settings = frmPayPalVars.settings;

		/**
		 * Gets first, middle or last name from the given field.
		 *
		 * @param {number|HTMLElement} field        Field ID or Field element.
		 * @param {string}             subFieldName Subfield name.
		 * @return {string} Name field value.
		 */
		const getNameFieldValue = function( field, subFieldName ) {
			if ( 'object' !== typeof field ) {
				field = document.getElementById( 'frm_field_' + field + '_container' );
			}

			if ( ! field || 'object' !== typeof field || 'function' !== typeof field.querySelector ) {
				return '';
			}

			subFieldEl = field.querySelector( '.frm_combo_inputs_container .frm_form_subfield-' + subFieldName + ' input' );
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
					: document.querySelector( '#frm_field_' + fieldID + '_container, .frm_field_' + fieldID + '_container' );
			}

			return queryForNameFieldIsFound
				? window.frmProForm.queryForNameField( fieldID, 'field', $form[ 0 ] )
				: $form[ 0 ].querySelector( '#frm_field_' + fieldID + '_container input, input[name="item_meta[' + fieldID + ']"], .frm_field_' + fieldID + '_container input' );
		}

		if ( firstNameID !== '' ) {
			firstFieldContainer = getNameFieldItem( firstNameID, 'container' );
			if ( firstFieldContainer && firstFieldContainer.querySelector( '.frm_combo_inputs_container' ) ) { // This is a name field.
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
			if ( lastFieldContainer && lastFieldContainer.querySelector( '.frm_combo_inputs_container' ) ) { // This is a name field.
				cardObject.name = cardObject.name + ' ' + getNameFieldValue( lastFieldContainer, 'last' );
			} else {
				lastField = getNameFieldItem( lastNameID, 'field', $form );
				if ( lastField && lastField.value ) {
					cardObject.name = cardObject.name + ' ' + lastField.value;
				}
			}
		}

		return cardObject;
	}

	document.addEventListener( 'DOMContentLoaded', async function() {
		if ( ! window.paypal ) {
			console.error( 'PayPal JS SDK failed to load properly' );
			return;
		}

		paypalInit();

		jQuery( document ).on( 'frmPageChanged', function() {
			paypalInit();
		} );
	} );
}() );

