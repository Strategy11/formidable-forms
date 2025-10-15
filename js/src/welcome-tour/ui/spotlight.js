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
 * @private
 * @param {HTMLElement} spotlightElement The spotlight container.
 * @return {void}
 */
function setupSpotlight( spotlightElement ) {
	const targetElement = document.querySelector( spotlightElement.dataset.target );
	if ( ! targetElement ) {
		return;
	}

	document.body.appendChild( spotlightElement );

	// Add scroll listeners to all scrollable ancestors
	const scrollableElements = getScrollableAncestors( targetElement );
	scrollableElements.forEach( element => {
		element.addEventListener( 'scroll', () => {
			updateSpotlightPosition( spotlightElement, targetElement );
			handleSpotlightFadeAnimation( spotlightElement );
		}, { passive: true } );
	} );

	window.addEventListener( 'resize', () => updateSpotlightPosition( spotlightElement, targetElement ), { passive: true } );

	// Re-position and show spotlight after a short delay on page load
	setTimeout( () => {
		updateSpotlightPosition( spotlightElement, targetElement );
		spotlightElement.classList.remove( 'frm-force-hidden' );
	}, 200 );
}

/**
 * Update spotlight position based on target element.
 *
 * @private
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

	let left = targetRect.left;
	const leftPositionAttr = spotlightElement.dataset.leftPosition;

	switch ( leftPositionAttr ) {
		case 'middle':
			left = targetRect.left + Math.round( targetRect.width / 2 );
			break;
		case 'end':
			left = targetRect.right;
			break;
		default:
			left = targetRect.left + parseInt( leftPositionAttr );
			break;
	}

	spotlightElement.style.top = `${ targetRect.top + Math.round( targetRect.height / 2 ) }px`;
	spotlightElement.style.left = `${ left }px`;
}

/**
 * Handle fade animations based on spotlight position relative to #frm_top_bar.
 *
 * @private
 * @param {HTMLElement} spotlightElement The spotlight container.
 * @return {void}
 */
function handleSpotlightFadeAnimation( spotlightElement ) {
	const topBar = document.querySelector( '#frm_top_bar' );
	if ( ! topBar ) {
		return;
	}

	const shouldFadeOut = ( spotlightElement.getBoundingClientRect().top + 24 ) <= topBar.getBoundingClientRect().bottom;

	if ( shouldFadeOut && ! spotlightElement.classList.contains( 'frm-fadeout' ) ) {
		spotlightElement.classList.remove( 'frm-fadein' );
		spotlightElement.classList.add( 'frm-fadeout' );
	} else if ( ! shouldFadeOut && ! spotlightElement.classList.contains( 'frm-fadein' ) ) {
		spotlightElement.classList.remove( 'frm-fadeout' );
		spotlightElement.classList.add( 'frm-fadein' );
	}
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
