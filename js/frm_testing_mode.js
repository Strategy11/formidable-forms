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

/***/ "./js/src/admin/addon-state.js":
/*!*************************************!*\
  !*** ./js/src/admin/addon-state.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addonError: () => (/* binding */ addonError),
/* harmony export */   afterAddonInstall: () => (/* binding */ afterAddonInstall),
/* harmony export */   extractErrorFromAddOnResponse: () => (/* binding */ extractErrorFromAddOnResponse),
/* harmony export */   toggleAddonState: () => (/* binding */ toggleAddonState)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");

var _frmDom = frmDom,
  div = _frmDom.div,
  svg = _frmDom.svg;

/**
 * Toggles the state of an add-on (ie. enable or disable an add-on).
 *
 * @param {Element} clicked
 * @param {string}  action
 */
function toggleAddonState(clicked, action) {
  var _window$ajaxurl;
  var ajaxurl = (_window$ajaxurl = window.ajaxurl) !== null && _window$ajaxurl !== void 0 ? _window$ajaxurl : frm_js.ajax_url; // eslint-disable-line camelcase

  // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
  jQuery('.frm-addon-error').remove();
  var button = jQuery(clicked);
  var plugin = button.attr('rel');
  var el = button.parent();
  var message = el.parent().find('.addon-status-label');
  button.addClass('frm_loading_button');

  // Process the Ajax to perform the activation.
  jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    async: true,
    cache: false,
    dataType: 'json',
    data: {
      action: action,
      nonce: frmGlobal.nonce,
      plugin: plugin
    },
    success: function success(response) {
      var _response$data, _response;
      response = (_response$data = (_response = response) === null || _response === void 0 ? void 0 : _response.data) !== null && _response$data !== void 0 ? _response$data : response;
      var saveAndReload;
      if ('string' !== typeof response && 'string' === typeof response.message) {
        if ('undefined' !== typeof response.saveAndReload) {
          saveAndReload = response.saveAndReload;
        }
        response = response.message;
      }
      var error = extractErrorFromAddOnResponse(response);
      if (error) {
        addonError(error, el, button);
        return;
      }
      afterAddonInstall(response, button, message, el, saveAndReload, action);

      /**
       * Trigger an action after successfully toggling the addon state.
       *
       * @param {Object} response
       */
      wp.hooks.doAction('frm_update_addon_state', response);
    },
    error: function error() {
      button.removeClass('frm_loading_button');
    }
  });
}
function extractErrorFromAddOnResponse(response) {
  if (typeof response !== 'string') {
    if (typeof response.success !== 'undefined' && response.success) {
      return false;
    }
    if (response.form) {
      if (jQuery(response.form).is('#message')) {
        return {
          message: jQuery(response.form).find('p').html()
        };
      }
    }
    return response;
  }
  return false;
}
function afterAddonInstall(response, button, message, el, saveAndReload) {
  var action = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 'frm_activate_addon';
  var frmAdminJs = frm_admin_js; // eslint-disable-line camelcase

  var addonStatuses = document.querySelectorAll('.frm-addon-status');
  addonStatuses.forEach(function (addonStatus) {
    addonStatus.textContent = response;
    addonStatus.style.display = 'block';
  });

  // The Ajax request was successful, so let's update the output.
  button.css({
    opacity: '0'
  });
  document.querySelectorAll('.frm-oneclick').forEach(function (oneClick) {
    oneClick.style.display = 'none';
  });
  showUpgradeModalSuccess();

  // Proceed with CSS changes
  var actionMap = {
    frm_activate_addon: {
      class: 'frm-addon-active',
      message: frmAdminJs.active
    },
    frm_deactivate_addon: {
      class: 'frm-addon-installed',
      message: frmAdminJs.installed
    },
    frm_uninstall_addon: {
      class: 'frm-addon-not-installed',
      message: frmAdminJs.not_installed
    }
  };
  actionMap.frm_install_addon = actionMap.frm_activate_addon;
  var messageElement = message[0];
  if (messageElement) {
    messageElement.textContent = actionMap[action].message;
  }
  var parentElement = el[0].parentElement;
  parentElement.classList.remove('frm-addon-not-installed', 'frm-addon-installed', 'frm-addon-active');
  parentElement.classList.add(actionMap[action].class);
  var buttonElement = button[0];
  buttonElement.classList.remove('frm_loading_button');

  // Maybe refresh import and SMTP pages
  var refreshPage = document.querySelectorAll('.frm-admin-page-import, #frm-admin-smtp, #frm-welcome');
  if (refreshPage.length > 0) {
    window.location.reload();
    return;
  }
  if (['settings', 'form_builder'].includes(saveAndReload)) {
    addonStatuses.forEach(function (addonStatus) {
      var inModal = null !== addonStatus.closest('#frm_upgrade_modal');
      addonStatus.appendChild(getSaveAndReloadSettingsOptions(saveAndReload, inModal));
    });
  }
}
function addonError(response, el, button) {
  if (response.form) {
    jQuery('.frm-inline-error').remove();
    button.closest('.frm-card').html(response.form).css({
      padding: 5
    }).find('#upgrade').attr('rel', button.attr('rel')).on('click', installAddonWithCreds);
  } else {
    el.append('<div class="frm-addon-error frm_error_style"><p><strong>' + response.message + '</strong></p></div>');
    button.removeClass('frm_loading_button');
    jQuery('.frm-addon-error').delay(4000).fadeOut();
  }
}
function getSaveAndReloadSettingsOptions(saveAndReload, inModal) {
  var className = 'frm-save-and-reload-options';
  var children = [saveAndReloadSettingsButton(saveAndReload)];
  if (inModal) {
    children.push(closePopupButton());
  }
  return div({
    className: className,
    children: children
  });
}
function saveAndReloadSettingsButton(saveAndReload) {
  var button = document.createElement('button');
  button.classList.add('frm-save-and-reload', 'button', 'button-primary', 'frm-button-primary');
  button.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Save and Reload', 'formidable');
  button.addEventListener('click', function () {
    if (saveAndReload === 'form_builder') {
      saveAndReloadFormBuilder();
    } else if (saveAndReload === 'settings') {
      saveAndReloadSettings();
    }
  });
  return button;
}
function saveAndReloadSettings() {
  var page = document.getElementById('form_settings_page');
  if (null !== page) {
    var form = page.querySelector('form.frm_form_settings');
    if (null !== form) {
      wp.hooks.doAction('frm_reset_fields_updated');
      form.submit();
    }
  }
}
function closePopupButton() {
  var a = document.createElement('a');
  a.setAttribute('href', '#');
  a.classList.add('button', 'button-secondary', 'frm-button-secondary', 'dismiss');
  a.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Not Now', 'formidable');
  return a;
}
function saveAndReloadFormBuilder() {
  var submitButton = document.getElementById('frm_submit_side_top');
  if (submitButton.classList.contains('frm_submit_ajax')) {
    submitButton.setAttribute('data-new-addon-installed', true);
  }
  submitButton.click();
}

