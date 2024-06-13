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

/***/ "./node_modules/@wordpress/dom-ready/build-module/index.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@wordpress/dom-ready/build-module/index.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ domReady; }
/* harmony export */ });
/**
 * @typedef {() => void} Callback
 *
 * TODO: Remove this typedef and inline `() => void` type.
 *
 * This typedef is used so that a descriptive type is provided in our
 * automatically generated documentation.
 *
 * An in-line type `() => void` would be preferable, but the generated
 * documentation is `null` in that case.
 *
 * @see https://github.com/WordPress/gutenberg/issues/18045
 */

/**
 * Specify a function to execute when the DOM is fully loaded.
 *
 * @param {Callback} callback A function to execute after the DOM is ready.
 *
 * @example
 * ```js
 * import domReady from '@wordpress/dom-ready';
 *
 * domReady( function() {
 * 	//do something after DOM loads.
 * } );
 * ```
 *
 * @return {void}
 */
function domReady(callback) {
  if (typeof document === 'undefined') {
    return;
  }

  if (document.readyState === 'complete' || // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.
  document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.
  ) {
      return void callback();
    } // DOMContentLoaded has not fired yet, delay callback until then.


  document.addEventListener('DOMContentLoaded', callback);
}
//# sourceMappingURL=index.js.map

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

/***/ "./js/src/common/constants.js":
/*!************************************!*\
  !*** ./js/src/common/constants.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CHECKED_CLASS: function() { return /* binding */ CHECKED_CLASS; },
/* harmony export */   HIDDEN_CLASS: function() { return /* binding */ HIDDEN_CLASS; }
/* harmony export */ });
var HIDDEN_CLASS = 'frm_hidden';
var CHECKED_CLASS = 'frm-checked';

/***/ }),

/***/ "./js/src/common/utilities/index.js":
/*!******************************************!*\
  !*** ./js/src/common/utilities/index.js ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: function() { return /* reexport safe */ _requestQueue__WEBPACK_IMPORTED_MODULE_1__.addToRequestQueue; },
/* harmony export */   getQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.getQueryParam; },
/* harmony export */   hasQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.hasQueryParam; },
/* harmony export */   hide: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.hide; },
/* harmony export */   hideElements: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.hideElements; },
/* harmony export */   isEmptyObject: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_2__.isEmptyObject; },
/* harmony export */   isHTMLElement: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_2__.isHTMLElement; },
/* harmony export */   isValidEmail: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_2__.isValidEmail; },
/* harmony export */   isVisible: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.isVisible; },
/* harmony export */   removeQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.removeQueryParam; },
/* harmony export */   setQueryParam: function() { return /* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.setQueryParam; },
/* harmony export */   show: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.show; },
/* harmony export */   showElements: function() { return /* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.showElements; },
/* harmony export */   showFormError: function() { return /* reexport safe */ _uiUtils__WEBPACK_IMPORTED_MODULE_3__.showFormError; }
/* harmony export */ });
/* harmony import */ var _visibility__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./visibility */ "./js/src/common/utilities/visibility.js");
/* harmony import */ var _requestQueue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./requestQueue */ "./js/src/common/utilities/requestQueue.js");
/* harmony import */ var _validation__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./validation */ "./js/src/common/utilities/validation.js");
/* harmony import */ var _uiUtils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./uiUtils */ "./js/src/common/utilities/uiUtils.js");
/* harmony import */ var _url__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./url */ "./js/src/common/utilities/url.js");






/***/ }),

/***/ "./js/src/common/utilities/requestQueue.js":
/*!*************************************************!*\
  !*** ./js/src/common/utilities/requestQueue.js ***!
  \*************************************************/
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

/***/ "./js/src/common/utilities/uiUtils.js":
/*!********************************************!*\
  !*** ./js/src/common/utilities/uiUtils.js ***!
  \********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showFormError: function() { return /* binding */ showFormError; }
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/common/utilities/index.js");
/**
 * Internal dependencies
 */


/**
 * Displays form validation error messages.
 *
 * @param {string} inputId The ID selector for the input field with the error.
 * @param {string} errorId The ID selector for the error message display element.
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
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

/***/ "./js/src/common/utilities/url.js":
/*!****************************************!*\
  !*** ./js/src/common/utilities/url.js ***!
  \****************************************/
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
 * @param {string} paramName The name of the query parameter to set.
 * @param {string} paramValue The value to set for the query parameter.
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

/***/ "./js/src/common/utilities/validation.js":
/*!***********************************************!*\
  !*** ./js/src/common/utilities/validation.js ***!
  \***********************************************/
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

/***/ "./js/src/common/utilities/visibility.js":
/*!***********************************************!*\
  !*** ./js/src/common/utilities/visibility.js ***!
  \***********************************************/
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
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../constants */ "./js/src/common/constants.js");


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
  return element === null || element === void 0 ? void 0 : element.classList.remove(_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
};

/**
 * Adds the hidden class to hide the element.
 *
 * @param {Element} element The element to hide.
 * @return {void}
 */
var hide = function hide(element) {
  return element === null || element === void 0 ? void 0 : element.classList.add(_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
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

/***/ "./js/src/core/factory/createPageElements.js":
/*!***************************************************!*\
  !*** ./js/src/core/factory/createPageElements.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageElements: function() { return /* binding */ createPageElements; }
/* harmony export */ });
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/**
 * Creates a page elements manager.
 *
 * @param {Object} [initialElements={}] An object containing initial DOM elements.
 * @throws {Error} Throws an error if the `initialElements` is not an object.
 * @return {Object} An object with methods to get and add elements.
 */
function createPageElements() {
  var initialElements = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var elements = null;

  /**
   * Initializes the page elements with the provided initial elements.
   *
   * @throws {Error} Throws an error if `initialElements` is not a plain object.
   * @return {void}
   */
  var initializePageElements = function initializePageElements() {
    if (_typeof(initialElements) !== 'object' || initialElements === null) {
      throw new Error('initializePageElements: initialElements must be a non-null object');
    }
    elements = initialElements;
  };

  /**
   * Retrieve the initialized essential DOM elements.
   *
   * @return {Object} The initialized elements object.
   */
  function getElements() {
    return elements;
  }

  /**
   * Add new elements to the elements object.
   *
   * @param {Object} newElements An object containing new elements to be added.
   * @throws {Error} Throws an error if the `newElements` is not a non-null object.
   * @return {void} Updates the elements object by merging the new elements into it.
   */
  function addElements(newElements) {
    if (_typeof(newElements) !== 'object' || newElements === null) {
      throw new Error('addElements: newElements must be a non-null object');
    }
    elements = _objectSpread(_objectSpread({}, elements), newElements);
  }
  return {
    initializePageElements: initializePageElements,
    getElements: getElements,
    addElements: addElements
  };
}

/***/ }),

/***/ "./js/src/core/factory/createPageState.js":
/*!************************************************!*\
  !*** ./js/src/core/factory/createPageState.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageState: function() { return /* binding */ createPageState; }
/* harmony export */ });
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/**
 * Creates a page state manager.
 *
 * @param {Object} [initialState={}] An object containing the initial state.
 * @throws {Error} Throws an error if the `initialState` is not a plain object.
 * @return {Object} An object with methods to initialize, get, and set the page state.
 */
function createPageState() {
  var initialState = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var state = null;

  /**
   * Initializes the page state with the provided initial state.
   *
   * @throws {Error} Throws an error if `initialState` is not a plain object.
   * @return {void}
   */
  var initializePageState = function initializePageState() {
    if (_typeof(initialState) !== 'object' || initialState === null) {
      throw new Error('initializePageState: initialState must be a non-null object');
    }
    state = initialState;
  };

  /**
   * Returns the current page state.
   *
   * @return {Object|null} The current state of the page or null if not initialized.
   */
  var getState = function getState() {
    return state;
  };

  /**
   * Returns a specific property from the current page state.
   *
   * @param {string} propertyName The name of the property to retrieve.
   * @return {*} The value of the specified property, or null if it doesn't exist.
   */
  var getSingleState = function getSingleState(propertyName) {
    var _Reflect$get;
    return (_Reflect$get = Reflect.get(state, propertyName)) !== null && _Reflect$get !== void 0 ? _Reflect$get : null;
  };

  /**
   * Updates the page state with new values.
   *
   * @param {Object} newState The new values to update the state with.
   * @throws {Error} Throws an error if `newState` is not a plain object.
   * @return {void}
   */
  var setState = function setState(newState) {
    if (_typeof(newState) !== 'object' || newState === null) {
      throw new Error('setState: newState must be a non-null object');
    }
    state = _objectSpread(_objectSpread({}, state), newState);
  };

  /**
   * Updates a specific property in the page state with a new value.
   *
   * @param {string} propertyName The name of the property to update.
   * @param {*}      value        The new value to set for the property.
   * @return {void}
   */
  var setSingleState = function setSingleState(propertyName, value) {
    if (Reflect.has(state, propertyName)) {
      Reflect.set(state, propertyName, value);
    }
  };
  return {
    initializePageState: initializePageState,
    getState: getState,
    getSingleState: getSingleState,
    setState: setState,
    setSingleState: setSingleState
  };
}

/***/ }),

/***/ "./js/src/core/factory/index.js":
/*!**************************************!*\
  !*** ./js/src/core/factory/index.js ***!
  \**************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageElements: function() { return /* reexport safe */ _createPageElements__WEBPACK_IMPORTED_MODULE_0__.createPageElements; },
/* harmony export */   createPageState: function() { return /* reexport safe */ _createPageState__WEBPACK_IMPORTED_MODULE_1__.createPageState; }
/* harmony export */ });
/* harmony import */ var _createPageElements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./createPageElements */ "./js/src/core/factory/createPageElements.js");
/* harmony import */ var _createPageState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./createPageState */ "./js/src/core/factory/createPageState.js");



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

/***/ "./js/src/core/utils/event.js":
/*!************************************!*\
  !*** ./js/src/core/utils/event.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dispatchCustomEvent: function() { return /* binding */ dispatchCustomEvent; },
/* harmony export */   onClickPreventDefault: function() { return /* binding */ onClickPreventDefault; }
/* harmony export */ });
var onClickPreventDefault = window.frmDom.util.onClickPreventDefault;

/**
 * Dispatches a custom event with the given name and detail.
 *
 * @param {string} eventName The name of the custom event.
 * @param {Object} detail    The detail object to pass with the event.
 * @return {void}
 */

var dispatchCustomEvent = function dispatchCustomEvent(eventName, detail) {
  document.dispatchEvent(new CustomEvent(eventName, {
    detail: detail
  }));
};

/***/ }),

/***/ "./js/src/core/utils/index.js":
/*!************************************!*\
  !*** ./js/src/core/utils/index.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dispatchCustomEvent: function() { return /* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_0__.dispatchCustomEvent; },
/* harmony export */   frmAnimate: function() { return /* reexport safe */ _animation__WEBPACK_IMPORTED_MODULE_1__.frmAnimate; },
/* harmony export */   onClickPreventDefault: function() { return /* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault; }
/* harmony export */ });
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./event */ "./js/src/core/utils/event.js");
/* harmony import */ var _animation__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./animation */ "./js/src/core/utils/animation.js");



/***/ }),

/***/ "./js/src/form-templates/elements/applicationTemplatesElement.js":
/*!***********************************************************************!*\
  !*** ./js/src/form-templates/elements/applicationTemplatesElement.js ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplatesElement: function() { return /* binding */ addApplicationTemplatesElement; },
/* harmony export */   createApplicationTemplates: function() { return /* binding */ createApplicationTemplates; }
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./elements */ "./js/src/form-templates/elements/elements.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


var _window$frmDom = window.frmDom,
  tag = _window$frmDom.tag,
  div = _window$frmDom.div,
  span = _window$frmDom.span,
  a = _window$frmDom.a,
  img = _window$frmDom.img;

// Application templates element
var applicationTemplates;

// Base URL for the thumbnail images of applications
var thumbnailBaseURL = "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PLUGIN_URL, "/images/applications/thumbnails");

/**
 * Create and return the application templates HTML element.
 *
 * @param {Object[]} applications Array of application objects.
 * @return {HTMLElement|null} The application templates element or null if no applications are provided.
 */
function createApplicationTemplates(applications) {
  if (!applications || !applications.length) {
    return;
  }
  var templateItems = applications.map(function (template) {
    return createTemplateItem(template);
  });
  applicationTemplates = div({
    id: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-applications"),
    className: _shared__WEBPACK_IMPORTED_MODULE_1__.HIDDEN_CLASS,
    children: [tag('h2', {
      text: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Application Templates'),
      className: 'frm-text-sm frm-mb-sm'
    }), tag('ul', {
      className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-list ").concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-grid-layout"),
      children: templateItems
    })]
  });
}

/**
 * Create and return an individual item element for a application template.
 *
 * @private
 * @param {Object} template The application object.
 * @return {HTMLElement} Element representing a single application template.
 */
function createTemplateItem(template) {
  var name = template.name,
    key = template.key,
    hasLiteThumbnail = template.hasLiteThumbnail;
  var thumbnailURL = hasLiteThumbnail ? "".concat(thumbnailBaseURL, "/").concat(key, ".png") : "".concat(thumbnailBaseURL, "/placeholder.svg");
  return tag('li', {
    className: 'frm-card-item',
    data: {
      href: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.applicationsUrl, "&triggerViewApplicationModal=1&template=").concat(key),
      'frm-search-text': name.toLowerCase()
    },
    children: [div({
      className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-item-icon"),
      child: img({
        src: thumbnailURL
      })
    }), div({
      className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-item-body"),
      children: [span({
        text: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Ready Made Solution', 'formidable'),
        className: 'frm-meta-tag frm-orange-tag frm-text-xs'
      }), tag('h3', {
        text: name,
        className: 'frm-text-sm frm-font-medium frm-m-0'
      }), a({
        text: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('See all applications', 'formidable'),
        className: 'frm-text-xs frm-font-semibold',
        href: _shared__WEBPACK_IMPORTED_MODULE_1__.applicationsUrl
      })]
    })]
  });
}
;

