( function() {
	const actions = document.getElementById( 'frm_notification_settings' );
	if ( ! actions ) {
		return;
	}

	jQuery( actions ).on( 'change', 'select[name*="[post_content][paypal_layout]"]', onLayoutChange );
	jQuery( actions ).on( 'change', 'select.frm_trans_type', onPaymentTypeChange );
	actions.addEventListener( 'click', onMigrateLegacyClick );

	/**
	 * Convert a legacy PayPal action into a PayPal Commerce payment action, then reload the builder.
	 * The reload matters: saving the form with the old settings still on the page would delete the new action.
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	function onMigrateLegacyClick( event ) {
		const button = event.target.closest( '.frm-paypal-migrate-legacy' );
		if ( ! button || button.disabled ) {
			return;
		}

		const { __ } = wp.i18n;
		const result = button.closest( '.frm-paypal-legacy-migration' ).querySelector( '.frm-paypal-migration-result' );
		const body = new FormData();

		body.append( 'action', 'frm_paypal_migrate_legacy_action' );
		body.append( 'action_id', button.dataset.actionId );
		body.append( 'nonce', frmGlobal.nonce );

		button.disabled = true;
		result.replaceChildren( frmDom.tag( 'p', { text: __( 'Converting…', 'formidable' ) } ) );

		fetch( ajaxurl, { method: 'POST', body } )
			.then( response => response.json() )
			.then( response => {
				if ( ! response.success ) {
					button.disabled = false;
					result.replaceChildren(
						frmDom.tag( 'p', {
							className: 'frm_error_style',
							text: response.data?.message ?? __( 'The action could not be converted.', 'formidable' )
						} )
					);
					return;
				}

				const children = [ frmDom.tag( 'p', { text: __( 'Your PayPal Commerce action is ready and this legacy action is now off.', 'formidable' ) } ) ];

				if ( response.data.notes.length ) {
					children.push( frmDom.tag( 'p', { text: __( 'Review these settings on the new action:', 'formidable' ) } ) );
					children.push(
						frmDom.tag( 'ul', {
							children: response.data.notes.map( note => frmDom.tag( 'li', { text: note } ) )
						} )
					);
				}

				const reload = frmDom.tag( 'button', {
					className: 'button frm-button-primary',
					text: __( 'Reload the form builder', 'formidable' )
				} );
				reload.type = 'button';
				reload.addEventListener( 'click', () => window.location.reload() );
				children.push( reload );

				result.replaceChildren( ...children );
				button.remove();
			} )
			.catch( () => {
				button.disabled = false;
				result.replaceChildren(
					frmDom.tag( 'p', { className: 'frm_error_style', text: __( 'The action could not be converted.', 'formidable' ) } )
				);
			} );
	}

	function onLayoutChange() {
		const settings = this.closest( '.frm_form_action_settings' );
		if ( ! settings ) {
			return;
		}

		const buttonSettings = settings.querySelector( '.frm_paypal_button_settings' );
		if ( ! buttonSettings ) {
			return;
		}

		buttonSettings.classList.toggle( 'frm_hidden', 'card_only' === this.value );
	}

	function onPaymentTypeChange() {
		const settings = this.closest( '.frm_form_action_settings' );
		if ( ! settings ) {
			return;
		}

		const layoutSetting = settings.querySelector( '.frm_paypal_layout_setting' );
		if ( ! layoutSetting ) {
			return;
		}

		layoutSetting.classList.toggle( 'frm_hidden', 'recurring' === this.value );
	}
}() );
