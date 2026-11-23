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
 * @param {Array}       addons   Missing dependencies returned by the server.
 * @param {HTMLElement} opener   The template control that opened the dialog.
 * @param {Function}    onImport Retry the import. Pass true to import without the add-ons.
 * @return {void}
 */
export function showTemplateDependencies( addons, opener, onImport ) {
	const progress = span( { className: 'screen-reader-text' } );
	progress.setAttribute( 'role', 'status' );
	const rows = addons.map( addon => createDependencyRow( addon, progress ) );
	const pending = rows.filter( row => row.addon.action );
	const manual = rows.filter( row => ! row.addon.action );

	const description = tag( 'p', {
		id: `${ MODAL_ID }-description`,
		className: 'frm-template-deps-description',
		text: _n(
			'This template uses an add-on that needs setup.',
			'This template uses add-ons that need setup.',
			addons.length,
			'formidable'
		)
	} );
	const list = tag( 'ul', {
		className: 'frm-template-deps-list',
		children: rows.map( row => row.element )
	} );
	list.setAttribute( 'role', 'list' );
	const notes = [ ...new Set( manual.map( row => row.addon.message ) ) ].map( message => tag( 'p', {
		className: 'frm-template-deps-note',
		text: message
	} ) );
	if ( pending.length && manual.length ) {
		notes.push( tag( 'p', {
			className: 'frm-template-deps-note',
			text: __( 'After setup, you can review the remaining add-ons or use the template anyway.', 'formidable' )
		} ) );
	}

	const content = div( {
		className: 'inside',
		children: [ description, list, ...notes, progress ]
	} );

	const cancelButton = tag( 'button', {
		className: 'button button-secondary frm-button-secondary dismiss',
		text: __( 'Cancel', 'formidable' )
	} );
	cancelButton.type = 'button';
	const footerButtons = [ cancelButton ];

	const skipButton = tag( 'button', {
		className: 'button button-secondary frm-button-secondary',
		text: __( 'Use Template Anyway', 'formidable' )
	} );
	skipButton.type = 'button';
	skipButton.hidden = Boolean( pending.length );
	skipButton.addEventListener( 'click', () => {
		closeButton.click();
		onImport( true );
	} );
	footerButtons.push( skipButton );

	let primaryButton;
	if ( pending.length ) {
		primaryButton = tag( 'button', {
			className: 'button button-primary frm-button-primary',
			text: getPrimaryLabel( pending )
		} );
		primaryButton.type = 'button';
		footerButtons.push( primaryButton );
	}

	if ( ! pending.length && manual.some( row => row.addon.url ) ) {
		const manageLink = tag( 'a', {
			className: 'button button-primary frm-button-primary',
			text: __( 'Go to Add-Ons', 'formidable' )
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
		width: 500,
		closeLabel: __( 'Close', 'formidable' ),
		descriptionId: description.id,
		dialogClass: 'frm-template-deps-dialog'
	} );
	modal.classList.add( 'frm_common_modal', 'frm-white-body', 'wp-core-ui' );
	const closeButton = modal.querySelector( '.frm_modal_top .dismiss' );

	let closed = false;
	jQuery( modal ).one( 'dialogclose', () => {
		closed = true;
		if ( ! opener.getClientRects().length ) {
			// Template actions are revealed when their card has focus.
			opener.closest( '.frm-card-item' )?.focus();
		}
		opener.focus();
	} );

	primaryButton?.addEventListener( 'click', async () => {
		if ( primaryButton.hasAttribute( 'aria-disabled' ) ) {
			return;
		}

		primaryButton.setAttribute( 'aria-disabled', 'true' );
		primaryButton.textContent = __( 'Setting up add-ons…', 'formidable' );
		skipButton.hidden = true;

		let failed = false;
		// One at a time, since each request installs or activates a plugin.
		for ( const row of pending.filter( item => ! item.done ) ) {
			if ( ! await row.activate() ) {
				failed = true;
			}

			if ( closed ) {
				// The modal was closed while an add-on was installing, so do not import the template.
				return;
			}
		}

		if ( failed ) {
			primaryButton.removeAttribute( 'aria-disabled' );
			primaryButton.textContent = __( 'Try Again', 'formidable' );
			skipButton.hidden = false;
			return;
		}

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
		return __( 'Activate and Use Template', 'formidable' );
	}
	if ( pending.every( row => 'frm_install_addon' === row.addon.action ) ) {
		return __( 'Install and Use Template', 'formidable' );
	}
	return __( 'Set Up Add-Ons and Use Template', 'formidable' );
}

/**
 * Build one add-on row with a status that follows the install.
 *
 * @since x.x
 *
 * @param {Object}      addon    The add-on name, description and available action.
 * @param {HTMLElement} progress The live region for setup updates.
 * @return {Object} The row element, its add-on and an activate function.
 */
function createDependencyRow( addon, progress ) {
	const status = span( { className: 'frm-meta-tag frm-grey-tag frm-text-xs frm-template-deps-status' } );
	const error = tag( 'p', { className: 'frm-template-deps-error' } );
	error.setAttribute( 'role', 'alert' );

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
		status.textContent = label;
		status.classList.toggle( 'frm-grey-tag', 'done' !== state && 'failed' !== state );
		status.classList.toggle( 'frm-lt-green-tag', 'done' === state );
		status.classList.toggle( 'frm-red-tag', 'failed' === state );
		if ( 'done' === state ) {
			const checkmark = svg( { href: '#frm_checkmark_icon' } );
			checkmark.setAttribute( 'aria-hidden', 'true' );
			status.prepend( checkmark );
		}

		if ( 'working' === state || 'done' === state ) {
			// translators: %1$s: Add-on name. %2$s: Setup status.
			progress.textContent = sprintf( __( '%1$s: %2$s', 'formidable' ), addon.name, label );
		}
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
		error.textContent = '';
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
		const errorSource = span( {
			className: 'screen-reader-text',
			// translators: %s: Add-on name.
			text: `${ sprintf( __( '%s:', 'formidable' ), addon.name ) } `
		} );
		error.replaceChildren( errorSource, message );
		return false;
	}

	return row;
}
