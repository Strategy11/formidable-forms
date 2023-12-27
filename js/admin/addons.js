/**
 * Add-Ons Page Script.
 *
 * Script for handling addon interactions on the Add-Ons page.
 */

wp.domReady( () => {

	/**
	 * Internal dependencies
	 */
	const { toggleAddonState } = window.frmAdminBuild;

	document.addEventListener( 'click', ( event ) => {
		if ( event.target.matches( '.frm-uninstall-addon' ) ) {
			event.preventDefault();
			toggleAddonState( event.target, 'frm_uninstall_addon' );
		} else if ( event.target.matches( '.frm-deactivate-addon' ) ) {
			event.preventDefault();
			toggleAddonState( event.target, 'frm_deactivate_addon' );
		}
	});
});
