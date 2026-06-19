( function() {
	const actions = document.getElementById( 'frm_notification_settings' );
	if ( ! actions ) {
		return;
	}

	jQuery( actions ).on( 'change', 'select[name*="[post_content][paypal_layout]"]', onLayoutChange );
	jQuery( actions ).on( 'change', 'select.frm_trans_type', onPaymentTypeChange );

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
