/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@tannin/compile/index.js":
/*!***********************************************!*\
  !*** ./node_modules/@tannin/compile/index.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ compile; }
/* harmony export */ });
/* harmony import */ var _tannin_postfix__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tannin/postfix */ "./node_modules/@tannin/postfix/index.js");
/* harmony import */ var _tannin_evaluate__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @tannin/evaluate */ "./node_modules/@tannin/evaluate/index.js");



/**
 * Given a C expression, returns a function which can be called to evaluate its
 * result.
 *
 * @example
 *
 * ```js
 * import compile from '@tannin/compile';
 *
 * const evaluate = compile( 'n > 1' );
 *
 * evaluate( { n: 2 } );
 * // ⇒ true
 * ```
 *
 * @param {string} expression C expression.
 *
 * @return {(variables?:{[variable:string]:*})=>*} Compiled evaluator.
 */
function compile( expression ) {
	var terms = (0,_tannin_postfix__WEBPACK_IMPORTED_MODULE_0__["default"])( expression );

	return function( variables ) {
		return (0,_tannin_evaluate__WEBPACK_IMPORTED_MODULE_1__["default"])( terms, variables );
	};
}


/***/ }),

/***/ "./node_modules/@tannin/evaluate/index.js":
/*!************************************************!*\
  !*** ./node_modules/@tannin/evaluate/index.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ evaluate; }
/* harmony export */ });
/**
 * Operator callback functions.
 *
 * @type {Object}
 */
var OPERATORS = {
	'!': function( a ) {
		return ! a;
	},
	'*': function( a, b ) {
		return a * b;
	},
	'/': function( a, b ) {
		return a / b;
	},
	'%': function( a, b ) {
		return a % b;
	},
	'+': function( a, b ) {
		return a + b;
	},
	'-': function( a, b ) {
		return a - b;
	},
	'<': function( a, b ) {
		return a < b;
	},
	'<=': function( a, b ) {
		return a <= b;
	},
	'>': function( a, b ) {
		return a > b;
	},
	'>=': function( a, b ) {
		return a >= b;
	},
	'==': function( a, b ) {
		return a === b;
	},
	'!=': function( a, b ) {
		return a !== b;
	},
	'&&': function( a, b ) {
		return a && b;
	},
	'||': function( a, b ) {
		return a || b;
	},
	'?:': function( a, b, c ) {
		if ( a ) {
			throw b;
		}

		return c;
	},
};

/**
 * Given an array of postfix terms and operand variables, returns the result of
 * the postfix evaluation.
 *
 * @example
 *
 * ```js
 * import evaluate from '@tannin/evaluate';
 *
 * // 3 + 4 * 5 / 6 ⇒ '3 4 5 * 6 / +'
 * const terms = [ '3', '4', '5', '*', '6', '/', '+' ];
 *
 * evaluate( terms, {} );
 * // ⇒ 6.333333333333334
 * ```
 *
 * @param {string[]} postfix   Postfix terms.
 * @param {Object}   variables Operand variables.
 *
 * @return {*} Result of evaluation.
 */
function evaluate( postfix, variables ) {
	var stack = [],
		i, j, args, getOperatorResult, term, value;

	for ( i = 0; i < postfix.length; i++ ) {
		term = postfix[ i ];

		getOperatorResult = OPERATORS[ term ];
		if ( getOperatorResult ) {
			// Pop from stack by number of function arguments.
			j = getOperatorResult.length;
			args = Array( j );
			while ( j-- ) {
				args[ j ] = stack.pop();
			}

			try {
				value = getOperatorResult.apply( null, args );
			} catch ( earlyReturn ) {
				return earlyReturn;
			}
		} else if ( variables.hasOwnProperty( term ) ) {
			value = variables[ term ];
		} else {
			value = +term;
		}

		stack.push( value );
	}

	return stack[ 0 ];
}


/***/ }),

/***/ "./node_modules/@tannin/plural-forms/index.js":
/*!****************************************************!*\
  !*** ./node_modules/@tannin/plural-forms/index.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ pluralForms; }
/* harmony export */ });
/* harmony import */ var _tannin_compile__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tannin/compile */ "./node_modules/@tannin/compile/index.js");


/**
 * Given a C expression, returns a function which, when called with a value,
 * evaluates the result with the value assumed to be the "n" variable of the
 * expression. The result will be coerced to its numeric equivalent.
 *
 * @param {string} expression C expression.
 *
 * @return {Function} Evaluator function.
 */
function pluralForms( expression ) {
	var evaluate = (0,_tannin_compile__WEBPACK_IMPORTED_MODULE_0__["default"])( expression );

	return function( n ) {
		return +evaluate( { n: n } );
	};
}


/***/ }),

/***/ "./node_modules/@tannin/postfix/index.js":
/*!***********************************************!*\
  !*** ./node_modules/@tannin/postfix/index.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ postfix; }
/* harmony export */ });
var PRECEDENCE, OPENERS, TERMINATORS, PATTERN;

/**
 * Operator precedence mapping.
 *
 * @type {Object}
 */
PRECEDENCE = {
	'(': 9,
	'!': 8,
	'*': 7,
	'/': 7,
	'%': 7,
	'+': 6,
	'-': 6,
	'<': 5,
	'<=': 5,
	'>': 5,
	'>=': 5,
	'==': 4,
	'!=': 4,
	'&&': 3,
	'||': 2,
	'?': 1,
	'?:': 1,
};

/**
 * Characters which signal pair opening, to be terminated by terminators.
 *
 * @type {string[]}
 */
OPENERS = [ '(', '?' ];

/**
 * Characters which signal pair termination, the value an array with the
 * opener as its first member. The second member is an optional operator
 * replacement to push to the stack.
 *
 * @type {string[]}
 */
TERMINATORS = {
	')': [ '(' ],
	':': [ '?', '?:' ],
};

/**
 * Pattern matching operators and openers.
 *
 * @type {RegExp}
 */
PATTERN = /<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/;

/**
 * Given a C expression, returns the equivalent postfix (Reverse Polish)
 * notation terms as an array.
 *
 * If a postfix string is desired, simply `.join( ' ' )` the result.
 *
 * @example
 *
 * ```js
 * import postfix from '@tannin/postfix';
 *
 * postfix( 'n > 1' );
 * // ⇒ [ 'n', '1', '>' ]
 * ```
 *
 * @param {string} expression C expression.
 *
 * @return {string[]} Postfix terms.
 */
function postfix( expression ) {
	var terms = [],
		stack = [],
		match, operator, term, element;

	while ( ( match = expression.match( PATTERN ) ) ) {
		operator = match[ 0 ];

		// Term is the string preceding the operator match. It may contain
		// whitespace, and may be empty (if operator is at beginning).
		term = expression.substr( 0, match.index ).trim();
		if ( term ) {
			terms.push( term );
		}

		while ( ( element = stack.pop() ) ) {
			if ( TERMINATORS[ operator ] ) {
				if ( TERMINATORS[ operator ][ 0 ] === element ) {
					// Substitution works here under assumption that because
					// the assigned operator will no longer be a terminator, it
					// will be pushed to the stack during the condition below.
					operator = TERMINATORS[ operator ][ 1 ] || operator;
					break;
				}
			} else if ( OPENERS.indexOf( element ) >= 0 || PRECEDENCE[ element ] < PRECEDENCE[ operator ] ) {
				// Push to stack if either an opener or when pop reveals an
				// element of lower precedence.
				stack.push( element );
				break;
			}

			// For each popped from stack, push to terms.
			terms.push( element );
		}

		if ( ! TERMINATORS[ operator ] ) {
			stack.push( operator );
		}

		// Slice matched fragment from expression to continue match.
		expression = expression.substr( match.index + operator.length );
	}

	// Push remainder of operand, if exists, to terms.
	expression = expression.trim();
	if ( expression ) {
		terms.push( expression );
	}

	// Pop remaining items from stack into terms.
	return terms.concat( stack.reverse() );
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createAddHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createAddHook.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _validateNamespace_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./validateNamespace.js */ "./node_modules/@wordpress/hooks/build-module/validateNamespace.js");
/* harmony import */ var _validateHookName_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./validateHookName.js */ "./node_modules/@wordpress/hooks/build-module/validateHookName.js");
/**
 * Internal dependencies
 */


/**
 * @callback AddHook
 *
 * Adds the hook to the appropriate hooks container.
 *
 * @param {string}               hookName  Name of hook to add
 * @param {string}               namespace The unique namespace identifying the callback in the form `vendor/plugin/function`.
 * @param {import('.').Callback} callback  Function to call when the hook is run
 * @param {number}               [priority=10]  Priority of this hook
 */

/**
 * Returns a function which, when invoked, will add a hook.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 *
 * @return {AddHook} Function that adds a new hook.
 */

function createAddHook(hooks, storeKey) {
  return function addHook(hookName, namespace, callback) {
    var priority = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 10;
    var hooksStore = hooks[storeKey];

    if (!(0,_validateHookName_js__WEBPACK_IMPORTED_MODULE_1__["default"])(hookName)) {
      return;
    }

    if (!(0,_validateNamespace_js__WEBPACK_IMPORTED_MODULE_0__["default"])(namespace)) {
      return;
    }

    if ('function' !== typeof callback) {
      // eslint-disable-next-line no-console
      console.error('The hook callback must be a function.');
      return;
    } // Validate numeric priority


    if ('number' !== typeof priority) {
      // eslint-disable-next-line no-console
      console.error('If specified, the hook priority must be a number.');
      return;
    }

    var handler = {
      callback: callback,
      priority: priority,
      namespace: namespace
    };

    if (hooksStore[hookName]) {
      // Find the correct insert index of the new hook.
      var handlers = hooksStore[hookName].handlers;
      /** @type {number} */

      var i;

      for (i = handlers.length; i > 0; i--) {
        if (priority >= handlers[i - 1].priority) {
          break;
        }
      }

      if (i === handlers.length) {
        // If append, operate via direct assignment.
        handlers[i] = handler;
      } else {
        // Otherwise, insert before index via splice.
        handlers.splice(i, 0, handler);
      } // We may also be currently executing this hook.  If the callback
      // we're adding would come after the current callback, there's no
      // problem; otherwise we need to increase the execution index of
      // any other runs by 1 to account for the added element.


      hooksStore.__current.forEach(function (hookInfo) {
        if (hookInfo.name === hookName && hookInfo.currentIndex >= i) {
          hookInfo.currentIndex++;
        }
      });
    } else {
      // This is the first hook of its type.
      hooksStore[hookName] = {
        handlers: [handler],
        runs: 0
      };
    }

    if (hookName !== 'hookAdded') {
      hooks.doAction('hookAdded', hookName, namespace, callback, priority);
    }
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createAddHook);
//# sourceMappingURL=createAddHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createCurrentHook.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createCurrentHook.js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * Returns a function which, when invoked, will return the name of the
 * currently running hook, or `null` if no hook of the given type is currently
 * running.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 *
 * @return {() => string | null} Function that returns the current hook name or null.
 */
function createCurrentHook(hooks, storeKey) {
  return function currentHook() {
    var _hooksStore$__current, _hooksStore$__current2;

    var hooksStore = hooks[storeKey];
    return (_hooksStore$__current = (_hooksStore$__current2 = hooksStore.__current[hooksStore.__current.length - 1]) === null || _hooksStore$__current2 === void 0 ? void 0 : _hooksStore$__current2.name) !== null && _hooksStore$__current !== void 0 ? _hooksStore$__current : null;
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createCurrentHook);
//# sourceMappingURL=createCurrentHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createDidHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createDidHook.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _validateHookName_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./validateHookName.js */ "./node_modules/@wordpress/hooks/build-module/validateHookName.js");
/**
 * Internal dependencies
 */

/**
 * @callback DidHook
 *
 * Returns the number of times an action has been fired.
 *
 * @param  {string} hookName The hook name to check.
 *
 * @return {number | undefined} The number of times the hook has run.
 */

/**
 * Returns a function which, when invoked, will return the number of times a
 * hook has been called.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 *
 * @return {DidHook} Function that returns a hook's call count.
 */

function createDidHook(hooks, storeKey) {
  return function didHook(hookName) {
    var hooksStore = hooks[storeKey];

    if (!(0,_validateHookName_js__WEBPACK_IMPORTED_MODULE_0__["default"])(hookName)) {
      return;
    }

    return hooksStore[hookName] && hooksStore[hookName].runs ? hooksStore[hookName].runs : 0;
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createDidHook);
//# sourceMappingURL=createDidHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createDoingHook.js":
/*!***********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createDoingHook.js ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * @callback DoingHook
 * Returns whether a hook is currently being executed.
 *
 * @param  {string} [hookName] The name of the hook to check for.  If
 *                             omitted, will check for any hook being executed.
 *
 * @return {boolean} Whether the hook is being executed.
 */

/**
 * Returns a function which, when invoked, will return whether a hook is
 * currently being executed.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 *
 * @return {DoingHook} Function that returns whether a hook is currently
 *                     being executed.
 */
function createDoingHook(hooks, storeKey) {
  return function doingHook(hookName) {
    var hooksStore = hooks[storeKey]; // If the hookName was not passed, check for any current hook.

    if ('undefined' === typeof hookName) {
      return 'undefined' !== typeof hooksStore.__current[0];
    } // Return the __current hook.


    return hooksStore.__current[0] ? hookName === hooksStore.__current[0].name : false;
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createDoingHook);
//# sourceMappingURL=createDoingHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createHasHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createHasHook.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * @callback HasHook
 *
 * Returns whether any handlers are attached for the given hookName and optional namespace.
 *
 * @param {string} hookName    The name of the hook to check for.
 * @param {string} [namespace] Optional. The unique namespace identifying the callback
 *                             in the form `vendor/plugin/function`.
 *
 * @return {boolean} Whether there are handlers that are attached to the given hook.
 */

/**
 * Returns a function which, when invoked, will return whether any handlers are
 * attached to a particular hook.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 *
 * @return {HasHook} Function that returns whether any handlers are
 *                   attached to a particular hook and optional namespace.
 */
function createHasHook(hooks, storeKey) {
  return function hasHook(hookName, namespace) {
    var hooksStore = hooks[storeKey]; // Use the namespace if provided.

    if ('undefined' !== typeof namespace) {
      return hookName in hooksStore && hooksStore[hookName].handlers.some(function (hook) {
        return hook.namespace === namespace;
      });
    }

    return hookName in hooksStore;
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createHasHook);
//# sourceMappingURL=createHasHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createHooks.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createHooks.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _Hooks: function() { return /* binding */ _Hooks; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/classCallCheck */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _createAddHook__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./createAddHook */ "./node_modules/@wordpress/hooks/build-module/createAddHook.js");
/* harmony import */ var _createRemoveHook__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./createRemoveHook */ "./node_modules/@wordpress/hooks/build-module/createRemoveHook.js");
/* harmony import */ var _createHasHook__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./createHasHook */ "./node_modules/@wordpress/hooks/build-module/createHasHook.js");
/* harmony import */ var _createRunHook__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./createRunHook */ "./node_modules/@wordpress/hooks/build-module/createRunHook.js");
/* harmony import */ var _createCurrentHook__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./createCurrentHook */ "./node_modules/@wordpress/hooks/build-module/createCurrentHook.js");
/* harmony import */ var _createDoingHook__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./createDoingHook */ "./node_modules/@wordpress/hooks/build-module/createDoingHook.js");
/* harmony import */ var _createDidHook__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./createDidHook */ "./node_modules/@wordpress/hooks/build-module/createDidHook.js");


