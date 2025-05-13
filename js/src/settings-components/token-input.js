/**
 * Token Input Component
 *
 * Transforms space-separated values in a text input into selectable tokens
 */

/**
 * Internal dependencies
 */
const { span, svg } = window.frmDom;

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

	createTokensFromValue( field.value, tokensWrapper );
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

export { initTokenInputFields };