/**
 * Inject application Templates elements into the DOM and the elements object.
 *
 * @return {void}
 */
function addApplicationTemplatesElement() {
  var elements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)();
  if (elements.applicationTemplates || undefined === applicationTemplates) {
    return;
  }
  elements.bodyContent.appendChild(applicationTemplates);
  (0,_elements__WEBPACK_IMPORTED_MODULE_2__.addElements)({
    applicationTemplates: applicationTemplates,
    applicationTemplatesTitle: applicationTemplates.querySelector('h2'),
    applicationTemplatesList: applicationTemplates.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-list")),
    applicationTemplateItems: applicationTemplates.querySelectorAll('.frm-card-item')
  });
}

/***/ }),

/***/ "./js/src/form-templates/elements/elements.js":
/*!****************************************************!*\
  !*** ./js/src/form-templates/elements/elements.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: function() { return /* binding */ addElements; },
/* harmony export */   getElements: function() { return /* binding */ getElements; },
/* harmony export */   initializeElements: function() { return /* binding */ initializeElements; }
/* harmony export */ });
/* harmony import */ var _emptyStateElement__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./emptyStateElement */ "./js/src/form-templates/elements/emptyStateElement.js");
/* harmony import */ var _getDOMElements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./getDOMElements */ "./js/src/form-templates/elements/getDOMElements.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Internal dependencies
 */


var elements = null;

/**
 * Initialize the elements.
 *
 * @return {void}
 */
function initializeElements() {
  elements = (0,_getDOMElements__WEBPACK_IMPORTED_MODULE_1__["default"])();
  addEmptyStateElements();
  addBodyContentChildren();
}

/**
 * Retrieve the initialized essential DOM elements.
 *
 * @return {Object|null} The initialized elements object or null.
 */
function getElements() {
  return elements;
}

/**
 * Add new elements to the elements object.
 *
 * @param {Object} newElements An object containing new elements to be added.
 * @return {void} Updates the global `elements` object by merging the new elements into it.
 */
function addElements(newElements) {
  elements = _objectSpread(_objectSpread({}, elements), newElements);
}

/**
 * Inject empty state elements into the DOM and the elements object.
 *
 * @private
 * @return {void}
 */
function addEmptyStateElements() {
  var _elements$bodyContent;
  if (elements.emptyState) {
    return;
  }
  var emptyState = (0,_emptyStateElement__WEBPACK_IMPORTED_MODULE_0__.createEmptyStateElement)();
  (_elements$bodyContent = elements.bodyContent) === null || _elements$bodyContent === void 0 || _elements$bodyContent.appendChild(emptyState);
  var emptyStateElements = (0,_emptyStateElement__WEBPACK_IMPORTED_MODULE_0__.getEmptyStateElements)();
  elements = _objectSpread(_objectSpread({}, elements), emptyStateElements);
}

/**
 * Add children of the bodyContent to the elements object.
 *
 * @private
 * @return {void}
 */
function addBodyContentChildren() {
  var _elements$bodyContent2;
  var bodyContentChildren = (_elements$bodyContent2 = elements.bodyContent) === null || _elements$bodyContent2 === void 0 ? void 0 : _elements$bodyContent2.children;
  elements = _objectSpread(_objectSpread({}, elements), {}, {
    bodyContentChildren: bodyContentChildren
  });
}

/***/ }),

/***/ "./js/src/form-templates/elements/emptyStateElement.js":
/*!*************************************************************!*\
  !*** ./js/src/form-templates/elements/emptyStateElement.js ***!
  \*************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createEmptyStateElement: function() { return /* binding */ createEmptyStateElement; },
/* harmony export */   getEmptyStateElements: function() { return /* binding */ getEmptyStateElements; }
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */

var _window$frmDom = window.frmDom,
  tag = _window$frmDom.tag,
  div = _window$frmDom.div,
  a = _window$frmDom.a,
  img = _window$frmDom.img;

/**
 * Create and return the Empty State HTML element.
 *
 * @return {HTMLElement} The Empty State element.
 */
function createEmptyStateElement() {
  var button = a({
    className: 'button button-primary frm-button-primary'
  });
  button.setAttribute('role', 'button');
  return div({
    id: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-empty-state"),
    className: "frm-flex-col frm-flex-center frm-gap-md ".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.HIDDEN_CLASS),
    children: [img({
      src: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PLUGIN_URL, "/images/form-templates/empty-state.svg"),
      alt: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Empty State', 'formidable')
    }), div({
      className: 'frmcenter',
      children: [tag('h2', {
        className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-title frm-mb-0")
      }), tag('p', {
        className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-text frm-mb-0")
      })]
    }), button]
  });
}

/**
 * Return the elements related to the Empty State.
 *
 * @return {Object} Object containing Empty State related DOM elements.
 */
function getEmptyStateElements() {
  var emptyState = document.querySelector("#".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-empty-state"));
  return {
    emptyState: emptyState,
    emptyStateTitle: emptyState === null || emptyState === void 0 ? void 0 : emptyState.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-title")),
    emptyStateText: emptyState === null || emptyState === void 0 ? void 0 : emptyState.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-text")),
    emptyStateButton: emptyState === null || emptyState === void 0 ? void 0 : emptyState.querySelector('.button')
  };
}

/***/ }),

/***/ "./js/src/form-templates/elements/getDOMElements.js":
/*!**********************************************************!*\
  !*** ./js/src/form-templates/elements/getDOMElements.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Internal dependencies
 */


/**
 * Return essential DOM elements.
 *
 * @return {Object} The DOM elements queried and constructed into an object.
 */
function getDOMElements() {
  // Body Elements
  var bodyContent = document.getElementById('post-body-content');
  var bodyElements = {
    bodyContent: bodyContent,
    headerCancelButton: document.getElementById('frm-publishing').querySelector('a'),
    createFormButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-create-form")),
    pageTitle: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-page-title")),
    pageTitleText: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-page-title-text")),
    pageTitleDivider: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-page-title-divider")),
    upsellBanner: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-upsell-banner")),
    extraTemplateCountElements: document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-extra-templates-count"))
  };

  // Templates Elements
  var templatesList = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-list"));
  var templates = {
    templatesList: templatesList,
    templateItems: templatesList.querySelectorAll('.frm-card-item'),
    availableTemplateItems: templatesList.querySelectorAll(".frm-card-item:not(.".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-locked-item)")),
    freeTemplateItems: templatesList.querySelectorAll('.frm-card-item.frm-free-template'),
    twinFeaturedTemplateItems: templatesList.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-featured-item")),
    firstLockedFreeTemplate: templatesList.querySelector('.frm-free-template'),
    featuredTemplatesList: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-featured-list"))
  };

  // Custom Templates Section Element
  var customTemplatesSection = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-custom-list-section"));
  var customTemplates = {
    customTemplatesSection: customTemplatesSection,
    customTemplateItems: customTemplatesSection.querySelectorAll('.frm-card-item'),
    customTemplatesTitle: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-custom-list-title")),
    customTemplatesList: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-custom-list"))
  };

  // Sidebar Elements
  var sidebar = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-sidebar"));
  var favoritesCategory = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-item[data-category=\"").concat(_shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.FAVORITES, "\"]"));
  var sidebarElements = {
    sidebar: sidebar,
    favoritesCategory: favoritesCategory,
    favoritesCategoryCountEl: favoritesCategory.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-count")),
    searchInput: document.getElementById('template-search-input'),
    allTemplatesCategory: document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-item[data-category=\"").concat(_shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.ALL_TEMPLATES, "\"]")),
    availableTemplatesCategory: document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-item[data-category=\"").concat(_shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.AVAILABLE_TEMPLATES, "\"]")),
    freeTemplatesCategory: document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-item[data-category=\"").concat(_shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.FREE_TEMPLATES, "\"]"))
  };

  // Modal Elements
  var modal = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-modal"));
  var modalElements = {
    modal: modal,
    modalItems: modal === null || modal === void 0 ? void 0 : modal.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-modal-item")),
    // Create New Template Modal
    showCreateTemplateModalButton: document.getElementById('frm-show-create-template-modal'),
    createTemplateModal: document.getElementById('frm-create-template-modal'),
    createTemplateFormsDropdown: document.getElementById('frm-create-template-modal-forms-select'),
    createTemplateName: document.getElementById('frm_create_template_name'),
    createTemplateDescription: document.getElementById('frm_create_template_description'),
    createTemplateButton: document.getElementById('frm-create-template-button'),
    // Renew Account Modal
    renewAccountModal: document.getElementById('frm-renew-modal'),
    // Leave Email Modal
    leaveEmailModal: document.getElementById('frm-leave-email-modal'),
    leaveEmailModalInput: document.getElementById('frm_leave_email'),
    leaveEmailModalApiEmailForm: document.getElementById('frmapi-email-form'),
    leaveEmailModalGetCodeButton: document.getElementById('frm-get-code-button'),
    // Code from Email Modal
    codeFromEmailModal: document.getElementById('frm-code-from-email-modal'),
    codeFromEmailModalInput: document.getElementById('frm_code_from_email'),
    // Upgrade Modal
    upgradeModal: document.getElementById('frm-form-upgrade-modal'),
    upgradeModalTemplateNames: modal === null || modal === void 0 ? void 0 : modal.querySelectorAll('.frm-upgrade-modal-template-name'),
    upgradeModalPlansIcons: modal === null || modal === void 0 ? void 0 : modal.querySelectorAll('.frm-upgrade-modal-plan-icon'),
    upgradeModalLink: document.getElementById('frm-upgrade-modal-link')
  };

  // New Template Form Elements
  var newTemplateForm = document.getElementById('frm-new-template');
  var newTemplateFormElements = {
    newTemplateForm: newTemplateForm,
    newTemplateNameInput: document.getElementById('frm_template_name'),
    newTemplateDescriptionInput: document.getElementById('frm_template_desc'),
    newTemplateLinkInput: document.getElementById('frm_link'),
    newTemplateActionInput: document.getElementById('frm_action_type')
  };
  return _objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread({}, bodyElements), templates), customTemplates), sidebarElements), modalElements), newTemplateFormElements);
}
/* harmony default export */ __webpack_exports__["default"] = (getDOMElements);

/***/ }),

/***/ "./js/src/form-templates/elements/index.js":
/*!*************************************************!*\
  !*** ./js/src/form-templates/elements/index.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplatesElement: function() { return /* reexport safe */ _applicationTemplatesElement__WEBPACK_IMPORTED_MODULE_1__.addApplicationTemplatesElement; },
/* harmony export */   addElements: function() { return /* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.addElements; },
/* harmony export */   createApplicationTemplates: function() { return /* reexport safe */ _applicationTemplatesElement__WEBPACK_IMPORTED_MODULE_1__.createApplicationTemplates; },
/* harmony export */   getElements: function() { return /* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.getElements; },
/* harmony export */   initializeElements: function() { return /* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.initializeElements; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./elements */ "./js/src/form-templates/elements/elements.js");
/* harmony import */ var _applicationTemplatesElement__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./applicationTemplatesElement */ "./js/src/form-templates/elements/applicationTemplatesElement.js");



/***/ }),

/***/ "./js/src/form-templates/events/applicationTemplateListener.js":
/*!*********************************************************************!*\
  !*** ./js/src/form-templates/events/applicationTemplateListener.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplateEvents: function() { return /* binding */ addApplicationTemplateEvents; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/**
 * Internal dependencies
*/


/**
 * Manages event handling for an application template.
 *
 * @return {void}
 */
function addApplicationTemplateEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    applicationTemplateItems = _getElements.applicationTemplateItems;
  if (undefined === applicationTemplateItems) {
    return;
  }

  // Attach click event listener
  applicationTemplateItems.forEach(function (template) {
    template.addEventListener('click', onApplicationTemplateClick);
  });
}

/**
 * Handles the click event on an application template.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onApplicationTemplateClick = function onApplicationTemplateClick(event) {
  // Check if the clicked element is an anchor tag
  if (event.target.closest('a')) {
    return;
  }
  var applicationTemplate = event.currentTarget;
  window.location.href = applicationTemplate.dataset.href;
};

/***/ }),

/***/ "./js/src/form-templates/events/categoryListener.js":
/*!**********************************************************!*\
  !*** ./js/src/form-templates/events/categoryListener.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/events/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */





/**
 * Manages event handling for sidebar category links.
 *
 * @return {void}
 */
function addCategoryEvents() {
  var categoryItems = document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-cat-item"));

  // Attach click and keyboard event listeners to each sidebar category
  categoryItems.forEach(function (category) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(category, onCategoryClick);
    category.addEventListener('keydown', onCategoryKeydown);
  });
}

/**
 * Handles the click event on a category item.
 *
 * @private
 * @param {Event} event The click event object.
 */
