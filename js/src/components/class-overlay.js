/**
 * External dependencies
 */
import { frmAnimate } from 'core/utils';

export class frmOverlay {

	constructor() {
		this.body = document.body;
	}

	/**
	 * Open overlay
	 *
	 * @param {Object} overlayData                  An object containing data for the overlay.
	 * @param {string} overlayData.hero_image       URL of the hero image.
	 * @param {string} overlayData.heading          Heading of the overlay.
	 * @param {string} overlayData.copy             Copy/content of the overlay.
	 * @param {Array}  overlayData.buttons          Array of button objects.
	 * @param {string} overlayData.buttons[].url    URL for the button.
	 * @param {string} overlayData.buttons[].target Target attribute for the button link.
	 * @param {string} overlayData.buttons[].label  Label/text of the button.
	 */
	open( overlayData ) {
		this.overlayData = {
			hero_image: null,
			heading: null,
			copy: null,
			buttons: []
		};

		this.overlayData = { ...this.overlayData, ...overlayData };
		this.bodyAddOverflowHidden();
		this.body.insertBefore( this.buildOverlay(), this.body.firstChild );
		this.initCloseButton();
		this.initOverlayIntroAnimation( 200 );

	}

	bodyAddOverflowHidden() {
		this.body.classList.add( 'frm-hidden-overflow' );
		setTimeout( () => {
			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
		}, 80 );
	}

	close() {
		const overlayWrapper = document.querySelector( '.frm-overlay--wrapper' );
		if ( overlayWrapper ) {
			document.body.classList.remove( 'frm-hidden-overflow' );
			overlayWrapper.remove();
		}
	}

	initCloseButton() {
		const overlayWrapper = document.querySelector( '.frm-overlay--wrapper' );

		if ( overlayWrapper ) {
			const closeButton = document.createElement( 'span' );
			closeButton.classList.add( 'frm-overlay--close' );
			closeButton.addEventListener( 'click', this.close );
			overlayWrapper.prepend( closeButton );
		}
	}

	getHeroImage() {
		if ( this.overlayData.hero_image ) {
			return frmDom.img({ src: this.overlayData.hero_image });
		}
		return '';
	}

	getButtons() {
		const buttons = this.overlayData.buttons.map( ( button, index ) => {
			if ( ! button.url || '' === button.url ) {
				return '';
			};
			const buttonTypeClassname = 1 === index ? 'frm-button-primary' : 'frm-button-secondary';
			const options = {
				href: button.url,
				text: button.label,
				className: 'button frm_animate_bg ' + buttonTypeClassname
			};
			if ( button.target ) {
				options.target = button.target;
			}
			return frmDom.a( options );
		});

		if ( buttons ) {
			const buttonsWrapperElementOptions = { className: 'frm-overlay--cta frm-flex-box', children: buttons };
			return frmDom.div( buttonsWrapperElementOptions );
		}

		return '';
	}

	getHeading() {
		if ( this.overlayData.heading )	{
			return frmDom.tag( 'h2', { className: 'frm-overlay--heading frm-text-xl', text: this.overlayData.heading });
		}
		return '';
	}

	getCopy() {
		if ( this.overlayData.copy ) {
			const copy = frmDom.tag( 'div' );
			copy.innerHTML = this.overlayData.copy;
			return frmDom.div({ className: 'frm-overlay--copy', child: copy });
		}
		return '';
	}

	initOverlayIntroAnimation( delay ) {
		setTimeout( () => {
			const elements = document.querySelectorAll( '.frm-overlay--hero-image, .frm-overlay--heading, .frm-overlay--copy, .frm-overlay--cta a' );
			new frmAnimate( elements, 'cascade-3d' ).cascadeFadeIn( 0.07 );
		}, delay );
	}

	buildOverlay() {
		const container = frmDom.div({
			className: 'frm-overlay--container',
			children: [
				frmDom.div({className: 'frm-overlay--hero-image frm-mb-md', children: [ this.getHeroImage() ] }),
				this.getHeading(),
				this.getCopy(),
				this.getButtons()
			]
		});

		return  frmDom.div({ className: 'frm-overlay--wrapper frm_wrap', children: [ container ] });
	}
}
