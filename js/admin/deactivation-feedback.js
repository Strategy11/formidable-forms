( function() {
	'use strict';

	const selectors = 'tr[data-slug="formidable"] .deactivate a, tr[data-slug="formidable-pro"] .deactivate a, tr[data-slug="formidable-forms-pro"] .deactivate a';

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
			href: deactivationUrl,
			className: 'frm-skip-link'
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

		deactivationUrl = event.target.href + '&frm_feedback_submitted=1';

		const pluginSlug = event.target.closest( 'tr' ).dataset.slug;

		const url = 'https://feedback.strategy11.com/wp-json/frm/v2/forms/deactivation-feedback?plugin_slug=' + pluginSlug + '&site=' + window.location.host + '&return=html&exclude_script=jquery&exclude_style=formidable-css';

		const response = fetch( url, {
			method: 'GET'
		});

		response
			.then( response => response.json() )
			.then( response => {
				const wrapper = document.getElementById( 'frm-deactivation-form-wrapper' );
				const form    = response.renderedHtml.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );

				jQuery( wrapper ).html( form );
				wrapper.setAttribute( 'data-slug', pluginSlug );
				addSkipBtn( wrapper );
				deactivationModal.dialog( 'open' );
			})
			.catch( error => {
				console.error( error );
			});
	};

	frmDom.util.documentOn( 'click', selectors, onClickDeactivate );

	document.addEventListener( 'frmFormCompleteBeforeReplace', function( event ) {
		document.getElementById( 'frm-deactivation-modal-icon' ).remove();
		window.location.href = deactivationUrl;
	});
}() );
