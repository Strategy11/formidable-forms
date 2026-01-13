<<<<<<< HEAD
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@tannin/compile/index.js":
/*!***********************************************!*\
  !*** ./node_modules/@tannin/compile/index.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ compile)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ evaluate)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ pluralForms)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ postfix)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ domReady)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createAddHook);
//# sourceMappingURL=createAddHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createCurrentHook.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createCurrentHook.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createCurrentHook);
//# sourceMappingURL=createCurrentHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createDidHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createDidHook.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createDidHook);
//# sourceMappingURL=createDidHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createDoingHook.js":
/*!***********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createDoingHook.js ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createDoingHook);
//# sourceMappingURL=createDoingHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createHasHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createHasHook.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createHasHook);
//# sourceMappingURL=createHasHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createHooks.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createHooks.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _Hooks: () => (/* binding */ _Hooks),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createHooks);
//# sourceMappingURL=createHooks.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createRemoveHook.js":
/*!************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createRemoveHook.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createRemoveHook);
//# sourceMappingURL=createRemoveHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/createRunHook.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/createRunHook.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createRunHook);
//# sourceMappingURL=createRunHook.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/index.js":
/*!*************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/index.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   actions: () => (/* binding */ actions),
/* harmony export */   addAction: () => (/* binding */ addAction),
/* harmony export */   addFilter: () => (/* binding */ addFilter),
/* harmony export */   applyFilters: () => (/* binding */ applyFilters),
/* harmony export */   createHooks: () => (/* reexport safe */ _createHooks__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   currentAction: () => (/* binding */ currentAction),
/* harmony export */   currentFilter: () => (/* binding */ currentFilter),
/* harmony export */   defaultHooks: () => (/* binding */ defaultHooks),
/* harmony export */   didAction: () => (/* binding */ didAction),
/* harmony export */   didFilter: () => (/* binding */ didFilter),
/* harmony export */   doAction: () => (/* binding */ doAction),
/* harmony export */   doingAction: () => (/* binding */ doingAction),
/* harmony export */   doingFilter: () => (/* binding */ doingFilter),
/* harmony export */   filters: () => (/* binding */ filters),
/* harmony export */   hasAction: () => (/* binding */ hasAction),
/* harmony export */   hasFilter: () => (/* binding */ hasFilter),
/* harmony export */   removeAction: () => (/* binding */ removeAction),
/* harmony export */   removeAllActions: () => (/* binding */ removeAllActions),
/* harmony export */   removeAllFilters: () => (/* binding */ removeAllFilters),
/* harmony export */   removeFilter: () => (/* binding */ removeFilter)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (validateHookName);
//# sourceMappingURL=validateHookName.js.map

/***/ }),

/***/ "./node_modules/@wordpress/hooks/build-module/validateNamespace.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/build-module/validateNamespace.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (validateNamespace);
//# sourceMappingURL=validateNamespace.js.map

/***/ }),

/***/ "./node_modules/@wordpress/i18n/build-module/create-i18n.js":
/*!******************************************************************!*\
  !*** ./node_modules/@wordpress/i18n/build-module/create-i18n.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createI18n: () => (/* binding */ createI18n)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   __: () => (/* binding */ __),
/* harmony export */   _n: () => (/* binding */ _n),
/* harmony export */   _nx: () => (/* binding */ _nx),
/* harmony export */   _x: () => (/* binding */ _x),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   getLocaleData: () => (/* binding */ getLocaleData),
/* harmony export */   hasTranslation: () => (/* binding */ hasTranslation),
/* harmony export */   isRTL: () => (/* binding */ isRTL),
/* harmony export */   resetLocaleData: () => (/* binding */ resetLocaleData),
/* harmony export */   setLocaleData: () => (/* binding */ setLocaleData),
/* harmony export */   subscribe: () => (/* binding */ subscribe)
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

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (i18n);
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   __: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.__),
/* harmony export */   _n: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__._n),
/* harmony export */   _nx: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__._nx),
/* harmony export */   _x: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__._x),
/* harmony export */   createI18n: () => (/* reexport safe */ _create_i18n__WEBPACK_IMPORTED_MODULE_1__.createI18n),
/* harmony export */   defaultI18n: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__["default"]),
/* harmony export */   getLocaleData: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.getLocaleData),
/* harmony export */   hasTranslation: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.hasTranslation),
/* harmony export */   isRTL: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.isRTL),
/* harmony export */   resetLocaleData: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.resetLocaleData),
/* harmony export */   setLocaleData: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.setLocaleData),
/* harmony export */   sprintf: () => (/* reexport safe */ _sprintf__WEBPACK_IMPORTED_MODULE_0__.sprintf),
/* harmony export */   subscribe: () => (/* reexport safe */ _default_i18n__WEBPACK_IMPORTED_MODULE_2__.subscribe)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   sprintf: () => (/* binding */ sprintf)
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

/***/ "./js/src/core/constants.js":
/*!**********************************!*\
  !*** ./js/src/core/constants.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CHECKED_CLASS: () => (/* binding */ CHECKED_CLASS),
/* harmony export */   CURRENT_CLASS: () => (/* binding */ CURRENT_CLASS),
/* harmony export */   DISABLED_CLASS: () => (/* binding */ DISABLED_CLASS),
/* harmony export */   HIDDEN_CLASS: () => (/* binding */ HIDDEN_CLASS),
/* harmony export */   HIDE_JS_CLASS: () => (/* binding */ HIDE_JS_CLASS),
/* harmony export */   HOOKS: () => (/* binding */ HOOKS),
/* harmony export */   PLUGIN_URL: () => (/* binding */ PLUGIN_URL),
/* harmony export */   SINGLE_SETTINGS_CLASS: () => (/* binding */ SINGLE_SETTINGS_CLASS),
/* harmony export */   nonce: () => (/* binding */ nonce)
/* harmony export */ });
var _window$frmGlobal = window.frmGlobal,
  PLUGIN_URL = _window$frmGlobal.url,
  nonce = _window$frmGlobal.nonce;

var HIDDEN_CLASS = 'frm_hidden';
var DISABLED_CLASS = 'frm_disabled';
var HIDE_JS_CLASS = 'frm-hide-js';
var CURRENT_CLASS = 'frm-current';
var CHECKED_CLASS = 'frm-checked';
var SINGLE_SETTINGS_CLASS = 'frm-single-settings';
var HOOKS = {
  SHOW_FIELD_SETTINGS: 'frmShowedFieldSettings'
};

/***/ }),

/***/ "./js/src/core/factory/createPageElements.js":
/*!***************************************************!*\
  !*** ./js/src/core/factory/createPageElements.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageElements: () => (/* binding */ createPageElements)
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
  if (_typeof(initialElements) !== 'object' || initialElements === null) {
    throw new Error('createPageElements: initialElements must be a non-null object');
  }
  var elements = initialElements;

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
    getElements: getElements,
    addElements: addElements
  };
}

/***/ }),

/***/ "./js/src/core/factory/createPageState.js":
/*!************************************************!*\
  !*** ./js/src/core/factory/createPageState.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageState: () => (/* binding */ createPageState)
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
  if (_typeof(initialState) !== 'object' || initialState === null) {
    throw new Error('createPageState: initialState must be a non-null object');
  }
  var state = initialState;

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
    var value = Reflect.get(state, propertyName);

    // We convert `undefined` to `null` for a consistent API.
    // This makes it easier for users to handle the results since all missing properties return `null`.
    return value === undefined ? null : value;
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageElements: () => (/* reexport safe */ _createPageElements__WEBPACK_IMPORTED_MODULE_0__.createPageElements),
/* harmony export */   createPageState: () => (/* reexport safe */ _createPageState__WEBPACK_IMPORTED_MODULE_1__.createPageState)
/* harmony export */ });
/* harmony import */ var _createPageElements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./createPageElements */ "./js/src/core/factory/createPageElements.js");
/* harmony import */ var _createPageState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./createPageState */ "./js/src/core/factory/createPageState.js");



/***/ }),

/***/ "./js/src/core/page-skeleton/constants.js":
/*!************************************************!*\
  !*** ./js/src/core/page-skeleton/constants.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PREFIX: () => (/* binding */ PREFIX),
/* harmony export */   SEARCH_RESULT_ITEM: () => (/* binding */ SEARCH_RESULT_ITEM),
/* harmony export */   VIEWS: () => (/* binding */ VIEWS)
/* harmony export */ });
var PREFIX = 'frm-page-skeleton';
var SEARCH_RESULT_ITEM = 'frm-card-item';
var VIEWS = {
  ALL_ITEMS: 'all-items'
};

/***/ }),

/***/ "./js/src/core/page-skeleton/elements/elements.js":
/*!********************************************************!*\
  !*** ./js/src/core/page-skeleton/elements/elements.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* binding */ addElements),
/* harmony export */   getElements: () => (/* binding */ getElements)
/* harmony export */ });
/* harmony import */ var core_factory__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/factory */ "./js/src/core/factory/index.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../constants */ "./js/src/core/page-skeleton/constants.js");
/* harmony import */ var _emptyStateElement__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./emptyStateElement */ "./js/src/core/page-skeleton/elements/emptyStateElement.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


var bodyContent = document.getElementById('post-body-content');
var sidebar = document.getElementById("".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-sidebar"));

// Append empty state elements to body content
var emptyState = (0,_emptyStateElement__WEBPACK_IMPORTED_MODULE_2__.createEmptyStateElement)();
bodyContent === null || bodyContent === void 0 || bodyContent.appendChild(emptyState);
var emptyStateElements = (0,_emptyStateElement__WEBPACK_IMPORTED_MODULE_2__.getEmptyStateElements)();
var _createPageElements = (0,core_factory__WEBPACK_IMPORTED_MODULE_0__.createPageElements)(_objectSpread({
    bodyContent: bodyContent,
    // Sidebar elements
    sidebar: sidebar,
    searchInput: sidebar.querySelector('.frm-search-input'),
    categoryItems: sidebar.querySelectorAll(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-cat")),
    allItemsCategory: sidebar.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-cat[data-category=\"").concat(_constants__WEBPACK_IMPORTED_MODULE_1__.VIEWS.ALL_ITEMS, "\"]"))
  }, emptyStateElements)),
  getElements = _createPageElements.getElements,
  addElements = _createPageElements.addElements;


/***/ }),

/***/ "./js/src/core/page-skeleton/elements/emptyStateElement.js":
/*!*****************************************************************!*\
  !*** ./js/src/core/page-skeleton/elements/emptyStateElement.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createEmptyStateElement: () => (/* binding */ createEmptyStateElement),
/* harmony export */   getEmptyStateElements: () => (/* binding */ getEmptyStateElements)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../constants */ "./js/src/core/page-skeleton/constants.js");
/**
 * WordPress dependencies
 */


/**
 * External dependencies
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
    id: "".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-empty-state"),
    className: "frm-flex-col frm-flex-center frm-gap-md ".concat(core_constants__WEBPACK_IMPORTED_MODULE_1__.HIDDEN_CLASS),
    children: [img({
      src: "".concat(core_constants__WEBPACK_IMPORTED_MODULE_1__.PLUGIN_URL, "/images/page-skeleton/empty-state.svg"),
      alt: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Empty State', 'formidable')
    }), div({
      className: 'frmcenter',
      children: [tag('h2', {
        className: "".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-title frm-mb-0")
      }), tag('p', {
        className: "".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-text frm-mb-0")
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
  var emptyState = document.querySelector("#".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-empty-state"));
  return {
    emptyState: emptyState,
    emptyStateTitle: emptyState === null || emptyState === void 0 ? void 0 : emptyState.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-title")),
    emptyStateText: emptyState === null || emptyState === void 0 ? void 0 : emptyState.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-text")),
    emptyStateButton: emptyState === null || emptyState === void 0 ? void 0 : emptyState.querySelector('.button')
  };
}

/***/ }),

/***/ "./js/src/core/page-skeleton/elements/index.js":
/*!*****************************************************!*\
  !*** ./js/src/core/page-skeleton/elements/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.addElements),
/* harmony export */   getElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.getElements)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./elements */ "./js/src/core/page-skeleton/elements/elements.js");


/***/ }),

/***/ "./js/src/core/page-skeleton/events/categoryListener.js":
/*!**************************************************************!*\
  !*** ./js/src/core/page-skeleton/events/categoryListener.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addCategoryEvents: () => (/* binding */ addCategoryEvents)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/core/page-skeleton/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/core/page-skeleton/shared/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! . */ "./js/src/core/page-skeleton/events/index.js");
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
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    categoryItems = _getElements.categoryItems;

  // Attach click and keyboard event listeners to each sidebar category
  categoryItems.forEach(function (category) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.onClickPreventDefault)(category, onCategoryClick);
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
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
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
   * @param {string} selectedCategory The selected category
   */
  selectedCategory = wp.hooks.applyFilters('frmPage.selectedCategory', newSelectedCategory);

  // Highlight the newly clicked category and update the application state
  selectedCategoryEl.classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS);
  selectedCategoryEl = clickedCategory;
  selectedCategoryEl.classList.add(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS);
  (0,_shared__WEBPACK_IMPORTED_MODULE_3__.setState)({
    selectedCategory: selectedCategory,
    selectedCategoryEl: selectedCategoryEl
  });

  // Reset the search input if it contains text
  if (notEmptySearchText) {
    (0,___WEBPACK_IMPORTED_MODULE_4__.resetSearchInput)();
  }

  /**
   * Trigger custom action to update category content.
   *
   * @param {string} selectedCategory The selected category.
   */
  wp.hooks.doAction('frmPage.onCategoryClick', selectedCategory);

  // Smoothly display the updated UI elements
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    bodyContent = _getElements2.bodyContent;
  new core_utils__WEBPACK_IMPORTED_MODULE_1__.frmAnimate(bodyContent).fadeIn();
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
    event.preventDefault();
    onCategoryClick(event);
  }
}

/***/ }),

/***/ "./js/src/core/page-skeleton/events/index.js":
/*!***************************************************!*\
  !*** ./js/src/core/page-skeleton/events/index.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addCategoryEvents: () => (/* reexport safe */ _categoryListener__WEBPACK_IMPORTED_MODULE_1__.addCategoryEvents),
/* harmony export */   resetSearchInput: () => (/* binding */ resetSearchInput)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/core/page-skeleton/elements/index.js");
/* harmony import */ var _categoryListener__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./categoryListener */ "./js/src/core/page-skeleton/events/categoryListener.js");
/**
 * Internal dependencies
 */


/**
 * Resets the value of the search input and triggers an input event.
 *
 * @return {void}
 */
function resetSearchInput() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    searchInput = _getElements.searchInput;
  searchInput.value = '';
  searchInput.dispatchEvent(new Event('input', {
    bubbles: true
  }));
}


/***/ }),

/***/ "./js/src/core/page-skeleton/index.js":
/*!********************************************!*\
  !*** ./js/src/core/page-skeleton/index.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PREFIX: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PREFIX),
/* harmony export */   SEARCH_RESULT_ITEM: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.SEARCH_RESULT_ITEM),
/* harmony export */   VIEWS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.VIEWS),
/* harmony export */   addCategoryEvents: () => (/* reexport safe */ _events__WEBPACK_IMPORTED_MODULE_3__.addCategoryEvents),
/* harmony export */   addElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_1__.addElements),
/* harmony export */   getElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_1__.getElements),
/* harmony export */   getSingleState: () => (/* reexport safe */ _shared__WEBPACK_IMPORTED_MODULE_2__.getSingleState),
/* harmony export */   getState: () => (/* reexport safe */ _shared__WEBPACK_IMPORTED_MODULE_2__.getState),
/* harmony export */   resetSearchInput: () => (/* reexport safe */ _events__WEBPACK_IMPORTED_MODULE_3__.resetSearchInput),
/* harmony export */   setSingleState: () => (/* reexport safe */ _shared__WEBPACK_IMPORTED_MODULE_2__.setSingleState),
/* harmony export */   setState: () => (/* reexport safe */ _shared__WEBPACK_IMPORTED_MODULE_2__.setState)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/core/page-skeleton/constants.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./elements */ "./js/src/core/page-skeleton/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./shared */ "./js/src/core/page-skeleton/shared/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./events */ "./js/src/core/page-skeleton/events/index.js");





/***/ }),

/***/ "./js/src/core/page-skeleton/shared/index.js":
/*!***************************************************!*\
  !*** ./js/src/core/page-skeleton/shared/index.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getSingleState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_0__.getSingleState),
/* harmony export */   getState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_0__.getState),
/* harmony export */   setSingleState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_0__.setSingleState),
/* harmony export */   setState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_0__.setState)
/* harmony export */ });
/* harmony import */ var _pageState__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pageState */ "./js/src/core/page-skeleton/shared/pageState.js");


/***/ }),

/***/ "./js/src/core/page-skeleton/shared/pageState.js":
/*!*******************************************************!*\
  !*** ./js/src/core/page-skeleton/shared/pageState.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getSingleState: () => (/* binding */ getSingleState),
/* harmony export */   getState: () => (/* binding */ getState),
/* harmony export */   setSingleState: () => (/* binding */ setSingleState),
/* harmony export */   setState: () => (/* binding */ setState)
/* harmony export */ });
/* harmony import */ var core_factory__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/factory */ "./js/src/core/factory/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/core/page-skeleton/elements/index.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../constants */ "./js/src/core/page-skeleton/constants.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
  allItemsCategory = _getElements.allItemsCategory;
var _createPageState = (0,core_factory__WEBPACK_IMPORTED_MODULE_0__.createPageState)({
    notEmptySearchText: false,
    selectedCategory: _constants__WEBPACK_IMPORTED_MODULE_2__.VIEWS.ALL_ITEMS,
    selectedCategoryEl: allItemsCategory
  }),
  getState = _createPageState.getState,
  getSingleState = _createPageState.getSingleState,
  setState = _createPageState.setState,
  setSingleState = _createPageState.setSingleState;


/***/ }),

/***/ "./js/src/core/ui/addProgressToCardBoxes.js":
/*!**************************************************!*\
  !*** ./js/src/core/ui/addProgressToCardBoxes.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * Internal Dependencies
 */
var span = window.frmDom.span;

/**
 * Adds a progress bar to each card box element to visually indicate its position in the sequence.
 *
 * @param {Element[]} cardBoxes Collection of card box elements to enhance with progress bars.
 * @return {void}
 */
