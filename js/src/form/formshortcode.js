/**
 * Form shortcode of current form
 */

/**
 * Internal dependencies
 */
import { setTextAttribute } from '../common/utilities/values';

function createParamsText( atts ) {
	const {
		formId,
		title,
		description,
		minimize
	} = atts;

	let paramsText = '';

	paramsText += setTextAttribute( formId, 'id' );
	paramsText += setTextAttribute( title, 'title' );
	paramsText += setTextAttribute( description, 'description' );
	paramsText += setTextAttribute( minimize, 'minimize' );

	return paramsText;
}

function FormShortcode( attributes ) {
	// NOTE: Due to the outdated version of our block implementation, the following solution
	// has been adopted to handle form submission within the WP Block Editor.
	// TODO: Refactor to use the useEffect and useCallback hooks in future versions.
	document.addEventListener( 'submit', function( event ) {
		if ( event.target && event.target.matches( '.frm-show-form' ) ) {
			event.preventDefault();
		}
	}, true ); // useCapture = true

	return (
		<div>
			[formidable { createParamsText( attributes ) }]
		</div>
	);
}

export default FormShortcode;
