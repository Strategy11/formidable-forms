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
		wrapper.innerHTML = this.getTabDelimiter() + this.getTabs() + this.getTabContainer();

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
		return `<div class="frm-tabs-delimiter">
			<span data-initial-width="123" class="frm-tabs-active-underline frm-first"></span>
		</div>`;
	}

	/**
	 * Gets the tab headings.
	 * @return {string} - The tab headings.
	 */
	getTabs() {
		const tabHeadings = Array.from( this.tabs ).map( ( tab, index ) => {
			return this.createTabHeading( tab, index );
		});

		return `<div class="frm-tabs-navs">
			<ul>
				${tabHeadings.join( '' )}
			</ul>
		</div>`;
	}

	/**
	 * Gets the tab container.
	 * @return {string} - The tab container.
	 */
	getTabContainer() {
		const tabContainer = Array.from( this.tabs ).map( ( tab, index ) => {
			return this.createTabContainer( tab, index );
		});

		return `<div class="frm-tabs-container">
			<div class="frm-tabs-slide-track frm-flex-box">
				${tabContainer.join( '' )}
			</div>
		</div>`;
	}

	/**
	 * Creates a tab heading.
	 * @param {Element} tab   - The tab element.
	 * @param {number}  index - The index of the tab.
	 * @return {string} - The tab heading.
	 */
	createTabHeading( tab, index ) {
		const className = index === 0 ? 'frm-active' : '';
		return `<li class="${className}">${tab.getAttribute( 'data-tab-title' )}</li>`;
	}

	/**
	 * Creates a tab container.
	 * @param {Element} tab   - The tab element.
	 * @param {number}  index - The index of the tab.
	 * @return {string} - The tab container.
	 */
	createTabContainer( tab, index ) {
		const className = index === 0 ? 'frm-active' : '';
		return `<div class="frm-tab-container ${className}">
			${tab.innerHTML}
		</div>`;
	}

	/**
	 * Gets the tab underline.
	 * @return {Element} - The tab underline.
	 */
	getTabUnderline() {
		return this.shadowRoot.querySelector( '.frm-tabs-active-underline' );
	}
	
}