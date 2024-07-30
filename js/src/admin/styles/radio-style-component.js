/**
 * Represents a radio style component.
 * @class
 */
export default class frmRadioStyleComponent {

	constructor() {
		this.elements = document.querySelectorAll( '.frm-style-component.frm-radio-component' );
		if ( 0 === this.elements.length ) {
			return;
		}

		this.init();
	}

	/**
	 * Initializes the radio style component.
	 */
	init() {
		this.elements.forEach( ( element ) => {
			this.initOnRadioChange( element );
		});
	}

	/**
	 * Initializes the onRadioChange event for the given wrapper.
	 * @param {HTMLElement} wrapper - The wrapper element.
	 */
	initOnRadioChange( wrapper ) {
		wrapper.querySelectorAll( 'input[type="radio"]' ).forEach( ( radio ) => {
			radio.addEventListener( 'change', ( event ) => {
				this.onRadioChange( event.target.closest( '.frm-style-component.frm-radio-component' ) );
			});
		});
	}

	/**
	 * Handles the onRadioChange event for the given wrapper.
	 * @param {HTMLElement} wrapper - The wrapper element.
	 */
	onRadioChange( wrapper ) {
		const activeItem = wrapper.querySelector( 'input[type="radio"]:checked + label' );
		this.moveTracker( activeItem, wrapper );
	}

	/**
	 * Gets the index of the radio button.
	 * @param {HTMLElement} radio - The radio button element.
	 * @return {number} The index of the radio button.
	 */
	getRadioIndex( radio ) {
		const radioButtons = Array.from( wrapper.querySelectorAll( 'input[type="radio"]' ) );
		return radioButtons.indexOf( radio );
	}

	/**
	 * Moves the tracker to the active item.
	 * @param {HTMLElement} activeItem - The active item element.
	 * @param {HTMLElement} wrapper    - The wrapper element.
	 */
	moveTracker( activeItem, wrapper ) {
		const offset  = activeItem.offsetLeft;
		const width   = activeItem.offsetWidth;
		const tracker = wrapper.querySelector( '.frm-radio-active-tracker' );

		tracker.style.left = 0;
		tracker.style.width = width;
		tracker.style.transform = `translateX(${ offset }px)`;
	}
}