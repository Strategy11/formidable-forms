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
					if ( 'undefined' !== typeof response.redirect_url ) {
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
					if ( 'undefined' !== typeof response.success && response.success ) {
						window.location.reload();
					}
				}
			);
		}
	);
}() );
