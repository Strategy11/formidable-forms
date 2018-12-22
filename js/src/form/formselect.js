/**
 * Form selector
 */
import ItemSelect from '../common/components/itemselect';
import PropTypes from 'prop-types';

const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

export default class FormSelect extends Component {
	render() {
		const {
			form_id,
			setAttributes,
			forms,
		} = this.props;

		return (
			<ItemSelect
				selected={ form_id }
				itemName={ __( 'form', 'formidable' ) }
				itemNamePlural={ __( 'forms', 'formidable' ) }
				items={ forms }
				onChange={ newFormId => {
					setAttributes( {
						form_id: newFormId,
					} );
				} }
			>
			</ItemSelect>
		);
	}
}

FormSelect.propTypes = {
	form_id: PropTypes.string, //current form id
	setAttributes: PropTypes.func.isRequired, //setAttributes of block
};
