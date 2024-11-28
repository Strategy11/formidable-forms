import { show, hide } from 'core/utils';

/**
 * Represents a radio style component.
 * @class
 */
export default class frmRadioStyleComponent {

	constructor() {
		this.radioElements = document.querySelectorAll( '.frm-style-component.frm-radio-component' );
		if ( 0 < this.radioElements.length ) {
			this.init();
		}
	}

	/**
	 * Initializes the radio style component.
	 */
	init() {
		this.radioElements.forEach( ( element ) => {
			this.initOnRadioChange( element );
		});
		this.initTrackerOnAccordionClick();
	}

	initTrackerOnAccordionClick() {
		const accordionitems = document.querySelectorAll( '#frm_style_sidebar .accordion-section h3' );

		accordionitems.forEach( ( accordionitem ) => {
			accordionitem.addEventListener( 'click', ( event ) => {
				const wrapper      = event.target.closest( '.accordion-section' );
				const radioButtons = wrapper.querySelectorAll( '.frm-style-component.frm-radio-component input[type="radio"]:checked' );

				radioButtons.forEach( ( radio ) => {
					setTimeout( () => this.onRadioChange( radio ), 200 );
				});
			});
		});
	}

	/**
	 * Initializes the onRadioChange event for the given wrapper.
	 * @param {HTMLElement} radioElement - The radio element.
	 */
	initOnRadioChange( radioElement ) {
		radioElement.querySelectorAll( 'input[type="radio"]' ).forEach( ( radio ) => {
			if ( radio.checked ) {
				this.onRadioChange( radio );
			}
			radio.addEventListener( 'change', ( event ) => {
				this.onRadioChange( event.target );
			});
		});
	}

	/**
	 * Handles the onRadioChange event for the given wrapper.
	 * @param {HTMLElement} target - The active radio button.
	 */
	onRadioChange( target ) {
		const wrapper    = target.closest( '.frm-style-component.frm-radio-component' );
		const activeItem = wrapper.querySelector( 'input[type="radio"]:checked + label' );

		if ( null === activeItem ) {
			return;
		}

		this.moveTracker( activeItem, wrapper );
		this.hideExtraElements( target );
		this.maybeShowExtraElements( target );
	}

	/**
	 * Display additional elements related to the selected radio option.
	 * @param {HTMLElement} radio - The radio button element.
	 */
	maybeShowExtraElements( radio ) {
		const elementAttr = radio.getAttribute( 'data-frm-show-element' );
		if ( null === elementAttr ) {
			return;
		}

		const elements = document.querySelectorAll( `div[data-frm-element="${elementAttr}"]` );

		if ( 0 === elements.length ) {
			return;
		}

		elements.forEach( ( element ) => {
			show( element );
			element.classList.add( 'frm-element-is-visible' );
		});
	}

	/**
	 * Hide the possible opepend extra elements.
	 */
	hideExtraElements() {
		const elements = document.querySelectorAll( '.frm-element-is-visible' );
		if ( 0 === elements.length ) {
			return;
		}
		elements.forEach( ( element ) => {
			element.classList.remove( 'frm-element-is-visible' );
			element.classList.add( 'frm_hidden' );
			hide( element );
		});
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
		tracker.style.width = `${width}px`;
		tracker.style.transform = `translateX(${ offset }px)`;
	}
}