var onCategoryClick = function onCategoryClick(event) {
  var clickedCategory = event.currentTarget;
  var newSelectedCategory = clickedCategory.getAttribute('data-category');
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    selectedCategory = _getState.selectedCategory,
    selectedCategoryEl = _getState.selectedCategoryEl,
    notEmptySearchText = _getState.notEmptySearchText;

  // If the selected category hasn't changed, return early
  if (selectedCategory === newSelectedCategory) {
    return;
  }

  /**
   * Filter hook to modify the selected category.
   *
   * @param {string} selectedCategory The selected category.
   */
  selectedCategory = wp.hooks.applyFilters('frmFormTemplates.selectedCategory', newSelectedCategory);

  // Highlight the newly clicked category and update the application state
  selectedCategoryEl.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_2__.CURRENT_CLASS);
  selectedCategoryEl = clickedCategory;
  selectedCategoryEl.classList.add(_shared__WEBPACK_IMPORTED_MODULE_2__.CURRENT_CLASS);
  (0,_shared__WEBPACK_IMPORTED_MODULE_2__.setState)({
    selectedCategory: selectedCategory,
    selectedCategoryEl: selectedCategoryEl
  });

  // Reset the search input if it contains text
  if (notEmptySearchText) {
    (0,___WEBPACK_IMPORTED_MODULE_4__.resetSearchInput)();
  }

  // Display templates of the selected category
  (0,_ui__WEBPACK_IMPORTED_MODULE_3__.showSelectedCategory)(selectedCategory);

  // Smoothly display the updated UI elements
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    bodyContent = _getElements.bodyContent;
  var bodyContentAnimate = new core_utils__WEBPACK_IMPORTED_MODULE_0__.frmAnimate(bodyContent);
  bodyContentAnimate.fadeIn();
};

/**
 * Handles the keyboard event on a category item.
 *
 * @param {KeyboardEvent} event The keyboard event object.
 * @return {void}
 */
function onCategoryKeydown(event) {
  // Only respond to 'Enter' or 'Space' key presses
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault(); // Prevent default action
    onCategoryClick(event);
  }
}
/* harmony default export */ __webpack_exports__["default"] = (addCategoryEvents);

/***/ }),

/***/ "./js/src/form-templates/events/createFormButtonListener.js":
/*!******************************************************************!*\
  !*** ./js/src/form-templates/events/createFormButtonListener.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/**
 * WordPress dependencies
 */


/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


/**
 * Manages event handling for the "Create a blank form" button.
 *
 * @return {void}
 */
function addCreateFormButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    createFormButton = _getElements.createFormButton;

  // Attach click event listener
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.onClickPreventDefault)(createFormButton, onCreateFormButtonClick);
}

/**
 * Handles the click event on the "Create a blank form" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onCreateFormButtonClick = function onCreateFormButtonClick() {
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    createFormButton = _getElements2.createFormButton,
    newTemplateForm = _getElements2.newTemplateForm,
    newTemplateNameInput = _getElements2.newTemplateNameInput,
    newTemplateActionInput = _getElements2.newTemplateActionInput;
  var installNewForm = window.frmAdminBuild.installNewForm;
  newTemplateNameInput.value = '';
  newTemplateActionInput.value = 'frm_install_form';
  installNewForm(newTemplateForm, 'frm_install_form', createFormButton);
};
/* harmony default export */ __webpack_exports__["default"] = (addCreateFormButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/createTemplateListeners.js":
/*!*****************************************************************!*\
  !*** ./js/src/form-templates/events/createTemplateListeners.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/**
 * WordPress dependencies
 */


/**
 * External dependencies
 */


/**
 * Internal dependencies
*/





/**
 * Manages event handling for the 'Create New Template' modal.
 *
 * @return {void}
 */
function addCreateTemplateEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    createTemplateFormsDropdown = _getElements.createTemplateFormsDropdown,
    createTemplateButton = _getElements.createTemplateButton,
    showCreateTemplateModalButton = _getElements.showCreateTemplateModalButton,
    emptyStateButton = _getElements.emptyStateButton;

  // Show the 'Create New Template' modal when either empty state or show modal button is clicked
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.onClickPreventDefault)(showCreateTemplateModalButton, onShowCreateTemplateModalButtonClick);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.onClickPreventDefault)(emptyStateButton, onShowCreateTemplateModalButtonClick);

  // Handle changes in the forms selection dropdown for creating a new template
  createTemplateFormsDropdown.addEventListener('change', onFormsSelectChange);

  // Create a new template when the create button inside the modal is clicked
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.onClickPreventDefault)(createTemplateButton, onCreateTemplateButtonClick);
}

/**
 * Handles the click event on the 'Create Template' button, showing the 'Create New Template' modal.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onShowCreateTemplateModalButtonClick = function onShowCreateTemplateModalButtonClick() {
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
    selectedCategory = _getState.selectedCategory;
  if (!(0,_utils__WEBPACK_IMPORTED_MODULE_5__.isCustomCategory)(selectedCategory)) {
    return;
  }
  (0,_ui__WEBPACK_IMPORTED_MODULE_4__.showCreateTemplateModal)();
};

/**
 * Handles changes in the forms selection dropdown for creating a new template.
 *
 * @private
 * @param {Event} event The change event object.
 * @return {void}
 */
var onFormsSelectChange = function onFormsSelectChange() {
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    formsSelect = _getElements2.createTemplateFormsDropdown;
  var formId = formsSelect.value;
  if (!formId || formId === 'no-forms') {
    toggleDisableModalElements(true);
    return;
  }
  toggleDisableModalElements(false);
  var selectedOption = formsSelect.options[formsSelect.selectedIndex];
  var formDescription = selectedOption.dataset.description.trim();
  var formName = selectedOption.dataset.name.trim();
  var templateString = " ".concat((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Template', 'formidable'));
  if (!formName.endsWith(templateString)) {
    formName += templateString;
  }
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    createTemplateName = _getElements3.createTemplateName,
    createTemplateDescription = _getElements3.createTemplateDescription;
  createTemplateName.value = formName;
  createTemplateDescription.value = formDescription;
};

/**
 * Toggles the disabled state of elements in the 'Create Template' modal.
 *
 * @private
 * @param {boolean} shouldDisable True to disable, false to enable.
 * @return {void}
 */
var toggleDisableModalElements = function toggleDisableModalElements(shouldDisable) {
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    createTemplateName = _getElements4.createTemplateName,
    createTemplateDescription = _getElements4.createTemplateDescription,
    createTemplateButton = _getElements4.createTemplateButton;

  // Toggle the disabled attribute for input and textarea
  [createTemplateName, createTemplateDescription].forEach(function (element) {
    element.disabled = shouldDisable;
    if (shouldDisable) {
      element.value = ''; // Clear the content for input and textarea
    }
  });

  // Toggle the disabled class for the button
  createTemplateButton.classList.toggle('disabled', shouldDisable);
};

/**
 * Handles the click event on the 'Create Template' button to create a new template.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onCreateTemplateButtonClick = function onCreateTemplateButtonClick() {
  var installNewForm = window.frmAdminBuild.installNewForm;
  var actionName = 'frm_create_template';
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    newTemplateForm = _getElements5.newTemplateForm,
    newTemplateActionInput = _getElements5.newTemplateActionInput,
    newTemplateNameInput = _getElements5.newTemplateNameInput,
    newTemplateDescriptionInput = _getElements5.newTemplateDescriptionInput,
    newTemplateLinkInput = _getElements5.newTemplateLinkInput,
    createTemplateName = _getElements5.createTemplateName,
    createTemplateDescription = _getElements5.createTemplateDescription,
    createTemplateFormsDropdown = _getElements5.createTemplateFormsDropdown,
    createTemplateButton = _getElements5.createTemplateButton;
  newTemplateActionInput.value = actionName;
  newTemplateNameInput.value = createTemplateName.value.trim();
  newTemplateDescriptionInput.value = createTemplateDescription.value.trim();
  newTemplateLinkInput.value = createTemplateFormsDropdown.value;

  // Install new form template
  installNewForm(newTemplateForm, actionName, createTemplateButton);
};
/* harmony default export */ __webpack_exports__["default"] = (addCreateTemplateEvents);

/***/ }),

/***/ "./js/src/form-templates/events/emptyStateButtonListener.js":
/*!******************************************************************!*\
  !*** ./js/src/form-templates/events/emptyStateButtonListener.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/events/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */





/**
 * Manages event handling for the empty state button.
 *
 * @return {void}
 */
function addEmptyStateButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    emptyStateButton = _getElements.emptyStateButton;

  // Attach click event listener to the button
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(emptyStateButton, onEmptyStateButtonClick);
}

