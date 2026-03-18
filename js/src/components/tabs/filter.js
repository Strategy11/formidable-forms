/**
 * Applies a filter to content groups by matching filterValue against data-group attributes.
 *
 * @param {Element} target      The container element with filterable groups.
 * @param {string}  filterValue The filter key matching data-group, or 'all'.
 */
export function applyContentFilter( target, filterValue ) {
	target.dataset.activeFilter = filterValue;
	target.querySelectorAll( '[data-group]' ).forEach( group => {
		group.classList.toggle( 'frm_hidden', 'all' !== filterValue && group.dataset.group !== filterValue );
	} );
}

/**
 * Checks if a target element has filterable groups.
 *
 * @param {Element} target The container element to check.
 * @return {boolean} True if target has data-group children.
 */
export function hasFilterableGroups( target ) {
	return target instanceof Element && target.querySelectorAll( '[data-group]' ).length > 0;
}
