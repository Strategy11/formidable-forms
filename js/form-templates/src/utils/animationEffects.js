/**
 * Internal dependencies
 */
import { PREFIX } from '../shared';
import { isVisible, show } from './';

/**
 * Applies a fade-in animation to an element.
 *
 * @param {HTMLElement} element The element to apply the fade-in to.
 * @param {string} [fadingClass=`${PREFIX}-flex`] The CSS class to apply during the fading.
 * @return {void}
 */
export const fadeIn = ( element, fadingClass = `${ PREFIX }-flex` ) => {
	if ( ! element ) {
		return;
	}

	if ( ! isVisible( element ) ) {
		show( element );
	}

	element.classList.add( `${ PREFIX }-fadein`, fadingClass );

	element.addEventListener( 'animationend', () => {
		element.classList.remove( `${ PREFIX }-fadein`, fadingClass );
	}, { once: true });
};
