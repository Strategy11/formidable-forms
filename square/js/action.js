( function() {
	const hookNamespace = 'formidable-square';
	wp.hooks.addAction( 'frm_trans_toggled_gateway', hookNamespace, onGatewayToggle );

	function onGatewayToggle( { gateway, settings } ) {
		const currencySetting = settings.get( 0 ).querySelector( '[name*="[post_content][currency]"]' );
		if ( ! currencySetting ) {
			return;
		}

		let option = currencySetting.querySelector( 'option.square-currency' );

		if ( option ) {
			if ( 'square' === gateway ) {
				currencySetting.value    = option.value;
				currencySetting.disabled = true;
			} else {
				currencySetting.disabled = false;
				option.remove();
			}
			return;
		}

		// Option didn't exist yet, so add it.
		if ( 'square' === gateway ) {
			option = document.createElement( 'option' );
			option.value       = 'square';
			option.textContent = 'Use Square Merchant Currency';
			option.classList.add( 'square-currency' );
			currencySetting.appendChild( option );

			currencySetting.value = option.value;
			currencySetting.disabled = true;
		} else {
			currencySetting.disabled = false;
		}
	}
}() );