/**
 * Internal dependencies
 */







/**
 * Internal class for constructing hooks. Use `createHooks()` function
 *
 * Note, it is necessary to expose this class to make its type public.
 *
 * @private
 */

var _Hooks = function _Hooks() {
  (0,_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, _Hooks);

  /** @type {import('.').Store} actions */
  this.actions = Object.create(null);
  this.actions.__current = [];
  /** @type {import('.').Store} filters */

  this.filters = Object.create(null);
  this.filters.__current = [];
  this.addAction = (0,_createAddHook__WEBPACK_IMPORTED_MODULE_1__["default"])(this, 'actions');
  this.addFilter = (0,_createAddHook__WEBPACK_IMPORTED_MODULE_1__["default"])(this, 'filters');
  this.removeAction = (0,_createRemoveHook__WEBPACK_IMPORTED_MODULE_2__["default"])(this, 'actions');
  this.removeFilter = (0,_createRemoveHook__WEBPACK_IMPORTED_MODULE_2__["default"])(this, 'filters');
  this.hasAction = (0,_createHasHook__WEBPACK_IMPORTED_MODULE_3__["default"])(this, 'actions');
  this.hasFilter = (0,_createHasHook__WEBPACK_IMPORTED_MODULE_3__["default"])(this, 'filters');
  this.removeAllActions = (0,_createRemoveHook__WEBPACK_IMPORTED_MODULE_2__["default"])(this, 'actions', true);
  this.removeAllFilters = (0,_createRemoveHook__WEBPACK_IMPORTED_MODULE_2__["default"])(this, 'filters', true);
  this.doAction = (0,_createRunHook__WEBPACK_IMPORTED_MODULE_4__["default"])(this, 'actions');
  this.applyFilters = (0,_createRunHook__WEBPACK_IMPORTED_MODULE_4__["default"])(this, 'filters', true);
  this.currentAction = (0,_createCurrentHook__WEBPACK_IMPORTED_MODULE_5__["default"])(this, 'actions');
  this.currentFilter = (0,_createCurrentHook__WEBPACK_IMPORTED_MODULE_5__["default"])(this, 'filters');
  this.doingAction = (0,_createDoingHook__WEBPACK_IMPORTED_MODULE_6__["default"])(this, 'actions');
  this.doingFilter = (0,_createDoingHook__WEBPACK_IMPORTED_MODULE_6__["default"])(this, 'filters');
  this.didAction = (0,_createDidHook__WEBPACK_IMPORTED_MODULE_7__["default"])(this, 'actions');
  this.didFilter = (0,_createDidHook__WEBPACK_IMPORTED_MODULE_7__["default"])(this, 'filters');
};
/** @typedef {_Hooks} Hooks */

/**
 * Returns an instance of the hooks object.
 *
 * @return {Hooks} A Hooks instance.
 */

function createHooks() {
  return new _Hooks();
}

/* harmony default export */ __webpack_exports__["default"] = (createHooks);
//# sourceMappingURL=createHooks.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createRemoveHook.js":
/*!************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createRemoveHook.js ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _validateNamespace_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./validateNamespace.js */ "./node_modules/@wordpress/hooks/build-module/validateNamespace.js");
/* harmony import */ var _validateHookName_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./validateHookName.js */ "./node_modules/@wordpress/hooks/build-module/validateHookName.js");
/**
 * Internal dependencies
 */


/**
 * @callback RemoveHook
 * Removes the specified callback (or all callbacks) from the hook with a given hookName
 * and namespace.
 *
 * @param {string} hookName  The name of the hook to modify.
 * @param {string} namespace The unique namespace identifying the callback in the
 *                           form `vendor/plugin/function`.
 *
 * @return {number | undefined} The number of callbacks removed.
 */

/**
 * Returns a function which, when invoked, will remove a specified hook or all
 * hooks by the given name.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 * @param  {boolean}              [removeAll=false] Whether to remove all callbacks for a hookName,
 *                                                  without regard to namespace. Used to create
 *                                                  `removeAll*` functions.
 *
 * @return {RemoveHook} Function that removes hooks.
 */

function createRemoveHook(hooks, storeKey) {
  var removeAll = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
  return function removeHook(hookName, namespace) {
    var hooksStore = hooks[storeKey];

    if (!(0,_validateHookName_js__WEBPACK_IMPORTED_MODULE_1__["default"])(hookName)) {
      return;
    }

    if (!removeAll && !(0,_validateNamespace_js__WEBPACK_IMPORTED_MODULE_0__["default"])(namespace)) {
      return;
    } // Bail if no hooks exist by this name


    if (!hooksStore[hookName]) {
      return 0;
    }

    var handlersRemoved = 0;

    if (removeAll) {
      handlersRemoved = hooksStore[hookName].handlers.length;
      hooksStore[hookName] = {
        runs: hooksStore[hookName].runs,
        handlers: []
      };
    } else {
      // Try to find the specified callback to remove.
      var handlers = hooksStore[hookName].handlers;

      var _loop = function _loop(i) {
        if (handlers[i].namespace === namespace) {
          handlers.splice(i, 1);
          handlersRemoved++; // This callback may also be part of a hook that is
          // currently executing.  If the callback we're removing
          // comes after the current callback, there's no problem;
          // otherwise we need to decrease the execution index of any
          // other runs by 1 to account for the removed element.

          hooksStore.__current.forEach(function (hookInfo) {
            if (hookInfo.name === hookName && hookInfo.currentIndex >= i) {
              hookInfo.currentIndex--;
            }
          });
        }
      };

      for (var i = handlers.length - 1; i >= 0; i--) {
        _loop(i);
      }
    }

    if (hookName !== 'hookRemoved') {
      hooks.doAction('hookRemoved', hookName, namespace);
    }

    return handlersRemoved;
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createRemoveHook);
//# sourceMappingURL=createRemoveHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createRunHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createRunHook.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_esm_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/toConsumableArray */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");


/**
 * Returns a function which, when invoked, will execute all callbacks
 * registered to a hook of the specified type, optionally returning the final
 * value of the call chain.
 *
 * @param  {import('.').Hooks}    hooks Hooks instance.
 * @param  {import('.').StoreKey} storeKey
 * @param  {boolean}              [returnFirstArg=false] Whether each hook callback is expected to
 *                                                       return its first argument.
 *
 * @return {(hookName:string, ...args: unknown[]) => unknown} Function that runs hook callbacks.
 */
function createRunHook(hooks, storeKey) {
  var returnFirstArg = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
  return function runHooks(hookName) {
    var hooksStore = hooks[storeKey];

    if (!hooksStore[hookName]) {
      hooksStore[hookName] = {
        handlers: [],
        runs: 0
      };
    }

    hooksStore[hookName].runs++;
    var handlers = hooksStore[hookName].handlers; // The following code is stripped from production builds.

    if (true) {
      // Handle any 'all' hooks registered.
      if ('hookAdded' !== hookName && hooksStore.all) {
        handlers.push.apply(handlers, (0,_babel_runtime_helpers_esm_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(hooksStore.all.handlers));
      }
    }

    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    if (!handlers || !handlers.length) {
      return returnFirstArg ? args[0] : undefined;
    }

    var hookInfo = {
      name: hookName,
      currentIndex: 0
    };

    hooksStore.__current.push(hookInfo);

    while (hookInfo.currentIndex < handlers.length) {
      var handler = handlers[hookInfo.currentIndex];
      var result = handler.callback.apply(null, args);

      if (returnFirstArg) {
        args[0] = result;
      }

      hookInfo.currentIndex++;
    }

    hooksStore.__current.pop();

    if (returnFirstArg) {
      return args[0];
    }
  };
}

/* harmony default export */ __webpack_exports__["default"] = (createRunHook);
//# sourceMappingURL=createRunHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/index.js":
/*!*************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/index.js ***!
  \*************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   actions: function() { return /* binding */ actions; },
/* harmony export */   addAction: function() { return /* binding */ addAction; },
/* harmony export */   addFilter: function() { return /* binding */ addFilter; },
/* harmony export */   applyFilters: function() { return /* binding */ applyFilters; },
/* harmony export */   createHooks: function() { return /* reexport safe */ _createHooks__WEBPACK_IMPORTED_MODULE_0__["default"]; },
/* harmony export */   currentAction: function() { return /* binding */ currentAction; },
/* harmony export */   currentFilter: function() { return /* binding */ currentFilter; },
/* harmony export */   defaultHooks: function() { return /* binding */ defaultHooks; },
/* harmony export */   didAction: function() { return /* binding */ didAction; },
/* harmony export */   didFilter: function() { return /* binding */ didFilter; },
/* harmony export */   doAction: function() { return /* binding */ doAction; },
/* harmony export */   doingAction: function() { return /* binding */ doingAction; },
/* harmony export */   doingFilter: function() { return /* binding */ doingFilter; },
/* harmony export */   filters: function() { return /* binding */ filters; },
/* harmony export */   hasAction: function() { return /* binding */ hasAction; },
/* harmony export */   hasFilter: function() { return /* binding */ hasFilter; },
/* harmony export */   removeAction: function() { return /* binding */ removeAction; },
/* harmony export */   removeAllActions: function() { return /* binding */ removeAllActions; },
/* harmony export */   removeAllFilters: function() { return /* binding */ removeAllFilters; },
/* harmony export */   removeFilter: function() { return /* binding */ removeFilter; }
/* harmony export */ });
/* harmony import */ var _createHooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./createHooks */ "./node_modules/@wordpress/hooks/build-module/createHooks.js");
/**
 * Internal dependencies
 */

/** @typedef {(...args: any[])=>any} Callback */

/**
 * @typedef Handler
 * @property {Callback} callback  The callback
 * @property {string}   namespace The namespace
 * @property {number}   priority  The namespace
 */

/**
 * @typedef Hook
 * @property {Handler[]} handlers Array of handlers
 * @property {number}    runs     Run counter
 */

/**
 * @typedef Current
 * @property {string} name         Hook name
 * @property {number} currentIndex The index
 */

/**
 * @typedef {Record<string, Hook> & {__current: Current[]}} Store
 */

/**
 * @typedef {'actions' | 'filters'} StoreKey
 */

/**
 * @typedef {import('./createHooks').Hooks} Hooks
 */

var defaultHooks = (0,_createHooks__WEBPACK_IMPORTED_MODULE_0__["default"])();
var addAction = defaultHooks.addAction,
    addFilter = defaultHooks.addFilter,
    removeAction = defaultHooks.removeAction,
    removeFilter = defaultHooks.removeFilter,
    hasAction = defaultHooks.hasAction,
    hasFilter = defaultHooks.hasFilter,
    removeAllActions = defaultHooks.removeAllActions,
    removeAllFilters = defaultHooks.removeAllFilters,
    doAction = defaultHooks.doAction,
    applyFilters = defaultHooks.applyFilters,
    currentAction = defaultHooks.currentAction,
    currentFilter = defaultHooks.currentFilter,
    doingAction = defaultHooks.doingAction,
    doingFilter = defaultHooks.doingFilter,
    didAction = defaultHooks.didAction,
    didFilter = defaultHooks.didFilter,
    actions = defaultHooks.actions,
    filters = defaultHooks.filters;

//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/validateHookName.js":
/*!************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/validateHookName.js ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * Validate a hookName string.
 *
 * @param  {string} hookName The hook name to validate. Should be a non empty string containing
 *                           only numbers, letters, dashes, periods and underscores. Also,
 *                           the hook name cannot begin with `__`.
 *
 * @return {boolean}            Whether the hook name is valid.
 */
function validateHookName(hookName) {
  if ('string' !== typeof hookName || '' === hookName) {
    // eslint-disable-next-line no-console
    console.error('The hook name must be a non-empty string.');
    return false;
  }

  if (/^__/.test(hookName)) {
    // eslint-disable-next-line no-console
    console.error('The hook name cannot begin with `__`.');
    return false;
  }

  if (!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(hookName)) {
    // eslint-disable-next-line no-console
    console.error('The hook name can only contain numbers, letters, dashes, periods and underscores.');
    return false;
  }

  return true;
}

/* harmony default export */ __webpack_exports__["default"] = (validateHookName);
//# sourceMappingURL=validateHookName.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/validateNamespace.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/validateNamespace.js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * Validate a namespace string.
 *
 * @param  {string} namespace The namespace to validate - should take the form
 *                            `vendor/plugin/function`.
 *
 * @return {boolean}             Whether the namespace is valid.
 */
function validateNamespace(namespace) {
  if ('string' !== typeof namespace || '' === namespace) {
    // eslint-disable-next-line no-console
    console.error('The namespace must be a non-empty string.');
    return false;
  }

  if (!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(namespace)) {
    // eslint-disable-next-line no-console
    console.error('The namespace can only contain numbers, letters, dashes, periods, underscores and slashes.');
    return false;
  }

  return true;
}

/* harmony default export */ __webpack_exports__["default"] = (validateNamespace);
//# sourceMappingURL=validateNamespace.js.map

/***/ }),

