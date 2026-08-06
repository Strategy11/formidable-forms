'use strict';

/**
 * jQuery methods that return a new jQuery collection (not a scalar value).
 * Used to propagate jQuery-variable tracking through chained assignments.
 */
const COLLECTION_RETURNING_METHODS = new Set( [
	'add', 'addBack', 'andSelf', 'children', 'closest', 'contents',
	'end', 'eq', 'even', 'filter', 'find', 'first', 'has', 'last',
	'map', 'next', 'nextAll', 'nextUntil', 'not', 'odd', 'offsetParent',
	'parent', 'parents', 'parentsUntil', 'prev', 'prevAll', 'prevUntil',
	'siblings', 'slice', 'clone', 'detach', 'remove', 'replaceAll',
	'wrap', 'wrapAll', 'wrapInner', 'unwrap',
	'addClass', 'removeClass', 'toggleClass',
	'append', 'appendTo', 'prepend', 'prependTo',
	'after', 'before', 'insertAfter', 'insertBefore',
	'hide', 'show', 'toggle', 'fadeIn', 'fadeOut', 'fadeTo', 'fadeToggle',
	'slideDown', 'slideUp', 'slideToggle', 'animate', 'stop', 'delay',
	'css', 'attr', 'removeAttr', 'prop', 'removeProp',
	'on', 'off', 'one', 'trigger', 'triggerHandler',
	'each', 'ready',
] );

/**
 * Checks if a node is a call to jQuery() or $().
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether this is a jQuery constructor call.
 */
function isjQueryConstructor( node ) {
	if ( node.type !== 'CallExpression' ) {
		return false;
	}
	const callee = node.callee;
	return callee.type === 'Identifier' && ( callee.name === 'jQuery' || callee.name === '$' );
}

/**
 * Checks if a node is a method call on a tracked jQuery variable that returns a jQuery collection.
 *
 * @param {Object} node           The AST node.
 * @param {Set}    jQueryVarNames Set of known jQuery variable names in scope.
 * @return {boolean} Whether this is a jQuery-returning method call on a tracked variable.
 */
function isjQueryMethodReturningCollection( node, jQueryVarNames ) {
	if ( node.type !== 'CallExpression' || node.callee.type !== 'MemberExpression' ) {
		return false;
	}

	const { object, property } = node.callee;
	if ( property.type !== 'Identifier' || ! COLLECTION_RETURNING_METHODS.has( property.name ) ) {
		return false;
	}

	if ( object.type === 'Identifier' ) {
		return jQueryVarNames.has( object.name );
	}

	return isjQueryConstructor( object ) || isjQueryChainedCollectionCall( object );
}

/**
 * Checks if a node is a chain of jQuery collection-returning method calls.
 *
 * Recognizes patterns like `obj.closest( 'form' ).find( 'sel' )` where
 * at least two chained methods are collection-returning, treating the
 * chain itself as a strong jQuery signal regardless of the root object.
 *
 * @param {Object} node The AST node.
 * @return {boolean} Whether this is a chained jQuery collection call.
 */
function isjQueryChainedCollectionCall( node ) {
	if ( node.type !== 'CallExpression' || node.callee.type !== 'MemberExpression' ) {
		return false;
	}

	const { property } = node.callee;
	return property.type === 'Identifier' && COLLECTION_RETURNING_METHODS.has( property.name );
}

/**
 * Extracts parameter names annotated with `@param {jQuery}` from a JSDoc block comment.
 *
 * @param {Object} sourceCode The ESLint source code object.
 * @param {Object} node       The function AST node.
 * @return {Array} Parameter names typed as jQuery.
 */
function getjQueryParamNames( sourceCode, node ) {
	const jsdocPattern = /@param\s+\{jQuery\}\s+(\w+)/g;
	const names = [];

	for ( const comment of sourceCode.getCommentsBefore( node ) ) {
		if ( comment.type !== 'Block' ) {
			continue;
		}

		let match;
		while ( ( match = jsdocPattern.exec( comment.value ) ) !== null ) {
			names.push( match[ 1 ] );
		}
	}

	return names;
}

/**
 * Walks a chain of MemberExpression/CallExpression nodes to find the root Identifier.
 *
 * @param {Object} node The AST node.
 * @return {string|null} The root identifier name, or null if not found.
 */
function getRootIdentifier( node ) {
	if ( node.type === 'Identifier' ) {
		return node.name;
	}

	if ( node.type === 'CallExpression' ) {
		if ( node.callee.type === 'MemberExpression' ) {
			return getRootIdentifier( node.callee.object );
		}
		return null;
	}

	if ( node.type === 'MemberExpression' ) {
		return getRootIdentifier( node.object );
	}

	return null;
}

/**
 * Checks if a variable name follows the $ prefix convention for jQuery objects.
 *
 * @param {string} name The variable name.
 * @return {boolean} Whether the name starts with $ followed by a letter.
 */
