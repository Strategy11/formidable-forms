/**
 * Updates an attribute with the specified new value
 *
 * @param {string}   attributeName  Name of block attribute to be updated
 * @param {*}        attributeValue Value of block attribute to be updated
 * @param {Function} setAttributes  Function to set the block attribute to be updated
 */
export function updateAttribute( attributeName, attributeValue, setAttributes ) {
	setAttributes({
		[ attributeName ]: attributeValue
	});
}

/**
 * Sets text attribute for a shortcode from a key value pair
 *
 * @param {*}      value         Value of text attribute to be set
 * @param {string} attributeName Name of text attribute to be set
 * @return {string} String of the text attribute in the format " id=10"
 */
export function setTextAttribute( value, attributeName ) {
	if ( value ) {
		return ` ${ attributeName }="${ value }"`;
	}
	return '';
}

/**
 * Gets subdirectory of current site, if the site isn't on the top level of the domain
 *
 * @return {string} The subdirectory of the current site
 */
export function getSubDir() {
	const page = window.location.pathname;
	const index = page.indexOf( 'wp-admin' );

	let subDir = '/';

	if ( index > -1 ) {
		subDir = page.substr( 0, index );
	}

	return subDir;
}

export const cssHideAdvancedSettings = `
    .components-panel__body.editor-block-inspector__advanced {
        display:none;
    }
`;
