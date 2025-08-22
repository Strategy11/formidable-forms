export class frmAddonAPI {
	/**
	 * A function designed to toggle different addon states.
	 *
	 * @param {'frm_install_addon'|'frm_activate_addon'|'frm_multiple_addons'} action The addon state action type.
	 * @param {string}                                                         addon  The addon path. Ex: formidable-views/formidable-views.php
	 *
	 * @return {Promise<object>} The response from the server.
	 */
	static toggleAddonState( action, addon ) {
		return fetch( ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams({
				action: action,
				nonce: frmGlobal.nonce,
				plugin: addon
			})
		}).then( response => response.json() );
	}
}
