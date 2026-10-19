/**
 * Draw simple field previews in the form builder from data printed with the page.
 *
 * PHP prints an empty list item for each of these fields, plus the few values its preview
 * shows. The markup matches classes/views/frm-forms/add_field.php, and the settings panels are
 * requested separately by fieldSettingsLoader.js.
 *
 * @since x.x
 */

import { addFieldSettingsMetadata } from './deferredFieldSettings';

const MANIFEST_ID = 'frm-field-previews';
const TEXT_INPUT_TYPES = new Set( [ 'text', 'email', 'url', 'number', 'phone' ] );

/**
 * Marks a list item whose preview has not been drawn yet.
 */
export const PENDING_PREVIEW_CLASS = 'frm_field_preview_pending';

/**
 * Target time spent drawing previews before the browser gets a frame to paint and handle input.
 */
const RENDER_BUDGET = 4;

let pendingPreviews = [];
let template;
let strings;

/**
 * Turn each preview placeholder into an empty field list item, before the builder scans its grid.
 *
 * The list items get their ids, classes and saved order and layout values, which is all the
 * builder and add-ons need to find the fields. The previews themselves are drawn later by
 * renderFieldPreviews(), so the page does not lay out every field at once.
 *
 * @since x.x
 * @return {string[]} The ids of the fields, in page order.
 */
export function hydrateFieldPreviews() {
	const manifestElement = document.getElementById( MANIFEST_ID );
	const fieldsContainer = document.getElementById( 'frm-show-fields' );
	if ( ! manifestElement || ! fieldsContainer ) {
		return [];
	}

	const manifest = JSON.parse( manifestElement.textContent );
	( { strings } = manifest );
	template = createFieldTemplate( strings );

	fieldsContainer.querySelectorAll( 'li[data-frm-preview]' ).forEach( placeholder => {
		const data = manifest.fields[ placeholder.dataset.frmPreview ];
		const [ className, formId, type, ftype ] = manifest.definitions[ data.definition ];
		Object.assign( data, { className, formId, type, ftype } );

		const fieldId = String( data.id );
		placeholder.id = `frm_field_id_${ fieldId }`;
		placeholder.className = `${ className } ${ PENDING_PREVIEW_CLASS }`;
		placeholder.dataset.fid = fieldId;
		placeholder.dataset.formid = formId;
		placeholder.dataset.ftype = ftype;
		placeholder.dataset.type = type;
		delete placeholder.dataset.frmPreview;

		// Layout and order changes made before the settings arrive are kept here.
		addFieldSettingsMetadata( placeholder, fieldId, getSettingsMetadata( data ) );
		pendingPreviews.push( { placeholder, data } );
	} );
	manifestElement.remove();

	return pendingPreviews.map( ( { data } ) => String( data.id ) );
}

/**
 * Draw the hydrated previews in short slices, giving the browser a frame between slices.
 *
 * @since x.x
 * @param {Function} onRendered Called with the old and new list item of each preview drawn.
 * @param {Function} onSlice    Called with what onRendered returned for each preview in a slice.
 * @return {Promise<void>} Resolves once every preview has been drawn.
 */
export async function renderFieldPreviews( onRendered, onSlice ) {
	const previews = pendingPreviews;
	pendingPreviews = [];

	let index = 0;
	while ( index < previews.length ) {
		// Wait for a frame first, so the page paints before the first slice too.
		await waitForNextFrame();

		const sliceStarted = performance.now();
		const slice = [];
		while ( index < previews.length && performance.now() - sliceStarted < RENDER_BUDGET ) {
			const { placeholder, data } = previews[ index++ ];
			if ( ! placeholder.isConnected ) {
				continue;
			}
			const field = createField( template, data, strings );
			// Keep the order input object, which drag and drop code caches, and any settings
			// that were needed before the preview was drawn.
			// eslint-disable-next-line formidable/prefer-document-fragment -- the field is not in the page yet
			field.append( ...placeholder.querySelectorAll( ':scope > .frm-deferred-settings-meta, :scope > .frm-single-settings' ) );
			const rendered = onRendered( placeholder, field );
			placeholder.replaceWith( field );
			slice.push( rendered );
		}
		onSlice( slice );
	}
}

