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
			formId,
			setAttributes,
			forms,
		} = this.props;

		return (
			<ItemSelect
				selected={ formId }
				itemName={ __( 'form', 'formidable' ) }
				itemNamePlural={ __( 'forms', 'formidable' ) }
				items={ forms }
				onChange={ newFormId => {
					setAttributes( {
						formId: newFormId,
					} );
				} }
			>
			</ItemSelect>
		);
	}
}

FormSelect.propTypes = {
	formId: PropTypes.string, //current formId
	setAttributes: PropTypes.func.isRequired, //setAttributes of block
};
