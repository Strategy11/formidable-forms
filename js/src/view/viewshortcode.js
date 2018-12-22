/**
 * View shortcode for current View
 */
const { Component } = wp.element;
import {
	setTextAttribute,
} from '../common/utilities/values';

function createParamsText( atts ) {
	const {
		viewId,
		filter,
	} = atts;

	let paramsText = '';

	paramsText += setTextAttribute( viewId, 'id' );

	paramsText += setTextAttribute( filter, 'filter' );

	return paramsText;
}

export default class ViewShortcode extends Component {
	render() {
		return (
			<div>
				[display-frm-data
				{ createParamsText( this.props ) }
				]
			</div>
		);
	}
}
