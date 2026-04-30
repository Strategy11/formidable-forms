( function() {
	// Use event delegation so Reconnect buttons rendered after AJAX seller-status
	// responses also trigger the OAuth flow.
	document.addEventListener( 'click', function( e ) {
		const button = e.target.closest( '.frm-connect-paypal-with-oauth' );
		if ( ! button ) {
			return;
		}

		e.preventDefault();

		const { mode, reconnect } = button.dataset;
		const formData = new FormData();
		formData.append( 'mode', mode );
		if ( reconnect ) {
			formData.append( 'reconnect', reconnect );
		}
		frmDom.ajax.doJsonPost( 'paypal_oauth', formData ).then(
			function( response ) {
				if ( response.redirect_url !== undefined ) {
					window.location = response.redirect_url;
				}
			}
		).catch(
			function( error ) {
				/* eslint-disable-next-line no-console */
				console.error( 'PayPal OAuth request failed:', error );
			}
		);
	} );

	document.addEventListener(
		'click',
		function( event ) {
			if ( ! event.target.id.startsWith( 'frm_disconnect_paypal_' ) ) {
				return;
			}

			event.preventDefault();
			const formData = new FormData();
			formData.append( 'testMode', 'test' === event.target.id.replace( 'frm_disconnect_paypal_', '' ) ? 1 : 0 );
			frmDom.ajax.doJsonPost( 'paypal_disconnect', formData ).then(
				function( response ) {
					if ( response.success ) {
						window.location.reload();
					}
				}
			);
		}
	);

	document.querySelectorAll( '.frm_paypal_seller_status_placeholder' ).forEach(
		function( placeholder ) {
			const { mode } = placeholder.dataset;
			const interval = setInterval(
				function() {
					if ( placeholder.offsetParent === null ) {
						return;
					}

					clearInterval( interval );

					const formData = new FormData();
					formData.append( 'testMode', 'test' === mode ? 1 : 0 );
					frmDom.ajax.doJsonPost( 'paypal_render_seller_status', formData )
						.then(
							function( sellerStatus ) {
								placeholder.innerHTML = sellerStatus;
							}
						).catch(
							function( error ) {
								if ( 'string' === typeof error ) {
									placeholder.innerHTML = error;
								}

								clearInterval( interval );
							}
						);
				},
				100
			);
		}
	);
}() );
