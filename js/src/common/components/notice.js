import PropTypes from 'prop-types';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { Notice } = wp.components;

/**
 * Displays a notice with the given message, of the specified type
 *
 */
export default class FormidableNotice extends Component {
    constructor( props ) {
        super( ...arguments );
    }

    render() {
        const {
            message,
            type,
        } = this.props;

        let notice_type = type ? type : 'warning';

        return (
            <Notice status={ notice_type } isDismissible={ false }>
                { message }
            </Notice>
        );
    }
}

FormidableNotice.propTypes = {
    message: PropTypes.string.isRequired,//message to display
    type: PropTypes.string,//notice type.  Options: warning (yellow), success (green), error (red)
};