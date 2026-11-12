/**
 * A small DOM stand-in for node tests. It supports the selectors the tests use:
 * tag, #id, .class, [attr] and [attr="value"] compounds, comma lists, and a leading ":scope > ".
 */

function toDataAttribute( key ) {
	return `data-${ key.replace( /[A-Z]/g, letter => `-${ letter.toLowerCase() }` ) }`;
}

function parseCompound( selector ) {
	const parts = { tag: '', id: '', classes: [], attrs: [] };
	const pattern = /([.#]?[\w-]+)|\[([\w-]+)(?:="([^"]*)")?\]/g;
	let match;
	while ( ( match = pattern.exec( selector ) ) ) {
		if ( match[ 2 ] ) {
			parts.attrs.push( [ match[ 2 ], match[ 3 ] ] );
		} else if ( match[ 1 ].startsWith( '.' ) ) {
			parts.classes.push( match[ 1 ].slice( 1 ) );
		} else if ( match[ 1 ].startsWith( '#' ) ) {
			parts.id = match[ 1 ].slice( 1 );
		} else {
			parts.tag = match[ 1 ].toUpperCase();
		}
	}
	return parts;
}

export class FakeElement {
	constructor( tagName = 'div', attributes = {} ) {
		this.tagName = tagName.toUpperCase();
		this.nodeType = 1;
		this.children = [];
		this.parentElement = null;
		this.attributeMap = new Map();
		this.innerHTMLValue = '';
		this.textContent = '';
		this.value = '';
		this.checked = false;
		this.styleValues = {};
		const element = this;
		this.style = new Proxy( {}, {
			get( target, prop ) {
				if ( 'setProperty' === prop ) {
					return ( name, value ) => {
						element.styleValues[ name ] = value;
					};
				}
				if ( 'removeProperty' === prop ) {
					return name => delete element.styleValues[ name ];
				}
				return element.styleValues[ prop ] ?? '';
			},
			set( target, prop, value ) {
				element.styleValues[ prop ] = value;
				return true;
			},
		} );
		this.dataset = new Proxy( {}, {
			get: ( target, key ) => element.getAttribute( toDataAttribute( key ) ) ?? undefined,
			set: ( target, key, value ) => {
				element.setAttribute( toDataAttribute( key ), value );
				return true;
			},
		} );
		this.classList = {
			contains: name => element.classes().includes( name ),
			add: ( ...names ) => element.setClasses( [ ...new Set( [ ...element.classes(), ...names ] ) ] ),
			remove: ( ...names ) => element.setClasses( element.classes().filter( name => ! names.includes( name ) ) ),
			toggle: ( name, force ) => {
				const add = force === undefined ? ! element.classList.contains( name ) : force;
				const others = element.classes().filter( className => className !== name );
				element.setClasses( add ? [ ...others, name ] : others );
				return add;
			},
		};
		Object.entries( attributes ).forEach( ( [ name, value ] ) => {
			if ( [ 'value', 'checked', 'type', 'name' ].includes( name ) ) {
				this[ name ] = value;
			}
			if ( 'checked' !== name ) {
				this.setAttribute( name, value );
			}
		} );
	}

	classes() {
		return ( this.getAttribute( 'class' ) || '' ).split( /\s+/ ).filter( Boolean );
	}

	setClasses( names ) {
		this.setAttribute( 'class', names.join( ' ' ) );
	}

	get id() {
		return this.getAttribute( 'id' ) || '';
	}

	get type() {
		return this.typeValue ?? this.getAttribute( 'type' ) ?? '';
	}

	set type( value ) {
		this.typeValue = value;
	}

	get name() {
		return this.nameValue ?? this.getAttribute( 'name' ) ?? '';
	}

	set name( value ) {
		this.nameValue = value;
	}

	get innerHTML() {
		return this.innerHTMLValue;
	}

	set innerHTML( html ) {
		this.innerHTMLValue = html;
		this.children.forEach( child => {
			child.parentElement = null;
		} );
		this.children = [];
	}

	getAttribute( name ) {
		return this.attributeMap.has( name ) ? this.attributeMap.get( name ) : null;
	}

	setAttribute( name, value ) {
		this.attributeMap.set( name, String( value ) );
	}

	removeAttribute( name ) {
		this.attributeMap.delete( name );
	}

	hasAttribute( name ) {
		return this.attributeMap.has( name );
	}

	toggleAttribute( name, force ) {
		if ( force ) {
			this.setAttribute( name, '' );
		} else {
			this.removeAttribute( name );
		}
		return Boolean( force );
	}

	append( ...children ) {
		children.forEach( child => {
			child.remove();
			child.parentElement = this;
			this.children.push( child );
		} );
		return this;
	}

	remove() {
		if ( this.parentElement ) {
			this.parentElement.children = this.parentElement.children.filter( child => child !== this );
			this.parentElement = null;
		}
	}

	replaceWith( other ) {
		const parent = this.parentElement;
		const index = parent.children.indexOf( this );
		other.remove();
		parent.children.splice( index, 1, other );
		other.parentElement = parent;
		this.parentElement = null;
	}

	cloneNode() {
		const clone = new FakeElement( this.tagName );
		this.attributeMap.forEach( ( value, name ) => clone.setAttribute( name, value ) );
		return clone;
	}

	get isConnected() {
		let node = this;
		while ( node.parentElement ) {
			node = node.parentElement;
		}
		return Boolean( node.isDocumentRoot );
	}

	matchesCompound( selector ) {
		const { tag, id, classes, attrs } = parseCompound( selector );
		return ( ! tag || tag === this.tagName ) &&
			( ! id || id === this.id ) &&
			classes.every( name => this.classList.contains( name ) ) &&
			attrs.every( ( [ name, value ] ) => this.hasAttribute( name ) && ( value === undefined || value === this.getAttribute( name ) ) );
	}

	matches( selector ) {
		return selector.split( ',' ).some( part => this.matchesCompound( part.trim() ) );
	}

	closest( selector ) {
		let node = this;
		while ( node ) {
			if ( node.matches( selector ) ) {
				return node;
			}
			node = node.parentElement;
		}
		return null;
	}

	descendants() {
		return this.children.flatMap( child => [ child, ...child.descendants() ] );
	}

	querySelectorAll( selector ) {
		if ( selector.startsWith( ':scope >' ) ) {
			const childSelector = selector.slice( ':scope >'.length ).trim();
			return this.children.filter( child => child.matches( childSelector ) );
		}
		return this.descendants().filter( node => node.matches( selector ) );
	}

	querySelector( selector ) {
		return this.querySelectorAll( selector )[ 0 ] || null;
	}
}

/**
 * Create a fake document and install it, with the element factory, on globalThis.
 *
 * @return {Object} The fake document.
 */
export function installFakeDocument() {
	const root = new FakeElement( 'html' );
	root.isDocumentRoot = true;
	const listeners = {};
	const document = {
		root,
		readyState: 'complete',
		body: root,
		getElementById: id => root.descendants().find( node => node.id === id ) || null,
		querySelectorAll: selector => root.querySelectorAll( selector ),
		querySelector: selector => root.querySelector( selector ),
		addEventListener( type, listener ) {
			( listeners[ type ] = listeners[ type ] || [] ).push( listener );
		},
		dispatchEvent( event ) {
			( listeners[ event.type ] || [] ).forEach( listener => listener( event ) );
		},
	};
	globalThis.document = document;
	return document;
}

/**
 * Create an element with children.
 *
 * @param {string}        tagName    The tag name.
 * @param {Object}        attributes The attributes.
 * @param {FakeElement[]} children   The children.
 * @return {FakeElement} The element.
 */
export function el( tagName, attributes = {}, children = [] ) {
	return new FakeElement( tagName, attributes ).append( ...children );
}

/**
 * Install a minimal wp.hooks that records actions.
 *
 * @return {Object} The recorded actions and the registered action callbacks.
 */
export function installFakeHooks() {
	const recorded = [];
	const callbacks = {};
	globalThis.wp = {
		hooks: {
			doAction( name, ...args ) {
				recorded.push( [ name, ...args ] );
				( callbacks[ name ] || [] ).forEach( callback => callback( ...args ) );
			},
			addAction( name, namespace, callback ) {
				( callbacks[ name ] = callbacks[ name ] || [] ).push( callback );
			},
			applyFilters: ( name, value ) => value,
		},
	};
	return { recorded, callbacks };
}

/**
 * Wait for queued microtasks.
 *
 * @return {Promise<void>}
 */
export function flushMicrotasks() {
	return new Promise( resolve => setTimeout( resolve, 0 ) );
}
