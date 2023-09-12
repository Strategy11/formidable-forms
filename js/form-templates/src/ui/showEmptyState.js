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
import { show } from '../utils';

/**
 * Display the search-empty state.
 *
 * @return {void}
 */
export const showSearchEmptyState = () => {
	const { emptyState } = getElements();

	// Exit early if the UI is already in the empty search state
	if ( 'search' === emptyState.dataset?.ui ) {
		return;
	}

	const { emptyStateTitle, emptyStateText, emptyStateButton } = getElements();

	// Update text content
	emptyStateTitle.textContent = __( 'No results found', 'sherv-challenge' );
	emptyStateText.textContent = __(
		'Sorry, we didn\'t find any templates that match your criteria.',
		'sherv-challenge'
	);
	emptyStateButton.textContent = __( 'Start from scratch', 'sherv-challenge' );

	// Assign unique ID and state attributes
	emptyStateButton.setAttribute( 'id', 'frm-search-empty-state-button' );
	emptyState.setAttribute( 'data-ui', 'search' );

	// Display the empty state element
	show( emptyState );
};
