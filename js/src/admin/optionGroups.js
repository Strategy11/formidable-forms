/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Option groups for dropdown fields in the form builder.
 *
 * Each option row carries a hidden group input. Rows that share a group are kept next to each
 * other and shown under a heading that can be renamed or removed. Several options can be put
 * into a group at once from the selection bar, or with "## Group name" lines in Bulk Edit.
 *
 * @since x.x
 */

const BULK_EDIT_MARKER = '##';
const GROUP_INPUT_SELECTOR = ':scope > .frm-option-group-input';
const SELECTING_CLASS = 'frm-option-groups-selecting';

const initializedLists = new WeakSet();
const knownRows = new WeakSet();
const observers = new WeakMap();
const lastCheckedRows = new WeakMap();

/**
 * @param {HTMLElement} row An option list item.
 * @return {HTMLInputElement|null} The hidden group input, when the row supports groups.
 */
function getGroupInput( row ) {
	return row.querySelector( GROUP_INPUT_SELECTOR );
}

/**
 * @param {HTMLElement} row An option list item.
 * @return {string} The group name, or an empty string.
 */
function getGroup( row ) {
	return getGroupInput( row )?.value.trim() ?? '';
}

/**
 * @param {HTMLElement} row  An option list item.
 * @param {string}      name The new group name. Empty removes the option from its group.
 * @return {void}
 */
function setGroup( row, name ) {
	const input = getGroupInput( row );
	if ( input ) {
		input.value = name;
	}
}

/**
 * @param {HTMLElement} list The option list.
 * @return {HTMLElement[]} Every real option row, skipping the hidden template and drag clones.
 */
function getRows( list ) {
	return Array.from( list.children ).filter(
		row => row.matches( '.frm_single_option:not(.frm_option_template):not(.ui-sortable-helper)' ) && null !== getGroupInput( row )
	);
}

/**
 * @param {HTMLElement} list The option list.
 * @return {boolean} True when the list belongs to a field that supports groups.
 */
function supportsGroups( list ) {
	return null !== list.querySelector( '.frm-option-group-input' );
}

/**
 * @param {HTMLElement} list The option list.
 * @return {string} The field id.
 */
function getFieldId( list ) {
	return list.id.replace( 'frm_field_', '' ).replace( '_opts', '' );
}

/**
 * @param {HTMLElement} list The option list.
 * @return {string[]} Unique group names in the order they appear.
 */
function getGroupNames( list ) {
	return [ ...new Set( getRows( list ).map( getGroup ).filter( Boolean ) ) ];
}

/**
 * Splits the rows into runs of consecutive options that share a group.
 *
 * @param {HTMLElement} list The option list.
 * @return {Array<{group: string, rows: HTMLElement[]}>} The runs in order.
 */
function getRuns( list ) {
	const runs = [];
	let current = null;

	Array.from( list.children ).forEach( child => {
		if ( child.classList.contains( 'frm_option_template' ) || child.classList.contains( 'ui-sortable-helper' ) ) {
			return;
		}

		// Rows without a group input, like "Other", always end a group.
		const group = getGroupInput( child ) ? getGroup( child ) : '';

		if ( ! current || current.group !== group ) {
			current = { group, rows: [] };
			runs.push( current );
		}
		current.rows.push( child );
	} );

	return runs;
}

/**
 * Lets the rest of the builder know the options changed. A change event on an option input makes
 * the builder mark the form as edited and redraw the field preview in the new order.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function optionsChanged( list ) {
	const [ firstRow ] = getRows( list );

	if ( firstRow ) {
		getGroupInput( firstRow ).dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	frmAdminBuild.adjustConditionalLogicOptionOrders( getFieldId( list ) );
}

/**
 * @param {HTMLElement}   list The option list.
 * @param {HTMLElement[]} rows The rows in the group run.
 * @return {HTMLElement} The heading shown above the first option of a group.
 */
