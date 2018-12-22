/**
 * Updates an attribute with the specified new value
 *
 * @param attributeName
 * @param attributeValue
 * @param setAttributes
 */
export function updateAttribute( attributeName, attributeValue, setAttributes ) {
	setAttributes( {
		[ attributeName ]: attributeValue,
	} );
}

/**
 * Sets text attribute for a shortcode from a key value pair
 *
 * @param value
 * @param attributeName
 * @returns {string}
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
 * @returns {string}
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
