/**
 * Form shortcode of current form
 */
import PropTypes from 'prop-types';
import createParamsText from './params';

const { __ } = wp.i18n;
const { Component } = wp.element;

export default class FormShortcode extends Component {
	constructor( props ) {
		super( ...arguments );
	}

	render() {
		const {
			className,
		} = this.props;

		return (
			<div className={ className }>
				[formidable
				{ createParamsText( this.props ) }
				]
			</div>
		);
	}
}

FormShortcode.propTypes = {
	className: PropTypes.string,//block class name
};