function is$Prefixed( name ) {
	return name.length > 1 && name[ 0 ] === '$' && name[ 1 ] !== '$';
}

module.exports = {
	meta: {
		type: 'suggestion',
		docs: {
			description: 'Disallow specific jQuery methods on variables that hold jQuery objects. Complements eslint-plugin-no-jquery by tracking variable assignments.',
		},
		fixable: null,
		schema: [
			{
				type: 'object',
				properties: {
					methods: {
						type: 'array',
						items: { type: 'string' },
						uniqueItems: true,
					},
				},
				additionalProperties: false,
			},
		],
		messages: {
			noMethod: 'Avoid using jQuery `.{{method}}()` on `{{variable}}`. Use a native DOM alternative instead.',
		},
	},

	create( context ) {
		const options = context.options[ 0 ] || {};
		const bannedMethods = new Set( options.methods || [] );

		if ( bannedMethods.size === 0 ) {
			return {};
		}

		/**
		 * Track jQuery variable names per function scope.
		 * Each entry is a Set of variable names known to hold jQuery objects.
		 */
		const scopeStack = [];

		/**
		 * Get the current scope's jQuery variable set.
		 *
		 * @return {Set} Set of jQuery variable names.
		 */
		function currentjQueryVars() {
			return scopeStack.length > 0 ? scopeStack[ scopeStack.length - 1 ] : new Set();
		}

		/**
		 * Check if a node initializer is a jQuery expression and track the variable.
		 *
		 * @param {string} name The variable name.
		 * @param {Object} init The initializer AST node.
		 */
		function maybeTrack( name, init ) {
			if ( ! init ) {
				if ( is$Prefixed( name ) ) {
					currentjQueryVars().add( name );
				}
				return;
			}

			if ( isjQueryConstructor( init ) ) {
				currentjQueryVars().add( name );
				return;
			}

			if ( isjQueryMethodReturningCollection( init, currentjQueryVars() ) ) {
				currentjQueryVars().add( name );
				return;
			}

			if ( is$Prefixed( name ) ) {
				currentjQueryVars().add( name );
			}
		}

		return {
			// Scope tracking: push/pop for functions.
			'Program'() {
				scopeStack.push( new Set() );
			},
			'Program:exit'() {
				scopeStack.pop();
			},
			'FunctionDeclaration'() {
				scopeStack.push( new Set() );
			},
			'FunctionDeclaration:exit'() {
				scopeStack.pop();
			},
			'FunctionExpression'() {
				scopeStack.push( new Set() );
			},
			'FunctionExpression:exit'() {
				scopeStack.pop();
			},
			'ArrowFunctionExpression'() {
				scopeStack.push( new Set() );
			},
			'ArrowFunctionExpression:exit'() {
				scopeStack.pop();
			},

			// Track variable declarations: const $form = jQuery(this);
			VariableDeclarator( node ) {
				if ( node.id.type === 'Identifier' ) {
					maybeTrack( node.id.name, node.init );
				}
			},

			// Track assignments: formatted = totalField.prev(...);
			AssignmentExpression( node ) {
				if ( node.left.type === 'Identifier' && node.operator === '=' ) {
					maybeTrack( node.left.name, node.right );
				}
			},

			// Track function parameters with $ prefix or @param {jQuery} annotation.
			'FunctionDeclaration, FunctionExpression, ArrowFunctionExpression'( node ) {
				const jsdocParams = new Set( getjQueryParamNames( context.sourceCode, node ) );

				for ( const param of node.params ) {
					if ( param.type !== 'Identifier' ) {
						continue;
					}

					if ( is$Prefixed( param.name ) || jsdocParams.has( param.name ) ) {
						currentjQueryVars().add( param.name );
					}
				}
			},

			// Detect banned method calls on tracked jQuery variables.
			'CallExpression:exit'( node ) {
				if ( node.callee.type !== 'MemberExpression' ) {
					return;
				}

				const { object, property } = node.callee;
				if ( property.type !== 'Identifier' || ! bannedMethods.has( property.name ) ) {
					return;
				}

				const jQueryVars = currentjQueryVars();

				// Direct call on a tracked variable: trackedVar.method().
				if ( object.type === 'Identifier' && jQueryVars.has( object.name ) ) {
					context.report( {
						node,
						messageId: 'noMethod',
						data: {
							method: property.name,
							variable: object.name,
						},
					} );
					return;
				}

				// Chained call: trackedVar.closest(...).method().
				if ( isjQueryMethodReturningCollection( object, jQueryVars ) || isjQueryChainedCollectionCall( object ) ) {
					const rootName = getRootIdentifier( object );
					context.report( {
						node,
						messageId: 'noMethod',
						data: {
							method: property.name,
							variable: rootName || 'jQuery chain',
						},
					} );
				}
			},
		};
	},
};
