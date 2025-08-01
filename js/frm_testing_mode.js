( function() {
	function onReady() {
		const frmDom = window.frmDom;

		frmDom.bootstrap.setupBootstrapDropdowns( function( frmDropdownMenu ) {
			const toggle = document.querySelector( '.dropdown-toggle' );
			if ( toggle ) {
				toggle.classList.add( 'frm-dropdown-toggle' );
				if ( ! toggle.hasAttribute( 'role' ) ) {
					toggle.setAttribute( 'role', 'button' );
				}
				if ( ! toggle.hasAttribute( 'tabindex' ) ) {
					toggle.setAttribute( 'tabindex', 0 );
				}
			}
		} );

		const element = document.getElementById( 'frm_testmode_enabled_form_actions' );
		if ( element ) {
			element.style.display = 'none';
			frmDom.bootstrap.multiselect.init.bind( element )();
		}
	}

	if ( document.readyState === 'complete' ) {
		onReady();
	} else {
		document.addEventListener( 'DOMContentLoaded', onReady );
	}
}() );