/**
 * Handles the click event on the empty state button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onEmptyStateButtonClick = function onEmptyStateButtonClick() {
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    selectedCategory = _getState.selectedCategory;
  if ((0,_utils__WEBPACK_IMPORTED_MODULE_3__.isCustomCategory)(selectedCategory)) {
    return;
  }

  // Set selectedCategory to '' as search state flag that triggers 'allTemplates' category if search input is empty
  // @see searchListener.js: handleSearchResult method
  (0,_shared__WEBPACK_IMPORTED_MODULE_2__.setSingleState)('selectedCategory', '');
  (0,___WEBPACK_IMPORTED_MODULE_4__.resetSearchInput)();
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    searchInput = _getElements2.searchInput;
  searchInput.focus();
};
/* harmony default export */ __webpack_exports__["default"] = (addEmptyStateButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/favoriteButtonListener.js":
/*!****************************************************************!*\
  !*** ./js/src/form-templates/events/favoriteButtonListener.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */




var FAVORITE_BUTTON_CLASS = ".".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-item-favorite-button");
var HEART_ICON_SELECTOR = "".concat(FAVORITE_BUTTON_CLASS, " use");
var FILLED_HEART_ICON = '#frm_heart_solid_icon';
var LINEAR_HEART_ICON = '#frm_heart_icon';
var OPERATION = {
  ADD: 'add',
  REMOVE: 'remove'
};

/**
 * Manages event handling for favorite buttons.
 *
 * @return {void}
 */
function addFavoriteButtonEvents() {
  var favoriteButtons = document.querySelectorAll(FAVORITE_BUTTON_CLASS);

  // Attach click event listeners to each favorite button
  favoriteButtons.forEach(function (favoriteButton) {
    return (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(favoriteButton, onFavoriteButtonClick);
  });
}

/**
 * Handles the click event on the add to favorite button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onFavoriteButtonClick = function onFavoriteButtonClick(event) {
  var _twinFeaturedTemplate;
  var favoriteButton = event.currentTarget;
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    templatesList = _getElements.templatesList,
    featuredTemplatesList = _getElements.featuredTemplatesList,
    favoritesCategoryCountEl = _getElements.favoritesCategoryCountEl,
    customTemplatesTitle = _getElements.customTemplatesTitle;

  /**
   * Get necessary template information
   */
  var template = favoriteButton.closest('.frm-card-item');
  var templateId = template.dataset.id;
  var isFavorited = (0,_utils__WEBPACK_IMPORTED_MODULE_4__.isFavoriteTemplate)(template);
  var isTemplateCustom = (0,_utils__WEBPACK_IMPORTED_MODULE_4__.isCustomTemplate)(template);
  var isTemplateFeatured = (0,_utils__WEBPACK_IMPORTED_MODULE_4__.isFeaturedTemplate)(template);

  /**
   * Toggle the favorite status in the UI.
   * If template is featured, toggle its twin version in the respective list.
   */
  var twinFeaturedTemplate = null;
  template.classList.toggle("".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-favorite-item"), !isFavorited);
  if (isTemplateFeatured) {
    var templateList = template.closest("#".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-list")) ? featuredTemplatesList : templatesList;
    if (templateList) {
      twinFeaturedTemplate = templateList.querySelector(".frm-card-item[data-id=\"".concat(templateId, "\"]"));
      // Toggle twin template's favorite status
      twinFeaturedTemplate.classList.toggle("".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-favorite-item"), !isFavorited);
    }
  }

  /**
   * Update favorite counts and icons based on the new state
   */
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    selectedCategory = _getState.selectedCategory,
    favoritesCount = _getState.favoritesCount;
  var currentOperation = isFavorited ? OPERATION.REMOVE : OPERATION.ADD;
  var heartIcon = template.querySelector(HEART_ICON_SELECTOR);
  var twinTemplateHeartIcon = (_twinFeaturedTemplate = twinFeaturedTemplate) === null || _twinFeaturedTemplate === void 0 ? void 0 : _twinFeaturedTemplate.querySelector(HEART_ICON_SELECTOR);
  if (OPERATION.ADD === currentOperation) {
    // Increment favorite counts
    ++favoritesCount.total;
    isTemplateCustom ? ++favoritesCount.custom : ++favoritesCount.default;
    // Set heart icon to filled
    heartIcon.setAttribute('xlink:href', FILLED_HEART_ICON);
    twinTemplateHeartIcon === null || twinTemplateHeartIcon === void 0 || twinTemplateHeartIcon.setAttribute('xlink:href', FILLED_HEART_ICON);
  } else {
    // Decrement favorite counts
    --favoritesCount.total;
    isTemplateCustom ? --favoritesCount.custom : --favoritesCount.default;
    // Set heart icon to outline
    heartIcon.setAttribute('xlink:href', LINEAR_HEART_ICON);
    twinTemplateHeartIcon === null || twinTemplateHeartIcon === void 0 || twinTemplateHeartIcon.setAttribute('xlink:href', LINEAR_HEART_ICON);
  }

  // Update UI and state to reflect new favorite counts
  favoritesCategoryCountEl.textContent = favoritesCount.total;
  (0,_shared__WEBPACK_IMPORTED_MODULE_2__.setSingleState)('favoritesCount', favoritesCount);

  /**
   * Hide UI elements if 'Favorites' is active and counts are zero.
   */
  if ((0,_utils__WEBPACK_IMPORTED_MODULE_4__.isFavoritesCategory)(selectedCategory)) {
    if (0 === favoritesCount.total) {
      (0,_ui__WEBPACK_IMPORTED_MODULE_3__.showFavoritesEmptyState)();
    }
    (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hide)(template);
    if (0 === favoritesCount.default) {
      (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hide)(templatesList);
    }
    if (0 === favoritesCount.custom || 0 === favoritesCount.default) {
      (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hide)(customTemplatesTitle);
    }
  }

  // Update server-side data for favorite templates
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.addToRequestQueue)(function () {
    return updateFavoriteTemplate(templateId, currentOperation, isTemplateCustom);
  });
};

/**
 * Update server-side data for favorite templates.
 *
 * @param {string}  id        The template ID.
 * @param {string}  operation The operation to perform ('add' or 'remove').
 * @param {boolean} isCustom  Flag indicating whether the template is custom.
 * @return {Promise<any>}
 */
function updateFavoriteTemplate(id, operation, isCustom) {
  var formData = new FormData();
  var doJsonPost = frmDom.ajax.doJsonPost;
  formData.append('template_id', id);
  formData.append('operation', operation);
  formData.append('is_custom_template', isCustom);
  return doJsonPost('add_or_remove_favorite_template', formData);
}
/* harmony default export */ __webpack_exports__["default"] = (addFavoriteButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/getCodeButtonListener.js":
/*!***************************************************************!*\
  !*** ./js/src/form-templates/events/getCodeButtonListener.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */




/**
 * Manages event handling for the "Get Code" button.
 *
 * @return {void}
 */
function addGetCodeButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    getCodeButton = _getElements.leaveEmailModalGetCodeButton;

  // Attach click event listener
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(getCodeButton, onGetCodeButtonClick);
}

/**
 * Handles the click event on the "Get Code" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onGetCodeButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var _doc$querySelector;
    var _getElements2, leaveEmailModalInput, email, _getElements3, leaveEmailModalHiddenForm, leaveEmailModalHiddenInput, formData, doc, response, text, parser, message;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), leaveEmailModalInput = _getElements2.leaveEmailModalInput;
          email = leaveEmailModalInput.value.trim(); // Check if the email field is empty
          if (email) {
            _context.next = 5;
            break;
          }
          (0,_ui__WEBPACK_IMPORTED_MODULE_2__.showEmailAddressError)('empty');
          return _context.abrupt("return");
        case 5:
          if ((0,_utils__WEBPACK_IMPORTED_MODULE_3__.isValidEmail)(email)) {
            _context.next = 8;
            break;
          }
          (0,_ui__WEBPACK_IMPORTED_MODULE_2__.showEmailAddressError)('invalid');
          return _context.abrupt("return");
        case 8:
          _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), leaveEmailModalHiddenForm = _getElements3.leaveEmailModalHiddenForm, leaveEmailModalHiddenInput = _getElements3.leaveEmailModalHiddenInput; // Check if the hidden form exists
          if (leaveEmailModalHiddenForm) {
            _context.next = 11;
            break;
          }
          return _context.abrupt("return");
        case 11:
          // Set the email value in the hidden input field
          leaveEmailModalHiddenInput.value = email;

          // Prepare FormData for the POST request
          formData = new FormData(leaveEmailModalHiddenForm);
          formData.append('action', 'frm_forms_preview');
          _context.prev = 14;
          _context.next = 17;
          return fetch(leaveEmailModalHiddenForm.getAttribute('action'), {
            method: 'POST',
            body: formData
          });
        case 17:
          response = _context.sent;
          _context.next = 20;
          return response.text();
        case 20:
          text = _context.sent;
          parser = new DOMParser();
          doc = parser.parseFromString(text, 'text/html');
          _context.next = 29;
          break;
        case 25:
          _context.prev = 25;
          _context.t0 = _context["catch"](14);
          console.error('An error occurred:', _context.t0);
          return _context.abrupt("return");
        case 29:
          // Extract and trim the message from the HTML response
          message = (_doc$querySelector = doc.querySelector('.frm_message')) === null || _doc$querySelector === void 0 ? void 0 : _doc$querySelector.textContent.trim(); // Check if the message indicates success ("Thanks!")
          if (message && message.indexOf('Thanks!') >= 0) {
            (0,_ui__WEBPACK_IMPORTED_MODULE_2__.showCodeFromEmailModal)();
          } else {
            // Show an error if the email is invalid
            (0,_ui__WEBPACK_IMPORTED_MODULE_2__.showEmailAddressError)('invalid');
          }
        case 31:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[14, 25]]);
  }));
  return function onGetCodeButtonClick() {
    return _ref.apply(this, arguments);
  };
}();
/* harmony default export */ __webpack_exports__["default"] = (addGetCodeButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/index.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/events/index.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplateEvents: function() { return /* reexport safe */ _applicationTemplateListener__WEBPACK_IMPORTED_MODULE_9__.addApplicationTemplateEvents; },
/* harmony export */   addEventListeners: function() { return /* binding */ addEventListeners; },
/* harmony export */   resetSearchInput: function() { return /* reexport safe */ _searchListener__WEBPACK_IMPORTED_MODULE_4__.resetSearchInput; }
/* harmony export */ });
/* harmony import */ var _categoryListener__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./categoryListener */ "./js/src/form-templates/events/categoryListener.js");
/* harmony import */ var _createFormButtonListener__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./createFormButtonListener */ "./js/src/form-templates/events/createFormButtonListener.js");
/* harmony import */ var _favoriteButtonListener__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./favoriteButtonListener */ "./js/src/form-templates/events/favoriteButtonListener.js");
/* harmony import */ var _useTemplateButtonListener__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./useTemplateButtonListener */ "./js/src/form-templates/events/useTemplateButtonListener.js");
/* harmony import */ var _searchListener__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./searchListener */ "./js/src/form-templates/events/searchListener.js");
/* harmony import */ var _emptyStateButtonListener__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./emptyStateButtonListener */ "./js/src/form-templates/events/emptyStateButtonListener.js");
/* harmony import */ var _createTemplateListeners__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./createTemplateListeners */ "./js/src/form-templates/events/createTemplateListeners.js");
/* harmony import */ var _getCodeButtonListener__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./getCodeButtonListener */ "./js/src/form-templates/events/getCodeButtonListener.js");
/* harmony import */ var _saveCodeButtonListener__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./saveCodeButtonListener */ "./js/src/form-templates/events/saveCodeButtonListener.js");
/* harmony import */ var _applicationTemplateListener__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./applicationTemplateListener */ "./js/src/form-templates/events/applicationTemplateListener.js");
/**
 * Internal dependencies
 */










/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
function addEventListeners() {
  (0,_categoryListener__WEBPACK_IMPORTED_MODULE_0__["default"])();
  (0,_createFormButtonListener__WEBPACK_IMPORTED_MODULE_1__["default"])();
  (0,_favoriteButtonListener__WEBPACK_IMPORTED_MODULE_2__["default"])();
  (0,_useTemplateButtonListener__WEBPACK_IMPORTED_MODULE_3__["default"])();
  (0,_searchListener__WEBPACK_IMPORTED_MODULE_4__["default"])();
  (0,_emptyStateButtonListener__WEBPACK_IMPORTED_MODULE_5__["default"])();
  (0,_createTemplateListeners__WEBPACK_IMPORTED_MODULE_6__["default"])();
  (0,_getCodeButtonListener__WEBPACK_IMPORTED_MODULE_7__["default"])();
  (0,_saveCodeButtonListener__WEBPACK_IMPORTED_MODULE_8__["default"])();
}



/***/ }),

/***/ "./js/src/form-templates/events/saveCodeButtonListener.js":
/*!****************************************************************!*\
  !*** ./js/src/form-templates/events/saveCodeButtonListener.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */





/**
 * Manages event handling for the "Save Code" button.
 *
 * @return {void}
 */
function addSaveCodeButtonEvents() {
  var saveCodeButton = document.getElementById('frm-confirm-email-address');
  var backButton = document.getElementById('frm-code-modal-back-button');
  var changeEmailButton = document.getElementById('frm-change-email-address');
  var resendCode = document.getElementById('frm-resend-code');

  // Attach click event to the "Save Code" button
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(saveCodeButton, onSaveCodeButtonClick);

  // Attach click events to the "Back" and "Change email address" buttons
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(backButton, onBackButton);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(changeEmailButton, onBackButton);

  // Attach click event to the "Resend code" button
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(resendCode, onResendCode);
}

/**
 * Handles the click event on the "Save Code" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onSaveCodeButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var _getElements, codeFromEmailModalInput, code, _getState, selectedTemplate, formData, data, response, _data, _ref2, errorMessage, errorType, useTemplateButton;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), codeFromEmailModalInput = _getElements.codeFromEmailModalInput;
          code = codeFromEmailModalInput.value.trim(); // Check if the code field is empty
          if (code) {
            _context.next = 5;
            break;
          }
          (0,_ui__WEBPACK_IMPORTED_MODULE_3__.showConfirmEmailAddressError)('empty');
          return _context.abrupt("return");
        case 5:
          _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(), selectedTemplate = _getState.selectedTemplate; // Prepare FormData for the POST request
          formData = new FormData();
          formData.append('action', 'template_api_signup');
          formData.append('nonce', _shared__WEBPACK_IMPORTED_MODULE_2__.nonce);
          formData.append('code', code);
          formData.append('key', selectedTemplate.dataset.key);
          _context.prev = 11;
          _context.next = 14;
          return fetch(ajaxurl, {
            method: 'POST',
            body: formData
          });
        case 14:
          response = _context.sent;
          _context.next = 17;
          return response.json();
        case 17:
          data = _context.sent;
          _context.next = 24;
          break;
        case 20:
          _context.prev = 20;
          _context.t0 = _context["catch"](11);
          console.error('An error occurred:', _context.t0);
          return _context.abrupt("return");
        case 24:
          if (data.success) {
            _context.next = 30;
            break;
          }
          _ref2 = ((_data = data) === null || _data === void 0 || (_data = _data.data) === null || _data === void 0 ? void 0 : _data[0]) || {}, errorMessage = _ref2.message;
          errorType = errorMessage ? 'custom' : 'invalid';
          (0,_ui__WEBPACK_IMPORTED_MODULE_3__.showConfirmEmailAddressError)(errorType, errorMessage);
          (0,_utils__WEBPACK_IMPORTED_MODULE_4__.show)(document.getElementById('frm_code_from_email_options'));
          return _context.abrupt("return");
        case 30:
          if (!(0,_utils__WEBPACK_IMPORTED_MODULE_4__.hasQueryParam)('free-templates')) {
            _context.next = 33;
            break;
          }
          window.location.href = (0,_utils__WEBPACK_IMPORTED_MODULE_4__.removeQueryParam)('free-templates');
          return _context.abrupt("return");
        case 33:
          if (!(!data.data || !data.data.url)) {
            _context.next = 35;
            break;
          }
          return _context.abrupt("return");
        case 35:
          // Remove the 'locked' status from the selected template
          selectedTemplate.classList.remove("".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-locked-item"));

          // Set the URL to the 'Use Template' button and trigger its click event
          useTemplateButton = selectedTemplate.querySelector('.frm-form-templates-use-template-button');
          useTemplateButton.setAttribute('href', data.data.url);
          useTemplateButton.dispatchEvent(new Event('click', {
            bubbles: true
          }));
        case 39:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[11, 20]]);
  }));
  return function onSaveCodeButtonClick() {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Handles the click event on the "Back" or "Change email address" buttons.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onBackButton = function onBackButton() {
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    leaveEmailModal = _getElements2.leaveEmailModal,
    codeFromEmailModal = _getElements2.codeFromEmailModal;
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hide)(codeFromEmailModal);
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.show)(leaveEmailModal);
};

/**
 * Handles the click event on the "Resend code" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onResendCode = function onResendCode() {
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    codeFromEmailModalInput = _getElements3.codeFromEmailModalInput,
    getCodeButton = _getElements3.leaveEmailModalGetCodeButton;
  codeFromEmailModalInput.value = '';
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hideElements)(document.querySelectorAll('#frm_code_from_email_options, #frm_code_from_email_error'));
  getCodeButton.dispatchEvent(new Event('click', {
    bubbles: true
  }));
};
/* harmony default export */ __webpack_exports__["default"] = (addSaveCodeButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/searchListener.js":
/*!********************************************************!*\
  !*** ./js/src/form-templates/events/searchListener.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   resetSearchInput: function() { return /* binding */ resetSearchInput; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/**
 * Internal dependencies
 */



var initSearch = window.frmDom.search.init;

/**
 * Adds search-related event listeners by calling the 'initSearch' function.
 *
 * @see frmDom.search method
 * @return {void}
 */
function addSearchEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    searchInput = _getElements.searchInput;
  initSearch(searchInput, 'frm-card-item', {
    handleSearchResult: handleSearchResult
  });
}

/**
 * Manages UI state based on search results and input value.
 *
 * @private
 * @param {Object}  args                    Contains flags for search status.
 * @param {boolean} args.foundSomething     True if search yielded results.
 * @param           event
 * @param {boolean} args.notEmptySearchText True if search input is not empty.
 * @return {void}
 */