/***/ "./node_modules/@wordpress/i18n/build-module/create-i18n.js":
/*!******************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/build-module/create-i18n.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createI18n: function() { return /* binding */ createI18n; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/defineProperty */ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var tannin__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tannin */ "./node_modules/tannin/index.js");


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { (0,_babel_runtime_helpers_esm_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * External dependencies
 */

/**
 * @typedef {Record<string,any>} LocaleData
 */

/**
 * Default locale data to use for Tannin domain when not otherwise provided.
 * Assumes an English plural forms expression.
 *
 * @type {LocaleData}
 */

var DEFAULT_LOCALE_DATA = {
  '': {
    /** @param {number} n */
    plural_forms: function plural_forms(n) {
      return n === 1 ? 0 : 1;
    }
  }
};
/*
 * Regular expression that matches i18n hooks like `i18n.gettext`, `i18n.ngettext`,
 * `i18n.gettext_domain` or `i18n.ngettext_with_context` or `i18n.has_translation`.
 */

var I18N_HOOK_REGEXP = /^i18n\.(n?gettext|has_translation)(_|$)/;
/**
 * @typedef {(domain?: string) => LocaleData} GetLocaleData
 *
 * Returns locale data by domain in a
 * Jed-formatted JSON object shape.
 *
 * @see http://messageformat.github.io/Jed/
 */

/**
 * @typedef {(data?: LocaleData, domain?: string) => void} SetLocaleData
 *
 * Merges locale data into the Tannin instance by domain. Accepts data in a
 * Jed-formatted JSON object shape.
 *
 * @see http://messageformat.github.io/Jed/
 */

/**
 * @typedef {(data?: LocaleData, domain?: string) => void} ResetLocaleData
 *
 * Resets all current Tannin instance locale data and sets the specified
 * locale data for the domain. Accepts data in a Jed-formatted JSON object shape.
 *
 * @see http://messageformat.github.io/Jed/
 */

/** @typedef {() => void} SubscribeCallback */

/** @typedef {() => void} UnsubscribeCallback */

/**
 * @typedef {(callback: SubscribeCallback) => UnsubscribeCallback} Subscribe
 *
 * Subscribes to changes of locale data
 */

/**
 * @typedef {(domain?: string) => string} GetFilterDomain
 * Retrieve the domain to use when calling domain-specific filters.
 */

/**
 * @typedef {(text: string, domain?: string) => string} __
 *
 * Retrieve the translation of text.
 *
 * @see https://developer.wordpress.org/reference/functions/__/
 */

/**
 * @typedef {(text: string, context: string, domain?: string) => string} _x
 *
 * Retrieve translated string with gettext context.
 *
 * @see https://developer.wordpress.org/reference/functions/_x/
 */

/**
 * @typedef {(single: string, plural: string, number: number, domain?: string) => string} _n
 *
 * Translates and retrieves the singular or plural form based on the supplied
 * number.
 *
 * @see https://developer.wordpress.org/reference/functions/_n/
 */

/**
 * @typedef {(single: string, plural: string, number: number, context: string, domain?: string) => string} _nx
 *
 * Translates and retrieves the singular or plural form based on the supplied
 * number, with gettext context.
 *
 * @see https://developer.wordpress.org/reference/functions/_nx/
 */

/**
 * @typedef {() => boolean} IsRtl
 *
 * Check if current locale is RTL.
 *
 * **RTL (Right To Left)** is a locale property indicating that text is written from right to left.
 * For example, the `he` locale (for Hebrew) specifies right-to-left. Arabic (ar) is another common
 * language written RTL. The opposite of RTL, LTR (Left To Right) is used in other languages,
 * including English (`en`, `en-US`, `en-GB`, etc.), Spanish (`es`), and French (`fr`).
 */

/**
 * @typedef {(single: string, context?: string, domain?: string) => boolean} HasTranslation
 *
 * Check if there is a translation for a given string in singular form.
 */

/** @typedef {import('@wordpress/hooks').Hooks} Hooks */

/**
 * An i18n instance
 *
 * @typedef I18n
 * @property {GetLocaleData} getLocaleData     Returns locale data by domain in a Jed-formatted JSON object shape.
 * @property {SetLocaleData} setLocaleData     Merges locale data into the Tannin instance by domain. Accepts data in a
 *                                             Jed-formatted JSON object shape.
 * @property {ResetLocaleData} resetLocaleData Resets all current Tannin instance locale data and sets the specified
 *                                             locale data for the domain. Accepts data in a Jed-formatted JSON object shape.
 * @property {Subscribe} subscribe             Subscribes to changes of Tannin locale data.
 * @property {__} __                           Retrieve the translation of text.
 * @property {_x} _x                           Retrieve translated string with gettext context.
 * @property {_n} _n                           Translates and retrieves the singular or plural form based on the supplied
 *                                             number.
 * @property {_nx} _nx                         Translates and retrieves the singular or plural form based on the supplied
 *                                             number, with gettext context.
 * @property {IsRtl} isRTL                     Check if current locale is RTL.
 * @property {HasTranslation} hasTranslation   Check if there is a translation for a given string.
 */

/**
 * Create an i18n instance
 *
 * @param {LocaleData} [initialData]    Locale data configuration.
 * @param {string}     [initialDomain]  Domain for which configuration applies.
 * @param {Hooks} [hooks]     Hooks implementation.
 * @return {I18n}                       I18n instance
 */

var createI18n = function createI18n(initialData, initialDomain, hooks) {
  /**
   * The underlying instance of Tannin to which exported functions interface.
   *
   * @type {Tannin}
   */
  var tannin = new tannin__WEBPACK_IMPORTED_MODULE_1__["default"]({});
  var listeners = new Set();

  var notifyListeners = function notifyListeners() {
    listeners.forEach(function (listener) {
      return listener();
    });
  };
  /**
   * Subscribe to changes of locale data.
   *
   * @param {SubscribeCallback} callback Subscription callback.
   * @return {UnsubscribeCallback} Unsubscribe callback.
   */


  var subscribe = function subscribe(callback) {
    listeners.add(callback);
    return function () {
      return listeners.delete(callback);
    };
  };
  /** @type {GetLocaleData} */


  var getLocaleData = function getLocaleData() {
    var domain = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
    return tannin.data[domain];
  };
  /**
   * @param {LocaleData} [data]
   * @param {string} [domain]
   */


  var doSetLocaleData = function doSetLocaleData(data) {
    var domain = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
    tannin.data[domain] = _objectSpread(_objectSpread(_objectSpread({}, DEFAULT_LOCALE_DATA), tannin.data[domain]), data); // Populate default domain configuration (supported locale date which omits
    // a plural forms expression).

    tannin.data[domain][''] = _objectSpread(_objectSpread({}, DEFAULT_LOCALE_DATA['']), tannin.data[domain]['']);
  };
  /** @type {SetLocaleData} */


  var setLocaleData = function setLocaleData(data, domain) {
    doSetLocaleData(data, domain);
    notifyListeners();
  };
  /** @type {ResetLocaleData} */


  var resetLocaleData = function resetLocaleData(data, domain) {
    // Reset all current Tannin locale data.
    tannin.data = {}; // Reset cached plural forms functions cache.

    tannin.pluralForms = {};
    setLocaleData(data, domain);
  };
  /**
   * Wrapper for Tannin's `dcnpgettext`. Populates default locale data if not
   * otherwise previously assigned.
   *
   * @param {string|undefined} domain   Domain to retrieve the translated text.
   * @param {string|undefined} context  Context information for the translators.
   * @param {string}           single   Text to translate if non-plural. Used as
   *                                    fallback return value on a caught error.
   * @param {string}           [plural] The text to be used if the number is
   *                                    plural.
   * @param {number}           [number] The number to compare against to use
   *                                    either the singular or plural form.
   *
   * @return {string} The translated string.
   */


  var dcnpgettext = function dcnpgettext() {
    var domain = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
    var context = arguments.length > 1 ? arguments[1] : undefined;
    var single = arguments.length > 2 ? arguments[2] : undefined;
    var plural = arguments.length > 3 ? arguments[3] : undefined;
    var number = arguments.length > 4 ? arguments[4] : undefined;

    if (!tannin.data[domain]) {
      // use `doSetLocaleData` to set silently, without notifying listeners
      doSetLocaleData(undefined, domain);
    }

    return tannin.dcnpgettext(domain, context, single, plural, number);
  };
  /** @type {GetFilterDomain} */


  var getFilterDomain = function getFilterDomain() {
    var domain = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
    return domain;
  };
  /** @type {__} */


  var __ = function __(text, domain) {
    var translation = dcnpgettext(domain, undefined, text);

    if (!hooks) {
      return translation;
    }
    /**
     * Filters text with its translation.
     *
     * @param {string} translation Translated text.
     * @param {string} text        Text to translate.
     * @param {string} domain      Text domain. Unique identifier for retrieving translated strings.
     */


    translation =
    /** @type {string} */

    /** @type {*} */
    hooks.applyFilters('i18n.gettext', translation, text, domain);
    return (
      /** @type {string} */

      /** @type {*} */
      hooks.applyFilters('i18n.gettext_' + getFilterDomain(domain), translation, text, domain)
    );
  };
  /** @type {_x} */


  var _x = function _x(text, context, domain) {
    var translation = dcnpgettext(domain, context, text);

    if (!hooks) {
      return translation;
    }
    /**
     * Filters text with its translation based on context information.
     *
     * @param {string} translation Translated text.
     * @param {string} text        Text to translate.
     * @param {string} context     Context information for the translators.
     * @param {string} domain      Text domain. Unique identifier for retrieving translated strings.
     */


    translation =
    /** @type {string} */

    /** @type {*} */
    hooks.applyFilters('i18n.gettext_with_context', translation, text, context, domain);
    return (
      /** @type {string} */

      /** @type {*} */
      hooks.applyFilters('i18n.gettext_with_context_' + getFilterDomain(domain), translation, text, context, domain)
    );
  };
  /** @type {_n} */


  var _n = function _n(single, plural, number, domain) {
    var translation = dcnpgettext(domain, undefined, single, plural, number);

    if (!hooks) {
      return translation;
    }
    /**
     * Filters the singular or plural form of a string.
     *
     * @param {string} translation Translated text.
     * @param {string} single      The text to be used if the number is singular.
     * @param {string} plural      The text to be used if the number is plural.
     * @param {string} number      The number to compare against to use either the singular or plural form.
     * @param {string} domain      Text domain. Unique identifier for retrieving translated strings.
     */


    translation =
    /** @type {string} */

    /** @type {*} */
    hooks.applyFilters('i18n.ngettext', translation, single, plural, number, domain);
    return (
      /** @type {string} */

      /** @type {*} */
      hooks.applyFilters('i18n.ngettext_' + getFilterDomain(domain), translation, single, plural, number, domain)
    );
  };
  /** @type {_nx} */


  var _nx = function _nx(single, plural, number, context, domain) {
    var translation = dcnpgettext(domain, context, single, plural, number);

    if (!hooks) {
      return translation;
    }
    /**
     * Filters the singular or plural form of a string with gettext context.
     *
     * @param {string} translation Translated text.
     * @param {string} single      The text to be used if the number is singular.
     * @param {string} plural      The text to be used if the number is plural.
     * @param {string} number      The number to compare against to use either the singular or plural form.
     * @param {string} context     Context information for the translators.
     * @param {string} domain      Text domain. Unique identifier for retrieving translated strings.
     */


    translation =
    /** @type {string} */

    /** @type {*} */
    hooks.applyFilters('i18n.ngettext_with_context', translation, single, plural, number, context, domain);
    return (
      /** @type {string} */

      /** @type {*} */
      hooks.applyFilters('i18n.ngettext_with_context_' + getFilterDomain(domain), translation, single, plural, number, context, domain)
    );
  };
  /** @type {IsRtl} */


  var isRTL = function isRTL() {
    return 'rtl' === _x('ltr', 'text direction');
  };
  /** @type {HasTranslation} */


  var hasTranslation = function hasTranslation(single, context, domain) {
    var _tannin$data, _tannin$data2;

    var key = context ? context + "\x04" + single : single;
    var result = !!((_tannin$data = tannin.data) !== null && _tannin$data !== void 0 && (_tannin$data2 = _tannin$data[domain !== null && domain !== void 0 ? domain : 'default']) !== null && _tannin$data2 !== void 0 && _tannin$data2[key]);

    if (hooks) {
      /**
       * Filters the presence of a translation in the locale data.
       *
       * @param {boolean} hasTranslation Whether the translation is present or not..
       * @param {string} single The singular form of the translated text (used as key in locale data)
       * @param {string} context Context information for the translators.
       * @param {string} domain Text domain. Unique identifier for retrieving translated strings.
       */
      result =
      /** @type { boolean } */

      /** @type {*} */
      hooks.applyFilters('i18n.has_translation', result, single, context, domain);
      result =
      /** @type { boolean } */

      /** @type {*} */
      hooks.applyFilters('i18n.has_translation_' + getFilterDomain(domain), result, single, context, domain);
    }

    return result;
  };

  if (initialData) {
    setLocaleData(initialData, initialDomain);
  }

  if (hooks) {
    /**
     * @param {string} hookName
     */
    var onHookAddedOrRemoved = function onHookAddedOrRemoved(hookName) {
      if (I18N_HOOK_REGEXP.test(hookName)) {
        notifyListeners();
      }
    };

    hooks.addAction('hookAdded', 'core/i18n', onHookAddedOrRemoved);
    hooks.addAction('hookRemoved', 'core/i18n', onHookAddedOrRemoved);
  }

  return {
    getLocaleData: getLocaleData,
    setLocaleData: setLocaleData,
    resetLocaleData: resetLocaleData,
    subscribe: subscribe,
    __: __,
    _x: _x,
    _n: _n,
    _nx: _nx,
    isRTL: isRTL,
    hasTranslation: hasTranslation
  };
};
//# sourceMappingURL=create-i18n.js.map

/***/ }),

/***/ "./node_modules/@wordpress/i18n/build-module/default-i18n.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/build-module/default-i18n.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   __: function() { return /* binding */ __; },
/* harmony export */   _n: function() { return /* binding */ _n; },
/* harmony export */   _nx: function() { return /* binding */ _nx; },
/* harmony export */   _x: function() { return /* binding */ _x; },
/* harmony export */   getLocaleData: function() { return /* binding */ getLocaleData; },
/* harmony export */   hasTranslation: function() { return /* binding */ hasTranslation; },
/* harmony export */   isRTL: function() { return /* binding */ isRTL; },
/* harmony export */   resetLocaleData: function() { return /* binding */ resetLocaleData; },
/* harmony export */   setLocaleData: function() { return /* binding */ setLocaleData; },
/* harmony export */   subscribe: function() { return /* binding */ subscribe; }
/* harmony export */ });
/* harmony import */ var _create_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./create-i18n */ "./node_modules/@wordpress/i18n/build-module/create-i18n.js");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/hooks */ "./node_modules/@wordpress/hooks/build-module/index.js");
/**
 * Internal dependencies
 */

/**
 * WordPress dependencies
 */


var i18n = (0,_create_i18n__WEBPACK_IMPORTED_MODULE_0__.createI18n)(undefined, undefined, _wordpress_hooks__WEBPACK_IMPORTED_MODULE_1__.defaultHooks);
/**
 * Default, singleton instance of `I18n`.
 */

/* harmony default export */ __webpack_exports__["default"] = (i18n);
/*
 * Comments in this file are duplicated from ./i18n due to
 * https://github.com/WordPress/gutenberg/pull/20318#issuecomment-590837722
 */

/**
 * @typedef {import('./create-i18n').LocaleData} LocaleData
 * @typedef {import('./create-i18n').SubscribeCallback} SubscribeCallback
 * @typedef {import('./create-i18n').UnsubscribeCallback} UnsubscribeCallback
 */

/**
 * Returns locale data by domain in a Jed-formatted JSON object shape.
 *
 * @see http://messageformat.github.io/Jed/
 *
 * @param {string} [domain] Domain for which to get the data.
 * @return {LocaleData} Locale data.
 */

var getLocaleData = i18n.getLocaleData.bind(i18n);
/**
 * Merges locale data into the Tannin instance by domain. Accepts data in a
 * Jed-formatted JSON object shape.
 *
 * @see http://messageformat.github.io/Jed/
 *
 * @param {LocaleData} [data]   Locale data configuration.
 * @param {string}     [domain] Domain for which configuration applies.
 */

var setLocaleData = i18n.setLocaleData.bind(i18n);
/**
 * Resets all current Tannin instance locale data and sets the specified
 * locale data for the domain. Accepts data in a Jed-formatted JSON object shape.
 *
 * @see http://messageformat.github.io/Jed/
 *
 * @param {LocaleData} [data]   Locale data configuration.
 * @param {string}     [domain] Domain for which configuration applies.
 */

var resetLocaleData = i18n.resetLocaleData.bind(i18n);
/**
 * Subscribes to changes of locale data
 *
 * @param {SubscribeCallback} callback Subscription callback
 * @return {UnsubscribeCallback} Unsubscribe callback
 */

