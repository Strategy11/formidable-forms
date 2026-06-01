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

/**
 * Walk up the scope chain to find the Variable object for a given name.
 *
 * @param {Object} scope ESLint scope object.
 * @param {string} name  Variable name to resolve.
 * @return {Object|null} The Variable object, or null if not found.
 */
function findVariable( scope, name ) {
	let current = scope;
	while ( current ) {
		const variable = current.set.get( name );
		if ( variable ) {
			return variable;
		}
		current = current.upper;
	}
	return null;
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
		const trackedNodes = new WeakSet();

		function trackIfDomQueryResult( targetNode, valueNode ) {
			if ( targetNode && targetNode.type === 'Identifier' && isDocumentDomQueryCall( valueNode ) ) {
				const scope = sourceCode.getScope( targetNode );
				const variable = findVariable( scope, targetNode.name );
				if ( variable ) {
					trackedNodes.add( variable );
				}
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

				const scope = sourceCode.getScope( identifierNode );
				const variable = findVariable( scope, identifierNode.name );
				if ( ! variable || ! trackedNodes.has( variable ) ) {
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
