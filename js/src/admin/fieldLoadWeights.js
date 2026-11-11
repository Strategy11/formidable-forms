const BATCH_WEIGHT_LIMIT = 40;
const FIELD_WEIGHTS = {
	select: 10,
	radio: 5,
	checkbox: 5,
	name: 3,
	address: 3,
	file: 3,
	form: 20,
	data: 20,
	lookup: 20
};
const LIGHT_FIELD_TYPES = new Set( [
	'text', 'textarea', 'email', 'url', 'password', 'number', 'phone',
	'date', 'time', 'hidden', 'html', 'user_id', 'range', 'submit'
] );
const ADDON_FIELD_WEIGHT = 10;

/**
 * Keep expensive choice and add-on settings from filling a request with heavy fields.
 * Lightweight fields can still share the caller's full batch size.
 *
 * @since x.x
 * @param {Iterable<string>} fieldIds      Pending field IDs in request order.
 * @param {number}           maxFields     The caller's maximum number of fields per request.
 * @param {number}           minimumWeight The baseline cost of a field in this request.
 * @return {string[]} The next batch, preserving order and always including the first field.
 */
export function getFieldLoadBatch( fieldIds, maxFields, minimumWeight = 1 ) {
	const batch = [];
	let weight = 0;
	for ( const fieldId of fieldIds ) {
		const field = document.getElementById( `frm_field_id_${ fieldId }` );
		const type = field?.dataset.type || field?.dataset.ftype;
		const fieldWeight = Math.max( minimumWeight, FIELD_WEIGHTS[ type ] ||
			( LIGHT_FIELD_TYPES.has( type ) ? 1 : ADDON_FIELD_WEIGHT ) );
		if ( batch.length && ( batch.length >= maxFields || weight + fieldWeight > BATCH_WEIGHT_LIMIT ) ) {
			break;
		}
		batch.push( fieldId );
		weight += fieldWeight;
	}
	return batch;
}
