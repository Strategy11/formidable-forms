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
 * Inserts `?.` only at the specific boundary positions where each guard operand ends.
 * All other positions use regular `.` access.
 *
 * @param {string[]} chain             The full chain segments.
 * @param {Set}      optionalPositions Set of 1-based indices in the chain where `?.` should be used.
 * @return {string} The optional chaining expression.
 */
function buildOptionalChain( chain, optionalPositions ) {
	let result = chain[ 0 ];
	for ( let i = 1; i < chain.length; i++ ) {
		result += optionalPositions.has( i ) ? '?.' + chain[ i ] : '.' + chain[ i ];
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

/**
 * Checks if a node is a call expression whose callee is a pure member-access chain.
 * Matches patterns like `a.method()` or `a.b.method()`.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node is a method call on a pure access chain.
 */
function isMethodCall( node ) {
	if ( node.type !== 'CallExpression' ) {
		return false;
	}
	const callee = node.callee;
	return callee.type === 'MemberExpression' && ! callee.computed && isPureAccess( callee.object );
}

/**
 * Gets the access chain for chain-comparison purposes.
 * For pure access nodes, returns the chain directly.
 * For call expressions, returns the chain of the callee (the method access, not the call itself).
 *
 * @param {Object} node       The AST node.
 * @param {Object} sourceCode The source code object.
 * @return {string[]|null} The chain segments, or null if not applicable.
 */
function getAccessChain( node, sourceCode ) {
	if ( isPureAccess( node ) ) {
		return getChain( node, sourceCode );
	}
	if ( isMethodCall( node ) ) {
		return getChain( node.callee, sourceCode );
	}
	return null;
}

/**
 * Checks if a node is used in a boolean context where only truthiness matters.
 * This includes if/while/for/do-while conditions and ternary test expressions.
 * Walks up through parent LogicalExpressions and UnaryExpressions (!) to find the consuming context.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node's value is only used for truthiness.
 */
function isBooleanContext( node ) {
	let current = node;

	// Walk up through wrapping logical expressions and negations.
	while ( current.parent ) {
		const { parent } = current;

		if ( parent.type === 'LogicalExpression' && ( parent.operator === '&&' || parent.operator === '||' ) ) {
			// If we're the right operand, the parent's context determines ours.
			// If we're the left operand, we're already skipping via the left-child check.
			current = parent;
			continue;
		}

		if ( parent.type === 'UnaryExpression' && parent.operator === '!' ) {
			current = parent;
			continue;
		}

		// Check if the consuming parent uses us as a boolean test.
		if ( parent.type === 'IfStatement' && parent.test === current ) {
			return true;
		}
		if ( parent.type === 'WhileStatement' && parent.test === current ) {
			return true;
		}
		if ( parent.type === 'DoWhileStatement' && parent.test === current ) {
			return true;
		}
		if ( parent.type === 'ForStatement' && parent.test === current ) {
			return true;
		}
		if ( parent.type === 'ConditionalExpression' && parent.test === current ) {
			return true;
		}

		return false;
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

				// Only flag when the result is used in a boolean context (if, while, for, ternary test).
				// In other contexts (return, assignment, variable init) the conversion changes the
				// return type from `false` to `undefined`, which alters semantics.
				if ( ! isBooleanContext( node ) ) {
					return;
				}

				const operands = flattenLogical( node, '&&' );
				if ( operands.length < 2 ) {
					return;
				}

				// Find the longest consecutive sequence of operands where each is a strict prefix of the next.
				// All operands must be pure access chains, except the last one which may be a method call.
				let bestStart = -1;
				let bestLength = 0;

				for ( let start = 0; start < operands.length - 1; start++ ) {
					if ( ! isPureAccess( operands[ start ] ) ) {
						continue;
					}

					let length = 1;

					for ( let j = start + 1; j < operands.length; j++ ) {
						const prevChain = getAccessChain( operands[ j - 1 ], sourceCode );
						const currChain = getAccessChain( operands[ j ], sourceCode );

						if ( ! prevChain || ! currChain || ! isStrictPrefix( prevChain, currChain ) ) {
							break;
						}

						// Non-last operands must be pure access (not calls).
						if ( ! isPureAccess( operands[ j - 1 ] ) ) {
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
				// Collect the boundary positions: each guard operand's chain length marks where `?.` goes.
				const optionalPositions = new Set();
				for ( let i = bestStart; i < bestStart + bestLength - 1; i++ ) {
					const guardChain = getAccessChain( operands[ i ], sourceCode );
					optionalPositions.add( guardChain.length );
				}

				const lastOperand = operands[ bestStart + bestLength - 1 ];
				const lastIsCall = isMethodCall( lastOperand );
				const lastChain = lastIsCall
					? getChain( lastOperand.callee, sourceCode )
					: getChain( lastOperand, sourceCode );

				let replacement = buildOptionalChain( lastChain, optionalPositions );

				// Append the call arguments if the last operand is a method call.
				if ( lastIsCall ) {
					const argsText = lastOperand.arguments
						.map( arg => sourceCode.getText( arg ) )
						.join( ', ' );
					replacement += '( ' + argsText + ' )';
				}

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