function handleSearchResult(_ref, event) {
  var foundSomething = _ref.foundSomething,
    notEmptySearchText = _ref.notEmptySearchText;
  // Prevent double calls as window.frmDom.search.init attaches both 'input' and 'search' events,
  // triggering this method twice on 'x' button click.
  if (event && event.type === 'search' && event.target.value === '') {
    return;
  }
  var state = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.getState)();
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    allTemplatesCategory = _getElements2.allTemplatesCategory;
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.setSingleState)('notEmptySearchText', notEmptySearchText);

  // Revert to 'All Templates' if search and selected category are both empty
  if (!state.notEmptySearchText && !state.selectedCategory) {
    allTemplatesCategory.dispatchEvent(new Event('click', {
      bubbles: true
    }));
    return;
  }

  // Display search state if a category is selected
  if (state.selectedCategory) {
    (0,_ui__WEBPACK_IMPORTED_MODULE_2__.showSearchState)(notEmptySearchText);

    // Setting "selectedCategory" to an empty string as a flag for search state
    if (notEmptySearchText) {
      (0,_shared__WEBPACK_IMPORTED_MODULE_1__.setSingleState)('selectedCategory', '');
    }
  }
  (0,_ui__WEBPACK_IMPORTED_MODULE_2__.displaySearchElements)(foundSomething, notEmptySearchText);
}

/**
 * Resets the value of the search input and triggers an input event.
 *
 * @return {void}
 */
function resetSearchInput() {
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    searchInput = _getElements3.searchInput;
  searchInput.value = '';
  searchInput.dispatchEvent(new Event('input', {
    bubbles: true
  }));
}
/* harmony default export */ __webpack_exports__["default"] = (addSearchEvents);

/***/ }),

/***/ "./js/src/form-templates/events/useTemplateButtonListener.js":
/*!*******************************************************************!*\
  !*** ./js/src/form-templates/events/useTemplateButtonListener.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui/ */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/**
 * Internal dependencies
 */





/**
 * Manages event handling for use template buttons.
 *
 * @return {void}
 */
function addUseTemplateButtonEvents() {
  var useTemplateButtons = document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-use-template-button"));

  // Attach click event listeners to each use template button
  useTemplateButtons.forEach(function (useTemplateButton) {
    return useTemplateButton.addEventListener('click', onUseTemplateButtonClick);
  });
}

/**
 * Handles the click event on the use template button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onUseTemplateButtonClick = function onUseTemplateButtonClick(event) {
  var useTemplateButton = event.currentTarget;
  var template = useTemplateButton.closest('.frm-card-item');
  var isLocked = (0,_utils__WEBPACK_IMPORTED_MODULE_3__.isLockedTemplate)(template);
  var isTemplateCustom = (0,_utils__WEBPACK_IMPORTED_MODULE_3__.isCustomTemplate)(template);

  // Allow the default link behavior, if the template is custom and not locked
  if (!isLocked && isTemplateCustom) {
    return;
  }

  // Prevent the default link behavior for non-custom or locked templates
  event.preventDefault();

  // Update app state with selected template
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.setSingleState)('selectedTemplate', template);

  // Handle locked templates
  if (isLocked) {
    (0,_ui___WEBPACK_IMPORTED_MODULE_2__.showLockedTemplateModal)(template);
    return;
  }

  // Prepare for new template installation
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    newTemplateForm = _getElements.newTemplateForm,
    newTemplateNameInput = _getElements.newTemplateNameInput,
    newTemplateDescriptionInput = _getElements.newTemplateDescriptionInput,
    newTemplateLinkInput = _getElements.newTemplateLinkInput,
    newTemplateActionInput = _getElements.newTemplateActionInput;
  var installNewForm = window.frmAdminBuild.installNewForm;
  var templateName = template.querySelector('.frm-form-template-name').textContent.trim();
  var templateDescription = template.querySelector('.frm-form-templates-item-description').textContent.trim();
  var actionName = 'frm_install_template';
  newTemplateNameInput.value = templateName;
  newTemplateDescriptionInput.value = templateDescription;
  newTemplateActionInput.value = actionName;
  newTemplateLinkInput.value = useTemplateButton.href;

  // Install new form template
  installNewForm(newTemplateForm, actionName, useTemplateButton);
};
/* harmony default export */ __webpack_exports__["default"] = (addUseTemplateButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/initializeFormTemplates.js":
/*!**********************************************************!*\
  !*** ./js/src/form-templates/initializeFormTemplates.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./templates */ "./js/src/form-templates/templates/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./events */ "./js/src/form-templates/events/index.js");
/**
 * Internal dependencies
 */






/**
 * Initializes form templates.
 *
 * @return {void}
 */
function initializeFormTemplates() {
  (0,_elements__WEBPACK_IMPORTED_MODULE_0__.initializeElements)();
  (0,_shared__WEBPACK_IMPORTED_MODULE_1__.initializePageState)();
  (0,_templates__WEBPACK_IMPORTED_MODULE_3__.maybeAddApplicationTemplates)();
  (0,_ui__WEBPACK_IMPORTED_MODULE_2__.initializeModal)();

  // Generate a categorized list of templates
  (0,_templates__WEBPACK_IMPORTED_MODULE_3__.buildCategorizedTemplates)();

  // Set up the initial view, including any required DOM manipulations for proper presentation
  (0,_ui__WEBPACK_IMPORTED_MODULE_2__.setupInitialView)();
  (0,_events__WEBPACK_IMPORTED_MODULE_4__.addEventListeners)();
}
/* harmony default export */ __webpack_exports__["default"] = (initializeFormTemplates);

/***/ }),

/***/ "./js/src/form-templates/shared/constants.js":
/*!***************************************************!*\
  !*** ./js/src/form-templates/shared/constants.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CURRENT_CLASS: function() { return /* binding */ CURRENT_CLASS; },
/* harmony export */   FEATURED_TEMPLATES_KEYS: function() { return /* binding */ FEATURED_TEMPLATES_KEYS; },
/* harmony export */   HIDDEN_CLASS: function() { return /* binding */ HIDDEN_CLASS; },
/* harmony export */   HIDE_JS_CLASS: function() { return /* binding */ HIDE_JS_CLASS; },
/* harmony export */   MODAL_SIZES: function() { return /* binding */ MODAL_SIZES; },
/* harmony export */   PLANS: function() { return /* binding */ PLANS; },
/* harmony export */   PLUGIN_URL: function() { return /* binding */ PLUGIN_URL; },
/* harmony export */   PREFIX: function() { return /* binding */ PREFIX; },
/* harmony export */   VIEW_SLUGS: function() { return /* binding */ VIEW_SLUGS; },
/* harmony export */   applicationsUrl: function() { return /* binding */ applicationsUrl; },
/* harmony export */   canAccessApplicationDashboard: function() { return /* binding */ canAccessApplicationDashboard; },
/* harmony export */   nonce: function() { return /* binding */ nonce; },
/* harmony export */   upgradeLink: function() { return /* binding */ upgradeLink; }
/* harmony export */ });
var _window$frmGlobal = window.frmGlobal,
  PLUGIN_URL = _window$frmGlobal.url,
  nonce = _window$frmGlobal.nonce,
  canAccessApplicationDashboard = _window$frmGlobal.canAccessApplicationDashboard,
  applicationsUrl = _window$frmGlobal.applicationsUrl;

var _window$frmFormTempla = window.frmFormTemplatesVars,
  FEATURED_TEMPLATES_KEYS = _window$frmFormTempla.FEATURED_TEMPLATES_KEYS,
  upgradeLink = _window$frmFormTempla.upgradeLink;

var PREFIX = 'frm-form-templates';
var HIDDEN_CLASS = 'frm_hidden';
var HIDE_JS_CLASS = 'frm-hide-js';
var CURRENT_CLASS = 'frm-current';
var VIEW_SLUGS = {
  ALL_TEMPLATES: 'all-templates',
  AVAILABLE_TEMPLATES: 'available-templates',
  FREE_TEMPLATES: 'free-templates',
  FAVORITES: 'favorites',
  CUSTOM: 'custom',
  SEARCH: 'search'
};
var PLANS = {
  BASIC: 'basic',
  PLUS: 'plus',
  BUSINESS: 'business',
  ELITE: 'elite',
  RENEW: 'renew',
  FREE: 'free'
};
var MODAL_SIZES = {
  GENERAL: '440px',
  CREATE_TEMPLATE: '550px'
};

/***/ }),

/***/ "./js/src/form-templates/shared/index.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/shared/index.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CURRENT_CLASS: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS; },
/* harmony export */   FEATURED_TEMPLATES_KEYS: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.FEATURED_TEMPLATES_KEYS; },
/* harmony export */   HIDDEN_CLASS: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS; },
/* harmony export */   HIDE_JS_CLASS: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.HIDE_JS_CLASS; },
/* harmony export */   MODAL_SIZES: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.MODAL_SIZES; },
/* harmony export */   PLANS: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PLANS; },
/* harmony export */   PLUGIN_URL: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PLUGIN_URL; },
/* harmony export */   PREFIX: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PREFIX; },
/* harmony export */   VIEW_SLUGS: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS; },
/* harmony export */   applicationsUrl: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.applicationsUrl; },
/* harmony export */   canAccessApplicationDashboard: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.canAccessApplicationDashboard; },
/* harmony export */   getSingleState: function() { return /* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.getSingleState; },
/* harmony export */   getState: function() { return /* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.getState; },
/* harmony export */   initializePageState: function() { return /* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.initializePageState; },
/* harmony export */   nonce: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.nonce; },
/* harmony export */   setSingleState: function() { return /* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.setSingleState; },
/* harmony export */   setState: function() { return /* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.setState; },
/* harmony export */   upgradeLink: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.upgradeLink; }
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/form-templates/shared/constants.js");
/* harmony import */ var _pageState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pageState */ "./js/src/form-templates/shared/pageState.js");



/***/ }),

/***/ "./js/src/form-templates/shared/pageState.js":
/*!***************************************************!*\
  !*** ./js/src/form-templates/shared/pageState.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getSingleState: function() { return /* binding */ getSingleState; },
/* harmony export */   getState: function() { return /* binding */ getState; },
/* harmony export */   initializePageState: function() { return /* binding */ initializePageState; },
/* harmony export */   setSingleState: function() { return /* binding */ setSingleState; },
/* harmony export */   setState: function() { return /* binding */ setState; }
/* harmony export */ });
/* harmony import */ var core_factory__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/factory */ "./js/src/core/factory/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! . */ "./js/src/form-templates/shared/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


var _window$frmFormTempla = window.frmFormTemplatesVars,
  templatesCount = _window$frmFormTempla.templatesCount,
  favoritesCount = _window$frmFormTempla.favoritesCount,
  customCount = _window$frmFormTempla.customCount;
var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
  allTemplatesCategory = _getElements.allTemplatesCategory,
  availableTemplateItems = _getElements.availableTemplateItems,
  freeTemplateItems = _getElements.freeTemplateItems,
  firstLockedFreeTemplate = _getElements.firstLockedFreeTemplate;
var availableTemplatesCount = availableTemplateItems.length;
var _createPageState = (0,core_factory__WEBPACK_IMPORTED_MODULE_0__.createPageState)({
    selectedCategory: ___WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.ALL_TEMPLATES,
    selectedCategoryEl: allTemplatesCategory,
    selectedTemplate: firstLockedFreeTemplate,
    notEmptySearchText: false,
    favoritesCount: favoritesCount,
    customCount: Number(customCount),
    availableTemplatesCount: availableTemplatesCount,
    freeTemplatesCount: freeTemplateItems.length,
    extraTemplatesCount: templatesCount - availableTemplatesCount
  }),
  initializePageState = _createPageState.initializePageState,
  getState = _createPageState.getState,
  getSingleState = _createPageState.getSingleState,
  setState = _createPageState.setState,
  setSingleState = _createPageState.setSingleState;


/***/ }),

/***/ "./js/src/form-templates/templates/applicationTemplates.js":
/*!*****************************************************************!*\
  !*** ./js/src/form-templates/templates/applicationTemplates.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   maybeAddApplicationTemplates: function() { return /* binding */ maybeAddApplicationTemplates; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../events */ "./js/src/form-templates/events/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/**
 * Internal dependencies
 */




/**
 * Adds application templates if the user has dashboard access.
 *
 * @return {void}
 */
function maybeAddApplicationTemplates() {
  // Exit if the user doesn't have permission to see application dashboard
  if (!_shared__WEBPACK_IMPORTED_MODULE_2__.canAccessApplicationDashboard) {
    return;
  }
  var doJsonFetch = frmDom.ajax.doJsonFetch;
  doJsonFetch('get_applications_data&view=templates').then(setupApplicationTemplates);
}

/**
 * Sets up application templates by creating HTML elements, injecting them into the DOM,
 * and adding event handlers.
 *
 * @private
 * @param {Object} data The data object containing information for application templates.
 * @return {void}
 */
function setupApplicationTemplates(data) {
  // Create application templates
  (0,_elements__WEBPACK_IMPORTED_MODULE_0__.createApplicationTemplates)(data.templates);

  // Inject templates into the DOM
  (0,_elements__WEBPACK_IMPORTED_MODULE_0__.addApplicationTemplatesElement)();

  // Set up event handling
  (0,_events__WEBPACK_IMPORTED_MODULE_1__.addApplicationTemplateEvents)();
}

/***/ }),

/***/ "./js/src/form-templates/templates/categorizeTemplates.js":
/*!****************************************************************!*\
  !*** ./js/src/form-templates/templates/categorizeTemplates.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   buildCategorizedTemplates: function() { return /* binding */ buildCategorizedTemplates; },
/* harmony export */   categorizedTemplates: function() { return /* binding */ categorizedTemplates; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/**
 * Internal dependencies
 */

var categorizedTemplates = {};

/**
 * Builds a categorized list of templates.
 *
 * @return {void}
 */
function buildCategorizedTemplates() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    templateItems = _getElements.templateItems;
  templateItems.forEach(function (template) {
    // Extract and split the categories from data attribute
    var categories = template.getAttribute('data-categories').split(',');
    categories.forEach(function (category) {
      // Initialize the category array if not already done
      if (!categorizedTemplates[category]) {
        categorizedTemplates[category] = [];
      }

      // Add the template to the appropriate category
      categorizedTemplates[category].push(template);
    });
  });
}

