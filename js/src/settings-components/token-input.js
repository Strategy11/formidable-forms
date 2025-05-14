/**
 * Token Input Component
 *
 * Transforms space-separated values in a text input into selectable tokens
 */

/**
 * Internal dependencies
 */
const { span, svg, tag } = window.frmDom;

/**
 * Initialize all token input fields on the page
 *
 * @return {void}
 */
function initTokenInputFields() {
	const tokenInputFields = document.querySelectorAll( '.frm-token-input-field' );

	if ( ! tokenInputFields.length ) {
		return;
	}

	// Track processed fields to prevent duplicate initialization
	const processedFields = new Set();

	tokenInputFields.forEach( field => {
		if ( ! processedFields.has( field.id ) ) {
			setupTokenInput( field );
			processedFields.add( field.id );
		}
	});
}

/**
 * Set up a token input field with token container
 *
 * @private
 *
 * @param {HTMLElement} field Input field for tokenization
 */
function setupTokenInput( field ) {
	// Get the main container (.frm-with-right-icon) to work with Formidable's modal system
	const container = field.closest( '.frm-with-right-icon' );

	if ( container.querySelector( '.frm-tokens' ) ) {
		return;
	}

	container.classList.add( 'frm-token-container' );

	const tokensWrapper = span({
		className: 'frm-tokens'
	});
	container.insertBefore( tokensWrapper, container.firstChild );

	const displayInput = tag( 'input', {
		className: 'frm-token-display-input'
	});
	displayInput.type = 'text';

	field.type = 'hidden';

	field.parentNode.insertBefore( displayInput, field );

	createTokensFromValue( field.value, tokensWrapper );

	// Setup event listeners
	addTokenEventListeners( field, displayInput, tokensWrapper );
}

/**
 * Create tokens from a space-separated string value
 *
 * @private
 *
 * @param {string}      value         Space-separated values
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
function createTokensFromValue( value, tokensWrapper ) {
	if ( ! value || ! value.trim() || ! tokensWrapper ) {
		return;
	}

	// Clear existing tokens if any
	tokensWrapper.innerHTML = '';

	// Create tokens from space-separated values
	const tokens = value.trim().split( ' ' );
	tokens.forEach( token => {
		if ( token.trim() !== '' ) {
			createToken( token.trim(), tokensWrapper );
		}
	});
}

/**
 * Create a single token element
 *
 * @private
 *
 * @param {string}      value         Token value
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
function createToken( value, tokensWrapper ) {
	const tokenElement = span({
		className: 'frm-token',
		'data-value': value,
		children: [
			span( value ),
			span({
				className: 'frm-token-remove',
				child: svg({ href: '#frm_close_icon' })
			})
		]
	});

	tokensWrapper.appendChild( tokenElement );
}

/**
 * Add event listeners to token input components
 *
 * @private
 *
 * @param {HTMLElement} hiddenField   The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function addTokenEventListeners( hiddenField, displayInput, tokensWrapper ) {
	displayInput.addEventListener( 'keydown', event => handleTokenInputKeydown( event, hiddenField, displayInput, tokensWrapper ));
	tokensWrapper.addEventListener( 'click', event => handleTokenRemoval( event, hiddenField ));
}

/**
 * Handle keydown events on the display input field
 *
 * @private
 *
 * @param {Event}       event         Keydown event
 * @param {HTMLElement} hiddenField   The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function handleTokenInputKeydown( event, hiddenField, displayInput, tokensWrapper ) {
	if ( event.key === ' ' || event.key === ',' || event.key === 'Enter' ) {
		event.preventDefault();

		const value = displayInput.value.trim();
		if ( value ) {
			const currentValue = hiddenField.value ? hiddenField.value + ' ' : '';
			hiddenField.value = currentValue + value;

			createToken( value, tokensWrapper );
			displayInput.value = '';
		}
	}
}

/**
 * Handle token removal when clicking the remove button
 *
 * @private
 *
 * @param {Event}       event       Click event
 * @param {HTMLElement} hiddenField The original hidden input field
 * @return {void}
 */
function handleTokenRemoval( event, hiddenField ) {
	const removeButton = event.target.closest( '.frm-token-remove' );
	if ( removeButton ) {
		const token = removeButton.closest( '.frm-token' );
		if ( token ) {
			const value = token.getAttribute( 'data-value' );

			const values = hiddenField.value.split( ' ' );
			hiddenField.value = values.filter( tokenValue => tokenValue !== value ).join( ' ' );

			token.remove();
		}
	}
}

export { initTokenInputFields };
