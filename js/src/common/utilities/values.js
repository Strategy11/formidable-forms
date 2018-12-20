/**
 * Updates an attribute with the specified new value
 *
 * @param attribute_name
 * @param attribute_value
 * @param setAttributes
 */
export function updateAttribute( attribute_name, attribute_value, setAttributes ) {
	setAttributes( {
		[ attribute_name ]: attribute_value,
	} );
}

/**
 * Sets text attribute for a shortcode from a key value pair
 *
 * @param value
 * @param attribute_name
 * @returns {string}
 */
export function setTextAttribute( value, attribute_name ) {
	if ( value ) {
		return ` ${ attribute_name }="${ value }"`;
	}
	return '';
}

/**
 * Gets subdirectory of current site, if the site isn't on the top level of the domain
 *
 * @returns {string}
 */
export function getSubDir() {
	let page = window.location.pathname;
	let index = page.indexOf( 'wp-admin' );

	let sub_dir = '/';

	if ( index > - 1 ) {

		sub_dir = page.substr( 0, index );
	}

	return sub_dir;
}
