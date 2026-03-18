'use strict';

/**
 * Check if a node is document.getElementById or document.querySelector call.
 *
 * @param {ASTNode|null} node
 * @return {boolean}
 */
function isDocumentDomQueryCall( node ) {
	if ( ! node || node.type !== 'CallExpression' ) {
		return false;
	}

	const callee = node.callee;
	if ( ! callee || callee.type !== 'MemberExpression' ) {
		return false;
	}

	const { object, property } = callee;
	if ( ! object || object.type !== 'Identifier' || object.name !== 'document' ) {
		return false;
	}

	if ( property.type !== 'Identifier' ) {
		return false;
	}

	return property.name === 'getElementById' || property.name === 'querySelector';
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Prefer truthy checks over strict null comparisons for DOM query results',
		},
		fixable: 'code',
		schema: [],
		messages: {
			preferTruthy: 'Use {{preferred}} instead of comparing {{name}} {{operator}} null.',
		},
	},

	create( context ) {
		const { sourceCode } = context;
		const trackedVariables = new Set();

		function trackIfDomQueryResult( targetNode, valueNode ) {
			if ( targetNode && targetNode.type === 'Identifier' && isDocumentDomQueryCall( valueNode ) ) {
				trackedVariables.add( targetNode.name );
			}
		}

		return {
			VariableDeclarator( node ) {
				trackIfDomQueryResult( node.id, node.init );
			},

			AssignmentExpression( node ) {
				trackIfDomQueryResult( node.left, node.right );
			},

			BinaryExpression( node ) {
				const { operator, left, right } = node;

				if ( operator !== '===' && operator !== '!==' ) {
					return;
				}

				let identifierNode = null;
				let literalNode = null;

				if ( left.type === 'Identifier' && right.type === 'Literal' && right.value === null ) {
					identifierNode = left;
					literalNode = right;
				} else if ( right.type === 'Identifier' && left.type === 'Literal' && left.value === null ) {
					identifierNode = right;
					literalNode = left;
				}

				if ( ! identifierNode || ! literalNode ) {
					return;
				}

				if ( ! trackedVariables.has( identifierNode.name ) ) {
					return;
				}

				const identifierText = sourceCode.getText( identifierNode );
				const preferred = operator === '===' ? `!${ identifierText }` : identifierText;
				const operatorLabel = operator === '===' ? '===' : '!==';

				context.report( {
					node,
					messageId: 'preferTruthy',
					data: {
						preferred,
						name: identifierText,
						operator: operatorLabel,
					},
					fix( fixer ) {
						return fixer.replaceText( node, preferred );
					},
				} );
			},
		};
	},
};
