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
		this.createNavMenu();
		this.createIconButton();

		// Add event listener to close the navigation menu when clicking outside of floating links wrapper
		this.addOutsideClickListener();

		// Add scroll event listener to hide/show floating links on scroll
		this.addScrollEventListener();

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
		this.wrapperElement.classList.add( 's11-floating-links', 's11-fadein' );

		// Add the wrapper to the DOM
		document.body.appendChild( this.wrapperElement );
	}

	/**
	 * Create the navigation menu element with the specified links and append it to the wrapper element.
	 *
	 * @memberof S11FloatingLinks
	 */
	createNavMenu() {
		// Create the navigation menu element
		this.navMenuElement = document.createElement( 'div' );
		this.navMenuElement.classList.add( 's11-floating-links-nav-menu', 's11-fadeout' );

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
				<span class="s11-floating-links-nav-text">${link.title}</span>
				<span class="s11-floating-links-nav-icon">${link.icon}</span>
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
	 * Create the icon button element, add a click event listener, and append it to the wrapper element.
	 *
	 * @memberof S11FloatingLinks
	 */
	createIconButton() {
		// Create the icon button element
		this.iconButtonElement = document.createElement( 'div' );
		this.iconButtonElement.classList.add( 's11-floating-links-logo-icon' );
		this.iconButtonElement.innerHTML = this.options.logoIcon.trim();

		// Define close icon
		const closeIcon = `
			<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32">
				<path fill="#1D2939" d="M23.625 21.957c.47.467.47 1.225 0 1.693a1.205 1.205 0 0 1-1.699 0l-5.915-5.937-5.958 5.935c-.47.467-1.23.467-1.7 0a1.194 1.194 0 0 1 0-1.693l5.96-5.933-5.961-5.979a1.194 1.194 0 0 1 0-1.693 1.205 1.205 0 0 1 1.699 0l5.96 5.982 5.957-5.935a1.205 1.205 0 0 1 1.7 0 1.194 1.194 0 0 1 0 1.693l-5.96 5.932 5.917 5.935Z"/>
			</svg>
		`;

		// Add a click event listener
		this.iconButtonElement.addEventListener( 'click', () => {
			// Toggle the navigation menu element
			this.toggleFade( this.navMenuElement );

			// Switch the icon of the icon button element
			this.switchIconButton( closeIcon );
		});

		// Append the icon button to the wrapper element
		this.wrapperElement.appendChild( this.iconButtonElement );
	}

	/**
	 * Switch the icon of the icon button element between the logo icon and the close icon.
	 * This method is called when the icon button is clicked.
	 *
	 * @memberof S11FloatingLinks
	 *
	 * @param {string} closeIcon - The SVG close icon that will replace the logo icon when the navigation menu is opened.
	 */
	switchIconButton( closeIcon ) {
		this.iconButtonElement.classList.toggle( 's11-show-close-icon' );

		if ( this.iconButtonElement.classList.contains( 's11-show-close-icon' ) ) {
			this.iconButtonElement.innerHTML = closeIcon.trim();
			return;
		}

		this.iconButtonElement.innerHTML = this.options.logoIcon.trim();
	}

	/**
	 * Toggle the fade-in and fade-out animation for the specified element.
	 * This method is used to show or hide the navigation menu when the icon button is clicked.
	 *
	 * @memberof S11FloatingLinks
	 *
	 * @param {HTMLElement} element - The element to apply the fade animation on.
	 */
	toggleFade( element ) {
		if ( ! element ) {
			return;
		}

		element.classList.add( 's11-fading' );
		element.classList.toggle( 's11-fadein' );
		element.classList.toggle( 's11-fadeout' );

		element.addEventListener( 'animationend', () => {
			element.classList.remove( 's11-fading' );
		}, { once: true });
	}

	/**
	 * Add a click event listener to the body to close the navigation when clicked outside of the floating links wrapper.
	 * Prevents the click event from bubbling up to the body when clicking on the floating links wrapper.
	 *
	 * @memberof S11FloatingLinks
	 */
	addOutsideClickListener() {
		document.body.addEventListener( 'click', ( e ) => {
			if ( ! this.wrapperElement.contains( e.target ) && this.navMenuElement.classList.contains( 's11-fadein' ) ) {
				// Toggle the navigation menu element
				this.toggleFade( this.navMenuElement );

				// Switch the icon of the icon button element
				this.switchIconButton( this.options.logoIcon.trim() );
			}
		});

		// Prevent click event from bubbling up to the body when clicking on the wrapper element
		this.wrapperElement.addEventListener( 'click', ( e ) => {
			e.stopPropagation();
		});
	}

	/**
	 * Add a scroll event listener to the window to toggle visibility of the Floating Links.
	 * Show the Floating Links when scrolling up, and hide when scrolling down.
	 *
	 * @memberof S11FloatingLinks
	 */
	addScrollEventListener() {
		window.addEventListener( 'scroll', () => {
			const currentScrollPosition = window.scrollY || document.documentElement.scrollTop;

			if ( currentScrollPosition < this.lastScrollPosition ) {
				// When scrolling up show the Floating Links
				if ( ! this.wrapperElement.classList.contains( 's11-fadein' ) ) {
					this.toggleFade( this.wrapperElement );
				}
			} else {
				// When scrolling down hide the Floating Links
				if ( ! this.wrapperElement.classList.contains( 's11-fadeout' ) ) {
					this.toggleFade( this.wrapperElement );
				}
			}

			this.lastScrollPosition = currentScrollPosition;
		});
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
				display: none;
				flex-direction: column;
				align-items: flex-end;
				gap: 16px;
			}

			.s11-floating-links.s11-fadein,
			.s11-floating-links.s11-fading {
				display: flex;
			}

			.s11-floating-links-logo-icon {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 64px;
				height: 64px;
				padding: 12px;
				background-color: #fff;
				border: 1px solid #f2f4f7;
				border-radius: 50%;
				box-shadow: 0 11px 22px -5px rgba(16, 24, 40, 0.18);
				cursor: pointer;
			}

			.s11-floating-links-nav-menu {
				display: none;
				grid-template-columns: 1fr;
				background-color: #fff;
				padding: 16px;
				border: 1px solid #f2f4f7;
				border-radius: 8px;
				box-shadow: 0 11px 22px -5px rgba(16, 24, 40, 0.18);
			}

			.s11-floating-links-nav-item {
				position: relative;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 8px;
				margin: 16px 0;
				text-decoration: none;
				z-index: 1;
			}

			.s11-floating-links-nav-item:first-child {
				margin-top: 0;
			}

			.s11-floating-links-nav-item:last-child {
				margin-bottom: 0;
			}

			.s11-floating-links-nav-item:focus {
				outline: 0;
				box-shadow: none;
			}

			.s11-floating-links-nav-item::before {
				content: '';
				position: absolute;
				top: -4px;
				left: -6px;
				width: calc(100% + 12px);
				height: calc(100% + 8px);
				z-index: 0;
				border-radius: 8px;
				background-color: transparent;
				transition: background-color 240ms ease-in;
				cursor: pointer;
			}

			.s11-floating-links-nav-item:hover::before {
				background-color: ${this.options?.bgHoverColor ? this.options?.bgHoverColor : '#F5FAFF'};
			}

			.s11-floating-links-nav-text {
				font-size: 0.875rem;
				line-height: 1.25rem;
				color: #1D2939;
				font-weight: 500;
				z-index: 2;
				transition: color 240ms ease-out;
			}

			.s11-floating-links-nav-item:hover .s11-floating-links-nav-text {
				color: ${this.options?.hoverColor ? this.options.hoverColor : '#4199FD'}
			}

			.s11-floating-links-nav-icon {
				display: flex;
				z-index: 2;
			}

			.s11-floating-links-nav-icon > svg path {
				transition: stroke 240ms ease-out;
			}

			.s11-floating-links-nav-item:hover .s11-floating-links-nav-icon > svg path {
				stroke: ${this.options?.hoverColor ? this.options.hoverColor : '#4199FD'}
			}

			.s11-fadein {
				display: block;
				animation: fadeInUp 240ms ease-in-out forwards;
			}

			.s11-fadeout {
				display: none;
				animation: fadeOutDown 240ms ease-in-out forwards;
			}

			.s11-fading {
				display: block;
			}

			@keyframes fadeInUp {
				0% {
					opacity: 0;
					transform: translateY(20px);
				}
				100% {
					opacity: 1;
					transform: translateY(0);
				}
			}

			@keyframes fadeOutDown {
				0% {
					opacity: 1;
					transform: translateY(0);
				}
				100% {
					opacity: 0;
					transform: translateY(20px);
				}
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

// Define links for the free version of the plugin
const freeVersionLinks = [
	{
		title: __( 'Upgrade', 'formidable' ),
		icon: upgradeIcon,
		url: 'https://formidableforms.com/lite-upgrade/',
		openInNewTab: true
	},
	{
		title: __( 'Support', 'formidable' ),
		icon: supportIcon,
		url: 'https://wordpress.org/support/plugin/formidable/',
		openInNewTab: true
	},
	{
		title: __( 'Documentation', 'formidable' ),
		icon: documentationIcon,
		url: 'https://formidableforms.com/knowledgebase/',
		openInNewTab: true
	}
];

// Define links for the pro version of the plugin
const proVersionLinks = [
	{
		title: __( 'Support & Docs', 'formidable' ),
		icon: supportIcon,
		url: 'https://wordpress.org/support/plugin/formidable/',
		openInNewTab: true
	}
];

// Define options
const options = {
	hoverColor: '#4199FD',
	bgHoverColor: '#F5FAFF',
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

// Determine the appropriate links and initialize the S11FloatingLinks class
const links = s11FloatingLinksData.proIsInstalled ? proVersionLinks : freeVersionLinks;
new S11FloatingLinks( links, options );