var subscribe = i18n.subscribe.bind(i18n);
/**
 * Retrieve the translation of text.
 *
 * @see https://developer.wordpress.org/reference/functions/__/
 *
 * @param {string} text     Text to translate.
 * @param {string} [domain] Domain to retrieve the translated text.
 *
 * @return {string} Translated text.
 */

var __ = i18n.__.bind(i18n);
/**
 * Retrieve translated string with gettext context.
 *
 * @see https://developer.wordpress.org/reference/functions/_x/
 *
 * @param {string} text     Text to translate.
 * @param {string} context  Context information for the translators.
 * @param {string} [domain] Domain to retrieve the translated text.
 *
 * @return {string} Translated context string without pipe.
 */

var _x = i18n._x.bind(i18n);
/**
 * Translates and retrieves the singular or plural form based on the supplied
 * number.
 *
 * @see https://developer.wordpress.org/reference/functions/_n/
 *
 * @param {string} single   The text to be used if the number is singular.
 * @param {string} plural   The text to be used if the number is plural.
 * @param {number} number   The number to compare against to use either the
 *                          singular or plural form.
 * @param {string} [domain] Domain to retrieve the translated text.
 *
 * @return {string} The translated singular or plural form.
 */

var _n = i18n._n.bind(i18n);
/**
 * Translates and retrieves the singular or plural form based on the supplied
 * number, with gettext context.
 *
 * @see https://developer.wordpress.org/reference/functions/_nx/
 *
 * @param {string} single   The text to be used if the number is singular.
 * @param {string} plural   The text to be used if the number is plural.
 * @param {number} number   The number to compare against to use either the
 *                          singular or plural form.
 * @param {string} context  Context information for the translators.
 * @param {string} [domain] Domain to retrieve the translated text.
 *
 * @return {string} The translated singular or plural form.
 */

var _nx = i18n._nx.bind(i18n);
/**
 * Check if current locale is RTL.
 *
 * **RTL (Right To Left)** is a locale property indicating that text is written from right to left.
 * For example, the `he` locale (for Hebrew) specifies right-to-left. Arabic (ar) is another common
 * language written RTL. The opposite of RTL, LTR (Left To Right) is used in other languages,
 * including English (`en`, `en-US`, `en-GB`, etc.), Spanish (`es`), and French (`fr`).
 *
 * @return {boolean} Whether locale is RTL.
 */

var isRTL = i18n.isRTL.bind(i18n);
/**
 * Check if there is a translation for a given string (in singular form).
 *
 * @param {string} single Singular form of the string to look up.
 * @param {string} [context] Context information for the translators.
 * @param {string} [domain] Domain to retrieve the translated text.
 * @return {boolean} Whether the translation exists or not.
 */

var hasTranslation = i18n.hasTranslation.bind(i18n);
//# sourceMappingURL=default-i18n.js.map

/***/ }),

/***/ "./node_modules/@wordpress/i18n/build-module/index.js":
/*!************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/build-module/index.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   __: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.__; },
/* harmony export */   _n: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__._n; },
/* harmony export */   _nx: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__._nx; },
/* harmony export */   _x: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__._x; },
/* harmony export */   createI18n: function() { return /* reexport safe */ _create_i18n__WEBPACK_IMPORTED_MODULE_1__.createI18n; },
/* harmony export */   defaultI18n: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__["default"]; },
/* harmony export */   getLocaleData: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.getLocaleData; },
/* harmony export */   hasTranslation: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.hasTranslation; },
/* harmony export */   isRTL: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.isRTL; },
/* harmony export */   resetLocaleData: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.resetLocaleData; },
/* harmony export */   setLocaleData: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.setLocaleData; },
/* harmony export */   sprintf: function() { return /* reexport safe */ _sprintf__WEBPACK_IMPORTED_MODULE_0__.sprintf; },
/* harmony export */   subscribe: function() { return /* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.subscribe; }
/* harmony export */ });
/* harmony import */ var _sprintf__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./sprintf */ "./node_modules/@wordpress/i18n/build-module/sprintf.js");
/* harmony import */ var _create_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./create-i18n */ "./node_modules/@wordpress/i18n/build-module/create-i18n.js");
/* harmony import */ var _default_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./default-i18n */ "./node_modules/@wordpress/i18n/build-module/default-i18n.js");



//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./node_modules/@wordpress/i18n/build-module/sprintf.js":
/*!**************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/build-module/sprintf.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   sprintf: function() { return /* binding */ sprintf; }
/* harmony export */ });
/* harmony import */ var memize__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! memize */ "./node_modules/memize/index.js");
/* harmony import */ var memize__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(memize__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var sprintf_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! sprintf-js */ "./node_modules/sprintf-js/src/sprintf.js");
/* harmony import */ var sprintf_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(sprintf_js__WEBPACK_IMPORTED_MODULE_1__);
/**
 * External dependencies
 */


/**
 * Log to console, once per message; or more precisely, per referentially equal
 * argument set. Because Jed throws errors, we log these to the console instead
 * to avoid crashing the application.
 *
 * @param {...*} args Arguments to pass to `console.error`
 */

var logErrorOnce = memize__WEBPACK_IMPORTED_MODULE_0___default()(console.error); // eslint-disable-line no-console

/**
 * Returns a formatted string. If an error occurs in applying the format, the
 * original format string is returned.
 *
 * @param {string}    format The format of the string to generate.
 * @param {...*} args Arguments to apply to the format.
 *
 * @see https://www.npmjs.com/package/sprintf-js
 *
 * @return {string} The formatted string.
 */

function sprintf(format) {
  try {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    return sprintf_js__WEBPACK_IMPORTED_MODULE_1___default().sprintf.apply((sprintf_js__WEBPACK_IMPORTED_MODULE_1___default()), [format].concat(args));
  } catch (error) {
    logErrorOnce('sprintf error: \n\n' + error.toString());
    return format;
  }
}
//# sourceMappingURL=sprintf.js.map

/***/ }),

/***/ "./js/src/admin/styles/dependent-updater-component.js":
/*!************************************************************!*\
  !*** ./js/src/admin/styles/dependent-updater-component.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ frmStyleDependentUpdaterComponent; }
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * This component updates the dependent style element's values and triggers a custom change event for each style element, initiating the style preview.
 * The names of the elements that will be updated are specified using the "will-change" attribute.
 * It is primarily used in components from Style/Quick Settings.
 * For instance, when the "FrmPrimaryColorStyleComponent" is changed, it simultaneously updates various style elements like border color, text color, and button backgrounds.
 */
var frmStyleDependentUpdaterComponent = /*#__PURE__*/function () {
  /**
   * Creates an instance of frmStyleDependentUpdaterComponent.
   * @param {HTMLElement} component - The component element.
   */
  function frmStyleDependentUpdaterComponent(component) {
    _classCallCheck(this, frmStyleDependentUpdaterComponent);
    this.component = component;
    try {
      var willChangeData = JSON.parse(this.component.dataset.willChange);
      this.data = {
        propagateInputs: this.initPropagationList(willChangeData),
        changeEvent: new Event('change', {
          bubbles: true
        })
      };
    } catch (error) {
      console.error('Error parsing JSON data from "will-change" attribute.', error);
    }
  }

  /**
   * Initializes the list of inputs to propagate changes to.
   * The selection is made by provided input's names list in "will-change" attribute.
   * @param {string[]} inputNames - The names of the inputs to propagate changes to.
   * @return {HTMLElement[]} - The list of inputs to propagate changes to.
   */
  return _createClass(frmStyleDependentUpdaterComponent, [{
    key: "initPropagationList",
    value: function initPropagationList(inputNames) {
      var list = [];
      inputNames.forEach(function (name) {
        var input = document.querySelector("input[name=\"".concat(name, "\"]"));
        if (null !== input) {
          list.push(input);
        }
      });
      return list;
    }

    /**
     * Updates all dependent elements with the given value.
     * @param {string} value - The value to update the dependent elements with.
     */
  }, {
    key: "updateAllDependentElements",
    value: function updateAllDependentElements(value) {
      this.data.propagateInputs.forEach(function (input) {
        input.value = value;
      });
      this.data.propagateInputs[0].dispatchEvent(this.data.changeEvent);
    }
  }]);
}();


/***/ }),

/***/ "./js/src/admin/styles/radio-style-component.js":
/*!******************************************************!*\
  !*** ./js/src/admin/styles/radio-style-component.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ frmRadioStyleComponent; }
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * Represents a radio style component.
 * @class
 */
var frmRadioStyleComponent = /*#__PURE__*/function () {
  function frmRadioStyleComponent() {
    _classCallCheck(this, frmRadioStyleComponent);
    this.radioElements = document.querySelectorAll('.frm-style-component.frm-radio-component');
    if (0 < this.radioElements.length) {
      this.init();
    }
  }

  /**
   * Initializes the radio style component.
   */
  return _createClass(frmRadioStyleComponent, [{
    key: "init",
    value: function init() {
      var _this = this;
      this.radioElements.forEach(function (element) {
        _this.initOnRadioChange(element);
      });
      this.initTrackerOnAccordionClick();
    }
  }, {
    key: "initTrackerOnAccordionClick",
    value: function initTrackerOnAccordionClick() {
      var _this2 = this;
      var accordionitems = document.querySelectorAll('#frm_style_sidebar .accordion-section h3');
      accordionitems.forEach(function (accordionitem) {
        accordionitem.addEventListener('click', function (event) {
          var wrapper = event.target.closest('.accordion-section');
          var radioButtons = wrapper.querySelectorAll('.frm-style-component.frm-radio-component input[type="radio"]:checked');
          radioButtons.forEach(function (radio) {
            setTimeout(function () {
              return _this2.onRadioChange(radio);
            }, 200);
          });
        });
      });
    }

    /**
     * Initializes the onRadioChange event for the given wrapper.
     * @param {HTMLElement} radioElement - The radio element.
     */
  }, {
    key: "initOnRadioChange",
    value: function initOnRadioChange(radioElement) {
      var _this3 = this;
      radioElement.querySelectorAll('input[type="radio"]').forEach(function (radio) {
        if (radio.checked) {
          _this3.onRadioChange(radio);
        }
        radio.addEventListener('change', function (event) {
          _this3.onRadioChange(event.target);
        });
      });
    }

    /**
     * Handles the onRadioChange event for the given wrapper.
     * @param {HTMLElement} target - The active radio button.
     */
  }, {
    key: "onRadioChange",
    value: function onRadioChange(target) {
      var wrapper = target.closest('.frm-style-component.frm-radio-component');
      var activeItem = wrapper.querySelector('input[type="radio"]:checked + label');
      if (null === activeItem) {
        return;
      }
      this.moveTracker(activeItem, wrapper);
      this.hideExtraElements(target);
      this.maybeShowExtraElements(target);
    }

    /**
     * Display additional elements related to the selected radio option.
     * @param {HTMLElement} radio - The radio button element.
     */
  }, {
    key: "maybeShowExtraElements",
    value: function maybeShowExtraElements(radio) {
      var elementAttr = radio.getAttribute('data-frm-show-element');
      if (null === elementAttr) {
        return;
      }
      var elements = document.querySelectorAll("div[data-frm-element=\"".concat(elementAttr, "\"]"));
      if (0 === elements.length) {
        return;
      }
      elements.forEach(function (element) {
        (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.show)(element);
        element.classList.add('frm-element-is-visible');
      });
    }

    /**
     * Hide the possible opepend extra elements.
     */
  }, {
    key: "hideExtraElements",
    value: function hideExtraElements() {
      var elements = document.querySelectorAll('.frm-element-is-visible');
      if (0 === elements.length) {
        return;
      }
      elements.forEach(function (element) {
        element.classList.remove('frm-element-is-visible');
        element.classList.add('frm_hidden');
        (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(element);
      });
    }

    /**
     * Moves the tracker to the active item.
     * @param {HTMLElement} activeItem - The active item element.
     * @param {HTMLElement} wrapper    - The wrapper element.
     */
  }, {
    key: "moveTracker",
    value: function moveTracker(activeItem, wrapper) {
      var offset = activeItem.offsetLeft;
      var width = activeItem.offsetWidth;
      var tracker = wrapper.querySelector('.frm-radio-active-tracker');
      tracker.style.left = 0;
      tracker.style.width = "".concat(width, "px");
      tracker.style.transform = "translateX(".concat(offset, "px)");
    }
  }]);
}();


/***/ }),

/***/ "./js/src/admin/styles/slider-style-component.js":
/*!*******************************************************!*\
  !*** ./js/src/admin/styles/slider-style-component.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ frmSliderStyleComponent; }
/* harmony export */ });
/* harmony import */ var _dependent_updater_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dependent-updater-component */ "./js/src/admin/styles/dependent-updater-component.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

/**
 * Represents a slider style component.
 * @class frmSliderStyleComponent
 */
