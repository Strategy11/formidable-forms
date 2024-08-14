( function() {
	'use strict';

	const selectors = '#deactivate-formidable, #deactivate-formidable-pro';

	let deactivationModal, deactivationUrl;

	const Modal = {
		init: function( id, width ) {
			const $info = jQuery( id );
			const self  = this;

			if ( ! $info.length ) {
				return false;
			}

			if ( typeof width === 'undefined' ) {
				width = '550px';
			}

			const dialogArgs = {
				dialogClass: 'frm-dialog',
				modal: true,
				autoOpen: false,
				closeOnEscape: true,
				width: width,
				resizable: false,
				draggable: false,
				open: function() {
					jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
					jQuery( '#wpwrap' ).addClass( 'frm_overlay' );
					jQuery( '.frm-dialog' ).removeClass( 'ui-widget ui-widget-content ui-corner-all' );
					$info.removeClass( 'ui-dialog-content ui-widget-content' );
					self.bindClickForDialogClose( $info );
				},
				close: function() {
					jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
					jQuery( '.spinner' ).css( 'visibility', 'hidden' );

					this.removeAttribute( 'data-option-type' );
					const optionType = document.getElementById( 'bulk-option-type' );
					if ( optionType ) {
						optionType.value = '';
					}
				}
			};

			$info.dialog( dialogArgs );

			return $info;
		},

		bindClickForDialogClose: function( $modal ) {
			const closeModal = function() {
				$modal.dialog( 'close' );
			};
			jQuery( '.ui-widget-overlay' ).on( 'click', closeModal );
			$modal.on( 'click', 'a.dismiss', closeModal );
		}
	};

	const addSkipBtn = formEl => {
		const btn = frmDom.a( {
			text: FrmDeactivationFeedbackI18n.skip_text,
			href: deactivationUrl
		});

		formEl.querySelector( '.frm_submit' ).prepend( btn );
	};

	const onClickDeactivate = event => {
		event.preventDefault();

		if ( ! deactivationModal ) {
			deactivationModal = Modal.init(
				'#frm-deactivation-modal',
				'440px'
			);
		}

		deactivationUrl = event.target.href;

		const pluginName = event.target.closest( '.plugin-title' ).querySelector( 'strong' ).innerText;

		const url = 'http://frmdev.test/wp-json/frm/v2/forms/deactivation-feedback?plugin_name=' + pluginName + '&return=html&exclude_script=jquery&exclude_style=formidable-css';

		const response = fetch( url, {
			method: 'GET'
		});

		response
			.then( response => response.json() )
			.then( response => {
				const wrapper = document.getElementById( 'frm-deactivation-form-wrapper' );
				const form    = response.renderedHtml.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );

				jQuery( wrapper ).html( form );
				addSkipBtn( wrapper );
				deactivationModal.dialog( 'open' );
			})
			.catch( error => {
				console.error( error );
			});
	};

	frmDom.util.documentOn( 'click', selectors, onClickDeactivate );

	document.addEventListener( 'frmFormCompleteBeforeReplace', function( event ) {
		window.location.href = deactivationUrl;
	});
}() );
