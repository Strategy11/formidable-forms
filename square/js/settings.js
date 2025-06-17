( function() {
	const buttons = document.querySelectorAll( '.frm-connect-square-with-oauth' );
	buttons.forEach( function( button ) {
		button.addEventListener( 'click', function( e ) {
			e.preventDefault();

			const mode = button.dataset.mode;
			if ( 'test' === mode ) {
				frmDom.modal.maybeCreateModal(
					'frm_square_test_setup_modal',
					{
						title: 'Setting up Square for Test payments',
						content: getSquareTestSetupModalContent()
					}
				);
				return;
			}

			const formData = new FormData();
			formData.append( 'mode', mode );
			frmDom.ajax.doJsonPost( 'square_oauth', formData ).then(
				function( response ) {
					if ( 'undefined' !== typeof response.redirect_url ) {
						window.location = response.redirect_url;
					}
				}
			);
		} );
	} );

	document.addEventListener(
		'click',
		function( event ) {
			if ( ! event.target.id.startsWith( 'frm_disconnect_square_' ) ) {
				return;
			}

			event.preventDefault();
			const formData = new FormData();
			formData.append( 'testMode', 'test' === event.target.id.replace( 'frm_disconnect_square_', '' ) ? 1 : 0 );
			frmDom.ajax.doJsonPost( 'square_disconnect', formData ).then(
				function( response ) {
					if ( 'undefined' !== typeof response.success && response.success ) {
						window.location.reload();
					}
				}
			);
		}
	);

	function getSquareTestSetupModalContent() {
		const signUpUrl = 'https://app.squareup.com/signup/';
		const confirmationTrigger = frmDom.a({ id: 'frm_confirm_square_test_modal', text: 'here', target: '_blank' });
		confirmationTrigger.addEventListener( 'click', function( e ) {
			e.preventDefault();
			const modal = document.getElementById( 'frm_square_test_setup_modal' );

			const formData = new FormData();
			formData.append( 'mode', 'test' );
			frmDom.ajax.doJsonPost( 'square_oauth', formData ).then(
				function( response ) {
					if ( 'undefined' !== typeof response.redirect_url ) {
						window.location = response.redirect_url;
						jQuery( modal ).dialog( 'close' );
					}
				}
			);
		} );
		const content = frmDom.div(
			{
				children: [
					frmDom.tag( 'div', { className: 'frm_note_style', text: 'Important! If you skip these initial steps, you will get stuck on a white screen.' } ),
					frmDom.tag( 'ol',
						{ children: [
							frmDom.span( { children: [ 'Click ', frmDom.a({ href: signUpUrl, text: 'here' }), ' to create a Square account if you do not already have one.' ] } ),
							frmDom.span( { children: [ 'Click ', frmDom.a({ href: 'https://developer.squareup.com/console/en/sandbox-test-accounts', text: 'here', target: '_blank' }), ' and create a Square sandbox test account.' ] } ),
							'Click "Square Dashboard" for the new sandbox test account. Leave the tab open and return to this page.',
							frmDom.span( { children: [ 'Click ', confirmationTrigger, '. You will be taken to Square to allow the required permissions for handling payments.' ] } ),
						].map(
							function( item ) {
								if ( 'string' === typeof item ) {
									return frmDom.tag( 'li', item );
								}
								return frmDom.tag( 'li', { child: item } );
							}
						) }
					)
				]
			}
		);
		content.style.padding = '0 var(--gap-md) var(--gap-md) var(--gap-md)';
		return content;
	}
}() );
