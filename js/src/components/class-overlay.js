import DOMPurify from 'dompurify';
export class FrmOverlay {

	constructor() {
		this.body = document.querySelector( 'body' );
	}

	open( overlayData ) {
		this.overlayData = {
			heroImage: null,
			heading: null,
			copy: null,
			buttons: []
		};

		this.overlayData = { ...this.overlayData, ...overlayData };
		this.body.insertAdjacentHTML( 'afterbegin', this.buildOverlay() );
		this.initCloseButton();
		this.initOverlayIntroAnimation( 200 );

	}

	close() {
		const overlayWrapper = document.querySelector( '.frm-overlay--wrapper' );
		if ( overlayWrapper ) {
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
		if ( this.overlayData.heroImage ) {
			return `<img src="${DOMPurify.sanitize( this.overlayData.heroImage )}"/>`;
		}
		return '';
	}

	getButtons() {
		const buttons = this.overlayData.buttons.map( ( button ) => {
			if ( ! button.url || '' === button.url ) {
				return '';
			};

			const target = button.target ? `target=${DOMPurify.sanitize( button.target )}` : '';
			return `<a href="${DOMPurify.sanitize( button.url ) }" ${target} >${DOMPurify.sanitize( button.label )}</a>`;
		});

		if ( buttons ) {
			return `<div class="frm-overlay--cta">${buttons.join( '' )}</div>`;
		}

		return '';
	}

	getHeading() {
		if ( this.overlayData.heading )	{
			return `<h2 class="frm-overlay--heading">${ DOMPurify.sanitize( this.overlayData.heading ) }</h2>`;
		}
		return '';
	}

	getCopy() {
		if ( this.overlayData.copy ) {
			return `<div class="frm-overlay--copy">${ DOMPurify.sanitize( this.overlayData.copy ) }</div>`;
		}
		return '';
	}

	initOverlayIntroAnimation( delay ) {
		const overlayWrapper = document.querySelector( '.frm-overlay--wrapper' );
		if ( overlayWrapper ) {
			setTimeout( () => {
				overlayWrapper.classList.add( 'frm-active' );
			}, delay );
		}
	}

	buildOverlay() {
		return `
			<div class="frm-overlay--wrapper frm_wrap">
				<div class="frm-overlay--container">
					<div class="frm-overlay--hero-image">${this.getHeroImage()}</div>
					${this.getHeading()}
					${this.getCopy()}
					${this.getButtons()}
				</div>
			</div>`;
	}
}
