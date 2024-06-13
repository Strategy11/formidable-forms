export const { onClickPreventDefault } = window.frmDom.util;

/**
 * Dispatches a custom event with the given name and detail.
 *
 * @param {string} eventName The name of the custom event.
 * @param {Object} detail    The detail object to pass with the event.
 * @return {void}
 */
export const dispatchCustomEvent = ( eventName, detail ) => {
	document.dispatchEvent( new CustomEvent( eventName, { detail } ) );
};