function addProgressToCardBoxes(cardBoxes) {
  if (!Array.isArray(cardBoxes) || !cardBoxes.length) {
    console.warn('addProgressToCardBoxes: Expected a non-empty array of cardBoxes.');
    return;
  }
  cardBoxes.forEach(function (element, index) {
    // Exclude cards that either don't require a progress bar or already include one
    if (!element.classList.contains('frm-has-progress-bar') || element.querySelector('.frm-card-box-progress-bar')) {
      return;
    }
    var progressBar = span();
    var widthPercentage = (index + 1) / cardBoxes.length * 100;
    progressBar.style.width = "".concat(widthPercentage, "%");
    var progressBarContainer = span({
      className: 'frm-card-box-progress-bar',
      child: progressBar
    });
    element.insertAdjacentElement('afterbegin', progressBarContainer);
  });
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addProgressToCardBoxes);

/***/ }),

/***/ "./js/src/core/ui/counter.js":
/*!***********************************!*\
  !*** ./js/src/core/ui/counter.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * Updates the text content of an element with a counter value using smooth animation.
 *
 * @param {HTMLElement|string} element          The DOM element or selector to update
 * @param {number|string}      value            The new counter value to set
 * @param {Object}             options          Animation options
 * @param {number}             options.duration Duration in milliseconds (default: 3000)
 * @param {Function}           options.easing   Easing function (default: easeOutQuart)
 * @throws {Error} When element is not found or invalid
 * @return {HTMLElement} The updated element for method chaining
 */
var counter = function counter(element, value) {
  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var targetElement = typeof element === 'string' ? document.querySelector(element) : element;
  if (!targetElement || !(targetElement instanceof HTMLElement)) {
    return null;
  }
  var targetValue = typeof value === 'number' ? value : parseInt(value, 10);
  if (isNaN(targetValue)) {
    console.warn('Counter: Invalid value provided, defaulting to 0');
    return setElementValueAndReturn(targetElement, '0');
  }

  // Don't run the animation if the sent value is 0
  if (targetValue === 0) {
    return setElementValueAndReturn(targetElement, '0');
  }
  var _options$duration = options.duration,
    duration = _options$duration === void 0 ? 3000 : _options$duration,
    _options$easing = options.easing,
    easing = _options$easing === void 0 ? easeOutQuart : _options$easing;
  var startValue = parseInt(targetElement.textContent, 10) || 0;
  var change = targetValue - startValue;

  // Skip animation if no change needed
  if (change === 0) {
    return targetElement;
  }

  // Cancel any existing animation
  if (targetElement._counterAnimation) {
    cancelAnimationFrame(targetElement._counterAnimation);
  }

  // Start animation
  targetElement.classList.add('frm-fadein');
  targetElement._counterAnimation = requestAnimationFrame(function (timestamp) {
    return _animateCounter(timestamp, targetElement, startValue, targetValue, duration, change, easing);
  });
  return targetElement;
};

/**
 * Helper function to set element text content and return element
 *
 * @param {HTMLElement}   element Target element
 * @param {string|number} value   Value to set
 * @return {HTMLElement} The element for method chaining
 */
var setElementValueAndReturn = function setElementValueAndReturn(element, value) {
  element.textContent = String(value);
  return element;
};

/**
 * Standalone animation function for counter (optimized to prevent redefinition)
 *
 * @param {number}      timestamp   Current timestamp from requestAnimationFrame
 * @param {HTMLElement} element     Target element to animate
 * @param {number}      startValue  Starting counter value
 * @param {number}      targetValue Target counter value
 * @param {number}      duration    Animation duration in milliseconds
 * @param {number}      change      Total change amount (targetValue - startValue)
 * @param {Function}    easing      Easing function
 * @return {void}
 */
var _animateCounter = function animateCounter(timestamp, element, startValue, targetValue, duration, change, easing) {
  if (!element._counterStartTime) {
    element._counterStartTime = timestamp;
    element._counterLastTimestamp = timestamp;
    element._counterFrameDropCount = 0;
    element._counterLastValue = startValue;
  }
  var frameDelta = timestamp - element._counterLastTimestamp;
  var elapsed = timestamp - element._counterStartTime;

  // Performance monitoring: detect animation stuttering
  // If frame gaps exceed 50ms (indicating browser lag/blocking), count as frame drop
  if (frameDelta > 50 && element._counterLastTimestamp !== null) {
    element._counterFrameDropCount++;

    // Fallback strategy: after 3 frame drops, abandon JS animation for CSS transition
    // This prevents choppy animations when browser is under heavy load
    if (element._counterFrameDropCount > 3) {
      element.style.transition = "opacity ".concat(Math.max(duration - elapsed, 100), "ms ease-out");
      element.textContent = String(targetValue);
      delete element._counterAnimation;
      return;
    }
  }

  // Calculate eased progress and current value
  var progress = Math.min(elapsed / duration, 1);
  var easedProgress = easing(progress);
  var currentValue = Math.round(startValue + change * easedProgress);

  // Only update DOM if value actually changed (reduce unnecessary reflows)
  if (currentValue !== element._counterLastValue) {
    element.textContent = String(currentValue);
    element._counterLastValue = currentValue;
  }
  element._counterLastTimestamp = timestamp;

  // Continue animation or finish
  if (progress < 1) {
    element._counterAnimation = requestAnimationFrame(function (timestamp) {
      return _animateCounter(timestamp, element, startValue, targetValue, duration, change, easing);
    });
    return;
  }

  // Ensure final value is exact
  element.textContent = String(targetValue);

  // Clean up all counter-related properties
  ['_counterAnimation', '_counterStartTime', '_counterLastTimestamp', '_counterFrameDropCount', '_counterLastValue'].forEach(function (prop) {
    return delete element[prop];
  });
  element.style.removeProperty('transition');
};

/**
 * Easing function for smooth animation
 *
 * @param {number} t Progress from 0 to 1
 * @return {number} Eased value
 */
var easeOutQuart = function easeOutQuart(t) {
  return 1 - Math.pow(1 - t, 4);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (counter);

/***/ }),

/***/ "./js/src/core/ui/index.js":
/*!*********************************!*\
  !*** ./js/src/core/ui/index.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addProgressToCardBoxes: () => (/* reexport safe */ _addProgressToCardBoxes__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   counter: () => (/* reexport safe */ _counter__WEBPACK_IMPORTED_MODULE_1__["default"])
/* harmony export */ });
/* harmony import */ var _addProgressToCardBoxes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./addProgressToCardBoxes */ "./js/src/core/ui/addProgressToCardBoxes.js");
/* harmony import */ var _counter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./counter */ "./js/src/core/ui/counter.js");



/***/ }),

/***/ "./js/src/core/utils/animation.js":
/*!****************************************!*\
  !*** ./js/src/core/utils/animation.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmAnimate: () => (/* binding */ frmAnimate)
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
   * @param {Element|Element[]}                elements The elements to animate.
   * @param {'default'|'cascade'|'cascade-3d'} type     The animation type: default | cascade | cascade-3d
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
     * @param {number} delay The transition delay value.
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: () => (/* binding */ addToRequestQueue)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showFormError: () => (/* binding */ showFormError)
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

/***/ "./js/src/core/utils/globalModules.js":
/*!********************************************!*\
  !*** ./js/src/core/utils/globalModules.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   a: () => (/* binding */ a),
/* harmony export */   bold: () => (/* binding */ bold),
/* harmony export */   button: () => (/* binding */ button),
/* harmony export */   div: () => (/* binding */ div),
/* harmony export */   doJsonPost: () => (/* binding */ doJsonPost),
/* harmony export */   documentOn: () => (/* binding */ documentOn),
/* harmony export */   footerButton: () => (/* binding */ footerButton),
/* harmony export */   img: () => (/* binding */ img),
/* harmony export */   maybeCreateModal: () => (/* binding */ maybeCreateModal),
/* harmony export */   onClickPreventDefault: () => (/* binding */ onClickPreventDefault),
/* harmony export */   p: () => (/* binding */ p),
/* harmony export */   span: () => (/* binding */ span),
/* harmony export */   svg: () => (/* binding */ svg),
/* harmony export */   tag: () => (/* binding */ tag)
/* harmony export */ });
var _frmDom = frmDom,
  div = _frmDom.div,
  span = _frmDom.span,
  tag = _frmDom.tag,
  a = _frmDom.a,
  img = _frmDom.img,
  svg = _frmDom.svg;
var _frmDom$modal = frmDom.modal,
  maybeCreateModal = _frmDom$modal.maybeCreateModal,
  footerButton = _frmDom$modal.footerButton;
var _frmDom$util = frmDom.util,
  onClickPreventDefault = _frmDom$util.onClickPreventDefault,
  documentOn = _frmDom$util.documentOn;
var doJsonPost = frmDom.ajax.doJsonPost;
var p = function p(args) {
  return tag('p', args);
};
var bold = function bold(args) {
  return tag('strong', args);
};
var button = function button(args) {
  return tag('button', args);
};


/***/ }),

/***/ "./js/src/core/utils/index.js":
/*!************************************!*\
  !*** ./js/src/core/utils/index.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   a: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.a),
/* harmony export */   addToRequestQueue: () => (/* reexport safe */ _async__WEBPACK_IMPORTED_MODULE_1__.addToRequestQueue),
/* harmony export */   bold: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.bold),
/* harmony export */   button: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.button),
/* harmony export */   div: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.div),
/* harmony export */   doJsonPost: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.doJsonPost),
/* harmony export */   documentOn: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.documentOn),
/* harmony export */   footerButton: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.footerButton),
/* harmony export */   frmAnimate: () => (/* reexport safe */ _animation__WEBPACK_IMPORTED_MODULE_0__.frmAnimate),
/* harmony export */   getQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.getQueryParam),
/* harmony export */   hasQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.hasQueryParam),
/* harmony export */   hide: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.hide),
/* harmony export */   hideElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.hideElements),
/* harmony export */   img: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.img),
/* harmony export */   isEmptyObject: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isEmptyObject),
/* harmony export */   isHTMLElement: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isHTMLElement),
/* harmony export */   isValidEmail: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isValidEmail),
/* harmony export */   isVisible: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.isVisible),
/* harmony export */   maybeCreateModal: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.maybeCreateModal),
/* harmony export */   onClickPreventDefault: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault),
/* harmony export */   p: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.p),
/* harmony export */   removeParamFromHistory: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.removeParamFromHistory),
/* harmony export */   removeQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.removeQueryParam),
/* harmony export */   setQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.setQueryParam),
/* harmony export */   show: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.show),
/* harmony export */   showElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.showElements),
/* harmony export */   showFormError: () => (/* reexport safe */ _error__WEBPACK_IMPORTED_MODULE_2__.showFormError),
/* harmony export */   span: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.span),
/* harmony export */   svg: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.svg),
/* harmony export */   tag: () => (/* reexport safe */ _globalModules__WEBPACK_IMPORTED_MODULE_3__.tag)
/* harmony export */ });
/* harmony import */ var _animation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./animation */ "./js/src/core/utils/animation.js");
/* harmony import */ var _async__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./async */ "./js/src/core/utils/async.js");
/* harmony import */ var _error__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./error */ "./js/src/core/utils/error.js");
/* harmony import */ var _globalModules__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./globalModules */ "./js/src/core/utils/globalModules.js");
/* harmony import */ var _url__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./url */ "./js/src/core/utils/url.js");
/* harmony import */ var _validation__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./validation */ "./js/src/core/utils/validation.js");
/* harmony import */ var _visibility__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./visibility */ "./js/src/core/utils/visibility.js");








/***/ }),

/***/ "./js/src/core/utils/url.js":
/*!**********************************!*\
  !*** ./js/src/core/utils/url.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getQueryParam: () => (/* binding */ getQueryParam),
/* harmony export */   hasQueryParam: () => (/* binding */ hasQueryParam),
/* harmony export */   removeParamFromHistory: () => (/* binding */ removeParamFromHistory),
/* harmony export */   removeQueryParam: () => (/* binding */ removeQueryParam),
/* harmony export */   setQueryParam: () => (/* binding */ setQueryParam)
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

/**
 * Removes a query parameter and updates history with replaceState.
 *
 * @param {string} paramName The query parameter to remove.
 * @return {void}
 */
var removeParamFromHistory = function removeParamFromHistory(paramName) {
  return history.replaceState({}, '', removeQueryParam(paramName));
};

/***/ }),

/***/ "./js/src/core/utils/validation.js":
/*!*****************************************!*\
  !*** ./js/src/core/utils/validation.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isEmptyObject: () => (/* binding */ isEmptyObject),
/* harmony export */   isHTMLElement: () => (/* binding */ isHTMLElement),
/* harmony export */   isValidEmail: () => (/* binding */ isValidEmail)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hide: () => (/* binding */ hide),
/* harmony export */   hideElements: () => (/* binding */ hideElements),
/* harmony export */   isVisible: () => (/* binding */ isVisible),
/* harmony export */   show: () => (/* binding */ show),
/* harmony export */   showElements: () => (/* binding */ showElements)
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

/***/ "./js/src/form-templates/elements/applicationTemplatesElement.js":
/*!***********************************************************************!*\
  !*** ./js/src/form-templates/elements/applicationTemplatesElement.js ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplatesElement: () => (/* binding */ addApplicationTemplatesElement),
/* harmony export */   createApplicationTemplates: () => (/* binding */ createApplicationTemplates)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./elements */ "./js/src/form-templates/elements/elements.js");
/**
 * WordPress dependencies
 */


/**
 * External dependencies
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
var thumbnailBaseURL = "".concat(core_constants__WEBPACK_IMPORTED_MODULE_1__.PLUGIN_URL, "/images/applications/thumbnails");

/**
 * Create and return the application templates HTML element.
 *
 * @param {Object[]} applications Array of application objects.
 * @return {void}
 */
function createApplicationTemplates(applications) {
  if (!applications || !applications.length) {
    return;
  }
  var templateItems = applications.map(function (template) {
    return createTemplateItem(template);
  });
  applicationTemplates = div({
    id: "".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-applications"),
    className: core_constants__WEBPACK_IMPORTED_MODULE_1__.HIDDEN_CLASS,
    children: [tag('h2', {
      text: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Application Templates'),
      className: 'frm-text-sm frm-mb-sm'
    }), tag('ul', {
      className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-list frm-list-grid-layout"),
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
    hasLiteThumbnail = template.hasLiteThumbnail,
    isWebp = template.isWebp;
  // eslint-disable-next-line no-nested-ternary
  var thumbnailURL = hasLiteThumbnail ? isWebp ? "".concat(thumbnailBaseURL, "/").concat(key, ".webp") : "".concat(thumbnailBaseURL, "/").concat(key, ".png") : "".concat(thumbnailBaseURL, "/placeholder.svg");
  return tag('li', {
    className: 'frm-card-item',
    data: {
      href: "".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.applicationsUrl, "&triggerViewApplicationModal=1&template=").concat(key),
      'frm-search-text': name.toLowerCase()
    },
    children: [div({
      className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-item-icon"),
      child: img({
        src: thumbnailURL
      })
    }), div({
      className: "".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-item-body"),
      children: [span({
        text: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Ready Made Solution', 'formidable'),
        className: 'frm-meta-tag frm-orange-tag frm-text-xs'
      }), tag('h3', {
        text: name,
        className: 'frm-text-sm frm-font-medium frm-m-0'
      }), a({
        text: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('See all applications', 'formidable'),
        className: 'frm-text-xs frm-font-semibold',
        href: _shared__WEBPACK_IMPORTED_MODULE_3__.applicationsUrl
      })]
    })]
  });
}

/**
 * Inject application Templates elements into the DOM and the elements object.
 *
 * @return {void}
 */
function addApplicationTemplatesElement() {
  var elements = (0,_elements__WEBPACK_IMPORTED_MODULE_4__.getElements)();
  if (elements.applicationTemplates || undefined === applicationTemplates) {
    return;
  }
  elements.bodyContent.appendChild(applicationTemplates);
  (0,_elements__WEBPACK_IMPORTED_MODULE_4__.addElements)({
    applicationTemplates: applicationTemplates,
    applicationTemplatesTitle: applicationTemplates.querySelector('h2'),
    applicationTemplatesList: applicationTemplates.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-list")),
    applicationTemplateItems: applicationTemplates.querySelectorAll('.frm-card-item')
  });
}

/***/ }),

/***/ "./js/src/form-templates/elements/elements.js":
/*!****************************************************!*\
  !*** ./js/src/form-templates/elements/elements.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* reexport safe */ core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.addElements),
/* harmony export */   getElements: () => (/* reexport safe */ core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.getElements)
/* harmony export */ });
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
var _document$getElementB, _document$getElementB2;
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */

