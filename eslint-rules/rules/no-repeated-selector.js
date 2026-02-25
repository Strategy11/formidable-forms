'use strict';

/**
 * Detects repeated calls to querySelector/querySelectorAll with the same selector
 * in the same function scope. Suggests caching the result in a variable.
 */
module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Disallow repeated DOM queries with the same selector. Cache the result in a variable instead.',
		},
		fixable: null, // Can't auto-fix this safely
		schema: [],
		messages: {
			cacheSelector: 'querySelector with selector "{{selector}}" is called {{count}} times. Cache the result in a variable.',
		},
	},

	create( context ) {
		// Track selectors per function scope
		const scopeStack = [];

		function enterScope() {
			scopeStack.push( new Map() );
		}

		function exitScope() {
			const selectorMap = scopeStack.pop();

			// Report violations for this scope
			for ( const [ selector, calls ] of selectorMap.entries() ) {
				if ( calls.length > 1 ) {
					// Report on the second call (first is fine, second+ are wasteful)
					for ( let i = 1; i < calls.length; i++ ) {
						context.report({
							node: calls[ i ],
							messageId: 'cacheSelector',
							data: {
								selector,
								count: calls.length,
							},
						});
					}
				}
			}
		}

		function trackSelector( node, methodName ) {
			if ( scopeStack.length === 0 ) {
				return;
			}

			const selectorMap = scopeStack[ scopeStack.length - 1 ];

			// Only track if there's a literal selector argument
			if ( node.arguments.length === 0 || node.arguments[0].type !== 'Literal' ) {
				return;
			}

			const selector = node.arguments[0].value;

			if ( typeof selector !== 'string' ) {
				return;
			}

			// Use method name + selector as key to differentiate querySelector vs querySelectorAll
			const key = `${ methodName }:${ selector }`;

			if ( ! selectorMap.has( key ) ) {
				selectorMap.set( key, [] );
			}

			selectorMap.get( key ).push( node );
		}

		return {
			// Enter function scope
			FunctionDeclaration: enterScope,
			FunctionExpression: enterScope,
			ArrowFunctionExpression: enterScope,

			// Exit function scope
			'FunctionDeclaration:exit': exitScope,
			'FunctionExpression:exit': exitScope,
			'ArrowFunctionExpression:exit': exitScope,

			// Track querySelector/querySelectorAll calls
			CallExpression( node ) {
				const { callee } = node;

				if ( callee.type !== 'MemberExpression' ) {
					return;
				}

				const methodName = callee.property.name;

				if ( methodName === 'querySelector' || methodName === 'querySelectorAll' ) {
					trackSelector( node, methodName );
				}
			},
		};
	},
};