var frmSliderStyleComponent = /*#__PURE__*/function () {
  function frmSliderStyleComponent() {
    var _this = this;
    _classCallCheck(this, frmSliderStyleComponent);
    this.sliderElements = document.querySelectorAll('.frm-slider-component');
    if (0 === this.sliderElements.length) {
      return;
    }

    // The slider bullet point width in pixels. Used in value calculation on drag event.
    this.sliderBulletWidth = 16;
    this.sliderMarginRight = 5;
    this.eventsChange = [];
    var debounce = frmDom.util.debounce;
    this.valueChangeDebouncer = debounce(function (index) {
      return _this.triggerValueChange(index);
    }, 25);
    this.initOptions();
    this.init();
  }

  /**
   * Initializes the options for the slider style component.
   */
  return _createClass(frmSliderStyleComponent, [{
    key: "initOptions",
    value: function initOptions() {
      var _this2 = this;
      this.options = [];
      this.sliderElements.forEach(function (element, index) {
        var parentWrapper = element.classList.contains('frm-has-multiple-values') ? element.closest('.frm-style-component') : element;
        _this2.options.push({
          dragging: false,
          startX: 0,
          translateX: 0,
          maxValue: parseInt(element.dataset.maxValue, 10),
          element: element,
          index: index,
          value: 0,
          dependentUpdater: parentWrapper.classList.contains('frm-style-dependent-updater-component') ? new _dependent_updater_component__WEBPACK_IMPORTED_MODULE_0__["default"](parentWrapper) : null
        });
      });
    }

    /**
     * Initializes the slider style component.
     */
  }, {
    key: "init",
    value: function init() {
      this.initSlidersPosition();
      this.initDraggable();
    }

    /**
     * Initializes the draggable functionality for the slider style component.
     */
  }, {
    key: "initDraggable",
    value: function initDraggable() {
      var _this3 = this;
      this.sliderElements.forEach(function (element, index) {
        _this3.eventsChange[index] = new Event('change', {
          bubbles: true,
          cancelable: true
        });
        var draggableBullet = element.querySelector('.frm-slider-bullet');
        var valueInput = element.querySelector('.frm-slider-value input[type="text"]');
        valueInput.addEventListener('change', function (event) {
          var unit = element.querySelector('select').value;
          if (_this3.getMaxValue(unit, index) < parseInt(event.target.value, 10)) {
            return;
          }
          _this3.initSliderWidth(element);
          _this3.options[index].fullValue = _this3.updateValue(element, valueInput.value + unit);
          _this3.triggerValueChange(index);
        });
        _this3.expandSliderGroup(element);
        _this3.updateOnUnitChange(element, valueInput, index);
        _this3.changeSliderPositionOnClick(element, valueInput, index);
        draggableBullet.addEventListener('mousedown', function (event) {
          event.preventDefault();
          event.stopPropagation();
          if (element.classList.contains('frm-disabled')) {
            return;
          }
          _this3.enableDragging(event, index);
        });
        draggableBullet.addEventListener('mousemove', function (event) {
          if (element.classList.contains('frm-disabled')) {
            return;
          }
          _this3.moveTracker(event, index);
        });
        draggableBullet.addEventListener('mouseup', function (event) {
          if (element.classList.contains('frm-disabled')) {
            return;
          }
          _this3.disableDragging(index, event);
        });
        draggableBullet.addEventListener('mouseleave', function (event) {
          if (element.classList.contains('frm-disabled')) {
            return;
          }
          _this3.disableDragging(index, event);
        });
      });
    }
  }, {
    key: "expandSliderGroup",
    value: function expandSliderGroup(element) {
      var svgIcon = element.querySelector('.frmsvg');
      if ('undefined' === typeof element.dataset.displaySliders || null === svgIcon) {
        return;
      }
      var sliderGroupItems = this.getSliderGroupItems(element);
      svgIcon.addEventListener('click', function () {
        sliderGroupItems.forEach(function (item) {
          item.classList.toggle('frm_hidden');
        });
      });
    }
  }, {
    key: "updateOnUnitChange",
    value: function updateOnUnitChange(element, valueInput, index) {
      var _this4 = this;
      element.querySelector('select').addEventListener('change', function (event) {
        var unit = event.target.value.toLowerCase();
        if ('' === unit) {
          element.classList.add('frm-disabled', 'frm-empty');
          return;
        }
        if ('auto' === unit) {
          element.classList.add('frm-disabled');
          _this4.updateValue(element, 'auto');
          _this4.triggerValueChange(index);
          return;
        }
        element.classList.remove('frm-disabled', 'frm-empty');
        _this4.options[index].fullValue = valueInput.value + unit;
        _this4.updateValue(element, _this4.options[index].fullValue);
        _this4.triggerValueChange(index);
      });
    }
  }, {
    key: "changeSliderPositionOnClick",
    value: function changeSliderPositionOnClick(element, valueInput, index) {
      var _this5 = this;
      var frmSlider = element.querySelector('.frm-slider');
      var customEvent = new Event('change', {
        'bubbles': true,
        'cancelable': true
      });
      frmSlider.addEventListener('click', function (event) {
        if (element.classList.contains('frm-disabled')) {
          return;
        }
        event.preventDefault();
        event.stopPropagation();
        if (!event.target.classList.contains('frm-slider') && !event.target.classList.contains('frm-slider-active-track')) {
          return;
        }
        var sliderWidth = frmSlider.offsetWidth - _this5.sliderBulletWidth;
        var sliderRect = frmSlider.getBoundingClientRect();
        var deltaX = event.clientX - sliderRect.left - _this5.sliderBulletWidth;
        var unit = element.querySelector('select').value;
        var value = _this5.calculateValue(sliderWidth, deltaX, _this5.getMaxValue(unit, index));
        if (value < 0) {
          return;
        }
        _this5.options[index].fullValue = _this5.updateValue(element, value + unit);
        _this5.initChildSlidersWidth(element, deltaX, index, value + unit);
        valueInput.value = value;
        valueInput.dispatchEvent(customEvent);
      });
    }

    /**
     * Retrieves an array of slider group items based on the provided element.
     *
     * @param {HTMLElement} element - The element to retrieve slider group items from.
     * @return {NodeList} - An array-like object containing the slider group items.
     */
  }, {
    key: "getSliderGroupItems",
    value: function getSliderGroupItems(element) {
      if ('undefined' === typeof element.dataset.displaySliders) {
        return [];
      }
      var slidersGroup = element.dataset.displaySliders.split(',');
      var query = slidersGroup.map(function (item) {
        return ".frm-slider-component[data-type=\"".concat(item, "\"]");
      }).join(', ');
      return element.closest('.frm-style-component').querySelectorAll(query);
    }

    /**
     * Initializes the position of sliders when a style accordion section is opened.
     */
  }, {
    key: "initSlidersPosition",
    value: function initSlidersPosition() {
      var _this6 = this;
      var accordionitems = document.querySelectorAll('#frm_style_sidebar .accordion-section h3');
      var quickSettings = document.querySelector('.frm-quick-settings');
      var openedAccordion = document.querySelector('.accordion-section.open');

      // Detect if upload background image upload has triggered and initialize the "Image Opacity" slider width.
      wp.hooks.addAction('frm_pro_on_bg_image_upload', 'formidable', function (event) {
        var imageBackgroundOpacitySlider = event.closest('.accordion-section-content').querySelector('#frm-bg-image-opacity-slider');
        _this6.initSlidersWidth(imageBackgroundOpacitySlider);
      });

      // init the sliders width from "Quick Settings" page.
      if (null !== quickSettings) {
        this.initSlidersWidth(quickSettings);
      }

      // Init the sliders width in opened accordion section from "Advanced Settings" page.
      if (null !== openedAccordion) {
        this.initSlidersWidth(openedAccordion);
      }

      // init the sliders width everytime when an accordion section is opened from "Advanced Settings" page.
      accordionitems.forEach(function (item) {
        item.addEventListener('click', function (event) {
          _this6.initSlidersWidth(event.target.closest('.accordion-section'));
        });
      });
      this.initSliderPositionOnFieldShapeChange();
    }

    /**
     * Initializes the width of "Corner Radius" slider that is dynamically is displayed on "Field Shape" option change from "Quick Settings".
     *
     * @return {void}
     */
  }, {
    key: "initSliderPositionOnFieldShapeChange",
    value: function initSliderPositionOnFieldShapeChange() {
      var _this7 = this;
      var fieldShapeType = document.querySelector('.frm-style-component.frm-field-shape');
      if (null === fieldShapeType) {
        return;
      }
      var radioButtons = fieldShapeType.querySelectorAll('input[type="radio"]');
      radioButtons.forEach(function (radio) {
        radio.addEventListener('change', function (event) {
          if (event.target.checked && 'rounded-corner' === event.target.value) {
            var slider = document.querySelector('div[data-frm-element="field-shape-corner-radius"] .frm-slider-component');
            _this7.initSliderWidth(slider);
          }
        });
      });
    }

    /**
     * Initializes the width of sliders within a given section.
     *
     * @param {HTMLElement} section - The section containing the sliders.
     * @return {void}
     */
  }, {
    key: "initSlidersWidth",
    value: function initSlidersWidth(section) {
      var _this8 = this;
      var sliders = section.querySelectorAll('.frm-slider-component');
      sliders.forEach(function (slider) {
        setTimeout(function () {
          _this8.initSliderWidth(slider);
        }, 100);
      });
    }

    /**
     * Initializes the width of a slider.
     *
     * @param {HTMLElement} slider - The slider element.
     * @return {void}
     */
  }, {
    key: "initSliderWidth",
    value: function initSliderWidth(slider) {
      if (slider.classList.contains('frm-disabled')) {
        return;
      }
      var index = this.getSliderIndex(slider);
      var sliderWidth = slider.querySelector('.frm-slider').offsetWidth - this.sliderBulletWidth;
      var value = parseInt(slider.querySelector('.frm-slider-value input[type="text"]').value, 10);
      var unit = slider.querySelector('select').value;
      var deltaX = '%' === unit ? Math.round(sliderWidth * value / 100) : Math.ceil(value / this.options[index].maxValue * sliderWidth);
      slider.querySelector('.frm-slider-active-track').style.width = "".concat(deltaX, "px");
      this.options[index].translateX = deltaX;
      this.options[index].value = value + unit;
    }

    /**
     * Initializes the width of child sliders.
     *
     * @param {HTMLElement} slider - The parent slider element.
     * @param {number}      width  - The width to set for the child sliders.
     * @param {number}      index  - The starting index for the child sliders.
     * @param {number}      value  - The value to set for the child sliders.
     */
  }, {
    key: "initChildSlidersWidth",
    value: function initChildSlidersWidth(slider, width, index, value) {
      var _this9 = this;
      if (!slider.classList.contains('frm-has-independent-fields') && !slider.classList.contains('frm-has-multiple-values')) {
        return;
      }
      var childSliders = slider.classList.contains('frm-has-independent-fields') ? slider.querySelectorAll('.frm-independent-slider-field') : this.getSliderGroupItems(slider);
      childSliders.forEach(function (item, childIndex) {
        item.querySelector('.frm-slider-active-track').style.width = "".concat(width, "px");
        _this9.options[index + childIndex + 1].translateX = width;
        _this9.options[index + childIndex + 1].value = value;
      });
    }

    /**
     * Returns the index of the specified slider element.
     *
     * @param {HTMLElement} slider - The slider element.
     * @return {number} The index of the slider element.
     */
  }, {
    key: "getSliderIndex",
    value: function getSliderIndex(slider) {
      return this.options.filter(function (option) {
        return option.element === slider;
      })[0].index;
    }
  }, {
    key: "moveTracker",
    value:
    /**
     * Handles the movement of the slider tracker.
     *
     * @param {Event}  event - The event object representing the mouse movement.
     * @param {number} index - The index of the slider element.
     * @return {void}
     */
    function moveTracker(event, index) {
      if (!this.options[index].dragging) {
        return;
      }
      var deltaX = event.clientX - this.options[index].startX;
      var element = this.sliderElements[index];
      var sliderWidth = element.querySelector('.frm-slider').offsetWidth;

      // Ensure deltaX does not go below 0
      deltaX = Math.max(deltaX, 0);
      if (deltaX + this.sliderBulletWidth / 2 + this.sliderMarginRight >= sliderWidth) {
        return;
      }
      var unit = element.querySelector('select').value;
      var value = this.calculateValue(sliderWidth, deltaX, this.getMaxValue(unit, index));
      element.querySelector('.frm-slider-value input[type="text"]').value = value;
      element.querySelector('.frm-slider-bullet .frm-slider-value-label').innerText = value;
      element.querySelector('.frm-slider-active-track').style.width = "".concat(deltaX, "px");
      this.initChildSlidersWidth(element, deltaX, index, value + unit);
      this.options[index].translateX = deltaX;
      this.options[index].value = value + unit;
      this.options[index].fullValue = this.updateValue(element, this.options[index].value);
      this.valueChangeDebouncer(index);
    }

    /**
     * Get the maximum value based on the unit and index.
     *
     * @param {string} unit  - The unit of measurement.
     * @param {number} index - The index of the option.
     * @return {number} The maximum value.
     */
  }, {
    key: "getMaxValue",
    value: function getMaxValue(unit, index) {
      return '%' === unit ? 100 : this.options[index].maxValue;
    }

    /**
     * Enables dragging for the slider style component.
     *
     * @param {Event}  event - The event object.
     * @param {number} index - The index of the option being dragged.
     */
  }, {
    key: "enableDragging",
    value: function enableDragging(event, index) {
      event.target.classList.add('frm-dragging');
      this.options[index].dragging = true;
      this.options[index].startX = event.clientX - this.options[index].translateX;
    }

    /**
     * Disables dragging for a specific index.
     *
     * @param {number} index - The index of the option to disable dragging for.
     * @param {Event}  event - The event object triggered by the dragging action.
     */
  }, {
    key: "disableDragging",
    value: function disableDragging(index, event) {
      if (false === this.options[index].dragging) {
        return;
      }
      event.target.classList.remove('frm-dragging');
      this.options[index].dragging = false;
      this.triggerValueChange(index);
    }

    /**
     * Triggers a value change for the specified index.
     *
     * @param {number} index - The index of the value to be changed.
     */
  }, {
    key: "triggerValueChange",
    value: function triggerValueChange(index) {
      var _this10 = this;
      if (null !== this.options[index].dependentUpdater) {
        this.options[index].dependentUpdater.updateAllDependentElements(this.options[index].fullValue);
        return;
      }
      var input = this.sliderElements[index].classList.contains('frm-has-multiple-values') ? this.sliderElements[index].closest('.frm-style-component').querySelector('input[type="hidden"]') : this.sliderElements[index].querySelectorAll('.frm-slider-value input[type="hidden"]');
      if (input instanceof NodeList) {
        input.forEach(function (item) {
          item.dispatchEvent(_this10.eventsChange[index]);
        });
        return;
      }
      input.dispatchEvent(this.eventsChange[index]);
    }

    /**
     * Calculates the value based on the width, deltaX, and maxValue.
     *
     * @param {number} width    - The width of the slider.
     * @param {number} deltaX   - The change in x-coordinate.
     * @param {number} maxValue - The maximum value.
     * @return {number} - The calculated value.
     */
  }, {
    key: "calculateValue",
    value: function calculateValue(width, deltaX, maxValue) {
      // Indicates the additional value generated by the slider's drag progress (up to 100%) and the width of the slider bullet.
      // Generates a more accurate value for the slider's start (0) and end (maximum value) positions, taking into account the slider's position and bullet width.
      var delta = Math.ceil(this.sliderBulletWidth * (deltaX / width));
      var value = Math.ceil((deltaX + delta) / width * maxValue);
      return Math.min(value, maxValue);
    }

    /**
     * Updates the value of a slider component.
     *
     * @param {HTMLElement} element - The slider component element.
     * @param {string}      value   - The new value to be set.
     * @return {string} - The updated value.
     */
  }, {
    key: "updateValue",
    value: function updateValue(element, value) {
      var _this11 = this;
      // When the slider component is used for "Base Font Size", we need to update a hidden input field when change happens to indicate that the "Base Font Size" has been adjusted.
      // Used to avoid conflicts with other possible font sizes adjustemnts in "Advanced Settings" when moving from "Quick Settings" when "Base Font Size" is not changed.
      if (element.classList.contains('frm-base-font-size')) {
        var userBaseFontSizeInput = document.querySelector('input[name="frm_style_setting[post_content][use_base_font_size]"]');
        if (null !== userBaseFontSizeInput) {
          userBaseFontSizeInput.value = 'true';
        }
      }
      if (element.classList.contains('frm-has-multiple-values')) {
        var input = element.closest('.frm-style-component').querySelector('input[type="hidden"]');
        var inputValue = input.value.split(' ');
        var type = element.dataset.type;
        if (!inputValue[2]) {
          inputValue[2] = '0px';
        }
        if (!inputValue[3]) {
          inputValue[3] = '0px';
        }
        switch (type) {
          case 'vertical':
            inputValue[0] = value;
            inputValue[2] = value;
            break;
          case 'horizontal':
            inputValue[1] = value;
            inputValue[3] = value;
            break;
          case 'top':
            inputValue[0] = value;
            break;
          case 'bottom':
            inputValue[2] = value;
            break;
          case 'left':
            inputValue[3] = value;
            break;
          case 'right':
            inputValue[1] = value;
            break;
        }
        var newValue = inputValue.join(' ');
        input.value = newValue;
        var childSlidersGroup = this.getSliderGroupItems(element);
        childSlidersGroup.forEach(function (slider) {
          var unitMeasure = _this11.getUnitMeasureFromValue(value);
          slider.querySelector('.frm-slider-value input[type="text"]').value = parseInt(value, 10);
          slider.querySelector('select').value = unitMeasure;
        });
        return newValue;
      }
      if (element.classList.contains('frm-has-independent-fields')) {
        var inputValues = element.querySelectorAll('.frm-slider-value input[type="hidden"]');
        var visibleValues = element.querySelectorAll('.frm-slider-value input[type="text"]');
        inputValues.forEach(function (input, index) {
          input.value = value;
          visibleValues[index + 1].value = parseInt(value, 10);
        });
        return value;
      }
      element.querySelector('.frm-slider-value input[type="hidden"]').value = value;
      return value;
    }

    /**
     * Returns the unit of measurement used in the given value.
     *
     * @param {string} value - The value to check for the unit of measurement.
     * @return {string} The unit of measurement ('%', 'px', 'em') found in the value, or an empty string if none is found.
     */
  }, {
    key: "getUnitMeasureFromValue",
    value: function getUnitMeasureFromValue(value) {
      return ['%', 'px', 'em'].find(function (unit) {
        return value.includes(unit);
      }) || '';
    }
  }]);
}();


