'use strict';

/**
 * Detects appendChild calls inside loops (forEach, for, while, etc.).
 * Suggests using DocumentFragment to batch DOM operations.
 */
module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Prefer DocumentFragment when calling appendChild in a loop to avoid multiple reflows.',
		},
		fixable: null, // Can't auto-fix this safely without context
		schema: [],
		messages: {
			useFragment: 'Avoid calling appendChild in a loop. Use DocumentFragment to batch DOM operations and prevent multiple reflows.',
		},
	},

	create( context ) {
		const loopStack = [];

		function enterLoop() {
			loopStack.push( true );
		}

		function exitLoop() {
			loopStack.pop();
		}

		function isInLoop() {
			return loopStack.length > 0;
		}

		return {
			// Enter loops
			ForStatement: enterLoop,
			ForInStatement: enterLoop,
			ForOfStatement: enterLoop,
			WhileStatement: enterLoop,
			DoWhileStatement: enterLoop,

			// Exit loops
			'ForStatement:exit': exitLoop,
			'ForInStatement:exit': exitLoop,
			'ForOfStatement:exit': exitLoop,
			'WhileStatement:exit': exitLoop,
			'DoWhileStatement:exit': exitLoop,

			// Track .forEach, .map on arrays
			CallExpression( node ) {
				const { callee } = node;

				// Check for .forEach/.map
				if ( callee.type === 'MemberExpression' ) {
					const methodName = callee.property.name;

					if ( methodName === 'forEach' || methodName === 'map' ) {
						// Enter loop context
						enterLoop();

						// We'll exit on the exit event, but we need to handle the callback
						return;
					}

					// Check for appendChild inside a loop
					if ( methodName === 'appendChild' || methodName === 'append' || methodName === 'prepend' ) {
						if ( isInLoop() ) {
							context.report({
								node,
								messageId: 'useFragment',
							});
						}
					}
				}
			},

			'CallExpression:exit'( node ) {
				const { callee } = node;

				if ( callee.type === 'MemberExpression' ) {
					const methodName = callee.property.name;

					if ( methodName === 'forEach' || methodName === 'map' ) {
						exitLoop();
					}
				}
			},
		};
	},
};
