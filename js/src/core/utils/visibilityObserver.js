/**
 * @type {IntersectionObserver|null}
 */
let observer = null;

/**
 * Checks if IntersectionObserver is supported.
 *
 * @return {boolean} True if supported.
 */
export function isVisibilityObserverSupported() {
	return 'IntersectionObserver' in window;
}

/**
 * Observes an element and calls callback once when it becomes visible.
 *
 * @param {Element}  element  The element to observe.
 * @param {Function} callback Called once when element becomes visible.
 */
export function observeVisibility( element, callback ) {
	if ( ! isVisibilityObserverSupported() ) {
		return;
	}

	if ( ! ( element instanceof Element ) || 'function' !== typeof callback ) {
		return;
	}

	observer = new IntersectionObserver( entries => {
		if ( entries[ 0 ].isIntersecting ) {
			callback();
			disconnectVisibilityObserver();
		}
	} );

	observer.observe( element );
}

/**
 * Disconnects the visibility observer.
 */
export function disconnectVisibilityObserver() {
	if ( observer ) {
		observer.disconnect();
		observer = null;
	}
}
