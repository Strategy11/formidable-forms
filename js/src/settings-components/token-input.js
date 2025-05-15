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
	findAndInitializeTokenFields();

	// Adjust padding for all token inputs when field settings are shown
	wp.hooks.addAction( 'frmShowedFieldSettings', 'formidable-token-input', adjustAllTokenInputPaddings );
}

/**
 * Find all token input fields and initialize them
 *
 * @private
 *
 * @return {void}
 */
function findAndInitializeTokenFields() {
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
	const container = createTokenContainer( field );

	if ( ! container ) {
		return;
	}

	const tokensWrapper = container.querySelector( '.frm-tokens' );
	const displayInput = container.querySelector( '.frm-token-display-input' );

	createTokensFromValue( field.value, tokensWrapper );
	addEventListeners( field, displayInput, tokensWrapper );
}

/**
 * Create the token container and input elements
 *
 * @private
 *
 * @param {HTMLElement} field Input field for tokenization
 * @return {HTMLElement|null} The container element or null if already initialized
 */
function createTokenContainer( field ) {
	// Get the main container (.frm-with-right-icon) to work with Formidable's modal system
	const container = field.closest( '.frm-with-right-icon' );

	if ( container.querySelector( '.frm-tokens' ) ) {
		return null;
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

	// Inserting displayInput after the field is important to maintain compatibility with Formidable’s modal system
	field.parentNode.insertBefore( displayInput, field.nextSibling );
	field.classList.add( 'frm_hidden' );

	return container;
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
	if ( ! tokensWrapper ) {
		return;
	}

	// Clear existing tokens if any
	tokensWrapper.innerHTML = '';

	if ( ! value?.trim() ) {
		return;
	}

	// Create tokens from space-separated values
	value.trim()
		.split( /\s+/ )
		.filter( Boolean )
		.forEach( token => createToken( token, tokensWrapper ) );
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
		children: [
			span({
				text: value,
				className: 'frm-token-value'
			}),
			span({
				className: 'frm-token-remove',
				child: svg({ href: '#frm_close_icon' })
			})
		]
	});

	tokensWrapper.appendChild( tokenElement );
	adjustTokenInputPadding( tokensWrapper );
}

/**
 * Add event listeners to token input components
 *
 * @private
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function addEventListeners( field, displayInput, tokensWrapper ) {
	displayInput.addEventListener( 'keydown', event => handleTokenInputKeydown( event, field, displayInput, tokensWrapper ));
	tokensWrapper.addEventListener( 'click', event => handleTokenRemoval( event, field ));
	displayInput.addEventListener( 'blur', () => addTokenFromInput( field, displayInput, tokensWrapper ) );
	// Use jQuery change event to catch programmatic updates, as "Add Layout Classes" triggers value changes via jQuery
	jQuery( field ).on( 'change', () => createTokensFromValue( field.value, tokensWrapper ) );
}

/**
 * Create a token from the current input value
 *
 * @private
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {boolean} Whether a token was added
 */
function addTokenFromInput( field, displayInput, tokensWrapper ) {
	const value = displayInput.value.trim();
	if ( ! value ) {
		return false;
	}

	// Update field value with the new token
	const currentValue = field.value ? field.value + ' ' : '';
	field.value = currentValue + value;

	// Trigger jQuery change event to detect changes and update the builder preview
	jQuery( field ).trigger( 'change' );

	createToken( value, tokensWrapper );
	displayInput.value = '';

	return true;
}

/**
 * Handle keydown events on the display input field
 *
 * @private
 *
 * @param {Event}       event         Keydown event
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function handleTokenInputKeydown( event, field, displayInput, tokensWrapper ) {
	if ( ! [ ' ', ',', 'Enter', 'Tab' ].includes( event.key ) ) {
		return;
	}

	event.preventDefault();
	addTokenFromInput( field, displayInput, tokensWrapper );
}

/**
 * Handle token removal when clicking the remove button
 *
 * @private
 *
 * @param {Event}       event Click event
 * @param {HTMLElement} field The original hidden input field
 * @return {void}
 */
function handleTokenRemoval( event, field ) {
	const removeButton = event.target.closest( '.frm-token-remove' );
	if ( ! removeButton ) {
		return;
	}

	const token = removeButton.closest( '.frm-token' );
	if ( ! token ) {
		return;
	}

	const tokensWrapper = token.parentElement;
	const value = token.querySelector( '.frm-token-value' ).textContent;

	field.value = field.value
		.split( /\s+/ )
		.filter( tokenValue => tokenValue && tokenValue !== value )
		.join( ' ' );

	// Must trigger jQuery change event to detect changes and update the builder preview
	jQuery( field ).trigger( 'change' );

	token.remove();
	adjustTokenInputPadding( tokensWrapper );

	// Focus the input field after token removal
	const displayInput = tokensWrapper.closest( '.frm-token-container' )?.querySelector( '.frm-token-display-input' );
	displayInput?.focus();
}

/**
 * Adjust the padding-left of the display input based on the tokens wrapper width
 *
 * @private
 *
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function adjustTokenInputPadding( tokensWrapper ) {
	if ( ! tokensWrapper ) {
		return;
	}

	// Get the display input using its specific class name
	const displayInput = tokensWrapper.closest( '.frm-token-container' )?.querySelector( '.frm-token-display-input' );
	if ( ! displayInput ) {
		return;
	}

	// Set padding based on whether there are tokens
	const hasTokens = tokensWrapper.children.length > 0;
	displayInput.style.paddingLeft = hasTokens ? `${tokensWrapper.offsetWidth - 4}px` : '';
}

/**
 * Adjust padding for all token inputs on the page
 *
 * @return {void}
 */
function adjustAllTokenInputPaddings() {
	const tokenContainers = document.querySelectorAll( '.frm-token-container' );

	tokenContainers.forEach( container => {
		const tokensWrapper = container.querySelector( '.frm-tokens' );
		if ( tokensWrapper ) {
			adjustTokenInputPadding( tokensWrapper );
		}
	});
}

export { initTokenInputFields };
