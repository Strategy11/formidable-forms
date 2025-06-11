( function() {
	if ( ! window.frmSquareVars ) {
		return;
	}

	const appId      = frmSquareVars.appId;
	const locationId = frmSquareVars.locationId;

	// Track the state of the Square card element
	let squareCardElementIsComplete = false;
	let thisForm                    = null;
	let running                     = 0;

	let cardGlobal;

	// Track the state of each field in the card form
	const cardFields = {
		cardNumber: false,
		expirationDate: false,
		cvv: false,
		postalCode: false
	};

	async function initializeCard( payments ) {
		const cardElement = document.querySelector( '.frm-card-element' );
		if ( ! cardElement ) {
			return;
		}

		const card      = await payments.card();
		const cardStyle = frmSquareVars.style;
		await card.attach( '.frm-card-element' );

		card.configure( { style: cardStyle } );

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
		const formData = new FormData( thisForm );
		formData.append( 'action', 'frm_verify_buyer' );
		formData.append( 'nonce', frmSquareVars.nonce );
		const response = await fetch( frmSquareVars.ajax, {
			method: 'POST',
			body: formData
		} );

		if ( ! response.ok ) {
			throw new Error( 'Failed to verify buyer' );
		}

		const verificationData = await response.json();
		if ( ! verificationData.success ) {
			throw new Error( verificationData.data );
		}

		const verificationDetails = verificationData.data.verificationDetails;
		const verificationResults = await payments.verifyBuyer( token, verificationDetails );
		return verificationResults.token;
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
		}
	}

	async function squareInit() {
		// Find the form containing the Square payment element
		const cardContainer = document.querySelector( '.frm-card-element' );
		if ( cardContainer ) {
			thisForm = cardContainer.closest( 'form' );
			if ( thisForm ) {
				// Initially disable the submit button until card is valid
				disableSubmit( thisForm );

				// Add event listener for form submission
				thisForm.addEventListener( 'submit', function( event ) {
					event.preventDefault();
					event.stopPropagation();

					if ( ! squareCardElementIsComplete ) {
						const statusContainer = thisForm.querySelector( '.frm-card-errors' );
						if ( statusContainer ) {
							statusContainer.textContent = 'Please complete all card details before submitting.';
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
		} catch ( e ) {
			const statusContainer            = document.querySelector( '.frm-card-errors' );
			statusContainer.classList.add( 'missing-credentials', 'frm_error' );
			statusContainer.style.visibility = 'visible';
			statusContainer.textContent      = e.message;
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

		/**
		 * @param {Object} $form
		 * @return {Boolean} false if there are errors.
		 */
		function validateFormSubmit( $form ) {
			var errors, keys;

			errors = frmFrontForm.validateFormSubmit( $form );
			keys   = Object.keys( errors );

			if ( 1 === keys.length && errors[ keys[0] ] === '' ) {
				// Pop the empty error that gets added by invisible recaptcha.
				keys.pop();
			}

			return 0 === keys.length;
		}

		async function handlePaymentMethodSubmission( event, card ) {
			try {
				if ( ! validateFormSubmit( thisForm ) ) {
					return;
				}

				event.preventDefault();

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
				displayPaymentFailure( e.message );
			}
		}
	}

	document.addEventListener( 'DOMContentLoaded', async function () {
		if ( ! window.Square ) {
			console.error( 'Square.js failed to load properly' );
			return;
		}

		squareInit();

		jQuery( document ).on( 'frmPageChanged', function() {
			squareInit();
		} );
	});
}() );
