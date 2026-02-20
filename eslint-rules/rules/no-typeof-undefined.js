'use strict';

/**
 * Checks if a node is a typeof expression.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node is a typeof expression.
 */
function isTypeofExpression( node ) {
	return node.type === 'UnaryExpression' && node.operator === 'typeof';
}

/**
 * Checks if a node is the string literal 'undefined'.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether the node is the string 'undefined'.
 */
function isUndefinedString( node ) {
	return node.type === 'Literal' && node.value === 'undefined';
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Disallow comparing `typeof` against the string `"undefined"`. Use direct `=== undefined` comparison instead.',
		},
		fixable: 'code',
		schema: [],
		messages: {
			noTypeofUndefined: 'Compare directly against `undefined` instead of using `typeof` with the string `"undefined"`.',
		},
	},

	create( context ) {
		const sourceCode = context.sourceCode;

		/**
		 * Checks if a variable name is declared in the current scope chain.
		 * Variables declared via var/let/const, function params, imports, and
		 * /*global* / comments all count as declared.
		 *
		 * @param {Object} astNode The AST node where the check occurs.
		 * @param {string} name    The variable name to look up.
		 * @return {boolean} Whether the variable is declared.
		 */
		function isDeclaredVariable( astNode, name ) {
			const scope = sourceCode.getScope( astNode );
			let current = scope;

			while ( current ) {
				const variable = current.set.get( name );
				if ( variable && variable.defs.length > 0 ) {
					return true;
				}

				current = current.upper;
			}

			return false;
		}

		return {
			BinaryExpression( node ) {
				const { operator, left, right } = node;

				if ( operator !== '===' && operator !== '!==' && operator !== '==' && operator !== '!=' ) {
					return;
				}

				let typeofNode;

				// typeof x === 'undefined' or typeof x == 'undefined'
				if ( isTypeofExpression( left ) && isUndefinedString( right ) ) {
					typeofNode = left;
				// 'undefined' === typeof x or 'undefined' == typeof x (yoda)
				} else if ( isUndefinedString( left ) && isTypeofExpression( right ) ) {
					typeofNode = right;
				} else {
					return;
				}

				// Skip bare identifiers that are not declared in scope.
				// typeof is the only safe way to check for undeclared globals
				// (e.g., typeof frmProForm !== 'undefined') without a ReferenceError.
				// Member expressions (e.g., typeof obj.prop) are always safe to convert.
				const argument = typeofNode.argument;
				if ( argument.type === 'Identifier' && ! isDeclaredVariable( node, argument.name ) ) {
					return;
				}

				const argumentText = sourceCode.getText( argument );
				const isEqual = operator === '===' || operator === '==';
				const replacement = isEqual
					? `${ argumentText } === undefined`
					: `${ argumentText } !== undefined`;

				context.report({
					node,
					messageId: 'noTypeofUndefined',
					fix( fixer ) {
						return fixer.replaceText( node, replacement );
					},
				});
			},
		};
	},
};
