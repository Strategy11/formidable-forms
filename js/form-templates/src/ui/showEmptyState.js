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
import { show, hide, showElements, hideElements } from '../utils';

/**
 * Display the search-empty state.
 *
 * @return {void}
 */
export function showSearchEmptyState() {
	const { notEmptySearchText } = getAppState();
	const { pageTitle, emptyState, emptyStateButton } = getElements();

	// Toggle visibility and remove attributes based on search status
	if ( VIEW_SLUGS.SEARCH === emptyState.dataset?.view ) {
		if ( notEmptySearchText ) {
			show( emptyState );
			hide( pageTitle );
		} else {
			hide( emptyState );
			emptyState.removeAttribute( 'data-view' );
		}

		return;
	}

	// Assign state attributes
	emptyState.setAttribute( 'data-view', VIEW_SLUGS.SEARCH );

	// Update text content
	const { emptyStateTitle, emptyStateText } = getElements();
	emptyStateTitle.textContent = __( 'No results found', 'formidable' );
	emptyStateText.textContent = __(
		'Sorry, we didn\'t find any templates that match your criteria.',
		'formidable'
	);
	emptyStateButton.textContent = __( 'Start from scratch', 'formidable' );

	// Display the empty state
	hide( pageTitle );
	showElements([ emptyState, emptyStateButton ]);
};

/**
 * Display the favorites-empty state.
 *
 * @return {void}
 */
export function showFavoritesEmptyState() {
	const { pageTitle, emptyState, emptyStateButton } = getElements();

	// Assign state attributes
	emptyState.setAttribute( 'data-view', VIEW_SLUGS.FAVORITES );

	// Update text content
	const { emptyStateTitle, emptyStateText } = getElements();
	emptyStateTitle.textContent = __( 'No favorites', 'formidable' );
	emptyStateText.textContent = __(
		'You haven\'t added any templates to your favorites yet.',
		'formidable'
	);

	// Display the empty state
	hideElements([ pageTitle, emptyStateButton ]);
	show( emptyState );
};

/**
 * Display the custom-empty state.
 *
 * @return {void}
 */
export function showCustomTemplatesEmptyState() {
	const { pageTitle, emptyState, emptyStateButton } = getElements();

	// Assign state attributes
	emptyState.setAttribute( 'data-view', VIEW_SLUGS.CUSTOM );

	// Update text content
	const { emptyStateTitle, emptyStateText } = getElements();
	emptyStateTitle.textContent = __( 'No custom templates', 'formidable' );
	emptyStateText.textContent = __(
		'You haven\'t created any custom templates yet. You can create a new blank form or explore the wide range of templates.',
		'formidable'
	);
	emptyStateButton.textContent = __( 'All Templates', 'formidable' );

	// Display the empty state
	hide( pageTitle );
	showElements([ emptyState, emptyStateButton ]);
};

/**
 * Display the available-templates-empty state.
 *
 * @return {void}
 */
export function showAvailableTemplatesEmptyState() {
	const { pageTitle, emptyState, emptyStateButton } = getElements();

	// Assign state attributes
	emptyState.setAttribute( 'data-view', VIEW_SLUGS.AVAILABLE_TEMPLATES );

	// Update text content
	const { emptyStateTitle, emptyStateText } = getElements();
	emptyStateTitle.textContent = __( 'No Templates Available', 'formidable' );
	emptyStateText.textContent = __(
		'Upgrade to PRO for 200+ options or explore Free Templates.',
		'formidable'
	);

	// Display the empty state
	hideElements([ pageTitle, emptyStateButton ]);
	show( emptyState );
};