function buildGroupHeader( list, rows ) {
	const { div, span, tag, svg, setAttributes } = frmDom;
	const group = getGroup( rows[ 0 ] );

	const nameInput = tag( 'input', {
		className: 'frm-option-group-name',
		data: { group },
	} );
	setAttributes( nameInput, {
		type: 'text',
		'aria-label': __( 'Group name', 'formidable' ),
	} );
	nameInput.value = group;

	const removeButton = tag( 'button', {
		className: 'frm-option-group-remove',
		child: svg( { href: '#frm_close_icon' } ),
	} );
	setAttributes( removeButton, {
		type: 'button',
		title: __( 'Ungroup these options', 'formidable' ),
		'aria-label': sprintf(
			/* translators: %s: Option group name. */
			__( 'Ungroup %s', 'formidable' ),
			group
		),
	} );

	return div( {
		className: 'frm-option-group-header',
		children: [
			svg( { href: '#frm_folder_icon' } ),
			nameInput,
			span( {
				className: 'frm-option-group-count',
				text: sprintf(
					/* translators: %d: Number of options in the group. */
					_n( '%d option', '%d options', rows.length, 'formidable' ),
					rows.length
				),
			} ),
			removeButton,
		],
	} );
}

/**
 * Adds or removes the selection checkbox on a row.
 *
 * @param {HTMLElement} row       An option list item.
 * @param {boolean}     selecting Whether the list is in selection mode.
 * @return {void}
 */
function syncRowCheckbox( row, selecting ) {
	let checkbox = row.querySelector( ':scope > .frm-option-group-check' );

	if ( ! selecting ) {
		checkbox?.remove();
		return;
	}

	if ( checkbox ) {
		return;
	}

	const label = row.querySelector( 'input[name$="[label]"]' )?.value ?? '';
	checkbox = frmDom.tag( 'input', { className: 'frm-option-group-check' } );
	frmDom.setAttributes( checkbox, {
		type: 'checkbox',
		'aria-label': sprintf(
			/* translators: %s: Option label. */
			__( 'Select %s', 'formidable' ),
			label
		),
	} );

	const dragIcon = row.querySelector( ':scope > .frm-drag' );
	if ( dragIcon ) {
		dragIcon.after( checkbox );
	} else {
		row.prepend( checkbox );
	}
}

/**
 * Redraws the group headings and row styling for a list.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function render( list ) {
	const selecting = list.classList.contains( SELECTING_CLASS );

	list.querySelectorAll( ':scope > li > .frm-option-group-header' ).forEach( header => header.remove() );

	getRuns( list ).forEach( ( { group, rows } ) => {
		rows.forEach( ( row, index ) => {
			row.classList.toggle( 'frm-option-in-group', '' !== group );
			row.classList.toggle( 'frm-option-group-first', '' !== group && 0 === index );
			row.classList.toggle( 'frm-option-group-last', '' !== group && rows.length - 1 === index );

			if ( getGroupInput( row ) ) {
				syncRowCheckbox( row, selecting );
			}
		} );

		if ( '' !== group ) {
			rows[ 0 ].append( buildGroupHeader( list, rows ) );
		}
	} );

	updateSelectionBar( list );
}

/**
 * Applies a group name to rows, keeping every group's options next to each other.
 *
 * @param {HTMLElement}   list The option list.
 * @param {HTMLElement[]} rows The rows to move, in list order.
 * @param {string}        name The group name. Empty removes the rows from their groups.
 * @return {void}
 */
