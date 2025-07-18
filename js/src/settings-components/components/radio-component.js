/**
 * Internal dependencies
 */
import { HIDDEN_CLASS } from 'core/constants';
import { show, hide, isVisible } from 'core/utils';

/**
 * Represents a radio component.
 * @class
 */
export default class frmRadioComponent {

	constructor() {
		this.radioElements = document.querySelectorAll( '.frm-style-component.frm-radio-component' );
		if ( 0 < this.radioElements.length ) {
			this.init();
		}

		/**
		 * Handles the addition of new fields.
		 *
		 * @param {Event}       event          The frm_added_field event.
		 * @param {HTMLElement} event.frmField The added field object being destructured from the event.
		 */
		document.addEventListener( 'frm_added_field', ( { frmField } ) => {
			this.radioElements = document.getElementById( `frm-single-settings-${ frmField.dataset.fid }` )
				.querySelectorAll( '.frm-style-component.frm-radio-component' );

			this.initRadio();
		});
	}

	/**
	 * Initializes the radio component.
	 */
	init() {
		this.initRadio();
		this.initTrackerOnAccordionClick();
	}

	/**
	 * Initializes the radio component.
	 */
	initRadio() {
		this.radioElements.forEach( ( element ) => {
			this.initOnRadioChange( element );
			this.initVisibilityObserver( element );
		});
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
	 * Initializes visibility observer for the radio component. This handles cases when components are conditionally shown.
	 *
	 * @param {HTMLElement} element The radio component element
	 * @return {void}
	 */
	initVisibilityObserver( element ) {
		const observer = new MutationObserver( () => {
			// Check if element is now visible
			if ( isVisible( element ) ) {
				const radio = element.querySelector( 'input[type="radio"]:checked' );
				if ( radio ) {
					this.onRadioChange( radio );
				}
			}
		});

		// Observe for attribute changes on the component and its ancestors
		observer.observe( element, {
			attributes: true,
			attributeFilter: [ 'class', 'style' ]
		});

		// Also observe parent elements up to a reasonable depth
		let parent = element.parentElement;
		for ( let i = 0; i < 7 && parent; i++ ) {
			observer.observe( parent, {
				attributes: true,
				attributeFilter: [ 'class', 'style' ]
			});
			parent = parent.parentElement;
		}
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
			element.classList.add( HIDDEN_CLASS );
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
