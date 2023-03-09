( function() {
	function frmTransLiteAdminJS() {
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

		function runAjaxLink( e ) {
			var $link, confirmText, href, loadingImage;

			e.preventDefault();

			$link       = jQuery( this );
			confirmText = $link.data( 'deleteconfirm' );

			if ( typeof confirmText === 'undefined' || confirm( confirmText ) ) {
				href = $link.attr( 'href' );

				loadingImage = document.createElement( 'span' );
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
			}

			return false;
		}

		return {
			init: function() {
				var actions = document.getElementById( 'frm_notification_settings' );
				if ( actions !== null ) {
					jQuery( actions ).on( 'change', '.frm_trans_type', toggleSub );
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

	jQuery( document ).ready( frmTransLiteAdminJS().init );
}() );