function moveRowsToGroup( list, rows, name ) {
	if ( ! rows.length ) {
		return;
	}

	const moving = new Set( rows );
	let anchor = null;

	if ( '' === name ) {
		// Place ungrouped options right after the group they leave, so the group is not split.
		const lastRun = getRuns( list ).findLast( run => '' !== run.group && run.rows.some( row => moving.has( row ) ) );
		anchor = lastRun?.rows.findLast( row => ! moving.has( row ) ) ?? null;
	} else {
		anchor = getRows( list ).findLast( row => getGroup( row ) === name && ! moving.has( row ) ) ?? null;
	}

	rows.forEach( row => setGroup( row, name ) );

	if ( anchor ) {
		anchor.after( ...rows );
	} else if ( '' !== name ) {
		// A new group starts where the first selected option was.
		rows[ 0 ].after( ...rows.slice( 1 ) );
	}

	render( list );
	optionsChanged( list );
}

/**
 * @param {HTMLElement} list The option list.
 * @return {HTMLElement[]} The selected rows in list order.
 */
function getSelectedRows( list ) {
	return getRows( list ).filter( row => row.querySelector( ':scope > .frm-option-group-check' )?.checked );
}

/**
 * @param {HTMLElement} list      The option list.
 * @param {boolean}     selecting Whether to turn selection mode on.
 * @return {void}
 */
function setSelecting( list, selecting ) {
	list.classList.toggle( SELECTING_CLASS, selecting );
	lastCheckedRows.delete( list );
	render( list );

	const controls = getControls( list );
	const toggle = controls.querySelector( '.frm-option-groups-toggle' );
	toggle.hidden = selecting;
	controls.querySelector( '.frm-option-groups-bar' ).hidden = ! selecting;

	if ( selecting ) {
		getRows( list )[ 0 ]?.querySelector( '.frm-option-group-check' )?.focus();
	} else {
		toggle.focus();
	}
}

/**
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function updateSelectionBar( list ) {
	const controls = list.nextElementSibling?.classList.contains( 'frm-option-groups-controls' ) ? list.nextElementSibling : null;
	if ( ! controls ) {
		return;
	}

	const count = getSelectedRows( list ).length;
	controls.querySelector( '.frm-option-groups-count' ).textContent = sprintf(
		/* translators: %d: Number of selected options. */
		_n( '%d selected', '%d selected', count, 'formidable' ),
		count
	);
	controls.querySelectorAll( '.frm-option-groups-apply, .frm-option-groups-ungroup' ).forEach( button => {
		button.disabled = 0 === count;
	} );

	const datalist = controls.querySelector( 'datalist' );
	datalist.replaceChildren( ...getGroupNames( list ).map( name => frmDom.tag( 'option', { text: name } ) ) );
}

/**
 * Builds the "Group options" button and the selection bar shown below the option list.
 *
 * @param {HTMLElement} list The option list.
 * @return {HTMLElement} The controls container.
 */
function buildControls( list ) {
	const { div, span, tag, svg, setAttributes } = frmDom;
	const fieldId = getFieldId( list );
	const datalistId = `frm-option-groups-list-${ fieldId }`;

	const toggle = tag( 'button', {
		className: 'button frm-button-secondary frm-rounded-6 frm-font-normal frm-py-2xs frm-px-xs frm-option-groups-toggle',
		children: [
			svg( { href: '#frm_folder_icon' } ),
			span( __( 'Group options', 'formidable' ) ),
		],
	} );
	toggle.type = 'button';

	const nameInput = tag( 'input', { className: 'frm-option-groups-name' } );
	setAttributes( nameInput, {
		type: 'text',
		placeholder: __( 'New or existing group', 'formidable' ),
		'aria-label': __( 'Group name', 'formidable' ),
		list: datalistId,
	} );

	const makeButton = ( className, text ) => {
		const button = tag( 'button', { className, text } );
		button.type = 'button';
		return button;
	};

	const bar = div( {
		className: 'frm-option-groups-bar',
		children: [
			div( {
				className: 'frm-option-groups-bar-top',
				children: [
					span( { className: 'frm-option-groups-count' } ),
					makeButton( 'frm-option-groups-ungroup', __( 'Ungroup', 'formidable' ) ),
					makeButton( 'frm-option-groups-cancel', __( 'Done', 'formidable' ) ),
				],
			} ),
			div( {
				className: 'frm-option-groups-bar-form',
				children: [
					nameInput,
					tag( 'datalist', { id: datalistId } ),
					makeButton( 'button frm-button-primary frm-button-sm frm-option-groups-apply', __( 'Move to group', 'formidable' ) ),
				],
			} ),
			span( {
				className: 'frm-option-groups-tip',
				text: __( 'Tip: Shift-click to select a range of options.', 'formidable' ),
			} ),
		],
	} );
	bar.hidden = true;
	setAttributes( bar, {
		role: 'group',
		'aria-label': __( 'Group selected options', 'formidable' ),
	} );

	return div( {
		className: 'frm-option-groups-controls frm12',
		children: [ toggle, bar ],
	} );
}

