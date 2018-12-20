/**
 *  Utilities for Form block
 */

/**
 * Filters a list of forms to remove Repeaters, drafts/trash, templates, and forms without names
 *
 * @param forms
 * @returns {{}}
 */
export function filterForms( forms ) {
	if ( ! forms ) {
		return {};
	}
	return Object.keys( forms ).reduce( ( list, key ) => {
		if (
			( ! forms[ key ].hasOwnProperty( 'parent_form_id' ) || forms[ key ].parent_form_id === '0' ) &&
			( forms[ key ].hasOwnProperty( 'status' ) && forms[ key ].status === 'published' ) &&
			( ! forms[ key ].hasOwnProperty( 'is_template' ) || forms[ key ].is_template === '0' ) &&
			( forms[ key ].hasOwnProperty( 'name' ) && forms[ key ].name )

		) {
			return {
				...list,
				[ key ]: forms[ key ],
			};
		}
		return list;
	}, {} );
}