/***/ }),

/***/ "./js/src/form-templates/templates/index.js":
/*!**************************************************!*\
  !*** ./js/src/form-templates/templates/index.js ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   buildCategorizedTemplates: function() { return /* reexport safe */ _categorizeTemplates__WEBPACK_IMPORTED_MODULE_0__.buildCategorizedTemplates; },
/* harmony export */   categorizedTemplates: function() { return /* reexport safe */ _categorizeTemplates__WEBPACK_IMPORTED_MODULE_0__.categorizedTemplates; },
/* harmony export */   maybeAddApplicationTemplates: function() { return /* reexport safe */ _applicationTemplates__WEBPACK_IMPORTED_MODULE_1__.maybeAddApplicationTemplates; }
/* harmony export */ });
/* harmony import */ var _categorizeTemplates__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./categorizeTemplates */ "./js/src/form-templates/templates/categorizeTemplates.js");
/* harmony import */ var _applicationTemplates__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./applicationTemplates */ "./js/src/form-templates/templates/applicationTemplates.js");



/***/ }),

/***/ "./js/src/form-templates/ui/index.js":
/*!*******************************************!*\
  !*** ./js/src/form-templates/ui/index.js ***!
  \*******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   displaySearchElements: function() { return /* reexport safe */ _searchState__WEBPACK_IMPORTED_MODULE_5__.displaySearchElements; },
/* harmony export */   getModalWidget: function() { return /* reexport safe */ _initializeModal__WEBPACK_IMPORTED_MODULE_1__.getModalWidget; },
/* harmony export */   initializeModal: function() { return /* reexport safe */ _initializeModal__WEBPACK_IMPORTED_MODULE_1__.initializeModal; },
/* harmony export */   setupInitialView: function() { return /* reexport safe */ _setupInitialView__WEBPACK_IMPORTED_MODULE_0__["default"]; },
/* harmony export */   showAllTemplates: function() { return /* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showAllTemplates; },
/* harmony export */   showAvailableTemplates: function() { return /* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showAvailableTemplates; },
/* harmony export */   showAvailableTemplatesEmptyState: function() { return /* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showAvailableTemplatesEmptyState; },
/* harmony export */   showCodeFromEmailModal: function() { return /* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showCodeFromEmailModal; },
/* harmony export */   showConfirmEmailAddressError: function() { return /* reexport safe */ _showError__WEBPACK_IMPORTED_MODULE_8__.showConfirmEmailAddressError; },
/* harmony export */   showCreateTemplateModal: function() { return /* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showCreateTemplateModal; },
/* harmony export */   showCustomTemplates: function() { return /* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showCustomTemplates; },
/* harmony export */   showCustomTemplatesEmptyState: function() { return /* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showCustomTemplatesEmptyState; },
/* harmony export */   showEmailAddressError: function() { return /* reexport safe */ _showError__WEBPACK_IMPORTED_MODULE_8__.showEmailAddressError; },
/* harmony export */   showFavoriteTemplates: function() { return /* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showFavoriteTemplates; },
/* harmony export */   showFavoritesEmptyState: function() { return /* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showFavoritesEmptyState; },
/* harmony export */   showFreeTemplates: function() { return /* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showFreeTemplates; },
/* harmony export */   showHeaderCancelButton: function() { return /* reexport safe */ _showHeaderCancelButton__WEBPACK_IMPORTED_MODULE_3__.showHeaderCancelButton; },
/* harmony export */   showLeaveEmailModal: function() { return /* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showLeaveEmailModal; },
/* harmony export */   showLockedTemplateModal: function() { return /* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showLockedTemplateModal; },
/* harmony export */   showRenewAccountModal: function() { return /* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showRenewAccountModal; },
/* harmony export */   showSearchEmptyState: function() { return /* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showSearchEmptyState; },
/* harmony export */   showSearchState: function() { return /* reexport safe */ _searchState__WEBPACK_IMPORTED_MODULE_5__.showSearchState; },
/* harmony export */   showSelectedCategory: function() { return /* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showSelectedCategory; },
/* harmony export */   showUpgradeModal: function() { return /* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showUpgradeModal; },
/* harmony export */   updatePageTitle: function() { return /* reexport safe */ _pageTitle__WEBPACK_IMPORTED_MODULE_2__.updatePageTitle; }
/* harmony export */ });
/* harmony import */ var _setupInitialView__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setupInitialView */ "./js/src/form-templates/ui/setupInitialView.js");
/* harmony import */ var _initializeModal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./initializeModal */ "./js/src/form-templates/ui/initializeModal.js");
/* harmony import */ var _pageTitle__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./pageTitle */ "./js/src/form-templates/ui/pageTitle.js");
/* harmony import */ var _showHeaderCancelButton__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./showHeaderCancelButton */ "./js/src/form-templates/ui/showHeaderCancelButton.js");
/* harmony import */ var _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./showSelectedCategory */ "./js/src/form-templates/ui/showSelectedCategory.js");
/* harmony import */ var _searchState__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./searchState */ "./js/src/form-templates/ui/searchState.js");
/* harmony import */ var _showEmptyState__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./showEmptyState */ "./js/src/form-templates/ui/showEmptyState.js");
/* harmony import */ var _showModal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./showModal */ "./js/src/form-templates/ui/showModal.js");
/* harmony import */ var _showError__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./showError */ "./js/src/form-templates/ui/showError.js");










/***/ }),

/***/ "./js/src/form-templates/ui/initializeModal.js":
/*!*****************************************************!*\
  !*** ./js/src/form-templates/ui/initializeModal.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getModalWidget: function() { return /* binding */ getModalWidget; },
/* harmony export */   initializeModal: function() { return /* binding */ initializeModal; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * Internal dependencies
 */




var modalWidget = null;

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
function initializeModal() {
  return _initializeModal.apply(this, arguments);
}

/**
 * Retrieve the modal widget.
 *
 * @return {Object|false} The modal widget or false.
 */
function _initializeModal() {
  _initializeModal = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var _window$frmAdminBuild, initModal, offsetModalY, _getElements, leaveEmailModal;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _window$frmAdminBuild = window.frmAdminBuild, initModal = _window$frmAdminBuild.initModal, offsetModalY = _window$frmAdminBuild.offsetModalY;
          modalWidget = initModal('#frm-form-templates-modal', _shared__WEBPACK_IMPORTED_MODULE_1__.MODAL_SIZES.GENERAL);

          // Set the vertical offset for the modal
          if (modalWidget) {
            offsetModalY(modalWidget, '103px');
          }

          // Show the email modal if the 'free-templates' query param is present
          if ((0,_utils__WEBPACK_IMPORTED_MODULE_2__.hasQueryParam)('free-templates')) {
            _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(), leaveEmailModal = _getElements.leaveEmailModal;
            if (leaveEmailModal) {
              (0,___WEBPACK_IMPORTED_MODULE_3__.showLeaveEmailModal)();
            }
          }

          // Maybe fetch and inject the API email form into the modal
          maybeFetchInjectForm();

          // Customize the confirm modal appearance: adjusting its width and vertical position
          wp.hooks.addAction('frmAdmin.beforeOpenConfirmModal', 'frmFormTemplates', function (options) {
            var confirmModal = options.$info;
            confirmModal.dialog('option', 'width', _shared__WEBPACK_IMPORTED_MODULE_1__.MODAL_SIZES.CREATE_TEMPLATE);
            offsetModalY(confirmModal, '103px');
          });
        case 6:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return _initializeModal.apply(this, arguments);
}
function getModalWidget() {
  return modalWidget;
}

/**
 * Maybe fetch and inject the API email form into the "Leave your email address" modal.
 *
 * If the "Leave your email address" modal is present for capturing the user's email
 * and sending a code to unlock free templates, this function may fetch and
 * inject the API email form.
 *
 * @private
 * @return {void}
 */
function maybeFetchInjectForm() {
  return _maybeFetchInjectForm.apply(this, arguments);
}
function _maybeFetchInjectForm() {
  _maybeFetchInjectForm = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
    var _getElements2, leaveEmailModalApiEmailForm, url, json, response, formHtml, regex, leaveEmailModalHiddenForm, leaveEmailModalHiddenInput;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(), leaveEmailModalApiEmailForm = _getElements2.leaveEmailModalApiEmailForm; // Check if the element is present
          if (leaveEmailModalApiEmailForm) {
            _context2.next = 3;
            break;
          }
          return _context2.abrupt("return");
        case 3:
          // Get the URL to fetch the form HTML from
          url = leaveEmailModalApiEmailForm.getAttribute('data-url');
          _context2.prev = 4;
          _context2.next = 7;
          return fetch(url);
        case 7:
          response = _context2.sent;
          _context2.next = 10;
          return response.json();
        case 10:
          json = _context2.sent;
          _context2.next = 17;
          break;
        case 13:
          _context2.prev = 13;
          _context2.t0 = _context2["catch"](4);
          console.error('An error occurred:', _context2.t0);
          return _context2.abrupt("return");
        case 17:
          if (json.renderedHtml) {
            _context2.next = 20;
            break;
          }
          console.warn('renderedHtml is not available.');
          return _context2.abrupt("return");
        case 20:
          formHtml = json.renderedHtml; // Remove unnecessary link tags from the HTML
          regex = /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi;
          formHtml = formHtml.replace(regex, '');

          // Inject form HTML into the email form container
          leaveEmailModalApiEmailForm.innerHTML = formHtml;

          // Add the fetched form and email input to the initialized elements list for later use
          leaveEmailModalHiddenForm = leaveEmailModalApiEmailForm.querySelector('form');
          leaveEmailModalHiddenInput = leaveEmailModalHiddenForm.querySelector('[type="email"]:not(.frm_verify)');
          (0,_elements__WEBPACK_IMPORTED_MODULE_0__.addElements)({
            leaveEmailModalHiddenForm: leaveEmailModalHiddenForm,
            leaveEmailModalHiddenInput: leaveEmailModalHiddenInput
          });
        case 27:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[4, 13]]);
  }));
  return _maybeFetchInjectForm.apply(this, arguments);
}

/***/ }),

/***/ "./js/src/form-templates/ui/pageTitle.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/ui/pageTitle.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   updatePageTitle: function() { return /* binding */ updatePageTitle; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/**
 * Internal dependencies
 */



/**
 * Sets the page title based on a given string or the currently selected category.
 *
 * @param {string} [title] Optional title to display.
 * @return {void}
 */
function updatePageTitle(title) {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    pageTitleText = _getElements.pageTitleText;
  var newTitle = title || (0,_shared__WEBPACK_IMPORTED_MODULE_1__.getSingleState)('selectedCategoryEl').querySelector('.frm-form-templates-cat-text').textContent;
  pageTitleText.textContent = newTitle;
}

/***/ }),

/***/ "./js/src/form-templates/ui/searchState.js":
/*!*************************************************!*\
  !*** ./js/src/form-templates/ui/searchState.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   displaySearchElements: function() { return /* binding */ displaySearchElements; },
/* harmony export */   showSearchState: function() { return /* binding */ showSearchState; }
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! . */ "./js/src/form-templates/ui/index.js");
/**
 * WordPress dependencies
 */


/**
 * External dependencies
 */


/**
 * Internal dependencies
 */





/**
 * Updates the UI to display the search state.
 *
 * @param {boolean} notEmptySearchText True if search input is not empty.
 * @return {void}
 */
function showSearchState(notEmptySearchText) {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    bodyContent = _getElements.bodyContent,
    bodyContentChildren = _getElements.bodyContentChildren,
    pageTitle = _getElements.pageTitle,
    templatesList = _getElements.templatesList,
    applicationTemplates = _getElements.applicationTemplates;
  var bodyContentAnimate = new core_utils__WEBPACK_IMPORTED_MODULE_1__.frmAnimate(bodyContent);

  // Remove highlighting from the currently selected category if the search text is not empty
  if (notEmptySearchText) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getSingleState)('selectedCategoryEl').classList.remove(_shared__WEBPACK_IMPORTED_MODULE_3__.CURRENT_CLASS);
  }

  // Hide non-relevant elements in the body content
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hideElements)(bodyContentChildren);

  // Update the page title and display relevant elements
  (0,___WEBPACK_IMPORTED_MODULE_5__.updatePageTitle)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Search Result', 'formidable'));
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.showElements)([pageTitle, templatesList, applicationTemplates]);

  // Smoothly display the updated UI elements
  bodyContentAnimate.fadeIn();
}
;

/**
 * Displays search results based on search outcome.
 *
 * @param {boolean} foundSomething True if search yielded results.
 * @return {void}
 */
function displaySearchElements(foundSomething) {
  // Show empty state if no templates found
  if (!foundSomething) {
    (0,___WEBPACK_IMPORTED_MODULE_5__.showSearchEmptyState)();
    return;
  }

  // Hide empty state if currently displayed
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    emptyState = _getElements2.emptyState;
  if ((0,_utils__WEBPACK_IMPORTED_MODULE_4__.isVisible)(emptyState)) {
    var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
      pageTitle = _getElements3.pageTitle;
    (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hide)(emptyState);
    (0,_utils__WEBPACK_IMPORTED_MODULE_4__.show)(pageTitle);
  }
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    templatesList = _getElements4.templatesList,
    applicationTemplates = _getElements4.applicationTemplates,
    applicationTemplatesTitle = _getElements4.applicationTemplatesTitle,
    applicationTemplatesList = _getElements4.applicationTemplatesList;
  (0,_utils__WEBPACK_IMPORTED_MODULE_4__.showElements)([templatesList, applicationTemplates, applicationTemplatesTitle]);
  if (templatesList.offsetHeight === 0) {
    (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hideElements)([templatesList, applicationTemplatesTitle]);
  }
  if ((applicationTemplatesList === null || applicationTemplatesList === void 0 ? void 0 : applicationTemplatesList.offsetHeight) === 0) {
    (0,_utils__WEBPACK_IMPORTED_MODULE_4__.hide)(applicationTemplates);
  }
}

