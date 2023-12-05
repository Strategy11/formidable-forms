/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getAppStateProperty } from '../shared';

/**
 * Sets the page title based on a given string or the currently selected category.
 *
 * @param {string} [title] Optional title to display.
 * @return {void}
 */
export function updatePageTitle( title ) {
	const { pageTitleText } = getElements();

	const newTitle =
		title ||
		getAppStateProperty( 'selectedCategoryEl' ).querySelector( '.frm-form-templates-cat-text' ).textContent;

	pageTitleText.textContent = newTitle;
}
