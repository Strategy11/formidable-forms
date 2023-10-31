import { FrmTabsNavigator } from './components/class-tabs-navigator';
import { FrmCounter } from './components/class-counter';

class FrmDashboard {

	constructor() {
		this.options = {
			ajax: {
				action: 'dashboard_ajax_action',
				dashboardActions: {
					welcomeBanner: 'welcome-banner-cookie'
				}
			}
		};
	}

	initInbox() {
		new FrmTabsNavigator( '.frm-inbox-wrapper' );
	}

	initIntroWidgetAnimation() {
		const widgets = document.querySelectorAll( '.frm-dashboard-widget.frm-animate' );
		widgets.forEach( ( widget, index ) => {
			widget.classList.remove( 'frm-animate' );
			widget.style.transitionDelay = ( index + 1 ) * 0.025 + 's';
		});
	}

	initCounter() {
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

	closeWelcomeBannerSaveCookieRequest() {
		return new Promise( ( resolve, reject ) => {
			fetch( window.ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: new URLSearchParams({
					action: this.options.ajax.action,
					dashboard_action: this.options.ajax.dashboardActions.welcomeBanner,
					banner_has_closed: 1
				})
			}).then( result => {
				result.json().then( ( data ) => {
					resolve( data );
				});
			});
		});
	}

}

const dashboard = new FrmDashboard();
document.addEventListener( 'DOMContentLoaded', () => {
	dashboard.initInbox();
	dashboard.initIntroWidgetAnimation();
	dashboard.initCounter();
	dashboard.initCloseWelcomeBanner();
});
