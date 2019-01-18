/**
 *  Color Picker wrapper where we can choose a color picker and set defaults
 */
const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

import { SketchPicker } from 'react-color';

export default class FrmColorPicker extends Component {

	render() {

		const {
			color,
			updateState,
			property,
			className,
		} = this.props;

		return (
			<div className={ className }>
				<SketchPicker
					color={ color }
					onChangeComplete={ ( newColor ) => {
						updateState( {
							[ property ]: `rgba( ${newColor.rgb.r}, ${newColor.rgb.g} , ${newColor.rgb.b}, ${newColor.rgb.a} )`,
							}
						);
					}
					}
				/>
			</div>
		);
	}
}