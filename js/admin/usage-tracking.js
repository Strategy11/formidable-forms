( function() {
	'use strict';

	// Track edit form clicked link.
	frmDom.util.documentOn( 'click', '.toplevel_page_formidable #the-list .column-primary .row-actions a', function( event ) {
		sendData( 'edit_form_clicked_link', event.target.parentNode.className );
	});

	// Track form templates.
	frmDom.util.documentOn( 'click', '.frm-form-templates-use-template-button', function( event ) {
		sendData( 'form_templates', event.target.closest( 'li' ).dataset.slug );
	});

	const sendData = ( key, value ) => {
		const formData = new FormData();
		formData.append( 'key', key );
		formData.append( 'value', value );
		frmDom.ajax.doJsonPost( 'track_flows', formData );
	};
}() );
