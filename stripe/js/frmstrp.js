( function() {
	let thisForm = false,
		formID = 0,
		event = false,
		frmstripe,
		running = 100,
		elements,
		isStripeLink = false,
		linkAuthenticationElementIsComplete = false,
		stripeLinkElementIsComplete = false;

	const triggerCustomEvent = function( el, eventName, data ) {
		frmFrontForm.triggerCustomEvent( el, eventName, data );
	};

	/**
	 * @param {Event} e
	 */
	function validateForm( e ) {
		thisForm = this;
		formID = jQuery( thisForm ).find( 'input[name="form_id"]' ).val();

		if ( shouldProcessForm() ) {
			e.preventDefault();
			event = e;
			processForm();
			return;
		}

		frmFrontForm.submitFormManual( e, thisForm );

		return false;
	}

	/**
	 * @return {boolean} True if form should be processed by Stripe.
	 */
	function shouldProcessForm() {
		if ( formID != frm_stripe_vars.form_id ) {
			return false;
		}

		if ( ! currentActionTypeShouldBeProcessed() ) {
			return false;
		}

		const ccField = jQuery( thisForm ).find( '.frm-card-element' );
		if ( ccField.length && ! ccField.is( ':hidden' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return {boolean} True if current action type should be processed.
	 */
	function currentActionTypeShouldBeProcessed() {
		const action = jQuery( thisForm ).find( 'input[name="frm_action"]' ).val();

		if ( 'object' !== typeof window.frmProForm || 'function' !== typeof window.frmProForm.currentActionTypeShouldBeProcessed ) {
			return 'create' === action;
		}

		return window.frmProForm.currentActionTypeShouldBeProcessed(
			action,
			{
				thisForm: thisForm
			}
		);
	}

	function processForm() {
		const $form = jQuery( thisForm );

		// Run javascript validation.
		$form.addClass( 'frm_js_validate' );

		if ( ! validateFormSubmit( $form ) ) {
			return;
		}

		frmFrontForm.showSubmitLoading( $form );
		const meta = addName( $form );

		if ( 'object' === typeof window.frmProForm && 'function' === typeof window.frmProForm.addAddressMeta ) {
			window.frmProForm.addAddressMeta( $form, meta );
		}

		if ( ! isStripeLink ) {
			return;
		}

		stripeLinkSubmit( $form.get( 0 ), meta );
	}

	/**
	 * Submit a form for Stripe link.
	 * First it forces a form submission (with AJAX) so create an entry before calling confirmSetup/confirmPayment.
	 * confirmSetup gets called for a recurring payment and confirmPayment is called for one-time payments.
	 * In both cases they redirect to the return url which uses the frmstrplinkreturn AJAX action.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} object
	 * @param {Object}  meta
	 * @return {void}
	 */
	function stripeLinkSubmit( object, meta ) {
		object.classList.add( 'frm_trigger_event_on_submit', 'frm_ajax_submit' );

		object.addEventListener( 'frmSubmitEvent', confirmPayment );
		running = 0;
		submitForm();

		function confirmPayment( event ) {
			if ( ! checkEventDataForError( event ) ) {
				return;
			}

			window.onpageshow = function( event ) {
				// Force the form to reload on back button after submitting.
				if ( event.persisted || ( window.performance && window.performance.getEntriesByType( 'navigation' )[ 0 ].type === 'back_forward' ) ) {
					window.location.reload();
				}
			};

			let params = {
				elements: elements,
				confirmParams: {
					return_url: getReturnUrl()
				}
			};

			if ( 'object' === typeof window.frmProForm && 'function' === typeof frmProForm.beforeConfirmPayment ) {
				params = frmProForm.beforeConfirmPayment( params, meta );
			}

			const confirmFunction = isRecurring() ? 'confirmSetup' : 'confirmPayment';

			frmstripe[ confirmFunction ]( params ).then( handleConfirmPromise );
		}

		function getReturnUrl() {
			const url = new URL( frm_stripe_vars.ajax ); // eslint-disable-line compat/compat

			url.searchParams.append( 'action', 'frmstrplinkreturn' );
			return url.toString();
		}

		function handleConfirmPromise( result ) {
			if ( result.error ) {
				handleConfirmPaymentError( result.error );
			}
		}

		function handleConfirmPaymentError( error ) {
			running--;
			enableSubmit();

			const fieldset = jQuery( object ).find( '.frm_form_field' );
			fieldset.removeClass( 'frm_doing_ajax' );

			object.classList.remove( 'frm_loading_form' );

			// Don't show validation_error here as those are added automatically to the email and postal code fields, etc.
			if ( 'card_error' === error.type || 'invalid_request_error' === error.type || 'form_submit_error' === error.type ) {
				const cardErrors = object.querySelector( '.frm-card-errors' );
				if ( cardErrors ) {
					cardErrors.textContent = error.message;
				}
			}
		}

		/**
		 * Check the event content for any possible errors.
		 * Some types of errors will appear here, like the errors added when calling FrmStrpLiteActionsController::trigger_gateway.
		 *
		 * @since 6.10
		 *
		 * @param {CustomEvent} event
		 * @return {boolean} True if no errors found in event data.
		 */
		function checkEventDataForError( event ) {
			if ( ! event.frmData || ! event.frmData.content.length || -1 === event.frmData.content.indexOf( '<div class="frm_error_style' ) ) {
				return true;
			}

			const element = document.createElement( 'div' );
			element.innerHTML = event.frmData.content;

			const error = element.querySelector( '.frm_error_style' );
			if ( error ) {
				handleConfirmPaymentError( {
					type: 'form_submit_error',
					message: error.textContent
				} );
				return false;
			}

			return true;
		}
	}

	/**
	 * Check if the stripe setting is for a recurring payment.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @return {boolean} True if payment is recurring.
	 */
	function isRecurring() {
		let isRecurring = false;

		each(
			getStripeSettings(),
			function( setting ) {
				if ( 'recurring' === setting.one ) {
					isRecurring = true;
					return false;
				}
			}
		);

		return isRecurring;
	}

	/**
	 * @param {Object} $form
	 * @return {boolean} false if there are errors.
	 */
	function validateFormSubmit( $form ) {
		const errors = frmFrontForm.validateFormSubmit( $form );
		const keys = Object.keys( errors );

		if ( 1 === keys.length && errors[ keys[ 0 ] ] === '' ) {
			// Pop the empty error that gets added by invisible recaptcha.
			keys.pop();
		}

		return 0 === keys.length;
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
		const settings = frm_stripe_vars.settings;

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

	function submitForm() {
		if ( running > 0 ) {
			return;
		}
		frmFrontForm.submitFormManual( event, thisForm );
	}

	function enableSubmit() {
		if ( running > 0 ) {
			return;
		}

		thisForm.classList.add( 'frm_loading_form' );
		frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'enable', 0 );

		triggerCustomEvent( document, 'frmStripeLiteEnableSubmit', { form: thisForm } );
	}

	function getPriceFields() {
		const priceFields = [];

		function checkStripeSettingForPriceFields( setting ) {
			if ( -1 !== setting.fields ) {
				each( setting.fields, addFieldDataToPriceFieldsArray );
			}
		}

		function addFieldDataToPriceFieldsArray( field ) {
			if ( isNaN( field ) ) {
				priceFields.push( 'field_' + field );
			} else {
				priceFields.push( field );
			}
		}

		each( getStripeSettings(), checkStripeSettingForPriceFields );

		return priceFields;
	}

	/**
	 * Get all variables from frm_stripe_vars.settings that match the Stripe gateway.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @return {Array} Array of Stripe settings.
	 */
	function getStripeSettings() {
		const stripeSettings = [];
		each(
			frm_stripe_vars.settings,
			function( setting ) {
				if ( -1 !== setting.gateways.indexOf( 'stripe' ) ) {
					stripeSettings.push( setting );
				}
			}
		);
		return stripeSettings;
	}

	// Update price intent on change.
	function priceChanged( _, field, fieldId ) {
		let i;
		let data;
		const price = getPriceFields();
		let run = price.includes( fieldId ) || price.includes( field.id );
		if ( ! run ) {
			for ( i = 0; i < price.length; i++ ) {
				if ( field.id.indexOf( price[ i ] ) === 0 ) {
					run = true;
				}
			}
		}
		if ( run ) {
			data = {
				action: 'frm_strp_amount',
				form: JSON.stringify( jQuery( field ).closest( 'form' ).serializeArray() ),
				nonce: frm_stripe_vars.nonce
			};
			postAjax( data, function() {
				// Amount has been conditionally updated.
			} );
		}
	}

	function postAjax( data, success ) {
		const xmlHttp = new XMLHttpRequest();
		const params = typeof data == 'string' ? data : Object.keys( data ).map(
			function( k ) {
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[ k ] );
			}
		).join( '&' );

		xmlHttp.open( 'post', frm_stripe_vars.ajax, true );
		xmlHttp.onreadystatechange = function() {
			let response;
			if ( xmlHttp.readyState > 3 && xmlHttp.status == 200 ) {
				response = xmlHttp.responseText;
				if ( response !== '' ) {
					try {
						response = JSON.parse( response );
					} catch ( error ) {
						response = '';
					}
				}
				success( response );
			}
		};
		xmlHttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		xmlHttp.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded' );
		xmlHttp.send( params );
		return xmlHttp;
	}

	function loadElements() {
		if ( document.getElementsByClassName( 'frm-card-element' ).length ) {
			maybeLoadStripeLink();
		}
	}

	/**
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @return {boolean} True if stripe link loads successfully.
	 */
	function maybeLoadStripeLink() {
		const stripeLinkForm = document.querySelector( 'form.frm_stripe_link_form' );
		if ( ! stripeLinkForm ) {
			return false;
		}

		const formId = parseInt( stripeLinkForm.querySelector( 'input[name="form_id"]' ).value );
		const intentField = stripeLinkForm.querySelector( 'input[name="frmintent' + formId + '[]"]' );

		if ( ! intentField ) {
			return false;
		}

		disableSubmit( stripeLinkForm );
		loadStripeLinkElements( intentField.value );

		triggerCustomEvent(
			document,
			'frmStripeLiteLoad',
			{
				form: stripeLinkForm
			}
		);

		return true;
	}

	/**
	 * Disable submit button for a target form.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} form
	 * @return {void}
	 */
	function disableSubmit( form ) {
		jQuery( form ).find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).not( '.frm_prev_page' ).attr( 'disabled', 'disabled' );
		triggerCustomEvent( document, 'frmStripeLiteDisableSubmit', { form: form } );
	}

	/**
	 * Load elements for Stripe link (a Link Authentication Element and a Payment Element).
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {string} clientSecret
	 * @return {void}
	 */
	function loadStripeLinkElements( clientSecret ) {
		const cardElement = document.querySelector( '.frm-card-element' );
		if ( ! cardElement ) {
			return;
		}

		// Customize the Stripe elements using the Stripe Appearance API.
		const appearance = {
			theme: 'stripe',
			variables: {
				fontSizeBase: frm_stripe_vars.baseFontSize,
				colorText: maybeAdjustColorForStripe( frm_stripe_vars.appearanceRules[ '.Input' ].color ),
				colorBackground: maybeAdjustColorForStripe( frm_stripe_vars.appearanceRules[ '.Input' ].backgroundColor ),
				fontSmooth: 'auto'
			},
			rules: frm_stripe_vars.appearanceRules
		};
		elements = frmstripe.elements( { clientSecret: clientSecret, appearance: appearance } );
		isStripeLink = true;

		insertAuthenticationElement( cardElement );
		insertPaymentElement( cardElement );

		triggerCustomEvent(
			document,
			'frmStripeLiteLoadElements',
			{ cardElement: cardElement }
		);
	}

	/**
	 * Stripe doesn't support RGBA so convert it to HEX.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {string} color
	 * @return {string} Converted color value.
	 */
	function maybeAdjustColorForStripe( color ) {
		if ( 0 !== color.indexOf( 'rgba' ) ) {
			return color;
		}

		const rgba = color.replace( /^rgba?\(|\s+|\)$/g, '' ).split( ',' );
		/* eslint-disable no-bitwise */
		return `#${ ( ( 1 << 24 ) + ( parseInt( rgba[ 0 ], 10 ) << 16 ) + ( parseInt( rgba[ 1 ], 10 ) << 8 ) + parseInt( rgba[ 2 ], 10 ) )
			.toString( 16 )
			.slice( 1 ) }`;
	}

	/**
	 * The Authentication Element includes an email field that works with the Payment element.
	 * If the email matches a Stripe link account, this field will also include the 6 digit code prompt for using your linked credit card instead.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} cardElement
	 * @return {void}
	 */
	function insertAuthenticationElement( cardElement ) {
		let emailInput, cardFieldContainer;

		let addAboveCardElement = true;
		const emailField = checkForEmailField();
		const authenticationMountTarget = createMountTarget( 'frm-link-authentication-element' );

		if ( false !== emailField ) {
			if ( 'hidden' === emailField.getAttribute( 'type' ) ) {
				emailInput = emailField;
			} else {
				addAboveCardElement = false;
				emailInput = emailField.querySelector( 'input' );
				replaceEmailField( emailField, emailInput, authenticationMountTarget );
			}
		}

		if ( addAboveCardElement ) {
			// If no email field is found, add the email field above the credit card.
			cardFieldContainer = cardElement.closest( '.frm_form_field' );
			cardFieldContainer.parentNode.insertBefore( authenticationMountTarget, cardFieldContainer );

			triggerCustomEvent(
				document,
				'frmStripeLiteAddAuthElementAboveCardElement',
				{
					cardElement: cardElement,
					cardFieldContainer: cardFieldContainer,
					authenticationMountTarget: authenticationMountTarget
				}
			);
		}

		const defaultEmailValue = false !== emailField ? getSettingFieldValue( emailField ) : '';
		const authenticationElement = elements.create(
			'linkAuthentication',
			{
				defaultValues: {
					email: defaultEmailValue
				}
			}
		);
		authenticationElement.mount( '.frm-link-authentication-element' );
		authenticationElement.on( 'change', getAuthenticationChangeHandler( cardElement, emailInput ) );
	}

	/**
	 * Get a handler to listen for Authentication element changes.
	 * This is used to sync an email value to a hidden email input if one is mapped to the Stripe setting.
	 * This is also used to toggle conversational buttons based of whether the event is "complete" or not.
	 * In a non-conversational form we need to check if the authentication element is complete as well.
	 * If we do not, the button could still be disabled after everything is filled out if we fill out the email last.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} cardElement
	 * @param {Element} emailInput
	 * @return {Function} Authentication change handler function.
	 */
	function getAuthenticationChangeHandler( cardElement, emailInput ) {
		function syncEmailInput( emailValue ) {
			if ( 'string' === typeof emailValue && emailValue.length ) {
				emailInput.value = emailValue;
			}
		}

		return function( event ) {
			linkAuthenticationElementIsComplete = event.complete;

			if ( linkAuthenticationElementIsComplete && 'undefined' !== typeof emailInput ) {
				syncEmailInput( event.value.email );
			}

			const form = cardElement.closest( 'form' );

			if (
				'object' === typeof window.frmChatForm &&
				'function' === typeof frmChatForm.maybeHandleAuthenticationChange &&
				frmChatForm.maybeHandleAuthenticationChange( form, event.complete )
			) {
				// Allow conversational forms to override behaviour and exit early.
				return;
			}

			if ( readyToSubmitStripeLink( form ) ) {
				thisForm = form;
				running = 0;
				enableSubmit();
			} else {
				disableSubmit( form );
			}
		};
	}

	/**
	 * Hide email field and put the Stripe link authentication element to be used in its place.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} emailField
	 * @param {Element} emailInput
	 * @param {Element} authenticationMountTarget
	 * @return {void}
	 */
	function replaceEmailField( emailField, emailInput, authenticationMountTarget ) {
		emailField.insertBefore( authenticationMountTarget, emailInput );
		emailInput.type = 'hidden';
		const emailLabel = emailField.querySelector( '.frm_primary_label' );

		if ( emailLabel ) {
			// Authentication elements include an Email label already, so hide the Formidable label.
			emailLabel.style.display = 'none';
		}
	}

	/**
	 * Returns the layout for the Stripe Link elements.
	 *
	 * @since 6.21
	 *
	 * @return {string} Layout type for Stripe elements.
	 */
	function getLayout() {
		const settings = getStripeSettings()[ 0 ];
		return ( settings.hasOwnProperty( 'layout' ) && settings.layout ) || 'tabs';
	}

	/**
	 * The Payment element for Stripe link includes credit card, country, and postal code.
	 * When a new Stripe link account is being set up, it will also include an additional block underneath that asks for Phone Number and Full Name.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} cardElement
	 * @return {void}
	 */
	function insertPaymentElement( cardElement ) {
		// Add the payment element above the credit card field.
		// With Stripe Link this is used instead of a Credit Card field (it still includes Credit Card fields).
		cardElement.parentNode.insertBefore( createMountTarget( 'frm-payment-element' ), cardElement );

		const paymentElement = elements.create(
			'payment',
			{
				layout: {
					type: getLayout()
				},
				defaultValues: {
					billingDetails: {
						name: getFullNameValueDefault(),
						phone: ''
					}
				}
			}
		);
		paymentElement.mount( '.frm-payment-element' );
		paymentElement.on( 'change', handlePaymentElementChange );

		function handlePaymentElementChange( event ) {
			stripeLinkElementIsComplete = event.complete;
			toggleButtonsOnPaymentElementChange( cardElement );
			triggerCustomEvent(
				document,
				'frmStripeLitePaymentElementChange',
				{
					complete: event.complete
				}
			);
		}
	}

	/**
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} cardElement
	 * @return {void}
	 */
	function toggleButtonsOnPaymentElementChange( cardElement ) {
		const form = cardElement.closest( '.frm-show-form' );

		if (
			'object' === typeof window.frmChatForm &&
			'function' === typeof frmChatForm.maybeHandlePaymentChange &&
			frmChatForm.maybeHandlePaymentChange( form, stripeLinkElementIsComplete )
		) {
			// Allow conversational forms to override behaviour and exit early.
			return;
		}

		// Handle final question or non-conversational form.
		if ( readyToSubmitStripeLink( form ) ) {
			thisForm = form;
			running = 0;
			enableSubmit();
		} else {
			disableSubmit( form );
		}
	}

	/**
	 * The submit button toggles enabled/disabled based on if the payment element is "complete" or not.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} form
	 * @return {boolean} True if ready to submit Stripe link.
	 */
	function readyToSubmitStripeLink( form ) {
		if ( ! linkAuthenticationElementIsComplete || ! stripeLinkElementIsComplete ) {
			return false;
		}

		if ( 'object' !== typeof window.frmProForm || 'function' !== typeof window.frmProForm.submitButtonIsConditionallyDisabled ) {
			return true;
		}

		return ! window.frmProForm.submitButtonIsConditionallyDisabled( form );
	}

	/**
	 * Check Stripe settings for first name and last name fields for the default "Full Name" value for Stripe Link's payment element.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @return {string} Full name value for Stripe.
	 */
	function getFullNameValueDefault() {
		const nameValues = [];
		const firstNameField = checkForStripeSettingField( 'first_name' );
		if ( false !== firstNameField ) {
			nameValues.push( getSettingFieldValue( firstNameField ) );
		}
		const lastNameField = checkForStripeSettingField( 'last_name' );
		if ( false !== lastNameField ) {
			nameValues.push( getSettingFieldValue( lastNameField ) );
		}
		return nameValues.join( ' ' );
	}

	/**
	 * Get value for a form field. It may be a field container or a hidden input if it's a field from another page.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Element} field
	 * @return {string} Field value.
	 */
	function getSettingFieldValue( field ) {
		let value;
		if ( 'hidden' === field.getAttribute( 'type' ) ) {
			value = field.value;
		} else {
			value = field.querySelector( 'input' ).value;
		}
		return value;
	}

	/**
	 * Check Stripe settings and DOM for a mapped email field.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @return {Element|false} Email field element or false.
	 */
	function checkForEmailField() {
		return checkForStripeSettingField( 'email' );
	}

	/**
	 * @param {string} settingKey supports 'first_name', 'last_name', and 'email'.
	 * @return {Element|false} Setting field element or false.
	 */
	function checkForStripeSettingField( settingKey ) {
		let settingField = false;

		each( getStripeSettings(), checkStripeSettingForField );

		function checkStripeSettingForField( currentSetting ) {
			let currentFieldId, fieldMatchByKey, fieldContainer, hiddenInput;

			if ( 'string' !== typeof currentSetting[ settingKey ] || ! currentSetting[ settingKey ].length ) {
				return;
			}

			const currentSettingValue = currentSetting[ settingKey ];
			const settingIsWrappedAsShortcode = '[' === currentSettingValue[ 0 ] && ']' === currentSettingValue[ currentSettingValue.length - 1 ];

			if ( settingIsWrappedAsShortcode ) {
				// Email is wrapped as a shortcode.
				currentFieldId = currentSettingValue.substr( 1, currentSettingValue.length - 2 );

				if ( isNaN( currentFieldId ) ) {
					// If it is not a number, try as a field key.
					fieldMatchByKey = fieldContainer = document.getElementById( 'field_' + currentFieldId );
				}
			} else {
				// First name and last name are not wrapped as shortcodes.
				currentFieldId = currentSettingValue;
			}

			if ( fieldMatchByKey ) {
				fieldContainer = fieldMatchByKey.closest( '.frm_form_field' );
			} else {
				fieldContainer = document.getElementById( 'frm_field_' + currentFieldId + '_container' );
			}

			if ( ! fieldContainer ) {
				hiddenInput = document.querySelector( 'input[name="item_meta[' + currentFieldId + ']"]' );

				if ( ! hiddenInput ) {
					if ( 'first_name' === settingKey ) {
						hiddenInput = document.querySelector( 'input[name="item_meta[' + currentFieldId + '][first]"]' );
					} else if ( 'last_name' === settingKey ) {
						hiddenInput = document.querySelector( 'input[name="item_meta[' + currentFieldId + '][last]"]' );
					}
				}

				if ( hiddenInput ) {
					settingField = hiddenInput;
					return false;
				}

				return;
			}

			settingField = fieldContainer;
			return false;
		}

		return settingField;
	}

	/**
	 * Create and return a new element to use for mounting a Stripe element to.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {string} className
	 * @return {Element} New mount target element.
	 */
	function createMountTarget( className ) {
		const newElement = document.createElement( 'div' );
		newElement.className = className + ' frm_form_field form-field';
		return newElement;
	}

	/**
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param {Array|NodeList} items
	 * @param {Function}       callback
	 * @return {void}
	 */
	function each( items, callback ) {
		let index;
		const length = items.length;
		for ( index = 0; index < length; index++ ) {
			if ( false === callback( items[ index ], index ) ) {
				break;
			}
		}
	}

	/**
	 * Check for Price fields on load and possibly update the intent's price.
	 * This is required when a Stripe action uses a shortcode amount when
	 * the amount never changes after load.
	 *
	 * @return {void}
	 */
	function checkPriceFieldsOnLoad() {
		each(
			getPriceFields(),
			function( fieldId ) {
				const fieldContainer = document.getElementById( 'frm_field_' + fieldId + '_container' );
				if ( ! fieldContainer ) {
					return;
				}

				const input = fieldContainer.querySelector( 'input[name^=item_meta]' );
				if ( input && '' !== input.value ) {
					priceChanged( null, input, fieldId );
				}
			}
		);
	}

	jQuery( document ).ready(
		function() {
			const stripeParams = {
				locale: frm_stripe_vars.locale,
				stripeAccount: frm_stripe_vars.account_id
			};

			frmstripe = Stripe( frm_stripe_vars.publishable_key, stripeParams );
			loadElements();
			jQuery( document ).on( 'frmPageChanged', loadElements );
			jQuery( document ).off( 'submit.formidable', '.frm-show-form' );
			jQuery( document ).on( 'submit.frmstrp', '.frm-show-form', validateForm );
			jQuery( document ).on( 'frmFieldChanged', priceChanged );
			checkPriceFieldsOnLoad();
		}
	);

	window.frmStripeLiteForm = {
		readyToSubmitStripeLink: readyToSubmitStripeLink,
		processForm: function( _, e, form ) {
			event = e;
			thisForm = form;
			processForm();
		}
	};
}() );
