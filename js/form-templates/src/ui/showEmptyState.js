/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { VIEW_SLUGS, getAppState } from '../shared';
import { onEmptyStateButtonClick } from '../events';
import { show, hide, showElements, hideElements, onClickPreventDefault } from '../utils';

/**
 * Display the search-empty state.
 *
 * @return {void}
 */
export const showSearchEmptyState = () => {
	const { notEmptySearchText } = getAppState();
	const { pageTitle, emptyState, emptyStateButton } = getElements();

	// Toggle visibility and remove attributes based on search status
	if ( VIEW_SLUGS.SEARCH === emptyState.dataset?.view ) {
		if ( notEmptySearchText ) {
			show( emptyState );
			hide( pageTitle );
		} else {
			hide( emptyState );
			// Clear button's unique ID and detach its click listener
			emptyStateButton.removeAttribute( 'id' );
			emptyStateButton.removeEventListener( 'click', onEmptyStateButtonClick );
			// Clear element's state attribute
			emptyState.removeAttribute( 'data-view' );
		}

		return;
	}

	// Assign unique ID and state attributes
	emptyStateButton.setAttribute( 'id', 'frm-search-empty-state-button' );
	emptyState.setAttribute( 'data-view', VIEW_SLUGS.SEARCH );

	// Attach click event listener to the button
	onClickPreventDefault( emptyStateButton, onEmptyStateButtonClick );

	// Update text content
	const { emptyStateTitle, emptyStateText } = getElements();
	emptyStateTitle.textContent = __( 'No results found', 'sherv-challenge' );
	emptyStateText.textContent = __(
		'Sorry, we didn\'t find any templates that match your criteria.',
		'sherv-challenge'
	);
	emptyStateButton.textContent = __( 'Start from scratch', 'sherv-challenge' );

	// Display the empty state
	hide( pageTitle );
	showElements([ emptyState, emptyStateButton ]);
};

/**
 * Display the favorites-empty state.
 *
 * @return {void}
 */
export const showFavoritesEmptyState = () => {
	const { pageTitle, emptyState, emptyStateButton } = getElements();

	// Assign state attributes
	emptyState.setAttribute( 'data-view', VIEW_SLUGS.FAVORITES );

	// Update text content
	const { emptyStateTitle, emptyStateText } = getElements();
	emptyStateTitle.textContent = __( 'No favorites', 'sherv-challenge' );
	emptyStateText.textContent = __(
		'You haven\'t added any templates to your favorites yet.',
		'sherv-challenge'
	);

	// Display the empty state
	hideElements([ pageTitle, emptyStateButton ]);
	show( emptyState );
};
