/**
 * Target time spent inserting fields before their controls initialize and input can run.
 * One expensive field or an initialization listener can exceed this budget.
 */
const FIELD_RENDER_BUDGET = 8;

/**
 * Separate the network batch size from the amount of synchronous browser work.
 *
 * @since x.x
 * @param {Array}    fields          The fields returned by one request, in insertion order.
 * @param {Function} renderField     Inserts a field and returns its initialization data.
 * @param {Function} initializeSlice Initializes all fields inserted since the last yield.
 * @return {Promise<void>} Resolves when every field has been inserted and initialized.
 */
export async function processFieldLoadBatch( fields, renderField, initializeSlice ) {
	let slice = [];
	let sliceStarted = performance.now();
	const lastIndex = fields.length - 1;

	for ( let index = 0; index <= lastIndex; ++index ) {
		slice.push( renderField( fields[ index ] ) );
		const isLastField = index === lastIndex;
		if ( ! isLastField && performance.now() - sliceStarted < FIELD_RENDER_BUDGET ) {
			continue;
		}

		initializeSlice( slice );
		slice = [];
		if ( ! isLastField ) {
			// A new task lets input run. A resolved Promise would only queue a microtask.
			await new Promise( resolve => setTimeout( resolve, 0 ) );
			sliceStarted = performance.now();
		}
	}
}
