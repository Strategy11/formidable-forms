( function() {
	// TODO This needs to support a global var so we can switch between sandbox and production.
	const appId		 = 'sandbox-sq0idb-MXl8ilzmhAgsHWKV9c6ycQ';
	const locationId = 'L7Q1NBZ6SSJ79';

	// Track the state of the Square card element
	let squareCardElementIsComplete = false;

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
		card.addEventListener('focusClassRemoved', (e) => {
			let field = e.detail.field;
			let value = e.detail.currentState.isCompletelyValid;
			cardFields[field] = value;

			// Check if all fields are valid
			squareCardElementIsComplete = Object.values(cardFields).every(item => item === true);

			// Update button state based on form validity
			updateButtonState();
		});

		return card;
	}

	function updateButtonState() {
		const cardButton = document.getElementById('card-button');
		if (cardButton) {
			cardButton.disabled = !squareCardElementIsComplete;
		}
	}

	async function createPayment( event, token, verificationToken ) {
		const tokenInput = document.createElement( 'input' );
		tokenInput.type  = 'hidden';
		tokenInput.value = token;
		tokenInput.setAttribute( 'name', 'square-token' );

		const verificationInput = document.createElement( 'input' );
		verificationInput.type  = 'hidden';
		verificationInput.value = verificationToken;
		verificationInput.setAttribute( 'name', 'square-verification-token' );

		const form = document.getElementById( 'card-button' ).closest( 'form' );
		form.appendChild( tokenInput );
		form.appendChild( verificationInput );

		if ( typeof frmFrontForm.submitFormManual === 'function' ) {
			frmFrontForm.submitFormManual( event, form );
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

		async function handlePaymentMethodSubmission( event, card ) {
			event.preventDefault();

			try {
				// disable the submit button as we await tokenization and make a payment request.
				cardButton.disabled     = true;
				const token             = await tokenize(card);
				const verificationToken = await verifyBuyer(payments, token);
				await createPayment( event, token, verificationToken );
			} catch ( e ) {
				cardButton.disabled = false;
				displayPaymentFailure();
			}
		}

		const cardButton = document.getElementById( 'card-button' );
		cardButton.addEventListener( 'click', async function ( event ) {
			await handlePaymentMethodSubmission( event, card );
		});
	});
}() );