var _getElements = (0,core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
  bodyContent = _getElements.bodyContent;
var templatesList = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-list"));
var customTemplatesSection = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-custom-list-section"));
var favoritesCategory = document.querySelector(".".concat(core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat[data-category=\"").concat(_shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.FAVORITES, "\"]"));
var modal = document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-modal"));
(0,core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.addElements)({
  // Body elements
  headerCancelButton: (_document$getElementB = document.getElementById('frm-publishing')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.querySelector('a'),
  createFormButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-create-form")),
  pageTitle: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-page-title")),
  pageTitleText: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-page-title-text")),
  pageTitleDivider: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-page-title-divider")),
  upsellBanner: (_document$getElementB2 = document.getElementById('frm-renew-subscription-banner')) !== null && _document$getElementB2 !== void 0 ? _document$getElementB2 : document.getElementById('frm-upgrade-banner'),
  extraTemplateCountElements: document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-extra-templates-count")),
  // Templates elements
  templatesList: templatesList,
  templateItems: templatesList.querySelectorAll('.frm-card-item'),
  availableTemplateItems: templatesList.querySelectorAll(".frm-card-item:not(.".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-locked-item)")),
  twinFeaturedTemplateItems: templatesList.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-featured-item")),
  featuredTemplatesList: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-featured-list")),
  // Custom Templates Section elements
  customTemplatesSection: customTemplatesSection,
  customTemplateItems: customTemplatesSection.querySelectorAll('.frm-card-item'),
  customTemplatesTitle: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-custom-list-title")),
  customTemplatesList: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-custom-list")),
  // Sidebar elements
  favoritesCategory: favoritesCategory,
  favoritesCategoryCountEl: favoritesCategory === null || favoritesCategory === void 0 ? void 0 : favoritesCategory.querySelector(".".concat(core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-count")),
  availableTemplatesCategory: document.querySelector(".".concat(core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat[data-category=\"").concat(_shared__WEBPACK_IMPORTED_MODULE_1__.VIEW_SLUGS.AVAILABLE_TEMPLATES, "\"]")),
  getFreeTemplatesBannerButton: document.querySelector('.frm-get-free-templates-banner .button'),
  // Modal elements
  modal: modal,
  modalItems: modal === null || modal === void 0 ? void 0 : modal.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-modal-item")),
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
  leaveEmailModalButton: document.getElementById('frm-get-code-button'),
  // Upgrade Modal
  upgradeModal: document.getElementById('frm-form-upgrade-modal'),
  upgradeModalTemplateNames: modal === null || modal === void 0 ? void 0 : modal.querySelectorAll('.frm-upgrade-modal-template-name'),
  upgradeModalPlansIcons: modal === null || modal === void 0 ? void 0 : modal.querySelectorAll('.frm-upgrade-modal-plan-icon'),
  upgradeModalLink: document.getElementById('frm-upgrade-modal-link'),
  // New Template Form elements
  newTemplateForm: document.getElementById('frm-new-template'),
  newTemplateNameInput: document.getElementById('frm_template_name'),
  newTemplateDescriptionInput: document.getElementById('frm_template_desc'),
  newTemplateLinkInput: document.getElementById('frm_link'),
  newTemplateActionInput: document.getElementById('frm_action_type'),
  // Add children of the bodyContent to the elements object.
  bodyContentChildren: bodyContent === null || bodyContent === void 0 ? void 0 : bodyContent.children
});


/***/ }),

/***/ "./js/src/form-templates/elements/index.js":
/*!*************************************************!*\
  !*** ./js/src/form-templates/elements/index.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplatesElement: () => (/* reexport safe */ _applicationTemplatesElement__WEBPACK_IMPORTED_MODULE_1__.addApplicationTemplatesElement),
/* harmony export */   addElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.addElements),
/* harmony export */   createApplicationTemplates: () => (/* reexport safe */ _applicationTemplatesElement__WEBPACK_IMPORTED_MODULE_1__.createApplicationTemplates),
/* harmony export */   getElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.getElements)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./elements */ "./js/src/form-templates/elements/elements.js");
/* harmony import */ var _applicationTemplatesElement__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./applicationTemplatesElement */ "./js/src/form-templates/elements/applicationTemplatesElement.js");



/***/ }),

/***/ "./js/src/form-templates/events/applicationTemplateListener.js":
/*!*********************************************************************!*\
  !*** ./js/src/form-templates/events/applicationTemplateListener.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplateEvents: () => (/* binding */ addApplicationTemplateEvents)
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

/***/ "./js/src/form-templates/events/createFormButtonListener.js":
/*!******************************************************************!*\
  !*** ./js/src/form-templates/events/createFormButtonListener.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addCreateFormButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/createTemplateListeners.js":
/*!*****************************************************************!*\
  !*** ./js/src/form-templates/events/createTemplateListeners.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addCreateTemplateEvents);

/***/ }),

/***/ "./js/src/form-templates/events/favoriteButtonListener.js":
/*!****************************************************************!*\
  !*** ./js/src/form-templates/events/favoriteButtonListener.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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
    isTemplateCustom ? ++favoritesCount.custom : ++favoritesCount.default; // eslint-disable-line no-unused-expressions
    // Set heart icon to filled
    heartIcon.setAttribute('href', FILLED_HEART_ICON);
    twinTemplateHeartIcon === null || twinTemplateHeartIcon === void 0 || twinTemplateHeartIcon.setAttribute('href', FILLED_HEART_ICON);
  } else {
    // Decrement favorite counts
    --favoritesCount.total;
    isTemplateCustom ? --favoritesCount.custom : --favoritesCount.default; // eslint-disable-line no-unused-expressions
    // Set heart icon to outline
    heartIcon.setAttribute('href', LINEAR_HEART_ICON);
    twinTemplateHeartIcon === null || twinTemplateHeartIcon === void 0 || twinTemplateHeartIcon.setAttribute('href', LINEAR_HEART_ICON);
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
    (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(template);
    if (0 === favoritesCount.default) {
      (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(templatesList);
    }
    if (0 === favoritesCount.custom || 0 === favoritesCount.default) {
      (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(customTemplatesTitle);
    }
  }

  // Update server-side data for favorite templates
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.addToRequestQueue)(function () {
    return updateFavoriteTemplate(templateId, currentOperation, isTemplateCustom);
  });
};

/**
 * Update server-side data for favorite templates.
 *
 * @param {string}  id        The template ID.
 * @param {string}  operation The operation to perform ('add' or 'remove').
 * @param {boolean} isCustom  Flag indicating whether the template is custom.
 * @return {Promise<any>} The result of the server-side update.
 */
function updateFavoriteTemplate(id, operation, isCustom) {
  var formData = new FormData();
  var doJsonPost = frmDom.ajax.doJsonPost;
  formData.append('template_id', id);
  formData.append('operation', operation);
  formData.append('is_custom_template', isCustom);
  return doJsonPost('add_or_remove_favorite_template', formData);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addFavoriteButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/events/getFreeTemplatesListener.js":
/*!******************************************************************!*\
  !*** ./js/src/form-templates/events/getFreeTemplatesListener.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * External dependencies
 */

var tag = window.frmDom.tag;

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Manages event handling for the "Get Templates" button.
 *
 * @return {void}
 */
function addGetFreeTemplatesEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    leaveEmailModalButton = _getElements.leaveEmailModalButton,
    getFreeTemplatesBannerButton = _getElements.getFreeTemplatesBannerButton;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(leaveEmailModalButton, onGetTemplatesButtonClick);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(getFreeTemplatesBannerButton, _ui__WEBPACK_IMPORTED_MODULE_3__.showLeaveEmailModal);
}

/**
 * Handles the click event on the "Get Templates" button.
 *
 * @private
 * @return {void}
 */
var onGetTemplatesButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var _getElements2, leaveEmailModalInput, email, _getElements3, leaveEmailModalButton, formData, data, doJsonPost;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(), leaveEmailModalInput = _getElements2.leaveEmailModalInput;
          email = leaveEmailModalInput.value.trim(); // Check if the email field is empty
          if (email) {
            _context.next = 5;
            break;
          }
          (0,_ui__WEBPACK_IMPORTED_MODULE_3__.showEmailAddressError)('empty');
          return _context.abrupt("return");
        case 5:
          if ((0,core_utils__WEBPACK_IMPORTED_MODULE_0__.isValidEmail)(email)) {
            _context.next = 8;
            break;
          }
          (0,_ui__WEBPACK_IMPORTED_MODULE_3__.showEmailAddressError)('invalid');
          return _context.abrupt("return");
        case 8:
          // Disable the button
          _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(), leaveEmailModalButton = _getElements3.leaveEmailModalButton;
          leaveEmailModalButton.style.setProperty('cursor', 'not-allowed');
          leaveEmailModalButton.classList.add('frm_loading_button');
          formData = new FormData();
          formData.append('email', email);
          doJsonPost = frmDom.ajax.doJsonPost;
          _context.prev = 14;
          _context.next = 17;
          return doJsonPost('get_free_templates', formData);
        case 17:
          data = _context.sent;
          _context.next = 25;
          break;
        case 20:
          _context.prev = 20;
          _context.t0 = _context["catch"](14);
          console.error('An error occurred:', _context.t0);
          showFailedToGetTemplates();
          return _context.abrupt("return");
        case 25:
          if (data.success) {
            _context.next = 28;
            break;
          }
          showFailedToGetTemplates();
          return _context.abrupt("return");
        case 28:
          if ((0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hasQueryParam)('free-templates')) {
            (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.removeQueryParam)('free-templates');
          }
          (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.setQueryParam)('registered-for-free-templates', '1');
          window.location.reload();
        case 31:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[14, 20]]);
  }));
  return function onGetTemplatesButtonClick() {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Shows a message indicating that templates could not be retrieved.
 *
 * @private
 * @return {void}
 */
function showFailedToGetTemplates() {
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    leaveEmailModal = _getElements4.leaveEmailModal;
  leaveEmailModal.querySelector('.inside').replaceChildren(tag('p', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Failed to get templates, please try again later.', 'formidable')));
  leaveEmailModal.querySelector('.frm_modal_footer').classList.add('frm_hidden');
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addGetFreeTemplatesEvents);

/***/ }),

/***/ "./js/src/form-templates/events/index.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/events/index.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addApplicationTemplateEvents: () => (/* reexport safe */ _applicationTemplateListener__WEBPACK_IMPORTED_MODULE_8__.addApplicationTemplateEvents),
/* harmony export */   addEventListeners: () => (/* binding */ addEventListeners)
/* harmony export */ });
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _createFormButtonListener__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./createFormButtonListener */ "./js/src/form-templates/events/createFormButtonListener.js");
/* harmony import */ var _favoriteButtonListener__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./favoriteButtonListener */ "./js/src/form-templates/events/favoriteButtonListener.js");
/* harmony import */ var _useTemplateButtonListener__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./useTemplateButtonListener */ "./js/src/form-templates/events/useTemplateButtonListener.js");
/* harmony import */ var _searchListener__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./searchListener */ "./js/src/form-templates/events/searchListener.js");
/* harmony import */ var _createTemplateListeners__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./createTemplateListeners */ "./js/src/form-templates/events/createTemplateListeners.js");
/* harmony import */ var _getFreeTemplatesListener__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./getFreeTemplatesListener */ "./js/src/form-templates/events/getFreeTemplatesListener.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _applicationTemplateListener__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./applicationTemplateListener */ "./js/src/form-templates/events/applicationTemplateListener.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */








/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
function addEventListeners() {
  (0,core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.addCategoryEvents)();
  wp.hooks.addAction('frmPage.onCategoryClick', 'frmFormTemplates', function (selectedCategory) {
    // Display templates of the selected category
    (0,_ui__WEBPACK_IMPORTED_MODULE_7__.showSelectedCategory)(selectedCategory);
  });
  (0,_createFormButtonListener__WEBPACK_IMPORTED_MODULE_1__["default"])();
  (0,_favoriteButtonListener__WEBPACK_IMPORTED_MODULE_2__["default"])();
  (0,_useTemplateButtonListener__WEBPACK_IMPORTED_MODULE_3__["default"])();
  (0,_searchListener__WEBPACK_IMPORTED_MODULE_4__["default"])();
  (0,_createTemplateListeners__WEBPACK_IMPORTED_MODULE_5__["default"])();
  (0,_getFreeTemplatesListener__WEBPACK_IMPORTED_MODULE_6__["default"])();
}


/***/ }),

/***/ "./js/src/form-templates/events/searchListener.js":
/*!********************************************************!*\
  !*** ./js/src/form-templates/events/searchListener.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../ui */ "./js/src/form-templates/ui/index.js");
/**
 * External dependencies
 */



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
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    searchInput = _getElements.searchInput,
    emptyStateButton = _getElements.emptyStateButton;
  initSearch(searchInput, 'frm-card-item', {
    handleSearchResult: handleSearchResult
  });
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(emptyStateButton, onEmptyStateButtonClick);
}

/**
 * Manages UI state based on search results and input value.
 *
 * @private
 * @param {Object}  args                    Contains flags for search status.
 * @param {boolean} args.foundSomething     True if search yielded results.
 * @param {boolean} args.notEmptySearchText True if search input is not empty.
 * @param {Event}   event                   The event object (input, search, or change event).
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
  var state = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)();
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    allItemsCategory = _getElements2.allItemsCategory;
  (0,_shared__WEBPACK_IMPORTED_MODULE_3__.setSingleState)('notEmptySearchText', notEmptySearchText);

  // Revert to 'All Templates' if search and selected category are both empty
  if (!state.notEmptySearchText && !state.selectedCategory) {
    allItemsCategory.dispatchEvent(new Event('click', {
      bubbles: true
    }));
    return;
  }

  // Display search state if a category is selected
  if (state.selectedCategory) {
    (0,_ui__WEBPACK_IMPORTED_MODULE_4__.showSearchState)(notEmptySearchText);

    // Setting "selectedCategory" to an empty string as a flag for search state
    if (notEmptySearchText) {
      (0,_shared__WEBPACK_IMPORTED_MODULE_3__.setSingleState)('selectedCategory', '');
    }
  }
  (0,_ui__WEBPACK_IMPORTED_MODULE_4__.displaySearchElements)(foundSomething, notEmptySearchText);
}

/**
 * Handles the click event on the empty state button.
 *
 * @private
 * @return {void}
 */
var onEmptyStateButtonClick = function onEmptyStateButtonClick() {
  var _emptyState$dataset;
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    emptyState = _getElements3.emptyState;
  if (_shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.SEARCH !== ((_emptyState$dataset = emptyState.dataset) === null || _emptyState$dataset === void 0 ? void 0 : _emptyState$dataset.view)) {
    return;
  }

  // Set selectedCategory to '' as search state flag that triggers ALL_ITEMS category if search input is empty
  // @see handleSearchResult()
  (0,_shared__WEBPACK_IMPORTED_MODULE_3__.setSingleState)('selectedCategory', '');
  (0,core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__.resetSearchInput)();
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    searchInput = _getElements4.searchInput;
  searchInput.focus();
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addSearchEvents);

/***/ }),

/***/ "./js/src/form-templates/events/useTemplateButtonListener.js":
/*!*******************************************************************!*\
  !*** ./js/src/form-templates/events/useTemplateButtonListener.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addUseTemplateButtonEvents);

/***/ }),

/***/ "./js/src/form-templates/initializeFormTemplates.js":
/*!**********************************************************!*\
  !*** ./js/src/form-templates/initializeFormTemplates.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ui */ "./js/src/form-templates/ui/index.js");
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./templates */ "./js/src/form-templates/templates/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./events */ "./js/src/form-templates/events/index.js");
/**
 * Internal dependencies
 */




/**
 * Initializes form templates.
 *
 * @return {void}
 */
function initializeFormTemplates() {
  (0,_templates__WEBPACK_IMPORTED_MODULE_1__.maybeAddApplicationTemplates)();
  (0,_ui__WEBPACK_IMPORTED_MODULE_0__.initializeModal)();
  (0,_templates__WEBPACK_IMPORTED_MODULE_1__.buildCategorizedTemplates)();
  (0,_ui__WEBPACK_IMPORTED_MODULE_0__.setupInitialView)();
  (0,_events__WEBPACK_IMPORTED_MODULE_2__.addEventListeners)();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (initializeFormTemplates);

/***/ }),

/***/ "./js/src/form-templates/shared/constants.js":
/*!***************************************************!*\
  !*** ./js/src/form-templates/shared/constants.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FEATURED_TEMPLATES_IDS: () => (/* binding */ FEATURED_TEMPLATES_IDS),
/* harmony export */   FREE_TEMPLATES_IDS: () => (/* binding */ FREE_TEMPLATES_IDS),
/* harmony export */   MODAL_SIZES: () => (/* binding */ MODAL_SIZES),
/* harmony export */   PLANS: () => (/* binding */ PLANS),
/* harmony export */   PREFIX: () => (/* binding */ PREFIX),
/* harmony export */   VIEW_SLUGS: () => (/* binding */ VIEW_SLUGS),
/* harmony export */   applicationsUrl: () => (/* binding */ applicationsUrl),
/* harmony export */   canAccessApplicationDashboard: () => (/* binding */ canAccessApplicationDashboard),
/* harmony export */   upgradeLink: () => (/* binding */ upgradeLink)
/* harmony export */ });
var _window$frmGlobal = window.frmGlobal,
  canAccessApplicationDashboard = _window$frmGlobal.canAccessApplicationDashboard,
  applicationsUrl = _window$frmGlobal.applicationsUrl;

var _window$frmFormTempla = window.frmFormTemplatesVars,
  FEATURED_TEMPLATES_IDS = _window$frmFormTempla.FEATURED_TEMPLATES_IDS,
  FREE_TEMPLATES_IDS = _window$frmFormTempla.FREE_TEMPLATES_IDS,
  upgradeLink = _window$frmFormTempla.upgradeLink;

var PREFIX = 'frm-form-templates';
var VIEW_SLUGS = {
  AVAILABLE_TEMPLATES: 'available-templates',
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FEATURED_TEMPLATES_IDS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.FEATURED_TEMPLATES_IDS),
/* harmony export */   FREE_TEMPLATES_IDS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.FREE_TEMPLATES_IDS),
/* harmony export */   MODAL_SIZES: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.MODAL_SIZES),
/* harmony export */   PLANS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PLANS),
/* harmony export */   PREFIX: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PREFIX),
/* harmony export */   VIEW_SLUGS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.VIEW_SLUGS),
/* harmony export */   applicationsUrl: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.applicationsUrl),
/* harmony export */   canAccessApplicationDashboard: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.canAccessApplicationDashboard),
/* harmony export */   getSingleState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.getSingleState),
/* harmony export */   getState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.getState),
/* harmony export */   setSingleState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.setSingleState),
/* harmony export */   setState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.setState),
/* harmony export */   upgradeLink: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.upgradeLink)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/form-templates/shared/constants.js");
/* harmony import */ var _pageState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pageState */ "./js/src/form-templates/shared/pageState.js");



/***/ }),

/***/ "./js/src/form-templates/shared/pageState.js":
/*!***************************************************!*\
  !*** ./js/src/form-templates/shared/pageState.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getSingleState: () => (/* reexport safe */ core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.getSingleState),
/* harmony export */   getState: () => (/* reexport safe */ core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.getState),
/* harmony export */   setSingleState: () => (/* reexport safe */ core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.setSingleState),
/* harmony export */   setState: () => (/* reexport safe */ core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.setState)
/* harmony export */ });
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
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
  availableTemplateItems = _getElements.availableTemplateItems;
var availableTemplatesCount = availableTemplateItems.length;
(0,core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.setState)({
  availableTemplatesCount: availableTemplatesCount,
  customCount: Number(customCount),
  extraTemplatesCount: templatesCount - availableTemplatesCount,
  favoritesCount: favoritesCount
});


/***/ }),

