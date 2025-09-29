/**
 * Initialize the spotlight widget.
 *
 * @return {void}
 */
function initializeSpotlight() {
	const spotlightElements = document.querySelectorAll( '.frm-spotlight' );

	if ( ! spotlightElements ) {
		return;
	}

	spotlightElements.forEach( spotlightElement => positionSpotlight( spotlightElement ) );
}

/**
 * Position spotlight content relative to target element.
 *
 * @param {HTMLElement} spotlightElement The spotlight container.
 * @return {void}
 */
function positionSpotlight( spotlightElement ) {
	const targetSelector = spotlightElement.dataset.target;
	const targetElement = document.querySelector( targetSelector );

	if ( ! targetElement ) {
		return;
	}

	const computedStyle = window.getComputedStyle( targetElement );
	if ( computedStyle.position === 'static' ) {
		targetElement.style.position = 'relative';
	}

	targetElement.appendChild( spotlightElement );
	spotlightElement.classList.remove( 'frm-force-hidden' );
}

export default initializeSpotlight;
