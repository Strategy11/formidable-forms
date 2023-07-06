/**
 * Form shortcode of current form
 */
const { Component } = wp.element;

import {
	setTextAttribute,
} from '../common/utilities/values';

function createParamsText( atts ) {
	const {
		formId,
		title,
		description,
		minimize,
	} = atts;

	let paramsText = '';

	paramsText += setTextAttribute( formId, 'id' );
	paramsText += setTextAttribute( title, 'title' );
	paramsText += setTextAttribute( description, 'description' );
	paramsText += setTextAttribute( minimize, 'minimize' );

	return paramsText;
}

export default class FormShortcode extends Component {
	render() {
		return (
			<div>
				[formidable
				{ createParamsText( this.props ) }
				]
			</div>
		);
	}
}
