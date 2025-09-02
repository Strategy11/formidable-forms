/**
 * Token elements
 *
 * Functions for creating token DOM elements
 */

/**
 * Internal dependencies
 */
import { HIDDEN_CLASS } from 'core/constants';
import { CLASS_NAMES } from './constants';

const { span, svg, tag } = window.frmDom;

/**
 * Create token container and input elements
 *
 * @param {HTMLElement} field Input field for tokenization
 * @return {HTMLElement|null} The container element or null if already initialized
 */
export function createTokenContainerElement( field ) {
	// Get the main container (.frm-with-right-icon) to work with Formidable's modal system
	const container = field.closest( `.${ CLASS_NAMES.WITH_RIGHT_ICON }` );

	if ( container.querySelector( `.${ CLASS_NAMES.TOKENS_WRAPPER }` ) ) {
		return null;
	}

	container.classList.add( CLASS_NAMES.CONTAINER );

	const tokensWrapper = span( {
		className: CLASS_NAMES.TOKENS_WRAPPER
	} );

	container.insertBefore( tokensWrapper, container.firstChild );

	const proxyInput = tag( 'input', {
		className: CLASS_NAMES.TOKEN_PROXY_INPUT,
		id: `${ field.id }-proxy-input`
	} );

	proxyInput.type = 'text';

	// Inserting proxyInput after the field is important to maintain compatibility with Formidable's modal system
	field.parentNode.insertBefore( proxyInput, field.nextSibling );
	field.classList.add( HIDDEN_CLASS );

	return container;
}

/**
 * Create a single token element
 *
 * @param {string}      value         Token value
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
export function createTokenElement( value, tokensWrapper ) {
	const tokenElement = span( {
		className: CLASS_NAMES.TOKEN,
		children: [
			span( {
				text: value,
				className: CLASS_NAMES.TOKEN_VALUE
			} ),
			span( {
				className: CLASS_NAMES.TOKEN_REMOVE,
				child: svg( { href: '#frm_close_icon' } )
			} )
		]
	} );

	tokensWrapper.appendChild( tokenElement );
}