/**
 * @return {Promise<void>} Resolves after the next frame, in a new task so input can run first.
 */
function waitForNextFrame() {
	return new Promise( resolve => requestAnimationFrame( () => setTimeout( resolve, 0 ) ) );
}

/**
 * @param {Object} data The values printed for a field.
 * @return {Object} The saved settings that layout and order code read before the settings load.
 */
function getSettingsMetadata( data ) {
	return {
		// The name is only printed when it differs from the sanitized label.
		name: data.name ?? data.label ?? '',
		type: data.type,
		key: data.key,
		order: data.order,
		classes: data.classes,
		align: data.align
	};
}

/**
 * Build the parts every preview shares once, so each field is a clone of it.
 *
 * @param {Object} strings Translated text and the dropdown alignment class.
 * @return {HTMLElement} The field list item without any field values.
 */
function createFieldTemplate( strings ) {
	const { div, span, tag, a, svg, setAttributes } = frmDom;

	const moveLink = a( {
		className: 'frm_bstooltip frm-move frm-hover-icon',
		child: svg( { href: '#frm_thick_move_icon' } )
	} );
	setAttributes( moveLink, {
		href: '#',
		title: strings.moveField,
		'data-container': 'body',
		'aria-label': strings.moveField
	} );

	const dropdownToggle = a( {
		className: 'frm_bstooltip frm-hover-icon frm-dropdown-toggle dropdown-toggle',
		children: [
			svg( { href: '#frm_thick_more_vert_icon' } ),
			' ',
			span( { className: 'screen-reader-text', text: strings.toggleOptions } )
		]
	} );
	setAttributes( dropdownToggle, {
		href: '#',
		title: strings.moreOptions,
		'data-toggle': 'dropdown',
		'data-container': 'body',
		'data-trigger': 'hover',
		'aria-expanded': 'false',
		'data-bs-display': 'static'
	} );

	const dropdownMenu = tag( 'ul', { className: `frm-dropdown-menu frm-p-1 ${ strings.dropdownAlign }` } );
	dropdownMenu.setAttribute( 'role', 'menu' );

	const actionIcons = div( {
		className: 'frm-field-action-icons frm-show-hover',
		children: [
			div( { className: 'frm-sub-label frm-field-id' } ),
			' ',
			moveLink,
			' ',
			div( { className: 'dropdown', children: [ dropdownToggle, ' ', dropdownMenu ] } )
		]
	} );

	const label = tag( 'label', {
		className: 'frm_primary_label',
		children: [
			span( { className: 'frm-label-text' } ),
			' ',
			span( { className: 'frm_required' } ),
			' ',
			span( { className: 'frm-sub-label frm-collapsed-label', text: strings.collapsed } )
		]
	} );

	const inputs = div( {
		className: 'frm_form_fields frm_opt_container',
		child: div( { className: 'clear' } )
	} );

	return tag( 'li', {
		child: div( {
			className: 'frm_inner_field_container',
			children: [ actionIcons, ' ', label, ' ', inputs ]
		} )
	} );
}

/**
 * @param {HTMLElement} template The shared field markup.
 * @param {Object}      data     The values printed for this field.
 * @param {Object}      strings  Translated text shared by every preview.
 * @return {HTMLElement} The field list item.
 */
