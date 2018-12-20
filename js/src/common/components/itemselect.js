const { __ } = wp.i18n;
import PropTypes from 'prop-types';

const {
    Component
} = wp.element;
const {
    SelectControl,
    Spinner
} = wp.components;

/**
 * Displays a Select control with the specified items as options
 *
 */
export default class ItemSelect extends Component {
    constructor( props ) {
        super( ...arguments );
    }

    createOptions( items, itemName ) {

        let options = items.map( item => {
            return {
                label: item.label,
                value: item.value,
            };
        } );

        return [
            {
                label: __( `Select a ${ itemName }` ),
                value: '',
            },
            ...options
        ];
    }

    render() {
        const {
            selected,
            items,
            onChange,
            itemName,
            itemNamePlural,
            label,
            help,
            complete,
            className,
        } = this.props;

        let {
            dependent_item_name,
        } = this.props;

        if ( ( ! items || items.length === 0 ) && complete ) {
            return (
                <p className={ "frm-block-select-no-items" }>
                    { __( `Currently, there are no ${ itemNamePlural }.` ) }
                </p>
            )
        }
        if ( ! items || items.length === 0 ) {
            return (
                <p className={ "frm-block-spinner" }>
                    <Spinner/>
                    { __( `Loading ${ itemNamePlural }` ) }
                </p>
            );
        }
        return (
            <SelectControl
                value={ selected }
                options={
                    this.createOptions( items, itemName )
                }
                label={ label }
                help={ help }
                onChange={ onChange }
                className={ className }
            />
        );
    }
}

ItemSelect.defaultProps = {
    itemName: 'item',
    itemNamePlural: 'items',
};

ItemSelect.propTypes = {
    selected: PropTypes.oneOfType( [
        PropTypes.string,
        PropTypes.number,
        PropTypes.array,
    ] ),//selected item or items
    items: PropTypes.array,//list of possible items
    onChange: PropTypes.func,
    itemName: PropTypes.string,//name for item in select label
    itemNamePlural: PropTypes.string,//plural of items, used in some labels
    label: PropTypes.string,
    help: PropTypes.string,
    multiple: PropTypes.bool,//true if multiple selections can be made
    dependent: PropTypes.bool,//boolean, whether this data is dependent on the presence of the form id
    form_id: PropTypes.oneOfType( [
        PropTypes.string,
        PropTypes.number,
    ] ),//form id (or other data on which this form is dependent)
    complete: PropTypes.bool,//boolean -- whether data retrieval from the store has completed or not
};