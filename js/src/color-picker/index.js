/**
 * Shared adapter for WordPress's block editor color picker.
 */
import { addColorHash, colorToHex, formatColor, isHexField, isTypedHexValid } from './format-color';

const pickers = new WeakMap();
const noop = { update() {}, close() {} };
const { createElement, createRoot } = wp.element;
const { ColorPicker } = wp.components;
const { __ } = wp.i18n;
let activePicker;

/**
 * Initialize once, preserving the original input for form serialization.
 *
 * @since 7.1
 * @param {HTMLInputElement} input   Color setting input.
 * @param {Object}           options Picker callbacks and default color.
 * @return {Object} The picker adapter.
 */
function init( input, options = {} ) {
	if ( pickers.has( input ) ) {
		return pickers.get( input );
	}
	if ( input.closest( '.wp-picker-container' ) ) {
		// Another script already turned this input into a WordPress Iris picker.
		releaseToIris( input );
		return noop;
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
	const wrapper = existing || div( { className: 'frm-block-color-picker' } );
	const clear = tag( 'button', {
		className: 'frm-block-color-clear',
		text: __( 'Clear', 'formidable' ),
	} );
	const done = tag( 'button', {
		className: 'frm-block-color-done frm-button-primary frm-button-sm',
		text: __( 'Done', 'formidable' ),
	} );
	const actions = div( { className: 'frm-block-color-actions', children: [ clear, done ] } );
	if ( ! existing ) {
		input.before( wrapper );
	}
	button.type = 'button';
	clear.type = 'button';
	done.type = 'button';
	panel.hidden = true;
	swatch.replaceChildren();
	swatch.setAttribute( 'aria-hidden', 'true' );
	wrapper.append( button, input );
	panel.append( actions );
	input.hidden = true;
	button.setAttribute( 'aria-expanded', 'false' );
	panel.setAttribute( 'role', 'group' );
	panel.setAttribute( 'aria-label', __( 'Color', 'formidable' ) );
	if ( input.id ) {
		button.id = `${ input.id }_visible`;
		panel.id = `${ input.id }_picker`;
		document.querySelectorAll( 'label' ).forEach( label => {
			if ( label.htmlFor === input.id ) {
				label.htmlFor = button.id;
			}
		} );
	}
	let root;
	let selectedFormat = input.value.trim().match( /^(rgb|hsl)a?\(/i )?.[ 1 ].toLowerCase() || 'hex';
	const getSelectedFormat = () => {
		const select = mount.querySelector( 'select' );
		const trigger = mount.querySelector( '[aria-haspopup="listbox"]' );
		const value = ( select?.value || trigger?.textContent || '' ).trim().toLowerCase();
		return [ 'hex', 'rgb', 'hsl' ].includes( value ) ? value : selectedFormat;
	};
	const update = () => {
		// Style settings save hex colors without the hash, so add it back to display them.
		const color = addColorHash( input.value );
		text.textContent = color || __( 'Select Color', 'formidable' );
		swatch.style.backgroundColor = color || 'transparent';
		button.disabled = input.disabled;
		if ( ! root || panel.hidden ) {
			return;
		}
		root.render( createElement( ColorPicker, {
			className: 'frm-block-color-control',
			color: color || options.defaultColor || '#ffffff',
			enableAlpha: input.dataset.alphaEnabled === 'true',
			copyFormat: selectedFormat,
			onChange: change,
		} ) );
	};
	const notify = event => {
		update();
		options.change?.( event, null );
	};
	const change = hex => {
		// WordPress emits black while the hex field holds text it cannot parse. Keep the saved color until the text is valid.
		// Only the hex format shows this field, so pure black picked in RGB or HSL still applies.
		const hexField = [ ...mount.querySelectorAll( 'input[maxlength]' ) ].find( isHexField );
		if ( hex && hexField && ! isTypedHexValid( hexField.value ) && /^#000000(?:ff)?$/i.test( hex ) ) {
			return;
		}
		selectedFormat = getSelectedFormat();
		input.value = hex ? formatColor( hex, input.value, selectedFormat ) : '';
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	};
	// WordPress exposes color changes as hex, but has no callback for its format menu.
	const formatObserver = new MutationObserver( () => {
		const format = getSelectedFormat();
		if ( format === selectedFormat ) {
			return;
		}
		selectedFormat = format;
		if ( ! input.value ) {
			update();
			return;
		}
		const hex = colorToHex( input.value );
		if ( hex ) {
			change( hex );
		}
	} );
	const contains = target => {
		if ( wrapper.contains( target ) || panel.contains( target ) ) {
			return true;
		}
		// WordPress 7.2+ renders the format menu as a popup portaled outside the picker panel.
		// WordPress 7.1 and older use a native select inside the panel, so this lookup does not apply there.
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
		formatObserver.disconnect();
		window.removeEventListener( 'scroll', position, true );
		window.removeEventListener( 'resize', position );
		button.setAttribute( 'aria-expanded', 'false' );
		// The panel leaves the DOM while closed, so only reference it while it is open.
		button.removeAttribute( 'aria-controls' );
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
		if ( panel.id ) {
			button.setAttribute( 'aria-controls', panel.id );
		}
		selectedFormat = input.value.trim().match( /^(rgb|hsl)a?\(/i )?.[ 1 ].toLowerCase() || 'hex';
		root ??= createRoot( mount );
		formatObserver.observe( mount, { childList: true, subtree: true, characterData: true } );
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
	clear.addEventListener( 'click', () => change( '' ) );
	done.addEventListener( 'click', () => {
		close();
		button.focus();
	} );
	input.addEventListener( 'input', update );
	input.addEventListener( 'change', notify );
	input.addEventListener( 'frm:color-sync', update );
	input.form?.addEventListener( 'reset', () => setTimeout( update, 0 ) );
	const picker = { update, close };
	pickers.set( input, picker );
	update();
	return picker;
}

/**
 * Remove the Lite toggle from an input that another script turned into a WordPress Iris picker.
 *
 * Pro 7.0.1 and older call wpColorPicker() on the color inputs in its email style settings.
 *
 * @since 7.1
 * @param {HTMLInputElement} input Color setting input.
 * @return {void}
 */
function releaseToIris( input ) {
	input.hidden = false;
	input.closest( '.frm-block-color-picker' )?.querySelector( ':scope > .frm-block-color-toggle' )?.remove();
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
frmDom.colorPicker = { init, releaseToIris };
