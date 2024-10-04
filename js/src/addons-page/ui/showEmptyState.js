/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { showElements } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from "../elements";
import { getState } from '../shared';
import { addonRequestURL } from '../constants';

/**
 * Display the search-empty state.
 *
 * @return {void}
 */
export function showEmptyState() {
	const { selectedCategory } = getState();
	const { emptyState, emptyStateButton, emptyStateTitle, emptyStateText } =
		getElements();

	emptyState.setAttribute( 'data-view', selectedCategory );

	emptyStateTitle.textContent = __( 'No add-ons found', 'formidable' );
	emptyStateText.textContent = __(
		'Sorry, we didn\'t find any add-ons that match your criteria.',
		'formidable'
	);

	emptyStateButton.textContent = __( 'Request Add-On', 'formidable' );
	emptyStateButton.href = addonRequestURL;
	emptyStateButton.setAttribute( 'target', '_blank' );

	showElements( [ emptyState, emptyStateButton ] );
}
