( function() {
	const appId		 = 'sandbox-sq0idb-MXl8ilzmhAgsHWKV9c6ycQ';
	const locationId = 'L7Q1NBZ6SSJ79';

	async function initializeCard(payments) {
		const card = await payments.card();
		await card.attach('#card-container');

		return card;
	}

	async function createPayment(token, verificationToken) {
		const body = JSON.stringify({
			locationId,
			sourceId: token,
			verificationToken,
			idempotencyKey: window.crypto.randomUUID(),
		});

		const paymentResponse = await fetch('/payment', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body,
		});

		if ( paymentResponse.ok ) {
			return paymentResponse.json();
		}

		const errorBody = await paymentResponse.text();
		throw new Error( errorBody );
	}

	async function tokenize(paymentMethod) {
		const tokenResult = await paymentMethod.tokenize();

		if (tokenResult.status === 'OK') {
			return tokenResult.token;
		}

		let errorMessage = `Tokenization failed with status: ${tokenResult.status}`;
		if (tokenResult.errors) {
		errorMessage += ` and errors: ${JSON.stringify(
			tokenResult.errors,
		)}`;
		}

		throw new Error(errorMessage);
	}

	// Required in SCA Mandated Regions: Learn more at https://developer.squareup.com/docs/sca-overview
	async function verifyBuyer(payments, token) {
		const verificationDetails = {
			amount: '1.00',
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
			intent: 'CHARGE',
		};

		const verificationResults = await payments.verifyBuyer(
			token,
			verificationDetails,
		);
		return verificationResults.token;
	}

	// status is either SUCCESS or FAILURE;
	function displayPaymentResults( status ) {
		const statusContainer = document.getElementById(
			'payment-status-container',
		);
		if ( status === 'SUCCESS' ) {
			statusContainer.classList.remove( 'is-failure' );
			statusContainer.classList.add( 'is-success' );
		} else {
			statusContainer.classList.remove( 'is-success' );
			statusContainer.classList.add( 'is-failure' );
		}

		statusContainer.style.visibility = 'visible';
	}

	document.addEventListener('DOMContentLoaded', async function () {
		if ( ! window.Square ) {
			throw new Error('Square.js failed to load properly');
		}

		let payments;
		try {
			// This requires HTTPS to work.
			payments = window.Square.payments( appId, locationId );
		} catch {
			const statusContainer = document.getElementById(
			'payment-status-container',
			);
			statusContainer.className = 'missing-credentials';
			statusContainer.style.visibility = 'visible';
			return;
		}

		let card;
		try {
			card = await initializeCard(payments);
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
				const paymentResults    = await createPayment(
					token,
					verificationToken,
				);
				displayPaymentResults( 'SUCCESS' );

				console.debug( 'Payment Success', paymentResults );
			} catch ( e ) {
				cardButton.disabled = false;
				displayPaymentResults( 'FAILURE' );
				console.error( e.message );
			}
		}

		const cardButton = document.getElementById( 'card-button' );
		cardButton.addEventListener('click', async function ( event ) {
			await handlePaymentMethodSubmission( event, card );
		});
	});
}() );