/***/ "./js/src/form-templates/templates/applicationTemplates.js":
/*!*****************************************************************!*\
  !*** ./js/src/form-templates/templates/applicationTemplates.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   maybeAddApplicationTemplates: () => (/* binding */ maybeAddApplicationTemplates)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   buildCategorizedTemplates: () => (/* binding */ buildCategorizedTemplates),
/* harmony export */   categorizedTemplates: () => (/* binding */ categorizedTemplates)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   buildCategorizedTemplates: () => (/* reexport safe */ _categorizeTemplates__WEBPACK_IMPORTED_MODULE_0__.buildCategorizedTemplates),
/* harmony export */   categorizedTemplates: () => (/* reexport safe */ _categorizeTemplates__WEBPACK_IMPORTED_MODULE_0__.categorizedTemplates),
/* harmony export */   maybeAddApplicationTemplates: () => (/* reexport safe */ _applicationTemplates__WEBPACK_IMPORTED_MODULE_1__.maybeAddApplicationTemplates)
/* harmony export */ });
/* harmony import */ var _categorizeTemplates__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./categorizeTemplates */ "./js/src/form-templates/templates/categorizeTemplates.js");
/* harmony import */ var _applicationTemplates__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./applicationTemplates */ "./js/src/form-templates/templates/applicationTemplates.js");



/***/ }),

/***/ "./js/src/form-templates/ui/index.js":
/*!*******************************************!*\
  !*** ./js/src/form-templates/ui/index.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   displaySearchElements: () => (/* reexport safe */ _searchState__WEBPACK_IMPORTED_MODULE_5__.displaySearchElements),
/* harmony export */   getModalWidget: () => (/* reexport safe */ _initializeModal__WEBPACK_IMPORTED_MODULE_1__.getModalWidget),
/* harmony export */   initializeModal: () => (/* reexport safe */ _initializeModal__WEBPACK_IMPORTED_MODULE_1__.initializeModal),
/* harmony export */   setupInitialView: () => (/* reexport safe */ _setupInitialView__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   showAllTemplates: () => (/* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showAllTemplates),
/* harmony export */   showAvailableTemplates: () => (/* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showAvailableTemplates),
/* harmony export */   showAvailableTemplatesEmptyState: () => (/* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showAvailableTemplatesEmptyState),
/* harmony export */   showCreateTemplateModal: () => (/* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showCreateTemplateModal),
/* harmony export */   showCustomTemplates: () => (/* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showCustomTemplates),
/* harmony export */   showCustomTemplatesEmptyState: () => (/* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showCustomTemplatesEmptyState),
/* harmony export */   showEmailAddressError: () => (/* reexport safe */ _showError__WEBPACK_IMPORTED_MODULE_8__.showEmailAddressError),
/* harmony export */   showFavoriteTemplates: () => (/* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showFavoriteTemplates),
/* harmony export */   showFavoritesEmptyState: () => (/* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showFavoritesEmptyState),
/* harmony export */   showHeaderCancelButton: () => (/* reexport safe */ _showHeaderCancelButton__WEBPACK_IMPORTED_MODULE_3__.showHeaderCancelButton),
/* harmony export */   showLeaveEmailModal: () => (/* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showLeaveEmailModal),
/* harmony export */   showLockedTemplateModal: () => (/* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showLockedTemplateModal),
/* harmony export */   showRenewAccountModal: () => (/* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showRenewAccountModal),
/* harmony export */   showSearchEmptyState: () => (/* reexport safe */ _showEmptyState__WEBPACK_IMPORTED_MODULE_6__.showSearchEmptyState),
/* harmony export */   showSearchState: () => (/* reexport safe */ _searchState__WEBPACK_IMPORTED_MODULE_5__.showSearchState),
/* harmony export */   showSelectedCategory: () => (/* reexport safe */ _showSelectedCategory__WEBPACK_IMPORTED_MODULE_4__.showSelectedCategory),
/* harmony export */   showUpgradeModal: () => (/* reexport safe */ _showModal__WEBPACK_IMPORTED_MODULE_7__.showUpgradeModal),
/* harmony export */   updatePageTitle: () => (/* reexport safe */ _pageTitle__WEBPACK_IMPORTED_MODULE_2__.updatePageTitle)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getModalWidget: () => (/* binding */ getModalWidget),
/* harmony export */   initializeModal: () => (/* binding */ initializeModal)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
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
  _initializeModal = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var _window$frmAdminBuild, initModal, offsetModalY, _getElements, leaveEmailModal;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _window$frmAdminBuild = window.frmAdminBuild, initModal = _window$frmAdminBuild.initModal, offsetModalY = _window$frmAdminBuild.offsetModalY;
          modalWidget = initModal('#frm-form-templates-modal', _shared__WEBPACK_IMPORTED_MODULE_2__.MODAL_SIZES.GENERAL);

          // Set the vertical offset for the modal
          if (modalWidget) {
            offsetModalY(modalWidget, '103px');
          }

          // Show the email modal if the 'free-templates' query param is present
          if ((0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hasQueryParam)('free-templates')) {
            _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), leaveEmailModal = _getElements.leaveEmailModal;
            if (leaveEmailModal) {
              (0,___WEBPACK_IMPORTED_MODULE_3__.showLeaveEmailModal)();
            }
          }

          // Customize the confirm modal appearance: adjusting its width and vertical position
          wp.hooks.addAction('frmAdmin.beforeOpenConfirmModal', 'frmFormTemplates', function (options) {
            var confirmModal = options.$info;
            confirmModal.dialog('option', 'width', _shared__WEBPACK_IMPORTED_MODULE_2__.MODAL_SIZES.CREATE_TEMPLATE);
            offsetModalY(confirmModal, '103px');
          });
        case 5:
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

/***/ }),

/***/ "./js/src/form-templates/ui/pageTitle.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/ui/pageTitle.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   updatePageTitle: () => (/* binding */ updatePageTitle)
/* harmony export */ });
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/**
 * External dependencies
 */


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
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    pageTitleText = _getElements.pageTitleText;
  var newTitle = title || (0,_shared__WEBPACK_IMPORTED_MODULE_2__.getSingleState)('selectedCategoryEl').querySelector(".".concat(core_page_skeleton__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-cat-text")).textContent;
  pageTitleText.textContent = newTitle;
}

/***/ }),

/***/ "./js/src/form-templates/ui/searchState.js":
/*!*************************************************!*\
  !*** ./js/src/form-templates/ui/searchState.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   displaySearchElements: () => (/* binding */ displaySearchElements),
/* harmony export */   showSearchState: () => (/* binding */ showSearchState)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
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
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_3__.getElements)(),
    bodyContent = _getElements.bodyContent,
    bodyContentChildren = _getElements.bodyContentChildren,
    pageTitle = _getElements.pageTitle,
    templatesList = _getElements.templatesList,
    applicationTemplates = _getElements.applicationTemplates;
  var bodyContentAnimate = new core_utils__WEBPACK_IMPORTED_MODULE_2__.frmAnimate(bodyContent);

  // Remove highlighting from the currently selected category if the search text is not empty
  if (notEmptySearchText) {
    (0,_shared__WEBPACK_IMPORTED_MODULE_4__.getSingleState)('selectedCategoryEl').classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
  }

  // Hide non-relevant elements in the body content
  (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)(bodyContentChildren);

  // Update the page title and display relevant elements
  (0,___WEBPACK_IMPORTED_MODULE_5__.updatePageTitle)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Search Result', 'formidable'));
  (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([pageTitle, templatesList, applicationTemplates]);

  // Smoothly display the updated UI elements
  bodyContentAnimate.fadeIn();
}

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
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_3__.getElements)(),
    emptyState = _getElements2.emptyState;
  if ((0,core_utils__WEBPACK_IMPORTED_MODULE_2__.isVisible)(emptyState)) {
    var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_3__.getElements)(),
      pageTitle = _getElements3.pageTitle;
    (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(emptyState);
    (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.show)(pageTitle);
  }
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_3__.getElements)(),
    templatesList = _getElements4.templatesList,
    applicationTemplates = _getElements4.applicationTemplates,
    applicationTemplatesTitle = _getElements4.applicationTemplatesTitle,
    applicationTemplatesList = _getElements4.applicationTemplatesList;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.showElements)([templatesList, applicationTemplates, applicationTemplatesTitle]);
  if (templatesList.offsetHeight === 0) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.hideElements)([templatesList, applicationTemplatesTitle]);
  }
  if ((applicationTemplatesList === null || applicationTemplatesList === void 0 ? void 0 : applicationTemplatesList.offsetHeight) === 0) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(applicationTemplates);
  }
}

/***/ }),

/***/ "./js/src/form-templates/ui/setupInitialView.js":
/*!******************************************************!*\
  !*** ./js/src/form-templates/ui/setupInitialView.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var core_ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core/ui */ "./js/src/core/ui/index.js");
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
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
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_4__.getElements)(),
    sidebar = _getElements.sidebar,
    searchInput = _getElements.searchInput,
    bodyContent = _getElements.bodyContent,
    twinFeaturedTemplateItems = _getElements.twinFeaturedTemplateItems,
    availableTemplatesCategory = _getElements.availableTemplatesCategory,
    extraTemplateCountElements = _getElements.extraTemplateCountElements;
  var bodyContentAnimate = new core_utils__WEBPACK_IMPORTED_MODULE_1__.frmAnimate(bodyContent);
  searchInput.value = '';

  // Hide the twin featured template items
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hideElements)(twinFeaturedTemplateItems);
  setupAvailableTemplatesCategory(availableTemplatesCategory);

  // Update extra templates count
  extraTemplateCountElements.forEach(function (element) {
    return element.textContent = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.getSingleState)('extraTemplatesCount');
  });

  // Smoothly display the updated UI elements
  bodyContent.classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDE_JS_CLASS);
  sidebar.classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDE_JS_CLASS);
  bodyContentAnimate.fadeIn();

  // Show the "Cancel" button in the header if the 'return_page' query param is present
  if ((0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hasQueryParam)('return_page')) {
    (0,___WEBPACK_IMPORTED_MODULE_6__.showHeaderCancelButton)();
  }
}

/**
 * Sets up the 'Available Templates' category with proper count display
 *
 * @param {Element} availableTemplatesCategory The Available Templates category element
 * @return {void}
 */
function setupAvailableTemplatesCategory(availableTemplatesCategory) {
  if (!availableTemplatesCategory) {
    return;
  }
  var availableTemplatesCount = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.getSingleState)('availableTemplatesCount');
  if (!(0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hasQueryParam)('registered-for-free-templates')) {
    availableTemplatesCategory.querySelector(".".concat(core_page_skeleton__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-cat-count")).textContent = availableTemplatesCount;
    return;
  }
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.removeParamFromHistory)('registered-for-free-templates');
  runAvailableTemplatesEffects(availableTemplatesCategory, availableTemplatesCount);
}

/**
 * Runs effects for the Available Templates category when the
 * 'registered-for-free-templates' query parameter is present.
 *
 * @param {Element} element The Available Templates category element
 * @param {number}  count   The count of available templates
 * @return {void}
 */
function runAvailableTemplatesEffects(element, count) {
  setTimeout(function () {
    element.dispatchEvent(new Event('click', {
      bubbles: true
    }));
  }, 0);
  setTimeout(function () {
    (0,core_ui__WEBPACK_IMPORTED_MODULE_2__.counter)(element.querySelector(".".concat(core_page_skeleton__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-cat-count")), count);
  }, 150);
  setTimeout(function () {
    var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_4__.getElements)(),
      availableTemplateItems = _getElements2.availableTemplateItems;
    availableTemplateItems.forEach(function (item) {
      if (_shared__WEBPACK_IMPORTED_MODULE_5__.FREE_TEMPLATES_IDS.includes(Number(item.dataset.id))) {
        return;
      }
      item.classList.add('frm-background-highlight');

      // Remove class after animation completes to prevent restart
      item.addEventListener('animationend', function handleAnimationEnd(event) {
        if (event.animationName === 'backgroundHighlight') {
          this.classList.remove('frm-background-highlight');
          this.removeEventListener('animationend', handleAnimationEnd);
        }
      });
    });
  }, 750);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (setupInitialView);

/***/ }),

/***/ "./js/src/form-templates/ui/showEmptyState.js":
/*!****************************************************!*\
  !*** ./js/src/form-templates/ui/showEmptyState.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showAvailableTemplatesEmptyState: () => (/* binding */ showAvailableTemplatesEmptyState),
/* harmony export */   showCustomTemplatesEmptyState: () => (/* binding */ showCustomTemplatesEmptyState),
/* harmony export */   showFavoritesEmptyState: () => (/* binding */ showFavoritesEmptyState),
/* harmony export */   showSearchEmptyState: () => (/* binding */ showSearchEmptyState)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
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
 * Display the search-empty state.
 *
 * @return {void}
 */
function showSearchEmptyState() {
  var _emptyState$dataset;
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
    notEmptySearchText = _getState.notEmptySearchText;
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    pageTitle = _getElements.pageTitle,
    emptyState = _getElements.emptyState,
    emptyStateButton = _getElements.emptyStateButton,
    applicationTemplates = _getElements.applicationTemplates;

  // Toggle visibility and remove attributes based on search status
  if (_shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.SEARCH === ((_emptyState$dataset = emptyState.dataset) === null || _emptyState$dataset === void 0 ? void 0 : _emptyState$dataset.view)) {
    if (notEmptySearchText) {
      (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(emptyState);
      (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hideElements)([pageTitle, applicationTemplates]);
    } else {
      (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hide)(emptyState);
      emptyState.removeAttribute('data-view');
    }
    return;
  }

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.SEARCH);

  // Update text content
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    emptyStateTitle = _getElements2.emptyStateTitle,
    emptyStateText = _getElements2.emptyStateText;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No templates found', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Sorry, we didn\'t find any templates that match your criteria.', 'formidable');
  emptyStateButton.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Start from Scratch', 'formidable');

  // Display the empty state
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hideElements)([pageTitle, applicationTemplates]);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.showElements)([emptyState, emptyStateButton]);
}

/**
 * Display the favorites-empty state.
 *
 * @return {void}
 */
function showFavoritesEmptyState() {
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    pageTitle = _getElements3.pageTitle,
    emptyState = _getElements3.emptyState,
    emptyStateButton = _getElements3.emptyStateButton;

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.FAVORITES);

  // Update text content
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    emptyStateTitle = _getElements4.emptyStateTitle,
    emptyStateText = _getElements4.emptyStateText;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No favorites', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You haven\'t added any templates to your favorites yet.', 'formidable');

  // Display the empty state
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hideElements)([pageTitle, emptyStateButton]);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(emptyState);
}

/**
 * Display the custom-empty state.
 *
 * @return {void}
 */
function showCustomTemplatesEmptyState() {
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    pageTitle = _getElements5.pageTitle,
    emptyState = _getElements5.emptyState,
    emptyStateButton = _getElements5.emptyStateButton;

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.CUSTOM);

  // Update text content
  var _getElements6 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    emptyStateTitle = _getElements6.emptyStateTitle,
    emptyStateText = _getElements6.emptyStateText;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You currently have no templates.', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You haven\'t created any form templates. Begin now to simplify your workflow and save time.', 'formidable');
  emptyStateButton.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Create Template', 'formidable');

  // Display the empty state
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hide)(pageTitle);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.showElements)([emptyState, emptyStateButton]);
}

/**
 * Display the available-templates-empty state.
 *
 * @return {void}
 */
function showAvailableTemplatesEmptyState() {
  var _getElements7 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    pageTitle = _getElements7.pageTitle,
    emptyState = _getElements7.emptyState,
    emptyStateButton = _getElements7.emptyStateButton;

  // Assign state attributes
  emptyState.setAttribute('data-view', _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.AVAILABLE_TEMPLATES);

  // Update text content
  var _getElements8 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    emptyStateTitle = _getElements8.emptyStateTitle,
    emptyStateText = _getElements8.emptyStateText;
  var _getState2 = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
    extraTemplatesCount = _getState2.extraTemplatesCount;
  emptyStateTitle.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No Templates Available', 'formidable');
  emptyStateText.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)(
  // translators: %s is the number of extra templates available
  (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Upgrade to PRO for %s+ options or explore Free Templates.', 'formidable'), extraTemplatesCount);

  // Display the empty state
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hideElements)([pageTitle, emptyStateButton]);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(emptyState);
}

/***/ }),

/***/ "./js/src/form-templates/ui/showError.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/ui/showError.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showEmailAddressError: () => (/* binding */ showEmailAddressError)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/**
 * External dependencies
 */


/**
 * Displays errors related to the email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
var showEmailAddressError = function showEmailAddressError(type) {
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.showFormError)('#frm_leave_email', '#frm_leave_email_error', type);
};

/***/ }),

/***/ "./js/src/form-templates/ui/showHeaderCancelButton.js":
/*!************************************************************!*\
  !*** ./js/src/form-templates/ui/showHeaderCancelButton.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showHeaderCancelButton: () => (/* binding */ showHeaderCancelButton)
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

/***/ }),

/***/ "./js/src/form-templates/ui/showModal.js":
/*!***********************************************!*\
  !*** ./js/src/form-templates/ui/showModal.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showCreateTemplateModal: () => (/* binding */ showCreateTemplateModal),
/* harmony export */   showLeaveEmailModal: () => (/* binding */ showLeaveEmailModal),
/* harmony export */   showLockedTemplateModal: () => (/* binding */ showLockedTemplateModal),
/* harmony export */   showRenewAccountModal: () => (/* binding */ showRenewAccountModal),
/* harmony export */   showUpgradeModal: () => (/* binding */ showUpgradeModal)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
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
 * Display the locked template modal.
 *
 * @param {HTMLElement} template The template element.
 * @return {void}
 */
