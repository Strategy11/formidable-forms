let filterTarget;

/**
 * Resolves filter target from a wrapper element's data-filter-target attribute and stores it internally.
 *
 * @param {Element} wrapper The wrapper element containing data-filter-target.
 * @return {Element|null} The filter target element if valid, null otherwise.
 */
export function getFilterTarget( wrapper ) {
	filterTarget = null;

	const selector = wrapper?.dataset?.filterTarget;
	if ( selector ) {
		const target = document.querySelector( selector );
		if ( hasFilterableGroups( target ) ) {
			filterTarget = target;
		}
	}

	return filterTarget;
}

/**
 * Checks if a target element has filterable groups.
 *
 * @param {Element} target The container element to check.
 * @return {boolean} True if target has data-group children.
 */
function hasFilterableGroups( target ) {
	return target.querySelectorAll( '[data-group]' ).length > 0;
}

/**
 * Applies a filter to content groups by matching filterValue against data-group attributes.
 *
 * @param {string} filterValue The filter key matching data-group, or 'all'.
 */
export function applyContentFilter( filterValue ) {
	if ( ! filterTarget ) {
		return;
	}

	filterTarget.dataset.activeFilter = filterValue;
	filterTarget.querySelectorAll( '[data-group]' ).forEach( group => {
		group.classList.toggle( 'frm_hidden', 'all' !== filterValue && group.dataset.group !== filterValue );
	} );
}
