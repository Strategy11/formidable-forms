/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { showElements, hideElements } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState } from '../shared';

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

	hideElements( [ emptyStateButton ] );
	showElements( [ emptyState ] );
}
