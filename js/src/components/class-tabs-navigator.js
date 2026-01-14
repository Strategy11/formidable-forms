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
		this.navs = this.wrapper.querySelectorAll( '.frm-tabs-navs ul > li' );
		this.slideTrackLine = this.wrapper.querySelector( '.frm-tabs-active-underline' );
		this.slideTrack = this.wrapper.querySelector( '.frm-tabs-slide-track' );
		this.slides = this.wrapper.querySelectorAll( '.frm-tabs-slide-track > div' );
		this.isRTL = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
		this.resizeObserver = null;

		this.init();
	}

	init() {
		if ( null === this.wrapper || ! this.navs.length || null === this.slideTrackLine || null === this.slideTrack || ! this.slides.length ) {
			return;
		}

		this.navs.forEach( ( nav, index ) => {
			nav.addEventListener( 'click', event => this.onNavClick( event, index ) );
			if ( nav.classList.contains( 'frm-active' ) ) {
				this.initSlideTrackUnderline( nav );
			}
		} );
		this.slideTrackLine.style.display = 'block';

		this.setupScrollbarObserver();
		// Cleanup observers when page unloads to prevent memory leaks
		window.addEventListener( 'beforeunload', this.cleanupObservers );
	}

	onNavClick( event, index ) {
		const navItem = event.currentTarget;

		event.preventDefault();

		this.removeActiveClassnameFromNavs();
		navItem.classList.add( 'frm-active' );
		this.initSlideTrackUnderline( navItem );
		this.changeSlide( index );

		// Handle special case for frm_insert_fields_tab
		const navLink = navItem.querySelector( 'a' );
		if ( navLink && navLink.id === 'frm_insert_fields_tab' && ! navLink.closest( '#frm_adv_info' ) ) {
			window.frmAdminBuild?.clearSettingsBox?.();
		}
	}

	initSlideTrackUnderline( nav ) {
		this.slideTrackLine.classList.remove( 'frm-first', 'frm-last' );
		const activeNav = 'undefined' !== typeof nav ? nav : this.navs.filter( nav => nav.classList.contains( 'frm-active' ) );
		this.positionUnderlineIndicator( activeNav );
	}

	/**
	 * Sets up a ResizeObserver to watch for scrollbar changes in the parent container.
	 * Automatically repositions the underline indicator when layout changes occur.
	 */
	setupScrollbarObserver() {
		const resizeObserverTarget = document.querySelector( '.frm-scrollbar-wrapper, .styling_settings' ) || document.body;
		if ( ! resizeObserverTarget || ! ( 'ResizeObserver' in window ) ) {
			return;
		}

		this.resizeObserver = new ResizeObserver( () => {
			const activeNav = this.wrapper.querySelector( '.frm-tabs-navs ul > li.frm-active' );
			if ( activeNav ) {
				this.positionUnderlineIndicator( activeNav );
			}
		} );
		this.resizeObserver.observe( resizeObserverTarget );
	}

	/**
	 * Cleans up observers to prevent memory leaks.
	 */
	cleanupObservers() {
		if ( this.resizeObserver ) {
			this.resizeObserver.disconnect();
			this.resizeObserver = null;
		}
	}

	/**
	 * Positions the underline indicator based on the active navigation element.
	 *
	 * @param {HTMLElement} activeNav The active navigation element to position the underline under
	 */
	positionUnderlineIndicator( activeNav ) {
		requestAnimationFrame( () => {
			const position = this.isRTL
				? -( activeNav.parentElement.offsetWidth - activeNav.offsetLeft - activeNav.offsetWidth )
				: activeNav.offsetLeft;

			this.slideTrackLine.style.transform = `translateX(${ position }px)`;
			this.slideTrackLine.style.width = activeNav.clientWidth + 'px';
		} );
	}

	changeSlide( index ) {
		this.removeActiveClassnameFromSlides();
		const translate = index == 0 ? '0px' : `calc( ( ${ ( index * 100 ) }% + ${ parseInt( this.flexboxSlidesGap, 10 ) * index }px ) * ${ this.isRTL ? 1 : -1 } )`;
		if ( '0px' !== translate ) {
			this.slideTrack.style.transform = `translateX(${ translate })`;
		} else {
			this.slideTrack.style.removeProperty( 'transform' );
		}
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