/**
 * @param {HTMLElement} list The option list.
 * @return {HTMLElement} The controls container, created when missing.
 */
function getControls( list ) {
	if ( list.nextElementSibling?.classList.contains( 'frm-option-groups-controls' ) ) {
		return list.nextElementSibling;
	}

	const controls = buildControls( list );
	list.after( controls );
	return controls;
}

/**
 * Applies the typed group name to the selected options.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function applyGroupFromBar( list ) {
	const nameInput = getControls( list ).querySelector( '.frm-option-groups-name' );
	const name = nameInput.value.trim();

	if ( '' === name ) {
		nameInput.focus();
		return;
	}

	// Leave selection mode first. The builder preview treats a checked box in a row as the default.
	const rows = getSelectedRows( list );
	nameInput.value = '';
	setSelecting( list, false );
	moveRowsToGroup( list, rows, name );
}

/**
 * Selects every checkbox between the last clicked one and this one.
 *
 * @param {HTMLElement}      list     The option list.
 * @param {HTMLInputElement} checkbox The checkbox that was clicked.
 * @param {boolean}          isRange  Whether Shift was held.
 * @return {void}
 */
function handleCheckboxClick( list, checkbox, isRange ) {
	const row = checkbox.closest( 'li' );
	const lastRow = lastCheckedRows.get( list );

	if ( isRange && lastRow?.isConnected ) {
		const rows = getRows( list );
		const start = rows.indexOf( lastRow );
		const end = rows.indexOf( row );
		const [ from, to ] = start < end ? [ start, end ] : [ end, start ];

		rows.slice( from, to + 1 ).forEach( rangeRow => {
			const rangeCheckbox = rangeRow.querySelector( ':scope > .frm-option-group-check' );
			if ( rangeCheckbox ) {
				rangeCheckbox.checked = checkbox.checked;
			}
		} );
	}

	lastCheckedRows.set( list, row );
	updateSelectionBar( list );
}

/**
 * Renames every option in the group run that a heading belongs to.
 *
 * @param {HTMLInputElement} nameInput The heading's name input.
 * @param {boolean}          isFinal   True on change, when an empty name should be reverted.
 * @return {void}
 */
function renameGroupFromHeader( nameInput, isFinal ) {
	const list = nameInput.closest( '.frm_sortable_field_opts' );
	const oldName = nameInput.dataset.group;
	let newName = nameInput.value.trim();

	if ( isFinal && '' === newName ) {
		newName = oldName;
		nameInput.value = oldName;
	}

	if ( '' === newName ) {
		return;
	}

	const run = getRuns( list ).find( ( { rows } ) => rows.includes( nameInput.closest( 'li' ) ) );
	run?.rows.forEach( row => setGroup( row, newName ) );
	nameInput.dataset.group = newName;

	if ( isFinal ) {
		render( list );
		optionsChanged( list );
	}
}

/**
 * Gives new and moved options a sensible group after the list changes.
 *
 * An option added with a row's "+" button joins that row's group. An option dragged between two
 * options that agree, grouped or not, takes their group. Otherwise it keeps its group when it is
 * still next to it, or joins the group above it.
 *
 * @param {HTMLElement}      list    The option list.
 * @param {MutationRecord[]} records The list changes.
 * @return {boolean} True when a group was changed.
 */
