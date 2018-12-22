/**
 *  Utilities for View block
 */

export function updateViewId( newViewId, setAttributes ) {
	const showCounts = formidable_form_selector.show_counts;
	const showCount = showCounts && showCounts[ newViewId ] && showCounts[ newViewId ].meta_value ? showCounts[ newViewId ].meta_value : '';
	const viewOptions = formidable_form_selector.view_options;
	const limit = viewOptions && viewOptions[ newViewId ] && viewOptions[ newViewId ].meta_value && viewOptions[ newViewId ].meta_value.limits ? viewOptions[ newViewId ].meta_value.limits : null;

	setAttributes( {
		viewId: newViewId,
		useDefaultLimit: ( showCount === 'calendar' || showCount === 'one' || limit ) ? false : true,
	} );
}
