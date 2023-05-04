/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Class representing a floating action button for showing links.
 *
 * @class S11FloatingLinks
 */
class S11FloatingLinks {

	/**
	 * Create a new S11FloatingLinks instance.
	 *
	 * @constructor
	 *
	 * @param {Array<Object>} links - An array of link objects to display, each containing 'title', 'icon', 'url', and an optional 'openInNewTab' property.
	 * @param {Object} options - Configuration options for the floating links component.
	 * @param {string} options.logoIcon - The SVG icon of the plugin logo that used for the floating action button.
	 */
	constructor( links, options ) {
		this.validateInputs( links, options );

		this.links = links;
		this.options = options;

		this.initComponent();
	}

	/**
	 * Validate the input parameters.
	 *
	 * @param {Array<Object>} links - The links array.
	 * @param {Object} options - The options object.
	 *
	 * @throws {Error} If the links array is empty or not provided.
	 * @throws {Error} If the options.logoIcon is not provided or is an empty string.
	 */
	validateInputs( links, options ) {
		if ( ! Array.isArray( links ) || links.length === 0 ) {
			throw new Error( 'The "links" array is required and must not be empty.' );
		}

		if ( ! options || typeof options.logoIcon !== 'string' || options.logoIcon.trim() === '' ) {
			throw new Error( 'The "options.logoIcon" is required and must be a non-empty string.' );
		}
	}

	/**
	 * Initialize the floating links component by creating the required elements, and applying styles.
	 *
	 * @memberof S11FloatingLinks
	 */
	initComponent() {
		// Create and append elements
		this.createWrapper();
		this.createIconButton();
		this.createNavMenu();

		// Apply styles
		this.applyComponentStyles();
	}

	/**
	 * Create the wrapper element and add it to the DOM.
	 *
	 * @memberof S11FloatingLinks
	 */
	createWrapper() {
		// Create the wrapper element
		this.wrapperElement = document.createElement( 'div' );
		this.wrapperElement.classList.add( 's11-floating-links' );

		// Add the wrapper to the DOM
		document.body.appendChild( this.wrapperElement );
	}

	/**
	 * Create the icon button element, add a click event listener, and append it to the wrapper element.
	 *
	 * @memberof S11FloatingLinks
	 */
	createIconButton() {
		// Create the icon button element
		this.iconButtonElement = document.createElement( 'div' );
		this.iconButtonElement.classList.add( 's11-floating-links-logo-icon' );
		this.iconButtonElement.innerHTML = this.options.logoIcon;

		// Add a click event listener
		this.iconButtonElement.addEventListener( 'click', () => {
			this.navMenuElement.classList.toggle( 's11-fade-toggle' );
		});

		// Append the icon button to the wrapper element
		this.wrapperElement.appendChild( this.iconButtonElement );
	}

	/**
	 * Create the navigation menu element with the specified links and append it to the wrapper element.
	 *
	 * @memberof S11FloatingLinks
	 */
    createNavMenu() {
		// Create the navigation menu element
		this.navMenuElement = document.createElement( 'div' );
		this.navMenuElement.classList.add( 's11-floating-links-nav-menu' );

		// Create and append link elements
		this.links.forEach( ( link ) => {
			if ( ! this.linkHasRequiredProperties( link ) ) {
				return;
			}

			const linkElement = document.createElement( 'a' );
			linkElement.classList.add( 's11-floating-links-nav-item' );

			linkElement.href = link.url;
			linkElement.title = link.title;

			if ( link.openInNewTab ===  true ) {
				linkElement.target = '_blank';
				linkElement.rel = 'noopener noreferrer';
			}

			linkElement.innerHTML = `
				<span class="s11-floating-links-nav-icon">${link.icon}</span>
				<span class="s11-floating-links-nav-text">${link.title}</span>
			`;

			this.navMenuElement.appendChild( linkElement );
		});

		// Append the navigation menu to the wrapper element
		this.wrapperElement.appendChild( this.navMenuElement );
	}

	linkHasRequiredProperties( link ) {
		const requiredProperties = [ 'title', 'icon', 'url' ];
		return requiredProperties.every( prop => link.hasOwnProperty( prop ) );
	}

