'use strict';

/**
 * Checks if a string literal value is safe for strict comparison.
 * A string is safe if it is non-empty and non-numeric.
 *
 * @param {string} value The raw string value (without quotes).
 * @return {boolean} Whether the string is safe for strict comparison.
 */
function isSafeString( value ) {
	if ( value === '' ) {
		return false;
	}

	if ( ! isNaN( Number( value ) ) ) {
		return false;
	}

	return true;
}

/**
 * Gets the raw string value from a Literal AST node.
 *
 * @param {Object} node The AST node.
 * @return {string|null} The string value, or null if not a string literal.
 */
function getStringValue( node ) {
	if ( node.type === 'Literal' && typeof node.value === 'string' ) {
		return node.value;
	}

	if ( node.type === 'TemplateLiteral' && node.expressions.length === 0 && node.quasis.length === 1 ) {
		return node.quasis[0].value.cooked;
	}

	return null;
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Enforce strict equality (===, !==) when comparing against non-empty, non-numeric string literals.',
		},
		fixable: 'code',
		schema: [],
		messages: {
			useStrict: 'Use {{strict}} instead of {{loose}} when comparing to string "{{value}}".',
		},
	},

	create( context ) {
		return {
			BinaryExpression( node ) {
				const { operator, left, right } = node;

				if ( operator !== '==' && operator !== '!=' ) {
					return;
				}

				const leftString = getStringValue( left );
				const rightString = getStringValue( right );

				// At least one side must be a safe string literal.
				if ( leftString === null && rightString === null ) {
					return;
				}

				const stringValue = leftString !== null ? leftString : rightString;

				if ( ! isSafeString( stringValue ) ) {
					return;
				}

				const strictOperator = operator === '==' ? '===' : '!==';

				context.report({
					node,
					messageId: 'useStrict',
					data: {
						strict: strictOperator,
						loose: operator,
						value: stringValue,
					},
					fix( fixer ) {
						return fixer.replaceTextRange(
							[ left.range[1], right.range[0] ],
							context.sourceCode.getText().slice( left.range[1], right.range[0] ).replace( operator, strictOperator )
						);
					},
				});
			},
		};
	},
};
