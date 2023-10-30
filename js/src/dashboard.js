import { FrmTabsNavigator } from './components/class-tabs-navigator';
import { FrmCounter } from './components/class-counter';

class FrmDashboard {

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

}

const dashboard = new FrmDashboard();
document.addEventListener( 'DOMContentLoaded', () => {
	dashboard.initInbox();
	dashboard.initIntroWidgetAnimation();
	dashboard.initCounter();
});
