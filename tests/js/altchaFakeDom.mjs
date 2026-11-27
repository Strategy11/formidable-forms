/**
 * DOM stand-ins for the ALTCHA tests, which run in Node without a browser.
 */

/**
 * A minimal element stand-in: attributes, classes, children, closest() and querySelector().
 */
export class FakeElement {
	constructor( tag, { id = '', classes = [], type = '' } = {} ) {
		this.tagName = tag.toUpperCase();
		this.id = id;
		this.type = type;
		this.attributes = {};
		this.classes = new Set( classes );
		this.children = [];
		this.parent = null;
		this.textContent = '';
		this.listeners = {};
		this.dataset = {};
		this.ownerDocument = { createElement: name => new FakeElement( name ) };
		this.classList = {
			add: name => this.classes.add( name ),
			remove: ( ...names ) => names.forEach( name => this.classes.delete( name ) ),
			contains: name => this.classes.has( name )
		};
	}

	set className( value ) {
		this.classes = new Set( value.split( ' ' ).filter( Boolean ) );
	}

	setAttribute( name, value ) {
		this.attributes[ name ] = String( value );
	}

	getAttribute( name ) {
		return name in this.attributes ? this.attributes[ name ] : null;
	}

	removeAttribute( name ) {
		delete this.attributes[ name ];
	}

	append( child ) {
		child.parent = this;
		this.children.push( child );
	}

	remove() {
		this.parent.children = this.parent.children.filter( child => child !== this );
		this.parent = null;
	}

	matches( selector ) {
		if ( selector === 'input[type="checkbox"]' ) {
			return this.tagName === 'INPUT' && this.type === 'checkbox';
		}

		return selector.split( ',' ).map( part => part.trim() ).some( part => {
			const [ tag, ...classes ] = part.split( '.' );
			return ( ! tag || tag.toUpperCase() === this.tagName ) && classes.every( name => this.classes.has( name ) );
		} );
	}

	descendants() {
		return this.children.flatMap( child => [ child, ...child.descendants() ] );
	}

	querySelector( selector ) {
		return this.descendants().find( el => el.matches( selector ) ) || null;
	}

	querySelectorAll( selector ) {
		return this.descendants().filter( el => el.matches( selector ) );
	}

	closest( selector ) {
		let el = this;

		while ( el && ! el.matches( selector ) ) {
			el = el.parent;
		}

		return el;
	}

	addEventListener( type, listener ) {
		( this.listeners[ type ] ||= [] ).push( listener );
	}

	removeEventListener( type, listener ) {
		this.listeners[ type ] = ( this.listeners[ type ] || [] ).filter( item => item !== listener );
	}

	dispatch( type ) {
		( this.listeners[ type ] || [] ).slice().forEach( listener => listener( { type } ) );
	}
}
