( function() {
	const buttons = document.querySelectorAll( '.frm-connect-paypal-with-oauth' );
	buttons.forEach( function( button ) {
		button.addEventListener( 'click', function( e ) {
			e.preventDefault();

			const mode = button.dataset.mode;
			const formData = new FormData();
			formData.append( 'mode', mode );
			frmDom.ajax.doJsonPost( 'paypal_oauth', formData ).then(
				function( response ) {
					if ( response.redirect_url !== undefined ) {
						window.location = response.redirect_url;
					}
				}
			);
		} );
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
			const mode = placeholder.dataset.mode;
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