function adoptGroupsForAddedRows( list, records ) {
	let changed = false;
	const rows = getRows( list );

	records.forEach( record => {
		record.addedNodes.forEach( node => {
			if ( 1 !== node.nodeType || ! rows.includes( node ) ) {
				return;
			}

			const index = rows.indexOf( node );
			const previousRow = rows[ index - 1 ];
			const nextRow = rows[ index + 1 ];
			const previousGroup = previousRow ? getGroup( previousRow ) : '';
			let group = getGroup( node );

			if ( ! knownRows.has( node ) ) {
				group = '' === group ? previousGroup : group;
			} else {
				// At either end of the list, the only neighbor decides.
				const above = previousRow ? previousGroup : getGroup( nextRow ?? node );
				const below = nextRow ? getGroup( nextRow ) : above;

				if ( above === below || ( group !== above && group !== below ) ) {
					group = above;
				}
			}

			if ( group !== getGroup( node ) ) {
				setGroup( node, group );
				changed = true;
			}

			knownRows.add( node );
		} );
	} );

	return changed;
}

/**
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function rememberRows( list ) {
	getRows( list ).forEach( row => knownRows.add( row ) );
}

/**
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function initList( list ) {
	if ( initializedLists.has( list ) || ! supportsGroups( list ) ) {
		return;
	}

	initializedLists.add( list );
	rememberRows( list );
	getControls( list );
	render( list );

	// Selecting options is not an edit, so keep it from marking the form as changed.
	list.addEventListener( 'change', event => {
		if ( event.target.matches( '.frm-option-group-check' ) ) {
			event.stopPropagation();
		}
	} );

	// Mutation callbacks already arrive batched, once per task, so the list can be redrawn right away.
	// Rendering only changes the rows' contents, which this observer does not watch.
	const observer = new MutationObserver( records => {
		// Wait until jQuery UI finishes a drag. It inserts placeholders and clones while dragging.
		if ( list.querySelector( ':scope > .ui-sortable-placeholder' ) ) {
			return;
		}

		const changed = adoptGroupsForAddedRows( list, records );
		render( list );
		if ( changed ) {
			optionsChanged( list );
		}
	} );
	observer.observe( list, { childList: true } );
	observers.set( list, observer );
}

/**
 * @param {HTMLElement} container An element that may contain option lists.
 * @return {void}
 */
export function initOptionGroupLists( container ) {
	container?.querySelectorAll( '.frm_sortable_field_opts' ).forEach( initList );
}

/**
 * Adds a "## Group name" line to the Bulk Edit Options text each time the group changes.
 *
 * @param {string}      prefix        Text to add before the option line.
 * @param {Object}      args          Details about the option line.
 * @param {HTMLElement} args.optionEl The option's list item.
 * @return {string} The prefix, with a group line when needed.
 */
export function addGroupLineToBulkEdit( prefix, { optionEl } ) {
	if ( ! optionEl || ! getGroupInput( optionEl ) ) {
		return prefix;
	}

	const rows = getRows( optionEl.parentElement );
	const index = rows.indexOf( optionEl );
	const group = getGroup( optionEl );
	const previousGroup = index > 0 ? getGroup( rows[ index - 1 ] ) : '';

	if ( group === previousGroup ) {
		return prefix;
	}

	const line = '' === group ? BULK_EDIT_MARKER : `${ BULK_EDIT_MARKER } ${ group }`;
	return `${ prefix }${ line }\r\n`;
}

/**
 * Shows how to write groups in Bulk Edit when the field being edited supports them.
 *
 * @param {Event} event The click event.
 * @return {void}
 */
