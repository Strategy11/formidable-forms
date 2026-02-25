/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { PLUGIN_URL, HIDDEN_CLASS } from 'core/constants';

/**
 * Internal dependencies
 */
import { PREFIX } from '../constants';

const { tag, div, a, img } = window.frmDom;

/**
 * Create and return the Empty State HTML element.
 *
 * @return {HTMLElement} The Empty State element.
 */
export function createEmptyStateElement() {
	const button = a( {
		className: 'button button-primary frm-button-primary',
	} );
	button.setAttribute( 'role', 'button' );

	return div( {
		id: `${ PREFIX }-empty-state`,
		className: `frm-flex-col frm-flex-center frm-gap-md ${ HIDDEN_CLASS }`,
		children: [
			img( {
				src: `${ PLUGIN_URL }/images/page-skeleton/empty-state.svg`,
				alt: __( 'Empty State', 'formidable' ),
			} ),
			div( {
				className: 'frmcenter',
				children: [
					tag( 'h2', {
						className: `${ PREFIX }-title frm-mb-0`,
					} ),
					tag( 'p', {
						className: `${ PREFIX }-text frm-mb-0`,
					} ),
				],
			} ),
			button,
		],
	} );
}

/**
 * Return the elements related to the Empty State.
 *
 * @return {Object} Object containing Empty State related DOM elements.
 */
export function getEmptyStateElements() {
	const emptyState = document.querySelector( `#${ PREFIX }-empty-state` );

	return {
		emptyState,
		emptyStateTitle: emptyState?.querySelector( `.${ PREFIX }-title` ),
		emptyStateText: emptyState?.querySelector( `.${ PREFIX }-text` ),
		emptyStateButton: emptyState?.querySelector( '.button' ),
	};
}
