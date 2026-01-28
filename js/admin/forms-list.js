( function() {
	'use strict';

	const { documentOn } = frmDom.util;

	// Click the gear icon.
	documentOn( 'click', '.frm-forms-list-settings-btn', event => {
		event.preventDefault();

		if ( event.target.nextElementSibling && event.target.nextElementSibling.classList.contains( 'frm-forms-list-settings' ) ) {
			event.target.nextElementSibling.classList.toggle( 'frm_hidden' );
			return;
		}

		const dropdownWrapper = document.getElementById( 'frm-forms-list-settings-tmpl' );
		if ( ! dropdownWrapper ) {
			return;
		}

		const clonedEl = dropdownWrapper.cloneNode( true );
		clonedEl.id = '';

		event.target.after( clonedEl );
		clonedEl.classList.toggle( 'frm_hidden' );
	});
}() );
