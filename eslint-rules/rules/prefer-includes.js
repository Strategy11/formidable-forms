'use strict';

/**
 * Checks if a node is the literal value -1.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node is -1.
 */
function isNegativeOne( node ) {
	if ( node.type === 'UnaryExpression' && node.operator === '-' && node.argument.type === 'Literal' && node.argument.value === 1 ) {
		return true;
	}

	if ( node.type === 'Literal' && node.value === -1 ) {
		return true;
	}

	return false;
}

/**
 * Checks if a node is a .indexOf() call expression.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node is a .indexOf() call.
 */
function isIndexOfCall( node ) {
	return (
		node.type === 'CallExpression' &&
		node.callee.type === 'MemberExpression' &&
		node.callee.property.type === 'Identifier' &&
		node.callee.property.name === 'indexOf' &&
		node.arguments.length === 1
	);
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Prefer `.includes()` over `.indexOf()` comparisons with -1, including yoda-style.',
		},
		fixable: 'code',
		schema: [],
		messages: {
			preferIncludes: 'Use `.includes()` instead of `.indexOf()` comparison with -1.',
		},
	},

	create( context ) {
		const sourceCode = context.sourceCode;

		return {
			BinaryExpression( node ) {
				const { operator, left, right } = node;

				let indexOfNode;
				let negativeOneNode;
				let isNegated;

				// Detect: expr.indexOf(x) !== -1 / expr.indexOf(x) != -1
				// Detect: expr.indexOf(x) === -1 / expr.indexOf(x) == -1
				// Detect: expr.indexOf(x) > -1
				// Detect: expr.indexOf(x) >= 0
				// Detect: -1 !== expr.indexOf(x) / -1 != expr.indexOf(x) (yoda)
				// Detect: -1 === expr.indexOf(x) / -1 == expr.indexOf(x) (yoda)
				// Detect: -1 < expr.indexOf(x) (yoda for > -1)

				if ( isIndexOfCall( left ) && isNegativeOne( right ) ) {
					indexOfNode = left;
					negativeOneNode = right;

					if ( operator === '!==' || operator === '!=' || operator === '>' ) {
						isNegated = false;
					} else if ( operator === '===' || operator === '==' ) {
						isNegated = true;
					} else {
						return;
					}
				} else if ( isNegativeOne( left ) && isIndexOfCall( right ) ) {
					// Yoda style: -1 !== expr.indexOf(x)
					indexOfNode = right;
					negativeOneNode = left;

					if ( operator === '!==' || operator === '!=' || operator === '<' ) {
						isNegated = false;
					} else if ( operator === '===' || operator === '==' ) {
						isNegated = true;
					} else {
						return;
					}
				} else if ( isIndexOfCall( left ) && right.type === 'Literal' && right.value === 0 && operator === '>=' ) {
					// expr.indexOf(x) >= 0
					indexOfNode = left;
					isNegated = false;
				} else {
					return;
				}

				const objectText = sourceCode.getText( indexOfNode.callee.object );
				const argumentText = sourceCode.getText( indexOfNode.arguments[0] );
				const includesCall = `${ objectText }.includes( ${ argumentText } )`;
				const replacement = isNegated ? `! ${ includesCall }` : includesCall;

				context.report({
					node,
					messageId: 'preferIncludes',
					fix( fixer ) {
						return fixer.replaceText( node, replacement );
					},
				});
			},
		};
	},
};
