/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { frmAddonAPI } from '../api/class-addon-api';

const { div, span, tag, svg } = frmDom;
const MODAL_ID = 'frmTemplateDependenciesModal';

/**
 * Let users install the add-ons a template needs, then import it in one step.
 *
 * @since x.x
 *
 * @param {Array}    addons   Missing dependencies returned by the server.
 * @param {Function} onImport Retry the import with a fresh dependency check.
 * @return {void}
 */
export function showTemplateDependencies( addons, onImport ) {
	const rows = addons.map( createDependencyRow );
	const pending = rows.filter( row => row.addon.action );
	const manual = rows.filter( row => ! row.addon.action );

	const description = tag( 'p', {
		className: 'frm-template-deps-description',
		text: pending.length
			? _n(
				'It uses fields from an add-on that isn\'t active yet. We\'ll set it up, then create your form.',
				'It uses fields from add-ons that aren\'t active yet. We\'ll set them up, then create your form.',
				addons.length,
				'formidable'
			)
			: _n(
				'It uses fields from an add-on that isn\'t active on this site.',
				'It uses fields from add-ons that aren\'t active on this site.',
				addons.length,
				'formidable'
			)
	} );
	const list = tag( 'ul', {
		className: 'frm-template-deps-list',
		children: rows.map( row => row.element )
	} );
	const notes = [ ...new Set( manual.map( row => row.addon.message ) ) ].map( message => tag( 'p', {
		className: 'frm-template-deps-note',
		text: message
	} ) );
	const content = div( {
		className: 'inside',
		children: [ description, list, ...notes ]
	} );

	const cancelButton = frmDom.modal.footerButton( {
		text: __( 'Cancel', 'formidable' ),
		buttonType: 'cancel',
		className: 'dismiss frm-button-secondary'
	} );
	cancelButton.href = '#';
	const footerButtons = [ cancelButton ];

	let primaryButton;
	if ( pending.length ) {
		primaryButton = tag( 'button', {
			className: 'button button-primary frm-button-primary',
			text: getPrimaryLabel( pending )
		} );
		primaryButton.type = 'button';
		footerButtons.push( primaryButton );
	} else if ( manual.some( row => row.addon.url ) ) {
		const manageLink = tag( 'a', {
			className: 'button button-primary frm-button-primary',
			text: __( 'Go to add-ons', 'formidable' )
		} );
		manageLink.href = manual.find( row => row.addon.url ).addon.url;
		footerButtons.push( manageLink );
	}

	const modal = frmDom.modal.maybeCreateModal( MODAL_ID, {
		title: 1 === addons.length
			// translators: %s: Add-on name.
			? sprintf( __( 'This template needs %s', 'formidable' ), addons[ 0 ].name )
			// translators: %d: Number of add-ons.
			: sprintf( __( 'This template needs %d add-ons', 'formidable' ), addons.length ),
		content,
		footer: div( { className: 'frm-template-deps-footer', children: footerButtons } ),
		width: '500px',
		dialogClass: 'frm-template-deps-dialog'
	} );
	modal.classList.add( 'frm_common_modal', 'frm-white-body', 'wp-core-ui' );
	const closeButton = modal.querySelector( '.frm_modal_top .dismiss' );
	closeButton.href = '#';
	closeButton.setAttribute( 'aria-label', __( 'Close', 'formidable' ) );

	primaryButton?.addEventListener( 'click', async () => {
		primaryButton.disabled = true;
		primaryButton.classList.add( 'frm_loading_button' );
		cancelButton.setAttribute( 'aria-disabled', 'true' );

		let failed = false;
		// One at a time, since each request installs or activates a plugin.
		for ( const row of pending.filter( item => ! item.done ) ) {
			if ( ! await row.activate() ) {
				failed = true;
			}
		}

		primaryButton.classList.remove( 'frm_loading_button' );
		if ( failed ) {
			primaryButton.disabled = false;
			primaryButton.textContent = __( 'Try again', 'formidable' );
			cancelButton.removeAttribute( 'aria-disabled' );
			return;
		}

		primaryButton.textContent = __( 'Creating form…', 'formidable' );
		closeButton.click();
		onImport();
	} );
}

/**
 * @param {Array} pending Rows that can be installed or activated.
 * @return {string} The primary button label.
 */
function getPrimaryLabel( pending ) {
	if ( pending.every( row => 'frm_activate_addon' === row.addon.action ) ) {
		return _n( 'Activate and use template', 'Activate both and use template', pending.length, 'formidable' );
	}
	return _n( 'Install and use template', 'Install both and use template', pending.length, 'formidable' );
}

/**
 * Build one add-on row with a status that follows the install.
 *
 * @since x.x
 *
 * @param {Object} addon The add-on name, description and available action.
 * @return {Object} The row element, its add-on and an activate function.
 */
function createDependencyRow( addon ) {
	const status = span( { className: 'frm-template-deps-status' } );
	status.setAttribute( 'role', 'status' );
	const error = tag( 'p', { className: 'frm-template-deps-error' } );
	error.setAttribute( 'role', 'alert' );
	error.hidden = true;

	const text = div( {
		className: 'frm-template-deps-text',
		children: [
			div( {
				className: 'frm-template-deps-heading',
				children: [ span( { className: 'frm-template-deps-name', text: addon.name } ), status ]
			} ),
			... addon.description ? [ span( { className: 'frm-template-deps-excerpt', text: addon.description } ) ] : [],
			error
		]
	} );
	const element = tag( 'li', { child: text } );

	const setStatus = ( state, label ) => {
		element.dataset.state = state;
		status.replaceChildren( ... 'done' === state ? [ svg( { href: '#frm_checkmark_icon' } ) ] : [], label );
	};

	let initialLabel = __( 'Not available', 'formidable' );
	if ( addon.action ) {
		initialLabel = 'frm_activate_addon' === addon.action ? __( 'Inactive', 'formidable' ) : __( 'Not installed', 'formidable' );
	}
	setStatus( 'idle', initialLabel );

	const row = { element, addon, done: false };

	/**
	 * Installs or activates the add-on with the existing endpoints.
	 *
	 * @return {Promise<boolean>} Whether the add-on is now active.
	 */
	row.activate = async () => {
		error.hidden = true;
		setStatus( 'working', 'frm_activate_addon' === addon.action ? __( 'Activating…', 'formidable' ) : __( 'Installing…', 'formidable' ) );

		let response;
		try {
			response = await frmAddonAPI.toggleAddonState( addon.action, addon.plugin );
		} catch {
			return fail( __( 'Something went wrong. Please try again.', 'formidable' ) );
		}

		const result = response?.data ?? response;
		const success = response?.success !== false &&
			( typeof result === 'string' || typeof result?.message === 'string' ) &&
			result?.success !== false && ! result?.form;
		if ( ! success ) {
			return fail( result?.message || result?.error ||
				__( 'Check your license and plugin permissions, then try again.', 'formidable' ) );
		}

		row.done = true;
		setStatus( 'done', __( 'Ready', 'formidable' ) );
		return true;
	};

	/**
	 * @param {string} message The error to show under the add-on name.
	 * @return {boolean} Always false.
	 */
	function fail( message ) {
		setStatus( 'failed', __( 'Failed', 'formidable' ) );
		error.textContent = message;
		error.hidden = false;
		return false;
	}

	return row;
}
