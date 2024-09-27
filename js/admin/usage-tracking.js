( function() {
	'use strict';

	// Track edit form clicked link.
	frmDom.util.documentOn( 'click', '.toplevel_page_formidable #the-list .column-primary .row-actions a', function( event ) {
		sendData({
			key: 'edit_form_clicked_link',
			value: event.target.parentNode.className
		});
	});

	// Track form templates.
	frmDom.util.documentOn( 'click', '.frm-form-templates-use-template-button', function( event ) {
		sendData({
			key: 'form_templates',
			value: event.target.closest( 'li' ).dataset.slug
		});
	});

	const sendData = ( data ) => {
		const formData = new FormData();
		Object.keys( data ).forEach( key => {
			formData.append( key, data[ key ] );
		});
		frmDom.ajax.doJsonPost( 'track_flows', formData );
	};
}() );
