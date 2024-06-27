/**
 * Internal dependencies
 */
import { getElements } from '../elements';

let previousAction;

/**
 * Manages event handling for addons toggle.
 *
 * @return {void}
 */
function addAddonToggleEvents() {
	const { addonsToggle } = getElements();

	addonsToggle.forEach( addonToggle =>
		addonToggle.addEventListener( 'click', onAddonToggleClick )
	);
}

/**
 * Handles the click event on the addon toggle.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onAddonToggleClick = ( event ) => {
	if ( previousAction && previousAction.classList.contains( 'frm_loading_button' ) ) {
		return;
	}

	const addonToggle = event.currentTarget;
	const addon = addonToggle.closest( '.frm-card-item' );

	const actionMap = new Map([
		[ 'frm-addon-not-installed', '.frm-install-addon' ],
		[ 'frm-addon-installed', '.frm-activate-addon' ],
		[ 'frm-addon-active', '.frm-deactivate-addon' ]
	]);

	for ( const [ className, selector ] of actionMap.entries() ) {
		if ( addon.classList.contains( className ) ) {
			previousAction = addon.querySelector( selector );
			previousAction.click();
			break;
		}
	}
};

export default addAddonToggleEvents;
