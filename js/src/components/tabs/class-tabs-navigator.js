import { __ } from '@wordpress/i18n';
import { applyContentFilter, getFilterTarget } from './filter.js';
import { observeVisibility, disconnectVisibilityObserver } from 'core/utils/visibilityObserver';

let tablistCount = 0;

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
		this.filterTarget = getFilterTarget( this.wrapper );

		this.init();
	}

	init() {
		const isMissingTrackAndFilter = ! this.filterTarget && ( null === this.slideTrack || 0 === this.slides.length );

		if ( null === this.wrapper || ! this.navs.length || null === this.slideTrackLine || isMissingTrackAndFilter ) {
			return;
		}

		const navList = this.navs[ 0 ]?.parentElement;
		if ( navList ) {
			navList.setAttribute( 'role', this.filterTarget ? 'group' : 'tablist' );
			navList.setAttribute( 'aria-label', this.filterTarget ? __( 'Filters', 'formidable' ) : __( 'Sections', 'formidable' ) );
		}

		const activeIndex = Array.from( this.navs ).findIndex( nav => nav.classList.contains( 'frm-active' ) );
		const selectedIndex = Math.max( activeIndex, 0 );
		const root = this.wrapper.getRootNode();
		let idPrefix;
		do {
			idPrefix = `frm-tablist-${ ++tablistCount }`;
		} while ( root.getElementById( `${ idPrefix }-tab-0` ) || root.getElementById( `${ idPrefix }-panel-0` ) );

		this.navs.forEach( ( nav, index ) => {
			const isSelected = index === selectedIndex;
			nav.classList.toggle( 'frm-active', isSelected );
			nav.setAttribute( 'tabindex', this.filterTarget || isSelected ? '0' : '-1' );
			nav.setAttribute( 'role', this.filterTarget ? 'button' : 'tab' );
			nav.setAttribute( this.filterTarget ? 'aria-pressed' : 'aria-selected', String( isSelected ) );
			nav.querySelectorAll( 'a' ).forEach( anchor => anchor.setAttribute( 'tabindex', '-1' ) );

			if ( ! this.filterTarget && this.slides[ index ] ) {
				const panel = this.slides[ index ];
				nav.id = nav.id || `${ idPrefix }-tab-${ index }`;
				panel.id = panel.id || `${ idPrefix }-panel-${ index }`;
				nav.setAttribute( 'aria-controls', panel.id );
				panel.setAttribute( 'role', 'tabpanel' );
				panel.setAttribute( 'aria-labelledby', nav.id );
				panel.setAttribute( 'tabindex', '0' );
			}

			nav.addEventListener( 'click', event => this.onNavClick( event, index ) );
			nav.addEventListener( 'keydown', event => this.onNavKeydown( event, index ) );

			if ( nav.classList.contains( 'frm-active' ) ) {
				this.initSlideTrackUnderline( nav );
				if ( this.filterTarget ) {
					applyContentFilter( nav.dataset.filter || 'all' );
				}
			}
		} );
		if ( ! this.filterTarget ) {
			this.changeSlide( selectedIndex );
		}
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
		this.navs.forEach( nav => {
			nav.setAttribute( this.filterTarget ? 'aria-pressed' : 'aria-selected', String( nav === navItem ) );
			if ( ! this.filterTarget ) {
				nav.setAttribute( 'tabindex', nav === navItem ? '0' : '-1' );
			}
		} );
		this.initSlideTrackUnderline( navItem );

		if ( this.filterTarget ) {
			applyContentFilter( navItem.dataset.filter || 'all' );
			return;
		}

		this.changeSlide( index );

		// Handle special case for frm_insert_fields_tab
		const navLink = navItem.querySelector( 'a' );
		if ( navLink && navLink.id === 'frm_insert_fields_tab' && ! navLink.closest( '#frm_adv_info' ) ) {
			window.frmAdminBuild?.clearSettingsBox?.();
		}
	}

	/**
	 * Handles keyboard activation for tab navigation.
	 *
	 * @param {KeyboardEvent} event The keydown event.
	 * @param {number}        index The index of the focused tab in `this.navs`.
	 * @return {void}
	 */
	onNavKeydown( event, index ) {
		if ( event.key === 'Enter' || event.key === ' ' ) {
			event.preventDefault();
			this.onNavClick( event, index );
			return;
		}

		if ( this.filterTarget ) {
			return;
		}

		const lastIndex = this.navs.length - 1;
		const nextIndex = {
			ArrowLeft: ( index + ( this.isRTL ? 1 : lastIndex ) ) % this.navs.length,
			ArrowRight: ( index + ( this.isRTL ? lastIndex : 1 ) ) % this.navs.length,
			Home: 0,
			End: lastIndex,
		}[ event.key ];
		if ( nextIndex === undefined ) {
			return;
		}

		event.preventDefault();
		this.navs.forEach( ( nav, navIndex ) => nav.setAttribute( 'tabindex', navIndex === nextIndex ? '0' : '-1' ) );
		this.navs[ nextIndex ].focus();
	}

	initSlideTrackUnderline( nav ) {
		const activeNav = nav !== undefined ? nav : this.navs.filter( nav => nav.classList.contains( 'frm-active' ) );
		this.positionUnderlineIndicator( activeNav );
	}

	/**
	 * Automatically repositions the underline indicator when the wrapper becomes visible.
	 */
	setupVisibilityObserver() {
		observeVisibility( this.wrapper, () => {
			const activeNav = this.wrapper.querySelector( '.frm-tabs-navs ul > li.frm-active' );
			if ( activeNav ) {
				this.positionUnderlineIndicator( activeNav );
			}
		} );
	}

	/**
	 * Uses ResizeObserver to reposition the underline indicator when the parent container layout changes.
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
		disconnectVisibilityObserver();
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
			this.slideTrackLine.style.width = `${ activeNav.clientWidth }px`;
		} );
	}

	changeSlide( index ) {
		this.removeActiveClassnameFromSlides();
		this.slides.forEach( ( panel, panelIndex ) => {
			const inactive = panelIndex !== index;
			panel.toggleAttribute( 'inert', inactive );
			panel.setAttribute( 'aria-hidden', String( inactive ) );
		} );
		const translate = index == 0 ? '0px' : `calc( ( ${ index * 100 }% + ${ parseInt( this.flexboxSlidesGap, 10 ) * index }px ) * ${ this.isRTL ? 1 : -1 } )`;
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
