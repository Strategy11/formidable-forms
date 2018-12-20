/**
 * View Select
 *
 * Dropdown to select a View
 */

import ItemSelect from "../common/components/itemselect";
import PropTypes from 'prop-types';
import createOptions from "../common/utilities/getselectoptions";
import { updateViewId } from "./utilities";

const { __ } = wp.i18n;
const {
	Component
} = wp.element;

export default class ViewSelect extends Component {
	constructor( props ) {
		super( ...arguments );
	}

	render() {
		const {
			view_id,
			setAttributes,
		} = this.props;

		let views = formidable_form_selector.views;

		return (
			<ItemSelect
				selected={ view_id }
				itemName={ __( 'View' ) }
				itemNamePlural={ __( 'Views' ) }
				items={ createOptions( views, 'post_title', 'ID' ) }
				onChange={ ( new_view_id ) => {
					updateViewId( new_view_id, setAttributes );
				} }
			>
			</ItemSelect>
		);
	}
}

ViewSelect.propTypes = {
	view_id: PropTypes.string,//current view id
	setAttributes: PropTypes.func.isRequired,//setAttributes of block
};