/***/ }),

/***/ "./js/src/admin/styles/tabs-style-component.js":
/*!*****************************************************!*\
  !*** ./js/src/admin/styles/tabs-style-component.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ frmTabsStyleComponent; }
/* harmony export */ });
/* harmony import */ var _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../components/class-tabs-navigator */ "./js/src/components/class-tabs-navigator.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

/**
 * Represents a Tabs Style Component.
 * @class
 */
var frmTabsStyleComponent = /*#__PURE__*/function () {
  function frmTabsStyleComponent() {
    _classCallCheck(this, frmTabsStyleComponent);
    this.elements = document.querySelectorAll('.frm-style-tabs-wrapper');
    if (0 < this.elements.length) {
      this.init();
    }
  }

  /**
   * Initializes the Tabs Style Component.
   */
  return _createClass(frmTabsStyleComponent, [{
    key: "init",
    value: function init() {
      this.elements.forEach(function (element) {
        new _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__.frmTabsNavigator(element);
      });
    }

    /**
     * Initializes the component on tab click.
     * @param {Element} wrapper - The wrapper element.
     */
  }, {
    key: "initOnTabClick",
    value: function initOnTabClick(wrapper) {
      var _this = this;
      this.initActiveBackgroundWidth(wrapper);
      wrapper.querySelectorAll('.frm-tab-item').forEach(function (tab) {
        tab.addEventListener('click', function (event) {
          _this.onTabClick(event.target.closest('.frm-tabs-wrapper'));
        });
      });
    }
  }]);
}();


/***/ }),

/***/ "./js/src/components/class-tabs-navigator.js":
/*!***************************************************!*\
  !*** ./js/src/components/class-tabs-navigator.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmTabsNavigator: function() { return /* binding */ frmTabsNavigator; }
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var frmTabsNavigator = /*#__PURE__*/function () {
  function frmTabsNavigator(wrapper) {
    _classCallCheck(this, frmTabsNavigator);
    if ('undefined' === typeof wrapper) {
      return;
    }
    this.wrapper = wrapper instanceof Element ? wrapper : document.querySelector(wrapper);
    if (null === this.wrapper) {
      return;
    }
    this.flexboxSlidesGap = '16px';
    this.navs = this.wrapper.querySelectorAll('.frm-tabs-navs ul > li');
    this.slideTrackLine = this.wrapper.querySelector('.frm-tabs-active-underline');
    this.slideTrack = this.wrapper.querySelector('.frm-tabs-slide-track');
    this.slides = this.wrapper.querySelectorAll('.frm-tabs-slide-track > div');
    this.init();
  }
  return _createClass(frmTabsNavigator, [{
    key: "init",
    value: function init() {
      var _this = this;
      if (null === this.wrapper || !this.navs.length || null === this.trackLine || null === this.slideTrack || !this.slides.length) {
        return;
      }
      this.initDefaultSlideTrackerWidth();
      this.navs.forEach(function (nav, index) {
        nav.addEventListener('click', function (event) {
          return _this.onNavClick(event, index);
        });
      });
    }
  }, {
    key: "onNavClick",
    value: function onNavClick(event, index) {
      this.removeActiveClassnameFromNavs();
      event.target.classList.add('frm-active');
      this.initSlideTrackUnderline(event.target, index);
      this.changeSlide(index);
    }
  }, {
    key: "initDefaultSlideTrackerWidth",
    value: function initDefaultSlideTrackerWidth() {
      if (!this.slideTrackLine.dataset.initialWidth) {
        return;
      }
      this.slideTrackLine.style.width = "".concat(this.slideTrackLine.dataset.initialWidth, "px");
    }
  }, {
    key: "initSlideTrackUnderline",
    value: function initSlideTrackUnderline(nav, index) {
      this.slideTrackLine.classList.remove('frm-first', 'frm-last');
      var activeNav = 'undefined' !== typeof nav ? nav : this.navs.filter(function (nav) {
        return nav.classList.contains('frm-active');
      });
      this.slideTrackLine.style.transform = "translateX(".concat(activeNav.offsetLeft, "px)");
      this.slideTrackLine.style.width = activeNav.clientWidth + 'px';
      if (this.navs.length === index + 1) {
        this.slideTrackLine.classList.add('frm-last');
        return;
      }
      if (0 === index) {
        this.slideTrackLine.classList.add('frm-first');
      }
    }
  }, {
    key: "changeSlide",
    value: function changeSlide(index) {
      this.removeActiveClassnameFromSlides();
      var translate = index == 0 ? '0px' : "calc( ( ".concat(index * 100, "% + ").concat(parseInt(this.flexboxSlidesGap, 10) * index, "px ) * -1 )");
      this.slideTrack.style.transform = "translateX(".concat(translate, ")");
      if (index in this.slides) {
        this.slides[index].classList.add('frm-active');
      }
    }
  }, {
    key: "removeActiveClassnameFromSlides",
    value: function removeActiveClassnameFromSlides() {
      this.slides.forEach(function (slide) {
        return slide.classList.remove('frm-active');
      });
    }
  }, {
    key: "removeActiveClassnameFromNavs",
    value: function removeActiveClassnameFromNavs() {
      this.navs.forEach(function (nav) {
        return nav.classList.remove('frm-active');
      });
    }
  }]);
}();

/***/ }),

/***/ "./js/src/core/constants.js":
/*!**********************************!*\
  !*** ./js/src/core/constants.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CHECKED_CLASS: function() { return /* binding */ CHECKED_CLASS; },
/* harmony export */   CURRENT_CLASS: function() { return /* binding */ CURRENT_CLASS; },
/* harmony export */   HIDDEN_CLASS: function() { return /* binding */ HIDDEN_CLASS; },
/* harmony export */   HIDE_JS_CLASS: function() { return /* binding */ HIDE_JS_CLASS; },
/* harmony export */   PLUGIN_URL: function() { return /* binding */ PLUGIN_URL; },
/* harmony export */   nonce: function() { return /* binding */ nonce; }
/* harmony export */ });
var _window$frmGlobal = window.frmGlobal,
  PLUGIN_URL = _window$frmGlobal.url,
  nonce = _window$frmGlobal.nonce;

var HIDDEN_CLASS = 'frm_hidden';
var HIDE_JS_CLASS = 'frm-hide-js';
var CURRENT_CLASS = 'frm-current';
var CHECKED_CLASS = 'frm-checked';

/***/ }),

/***/ "./js/src/core/utils/animation.js":
/*!****************************************!*\
  !*** ./js/src/core/utils/animation.js ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmAnimate: function() { return /* binding */ frmAnimate; }
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var frmAnimate = /*#__PURE__*/function () {
  /**
   * Construct frmAnimate
   *
   * @param {Element|Element[]} elements
   * @param {'default'|'cascade'|'cascade-3d'} type - The animation type: default | cascade | cascade-3d
   *
   */
  function frmAnimate(elements) {
    var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
    _classCallCheck(this, frmAnimate);
    this.elements = elements;
    this.type = type;
    this.prepareElements();
  }

  /**
   * Init animation - fadeIn.
   * Requires this.type = 'default';
   * ex: new frmAnimate( elements ).fadeIn();
   */
  return _createClass(frmAnimate, [{
    key: "fadeIn",
    value: function fadeIn() {
      var _this = this;
      this.applyStyleToElements(function (element) {
        element.classList.add('frm-fadein-up');
        element.addEventListener('animationend', function () {
          _this.resetOpacity();
          element.classList.remove('frm-fadein-up');
        }, {
          once: true
        });
      });
    }

    /**
     * Init animation - cascadeFadeIn.
     * Requires this.type = 'cascade'|'cascade-3d';
     * ex: new frmAnimate( elements, 'cascade' ).cascadeFadeIn();
     *     new frmAnimate( elements, 'cascade-3d' ).cascadeFadeIn();
     *
     * @param {float} delay - The transition delay value.
     *
     */
  }, {
    key: "cascadeFadeIn",
    value: function cascadeFadeIn() {
      var _this2 = this;
      var delay = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0.03;
      setTimeout(function () {
        _this2.applyStyleToElements(function (element, index) {
          element.classList.remove('frm-animate');
          element.style.transitionDelay = (index + 1) * delay + 's';
        });
      }, 200);
    }
  }, {
    key: "prepareElements",
    value: function prepareElements() {
      var _this3 = this;
      this.applyStyleToElements(function (element) {
        if ('default' === _this3.type) {
          element.style.opacity = '0.0';
        }
        if ('cascade' === _this3.type) {
          element.classList.add('frm-init-cascade-animation');
        }
        if ('cascade-3d' === _this3.type) {
          element.classList.add('frm-init-fadein-3d');
        }
        element.classList.add('frm-animate');
      });
    }
  }, {
    key: "resetOpacity",
    value: function resetOpacity() {
      this.applyStyleToElements(function (element) {
        return element.style.opacity = '1.0';
      });
    }
  }, {
    key: "applyStyleToElements",
    value: function applyStyleToElements(callback) {
      if (this.elements instanceof Element) {
        callback(this.elements, 0);
        return;
      }
      if (0 < this.elements.length) {
        this.elements.forEach(function (element, index) {
          return callback(element, index);
        });
      }
    }
  }]);
}();

/***/ }),

/***/ "./js/src/core/utils/async.js":
/*!************************************!*\
  !*** ./js/src/core/utils/async.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: function() { return /* binding */ addToRequestQueue; }
/* harmony export */ });
// Initialize lastPromise with a resolved promise as the starting point for the queue
var lastPromise = Promise.resolve();

/**
 * Adds a task to the request queue.
 *
 * @param {function(): Promise<any>} task A function that returns a promise.
 * @return {Promise<any>} The new last promise in the queue.
 */
var addToRequestQueue = function addToRequestQueue(task) {
  return lastPromise = lastPromise.then(task).catch(task);
};

/***/ }),

/***/ "./js/src/core/utils/error.js":
/*!************************************!*\
  !*** ./js/src/core/utils/error.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showFormError: function() { return /* binding */ showFormError; }
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/core/utils/index.js");
/**
 * Internal dependencies
 */


/**
 * Displays form validation error messages.
 *
 * @param {string} inputId   The ID selector for the input field with the error.
 * @param {string} errorId   The ID selector for the error message display element.
 * @param {string} type      The categorization of the error (e.g., "invalid", "empty").
 * @param {string} [message] Optional. The specific error message to display.
 * @return {void}
 */
var showFormError = function showFormError(inputId, errorId, type, message) {
  var inputElement = document.querySelector(inputId);
  var errorElement = document.querySelector(errorId);
  if (!inputElement || !errorElement) {
    console.warn('showFormError: Unable to find input or error element.');
    return;
  }

  // If a message is provided, update the span element's text that matches the error type
  if (message) {
    var span = errorElement.querySelector("span[frm-error=\"".concat(type, "\"]"));
    if (span) {
      span.textContent = message;
    }
  }

  // Assign the error type and make the error message visible
  errorElement.setAttribute('frm-error', type);
  (0,___WEBPACK_IMPORTED_MODULE_0__.show)(errorElement);

  // Hide the error message when the user starts typing in the faulty input field
  inputElement.addEventListener('keyup', function () {
    (0,___WEBPACK_IMPORTED_MODULE_0__.hide)(errorElement);
  }, {
    once: true
  });
};

/***/ }),

/***/ "./js/src/core/utils/event.js":
/*!************************************!*\
  !*** ./js/src/core/utils/event.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   onClickPreventDefault: function() { return /* binding */ onClickPreventDefault; }
/* harmony export */ });
var onClickPreventDefault = window.frmDom.util.onClickPreventDefault;


/***/ }),

/***/ "./js/src/core/utils/index.js":
/*!************************************!*\
  !*** ./js/src/core/utils/index.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: function() { return /* reexport safe */ _async__WEBPACK_IMPORTED_MODULE_1__.addToRequestQueue; },
/* harmony export */   frmAnimate: function() { return /* reexport safe */ _animation__WEBPACK_IMPORTED_MODULE_0__.frmAnimate; },
/* harmony export */   getQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.getQueryParam; },
/* harmony export */   hasQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.hasQueryParam; },
/* harmony export */   hide: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.hide; },
/* harmony export */   hideElements: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.hideElements; },
/* harmony export */   isEmptyObject: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isEmptyObject; },
/* harmony export */   isHTMLElement: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isHTMLElement; },
/* harmony export */   isValidEmail: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isValidEmail; },
/* harmony export */   isVisible: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.isVisible; },
/* harmony export */   onClickPreventDefault: function() { return /* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault; },
/* harmony export */   removeQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.removeQueryParam; },
/* harmony export */   setQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.setQueryParam; },
/* harmony export */   show: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.show; },
/* harmony export */   showElements: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.showElements; },
/* harmony export */   showFormError: function() { return /* reexport safe */ _error__WEBPACK_IMPORTED_MODULE_2__.showFormError; }
/* harmony export */ });
/* harmony import */ var _animation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./animation */ "./js/src/core/utils/animation.js");
/* harmony import */ var _async__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./async */ "./js/src/core/utils/async.js");
/* harmony import */ var _error__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./error */ "./js/src/core/utils/error.js");
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./event */ "./js/src/core/utils/event.js");
/* harmony import */ var _url__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./url */ "./js/src/core/utils/url.js");
/* harmony import */ var _validation__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./validation */ "./js/src/core/utils/validation.js");
/* harmony import */ var _visibility__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./visibility */ "./js/src/core/utils/visibility.js");








/***/ }),

/***/ "./js/src/core/utils/url.js":
/*!**********************************!*\
  !*** ./js/src/core/utils/url.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getQueryParam: function() { return /* binding */ getQueryParam; },
/* harmony export */   hasQueryParam: function() { return /* binding */ hasQueryParam; },
/* harmony export */   removeQueryParam: function() { return /* binding */ removeQueryParam; },
/* harmony export */   setQueryParam: function() { return /* binding */ setQueryParam; }
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Initializes URL and URLSearchParams objects from the current window's location
 */
var url = new URL(window.location.href);
var urlParams = url.searchParams;

/**
 * Gets the value of a specified query parameter from the current URL.
 *
 * @param {string} paramName The name of the query parameter to retrieve.
 * @return {string|null} The value associated with the specified query parameter name, or null if not found.
 */
