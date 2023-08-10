( function() {
	function frmTransLiteAdminJS() {
		return {
			init: function() {
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
