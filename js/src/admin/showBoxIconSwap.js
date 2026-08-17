/**
 * Shortcode box icon swap.
 *
 * The glyph is swapped by rewriting the href on a single <use>, from several call sites, so it
 * changes in one frame with nothing to soften it. Watching for the attribute change covers every
 * call site without editing each one. Animation lives in _has-shortcodes.scss.
 *
 * @since x.x
 */

const SWAP_CLASS = 'frm-icon-swapping';

/**
 * Watches every shortcode box for an icon change.
 *
 * @since x.x
 *
 * @return {void}
 */
export function initShowBoxIconSwap() {
	if ( ! window.MutationObserver ) {
		return;
	}

	const observer = new MutationObserver( mutations => {
		// A single swap can report more than once, so collect before touching the DOM.
		const swapped = new Set();

		mutations.forEach( mutation => {
			// Not attributeFilter: it only matches attributes in no namespace, so it misses the
			// xlink:href writes hideShortcodes() makes and closing would not animate.
			if ( 'href' !== mutation.attributeName ) {
				return;
			}

			const svg = mutation.target.parentElement;

			if ( svg?.classList?.contains( 'frm-show-box' ) ) {
				swapped.add( svg );
			}
		} );

		swapped.forEach( replayBloom );
	} );

	observer.observe( document.body, {
		subtree: true,
		attributes: true
	} );
}

/**
 * Restarts the bloom on an icon that just changed.
 *
 * @since x.x
 *
 * @param {HTMLElement} svg The .frm-show-box element.
 * @return {void}
 */
function replayBloom( svg ) {
	svg.classList.remove( SWAP_CLASS );

	// Across two frames, so the removal lands before the class goes back on. Removing and adding
	// within one frame leaves the animation unrestarted on a second swap.
	requestAnimationFrame( () => requestAnimationFrame( () => {
		svg.classList.add( SWAP_CLASS );

		svg.addEventListener(
			'animationend',
			() => svg.classList.remove( SWAP_CLASS ),
			{ once: true }
		);
	} ) );
}
