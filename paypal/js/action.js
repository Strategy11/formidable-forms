( function() {
	alert( 'here' );
	const actions = document.getElementById( 'frm_notification_settings' );
	if ( ! actions ) {
		return;
	}

	jQuery( actions ).on( 'change', 'select[name*="[post_content][layout]"]', onLayoutChange );

	function onLayoutChange() {
		const settings = this.closest( '.frm_form_action_settings' );
		if ( ! settings ) {
			return;
		}

		const buttonSettings = settings.querySelector( '.frm_paypal_button_settings' );
		if ( ! buttonSettings ) {
			return;
		}

		if ( 'card_only' === this.value ) {
			buttonSettings.classList.add( 'frm_hidden' );
		} else {
			buttonSettings.classList.remove( 'frm_hidden' );
		}
	}
}() );
