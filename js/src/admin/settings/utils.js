/**
 * Gets the field ID from the single settings element or the closest single settings element to the field.
 *
 * @since x.x
 *
 * @param {HTMLElement} singleSettings The single settings element.
 * @param {HTMLElement} field          The field element to get field ID from.
 *
 * @return {string|undefined} The field ID or undefined if not found.
 */
export const getFieldId = ( singleSettings = null, field = null ) =>
	singleSettings ? singleSettings.dataset.fid : field?.closest( '.frm-single-settings' )?.dataset.fid;

/**
 * Gets the field type from the single settings element.
 *
 * @since x.x
 *
 * @param {HTMLElement} singleSettings The single settings element.
 * @param {HTMLElement} field          The field element.
 *
 * @return {string|undefined} The field type or undefined if not found.
 */
export const getFieldType = ( singleSettings = null, field = null ) => {
	if ( ! singleSettings ) {
		singleSettings = field?.closest( '.frm-single-settings' );
	}

	return singleSettings?.className.match( /frm-type-(\w+)/ )?.[ 1 ];
};
