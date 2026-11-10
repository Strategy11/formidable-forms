/**
 * Shared adapter for WordPress's block editor color picker.
 */
import { formatColor } from './format-color';

const pickers = new WeakMap();
const { createElement, createRoot } = wp.element;
const { ColorPicker } = wp.components;
const { __ } = wp.i18n;
let activePicker;

/**
 * Initialize once, preserving the original input for form serialization.
 *
 * @since x.x
 * @param {HTMLInputElement} input   Color setting input.
 * @param {Object}           options Picker callbacks and default color.
 * @return {Object} The picker adapter.
 */
function init( input, options = {} ) {
	if ( pickers.has( input ) ) {
		return pickers.get( input );
	}
	const { div, span, tag } = frmDom;
	const existing = input.closest( '.frm-block-color-picker' );
	const swatch = existing?.querySelector( '.frm-block-color-swatch' ) || span( { className: 'frm-block-color-swatch' } );
	const text = existing?.querySelector( '.frm-block-color-value' ) || span( { className: 'frm-block-color-value' } );
	const button = existing?.querySelector( '.frm-block-color-toggle' ) || tag( 'button', {
		className: 'frm-block-color-toggle',
		children: [ swatch, text ],
	} );
	const mount = div();
	const panel = div( { className: 'frm-block-color-panel', child: mount } );
	const valueInput = tag( 'input', { className: 'frm-block-color-input' } );
	const wrapper = existing || div( { className: 'frm-block-color-picker' } );
	const clear = tag( 'button', {
		className: 'frm-block-color-clear',
		text: __( 'Clear', 'formidable' ),
	} );
	if ( ! existing ) {
		input.before( wrapper );
	}
	button.type = 'button';
	clear.type = 'button';
	panel.hidden = true;
	swatch.replaceChildren();
	swatch.setAttribute( 'aria-hidden', 'true' );
	wrapper.append( button, input );
	panel.append( valueInput, clear );
	input.hidden = true;
	button.setAttribute( 'aria-expanded', 'false' );
	panel.setAttribute( 'role', 'group' );
	panel.setAttribute( 'aria-label', __( 'Color', 'formidable' ) );
	if ( input.id ) {
		button.id = `${ input.id }_visible`;
		panel.id = `${ input.id }_picker`;
		button.setAttribute( 'aria-controls', panel.id );
		document.querySelectorAll( 'label' ).forEach( label => {
			if ( label.htmlFor === input.id ) {
				label.htmlFor = button.id;
			}
		} );
	}
	valueInput.setAttribute( 'aria-label', __( 'Color value', 'formidable' ) );
	let root;
	const update = () => {
		text.textContent = input.value || __( 'Select Color', 'formidable' );
		swatch.style.backgroundColor = input.value || 'transparent';
		button.disabled = input.disabled;
		valueInput.value = input.value;
		if ( ! root || panel.hidden ) {
			return;
		}
		root.render( createElement( ColorPicker, {
			color: input.value || options.defaultColor || '#ffffff',
			enableAlpha: input.dataset.alphaEnabled === 'true',
			onChange: change,
		} ) );
	};
	const notify = event => {
		update();
		options.change?.( event, null );
	};
	const change = hex => {
		// The format menu changes the display. Preserve the stored format when editing.
		input.value = hex ? formatColor( hex, input.value ) : '';
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	};
	const contains = target => {
		if ( wrapper.contains( target ) || panel.contains( target ) ) {
			return true;
		}
		// Newer WordPress versions render the format menu outside the picker panel.
		const listbox = target.closest?.( '[role="listbox"]' ) ||
			target.closest?.( '[role="presentation"]' )?.querySelector( '[role="listbox"]' );
		if ( ! listbox?.id ) {
			return false;
		}
		return [ ...panel.querySelectorAll( '[aria-controls]' ) ].some(
			control => control.getAttribute( 'aria-controls' ).split( /\s+/ ).includes( listbox.id )
		);
	};
	const position = () => {
		if ( ! button.isConnected ) {
			close();
			return;
		}
		const gap = 8;
		const anchor = button.getBoundingClientRect();
		const toolbar = document.getElementById( 'wpadminbar' )?.getBoundingClientRect();
		const minimumTop = Math.max( gap, ( toolbar?.bottom || 0 ) + gap );
		panel.style.maxHeight = `${ window.innerHeight - minimumTop - gap }px`;
		const width = panel.offsetWidth;
		const height = panel.offsetHeight;
		const left = Math.max( gap, Math.min( anchor.right - width, window.innerWidth - width - gap ) );
		let top = anchor.bottom + gap;
		if ( top + height > window.innerHeight - gap ) {
			top = anchor.top - height - gap;
		}
		top = Math.max( minimumTop, Math.min( top, window.innerHeight - height - gap ) );
		panel.style.left = `${ left }px`;
		panel.style.top = `${ top }px`;
	};
	const observer = new ResizeObserver( position );
	const close = () => {
		panel.hidden = true;
		// Clear React's portals as well as the picker panel.
		root?.render( null );
		panel.remove();
		observer.disconnect();
		window.removeEventListener( 'scroll', position, true );
		window.removeEventListener( 'resize', position );
		button.setAttribute( 'aria-expanded', 'false' );
		if ( activePicker?.contains === contains ) {
			activePicker = null;
		}
	};
	button.addEventListener( 'click', () => {
		if ( ! panel.hidden ) {
			close();
			return;
		}
		activePicker?.close();
		document.body.append( panel );
		panel.hidden = false;
		button.setAttribute( 'aria-expanded', 'true' );
		root ??= createRoot( mount );
		activePicker = { contains, close };
		update();
		position();
		observer.observe( panel );
		window.addEventListener( 'scroll', position, true );
		window.addEventListener( 'resize', position );
	} );
	const handleKeyDown = event => {
		if ( event.key === 'Tab' && event.target === button && ! panel.hidden && ! event.shiftKey ) {
			event.preventDefault();
			panel.querySelector( '[role="slider"], input, button' )?.focus();
		}
		if ( event.key === 'Escape' ) {
			event.preventDefault();
			event.stopPropagation();
			close();
			button.focus();
		}
	};
	const handleFocusOut = event => {
		if ( event.relatedTarget && ! contains( event.relatedTarget ) ) {
			close();
		}
	};
	[ wrapper, panel ].forEach( element => {
		element.addEventListener( 'keydown', handleKeyDown );
		element.addEventListener( 'focusout', handleFocusOut );
	} );
	valueInput.addEventListener( 'input', () => {
		input.value = valueInput.value;
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	} );
	valueInput.addEventListener( 'change', () => {
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
	clear.addEventListener( 'click', () => change( '' ) );
	input.addEventListener( 'input', update );
	input.addEventListener( 'change', notify );
	input.addEventListener( 'frm:color-sync', update );
	input.form?.addEventListener( 'reset', () => setTimeout( update, 0 ) );
	const picker = { update, close };
	pickers.set( input, picker );
	update();
	return picker;
}

document.addEventListener( 'pointerdown', event => {
	if ( activePicker && ! activePicker.contains( event.target ) ) {
		activePicker.close();
	}
} );

document.addEventListener( 'focusin', event => {
	if ( activePicker && ! activePicker.contains( event.target ) ) {
		activePicker.close();
	}
} );

// Extend the existing DOM utilities so Lite and Pro share a single adapter.
frmDom.colorPicker = { init };
