/**
 * Field errors for the ALTCHA widget, shown and connected to its checkbox the way Formidable
 * does for other inputs.
 */

const ERROR_SELECTOR = '.frm_error';

/**
 * Widgets that already clear their error once verified, so the listener is only added once.
 *
 * @type {WeakSet<HTMLElement>}
 */
const clearingWidgets = new WeakSet();

/**
 * The widget's checkbox, once the widget has rendered it.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {HTMLInputElement|null} The checkbox.
 */
export const getCheckbox = widget => widget.querySelector( 'input[type="checkbox"]' );

/**
 * The Formidable field container that holds a widget.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {HTMLElement|null} The field container.
 */
const getFieldContainer = widget => widget.closest( '.frm_form_field' );

/**
 * The ID Formidable gives the error message of a field container.
 *
 * @param {HTMLElement} container The field container, such as #frm_field_12_container.
 * @return {string} The error ID, such as frm_error_field_12.
 */
const getErrorId = container => {
	const match = /^frm_field_(.+)_container$/.exec( container.id || '' );
	return match ? `frm_error_field_${ match[ 1 ] }` : 'frm_error_altcha';
};

/**
 * Mark the checkbox invalid and describe it with the error message.
 *
 * @param {HTMLInputElement} checkbox The widget's checkbox.
 * @param {HTMLElement}      error    The error message element.
 * @return {void}
 */
export const linkError = ( checkbox, error ) => {
	if ( ! error.id ) {
		error.id = getErrorId( getFieldContainer( checkbox ) || error );
	}

	const describedBy = ( checkbox.getAttribute( 'aria-describedby' ) || '' ).split( ' ' ).filter( Boolean );

	if ( ! describedBy.includes( error.id ) ) {
		describedBy.unshift( error.id );
	}

	checkbox.setAttribute( 'aria-describedby', describedBy.join( ' ' ) );
	checkbox.setAttribute( 'aria-invalid', 'true' );
};

/**
 * Show an error message in the widget's field, unless the field already shows one.
 *
 * @param {HTMLElement} widget  The altcha-widget element.
 * @param {string}      message The error message.
 * @return {HTMLElement|null} The error message element.
 */
export const showError = ( widget, message ) => {
	const container = getFieldContainer( widget );

	if ( ! container ) {
		return null;
	}

	let error = container.querySelector( ERROR_SELECTOR );

	if ( ! error ) {
		error = container.ownerDocument.createElement( 'div' );
		error.className = 'frm_error';
		error.id = getErrorId( container );
		error.setAttribute( 'role', 'alert' );
		error.textContent = message;
		container.append( error );
	}

	container.classList.add( 'frm_blank_field' );

	return error;
};

/**
 * Remove the widget's field error once it is verified, so a verified checkbox is not shown as invalid.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {void}
 */
export const clearError = widget => {
	const container = getFieldContainer( widget );
	const checkbox = getCheckbox( widget );

	if ( checkbox ) {
		checkbox.removeAttribute( 'aria-invalid' );
	}

	if ( ! container ) {
		return;
	}

	container.classList.remove( 'frm_blank_field' );
	container.querySelectorAll( ERROR_SELECTOR ).forEach( error => {
		if ( checkbox && error.id ) {
			const describedBy = ( checkbox.getAttribute( 'aria-describedby' ) || '' ).split( ' ' ).filter( id => id && id !== error.id );

			if ( describedBy.length ) {
				checkbox.setAttribute( 'aria-describedby', describedBy.join( ' ' ) );
			} else {
				checkbox.removeAttribute( 'aria-describedby' );
			}
		}

		error.remove();
	} );
};

/**
 * Put a widget with an error back in the unverified state, inline in its field, until the visitor verifies again.
 *
 * Verification no longer starts by itself, so the widget does not go back to Verified while its field
 * still shows the error, for example when Formidable focuses the invalid checkbox. A floating widget is
 * shown in its field, next to its error, instead of staying hidden. The error is removed once verified.
 *
 * @param {HTMLElement}      widget The altcha-widget element.
 * @param {HTMLElement|null} error  The error message element in the widget's field.
 * @return {void}
 */
export const resetAfterError = ( widget, error ) => {
	if ( typeof widget.getState !== 'function' ) {
		return;
	}

	if ( widget.getState() !== 'unverified' ) {
		widget.reset();
	}

	widget.configure( { auto: 'off', display: 'standard' } );

	const checkbox = getCheckbox( widget );

	if ( checkbox && error ) {
		linkError( checkbox, error );
	}

	if ( clearingWidgets.has( widget ) ) {
		return;
	}

	clearingWidgets.add( widget );

	const clear = () => {
		if ( widget.getState() !== 'verified' ) {
			return;
		}

		widget.removeEventListener( 'statechange', clear );
		clearingWidgets.delete( widget );
		clearError( widget );
	};

	widget.addEventListener( 'statechange', clear );
};

/**
 * Connect an error that was printed with the page to the widget's checkbox, once the widget renders.
 *
 * Without AJAX, the server's error is in the field before the widget exists, so Formidable never
 * connected it to the checkbox for screen readers.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {void}
 */
export const connectRenderedError = widget => {
	const error = getFieldContainer( widget )?.querySelector( ERROR_SELECTOR );

	if ( error ) {
		resetAfterError( widget, error );
	}
};
