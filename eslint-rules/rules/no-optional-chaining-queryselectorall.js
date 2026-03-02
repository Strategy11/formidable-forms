'use strict';

/**
 * Detects unnecessary optional chaining on querySelectorAll results.
 * Since querySelectorAll always returns a NodeList (never null/undefined),
 * and NodeList.forEach handles empty lists safely, the ?. is redundant.
 */
module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Disallow unnecessary optional chaining on querySelectorAll and similar DOM methods that never return null.',
		},
		fixable: 'code',
		schema: [],
		messages: {
			unnecessaryChaining: 'Unnecessary optional chaining on {{method}}. {{reason}}',
		},
	},

	create( context ) {
		const sourceCode = context.sourceCode;

		// Methods that always return a value (never null/undefined)
		const alwaysReturns = new Set([
			'querySelectorAll',
			'getElementsByClassName',
			'getElementsByTagName',
			'getElementsByName',
			'children',
		]);

		return {
			ChainExpression( node ) {
				const { expression } = node;

				// Check if this is a call expression with optional chaining
				if ( expression.type !== 'CallExpression' || ! expression.optional ) {
					return;
				}

				const { callee } = expression;

				// Check if callee is a MemberExpression (e.g., document.querySelectorAll)
				if ( callee.type !== 'MemberExpression' ) {
					return;
				}

				const methodName = callee.property.name;

				if ( ! alwaysReturns.has( methodName ) ) {
					return;
				}

				let reason = '';
				if ( methodName === 'querySelectorAll' ) {
					reason = 'querySelectorAll always returns a NodeList. Use .querySelectorAll() without ?.';
				} else if ( methodName === 'children' ) {
					reason = 'children always returns an HTMLCollection. Use .children without ?.';
				} else {
					reason = `${ methodName } always returns a collection. Remove the ?.`;
				}

				context.report({
					node,
					messageId: 'unnecessaryChaining',
					data: {
						method: methodName,
						reason,
					},
					fix( fixer ) {
						// Remove the ?. by replacing the CallExpression with a non-optional version
						const callText = sourceCode.getText( expression );
						const fixedText = callText.replace( /\?\.([([])/g, '$1' );

						return fixer.replaceText( node, fixedText );
					},
				});
			},

			// Also catch cases like: elements?.forEach where elements is from querySelectorAll
			MemberExpression( node ) {
				if ( ! node.optional ) {
					return;
				}

				const { object } = node;

				// Check if object is a direct call to querySelectorAll or similar
				if ( object.type === 'CallExpression' && object.callee.type === 'MemberExpression' ) {
					const methodName = object.callee.property.name;

					if ( alwaysReturns.has( methodName ) ) {
						context.report({
							node,
							messageId: 'unnecessaryChaining',
							data: {
								method: methodName,
								reason: `${ methodName } always returns a collection, so ${ node.property.name } will not be null.`,
							},
							fix( fixer ) {
								// Replace ?. with .
								const objectText = sourceCode.getText( object );
								const propertyText = sourceCode.getText( node.property );
								const fixedText = `${ objectText }.${ propertyText }`;

								return fixer.replaceText( node, fixedText );
							},
						});
					}
				}
			},
		};
	},
};
