'use strict';

/**
 * Gets the full member expression chain as an array of source text segments.
 * For `a.b.c`, returns ['a', 'b', 'c'].
 * For `a[0].b`, returns ['a[0]', 'b'].
 * Only processes dot-access member expressions for the chain parts.
 *
 * @param {Object} node       The AST node.
 * @param {Object} sourceCode The source code object.
 * @return {string[]} The chain segments.
 */
function getChain( node, sourceCode ) {
	if ( node.type === 'MemberExpression' && ! node.computed && ! node.optional ) {
		return [ ...getChain( node.object, sourceCode ), sourceCode.getText( node.property ) ];
	}
	return [ sourceCode.getText( node ) ];
}

/**
 * Checks if chainA is a prefix of chainB.
 *
 * @param {string[]} chainA The prefix chain.
 * @param {string[]} chainB The full chain.
 * @return {boolean} Whether chainA is a strict prefix of chainB.
 */
function isStrictPrefix( chainA, chainB ) {
	if ( chainA.length >= chainB.length ) {
		return false;
	}
	return chainA.every( ( segment, i ) => segment === chainB[ i ] );
}

/**
 * Builds an optional chaining expression string from a chain.
 * The first `prefixLength` segments use `.`, the rest use `?.`.
 *
 * @param {string[]} chain        The full chain segments.
 * @param {number}   prefixLength Number of segments that are the base (use `.`).
 * @return {string} The optional chaining expression.
 */
function buildOptionalChain( chain, prefixLength ) {
	let result = chain[ 0 ];
	for ( let i = 1; i < chain.length; i++ ) {
		result += i < prefixLength ? '.' + chain[ i ] : '?.' + chain[ i ];
	}
	return result;
}

/**
 * Collects all left-nested LogicalExpression operands with the same operator.
 * `a && b && c` (parsed as `(a && b) && c`) returns [a, b, c].
 *
 * @param {Object} node     The AST node.
 * @param {string} operator The operator to match.
 * @return {Object[]} Flat array of operand nodes.
 */
function flattenLogical( node, operator ) {
	if ( node.type === 'LogicalExpression' && node.operator === operator ) {
		return [ ...flattenLogical( node.left, operator ), node.right ];
	}
	return [ node ];
}

/**
 * Checks if a node is a simple member-access or identifier (no calls, no assignments).
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node is a pure access chain.
 */
function isPureAccess( node ) {
	if ( node.type === 'Identifier' ) {
		return true;
	}
	if ( node.type === 'MemberExpression' ) {
		return isPureAccess( node.object );
	}
	return false;
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Prefer optional chaining (`?.`) over chained `&&` guard expressions (e.g., `a && a.b && a.b.c` becomes `a?.b?.c`).',
		},
		fixable: 'code',
		schema: [],
		messages: {
			preferOptionalChaining: 'Prefer optional chaining: `{{replacement}}`.',
		},
	},

	create( context ) {
		const sourceCode = context.sourceCode;

		/**
		 * Set of nodes already reported to avoid duplicate reports on
		 * inner LogicalExpression nodes that are part of a larger chain.
		 */
		const reported = new WeakSet();

		return {
			LogicalExpression( node ) {
				if ( node.operator !== '&&' ) {
					return;
				}

				// Skip if this node is the left child of a parent && (we process from the outermost).
				if (
					node.parent.type === 'LogicalExpression' &&
					node.parent.operator === '&&' &&
					node.parent.left === node
				) {
					return;
				}

				if ( reported.has( node ) ) {
					return;
				}

				const operands = flattenLogical( node, '&&' );
				if ( operands.length < 2 ) {
					return;
				}

				// Find the longest consecutive sequence of operands where each is a strict prefix of the next,
				// and all are pure access chains.
				let bestStart = -1;
				let bestLength = 0;

				for ( let start = 0; start < operands.length - 1; start++ ) {
					if ( ! isPureAccess( operands[ start ] ) ) {
						continue;
					}

					const startChain = getChain( operands[ start ], sourceCode );
					let length = 1;

					for ( let j = start + 1; j < operands.length; j++ ) {
						if ( ! isPureAccess( operands[ j ] ) ) {
							break;
						}

						const prevChain = getChain( operands[ j - 1 ], sourceCode );
						const currChain = getChain( operands[ j ], sourceCode );

						if ( ! isStrictPrefix( prevChain, currChain ) ) {
							break;
						}
						length++;
					}

					if ( length >= 2 && length > bestLength ) {
						bestStart = start;
						bestLength = length;
					}
				}

				if ( bestStart === -1 ) {
					return;
				}

				// Build the optional chaining replacement for the matched subsequence.
				const firstChain = getChain( operands[ bestStart ], sourceCode );
				const lastChain = getChain( operands[ bestStart + bestLength - 1 ], sourceCode );
				const replacement = buildOptionalChain( lastChain, firstChain.length );

				// Build the full replacement expression including non-matching operands.
				const parts = [];
				for ( let i = 0; i < operands.length; i++ ) {
					if ( i === bestStart ) {
						parts.push( replacement );
						i = bestStart + bestLength - 1;
						continue;
					}
					parts.push( sourceCode.getText( operands[ i ] ) );
				}

				const fullReplacement = parts.join( ' && ' );

				// Determine which node to report on and replace.
				// If the entire LogicalExpression is the match, replace the whole thing.
				// Otherwise we need to replace the outermost node.
				const reportNode = node;

				reported.add( node );

				context.report({
					node: reportNode,
					messageId: 'preferOptionalChaining',
					data: {
						replacement: fullReplacement,
					},
					fix( fixer ) {
						const needsParens = node.parent.type === 'UnaryExpression' ||
							( node.parent.type === 'ConditionalExpression' && node.parent.test === node );
						const text = needsParens ? '( ' + fullReplacement + ' )' : fullReplacement;
						return fixer.replaceText( reportNode, text );
					},
				});
			},
		};
	},
};
