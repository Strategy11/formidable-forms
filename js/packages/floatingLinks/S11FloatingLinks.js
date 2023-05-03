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
	 * @param {Array<Object>} links - An array of link objects to display, each containing 'title', 'url', and an optional 'openInNewTab' property.
	 * @param {Object} options - Configuration options for the floating links component.
	 * @param {string} options.svgIconPath - The path to the SVG icon file used for the floating action button.
	 */
	constructor( links, options ) {
		this.validateInputs( links, options );

		this.links = links;
		this.options = options;

		this.initComponent();
	}

	/**
	 * Validate the input parameters.
	 * @param {Array<Object>} links - The links array.
	 * @param {Object} options - The options object.
	 * @throws {Error} If the links array is empty or not provided.
	 * @throws {Error} If the options.svgIconPath is not provided or is an empty string.
	 */
	validateInputs( links, options ) {
		if ( ! Array.isArray( links ) || links.length === 0 ) {
			throw new Error( 'The "links" array is required and must not be empty.' );
		}

		if ( ! options || typeof options.svgIconPath !== 'string' || options.svgIconPath.trim() === '' ) {
			throw new Error( 'The "options.svgIconPath" is required and must be a non-empty string.' );
		}
	}

	/**
	 * Initialize the floating links component by loading the SVG icon, creating the required elements, and applying styles.
	 *
	 * @memberof S11FloatingLinks
	 */
	async initComponent() {
		// Load the SVG icon
		const svgIcon = await this.fetchSVGIcon( this.options.svgIconPath );

		// Create and append elements
		this.createWrapper();
		this.createIcon( svgIcon );
		this.createNav();

		// Apply styles.
		this.applyComponentStyles();
	}

	/**
	 * Fetch the SVG icon from the specified path.
	 *
	 * @memberof S11FloatingLinks
	 *
	 * @param {string} path - The path to the SVG icon file.
	 * @returns {Promise<string>} A promise that resolves to the SVG content as a string.
	 */
	async fetchSVGIcon( path ) {
		const response = await fetch( path );
		return response.text();
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
	 * Create the icon element, add a click event listener, and append it to the wrapper element.
	 *
	 * @memberof S11FloatingLinks
	 *
	 * @param {string} svgIcon - The SVG content as a string.
	 */
	createIcon( svgIcon ) {
		// Create the icon element
		this.iconElement = document.createElement( 'div' );
		this.iconElement.classList.add( 's11-floating-links-icon' );
		this.iconElement.innerHTML = svgIcon;

		// Add a click event listener
		this.iconElement.addEventListener( 'click', () => {
			this.navElement.classList.toggle( 's11-fade-toggle' );
		});

		// Append the icon to the wrapper element.
		this.wrapperElement.appendChild( this.iconElement );
	}

	/**
	 * Create the navigation element with the specified links and append it to the wrapper element.
	 *
	 * @memberof S11FloatingLinks
	 */
    createNav() {
		// Create the navigation element
		this.navElement = document.createElement( 'div' );
		this.navElement.classList.add( 's11-floating-links-nav' );
		this.links.forEach( ( link ) => {
			const anchor = document.createElement( 'a' );
			anchor.href = link.url;
			anchor.textContent = link.title;
			anchor.target = link.openInNewTab === true ? '_blank' : '';
			this.navElement.appendChild( anchor );
		});

		// Append the navigation to the wrapper element
		this.wrapperElement.appendChild( this.navElement );
	}

	/**
	 * Apply styles to the floating links component elements.
	 * @memberof S11FloatingLinks
	 */
	applyComponentStyles() {
		this.wrapperElement.style.cssText = `
			position: fixed;
			bottom: 48px;
			right: 48px;
			z-index: 1000;
		`;

		this.iconElement.style.cssText = `
			overflow: hidden;
		`;
	}
}
