import assert from 'node:assert/strict';
import { test } from 'node:test';
import { addFieldSettingsMetadata, cacheFieldSettings, materializeFieldSettings, setPendingFieldSetting } from '../../js/src/admin/deferredFieldSettings.js';

const elements = new Map();

function element( tagName, props = {} ) {
	const node = {
		tagName,
		children: [],
		className: '',
		id: '',
		name: '',
		value: '',
		parent: null,
		append( ...nodes ) {
			nodes.forEach( child => {
				child.parent = node;
				node.children.push( child );
			} );
		},
		remove() {
			node.parent.children = node.parent.children.filter( child => child !== node );
		},
		querySelectorAll( selector ) {
			const found = [];
			const walk = parent => parent.children.forEach( child => {
				if ( selector.split( ', ' ).includes( child.tagName ) ) {
					found.push( child );
				}
				walk( child );
			} );
			walk( node );
			return found;
		},
		querySelector( selector ) {
			const className = selector.replace( ':scope > ', '' ).replace( '.', '' );
			return node.children.find( child => child.className === className ) || null;
		},
		// Stands in for parsing the cached settings HTML.
		insertAdjacentHTML( position, html ) {
			node.append( html() );
		},
		...props
	};
	return node;
}

globalThis.frmDom = {
	tag: tagName => element( tagName ),
	div: props => element( 'div', props )
};
globalThis.document = {
	getElementById: id => {
		const walk = parent => {
			for ( const child of parent.children ) {
				if ( child.id === id ) {
					return child;
				}
				const match = walk( child );
				if ( match ) {
					return match;
				}
			}
			return null;
		};
		return elements.get( id ) || walk( { children: [ ...elements.values() ] } );
	}
};
globalThis.wp = { hooks: { doAction: () => {} } };

function addField( fieldId ) {
	const field = element( 'li', { id: `frm_field_id_${ fieldId }` } );
	elements.set( field.id, field );
	return field;
}

test( 'metadata posts dropdown fields as "dropdown" and skips missing values', () => {
	const field = addField( '1' );
	addFieldSettingsMetadata( field, '1', { name: 'Pick one', type: 'select', key: null } );
	const inputs = field.querySelector( '.frm-deferred-settings-meta' ).querySelectorAll( 'input' );
	assert.deepEqual( inputs.map( input => [ input.name, input.value ] ), [
		[ 'field_options[name_1]', 'Pick one' ],
		[ 'field_options[type_1]', 'dropdown' ]
	] );
} );

test( 'a setting changed before the panel loads is applied when the panel is inserted', () => {
	const field = addField( '2' );
	addFieldSettingsMetadata( field, '2', { name: 'Moved' } );
	assert.equal( setPendingFieldSetting( '2', 'in_section', 'frm_in_section_2', '9' ), true );
	assert.equal( setPendingFieldSetting( '2', 'in_section', 'frm_in_section_2', '10' ), true );

	const inSection = element( 'input', { id: 'frm_in_section_2', name: 'field_options[in_section_2]', value: '0' } );
	const settings = element( 'div', { id: 'frm-single-settings-2' } );
	settings.append( inSection );
	cacheFieldSettings( '2', () => settings );

	const initialized = [];
	assert.equal( materializeFieldSettings( '2', panel => initialized.push( panel ) ), settings );
	assert.equal( inSection.value, '10' );
	assert.deepEqual( initialized, [ settings ] );
	assert.equal( field.querySelector( '.frm-deferred-settings-meta' ), null );
} );

test( 'a pending setting is not kept for a field without deferred settings', () => {
	addField( '3' );
	assert.equal( setPendingFieldSetting( '3', 'in_section', 'frm_in_section_3', '9' ), false );
	assert.equal( setPendingFieldSetting( '4', 'in_section', 'frm_in_section_4', '9' ), false );
} );
