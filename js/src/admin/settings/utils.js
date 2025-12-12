/**
 * Gets field ID from element's closest settings container.
 *
 * @since x.x
 *
 * @param {HTMLElement} element The element to get field ID from.
 *
 * @return {string|undefined} The field ID or undefined if not found.
 */
export function getFieldId( element ) {
	return element.closest( '.frm-single-settings' )?.dataset.fid;
}