/**
 * Updates the upgrade modal to show successful addon installation state.
 *
 * @private
 * @return {void}
 */
function showUpgradeModalSuccess() {
  var upgradeModal = document.getElementById('frm_upgrade_modal');
  if (!upgradeModal) {
    return;
  }
  upgradeModal.classList.add('frm-success');
  var upgradeMessage = upgradeModal.querySelector('.frm-upgrade-message');
  if (upgradeMessage) {
    var image = upgradeMessage.querySelector('img');
    upgradeMessage.replaceChildren((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Great! Everything\'s ready to go!', 'formidable'), document.createElement('br'), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You just need to refresh the builder so the new field becomes available.', 'formidable'));
    if (image) {
      upgradeMessage.append(image);
    }
  }
  var frmAddonStatus = document.querySelector('.frm-addon-status');
  if (frmAddonStatus) {
    frmAddonStatus.textContent = '';
  }
  var circledIcon = upgradeModal.querySelector('.frm-circled-icon');
  if (circledIcon) {
    var _circledIcon$querySel;
    circledIcon.classList.add('frm-circled-icon-green');
    (_circledIcon$querySel = circledIcon.querySelector('svg')) === null || _circledIcon$querySel === void 0 || _circledIcon$querySel.replaceWith(svg({
      href: '#frm_checkmark_icon'
    }));
  }
}

/***/ }),

/***/ "./js/src/admin/upgrade-popup.js":
/*!***************************************!*\
  !*** ./js/src/admin/upgrade-popup.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addOneClick: () => (/* binding */ addOneClick),
/* harmony export */   initModal: () => (/* binding */ initModal),
/* harmony export */   initUpgradeModal: () => (/* binding */ initUpgradeModal)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");

var _frmDom = frmDom,
  svg = _frmDom.svg;
function getShowLinkHrefValue(link, showLink) {
  var customLink = link.getAttribute('data-link');
  if (customLink === null || typeof customLink === 'undefined' || customLink === '') {
    customLink = showLink.getAttribute('data-default');
  }
  return customLink;
}

/**
 * Allow addons to be installed from the upgrade modal.
 *
 * @param {Element}          link
 * @param {string}           context      Either 'modal' or 'tab'.
 * @param {string|undefined} upgradeLabel
 */
