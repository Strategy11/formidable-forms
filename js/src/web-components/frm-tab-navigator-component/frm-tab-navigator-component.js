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
	 * @return {Element} - The wrapper element.
	 */
	initView() {
		this.tabs = this.querySelectorAll( '.frm-tab' );
		if ( 0 === this.tabs.length ) {
			return null;
		}

		const wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'frm-tabs-wrapper' );
		wrapper.appendChild( this.getTabDelimiter() );
		wrapper.appendChild( this.getTabs() );
		wrapper.appendChild( this.getTabContainer() );

		new frmTabsNavigator( wrapper );

		return wrapper;
	}

	afterViewInit( wrapper ) {
		this.setInitialUnderlineWidth( wrapper );
	}

	/**
	 * Sets the initial underline width of active tab nav item.
	 * @param {Element} wrapper - The wrapper element.
	 */
	setInitialUnderlineWidth( wrapper ) {
		const li                 = wrapper.querySelector( 'li.frm-active' );
		const tabActiveUnderline = wrapper.querySelector( '.frm-tabs-delimiter .frm-tabs-active-underline' );

		if ( ! li || ! tabActiveUnderline ) {
			return;
		}
		tabActiveUnderline.style.width = `${li.clientWidth}px`;
	}

	/**
	 * Gets the tab delimiter.
	 * @return {string} - The tab delimiter.
	 */
	getTabDelimiter() {
		const delimiter = document.createElement( 'div' );
		const underline = document.createElement( 'span' );

		underline.setAttribute( 'data-initial-width', '123' );
		underline.classList.add( 'frm-tabs-active-underline', 'frm-first' );
		delimiter.className = 'frm-tabs-delimiter';
		delimiter.appendChild( underline );

		return delimiter;
	}

	/**
	 * Gets the tab headings.
	 * @return {string} - The tab headings.
	 */
	getTabs() {
		const tabHeadings = document.createElement( 'div' );
		const ul = document.createElement( 'ul' );

		tabHeadings.className = 'frm-tabs-navs';
		tabHeadings.appendChild( ul );

		Array.from( this.tabs ).forEach( ( tab, index ) => {
			ul.appendChild( this.createTabHeading( tab, index ) );
		});

		return tabHeadings;
	}

	/**
	 * Gets the tab container.
	 * @return {string} - The tab container.
	 */
	getTabContainer() {
		const tabContainer = document.createElement( 'div' );
		const slideTrack = document.createElement( 'div' );

		tabContainer.className = 'frm-tabs-container';
		slideTrack.className = 'frm-tabs-slide-track frm-flex-box';
		tabContainer.appendChild( slideTrack );

		Array.from( this.tabs ).forEach( ( tab, index ) => {
			slideTrack.appendChild( this.createTabContainer( tab, index ) );
		});

		return tabContainer;
	}

	/**
	 * Creates a tab heading.
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
	 * @param {Element} tab   - The tab element.
	 * @param {number}  index - The index of the tab.
	 * @return {string} - The tab container.
	 */
	createTabContainer( tab, index ) {
		const className = index === 0 ? 'frm-active' : '';
		const container = document.createElement( 'div' );

		container.className = `frm-tab-container ${className}`;

		Array.from( tab.children ).forEach( child => {
			container.appendChild( child );
		});

		return container;
	}

	/**
	 * Gets the tab underline.
	 * @return {Element} - The tab underline.
	 */
	getTabUnderline() {
		return this.shadowRoot.querySelector( '.frm-tabs-active-underline' );
	}
	
}