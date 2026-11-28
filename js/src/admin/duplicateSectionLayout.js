/**
 * Group the copied fields by the row their source field sat in, for each copied Section.
 *
 * @since x.x
 * @param {Map}      layouts      Source child IDs and their builder layout at duplication time.
 * @param {Map}      copies       Source child IDs and their copied fields.
 * @param {Function} isFieldGroup Tells whether an element is a builder row.
 * @return {Map} Each copied Section list mapped to its desired rows of copied fields, in source order.
 */
export function groupCopiesBySourceRow( layouts, copies, isFieldGroup ) {
	const sections = new Map();
	layouts.forEach( ( { row }, sourceId ) => {
		const field = copies.get( sourceId );
		const section = field?.parentElement.closest( 'ul.start_divider' );
		if ( ! section || ! row?.parentElement || ! isFieldGroup( row.parentElement ) ) {
			return;
		}
		if ( ! sections.has( section ) ) {
			sections.set( section, new Map() );
		}
		const rows = sections.get( section );
		if ( ! rows.has( row ) ) {
			rows.set( row, [] );
		}
		rows.get( row ).push( field );
	} );
	return sections;
}

/**
 * Check whether the copied rows have to be rearranged to match the source rows.
 *
 * The rows are only rearranged when they hold exactly the expected fields in a different layout.
 * Any other difference means the copy is not the one expected, so it is left alone.
 *
 * @since x.x
 * @param {Array<Array>} currentRows The copied fields, grouped by the row they are in now.
 * @param {Array<Array>} desiredRows The copied fields, grouped by the row they should be in.
 * @return {boolean} Whether the rows should be rebuilt.
 */
export function copiedRowsNeedRestore( currentRows, desiredRows ) {
	const allCurrentFields = currentRows.flat();
	const desiredFields = new Set( desiredRows.flat() );
	if ( ! currentRows.length || allCurrentFields.length !== desiredFields.size ||
		allCurrentFields.some( field => ! desiredFields.has( field ) ) ) {
		return false;
	}

	const rowsMatch = currentRows.length === desiredRows.length && currentRows.every(
		( fields, index ) => fields.length === desiredRows[ index ].length &&
			fields.every( ( field, fieldIndex ) => field === desiredRows[ index ][ fieldIndex ] )
	);
	return ! rowsMatch;
}
