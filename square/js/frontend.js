( function() {
	// TODO This needs to support a global var so we can switch between sandbox and production.
	const appId		 = 'sandbox-sq0idb-MXl8ilzmhAgsHWKV9c6ycQ';
	const locationId = 'L7Q1NBZ6SSJ79';

	// Track the state of the Square card element
	let squareCardElementIsComplete = false;
	let thisForm = null;
	let running = 0;

	let cardGlobal;

	// Track the state of each field in the card form
	const cardFields = {
		cardNumber: false,
		expirationDate: false,
		cvv: false,
		postalCode: false
	};

	async function initializeCard( payments ) {
		const card = await payments.card();
		await card.attach( '#card-container' );

		// Add event listener to track when the card form is valid
		card.addEventListener( 'focusClassRemoved', ( e ) => {
			const field = e.detail.field;
			const value = e.detail.currentState.isCompletelyValid;
			cardFields[field] = value;

			// Check if all fields are valid
			squareCardElementIsComplete = Object.values( cardFields ).every( item => item === true );

			// Update form submit button based on form validity
			if ( thisForm ) {
				if ( squareCardElementIsComplete ) {
					enableSubmit();
				} else {
					disableSubmit( thisForm );
				}
			}
		} );

		return card;
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
		const event = new CustomEvent( 'frmSquareLiteEnableSubmit', { 
			detail: { form: thisForm }
		});
		document.dispatchEvent(event);
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
		const event = new CustomEvent( 'frmSquareLiteDisableSubmit', { 
			detail: { form: form }
		});
		document.dispatchEvent( event );
	}

	async function createPayment( event, token, verificationToken ) {
		const tokenInput = document.createElement('input');
		tokenInput.type = 'hidden';
		tokenInput.value = token;
		tokenInput.setAttribute('name', 'square-token');

		const verificationInput = document.createElement('input');
		verificationInput.type = 'hidden';
		verificationInput.value = verificationToken;
		verificationInput.setAttribute( 'name', 'square-verification-token' );

		// Use the thisForm variable that we set earlier
		if ( thisForm ) {
			thisForm.appendChild( tokenInput );
			thisForm.appendChild( verificationInput );

			if ( typeof frmFrontForm.submitFormManual === 'function' ) {
				frmFrontForm.submitFormManual( event, thisForm );
			} else {
				// Fallback if submitFormManual is not available
				thisForm.submit();
			}
		}
	}

	async function tokenize( paymentMethod ) {
		const tokenResult = await paymentMethod.tokenize();

		if ( tokenResult.status === 'OK' ) {
			return tokenResult.token;
		}

		let errorMessage = `Tokenization failed with status: ${tokenResult.status}`;
		if ( tokenResult.errors ) {
			errorMessage += ` and errors: ${JSON.stringify( tokenResult.errors )}`;
		}

		throw new Error( errorMessage );
	}

	// Required in SCA Mandated Regions: Learn more at https://developer.squareup.com/docs/sca-overview
	async function verifyBuyer( payments, token ) {
		const verificationDetails = {
			// TODO How do we best handle amount in this case? Usually this isn't set with JS.
			amount: '1.00',
			// TODO Use form data instead of hard coded test values.
			billingContact: {
				givenName: 'John',
				familyName: 'Doe',
				email: 'john.doe@square.example',
				phone: '3214563987',
				addressLines: [ '123 Main Street', 'Apartment 1' ],
				city: 'London',
				state: 'LND',
				countryCode: 'GB',
			},
			currencyCode: 'GBP',
			intent: 'CHARGE'
		};

		const verificationResults = await payments.verifyBuyer( token, verificationDetails );
		return verificationResults.token;
	}

	function displayPaymentFailure() {
		const statusContainer = document.getElementById( 'payment-status-container', );
		statusContainer.classList.add( 'is-failure' );
		statusContainer.style.visibility = 'visible';
	}

	document.addEventListener( 'DOMContentLoaded', async function () {
		if ( ! window.Square ) {
			throw new Error( 'Square.js failed to load properly' );
		}

		// Find the form containing the Square payment element
		const cardContainer = document.getElementById('card-container');
		if ( cardContainer ) {
			thisForm = cardContainer.closest('form');
			if ( thisForm ) {
				// Initially disable the submit button until card is valid
				disableSubmit( thisForm );

				// Add event listener for form submission
				thisForm.addEventListener( 'submit', function( event ) {
					event.preventDefault();
					event.stopPropagation();

					if ( ! squareCardElementIsComplete ) {
						// Show error message
						const statusContainer = document.getElementById('payment-status-container');
						if ( statusContainer ) {
							statusContainer.textContent = 'Please complete all card details before submitting.';
							statusContainer.classList.add('is-failure');
							statusContainer.style.visibility = 'visible';
						}
					} else {
						handlePaymentMethodSubmission( event, cardGlobal );
					}

					return false;
				});
			}
		}

		let payments;
		try {
			// Square requires HTTPS to work.
			payments = window.Square.payments( appId, locationId );
		} catch {
			const statusContainer            = document.getElementById( 'payment-status-container' );
			statusContainer.className        = 'missing-credentials';
			statusContainer.style.visibility = 'visible';
			return;
		}

		let card;
		try {
			card = await initializeCard( payments );
		} catch ( e ) {
			console.error( 'Initializing Card failed', e );
			return;
		}

		cardGlobal = card;

		async function handlePaymentMethodSubmission( event, card ) {
			event.preventDefault();

			try {
				// Increment running counter and disable the submit button
				running++;
				if ( thisForm ) {
					disableSubmit( thisForm );
				}

				const token             = await tokenize( card );
				const verificationToken = await verifyBuyer( payments, token );
				await createPayment( event, token, verificationToken );
				
				// Decrement running counter after successful payment
				running--;
				if ( running === 0 && thisForm ) {
					enableSubmit();
				}
			} catch ( e ) {
				// Decrement running counter and re-enable submit if appropriate
				running--;
				if ( running === 0 && thisForm && squareCardElementIsComplete ) {
					enableSubmit();
				}
				displayPaymentFailure();
			}
		}
	});
}() );
