( function() {
	const actions = document.getElementById( 'frm_notification_settings' );
	if ( ! actions ) {
		return;
	}

	jQuery( actions ).on( 'change', 'select[name*="[post_content][paypal_layout]"]', onLayoutChange );
	jQuery( actions ).on( 'change', 'select.frm_trans_type', onPaymentTypeChange );
	actions.addEventListener( 'click', onMigrateLegacyClick );

	/**
	 * Convert a legacy PayPal action into a PayPal Commerce payment action, then reload the page.
	 * The reload matters. Saving the form with the old settings still on the page would delete the new action.
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
		const notice = button.closest( '.frm-paypal-legacy-migration' );
		const result = notice.querySelector( '.frm-paypal-migration-result' );
		const body = new FormData();
		const showError = message => {
			button.disabled = false;
			result.replaceChildren(
				frmDom.tag( 'div', {
					className: 'frm_error_style frm-mt-sm frm-mb-0',
					text: message || __( 'The action could not be converted.', 'formidable' )
				} )
			);
		};

		body.append( 'action', 'frm_paypal_migrate_legacy_action' );
		body.append( 'action_id', button.dataset.actionId );
		body.append( 'nonce', frmGlobal.nonce );

		button.disabled = true;
		result.replaceChildren( frmDom.tag( 'p', { className: 'frm-mt-sm frm-mb-0', text: __( 'Converting…', 'formidable' ) } ) );

		fetch( ajaxurl, { method: 'POST', body } )
			.then( response => response.json() )
			.then( response => {
				if ( ! response.success ) {
					showError( response.data?.message );
					return;
				}

				// Divs, not paragraphs, so the success message color is not replaced by the admin paragraph color.
				const children = [
					frmDom.tag( 'div', {
						className: 'frm-mb-xs frm-font-semibold',
						text: __( 'Converted to PayPal Commerce', 'formidable' )
					} ),
					frmDom.tag( 'div', {
						className: 'frm-mb-sm',
						text: __( 'Your PayPal Commerce action is ready and this legacy action is now off. Reload the page to see the new action.', 'formidable' )
					} )
				];

				if ( response.data.notes.length ) {
					children.push(
						frmDom.tag( 'div', { className: 'frm-mb-xs', text: __( 'Review these settings on the new action:', 'formidable' ) } ),
						frmDom.tag( 'ul', {
							className: 'frm-mt-0 frm-mb-sm frm-paypal-migration-notes',
							children: response.data.notes.map( note => frmDom.tag( 'li', { text: note } ) )
						} )
					);
				}

				const reload = frmDom.tag( 'button', {
					className: 'button frm-button-primary',
					text: __( 'Reload the page', 'formidable' )
				} );
				reload.type = 'button';
				reload.addEventListener( 'click', () => window.location.reload() );
				children.push( reload );

				notice.querySelector( '.frm-paypal-migration-intro' ).classList.add( 'frm_hidden' );
				notice.classList.replace( 'frm_note_style', 'frm_updated_message' );
				result.replaceChildren( ...children );
				reload.focus();
			} )
			.catch( () => showError() );
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
