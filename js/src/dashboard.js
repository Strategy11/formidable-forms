import { FrmTabsNavigator } from './components/class-tabs-navigator';
import { FrmCounter } from './components/class-counter';

class FrmDashboard {

	constructor() {

		this.options = {
			ajax: {
				action: 'dashboard_ajax_action',
				dashboardActions: {
					welcomeBanner: 'welcome-banner-cookie',
					checkEmailIfSubscribed: 'email-has-subscribed',
					saveSubscribedEmail: 'save-subscribed-email'
				}
			}
		};

		this.initInbox();
		this.initIntroWidgetAnimation();
		this.initCounters();
		this.initCloseWelcomeBanner();
	}

	initInbox() {
		new FrmTabsNavigator( '.frm-inbox-wrapper' );
		const userEmailInput  = document.querySelector( '#frm_leave_email' );
		const subscribeButton = document.querySelector( '#frm-add-my-email-address' );

		subscribeButton.addEventListener( 'click', () => {
			this.saveSubscribedEmail( userEmailInput.value ).then();
		});
	}

	initIntroWidgetAnimation() {
		const widgets = document.querySelectorAll( '.frm-dashboard-widget.frm-animate' );
		widgets.forEach( ( widget, index ) => {
			widget.classList.remove( 'frm-animate' );
			widget.style.transitionDelay = ( index + 1 ) * 0.025 + 's';
		});
	}

	initCounters() {
		const counters = document.querySelectorAll( '.frm-counter' );
		counters.forEach( counter => new FrmCounter( counter ) );
	}

	initCloseWelcomeBanner() {
		const closeButton = document.querySelector( '.frm-dashboard-banner-close' );
		const dashboardBanner = document.querySelector( '.frm-dashboard-banner' );

		if ( null === closeButton || null === dashboardBanner ) {
			return;
		}

		closeButton.addEventListener( 'click', () => {
			this.closeWelcomeBannerSaveCookieRequest().then( data => {
				if ( true === data.success ) {
					dashboardBanner.remove();
				}
			});
		});
	}

	saveSubscribedEmail( email ) {
		return fetch( window.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams({
				action: this.options.ajax.action,
				dashboard_action: this.options.ajax.dashboardActions.saveSubscribedEmail,
				email: email
			})
		}).then( result => result.json() );
	}

	closeWelcomeBannerSaveCookieRequest() {
		return fetch( window.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams({
				action: this.options.ajax.action,
				dashboard_action: this.options.ajax.dashboardActions.welcomeBanner,
				banner_has_closed: 1
			})
		}).then( result => result.json() );
	}
}

document.addEventListener( 'DOMContentLoaded', () => {
	new FrmDashboard();
});
