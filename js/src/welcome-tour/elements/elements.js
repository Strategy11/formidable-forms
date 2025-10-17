/**
 * External dependencies
 */
import { createPageElements } from 'core/factory';

let elements;

const welcomeTour = document.getElementById( 'frm-welcome-tour' );
if ( welcomeTour ) {
	elements = {
		welcomeTour,
		checklist: welcomeTour.querySelector( '.frm-checklist' ),
		dismiss: welcomeTour.querySelector( '.frm-checklist__dismiss' ),
		spotlight: document.querySelector( '.frm-welcome-tour-spotlight' ),
	};
}

export const { getElements, addElements } = createPageElements( elements );
