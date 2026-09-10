/**
 * Field list hover pill.
 *
 * One shared element slides to whichever field button is hovered. The per-button hover in
 * _insert-fields.scss remains as the no-JS fallback.
 *
 * @since x.x
 */

const PILL_CLASS = 'frm-insert-fields-hover';
const ACTIVE_CLASS = 'frm-has-hover-pill';
const ANIMATING_CLASS = 'frm-animating';

/**
 * Travel time bounds, in milliseconds.
 */
const MIN_TRAVEL = 120;
const MAX_TRAVEL = 300;
const TRAVEL_PER_PX = 0.9;

/**
 * Travels at or above this dim while moving. Shorter hops keep full opacity.
 *
 * @type {number}
 */
const DIM_ABOVE_TRAVEL = 200;

/**
 * Grace period on the fallback un-dim timer, in milliseconds.
 *
 * @type {number}
 */
const ANIMATING_GRACE = 60;

/**
 * Adds the sliding hover pill to every Add Fields list.
 *
 * Basic, Pricing and Advanced are separate lists, and each needs its own pill because positions
 * are measured against the list the button sits in.
 *
 * @since x.x
 *
 * @return {void}
 */
export function initFieldListHoverPill() {
	document.querySelectorAll( '#frm-insert-fields .field_type_list' ).forEach( addPillTo );
}

/**
 * Adds a pill to a single field list.
 *
 * @since x.x
 *
 * @param {HTMLElement} list The field list.
 * @return {void}
 */
function addPillTo( list ) {
	if ( list.classList.contains( ACTIVE_CLASS ) ) {
		return;
	}

	const pill = document.createElement( 'div' );
	pill.classList.add( PILL_CLASS, 'frm_hidden' );
	list.append( pill );
	list.classList.add( ACTIVE_CLASS );

	let animatingTimeout;
	let currentX = null;
	let currentY = null;

	/**
	 * Ends the dim on transitionend rather than a fixed delay, so a fast sweep across several
	 * cards cannot leave the pill stuck dimmed.
	 *
	 * @return {void}
	 */
	const settle = () => {
		clearTimeout( animatingTimeout );
		pill.classList.remove( ANIMATING_CLASS );
	};

	pill.addEventListener( 'transitionend', event => {
		if ( 'transform' === event.propertyName ) {
			settle();
		}
	} );

	/**
	 * Moves the pill over a field button, matching its size and position.
	 *
	 * @param {HTMLElement} button The hovered anchor.
	 * @return {void}
	 */
	const moveTo = button => {
		const listRect = list.getBoundingClientRect();
		const buttonRect = button.getBoundingClientRect();
		const x = buttonRect.left - listRect.left;
		const y = buttonRect.top - listRect.top;
		const isFirstShow = null === currentX;
		const distance = isFirstShow ? 0 : Math.hypot( x - currentX, y - currentY );
		const travel = Math.min( MAX_TRAVEL, Math.max( MIN_TRAVEL, Math.round( distance * TRAVEL_PER_PX ) ) );

		currentX = x;
		currentY = y;

		pill.style.setProperty( '--frm-pill-travel', `${ travel }ms` );
		pill.style.width = `${ buttonRect.width }px`;
		pill.style.height = `${ buttonRect.height }px`;
		pill.style.transform = `translate(${ x }px, ${ y }px)`;

		pill.classList.remove( 'frm_hidden' );

		if ( isFirstShow || travel < DIM_ABOVE_TRAVEL ) {
			settle();
			return;
		}

		pill.classList.add( ANIMATING_CLASS );

		// Fallback for when transitionend does not fire, e.g. an interrupted or zero-length travel.
		clearTimeout( animatingTimeout );
		animatingTimeout = setTimeout( settle, travel + ANIMATING_GRACE );
	};

	/**
	 * Hides the pill.
	 *
	 * @return {void}
	 */
	const hide = () => {
		settle();
		pill.classList.add( 'frm_hidden' );

		// Hidden means display:none, so the next appearance jumps. Forgetting the position
		// stops that jump being measured as a long travel.
		currentX = null;
		currentY = null;
	};

	// Delegated so field buttons added after load are covered too.
	list.addEventListener( 'mouseover', event => {
		const button = event.target.closest( 'li.frmbutton > a' );

		if ( ! button || button.classList.contains( 'disabled' ) ) {
			hide();
			return;
		}

		moveTo( button );
	} );

	list.addEventListener( 'mouseleave', hide );

	// Dragging a field lifts it away from the cursor, leaving the pill with nothing to sit under.
	list.addEventListener( 'mousedown', hide );
}
