import ItemSelect from '../../src/common/components/itemselect';
import { colorOptions } from '../options/field';
import FrmColorPicker from '../components/colorPicker';

const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

export default class SettingPanel extends Component {
	render() {
		const {
			color,
			backgroundColor,
			borderColor,
			borderWidth,
			borderStyle,
			colorProperty,
			updateState,
		} = this.props;

		return (
			<div className={ 'frm-values-panel' }>
				<h2>Field Settings</h2>
				<h3>Colors -- Single Color Picker Approach</h3>
				<div>
				<ItemSelect
					selected={ colorProperty }
					label={ 'Which color property would you like to set?' }
					items={ colorOptions }
					itemName={ 'color property' }
					itemNames={ 'color properties' }
					onChange={ ( newColorProperty ) => {
						updateState( { colorProperty: newColorProperty } );
					} }
				/>
				</div>
				<FrmColorPicker
					color={ this.props[this.props.colorProperty] }
					updateState={ updateState }
					property={ colorProperty }
				/>
			</div>
		);
	}
};