function showLockedTemplateModal(template) {
  var plan = template.dataset.requiredPlan;
  switch (plan) {
    case _shared__WEBPACK_IMPORTED_MODULE_3__.PLANS.BASIC:
    case _shared__WEBPACK_IMPORTED_MODULE_3__.PLANS.PLUS:
    case _shared__WEBPACK_IMPORTED_MODULE_3__.PLANS.BUSINESS:
    case _shared__WEBPACK_IMPORTED_MODULE_3__.PLANS.ELITE:
      showUpgradeModal(plan, template);
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_3__.PLANS.RENEW:
      showRenewAccountModal();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_3__.PLANS.FREE:
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
  return /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
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
          _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(), modalItems = _getElements.modalItems;
          (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hideElements)(modalItems);
          dialogWidget.dialog('option', 'width', _shared__WEBPACK_IMPORTED_MODULE_3__.MODAL_SIZES.GENERAL);
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
 * @param {string}      plan     Current plan name
 * @param {HTMLElement} template The template element
 * @return {void}
 */
var showUpgradeModal = showModal(function (plan, template) {
  var templateName = template.querySelector('.frm-form-template-name').textContent.trim();
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
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
    svg.setAttribute('href', shouldDisplayCheck ? '#frm_checkmark_icon' : '#frm_close_icon');
  });

  // Append template slug to the upgrade modal link URL
  var templateSlug = template.dataset.slug ? "-".concat(template.dataset.slug) : '';
  upgradeModalLink.href = _shared__WEBPACK_IMPORTED_MODULE_3__.upgradeLink + templateSlug;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(upgradeModal);
});

/**
 * Display the modal dialog to prompt the user to renew their account.
 *
 * @return {void}
 */
var showRenewAccountModal = showModal(function () {
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    renewAccountModal = _getElements3.renewAccountModal;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(renewAccountModal);
});

/**
 * Display the modal dialog to prompt the user to leave an email.
 *
 * @return {void}
 */
var showLeaveEmailModal = showModal(function () {
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    leaveEmailModal = _getElements4.leaveEmailModal;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(leaveEmailModal);
});

/**
 * Displays a modal dialog prompting the user to create a new template.
 *
 * @return {void}
 */
var showCreateTemplateModal = showModal(function () {
  var dialogWidget = (0,___WEBPACK_IMPORTED_MODULE_4__.getModalWidget)();
  dialogWidget.dialog('option', 'width', _shared__WEBPACK_IMPORTED_MODULE_3__.MODAL_SIZES.CREATE_TEMPLATE);
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    createTemplateModal = _getElements5.createTemplateModal;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(createTemplateModal);
});

/***/ }),

/***/ "./js/src/form-templates/ui/showSelectedCategory.js":
/*!**********************************************************!*\
  !*** ./js/src/form-templates/ui/showSelectedCategory.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   showAllTemplates: () => (/* binding */ showAllTemplates),
/* harmony export */   showAvailableTemplates: () => (/* binding */ showAvailableTemplates),
/* harmony export */   showCustomTemplates: () => (/* binding */ showCustomTemplates),
/* harmony export */   showFavoriteTemplates: () => (/* binding */ showFavoriteTemplates),
/* harmony export */   showSelectedCategory: () => (/* binding */ showSelectedCategory)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/form-templates/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../utils */ "./js/src/form-templates/utils/index.js");
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../templates */ "./js/src/form-templates/templates/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./ */ "./js/src/form-templates/ui/index.js");
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
/**
 * External dependencies
 */



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
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    bodyContentChildren = _getElements.bodyContentChildren,
    pageTitle = _getElements.pageTitle,
    showCreateTemplateModalButton = _getElements.showCreateTemplateModalButton,
    templatesList = _getElements.templatesList,
    templateItems = _getElements.templateItems,
    upsellBanner = _getElements.upsellBanner;
  if (core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__.VIEWS.ALL_ITEMS !== selectedCategory) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hideElements)(bodyContentChildren);
  }
  (0,___WEBPACK_IMPORTED_MODULE_6__.updatePageTitle)();
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(showCreateTemplateModalButton);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.show)(pageTitle);
  switch (selectedCategory) {
    case core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__.VIEWS.ALL_ITEMS:
      showAllTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.AVAILABLE_TEMPLATES:
      showAvailableTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.FAVORITES:
      showFavoriteTemplates();
      break;
    case _shared__WEBPACK_IMPORTED_MODULE_3__.VIEW_SLUGS.CUSTOM:
      showCustomTemplates();
      break;
    default:
      (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hideElements)(templateItems); // Clear the view for new content
      (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.showElements)([upsellBanner, templatesList].concat(_toConsumableArray(_templates__WEBPACK_IMPORTED_MODULE_5__.categorizedTemplates[selectedCategory])));
      break;
  }
}

/**
 * Shows all templates when 'All Templates' is the selected category.
 *
 * @return {void}
 */
function showAllTemplates() {
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    bodyContentChildren = _getElements2.bodyContentChildren,
    pageTitleDivider = _getElements2.pageTitleDivider,
    templateItems = _getElements2.templateItems,
    twinFeaturedTemplateItems = _getElements2.twinFeaturedTemplateItems,
    customTemplatesSection = _getElements2.customTemplatesSection,
    emptyState = _getElements2.emptyState,
    applicationTemplates = _getElements2.applicationTemplates;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.showElements)([].concat(_toConsumableArray(bodyContentChildren), _toConsumableArray(templateItems)));
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hideElements)([pageTitleDivider].concat(_toConsumableArray(twinFeaturedTemplateItems), [customTemplatesSection, emptyState, applicationTemplates]));
}

/**
 * Shows favorite templates.
 *
 * @return {void}
 */
function showFavoriteTemplates() {
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
    favoritesCount = _getState.favoritesCount;
  if (0 === favoritesCount.total) {
    (0,___WEBPACK_IMPORTED_MODULE_6__.showFavoritesEmptyState)();
    return;
  }
  var _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    bodyContent = _getElements3.bodyContent,
    templatesList = _getElements3.templatesList,
    templateItems = _getElements3.templateItems,
    customTemplatesSection = _getElements3.customTemplatesSection,
    customTemplatesTitle = _getElements3.customTemplatesTitle,
    customTemplatesList = _getElements3.customTemplatesList,
    customTemplateItems = _getElements3.customTemplateItems;

  // Clear the view for new content
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hideElements)(templateItems);
  var elementsToShow = [];

  // Get all favorite items from the DOM and add the elements to show
  var favoriteItems = bodyContent.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_3__.PREFIX, "-favorite-item"));
  elementsToShow.push.apply(elementsToShow, _toConsumableArray(favoriteItems));

  // Add default favorites if available
  if (favoritesCount.default > 0) {
    elementsToShow.push(templatesList);
  }

  // Add custom favorites if available
  if (favoritesCount.custom > 0) {
    var nonFavCustomTemplates = Array.from(customTemplateItems).filter(function (template) {
      return !(0,_utils__WEBPACK_IMPORTED_MODULE_4__.isFavoriteTemplate)(template);
    });
    (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hideElements)(nonFavCustomTemplates);
    elementsToShow.push(customTemplatesSection);
    elementsToShow.push(customTemplatesList);
    if (0 === favoritesCount.default) {
      (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(customTemplatesTitle);
    } else {
      elementsToShow.push(customTemplatesTitle);
    }
  }

  // Show elements that were selected to be shown
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.showElements)(elementsToShow);
}

/**
 * Shows custom templates.
 *
 * @return {void}
 */
function showCustomTemplates() {
  var _getState2 = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
    customCount = _getState2.customCount;
  if (0 === customCount) {
    (0,___WEBPACK_IMPORTED_MODULE_6__.showCustomTemplatesEmptyState)();
    return;
  }
  var _getElements4 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    showCreateTemplateModalButton = _getElements4.showCreateTemplateModalButton,
    pageTitleDivider = _getElements4.pageTitleDivider,
    customTemplatesSection = _getElements4.customTemplatesSection,
    customTemplatesList = _getElements4.customTemplatesList,
    customTemplatesTitle = _getElements4.customTemplatesTitle,
    customTemplateItems = _getElements4.customTemplateItems;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hide)(customTemplatesTitle);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.showElements)([showCreateTemplateModalButton, pageTitleDivider, customTemplatesSection, customTemplatesList].concat(_toConsumableArray(customTemplateItems)));
}

/**
 * Shows available templates.
 *
 * @return {void}
 */
function showAvailableTemplates() {
  var _getState3 = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(),
    availableTemplatesCount = _getState3.availableTemplatesCount;
  if (0 === availableTemplatesCount) {
    (0,___WEBPACK_IMPORTED_MODULE_6__.showAvailableTemplatesEmptyState)();
    return;
  }
  var _getElements5 = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    templatesList = _getElements5.templatesList,
    templateItems = _getElements5.templateItems,
    availableTemplateItems = _getElements5.availableTemplateItems,
    upsellBanner = _getElements5.upsellBanner;
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.hideElements)(templateItems); // Clear the view for new content
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.showElements)([upsellBanner, templatesList].concat(_toConsumableArray(availableTemplateItems)));
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (showSelectedCategory);

/***/ }),

/***/ "./js/src/form-templates/utils/index.js":
/*!**********************************************!*\
  !*** ./js/src/form-templates/utils/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isAllTemplatesCategory: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isAllTemplatesCategory),
/* harmony export */   isCustomCategory: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isCustomCategory),
/* harmony export */   isCustomTemplate: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isCustomTemplate),
/* harmony export */   isFavoriteTemplate: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isFavoriteTemplate),
/* harmony export */   isFavoritesCategory: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isFavoritesCategory),
/* harmony export */   isFeaturedTemplate: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isFeaturedTemplate),
/* harmony export */   isLockedTemplate: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_0__.isLockedTemplate)
/* harmony export */ });
/* harmony import */ var _validation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./validation */ "./js/src/form-templates/utils/validation.js");


/***/ }),

/***/ "./js/src/form-templates/utils/validation.js":
/*!***************************************************!*\
  !*** ./js/src/form-templates/utils/validation.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isAllTemplatesCategory: () => (/* binding */ isAllTemplatesCategory),
/* harmony export */   isCustomCategory: () => (/* binding */ isCustomCategory),
/* harmony export */   isCustomTemplate: () => (/* binding */ isCustomTemplate),
/* harmony export */   isFavoriteTemplate: () => (/* binding */ isFavoriteTemplate),
/* harmony export */   isFavoritesCategory: () => (/* binding */ isFavoritesCategory),
/* harmony export */   isFeaturedTemplate: () => (/* binding */ isFeaturedTemplate),
/* harmony export */   isLockedTemplate: () => (/* binding */ isLockedTemplate)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/page-skeleton */ "./js/src/core/page-skeleton/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/form-templates/shared/index.js");
/**
 * External dependencies
 */



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
  return core_page_skeleton__WEBPACK_IMPORTED_MODULE_1__.VIEWS.ALL_ITEMS === category;
};

/**
 * Checks if the category is "Favorites".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "Favorites", otherwise false.
 */
var isFavoritesCategory = function isFavoritesCategory(category) {
  return _shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.FAVORITES === category;
};

/**
 * Checks if the category is "Custom".
 *
 * @param {string} category The category slug.
 * @return {boolean} True if the category is "Custom", otherwise false.
 */
var isCustomCategory = function isCustomCategory(category) {
  return _shared__WEBPACK_IMPORTED_MODULE_2__.VIEW_SLUGS.CUSTOM === category;
};

/**
 * Checks if a template is a favorite.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is a favorite, otherwise false.
 */
var isFavoriteTemplate = function isFavoriteTemplate(template) {
  return (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.isHTMLElement)(template) ? template.classList.contains("".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-favorite-item")) : false;
};

/**
 * Checks if a template is custom.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is custom, otherwise false.
 */
var isCustomTemplate = function isCustomTemplate(template) {
  return (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.isHTMLElement)(template) ? template.classList.contains("".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-custom-item")) : false;
};

/**
 * Checks if a template is featured.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is featured, otherwise false.
 */
var isFeaturedTemplate = function isFeaturedTemplate(template) {
  return (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.isHTMLElement)(template) ? _shared__WEBPACK_IMPORTED_MODULE_2__.FEATURED_TEMPLATES_IDS.includes(Number(template.dataset.id)) : false;
};

/**
 * Checks if a template is locked.
 *
 * @param {HTMLElement} template The template element.
 * @return {boolean} True if the template is locked, otherwise false.
 */
var isLockedTemplate = function isLockedTemplate(template) {
  return (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.isHTMLElement)(template) ? template.classList.contains("".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-locked-item")) : false;
};

/***/ }),

/***/ "./node_modules/memize/index.js":
/*!**************************************!*\
  !*** ./node_modules/memize/index.js ***!
  \**************************************/
/***/ ((module) => {

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
/***/ ((module, exports, __webpack_require__) => {

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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Tannin)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _arrayLikeToArray)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _arrayWithoutHoles)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _classCallCheck)
/* harmony export */ });
function _classCallCheck(a, n) {
  if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/iterableToArray.js":
/*!**************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/iterableToArray.js ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _iterableToArray)
/* harmony export */ });
function _iterableToArray(r) {
  if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js":
/*!****************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _nonIterableSpread)
/* harmony export */ });
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}


/***/ }),

/***/ "./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/toConsumableArray.js":
/*!****************************************************************************************************!*\
  !*** ./node_modules/@wordpress/hooks/node_modules/@babel/runtime/helpers/esm/toConsumableArray.js ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _toConsumableArray)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _unsupportedIterableToArray)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _defineProperty)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPrimitive)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPropertyKey)
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
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _typeof)
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
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
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
   * @param {HTMLElement} $form The jQuery object containing the hidden form element.
   */
  wp.hooks.doAction('frm_new_form_modal_form', jQuery('#frm-new-template'));
});
})();

