( function() {
	if ( ! window.frmPayPalVars ) {
		return;
	}

	const clientId = frmPayPalVars.clientId;

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
			<div class="frm-paypal-card-name frm12" id="frm-paypal-card-name"></div>
			<div class="frm-paypal-card-number frm6" id="frm-paypal-card-number"></div>
			<div class="frm-paypal-card-expiry frm3" id="frm-paypal-card-expiry"></div>
			<div class="frm-paypal-card-cvv frm3" id="frm-paypal-card-cvv"></div>
		`;

		thisForm = cardElement.closest( 'form' );

		const cardFieldsConfig = {
			createOrder: createOrder,
			onApprove: onApprove,
			onError: onError,
			style: getCardFieldStyles(),
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
		//	createOrder: createOrder,
			createSubscription: createSubscription,
			onApprove: onApprove,
			onError: onError,
			style: {},
		} ).render( '#paypal-button-container' );

		const cardFields = window.paypal.CardFields( cardFieldsConfig );

		// Check eligibility for card fields
		if ( ! cardFields.isEligible() ) {
			console.warn( 'PayPal Card Fields not eligible for this configuration' );
			return null;
		}

		// Render individual card fields
		cardFields.NameField().render( '#frm-paypal-card-name' );
		cardFields.NumberField().render( '#frm-paypal-card-number' );
		cardFields.ExpiryField().render( '#frm-paypal-card-expiry' );
		cardFields.CVVField().render( '#frm-paypal-card-cvv' );

		return cardFields;
	}

	/**
	 * Get card field styles from localized vars or use defaults.
	 *
	 * @return {Object} Style configuration for PayPal card fields.
	 */
	function getCardFieldStyles() {
		if ( frmPayPalVars.style ) {
			return frmPayPalVars.style;
		}

		return {};
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

		if ( ! orderData.success || ! orderData.data.orderID ) {
			thisForm.classList.remove( 'frm_loading_form' );
			throw new Error( orderData.data || 'Failed to create PayPal order' );
		}

		return orderData.data.orderID;
	}

	/**
	 * Handle approved payment.
	 *
	 * @param {Object} data The approval data containing orderID.
	 */
	async function onApprove( data ) {
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

		try {
			// Submit the card fields - this triggers createOrder and onApprove
			// TODO: Stop hard coding the billing address and use actual form data.
			await cardFieldsInstance.submit(
				{
					billingAddress: {
						addressLine1: '555 Billing Ave',
						adminArea1: 'NY',
						adminArea2: 'New York',
						postalCode: '10001',
						countryCode: 'US'
					}
				}
			);
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
