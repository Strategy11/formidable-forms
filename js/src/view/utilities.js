/**
 *  Utilities for View block
 */

export function updateViewId( new_view_id, setAttributes ) {
	let show_counts = formidable_form_selector.show_counts;
	let show_count = show_counts && show_counts[ new_view_id ] && show_counts[ new_view_id ].meta_value ? show_counts[ new_view_id ].meta_value : '';
	let view_options = formidable_form_selector.view_options;
	let limit = view_options && view_options[ new_view_id ] && view_options[ new_view_id ].meta_value && view_options[ new_view_id ].meta_value.limits ? view_options[ new_view_id ].meta_value.limits : null;

	setAttributes( {
		view_id: new_view_id,
		use_default_limit: ( show_count === 'calendar' || show_count === 'one' || limit ) ? false : true,
	} );
}