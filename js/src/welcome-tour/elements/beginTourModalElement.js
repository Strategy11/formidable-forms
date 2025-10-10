/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { maybeCreateModal, div, p, button } from 'core/utils';
import { addElements, getElements } from './elements';
import { MODAL_SIZE } from '../shared';

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
	const beginTourButton = button( {
		className: 'button button-primary frm-button-primary',
		text: __( 'Begin Tour', 'formidable' )
	} );

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
				child: beginTourButton
			} ),
			width: MODAL_SIZE,
			dialogClass: 'frm-fadein-up',
		}
	);

	beginTourModal.classList.add( 'frm_wrap', 'frm-welcome-tour-modal', 'frmcenter' );

	return { beginTourModal, beginTourButton };
}

/**
 * Inject begin tour modal elements into the DOM and the elements object.
 *
 * @private
 * @param {Object}      root0
 * @param {HTMLElement} root0.beginTourModal  The begin tour modal element.
 * @param {HTMLElement} root0.beginTourButton The begin tour button element.
 * @return {void}
 */
function addBeginTourModalToElements( { beginTourModal, beginTourButton } ) {
	const elements = getElements();

	if ( elements.beginTourModal || undefined === beginTourModal ) {
		return;
	}

	addElements( { beginTourModal, beginTourButton } );
}
