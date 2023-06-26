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
	 */
	constructor() {
		wp.hooks.addAction( 'set_floating_links_config', 'S11FloatingLinks', ({ links, options }) => {
			this.validateInputs( links, options );

			this.links = links;
			this.options = options;

			this.initComponent();
		});
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
		this.setCSSVariables();
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
	 * Set dynamic CSS properties.
	 *
	 * @memberof S11FloatingLinks
	 */
	setCSSVariables() {
		const hoverColor = this.options?.hoverColor ? this.options.hoverColor : '#4199FD';
		const bgHoverColor = this.options?.bgHoverColor ? this.options.bgHoverColor : '#F5FAFF';

		// Set the CSS variables on the wrapper element
		this.wrapperElement.style.setProperty( '--floating-links-hover-color', hoverColor );
		this.wrapperElement.style.setProperty( '--floating-links-bg-hover-color', bgHoverColor );
	}
}

// Initialize Floating Links
new S11FloatingLinks();
