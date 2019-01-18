/**
 * Panel with fields
 */
const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

import Field from '../components/fields/textfieldactive';

export default class FieldsPanel extends Component {

	render() {
		return (
			<div className={ 'frm-fields-panel' }>
				<h3>Sample field</h3>
				<Field />
			</div>
		);
	}
}