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

	const targetRect = targetElement.getBoundingClientRect();

	const centerX = targetRect.left + ( targetRect.width / 2 );
	const centerY = targetRect.top + ( targetRect.height / 2 );

	spotlightElement.style.top = `${ centerY }px`;
	spotlightElement.style.left = `${ centerX }px`;
	spotlightElement.classList.remove( 'frm-force-hidden' );
}

export default initializeSpotlight;
