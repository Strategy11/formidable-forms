import { frmTabsNavigator } from '../../components/class-tabs-navigator';
/**
 * Represents a Tabs Style Component.
 * @class
 */
export default class frmTabsStyleComponent {

	constructor() {
		this.elements = document.querySelectorAll( '.frm-style-tabs-wrapper' );
		if ( 0 < this.elements.length ) {
			this.init();
		}
	}

	/**
	 * Initializes the Tabs Style Component.
	 */
	init() {
		this.elements.forEach( ( element ) => {
			new frmTabsNavigator( element );
		});
	}

	/**
	 * Initializes the component on tab click.
	 * @param {Element} wrapper - The wrapper element.
	 */
	initOnTabClick( wrapper ) {
		this.initActiveBackgroundWidth( wrapper );
		wrapper.querySelectorAll( '.frm-tab-item' ).forEach( ( tab ) => {
			tab.addEventListener( 'click', ( event ) => {
				this.onTabClick( event.target.closest( '.frm-tabs-wrapper' ) );
			});
		});
	}
}