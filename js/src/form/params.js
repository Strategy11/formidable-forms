/**
 *  Creates a string of parameters for the form shortcode or a form API call
 */
import {
	setTextAttribute,
} from '../common/utilities/values';

export default function createParamsText( atts ) {
	const {
		form_id,
		title,
		description,
		minimize,
	} = atts;

	let paramsText = '';

	paramsText += setTextAttribute( form_id, 'id' );
	paramsText += setTextAttribute( title, 'title' );
	paramsText += setTextAttribute( description, 'description' );
	paramsText += setTextAttribute( minimize, 'minimize' );

	return paramsText;
}
