import { MODAL_SIZES } from '../shared';

let modalWidget = null;

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
export async function initializeModal() {
	const { initModal, offsetModalY } = window.frmAdminBuild;

	modalWidget = initModal( '#frm-form-templates-modal', MODAL_SIZES.GENERAL );

	// Set the vertical offset for the modal
	if ( modalWidget ) {
		offsetModalY( modalWidget, '103px' );
	}

	// Customize the confirm modal appearance: adjusting its width and vertical position
	wp.hooks.addAction( 'frmAdmin.beforeOpenConfirmModal', 'frmFormTemplates', options => {
		const { $info: confirmModal } = options;

		confirmModal.dialog( 'option', 'width', MODAL_SIZES.CREATE_TEMPLATE );
		offsetModalY( confirmModal, '103px' );
	} );
}

/**
 * Retrieve the modal widget.
 *
 * @return {Object|false} The modal widget or false.
 */
export function getModalWidget() {
	return modalWidget;
}