var getQueryParam = function getQueryParam(paramName) {
  return urlParams.get(paramName);
};

/**
 * Removes a query parameter from the current URL and returns the updated URL string.
 *
 * @param {string} paramName The name of the query parameter to remove.
 * @return {string} The updated URL string.
 */
var removeQueryParam = function removeQueryParam(paramName) {
  urlParams.delete(paramName);
  url.search = urlParams.toString();
  return url.toString();
};

/**
 * Sets the value of a query parameter in the current URL and optionally updates the browser's history state.
 *
 * @param {string} paramName                  The name of the query parameter to set.
 * @param {string} paramValue                 The value to set for the query parameter.
 * @param {string} [updateMethod='pushState'] The method to use for updating the history state. Accepts 'pushState' or 'replaceState'.
 * @return {string} The updated URL string.
 */
var setQueryParam = function setQueryParam(paramName, paramValue) {
  var updateMethod = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'pushState';
  urlParams.set(paramName, paramValue);
  url.search = urlParams.toString();
  if (['pushState', 'replaceState'].includes(updateMethod)) {
    var state = _defineProperty({}, paramName, paramValue);
    window.history[updateMethod](state, '', url);
  }
  return url.toString();
};

/**
 * Checks if a query parameter exists in the current URL.
 *
 * @param {string} paramName The name of the query parameter to check.
 * @return {boolean} True if the query parameter exists, otherwise false.
 */
var hasQueryParam = function hasQueryParam(paramName) {
  return urlParams.has(paramName);
};

/***/ }),

/***/ "./js/src/core/utils/validation.js":
/*!*****************************************!*\
  !*** ./js/src/core/utils/validation.js ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isEmptyObject: function() { return /* binding */ isEmptyObject; },
/* harmony export */   isHTMLElement: function() { return /* binding */ isHTMLElement; },
/* harmony export */   isValidEmail: function() { return /* binding */ isValidEmail; }
/* harmony export */ });
/**
 * Validates an email address using a regular expression.
 *
 * @param {string} email The email address to validate.
 * @return {boolean} True if the email address is valid, otherwise false.
 */
var isValidEmail = function isValidEmail(email) {
  return typeof email === 'string' ? /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i.test(email) : false;
};

/**
 * Validates if the given element is an instance of HTMLElement.
 *
 * @param {any} element Element to be checked.
 * @return {boolean} True if it's an HTMLElement, otherwise false.
 */
var isHTMLElement = function isHTMLElement(element) {
  return element instanceof HTMLElement || console.warn('Invalid argument: Element must be an instance of HTMLElement') || false;
};

/**
 * Checks if the given object is empty.
 *
 * @param {Object} obj The object to check.
 * @return {boolean} True if the object is empty, otherwise false.
 */
var isEmptyObject = function isEmptyObject(obj) {
  return Object.keys(obj).length === 0 && obj.constructor === Object;
};

/***/ }),

/***/ "./js/src/core/utils/visibility.js":
/*!*****************************************!*\
  !*** ./js/src/core/utils/visibility.js ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hide: function() { return /* binding */ hide; },
/* harmony export */   hideElements: function() { return /* binding */ hideElements; },
/* harmony export */   isVisible: function() { return /* binding */ isVisible; },
/* harmony export */   show: function() { return /* binding */ show; },
/* harmony export */   showElements: function() { return /* binding */ showElements; }
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/**
 * Internal dependencies
 */


/**
 * Shows specified elements by removing the hidden class.
 *
 * @param {Array<Element>} elements An array of elements to show.
 * @return {void}
 */
var showElements = function showElements(elements) {
  var _Array$from;
  return (_Array$from = Array.from(elements)) === null || _Array$from === void 0 ? void 0 : _Array$from.forEach(function (element) {
    return show(element);
  });
};

/**
 * Hides specified elements by adding the hidden class.
 *
 * @param {Array<Element>} elements An array of elements to hide.
 * @return {void}
 */
var hideElements = function hideElements(elements) {
  var _Array$from2;
  return (_Array$from2 = Array.from(elements)) === null || _Array$from2 === void 0 ? void 0 : _Array$from2.forEach(function (element) {
    return hide(element);
  });
};

/**
 * Removes the hidden class to show the element.
 *
 * @param {Element} element The element to show.
 * @return {void}
 */
var show = function show(element) {
  return element === null || element === void 0 ? void 0 : element.classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
};

/**
 * Adds the hidden class to hide the element.
 *
 * @param {Element} element The element to hide.
 * @return {void}
 */
var hide = function hide(element) {
  return element === null || element === void 0 ? void 0 : element.classList.add(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
};

/**
 * Checks if an element is visible.
 *
 * @param {HTMLElement} element The HTML element to check for visibility.
 * @return {boolean} Returns true if the element is visible, otherwise false.
 */
var isVisible = function isVisible(element) {
  var styles = window.getComputedStyle(element);
  return styles.getPropertyValue('display') !== 'none';
};

/***/ }),

/***/ "./node_modules/memize/index.js":
/*!**************************************!*\
  !*** ./node_modules/memize/index.js ***!
  \**************************************/
/***/ (function(module) {

/**
 * Memize options object.
 *
 * @typedef MemizeOptions
 *
 * @property {number} [maxSize] Maximum size of the cache.
 */

/**
 * Internal cache entry.
 *
 * @typedef MemizeCacheNode
 *
 * @property {?MemizeCacheNode|undefined} [prev] Previous node.
 * @property {?MemizeCacheNode|undefined} [next] Next node.
 * @property {Array<*>}                   args   Function arguments for cache
 *                                               entry.
 * @property {*}                          val    Function result.
 */

/**
 * Properties of the enhanced function for controlling cache.
 *
 * @typedef MemizeMemoizedFunction
 *
 * @property {()=>void} clear Clear the cache.
 */

/**
 * Accepts a function to be memoized, and returns a new memoized function, with
 * optional options.
 *
 * @template {Function} F
 *
 * @param {F}             fn        Function to memoize.
 * @param {MemizeOptions} [options] Options object.
 *
 * @return {F & MemizeMemoizedFunction} Memoized function.
 */
function memize( fn, options ) {
	var size = 0;

	/** @type {?MemizeCacheNode|undefined} */
	var head;

	/** @type {?MemizeCacheNode|undefined} */
	var tail;

	options = options || {};

	function memoized( /* ...args */ ) {
		var node = head,
			len = arguments.length,
			args, i;

		searchCache: while ( node ) {
			// Perform a shallow equality test to confirm that whether the node
			// under test is a candidate for the arguments passed. Two arrays
			// are shallowly equal if their length matches and each entry is
			// strictly equal between the two sets. Avoid abstracting to a
			// function which could incur an arguments leaking deoptimization.

			// Check whether node arguments match arguments length
			if ( node.args.length !== arguments.length ) {
				node = node.next;
				continue;
			}

			// Check whether node arguments match arguments values
			for ( i = 0; i < len; i++ ) {
				if ( node.args[ i ] !== arguments[ i ] ) {
					node = node.next;
					continue searchCache;
				}
			}

			// At this point we can assume we've found a match

			// Surface matched node to head if not already
			if ( node !== head ) {
				// As tail, shift to previous. Must only shift if not also
				// head, since if both head and tail, there is no previous.
				if ( node === tail ) {
					tail = node.prev;
				}

				// Adjust siblings to point to each other. If node was tail,
				// this also handles new tail's empty `next` assignment.
				/** @type {MemizeCacheNode} */ ( node.prev ).next = node.next;
				if ( node.next ) {
					node.next.prev = node.prev;
				}

				node.next = head;
				node.prev = null;
				/** @type {MemizeCacheNode} */ ( head ).prev = node;
				head = node;
			}

			// Return immediately
			return node.val;
		}

		// No cached value found. Continue to insertion phase:

		// Create a copy of arguments (avoid leaking deoptimization)
		args = new Array( len );
		for ( i = 0; i < len; i++ ) {
			args[ i ] = arguments[ i ];
		}

		node = {
			args: args,

			// Generate the result from original function
			val: fn.apply( null, args ),
		};

		// Don't need to check whether node is already head, since it would
		// have been returned above already if it was

		// Shift existing head down list
		if ( head ) {
			head.prev = node;
			node.next = head;
		} else {
			// If no head, follows that there's no tail (at initial or reset)
			tail = node;
		}

		// Trim tail if we're reached max size and are pending cache insertion
		if ( size === /** @type {MemizeOptions} */ ( options ).maxSize ) {
			tail = /** @type {MemizeCacheNode} */ ( tail ).prev;
			/** @type {MemizeCacheNode} */ ( tail ).next = null;
		} else {
			size++;
		}

		head = node;

		return node.val;
	}

	memoized.clear = function() {
		head = null;
		tail = null;
		size = 0;
	};

	if ( false ) {}

	// Ignore reason: There's not a clear solution to create an intersection of
	// the function with additional properties, where the goal is to retain the
	// function signature of the incoming argument and add control properties
	// on the return value.

	// @ts-ignore
	return memoized;
}

module.exports = memize;


/***/ }),

/***/ "./node_modules/sprintf-js/src/sprintf.js":
/*!************************************************!*\
  !*** ./node_modules/sprintf-js/src/sprintf.js ***!
  \************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_RESULT__;/* global window, exports, define */

!function() {
    'use strict'

    var re = {
        not_string: /[^s]/,
        not_bool: /[^t]/,
        not_type: /[^T]/,
        not_primitive: /[^v]/,
        number: /[diefg]/,
        numeric_arg: /[bcdiefguxX]/,
        json: /[j]/,
        not_json: /[^j]/,
        text: /^[^\x25]+/,
        modulo: /^\x25{2}/,
        placeholder: /^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/,
        key: /^([a-z_][a-z_\d]*)/i,
        key_access: /^\.([a-z_][a-z_\d]*)/i,
        index_access: /^\[(\d+)\]/,
        sign: /^[+-]/
    }

    function sprintf(key) {
        // `arguments` is not an array, but should be fine for this call
        return sprintf_format(sprintf_parse(key), arguments)
    }

    function vsprintf(fmt, argv) {
        return sprintf.apply(null, [fmt].concat(argv || []))
    }

    function sprintf_format(parse_tree, argv) {
        var cursor = 1, tree_length = parse_tree.length, arg, output = '', i, k, ph, pad, pad_character, pad_length, is_positive, sign
        for (i = 0; i < tree_length; i++) {
            if (typeof parse_tree[i] === 'string') {
                output += parse_tree[i]
            }
            else if (typeof parse_tree[i] === 'object') {
                ph = parse_tree[i] // convenience purposes only
                if (ph.keys) { // keyword argument
                    arg = argv[cursor]
                    for (k = 0; k < ph.keys.length; k++) {
                        if (arg == undefined) {
                            throw new Error(sprintf('[sprintf] Cannot access property "%s" of undefined value "%s"', ph.keys[k], ph.keys[k-1]))
                        }
                        arg = arg[ph.keys[k]]
                    }
                }
                else if (ph.param_no) { // positional argument (explicit)
                    arg = argv[ph.param_no]
                }
                else { // positional argument (implicit)
                    arg = argv[cursor++]
                }

                if (re.not_type.test(ph.type) && re.not_primitive.test(ph.type) && arg instanceof Function) {
                    arg = arg()
                }

                if (re.numeric_arg.test(ph.type) && (typeof arg !== 'number' && isNaN(arg))) {
                    throw new TypeError(sprintf('[sprintf] expecting number but found %T', arg))
                }

                if (re.number.test(ph.type)) {
                    is_positive = arg >= 0
                }

                switch (ph.type) {
                    case 'b':
                        arg = parseInt(arg, 10).toString(2)
                        break
                    case 'c':
                        arg = String.fromCharCode(parseInt(arg, 10))
                        break
                    case 'd':
                    case 'i':
                        arg = parseInt(arg, 10)
                        break
                    case 'j':
                        arg = JSON.stringify(arg, null, ph.width ? parseInt(ph.width) : 0)
                        break
                    case 'e':
                        arg = ph.precision ? parseFloat(arg).toExponential(ph.precision) : parseFloat(arg).toExponential()
                        break
                    case 'f':
                        arg = ph.precision ? parseFloat(arg).toFixed(ph.precision) : parseFloat(arg)
                        break
                    case 'g':
                        arg = ph.precision ? String(Number(arg.toPrecision(ph.precision))) : parseFloat(arg)
                        break
                    case 'o':
                        arg = (parseInt(arg, 10) >>> 0).toString(8)
                        break
                    case 's':
                        arg = String(arg)
                        arg = (ph.precision ? arg.substring(0, ph.precision) : arg)
                        break
                    case 't':
                        arg = String(!!arg)
                        arg = (ph.precision ? arg.substring(0, ph.precision) : arg)
                        break
                    case 'T':
                        arg = Object.prototype.toString.call(arg).slice(8, -1).toLowerCase()
                        arg = (ph.precision ? arg.substring(0, ph.precision) : arg)
                        break
                    case 'u':
                        arg = parseInt(arg, 10) >>> 0
                        break
                    case 'v':
                        arg = arg.valueOf()
                        arg = (ph.precision ? arg.substring(0, ph.precision) : arg)
                        break
                    case 'x':
                        arg = (parseInt(arg, 10) >>> 0).toString(16)
                        break
                    case 'X':
                        arg = (parseInt(arg, 10) >>> 0).toString(16).toUpperCase()
                        break
                }
                if (re.json.test(ph.type)) {
                    output += arg
                }
                else {
                    if (re.number.test(ph.type) && (!is_positive || ph.sign)) {
                        sign = is_positive ? '+' : '-'
                        arg = arg.toString().replace(re.sign, '')
                    }
                    else {
                        sign = ''
                    }
                    pad_character = ph.pad_char ? ph.pad_char === '0' ? '0' : ph.pad_char.charAt(1) : ' '
                    pad_length = ph.width - (sign + arg).length
                    pad = ph.width ? (pad_length > 0 ? pad_character.repeat(pad_length) : '') : ''
                    output += ph.align ? sign + arg + pad : (pad_character === '0' ? sign + pad + arg : pad + sign + arg)
                }
            }
        }
        return output
    }

    var sprintf_cache = Object.create(null)

    function sprintf_parse(fmt) {
        if (sprintf_cache[fmt]) {
            return sprintf_cache[fmt]
        }

        var _fmt = fmt, match, parse_tree = [], arg_names = 0
        while (_fmt) {
            if ((match = re.text.exec(_fmt)) !== null) {
                parse_tree.push(match[0])
            }
            else if ((match = re.modulo.exec(_fmt)) !== null) {
                parse_tree.push('%')
            }
            else if ((match = re.placeholder.exec(_fmt)) !== null) {
                if (match[2]) {
                    arg_names |= 1
                    var field_list = [], replacement_field = match[2], field_match = []
                    if ((field_match = re.key.exec(replacement_field)) !== null) {
                        field_list.push(field_match[1])
                        while ((replacement_field = replacement_field.substring(field_match[0].length)) !== '') {
                            if ((field_match = re.key_access.exec(replacement_field)) !== null) {
                                field_list.push(field_match[1])
                            }
                            else if ((field_match = re.index_access.exec(replacement_field)) !== null) {
                                field_list.push(field_match[1])
                            }
                            else {
                                throw new SyntaxError('[sprintf] failed to parse named argument key')
                            }
                        }
                    }
                    else {
                        throw new SyntaxError('[sprintf] failed to parse named argument key')
                    }
                    match[2] = field_list
                }
                else {
                    arg_names |= 2
                }
                if (arg_names === 3) {
                    throw new Error('[sprintf] mixing positional and named placeholders is not (yet) supported')
                }

                parse_tree.push(
                    {
                        placeholder: match[0],
                        param_no:    match[1],
                        keys:        match[2],
                        sign:        match[3],
                        pad_char:    match[4],
                        align:       match[5],
                        width:       match[6],
                        precision:   match[7],
                        type:        match[8]
                    }
                )
            }
            else {
                throw new SyntaxError('[sprintf] unexpected placeholder')
            }
            _fmt = _fmt.substring(match[0].length)
        }
        return sprintf_cache[fmt] = parse_tree
    }

    /**
     * export to either browser or node.js
     */
    /* eslint-disable quote-props */
    if (true) {
        exports.sprintf = sprintf
        exports.vsprintf = vsprintf
    }
    if (typeof window !== 'undefined') {
        window['sprintf'] = sprintf
        window['vsprintf'] = vsprintf

        if (true) {
            !(__WEBPACK_AMD_DEFINE_RESULT__ = (function() {
                return {
                    'sprintf': sprintf,
                    'vsprintf': vsprintf
                }
            }).call(exports, __webpack_require__, exports, module),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__))
        }
    }
    /* eslint-enable quote-props */
}(); // eslint-disable-line


