/**
 * Internal dependencies
 */
import { HIDDEN_CLASS } from 'core/constants';

/**
 * Shows specified elements by removing the hidden class.
 *
 * @param {Array<Element>} elements An array of elements to show.
 * @return {void}
 */
export const showElements = elements =>
	Array.from( elements )?.forEach( element => show( element ) );

/**
 * Hides specified elements by adding the hidden class.
 *
 * @param {Array<Element>} elements An array of elements to hide.
 * @return {void}
 */
export const hideElements = elements =>
	Array.from( elements )?.forEach( element => hide( element ) );

/**
 * Removes the hidden class to show the element.
 *
 * @param {Element} element The element to show.
 * @return {void}
 */
export const show = element => element?.classList.remove( HIDDEN_CLASS );

/**
 * Adds the hidden class to hide the element.
 *
 * @param {Element} element The element to hide.
 * @return {void}
 */
export const hide = element => element?.classList.add( HIDDEN_CLASS );

/**
 * Checks if an element is visible.
 *
 * @param {HTMLElement} element The HTML element to check for visibility.
 * @return {boolean} Returns true if the element is visible, otherwise false.
 */
export const isVisible = element => {
	const styles = window.getComputedStyle( element );
	return styles.getPropertyValue( 'display' ) !== 'none';
};
