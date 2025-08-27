/**
 * External dependencies
 */
import { frmAnimate } from 'core/utils';

import { frmTabsNavigator } from './components/class-tabs-navigator';
import { frmCounter } from './components/class-counter';
class frmDashboard {
	constructor() {
		this.options = {
			ajax: {
				action: 'dashboard_ajax_action',
				dashboardActions: {
					welcomeBanner: 'welcome-banner-has-closed',
					checkEmailIfSubscribed: 'email-has-subscribed',
					saveSubscribedEmail: 'save-subscribed-email'
				}
			}
		};
		this.widgetsAnimate = new frmAnimate( document.querySelectorAll( '.frm-dashboard-widget' ), 'cascade' );
	}

	init() {
		this.initInbox();
		this.initCounters();
		this.initCloseWelcomeBanner();

		this.widgetsAnimate.cascadeFadeIn();
	}

	initInbox() {
		new frmTabsNavigator( '.frm-inbox-wrapper' );
		const userEmailInput = document.querySelector( '#frm_leave_email' );
		const subscribeButton = document.querySelector( '#frm-add-my-email-address' );

		subscribeButton.addEventListener( 'click', () => {
			this.saveSubscribedEmail( userEmailInput.value ).then();
		} );
	}

	initCounters() {
		const counters = document.querySelectorAll( '.frm-counter' );
		counters.forEach( counter => new frmCounter( counter ) );
	}

	initCloseWelcomeBanner() {
		const closeButton = document.querySelector( '.frm-dashboard-banner-close' );
		const dashboardBanner = document.querySelector( '.frm-dashboard-banner' );

		if ( ! closeButton || ! dashboardBanner ) {
			return;
		}

		closeButton.addEventListener( 'click', () => {
			this.closeWelcomeBannerSaveCookieRequest().then( data => {
				if ( true === data.success ) {
					dashboardBanner.remove();
				}
			} );
		} );
	}

	saveSubscribedEmail( email ) {
		return fetch( window.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams( {
				action: this.options.ajax.action,
				dashboard_action: this.options.ajax.dashboardActions.saveSubscribedEmail,
				email: email
			} )
		} ).then( result => result.json() );
	}

	closeWelcomeBannerSaveCookieRequest() {
		return fetch( window.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams( {
				action: this.options.ajax.action,
				dashboard_action: this.options.ajax.dashboardActions.welcomeBanner,
				banner_has_closed: 1
			} )
		} ).then( result => result.json() );
	}
}
const frmDashboardClass = new frmDashboard();
document.addEventListener( 'DOMContentLoaded', () => {
	frmDashboardClass.init();
} );
