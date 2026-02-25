/**
 * This component updates the dependent style element's values and triggers a custom change event for each style element, initiating the style preview.
 * The names of the elements that will be updated are specified using the "will-change" attribute.
 * It is primarily used in components from Style/Quick Settings.
 * For instance, when the "FrmPrimaryColorStyleComponent" is changed, it simultaneously updates various style elements like border color, text color, and button backgrounds.
 */
export default class frmStyleDependentUpdaterComponent {
	/**
	 * Creates an instance of frmStyleDependentUpdaterComponent.
	 *
	 * @param {HTMLElement} component - The component element.
	 */
	constructor( component ) {
		this.component = component;
		try {
			const willChangeData = JSON.parse( this.component.dataset.willChange );
			this.data = {
				propagateInputs: this.initPropagationList( willChangeData ),
				changeEvent: new Event( 'change', { bubbles: true } )
			};
		} catch ( error ) {
			console.error( 'Error parsing JSON data from "will-change" attribute.', error );
		}
	}

	/**
	 * Initializes the list of inputs to propagate changes to.
	 * The selection is made by provided input's names list in "will-change" attribute.
	 *
	 * @param {string[]} inputNames - The names of the inputs to propagate changes to.
	 * @return {HTMLElement[]} - The list of inputs to propagate changes to.
	 */
	initPropagationList( inputNames ) {
		const list = [];
		inputNames.forEach( name => {
			const input = document.querySelector( `input[name="${ name }"]` );
			if ( null !== input ) {
				list.push( input );
			}
		} );
		return list;
	}

	/**
	 * Updates all dependent elements with the given value.
	 *
	 * @param {string} value - The value to update the dependent elements with.
	 */
	updateAllDependentElements( value ) {
		this.data.propagateInputs.forEach( input => {
			input.value = value;
		} );
		this.data.propagateInputs[ 0 ].dispatchEvent( this.data.changeEvent );
	}
}