function createField( template, data, strings ) {
	const { id, type, ftype } = data;
	const field = template.cloneNode( true );
	field.id = `frm_field_id_${ id }`;
	field.className = data.className;
	field.dataset.fid = id;
	field.dataset.formid = data.formId;
	field.dataset.ftype = ftype;
	field.dataset.type = type;
	frmDom.setAttributes( field, data.attributes || {} );

	const container = field.firstElementChild;
	container.id = `field_${ id }_inner_container`;
	container.querySelector( '.frm-field-id' ).textContent = `(ID ${ id })`;

	const label = container.querySelector( '.frm_primary_label' );
	label.id = `field_label_${ id }`;
	const labelText = label.firstElementChild;
	labelText.insertAdjacentHTML( 'beforebegin', data.label || '' );
	labelText.remove();

	const required = label.querySelector( '.frm_required' );
	required.textContent = data.indicator ?? '*';
	if ( ! data.required ) {
		required.classList.add( 'frm_hidden' );
	}
	if ( data.afterLabel ) {
		label.insertAdjacentHTML( 'beforeend', data.afterLabel );
	}

	const inputs = container.lastElementChild;
	inputs.dataset.ftype = ftype;
	inputs.prepend( ...createInputs( data, strings ) );

	if ( data.description !== undefined ) {
		const description = frmDom.div( { id: `field_description_${ id }`, className: 'frm_description' } );
		description.innerHTML = data.description;
		container.append( description );
	}

	if ( data.before ) {
		field.insertAdjacentHTML( 'afterbegin', data.before );
	}
	if ( data.after ) {
		field.insertAdjacentHTML( 'beforeend', data.after );
	}

	return field;
}

/**
 * Create the inputs shown for a field type, matching what the field class renders in PHP.
 *
 * @param {Object} data    The values printed for this field.
 * @param {Object} strings Translated text shared by every preview.
 * @return {Array<HTMLElement|string>} The field inputs.
 */
function createInputs( data, strings ) {
	const { id, type } = data;
	const name = `item_meta[${ id }]`;

	if ( 'html' === type ) {
		const message = frmDom.div( { className: 'frm-embed-message' } );
		message.innerHTML = `${ strings.customHtml } ${ data.html || '' }`;
		return [ frmDom.div( { className: 'frm-embed-field-placeholder', child: message } ), ' ' ];
	}

	if ( 'hidden' === type ) {
		const input = createInput( { type: 'text', id: data.htmlId, name, value: data.value || '', class: 'dyn_default_value' } );
		const note = frmDom.tag( 'p', { className: 'howto frm_clear', text: strings.hiddenNote } );
		return [ input, ' ', note, ' ' ];
	}

	if ( 'textarea' === type ) {
		const textarea = frmDom.tag( 'textarea' );
		frmDom.setAttributes( textarea, {
			name,
			id: data.htmlId,
			rows: data.rows || '',
			class: 'dyn_default_value',
			placeholder: data.placeholder || ''
		} );
		if ( data.width ) {
			textarea.setAttribute( 'style', `width:${ data.width };` );
		}
		// The text was escaped for a textarea in PHP, which parses entities but not tags.
		textarea.innerHTML = ( data.text || '' ).replaceAll( '\0', '\uFFFD' );
		// Parsing a complete textarea element discards its first newline.
		if ( textarea.textContent.startsWith( '\n' ) ) {
			textarea.textContent = textarea.textContent.slice( 1 );
		}
		return [ textarea, ' ' ];
	}

	if ( TEXT_INPUT_TYPES.has( type ) ) {
		const attributes = { type: 'text', name, id: data.htmlId, value: data.value || '', placeholder: data.placeholder || '' };
		if ( data.readOnly ) {
			attributes.readonly = 'readonly';
			attributes.disabled = 'disabled';
		}
		return [ createInput( attributes ), ' ' ];
	}

	return wp.hooks.applyFilters( 'frm_builder_preview_inputs', [], data );
}

/**
 * @param {Object} attributes Input attributes.
 * @return {HTMLElement} The input.
 */
function createInput( attributes ) {
	const input = frmDom.tag( 'input' );
	frmDom.setAttributes( input, attributes );
	return input;
}
