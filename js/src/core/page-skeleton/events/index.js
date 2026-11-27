/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Resets the value of the search input and triggers an input event.
 *
 * @return {void}
 */
export function resetSearchInput() {
	const { searchInput } = getElements();

	searchInput.value = '';
	searchInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
}

export * from './categoryListener';