/***/ }),

/***/ "./js/src/form-templates/ui/setupInitialView.js":
/*!******************************************************!*\
  !*** ./js/src/form-templates/ui/setupInitialView.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */





/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    sidebar = _getElements.sidebar,
    searchInput = _getElements.searchInput,
    bodyContent = _getElements.bodyContent,
    twinFeaturedTemplateItems = _getElements.twinFeaturedTemplateItems,
    availableTemplatesCategory = _getElements.availableTemplatesCategory,
    freeTemplatesCategory = _getElements.freeTemplatesCategory;
  var bodyContentAnimate = new core_utils__WEBPACK_IMPORTED_MODULE_0__.frmAnimate(bodyContent);

  // Clear the value in the search input
  searchInput.value = '';

  // Hide the twin featured template items
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hideElements)(twinFeaturedTemplateItems);

  // Set the 'Available Templates' count if it is present
  if (availableTemplatesCategory) {
    var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
      availableTemplatesCount = _getState.availableTemplatesCount;
    availableTemplatesCategory.querySelector('.frm-form-templates-cat-count').textContent = availableTemplatesCount;
  }

  // Update the 'Free Templates' count and hide the category if count is zero
  var _getState2 = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    freeTemplatesCount = _getState2.freeTemplatesCount;
  freeTemplatesCategory.querySelector('.frm-form-templates-cat-count').textContent = freeTemplatesCount;
  if (0 === freeTemplatesCount) {
    (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hide)(freeTemplatesCategory);
  }

  // Update extra templates count
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    extraTemplateCountElements = _getElements2.extraTemplateCountElements;
  var _getState3 = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    extraTemplatesCount = _getState3.extraTemplatesCount;
  extraTemplateCountElements.forEach(function (element) {
    return element.textContent = extraTemplatesCount;
  });

  // Smoothly display the updated UI elements
  bodyContent.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_2__.HIDE_JS_CLASS);
  sidebar.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_2__.HIDE_JS_CLASS);
  bodyContentAnimate.fadeIn();
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(sidebar);

  // Show the "Cancel" button in the header if the 'return_page' query param is present
  if ((0,_utils__WEBPACK_IMPORTED_MODULE_3__.hasQueryParam)('return_page')) {
    (0,___WEBPACK_IMPORTED_MODULE_4__.showHeaderCancelButton)();
  }
}
/* harmony default export */ __webpack_exports__["default"] = (setupInitialView);

/***/ }),

/***/ "./js/src/form-templates/ui/showEmptyState.js":
/*!****************************************************!*\
  !*** ./js/src/form-templates/ui/showEmptyState.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showAvailableTemplatesEmptyState: function() { return /* binding */ showAvailableTemplatesEmptyState; },
/* harmony export */   showCustomTemplatesEmptyState: function() { return /* binding */ showCustomTemplatesEmptyState; },
/* harmony export */   showFavoritesEmptyState: function() { return /* binding */ showFavoritesEmptyState; },
/* harmony export */   showSearchEmptyState: function() { return /* binding */ showSearchEmptyState; }
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */




/**
 * Display the search-empty state.
 *
 * @return {void}
 */
function showSearchEmptyState() {
  var _emptyState$dataset;
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    notEmptySearchText = _getState.notEmptySearchText;
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    pageTitle = _getElements.pageTitle,
    emptyState = _getElements.emptyState,
    emptyStateButton = _getElements.emptyStateButton,
    applicationTemplates = _getElements.applicationTemplates;

  // Toggle visibility and remove attributes based on search status
  if (_shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.SEARCH === ((_emptyState$dataset = emptyState.dataset) === null || _emptyState$dataset === void 0 ? void 0 : _emptyState$dataset.view)) {
    if (notEmptySearchText) {
      (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(emptyState);
      (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hideElements)([pageTitle, applicationTemplates]);
    } else {
      (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hide)(emptyState);
      emptyState.removeAttribute('data-view');
    }
    return;
  }

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.SEARCH);

  // Update text content
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    emptyStateTitle = _getElements2.emptyStateTitle,
    emptyStateText = _getElements2.emptyStateText;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No templates found', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Sorry, we didn\'t find any templates that match your criteria.', 'formidable');
  emptyStateButton.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Start from Scratch', 'formidable');

  // Display the empty state
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hideElements)([pageTitle, applicationTemplates]);
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.showElements)([emptyState, emptyStateButton]);
}
;

/**
 * Display the favorites-empty state.
 *
 * @return {void}
 */
function showFavoritesEmptyState() {
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    pageTitle = _getElements3.pageTitle,
    emptyState = _getElements3.emptyState,
    emptyStateButton = _getElements3.emptyStateButton;

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.FAVORITES);

  // Update text content
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    emptyStateTitle = _getElements4.emptyStateTitle,
    emptyStateText = _getElements4.emptyStateText;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No favorites', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You haven\'t added any templates to your favorites yet.', 'formidable');

  // Display the empty state
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hideElements)([pageTitle, emptyStateButton]);
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(emptyState);
}
;

/**
 * Display the custom-empty state.
 *
 * @return {void}
 */
function showCustomTemplatesEmptyState() {
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    pageTitle = _getElements5.pageTitle,
    emptyState = _getElements5.emptyState,
    emptyStateButton = _getElements5.emptyStateButton;

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.CUSTOM);

  // Update text content
  var _getElements6 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    emptyStateTitle = _getElements6.emptyStateTitle,
    emptyStateText = _getElements6.emptyStateText;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You currently have no templates.', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You haven\'t created any form templates. Begin now to simplify your workflow and save time.', 'formidable');
  emptyStateButton.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Create Template', 'formidable');

  // Display the empty state
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hide)(pageTitle);
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.showElements)([emptyState, emptyStateButton]);
}
;

/**
 * Display the available-templates-empty state.
 *
 * @return {void}
 */
function showAvailableTemplatesEmptyState() {
  var _getElements7 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    pageTitle = _getElements7.pageTitle,
    emptyState = _getElements7.emptyState,
    emptyStateButton = _getElements7.emptyStateButton;

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.AVAILABLE_TEMPLATES);

  // Update text content
  var _getElements8 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    emptyStateTitle = _getElements8.emptyStateTitle,
    emptyStateText = _getElements8.emptyStateText;
  var _getState2 = (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getState)(),
    extraTemplatesCount = _getState2.extraTemplatesCount;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No Templates Available', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Upgrade to PRO for %s+ options or explore Free Templates.', 'formidable'), extraTemplatesCount);

  // Display the empty state
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hideElements)([pageTitle, emptyStateButton]);
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(emptyState);
}
;

/***/ }),

/***/ "./js/src/form-templates/ui/showError.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/ui/showError.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showConfirmEmailAddressError: function() { return /* binding */ showConfirmEmailAddressError; },
/* harmony export */   showEmailAddressError: function() { return /* binding */ showEmailAddressError; }
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/**
 * Internal dependencies
 */


/**
 * Displays errors related to the email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
var showEmailAddressError = function showEmailAddressError(type) {
  (0,_utils__WEBPACK_IMPORTED_MODULE_0__.showFormError)('#frm_leave_email', '#frm_leave_email_error', type);
};

/**
 * Displays errors related to the confirm email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @param {string} [message] Optional. The specific error message to display.
 * @return {void}
 */
var showConfirmEmailAddressError = function showConfirmEmailAddressError(type, message) {
  (0,_utils__WEBPACK_IMPORTED_MODULE_0__.showFormError)('#frm_code_from_email', '#frm_code_from_email_error', type, message);
};

/***/ }),

/***/ "./js/src/form-templates/ui/showHeaderCancelButton.js":
/*!************************************************************!*\
  !*** ./js/src/form-templates/ui/showHeaderCancelButton.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showHeaderCancelButton: function() { return /* binding */ showHeaderCancelButton; }
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


/**
 * Display the "Cancel" button in the header.
 *
 * @return {void}
 */
function showHeaderCancelButton() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    headerCancelButton = _getElements.headerCancelButton;
  new core_utils__WEBPACK_IMPORTED_MODULE_0__.frmAnimate(headerCancelButton).fadeIn();
}
;

/***/ }),

/***/ "./js/src/form-templates/ui/showModal.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/ui/showModal.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showCodeFromEmailModal: function() { return /* binding */ showCodeFromEmailModal; },
/* harmony export */   showCreateTemplateModal: function() { return /* binding */ showCreateTemplateModal; },
/* harmony export */   showLeaveEmailModal: function() { return /* binding */ showLeaveEmailModal; },
/* harmony export */   showLockedTemplateModal: function() { return /* binding */ showLockedTemplateModal; },
/* harmony export */   showRenewAccountModal: function() { return /* binding */ showRenewAccountModal; },
/* harmony export */   showUpgradeModal: function() { return /* binding */ showUpgradeModal; }
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */





/**
 * Display the locked template modal.
 *
 * @param {HTMLElement} template The template element.
 * @return {void}
 */
function showLockedTemplateModal(template) {
  var plan = template.dataset.requiredPlan;
  switch (plan) {
    case _shared__WEBPACK_IMPORTED_MODULE_2__.PLANS.BASIC:
    case _shared__WEBPACK_IMPORTED_MODULE_2__.PLANS.PLUS:
    case _shared__WEBPACK_IMPORTED_MODULE_2__.PLANS.BUSINESS:
    case _shared__WEBPACK_IMPORTED_MODULE_2__.PLANS.ELITE:
      showUpgradeModal(plan, template);
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_2__.PLANS.RENEW:
      showRenewAccountModal();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_2__.PLANS.FREE:
      showLeaveEmailModal();
      break;
  }
}

/**
 * Base function to show a modal dialog with a customizable pre-open execution step.
 *
 * @param {Function} executePreOpen The function to be executed before opening the modal dialog.
 * @return {Function} A higher-order function that can be invoked to display the modal dialog.
 */
var showModal = function showModal(executePreOpen) {
  return /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var dialogWidget,
      _getElements,
      modalItems,
      _len,
      params,
      _key,
      _args = arguments;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          dialogWidget = (0,___WEBPACK_IMPORTED_MODULE_4__.getModalWidget)();
          if (dialogWidget) {
            _context.next = 3;
            break;
          }
          return _context.abrupt("return");
        case 3:
          _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), modalItems = _getElements.modalItems;
          (0,_utils__WEBPACK_IMPORTED_MODULE_3__.hideElements)(modalItems);
          dialogWidget.dialog('option', 'width', _shared__WEBPACK_IMPORTED_MODULE_2__.MODAL_SIZES.GENERAL);
          for (_len = _args.length, params = new Array(_len), _key = 0; _key < _len; _key++) {
            params[_key] = _args[_key];
          }
          _context.next = 9;
          return executePreOpen === null || executePreOpen === void 0 ? void 0 : executePreOpen.apply(void 0, params);
        case 9:
          dialogWidget.dialog('open');
        case 10:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
};

// Mapping each plan to the subsequent plans it can upgrade to
var upgradablePlans = {
  basic: ['basic', 'plus', 'business', 'elite'],
  plus: ['plus', 'business', 'elite'],
  business: ['business', 'elite'],
  elite: ['elite']
};

/**
 * Display the modal dialog to prompt the user to upgrade their account.
 *
 * @param {string} plan Current plan name
 * @param {HTMLElement} template The template element
 * @return {void}
 */
var showUpgradeModal = showModal(function (plan, template) {
  var templateName = template.querySelector('.frm-form-template-name').textContent.trim();
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    upgradeModal = _getElements2.upgradeModal,
    upgradeModalTemplateNames = _getElements2.upgradeModalTemplateNames,
    upgradeModalPlansIcons = _getElements2.upgradeModalPlansIcons,
    upgradeModalLink = _getElements2.upgradeModalLink;

  // Update template names
  upgradeModalTemplateNames.forEach(function (element) {
    return element.textContent = templateName;
  });

  // Update plan icons and their availability
  upgradeModalPlansIcons.forEach(function (icon) {
    var planType = icon.dataset.plan;
    var shouldDisplayCheck = upgradablePlans[plan].includes(planType);

    // Toggle icon class based on plan availability
    icon.classList.toggle('frm_green', shouldDisplayCheck);

    // Update SVG icon
    var svg = icon.querySelector('svg > use');
    svg.setAttribute('xlink:href', shouldDisplayCheck ? '#frm_checkmark_icon' : '#frm_close_icon');
  });

  // Append template slug to the upgrade modal link URL
  var templateSlug = template.dataset.slug ? "-".concat(template.dataset.slug) : '';
  upgradeModalLink.href = _shared__WEBPACK_IMPORTED_MODULE_2__.upgradeLink + templateSlug;
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(upgradeModal);
});

/**
 * Display the modal dialog to prompt the user to renew their account.
 *
 * @return {void}
 */
var showRenewAccountModal = showModal(function () {
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    renewAccountModal = _getElements3.renewAccountModal;
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(renewAccountModal);
});

/**
 * Display the modal dialog to prompt the user to leave an email.
 *
 * @return {void}
 */
var showLeaveEmailModal = showModal(function () {
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    leaveEmailModal = _getElements4.leaveEmailModal;
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(leaveEmailModal);
});