	/**
	 * Apply styles to the component elements.
	 *
	 * @memberof S11FloatingLinks
	 */
	applyComponentStyles() {
		const css = `
			.s11-floating-links,
			.s11-floating-links::before,
			.s11-floating-links::after,
			.s11-floating-links *,
			.s11-floating-links *::before,
			.s11-floating-links *::after {
				box-sizing: border-box;
			}

			.s11-floating-links {
				position: fixed;
				right: 48px;
				bottom: 48px;
				z-index: 1000;
			}

			.s11-floating-links-logo-icon {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 64px;
				height: 64px;
				padding: 12px;
				border: 1px solid #f2f4f7;
				border-radius: 50%;
				box-shadow: 0 11px 22 -5 rgba(16, 24, 40, 0.18);
				background-color: #fff;
				cursor: pointer;
			}

			.s11-floating-links-logo-icon:hover {
			}

			.s11-floating-links-nav-menu {
				display: none;
			}

			.s11-floating-links-nav-item {
			}

			.s11-floating-links-nav-item:hover {
			}

			.s11-floating-links-nav-icon {
			}

			.s11-fade-toggle {
			}
		`;


		// Create a style element and append it to the document head
		const styleElement = document.createElement( 'style' );
		styleElement.appendChild( document.createTextNode( css ) );
		document.head.appendChild( styleElement );
	}
}

/**
 * Initialize Floating Links.
 * Setup links and options parameters and send them to the S11FloatingLinks class.
 *
 * @class S11FloatingLinks
 */

// Define link icons
const upgradeIcon = `
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
		<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m12 4.75 1.75 5.5h5.5l-4.5 3.5 1.5 5.5-4.25-3.5-4.25 3.5 1.5-5.5-4.5-3.5h5.5L12 4.75Z"/>
	</svg>
`;

const supportIcon = `
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
		<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 12a7.25 7.25 0 1 1-14.5 0 7.25 7.25 0 0 1 14.5 0Z"/>
		<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.25 12a3.25 3.25 0 1 1-6.5 0 3.25 3.25 0 0 1 6.5 0ZM7 17l2.5-2.5M17 17l-2.5-2.5m-5-5L7 7m7.5 2.5L17 7"/>
	</svg>
`;

const documentationIcon = `
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
		<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m14.924 16.002.482 2.432c.106.537.682.895 1.286.8l1.64-.256c.604-.095 1.007-.607.9-1.145l-.481-2.431m-3.827.6-1.157-5.835c-.106-.538.297-1.05.9-1.145l1.64-.257c.605-.095 1.18.264 1.287.801l1.157 5.836m-3.827.6 3.827-.6M8.75 15.75v2.5a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-2.5m-3.5 0v-8a1 1 0 0 1 1-1h1.5a1 1 0 0 1 1 1v8m-3.5 0h3.5m-7.5 0v2.5a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-2.5m-3.5 0v-10a1 1 0 0 1 1-1h1.5a1 1 0 0 1 1 1v10m-3.5 0h3.5"/>
	</svg>
`;

const notificationsIcon = `
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
		<path stroke="#667085" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 12v-2a5.25 5.25 0 1 0-10.5 0v2l-2 4.25h14.5l-2-4.25ZM9 16.75s0 2.5 3 2.5 3-2.5 3-2.5"/>
	</svg>
`;

// Define links
const links = [
	{
		title: __( 'Upgrade', 'formidable' ),
		icon: upgradeIcon,
		url: 'https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=top-bar&utm_campaign=liteplugin',
		openInNewTab: true
	},
	{
		title: __( 'Support', 'formidable' ),
		icon: supportIcon,
		url: 'https://formidableforms.com/knowledgebase/support-2/',
		openInNewTab: true
	},
	{
		title: __( 'Documentation', 'formidable' ),
		icon: documentationIcon,
		url: 'https://formidableforms.com/knowledgebase/',
		openInNewTab: true
	},
	{
		title: __( 'Notifications', 'formidable' ),
		icon: notificationsIcon,
		url: '#',
		openInNewTab: true
	}
];

// Define options
const options = {
	logoIcon: `
		<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
			<g clip-path="url(#a)">
				<path fill="#F15A24" d="M19.265 25.641h9.401v4.957h-9.401v-4.957Z"/>
				<path fill="#5E5E5F" d="M26.702 9.743H13.368a2.12 2.12 0 0 0-2.136 2.12V14.7h17.436V9.743h-1.966Zm-.171 7.864H11.249v12.991h4.957v-8.034h10.36a2.154 2.154 0 0 0 2.016-1.419 1.67 1.67 0 0 0 .103-.598v-2.94H26.53ZM20 40a20 20 0 0 1-6.748-38.827 20 20 0 0 1 14.526 37.254A19.847 19.847 0 0 1 20 40Zm0-37.35A17.35 17.35 0 0 0 7.727 32.272 17.358 17.358 0 0 0 32.275 7.726 17.232 17.232 0 0 0 20 2.666V2.65Z"/>
			</g>
			<defs>
				<clipPath id="a">
				<path fill="#fff" d="M0 0h40v40H0z"/>
				</clipPath>
			</defs>
		</svg>
	`
};

// Call S11FloatingLinks class
new S11FloatingLinks( links, options );
