'use strict';

/**
 * Checks if two AST nodes represent the same expression.
 *
 * @param {Object} a First AST node.
 * @param {Object} b Second AST node.
 * @param {Object} sourceCode The source code object.
 * @return {boolean} Whether the nodes represent the same expression.
 */
function isSameExpression( a, b, sourceCode ) {
	return sourceCode.getText( a ) === sourceCode.getText( b );
}

/**
 * Checks if a node is an undefined check (x !== undefined or undefined !== x).
 *
 * @param {Object} node The AST node.
 * @return {Object|null} The expression being checked, or null.
 */
function getUndefinedCheckExpression( node ) {
	if ( node.type !== 'BinaryExpression' || node.operator !== '!==' ) {
		return null;
	}

	if ( node.right.type === 'Identifier' && node.right.name === 'undefined' ) {
		return node.left;
	}

	if ( node.left.type === 'Identifier' && node.left.name === 'undefined' ) {
		return node.right;
	}

	return null;
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Disallow redundant undefined checks before truthy checks (e.g., `x !== undefined && x` simplifies to `x`).',
		},
		fixable: 'code',
		schema: [],
		messages: {
			redundant: 'The `!== undefined` check is redundant because the truthy check already covers it. Use just `{{expression}}`.',
		},
	},

	create( context ) {
		const sourceCode = context.sourceCode;

		return {
			LogicalExpression( node ) {
				if ( node.operator !== '&&' ) {
					return;
				}

				const { left, right } = node;

				// Pattern: x !== undefined && x
				const checkedExpression = getUndefinedCheckExpression( left );
				if ( checkedExpression === null ) {
					return;
				}

				if ( ! isSameExpression( checkedExpression, right, sourceCode ) ) {
					return;
				}

				context.report({
					node,
					messageId: 'redundant',
					data: {
						expression: sourceCode.getText( right ),
					},
					fix( fixer ) {
						return fixer.replaceText( node, sourceCode.getText( right ) );
					},
				});
			},
		};
	},
};
