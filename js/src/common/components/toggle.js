const { __ } = wp.i18n;
import PropTypes from 'prop-types';
import { updateAttribute } from "../utilities/values";

const {
    Component
} = wp.element;
const {
    ToggleControl
} = wp.components;

/**
 * Displays a Toggle Control that sets the value of the selected attribute to '1' or '0'
 *
 */
export default class Toggle extends Component {
    constructor( props ) {
        super( ...arguments );
    }

    render() {
        const {
            label,
            help_true,
            help_false,
            attribute_name,
            attribute_value,
            setAttributes,
        } = this.props;

        return (
            <ToggleControl
                label={ label }
                checked={ attribute_value === '1' }
                onChange={ response => {
                    updateAttribute( attribute_name, response ? '1' : '0', setAttributes );
                }
                }
            />
        );
    }
}


Toggle.propTypes = {
    label: PropTypes.string,//label of ToggleControl
    help_true: PropTypes.string,//label to be shown when true/on position is selected
    help_false: PropTypes.string,//label to be shown when false/off position is selecte
    attribute_name: PropTypes.string,//name of attribute, e.g. 'title'
    attribute_value: PropTypes.string,//value of attribute, either '1' or '0',
    setAttributes: PropTypes.func,//setAttributes of current block
};
