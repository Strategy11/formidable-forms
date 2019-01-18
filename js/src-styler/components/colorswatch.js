//adapted from SketchExample on https://casesandberg.github.io/react-color/
'use strict';

const {
	Component,
} = wp.element;

import { SketchPicker } from 'react-color';

export default class ColorSwatch extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			displayColorPicker: false,
		};
		this.handleClick = this.handleClick.bind( this );
		this.handleClose = this.handleClose.bind( this );
		this.handleChange = this.handleChange.bind( this );
	}

	handleClick() {
		this.setState( { displayColorPicker: ! this.state.displayColorPicker } );
	};

	handleClose() {
		this.setState( { displayColorPicker: false } );
	};

	handleChange( newColor, property, updateState ) {
		updateState( {
				[ property ]: `rgba( ${ newColor.rgb.r }, ${ newColor.rgb.g } , ${ newColor.rgb.b }, ${ newColor.rgb.a } )`,
			}
		);
	};

	render() {

		const {
			color,
			property,
			updateState,
		} = this.props;

		const styles = {
			color: {
				width: '36px',
				height: '14px',
				borderRadius: '2px',
				backgroundColor: color,
			},
			swatch: {
				padding: '5px',
				backgroundColor: '#fff',
				borderRadius: '1px',
				boxShadow: '0 0 0 1px rgba(0,0,0,.1)',
				display: 'inline-block',
				cursor: 'pointer',
			},
			popover: {
				position: 'absolute',
				zIndex: '2',
			},
			cover: {
				position: 'fixed',
				top: '0px',
				right: '0px',
				bottom: '0px',
				left: '0px',
			},
		};

		return (
			<div>
				<div style={ styles.swatch } onClick={ this.handleClick }>
					<div style={ styles.color }/>
				</div>
				{ this.state.displayColorPicker ? <div style={ styles.popover }>
					<div style={ styles.cover } onClick={ this.handleClose }/>
					<SketchPicker color={ color } onChange={ ( newColor ) => this.handleChange( newColor, property, updateState ) } />
				</div> : null }
			</div>
		)
	}
}
