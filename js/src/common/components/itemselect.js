const { __ } = wp.i18n;
import PropTypes from 'prop-types';

const {
	Component,
} = wp.element;
const {
	SelectControl,
} = wp.components;

/**
 * Displays a Select control with the specified items as options
 *
 */
export default class ItemSelect extends Component {
	createOptions( items, itemName ) {
		const options = items.map( item => {
			return {
				label: item.label,
				value: item.value,
			};
		} );

		return [
			{
				label: __( 'Select a ', 'formidable' ) + itemName,
				value: '',
			},
			...options,
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
		} = this.props;

		if ( ( ! items || items.length === 0 ) ) {
			return (
				<p className={ 'frm-block-select-no-items' }>
					{ __( 'Currently, there are no ', 'formidable' ) + itemNamePlural }
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
	] ), //selected item
	items: PropTypes.array, //list of possible items
	onChange: PropTypes.func,
	itemName: PropTypes.string, //name for item in select label
	itemNamePlural: PropTypes.string, //plural of items, used in some labels
	label: PropTypes.string,
	help: PropTypes.string,
};
