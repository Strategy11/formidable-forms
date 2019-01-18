/**
 * Main React Styler Panel
 */

const { __ } = wp.i18n;
const {
	Component,
} = wp.element;

import SettingsPanel from './settingspanel';
import FieldPanel from './fieldpanel';
import Color2Panel from './color2';
import returnCss from '../css/field';

export default class MainPanel extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			//color: '#555555',
			color: 'rgba(85,85,85,1)',
			backgroundColor: 'rgba(255,255,255,1)',
			borderColor: 'rgba(204,204,204,1)',
			colorProperty: 'color', //the color property currently being set
			borderWidth: '1px',
			borderStyle: 'solid',
		};

		this.updateState = this.updateState.bind( this );
	}

	updateState( newState ) {
		this.setState( newState );
	}

	render() {
		return (
			<div className={ 'frm-react-styler' }>
				<h2>React Field Settings Tester</h2>
				<div className={ 'frm-main-panel' }>
					<SettingsPanel
						{ ...this.state }
						updateState={ this.updateState }
					/>
					<FieldPanel></FieldPanel>
					<Color2Panel
						{ ...this.state }
						updateState={ this.updateState }
					/>
				</div>
				<style type="text/css">{ returnCss( this.state ) }</style>
			</div>
		);
	}
}
