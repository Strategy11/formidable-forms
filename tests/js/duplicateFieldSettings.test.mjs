/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { restoreFieldSettingValues } from '../../js/src/admin/duplicateFieldSettings.js';

function restoreSettings( repeat ) {
	const controls = [ 'form_select', 'name', 'field_key', 'field_order', 'form_id' ];
	const targets = controls.map( key => ( {
		name: `field_options[${ key }_202]`,
		value: `new-${ key }`,
		checked: false,
	} ) );
	const snapshot = {
		fieldId: '101',
		controls: controls.map( key => ( {
			name: `field_options[${ key }_101]`,
			value: `original-${ key }`,
			type: 'hidden',
			checked: false,
			disabled: false,
			select: null,
		} ) ),
	};
	const settings = {
		dataset: { fid: '202' },
		querySelectorAll: () => targets,
		querySelector: () => repeat === null ? null : { value: repeat },
	};

	restoreFieldSettingValues( snapshot, settings );
	return targets.map( target => target.value );
}

test( 'duplicated repeaters keep their new child form and generated identities', () => {
	assert.deepEqual( restoreSettings( '1' ), [
		'new-form_select', 'original-name', 'new-field_key', 'new-field_order', 'new-form_id',
	] );
} );

test( 'non-repeating fields still copy an unsaved form or field selection', () => {
	for ( const repeat of [ '0', '', null ] ) {
		assert.deepEqual( restoreSettings( repeat ), [
			'original-form_select', 'original-name', 'new-field_key', 'new-field_order', 'new-form_id',
		] );
	}
} );
