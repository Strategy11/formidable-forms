import { applyContentFilter, hasFilterableGroups } from './filter.js';
import { observeVisibility, disconnectVisibilityObserver } from 'core/utils/visibilityObserver';

export class frmTabsNavigator {
	constructor( wrapper ) {
		if ( wrapper === undefined ) {
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
		this.filterTarget = this.wrapper.dataset.filterTarget
			? document.querySelector( this.wrapper.dataset.filterTarget )
			: null;

		this.init();
	}

	init() {
		const isFilterMode = null !== this.filterTarget && hasFilterableGroups( this.filterTarget );
		const hasSlideTrack = null !== this.slideTrack && this.slides.length;

		if ( null === this.wrapper || ! this.navs.length || null === this.slideTrackLine || ( ! isFilterMode && ! hasSlideTrack ) ) {
			return;
		}

		this.navs.forEach( ( nav, index ) => {
			nav.addEventListener( 'click', event => this.onNavClick( event, index ) );
			if ( nav.classList.contains( 'frm-active' ) ) {
				this.initSlideTrackUnderline( nav );
				if ( this.filterTarget ) {
					applyContentFilter( this.filterTarget, nav.dataset.filter || 'all' );
				}
			}
		} );
		this.slideTrackLine.style.display = 'block';

		this.setupScrollbarObserver();
		this.setupVisibilityObserver();
		// Cleanup observers when page unloads to prevent memory leaks
		window.addEventListener( 'beforeunload', this.cleanupObservers );
	}

	onNavClick( event, index ) {
		const navItem = event.currentTarget;

		event.preventDefault();

		this.removeActiveClassnameFromNavs();
		navItem.classList.add( 'frm-active' );
		this.initSlideTrackUnderline( navItem );

		if ( this.filterTarget ) {
			applyContentFilter( this.filterTarget, navItem.dataset.filter || 'all' );
			return;
		}

		this.changeSlide( index );

		// Handle special case for frm_insert_fields_tab
		const navLink = navItem.querySelector( 'a' );
		if ( navLink && navLink.id === 'frm_insert_fields_tab' && ! navLink.closest( '#frm_adv_info' ) ) {
			window.frmAdminBuild?.clearSettingsBox?.();
		}
	}

	initSlideTrackUnderline( nav ) {
		const activeNav = nav !== undefined ? nav : this.navs.filter( nav => nav.classList.contains( 'frm-active' ) );
		this.positionUnderlineIndicator( activeNav );
	}

	/** Repositions underline when wrapper becomes visible (handles hidden panels). */
	setupVisibilityObserver() {
		observeVisibility( this.wrapper, () => {
			const activeNav = this.wrapper.querySelector( '.frm-tabs-navs ul > li.frm-active' );
			if ( activeNav ) {
				this.positionUnderlineIndicator( activeNav );
			}
		} );
	}

	/** Repositions underline when parent container resizes (scrollbar changes). */
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

	/** Cleans up observers to prevent memory leaks. */
	cleanupObservers() {
		if ( this.resizeObserver ) {
			this.resizeObserver.disconnect();
			this.resizeObserver = null;
		}
		disconnectVisibilityObserver();
	}

	/** @param {HTMLElement} activeNav The active nav element to position underline under. */
	positionUnderlineIndicator( activeNav ) {
		requestAnimationFrame( () => {
			const position = this.isRTL
				? -( activeNav.parentElement.offsetWidth - activeNav.offsetLeft - activeNav.offsetWidth )
				: activeNav.offsetLeft;

			this.slideTrackLine.style.transform = `translateX(${ position }px)`;
			this.slideTrackLine.style.width = `${ activeNav.clientWidth }px`;
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