/***/ }),

/***/ "./node_modules/tannin/index.js":
/*!**************************************!*\
  !*** ./node_modules/tannin/index.js ***!
  \**************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Tannin; }
/* harmony export */ });
/* harmony import */ var _tannin_plural_forms__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tannin/plural-forms */ "./node_modules/@tannin/plural-forms/index.js");


/**
 * Tannin constructor options.
 *
 * @typedef {Object} TanninOptions
 *
 * @property {string}   [contextDelimiter] Joiner in string lookup with context.
 * @property {Function} [onMissingKey]     Callback to invoke when key missing.
 */

/**
 * Domain metadata.
 *
 * @typedef {Object} TanninDomainMetadata
 *
 * @property {string}            [domain]       Domain name.
 * @property {string}            [lang]         Language code.
 * @property {(string|Function)} [plural_forms] Plural forms expression or
 *                                              function evaluator.
 */

/**
 * Domain translation pair respectively representing the singular and plural
 * translation.
 *
 * @typedef {[string,string]} TanninTranslation
 */

/**
 * Locale data domain. The key is used as reference for lookup, the value an
 * array of two string entries respectively representing the singular and plural
 * translation.
 *
 * @typedef {{[key:string]:TanninDomainMetadata|TanninTranslation,'':TanninDomainMetadata|TanninTranslation}} TanninLocaleDomain
 */

/**
 * Jed-formatted locale data.
 *
 * @see http://messageformat.github.io/Jed/
 *
 * @typedef {{[domain:string]:TanninLocaleDomain}} TanninLocaleData
 */

/**
 * Default Tannin constructor options.
 *
 * @type {TanninOptions}
 */
var DEFAULT_OPTIONS = {
	contextDelimiter: '\u0004',
	onMissingKey: null,
};

/**
 * Given a specific locale data's config `plural_forms` value, returns the
 * expression.
 *
 * @example
 *
 * ```
 * getPluralExpression( 'nplurals=2; plural=(n != 1);' ) === '(n != 1)'
 * ```
 *
 * @param {string} pf Locale data plural forms.
 *
 * @return {string} Plural forms expression.
 */
function getPluralExpression( pf ) {
	var parts, i, part;

	parts = pf.split( ';' );

	for ( i = 0; i < parts.length; i++ ) {
		part = parts[ i ].trim();
		if ( part.indexOf( 'plural=' ) === 0 ) {
			return part.substr( 7 );
		}
	}
}

/**
 * Tannin constructor.
 *
 * @class
 *
 * @param {TanninLocaleData} data      Jed-formatted locale data.
 * @param {TanninOptions}    [options] Tannin options.
 */
function Tannin( data, options ) {
	var key;

	/**
	 * Jed-formatted locale data.
	 *
	 * @name Tannin#data
	 * @type {TanninLocaleData}
	 */
	this.data = data;

	/**
	 * Plural forms function cache, keyed by plural forms string.
	 *
	 * @name Tannin#pluralForms
	 * @type {Object<string,Function>}
	 */
	this.pluralForms = {};

	/**
	 * Effective options for instance, including defaults.
	 *
	 * @name Tannin#options
	 * @type {TanninOptions}
	 */
	this.options = {};

	for ( key in DEFAULT_OPTIONS ) {
		this.options[ key ] = options !== undefined && key in options
			? options[ key ]
			: DEFAULT_OPTIONS[ key ];
	}
}

/**
 * Returns the plural form index for the given domain and value.
 *
 * @param {string} domain Domain on which to calculate plural form.
 * @param {number} n      Value for which plural form is to be calculated.
 *
 * @return {number} Plural form index.
 */
Tannin.prototype.getPluralForm = function( domain, n ) {
	var getPluralForm = this.pluralForms[ domain ],
		config, plural, pf;

	if ( ! getPluralForm ) {
		config = this.data[ domain ][ '' ];

		pf = (
			config[ 'Plural-Forms' ] ||
			config[ 'plural-forms' ] ||
			// Ignore reason: As known, there's no way to document the empty
			// string property on a key to guarantee this as metadata.
			// @ts-ignore
			config.plural_forms
		);

		if ( typeof pf !== 'function' ) {
			plural = getPluralExpression(
				config[ 'Plural-Forms' ] ||
				config[ 'plural-forms' ] ||
				// Ignore reason: As known, there's no way to document the empty
				// string property on a key to guarantee this as metadata.
				// @ts-ignore
				config.plural_forms
			);

			pf = (0,_tannin_plural_forms__WEBPACK_IMPORTED_MODULE_0__["default"])( plural );
		}

		getPluralForm = this.pluralForms[ domain ] = pf;
	}

	return getPluralForm( n );
};

/**
 * Translate a string.
 *
 * @param {string}      domain   Translation domain.
 * @param {string|void} context  Context distinguishing terms of the same name.
 * @param {string}      singular Primary key for translation lookup.
 * @param {string=}     plural   Fallback value used for non-zero plural
 *                               form index.
 * @param {number=}     n        Value to use in calculating plural form.
 *
 * @return {string} Translated string.
 */
Tannin.prototype.dcnpgettext = function( domain, context, singular, plural, n ) {
	var index, key, entry;

	if ( n === undefined ) {
		// Default to singular.
		index = 0;
	} else {
		// Find index by evaluating plural form for value.
		index = this.getPluralForm( domain, n );
	}

	key = singular;

	// If provided, context is prepended to key with delimiter.
	if ( context ) {
		key = context + this.options.contextDelimiter + singular;
	}

	entry = this.data[ domain ][ key ];

	// Verify not only that entry exists, but that the intended index is within
	// range and non-empty.
	if ( entry && entry[ index ] ) {
		return entry[ index ];
	}

	if ( this.options.onMissingKey ) {
		this.options.onMissingKey( singular, domain );
	}

	// If entry not found, fall back to singular vs. plural with zero index
	// representing the singular value.
	return index === 0 ? singular : plural;
};


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js":
/*!***************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js ***!
  \***************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _arrayLikeToArray; }
/* harmony export */ });
function _arrayLikeToArray(r, a) {
  (null == a || a > r.length) && (a = r.length);
  for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
  return n;
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js":
/*!****************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js ***!
  \****************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _arrayWithoutHoles; }
/* harmony export */ });
/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

function _arrayWithoutHoles(r) {
  if (Array.isArray(r)) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r);
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/classCallCheck.js":
/*!*************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/classCallCheck.js ***!
  \*************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _classCallCheck; }
/* harmony export */ });
function _classCallCheck(a, n) {
  if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/iterableToArray.js":
/*!**************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/iterableToArray.js ***!
  \**************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _iterableToArray; }
/* harmony export */ });
function _iterableToArray(r) {
  if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js":
/*!****************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js ***!
  \****************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _nonIterableSpread; }
/* harmony export */ });
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/toConsumableArray.js":
/*!****************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/toConsumableArray.js ***!
  \****************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _toConsumableArray; }
/* harmony export */ });
/* harmony import */ var _arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayWithoutHoles.js */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js");
/* harmony import */ var _iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./iterableToArray.js */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/iterableToArray.js");
/* harmony import */ var _unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./unsupportedIterableToArray.js */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js");
/* harmony import */ var _nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./nonIterableSpread.js */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js");




function _toConsumableArray(r) {
  return (0,_arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r) || (0,_iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__["default"])(r) || (0,_unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__["default"])(r) || (0,_nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__["default"])();
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js":
/*!*************************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js ***!
  \*************************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _unsupportedIterableToArray; }
/* harmony export */ });
/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ("string" == typeof r) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r, a) : void 0;
  }
}


/***/ }),

/***/ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/defineProperty.js":
/*!************************************************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/defineProperty.js ***!
  \************************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _defineProperty; }
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperty(e, r, t) {
  return (r = (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r)) in e ? Object.defineProperty(e, r, {
    value: t,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[r] = t, e;
}


/***/ }),

/***/ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/toPrimitive.js":
/*!*********************************************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/toPrimitive.js ***!
  \*********************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ toPrimitive; }
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/typeof.js");

function toPrimitive(t, r) {
  if ("object" != (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(t) || !t) return t;
  var e = t[Symbol.toPrimitive];
  if (void 0 !== e) {
    var i = e.call(t, r || "default");
    if ("object" != (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i)) return i;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return ("string" === r ? String : Number)(t);
}


/***/ }),

/***/ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/toPropertyKey.js":
/*!***********************************************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/toPropertyKey.js ***!
  \***********************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ toPropertyKey; }
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./toPrimitive.js */ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/toPrimitive.js");


function toPropertyKey(t) {
  var i = (0,_toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__["default"])(t, "string");
  return "symbol" == (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i) ? i : i + "";
}


/***/ }),

/***/ "./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!****************************************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \****************************************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _typeof; }
/* harmony export */ });
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";
/*!********************************!*\
  !*** ./js/src/admin/styles.js ***!
  \********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _styles_radio_style_component_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/radio-style-component.js */ "./js/src/admin/styles/radio-style-component.js");
/* harmony import */ var _styles_slider_style_component_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./styles/slider-style-component.js */ "./js/src/admin/styles/slider-style-component.js");
/* harmony import */ var _styles_tabs_style_component_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./styles/tabs-style-component.js */ "./js/src/admin/styles/tabs-style-component.js");
/* harmony import */ var _styles_dependent_updater_component_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./styles/dependent-updater-component.js */ "./js/src/admin/styles/dependent-updater-component.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }






/**
 * Represents the frmStyleOptions class.
 * @class
 */
var frmStyleOptions = /*#__PURE__*/function () {
  function frmStyleOptions() {
    _classCallCheck(this, frmStyleOptions);
    this.success = frmDom.success;
    this.init();
    this.initHover();
  }

  /**
   * Initializes the style components: frmRadioStyleComponent, frmSliderStyleComponent, and frmTabsStyleComponent.
   * Init the dependent 
   */
  return _createClass(frmStyleOptions, [{
    key: "init",
    value: function init() {
      new _styles_radio_style_component_js__WEBPACK_IMPORTED_MODULE_1__["default"]();
      new _styles_slider_style_component_js__WEBPACK_IMPORTED_MODULE_2__["default"]();
      new _styles_tabs_style_component_js__WEBPACK_IMPORTED_MODULE_3__["default"]();
      this.initColorPickerDependentUpdaterComponents();
      this.initStyleClassCopyToClipboard((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('The class name has been copied.', 'formidable'));
    }

    /**
     * Initializes the color picker dependent updater components.
     * Retrieves the components and elements, and adds them to the elements array.
     * Adds an action hook for the frm_style_options_color_change event.
     */
  }, {
    key: "initColorPickerDependentUpdaterComponents",
    value: function initColorPickerDependentUpdaterComponents() {
      var components = document.querySelectorAll('.frm-style-dependent-updater-component.frm-colorpicker');
      var elements = [];
      components.forEach(function (component) {
        var element = component.querySelector('input.hex');
        var id = 'undefined' !== typeof element ? element.getAttribute('id') : null;
        if (null !== id) {
          elements.push({
            id: id,
            dependentUpdaterClass: new _styles_dependent_updater_component_js__WEBPACK_IMPORTED_MODULE_4__["default"](component, 'colorpicker')
          });
        }
      });
      wp.hooks.addAction('frm_style_options_color_change', 'formidable', function (_ref) {
        var event = _ref.event,
          value = _ref.value;
        var container = event.target.closest('.wp-picker-container');
        var id = event.target.getAttribute('id');
        container.querySelector('.wp-color-result-text').innerText = value;
        elements.forEach(function (element) {
          if (element.id === id) {
            element.dependentUpdaterClass.updateAllDependentElements(value);
          }
        });
      });
    }

    /**
     * Initializes the hover functionality for the style options.
     * Creates a hover element and appends it to the settingsWrapper.
     * Adds event listeners for mouseover and click events.
     */
  }, {
    key: "initHover",
    value: function initHover() {
      var settingsWrapper = document.querySelector('.frm-right-panel .styling_settings .accordion-container');
      if (null === settingsWrapper) {
        return;
      }
      var hoverElement = document.createElement('div');
      hoverElement.classList.add('frm_hidden');
      hoverElement.classList.add('frm-style-settings-hover');
      settingsWrapper.appendChild(hoverElement);
      var styleOptionsMenu = settingsWrapper.querySelector(':scope > ul');
      styleOptionsMenu.querySelectorAll(':scope > li').forEach(function (item) {
        item.querySelector('h3').addEventListener('mouseover', function (event) {
          hoverElement.style.transform = "translateY(".concat(event.target.closest('li').offsetTop, "px)");
          hoverElement.classList.add('frm-animating');
          hoverElement.classList.remove('frm_hidden');
          setTimeout(function () {
            hoverElement.classList.remove('frm-animating');
          }, 250);
        });
      });
      var accordionitems = document.querySelectorAll('#frm_style_sidebar .accordion-section h3');
      accordionitems.forEach(function (item) {
        item.addEventListener('click', function () {
          hoverElement.classList.add('frm_hidden');
        });
      });
    }

    /**
     * Initializes the copy to clipboard functionality for style classes.
     * Adds a click event listener to the copyLabel element.
     * Copies the class name to the clipboard and displays a success message.
     * @param {string} successMessage - The success message to display.
     */
  }, {
    key: "initStyleClassCopyToClipboard",
    value: function initStyleClassCopyToClipboard(successMessage) {
      var _this = this;
      var copyLabel = document.querySelector('.frm-copy-text');
      copyLabel.addEventListener('click', function (event) {
        var className = event.currentTarget.innerText;
        navigator.clipboard.writeText(className).then(function () {
          _this.success(successMessage);
        });
      });
    }
  }]);
}();
new frmStyleOptions();
}();
/******/ })()
;
//# sourceMappingURL=formidable_styles.js.map