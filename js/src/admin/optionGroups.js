/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Option groups for dropdown fields in the form builder.
 *
 * An "Option groups" checkbox turns grouping on for a field. Turning it on puts every option in one
 * group; "Add Group" starts another. Each group shows as a block with a heading that can be renamed
 * and removed. Options move between groups by dragging them.
 *
 * Each option row carries a hidden group input, so a group is simply the run of rows that share a
 * name. The heading lives inside the first row of its run. "## Group name" lines in Bulk Edit still
 * read and write the same data.
 *
 * @since x.x
 */

const BULK_EDIT_MARKER = '##';
const GROUP_INPUT_SELECTOR = ':scope > .frm-option-group-input';
const ENABLED_CLASS = 'frm-option-groups-on';

const initializedLists = new WeakSet();
const knownRows = new WeakSet();
const observers = new WeakMap();
const pendingNewGroup = new WeakMap();

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
 * @param {HTMLElement} list The option list.
 * @return {boolean} Whether grouping is on for the field.
 */
function isEnabled( list ) {
	return list.classList.contains( ENABLED_CLASS );
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
		if ( child.classList.contains( 'frm_option_template' ) || child.classList.contains( 'ui-sortable-helper' ) || child.classList.contains( 'ui-sortable-placeholder' ) ) {
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
 * @param {HTMLElement} list The option list.
 * @param {HTMLElement} row  A row in the list.
 * @return {{group: string, rows: HTMLElement[]}|undefined} The run the row belongs to.
 */
function getRunOf( list, row ) {
	return getRuns( list ).find( ( { rows } ) => rows.includes( row ) );
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
 * @param {HTMLElement[]} rows The rows in the group run.
 * @return {HTMLElement} The heading shown at the top of a group block.
 */
function buildGroupHeader( rows ) {
	const { div, tag, svg, setAttributes } = frmDom;
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
		child: svg( { href: '#frm_delete_icon' } ),
	} );
	setAttributes( removeButton, {
		type: 'button',
		title: __( 'Remove group. Its options move to the group above.', 'formidable' ),
		'aria-label': sprintf(
			/* translators: %s: Option group name. */
			__( 'Remove group %s', 'formidable' ),
			group
		),
	} );

	return div( {
		className: 'frm-option-group-header',
		children: [
			nameInput,
			removeButton,
		],
	} );
}

/**
 * Redraws the group blocks for a list.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function render( list ) {
	const enabled = isEnabled( list );
	list.querySelectorAll( ':scope > li > .frm-option-group-header' ).forEach( header => header.remove() );

	const runs = getRuns( list );
	const groupedRuns = runs.filter( run => '' !== run.group );
	list.classList.toggle( 'frm-option-groups-single', groupedRuns.length < 2 );

	runs.forEach( run => {
		const { group, rows } = run;
		const inGroup = enabled && '' !== group;

		rows.forEach( ( row, index ) => {
			row.classList.toggle( 'frm-option-in-group', inGroup );
			row.classList.toggle( 'frm-option-group-first', inGroup && 0 === index );
			row.classList.toggle( 'frm-option-group-last', inGroup && rows.length - 1 === index );
		} );

		if ( inGroup ) {
			rows[ 0 ].append( buildGroupHeader( rows ) );
		}
	} );

	syncControls( list );
}

/**
 * Keeps the checkbox and the "Add Group" link in step with the list.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function syncControls( list ) {
	const { checkbox, addLink } = getControls( list );
	checkbox.checked = isEnabled( list );
	addLink.parentElement.hidden = ! isEnabled( list );
}

/**
 * The "Option groups" checkbox and the "Add Group" link. They sit with the field's other option
 * settings: the checkbox beside "Use separate values", the link beside 'Add "Other"'.
 *
 * @param {HTMLElement} list The option list.
 * @return {{checkbox: HTMLInputElement, addLink: HTMLElement}} The controls, created when missing.
 */
function getControls( list ) {
	const fieldId = getFieldId( list );
	const container = list.parentElement;
	let checkbox = document.getElementById( `frm-option-groups-enable-${ fieldId }` );
	let addLink = document.getElementById( `frm-option-groups-add-${ fieldId }` );

	if ( ! checkbox ) {
		const { tag, span, setAttributes } = frmDom;
		checkbox = tag( 'input', { id: `frm-option-groups-enable-${ fieldId }`, className: 'frm-option-groups-enable' } );
		checkbox.type = 'checkbox';
		const label = tag( 'label', {
			className: 'frm-h-stack-xs',
			children: [ checkbox, span( __( 'Option groups', 'formidable' ) ) ],
		} );
		setAttributes( label, { for: checkbox.id } );
		// Full width, and no top margin: the options area is a grid, so margins don't merge and the
		// setting above already leaves the gap.
		const setting = tag( 'p', { className: 'frm12 frm_form_field frm-mb-sm frm-option-groups-setting', child: label } );

		// Beside the other option settings, or right above the list without them.
		// The query returns the settings and the list in page order, so the ones before the list come first.
		const inOrder = Array.from( container.querySelectorAll( `:scope > p.frm_form_field, #${ list.id }` ) );
		const siblings = inOrder.slice( 0, inOrder.indexOf( list ) );
		const anchor = siblings.find( p => p.classList.contains( 'frm_sep_val_select' ) ) ?? siblings[ siblings.length - 1 ];
		if ( anchor ) {
			anchor.after( setting );
		} else {
			container.insertBefore( setting, container.firstChild );
		}
	}

	if ( ! addLink ) {
		const { tag, svg } = frmDom;
		addLink = tag( 'a', {
			id: `frm-option-groups-add-${ fieldId }`,
			className: 'frm-small-add frm-option-groups-add',
			children: [ svg( { href: '#frm_plus1_icon', classList: [ 'frm_add_tag', 'frm_svg13' ] } ), __( 'Add Group', 'formidable' ) ],
		} );
		addLink.href = '#';
		addLink.setAttribute( 'role', 'button' );

		// Its own row right under the groups. The 'Add "Other"' row shares its width with "Generate with AI".
		list.after( frmDom.div( { className: 'frm-option-groups-add-row frm12 frm_form_field frm-flex frm-justify-end', child: addLink } ) );
	}

	return { checkbox, addLink };
}

/**
 * @param {HTMLElement} list The option list.
 * @return {string} "Group N", unused in this field.
 */
function nextGroupName( list ) {
	const names = new Set( getGroupNames( list ) );
	let number = names.size + 1;
	/* translators: %d: Group number. */
	const name = n => sprintf( __( 'Group %d', 'formidable' ), n );
	while ( names.has( name( number ) ) ) {
		number++;
	}
	return name( number );
}

/**
 * Turns grouping on or off. On puts every option in one group; off removes every group.
 *
 * @param {HTMLElement} list    The option list.
 * @param {boolean}     enabled Whether grouping should be on.
 * @return {void}
 */
function setEnabled( list, enabled ) {
	list.classList.toggle( ENABLED_CLASS, enabled );
	const rows = getRows( list );

	if ( enabled ) {
		if ( ! rows.some( row => '' !== getGroup( row ) ) ) {
			const name = nextGroupName( list );
			// Leave a blank first option, like an empty "choose" entry, above the groups.
			const firstLabeled = rows.findIndex( row => '' !== ( row.querySelector( 'input[name$="[label]"]' )?.value.trim() ?? '' ) );
			rows.slice( Math.max( firstLabeled, 0 ) ).forEach( row => setGroup( row, name ) );
		}
	} else {
		rows.forEach( row => setGroup( row, '' ) );
	}

	render( list );
	optionsChanged( list );
}

/**
 * Starts a new group at the end with one empty option, and puts the cursor in its name.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function addGroup( list ) {
	const rows = getRows( list );
	const last = rows[ rows.length - 1 ];
	const add = last?.querySelector( '.frm_add_opt' );
	if ( ! add ) {
		return;
	}

	const name = nextGroupName( list );
	pendingNewGroup.set( list, name );

	// The builder adds the option right after this row; the list observer then gives it the new group.
	add.click();

	window.setTimeout( () => {
		const input = Array.from( list.querySelectorAll( '.frm-option-group-name' ) ).find( el => el.value === name );
		input?.focus();
		input?.select();
	}, 0 );
}

/**
 * Removes a group. Its options join the group above, or the one below for the first group.
 *
 * @param {HTMLElement} list The option list.
 * @param {HTMLElement} row  A row in the group.
 * @return {void}
 */
function removeGroup( list, row ) {
	const runs = getRuns( list ).filter( run => '' !== run.group );
	const index = runs.findIndex( run => run.rows.includes( row ) );
	if ( index < 0 || runs.length < 2 ) {
		return;
	}

	const target = runs[ index - 1 ] ?? runs[ index + 1 ];
	const { rows } = runs[ index ];
	rows.forEach( groupRow => setGroup( groupRow, target.group ) );

	if ( index > 0 ) {
		target.rows[ target.rows.length - 1 ].after( ...rows );
	} else {
		target.rows[ 0 ].before( ...rows );
	}

	ignoreOwnMoves( list );
	render( list );
	optionsChanged( list );
	target.rows[ 0 ].querySelector( '.frm-option-group-name' )?.focus();
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

	const run = getRunOf( list, nameInput.closest( 'li' ) );
	run?.rows.forEach( row => setGroup( row, newName ) );

	nameInput.dataset.group = newName;

	if ( isFinal ) {
		render( list );
		optionsChanged( list );
	}
}

/**
 * Removing a group moves its options next to the group they join, with their new group already set,
 * so the list observer must not treat that like a single option being dragged next to another group.
 * Dropping its pending records does that.
 *
 * @param {HTMLElement} list The option list.
 * @return {void}
 */
function ignoreOwnMoves( list ) {
	observers.get( list )?.takeRecords();
}

/**
 * Gives new and moved options a sensible group after the list changes.
 *
 * An option added with a row's "+" button joins that row's group, or the group "Add Group" is
 * starting. An option dragged between two options that agree takes their group. Otherwise it keeps
 * its group when it is still next to it, or joins the group above it.
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
				if ( pendingNewGroup.has( list ) ) {
					group = pendingNewGroup.get( list );
					pendingNewGroup.delete( list );
				} else {
					group = '' === group ? previousGroup : group;
				}
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
	list.classList.toggle( ENABLED_CLASS, getRows( list ).some( row => '' !== getGroup( row ) ) );
	getControls( list );
	render( list );

	// Mutation callbacks already arrive batched, once per task, so the list can be redrawn right away.
	// Rendering only changes the rows' contents, which this observer does not watch.
	const observer = new MutationObserver( records => {
		// Wait until jQuery UI finishes a drag, and ignore the drop line of a group drag.
		if ( list.querySelector( ':scope > .ui-sortable-placeholder, :scope > .frm-option-group-drop' ) ) {
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
 * @param {string}      prefix        Text to add before the option line. It is kept as is, so it should already end with `\r\n` when it holds a whole line.
 * @param {Object}      args          Details about the option line.
 * @param {HTMLElement} args.optionEl The option's list item.
 * @return {string} The prefix, with a group line ending in `\r\n` when needed.
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
 * @param {HTMLElement} target A clicked or focused element.
 * @return {HTMLElement|null} The option list it belongs to, including its checkbox and "Add Group" link.
 */
function findList( target ) {
	const inList = target.closest( '.frm_sortable_field_opts' );
	if ( inList ) {
		return inList;
	}

	const control = target.closest( '.frm-option-groups-enable, .frm-option-groups-add' );
	if ( ! control ) {
		return null;
	}

	const fieldId = control.id.replace( /^frm-option-groups-(enable|add)-/, '' );
	return document.getElementById( `frm_field_${ fieldId }_opts` );
}

/**
 * A group's options are separate rows, so hovering any of them marks the group's first row, which
 * holds the heading. That shows the name field and the remove button.
 *
 * @param {MouseEvent} event The mouseover event.
 * @return {void}
 */
function markHoveredGroup( event ) {
	const row = event.target.closest( '.frm-option-in-group' );
	const list = row?.parentElement;
	const first = row ? getRunOf( list, row )?.rows[ 0 ] : null;

	document.querySelectorAll( '.frm-option-group-hover' ).forEach( el => {
		if ( el !== first ) {
			el.classList.remove( 'frm-option-group-hover' );
		}
	} );
	first?.classList.add( 'frm-option-group-hover' );
}

/**
 * @param {Event} event The click event.
 * @return {void}
 */
function handleClick( event ) {
	const { target } = event;
	const list = findList( target );

	if ( ! list || ! initializedLists.has( list ) ) {
		return;
	}

	const row = target.closest( 'li' );

	if ( target.closest( '.frm-option-groups-add' ) ) {
		event.preventDefault();
		addGroup( list );
	} else if ( target.closest( '.frm-option-group-remove' ) ) {
		removeGroup( list, row );
	}
}

/**
 * @param {KeyboardEvent} event The keydown event.
 * @return {void}
 */
function handleKeydown( event ) {
	const { target } = event;

	if ( 'Enter' === event.key && target.matches( '.frm-option-group-name' ) ) {
		// Don't submit the builder form.
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
	builder.addEventListener( 'mouseover', markHoveredGroup );
	builder.addEventListener( 'mouseleave', markHoveredGroup );
	builder.addEventListener( 'input', event => {
		if ( event.target.matches( '.frm-option-group-name' ) ) {
			renameGroupFromHeader( event.target, false );
		}
	} );
	builder.addEventListener( 'change', event => {
		if ( event.target.matches( '.frm-option-group-name' ) ) {
			renameGroupFromHeader( event.target, true );
		} else if ( event.target.matches( '.frm-option-groups-enable' ) ) {
			// Turning groups on or off is the edit; the builder doesn't need to see the checkbox itself.
			event.stopPropagation();
			const list = findList( event.target );
			if ( list && initializedLists.has( list ) ) {
				setEnabled( list, event.target.checked );
			}
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
	list.classList.toggle( ENABLED_CLASS, getRows( list ).some( row => '' !== getGroup( row ) ) );
	render( list );
}
