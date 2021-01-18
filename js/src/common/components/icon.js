/**
 * Formidable Forms icon
 */
const { Component } = wp.element;
const { Dashicon } = wp.components;

export default class FormidableIcon extends Component {
	render() {
		if ( formidable_form_selector.icon !== 'svg' ) {
			return <Dashicon icon={ formidable_form_selector.icon } size="120" />;
		}

		return (
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 599.68 601.37" width="120" height="120">
				<path className="cls-1 orange" d="M289.6 384h140v76h-140z"/>
				<path className="cls-1" d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264z"/>
				<path className="cls-1" d="M299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z"/>
			</svg>
		);
	}
}
