/**
 * Formidable Forms icon
 */
const { Component } = wp.element;
const { Dashicon } = wp.components;

export default class FormidableIcon extends Component {

	loadCustomSvgIcon( ) {
		const icon = formidable_form_selector.icon;
		if ( icon.match( /frm_white_label_icon/ ) ) {
			return (
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="120" height="120">
					<path d="M18.1 1.3H2C.9 1.3 0 2 0 3V17c0 1 .8 1.9 1.9 1.9H18c1 0 1.9-.9 1.9-2V3.2c0-1-.8-1.9-1.9-1.9zM18 16.9H2a.2.2 0 0 1-.2-.3V3.4c0-.2 0-.3.2-.3H18c.1 0 .2.1.2.3v13.2c0 .2 0 .3-.2.3zm-1.6-3.6v1c0 .2-.3.4-.5.4H8a.5.5 0 0 1-.5-.5v-1c0-.2.2-.4.5-.4h7.8c.2 0 .4.2.4.5zm0-3.8v1c0 .2-.3.4-.5.4H8a.5.5 0 0 1-.5-.4v-1c0-.2.2-.4.5-.4h7.8c.2 0 .4.2.4.4zm0-3.7v1c0 .2-.3.4-.5.4H8a.5.5 0 0 1-.5-.5v-1c0-.2.2-.4.5-.4h7.8c.2 0 .4.2.4.5zm-9.9.5a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0zm0 3.7a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0zm0 3.8a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0z"></path>
				</svg>
			)
		}
		return false;
	}

	render() {

		if ( false !== this.loadCustomSvgIcon() ) {
			return this.loadCustomSvgIcon();
		}

		if ( formidable_form_selector.icon !== 'svg' ) {
			return <Dashicon icon={ formidable_form_selector.icon } />;
		}

		return (
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 599.68 601.37" width="120" height="120">
				<path className="cls-1 orange" d="M289.6 384h140v76h-140z" />
				<path className="cls-1" d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264z" />
				<path className="cls-1" d="M299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z" />
			</svg>
		);
	}
}
