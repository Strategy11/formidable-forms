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
 * Internal dependencies
 */
import { show } from '../utils';
import { emptyState, emptyStateTitle, emptyStateText, emptyStateButton } from '../elements';

/**
 * Display the search-empty state.
 *
 * @since x.x
 */
export const showSearchEmptyState = () => {
	// Exit early if the UI is already in the empty search state
	if ( 'search' === emptyState.dataset?.ui ) {
		return;
	}

	// Update text content
	emptyStateTitle.textContent = __( 'No results found', 'formidable' );
	emptyStateText.textContent = __( 'Sorry, we didn\'t find any templates that match your criteria.', 'formidable' );
	emptyStateButton.textContent = __( 'Start from scratch', 'formidable' );

	// Assign unique ID and state attributes
	emptyStateButton.setAttribute( 'id', 'frm-search-empty-state-button' );
	emptyState.setAttribute( 'data-ui', 'search' );

	// Display the empty state element
	show( emptyState );
};
