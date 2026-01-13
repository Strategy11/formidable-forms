( function() {
	'use strict';

	const { documentOn } = frmDom.util;

	// Click the gear icon.
	documentOn( 'click', '#frm-forms-list-settings-btn', event => {
		event.preventDefault();

		const dropdownWrapper = document.getElementById( 'frm-forms-list-settings' );
		if ( ! dropdownWrapper ) {
			return;
		}

		if ( ! event.target.nextElementSibling || 'frm-forms-list-settings' !== event.target.nextElementSibling.id ) {
			event.target.after( dropdownWrapper );
		}

		dropdownWrapper.classList.toggle( 'frm_hidden' );
	});
}() );
