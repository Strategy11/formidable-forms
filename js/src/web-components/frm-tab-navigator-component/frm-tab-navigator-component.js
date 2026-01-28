import { frmTabsNavigator } from '../../components/class-tabs-navigator';
import { frmWebComponent } from '../frm-web-component';
import style from './frm-tab-navigator-component.css';

export class frmTabNavigatorComponent extends frmWebComponent {
	constructor() {
		super();
		this.componentStyle = style;
	}

	/**
	 * Initializes the view.
	 *
	 * @return {Element} - The wrapper element.
	 */
	initView() {
		this.tabs = this.querySelectorAll( '.frm-tab' );
		if ( 0 === this.tabs.length ) {
			return null;
		}

		const wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'frm-tabs-wrapper' );
		wrapper.append( this.getTabDelimiter() );
		wrapper.append( this.getTabs() );
		wrapper.append( this.getTabContainer() );

		return wrapper;
	}

	afterViewInit( wrapper ) {
		new frmTabsNavigator( wrapper );
	}

	/**
	 * Gets the tab delimiter.
	 *
	 * @return {string} - The tab delimiter.
	 */
	getTabDelimiter() {
		const delimiter = document.createElement( 'div' );
		const underline = document.createElement( 'span' );

		underline.classList.add( 'frm-tabs-active-underline' );
		delimiter.className = 'frm-tabs-delimiter';
		delimiter.append( underline );

		return delimiter;
	}

	/**
	 * Gets the tab headings.
	 *
	 * @return {string} - The tab headings.
	 */
	getTabs() {
		const tabHeadings = document.createElement( 'div' );
		const ul = document.createElement( 'ul' );

		tabHeadings.className = 'frm-tabs-navs';
		tabHeadings.append( ul );

		Array.from( this.tabs ).forEach( ( tab, index ) => {
			ul.append( this.createTabHeading( tab, index ) );
		} );

		return tabHeadings;
	}

	/**
	 * Gets the tab container.
	 *
	 * @return {string} - The tab container.
	 */
	getTabContainer() {
		const tabContainer = document.createElement( 'div' );
		const slideTrack = document.createElement( 'div' );

		tabContainer.className = 'frm-tabs-container';
		slideTrack.className = 'frm-tabs-slide-track frm-flex-box';
		tabContainer.append( slideTrack );

		Array.from( this.tabs ).forEach( ( tab, index ) => {
			slideTrack.append( this.createTabContainer( tab, index ) );
		} );

		return tabContainer;
	}

	/**
	 * Creates a tab heading.
	 *
	 * @param {Element} tab   - The tab element.
	 * @param {number}  index - The index of the tab.
	 * @return {string} - The tab heading.
	 */
	createTabHeading( tab, index ) {
		const className = index === 0 ? 'frm-active' : '';
		const li = document.createElement( 'li' );
		li.className = className;
		li.innerText = tab.getAttribute( 'data-tab-title' );
		return li;
	}

	/**
	 * Creates a tab container.
	 *
	 * @param {Element} tab   - The tab element.
	 * @param {number}  index - The index of the tab.
	 * @return {string} - The tab container.
	 */
	createTabContainer( tab, index ) {
		const className = index === 0 ? 'frm-active' : '';
		const container = document.createElement( 'div' );

		container.className = `frm-tab-container ${ className }`;

		Array.from( tab.children ).forEach( child => {
			container.append( child );
		} );

		return container;
	}

	/**
	 * Gets the tab underline.
	 *
	 * @return {Element} - The tab underline.
	 */
	getTabUnderline() {
		return this.shadowRoot.querySelector( '.frm-tabs-active-underline' );
	}
}
