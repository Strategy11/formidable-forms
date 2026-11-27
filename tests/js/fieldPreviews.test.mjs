import assert from 'node:assert/strict';
import { register } from 'node:module';
import { test } from 'node:test';

// The source imports its siblings without an extension, the way webpack resolves them.
register( `data:text/javascript,${ encodeURIComponent( `
export async function resolve( specifier, context, next ) {
	if ( specifier.startsWith( './' ) && ! /\\.[cm]?js$/.test( specifier ) ) {
		return next( specifier + '.js', context );
	}
	return next( specifier, context );
}
` ) }` );

/**
 * A stand-in for the few element features the previews use.
 *
 * @param {Object} props Class name, text and children.
 * @return {Object} The element.
 */
function element( props = {} ) {
	const node = {
		id: '',
		className: props.className || '',
		textContent: props.text || '',
		innerHTML: '',
		dataset: {},
		children: props.children || ( props.child ? [ props.child ] : [] ),
		// Markup inserted as HTML, as [ position, html ] pairs.
		html: [],
		classList: {
			contains: name => node.className.split( ' ' ).includes( name ),
			add: name => {
				node.className += ` ${ name }`;
			},
			remove() {}
		},
		get firstElementChild() {
			return node.children.find( child => typeof child === 'object' );
		},
		get lastElementChild() {
			return node.children.findLast( child => typeof child === 'object' );
		},
		append( ...nodes ) {
			node.children.push( ...nodes );
		},
		prepend( ...nodes ) {
			node.children.unshift( ...nodes );
		},
		insertAdjacentHTML( position, html ) {
			node.html.push( [ position, html ] );
		},
		setAttribute() {},
		querySelector( selector ) {
			const className = selector.slice( 1 );
			for ( const child of node.children ) {
				if ( typeof child !== 'object' ) {
					continue;
				}
				if ( child.classList.contains( className ) ) {
					return child;
				}
				const match = child.querySelector( selector );
				if ( match ) {
					return match;
				}
			}
			return null;
		},
		cloneNode() {
			return element( {
				className: node.className,
				text: node.textContent,
				children: node.children.map( child => typeof child === 'object' ? child.cloneNode() : child )
			} );
		}
	};
	return node;
}

globalThis.frmDom = {
	div: element,
	span: element,
	a: element,
	svg: element,
	tag: ( tagName, props ) => element( props ),
	setAttributes() {}
};
// Pro draws section inputs through this filter.
globalThis.wp = { hooks: { applyFilters: ( name, value ) => value } };

const { createField, hydrateFieldPreviews, getFieldsWithLogicOn } = await import( '../../js/src/admin/fieldPreviews.js' );

const strings = { collapsed: '(Collapsed)', customHtml: 'Custom HTML:', hiddenNote: '' };

function createTemplate() {
	return element( {
		child: element( {
			className: 'frm_inner_field_container',
			children: [
				element( { child: element( { className: 'frm-field-id' } ) } ),
				' ',
				element( {
					className: 'frm_primary_label',
					children: [ element( { className: 'frm_builder_field_name' } ), ' ', element( { className: 'frm_required' } ) ]
				} ),
				' ',
				element( { className: 'frm_form_fields' } )
			]
		} )
	} );
}

test( 'createField inserts input HTML printed by the server instead of drawing inputs', () => {
	const inputHtml = '<div class="frm_radio"><input type="radio" /> First</div>';
	const field = createField( createTemplate(), { id: 7, type: 'radio', ftype: 'radio', className: 'form-field', inputHtml }, strings );
	const inputs = field.firstElementChild.lastElementChild;

	assert.deepEqual( inputs.html, [ [ 'afterbegin', inputHtml ] ] );
	assert.equal( inputs.children.length, 0, 'No inputs are drawn beside the server markup.' );
} );

test( 'createField draws text inputs when there is no input HTML', () => {
	const field = createField( createTemplate(), { id: 8, type: 'text', ftype: 'text', className: 'form-field', htmlId: 'field_abc' }, strings );
	const inputs = field.firstElementChild.lastElementChild;

	assert.deepEqual( inputs.html, [] );
	assert.equal( inputs.children.length, 2, 'The input and its trailing space are drawn.' );
} );

test( 'createField only labels a section as collapsed', () => {
	const label = data => createField( createTemplate(), data, strings ).firstElementChild.querySelector( '.frm_primary_label' );
	const collapsedLabels = node => node.children.filter( child => typeof child === 'object' && child.classList.contains( 'frm-collapsed-label' ) );

	assert.equal( collapsedLabels( label( { id: 9, type: 'radio', className: 'form-field', inputHtml: '' } ) ).length, 0 );
	assert.equal( collapsedLabels( label( { id: 10, type: 'divider', className: 'form-field divider_section_only' } ) ).length, 1 );
} );

test( 'hydrateFieldPreviews records which fields have conditional logic on each field', () => {
	const manifest = {
		strings,
		definitions: [ [ 'form-field', 1, 'text', 'text' ] ],
		fields: [
			{ id: 20, definition: 0, logic: [ 5, 6 ] },
			{ id: 21, definition: 0, logic: [ 5 ] },
			{ id: 22, definition: 0 }
		]
	};
	const placeholders = manifest.fields.map( ( field, index ) => {
		const placeholder = element();
		placeholder.dataset.frmPreview = String( index );
		return placeholder;
	} );
	globalThis.document = {
		getElementById: id => ( {
			'frm-field-previews': { textContent: JSON.stringify( manifest ), remove() {} },
			'frm-show-fields': { querySelectorAll: () => placeholders }
		}[ id ] ?? null )
	};

	assert.deepEqual( hydrateFieldPreviews(), [ '20', '21', '22' ] );
	assert.deepEqual( getFieldsWithLogicOn( '5' ), [ '20', '21' ] );
	assert.deepEqual( getFieldsWithLogicOn( 6 ), [ '20' ] );
	assert.deepEqual( getFieldsWithLogicOn( '22' ), [] );
} );
