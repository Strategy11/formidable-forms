import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

const source = readFileSync( new URL( '../../js/formidable.js', import.meta.url ), 'utf8' );

test( 'Only a priced dropdown runs the product total calculation', () => {
	let handler;
	let calculations = 0;
	const context = {
		window: { addEventListener() {}, requestAnimationFrame() {} },
		ResizeObserver: class {},
		MutationObserver: class {
			observe() {}
		},
		document: {
			body: { classList: { contains: () => false } },
			addEventListener() {},
			querySelector: () => null,
			getElementsByClassName: () => [],
			querySelectorAll( selector ) {
				if ( '[data-frmtotal]' === selector ) {
					calculations++;
				}
				return [];
			}
		},
		jQuery: () => ( {
			off() {},
			on( event, selector, callback ) {
				if ( 'change' === event && typeof selector === 'string' && selector.includes( 'data-frmprice' ) ) {
					handler = callback;
				}
			},
			ready() {}
		} ),
		__FRMCURR: {}
	};
	runInNewContext( source, context );
	context.frmFrontForm = context.window.frmFrontForm;
	context.frmFrontForm.init();

	assert.equal( typeof handler, 'function' );
	calculations = 0;
	handler.call( { tagName: 'SELECT', querySelector: () => null }, {} );
	assert.equal( calculations, 0 );

	handler.call( { tagName: 'SELECT', querySelector: selector => '[data-frmprice]' === selector ? {} : null }, {} );
	assert.equal( calculations, 1 );

	handler.call( { tagName: 'INPUT' }, {} );
	assert.equal( calculations, 2 );
} );
