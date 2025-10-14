/**
 * Initialize the spotlight widget.
 *
 * @return {void}
 */
function initializeSpotlight() {
	document.querySelectorAll( '.frm-spotlight' ).forEach( spotlightElement => {
		setTimeout( () => {
			setupSpotlight( spotlightElement );
		}, 0 );
	} );
}

/**
 * Setup spotlight with positioning and scroll listeners.
 *
 * @param {HTMLElement} spotlightElement The spotlight container.
 * @return {void}
 */
function setupSpotlight( spotlightElement ) {
	const targetElement = document.querySelector( spotlightElement.dataset.target );
	if ( ! targetElement ) {
		return;
	}

	// Append spotlight to body to ensure it's visible
	document.body.appendChild( spotlightElement );
	spotlightElement.classList.remove( 'frm-force-hidden' );

	updateSpotlightPosition( spotlightElement, targetElement );

	const scrollableElements = getScrollableAncestors( targetElement );
	scrollableElements.forEach( element => {
		element.addEventListener( 'scroll', () => updateSpotlightPosition( spotlightElement, targetElement ), { passive: true } );
	} );

	window.addEventListener( 'resize', () => updateSpotlightPosition( spotlightElement, targetElement ), { passive: true } );
}

/**
 * Update spotlight position based on target element.
 *
 * @param {HTMLElement} spotlightElement The spotlight container.
 * @param {HTMLElement} targetElement    The target element.
 * @return {void}
 */
function updateSpotlightPosition( spotlightElement, targetElement ) {
	if ( ! targetElement.offsetParent ) {
		return;
	}

	const targetRect = targetElement.getBoundingClientRect();
	if ( targetRect.width === 0 || targetRect.height === 0 ) {
		return;
	}

	// Simple viewport bounds check - adjust if target is off-screen
	let top = targetRect.top + ( targetRect.height / 2 );
	let left = targetRect.left;

	const leftPosition = spotlightElement.dataset.leftPosition;
	switch ( leftPosition ) {
		case 'middle':
			left = targetRect.left + ( targetRect.width / 2 );
			break;
		case 'end':
			left = targetRect.right;
			break;
		default:
			const offset = leftPosition && leftPosition.includes( 'px' ) ? parseInt( leftPosition ) : 0;
			left = targetRect.left + offset;
			break;
	}

	// Simple collision detection - keep spotlight in viewport
	const margin = 10; // Safe margin from viewport edges
	const viewportWidth = window.innerWidth;
	const viewportHeight = window.innerHeight;

	// Constrain to viewport bounds
	top = Math.max( margin, Math.min( top, viewportHeight - margin ) );
	left = Math.max( margin, Math.min( left, viewportWidth - margin ) );

	spotlightElement.style.top = `${ top }px`;
	spotlightElement.style.left = `${ left }px`;
}

/**
 * Get all scrollable ancestor elements.
 *
 * @private
 * @param {HTMLElement} element The target element.
 * @return {HTMLElement[]} Array of scrollable elements.
 */
function getScrollableAncestors( element ) {
	const scrollables = [ window ];
	let parent = element.parentElement;

	while ( parent && document.body !== parent ) {
		const { overflow, overflowY } = getComputedStyle( parent );
		if ( [ 'auto', 'scroll' ].includes( overflow ) || [ 'auto', 'scroll' ].includes( overflowY ) ) {
			scrollables.push( parent );
		}

		parent = parent.parentElement;
	}

	return scrollables;
}

export default initializeSpotlight;
