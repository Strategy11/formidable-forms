/**
 * Settings panel
 */
const { __ } = wp.i18n;
const {
	Component,
} = wp.element;
const {
	TextControl,
} = wp.components;

import FrmColorPicker from '../components/colorPicker';
import ColorSwatch from '../components/colorswatch';
import ItemSelect from '../../src/common/components/itemselect';
import { colorOptions, borderStyleOptions} from '../options/field';

export default class SettingsPanel extends Component {
	render() {
		const {
			color,
			backgroundColor,
			borderColor,
			updateState,
			colorProperty,
			borderStyle,
			borderWidth,
		} = this.props;

		return (
			<div className={ 'frm-settings-panel' }>
				<h3>Field Settings</h3>
				<h3>Colors -- Swatch Approach</h3>
				<h4>Text color</h4>
				<ColorSwatch
					color={ color }
					property={ 'color' }
					updateState={ updateState }
				/>
				<h4>Background color</h4>
				<ColorSwatch
					color={ backgroundColor }
					property={ 'backgroundColor' }
					updateState={ updateState }
				/>
				<h4>Border color</h4>
				<ColorSwatch
					color={ borderColor }
					property={ 'borderColor' }
					updateState={ updateState }
				/>
				<ItemSelect
					selected={ borderStyle }
					label={ 'border style' }
					items={ borderStyleOptions }
					itemName={ 'border style' }
					itemNames={ 'border styles' }
					onChange={ ( newBorderStyle ) => {
						updateState( { borderStyle: newBorderStyle } );
					} }
				/>
				<TextControl
					label="border width"
					value={ borderWidth }
					onChange={ ( newBorderWidth ) => {
						updateState( { borderWidth: newBorderWidth } );
					} }
				/>
			</div>
		);
	}
}
