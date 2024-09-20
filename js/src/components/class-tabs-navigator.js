export class frmTabsNavigator {

	constructor( wrapper ) {
		if ( 'undefined' === typeof wrapper ) {
			return;
		}

		this.wrapper = wrapper instanceof Element ? wrapper : document.querySelector( wrapper );

		if ( null === this.wrapper ) {
			return;
		}

		this.flexboxSlidesGap = '16px';
		this.navs             = this.wrapper.querySelectorAll( '.frm-tabs-navs ul > li' );
		this.slideTrackLine   = this.wrapper.querySelector( '.frm-tabs-active-underline' );
		this.slideTrack       = this.wrapper.querySelector( '.frm-tabs-slide-track' );
		this.slides           = this.wrapper.querySelectorAll( '.frm-tabs-slide-track > div' );

		this.init();
	}

	init() {
		if ( null === this.wrapper || ! this.navs.length || null === this.trackLine || null === this.slideTrack || ! this.slides.length ) {
			return;
		}

		this.initDefaultSlideTrackerWidth();
		this.navs.forEach( ( nav, index ) => {
			nav.addEventListener( 'click', event => this.onNavClick( event, index ) );
		});
	}

	onNavClick( event, index ) {
		this.removeActiveClassnameFromNavs();
		event.target.classList.add( 'frm-active' );
		this.initSlideTrackUnderline( event.target, index );
		this.changeSlide( index );
	}

	initDefaultSlideTrackerWidth() {
		if ( ! this.slideTrackLine.dataset.initialWidth ) {
			return;
		}
		this.slideTrackLine.style.width = `${this.slideTrackLine.dataset.initialWidth}px`;
	}
	initSlideTrackUnderline( nav, index ) {
		this.slideTrackLine.classList.remove( 'frm-first', 'frm-last' );
		const activeNav = 'undefined' !== typeof nav ? nav : this.navs.filter( nav => nav.classList.contains( 'frm-active' ) ) ;
		this.slideTrackLine.style.transform = `translateX(${activeNav.offsetLeft}px)`;
		this.slideTrackLine.style.width = activeNav.clientWidth + 'px';

		if ( this.navs.length === index + 1 ) { 
			this.slideTrackLine.classList.add( 'frm-last' );
			return;
		}
		if ( 0 === index ) {
			this.slideTrackLine.classList.add( 'frm-first' );
		}
	}

	changeSlide( index ) {
		this.removeActiveClassnameFromSlides();
		const translate = index == 0 ? '0px' : `calc( ( ${( index * 100 )}% + ${parseInt( this.flexboxSlidesGap, 10 ) * index }px ) * -1 )`;
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
