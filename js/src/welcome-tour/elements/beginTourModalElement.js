/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { maybeCreateModal, div, p, a } from 'core/utils';
import { addElements, getElements } from './elements';
import { MODAL_SIZE, TOUR_URL } from '../shared';

/**
 * Build the begin tour modal element.
 *
 * @return {void}
 */
export function buildBeginTourModalElement() {
	addBeginTourModalToElements( createBeginTourModalElement() );
}

/**
 * Create and return the begin tour modal HTML element.
 *
 * @private
 * @return {HTMLElement} The begin tour modal element.
 */
function createBeginTourModalElement() {
	const beginTourModal = maybeCreateModal(
		'frm-get-started-modal',
		{
			title: __( 'Get Started with Formidable Forms', 'formidable' ),
			content: p( {
				className: 'frm-px-md',
				text: __( 'Here\'s a quick checklist to help you set up and explore the key features of the plugin, so you can start building powerful forms in no time.', 'formidable' )
			} ),
			footer: div( {
				className: 'frmcenter',
				child: a( {
					className: 'button button-primary frm-button-primary',
					href: TOUR_URL,
					text: __( 'Begin Tour', 'formidable' )
				} )
			} ),
			width: MODAL_SIZE,
			dialogClass: 'frm-fadein-up-back',
		}
	);

	beginTourModal.classList.add( 'frm_wrap', 'frm-welcome-tour-modal', 'frmcenter' );

	return beginTourModal;
}

/**
 * Inject begin tour modal elements into the DOM and the elements object.
 *
 * @private
 * @param {HTMLElement} beginTourModal The begin tour modal element.
 * @return {void}
 */
function addBeginTourModalToElements( beginTourModal ) {
	const elements = getElements();

	if ( elements.beginTourModal || ! ( beginTourModal instanceof HTMLElement ) ) {
		return;
	}

	addElements( { beginTourModal } );
}
