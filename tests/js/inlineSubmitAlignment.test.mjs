import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';
import { parseSync } from '@babel/core';

const source = readFileSync( new URL( '../../js/formidable.js', import.meta.url ), 'utf8' );
const ast = parseSync( source, { configFile: false, babelrc: false } );
const frontend = ast.program.body.find( node => node.id?.name === 'frmFrontFormJS' );
const names = [ 'initInlineSubmitAlignment', 'isInlineFormMutation', 'alignInlineSubmitButtons', 'alignInlineSubmitButton', 'getInlineSubmitRowInput' ];
const functions = names.map( name => {
	const declaration = frontend.body.body.find( node => node.id?.name === name );
	assert.ok( declaration, `${ name } must exist in the frontend` );
	return source.slice( declaration.start, declaration.end );
} ).join( '\n' );
const property = '--frm-inline-submit-offset';

/**
 * Build a MutationRecord like object.
 *
 * @param {Object}  options
 * @param {boolean} options.inForm       Whether the changed parent is inside an inline form.
 * @param {Array}   options.addedNodes   The inserted nodes.
 * @param {Array}   options.removedNodes The removed nodes.
 * @return {Object} The mutation record.
 */
function mutation( { inForm = false, addedNodes = [], removedNodes = [] } = {} ) {
	return { target: { closest: () => inForm ? {} : null }, addedNodes, removedNodes };
}

/**
 * Build an element node for a mutation record.
 *
 * @param {boolean} hasInlineForm Whether the node is or contains an inline form.
 * @return {Object} The node.
 */
function elementNode( hasInlineForm ) {
	return { nodeType: 1, matches: () => hasInlineForm, querySelector: () => null };
}

/**
 * Create independently measurable elements and load the production alignment functions.
 *
 * @return {Object} The elements, scheduling callbacks and production functions.
 */
function setup() {
	const styles = new Map();
	const input = { getClientRects: () => [ {} ], getBoundingClientRect: () => ( { top: 128 } ) };
	const field = {
		isConnected: true,
		classList: { contains: () => true },
		getClientRects: () => [ {} ],
		getBoundingClientRect: () => ( { top: 100, bottom: 250 } ),
		querySelector: () => input,
		previousElementSibling: null
	};
	const button = { matches: () => true, getClientRects: () => [ {} ], getBoundingClientRect: () => ( { top: 132 } ) };
	const submit = {
		isConnected: true,
		style: { removeProperty: key => styles.delete( key ), setProperty: ( key, value ) => styles.set( key, value ) },
		getBoundingClientRect: () => ( { top: 100 } ),
		children: [ button ],
		previousElementSibling: field
	};
	const frames = [];
	const listeners = new Map();
	const observed = new Set();
	const state = { styles, input, field, button, submit, frames, listeners, observed, fields: [ field, submit ] };
	state.queries = 0;
	const document = {
		body: {},
		querySelectorAll: selector => {
			state.queries++;
			return selector.includes( '> *' ) ? state.fields : [ submit ];
		}
	};
	const functionsToLoad = `${ functions }\n({ initInlineSubmitAlignment, alignInlineSubmitButton, getInlineSubmitRowInput })`;
	Object.assign( state, runInNewContext( functionsToLoad, {
		document,
		Node: { ELEMENT_NODE: 1 },
		window: {
			requestAnimationFrame: callback => frames.push( callback ),
			addEventListener: ( event, callback ) => listeners.set( event, callback )
		},
		ResizeObserver: class {
			constructor( callback ) {
				state.resize = callback;
			}
			observe( element ) {
				observed.add( element );
			}
			unobserve( element ) {
				observed.delete( element );
			}
		},
		MutationObserver: class {
			constructor( callback ) {
				state.mutate = callback;
			}
			observe() {}
		}
	} ) );
	return state;
}

test( 'alignment follows the input instead of descriptions or validation errors', () => {
	for ( const bottom of [ 164, 188, 250 ] ) {
		const state = setup();
		state.field.getBoundingClientRect = () => ( { top: 100, bottom } );
		state.alignInlineSubmitButton( state.submit );
		assert.equal( state.styles.get( property ), '-4px' );
	}
} );

test( 'Previous and Submit align the first row when the buttons wrap', () => {
	const state = setup();
	state.submit.children.unshift( { matches: () => true, getClientRects: () => [ {} ], getBoundingClientRect: () => ( { top: 168 } ) } );
	state.alignInlineSubmitButton( state.submit );
	assert.equal( state.styles.get( property ), '-4px' );
} );

test( 'stacked fields clear a previous desktop offset', () => {
	const state = setup();
	state.styles.set( property, '-4px' );
	state.submit.getBoundingClientRect = () => ( { top: 300 } );
	state.alignInlineSubmitButton( state.submit );
	assert.equal( state.styles.has( property ), false );
} );

test( 'hidden fields are skipped when finding the closest input', () => {
	const state = setup();
	state.submit.previousElementSibling = { ...state.field, getClientRects: () => [], previousElementSibling: state.field };
	assert.equal( state.getInlineSubmitRowInput( state.submit ), state.input );
} );

test( 'hidden controls and empty submit areas do not keep stale offsets', () => {
	const state = setup();
	state.input.getClientRects = () => [];
	state.styles.set( property, '12px' );
	state.alignInlineSubmitButton( state.submit );
	assert.equal( state.styles.has( property ), false );
	state.input.getClientRects = () => [ {} ];
	state.submit.children = [];
	state.alignInlineSubmitButton( state.submit );
	assert.equal( state.styles.has( property ), false );
} );

test( 'resizing and DOM changes share a single animation frame', () => {
	const state = setup();
	state.initInlineSubmitAlignment();
	state.listeners.get( 'resize' )();
	state.resize();
	state.mutate( [ mutation( { inForm: true } ) ] );
	assert.equal( state.frames.length, 1 );
	state.frames.shift()();
	assert.equal( state.styles.get( property ), '-4px' );
} );

test( 'new forms are observed and detached fields are released after AJAX replacement', () => {
	const state = setup();
	state.initInlineSubmitAlignment();
	assert.equal( state.observed.has( state.field ), true );
	const inserted = { isConnected: true };
	state.field.isConnected = false;
	state.fields = [ inserted, state.submit ];
	state.mutate( [ mutation( { addedNodes: [ elementNode( true ) ], removedNodes: [ elementNode( true ) ] } ) ] );
	assert.equal( state.observed.has( state.field ), false );
	assert.equal( state.observed.has( inserted ), true );
} );

test( 'DOM changes outside inline forms skip the field lookup', () => {
	const state = setup();
	state.initInlineSubmitAlignment();
	state.frames.length = 0;
	const queries = state.queries;
	state.mutate( [
		mutation( { addedNodes: [ { nodeType: 3 }, elementNode( false ) ], removedNodes: [ elementNode( false ) ] } ),
		mutation()
	] );
	assert.equal( state.queries, queries );
	assert.equal( state.frames.length, 0 );
} );

test( 'removing an inline form releases its fields', () => {
	const state = setup();
	state.initInlineSubmitAlignment();
	state.field.isConnected = false;
	state.fields = [];
	state.mutate( [ mutation( { removedNodes: [ elementNode( false ) ] } ) ] );
	assert.equal( state.observed.has( state.field ), false );
} );

test( 'loading indicators do not move the buttons away from the input', () => {
	const state = setup();
	state.submit.children.unshift( { matches: () => false, getClientRects: () => [ {} ], getBoundingClientRect: () => ( { top: 100 } ) } );
	state.alignInlineSubmitButton( state.submit );
	assert.equal( state.styles.get( property ), '-4px' );
} );
