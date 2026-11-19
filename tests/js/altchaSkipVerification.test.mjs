import assert from 'node:assert/strict';
import { test } from 'node:test';
import { skipsVerification } from '../../js/src/altcha-skip-verification.js';

/**
 * Build a stand-in for a form with an optional Formidable Pro draft flag.
 *
 * @param {string|null} draft Value of the hidden frm_saving_draft input, or null when the form has none.
 * @return {Object} The form stand-in.
 */
function form( draft = null ) {
	return {
		querySelector: selector => selector === 'input.frm_saving_draft' && draft !== null ? { value: draft } : null
	};
}

/**
 * Build a stand-in for a submit button that matches the given selectors.
 *
 * @param {string[]} matching Selectors the button matches.
 * @return {Object} The button stand-in.
 */
function button( matching ) {
	return {
		matches: selector => selector.split( ', ' ).some( part => matching.includes( part ) )
	};
}

test( 'previous page and save draft buttons skip verification', () => {
	assert.equal( skipsVerification( form(), button( [ '.frm_prev_page' ] ) ), true );
	assert.equal( skipsVerification( form(), button( [ '[name="frm_prev_page"]' ] ) ), true );
	assert.equal( skipsVerification( form(), button( [ '.frm_page_back' ] ) ), true );
	assert.equal( skipsVerification( form(), button( [ '.frm_save_draft' ] ) ), true );
} );

test( 'a draft flagged by Pro skips verification without a submitter', () => {
	assert.equal( skipsVerification( form( '1' ) ), true );
} );

test( 'submit and next page buttons still verify', () => {
	assert.equal( skipsVerification( form( '' ), button( [ '.frm_button_submit' ] ) ), false );
	assert.equal( skipsVerification( form(), null ), false );
	assert.equal( skipsVerification( form() ), false );
} );
