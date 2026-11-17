/**
 * Keep preview data in memory until its placeholder approaches the viewport.
 *
 * @since x.x
 */
const pending = new Map();
const visible = new Set();
const RENDER_BUDGET = 4;
const VIEWPORT_MARGIN = '400px 0px';
const SMALL_FORM_LIMIT = 40;
const INITIAL_PREVIEW_COUNT = 15;
let initialFieldIds = new Set();
let observer;
let scheduled = false;

/**
 * Render small forms eagerly and reserve viewport loading for fields after the first fifteen.
 *
 * @since x.x
 * @param {NodeList|HTMLElement[]} fields All builder fields in form order, including rendered fields.
 * @return {void}
 */
export function setInitialFieldPreviews( fields ) {
	const count = fields.length < SMALL_FORM_LIMIT ? fields.length : INITIAL_PREVIEW_COUNT;
	initialFieldIds = new Set( Array.from( fields ).slice( 0, count ).map( field => field.dataset.fid ) );
}

/**
 * @since x.x
 * @param {HTMLElement} placeholder The field's sized placeholder.
 * @param {Function}    render      Replaces the placeholder and returns initialization data.
 * @param {Function}    initialize  Initializes the fields drawn in one slice.
 * @return {void}
 */
export function queueFieldPreview( placeholder, render, initialize ) {
	if ( ! observer ) {
		observer = new IntersectionObserver( entries => {
			entries.forEach( ( { target, isIntersecting } ) => {
				const id = target.dataset.fid;
				if ( isIntersecting ) {
					visible.add( id );
				} else {
					visible.delete( id );
				}
			} );
			scheduleSlice();
		}, {
			root: document.getElementById( 'post-body-content' ),
			rootMargin: VIEWPORT_MARGIN
		} );
	}
	pending.set( placeholder.dataset.fid, { placeholder, render, initialize } );
	if ( initialFieldIds.has( placeholder.dataset.fid ) ) {
		visible.add( placeholder.dataset.fid );
		scheduleSlice();
	} else {
		observer.observe( placeholder );
	}
}

/**
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @return {void}
 */
export function forgetFieldPreview( fieldId ) {
	const key = String( fieldId );
	const preview = pending.get( key );
	if ( preview ) {
		observer.unobserve( preview.placeholder );
		pending.delete( key );
	}
	visible.delete( key );
}

/**
 * Draw a preview immediately when an interaction needs its controls.
 *
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @return {Object|null} Initialization data for the drawn field.
 */
export function renderFieldPreviewNow( fieldId ) {
	const preview = pending.get( String( fieldId ) );
	if ( ! preview ) {
		return null;
	}
	const field = drawPreview( fieldId, preview );
	if ( field ) {
		preview.initialize( [ field ] );
	}
	return field;
}

/**
 * @return {void}
 */
function scheduleSlice() {
	if ( scheduled || ! visible.size ) {
		return;
	}
	scheduled = true;
	requestAnimationFrame( () => setTimeout( renderSlice, 0 ) );
}

/**
 * @return {void}
 */
function renderSlice() {
	scheduled = false;
	const started = performance.now();
	const groups = new Map();
	for ( const id of visible ) {
		const preview = pending.get( id );
		if ( ! preview ) {
			visible.delete( id );
			continue;
		}
		const field = drawPreview( id, preview );
		if ( field ) {
			if ( ! groups.has( preview.initialize ) ) {
				groups.set( preview.initialize, [] );
			}
			groups.get( preview.initialize ).push( field );
		}
		if ( performance.now() - started >= RENDER_BUDGET ) {
			break;
		}
	}
	groups.forEach( ( fields, initialize ) => initialize( fields ) );
	scheduleSlice();
}

/**
 * @param {string} fieldId Numeric field ID.
 * @param {Object} preview The placeholder and renderer registered for the field.
 * @return {Object|null} Initialization data for the drawn field.
 */
function drawPreview( fieldId, preview ) {
	forgetFieldPreview( fieldId );
	return preview.placeholder.isConnected ? preview.render() : null;
}
