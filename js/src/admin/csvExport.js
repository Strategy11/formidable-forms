import domReady from '@wordpress/dom-ready';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Initializes the entries CSV column picker.
 *
 * @since x.x
 */
domReady( () => {
	const modal = document.getElementById( 'frm-csv-export' );
	if ( ! modal || ! modal.showModal ) {
		return;
	}

	const list = modal.querySelector( '.frm-csv-columns' );
	const status = modal.querySelector( '.frm-csv-status' );
	const remember = modal.querySelector( '.js-frm-csv-remember' );
	const download = modal.querySelector( '.js-frm-csv-download' );
	let exportURL;
	let columns = [];
	let draggedRow;

	/**
	 * Sends a column picker request for the selected form.
	 *
	 * @param {string} action   The AJAX action suffix.
	 * @param {Array}  selected Column keys to save.
	 * @return {Promise} The AJAX response.
	 */
	function request( action, selected = [] ) {
		const data = new FormData();
		data.append( 'form', exportURL.searchParams.get( 'form' ) );
		selected.forEach( ( column, index ) => data.set( `columns[${ index }]`, column ) );
		return frmDom.ajax.doJsonPost( action, data );
	}

	/**
	 * Updates selection and movement controls after reordering.
	 */
	function updateControls() {
		const rows = Array.from( list.children );
		rows.forEach( ( row, index ) => {
			row.querySelector( '.js-frm-csv-up' ).disabled = index === 0;
			row.querySelector( '.js-frm-csv-down' ).disabled = index === rows.length - 1;
		} );
		download.disabled = ! list.querySelector( 'input:checked' );
	}

	/**
	 * Renders the columns in their selected order.
	 *
	 * @param {Array} selected Column keys in export order.
	 */
	function renderColumns( selected ) {
		const byKey = new Map( columns.map( column => [ column.value, column ] ) );
		const keys = [ ...new Set( [ ...selected, ...byKey.keys() ] ) ];
		const selectedKeys = new Set( selected );
		const fragment = document.createDocumentFragment();
		const rows = keys.map( key => {
			const column = byKey.get( key );
			if ( ! column ) {
				return null;
			}
			const checkbox = frmDom.tag( 'input' );
			checkbox.type = 'checkbox';
			checkbox.value = key;
			checkbox.checked = selectedKeys.has( key );
			const label = frmDom.tag( 'label', { children: [ checkbox, column.label ] } );
			const row = frmDom.tag( 'li', { children: [ label ] } );
			row.draggable = true;
			const buttons = [ 'up', 'down' ].map( direction => {
				const button = frmDom.tag( 'button', {
					className: `button js-frm-csv-${ direction }`,
					text: direction === 'up' ? '↑' : '↓',
				} );
				button.type = 'button';
				button.setAttribute( 'aria-label', direction === 'up'
					? sprintf( __( 'Move %s up', 'formidable' ), column.label )
					: sprintf( __( 'Move %s down', 'formidable' ), column.label ) );
				button.addEventListener( 'click', () => {
					const sibling = direction === 'up' ? row.previousElementSibling : row.nextElementSibling;
					if ( ! sibling ) {
						return;
					}
					if ( direction === 'up' ) {
						sibling.before( row );
					} else {
						sibling.after( row );
					}
					status.textContent = sprintf( __( 'Moved %s.', 'formidable' ), column.label );
					updateControls();
					if ( button.disabled ) {
						row.querySelector( 'input' ).focus();
					} else {
						button.focus();
					}
				} );
				return button;
			} );
			row.replaceChildren( label, ...buttons );
			return row;
		} );
		fragment.replaceChildren( ...rows.filter( Boolean ) );
		list.replaceChildren( fragment );
		updateControls();
	}

	document.addEventListener( 'click', async event => {
		const link = event.target.closest( 'a[href*="action=frm_entries_csv"]' );
		if ( ! link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ) {
			return;
		}
		event.preventDefault();
		exportURL = new URL( link.href, window.location.href );
		list.replaceChildren();
		status.textContent = __( 'Loading export columns…', 'formidable' );
		download.disabled = true;
		modal.showModal();
		let response;
		try {
			response = await request( 'csv_export_options' );
		} catch ( error ) {
			status.textContent = __( 'Unable to load export columns. Close this window and try again.', 'formidable' );
			return;
		}
		( { columns } = response );
		remember.checked = response.saved;
		const custom = exportURL.searchParams.get( 'columns' );
		renderColumns( custom === null ? response.selected : custom.split( ',' ) );
		status.textContent = '';
	} );

	list.addEventListener( 'change', updateControls );
	list.addEventListener( 'dragstart', event => {
		draggedRow = event.target.closest( 'li' );
		event.dataTransfer.setData( 'text/plain', draggedRow.querySelector( 'input' ).value );
	} );
	list.addEventListener( 'dragover', event => event.preventDefault() );
	list.addEventListener( 'dragend', () => {
		draggedRow = null;
	} );
	list.addEventListener( 'drop', event => {
		event.preventDefault();
		const target = event.target.closest( 'li' );
		if ( ! target || ! draggedRow || target === draggedRow ) {
			return;
		}
		const middle = target.getBoundingClientRect().top + ( target.offsetHeight / 2 );
		if ( event.clientY < middle ) {
			target.before( draggedRow );
		} else {
			target.after( draggedRow );
		}
		updateControls();
	} );
	modal.querySelector( '.js-frm-csv-all' ).addEventListener( 'click', () => {
		list.querySelectorAll( 'input' ).forEach( input => {
			input.checked = true;
		} );
		updateControls();
	} );
	modal.querySelector( '.js-frm-csv-none' ).addEventListener( 'click', () => {
		list.querySelectorAll( 'input' ).forEach( input => {
			input.checked = false;
		} );
		updateControls();
	} );
	modal.querySelector( '.js-frm-csv-reset' ).addEventListener( 'click', () => {
		renderColumns( columns.map( column => column.value ) );
	} );
	download.addEventListener( 'click', async () => {
		const selected = Array.from( list.querySelectorAll( 'input:checked' ), input => input.value );
		if ( ! selected.length ) {
			return;
		}
		if ( remember.checked ) {
			download.disabled = true;
			try {
				await request( 'csv_export_preferences', selected );
			} catch ( error ) {
				status.textContent = __( 'Unable to save export preferences. Try again or turn off Remember.', 'formidable' );
				download.disabled = false;
				return;
			}
		}
		exportURL.searchParams.set( 'columns', selected.join( ',' ) );
		modal.close();
		window.location.assign( exportURL.href );
	} );
} );
