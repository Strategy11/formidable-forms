/**
 * Add-Ons Page Script.
 *
 * Handles ... on the Add-Ons admin page.
 */

wp.domReady( () => {

	/**
	 * Internal dependencies
	 */
	const { toggleAddonState } = window.frmAdminBuild;
	const { onClickPreventDefault } = frmDom.util;

	document.querySelectorAll( '.frm-uninstall-addon' ).forEach( uninstallButton => {
		onClickPreventDefault( uninstallButton,	() => toggleAddonState( uninstallButton, 'frm_uninstall_addon' ) );
	});

	document.querySelectorAll( '.frm-deactivate-addon' ).forEach( deactivateButton => {
		onClickPreventDefault( deactivateButton, () => toggleAddonState( deactivateButton, 'frm_deactivate_addon' ) );
	});
});
