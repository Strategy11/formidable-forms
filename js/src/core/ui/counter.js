/**
 * Updates the text content of an element with a counter value using smooth animation.
 *
 * @param {HTMLElement|string} element          The DOM element or selector to update
 * @param {number|string}      value            The new counter value to set
 * @param {Object}             options          Animation options
 * @param {number}             options.duration Duration in milliseconds (default: 3000)
 * @param {Function}           options.easing   Easing function (default: easeOutQuart)
 * @throws {Error} When element is not found or invalid
 * @return {HTMLElement} The updated element for method chaining
 */
const counter = ( element, value, options = {} ) => {
	const targetElement = typeof element === 'string'
		? document.querySelector( element )
		: element;

	if ( ! targetElement || ! ( targetElement instanceof HTMLElement ) ) {
		return null;
	}

	const targetValue = typeof value === 'number' ? value : parseInt( value, 10 );
	if ( isNaN( targetValue ) ) {
		console.warn( 'Counter: Invalid value provided, defaulting to 0' );
		return setElementValueAndReturn( targetElement, '0' );
	}

	// Don't run the animation if the sent value is 0
	if ( targetValue === 0 ) {
		return setElementValueAndReturn( targetElement, '0' );
	}

	const { duration = 3000, easing = easeOutQuart } = options;

	const startValue = parseInt( targetElement.textContent, 10 ) || 0;
	const change = targetValue - startValue;

	// Skip animation if no change needed
	if ( change === 0 ) {
		return targetElement;
	}

	// Cancel any existing animation
	if ( targetElement._counterAnimation ) {
		cancelAnimationFrame( targetElement._counterAnimation );
	}

	// Start animation
	targetElement.classList.add( 'frm-fadein' );
	targetElement._counterAnimation = requestAnimationFrame( timestamp =>
		animateCounter( timestamp, targetElement, startValue, targetValue, duration, change, easing )
	);

	return targetElement;
};

/**
 * Helper function to set element text content and return element
 *
 * @param {HTMLElement}   element Target element
 * @param {string|number} value   Value to set
 * @return {HTMLElement} The element for method chaining
 */
const setElementValueAndReturn = ( element, value ) => {
	element.textContent = String( value );
	return element;
};

/**
 * Standalone animation function for counter (optimized to prevent redefinition)
 *
 * @param {number}      timestamp   Current timestamp from requestAnimationFrame
 * @param {HTMLElement} element     Target element to animate
 * @param {number}      startValue  Starting counter value
 * @param {number}      targetValue Target counter value
 * @param {number}      duration    Animation duration in milliseconds
 * @param {number}      change      Total change amount (targetValue - startValue)
 * @param {Function}    easing      Easing function
 * @return {void}
 */
const animateCounter = ( timestamp, element, startValue, targetValue, duration, change, easing ) => {
	if ( ! element._counterStartTime ) {
		element._counterStartTime = timestamp;
		element._counterLastTimestamp = timestamp;
		element._counterFrameDropCount = 0;
		element._counterLastValue = startValue;
	}

	const frameDelta = timestamp - element._counterLastTimestamp;
	const elapsed = timestamp - element._counterStartTime;

	// Performance monitoring: detect animation stuttering
	// If frame gaps exceed 50ms (indicating browser lag/blocking), count as frame drop
	if ( frameDelta > 50 && element._counterLastTimestamp !== null ) {
		element._counterFrameDropCount++;

		// Fallback strategy: after 3 frame drops, abandon JS animation for CSS transition
		// This prevents choppy animations when browser is under heavy load
		if ( element._counterFrameDropCount > 3 ) {
			element.style.transition = `opacity ${ Math.max( duration - elapsed, 100 ) }ms ease-out`;
			element.textContent = String( targetValue );
			delete element._counterAnimation;
			return;
		}
	}

	// Calculate eased progress and current value
	const progress = Math.min( elapsed / duration, 1 );
	const easedProgress = easing( progress );
	const currentValue = Math.round( startValue + ( change * easedProgress ) );

	// Only update DOM if value actually changed (reduce unnecessary reflows)
	if ( currentValue !== element._counterLastValue ) {
		element.textContent = String( currentValue );
		element._counterLastValue = currentValue;
	}

	element._counterLastTimestamp = timestamp;

	// Continue animation or finish
	if ( progress < 1 ) {
		element._counterAnimation = requestAnimationFrame( timestamp =>
			animateCounter( timestamp, element, startValue, targetValue, duration, change, easing )
		);
		return;
	}

	// Ensure final value is exact
	element.textContent = String( targetValue );

	// Clean up all counter-related properties
	[ '_counterAnimation', '_counterStartTime', '_counterLastTimestamp', '_counterFrameDropCount', '_counterLastValue' ]
		.forEach( prop => delete element[ prop ] );

	element.style.removeProperty( 'transition' );
};

/**
 * Easing function for smooth animation
 * @param {number} t Progress from 0 to 1
 * @return {number} Eased value
 */
const easeOutQuart = t => 1 - Math.pow( 1 - t, 4 );

export default counter;