/**
 * Display the modal dialog to prompt the user to save the code sent to their email address.
 *
 * @return {void}
 */
var showCodeFromEmailModal = showModal(function () {
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    codeFromEmailModal = _getElements5.codeFromEmailModal;
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(codeFromEmailModal);
});

/**
 * Displays a modal dialog prompting the user to create a new template.
 *
 * @return {void}
 */
var showCreateTemplateModal = showModal(function () {
  var dialogWidget = (0,___WEBPACK_IMPORTED_MODULE_4__.getModalWidget)();
  dialogWidget.dialog('option', 'width', _shared__WEBPACK_IMPORTED_MODULE_2__.MODAL_SIZES.CREATE_TEMPLATE);
  var _getElements6 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    createTemplateModal = _getElements6.createTemplateModal;
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(createTemplateModal);
});

/***/ }),

/***/ "./js/src/form-templates/ui/showSelectedCategory.js":
/*!**********************************************************!*\
  !*** ./js/src/form-templates/ui/showSelectedCategory.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showAllTemplates: function() { return /* binding */ showAllTemplates; },
/* harmony export */   showAvailableTemplates: function() { return /* binding */ showAvailableTemplates; },
/* harmony export */   showCustomTemplates: function() { return /* binding */ showCustomTemplates; },
/* harmony export */   showFavoriteTemplates: function() { return /* binding */ showFavoriteTemplates; },
/* harmony export */   showFreeTemplates: function() { return /* binding */ showFreeTemplates; },
/* harmony export */   showSelectedCategory: function() { return /* binding */ showSelectedCategory; }
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../templates */ "./js/src/form-templates/templates/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
/**
 * Internal dependencies
 */






/**
 * Show templates based on selected category.
 *
 * @param {string} selectedCategory The selected category to display templates for.
 * @return {void}
 */
function showSelectedCategory(selectedCategory) {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    bodyContentChildren = _getElements.bodyContentChildren,
    pageTitle = _getElements.pageTitle,
    showCreateTemplateModalButton = _getElements.showCreateTemplateModalButton,
    templatesList = _getElements.templatesList,
    templateItems = _getElements.templateItems;
  if (_shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.ALL_TEMPLATES !== selectedCategory) {
    (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(bodyContentChildren);
  }
  (0,___WEBPACK_IMPORTED_MODULE_4__.updatePageTitle)();
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(showCreateTemplateModalButton);
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.show)(pageTitle);
  switch (selectedCategory) {
    case _shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.ALL_TEMPLATES:
      showAllTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.AVAILABLE_TEMPLATES:
      showAvailableTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.FREE_TEMPLATES:
      showFreeTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.FAVORITES:
      showFavoriteTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.CUSTOM:
      showCustomTemplates();
      break;
    default:
      (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(templateItems); // Clear the view for new content
      (0,_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([templatesList].concat(_toConsumableArray(_templates__WEBPACK_IMPORTED_MODULE_3__.categorizedTemplates[selectedCategory])));
      break;
  }
}

/**
 * Shows all templates when 'All Templates' is the selected category.
 *
 * @return {void}
 */
function showAllTemplates() {
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    bodyContentChildren = _getElements2.bodyContentChildren,
    pageTitleDivider = _getElements2.pageTitleDivider,
    templateItems = _getElements2.templateItems,
    twinFeaturedTemplateItems = _getElements2.twinFeaturedTemplateItems,
    customTemplatesSection = _getElements2.customTemplatesSection,
    emptyState = _getElements2.emptyState,
    applicationTemplates = _getElements2.applicationTemplates;
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([].concat(_toConsumableArray(bodyContentChildren), _toConsumableArray(templateItems)));
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)([pageTitleDivider].concat(_toConsumableArray(twinFeaturedTemplateItems), [customTemplatesSection, emptyState, applicationTemplates]));
}

/**
 * Shows favorite templates.
 *
 * @return {void}
 */
function showFavoriteTemplates() {
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.getState)(),
    favoritesCount = _getState.favoritesCount;
  if (0 === favoritesCount.total) {
    (0,___WEBPACK_IMPORTED_MODULE_4__.showFavoritesEmptyState)();
    return;
  }
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    bodyContent = _getElements3.bodyContent,
    templatesList = _getElements3.templatesList,
    templateItems = _getElements3.templateItems,
    customTemplatesSection = _getElements3.customTemplatesSection,
    customTemplatesTitle = _getElements3.customTemplatesTitle,
    customTemplatesList = _getElements3.customTemplatesList,
    customTemplateItems = _getElements3.customTemplateItems;

  // Clear the view for new content
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(templateItems);
  var elementsToShow = [];

  // Get all favorite items from the DOM and add the elements to show
  var favoriteItems = bodyContent.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-favorite-item"));
  elementsToShow.push.apply(elementsToShow, _toConsumableArray(favoriteItems));

  // Add default favorites if available
  if (favoritesCount.default > 0) {
    elementsToShow.push(templatesList);
  }

  // Add custom favorites if available
  if (favoritesCount.custom > 0) {
    var nonFavCustomTemplates = Array.from(customTemplateItems).filter(function (template) {
      return !(0,_utils__WEBPACK_IMPORTED_MODULE_2__.isFavoriteTemplate)(template);
    });
    (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(nonFavCustomTemplates);
    elementsToShow.push(customTemplatesSection);
    elementsToShow.push(customTemplatesList);
    if (0 === favoritesCount.default) {
      (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(customTemplatesTitle);
    } else {
      elementsToShow.push(customTemplatesTitle);
    }
  }

  // Show elements that were selected to be shown
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)(elementsToShow);
}

/**
 * Shows custom templates.
 *
 * @return {void}
 */
function showCustomTemplates() {
  var _getState2 = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.getState)(),
    customCount = _getState2.customCount;
  if (0 === customCount) {
    (0,___WEBPACK_IMPORTED_MODULE_4__.showCustomTemplatesEmptyState)();
    return;
  }
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    showCreateTemplateModalButton = _getElements4.showCreateTemplateModalButton,
    pageTitleDivider = _getElements4.pageTitleDivider,
    customTemplatesSection = _getElements4.customTemplatesSection,
    customTemplatesList = _getElements4.customTemplatesList,
    customTemplatesTitle = _getElements4.customTemplatesTitle,
    customTemplateItems = _getElements4.customTemplateItems;
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(customTemplatesTitle);
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([showCreateTemplateModalButton, pageTitleDivider, customTemplatesSection, customTemplatesList].concat(_toConsumableArray(customTemplateItems)));
}

/**
 * Shows available templates.
 *
 * @return {void}
 */
function showAvailableTemplates() {
  var _getState3 = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.getState)(),
    availableTemplatesCount = _getState3.availableTemplatesCount;
  if (0 === availableTemplatesCount) {
    (0,___WEBPACK_IMPORTED_MODULE_4__.showAvailableTemplatesEmptyState)();
    return;
  }
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    templatesList = _getElements5.templatesList,
    templateItems = _getElements5.templateItems,
    availableTemplateItems = _getElements5.availableTemplateItems;
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(templateItems); // Clear the view for new content
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([templatesList].concat(_toConsumableArray(availableTemplateItems)));
}

/**
 * Shows free templates.
 *
 * @return {void}
 */
function showFreeTemplates() {
  var _getElements6 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    templatesList = _getElements6.templatesList,
    templateItems = _getElements6.templateItems,
    freeTemplateItems = _getElements6.freeTemplateItems;
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(templateItems); // Clear the view for new content
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([templatesList].concat(_toConsumableArray(freeTemplateItems)));
}
/* harmony default export */ __webpack_exports__["default"] = (showSelectedCategory);

/***/ }),

/***/ "./js/src/form-templates/utils/index.js":
/*!**********************************************!*\
  !*** ./js/src/form-templates/utils/index.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.addToRequestQueue; },
/* harmony export */   getQueryParam: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.getQueryParam; },
/* harmony export */   hasQueryParam: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.hasQueryParam; },
/* harmony export */   hide: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.hide; },
/* harmony export */   hideElements: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.hideElements; },
/* harmony export */   isAllTemplatesCategory: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isAllTemplatesCategory; },
/* harmony export */   isCustomCategory: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isCustomCategory; },
/* harmony export */   isCustomTemplate: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isCustomTemplate; },
/* harmony export */   isEmptyObject: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isEmptyObject; },
/* harmony export */   isFavoriteTemplate: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isFavoriteTemplate; },
/* harmony export */   isFavoritesCategory: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isFavoritesCategory; },
/* harmony export */   isFeaturedTemplate: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isFeaturedTemplate; },
/* harmony export */   isHTMLElement: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isHTMLElement; },
/* harmony export */   isLockedTemplate: function() { return /* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_1__.isLockedTemplate; },
/* harmony export */   isValidEmail: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isValidEmail; },
/* harmony export */   isVisible: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isVisible; },
/* harmony export */   removeQueryParam: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.removeQueryParam; },
/* harmony export */   setQueryParam: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.setQueryParam; },
/* harmony export */   show: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.show; },
/* harmony export */   showElements: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.showElements; },
/* harmony export */   showFormError: function() { return /* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.showFormError; }
/* harmony export */ });
/* harmony import */ var _common_utilities__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../common/utilities */ "./js/src/common/utilities/index.js");
/* harmony import */ var _validation__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./validation */ "./js/src/form-templates/utils/validation.js");



/***/ }),

/***/ "./js/src/form-templates/utils/validation.js":
/*!***************************************************!*\
  !*** ./js/src/form-templates/utils/validation.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isAllTemplatesCategory: function() { return /* binding */ isAllTemplatesCategory; },
/* harmony export */   isCustomCategory: function() { return /* binding */ isCustomCategory; },
/* harmony export */   isCustomTemplate: function() { return /* binding */ isCustomTemplate; },
/* harmony export */   isFavoriteTemplate: function() { return /* binding */ isFavoriteTemplate; },
/* harmony export */   isFavoritesCategory: function() { return /* binding */ isFavoritesCategory; },
/* harmony export */   isFeaturedTemplate: function() { return /* binding */ isFeaturedTemplate; },
/* harmony export */   isLockedTemplate: function() { return /* binding */ isLockedTemplate; }
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/utils/index.js");
/**
 * Internal dependencies
 */



/**
 * Checks if the category is "All Templates".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "All Templates", otherwise false.
 */
var isAllTemplatesCategory = function isAllTemplatesCategory(category) {
  return _shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.ALL_TEMPLATES === category;
};

/**
 * Checks if the category is "Favorites".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "Favorites", otherwise false.
 */
var isFavoritesCategory = function isFavoritesCategory(category) {
  return _shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.FAVORITES === category;
};

/**
 * Checks if the category is "Custom".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "Custom", otherwise false.
 */
var isCustomCategory = function isCustomCategory(category) {
  return _shared__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS.CUSTOM === category;
};

/**
 * Checks if a template is a favorite.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is a favorite, otherwise false.
 */
var isFavoriteTemplate = function isFavoriteTemplate(template) {
  return (0,___WEBPACK_IMPORTED_MODULE_1__.isHTMLElement)(template) ? template.classList.contains("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-favorite-item")) : false;
};

/**
 * Checks if a template is custom.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is custom, otherwise false.
 */
var isCustomTemplate = function isCustomTemplate(template) {
  return (0,___WEBPACK_IMPORTED_MODULE_1__.isHTMLElement)(template) ? template.classList.contains("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-custom-item")) : false;
};

/**
 * Checks if a template is featured.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is featured, otherwise false.
 */
var isFeaturedTemplate = function isFeaturedTemplate(template) {
  return (0,___WEBPACK_IMPORTED_MODULE_1__.isHTMLElement)(template) ? _shared__WEBPACK_IMPORTED_MODULE_0__.FEATURED_TEMPLATES_KEYS.includes(Number(template.dataset.id)) : false;
};

/**
 * Checks if a template is locked.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is locked, otherwise false.
 */
var isLockedTemplate = function isLockedTemplate(template) {
  return (0,___WEBPACK_IMPORTED_MODULE_1__.isHTMLElement)(template) ? template.classList.contains("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-locked-item")) : false;
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
/*!****************************************!*\
  !*** ./js/src/form-templates/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _initializeFormTemplates__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./initializeFormTemplates */ "./js/src/form-templates/initializeFormTemplates.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__["default"])(function () {
  /**
   * Entry point for pre-initialization adjustments to the page state.
   *
   * @param {Object} state Current state of the page.
   */
  wp.hooks.doAction('frmFormTemplates.beforeInitialize', {
    getState: _shared__WEBPACK_IMPORTED_MODULE_0__.getState,
    setState: _shared__WEBPACK_IMPORTED_MODULE_0__.setState
  });

  // Initialize the form templates
  (0,_initializeFormTemplates__WEBPACK_IMPORTED_MODULE_1__["default"])();

  /**
   * Entry point for post-initialization custom logic or adjustments to the page state.
   *
   * @param {Object} state Current state of the page.
   */
  wp.hooks.doAction('frmFormTemplates.afterInitialize', {
    getState: _shared__WEBPACK_IMPORTED_MODULE_0__.getState,
    setState: _shared__WEBPACK_IMPORTED_MODULE_0__.setState
  });

  /**
   * Trigger a specific action to interact with the hidden form '#frm-new-template',
   * which is used for creating or using a form template.
   *
   * @param {jQuery} jQuery('#frm-new-template') The jQuery object containing the hidden form element.
   */
  wp.hooks.doAction('frm_new_form_modal_form', jQuery('#frm-new-template'));
});
}();
/******/ })()
;
//# sourceMappingURL=form-templates.js.map