function maybeShowBulkEditHint( event ) {
	const link = event.target.closest?.( 'a.frm-bulk-edit-link' );
	const textarea = document.getElementById( 'frm_bulk_options' );

	if ( ! link || ! textarea ) {
		return;
	}

	const fieldId = link.closest( '[data-fid]' )?.dataset.fid;
	const list = document.getElementById( `frm_field_${ fieldId }_opts` );
	let hint = document.getElementById( 'frm-option-groups-bulk-hint' );

	if ( ! hint ) {
		hint = frmDom.tag( 'p', {
			id: 'frm-option-groups-bulk-hint',
			className: 'frm-option-groups-bulk-hint',
			text: __( 'To group options, add a line like "## Fruits" before them. A line with only "##" ends the group.', 'formidable' ),
		} );
		textarea.after( hint );
	}

	hint.hidden = ! list || ! supportsGroups( list );
}

/**
 * @param {Event} event The click event.
 * @return {void}
 */
function handleClick( event ) {
	const { target } = event;
	const list = target.closest( '.frm_sortable_field_opts' ) ?? target.closest( '.frm-option-groups-controls' )?.previousElementSibling;

	if ( ! list || ! initializedLists.has( list ) ) {
		return;
	}

	if ( target.closest( '.frm-option-groups-toggle' ) ) {
		setSelecting( list, true );
	} else if ( target.closest( '.frm-option-groups-cancel' ) ) {
		setSelecting( list, false );
	} else if ( target.closest( '.frm-option-groups-apply' ) ) {
		applyGroupFromBar( list );
	} else if ( target.closest( '.frm-option-groups-ungroup' ) ) {
		const rows = getSelectedRows( list );
		setSelecting( list, false );
		moveRowsToGroup( list, rows, '' );
	} else if ( target.closest( '.frm-option-group-remove' ) ) {
		const run = getRuns( list ).find( ( { rows } ) => rows.includes( target.closest( 'li' ) ) );
		moveRowsToGroup( list, run?.rows ?? [], '' );
	} else if ( target.matches( '.frm-option-group-check' ) ) {
		handleCheckboxClick( list, target, event.shiftKey );
	}
}

/**
 * @param {KeyboardEvent} event The keydown event.
 * @return {void}
 */
function handleKeydown( event ) {
	if ( 'Enter' !== event.key ) {
		return;
	}

	const { target } = event;

	if ( target.matches( '.frm-option-groups-name' ) ) {
		// Don't submit the builder form.
		event.preventDefault();
		applyGroupFromBar( target.closest( '.frm-option-groups-controls' ).previousElementSibling );
	} else if ( target.matches( '.frm-option-group-name' ) ) {
		event.preventDefault();
		target.blur();
	}
}

/**
 * @since x.x
 *
 * @return {void}
 */
export function setupOptionGroups() {
	const builder = document.getElementById( 'frm_builder_page' );
	if ( ! builder ) {
		return;
	}

	// Capture, because the builder stops the click from bubbling once the modal opens.
	document.addEventListener( 'click', maybeShowBulkEditHint, true );

	builder.addEventListener( 'click', handleClick );
	builder.addEventListener( 'keydown', handleKeydown );
	builder.addEventListener( 'input', event => {
		if ( event.target.matches( '.frm-option-group-name' ) ) {
			renameGroupFromHeader( event.target, false );
		}
	} );
	builder.addEventListener( 'change', event => {
		if ( event.target.matches( '.frm-option-group-name' ) ) {
			renameGroupFromHeader( event.target, true );
		}
	} );
}

/**
 * Refreshes groups after Bulk Edit replaces the option rows.
 *
 * @since x.x
 *
 * @param {string} fieldId The field whose options were replaced.
 * @return {void}
 */
export function refreshOptionGroups( fieldId ) {
	const list = document.getElementById( `frm_field_${ fieldId }_opts` );
	if ( ! list ) {
		return;
	}

	observers.get( list )?.takeRecords();
	rememberRows( list );
	initList( list );
	render( list );
}
