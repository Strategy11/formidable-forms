const controlsSelector = 'input[name], select[name], textarea[name]';

/**
 * Clone native control state as well as markup without copying widget instances.
 *
 * @since x.x
 * @param {HTMLElement} element The source markup.
 * @return {HTMLElement} A detached copy with current values and selections.
 */
function cloneCurrentMarkup( element ) {
	const copy = element.cloneNode( true );
	const originals = [ element, ...element.querySelectorAll( '*' ) ].filter( node => node.matches( 'input, select, textarea' ) );
	const copies = [ copy, ...copy.querySelectorAll( '*' ) ].filter( node => node.matches( 'input, select, textarea' ) );
	originals.forEach( ( input, index ) => {
		const target = copies[ index ];
		if ( input.type !== 'file' ) {
			target.value = input.value;
		}
		target.checked = input.checked;
		if ( input.tagName === 'SELECT' ) {
			Array.from( input.options ).forEach( ( option, optionIndex ) => {
				target.options[ optionIndex ].selected = option.selected;
			} );
		}
	} );
	return copy;
}

/**
 * Capture unsaved settings at the time Duplicate is clicked.
 *
 * @since x.x
 * @param {HTMLElement} settings The materialized source settings.
 * @param {HTMLElement} field    The source builder field.
 * @return {Object} A detached snapshot of controls, choices, and static preview markup.
 */
export function captureFieldSettings( settings, field ) {
	const controls = Array.from( settings.querySelectorAll( controlsSelector ), input => ( {
		name: input.name,
		type: input.type,
		value: input.classList.contains( 'wp-editor-area' )
			? window.tinymce?.get( input.id )?.getContent() ?? input.value
			: input.value,
		checked: input.checked,
		disabled: input.disabled,
		select: input.tagName === 'SELECT' ? cloneCurrentMarkup( input ) : null,
		reference: input.dataset.changeme
	} ) );
	const choices = settings.querySelector( '.frm_sortable_field_opts' );
	const preview = field.querySelector( ':scope > .frm_inner_field_container > .frm_form_fields' );
	return {
		fieldId: settings.dataset.fid,
		fieldKey: settings.querySelector( `[name="field_options[field_key_${ settings.dataset.fid }]"]` )?.value,
		controls,
		choices: choices ? cloneCurrentMarkup( choices ) : null,
		preview: preview && ! preview.querySelector( 'iframe, .wp-editor-wrap' )
			? cloneCurrentMarkup( preview ) : null
	};
}

/**
 * Replace only the owner ID in a control name. Nested choice keys and values stay intact.
 *
 * @since x.x
 * @param {string} name   The source control name.
 * @param {string} fromId The source field ID.
 * @param {string} toId   The duplicate field ID.
 * @return {string} The duplicate control name.
 */
function copyControlName( name, fromId, toId ) {
	return name.replace( new RegExp( `(^|_|\\[)${ fromId }(?=\\]|\\[|$)` ), `$1${ toId }` );
}

/**
 * Remap markup identities without changing saved values or references to other fields.
 *
 * @since x.x
 * @param {HTMLElement} element    The detached markup to remap.
 * @param {string}      fromId     The source field ID.
 * @param {string}      toId       The duplicate field ID.
 * @param {Array}       references Pairs of source and duplicate preview IDs.
 * @return {void}
 */
function remapMarkup( element, fromId, toId, references ) {
	const identityAttributes = new Set( [
		'id', 'for', 'class', 'href', 'aria-controls', 'aria-labelledby', 'aria-describedby',
		'data-fid', 'data-field-id', 'data-changeme', 'data-removeid', 'data-removemore',
		'data-showlast', 'data-open'
	] );
	const ownerPattern = new RegExp( `(^|[_#\\[-])${ fromId }(?=$|[_\\]\\s-])` );
	[ element, ...element.querySelectorAll( '*' ) ].forEach( node => {
		Array.from( node.attributes ).forEach( attribute => {
			if ( attribute.name === 'name' ) {
				node.setAttribute( 'name', copyControlName( attribute.value, fromId, toId ) );
				return;
			}
			if ( ! identityAttributes.has( attribute.name ) ) {
				return;
			}
			const value = attribute.value.split( ' ' ).map( token => {
				const reference = references.find( ( [ original ] ) => {
					const index = token.indexOf( original );
					const end = index + original.length;
					return index >= 0 && ( index === 0 || '_#'.includes( token[ index - 1 ] ) ) &&
						( end === token.length || '_-'.includes( token[ end ] ) );
				} );
				if ( reference ) {
					return token.replace( reference[ 0 ], reference[ 1 ] );
				}
				return token.replace( ownerPattern, `$1${ toId }` );
			} ).join( ' ' );
			node.setAttribute( attribute.name, value );
		} );
	} );
}

