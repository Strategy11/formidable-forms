/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { frmAddonAPI } from '../api/class-addon-api';

const { div, tag } = frmDom;
const MODAL_ID = 'frmTemplateDependenciesModal';

/**
 * Let users activate template dependencies before retrying the import.
 *
 * @since x.x
 *
 * @param {Array}    addons   Missing dependencies returned by the server.
 * @param {Function} onImport Retry the import with a fresh dependency check.
 * @return {void}
 */
export function showTemplateDependencies( addons, onImport ) {
	const continueButton = tag( 'button', {
		className: 'button button-primary frm-button-primary frm-mx-0',
		text: __( 'Continue import', 'formidable' )
	} );
	continueButton.type = 'button';
	let pendingActions = 0;
	const onPendingChange = change => {
		pendingActions += change;
		continueButton.disabled = pendingActions > 0;
	};
	const rows = addons.flatMap( addon => createDependencyRows( addon, onPendingChange ) );
	const headings = [
		__( 'Add-on', 'formidable' ),
		__( 'Status', 'formidable' ),
		__( 'Action', 'formidable' )
	].map( label => {
		const heading = tag( 'th', label );
		heading.scope = 'col';
		return heading;
	} );
	const table = tag( 'table', {
		className: 'widefat frm-border frm-m-0',
		children: [
			tag( 'caption', {
				className: 'screen-reader-text',
				text: __( 'Required add-ons', 'formidable' )
			} ),
			tag( 'thead', { child: tag( 'tr', { children: headings } ) } ),
			tag( 'tbody', { children: rows } )
		]
	} );
	const manualMessages = [ ...new Set(
		addons.filter( addon => ! addon.action ).map( addon => addon.message )
	) ].map( message => tag( 'p', {
		className: 'frm-mt-sm frm-mb-0 frm-text-grey-600',
		text: message
	} ) );
	const content = div( {
		className: 'inside',
		children: [
			tag( 'p', {
				className: 'frm-mt-0 frm-mb-sm frm-text-grey-600',
				text: __( 'Install and activate these add-ons to import this template.', 'formidable' )
			} ),
			table,
			...manualMessages
		]
	} );
	const cancelButton = frmDom.modal.footerButton( {
		text: __( 'Cancel', 'formidable' ),
		buttonType: 'cancel',
		className: 'dismiss frm-button-secondary'
	} );
	cancelButton.href = '#';
	const footer = div( { children: [ cancelButton, continueButton ] } );
	const modal = frmDom.modal.maybeCreateModal( MODAL_ID, {
		title: __( 'Required add-ons', 'formidable' ),
		content,
		footer
	} );
	modal.classList.add( 'frm_common_modal', 'frm-white-body', 'wp-core-ui' );
	const closeButton = modal.querySelector( '.frm_modal_top .dismiss' );
	closeButton.href = '#';
	closeButton.setAttribute( 'aria-label', __( 'Close', 'formidable' ) );
	continueButton.addEventListener( 'click', () => {
		continueButton.disabled = true;
		closeButton.click();
		onImport();
	} );
}

/**
 * Create a compact dependency row and a hidden row for action errors.
 *
 * @since x.x
 *
 * @param {Object}   addon           The add-on label and available action.
 * @param {Function} onPendingChange Track pending activation requests.
 * @return {HTMLElement[]} The dependency and error rows.
 */
function createDependencyRows( addon, onPendingChange ) {
	let statusLabel = __( 'Action needed', 'formidable' );
	if ( addon.action ) {
		statusLabel = 'frm_activate_addon' === addon.action
			? __( 'Inactive', 'formidable' ) : __( 'Not installed', 'formidable' );
	}
	const status = tag( 'span', {
		className: 'frm-meta-tag frm-grey-tag',
		text: statusLabel
	} );
	status.setAttribute( 'role', 'status' );
	status.setAttribute( 'aria-atomic', 'true' );
	const label = tag( 'th', addon.name );
	label.scope = 'row';
	const actionCell = tag( 'td' );
	const row = tag( 'tr', {
		children: [ label, tag( 'td', { child: status } ), actionCell ]
	} );
	const error = div( { className: 'frm_error_style frm-m-0' } );
	error.setAttribute( 'role', 'alert' );
	const errorCell = tag( 'td', { child: error } );
	errorCell.colSpan = 3;
	const errorRow = tag( 'tr', { child: errorCell } );
	errorRow.hidden = true;
	const showError = message => {
		status.textContent = __( 'Failed', 'formidable' );
		status.className = 'frm-meta-tag frm-red-tag';
		error.textContent = message;
		errorRow.hidden = false;
	};
	if ( ! addon.action ) {
		const link = tag( 'a', {
			className: 'button frm-button-secondary frm-button-sm',
			text: __( 'Manage add-ons', 'formidable' )
		} );
		link.href = addon.url;
		link.target = '_blank';
		link.rel = 'noopener noreferrer';
		actionCell.append( link );
		return [ row, errorRow ];
	}

	const button = tag( 'button', {
		className: 'button frm-button-secondary frm-button-sm',
		text: 'frm_activate_addon' === addon.action
			? __( 'Activate', 'formidable' ) : __( 'Install & activate', 'formidable' )
	} );
	button.type = 'button';
	button.addEventListener( 'click', async () => {
		onPendingChange( 1 );
		errorRow.hidden = true;
		await activateDependency( addon, button, status, showError );
		onPendingChange( -1 );
	} );
	actionCell.append( button );
	return [ row, errorRow ];
}

/**
 * Use the existing add-on endpoints and keep failures retryable.
 *
 * @since x.x
 *
 * @param {Object}      addon     The add-on action and plugin identifier.
 * @param {HTMLElement} button    The install or activate button.
 * @param {HTMLElement} status    The row's status badge.
 * @param {Function}    showError Display an action error below the row.
 * @return {Promise<void>} Resolves when the action finishes.
 */
async function activateDependency( addon, button, status, showError ) {
	button.disabled = true;
	button.classList.add( 'frm_loading_button' );
	status.className = 'frm-meta-tag frm-grey-tag';
	status.textContent = __( 'Activating…', 'formidable' );
	let response;
	try {
		response = await frmAddonAPI.toggleAddonState( addon.action, addon.plugin );
	} catch {
		showError( __( 'Unable to activate this add-on. Please try again.', 'formidable' ) );
		button.disabled = false;
		button.classList.remove( 'frm_loading_button' );
		return;
	}

	const result = response?.data ?? response;
	const success = response?.success !== false &&
		( typeof result === 'string' || typeof result?.message === 'string' ) &&
		result?.success !== false && ! result?.form;
	button.classList.remove( 'frm_loading_button' );
	if ( ! success ) {
		showError( result?.message || result?.error ||
			__( 'Unable to activate this add-on. Check your license and plugin installation permissions, then try again.', 'formidable' ) );
		button.disabled = false;
		return;
	}

	status.className = 'frm-meta-tag frm-lt-green-tag';
	status.textContent = __( 'Active', 'formidable' );
	button.textContent = __( 'Activated', 'formidable' );
}
