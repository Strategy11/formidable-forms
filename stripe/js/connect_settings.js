( function() {
	function setupStripeConnectListener() {
		onclickPreventDefault( '#frm_disconnect_stripe', handleStripeDisconnectClick );
		onclickPreventDefault( '#frm_reauth_stripe', handleStripeReauthClick );
		onclickPreventDefault( '#frm_connect_with_oauth', handleConnectWithOauth );
		renderStripeConnectSettingsButton();
	}

	function renderStripeConnectSettingsButton() {
		var container = document.getElementById( 'frm_strp_settings_container' );
		if ( null !== container ) {
			postAjax(
				{
					action: 'frm_strp_connect_get_settings_button'
				},
				function( data ) {
					container.innerHTML = data.html;
				}
			);
		}
	}

	function onclickPreventDefault( selector, callback ) {
		jQuery( document ).on( 'click', selector, function( event ) {
			event.preventDefault();
			callback( this );
		});
	}

	function handleStripeDisconnectClick( trigger ) {
		const testMode = isTriggerInTestMode( trigger );
		const spinner  = frmDom.span({ className: 'frm-wait frm_visible_spinner' });

		spinner.style.margin = 0; // The default 20px margin causes the spinner to look bad.
		trigger.replaceWith( spinner );

		strpSettingsAjaxRequest(
			'frm_stripe_connect_disconnect',
			function() {
				renderStripeConnectSettingsButton();
			},
			testMode
		);
	}

	function handleStripeReauthClick( trigger ) {
		strpSettingsAjaxRequest(
			'frm_stripe_connect_reauth',
			function( data ) {
				if ( 'undefined' !== typeof data.connect_url ) {
					window.location = data.connect_url;
				} else {
					renderStripeConnectSettingsButton();
				}
			},
			isTriggerInTestMode( trigger )
		);
	}

	function handleConnectWithOauth( trigger ) {
		trigger.classList.add( 'frm_loading_button' );
		strpSettingsAjaxRequest(
			'frm_stripe_connect_oauth',
			function( data ) {
				if ( 'undefined' !== typeof data.redirect_url ) {
					window.location = data.redirect_url;
				} else {
					renderStripeConnectSettingsButton();
				}
			},
			isTriggerInTestMode( trigger )
		);
	}

	function strpSettingsAjaxRequest( action, success, testMode ) {
		var data = {
			action: action,
			testMode: testMode,
			nonce: frmGlobal.nonce
		};
		postAjax( data, success );
	}

	function postAjax( data, success ) {
		var xmlHttp, params;

		xmlHttp = new XMLHttpRequest();
		params = typeof data === 'string' ? data : Object.keys( data ).map(
			function( k ) {
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[ k ]);
			}
		).join( '&' );

		xmlHttp.open( 'post', ajaxurl, true );
		xmlHttp.onreadystatechange = function() {
			var response;
			if ( xmlHttp.readyState > 3 && xmlHttp.status == 200 ) {
				response = xmlHttp.responseText;
				if ( response !== '' ) {
					response = JSON.parse( response );
					if ( response.success ) {
						if ( 'undefined' === typeof response.data ) {
							response.data = {};
						}
						success( response.data );
					}
				}
			}
		};
		xmlHttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		xmlHttp.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded' );
		xmlHttp.send( params );
		return xmlHttp;
	}

	function isTriggerInTestMode( trigger ) {
		return parseInt( jQuery( trigger ).closest( '[data-test-mode]' ).attr( 'data-test-mode' ) );
	}

	jQuery( document ).ready( setupStripeConnectListener );
}() );
