( function() {
	if ( ! window.frmPayPalVars ) {
		return;
	}

	// Track the eligible funding sources
	const renderedButtons = [];

	// Track the state of the PayPal card fields
	let cardFieldsValid = false;
	let thisForm = null;
	let running = 0;
	let cardFieldsInstance = null;
	let submitEvent = null;

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

		thisForm = cardElement.closest( 'form' );

		const settings = getPayPalSettings()[ 0 ];
		const isRecurring = 'single' !== settings.one;

		let cardFields = {};

		if ( ! isRecurring ) {
			cardFields = window.paypal.CardFields(
				{
					onApprove,
					onError,
					createOrder,
					style: frmPayPalVars.style,
					inputEvents: {
						onChange: data => {
							cardFieldsValid = data.isFormValid;
							console.log( 'onChange', data );

							const allEmpty = Object.values( data.fields ).every( field => field.isEmpty );
							const buttonContainer = document.getElementById( 'paypal-button-container' );
							const separator = buttonContainer.parentNode.querySelector( '.separator' );

							if ( allEmpty ) {
								buttonContainer.style.display = 'block';
								separator.style.display = 'block';
							} else {
								buttonContainer.style.display = 'none';
								separator.style.display = 'none';
							}

							if ( cardFieldsValid ) {
								enableSubmit();
							} else {
								disableSubmit( thisForm );
							}
						}
					}
				}
			);
		} else {
			// Card fields require a Vault setup token.
			// For now, we will just disable the card fields
			// as we do not support vaulting yet.
			cardFields = {
				isEligible() {
					return false;
				}
			};
		}

		// Create the card fields container structure
		// TODO: Make these IDs unique.
		cardElement.innerHTML = '';

		const layout = getPayPalSettings()[ 0 ].layout;

		const cardFieldsEligible = cardFields.isEligible() && layout !== 'checkout_only';

		const buttonIsEnabled = getPayPalSettings()[ 0 ].layout !== 'card_only'; // TODO: Put this behind a setting.
		if ( buttonIsEnabled ) {
			const buttonContainer = document.createElement( 'div' );
			buttonContainer.id = 'paypal-button-container';
			cardElement.prepend( buttonContainer );

			const renderPayPalButton = makeRenderPayPalButton( cardElement );
			const fundingSources = [
				paypal.FUNDING.BANCONTACT,
				paypal.FUNDING.BLIK,
				paypal.FUNDING.EPS,
				paypal.FUNDING.P24,
				paypal.FUNDING.TRUSTLY,
				paypal.FUNDING.SATISPAY,
				paypal.FUNDING.SEPA,
				paypal.FUNDING.MYBANK,
				paypal.FUNDING.IDEAL,
				paypal.FUNDING.PAYLATER
			];
			fundingSources.forEach( renderPayPalButton );

			if ( renderedButtons.includes( paypal.FUNDING.PAYLATER ) ) {
				renderMessages( cardElement );
				jQuery( document ).on( 'frmFieldChanged', priceChanged );
				checkPriceFieldsOnLoad();
			}

			const buttonConfig = {
				onApprove,
				onError,
				onCancel,
				style: frmPayPalVars.buttonStyle,
				fundingSource: paypal.FUNDING.PAYPAL,
			};

			const setting = getPayPalSettings()[ 0 ];
			const isRecurring = 'single' !== setting.one;

			if ( isRecurring ) {
				buttonConfig.createSubscription = createSubscription;
			} else {
				buttonConfig.createOrder = createOrder;
			}

			paypal.Buttons( buttonConfig ).render( '#paypal-button-container' );
		}

		if ( ! cardFieldsEligible ) {
			return null;
		}

		cardElement.classList.add( 'frm_grid_container' );

		if ( buttonIsEnabled ) {
			const separator = document.createElement( 'div' );
			separator.classList.add( 'separator' );
			separator.textContent = 'OR'; // TODO: Make this customizable.
			cardElement.append( separator );
		}

		const cardNumberWrapper = document.createElement( 'div' );
		cardNumberWrapper.id = 'frm-paypal-card-number';
		cardNumberWrapper.classList.add( 'frm6', 'frm-payment-card-number' );

		const expiryWrapper = document.createElement( 'div' );
		expiryWrapper.id = 'frm-paypal-card-expiry';
		expiryWrapper.classList.add( 'frm3', 'frm-payment-card-expiry' );

		const cvvWrapper = document.createElement( 'div' );
		cvvWrapper.id = 'frm-paypal-card-cvv';
		cvvWrapper.classList.add( 'frm3', 'frm-payment-card-cvv' );

		cardElement.append( cardNumberWrapper );
		cardElement.append( expiryWrapper );
		cardElement.append( cvvWrapper );

		disableSubmit( thisForm );

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

		// TODO We can use a value here if the amount is not dynamic.
		// Otherwise we might need to wait until we know an amount
		// and we might need to try refreshing this message when the amount
		// changes.

		getPrice( function( result ) {
			payLaterBanner.setAttribute( 'data-pp-amount', result.data.amount );
		} );

		paypal.Messages( buildMessagesArgs() ).render( '#my-pay-later-banner' );
	}

	function buildMessagesArgs() {
		return {
			style: {
				layout: 'text',
				logo: {
					type: 'primary'
				},
			}
		};
	}

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
						priceFields.push( 'field_' + field );
					} else {
						priceFields.push( field );
					}
				} );
			}
		} );
		return priceFields;
	}

	/**
	 * Handle price field changes. Calls AJAX to get the updated amount
	 * and refreshes the Pay Later message.
	 *
	 * @param {Event}       _       The event object (unused).
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

		// Remove a few fields so form validation does not incorrectly trigger.
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
		const banner = document.getElementById( 'my-pay-later-banner' );
		if ( banner ) {
			banner.setAttribute( 'data-pp-amount', amount );
		}
	}

	/**
	 * Check for price fields on load and trigger an initial price update.
	 */
	function checkPriceFieldsOnLoad() {
		getPriceFields().forEach( function( fieldId ) {
			const fieldContainer = document.getElementById( 'frm_field_' + fieldId + '_container' );
			if ( ! fieldContainer ) {
				return;
			}

			const input = fieldContainer.querySelector( 'input[name^=item_meta]' );
			if ( input && '' !== input.value ) {
				priceChanged( null, input, fieldId );
			}
		} );
	}

	function makeRenderPayPalButton( cardElement ) {
		const setting = getPayPalSettings()[ 0 ];
		const isRecurring = 'single' !== setting.one;

		return function( fundingSource ) {
			const buttonConfig = {
				fundingSource,
				onApprove,
				onError,
				onCancel,
				style: frmPayPalVars.buttonStyle,
			};

			if ( isRecurring ) {
				buttonConfig.createSubscription = createSubscription;
			} else {
				buttonConfig.createOrder = createOrder;
			}

			const button = paypal.Buttons( buttonConfig );

			if ( ! button.isEligible() ) {
				return;
			}

			const containerId = 'frm-paypal-button-' + fundingSource + '-container';
			const container = document.createElement( 'div' );
			container.id = containerId;
			cardElement.prepend( container );

			button.render( '#' + containerId );

			renderedButtons.push( fundingSource );
		};
	}

	/**
	 * Create a PayPal order via AJAX.
	 *
	 * @param {Object} data
	 * @return {Promise<string>} The order ID.
	 */
	async function createOrder( data ) {
		console.log( 'createOrder', data );

		thisForm.classList.add( 'frm_loading_form' );

		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_paypal_create_order' );
		formData.append( 'nonce', frmPayPalVars.nonce );
		formData.append( 'payment_source', data.paymentSource );

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

	async function createSubscription( data ) {
		console.log( 'createSubscription', data );

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

		if ( 'NO' === data.liabilityShift || 'UNKNOWN' === data.liabilityShift ) {
			onError( new Error( 'This payment was flagged as possible fraud and has been rejected.' ) );
			return;
		}

		// Add the order ID to the form
		const orderInput = document.createElement( 'input' );
		orderInput.type = 'hidden';
		orderInput.name = 'paypal_order_id';
		orderInput.value = data.orderID;
		thisForm.append( orderInput );

		const paymentSourceInput = document.createElement( 'input' );
		paymentSourceInput.type = 'hidden';
		paymentSourceInput.name = 'paypal_payment_source';
		paymentSourceInput.value = data.paymentSource;
		thisForm.append( paymentSourceInput );

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
		frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'disable', 0 );
	}

	function onCancel() {
		thisForm.classList.add( 'frm_loading_form' );
		frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'disable', 0 );
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
			detail: { form }
		} );
		document.dispatchEvent( event );
	}

	function hideSubmit( form ) {
		jQuery( form ).find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).not( '.frm_prev_page' ).hide();
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

		//		submitArgs.verification = {
		//			method: 'SCA', // Standard for PSD2 compliance
		//		};

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

		try {
			cardFieldsInstance = await initializeCardFields();

			if ( ! cardFieldsInstance ) {
				// TOOD: We would need to not hide the button if PayPal may be used conditionally.
				disableSubmit( thisForm );
				hideSubmit( thisForm );
				return;
			}

			// Add event listener for form submission
			thisForm.addEventListener( 'submit', handleCardSubmission );
		} catch ( e ) {
			console.error( 'Initializing PayPal Card Fields failed', e );
			displayPaymentFailure( 'Failed to initialize payment form.' );
		}

		// Initially disable the submit button until PayPal is ready
		disableSubmit( thisForm );
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