/**
 * Restore snapshot values after dependency handlers refresh the duplicate's controls.
 *
 * @since x.x
 * @param {Object}      snapshot The source settings captured at click time.
 * @param {HTMLElement} settings The duplicate settings panel.
 * @return {Array} Controls whose values have changed.
 */
export function restoreFieldSettingValues( snapshot, settings ) {
	const fromId = snapshot.fieldId;
	const toId = settings.dataset.fid;
	const targets = new Map();
	settings.querySelectorAll( controlsSelector ).forEach( input => {
		const matches = targets.get( input.name ) || [];
		matches.push( input );
		targets.set( input.name, matches );
	} );
	const preserved = new Set( [ 'field_key', 'field_order', 'id', 'form_id' ].map(
		key => `field_options[${ key }_${ toId }]`
	) );
	const changed = [];
	snapshot.controls.forEach( control => {
		const name = copyControlName( control.name, fromId, toId );
		if ( name === control.name || preserved.has( name ) || control.type === 'file' ) {
			return;
		}
		const target = targets.get( name )?.shift();
		if ( ! target ) {
			return;
		}
		if ( control.select ) {
			const copy = cloneCurrentMarkup( control.select );
			target.replaceChildren( ...copy.childNodes );
		}
		const differs = target.value !== control.value || target.checked !== control.checked || control.select !== null;
		target.value = control.value;
		if ( control.select?.multiple ) {
			Array.from( control.select.options ).forEach( ( option, index ) => {
				target.options[ index ].selected = option.selected;
			} );
		}
		if ( control.type === 'checkbox' || control.type === 'radio' ) {
			target.checked = control.checked;
		}
		target.disabled = control.disabled;
		if ( differs ) {
			changed.push( target );
		}
	} );

	return changed;
}

/**
 * Overlay a snapshot on PHP's saved-value duplicate without writing to the database.
 *
 * @since x.x
 * @param {Object}      snapshot The source settings captured before the request.
 * @param {HTMLElement} settings The duplicate settings panel.
 * @param {HTMLElement} field    The duplicate builder field.
 * @return {Array} Controls whose values have changed and need their UI refreshed.
 */
export function applyFieldSettings( snapshot, settings, field ) {
	const fromId = snapshot.fieldId;
	const toId = settings.dataset.fid;
	const references = [];
	const fieldKey = settings.querySelector( `[name="field_options[field_key_${ toId }]"]` )?.value;
	if ( snapshot.fieldKey && fieldKey ) {
		references.push( [ `field_${ snapshot.fieldKey }`, `field_${ fieldKey }` ] );
	}
	const originalLabel = snapshot.preview?.querySelector( 'fieldset' )?.getAttribute( 'aria-labelledby' );
	const copyLabel = field.querySelector( '.frm_form_fields fieldset' )?.getAttribute( 'aria-labelledby' );
	if ( originalLabel && copyLabel ) {
		references.push( [ originalLabel, copyLabel ] );
	}
	const targets = new Map();
	settings.querySelectorAll( controlsSelector ).forEach( input => {
		const matches = targets.get( input.name ) || [];
		matches.push( input );
		targets.set( input.name, matches );
	} );
	snapshot.controls.forEach( control => {
		const name = copyControlName( control.name, fromId, toId );
		const target = targets.get( name )?.[ 0 ];
		if ( control.reference && target?.dataset.changeme ) {
			references.push( [ control.reference, target.dataset.changeme ] );
		}
	} );

	const choices = settings.querySelector( '.frm_sortable_field_opts' );
	if ( snapshot.choices && choices ) {
		references.push( [ `field_${ snapshot.choices.dataset.key }`, `field_${ choices.dataset.key }` ] );
		const copy = cloneCurrentMarkup( snapshot.choices );
		remapMarkup( copy, fromId, toId, references );
		choices.replaceChildren( ...copy.childNodes );
	}

	const changed = restoreFieldSettingValues( snapshot, settings );

	const preview = field.querySelector( ':scope > .frm_inner_field_container > .frm_form_fields' );
	if ( snapshot.preview && preview ) {
		const copy = cloneCurrentMarkup( snapshot.preview );
		remapMarkup( copy, fromId, toId, references );
		preview.replaceWith( copy );
	}
	return changed;
}
