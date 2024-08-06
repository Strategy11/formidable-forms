( function() {
	'use strict';

	const selectors = '#deactivate-formidable, #deactivate-formidable-pro';

	function frmapiGetData( frmcont ) {
		jQuery.ajax({
			dataType: 'json',
			url: 'http://frmdev.test/wp-json/frm/v2/forms/deactivation-feedback?plugin_name=Formidable&return=html&exclude_script=jquery&exclude_style=formidable-css',
			success: ( json ) => {
				console.log( json );
				const form = json.renderedHtml.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );
				jQuery( '#frmapi-feedback' ).html( form );
			}
		});
	}

	const onClickDeactivate = event => {
		event.preventDefault();
		frmapiGetData( jQuery( event.target ) );
	};

	frmDom.util.documentOn( 'click', selectors, onClickDeactivate );

	document.addEventListener( 'frmFormCompleteBeforeReplace', function( a, b ) {
		console.log( a );
		console.log( b );
	});

	// jQuery.on( document, 'frmFormComplete', function( a, b ) {
	// 	console.log( a );
	// 	console.log( b );
	// });
}() );
