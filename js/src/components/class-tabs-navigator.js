export class frmTabsNavigator {

	constructor( wrapper ) {
		if ( 'undefined' === typeof wrapper ) {
			return;
		}
		this.flexboxSlidesGap = '16px';
		this.wrapper          = document.querySelector( wrapper );
		this.navs             = this.wrapper.querySelectorAll( '.frm-tabs-navs ul > li' );
		this.slideTrackLine   = this.wrapper.querySelector( '.frm-tabs-active-underline' );
		this.slideTrack       = this.wrapper.querySelector( '.frm-tabs-slide-track' );
		this.slides           = this.wrapper.querySelectorAll( '.frm-tabs-slide-track > div' );

		this.init();
	}

	init() {
		if ( null === this.wrapper || null === this.navs || null === this.trackLine || null === this.slideTrack || null === this.slides ) {
			return;
		}
		this.navs.forEach( ( nav, index ) => {
			nav.addEventListener( 'click', event => this.onNavClick( event, index ) );
		});
	}

	onNavClick( event, index ) {
		this.removeActiveClassnameFromNavs();
		event.target.classList.add( 'frm-active' );
		this.initSlideTrackUnterline( event.target );
		this.changeSlide( index );
	}

	initSlideTrackUnterline( nav ) {
		const activeNav = 'undefined' !== typeof nav ? nav : this.navs.filter( nav => nav.classList.contains( 'frm-active' ) ) ;
		this.slideTrackLine.style.transform = `translateX(${activeNav.offsetLeft}px)`;
		this.slideTrackLine.style.width = activeNav.offsetWidth + 'px';
	}

	changeSlide( index ) {
		this.removeActiveClassnameFromSlides();
		const translate = index == 0 ? '0px' : `calc( ( ${( index * 100 )}% + ${this.flexboxSlidesGap} ) * -1 )`;
		this.slideTrack.style.transform = `translateX(${translate})`;
		if ( index in this.slides ) {
			this.slides[ index ].classList.add( 'frm-active' );
		}
	}

	removeActiveClassnameFromSlides() {
		this.slides.forEach( slide => slide.classList.remove( 'frm-active' ) );
	}

	removeActiveClassnameFromNavs() {
		this.navs.forEach( nav => nav.classList.remove( 'frm-active' ) );
	}
}
