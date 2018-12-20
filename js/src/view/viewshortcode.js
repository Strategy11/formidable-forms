/**
 * View shortcode for current View
 */
const { Component } = wp.element;
import PropTypes from 'prop-types';
import createParamsText from './params';

export default class FormShortcode extends Component {
	constructor( props ) {
		super( ...arguments );
	}

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