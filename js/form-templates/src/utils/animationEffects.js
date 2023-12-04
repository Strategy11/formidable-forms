/**
 * Internal dependencies
 */
import { PREFIX } from '../shared';
import { isVisible, show } from './';

/**
 * Applies a fade-in animation to an element.
 *
 * @param {HTMLElement} element The element to apply the fade-in to.
 * @return {void}
 */
export const fadeIn = ( element ) => {
	if ( ! element ) {
		return;
	}

	if ( ! isVisible( element ) ) {
		show( element );
	}

	element.classList.add( 'frm-fadein-up' );

	element.addEventListener( 'animationend', () => {
		element.classList.remove( 'frm-fadein-up' );
	}, { once: true });
};