/******/ })()
;
//# sourceMappingURL=form-templates.js.map
=======
/*! For license information please see form-templates.js.LICENSE.txt */
(()=>{var t={8616:t=>{t.exports=function(t,e){var n,r,o=0;function a(){var a,i,c=n,l=arguments.length;t:for(;c;){if(c.args.length===arguments.length){for(i=0;i<l;i++)if(c.args[i]!==arguments[i]){c=c.next;continue t}return c!==n&&(c===r&&(r=c.prev),c.prev.next=c.next,c.next&&(c.next.prev=c.prev),c.next=n,c.prev=null,n.prev=c,n=c),c.val}c=c.next}for(a=new Array(l),i=0;i<l;i++)a[i]=arguments[i];return c={args:a,val:t.apply(null,a)},n?(n.prev=c,c.next=n):r=c,o===e.maxSize?(r=r.prev).next=null:o++,n=c,c.val}return e=e||{},a.clear=function(){n=null,r=null,o=0},a}},7604:(t,e,n)=>{var r;!function(){"use strict";var o={not_string:/[^s]/,not_bool:/[^t]/,not_type:/[^T]/,not_primitive:/[^v]/,number:/[diefg]/,numeric_arg:/[bcdiefguxX]/,json:/[j]/,not_json:/[^j]/,text:/^[^\x25]+/,modulo:/^\x25{2}/,placeholder:/^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/,key:/^([a-z_][a-z_\d]*)/i,key_access:/^\.([a-z_][a-z_\d]*)/i,index_access:/^\[(\d+)\]/,sign:/^[+-]/};function a(t){return function(t,e){var n,r,i,c,l,u,s,m,f,p=1,d=t.length,y="";for(r=0;r<d;r++)if("string"==typeof t[r])y+=t[r];else if("object"==typeof t[r]){if((c=t[r]).keys)for(n=e[p],i=0;i<c.keys.length;i++){if(null==n)throw new Error(a('[sprintf] Cannot access property "%s" of undefined value "%s"',c.keys[i],c.keys[i-1]));n=n[c.keys[i]]}else n=c.param_no?e[c.param_no]:e[p++];if(o.not_type.test(c.type)&&o.not_primitive.test(c.type)&&n instanceof Function&&(n=n()),o.numeric_arg.test(c.type)&&"number"!=typeof n&&isNaN(n))throw new TypeError(a("[sprintf] expecting number but found %T",n));switch(o.number.test(c.type)&&(m=n>=0),c.type){case"b":n=parseInt(n,10).toString(2);break;case"c":n=String.fromCharCode(parseInt(n,10));break;case"d":case"i":n=parseInt(n,10);break;case"j":n=JSON.stringify(n,null,c.width?parseInt(c.width):0);break;case"e":n=c.precision?parseFloat(n).toExponential(c.precision):parseFloat(n).toExponential();break;case"f":n=c.precision?parseFloat(n).toFixed(c.precision):parseFloat(n);break;case"g":n=c.precision?String(Number(n.toPrecision(c.precision))):parseFloat(n);break;case"o":n=(parseInt(n,10)>>>0).toString(8);break;case"s":n=String(n),n=c.precision?n.substring(0,c.precision):n;break;case"t":n=String(!!n),n=c.precision?n.substring(0,c.precision):n;break;case"T":n=Object.prototype.toString.call(n).slice(8,-1).toLowerCase(),n=c.precision?n.substring(0,c.precision):n;break;case"u":n=parseInt(n,10)>>>0;break;case"v":n=n.valueOf(),n=c.precision?n.substring(0,c.precision):n;break;case"x":n=(parseInt(n,10)>>>0).toString(16);break;case"X":n=(parseInt(n,10)>>>0).toString(16).toUpperCase()}o.json.test(c.type)?y+=n:(!o.number.test(c.type)||m&&!c.sign?f="":(f=m?"+":"-",n=n.toString().replace(o.sign,"")),u=c.pad_char?"0"===c.pad_char?"0":c.pad_char.charAt(1):" ",s=c.width-(f+n).length,l=c.width&&s>0?u.repeat(s):"",y+=c.align?f+n+l:"0"===u?f+l+n:l+f+n)}return y}(function(t){if(c[t])return c[t];for(var e,n=t,r=[],a=0;n;){if(null!==(e=o.text.exec(n)))r.push(e[0]);else if(null!==(e=o.modulo.exec(n)))r.push("%");else{if(null===(e=o.placeholder.exec(n)))throw new SyntaxError("[sprintf] unexpected placeholder");if(e[2]){a|=1;var i=[],l=e[2],u=[];if(null===(u=o.key.exec(l)))throw new SyntaxError("[sprintf] failed to parse named argument key");for(i.push(u[1]);""!==(l=l.substring(u[0].length));)if(null!==(u=o.key_access.exec(l)))i.push(u[1]);else{if(null===(u=o.index_access.exec(l)))throw new SyntaxError("[sprintf] failed to parse named argument key");i.push(u[1])}e[2]=i}else a|=2;if(3===a)throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported");r.push({placeholder:e[0],param_no:e[1],keys:e[2],sign:e[3],pad_char:e[4],align:e[5],width:e[6],precision:e[7],type:e[8]})}n=n.substring(e[0].length)}return c[t]=r}(t),arguments)}function i(t,e){return a.apply(null,[t].concat(e||[]))}var c=Object.create(null);e.sprintf=a,e.vsprintf=i,"undefined"!=typeof window&&(window.sprintf=a,window.vsprintf=i,void 0===(r=function(){return{sprintf:a,vsprintf:i}}.call(e,n,e,t))||(t.exports=r))}()}},e={};function n(r){var o=e[r];if(void 0!==o)return o.exports;var a=e[r]={exports:{}};return t[r](a,a.exports,n),a.exports}n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var r in e)n.o(e,r)&&!n.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:e[r]})},n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),(()=>{"use strict";var t=window.frmGlobal,e=t.canAccessApplicationDashboard,r=t.applicationsUrl,o=window.frmFormTemplatesVars,a=o.FEATURED_TEMPLATES_IDS,i=o.FREE_TEMPLATES_IDS,c=o.upgradeLink,l="frm-form-templates",u="available-templates",s="favorites",m="custom",f="search",p="440px",d="550px",y="frm-page-skeleton",v="all-items";function h(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)}return n}function b(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?h(Object(n),!0).forEach(function(e){g(t,e,n[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):h(Object(n)).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))})}return t}function g(t,e,n){return(e=function(t){var e=function(t){if("object"!=w(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var n=e.call(t,"string");if("object"!=w(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==w(e)?e:e+""}(e))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function w(t){return w="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},w(t)}var S,T,_,E,x=n(8616),O=n.n(x),j=n(7604),I=n.n(j),C=O()(console.error);function A(t){return A="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},A(t)}function k(t){var e=function(t){if("object"!=A(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var n=e.call(t,"string");if("object"!=A(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==A(e)?e:e+""}S={"(":9,"!":8,"*":7,"/":7,"%":7,"+":6,"-":6,"<":5,"<=":5,">":5,">=":5,"==":4,"!=":4,"&&":3,"||":2,"?":1,"?:":1},T=["(","?"],_={")":["("],":":["?","?:"]},E=/<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/;var P={"!":function(t){return!t},"*":function(t,e){return t*e},"/":function(t,e){return t/e},"%":function(t,e){return t%e},"+":function(t,e){return t+e},"-":function(t,e){return t-e},"<":function(t,e){return t<e},"<=":function(t,e){return t<=e},">":function(t,e){return t>e},">=":function(t,e){return t>=e},"==":function(t,e){return t===e},"!=":function(t,e){return t!==e},"&&":function(t,e){return t&&e},"||":function(t,e){return t||e},"?:":function(t,e,n){if(t)throw e;return n}};var F={contextDelimiter:"",onMissingKey:null};function L(t,e){var n;for(n in this.data=t,this.pluralForms={},this.options={},F)this.options[n]=void 0!==e&&n in e?e[n]:F[n]}function B(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)}return n}function D(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?B(Object(n),!0).forEach(function(e){var r,o,a;r=t,o=e,a=n[e],(o=k(o))in r?Object.defineProperty(r,o,{value:a,enumerable:!0,configurable:!0,writable:!0}):r[o]=a}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):B(Object(n)).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))})}return t}L.prototype.getPluralForm=function(t,e){var n,r,o,a,i=this.pluralForms[t];return i||("function"!=typeof(o=(n=this.data[t][""])["Plural-Forms"]||n["plural-forms"]||n.plural_forms)&&(r=function(t){var e,n,r;for(e=t.split(";"),n=0;n<e.length;n++)if(0===(r=e[n].trim()).indexOf("plural="))return r.substr(7)}(n["Plural-Forms"]||n["plural-forms"]||n.plural_forms),a=function(t){var e=function(t){for(var e,n,r,o,a=[],i=[];e=t.match(E);){for(n=e[0],(r=t.substr(0,e.index).trim())&&a.push(r);o=i.pop();){if(_[n]){if(_[n][0]===o){n=_[n][1]||n;break}}else if(T.indexOf(o)>=0||S[o]<S[n]){i.push(o);break}a.push(o)}_[n]||i.push(n),t=t.substr(e.index+n.length)}return(t=t.trim())&&a.push(t),a.concat(i.reverse())}(t);return function(t){return function(t,e){var n,r,o,a,i,c,l=[];for(n=0;n<t.length;n++){if(i=t[n],a=P[i]){for(r=a.length,o=Array(r);r--;)o[r]=l.pop();try{c=a.apply(null,o)}catch(t){return t}}else c=e.hasOwnProperty(i)?e[i]:+i;l.push(c)}return l[0]}(e,t)}}(r),o=function(t){return+a({n:t})}),i=this.pluralForms[t]=o),i(e)},L.prototype.dcnpgettext=function(t,e,n,r,o){var a,i,c;return a=void 0===o?0:this.getPluralForm(t,o),i=n,e&&(i=e+this.options.contextDelimiter+n),(c=this.data[t][i])&&c[a]?c[a]:(this.options.onMissingKey&&this.options.onMissingKey(n,t),0===a?n:r)};var M={"":{plural_forms:function(t){return 1===t?0:1}}},q=/^i18n\.(n?gettext|has_translation)(_|$)/;const N=function(t){return"string"!=typeof t||""===t?(console.error("The namespace must be a non-empty string."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(t)||(console.error("The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."),!1)},G=function(t){return"string"!=typeof t||""===t?(console.error("The hook name must be a non-empty string."),!1):/^__/.test(t)?(console.error("The hook name cannot begin with `__`."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(t)||(console.error("The hook name can only contain numbers, letters, dashes, periods and underscores."),!1)},R=function(t,e){return function(n,r,o){var a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:10,i=t[e];if(G(n)&&N(r))if("function"==typeof o)if("number"==typeof a){var c={callback:o,priority:a,namespace:r};if(i[n]){var l,u=i[n].handlers;for(l=u.length;l>0&&!(a>=u[l-1].priority);l--);l===u.length?u[l]=c:u.splice(l,0,c),i.__current.forEach(function(t){t.name===n&&t.currentIndex>=l&&t.currentIndex++})}else i[n]={handlers:[c],runs:0};"hookAdded"!==n&&t.doAction("hookAdded",n,r,o,a)}else console.error("If specified, the hook priority must be a number.");else console.error("The hook callback must be a function.")}},z=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return function(r,o){var a=t[e];if(G(r)&&(n||N(o))){if(!a[r])return 0;var i=0;if(n)i=a[r].handlers.length,a[r]={runs:a[r].runs,handlers:[]};else for(var c=a[r].handlers,l=function(t){c[t].namespace===o&&(c.splice(t,1),i++,a.__current.forEach(function(e){e.name===r&&e.currentIndex>=t&&e.currentIndex--}))},u=c.length-1;u>=0;u--)l(u);return"hookRemoved"!==r&&t.doAction("hookRemoved",r,o),i}}},V=function(t,e){return function(n,r){var o=t[e];return void 0!==r?n in o&&o[n].handlers.some(function(t){return t.namespace===r}):n in o}},$=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return function(r){var o=t[e];o[r]||(o[r]={handlers:[],runs:0}),o[r].runs++;for(var a=o[r].handlers,i=arguments.length,c=new Array(i>1?i-1:0),l=1;l<i;l++)c[l-1]=arguments[l];if(!a||!a.length)return n?c[0]:void 0;var u={name:r,currentIndex:0};for(o.__current.push(u);u.currentIndex<a.length;){var s=a[u.currentIndex].callback.apply(null,c);n&&(c[0]=s),u.currentIndex++}return o.__current.pop(),n?c[0]:void 0}},U=function(t,e){return function(){var n,r,o=t[e];return null!==(n=null===(r=o.__current[o.__current.length-1])||void 0===r?void 0:r.name)&&void 0!==n?n:null}},H=function(t,e){return function(n){var r=t[e];return void 0===n?void 0!==r.__current[0]:!!r.__current[0]&&n===r.__current[0].name}},Z=function(t,e){return function(n){var r=t[e];if(G(n))return r[n]&&r[n].runs?r[n].runs:0}};var J=function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.actions=Object.create(null),this.actions.__current=[],this.filters=Object.create(null),this.filters.__current=[],this.addAction=R(this,"actions"),this.addFilter=R(this,"filters"),this.removeAction=z(this,"actions"),this.removeFilter=z(this,"filters"),this.hasAction=V(this,"actions"),this.hasFilter=V(this,"filters"),this.removeAllActions=z(this,"actions",!0),this.removeAllFilters=z(this,"filters",!0),this.doAction=$(this,"actions"),this.applyFilters=$(this,"filters",!0),this.currentAction=U(this,"actions"),this.currentFilter=U(this,"filters"),this.doingAction=H(this,"actions"),this.doingFilter=H(this,"filters"),this.didAction=Z(this,"actions"),this.didFilter=Z(this,"filters")},Y=new J,K=(Y.addAction,Y.addFilter,Y.removeAction,Y.removeFilter,Y.hasAction,Y.hasFilter,Y.removeAllActions,Y.removeAllFilters,Y.doAction,Y.applyFilters,Y.currentAction,Y.currentFilter,Y.doingAction,Y.doingFilter,Y.didAction,Y.didFilter,Y.actions,Y.filters,function(t,e,n){var r=new L({}),o=new Set,a=function(){o.forEach(function(t){return t()})},i=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"default";r.data[e]=D(D(D({},M),r.data[e]),t),r.data[e][""]=D(D({},M[""]),r.data[e][""])},c=function(t,e){i(t,e),a()},l=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default",e=arguments.length>1?arguments[1]:void 0,n=arguments.length>2?arguments[2]:void 0,o=arguments.length>3?arguments[3]:void 0,a=arguments.length>4?arguments[4]:void 0;return r.data[t]||i(void 0,t),r.dcnpgettext(t,e,n,o,a)},u=function(){return arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default"},s=function(t,e,r){var o=l(r,e,t);return n?(o=n.applyFilters("i18n.gettext_with_context",o,t,e,r),n.applyFilters("i18n.gettext_with_context_"+u(r),o,t,e,r)):o};if(n){var m=function(t){q.test(t)&&a()};n.addAction("hookAdded","core/i18n",m),n.addAction("hookRemoved","core/i18n",m)}return{getLocaleData:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default";return r.data[t]},setLocaleData:c,resetLocaleData:function(t,e){r.data={},r.pluralForms={},c(t,e)},subscribe:function(t){return o.add(t),function(){return o.delete(t)}},__:function(t,e){var r=l(e,void 0,t);return n?(r=n.applyFilters("i18n.gettext",r,t,e),n.applyFilters("i18n.gettext_"+u(e),r,t,e)):r},_x:s,_n:function(t,e,r,o){var a=l(o,void 0,t,e,r);return n?(a=n.applyFilters("i18n.ngettext",a,t,e,r,o),n.applyFilters("i18n.ngettext_"+u(o),a,t,e,r,o)):a},_nx:function(t,e,r,o,a){var i=l(a,o,t,e,r);return n?(i=n.applyFilters("i18n.ngettext_with_context",i,t,e,r,o,a),n.applyFilters("i18n.ngettext_with_context_"+u(a),i,t,e,r,o,a)):i},isRTL:function(){return"rtl"===s("ltr","text direction")},hasTranslation:function(t,e,o){var a,i,c=e?e+""+t:t,l=!(null===(a=r.data)||void 0===a||null===(i=a[null!=o?o:"default"])||void 0===i||!i[c]);return n&&(l=n.applyFilters("i18n.has_translation",l,t,e,o),l=n.applyFilters("i18n.has_translation_"+u(o),l,t,e,o)),l}}}(0,0,Y));K.getLocaleData.bind(K),K.setLocaleData.bind(K),K.resetLocaleData.bind(K),K.subscribe.bind(K);var X=K.__.bind(K),W=(K._x.bind(K),K._n.bind(K),K._nx.bind(K),K.isRTL.bind(K),K.hasTranslation.bind(K),window.frmGlobal),Q=W.url,tt=(W.nonce,"frm_hidden"),et="frm-hide-js",nt="frm-current",rt=window.frmDom,ot=rt.tag,at=rt.div,it=rt.a,ct=rt.img;function lt(t){return lt="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},lt(t)}function ut(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)}return n}function st(t,e,n){return(e=function(t){var e=function(t){if("object"!=lt(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var n=e.call(t,"string");if("object"!=lt(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==lt(e)?e:e+""}(e))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}var mt,ft=document.getElementById("post-body-content"),pt=document.getElementById("".concat(y,"-sidebar")),dt=((mt=it({className:"button button-primary frm-button-primary"})).setAttribute("role","button"),at({id:"".concat(y,"-empty-state"),className:"frm-flex-col frm-flex-center frm-gap-md ".concat(tt),children:[ct({src:"".concat(Q,"/images/page-skeleton/empty-state.svg"),alt:X("Empty State","formidable")}),at({className:"frmcenter",children:[ot("h2",{className:"".concat(y,"-title frm-mb-0")}),ot("p",{className:"".concat(y,"-text frm-mb-0")})]}),mt]}));null==ft||ft.appendChild(dt);var yt=function(){var t=document.querySelector("#".concat(y,"-empty-state"));return{emptyState:t,emptyStateTitle:null==t?void 0:t.querySelector(".".concat(y,"-title")),emptyStateText:null==t?void 0:t.querySelector(".".concat(y,"-text")),emptyStateButton:null==t?void 0:t.querySelector(".button")}}(),vt=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if("object"!==w(t)||null===t)throw new Error("createPageElements: initialElements must be a non-null object");var e=t;return{getElements:function(){return e},addElements:function(t){if("object"!==w(t)||null===t)throw new Error("addElements: newElements must be a non-null object");e=b(b({},e),t)}}}(function(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?ut(Object(n),!0).forEach(function(e){st(t,e,n[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):ut(Object(n)).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))})}return t}({bodyContent:ft,sidebar:pt,searchInput:pt.querySelector(".frm-search-input"),categoryItems:pt.querySelectorAll(".".concat(y,"-cat")),allItemsCategory:pt.querySelector(".".concat(y,'-cat[data-category="').concat(v,'"]'))},yt)),ht=vt.getElements,bt=vt.addElements;function gt(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)}return n}function wt(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?gt(Object(n),!0).forEach(function(e){St(t,e,n[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):gt(Object(n)).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))})}return t}function St(t,e,n){return(e=function(t){var e=function(t){if("object"!=Tt(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var n=e.call(t,"string");if("object"!=Tt(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==Tt(e)?e:e+""}(e))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function Tt(t){return Tt="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},Tt(t)}var _t=ht().allItemsCategory,Et=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if("object"!==Tt(t)||null===t)throw new Error("createPageState: initialState must be a non-null object");var e=t;return{getState:function(){return e},getSingleState:function(t){var n=Reflect.get(e,t);return void 0===n?null:n},setState:function(t){if("object"!==Tt(t)||null===t)throw new Error("setState: newState must be a non-null object");e=wt(wt({},e),t)},setSingleState:function(t,n){Reflect.has(e,t)&&Reflect.set(e,t,n)}}}({notEmptySearchText:!1,selectedCategory:v,selectedCategoryEl:_t}),xt=Et.getState,Ot=Et.getSingleState,jt=Et.setState,It=Et.setSingleState;function Ct(t){return Ct="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},Ct(t)}function At(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,kt(r.key),r)}}function kt(t){var e=function(t){if("object"!=Ct(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var n=e.call(t,"string");if("object"!=Ct(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==Ct(e)?e:e+""}var Pt=function(){return t=function t(e){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"default";!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.elements=e,this.type=n,this.prepareElements()},e=[{key:"fadeIn",value:function(){var t=this;this.applyStyleToElements(function(e){e.classList.add("frm-fadein-up"),e.addEventListener("animationend",function(){t.resetOpacity(),e.classList.remove("frm-fadein-up")},{once:!0})})}},{key:"cascadeFadeIn",value:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:.03;setTimeout(function(){t.applyStyleToElements(function(t,n){t.classList.remove("frm-animate"),t.style.transitionDelay=(n+1)*e+"s"})},200)}},{key:"prepareElements",value:function(){var t=this;this.applyStyleToElements(function(e){"default"===t.type&&(e.style.opacity="0.0"),"cascade"===t.type&&e.classList.add("frm-init-cascade-animation"),"cascade-3d"===t.type&&e.classList.add("frm-init-fadein-3d"),e.classList.add("frm-animate")})}},{key:"resetOpacity",value:function(){this.applyStyleToElements(function(t){return t.style.opacity="1.0"})}},{key:"applyStyleToElements",value:function(t){this.elements instanceof Element?t(this.elements,0):0<this.elements.length&&this.elements.forEach(function(e,n){return t(e,n)})}}],e&&At(t.prototype,e),Object.defineProperty(t,"prototype",{writable:!1}),t;var t,e}(),Ft=Promise.resolve(),Lt=frmDom,Bt=(Lt.div,Lt.span,Lt.tag,Lt.a,Lt.img,Lt.svg,frmDom.modal),Dt=(Bt.maybeCreateModal,Bt.footerButton,frmDom.util),Mt=Dt.onClickPreventDefault;function qt(t){return qt="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},qt(t)}Dt.documentOn,frmDom.ajax.doJsonPost;var Nt,Gt,Rt=new URL(window.location.href),zt=Rt.searchParams,Vt=function(t){return zt.delete(t),Rt.search=zt.toString(),Rt.toString()},$t=function(t,e){var n,r,o,a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"pushState";if(zt.set(t,e),Rt.search=zt.toString(),["pushState","replaceState"].includes(a)){var i=(n={},o=e,(r=function(t){var e=function(t){if("object"!=qt(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var n=e.call(t,"string");if("object"!=qt(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==qt(e)?e:e+""}(r=t))in n?Object.defineProperty(n,r,{value:o,enumerable:!0,configurable:!0,writable:!0}):n[r]=o,n);window.history[a](i,"",Rt)}return Rt.toString()},Ut=function(t){return zt.has(t)},Ht=function(t){return"string"==typeof t&&/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i.test(t)},Zt=function(t){return t instanceof HTMLElement||console.warn("Invalid argument: Element must be an instance of HTMLElement")||!1},Jt=function(t){var e;return null===(e=Array.from(t))||void 0===e?void 0:e.forEach(function(t){return Kt(t)})},Yt=function(t){var e;return null===(e=Array.from(t))||void 0===e?void 0:e.forEach(function(t){return Xt(t)})},Kt=function(t){return null==t?void 0:t.classList.remove(tt)},Xt=function(t){return null==t?void 0:t.classList.add(tt)},Wt=function(t){var e=t.currentTarget,n=e.getAttribute("data-category"),r=xt(),o=r.selectedCategory,a=r.selectedCategoryEl,i=r.notEmptySearchText;if(o!==n){o=wp.hooks.applyFilters("frmPage.selectedCategory",n),a.classList.remove(nt),(a=e).classList.add(nt),jt({selectedCategory:o,selectedCategoryEl:a}),i&&te(),wp.hooks.doAction("frmPage.onCategoryClick",o);var c=ht().bodyContent;new Pt(c).fadeIn()}};function Qt(t){"Enter"!==t.key&&" "!==t.key||(t.preventDefault(),Wt(t))}function te(){var t=ht().searchInput;t.value="",t.dispatchEvent(new Event("input",{bubbles:!0}))}var ee=ht().bodyContent,ne=document.getElementById("".concat(l,"-list")),re=document.getElementById("".concat(l,"-custom-list-section")),oe=document.querySelector(".".concat(y,'-cat[data-category="').concat(s,'"]')),ae=document.getElementById("".concat(l,"-modal"));bt({headerCancelButton:null===(Nt=document.getElementById("frm-publishing"))||void 0===Nt?void 0:Nt.querySelector("a"),createFormButton:document.getElementById("".concat(l,"-create-form")),pageTitle:document.getElementById("".concat(l,"-page-title")),pageTitleText:document.getElementById("".concat(l,"-page-title-text")),pageTitleDivider:document.getElementById("".concat(l,"-page-title-divider")),upsellBanner:null!==(Gt=document.getElementById("frm-renew-subscription-banner"))&&void 0!==Gt?Gt:document.getElementById("frm-upgrade-banner"),extraTemplateCountElements:document.querySelectorAll(".".concat(l,"-extra-templates-count")),templatesList:ne,templateItems:ne.querySelectorAll(".frm-card-item"),availableTemplateItems:ne.querySelectorAll(".frm-card-item:not(.".concat(l,"-locked-item)")),twinFeaturedTemplateItems:ne.querySelectorAll(".".concat(l,"-featured-item")),featuredTemplatesList:document.getElementById("".concat(l,"-featured-list")),customTemplatesSection:re,customTemplateItems:re.querySelectorAll(".frm-card-item"),customTemplatesTitle:document.getElementById("".concat(l,"-custom-list-title")),customTemplatesList:document.getElementById("".concat(l,"-custom-list")),favoritesCategory:oe,favoritesCategoryCountEl:null==oe?void 0:oe.querySelector(".".concat(y,"-cat-count")),availableTemplatesCategory:document.querySelector(".".concat(y,'-cat[data-category="').concat(u,'"]')),getFreeTemplatesBannerButton:document.querySelector(".frm-get-free-templates-banner .button"),modal:ae,modalItems:null==ae?void 0:ae.querySelectorAll(".".concat(l,"-modal-item")),showCreateTemplateModalButton:document.getElementById("frm-show-create-template-modal"),createTemplateModal:document.getElementById("frm-create-template-modal"),createTemplateFormsDropdown:document.getElementById("frm-create-template-modal-forms-select"),createTemplateName:document.getElementById("frm_create_template_name"),createTemplateDescription:document.getElementById("frm_create_template_description"),createTemplateButton:document.getElementById("frm-create-template-button"),renewAccountModal:document.getElementById("frm-renew-modal"),leaveEmailModal:document.getElementById("frm-leave-email-modal"),leaveEmailModalInput:document.getElementById("frm_leave_email"),leaveEmailModalButton:document.getElementById("frm-get-code-button"),upgradeModal:document.getElementById("frm-form-upgrade-modal"),upgradeModalTemplateNames:null==ae?void 0:ae.querySelectorAll(".frm-upgrade-modal-template-name"),upgradeModalPlansIcons:null==ae?void 0:ae.querySelectorAll(".frm-upgrade-modal-plan-icon"),upgradeModalLink:document.getElementById("frm-upgrade-modal-link"),newTemplateForm:document.getElementById("frm-new-template"),newTemplateNameInput:document.getElementById("frm_template_name"),newTemplateDescriptionInput:document.getElementById("frm_template_desc"),newTemplateLinkInput:document.getElementById("frm_link"),newTemplateActionInput:document.getElementById("frm_action_type"),bodyContentChildren:null==ee?void 0:ee.children});var ie,ce=window.frmDom,le=ce.tag,ue=ce.div,se=ce.span,me=ce.a,fe=ce.img,pe="".concat(Q,"/images/applications/thumbnails");var de=window.frmFormTemplatesVars,ye=de.templatesCount,ve=de.favoritesCount,he=de.customCount,be=ht().availableTemplateItems.length;jt({availableTemplatesCount:be,customCount:Number(he),extraTemplatesCount:ye-be,favoritesCount:ve}),window.frmDom.span;var ge=function(t,e){return t.textContent=String(e),t},we=function(t,e,n,r,o,a,i){e._counterStartTime||(e._counterStartTime=t,e._counterLastTimestamp=t,e._counterFrameDropCount=0,e._counterLastValue=n);var c=t-e._counterLastTimestamp,l=t-e._counterStartTime;if(c>50&&null!==e._counterLastTimestamp&&(e._counterFrameDropCount++,e._counterFrameDropCount>3))return e.style.transition="opacity ".concat(Math.max(o-l,100),"ms ease-out"),e.textContent=String(r),void delete e._counterAnimation;var u=Math.min(l/o,1),s=i(u),m=Math.round(n+a*s);m!==e._counterLastValue&&(e.textContent=String(m),e._counterLastValue=m),e._counterLastTimestamp=t,u<1?e._counterAnimation=requestAnimationFrame(function(t){return we(t,e,n,r,o,a,i)}):(e.textContent=String(r),["_counterAnimation","_counterStartTime","_counterLastTimestamp","_counterFrameDropCount","_counterLastValue"].forEach(function(t){return delete e[t]}),e.style.removeProperty("transition"))},Se=function(t){return 1-Math.pow(1-t,4)};const Te=function(){var t,e=ht(),n=e.sidebar,r=e.searchInput,o=e.bodyContent,a=e.twinFeaturedTemplateItems,c=e.availableTemplatesCategory,l=e.extraTemplateCountElements,u=new Pt(o);r.value="",Yt(a),function(t){if(t){var e,n,r=Ot("availableTemplatesCount");if(Ut("registered-for-free-templates"))history.replaceState({},"",Vt("registered-for-free-templates")),e=t,n=r,setTimeout(function(){e.dispatchEvent(new Event("click",{bubbles:!0}))},0),setTimeout(function(){!function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r="string"==typeof t?document.querySelector(t):t;if(!(r&&r instanceof HTMLElement))return null;var o="number"==typeof e?e:parseInt(e,10);if(isNaN(o))return console.warn("Counter: Invalid value provided, defaulting to 0"),ge(r,"0");if(0===o)return ge(r,"0");var a=n.duration,i=void 0===a?3e3:a,c=n.easing,l=void 0===c?Se:c,u=parseInt(r.textContent,10)||0,s=o-u;0===s||(r._counterAnimation&&cancelAnimationFrame(r._counterAnimation),r.classList.add("frm-fadein"),r._counterAnimation=requestAnimationFrame(function(t){return we(t,r,u,o,i,s,l)}))}(e.querySelector(".".concat(y,"-cat-count")),n)},150),setTimeout(function(){ht().availableTemplateItems.forEach(function(t){i.includes(Number(t.dataset.id))||(t.classList.add("frm-background-highlight"),t.addEventListener("animationend",function t(e){"backgroundHighlight"===e.animationName&&(this.classList.remove("frm-background-highlight"),this.removeEventListener("animationend",t))}))})},750);else t.querySelector(".".concat(y,"-cat-count")).textContent=r}}(c),l.forEach(function(t){return t.textContent=Ot("extraTemplatesCount")}),o.classList.remove(et),n.classList.remove(et),u.fadeIn(),Ut("return_page")&&(t=ht().headerCancelButton,new Pt(t).fadeIn())};function _e(){var t,e,n="function"==typeof Symbol?Symbol:{},r=n.iterator||"@@iterator",o=n.toStringTag||"@@toStringTag";function a(n,r,o,a){var l=r&&r.prototype instanceof c?r:c,u=Object.create(l.prototype);return Ee(u,"_invoke",function(n,r,o){var a,c,l,u=0,s=o||[],m=!1,f={p:0,n:0,v:t,a:p,f:p.bind(t,4),d:function(e,n){return a=e,c=0,l=t,f.n=n,i}};function p(n,r){for(c=n,l=r,e=0;!m&&u&&!o&&e<s.length;e++){var o,a=s[e],p=f.p,d=a[2];n>3?(o=d===r)&&(l=a[(c=a[4])?5:(c=3,3)],a[4]=a[5]=t):a[0]<=p&&((o=n<2&&p<a[1])?(c=0,f.v=r,f.n=a[1]):p<d&&(o=n<3||a[0]>r||r>d)&&(a[4]=n,a[5]=r,f.n=d,c=0))}if(o||n>1)return i;throw m=!0,r}return function(o,s,d){if(u>1)throw TypeError("Generator is already running");for(m&&1===s&&p(s,d),c=s,l=d;(e=c<2?t:l)||!m;){a||(c?c<3?(c>1&&(f.n=-1),p(c,l)):f.n=l:f.v=l);try{if(u=2,a){if(c||(o="next"),e=a[o]){if(!(e=e.call(a,l)))throw TypeError("iterator result is not an object");if(!e.done)return e;l=e.value,c<2&&(c=0)}else 1===c&&(e=a.return)&&e.call(a),c<2&&(l=TypeError("The iterator does not provide a '"+o+"' method"),c=1);a=t}else if((e=(m=f.n<0)?l:n.call(r,f))!==i)break}catch(e){a=t,c=1,l=e}finally{u=1}}return{value:e,done:m}}}(n,o,a),!0),u}var i={};function c(){}function l(){}function u(){}e=Object.getPrototypeOf;var s=[][r]?e(e([][r]())):(Ee(e={},r,function(){return this}),e),m=u.prototype=c.prototype=Object.create(s);function f(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,u):(t.__proto__=u,Ee(t,o,"GeneratorFunction")),t.prototype=Object.create(m),t}return l.prototype=u,Ee(m,"constructor",u),Ee(u,"constructor",l),l.displayName="GeneratorFunction",Ee(u,o,"GeneratorFunction"),Ee(m),Ee(m,o,"Generator"),Ee(m,r,function(){return this}),Ee(m,"toString",function(){return"[object Generator]"}),(_e=function(){return{w:a,m:f}})()}function Ee(t,e,n,r){var o=Object.defineProperty;try{o({},"",{})}catch(t){o=0}Ee=function(t,e,n,r){function a(e,n){Ee(t,e,function(t){return this._invoke(e,n,t)})}e?o?o(t,e,{value:n,enumerable:!r,configurable:!r,writable:!r}):t[e]=n:(a("next",0),a("throw",1),a("return",2))},Ee(t,e,n,r)}function xe(t,e,n,r,o,a,i){try{var c=t[a](i),l=c.value}catch(t){return void n(t)}c.done?e(l):Promise.resolve(l).then(r,o)}var Oe=null;function je(){var t;return t=_e().m(function t(){var e,n,r;return _e().w(function(t){for(;;)switch(t.n){case 0:e=window.frmAdminBuild,n=e.initModal,r=e.offsetModalY,(Oe=n("#frm-form-templates-modal",p))&&r(Oe,"103px"),Ut("free-templates")&&ht().leaveEmailModal&&dn(),wp.hooks.addAction("frmAdmin.beforeOpenConfirmModal","frmFormTemplates",function(t){var e=t.$info;e.dialog("option","width",d),r(e,"103px")});case 1:return t.a(2)}},t)}),je=function(){var e=this,n=arguments;return new Promise(function(r,o){var a=t.apply(e,n);function i(t){xe(a,r,o,i,c,"next",t)}function c(t){xe(a,r,o,i,c,"throw",t)}i(void 0)})},je.apply(this,arguments)}function Ie(){return Oe}function Ce(t){var e=ht().pageTitleText,n=t||Ot("selectedCategoryEl").querySelector(".".concat(y,"-cat-text")).textContent;e.textContent=n}var Ae=function(t){return!!Zt(t)&&t.classList.contains("".concat(l,"-favorite-item"))},ke=function(t){return!!Zt(t)&&t.classList.contains("".concat(l,"-custom-item"))},Pe={},Fe=function(){var t=ht(),e=t.createFormButton,n=t.newTemplateForm,r=t.newTemplateNameInput,o=t.newTemplateActionInput,a=window.frmAdminBuild.installNewForm;r.value="",o.value="frm_install_form",a(n,"frm_install_form",e)};var Le=".".concat(l,"-item-favorite-button"),Be="".concat(Le," use"),De="#frm_heart_solid_icon",Me="#frm_heart_icon",qe=function(t){var e,n=t.currentTarget,r=ht(),o=r.templatesList,i=r.featuredTemplatesList,c=r.favoritesCategoryCountEl,u=r.customTemplatesTitle,m=n.closest(".frm-card-item"),f=m.dataset.id,p=Ae(m),d=ke(m),y=function(t){return!!Zt(t)&&a.includes(Number(t.dataset.id))}(m),v=null;if(m.classList.toggle("".concat(l,"-favorite-item"),!p),y){var h=m.closest("#".concat(l,"-list"))?i:o;h&&(v=h.querySelector('.frm-card-item[data-id="'.concat(f,'"]'))).classList.toggle("".concat(l,"-favorite-item"),!p)}var b,g=xt(),w=g.selectedCategory,S=g.favoritesCount,T=p?"remove":"add",_=m.querySelector(Be),E=null===(e=v)||void 0===e?void 0:e.querySelector(Be);"add"===T?(++S.total,d?++S.custom:++S.default,_.setAttribute("href",De),null==E||E.setAttribute("href",De)):(--S.total,d?--S.custom:--S.default,_.setAttribute("href",Me),null==E||E.setAttribute("href",Me)),c.textContent=S.total,It("favoritesCount",S),s===w&&(0===S.total&&on(),Xt(m),0===S.default&&Xt(o),0!==S.custom&&0!==S.default||Xt(u)),b=function(){return t=f,e=T,n=d,r=new FormData,o=frmDom.ajax.doJsonPost,r.append("template_id",t),r.append("operation",e),r.append("is_custom_template",n),o("add_or_remove_favorite_template",r);var t,e,n,r,o},Ft=Ft.then(b).catch(b)};var Ne=function(t){var e=t.currentTarget,n=e.closest(".frm-card-item"),r=function(t){return!!Zt(t)&&t.classList.contains("".concat(l,"-locked-item"))}(n),o=ke(n);if(r||!o)if(t.preventDefault(),r)!function(t){var e=t.dataset.requiredPlan;switch(e){case"basic":case"plus":case"business":case"elite":fn(e,t);break;case"renew":pn();break;case"free":dn()}}(n);else{var a=ht(),i=a.newTemplateForm,c=a.newTemplateNameInput,u=a.newTemplateDescriptionInput,s=a.newTemplateLinkInput,m=a.newTemplateActionInput,f=window.frmAdminBuild.installNewForm,p=n.querySelector(".frm-form-template-name").textContent.trim(),d=n.querySelector(".frm-form-templates-item-description").textContent.trim(),y="frm_install_template";c.value=p,u.value=d,m.value=y,s.value=e.href,f(i,y,e)}};var Ge=window.frmDom.search.init;function Re(t,e){var n=t.foundSomething,r=t.notEmptySearchText;if(!e||"search"!==e.type||""!==e.target.value){var o=xt(),a=ht().allItemsCategory;It("notEmptySearchText",r),o.notEmptySearchText||o.selectedCategory?(o.selectedCategory&&(function(t){var e=ht(),n=e.bodyContent,r=e.bodyContentChildren,o=e.pageTitle,a=e.templatesList,i=e.applicationTemplates,c=new Pt(n);t&&Ot("selectedCategoryEl").classList.remove(nt),Yt(r),Ce(X("Search Result","formidable")),Jt([o,a,i]),c.fadeIn()}(r),r&&It("selectedCategory","")),function(t){if(t){var e,n=ht().emptyState;if(e=n,"none"!==window.getComputedStyle(e).getPropertyValue("display")){var r=ht().pageTitle;Xt(n),Kt(r)}var o=ht(),a=o.templatesList,i=o.applicationTemplates,c=o.applicationTemplatesTitle,l=o.applicationTemplatesList;Jt([a,i,c]),0===a.offsetHeight&&Yt([a,c]),0===(null==l?void 0:l.offsetHeight)&&Xt(i)}else!function(){var t,e=xt().notEmptySearchText,n=ht(),r=n.pageTitle,o=n.emptyState,a=n.emptyStateButton,i=n.applicationTemplates;if(f!==(null===(t=o.dataset)||void 0===t?void 0:t.view)){o.setAttribute("data-view",f);var c=ht(),l=c.emptyStateTitle,u=c.emptyStateText;l.textContent=X("No templates found","formidable"),u.textContent=X("Sorry, we didn't find any templates that match your criteria.","formidable"),a.textContent=X("Start from Scratch","formidable"),Yt([r,i]),Jt([o,a])}else e?(Kt(o),Yt([r,i])):(Xt(o),o.removeAttribute("data-view"))}()}(n)):a.dispatchEvent(new Event("click",{bubbles:!0}))}}var ze=function(){var t,e=ht().emptyState;f===(null===(t=e.dataset)||void 0===t?void 0:t.view)&&(It("selectedCategory",""),te(),ht().searchInput.focus())};var Ve=function(){var t=xt().selectedCategory;m===t&&yn()},$e=function(){var t=ht().createTemplateFormsDropdown,e=t.value;if(e&&"no-forms"!==e){Ue(!1);var n=t.options[t.selectedIndex],r=n.dataset.description.trim(),o=n.dataset.name.trim(),a=" ".concat(X("Template","formidable"));o.endsWith(a)||(o+=a);var i=ht(),c=i.createTemplateName,l=i.createTemplateDescription;c.value=o,l.value=r}else Ue(!0)},Ue=function(t){var e=ht(),n=e.createTemplateName,r=e.createTemplateDescription,o=e.createTemplateButton;[n,r].forEach(function(e){e.disabled=t,t&&(e.value="")}),o.classList.toggle("disabled",t)},He=function(){var t=window.frmAdminBuild.installNewForm,e="frm_create_template",n=ht(),r=n.newTemplateForm,o=n.newTemplateActionInput,a=n.newTemplateNameInput,i=n.newTemplateDescriptionInput,c=n.newTemplateLinkInput,l=n.createTemplateName,u=n.createTemplateDescription,s=n.createTemplateFormsDropdown,m=n.createTemplateButton;o.value=e,a.value=l.value.trim(),i.value=u.value.trim(),c.value=s.value,t(r,e,m)};function Ze(){var t,e,n="function"==typeof Symbol?Symbol:{},r=n.iterator||"@@iterator",o=n.toStringTag||"@@toStringTag";function a(n,r,o,a){var l=r&&r.prototype instanceof c?r:c,u=Object.create(l.prototype);return Je(u,"_invoke",function(n,r,o){var a,c,l,u=0,s=o||[],m=!1,f={p:0,n:0,v:t,a:p,f:p.bind(t,4),d:function(e,n){return a=e,c=0,l=t,f.n=n,i}};function p(n,r){for(c=n,l=r,e=0;!m&&u&&!o&&e<s.length;e++){var o,a=s[e],p=f.p,d=a[2];n>3?(o=d===r)&&(l=a[(c=a[4])?5:(c=3,3)],a[4]=a[5]=t):a[0]<=p&&((o=n<2&&p<a[1])?(c=0,f.v=r,f.n=a[1]):p<d&&(o=n<3||a[0]>r||r>d)&&(a[4]=n,a[5]=r,f.n=d,c=0))}if(o||n>1)return i;throw m=!0,r}return function(o,s,d){if(u>1)throw TypeError("Generator is already running");for(m&&1===s&&p(s,d),c=s,l=d;(e=c<2?t:l)||!m;){a||(c?c<3?(c>1&&(f.n=-1),p(c,l)):f.n=l:f.v=l);try{if(u=2,a){if(c||(o="next"),e=a[o]){if(!(e=e.call(a,l)))throw TypeError("iterator result is not an object");if(!e.done)return e;l=e.value,c<2&&(c=0)}else 1===c&&(e=a.return)&&e.call(a),c<2&&(l=TypeError("The iterator does not provide a '"+o+"' method"),c=1);a=t}else if((e=(m=f.n<0)?l:n.call(r,f))!==i)break}catch(e){a=t,c=1,l=e}finally{u=1}}return{value:e,done:m}}}(n,o,a),!0),u}var i={};function c(){}function l(){}function u(){}e=Object.getPrototypeOf;var s=[][r]?e(e([][r]())):(Je(e={},r,function(){return this}),e),m=u.prototype=c.prototype=Object.create(s);function f(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,u):(t.__proto__=u,Je(t,o,"GeneratorFunction")),t.prototype=Object.create(m),t}return l.prototype=u,Je(m,"constructor",u),Je(u,"constructor",l),l.displayName="GeneratorFunction",Je(u,o,"GeneratorFunction"),Je(m),Je(m,o,"Generator"),Je(m,r,function(){return this}),Je(m,"toString",function(){return"[object Generator]"}),(Ze=function(){return{w:a,m:f}})()}function Je(t,e,n,r){var o=Object.defineProperty;try{o({},"",{})}catch(t){o=0}Je=function(t,e,n,r){function a(e,n){Je(t,e,function(t){return this._invoke(e,n,t)})}e?o?o(t,e,{value:n,enumerable:!r,configurable:!r,writable:!r}):t[e]=n:(a("next",0),a("throw",1),a("return",2))},Je(t,e,n,r)}function Ye(t,e,n,r,o,a,i){try{var c=t[a](i),l=c.value}catch(t){return void n(t)}c.done?e(l):Promise.resolve(l).then(r,o)}var Ke=window.frmDom.tag,Xe=function(){var t,e=(t=Ze().m(function t(){var e,n,r,o,a,i,c,l,u;return Ze().w(function(t){for(;;)switch(t.p=t.n){case 0:if(e=ht(),n=e.leaveEmailModalInput,r=n.value.trim()){t.n=1;break}return vn("empty"),t.a(2);case 1:if(Ht(r)){t.n=2;break}return vn("invalid"),t.a(2);case 2:return o=ht(),(a=o.leaveEmailModalButton).style.setProperty("cursor","not-allowed"),a.classList.add("frm_loading_button"),(i=new FormData).append("email",r),l=frmDom.ajax.doJsonPost,t.p=3,t.n=4,l("get_free_templates",i);case 4:c=t.v,t.n=6;break;case 5:return t.p=5,u=t.v,console.error("An error occurred:",u),We(),t.a(2);case 6:if(c.success){t.n=7;break}return We(),t.a(2);case 7:Ut("free-templates")&&Vt("free-templates"),$t("registered-for-free-templates","1"),window.location.reload();case 8:return t.a(2)}},t,null,[[3,5]])}),function(){var e=this,n=arguments;return new Promise(function(r,o){var a=t.apply(e,n);function i(t){Ye(a,r,o,i,c,"next",t)}function c(t){Ye(a,r,o,i,c,"throw",t)}i(void 0)})});return function(){return e.apply(this,arguments)}}();function We(){var t=ht().leaveEmailModal;t.querySelector(".inside").replaceChildren(Ke("p",X("Failed to get templates, please try again later.","formidable"))),t.querySelector(".frm_modal_footer").classList.add("frm_hidden")}var Qe=function(t){if(!t.target.closest("a")){var e=t.currentTarget;window.location.href=e.dataset.href}};function tn(){var t,e,n,r;ht().categoryItems.forEach(function(t){Mt(t,Wt),t.addEventListener("keydown",Qt)}),wp.hooks.addAction("frmPage.onCategoryClick","frmFormTemplates",function(t){!function(t){var e=ht(),n=e.bodyContentChildren,r=e.pageTitle,o=e.showCreateTemplateModalButton,a=e.templatesList,i=e.templateItems,c=e.upsellBanner;switch(v!==t&&Yt(n),Ce(),Xt(o),Kt(r),t){case v:!function(){var t=ht(),e=t.bodyContentChildren,n=t.pageTitleDivider,r=t.templateItems,o=t.twinFeaturedTemplateItems,a=t.customTemplatesSection,i=t.emptyState,c=t.applicationTemplates;Jt([].concat(nn(e),nn(r))),Yt([n].concat(nn(o),[a,i,c]))}();break;case u:!function(){if(0!==xt().availableTemplatesCount){var t=ht(),e=t.templatesList,n=t.templateItems,r=t.availableTemplateItems,o=t.upsellBanner;Yt(n),Jt([o,e].concat(nn(r)))}else!function(){var t=ht(),e=t.pageTitle,n=t.emptyState,r=t.emptyStateButton;n.setAttribute("data-view",u);var o=ht(),a=o.emptyStateTitle,i=o.emptyStateText,c=xt().extraTemplatesCount;a.textContent=X("No Templates Available","formidable"),i.textContent=function(t){try{for(var e=arguments.length,n=new Array(e>1?e-1:0),r=1;r<e;r++)n[r-1]=arguments[r];return I().sprintf.apply(I(),[t].concat(n))}catch(e){return C("sprintf error: \n\n"+e.toString()),t}}(X("Upgrade to PRO for %s+ options or explore Free Templates.","formidable"),c),Yt([e,r]),Kt(n)}()}();break;case s:!function(){var t=xt().favoritesCount;if(0!==t.total){var e=ht(),n=e.bodyContent,r=e.templatesList,o=e.templateItems,a=e.customTemplatesSection,i=e.customTemplatesTitle,c=e.customTemplatesList,u=e.customTemplateItems;Yt(o);var s=[],m=n.querySelectorAll(".".concat(l,"-favorite-item"));if(s.push.apply(s,nn(m)),t.default>0&&s.push(r),t.custom>0){var f=Array.from(u).filter(function(t){return!Ae(t)});Yt(f),s.push(a),s.push(c),0===t.default?Xt(i):s.push(i)}Jt(s)}else on()}();break;case m:!function(){if(0!==xt().customCount){var t=ht(),e=t.showCreateTemplateModalButton,n=t.pageTitleDivider,r=t.customTemplatesSection,o=t.customTemplatesList,a=t.customTemplatesTitle,i=t.customTemplateItems;Xt(a),Jt([e,n,r,o].concat(nn(i)))}else!function(){var t=ht(),e=t.pageTitle,n=t.emptyState,r=t.emptyStateButton;n.setAttribute("data-view",m);var o=ht(),a=o.emptyStateTitle,i=o.emptyStateText;a.textContent=X("You currently have no templates.","formidable"),i.textContent=X("You haven't created any form templates. Begin now to simplify your workflow and save time.","formidable"),r.textContent=X("Create Template","formidable"),Xt(e),Jt([n,r])}()}();break;default:Yt(i),Jt([c,a].concat(nn(Pe[t])))}}(t)}),r=ht().createFormButton,Mt(r,Fe),document.querySelectorAll(Le).forEach(function(t){return Mt(t,qe)}),document.querySelectorAll(".".concat(l,"-use-template-button")).forEach(function(t){return t.addEventListener("click",Ne)}),t=ht(),e=t.searchInput,n=t.emptyStateButton,Ge(e,"frm-card-item",{handleSearchResult:Re}),Mt(n,ze),function(){var t=ht(),e=t.createTemplateFormsDropdown,n=t.createTemplateButton,r=t.showCreateTemplateModalButton,o=t.emptyStateButton;Mt(r,Ve),Mt(o,Ve),e.addEventListener("change",$e),Mt(n,He)}(),function(){var t=ht(),e=t.leaveEmailModalButton,n=t.getFreeTemplatesBannerButton;Mt(e,Xe),Mt(n,dn)}()}function en(t){var e,n;(function(t){if(t&&t.length){var e=t.map(function(t){return function(t){var e=t.name,n=t.key,o=t.hasLiteThumbnail,a=t.isWebp,i=o?a?"".concat(pe,"/").concat(n,".webp"):"".concat(pe,"/").concat(n,".png"):"".concat(pe,"/placeholder.svg");return le("li",{className:"frm-card-item",data:{href:"".concat(r,"&triggerViewApplicationModal=1&template=").concat(n),"frm-search-text":e.toLowerCase()},children:[ue({className:"".concat(l,"-item-icon"),child:fe({src:i})}),ue({className:"".concat(l,"-item-body"),children:[se({text:X("Ready Made Solution","formidable"),className:"frm-meta-tag frm-orange-tag frm-text-xs"}),le("h3",{text:e,className:"frm-text-sm frm-font-medium frm-m-0"}),me({text:X("See all applications","formidable"),className:"frm-text-xs frm-font-semibold",href:r})]})]})}(t)});ie=ue({id:"".concat(l,"-applications"),className:tt,children:[le("h2",{text:X("Application Templates"),className:"frm-text-sm frm-mb-sm"}),le("ul",{className:"".concat(l,"-list frm-list-grid-layout"),children:e})]})}})(t.templates),(e=ht()).applicationTemplates||void 0===ie||(e.bodyContent.append(ie),bt({applicationTemplates:ie,applicationTemplatesTitle:ie.querySelector("h2"),applicationTemplatesList:ie.querySelector(".".concat(l,"-list")),applicationTemplateItems:ie.querySelectorAll(".frm-card-item")})),void 0!==(n=ht().applicationTemplateItems)&&n.forEach(function(t){t.addEventListener("click",Qe)})}function nn(t){return function(t){if(Array.isArray(t))return rn(t)}(t)||function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)}(t)||function(t,e){if(t){if("string"==typeof t)return rn(t,e);var n={}.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?rn(t,e):void 0}}(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function rn(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=Array(e);n<e;n++)r[n]=t[n];return r}function on(){var t=ht(),e=t.pageTitle,n=t.emptyState,r=t.emptyStateButton;n.setAttribute("data-view",s);var o=ht(),a=o.emptyStateTitle,i=o.emptyStateText;a.textContent=X("No favorites","formidable"),i.textContent=X("You haven't added any templates to your favorites yet.","formidable"),Yt([e,r]),Kt(n)}function an(){var t,e,n="function"==typeof Symbol?Symbol:{},r=n.iterator||"@@iterator",o=n.toStringTag||"@@toStringTag";function a(n,r,o,a){var l=r&&r.prototype instanceof c?r:c,u=Object.create(l.prototype);return cn(u,"_invoke",function(n,r,o){var a,c,l,u=0,s=o||[],m=!1,f={p:0,n:0,v:t,a:p,f:p.bind(t,4),d:function(e,n){return a=e,c=0,l=t,f.n=n,i}};function p(n,r){for(c=n,l=r,e=0;!m&&u&&!o&&e<s.length;e++){var o,a=s[e],p=f.p,d=a[2];n>3?(o=d===r)&&(l=a[(c=a[4])?5:(c=3,3)],a[4]=a[5]=t):a[0]<=p&&((o=n<2&&p<a[1])?(c=0,f.v=r,f.n=a[1]):p<d&&(o=n<3||a[0]>r||r>d)&&(a[4]=n,a[5]=r,f.n=d,c=0))}if(o||n>1)return i;throw m=!0,r}return function(o,s,d){if(u>1)throw TypeError("Generator is already running");for(m&&1===s&&p(s,d),c=s,l=d;(e=c<2?t:l)||!m;){a||(c?c<3?(c>1&&(f.n=-1),p(c,l)):f.n=l:f.v=l);try{if(u=2,a){if(c||(o="next"),e=a[o]){if(!(e=e.call(a,l)))throw TypeError("iterator result is not an object");if(!e.done)return e;l=e.value,c<2&&(c=0)}else 1===c&&(e=a.return)&&e.call(a),c<2&&(l=TypeError("The iterator does not provide a '"+o+"' method"),c=1);a=t}else if((e=(m=f.n<0)?l:n.call(r,f))!==i)break}catch(e){a=t,c=1,l=e}finally{u=1}}return{value:e,done:m}}}(n,o,a),!0),u}var i={};function c(){}function l(){}function u(){}e=Object.getPrototypeOf;var s=[][r]?e(e([][r]())):(cn(e={},r,function(){return this}),e),m=u.prototype=c.prototype=Object.create(s);function f(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,u):(t.__proto__=u,cn(t,o,"GeneratorFunction")),t.prototype=Object.create(m),t}return l.prototype=u,cn(m,"constructor",u),cn(u,"constructor",l),l.displayName="GeneratorFunction",cn(u,o,"GeneratorFunction"),cn(m),cn(m,o,"Generator"),cn(m,r,function(){return this}),cn(m,"toString",function(){return"[object Generator]"}),(an=function(){return{w:a,m:f}})()}function cn(t,e,n,r){var o=Object.defineProperty;try{o({},"",{})}catch(t){o=0}cn=function(t,e,n,r){function a(e,n){cn(t,e,function(t){return this._invoke(e,n,t)})}e?o?o(t,e,{value:n,enumerable:!r,configurable:!r,writable:!r}):t[e]=n:(a("next",0),a("throw",1),a("return",2))},cn(t,e,n,r)}function ln(t,e,n,r,o,a,i){try{var c=t[a](i),l=c.value}catch(t){return void n(t)}c.done?e(l):Promise.resolve(l).then(r,o)}function un(t){return function(){var e=this,n=arguments;return new Promise(function(r,o){var a=t.apply(e,n);function i(t){ln(a,r,o,i,c,"next",t)}function c(t){ln(a,r,o,i,c,"throw",t)}i(void 0)})}}var sn=function(t){return un(an().m(function e(){var n,r,o,a,i,c,l=arguments;return an().w(function(e){for(;;)switch(e.n){case 0:if(n=Ie()){e.n=1;break}return e.a(2);case 1:for(r=ht(),o=r.modalItems,Yt(o),n.dialog("option","width",p),a=l.length,i=new Array(a),c=0;c<a;c++)i[c]=l[c];return e.n=2,null==t?void 0:t.apply(void 0,i);case 2:n.dialog("open");case 3:return e.a(2)}},e)}))},mn={basic:["basic","plus","business","elite"],plus:["plus","business","elite"],business:["business","elite"],elite:["elite"]},fn=sn(function(t,e){var n=e.querySelector(".frm-form-template-name").textContent.trim(),r=ht(),o=r.upgradeModal,a=r.upgradeModalTemplateNames,i=r.upgradeModalPlansIcons,l=r.upgradeModalLink;a.forEach(function(t){return t.textContent=n}),i.forEach(function(e){var n=e.dataset.plan,r=mn[t].includes(n);e.classList.toggle("frm_green",r),e.querySelector("svg > use").setAttribute("href",r?"#frm_checkmark_icon":"#frm_close_icon")});var u=e.dataset.slug?"-".concat(e.dataset.slug):"";l.href=c+u,Kt(o)}),pn=sn(function(){var t=ht().renewAccountModal;Kt(t)}),dn=sn(function(){var t=ht().leaveEmailModal;Kt(t)}),yn=sn(function(){Ie().dialog("option","width",d);var t=ht().createTemplateModal;Kt(t)}),vn=function(t){!function(t,e,n){var r=document.querySelector("#frm_leave_email"),o=document.querySelector("#frm_leave_email_error");r&&o?(o.setAttribute("frm-error",n),Kt(o),r.addEventListener("keyup",function(){Xt(o)},{once:!0})):console.warn("showFormError: Unable to find input or error element.")}(0,0,t)};var hn;hn=function(){wp.hooks.doAction("frmFormTemplates.beforeInitialize",{getState:xt,setState:jt}),e&&(0,frmDom.ajax.doJsonFetch)("get_applications_data&view=templates").then(en),function(){je.apply(this,arguments)}(),ht().templateItems.forEach(function(t){t.getAttribute("data-categories").split(",").forEach(function(e){Pe[e]||(Pe[e]=[]),Pe[e].push(t)})}),Te(),tn(),wp.hooks.doAction("frmFormTemplates.afterInitialize",{getState:xt,setState:jt}),wp.hooks.doAction("frm_new_form_modal_form",jQuery("#frm-new-template"))},"undefined"!=typeof document&&("complete"!==document.readyState&&"interactive"!==document.readyState?document.addEventListener("DOMContentLoaded",hn):hn())})()})();
>>>>>>> master
