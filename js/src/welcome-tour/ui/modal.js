/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { maybeCreateModal, tag, div } from 'core/utils';
import { IS_WELCOME_TOUR_SEEN, MODAL_SIZE } from '../shared';

/**
 * Modal widget instance.
 *
 * @type {Object|null}
 */
let modalWidget = null;

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
export async function initializeModal() {
	if ( IS_WELCOME_TOUR_SEEN ) {
		return;
	}

	modalWidget = maybeCreateModal(
		'frm-get-started-modal',
		{
			title: __( 'Get Started with Formidable Forms', 'formidable' ),
			content: tag( 'p', {
				className: 'frm-px-md',
				text: __( 'Here\'s a quick checklist to help you set up and explore the key features of the plugin, so you can start building powerful forms in no time.', 'formidable' )
			} ),
			footer: div( {
				className: 'frmcenter',
				child: tag( 'button', {
					className: 'button button-primary frm-button-primary',
					text: __( 'Begin Tour', 'formidable' )
				} )
			} ),
			width: MODAL_SIZE
		}
	);

	modalWidget.classList.add( 'frm_wrap', 'frm-welcome-tour-modal', 'frmcenter' );
}

/**
 * Retrieve the modal widget.
 *
 * @return {Object|false} The modal widget or false.
 */
export function getModalWidget() {
	return modalWidget;
}
