/**
 * Form selector
 */
import ItemSelect from "../common/components/itemselect";
import PropTypes from 'prop-types';
import createOptions from "../common/utilities/getselectoptions";
import { filterForms } from "../common/utilities/values";

const { __ } = wp.i18n;
const {
	Component
} = wp.element;

export default class FormSelect extends Component {
	constructor( props ) {
		super( ...arguments );
	}

	render() {
		const {
			form_id,
			setAttributes,
		} = this.props;

		let forms = formidable_form_selector.forms;
		forms = filterForms( forms );

		return (
			<ItemSelect
				selected={ form_id }
				itemName={ __( 'form' ) }
				itemNamePlural={ __( 'forms' ) }
				items={ createOptions( forms ) }
				onChange={ new_form_id => {
					setAttributes( {
						form_id: new_form_id,
					} )
				} }
			>
			</ItemSelect>
		);
	}
}

FormSelect.propTypes = {
	form_id: PropTypes.string,//current form id
	setAttributes: PropTypes.func.isRequired,//setAttributes of block
};
