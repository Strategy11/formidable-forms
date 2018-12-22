/**
 * View Select
 *
 * Dropdown to select a View
 */

import ItemSelect from '../common/components/itemselect';
import PropTypes from 'prop-types';
import { updateViewId } from './utilities';

const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

export default class ViewSelect extends Component {
	render() {
		const {
			viewId,
			setAttributes,
			views,
		} = this.props;

		return (
			<ItemSelect
				selected={ viewId }
				itemName={ __( 'View', 'formidable' ) }
				itemNamePlural={ __( 'Views', 'formidable' ) }
				items={ views }
				onChange={ ( newViewId ) => {
					updateViewId( newViewId, setAttributes );
				} }
			>
			</ItemSelect>
		);
	}
}

ViewSelect.propTypes = {
	viewId: PropTypes.string, //current view id
	setAttributes: PropTypes.func.isRequired, //setAttributes of block
};