function addOneClick(link, context, upgradeLabel) {
  var container;
  if ('modal' === context) {
    container = document.getElementById('frm_upgrade_modal');
  } else if ('tab' === context) {
    container = document.getElementById(link.getAttribute('href').substr(1));
  } else {
    return;
  }
  var oneclickMessage = container.querySelector('.frm-oneclick');
  var upgradeMessage = container.querySelector('.frm-upgrade-message');
  var showLink = container.querySelector('.frm-upgrade-link');
  var button = container.querySelector('.frm-oneclick-button');
  var addonStatus = container.querySelector('.frm-addon-status');
  var oneclick = link.getAttribute('data-oneclick');
  var newMessage = link.getAttribute('data-message');
  var showIt = 'block';
  var showMsg = 'block';
  var hideIt = 'none';
  var modalIconWrapper = container.querySelector('.frm-circled-icon');
  if (modalIconWrapper) {
    var _modalIconWrapper$que;
    modalIconWrapper.classList.remove('frm-circled-icon-green');
    (_modalIconWrapper$que = modalIconWrapper.querySelector('svg')) === null || _modalIconWrapper$que === void 0 || _modalIconWrapper$que.replaceWith(svg({
      href: '#frm_filled_lock_icon'
    }));
  }
  var learnMoreLink = container.querySelector('.frm-learn-more');
  if (learnMoreLink) {
    learnMoreLink.href = link.dataset.learnMore;
  }

  // If one click upgrade, hide other content.
  if (oneclickMessage !== null && typeof oneclick !== 'undefined' && oneclick) {
    if (newMessage === null) {
      showMsg = 'none';
    }
    showIt = 'none';
    hideIt = 'block';
    oneclick = JSON.parse(oneclick);
    button.className = button.className.replace(' frm-install-addon', '').replace(' frm-activate-addon', '');
    button.className = button.className + ' ' + oneclick.class;
    button.rel = oneclick.url;
    oneclickMessage.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('This plugin is not activated. Would you like to activate it now?', 'formidable');
    button.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Activate', 'formidable');
    var linkIcon = link.querySelector('use');
    if (linkIcon) {
      modalIconWrapper === null || modalIconWrapper === void 0 || modalIconWrapper.querySelector('svg').replaceWith(svg({
        href: linkIcon.getAttribute('href') || linkIcon.getAttribute('xlink:href'),
        // Get the icon from xlink:href if it has not been updated to use href
        classList: ['frm_svg32']
      }));
    }
  }
  if (!newMessage) {
    newMessage = upgradeMessage.getAttribute('data-default');
  }
  if (undefined !== upgradeLabel) {
    newMessage = newMessage.replace('<span class="frm_feature_label"></span>', upgradeLabel);
  }
  upgradeMessage.innerHTML = newMessage;
  if (link.dataset.upsellImage) {
    upgradeMessage.appendChild(frmDom.img({
      src: link.dataset.upsellImage,
      alt: link.dataset.upgrade
    }));
  }

  // Either set the link or use the default.
  showLink.href = getShowLinkHrefValue(link, showLink);
  addonStatus.style.display = 'none';
  oneclickMessage.style.display = hideIt;
  button.style.display = hideIt === 'block' ? 'inline-block' : hideIt;
  upgradeMessage.style.display = showMsg;
  showLink.style.display = showIt === 'block' ? 'inline-block' : showIt;
  var showLinkParent = showLink.closest('.frm-upgrade-modal-actions');
  if (showLinkParent) {
    showLinkParent.style.display = showIt === 'block' ? 'flex' : showIt;
  }
}
function initModal(id, width) {
  var $info = jQuery(id);
  if (!$info.length) {
    return false;
  }
  if (typeof width === 'undefined') {
    width = '552px';
  }
  var dialogArgs = {
    dialogClass: 'frm-dialog',
    modal: true,
    autoOpen: false,
    closeOnEscape: true,
    width: width,
    resizable: false,
    draggable: false,
    open: function open() {
      jQuery('.ui-dialog-titlebar').addClass('frm_hidden').removeClass('ui-helper-clearfix');
      jQuery('#wpwrap').addClass('frm_overlay');
      jQuery('.frm-dialog').removeClass('ui-widget ui-widget-content ui-corner-all');
      $info.removeClass('ui-dialog-content ui-widget-content');
      bindClickForDialogClose($info);
    },
    close: function close() {
      jQuery('#wpwrap').removeClass('frm_overlay');
      jQuery('.spinner').css('visibility', 'hidden');
      this.removeAttribute('data-option-type');
      var optionType = document.getElementById('bulk-option-type');
      if (optionType) {
        optionType.value = '';
      }
    }
  };
  $info.dialog(dialogArgs);
  return $info;
}
function bindClickForDialogClose($modal) {
  var closeModal = function closeModal() {
    $modal.dialog('close');
  };
  jQuery('.ui-widget-overlay').on('click', closeModal);
  $modal.on('click', 'a.dismiss', closeModal);
}
function initUpgradeModal() {
  var $info = initModal('#frm_upgrade_modal');
  if ($info === false) {
    return;
  }
  document.addEventListener('click', handleUpgradeClick);
  frmDom.util.documentOn('change', 'select.frm_select_with_upgrade', handleUpgradeClick);
  function handleUpgradeClick(event) {
    var element, link, content;
    element = event.target;
    if (!element.classList) {
      return;
    }
    var showExpiredModal = element.classList.contains('frm_show_expired_modal') || null !== element.querySelector('.frm_show_expired_modal') || element.closest('.frm_show_expired_modal');

    // If a `select` element is clicked, check if the selected option has a 'data-upgrade' attribute
    if (event.type === 'change' && element.classList.contains('frm_select_with_upgrade')) {
      var selectedOption = element.options[element.selectedIndex];
      if (selectedOption && selectedOption.dataset.upgrade) {
        element = selectedOption;
      }
    }
    if (!element.dataset.upgrade) {
      var parent = element.closest('[data-upgrade]');
      if (!parent) {
        parent = element.closest('.frm_field_box');
        if (!parent) {
          return;
        }
        // Fake it if it's missing to avoid error.
        element.dataset.upgrade = '';
      }
      element = parent;
    }
    if (showExpiredModal) {
      var hookName = 'frm_show_expired_modal';
      wp.hooks.doAction(hookName, element);
      return;
    }
    var upgradeLabel = element.dataset.upgrade;
    if (!upgradeLabel || element.classList.contains('frm_show_upgrade_tab')) {
      return;
    }
    event.preventDefault();
    var modal = $info.get(0);
    var lockIcon = modal.querySelector('.frm_lock_icon');
    if (lockIcon) {
      lockIcon.style.display = 'block';
      lockIcon.classList.remove('frm_lock_open_icon');
      lockIcon.querySelector('use').setAttribute('href', '#frm_lock_icon');
    }
    var upgradeImageId = 'frm_upgrade_modal_image';
    var oldImage = document.getElementById(upgradeImageId);
    if (oldImage) {
      oldImage.remove();
    }
    if (element.dataset.image) {
      if (lockIcon) {
        lockIcon.style.display = 'none';
      }
      lockIcon.parentNode.insertBefore(frmDom.img({
        id: upgradeImageId,
        src: frmGlobal.url + '/images/' + element.dataset.image
      }), lockIcon);
    }
    var level = modal.querySelector('.license-level');
    if (level) {
      level.textContent = getRequiredLicenseFromTrigger(element);
    }

    // If one click upgrade, hide other content
    addOneClick(element, 'modal', upgradeLabel);
    modal.querySelector('.frm_are_not_installed').style.display = element.dataset.image || element.dataset.oneclick ? 'none' : 'inline-block';
    modal.querySelector('.frm-upgrade-modal-title-prefix').style.display = element.dataset.oneclick ? 'inline' : 'none';
    modal.querySelector('.frm_feature_label').textContent = upgradeLabel;
    modal.querySelector('.frm-upgrade-modal-title-suffix').style.display = 'none';
    modal.querySelector('h2').style.display = 'block';
    $info.dialog('open');

    // set the utm medium
    var button = modal.querySelector('.button-primary:not(.frm-oneclick-button)');
    link = button.getAttribute('href').replace(/(medium=)[a-z_-]+/ig, '$1' + element.getAttribute('data-medium'));
    content = element.getAttribute('data-content');
    if (content === null) {
      content = '';
    }
    link = link.replace(/(content=)[a-z_-]+/ig, '$1' + content);
    button.setAttribute('href', link);
  }
}
function getRequiredLicenseFromTrigger(element) {
  if (element.dataset.requires) {
    return element.dataset.requires;
  }
  return 'Pro';
}

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
/*!************************************!*\
  !*** ./js/src/frm_testing_mode.js ***!
  \************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
(function () {
  var _require = __webpack_require__(/*! ./admin/addon-state */ "./js/src/admin/addon-state.js"),
    toggleAddonState = _require.toggleAddonState;
  function onReady() {
    jQuery(document).on('click', '#frm_upgrade_modal .frm-install-addon', installAddon);
    jQuery(document).on('click', '#frm_upgrade_modal .frm-activate-addon', activateAddon);
    function activateAddon(e) {
      e.preventDefault();
      toggleAddonState(this, 'frm_activate_addon');
    }
    function installAddon(e) {
      e.preventDefault();
      toggleAddonState(this, 'frm_install_addon');
    }
    initUpgradeModal();
    setupBootstrapDropdowns();
    jQuery(document).on('mouseenter.frm', '.frm_help', function () {
      jQuery(this).off('mouseenter.frm');
      jQuery(this).tooltip('show');
    });
  }
  function initUpgradeModal() {
    return _initUpgradeModal.apply(this, arguments);
  }
  function _initUpgradeModal() {
    _initUpgradeModal = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
      var upgradePopup;
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            _context.next = 2;
            return Promise.resolve(/*! import() */).then(__webpack_require__.bind(__webpack_require__, /*! ./admin/upgrade-popup */ "./js/src/admin/upgrade-popup.js"));
          case 2:
            upgradePopup = _context.sent;
            upgradePopup.initUpgradeModal();
          case 4:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    return _initUpgradeModal.apply(this, arguments);
  }
  function setupBootstrapDropdowns() {
    var frmDom = window.frmDom;
    frmDom.bootstrap.setupBootstrapDropdowns(function () {
      var toggle = document.querySelector('#frm_testmode_enabled_form_actions .dropdown-toggle');
      if (toggle) {
        toggle.classList.add('frm-dropdown-toggle');
        if (!toggle.hasAttribute('role')) {
          toggle.setAttribute('role', 'button');
        }
        if (!toggle.hasAttribute('tabindex')) {
          toggle.setAttribute('tabindex', 0);
        }
      }
    });
    var element = document.getElementById('frm_testmode_enabled_form_actions');
    if (element) {
      element.style.display = 'none';
      frmDom.bootstrap.multiselect.init.bind(element)();
      if (element.disabled) {
        element.parentElement.querySelector('.dropdown-toggle').classList.add('frm_noallow');
      }
    }
  }
  if (document.readyState === 'complete') {
    onReady();
  } else {
    document.addEventListener('DOMContentLoaded', onReady);
  }
  document.addEventListener('frm_after_start_over', function () {
    setupBootstrapDropdowns();
  });
  jQuery(document).on('frmPageChanged frmFormComplete', function () {
    setupBootstrapDropdowns();
  });
})();
/******/ })()
;
//# sourceMappingURL=frm_testing_mode.js.map
=======
/*! For license information please see frm_testing_mode.js.LICENSE.txt */
(()=>{var e={65:(e,t,r)=>{"use strict";r.d(t,{A:()=>i});var n=r(8793),o=r(1323);function i(e){var t=(0,n.A)(e);return function(e){return(0,o.A)(t,e)}}},1323:(e,t,r)=>{"use strict";r.d(t,{A:()=>o});var n={"!":function(e){return!e},"*":function(e,t){return e*t},"/":function(e,t){return e/t},"%":function(e,t){return e%t},"+":function(e,t){return e+t},"-":function(e,t){return e-t},"<":function(e,t){return e<t},"<=":function(e,t){return e<=t},">":function(e,t){return e>t},">=":function(e,t){return e>=t},"==":function(e,t){return e===t},"!=":function(e,t){return e!==t},"&&":function(e,t){return e&&t},"||":function(e,t){return e||t},"?:":function(e,t,r){if(e)throw t;return r}};function o(e,t){var r,o,i,a,s,l,c=[];for(r=0;r<e.length;r++){if(s=e[r],a=n[s]){for(o=a.length,i=Array(o);o--;)i[o]=c.pop();try{l=a.apply(null,i)}catch(e){return e}}else l=t.hasOwnProperty(s)?t[s]:+s;c.push(l)}return c[0]}},1364:(e,t,r)=>{"use strict";r.d(t,{A:()=>o});var n=r(65);function o(e){var t=(0,n.A)(e);return function(e){return+t({n:e})}}},8793:(e,t,r)=>{"use strict";var n,o,i,a;function s(e){for(var t,r,s,l,c=[],u=[];t=e.match(a);){for(r=t[0],(s=e.substr(0,t.index).trim())&&c.push(s);l=u.pop();){if(i[r]){if(i[r][0]===l){r=i[r][1]||r;break}}else if(o.indexOf(l)>=0||n[l]<n[r]){u.push(l);break}c.push(l)}i[r]||u.push(r),e=e.substr(t.index+r.length)}return(e=e.trim())&&c.push(e),c.concat(u.reverse())}r.d(t,{A:()=>s}),n={"(":9,"!":8,"*":7,"/":7,"%":7,"+":6,"-":6,"<":5,"<=":5,">":5,">=":5,"==":4,"!=":4,"&&":3,"||":2,"?":1,"?:":1},o=["(","?"],i={")":["("],":":["?","?:"]},a=/<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/},7521:(e,t,r)=>{"use strict";r.d(t,{A:()=>i});var n=r(6956),o=r(7395);const i=function(e,t){return function(r,i,a){var s=arguments.length>3&&void 0!==arguments[3]?arguments[3]:10,l=e[t];if((0,o.A)(r)&&(0,n.A)(i))if("function"==typeof a)if("number"==typeof s){var c={callback:a,priority:s,namespace:i};if(l[r]){var u,d=l[r].handlers;for(u=d.length;u>0&&!(s>=d[u-1].priority);u--);u===d.length?d[u]=c:d.splice(u,0,c),l.__current.forEach(function(e){e.name===r&&e.currentIndex>=u&&e.currentIndex++})}else l[r]={handlers:[c],runs:0};"hookAdded"!==r&&e.doAction("hookAdded",r,i,a,s)}else console.error("If specified, the hook priority must be a number.");else console.error("The hook callback must be a function.")}}},11:(e,t,r)=>{"use strict";r.d(t,{A:()=>n});const n=function(e,t){return function(){var r,n,o=e[t];return null!==(r=null===(n=o.__current[o.__current.length-1])||void 0===n?void 0:n.name)&&void 0!==r?r:null}}},5375:(e,t,r)=>{"use strict";r.d(t,{A:()=>o});var n=r(7395);const o=function(e,t){return function(r){var o=e[t];if((0,n.A)(r))return o[r]&&o[r].runs?o[r].runs:0}}},3561:(e,t,r)=>{"use strict";r.d(t,{A:()=>n});const n=function(e,t){return function(r){var n=e[t];return void 0===r?void 0!==n.__current[0]:!!n.__current[0]&&r===n.__current[0].name}}},8830:(e,t,r)=>{"use strict";r.d(t,{A:()=>n});const n=function(e,t){return function(r,n){var o=e[t];return void 0!==n?r in o&&o[r].handlers.some(function(e){return e.namespace===n}):r in o}}},7765:(e,t,r)=>{"use strict";r.d(t,{A:()=>f});var n=r(3029),o=r(7521),i=r(4194),a=r(8830),s=r(6763),l=r(11),c=r(3561),u=r(5375),d=function e(){(0,n.A)(this,e),this.actions=Object.create(null),this.actions.__current=[],this.filters=Object.create(null),this.filters.__current=[],this.addAction=(0,o.A)(this,"actions"),this.addFilter=(0,o.A)(this,"filters"),this.removeAction=(0,i.A)(this,"actions"),this.removeFilter=(0,i.A)(this,"filters"),this.hasAction=(0,a.A)(this,"actions"),this.hasFilter=(0,a.A)(this,"filters"),this.removeAllActions=(0,i.A)(this,"actions",!0),this.removeAllFilters=(0,i.A)(this,"filters",!0),this.doAction=(0,s.A)(this,"actions"),this.applyFilters=(0,s.A)(this,"filters",!0),this.currentAction=(0,l.A)(this,"actions"),this.currentFilter=(0,l.A)(this,"filters"),this.doingAction=(0,c.A)(this,"actions"),this.doingFilter=(0,c.A)(this,"filters"),this.didAction=(0,u.A)(this,"actions"),this.didFilter=(0,u.A)(this,"filters")};const f=function(){return new d}},4194:(e,t,r)=>{"use strict";r.d(t,{A:()=>i});var n=r(6956),o=r(7395);const i=function(e,t){var r=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return function(i,a){var s=e[t];if((0,o.A)(i)&&(r||(0,n.A)(a))){if(!s[i])return 0;var l=0;if(r)l=s[i].handlers.length,s[i]={runs:s[i].runs,handlers:[]};else for(var c=s[i].handlers,u=function(e){c[e].namespace===a&&(c.splice(e,1),l++,s.__current.forEach(function(t){t.name===i&&t.currentIndex>=e&&t.currentIndex--}))},d=c.length-1;d>=0;d--)u(d);return"hookRemoved"!==i&&e.doAction("hookRemoved",i,a),l}}}},6763:(e,t,r)=>{"use strict";r.d(t,{A:()=>n});const n=function(e,t){var r=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return function(n){var o=e[t];o[n]||(o[n]={handlers:[],runs:0}),o[n].runs++;for(var i=o[n].handlers,a=arguments.length,s=new Array(a>1?a-1:0),l=1;l<a;l++)s[l-1]=arguments[l];if(!i||!i.length)return r?s[0]:void 0;var c={name:n,currentIndex:0};for(o.__current.push(c);c.currentIndex<i.length;){var u=i[c.currentIndex].callback.apply(null,s);r&&(s[0]=u),c.currentIndex++}return o.__current.pop(),r?s[0]:void 0}}},2133:(e,t,r)=>{"use strict";r.d(t,{se:()=>n});var n=(0,r(7765).A)();n.addAction,n.addFilter,n.removeAction,n.removeFilter,n.hasAction,n.hasFilter,n.removeAllActions,n.removeAllFilters,n.doAction,n.applyFilters,n.currentAction,n.currentFilter,n.doingAction,n.doingFilter,n.didAction,n.didFilter,n.actions,n.filters},7395:(e,t,r)=>{"use strict";r.d(t,{A:()=>n});const n=function(e){return"string"!=typeof e||""===e?(console.error("The hook name must be a non-empty string."),!1):/^__/.test(e)?(console.error("The hook name cannot begin with `__`."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(e)||(console.error("The hook name can only contain numbers, letters, dashes, periods and underscores."),!1)}},6956:(e,t,r)=>{"use strict";r.d(t,{A:()=>n});const n=function(e){return"string"!=typeof e||""===e?(console.error("The namespace must be a non-empty string."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(e)||(console.error("The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."),!1)}},772:(e,t,r)=>{"use strict";r.d(t,{h:()=>c});var n=r(4467),o=r(5397);function i(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),r.push.apply(r,n)}return r}function a(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?i(Object(r),!0).forEach(function(t){(0,n.A)(e,t,r[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):i(Object(r)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))})}return e}var s={"":{plural_forms:function(e){return 1===e?0:1}}},l=/^i18n\.(n?gettext|has_translation)(_|$)/,c=function(e,t,r){var n=new o.A({}),i=new Set,c=function(){i.forEach(function(e){return e()})},u=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"default";n.data[t]=a(a(a({},s),n.data[t]),e),n.data[t][""]=a(a({},s[""]),n.data[t][""])},d=function(e,t){u(e,t),c()},f=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default",t=arguments.length>1?arguments[1]:void 0,r=arguments.length>2?arguments[2]:void 0,o=arguments.length>3?arguments[3]:void 0,i=arguments.length>4?arguments[4]:void 0;return n.data[e]||u(void 0,e),n.dcnpgettext(e,t,r,o,i)},p=function(){return arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default"},m=function(e,t,n){var o=f(n,t,e);return r?(o=r.applyFilters("i18n.gettext_with_context",o,e,t,n),r.applyFilters("i18n.gettext_with_context_"+p(n),o,e,t,n)):o};if(e&&d(e,t),r){var v=function(e){l.test(e)&&c()};r.addAction("hookAdded","core/i18n",v),r.addAction("hookRemoved","core/i18n",v)}return{getLocaleData:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default";return n.data[e]},setLocaleData:d,resetLocaleData:function(e,t){n.data={},n.pluralForms={},d(e,t)},subscribe:function(e){return i.add(e),function(){return i.delete(e)}},__:function(e,t){var n=f(t,void 0,e);return r?(n=r.applyFilters("i18n.gettext",n,e,t),r.applyFilters("i18n.gettext_"+p(t),n,e,t)):n},_x:m,_n:function(e,t,n,o){var i=f(o,void 0,e,t,n);return r?(i=r.applyFilters("i18n.ngettext",i,e,t,n,o),r.applyFilters("i18n.ngettext_"+p(o),i,e,t,n,o)):i},_nx:function(e,t,n,o,i){var a=f(i,o,e,t,n);return r?(a=r.applyFilters("i18n.ngettext_with_context",a,e,t,n,o,i),r.applyFilters("i18n.ngettext_with_context_"+p(i),a,e,t,n,o,i)):a},isRTL:function(){return"rtl"===m("ltr","text direction")},hasTranslation:function(e,t,o){var i,a,s=t?t+""+e:e,l=!(null===(i=n.data)||void 0===i||null===(a=i[null!=o?o:"default"])||void 0===a||!a[s]);return r&&(l=r.applyFilters("i18n.has_translation",l,e,t,o),l=r.applyFilters("i18n.has_translation_"+p(o),l,e,t,o)),l}}}},5839:(e,t,r)=>{"use strict";r.d(t,{__:()=>a});var n=r(772),o=r(2133),i=(0,n.h)(void 0,void 0,o.se),a=(i.getLocaleData.bind(i),i.setLocaleData.bind(i),i.resetLocaleData.bind(i),i.subscribe.bind(i),i.__.bind(i));i._x.bind(i),i._n.bind(i),i._nx.bind(i),i.isRTL.bind(i),i.hasTranslation.bind(i)},9575:(e,t,r)=>{"use strict";r.d(t,{__:()=>n.__}),r(181),r(772);var n=r(5839)},181:(e,t,r)=>{"use strict";var n=r(8616),o=r.n(n);r(7604),o()(console.error)},1105:(e,t,r)=>{"use strict";r.r(t),r.d(t,{addonError:()=>u,afterAddonInstall:()=>c,extractErrorFromAddOnResponse:()=>l,toggleAddonState:()=>s});var n=r(9575),o=frmDom,i=o.div,a=o.svg;function s(e,t){var r,n=null!==(r=window.ajaxurl)&&void 0!==r?r:frm_js.ajax_url;jQuery(".frm-addon-error").remove();var o=jQuery(e),i=o.attr("rel"),a=o.parent(),s=a.parent().find(".addon-status-label");o.addClass("frm_loading_button"),jQuery.ajax({url:n,type:"POST",async:!0,cache:!1,dataType:"json",data:{action:t,nonce:frmGlobal.nonce,plugin:i},success:function(e){var r,n,i;"string"!=typeof(e=null!==(r=null===(n=e)||void 0===n?void 0:n.data)&&void 0!==r?r:e)&&"string"==typeof e.message&&(void 0!==e.saveAndReload&&(i=e.saveAndReload),e=e.message);var d=l(e);d?u(d,a,o):(c(e,o,s,a,i,t),wp.hooks.doAction("frm_update_addon_state",e))},error:function(){o.removeClass("frm_loading_button")}})}function l(e){return"string"!=typeof e&&(void 0===e.success||!e.success)&&(e.form&&jQuery(e.form).is("#message")?{message:jQuery(e.form).find("p").html()}:e)}function c(e,t,r,o,s){var l=arguments.length>5&&void 0!==arguments[5]?arguments[5]:"frm_activate_addon",c=frm_admin_js,u=document.querySelectorAll(".frm-addon-status");u.forEach(function(t){t.textContent=e,t.style.display="block"}),t.css({opacity:"0"}),document.querySelectorAll(".frm-oneclick").forEach(function(e){e.style.display="none"}),function(){var e=document.getElementById("frm_upgrade_modal");if(e){e.classList.add("frm-success");var t=e.querySelector(".frm-upgrade-message");if(t){var r=t.querySelector("img");t.replaceChildren((0,n.__)("Great! Everything's ready to go!","formidable"),document.createElement("br"),(0,n.__)("You just need to refresh the builder so the new field becomes available.","formidable")),r&&t.append(r)}var o=document.querySelector(".frm-addon-status");o&&(o.textContent="");var i,s=e.querySelector(".frm-circled-icon");if(s)s.classList.add("frm-circled-icon-green"),null===(i=s.querySelector("svg"))||void 0===i||i.replaceWith(a({href:"#frm_checkmark_icon"}))}}();var f={frm_activate_addon:{class:"frm-addon-active",message:c.active},frm_deactivate_addon:{class:"frm-addon-installed",message:c.installed},frm_uninstall_addon:{class:"frm-addon-not-installed",message:c.not_installed}};f.frm_install_addon=f.frm_activate_addon;var p=r[0];p&&(p.textContent=f[l].message);var m=o[0].parentElement;m.classList.remove("frm-addon-not-installed","frm-addon-installed","frm-addon-active"),m.classList.add(f[l].class),t[0].classList.remove("frm_loading_button"),document.querySelectorAll(".frm-admin-page-import, #frm-admin-smtp, #frm-welcome").length>0?window.location.reload():["settings","form_builder"].includes(s)&&u.forEach(function(e){var t=null!==e.closest("#frm_upgrade_modal");e.append(function(e,t){var r,o=[d(e)];return t&&o.push(((r=document.createElement("a")).setAttribute("href","#"),r.classList.add("button","button-secondary","frm-button-secondary","dismiss"),r.textContent=(0,n.__)("Not Now","formidable"),r)),i({className:"frm-save-and-reload-options",children:o})}(s,t))})}function u(e,t,r){e.form?(jQuery(".frm-inline-error").remove(),r.closest(".frm-card").html(e.form).css({padding:5}).find("#upgrade").attr("rel",r.attr("rel")).on("click",installAddonWithCreds)):(t.append('<div class="frm-addon-error frm_error_style"><p><strong>'+e.message+"</strong></p></div>"),r.removeClass("frm_loading_button"),jQuery(".frm-addon-error").delay(4e3).fadeOut())}function d(e){var t=document.createElement("button");return t.classList.add("frm-save-and-reload","button","button-primary","frm-button-primary"),t.textContent=(0,n.__)("Save and Reload","formidable"),t.addEventListener("click",function(){var t;"form_builder"===e?((t=document.getElementById("frm_submit_side_top")).classList.contains("frm_submit_ajax")&&t.setAttribute("data-new-addon-installed",!0),t.click()):"settings"===e&&function(){var e=document.getElementById("form_settings_page");if(null!==e){var t=e.querySelector("form.frm_form_settings");null!==t&&(wp.hooks.doAction("frm_reset_fields_updated"),t.submit())}}()}),t}},4260:(e,t,r)=>{"use strict";r.r(t),r.d(t,{addOneClick:()=>i,initModal:()=>a,initUpgradeModal:()=>s});var n=r(9575),o=frmDom.svg;function i(e,t,r){var i;if("modal"===t)i=document.getElementById("frm_upgrade_modal");else{if("tab"!==t)return;i=document.getElementById(e.getAttribute("href").substr(1))}var a,s=i.querySelector(".frm-oneclick"),l=i.querySelector(".frm-upgrade-message"),c=i.querySelector(".frm-upgrade-link"),u=i.querySelector(".frm-oneclick-button"),d=i.querySelector(".frm-addon-status"),f=e.getAttribute("data-oneclick"),p=e.getAttribute("data-message"),m="block",v="block",h="none",g=i.querySelector(".frm-circled-icon");g&&(g.classList.remove("frm-circled-icon-green"),null===(a=g.querySelector("svg"))||void 0===a||a.replaceWith(o({href:"#frm_filled_lock_icon"})));var y=i.querySelector(".frm-learn-more");if(y&&(y.href=e.dataset.learnMore),null!==s&&void 0!==f&&f){null===p&&(v="none"),m="none",h="block",f=JSON.parse(f),u.className=u.className.replace(" frm-install-addon","").replace(" frm-activate-addon",""),u.className=u.className+" "+f.class,u.rel=f.url,s.textContent=(0,n.__)("This plugin is not activated. Would you like to activate it now?","formidable"),u.textContent=(0,n.__)("Activate","formidable");var _=e.querySelector("use");_&&(null==g||g.querySelector("svg").replaceWith(o({href:_.getAttribute("href")||_.getAttribute("xlink:href"),classList:["frm_svg32"]})))}p||(p=l.getAttribute("data-default")),void 0!==r&&(p=p.replace('<span class="frm_feature_label"></span>',r)),l.innerHTML=p,e.dataset.upsellImage&&l.append(frmDom.img({src:e.dataset.upsellImage,alt:e.dataset.upgrade})),c.href=function(e,t){var r=e.getAttribute("data-link");return null!=r&&""!==r||(r=t.getAttribute("data-default")),r}(e,c),d.style.display="none",s.style.display=h,u.style.display="block"===h?"inline-block":h,l.style.display=v,c.style.display="block"===m?"inline-block":m;var b=c.closest(".frm-upgrade-modal-actions");b&&(b.style.display="block"===m?"flex":m)}function a(e,t){var r=jQuery(e);if(!r.length)return!1;void 0===t&&(t="552px");var n={dialogClass:"frm-dialog",modal:!0,autoOpen:!1,closeOnEscape:!0,width:t,resizable:!1,draggable:!1,open:function(){var e,t;jQuery(".ui-dialog-titlebar").addClass("frm_hidden").removeClass("ui-helper-clearfix"),jQuery("#wpwrap").addClass("frm_overlay"),jQuery(".frm-dialog").removeClass("ui-widget ui-widget-content ui-corner-all"),r.removeClass("ui-dialog-content ui-widget-content"),e=r,t=function(){e.dialog("close")},jQuery(".ui-widget-overlay").on("click",t),e.on("click","a.dismiss",t)},close:function(){jQuery("#wpwrap").removeClass("frm_overlay"),jQuery(".spinner").css("visibility","hidden"),this.removeAttribute("data-option-type");var e=document.getElementById("bulk-option-type");e&&(e.value="")}};return r.dialog(n),r}function s(){var e=a("#frm_upgrade_modal");function t(t){var r,n,o;if((r=t.target).classList){var a=r.classList.contains("frm_show_expired_modal")||null!==r.querySelector(".frm_show_expired_modal")||r.closest(".frm_show_expired_modal");if("change"===t.type&&r.classList.contains("frm_select_with_upgrade")){var s=r.options[r.selectedIndex];s&&s.dataset.upgrade&&(r=s)}if(!r.dataset.upgrade){var l=r.closest("[data-upgrade]");if(!l){if(!(l=r.closest(".frm_field_box")))return;r.dataset.upgrade=""}r=l}if(a)wp.hooks.doAction("frm_show_expired_modal",r);else{var c=r.dataset.upgrade;if(c&&!r.classList.contains("frm_show_upgrade_tab")){t.preventDefault();var u=e.get(0),d=u.querySelector(".frm_lock_icon");d&&(d.style.display="block",d.classList.remove("frm_lock_open_icon"),d.querySelector("use").setAttribute("href","#frm_lock_icon"));var f="frm_upgrade_modal_image",p=document.getElementById(f);p&&p.remove(),r.dataset.image&&(d&&(d.style.display="none"),d.parentNode.insertBefore(frmDom.img({id:f,src:frmGlobal.url+"/images/"+r.dataset.image}),d));var m=u.querySelector(".license-level");m&&(m.textContent=function(e){return e.dataset.requires?e.dataset.requires:"Pro"}(r)),i(r,"modal",c),u.querySelector(".frm_are_not_installed").style.display=r.dataset.image||r.dataset.oneclick?"none":"inline-block",u.querySelector(".frm-upgrade-modal-title-prefix").style.display=r.dataset.oneclick?"inline":"none",u.querySelector(".frm_feature_label").textContent=c,u.querySelector(".frm-upgrade-modal-title-suffix").style.display="none",u.querySelector("h2").style.display="block",e.dialog("open");var v=u.querySelector(".button-primary:not(.frm-oneclick-button)");n=v.getAttribute("href").replace(/(medium=)[a-z_-]+/gi,"$1"+r.getAttribute("data-medium")),null===(o=r.getAttribute("data-content"))&&(o=""),n=n.replace(/(content=)[a-z_-]+/gi,"$1"+o),v.setAttribute("href",n)}}}}!1!==e&&(document.addEventListener("click",t),frmDom.util.documentOn("change","select.frm_select_with_upgrade",t))}},8616:e=>{e.exports=function(e,t){var r,n,o=0;function i(){var i,a,s=r,l=arguments.length;e:for(;s;){if(s.args.length===arguments.length){for(a=0;a<l;a++)if(s.args[a]!==arguments[a]){s=s.next;continue e}return s!==r&&(s===n&&(n=s.prev),s.prev.next=s.next,s.next&&(s.next.prev=s.prev),s.next=r,s.prev=null,r.prev=s,r=s),s.val}s=s.next}for(i=new Array(l),a=0;a<l;a++)i[a]=arguments[a];return s={args:i,val:e.apply(null,i)},r?(r.prev=s,s.next=r):n=s,o===t.maxSize?(n=n.prev).next=null:o++,r=s,s.val}return t=t||{},i.clear=function(){r=null,n=null,o=0},i}},7604:(e,t,r)=>{var n;!function(){"use strict";var o={not_string:/[^s]/,not_bool:/[^t]/,not_type:/[^T]/,not_primitive:/[^v]/,number:/[diefg]/,numeric_arg:/[bcdiefguxX]/,json:/[j]/,not_json:/[^j]/,text:/^[^\x25]+/,modulo:/^\x25{2}/,placeholder:/^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/,key:/^([a-z_][a-z_\d]*)/i,key_access:/^\.([a-z_][a-z_\d]*)/i,index_access:/^\[(\d+)\]/,sign:/^[+-]/};function i(e){return function(e,t){var r,n,a,s,l,c,u,d,f,p=1,m=e.length,v="";for(n=0;n<m;n++)if("string"==typeof e[n])v+=e[n];else if("object"==typeof e[n]){if((s=e[n]).keys)for(r=t[p],a=0;a<s.keys.length;a++){if(null==r)throw new Error(i('[sprintf] Cannot access property "%s" of undefined value "%s"',s.keys[a],s.keys[a-1]));r=r[s.keys[a]]}else r=s.param_no?t[s.param_no]:t[p++];if(o.not_type.test(s.type)&&o.not_primitive.test(s.type)&&r instanceof Function&&(r=r()),o.numeric_arg.test(s.type)&&"number"!=typeof r&&isNaN(r))throw new TypeError(i("[sprintf] expecting number but found %T",r));switch(o.number.test(s.type)&&(d=r>=0),s.type){case"b":r=parseInt(r,10).toString(2);break;case"c":r=String.fromCharCode(parseInt(r,10));break;case"d":case"i":r=parseInt(r,10);break;case"j":r=JSON.stringify(r,null,s.width?parseInt(s.width):0);break;case"e":r=s.precision?parseFloat(r).toExponential(s.precision):parseFloat(r).toExponential();break;case"f":r=s.precision?parseFloat(r).toFixed(s.precision):parseFloat(r);break;case"g":r=s.precision?String(Number(r.toPrecision(s.precision))):parseFloat(r);break;case"o":r=(parseInt(r,10)>>>0).toString(8);break;case"s":r=String(r),r=s.precision?r.substring(0,s.precision):r;break;case"t":r=String(!!r),r=s.precision?r.substring(0,s.precision):r;break;case"T":r=Object.prototype.toString.call(r).slice(8,-1).toLowerCase(),r=s.precision?r.substring(0,s.precision):r;break;case"u":r=parseInt(r,10)>>>0;break;case"v":r=r.valueOf(),r=s.precision?r.substring(0,s.precision):r;break;case"x":r=(parseInt(r,10)>>>0).toString(16);break;case"X":r=(parseInt(r,10)>>>0).toString(16).toUpperCase()}o.json.test(s.type)?v+=r:(!o.number.test(s.type)||d&&!s.sign?f="":(f=d?"+":"-",r=r.toString().replace(o.sign,"")),c=s.pad_char?"0"===s.pad_char?"0":s.pad_char.charAt(1):" ",u=s.width-(f+r).length,l=s.width&&u>0?c.repeat(u):"",v+=s.align?f+r+l:"0"===c?f+l+r:l+f+r)}return v}(function(e){if(s[e])return s[e];for(var t,r=e,n=[],i=0;r;){if(null!==(t=o.text.exec(r)))n.push(t[0]);else if(null!==(t=o.modulo.exec(r)))n.push("%");else{if(null===(t=o.placeholder.exec(r)))throw new SyntaxError("[sprintf] unexpected placeholder");if(t[2]){i|=1;var a=[],l=t[2],c=[];if(null===(c=o.key.exec(l)))throw new SyntaxError("[sprintf] failed to parse named argument key");for(a.push(c[1]);""!==(l=l.substring(c[0].length));)if(null!==(c=o.key_access.exec(l)))a.push(c[1]);else{if(null===(c=o.index_access.exec(l)))throw new SyntaxError("[sprintf] failed to parse named argument key");a.push(c[1])}t[2]=a}else i|=2;if(3===i)throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported");n.push({placeholder:t[0],param_no:t[1],keys:t[2],sign:t[3],pad_char:t[4],align:t[5],width:t[6],precision:t[7],type:t[8]})}r=r.substring(t[0].length)}return s[e]=n}(e),arguments)}function a(e,t){return i.apply(null,[e].concat(t||[]))}var s=Object.create(null);"undefined"!=typeof window&&(window.sprintf=i,window.vsprintf=a,void 0===(n=function(){return{sprintf:i,vsprintf:a}}.call(t,r,t,e))||(e.exports=n))}()},5397:(e,t,r)=>{"use strict";r.d(t,{A:()=>i});var n=r(1364),o={contextDelimiter:"",onMissingKey:null};function i(e,t){var r;for(r in this.data=e,this.pluralForms={},this.options={},o)this.options[r]=void 0!==t&&r in t?t[r]:o[r]}i.prototype.getPluralForm=function(e,t){var r,o,i,a=this.pluralForms[e];return a||("function"!=typeof(i=(r=this.data[e][""])["Plural-Forms"]||r["plural-forms"]||r.plural_forms)&&(o=function(e){var t,r,n;for(t=e.split(";"),r=0;r<t.length;r++)if(0===(n=t[r].trim()).indexOf("plural="))return n.substr(7)}(r["Plural-Forms"]||r["plural-forms"]||r.plural_forms),i=(0,n.A)(o)),a=this.pluralForms[e]=i),a(t)},i.prototype.dcnpgettext=function(e,t,r,n,o){var i,a,s;return i=void 0===o?0:this.getPluralForm(e,o),a=r,t&&(a=t+this.options.contextDelimiter+r),(s=this.data[e][a])&&s[i]?s[i]:(this.options.onMissingKey&&this.options.onMissingKey(r,e),0===i?r:n)}},3029:(e,t,r)=>{"use strict";function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}r.d(t,{A:()=>n})},4467:(e,t,r)=>{"use strict";r.d(t,{A:()=>o});var n=r(9922);function o(e,t,r){return(t=(0,n.A)(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}},2327:(e,t,r)=>{"use strict";r.d(t,{A:()=>o});var n=r(2284);function o(e,t){if("object"!=(0,n.A)(e)||!e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var o=r.call(e,t||"default");if("object"!=(0,n.A)(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}},9922:(e,t,r)=>{"use strict";r.d(t,{A:()=>i});var n=r(2284),o=r(2327);function i(e){var t=(0,o.A)(e,"string");return"symbol"==(0,n.A)(t)?t:t+""}},2284:(e,t,r)=>{"use strict";function n(e){return n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},n(e)}r.d(t,{A:()=>n})}},t={};function r(n){var o=t[n];if(void 0!==o)return o.exports;var i=t[n]={exports:{}};return e[n](i,i.exports,r),i.exports}function n(){var e,t,r="function"==typeof Symbol?Symbol:{},i=r.iterator||"@@iterator",a=r.toStringTag||"@@toStringTag";function s(r,n,i,a){var s=n&&n.prototype instanceof c?n:c,u=Object.create(s.prototype);return o(u,"_invoke",function(r,n,o){var i,a,s,c=0,u=o||[],d=!1,f={p:0,n:0,v:e,a:p,f:p.bind(e,4),d:function(t,r){return i=t,a=0,s=e,f.n=r,l}};function p(r,n){for(a=r,s=n,t=0;!d&&c&&!o&&t<u.length;t++){var o,i=u[t],p=f.p,m=i[2];r>3?(o=m===n)&&(s=i[(a=i[4])?5:(a=3,3)],i[4]=i[5]=e):i[0]<=p&&((o=r<2&&p<i[1])?(a=0,f.v=n,f.n=i[1]):p<m&&(o=r<3||i[0]>n||n>m)&&(i[4]=r,i[5]=n,f.n=m,a=0))}if(o||r>1)return l;throw d=!0,n}return function(o,u,m){if(c>1)throw TypeError("Generator is already running");for(d&&1===u&&p(u,m),a=u,s=m;(t=a<2?e:s)||!d;){i||(a?a<3?(a>1&&(f.n=-1),p(a,s)):f.n=s:f.v=s);try{if(c=2,i){if(a||(o="next"),t=i[o]){if(!(t=t.call(i,s)))throw TypeError("iterator result is not an object");if(!t.done)return t;s=t.value,a<2&&(a=0)}else 1===a&&(t=i.return)&&t.call(i),a<2&&(s=TypeError("The iterator does not provide a '"+o+"' method"),a=1);i=e}else if((t=(d=f.n<0)?s:r.call(n,f))!==l)break}catch(t){i=e,a=1,s=t}finally{c=1}}return{value:t,done:d}}}(r,i,a),!0),u}var l={};function c(){}function u(){}function d(){}t=Object.getPrototypeOf;var f=[][i]?t(t([][i]())):(o(t={},i,function(){return this}),t),p=d.prototype=c.prototype=Object.create(f);function m(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,d):(e.__proto__=d,o(e,a,"GeneratorFunction")),e.prototype=Object.create(p),e}return u.prototype=d,o(p,"constructor",d),o(d,"constructor",u),u.displayName="GeneratorFunction",o(d,a,"GeneratorFunction"),o(p),o(p,a,"Generator"),o(p,i,function(){return this}),o(p,"toString",function(){return"[object Generator]"}),(n=function(){return{w:s,m}})()}function o(e,t,r,n){var i=Object.defineProperty;try{i({},"",{})}catch(e){i=0}o=function(e,t,r,n){function a(t,r){o(e,t,function(e){return this._invoke(t,r,e)})}t?i?i(e,t,{value:r,enumerable:!n,configurable:!n,writable:!n}):e[t]=r:(a("next",0),a("throw",1),a("return",2))},o(e,t,r,n)}function i(e,t,r,n,o,i,a){try{var s=e[i](a),l=s.value}catch(e){return void r(e)}s.done?t(l):Promise.resolve(l).then(n,o)}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},function(){var e=r(1105).toggleAddonState;function t(){jQuery(document).on("click","#frm_upgrade_modal .frm-install-addon",function(t){t.preventDefault(),e(this,"frm_install_addon")}),jQuery(document).on("click","#frm_upgrade_modal .frm-activate-addon",function(t){t.preventDefault(),e(this,"frm_activate_addon")}),function(){o.apply(this,arguments)}(),a(),jQuery(document).on("mouseenter.frm",".frm_help",function(){jQuery(this).off("mouseenter.frm"),jQuery(this).tooltip("show")})}function o(){var e;return e=n().m(function e(){return n().w(function(e){for(;;)switch(e.n){case 0:return e.n=1,Promise.resolve().then(r.bind(r,4260));case 1:e.v.initUpgradeModal();case 2:return e.a(2)}},e)}),o=function(){var t=this,r=arguments;return new Promise(function(n,o){var a=e.apply(t,r);function s(e){i(a,n,o,s,l,"next",e)}function l(e){i(a,n,o,s,l,"throw",e)}s(void 0)})},o.apply(this,arguments)}function a(){var e=window.frmDom;e.bootstrap.setupBootstrapDropdowns(function(){var e=document.querySelector("#frm_testmode_enabled_form_actions .dropdown-toggle");e&&(e.classList.add("frm-dropdown-toggle"),e.hasAttribute("role")||e.setAttribute("role","button"),e.hasAttribute("tabindex")||e.setAttribute("tabindex",0))});var t=document.getElementById("frm_testmode_enabled_form_actions");t&&(t.style.display="none",e.bootstrap.multiselect.init.bind(t)(),t.disabled&&t.parentElement.querySelector(".dropdown-toggle").classList.add("frm_noallow"))}"complete"===document.readyState?t():document.addEventListener("DOMContentLoaded",t),document.addEventListener("frm_after_start_over",function(){a()}),jQuery(document).on("frmPageChanged frmFormComplete",function(){a()})}()})();
>>>>>>> master
