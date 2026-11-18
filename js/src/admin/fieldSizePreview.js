/**
 * Keep the form builder preview in sync with the Field Size setting.
 *
 * PHP prints the saved width with each field preview, so nothing runs on load.
 * This only applies changes as the setting is edited.
 */

const SIZE_SETTING_SELECTOR = '.frm-single-settings input[name^="field_options[size_"]';
const SIZE_SETTING_PREFIX = 'field_options[size_';
const SKIPPED_INPUT_TYPES = new Set( [ 'hidden', 'checkbox', 'radio', 'range', 'file' ] );

/**
 * Listen for Field Size changes in the field settings.
 *
 * @since 7.1
 *
 * @return {void}
 */
export function initFieldSizePreview() {
	document.addEventListener( 'input', onFieldSizeInput );
}

/**
 * Update the builder preview width when a Field Size setting changes.
 *
 * @private
 * @param {Event} event The input event.
 * @return {void}
 */
function onFieldSizeInput( event ) {
	const setting = event.target;
	if ( ! setting.matches?.( SIZE_SETTING_SELECTOR ) ) {
		return;
	}

	const fieldId = setting.name.slice( SIZE_SETTING_PREFIX.length, -1 );
	const preview = getSizePreviewElement( fieldId );
	if ( preview ) {
		preview.style.width = normalizeSize( setting.value );
	}
}

/**
 * Get the preview input that the Field Size setting applies to.
 *
 * @private
 * @param {string} fieldId The field ID.
 * @return {HTMLElement|null} The preview input or textarea.
 */
function getSizePreviewElement( fieldId ) {
	const fieldContainer = document.getElementById( `frm_field_id_${ fieldId }` );
	const preview = fieldContainer?.querySelector( `[name="item_meta[${ fieldId }]"]` );
	if ( ! preview ) {
		return null;
	}

	if ( 'TEXTAREA' === preview.tagName ) {
		return preview;
	}

	if ( 'INPUT' === preview.tagName && ! SKIPPED_INPUT_TYPES.has( preview.type ) ) {
		return preview;
	}

	return null;
}

/**
 * Match the PHP output, which treats a bare number as pixels.
 *
 * @private
 * @param {string} size The Field Size setting value.
 * @return {string} A CSS width, or an empty string to clear it.
 */
function normalizeSize( size ) {
	const trimmed = size.trim();
	if ( '' === trimmed ) {
		return '';
	}

	return /^\d+(\.\d+)?$/.test( trimmed ) ? `${ trimmed }px` : trimmed;
}
