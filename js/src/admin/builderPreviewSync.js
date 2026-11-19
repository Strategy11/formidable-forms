/* global frmDom */

/**
 * Live preview updates for settings that neither data-changeme nor data-frmpreview can describe.
 * The textarea Field Size uses data-frmpreview. See pixels-wide.php.
 *
 * @since x.x
 */

import { getSettingKey, truncateText } from './livePreview';

const HTML_EXCERPT_LENGTH = 60;

/**
 * Encode the characters that PHP's htmlentities() encodes in the HTML field excerpt.
 *
 * @since x.x
 *
 * @param {string} text The HTML field content.
 * @return {string} The encoded text.
 */
function encodeHtmlCharacters( text ) {
	return text
		.replaceAll( '&', '&amp;' )
		.replaceAll( '<', '&lt;' )
		.replaceAll( '>', '&gt;' )
		.replaceAll( '"', '&quot;' )
		.replaceAll( '\'', '&#039;' );
}

/**
 * Update the "Custom HTML:" text of an HTML field preview when its content changes.
 *
 * @since x.x
 *
 * @param {HTMLTextAreaElement} textarea The content editor textarea.
 * @return {void}
 */
export function syncHtmlFieldPreview( textarea ) {
	const setting = getSettingKey( textarea );
	if ( 'description' !== setting?.key ) {
		return;
	}

	const field = document.getElementById( `frm_field_id_${ setting.fieldId }` );
	if ( 'html' !== field?.dataset.type ) {
		return;
	}

	const message = field.querySelector( '.frm-embed-message' );
	if ( ! message ) {
		return;
	}

	const { __ } = wp.i18n;
	// Like the PHP preview, the excerpt is cut from the encoded text and printed without decoding it again.
	// Every < in it is encoded, so it cannot add markup.
	// PHP trims the text while removing tags.
	const excerpt = truncateText( encodeHtmlCharacters( textarea.value ).trim(), HTML_EXCERPT_LENGTH );
	message.innerHTML = `${ __( 'Custom HTML:', 'formidable' ) } ${ excerpt }`;
}

/**
 * @since x.x
 * @return {void}
 */
export function initBuilderPreviewSync() {
	const { documentOn } = frmDom.util;
	[ 'input', 'change' ].forEach( eventType => {
		// The visual editor calls syncHtmlFieldPreview from handleTinyMceChange instead.
		documentOn( eventType, '.frm-single-settings textarea[name^="field_options[description_"]', event => syncHtmlFieldPreview( event.target ) );
	} );
}
