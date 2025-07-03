( function() {
	function toggleSub() {
		var val  = this.value;
		var show = val === 'recurring';
		slideOpts( this, show, '.frm_trans_sub_opts' );
		toggleOpts( this, ! show, '.frm_gateway_no_recur' );
	}

	function slideOpts( opt, show, c ) {
		var opts = jQuery( opt ).closest( '.frm_form_action_settings' ).find( c );
		if ( show ) {
			opts.slideDown( 'fast' );
		} else {
			opts.slideUp( 'fast' );
		}
	}

	function toggleOpts( opt, show, c ) {
		var opts = jQuery( opt ).closest( '.frm_form_action_settings' ).find( c );
		if ( show ) {
			opts.show();
		} else {
			opts.hide();
		}
	}

	function toggleGateway() {
		if ( ! this.checked ) {
			return;
		}

		const gateway = this.value;
		const checked = this.checked;

		toggleOpts( this, checked, '.show_' + gateway );

		const toggleOff       = 'stripe' === gateway ? 'square' : 'stripe';
		const settings        = jQuery( this ).closest( '.frm_form_action_settings' );
		const showClass       = 'show_' + settings.find( '.frm_gateway_opt input:checked' ).attr( 'value' );
		const gatewaySettings = settings.get( 0 ).querySelectorAll( '.show_' + toggleOff );

		gatewaySettings.forEach(
			setting => {
				if ( ! setting.classList.contains( showClass ) ) {
					setting.style.display = 'none';
				}
			}
		);

		wp.hooks.doAction( 'frm_trans_toggled_gateway', { gateway, checked, settings } );
	}

	function frmTransLiteAdminJS() {
		return {
			init: function() {
				var actions = document.getElementById( 'frm_notification_settings' );
				if ( actions !== null ) {
					jQuery( actions ).on( 'change', '.frm_trans_type', toggleSub );
					jQuery( '.frm_form_settings' ).on( 'change', '.frm_gateway_opt input', toggleGateway );
				}

				document.querySelectorAll( '.frm_trans_ajax_link' ).forEach(
					link => link.addEventListener(
						'click',
						function( event ) {
							runAjaxLink.bind( link )( event );
						}
					)
				);
			}
		};
	}

	function runAjaxLink( e ) {
		e.preventDefault();

		const $link                = jQuery( this );
		const handleConfirmedClick = e => {
			e.preventDefault();

			const href             = $link.attr( 'href' );
			const loadingImage     = document.createElement( 'span' );
			loadingImage.className = 'frm-loading-img';

			$link.replaceWith( loadingImage );
			jQuery.ajax({
				type: 'GET',
				url: href,
				data: {
					nonce: frm_trans_vars.nonce
				},
				success: function( html ) {
					jQuery( loadingImage ).replaceWith( html );
				}
			});
		};

		jQuery( '#frm-confirmed-click' ).one( 'click', handleConfirmedClick );

		// Prevent handleConfirmedClick from triggering when the current modal is closed so that it won't be run by other elements.
		const unbindHandleConfirmedClick = e => {
			if ( e.target.matches( '.ui-widget-overlay, .dismiss' ) ) {
				jQuery( '#frm-confirmed-click' ).unbind( 'click', handleConfirmedClick );
				document.removeEventListener( 'click', unbindHandleConfirmedClick );
			}
		};

		document.addEventListener( 'click', unbindHandleConfirmedClick );
		return false;
	}

	jQuery( document ).ready( frmTransLiteAdminJS().init );
}() );
