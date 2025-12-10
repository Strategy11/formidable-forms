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
/*!*******************************!*\
  !*** ./js/src/admin/admin.js ***!
  \*******************************/
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/* exported frm_add_logic_row, frm_remove_tag, frm_show_div, frmCheckAll, frmCheckAllLevel */
/* eslint-disable jsdoc/require-param, prefer-const, no-redeclare, @wordpress/no-unused-vars-before-return, jsdoc/check-types, jsdoc/check-tag-names, @wordpress/i18n-translator-comments, @wordpress/valid-sprintf, jsdoc/require-returns-description, jsdoc/require-param-type, no-unused-expressions, compat/compat */

window.FrmFormsConnect = window.FrmFormsConnect || function (document, window, $) {
  /*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl */

  var el = {
    messageBox: null,
    reset: null,
    setElements: function setElements() {
      el.messageBox = document.querySelector('.frm_pro_license_msg');
      el.reset = document.getElementById('frm_reconnect_link');
    }
  };

  /**
   * Public functions and properties.
   *
   * @since 4.03
   *
   * @type {Object}
   */
  var app = {
    /**
     * Register connect button event.
     *
     * @since 4.03
     */
    init: function init() {
      el.setElements();
      $(document.getElementById('frm_deauthorize_link')).on('click', app.deauthorize);
      $('.frm_authorize_link').on('click', app.authorize);
      // Handles FF dashboard Authorize & Reauthorize events.
      // Attach click event to parent as #frm_deauthorize_link & #frm_reconnect_link dynamically recreated by bootstrap.setupBootstrapDropdowns in dom.js
      $('.frm-dashboard-license-options').on('click', '#frm_deauthorize_link', app.deauthorize);
      $('.frm-dashboard-license-options').on('click', '#frm_reconnect_link', app.reauthorize);
      if (el.reset !== null) {
        $(el.reset).on('click', app.reauthorize);
      }
    },
    /* Manual license authorization */
    authorize: function authorize() {
      /*jshint validthis:true */
      var button = this;
      var pluginSlug = this.getAttribute('data-plugin');
      var input = document.getElementById('edd_' + pluginSlug + '_license_key');
      var license = input.value;
      var wpmu = document.getElementById('proplug-wpmu');
      this.classList.add('frm_loading_button');
      if (wpmu === null) {
        wpmu = 0;
      } else if (wpmu.checked) {
        wpmu = 1;
      } else {
        wpmu = 0;
      }
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'frm_addon_activate',
          license: license,
          plugin: pluginSlug,
          wpmu: wpmu,
          nonce: frmGlobal.nonce
        },
        success: function success(msg) {
          app.afterAuthorize(msg, input);
          button.classList.remove('frm_loading_button');
        }
      });
    },
    afterAuthorize: function afterAuthorize(msg, input) {
      if (msg.success === true) {
        input.value = '•••••••••••••••••••';
      }
      wp.hooks.doAction('frm_after_authorize', msg);
      app.showMessage(msg);
    },
    showProgress: function showProgress(msg) {
      if (el.messageBox === null) {
        // In case the message box was added after page load.
        el.setElements();
      }
      var messageBox = el.messageBox;
      if (messageBox === null) {
        return;
      }
      if (msg.success === true) {
        messageBox.classList.remove('frm_error_style');
        messageBox.classList.add('frm_message', 'frm_updated_message');
      } else {
        messageBox.classList.add('frm_error_style');
        messageBox.classList.remove('frm_message', 'frm_updated_message');
      }
      messageBox.classList.remove('frm_hidden');
      messageBox.innerHTML = msg.message;
    },
    showMessage: function showMessage(msg) {
      if (el.messageBox === null) {
        // In case the message box was added after page load.
        el.setElements();
      }
      var messageBox = el.messageBox;
      if (msg.success === true) {
        app.showAuthorized(true);
        app.showInlineSuccess();

        /**
         * Triggers the after license is authorized action for a confirmation/success modal.
         *
         * @param {Object} msg An object containing message data received from Authorize request.
         */
        wp.hooks.doAction('frmAdmin.afterLicenseAuthorizeSuccess', {
          msg: msg
        });
      }
      app.showProgress(msg);
      if (msg.message !== '') {
        setTimeout(function () {
          messageBox.innerHTML = '';
          messageBox.classList.add('frm_hidden');
          messageBox.classList.remove('frm_error_style', 'frm_message', 'frm_updated_message');
        }, 10000);
        var refreshPage = document.querySelector('.frm-admin-page-dashboard');
        if (refreshPage) {
          setTimeout(function () {
            window.location.reload();
          }, 1000);
        }
      }
    },
    showAuthorized: function showAuthorized(show) {
      var from = show ? 'unauthorized' : 'authorized';
      var to = show ? 'authorized' : 'unauthorized';
      var container = document.querySelectorAll('.frm_' + from + '_box');
      if (container.length) {
        // Replace all authorized boxes with unauthorized boxes.
        container.forEach(function (box) {
          box.className = box.className.replace('frm_' + from + '_box', 'frm_' + to + '_box');
        });
      }
    },
    /**
     * Use the data-success element to replace the element content.
     */
    showInlineSuccess: function showInlineSuccess() {
      var successElement = document.querySelectorAll('.frm-confirm-msg [data-success]');
      if (successElement.length) {
        successElement.forEach(function (element) {
          element.innerHTML = frmAdminBuild.purifyHtml(element.getAttribute('data-success'));
        });
      }
    },
    /* Clear the site license cache */
    reauthorize: function reauthorize() {
      /*jshint validthis:true */
      this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;float:none"></span>';
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'frm_reset_cache',
          plugin: 'formidable_pro',
          nonce: frmGlobal.nonce
        },
        success: function success(msg) {
          el.reset.textContent = msg.message;
          if (el.reset.getAttribute('data-refresh') === '1') {
            window.location.reload();
          }
        }
      });
      return false;
    },
    deauthorize: function deauthorize() {
      /*jshint validthis:true */
      if (!confirm(frmGlobal.deauthorize)) {
        return false;
      }
      var pluginSlug = this.getAttribute('data-plugin'),
        input = document.getElementById('edd_' + pluginSlug + '_license_key'),
        license = input.value,
        link = this;
      this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;"></span>';
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'frm_addon_deactivate',
          license: license,
          plugin: pluginSlug,
          nonce: frmGlobal.nonce
        },
        success: function success() {
          app.showAuthorized(false);
          input.value = '';
          link.replaceWith('Disconnected');

          /**
           * Triggers the after license is deauthorized sruccess action.
           */
          wp.hooks.doAction('frmAdmin.afterLicenseDeauthorizeSuccess', {});
        }
      });
      return false;
    }
  };

  // Provide access to public functions/properties.
  return app;
}(document, window, jQuery);
window.frmAdminBuildJS = function () {
  //'use strict';

  /*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl, fromDom */

  var MAX_FIELD_GROUP_SIZE = 12;
  var frmAdminJs = frm_admin_js; // eslint-disable-line camelcase
  var _frmDom = frmDom,
    tag = _frmDom.tag,
    div = _frmDom.div,
    span = _frmDom.span,
    a = _frmDom.a,
    svg = _frmDom.svg,
    img = _frmDom.img;
  var onClickPreventDefault = frmDom.util.onClickPreventDefault;
  var _frmDom$ajax = frmDom.ajax,
    doJsonFetch = _frmDom$ajax.doJsonFetch,
    doJsonPost = _frmDom$ajax.doJsonPost;
  frmAdminJs.contextualShortcodes = getContextualShortcodes();
  var icons = {
    save: svg({
      href: '#frm_save_icon'
    }),
    drag: svg({
      href: '#frm_drag_icon',
      classList: ['frm_drag_icon', 'frm-drag']
    })
  };
  var $newFields = jQuery(document.getElementById('frm-show-fields')),
    builderForm = document.getElementById('new_fields'),
    thisForm = document.getElementById('form_id'),
    copyHelper = false,
    fieldsUpdated = 0,
    thisFormId = 0,
    autoId = 0,
    optionMap = {},
    lastNewActionIdReturned = 0;
  var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    sprintf = _wp$i18n.sprintf;
  var debouncedSyncAfterDragAndDrop, postBodyContent, $postBodyContent;
  var dragState = {
    dragging: false
  };
  if (thisForm !== null) {
    thisFormId = thisForm.value;
  }
  var currentURL = new URL(window.location.href);
  var urlParams = currentURL.searchParams;
  var builderPage = document.getElementById('frm_builder_page');

  // Global settings
  var s;
  function showElement(element) {
    if (!element[0]) {
      return;
    }
    element[0].style.display = '';
  }
  function empty($obj) {
    if ($obj !== null) {
      while ($obj.firstChild) {
        $obj.removeChild($obj.firstChild);
      }
    }
  }
  function addClass($obj, className) {
    if ($obj.classList) {
      $obj.classList.add(className);
    } else {
      $obj.className += ' ' + className;
    }
  }
  function confirmClick(e) {
    /*jshint validthis:true */
    e.stopPropagation();
    e.preventDefault();
    confirmLinkClick(this);
  }
  function confirmLinkClick(link) {
    var message = link.getAttribute('data-frmverify'),
      loadedFrom = link.getAttribute('data-loaded-from');
    if (message === null || link.id === 'frm-confirmed-click') {
      return true;
    }
    if ('entries-list' === loadedFrom) {
      return wp.hooks.applyFilters('frm_on_multiple_entries_delete', {
        link: link,
        initModal: initModal
      });
    }
    return confirmModal(link);
  }
  function confirmModal(link) {
    var verify,
      $confirmMessage,
      i,
      dataAtts,
      btnClass,
      $info = initModal('#frm_confirm_modal', '400px'),
      continueButton = document.getElementById('frm-confirmed-click');
    if ($info === false) {
      return false;
    }
    verify = link.getAttribute('data-frmverify');
    btnClass = verify ? link.getAttribute('data-frmverify-btn') : '';
    $confirmMessage = jQuery('.frm-confirm-msg');
    $confirmMessage.empty();
    if (verify) {
      $confirmMessage.append(document.createTextNode(verify));
      if (btnClass) {
        continueButton.classList.add(btnClass);
      }
    }
    removeAtts = continueButton.dataset;
    for (i in dataAtts) {
      continueButton.removeAttribute('data-' + i);
    }
    dataAtts = link.dataset;
    for (i in dataAtts) {
      if (i !== 'frmverify') {
        continueButton.setAttribute('data-' + i, dataAtts[i]);
      }
    }

    /**
     * Triggers the pre-open action for a confirmation modal. This action passes
     * relevant modal information and associated link to any listening hooks.
     *
     * @param {Object}      options       An object containing modal elements and data.
     * @param {HTMLElement} options.$info The HTML element containing modal information.
     * @param {string}      options.link  The link associated with the modal action.
     */
    wp.hooks.doAction('frmAdmin.beforeOpenConfirmModal', {
      $info: $info,
      link: link
    });
    $info.dialog('open');
    continueButton.setAttribute('href', link.getAttribute('href') || link.getAttribute('data-href'));
    return false;
  }
  function infoModal(msg) {
    var $info = initModal('#frm_info_modal', '400px');
    if ($info === false) {
      return false;
    }
    jQuery('.frm-info-msg').html(msg);
    $info.dialog('open');
    return false;
  }
  function toggleItem(e) {
    /*jshint validthis:true */
    var toggle = this.getAttribute('data-frmtoggle');
    var text = this.getAttribute('data-toggletext');
    var $items = jQuery(toggle);
    e.preventDefault();
    $items.toggle();
    if (text !== null && text !== '') {
      this.setAttribute('data-toggletext', this.innerHTML);
      this.textContent = text;
    }
    return false;
  }

  /**
   * Toggle a class on target elements when an anchor is clicked, or when a radio or checkbox has been selected.
   *
   * @param {Event} e Event with either the change or click type.
   * @return {false}
   */
  function hideShowItem(e) {
    /*jshint validthis:true */
    var hide = this.getAttribute('data-frmhide');
    var show = this.getAttribute('data-frmshow');
    var uncheckList = this.getAttribute('data-frmuncheck');
    var uncheckListArray = uncheckList ? uncheckList.split(',') : [];

    // Flip unchecked checkboxes so an off value undoes the on value.
    if (isUncheckedCheckbox(this)) {
      if (hide !== null) {
        show = hide;
        hide = null;
      } else if (show !== null) {
        hide = show;
        show = null;
      }
    }
    e.preventDefault();
    var toggleClass = this.getAttribute('data-toggleclass') || 'frm_hidden';
    if (hide !== null) {
      jQuery(hide).addClass(toggleClass);
    }
    if (show !== null) {
      jQuery(show).removeClass(toggleClass);
    }
    var current = this.parentNode.querySelectorAll('a.current');
    if (current !== null) {
      for (var _i = 0; _i < current.length; _i++) {
        current[_i].classList.remove('current');
      }
      this.classList.add('current');
    }
    if (uncheckListArray.length) {
      uncheckListArray.forEach(function (uncheckItem) {
        var uncheckItemElement = document.querySelector(uncheckItem);
        if (uncheckItemElement) {
          uncheckItemElement.checked = false;
        }
      });
    }
    return false;
  }
  function isUncheckedCheckbox(element) {
    return 'INPUT' === element.nodeName && 'checkbox' === element.type && !element.checked;
  }

  /**
   * Load a tooltip for a single element.
   *
   * @since 6.26
   *
   * @param {HTMLElement} element
   * @param {boolean}     show
   */
  function loadTooltip(element) {
    var show = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var tooltipTarget = element;

    // Bootstrap 5 does not allow tooltips on dropdown triggers, so move the tooltip to the parent element.
    if (tooltipTarget.hasAttribute('data-toggle') || tooltipTarget.hasAttribute('data-bs-toggle')) {
      tooltipTarget.parentElement.setAttribute('title', tooltipTarget.getAttribute('title'));
      tooltipTarget.removeAttribute('title');
      tooltipTarget.classList.remove('frm_bstooltip');
      tooltipTarget.parentElement.classList.add('frm_bstooltip');
      tooltipTarget = tooltipTarget.parentElement;
    }
    jQuery(tooltipTarget).tooltip();
    if (show) {
      deleteTooltips();
      jQuery(tooltipTarget).tooltip('show');
    }
  }
  function loadTooltips() {
    var wrapClass = jQuery('.wrap, .frm_wrap'),
      confirmModal = document.getElementById('frm_confirm_modal'),
      doAction = false,
      confirmedBulkDelete = false;
    jQuery(confirmModal).on('click', '[data-deletefield]', deleteFieldConfirmed);
    jQuery(confirmModal).on('click', '[data-removeid]', removeThisTag);
    jQuery(confirmModal).on('click', '[data-trashtemplate]', trashTemplate);
    wrapClass.on('click', '.frm_remove_tag, .frm_remove_form_action', removeThisTag);
    wrapClass.on('click', 'a[data-frmverify]', confirmClick);
    wrapClass.on('click', 'a[data-frmtoggle]', toggleItem);
    wrapClass.on('click', 'a[data-frmhide], a[data-frmshow]', hideShowItem);
    wrapClass.on('change', 'input[data-frmhide], input[data-frmshow]', hideShowItem);
    wrapClass.on('click', '.widget-top,a.widget-action', clickWidget);
    wrapClass.on('mouseenter.frm', '.frm_bstooltip, .frm_help', function () {
      jQuery(this).off('mouseenter.frm');
      loadTooltip(this, true);
    });
    jQuery(document).on('click', '#doaction, #doaction2', function (event) {
      var isTop = this.id === 'doaction',
        suffix = isTop ? 'top' : 'bottom',
        bulkActionSelector = document.getElementById('bulk-action-selector-' + suffix),
        confirmBulkDelete = document.getElementById('confirm-bulk-delete-' + suffix);
      if (bulkActionSelector !== null && confirmBulkDelete !== null) {
        doAction = this;
        if (!confirmedBulkDelete && bulkActionSelector.value === 'bulk_delete') {
          event.preventDefault();
          confirmLinkClick(confirmBulkDelete);
          return false;
        }
      } else {
        doAction = false;
      }
    });
    jQuery(document).on('click', '#frm-confirmed-click', function (event) {
      if (doAction === false || event.target.classList.contains('frm-btn-inactive')) {
        return;
      }
      if (this.getAttribute('href') === 'confirm-bulk-delete') {
        event.preventDefault();
        confirmedBulkDelete = true;
        doAction.click();
        return false;
      }
    });
  }
  function deleteTooltips() {
    document.querySelectorAll('.tooltip').forEach(function (tooltip) {
      tooltip.remove();
    });
  }
  function removeThisTag() {
    /*jshint validthis:true */
    var show, hide, removeMore;
    if (parseInt(this.getAttribute('data-skip-frm-js')) || confirmLinkClick(this) === false) {
      return;
    }
    var deleteButton = jQuery(this);
    var id = deleteButton.attr('data-removeid');
    show = deleteButton.attr('data-showlast');
    if (typeof show === 'undefined') {
      show = '';
    }
    hide = deleteButton.attr('data-hidelast');
    if (typeof hide === 'undefined') {
      hide = '';
    }
    removeMore = deleteButton.attr('data-removemore');
    if (show !== '') {
      if (deleteButton.closest('.frm_add_remove').find('.frm_remove_tag:visible').length > 1) {
        show = '';
        hide = '';
      }
    } else if (id.indexOf('frm_postmeta_') === 0) {
      if (jQuery('#frm_postmeta_rows .frm_postmeta_row').length < 2) {
        show = '.frm_add_postmeta_row.button';
      }
      if (jQuery('.frm_toggle_cf_opts').length && jQuery('#frm_postmeta_rows .frm_postmeta_row:not(#' + id + ')').last().length) {
        if (show !== '') {
          show += ',';
        }
        show += '#' + jQuery('#frm_postmeta_rows .frm_postmeta_row:not(#' + id + ')').last().attr('id') + ' .frm_toggle_cf_opts';
      }
    }
    var fadeEle = document.getElementById(id);
    var $fadeEle = jQuery(fadeEle);
    $fadeEle.fadeOut(300, function () {
      var _document$querySelect;
      $fadeEle.remove();
      fieldUpdated();
      if (hide !== '') {
        jQuery(hide).hide();
      }
      if (show !== '') {
        jQuery(show + ' a,' + show).removeClass('frm_hidden').fadeIn('slow');
      }
      if (this.closest('.frm_form_action_settings')) {
        var type = this.closest('.frm_form_action_settings').querySelector('.frm_action_name').value;
        afterActionRemoved(type);
      }
      (_document$querySelect = document.querySelector('.tooltip')) === null || _document$querySelect === void 0 || _document$querySelect.remove();
    });
    if (typeof removeMore !== 'undefined') {
      removeMore = jQuery(removeMore);
      removeMore.fadeOut(400, function () {
        removeMore.remove();
      });
    }
    if (show !== '') {
      jQuery(this).closest('.frm_logic_rows').fadeOut('slow');
    }

    /**
     * Fires after a tag element has been removed in the admin interface.
     *
     * @param {string}      id      The ID of the removed element
     * @param {HTMLElement} fadeEle The removed element that was faded out
     */
    wp.hooks.doAction('frm_admin_tag_removed', id, fadeEle);
    return false;
  }
  function afterActionRemoved(type) {
    checkActiveAction(type);
    var hookName = 'frm_after_action_removed';
    var hookArgs = {
      type: type
    };
    wp.hooks.doAction(hookName, hookArgs);
  }
  function clickWidget(event, b) {
    /*jshint validthis:true */
    if (typeof b === 'undefined') {
      b = this;
    }
    popCalcFields(b, false);
    var cont = jQuery(b).closest('.frm_form_action_settings');
    var target = event.target;
    if (cont.length && typeof target !== 'undefined') {
      var className = target.parentElement.className;
      if ('string' === typeof className) {
        if (className.indexOf('frm_email_icons') > -1 || className.indexOf('frm_toggle') > -1) {
          // clicking on delete icon shouldn't open it
          event.stopPropagation();
          return;
        }
      }
    }
    var inside = cont.children('.widget-inside');
    if (cont.length && inside.find('p, div, table').length < 1) {
      var actionId = cont.find('input[name$="[ID]"]').val();
      var actionType = cont.find('input[name$="[post_excerpt]"]').val();
      if (actionType) {
        inside.html('<span class="frm-wait frm_spinner"></span>');
        cont.find('.spinner').fadeIn('slow');
        jQuery.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'frm_form_action_fill',
            action_id: actionId,
            action_type: actionType,
            nonce: frmGlobal.nonce
          },
          success: function success(html) {
            inside.html(html);
            initiateMultiselect();
            showInputIcon('#' + cont.attr('id'));
            initAutocomplete(inside);
            jQuery(b).trigger('frm-action-loaded');

            /**
             * Fires after filling form action content when opening.
             *
             * @since 5.5.4
             *
             * @param {Object} insideElement JQuery object of form action inside element.
             */
            wp.hooks.doAction('frm_filled_form_action', inside);
          }
        });
      }
    }
    jQuery(b).closest('.frm_field_box').siblings().find('.widget-inside').slideUp('fast');
    if (typeof b.className !== 'undefined' && b.className.indexOf('widget-action') !== -1 || jQuery(b).closest('.start_divider').length < 1) {
      return;
    }
    inside = jQuery(b).closest('div.widget').children('.widget-inside');
    if (inside.is(':hidden')) {
      inside.slideDown('fast');
    } else {
      inside.slideUp('fast');
    }
  }
  function clickNewTab() {
    /*jshint validthis:true */
    var t = this.getAttribute('href');
    if (typeof t === 'undefined') {
      return false;
    }
    var c = t.replace('#', '.');
    var $link = jQuery(this);
    $link.closest('li').addClass('frm-tabs active').siblings('li').removeClass('frm-tabs active starttab');
    $link.closest('div').children('.tabs-panel').not(t).not(c).hide();
    var tabContent = document.getElementById(t.replace('#', ''));
    if (tabContent) {
      tabContent.style.display = 'block';
    }

    // clearSettingsBox would hide field settings when opening the fields modal and we want to skip it there.
    if (this.id === 'frm_insert_fields_tab' && !this.closest('#frm_adv_info')) {
      clearSettingsBox();
    }
    return false;
  }
  function clickTab(link, auto) {
    link = jQuery(link);
    var t = link.attr('href');
    if (typeof t === 'undefined') {
      return;
    }
    var c = t.replace('#', '.');
    link.closest('li').addClass('frm-tabs active').siblings('li').removeClass('frm-tabs active starttab');
    if (link.closest('div').find('.tabs-panel').length) {
      link.closest('div').children('.tabs-panel').not(t).not(c).hide();
    } else if (document.getElementById('form_global_settings') !== null) {
      /* global settings */
      var ajax = link.data('frmajax');
      link.closest('.frm_wrap').find('.tabs-panel, .hide_with_tabs').hide();
      if (typeof ajax !== 'undefined' && ajax == '1') {
        loadSettingsTab(t);
      }
    } else {
      /* form settings page */
      jQuery('#frm-categorydiv .tabs-panel, .hide_with_tabs').hide();
    }
    jQuery(t).show();
    jQuery(c).show();
    hideShortcodes();
    if (auto !== 'auto') {
      // Hide success message on tab change.
      jQuery('.frm_updated_message').hide();
      jQuery('.frm_warning_style').hide();
    }
    if (jQuery(link).closest('#frm_adv_info').length) {
      return;
    }
    if (jQuery('.frm_form_settings').length) {
      jQuery('.frm_form_settings').attr('action', '?page=formidable&frm_action=settings&id=' + jQuery('.frm_form_settings input[name="id"]').val() + '&t=' + t.replace('#', ''));
    } else {
      jQuery('.frm_settings_form').attr('action', '?page=formidable-settings&t=' + t.replace('#', ''));
    }
  }
  function setupSortable(sortableSelector) {
    document.querySelectorAll(sortableSelector).forEach(function (list) {
      makeDroppable(list);
      Array.from(list.children).forEach(function (child) {
        return makeDraggable(child, '.frm-move');
      });
      var $sectionTitle = jQuery(list).children('[data-type="divider"]').children('.divider_section_only');
      if ($sectionTitle.length) {
        makeDroppable($sectionTitle);
      }
    });
    setupFieldOptionSorting(jQuery('#frm_builder_page'));
  }
  function makeDroppable(list) {
    jQuery(list).droppable({
      accept: '.frmbutton, li.frm_field_box',
      deactivate: handleFieldDrop,
      over: onDragOverDroppable,
      out: onDraggableLeavesDroppable,
      tolerance: 'pointer'
    });
  }
  function onDragOverDroppable(event, ui) {
    var droppable = getDroppableForOnDragOver(event.target);
    var draggable = ui.draggable[0];
    if (!allowDrop(draggable, droppable, event)) {
      droppable.classList.remove('frm-over-droppable');
      jQuery(droppable).parents('ul.frm_sorting').addClass('frm-over-droppable');
      return;
    }
    document.querySelectorAll('.frm-over-droppable').forEach(function (droppable) {
      return droppable.classList.remove('frm-over-droppable');
    });
    droppable.classList.add('frm-over-droppable');
    jQuery(droppable).parents('ul.frm_sorting').addClass('frm-over-droppable');
  }

  /**
   * Maybe change the droppable.
   * Section titles are made droppable, but are not a list, so we need to change the droppable to the section's list instead.
   *
   * @param {Element} droppable
   * @return {Element}
   */
  function getDroppableForOnDragOver(droppable) {
    if (droppable.classList.contains('divider_section_only')) {
      droppable = jQuery(droppable).nextAll('.start_divider.frm_sorting').get(0);
    }
    return droppable;
  }
  function onDraggableLeavesDroppable(event) {
    var droppable = event.target;
    droppable.classList.remove('frm-over-droppable');
  }
  function makeDraggable(draggable, handle) {
    var settings = {
      helper: getDraggableHelper,
      revert: 'invalid',
      delay: 10,
      start: handleDragStart,
      stop: handleDragStop,
      drag: handleDrag,
      cursor: 'grabbing',
      refreshPositions: true,
      cursorAt: {
        top: 0,
        left: 90 // The width of draggable button is 180. 90 should center the draggable on the cursor.
      }
    };
    if ('string' === typeof handle) {
      settings.handle = handle;
    }
    jQuery(draggable).draggable(settings);
  }
  function getDraggableHelper(event) {
    var draggable = event.delegateTarget;
    if (isFieldGroup(draggable)) {
      var newTextFieldClone = document.getElementById('frm-insert-fields').querySelector('.frm_ttext').cloneNode(true);
      newTextFieldClone.querySelector('use').setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#frm_field_group_layout_icon');
      newTextFieldClone.querySelector('span').textContent = __('Field Group', 'formidable');
      newTextFieldClone.classList.add('frm_field_box');
      newTextFieldClone.classList.add('ui-sortable-helper');
      return newTextFieldClone;
    }
    var copyTarget;
    var isNewField = draggable.classList.contains('frmbutton');
    if (isNewField) {
      copyTarget = draggable.cloneNode(true);
      copyTarget.classList.add('ui-sortable-helper');
      draggable.classList.add('frm-new-field');
      return copyTarget;
    }
    if (draggable.hasAttribute('data-ftype')) {
      var fieldType = draggable.getAttribute('data-ftype');
      copyTarget = document.getElementById('frm-insert-fields').querySelector('.frm_t' + fieldType);
      copyTarget = copyTarget.cloneNode(true);
      copyTarget.classList.add('form-field');
      copyTarget.classList.add('ui-sortable-helper');
      if (copyTarget) {
        return copyTarget.cloneNode(true);
      }
    }
    return div({
      className: 'frmbutton'
    });
  }
  function handleDragStart(event, ui) {
    dragState.dragging = true;
    var container = postBodyContent;
    container.classList.add('frm-dragging-field');
    document.body.classList.add('frm-dragging');
    ui.helper.addClass('frm-sortable-helper');
    ui.helper.initialOffset = container.scrollTop;
    event.target.classList.add('frm-drag-fade');
    unselectFieldGroups();
    deleteEmptyDividerWrappers();
    maybeRemoveGroupHoverTarget();
    closeOpenFieldDropdowns();
    deleteTooltips();
  }
  function handleDragStop() {
    var container = postBodyContent;
    container.classList.remove('frm-dragging-field');
    document.body.classList.remove('frm-dragging');
    var fade = document.querySelector('.frm-drag-fade');
    if (fade) {
      fade.classList.remove('frm-drag-fade');
    }
  }
  function handleDrag(event, ui) {
    maybeScrollBuilder(event);
    var draggable = event.target;
    var droppable = getDroppableTarget();
    var placeholder = document.getElementById('frm_drag_placeholder');
    if (!allowDrop(draggable, droppable, event)) {
      if (placeholder) {
        placeholder.remove();
      }
      return;
    }
    if (!placeholder) {
      placeholder = tag('li', {
        id: 'frm_drag_placeholder',
        className: 'sortable-placeholder'
      });
    }
    var frmSortableHelper = ui.helper.get(0);
    if (frmSortableHelper.classList.contains('form-field') || frmSortableHelper.classList.contains('frm_field_box')) {
      // Sync the y position of the draggable so it still follows the cursor after scrolling up and down the field list.
      frmSortableHelper.style.transform = 'translateY(' + getDragOffset(ui.helper) + 'px)';
    }
    if ('frm-show-fields' === droppable.id || droppable.classList.contains('start_divider')) {
      placeholder.style.left = 0;
      handleDragOverYAxis({
        droppable: droppable,
        y: event.clientY,
        placeholder: placeholder
      });
      return;
    }
    placeholder.style.top = '';
    handleDragOverFieldGroup({
      droppable: droppable,
      x: event.clientX,
      placeholder: placeholder
    });
  }
  function maybeScrollBuilder(event) {
    $postBodyContent.scrollTop(function (_, v) {
      var moved = event.clientY;
      var h = postBodyContent.offsetHeight;
      var relativePos = event.clientY - postBodyContent.offsetTop;
      var y = relativePos - h / 2;
      if (relativePos > h - 50 && moved > 5) {
        // Scrolling down.
        return v + y * 0.1;
      }
      if (relativePos < 70 && moved < 130) {
        // Scrolling up.
        return v - Math.abs(y * 0.1);
      }
      return v;
    });
  }
  function getDragOffset($helper) {
    return postBodyContent.scrollTop - $helper.initialOffset;
  }
  function getDroppableTarget() {
    var droppable = document.getElementById('frm-show-fields');
    while (droppable.querySelector('.frm-over-droppable')) {
      droppable = droppable.querySelector('.frm-over-droppable');
    }
    if ('frm-show-fields' === droppable.id && !droppable.classList.contains('frm-over-droppable')) {
      droppable = false;
    }
    return droppable;
  }
  function handleFieldDrop(_, ui) {
    if (!dragState.dragging) {
      // dragState.dragging is set to true on drag start.
      // The deactivate event gets called for every droppable. This check to make sure it happens once.
      return;
    }
    dragState.dragging = false;
    var draggable = ui.draggable[0];
    var placeholder = document.getElementById('frm_drag_placeholder');
    if (!placeholder) {
      ui.helper.remove();
      debouncedSyncAfterDragAndDrop();
      return;
    }
    maybeOpenCollapsedPage(placeholder);
    var $previousFieldContainer = ui.helper.parent();
    var previousSection = ui.helper.get(0).closest('ul.start_divider');
    var newSection = placeholder.closest('ul.start_divider');
    if (draggable.classList.contains('frm-new-field')) {
      insertNewFieldByDragging(draggable.id);
    } else {
      moveFieldThatAlreadyExists(draggable, placeholder);
      maybeMakeFieldGroupDraggableAfterDragging(placeholder.parentElement);
    }
    var previousSectionId = previousSection ? parseInt(previousSection.closest('.edit_field_type_divider').getAttribute('data-fid')) : 0;
    var newSectionId = newSection ? parseInt(newSection.closest('.edit_field_type_divider').getAttribute('data-fid')) : 0;
    placeholder.remove();
    ui.helper.remove();
    var $previousContainerFields = $previousFieldContainer.length ? getFieldsInRow($previousFieldContainer) : [];
    maybeUpdatePreviousFieldContainerAfterDrop($previousFieldContainer, $previousContainerFields);
    maybeUpdateDraggableClassAfterDrop(draggable, $previousContainerFields);
    if (previousSectionId !== newSectionId) {
      updateFieldAfterMovingBetweenSections(jQuery(draggable), previousSection);
    }
    debouncedSyncAfterDragAndDrop();
  }

  /**
   * When a field is moved into a field group, make sure the field group is draggable.
   *
   * @since 6.24
   *
   * @param {HTMLElement} placeholderParent
   * @return {void}
   */
  function maybeMakeFieldGroupDraggableAfterDragging(placeholderParent) {
    var isDroppingIntoFieldGroup = placeholderParent.nodeName === 'UL' && !placeholderParent.classList.contains('start_divider') && 'frm-show-fields' !== placeholderParent.id;
    if (!isDroppingIntoFieldGroup) {
      return;
    }
    var fieldGroupLi = placeholderParent.closest('li');
    if (fieldGroupLi && !fieldGroupLi.classList.contains('ui-draggable')) {
      makeDraggable(fieldGroupLi, '.frm-move');
    }
  }

  /**
   * If a page if collapsed, expand it before dragging since only the page break will move.
   *
   * @param {Element} placeholder
   * @return {void}
   */
  function maybeOpenCollapsedPage(placeholder) {
    if (!placeholder.previousElementSibling || !placeholder.previousElementSibling.classList.contains('frm-is-collapsed')) {
      return;
    }
    var $pageBreakField = jQuery(placeholder).prevUntil('[data-type="break"]');
    if (!$pageBreakField.length) {
      return;
    }
    var collapseButton = $pageBreakField.find('.frm-collapse-page').get(0);
    if (collapseButton) {
      collapseButton.click();
    }
  }
  function maybeUpdatePreviousFieldContainerAfterDrop($previousFieldContainer, $previousContainerFields) {
    if (!$previousFieldContainer.length) {
      return;
    }
    if ($previousContainerFields.length) {
      syncLayoutClasses($previousContainerFields.first());
    } else {
      maybeDeleteAnEmptyFieldGroup($previousFieldContainer.get(0));
    }
  }
  function maybeUpdateDraggableClassAfterDrop(draggable, $previousContainerFields) {
    if (0 !== $previousContainerFields.length || 1 !== getFieldsInRow(jQuery(draggable.parentNode)).length) {
      syncLayoutClasses(jQuery(draggable));
    }
  }

  /**
   * Remove an empty field group, but don't remove an empty section.
   *
   * @param {Element} previousFieldContainer
   * @return {void}
   */
  function maybeDeleteAnEmptyFieldGroup(previousFieldContainer) {
    var closestFieldBox = previousFieldContainer.closest('li.frm_field_box');
    if (closestFieldBox && !closestFieldBox.classList.contains('edit_field_type_divider')) {
      closestFieldBox.remove();
    }
  }
  function handleDragOverYAxis(_ref) {
    var droppable = _ref.droppable,
      y = _ref.y,
      placeholder = _ref.placeholder;
    var $list = jQuery(droppable);
    var top;
    $children = $list.children().not('.edit_field_type_end_divider');
    if (0 === $children.length) {
      $list.prepend(placeholder);
      top = 0;
    } else {
      var insertAtIndex = determineIndexBasedOffOfMousePositionInList($list, y);
      if (insertAtIndex === $children.length) {
        var $lastChild = jQuery($children.get(insertAtIndex - 1));
        top = $lastChild.offset().top + $lastChild.outerHeight();
        $list.append(placeholder);

        // Make sure nothing gets inserted after the end divider.
        var $endDivider = $list.children('.edit_field_type_end_divider');
        if ($endDivider.length) {
          $list.append($endDivider);
        }
      } else {
        top = jQuery($children.get(insertAtIndex)).offset().top;
        jQuery($children.get(insertAtIndex)).before(placeholder);
      }
    }
    top -= $list.offset().top;
    placeholder.style.top = top + 'px';
  }
  function determineIndexBasedOffOfMousePositionInList($list, y) {
    var $items = $list.children().not('.edit_field_type_end_divider');
    var length = $items.length;
    var index, item, itemTop, returnIndex;
    if (!document.querySelector('.frm-has-fields .frm_no_fields')) {
      // Always return 0 when there are no fields.
      return 0;
    }
    returnIndex = 0;
    for (index = length - 1; index >= 0; --index) {
      item = $items.get(index);
      itemTop = jQuery(item).offset().top;
      if (y > itemTop) {
        returnIndex = index;
        if (y > itemTop + jQuery(item).outerHeight() / 2) {
          returnIndex = index + 1;
        }
        break;
      }
    }
    return returnIndex;
  }
  function handleDragOverFieldGroup(_ref2) {
    var droppable = _ref2.droppable,
      x = _ref2.x,
      placeholder = _ref2.placeholder;
    var $row = jQuery(droppable);
    var $children = getFieldsInRow($row);
    if (!$children.length) {
      return;
    }
    var left;
    var insertAtIndex = determineIndexBasedOffOfMousePositionInRow($row, x);
    if (insertAtIndex === $children.length) {
      var $lastChild = jQuery($children.get(insertAtIndex - 1));
      left = $lastChild.offset().left + $lastChild.outerWidth();
      $row.append(placeholder);
    } else {
      left = jQuery($children.get(insertAtIndex)).offset().left;
      jQuery($children.get(insertAtIndex)).before(placeholder);
      var amountToOffsetLeftBy = 0 === insertAtIndex ? 4 : 8; // Offset by 8 in between rows, but only 4 for the first item in a group.
      left -= amountToOffsetLeftBy; // Offset the placeholder slightly so it appears between two fields.
    }
    left -= $row.offset().left;
    placeholder.style.left = left + 'px';
  }
  function syncAfterDragAndDrop() {
    fixUnwrappedListItems();
    toggleSectionHolder();
    maybeFixEndDividers();
    maybeDeleteEmptyFieldGroups();
    updateFieldOrder();
    var event = new Event('frm_sync_after_drag_and_drop', {
      bubbles: false
    });
    document.dispatchEvent(event);
  }
  function maybeFixEndDividers() {
    document.querySelectorAll('.edit_field_type_end_divider').forEach(function (endDivider) {
      return endDivider.parentNode.appendChild(endDivider);
    });
  }
  function maybeDeleteEmptyFieldGroups() {
    document.querySelectorAll('li.form_field_box:not(.form-field)').forEach(function (fieldGroup) {
      return !fieldGroup.children.length && fieldGroup.remove();
    });
  }
  function fixUnwrappedListItems() {
    var lists = document.querySelectorAll('ul#frm-show-fields, ul.start_divider');
    lists.forEach(function (list) {
      list.childNodes.forEach(function (child) {
        if ('undefined' === typeof child.classList) {
          return;
        }
        if (child.classList.contains('edit_field_type_end_divider')) {
          // Never wrap end divider in place.
          return;
        }
        if ('undefined' !== typeof child.classList && child.classList.contains('form-field')) {
          wrapFieldLiInPlace(child);
        }
      });
    });
  }
  function deleteEmptyDividerWrappers() {
    var dividers = document.querySelectorAll('ul.start_divider');
    if (!dividers.length) {
      return;
    }
    dividers.forEach(function (divider) {
      var children = [].slice.call(divider.children);
      children.forEach(function (child) {
        if (0 === child.children.length) {
          child.remove();
        } else if (1 === child.children.length && 'ul' === child.firstElementChild.nodeName.toLowerCase() && 0 === child.firstElementChild.children.length) {
          child.remove();
        }
      });
    });
  }
  function getFieldsInRow($row) {
    var $fields = jQuery();
    var row = $row.get(0);
    if (!row.children) {
      return $fields;
    }
    Array.from(row.children).forEach(function (child) {
      if ('none' === child.style.display) {
        return;
      }
      var classes = child.classList;
      if (!classes.contains('form-field') || classes.contains('edit_field_type_end_divider') || classes.contains('frm-sortable-helper')) {
        return;
      }
      $fields = $fields.add(child);
    });
    return $fields;
  }
  function determineIndexBasedOffOfMousePositionInRow($row, x) {
    var $inputs = getFieldsInRow($row),
      length = $inputs.length,
      index,
      input,
      inputLeft,
      returnIndex;
    returnIndex = 0;
    for (index = length - 1; index >= 0; --index) {
      input = $inputs.get(index);
      inputLeft = jQuery(input).offset().left;
      if (x > inputLeft) {
        returnIndex = index;
        if (x > inputLeft + jQuery(input).outerWidth() / 2) {
          returnIndex = index + 1;
        }
        break;
      }
    }
    return returnIndex;
  }
  function syncLayoutClasses($item, type) {
    var $fields, size, layoutClasses, classToAddFunction;
    if ('undefined' === typeof type) {
      type = 'even';
    }
    $fields = $item.parent().children('li.form-field, li.frmbutton_loadingnow').not('.edit_field_type_end_divider');
    size = $fields.length;
    layoutClasses = getLayoutClasses();
    if ('even' === type && 5 !== size) {
      $fields.each(getSyncLayoutClass(layoutClasses, getEvenClassForSize(size)));
    } else if ('clear' === type) {
      $fields.each(getSyncLayoutClass(layoutClasses, ''));
    } else {
      if (-1 !== ['left', 'right', 'middle', 'even'].indexOf(type)) {
        classToAddFunction = function classToAddFunction(index) {
          return getClassForBlock(size, type, index);
        };
      } else {
        classToAddFunction = function classToAddFunction(index) {
          var size = type[index];
          return getLayoutClassForSize(size);
        };
      }
      $fields.each(getSyncLayoutClass(layoutClasses, classToAddFunction));
    }
    updateFieldGroupControls($item.parent(), $fields.length);
  }
  function updateFieldGroupControls($row, count) {
    var rowOffset, shouldShowControls, controls;
    rowOffset = $row.offset();
    if ('undefined' === typeof rowOffset) {
      return;
    }
    shouldShowControls = count >= 2;
    controls = document.getElementById('frm_field_group_controls');
    if (null === controls) {
      if (!shouldShowControls) {
        // exit early. if we do not need controls and they do not exist, do nothing.
        return;
      }
      controls = div();
      controls.id = 'frm_field_group_controls';
      controls.setAttribute('role', 'group');
      controls.setAttribute('tabindex', 0);
      setFieldControlsHtml(controls);
      builderPage.appendChild(controls);
    }
    $row.append(controls);
    controls.style.display = shouldShowControls ? 'block' : 'none';
  }
  function setFieldControlsHtml(controls) {
    var layoutOption, moveOption;
    layoutOption = document.createElement('span');
    layoutOption.innerHTML = '<svg class="frmsvg"><use href="#frm_field_group_layout_icon"></use></svg>';
    var layoutOptionLabel = __('Set Row Layout', 'formidable');
    addTooltip(layoutOption, layoutOptionLabel);
    makeTabbable(layoutOption, layoutOptionLabel);
    moveOption = document.createElement('span');
    moveOption.innerHTML = '<svg class="frmsvg"><use href="#frm_thick_move_icon"></use></svg>';
    moveOption.classList.add('frm-move');
    var moveOptionLabel = __('Move Field Group', 'formidable');
    addTooltip(moveOption, moveOptionLabel);
    makeTabbable(moveOption, moveOptionLabel);
    controls.innerHTML = '';
    controls.appendChild(layoutOption);
    controls.appendChild(moveOption);
    controls.appendChild(getFieldControlsDropdown());
  }
  function addTooltip(element, title) {
    element.setAttribute('data-bs-toggle', 'tooltip');
    element.setAttribute('data-bs-container', 'body');
    element.setAttribute('title', title);
    element.addEventListener('mouseover', function () {
      if (null === element.getAttribute('data-original-title')) {
        jQuery(element).tooltip();
      }
    });
  }
  function getFieldControlsDropdown() {
    var dropdown = span({
      className: 'dropdown'
    });
    var trigger = a({
      className: 'frm_bstooltip frm-hover-icon frm-dropdown-toggle dropdown-toggle',
      children: [span({
        child: svg({
          href: '#frm_thick_more_vert_icon'
        })
      }), span({
        className: 'screen-reader-text',
        text: __('Toggle More Options Dropdown', 'formidable')
      })]
    });
    frmDom.setAttributes(trigger, {
      title: __('More Options', 'formidable'),
      'data-bs-toggle': 'dropdown',
      'data-bs-container': 'body',
      'data-bs-display': 'static'
    });
    makeTabbable(trigger, __('More Options', 'formidable'));
    dropdown.appendChild(trigger);
    var ul = div({
      className: 'frm-dropdown-menu dropdown-menu dropdown-menu-right'
    });
    ul.setAttribute('role', 'menu');
    dropdown.appendChild(ul);
    return dropdown;
  }
  function getSyncLayoutClass(layoutClasses, classToAdd) {
    return function (itemIndex) {
      var currentClassToAdd, length, layoutClassIndex, currentClass, activeLayoutClass, fieldId, layoutClassesInput;
      currentClassToAdd = 'function' === typeof classToAdd ? classToAdd(itemIndex) : classToAdd;
      length = layoutClasses.length;
      activeLayoutClass = false;
      for (layoutClassIndex = 0; layoutClassIndex < length; ++layoutClassIndex) {
        currentClass = layoutClasses[layoutClassIndex];
        if (this.classList.contains(currentClass)) {
          activeLayoutClass = currentClass;
          break;
        }
      }
      fieldId = this.dataset.fid;
      if ('undefined' === typeof fieldId) {
        // we are syncing the drag/drop placeholder before the actual field has loaded.
        // this will get called again afterward and the input will exist then.
        this.classList.add(currentClassToAdd);
        return;
      }
      moveFieldSettings(document.getElementById('frm-single-settings-' + fieldId));
      layoutClassesInput = document.getElementById('frm_classes_' + fieldId);
      if (null === layoutClassesInput) {
        // not every field type has a layout class input.
        return;
      }
      if (false === activeLayoutClass) {
        if ('' !== currentClassToAdd) {
          layoutClassesInput.value = layoutClassesInput.value.concat(' ' + currentClassToAdd);
        }
      } else {
        this.classList.remove(activeLayoutClass);
        layoutClassesInput.value = layoutClassesInput.value.replace(activeLayoutClass, currentClassToAdd);
      }
      if (this.classList.contains('frm_first')) {
        this.classList.remove('frm_first');
        layoutClassesInput.value = layoutClassesInput.value.replace('frm_first', '').trim();
      }
      if (0 === itemIndex) {
        this.classList.add('frm_first');
        layoutClassesInput.value = layoutClassesInput.value.concat(' frm_first');
      }
      jQuery(layoutClassesInput).trigger('change');
    };
  }
  function getLayoutClasses() {
    return ['frm_full', 'frm_half', 'frm_third', 'frm_fourth', 'frm_sixth', 'frm_two_thirds', 'frm_three_fourths', 'frm1', 'frm2', 'frm3', 'frm4', 'frm5', 'frm6', 'frm7', 'frm8', 'frm9', 'frm10', 'frm11', 'frm12'];
  }
  function setupFieldOptionSorting(sort) {
    var opts = {
      items: '.frm_sortable_field_opts li',
      axis: 'y',
      opacity: 0.65,
      forcePlaceholderSize: false,
      handle: '.frm-drag',
      helper: function helper(e, li) {
        copyHelper = li.clone().insertAfter(li);
        return li.clone();
      },
      stop: function stop(e, ui) {
        copyHelper && copyHelper.remove();
        var fieldId = ui.item.attr('id').replace('frm_delete_field_', '').replace('-' + ui.item.data('optkey') + '_container', '');
        resetDisplayedOpts(fieldId);
        fieldUpdated();
      }
    };
    jQuery(sort).sortable(opts);
  }

  // Get the section where a field is dropped
  function getSectionForFieldPlacement(currentItem) {
    var section = '';
    if (typeof currentItem !== 'undefined' && !currentItem.hasClass('edit_field_type_divider')) {
      section = currentItem.closest('.edit_field_type_divider');
    }
    return section;
  }

  // Get the form ID where a field is dropped
  function getFormIdForFieldPlacement(section) {
    var formId = '';
    if (typeof section[0] !== 'undefined') {
      var sDivide = section.children('.start_divider');
      sDivide.children('.edit_field_type_end_divider').appendTo(sDivide);
      if (typeof section.attr('data-formid') !== 'undefined') {
        var fieldId = section.attr('data-fid');
        formId = jQuery('input[name="field_options[form_select_' + fieldId + ']"]').val();
      }
    }
    if (typeof formId === 'undefined' || formId === '') {
      formId = thisFormId;
    }
    return formId;
  }

  // Get the section ID where a field is dropped
  function getSectionIdForFieldPlacement(section) {
    var sectionId = 0;
    if (typeof section[0] !== 'undefined') {
      sectionId = section.attr('id').replace('frm_field_id_', '');
    }
    return sectionId;
  }

  /**
   * Update a field after it is dragged and dropped into, out of, or between sections
   *
   * @param {Object} currentItem
   * @param {Object} previousSection
   * @return {void}
   */
  function updateFieldAfterMovingBetweenSections(currentItem, previousSection) {
    if (!currentItem.hasClass('form-field')) {
      // currentItem is a field group. Call for children recursively.
      getFieldsInRow(jQuery(currentItem.get(0).firstChild)).each(function () {
        updateFieldAfterMovingBetweenSections(jQuery(this), previousSection);
      });
      return;
    }
    var fieldId = currentItem.attr('id').replace('frm_field_id_', '');
    var section = getSectionForFieldPlacement(currentItem);
    var formId = getFormIdForFieldPlacement(section);
    var sectionId = getSectionIdForFieldPlacement(section);
    var previousFormId = previousSection ? getFormIdForFieldPlacement(jQuery(previousSection.parentNode)) : 0;
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_update_field_after_move',
        form_id: formId,
        field: fieldId,
        section_id: sectionId,
        previous_form_id: previousFormId,
        nonce: frmGlobal.nonce
      },
      success: function success() {
        toggleSectionHolder();
        updateInSectionValue(fieldId, sectionId);
      }
    });
  }

  // Update the in_section field value
  function updateInSectionValue(fieldId, sectionId) {
    document.getElementById('frm_in_section_' + fieldId).value = sectionId;
  }

  /**
   * Get the arguments for inserting a new field.
   *
   * @since 6.23
   *
   * @param {string} fieldType
   * @param {string} sectionId
   * @param {string} formId
   * @param {Number} hasBreak
   *
   * @return {Object}
   */
  function getInsertNewFieldArgs(fieldType, sectionId, formId, hasBreak) {
    var fieldArgs = {
      action: 'frm_insert_field',
      form_id: formId,
      field_type: fieldType,
      section_id: sectionId,
      nonce: frmGlobal.nonce,
      has_break: hasBreak
    };

    // Only send last row field IDs to update their order if this field isn't added to a repeater.
    var isInRepeater = sectionId > 0 && document.getElementById('form_id').value !== formId;
    if (!isInRepeater) {
      fieldArgs.last_row_field_ids = getFieldIdsInSubmitRow();
    }
    return fieldArgs;
  }

  /**
   * Returns true if it's a range field type and slider type is not selected.
   *
   * @since 6.23
   *
   * @param {string} fieldType
   * @return {boolean}
   */
  function shouldStopInsertingField(fieldType) {
    return wp.hooks.applyFilters('frm_should_stop_inserting_field', false, fieldType);
  }

  /**
   * Add a new field by dragging and dropping it from the Fields sidebar
   *
   * @param {string} fieldType
   */
  function insertNewFieldByDragging(fieldType) {
    if (shouldStopInsertingField(fieldType)) {
      wp.hooks.doAction('frm_stopped_inserting_by_dragging', fieldType);
      return;
    }
    var placeholder = document.getElementById('frm_drag_placeholder');
    var loadingID = fieldType.replace('|', '-') + '_' + getAutoId();
    var loading = tag('li', {
      id: loadingID,
      className: 'frm-wait frmbutton_loadingnow'
    });
    var $placeholder = jQuery(loading);
    var currentItem = jQuery(placeholder);
    var section = getSectionForFieldPlacement(currentItem);
    var formId = getFormIdForFieldPlacement(section);
    var sectionId = getSectionIdForFieldPlacement(section);
    placeholder.parentNode.insertBefore(loading, placeholder);
    placeholder.remove();
    syncLayoutClasses($placeholder);
    var hasBreak = 0;
    if ('summary' === fieldType) {
      // see if we need to insert a page break before this newly-added summary field. Check for at least 1 page break
      hasBreak = jQuery('.frmbutton_loadingnow#' + loadingID).prevAll('li[data-type="break"]').length ? 1 : 0;
    }
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: getInsertNewFieldArgs(fieldType, sectionId, formId, hasBreak),
      success: function success(msg) {
        handleInsertFieldByDraggingResponse(msg, $placeholder);
        var fieldId = checkMsgForFieldId(msg);
        if (fieldId) {
          /**
           * Fires after a field is added.
           *
           * @since 6.23
           *
           * @param {Object} fieldData            The field data.
           * @param {string} fieldData.field      The field HTML.
           * @param {string} fieldData.field_type The field type.
           * @param {string} fieldData.form_id    The form ID.
           */
          wp.hooks.doAction('frm_after_field_added_in_form_builder', {
            field: msg,
            fieldId: fieldId,
            fieldType: fieldType,
            form_id: formId
          });
        }
      },
      error: handleInsertFieldError
    });
  }

  /**
   * @param {string} msg
   * @param {Object} $placeholder jQuery object.
   */
  function handleInsertFieldByDraggingResponse(msg, $placeholder) {
    var replaceWith;
    document.getElementById('frm_form_editor_container').classList.add('frm-has-fields');
    var $siblings = $placeholder.siblings('li.form-field').not('.edit_field_type_end_divider');
    if (!$siblings.length) {
      // if dragging into a new row, we need to wrap the li first.
      replaceWith = wrapFieldLi(msg);
    } else {
      replaceWith = msgAsjQueryObject(msg);
      if (!$placeholder.get(0).parentNode.parentNode.classList.contains('ui-draggable')) {
        // If a field group wasn't draggable because it only had a single field, make it draggable.
        makeDraggable($placeholder.get(0).parentNode.parentNode, '.frm-move');
      }
    }
    $placeholder.replaceWith(replaceWith);
    updateFieldOrder();
    afterAddField(msg, false);
    if ($siblings.length) {
      syncLayoutClasses($siblings.first());
    }
    toggleSectionHolder();
    if (!$siblings.length) {
      makeDroppable(replaceWith.get(0).querySelector('ul.frm_sorting'));
      makeDraggable(replaceWith.get(0).querySelector('li.form-field'), '.frm-move');
    } else {
      makeDraggable(replaceWith.get(0), '.frm-move');
    }
  }

  /**
   * Get the field ID from the response message.
   *
   * @since 6.23
   *
   * @param {string} msg
   * @return {Number}
   */
  function checkMsgForFieldId(msg) {
    var result = msg.match(/data-fid="(\d+)"/);
    return result ? parseInt(result[1]) : 0;
  }
  function getFieldIdsInSubmitRow() {
    var submitField = document.querySelector('.edit_field_type_submit');
    if (!submitField) {
      return [];
    }
    var lastRowFields = submitField.parentNode.children;
    var ids = [];
    for (var _i2 = 0; _i2 < lastRowFields.length; _i2++) {
      ids.push(lastRowFields[_i2].dataset.fid);
    }
    return ids;
  }
  function moveFieldThatAlreadyExists(draggable, placeholder) {
    placeholder.parentNode.insertBefore(draggable, placeholder);
  }
  function msgAsjQueryObject(msg) {
    var element = div();
    element.innerHTML = msg;
    return jQuery(element.firstChild);
  }
  function handleInsertFieldError(jqXHR, _, errorThrown) {
    maybeShowInsertFieldError(errorThrown, jqXHR);
  }
  function maybeShowInsertFieldError(errorThrown, jqXHR) {
    if (!jqXHRAborted(jqXHR)) {
      infoModal(errorThrown + '. Please try again.');
    }
  }
  function jqXHRAborted(jqXHR) {
    return jqXHR.status === 0 || jqXHR.readyState === 0;
  }

  /**
   * Get a unique id that automatically increments with every function call.
   * Can be used for any UI that requires a unique id.
   * Not to be used in data.
   *
   * @return {number}
   */
  function getAutoId() {
    return ++autoId;
  }

  /**
   * Determine if a draggable element can be droppable into a droppable element.
   *
   * Don't allow page break, embed form, or section inside section field
   * Don't allow page breaks inside of field groups.
   * Don't allow field groups with sections inside of sections.
   * Don't allow field groups in field groups.
   * Don't allow hidden fields inside of field groups but allow them in sections.
   * Don't allow any fields below the submit button field.
   * Don't allow submit button field above any fields.
   * Don't allow GDPR fields in repeaters.
   *
   * @param {HTMLElement} draggable
   * @param {HTMLElement} droppable
   * @param {Event}       event
   * @return {Boolean}
   */
  function allowDrop(draggable, droppable, event) {
    if (false === droppable) {
      // Don't show drop placeholder if dragging somewhere off of the droppable area.
      return false;
    }
    if (droppable.closest('.frm-sortable-helper')) {
      // Do not allow drop into draggable.
      return false;
    }
    var isSubmitBtn = draggable.classList.contains('edit_field_type_submit');
    var containSubmitBtn = !draggable.classList.contains('form_field') && !!draggable.querySelector('.edit_field_type_submit');
    if ('frm-show-fields' === droppable.id) {
      var draggableIndex = determineIndexBasedOffOfMousePositionInList(jQuery(droppable), event.clientY);
      if (isSubmitBtn || containSubmitBtn) {
        // Do not allow dropping submit button to above position.
        var lastRowIndex = droppable.childElementCount - 1;
        return draggableIndex > lastRowIndex;
      }

      // Do not allow dropping other fields to below submit button.
      var submitButtonIndex = jQuery(droppable.querySelector('.edit_field_type_submit').closest('#frm-show-fields > li')).index();
      return draggableIndex <= submitButtonIndex;
    }
    if (isSubmitBtn) {
      if (droppable.classList.contains('start_divider')) {
        // Don't allow dropping submit button into a repeater.
        return false;
      }
      if (isLastRow(droppable.parentElement)) {
        // Allow dropping submit button into the last row.
        return true;
      }
      if (!isLastRow(droppable.parentElement.nextElementSibling)) {
        // Don't a dropping submit button into the row that isn't the second one from bottom.
        return false;
      }

      // Allow dropping submit button into the second row from bottom if there is only submit button in the last row.
      return !draggable.parentElement.querySelector('li.frm_field_box:not(.edit_field_type_submit)');
    }
    if (droppable.classList.contains('start_divider') && (draggable.classList.contains('edit_field_type_gdpr') || draggable.id === 'gdpr') && droppable.closest('.repeat_section')) {
      // Don't allow GDPR fields in repeaters.
      return false;
    }
    if (!droppable.classList.contains('start_divider')) {
      var $fieldsInRow = getFieldsInRow(jQuery(droppable));
      if (!groupCanFitAnotherField($fieldsInRow, jQuery(draggable))) {
        // Field group is full and cannot accept another field.
        return false;
      }
      if (draggable.id === 'divider' && droppable.closest('.start_divider')) {
        return false;
      }
    }
    var isNewField = draggable.classList.contains('frm-new-field');
    if (isNewField) {
      return allowNewFieldDrop(draggable, droppable);
    }
    return allowMoveField(draggable, droppable);
  }

  /**
   * Checks if given element is the last row in form builder.
   *
   * @param {HTMLElement} element Element.
   * @return {Boolean}
   */
  function isLastRow(element) {
    return element && element.matches('#frm-show-fields > li:last-child');
  }

  // Don't allow a new page break or hidden field in a field group.
  // Don't allow a new field into a field group that includes a page break or hidden field.
  // Don't allow a new section inside of a section.
  // Don't allow an embedded form in a section.
  function allowNewFieldDrop(draggable, droppable) {
    var classes = draggable.classList;
    var newPageBreakField = classes.contains('frm_tbreak');
    var newHiddenField = classes.contains('frm_thidden');
    var newSectionField = classes.contains('frm_tdivider');
    var newEmbedField = classes.contains('frm_tform');
    var newUserIdField = classes.contains('frm_tuser_id');
    var newFieldWillBeAddedToAGroup = !('frm-show-fields' === droppable.id || droppable.classList.contains('start_divider'));
    if (newFieldWillBeAddedToAGroup) {
      if (groupIncludesBreakOrHiddenOrUserId(droppable)) {
        // Never allow any field beside a page break or a hidden field.
        return false;
      }
      return !newHiddenField && !newPageBreakField && !newUserIdField;
    }
    var fieldTypeIsAlwaysAllowed = !newPageBreakField && !newHiddenField && !newSectionField && !newEmbedField;
    if (fieldTypeIsAlwaysAllowed) {
      return true;
    }
    var newFieldWillBeAddedToASection = droppable.classList.contains('start_divider') || null !== droppable.closest('.start_divider');
    if (newFieldWillBeAddedToASection) {
      // Don't allow a section or an embedded form in a section.
      return !newEmbedField && !newSectionField;
    }
    return true;
  }
  function allowMoveField(draggable, droppable) {
    if (isFieldGroup(draggable)) {
      return allowMoveFieldGroup(draggable, droppable);
    }
    var isPageBreak = draggable.classList.contains('edit_field_type_break');
    if (isPageBreak) {
      // Page breaks are only allowed in the main list of fields, not in sections or in field groups.
      return false;
    }
    if (droppable.classList.contains('start_divider')) {
      return allowMoveFieldToSection(draggable);
    }
    var isHiddenField = draggable.classList.contains('edit_field_type_hidden');
    var isUserIdField = draggable.classList.contains('edit_field_type_user_id');
    if (isHiddenField || isUserIdField) {
      // Hidden fields and user id fields should not be added to field groups since they're not shown
      // and don't make sense with the grid distribution.
      return false;
    }
    return allowMoveFieldToGroup(draggable, droppable);
  }
  function isFieldGroup(draggable) {
    return draggable.classList.contains('frm_field_box') && !draggable.classList.contains('form-field');
  }
  function allowMoveFieldGroup(fieldGroup, droppable) {
    if (droppable.classList.contains('start_divider') && null === fieldGroup.querySelector('.start_divider')) {
      // Allow a field group with no section inside of a section.
      return true;
    }
    return false;
  }
  function allowMoveFieldToSection(draggable) {
    var draggableIncludeEmbedForm = draggable.classList.contains('edit_field_type_form') || draggable.querySelector('.edit_field_type_form');
    if (draggableIncludeEmbedForm) {
      // Do not allow an embedded form inside of a section.
      return false;
    }
    var draggableIncludesSection = draggable.classList.contains('edit_field_type_divider') || draggable.querySelector('.edit_field_type_divider');
    if (draggableIncludesSection) {
      // Do not allow a section inside of a section.
      return false;
    }
    return true;
  }
  function allowMoveFieldToGroup(draggable, group) {
    if (groupIncludesBreakOrHiddenOrUserId(group)) {
      // Never allow any field beside a page break or a hidden field.
      return false;
    }
    var isFieldGroup = jQuery(draggable).children('ul.frm_sorting').not('.start_divider').length > 0;
    if (isFieldGroup) {
      // Do not allow a field group directly inside of a field group unless it's in a section.
      return false;
    }
    var draggableIncludesASection = draggable.classList.contains('edit_field_type_divider') || draggable.querySelector('.edit_field_type_divider');
    var draggableIsEmbedField = draggable.classList.contains('edit_field_type_form');
    var groupIsInASection = null !== group.closest('.start_divider');
    if (groupIsInASection && (draggableIncludesASection || draggableIsEmbedField)) {
      // Do not allow a section or an embed field inside of a section.
      return false;
    }
    return true;
  }
  function groupIncludesBreakOrHiddenOrUserId(group) {
    return null !== group.querySelector('.edit_field_type_break, .edit_field_type_hidden, .edit_field_type_user_id');
  }
  function groupCanFitAnotherField(fieldsInRow, $field) {
    var fieldId;
    if (fieldsInRow.length < MAX_FIELD_GROUP_SIZE) {
      return true;
    }
    if (fieldsInRow.length > MAX_FIELD_GROUP_SIZE) {
      return false;
    }
    fieldId = $field.attr('data-fid');
    // Allow the maximum number if we're not changing field groups.
    return 1 === jQuery(fieldsInRow).filter('[data-fid="' + fieldId + '"]').length;
  }
  function loadFields(fieldId) {
    var thisField = document.getElementById(fieldId);
    var $thisField = jQuery(thisField);
    var field = [];
    var addHtmlToField = function addHtmlToField(element) {
      var frmHiddenFdata = element.querySelector('.frm_hidden_fdata');
      element.classList.add('frm_load_now');
      if (frmHiddenFdata !== null) {
        field.push(frmHiddenFdata.innerHTML);
      }
    };
    var nextElement = thisField;
    addHtmlToField(nextElement);
    var nextField = getNextField(nextElement);
    while (nextField && field.length < 15) {
      addHtmlToField(nextField);
      nextElement = nextField;
      nextField = getNextField(nextField);
    }
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_load_field',
        field: field,
        form_id: thisFormId,
        nonce: frmGlobal.nonce
      },
      success: function success(html) {
        return handleAjaxLoadFieldSuccess(html, $thisField, field);
      }
    });
  }
  function getNextField(field) {
    var _field$parentNode;
    if (field.nextElementSibling) {
      return field.nextElementSibling;
    }
    return (_field$parentNode = field.parentNode) === null || _field$parentNode === void 0 || (_field$parentNode = _field$parentNode.closest('.frm_field_box')) === null || _field$parentNode === void 0 || (_field$parentNode = _field$parentNode.nextElementSibling) === null || _field$parentNode === void 0 ? void 0 : _field$parentNode.querySelector('.form-field');
  }
  function handleAjaxLoadFieldSuccess(html, $thisField, field) {
    var key, $nextSet;
    html = html.replace(/^\s+|\s+$/g, '');
    if (html.indexOf('{') !== 0) {
      jQuery('.frm_load_now').removeClass('.frm_load_now').html('Error');
      return;
    }
    html = JSON.parse(html);
    for (key in html) {
      jQuery('#frm_field_id_' + key).replaceWith(html[key]);
      setupSortable('#frm_field_id_' + key + '.edit_field_type_divider ul.frm_sorting');
      makeDraggable(document.getElementById('frm_field_id_' + key));
    }
    $nextSet = $thisField.nextAll('.frm_field_loading:not(.frm_load_now)');
    if ($nextSet.length) {
      loadFields($nextSet.attr('id'));
    } else {
      // go up a level
      $nextSet = jQuery(document.getElementById('frm-show-fields')).find('.frm_field_loading:not(.frm_load_now)');
      if ($nextSet.length) {
        loadFields($nextSet.attr('id'));
      }
    }
    initiateMultiselect();
    renumberPageBreaks();
    maybeHideQuantityProductFieldOption();
    var loadedEvent = new Event('frm_ajax_loaded_field', {
      bubbles: false
    });
    loadedEvent.frmFields = field.map(function (f) {
      return JSON.parse(f);
    });
    document.dispatchEvent(loadedEvent);
  }
  function addFieldClick() {
    /*jshint validthis:true */
    var $thisObj = jQuery(this);
    // there is no real way to disable a <a> (with a valid href attribute) in HTML - https://css-tricks.com/how-to-disable-links/
    if ($thisObj.hasClass('disabled')) {
      return false;
    }
    var $button = $thisObj.closest('.frmbutton');
    var fieldType = $button.attr('id');
    if (shouldStopInsertingField(fieldType)) {
      return;
    }
    var hasBreak = 0;
    if ('summary' === fieldType) {
      hasBreak = $newFields.children('li[data-type="break"]').length > 0 ? 1 : 0;
    }
    var formId = thisFormId;
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: getInsertNewFieldArgs(fieldType, 0, formId, hasBreak),
      success: function success(msg) {
        handleAddFieldClickResponse(msg);
        var fieldId = checkMsgForFieldId(msg);
        if (fieldId) {
          /**
           * Fires after a field is added.
           *
           * @since 6.23
           *
           * @param {Object} fieldData            The field data.
           * @param {string} fieldData.field      The field HTML.
           * @param {string} fieldData.field_type The field type.
           * @param {string} fieldData.form_id    The form ID.
           */
          wp.hooks.doAction('frm_after_field_added_in_form_builder', {
            field: msg,
            fieldId: fieldId,
            fieldType: fieldType,
            form_id: formId
          });
        }
      },
      error: handleInsertFieldError
    });
    return false;
  }
  function handleAddFieldClickResponse(msg) {
    document.getElementById('frm_form_editor_container').classList.add('frm-has-fields');
    var replaceWith = wrapFieldLi(msg);
    var submitField = $newFields[0].querySelector('.edit_field_type_submit');
    if (!submitField) {
      $newFields.append(replaceWith);
    } else {
      jQuery(submitField.closest('.frm_field_box:not(.form-field)')).before(replaceWith);
    }
    afterAddField(msg, true);
    replaceWith.each(function () {
      makeDroppable(this.querySelector('ul.frm_sorting'));
      makeDraggable(this.querySelector('.form-field'), '.frm-move');
    });
  }
  function insertFormField(fieldType) {
    var fieldOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    return new Promise(function (resolve) {
      var formId = thisFormId;
      var hasBreak = 0;
      if ('summary' === fieldType) {
        hasBreak = $newFields.children('li[data-type="break"]').length > 0 ? 1 : 0;
      }
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: Object.assign(getInsertNewFieldArgs(fieldType, 0, formId, hasBreak), {
          field_options: fieldOptions
        }),
        success: function success(msg) {
          resolve(msg);
          setTimeout(function () {
            updateFieldOrder();
            afterAddField(msg, true);
            var fieldId = checkMsgForFieldId(msg);
            if (fieldId) {
              /**
               * Fires after a field is added.
               *
               * @since 6.23
               *
               * @param {Object} fieldData            The field data.
               * @param {string} fieldData.field      The field HTML.
               * @param {string} fieldData.field_type The field type.
               * @param {string} fieldData.form_id    The form ID.
               */
              wp.hooks.doAction('frm_after_field_added_in_form_builder', {
                field: msg,
                fieldId: fieldId,
                fieldType: fieldType,
                form_id: formId
              });
            }
          }, 10);
        },
        error: handleInsertFieldError
      });
    });
  }
  function maybeHideQuantityProductFieldOption() {
    var hide = true,
      opts = document.querySelectorAll('.frmjs_prod_field_opt_cont');
    if ($newFields.find('li.edit_field_type_product').length > 1) {
      hide = false;
    }
    for (var _i3 = 0; _i3 < opts.length; _i3++) {
      if (hide) {
        opts[_i3].classList.add('frm_hidden');
      } else {
        opts[_i3].classList.remove('frm_hidden');
      }
    }
  }

  /**
   * Returns true if a field can be duplicated.
   *
   * @since 6.19
   *
   * @param {HTMLElement} field
   * @param {number}      maxFieldsInGroup
   *
   * @return {Boolean}
   */
  function canDuplicateField(field, maxFieldsInGroup) {
    if (field.classList.contains('frm-page-collapsed')) {
      return false;
    }
    var fieldGroup = field.closest('li.frm_field_box:not(.form-field)');
    if (!fieldGroup) {
      return true;
    }
    var fieldsInGroup = getFieldsInRow(jQuery(fieldGroup.querySelector('ul'))).length;
    return fieldsInGroup < maxFieldsInGroup;
  }
  function duplicateField() {
    var $field, fieldId, children, newRowId, fieldOrder;
    var maxFieldsInGroup = MAX_FIELD_GROUP_SIZE;
    $field = jQuery(this).closest('li.form-field');
    newRowId = this.getAttribute('frm-target-row-id');
    if (!(newRowId && newRowId.startsWith('frm_field_group_')) && !canDuplicateField($field.get(0), maxFieldsInGroup)) {
      /* translators: %1$d: Maximum number of fields allowed in a field group. */
      infoModal(sprintf(__('You can only have a maximum of %1$d fields in a field group. Delete or move out a field from the group and try again.', 'formidable'), maxFieldsInGroup));
      return;
    }
    closeOpenFieldDropdowns();
    fieldId = $field.data('fid');
    children = fieldsInSection(fieldId);
    if (null !== newRowId) {
      fieldOrder = this.getAttribute('frm-field-order');
    }
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_duplicate_field',
        field_id: fieldId,
        form_id: thisFormId,
        children: children,
        nonce: frmGlobal.nonce
      },
      success: function success(msg) {
        var _$field$0$querySelect;
        var newRow;
        var replaceWith;
        if (null !== newRowId) {
          newRow = document.getElementById(newRowId);
          if (null !== newRow) {
            replaceWith = msgAsjQueryObject(msg);
            if (replaceWith.get(0).querySelector('ul li[data-ftype="divider"]')) {
              jQuery(newRow).append(jQuery(replaceWith.get(0).querySelector('ul li')));
            } else {
              jQuery(newRow).append(replaceWith);
            }
            makeDraggable(replaceWith.get(0), '.frm-move');
            if (null !== fieldOrder) {
              newRow.lastElementChild.setAttribute('frm-field-order', fieldOrder);
            }
            jQuery(newRow).trigger('frm_added_duplicated_field_to_row', {
              duplicatedFieldHtml: msg,
              originalFieldId: fieldId
            });
            afterAddField(msg, false);
            setLayoutClassesForDuplicatedFieldInGroup($field.get(0), replaceWith.get(0));
            return;
          }
        }
        if ($field.siblings('li.form-field').length) {
          replaceWith = msgAsjQueryObject(msg);
          $field.after(replaceWith);
          syncLayoutClasses($field);
          makeDraggable(replaceWith.get(0), '.frm-move');
        } else {
          replaceWith = wrapFieldLi(msg);
          $field.parent().parent().after(replaceWith);
          makeDroppable(replaceWith.get(0).querySelector('ul.frm_sorting'));
          makeDraggable(replaceWith.get(0).querySelector('li.form-field'), '.frm-move');
        }
        updateFieldOrder();
        afterAddField(msg, false);
        maybeDuplicateUnsavedSettings(fieldId, msg);
        toggleOneSectionHolder(replaceWith.find('.start_divider'));
        (_$field$0$querySelect = $field[0].querySelector('.frm-dropdown-menu.dropdown-menu-right')) === null || _$field$0$querySelect === void 0 || _$field$0$querySelect.classList.remove('show');
        setLayoutClassesForDuplicatedFieldInGroup($field.get(0), replaceWith.get(0));
      }
    });
    return false;
  }

  /**
   * Sets the layout classes for a duplicated field in a field group from the layout classes of the original field.
   *
   * @param {HTMLElement} field    The original field.
   * @param {HTMLElement} newField The duplicated field.
   *
   * @return {void}
   */
  function setLayoutClassesForDuplicatedFieldInGroup(field, newField) {
    var _document$getElementB;
    var hoverTarget = field.closest('.frm-field-group-hover-target');
    if (!hoverTarget || !isFieldGroup(hoverTarget.parentElement)) {
      return;
    }
    var fieldId = field.dataset.fid;
    var fieldClasses = (_document$getElementB = document.getElementById('frm_classes_' + fieldId)) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value;
    if (!fieldClasses) {
      return;
    }
    fieldClasses = fieldClasses.replace('frm_first', '');
    if (!newField.className.includes(fieldClasses)) {
      newField.className += ' ' + fieldClasses;
      var classesInput = document.getElementById('frm_classes_' + newField.dataset.fid);
      if (classesInput) {
        classesInput.value = fieldClasses;
      }
    }
  }
  function maybeDuplicateUnsavedSettings(originalFieldId, newFieldHtml) {
    var originalSettings, newFieldId, copySettings, fieldOptionKeys, originalDefault, copyDefault;
    originalSettings = document.getElementById('frm-single-settings-' + originalFieldId);
    if (null === originalSettings) {
      return;
    }
    newFieldId = jQuery(newFieldHtml).attr('data-fid');
    if ('undefined' === typeof newFieldId) {
      return;
    }
    copySettings = document.getElementById('frm-single-settings-' + newFieldId);
    if (null === copySettings) {
      return;
    }
    fieldOptionKeys = ['name', 'required', 'unique', 'read_only', 'placeholder', 'description', 'size', 'max', 'format', 'prepend', 'append', 'separate_value'];
    originalSettings.querySelectorAll('input[name^="field_options["], textarea[name^="field_options["]').forEach(function (originalSetting) {
      var key, tagType, copySetting;
      key = getKeyFromSettingInput(originalSetting);
      if ('options' === key) {
        copyOption(originalSetting, copySettings, originalFieldId, newFieldId);
        return;
      }
      if (-1 === fieldOptionKeys.indexOf(key)) {
        return;
      }
      tagType = originalSetting.matches('input') ? 'input' : 'textarea';
      copySetting = copySettings.querySelector(tagType + '[name="field_options[' + key + '_' + newFieldId + ']"]');
      if (null === copySetting) {
        return;
      }
      if ('checkbox' === originalSetting.type) {
        if (originalSetting.checked !== copySetting.checked) {
          jQuery(copySetting).trigger('click');
        }
      } else if ('text' === originalSetting.type || 'textarea' === tagType) {
        if (originalSetting.value !== copySetting.value) {
          copySetting.value = originalSetting.value;
          jQuery(copySetting).trigger('change');
        }
      }
    });
    originalDefault = originalSettings.querySelector('input[name="default_value_' + originalFieldId + '"]');
    if (null !== originalDefault) {
      copyDefault = copySettings.querySelector('input[name="default_value_' + newFieldId + '"]');
      if (null !== copyDefault && originalDefault.value !== copyDefault.value) {
        copyDefault.value = originalDefault.value;
        jQuery(copyDefault).trigger('change');
      }
    }
  }
  function copyOption(originalSetting, copySettings, originalFieldId, newFieldId) {
    var remainingKeyDetails, copyKey, copySetting;
    remainingKeyDetails = originalSetting.name.substr(23 + ('' + originalFieldId).length);
    copyKey = 'field_options[options_' + newFieldId + ']' + remainingKeyDetails;
    copySetting = copySettings.querySelector('input[name="' + copyKey + '"]');
    if (null !== copySetting && copySetting.value !== originalSetting.value) {
      copySetting.value = originalSetting.value;
      jQuery(copySetting).trigger('change');
    }
  }
  function getKeyFromSettingInput(input) {
    var nameWithoutPrefix, nameSplit;
    nameWithoutPrefix = input.name.substr(14);
    nameSplit = nameWithoutPrefix.split('_');
    nameSplit.pop();
    return nameSplit.join('_');
  }
  function closeOpenFieldDropdowns() {
    var openSettings = document.querySelector('.frm-field-settings-open');
    if (null !== openSettings) {
      openSettings.classList.remove('frm-field-settings-open');
      jQuery(document).off('click', '#frm_builder_page', handleClickOutsideOfFieldSettings);
      jQuery('.frm-field-action-icons .dropdown.open').removeClass('open');
    }
  }
  function handleClickOutsideOfFieldSettings(event) {
    if (!jQuery(event.originalEvent.target).closest('.frm-field-action-icons').length) {
      closeOpenFieldDropdowns();
    }
  }
  function checkForMultiselectKeysOnMouseMove(event) {
    var keyIsDown = !!(event.ctrlKey || event.metaKey || event.shiftKey);
    jQuery(builderPage).toggleClass('frm-multiselect-key-is-down', keyIsDown);
    checkForActiveHoverTarget(event);
  }
  function checkForActiveHoverTarget(event) {
    var container, elementFromPoint, list, previousHoverTarget;
    container = postBodyContent;
    if (container.classList.contains('frm-dragging-field')) {
      return;
    }
    if (null !== document.querySelector('.frm-field-group-hover-target .frm-field-settings-open')) {
      // do not set a hover target if a dropdown is open for the current hover target.
      return;
    }
    elementFromPoint = document.elementFromPoint(event.clientX, event.clientY);
    if (null !== elementFromPoint && !elementFromPoint.classList.contains('edit_field_type_divider')) {
      list = elementFromPoint.closest('ul.frm_sorting');
      if (null !== list && !list.classList.contains('start_divider') && 'frm-show-fields' !== list.id) {
        previousHoverTarget = maybeRemoveGroupHoverTarget();
        if (false !== previousHoverTarget && !jQuery(previousHoverTarget).is(list)) {
          destroyFieldGroupPopup();
        }
        updateFieldGroupControls(jQuery(list), getFieldsInRow(jQuery(list)).length);
        list.classList.add('frm-field-group-hover-target');
        jQuery('#wpbody-content').on('mousemove', maybeRemoveHoverTargetOnMouseMove);
      }
    }
  }
  function maybeRemoveGroupHoverTarget() {
    var controls, previousHoverTarget;
    controls = document.getElementById('frm_field_group_controls');
    if (null !== controls) {
      controls.style.display = 'none';
    }
    previousHoverTarget = document.querySelector('.frm-field-group-hover-target');
    if (null === previousHoverTarget) {
      return false;
    }
    jQuery('#wpbody-content').off('mousemove', maybeRemoveHoverTargetOnMouseMove);
    previousHoverTarget.classList.remove('frm-field-group-hover-target');
    return previousHoverTarget;
  }
  function maybeRemoveHoverTargetOnMouseMove(event) {
    var elementFromPoint = document.elementFromPoint(event.clientX, event.clientY);
    if (null !== elementFromPoint && null !== elementFromPoint.closest('#frm-show-fields')) {
      return;
    }
    maybeRemoveGroupHoverTarget();
    deleteTooltips();
  }
  function onFieldActionDropdownShow(isFieldGroup) {
    unselectFieldGroups();

    // maybe offset the dropdown if it goes off of the right of the screen.
    setTimeout(function () {
      var ul, $ul;
      ul = document.querySelector('.dropdown .frm-dropdown-menu.show');
      if (null === ul) {
        return;
      }
      if (null === ul.getAttribute('aria-label')) {
        ul.setAttribute('aria-label', __('More Options', 'formidable'));
      }
      if (0 === ul.children.length) {
        fillFieldActionDropdown(ul, true === isFieldGroup);
      }
      $ul = jQuery(ul);
      if ($ul.offset().left > jQuery(window).width() - $ul.outerWidth()) {
        ul.style.left = -$ul.outerWidth() + 'px';
      }
      var firstAnchor = ul.firstElementChild.querySelector('a');
      if (firstAnchor) {
        firstAnchor.focus();
      }
    }, 0);
  }
  function onFieldGroupActionDropdownShow() {
    onFieldActionDropdownShow(true);
  }
  function changeSectionStyle(e) {
    var collapsedSection = e.target.closest('.frm-section-collapsed');
    if (!collapsedSection) {
      return;
    }
    if (e.type === 'show') {
      collapsedSection.style.zIndex = 3;
    } else {
      collapsedSection.style.zIndex = 1;
    }
  }
  function fillFieldActionDropdown(ul, isFieldGroup) {
    var classSuffix, options;
    classSuffix = isFieldGroup ? '_field_group' : '_field';
    options = [getDeleteActionOption(isFieldGroup), getDuplicateActionOption(isFieldGroup)];
    if (!isFieldGroup) {
      options.push({
        class: 'frm_select',
        icon: 'frm_settings_icon',
        label: __('Field Settings', 'formidable')
      });
    }
    options.forEach(function (option) {
      var li, anchor, span;
      li = document.createElement('div');
      li.classList.add('frm_more_options_li', 'dropdown-item');
      anchor = document.createElement('a');
      anchor.classList.add(option.class + classSuffix);
      anchor.setAttribute('href', '#');
      makeTabbable(anchor);
      span = document.createElement('span');
      span.textContent = option.label;
      anchor.innerHTML = '<svg class="frmsvg"><use href="#' + option.icon + '"></use></svg>';
      anchor.appendChild(document.createTextNode(' '));
      anchor.appendChild(span);
      li.appendChild(anchor);
      ul.appendChild(li);
    });
  }
  function getDeleteActionOption(isFieldGroup) {
    var option = {
      class: 'frm_delete',
      icon: 'frm_delete_icon'
    };
    option.label = isFieldGroup ? __('Delete Group', 'formidable') : __('Delete', 'formidable');
    return option;
  }
  function getDuplicateActionOption(isFieldGroup) {
    var option = {
      class: 'frm_clone',
      icon: 'frm_clone_icon'
    };
    option.label = isFieldGroup ? __('Duplicate Group', 'formidable') : __('Duplicate', 'formidable');
    return option;
  }
  function wrapFieldLi(field) {
    var wrapper = div();
    if ('string' === typeof field) {
      wrapper.innerHTML = field;
    } else {
      wrapper.appendChild(field);
    }
    var result = jQuery();
    Array.from(wrapper.children).forEach(function (li) {
      result = result.add(jQuery('<li>').addClass('frm_field_box').html(jQuery('<ul>').addClass('frm_grid_container frm_sorting').append(li)));
    });
    return result;
  }
  function wrapFieldLiInPlace(li) {
    var ul = tag('ul', {
      className: 'frm_grid_container frm_sorting'
    });
    var wrapper = tag('li', {
      className: 'frm_field_box',
      child: ul
    });
    li.replaceWith(wrapper);
    ul.appendChild(li);
    makeDroppable(ul);
    makeDraggable(wrapper, '.frm-move');
  }
  function afterAddField(msg, addFocus) {
    var regex = /id="(\S+)"/;
    var match = regex.exec(msg);
    var field = document.getElementById(match[1]);
    var section = '#' + match[1] + '.edit_field_type_divider ul.frm_sorting.start_divider';
    var $thisSection = jQuery(section);
    var type = field.getAttribute('data-type');
    checkHtmlForNewFields(msg);
    var toggled = false;
    fieldUpdated();
    setupSortable(section);
    if ('quantity' === type) {
      // try to automatically attach a product field
      maybeSetProductField(field);
    }
    if ('product' === type || 'quantity' === type) {
      // quantity too needs to be a part of the if stmt especially cos of the very
      // 1st quantity field (or even if it's just one quantity field in the form).
      maybeHideQuantityProductFieldOption();
    }
    if ($thisSection.length) {
      $thisSection.parent('.frm_field_box').children('.frm_no_section_fields').addClass('frm_block');
    } else {
      var $parentSection = jQuery(field).closest('ul.frm_sorting.start_divider');
      if ($parentSection.length) {
        toggleOneSectionHolder($parentSection);
        toggled = true;
      }
    }
    if (msg.indexOf('frm-collapse-page') !== -1) {
      renumberPageBreaks();
    }
    addClass(field, 'frm-newly-added');
    setTimeout(function () {
      field.classList.remove('frm-newly-added');
    }, 1000);
    var lastRowOrderInput = field.querySelector('#frm-last-row-fields-order');
    if (lastRowOrderInput) {
      updateLastRowFieldsOrder(JSON.parse(lastRowOrderInput.value));
    }
    if (addFocus) {
      var bounding = field.getBoundingClientRect(),
        container = document.getElementById('post-body-content'),
        inView = bounding.top >= 0 && bounding.left >= 0 && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight);
      if (!inView) {
        container.scroll({
          top: container.scrollHeight,
          left: 0,
          behavior: 'smooth'
        });
      }
      if (toggled === false) {
        toggleOneSectionHolder($thisSection);
      }
    }
    deselectFields();
    initiateMultiselect();
    document.getElementById('frm-show-fields').classList.remove('frm-over-droppable');

    // Bootstrap 5 uses data-bs-toggle instead of data-toggle, and requires that elements have the dropdown-menu class.
    field.querySelectorAll('[data-toggle]').forEach(function (toggle) {
      return toggle.setAttribute('data-bs-toggle', toggle.getAttribute('data-toggle'));
    });
    field.querySelectorAll('.frm-dropdown-menu').forEach(function (dropdownMenu) {
      return dropdownMenu.classList.add('dropdown-menu');
    });
    var addedEvent = new Event('frm_added_field', {
      bubbles: false
    });
    addedEvent.frmField = field;
    addedEvent.frmSection = section;
    addedEvent.frmType = type;
    addedEvent.frmToggles = toggled;
    document.dispatchEvent(addedEvent);
  }
  function updateLastRowFieldsOrder(fieldsOrder) {
    if (!fieldsOrder || 'object' !== _typeof(fieldsOrder)) {
      return;
    }
    Object.keys(fieldsOrder).forEach(function (fieldId) {
      var orderInput = document.querySelector('input[name="field_options[field_order_' + fieldId + ']"]');
      if (orderInput) {
        orderInput.value = fieldsOrder[fieldId];
      }
    });
  }

  /**
   * Since multiple new fields may get added when a new field is inserted, check the HTML.
   *
   * @param {string} html
   * @return {void}
   */
  function checkHtmlForNewFields(html) {
    var element = div();
    element.innerHTML = html;
    element.querySelectorAll('.form-field').forEach(addFieldIdToDraftFieldsInput);
  }

  /**
   * @param {HTMLElement} field
   * @return {void}
   */
  function addFieldIdToDraftFieldsInput(field) {
    if (!field.dataset.fid) {
      return;
    }
    var draftInput = document.getElementById('draft_fields');
    if (!draftInput) {
      return;
    }
    if ('' === draftInput.value) {
      draftInput.value = field.dataset.fid;
    } else {
      var split = draftInput.value.split(',');
      if (!split.includes(field.dataset.fid)) {
        draftInput.value += ',' + field.dataset.fid;
      }
    }
  }
  function clearSettingsBox(preventFieldGroups) {
    jQuery('#new_fields .frm-single-settings').addClass('frm_hidden');
    jQuery('#frm-options-panel > .frm-single-settings').removeClass('frm_hidden');
    deselectFields(preventFieldGroups);
  }
  function deselectFields(preventFieldGroups) {
    jQuery('li.ui-state-default.selected').removeClass('selected');
    jQuery('.frm-show-field-settings.selected').removeClass('selected');
    if (!preventFieldGroups) {
      unselectFieldGroups();
    }
  }
  function scrollToField(field) {
    var newPos = field.getBoundingClientRect().top,
      container = document.getElementById('post-body-content');
    if (typeof animate === 'undefined') {
      jQuery(container).scrollTop(newPos);
    } else {
      // TODO: smooth scroll
      jQuery(container).animate({
        scrollTop: newPos
      }, 500);
    }
  }
  function checkCalculationCreatedByUser() {
    var calculation = this.value;
    var warningMessage = checkMatchingParens(calculation);
    warningMessage += checkShortcodes(calculation, this);
    if (warningMessage !== '') {
      infoModal(calculation + '\n\n' + warningMessage);
    }
  }

  /**
   * Checks a string for parens, brackets, and curly braces and returns a message if any unmatched are found.
   *
   * @param  formula
   * @return {string}
   */
  function checkMatchingParens(formula) {
    var stack = [],
      formulaArray = formula.split(''),
      length = formulaArray.length,
      opening = ['{', '[', '('],
      closing = {
        '}': '{',
        ')': '(',
        ']': '['
      },
      unmatchedClosing = [],
      msg = '',
      i,
      top;
    for (i = 0; i < length; i++) {
      if (opening.includes(formulaArray[i])) {
        stack.push(formulaArray[i]);
        continue;
      }
      if (closing.hasOwnProperty(formulaArray[i])) {
        top = stack.pop();
        if (top !== closing[formulaArray[i]]) {
          unmatchedClosing.push(formulaArray[i]);
        }
      }
    }
    if (stack.length > 0 || unmatchedClosing.length > 0) {
      msg = frmAdminJs.unmatched_parens + '\n\n';
      return msg;
    }
    return '';
  }

  /**
   * Checks a calculation for shortcodes that shouldn't be in it and returns a message if found.
   *
   * @param  calculation
   * @param  inputElement
   * @return {string}
   */
  function checkShortcodes(calculation, inputElement) {
    var msg = checkNonNumericShortcodes(calculation, inputElement);
    msg += checkNonFormShortcodes(calculation);
    return msg;
  }

  /**
   * Checks if a numeric calculation has shortcodes that output non-numeric strings and returns a message if found.
   *
   * @param  calculation
   *
   * @param  inputElement
   * @return {string}
   */
  function checkNonNumericShortcodes(calculation, inputElement) {
    var msg = '';
    if (isTextCalculation(inputElement)) {
      return msg;
    }
    var nonNumericShortcodes = getNonNumericShortcodes();
    if (nonNumericShortcodes.test(calculation)) {
      msg = frmAdminJs.text_shortcodes + '\n\n';
    }
    return msg;
  }

  /**
   * Determines if the calculation input is from a text calculation.
   *
   * @param inputElement
   */
  function isTextCalculation(inputElement) {
    return jQuery(inputElement).siblings('label[for^="calc_type"]').children('input').prop('checked');
  }

  /**
   * Returns a regular expression of shortcodes that can't be used in numeric calculations.
   *
   * @return {RegExp}
   */
  function getNonNumericShortcodes() {
    return /\[(date|time|email|ip)\]/;
  }

  /**
   * Checks if a string has any shortcodes that do not belong in forms and returns a message if any are found.
   *
   * @param  formula
   * @return {string}
   */
  function checkNonFormShortcodes(formula) {
    var nonFormShortcodes = getNonFormShortcodes(),
      msg = '';
    if (nonFormShortcodes.test(formula)) {
      msg += frmAdminJs.view_shortcodes + '\n\n';
    }
    return msg;
  }

  /**
   * Returns a regular expression of shortcodes that can't be used in forms but can be used in Views, Email
   * Notifications, and other Formidable areas.
   *
   * @return {RegExp}
   */
  function getNonFormShortcodes() {
    return /\[id\]|\[key\]|\[if\s\w+\]|\[foreach\s\w+\]|\[created-at(\s*)?/g;
  }
  function isCalcBoxType(box, listClass) {
    var list = jQuery(box).find('.frm_code_list');
    return 1 === list.length && list.hasClass(listClass);
  }
  function extractExcludedOptions(exclude) {
    var opts = [];
    if (!Array.isArray(exclude)) {
      return opts;
    }
    for (var _i4 = 0; _i4 < exclude.length; _i4++) {
      if (exclude[_i4].startsWith('[')) {
        opts.push(exclude[_i4]);
        // remove it
        exclude.splice(_i4, 1);
        // https://love2dev.com/blog/javascript-remove-from-array/#remove-from-array-splice-value
        _i4--;
      }
    }
    return opts;
  }
  function hasExcludedOption(field, excludedOpts) {
    var hasOption = false;
    for (var _i5 = 0; _i5 < excludedOpts.length; _i5++) {
      var inputs = document.getElementsByName(getFieldOptionInputName(excludedOpts[_i5], field.fieldId));
      // 2nd condition checks that there's at least one non-empty value
      if (inputs.length && jQuery(inputs[0]).val()) {
        hasOption = true;
        break;
      }
    }
    return hasOption;
  }
  function getFieldOptionInputName(opt, fieldId) {
    var at = opt.indexOf(']');
    return 'field_options' + opt.substring(0, at) + '_' + fieldId + opt.substring(at);
  }
  function popCalcFields(v, force) {
    var box,
      exclude,
      fields,
      i,
      list,
      p = jQuery(v).closest('.frm-single-settings'),
      calc = p.find('.frm-calc-field');
    if (!force && (!calc.length || calc.val() === '' || calc.is(':hidden'))) {
      return;
    }
    var isSummary = isCalcBoxType(v, 'frm_js_summary_list');
    var fieldId = p.find('input[name="frm_fields_submitted[]"]').val();
    if (force) {
      box = v;
    } else {
      box = document.getElementById('frm-calc-box-' + fieldId);
    }
    exclude = getExcludeArray(box, isSummary);
    var excludedOpts = extractExcludedOptions(exclude);
    fields = getFieldList();
    list = document.getElementById('frm-calc-list-' + fieldId);
    list.innerHTML = '';
    for (i = 0; i < fields.length; i++) {
      if (exclude && exclude.includes(fields[i].fieldType) || excludedOpts.length && hasExcludedOption(fields[i], excludedOpts)) {
        continue;
      }
      var _a = document.createElement('a');
      _a.setAttribute('href', '#');
      _a.setAttribute('data-code', fields[i].fieldId);
      _a.classList.add('frm_insert_code');
      _a.appendChild(span(fields[i].fieldName));
      _a.appendChild(span({
        className: 'frm-text-sm frm-text-grey-500',
        text: '[' + fields[i].fieldId + ']'
      }));
      var li = document.createElement('li');
      li.classList.add('frm-field-list-' + fieldId);
      li.classList.add('frm-field-list-' + fields[i].fieldType);
      li.appendChild(_a);
      list.appendChild(li);
    }
  }
  function getExcludeArray(calcBox, isSummary) {
    var codeList = calcBox.querySelector('.frm_code_list');
    var exclude = JSON.parse(codeList.getAttribute('data-exclude'));
    if (isSummary) {
      // includedExtras are those that are normally excluded from the summary but the form owner can choose to include,
      // when they have been chosen to be included, then they can now be manually excluded in the calc box.
      var includedExtras = getIncludedExtras();
      if (includedExtras.length) {
        for (var _i6 = 0; _i6 < exclude.length; _i6++) {
          if (includedExtras.includes(exclude[_i6])) {
            // remove it
            exclude.splice(_i6, 1);
            // https://love2dev.com/blog/javascript-remove-from-array/#remove-from-array-splice-value
            _i6--;
          }
        }
      }
    }
    return exclude;
  }
  function getIncludedExtras() {
    var checked = [];
    var checkboxes = document.getElementsByClassName('frm_include_extras_field');
    for (var _i7 = 0; _i7 < checkboxes.length; _i7++) {
      if (checkboxes[_i7].checked) {
        checked.push(checkboxes[_i7].value);
      }
    }
    return checked;
  }
  function rePopCalcFieldsForSummary() {
    popCalcFields(jQuery('.frm-inline-modal.postbox:has(.frm_js_summary_list)')[0], true);
  }
  function getFieldList(fieldType) {
    var i,
      fields = [],
      allFields = document.querySelectorAll('li.frm_field_box'),
      checkType = 'undefined' !== typeof fieldType;
    for (i = 0; i < allFields.length; i++) {
      // data-ftype is better (than data-type) cos of fields loaded by AJAX - which might not be ready yet
      if (checkType && allFields[i].getAttribute('data-ftype') !== fieldType) {
        continue;
      }
      var fieldId = allFields[i].getAttribute('data-fid');
      if (typeof fieldId !== 'undefined' && fieldId) {
        fields.push({
          fieldId: fieldId,
          fieldName: getPossibleValue('frm_name_' + fieldId),
          fieldType: getPossibleValue('field_options_type_' + fieldId),
          fieldKey: getPossibleValue('field_options_field_key_' + fieldId)
        });
      }
    }
    return wp.hooks.applyFilters('frm_admin_get_field_list', fields, fieldType, allFields);
  }
  function popProductFields(field) {
    var i,
      checked,
      id,
      options = [],
      current = getCurrentProductFields(field),
      fName = field.getAttribute('data-frmfname'),
      products = getFieldList('product'),
      quantities = getFieldList('quantity'),
      isSelect = field.tagName === 'SELECT',
      // for reverse compatibility.
      // whether we have just 1 product and 1 quantity field & should therefore attach the latter to the former
      auto = 1 === quantities.length && 1 === products.length;
    if (isSelect) {
      // This fallback can be removed after 4.05.
      current = field.getAttribute('data-frmcurrent');
    }
    for (i = 0; i < products.length; i++) {
      // let's be double sure it's string, else indexOf will fail
      id = products[i].fieldId.toString();
      checked = auto || -1 !== current.indexOf(id);
      if (isSelect) {
        // This fallback can be removed after 4.05.
        checked = checked ? ' selected' : '';
        options.push('<option value="' + id + '"' + checked + '>' + products[i].fieldName + '</option>');
      } else {
        checked = checked ? ' checked' : '';
        options.push('<label class="frm6">');
        options.push('<input type="checkbox" name="' + fName + '" value="' + id + '"' + checked + '> ' + products[i].fieldName);
        options.push('</label>');
      }
    }
    field.innerHTML = options.join('');
  }
  function getCurrentProductFields(prodFieldOpt) {
    var products = prodFieldOpt.querySelectorAll('[type="checkbox"]:checked'),
      idsArray = [];
    for (var _i8 = 0; _i8 < products.length; _i8++) {
      idsArray.push(products[_i8].value);
    }
    return idsArray;
  }
  function popAllProductFields() {
    var opts = document.querySelectorAll('.frmjs_prod_field_opt');
    for (var _i9 = 0; _i9 < opts.length; _i9++) {
      popProductFields(opts[_i9]);
    }
  }
  function maybeSetProductField(field) {
    var fieldId = field.getAttribute('data-fid'),
      productFieldOpt = document.getElementById('field_options[product_field_' + fieldId + ']');
    if (null === productFieldOpt) {
      return;
    }
    popProductFields(productFieldOpt);
    // in order to move its settings to that LHS panel where
    // the update form resides, else it'll lose this setting
    moveFieldSettings(document.getElementById('frm-single-settings-' + fieldId));
  }

  /**
   * If the element doesn't exist, use a blank value.
   */
  function getPossibleValue(id) {
    var field = document.getElementById(id);
    if (field !== null) {
      return field.value;
    }
    return '';
  }
  function liveChanges() {
    /*jshint validthis:true */
    var option,
      newValue = this.value,
      changes = document.getElementById(this.getAttribute('data-changeme')),
      att = this.getAttribute('data-changeatt');
    if (changes === null) {
      return;
    }
    if (att !== null) {
      if (changes.tagName === 'SELECT' && att === 'placeholder') {
        option = changes.options[0];
        if (option.value === '') {
          option.innerHTML = newValue;
        } else {
          // Create a placeholder option if there are no blank values.
          addBlankSelectOption(changes, newValue);
        }
      } else if (att === 'class') {
        changeFieldClass(changes, this);
      } else if (isSliderField(changes)) {
        updateSliderFieldPreview(changes, att, newValue);
      } else {
        changes.setAttribute(att, newValue);
      }
    } else if (changes.id.indexOf('setup-message') === 0) {
      if (newValue !== '') {
        changes.innerHTML = '<input type="text" value="" disabled />';
      }
    } else {
      changes.innerHTML = purifyHtml(newValue);
      if ('TEXTAREA' === changes.nodeName && changes.classList.contains('wp-editor-area')) {
        // Trigger change events on wysiwyg textareas so we can also sync default values in the visual tab.
        jQuery(changes).trigger('change');
      }
      if (changes.classList.contains('frm_primary_label') && 'break' === changes.nextElementSibling.getAttribute('data-ftype')) {
        changes.nextElementSibling.querySelector('.frm_button_submit').textContent = newValue;
      }
    }
  }
  function updateSliderFieldPreview(field, att, newValue) {
    if (frmGlobal.proIncludesSliderJs) {
      var hookName = 'frm_update_slider_field_preview';
      var hookArgs = {
        field: field,
        att: att,
        newValue: newValue
      };
      wp.hooks.doAction(hookName, hookArgs);
      return;
    }

    // This functionality has been moved to pro since v5.4.3. This code should be removed eventually.
    if ('value' === att) {
      if ('' === newValue) {
        newValue = getSliderMidpoint(field);
      }
      field.value = newValue;
    } else {
      field.setAttribute(att, newValue);
    }
    if (-1 === ['value', 'min', 'max'].indexOf(att)) {
      return;
    }
    if (('max' === att || 'min' === att) && '' === getSliderDefaultValueInput(field.id)) {
      field.value = getSliderMidpoint(field);
    }
    field.parentNode.querySelector('.frm_range_value').textContent = field.value;
  }
  function getSliderDefaultValueInput(previewInputId) {
    return document.querySelector('input[data-changeme="' + previewInputId + '"][data-changeatt="value"]').value;
  }
  function getSliderMidpoint(sliderInput) {
    var max = parseFloat(sliderInput.getAttribute('max'));
    var min = parseFloat(sliderInput.getAttribute('min'));
    return (max - min) / 2 + min;
  }
  function isSliderField(previewInput) {
    return 'range' === previewInput.type && previewInput.parentNode.classList.contains('frm_range_container');
  }
  function toggleInvalidMsg() {
    /*jshint validthis:true */
    var typeDropdown,
      fieldType,
      fieldId = this.getAttribute('data-fid'),
      value = '';
    ['field_options_max_', 'frm_format_'].forEach(function (id) {
      var input = document.getElementById(id + fieldId);
      if (!input) {
        return;
      }
      value += input.value;
    });
    typeDropdown = document.getElementsByName('field_options[type_' + fieldId + ']')[0];
    fieldType = typeDropdown.options[typeDropdown.selectedIndex].value;
    if (fieldType === 'text') {
      toggleValidationBox('' !== value, '.frm_invalid_msg' + fieldId);
    }
  }
  function markRequired() {
    /*jshint validthis:true */
    var thisid = this.id.replace('frm_', ''),
      fieldId = thisid.replace('req_field_', ''),
      checked = this.checked,
      label = jQuery('#field_label_' + fieldId + ' .frm_required');
    toggleValidationBox(checked, '.frm_required_details' + fieldId);
    if (checked) {
      var $reqBox = jQuery('input[name="field_options[required_indicator_' + fieldId + ']"]');
      if ($reqBox.val() === '') {
        $reqBox.val('*');
      }
      label.removeClass('frm_hidden');
    } else {
      label.addClass('frm_hidden');
    }
  }
  function toggleValidationBox(hasValue, messageClass) {
    $msg = jQuery(messageClass);
    if (hasValue) {
      $msg.fadeIn('fast').closest('.frm_validation_msg').fadeIn('fast');
    } else {
      // Fade out validation options
      var $validationBox = $msg.fadeOut('fast').closest('.frm_validation_box');
      var v = $validationBox.css('display', 'block').children(':not(' + messageClass + '):visible').length;
      $validationBox.css('display', '');
      if (v === 0) {
        $msg.closest('.frm_validation_msg').fadeOut('fast');
      }
    }
  }
  function markUnique() {
    /*jshint validthis:true */
    var fieldId = jQuery(this).closest('.frm-single-settings').data('fid');
    var $thisField = jQuery('.frm_unique_details' + fieldId);
    if (this.checked) {
      $thisField.fadeIn('fast').closest('.frm_validation_msg').fadeIn('fast');
      $unqDetail = jQuery('.frm_unique_details' + fieldId + ' input');
      if ($unqDetail.val() === '') {
        $unqDetail.val(frmAdminJs.default_unique);
      }
    } else {
      var $validationBox = $thisField.fadeOut('fast').closest('.frm_validation_box');
      var v = $validationBox.css('display', 'block').children(':not(.frm_unique_details' + fieldId + '):visible').length;
      $validationBox.css('display', '');
      if (v === 0) {
        $thisField.closest('.frm_validation_msg').fadeOut('fast');
      }
    }
  }

  //Fade confirmation field and validation option in or out
  function addConf() {
    /*jshint validthis:true */
    var fieldId = jQuery(this).closest('.frm-single-settings').data('fid');
    var val = jQuery(this).val();
    var $thisField = jQuery(document.getElementById('frm_field_id_' + fieldId));
    toggleValidationBox(val !== '', '.frm_conf_details' + fieldId);
    if (val !== '') {
      //Add default validation message if empty
      var valMsg = jQuery('.frm_validation_box .frm_conf_details' + fieldId + ' input');
      if (valMsg.val() === '') {
        valMsg.val(frmAdminJs.default_conf);
      }
      setConfirmationFieldDescriptions(fieldId);

      //Add or remove class for confirmation field styling
      if (val === 'inline') {
        $thisField.removeClass('frm_conf_below').addClass('frm_conf_inline');
      } else if (val === 'below') {
        $thisField.removeClass('frm_conf_inline').addClass('frm_conf_below');
      }
      jQuery('.frm-conf-box-' + fieldId).removeClass('frm_hidden');
    } else {
      jQuery('.frm-conf-box-' + fieldId).addClass('frm_hidden');
      setTimeout(function () {
        $thisField.removeClass('frm_conf_inline frm_conf_below');
      }, 200);
    }
  }
  function setConfirmationFieldDescriptions(fieldId) {
    var fieldType = document.getElementsByName('field_options[type_' + fieldId + ']')[0].value;
    var fieldDescription = document.getElementById('field_description_' + fieldId);
    var hiddenDescName = 'field_options[description_' + fieldId + ']';
    var newValue = frmAdminJs['enter_' + fieldType];
    maybeSetNewDescription(fieldDescription, hiddenDescName, newValue);
    var confFieldDescription = document.getElementById('conf_field_description_' + fieldId);
    var hiddenConfName = 'field_options[conf_desc_' + fieldId + ']';
    var newConfValue = frmAdminJs['confirm_' + fieldType];
    maybeSetNewDescription(confFieldDescription, hiddenConfName, newConfValue);
  }
  function maybeSetNewDescription(descriptionDiv, hiddenName, newValue) {
    if (descriptionDiv.innerHTML === frmAdminJs.desc) {
      // Set the visible description value and the hidden description value
      descriptionDiv.innerHTML = newValue;
      document.getElementsByName(hiddenName)[0].value = newValue;
    }
  }
  function initBulkOptionsOverlay() {
    /*jshint validthis:true */
    var $info = initModal('#frm-bulk-modal', '700px');
    if ($info === false) {
      return;
    }
    jQuery('.frm-insert-preset').on('click', insertBulkPreset);
    jQuery(builderForm).on('click', 'a.frm-bulk-edit-link', function (event) {
      event.preventDefault();
      var i,
        key,
        label,
        content = '',
        optList,
        opts,
        fieldId = jQuery(this).closest('[data-fid]').data('fid'),
        separate = usingSeparateValues(fieldId),
        product = isProductField(fieldId);
      optList = document.getElementById('frm_field_' + fieldId + '_opts');
      if (!optList) {
        return;
      }
      opts = optList.getElementsByTagName('li');
      document.getElementById('bulk-field-id').value = fieldId;
      for (i = 0; i < opts.length; i++) {
        key = opts[i].getAttribute('data-optkey');
        if (key !== '000') {
          label = document.getElementsByName('field_options[options_' + fieldId + '][' + key + '][label]')[0];
          if (typeof label !== 'undefined') {
            content += label.value;
            if (separate) {
              content += '|' + document.getElementsByName('field_options[options_' + fieldId + '][' + key + '][value]')[0].value;
            }
            if (product) {
              content += '|' + document.getElementsByName('field_options[options_' + fieldId + '][' + key + '][price]')[0].value;
            }
            content += '\r\n';
          }
        }
        if (i >= opts.length - 1) {
          document.getElementById('frm_bulk_options').value = content;
        }
      }
      $info.dialog('open');
      return false;
    });
    jQuery('#frm-update-bulk-opts').on('click', function () {
      var fieldId = document.getElementById('bulk-field-id').value;
      var optionType = document.getElementById('bulk-option-type').value;
      if (optionType) {
        // Use custom handler for custom option type.
        return;
      }
      this.classList.add('frm_loading_button');
      frmAdminBuild.updateOpts(fieldId, document.getElementById('frm_bulk_options').value, $info);
      fieldUpdated();
    });
  }
  function insertBulkPreset(event) {
    /*jshint validthis:true */
    var opts = JSON.parse(this.getAttribute('data-opts'));
    event.preventDefault();
    document.getElementById('frm_bulk_options').value = opts.join('\n');
    return false;
  }

  //Add new option or "Other" option to radio/checkbox/dropdown
  function addFieldOption() {
    /*jshint validthis:true */
    var fieldId = jQuery(this).closest('.frm-single-settings').data('fid'),
      newOption = jQuery('#frm_field_' + fieldId + '_opts .frm_option_template').prop('outerHTML'),
      optType = jQuery(this).data('opttype'),
      optKey = 0,
      oldKey = '000',
      lastKey = getHighestOptKey(fieldId);
    if (lastKey !== oldKey) {
      optKey = lastKey + 1;
    }

    //Update hidden field
    if (optType === 'other') {
      document.getElementById('other_input_' + fieldId).value = 1;

      //Hide "Add Other" option now if this is radio field
      var ftype = jQuery(this).data('ftype');
      if (ftype === 'radio' || ftype === 'select') {
        jQuery(this).fadeOut('slow');
      }
      var _data = {
        action: 'frm_add_field_option',
        field_id: fieldId,
        opt_key: optKey,
        opt_type: optType,
        nonce: frmGlobal.nonce
      };
      jQuery.post(ajaxurl, _data, function (msg) {
        jQuery(document.getElementById('frm_field_' + fieldId + '_opts')).append(msg);
        resetDisplayedOpts(fieldId);
      });
    } else {
      newOption = newOption.replace(new RegExp('optkey="' + oldKey + '"', 'g'), 'optkey="' + optKey + '"');
      newOption = newOption.replace(new RegExp('-' + oldKey + '_', 'g'), '-' + optKey + '_');
      newOption = newOption.replace(new RegExp('-' + oldKey + '"', 'g'), '-' + optKey + '"');
      newOption = newOption.replace(new RegExp('\\[' + oldKey + '\\]', 'g'), '[' + optKey + ']');
      newOption = newOption.replace('frm_hidden frm_option_template', '');
      newOption = {
        newOption: newOption
      };
      addSaveAndDragIconsToOption(fieldId, newOption);
      var $thisOption = this.closest('.frm_single_option');
      if ($thisOption) {
        $thisOption.after(newOption.newOption);
      } else {
        // Backwards compatibility "@since 6.24"
        // Note: Keep it jQuery since some events are attached to the element
        jQuery("#frm_field_".concat(fieldId, "_opts")).append(newOption.newOption);
      }
      resetDisplayedOpts(fieldId);
    }
    fieldOptionEnableAllRemoveButtons(this);
    fieldUpdated();
  }

  /**
   * Enable all remove buttons for field options.
   *
   * @param {HTMLElement} element The add option button element.
   */
  function fieldOptionEnableAllRemoveButtons(element) {
    var _element$closest, _parentEl$querySelect;
    // Make sure all remove buttons are enabled
    var parentEl = element.classList.contains('frm-add-option-legacy') // Backwards compatibility "@since 6.24"
    ? (_element$closest = element.closest('.frm-collapse-me')) === null || _element$closest === void 0 ? void 0 : _element$closest.querySelector('.frm_sortable_field_opts') : element.closest('.frm_sortable_field_opts');
    parentEl === null || parentEl === void 0 || (_parentEl$querySelect = parentEl.querySelectorAll('.frm_remove_tag.frm_disabled')) === null || _parentEl$querySelect === void 0 || _parentEl$querySelect.forEach(function (button) {
      return button.classList.remove('frm_disabled');
    });
  }
  function getHighestOptKey(fieldId) {
    var i = 0,
      optKey = 0,
      opts = jQuery('#frm_field_' + fieldId + '_opts li'),
      lastKey = 0;
    for (i; i < opts.length; i++) {
      optKey = opts[i].getAttribute('data-optkey');
      if (opts.length === 1) {
        return optKey;
      }
      if (optKey !== '000') {
        optKey = optKey.replace('other_', '');
        optKey = parseInt(optKey, 10);
      }
      if (!isNaN(lastKey) && (optKey > lastKey || lastKey === '000')) {
        lastKey = optKey;
      }
    }
    return lastKey;
  }
  function toggleMultSel() {
    /*jshint validthis:true */
    var fieldId = jQuery(this).closest('.frm-single-settings').data('fid');
    toggleMultiSelect(fieldId, this.value);
  }
  function toggleMultiSelect(fieldId, value) {
    var setting = jQuery('.frm_multiple_cont_' + fieldId);
    if (value === 'select') {
      setting.fadeIn('fast');
    } else {
      setting.fadeOut('fast');
    }
  }
  function toggleSepValues() {
    /*jshint validthis:true */
    var fieldId = jQuery(this).closest('.frm-single-settings').data('fid');
    toggle(jQuery('.field_' + fieldId + '_option_key'));
    jQuery('.field_' + fieldId + '_option').toggleClass('frm_with_key');
  }
  function toggleImageOptions() {
    /*jshint validthis:true */
    var hasImageOptions,
      imageSize,
      $field = jQuery(this).closest('.frm-single-settings'),
      fieldId = $field.data('fid'),
      displayField = document.getElementById('frm_field_id_' + fieldId);
    refreshOptionDisplayNow(jQuery(this));
    toggle(jQuery('.field_' + fieldId + '_image_id'));
    toggle(jQuery('.frm_toggle_image_options_' + fieldId));
    toggle(jQuery('.frm_image_size_' + fieldId));
    toggle(jQuery('.frm_alignment_' + fieldId));
    toggle(jQuery('.frm-add-other#frm_add_field_' + fieldId));
    hasImageOptions = imagesAsOptions(fieldId);
    if (hasImageOptions) {
      setAlignment(fieldId, 'inline');
      removeImageSizeClasses(displayField);
      imageSize = getImageOptionSize(fieldId);
      displayField.classList.add('frm_image_options');
      displayField.classList.add('frm_image_size_' + imageSize);
      $field.find('.frm-bulk-edit-link').hide();
    } else {
      displayField.classList.remove('frm_image_options');
      removeImageSizeClasses(displayField);
      setAlignment(fieldId, 'block');
      $field.find('.frm-bulk-edit-link').show();
    }

    /**
     * Fires when image options are toggled for a field.
     *
     * @param {HTMLElement} field           The field element.
     * @param {boolean}     hasImageOptions Whether the field has image options enabled.
     */
    wp.hooks.doAction('frm_image_options_toggled', $field[0], hasImageOptions);
  }
  function removeImageSizeClasses(field) {
    field.classList.remove('frm_image_size_', 'frm_image_size_small', 'frm_image_size_medium', 'frm_image_size_large', 'frm_image_size_xlarge');
  }
  function setAlignment(fieldId, alignment) {
    jQuery('#field_options_align_' + fieldId).val(alignment).trigger('change');
  }
  function setImageSize() {
    var $field = jQuery(this).closest('.frm-single-settings'),
      fieldId = $field.data('fid'),
      displayField = document.getElementById('frm_field_id_' + fieldId);
    refreshOptionDisplay();
    if (imagesAsOptions(fieldId)) {
      removeImageSizeClasses(displayField);
      displayField.classList.add('frm_image_options');
      displayField.classList.add('frm_image_size_' + getImageOptionSize(fieldId));
    }
  }
  function refreshOptionDisplayNow(object) {
    var $field = object.closest('.frm-single-settings'),
      fieldID = $field.data('fid');
    jQuery('.field_' + fieldID + '_option').trigger('change');
  }
  function refreshOptionDisplay() {
    /*jshint validthis:true */
    refreshOptionDisplayNow(jQuery(this));
  }
  function addImageToOption(event) {
    var _wp;
    var imagePreview = event.target.closest('.frm_image_preview_wrapper');
    if (!((_wp = wp) !== null && _wp !== void 0 && _wp.media) || imagePreview !== null && imagePreview !== void 0 && imagePreview.dataset.upgrade) {
      return;
    }
    event.preventDefault();
    wp.media.model.settings.post.id = 0;
    var fileFrame = wp.media.frames.file_frame = wp.media({
      multiple: false,
      library: {
        type: ['image']
      }
    });
    fileFrame.on('select', function () {
      var attachment = fileFrame.state().get('selection').first().toJSON();
      var img = imagePreview.querySelector('img');
      img.setAttribute('src', attachment.url);
      img.classList.remove('frm_hidden');
      img.removeAttribute('srcset'); // Prevent the old image from sticking around.

      imagePreview.querySelector('.frm_image_preview_frame').style.display = 'block';
      imagePreview.querySelector('.frm_image_preview_title').textContent = attachment.filename;
      imagePreview.querySelector('.frm_choose_image_box').style.display = 'none';
      var $imagePreview = jQuery(imagePreview);
      $imagePreview.siblings('input[name*="[label]"]').data('frmimgurl', attachment.url);
      $imagePreview.find('input.frm_image_id').val(attachment.id).trigger('change');
      wp.media.model.settings.post.id = 0;
    });
    fileFrame.open();
  }
  function removeImageFromOption(event) {
    var $this = jQuery(this),
      previewWrapper = $this.closest('.frm_image_preview_wrapper');
    event.preventDefault();
    event.stopPropagation();
    previewWrapper.find('img').attr('src', '');
    previewWrapper.find('.frm_image_preview_frame').hide();
    previewWrapper.find('.frm_choose_image_box').show();
    previewWrapper.find('input.frm_image_id').val(0).trigger('change');
  }
  function toggleMultiselect() {
    /*jshint validthis:true */
    var dropdown = jQuery(this).closest('li').find('.frm_form_fields select');
    if (this.checked) {
      dropdown.attr('multiple', 'multiple');
    } else {
      dropdown.removeAttr('multiple');
    }
  }

  /**
   * Allow typing on form switcher click without an extra click to search.
   */
  function focusSearchBox() {
    var searchBox = document.getElementById('dropform-search-input');
    if (searchBox !== null) {
      setTimeout(function () {
        searchBox.focus();
      }, 100);
    }
  }

  /**
   * Dismiss a warning message and send an AJAX request to update the dismissal state.
   *
   * @since 6.3
   *
   * @param {Event} event The event object associated with the click on the dismiss icon.
   */
  function dismissWarningMessage(event) {
    var target = event.target;
    var warningEl = target.closest('.frm_warning_style');
    jQuery(warningEl).fadeOut(400, function () {
      return warningEl.remove();
    });
    var action = target.dataset.action;
    var formData = new FormData();
    doJsonPost(action, formData);
  }

  /**
   * If a field is clicked in the builder, prevent inputs from changing.
   */
  function stopFieldFocus(e) {
    e.preventDefault();
  }

  /**
   * Delete a field option.
   */
  function deleteFieldOption() {
    var parentLi = this.parentNode;
    var parentUl = parentLi.parentNode;

    // If only 2 visible options, add disabled class to the other delete button
    var visibleOptions = parentUl.querySelectorAll('li:not(.frm_hidden)');
    if (visibleOptions.length === 2) {
      var _Array$from$find$quer;
      (_Array$from$find$quer = Array.from(visibleOptions).find(function (li) {
        return li !== parentLi;
      }).querySelector('.frm_remove_tag')) === null || _Array$from$find$quer === void 0 || _Array$from$find$quer.classList.add('frm_disabled');
    }

    /*jshint validthis:true */
    var otherInput,
      fieldId = this.getAttribute('data-fid');
    jQuery(parentLi).fadeOut('fast', function () {
      wp.hooks.doAction('frm_before_delete_field_option', this);
      jQuery(parentLi).remove();
      var hasOther = jQuery(parentUl).find('.frm_other_option');
      if (hasOther.length < 1) {
        otherInput = document.getElementById('other_input_' + fieldId);
        if (otherInput !== null) {
          otherInput.value = 0;
        }
        jQuery('#other_button_' + fieldId).fadeIn('fast');
      }
    });
    fieldUpdated();
  }

  /**
   * If a radio button is set as default, allow a click to
   * deselect it.
   */
  function maybeUncheckRadio() {
    var $self, uncheck, unbind, up;

    /*jshint validthis:true */
    $self = jQuery(this);
    if ($self.is(':checked')) {
      uncheck = function uncheck() {
        setTimeout(function () {
          $self.prop('checked', false);
        }, 0);
      };
      unbind = function unbind() {
        $self.off('mouseup', up);
      };
      up = function up() {
        uncheck();
        unbind();
      };
      $self.on('mouseup', up);
      $self.one('mouseout', unbind);
    }
  }

  /**
   * If the field option has the default text, clear it out on click.
   */
  function maybeClearOptText() {
    /*jshint validthis:true */
    if (this.value === frmAdminJs.new_option) {
      this.setAttribute('data-value-on-focus', this.value);
      this.value = '';
    }
  }
  function confirmFieldsDeleteMessage(numberOfFields) {
    /* translators: %1$s: Number of fields that are selected to be deleted. */
    return sprintf(__('Are you sure you want to delete these %1$s selected field(s)?', 'formidable'), numberOfFields);
  }
  function clickDeleteField() {
    /*jshint validthis:true */
    var confirmMsg = frmAdminJs.conf_delete,
      maybeDivider = this.parentNode.parentNode.parentNode.parentNode.parentNode,
      li = maybeDivider.parentNode,
      field = jQuery(this).closest('li.form-field'),
      fieldId = field.data('fid');
    if (field.data('ftype') === 'divider') {
      var fieldBoxes = document.querySelectorAll('.frm-field-group-hover-target .start_divider .frm_field_box');
      var fieldIdsToDelete = 0;
      fieldBoxes.forEach(function (fieldBox) {
        var fieldsInsideFieldBox = fieldBox.querySelectorAll('li.form-field');
        if (fieldsInsideFieldBox) {
          fieldIdsToDelete += fieldsInsideFieldBox.length;
        }
      });
      if (fieldIdsToDelete) {
        confirmMsg = confirmFieldsDeleteMessage(++fieldIdsToDelete);
      }
    }
    if (li.classList.contains('frm-section-collapsed') || li.classList.contains('frm-page-collapsed')) {
      return false;
    }

    // If deleting a section, use a special message.
    if (maybeDivider.className === 'divider_section_only') {
      confirmMsg = frmAdminJs.conf_delete_sec;
    }
    this.setAttribute('data-frmverify', confirmMsg);
    this.setAttribute('data-frmverify-btn', 'frm-button-red');
    this.setAttribute('data-deletefield', fieldId);
    closeOpenFieldDropdowns();
    confirmLinkClick(this);
    return false;
  }
  function clickSelectField() {
    this.closest('li.form-field').click();
  }
  function clickDeleteFieldGroup() {
    var hoverTarget, decoy;
    hoverTarget = document.querySelector('.frm-field-group-hover-target');
    if (null === hoverTarget) {
      return;
    }
    hoverTarget.classList.add('frm-selected-field-group');
    decoy = document.createElement('div');
    decoy.classList.add('frm-delete-field-groups', 'frm_hidden');
    document.body.appendChild(decoy);
    decoy.click();
  }
  function duplicateFieldGroup() {
    var hoverTarget = document.querySelector('.frm-field-group-hover-target');
    if (null === hoverTarget) {
      return;
    }
    var newRowId = 'frm_field_group_' + getAutoId();
    var placeholderUlChild = document.createTextNode('');
    wrapFieldLiInPlace(placeholderUlChild);
    var newRow = jQuery(placeholderUlChild).closest('li').get(0);
    newRow.classList.add('frm_hidden');
    var newRowUl = newRow.querySelector('ul');
    newRowUl.id = newRowId;
    jQuery(hoverTarget.closest('li.frm_field_box')).after(newRow);
    var $fields = getFieldsInRow(jQuery(hoverTarget));
    var syncDetails = [];
    var injectedCloneOptions = [];
    var expectedLength = $fields.length;
    var originalFieldIdByDuplicatedFieldId = {};
    var duplicatedCount = 0;
    jQuery(newRow).on('frm_added_duplicated_field_to_row', function (_, args) {
      originalFieldIdByDuplicatedFieldId[jQuery(args.duplicatedFieldHtml).attr('data-fid')] = args.originalFieldId;
      if (expectedLength > ++duplicatedCount) {
        return;
      }
      var $newRowUl = jQuery(newRowUl);
      var $duplicatedFields = getFieldsInRow($newRowUl);
      injectedCloneOptions.forEach(function (cloneOption) {
        cloneOption.remove();
      });
      for (var index = 0; index < expectedLength; ++index) {
        $newRowUl.append($newRowUl.children('li.form-field[frm-field-order="' + index + '"]'));
      }
      syncLayoutClasses($duplicatedFields.first(), syncDetails);
      newRow.classList.remove('frm_hidden');
      updateFieldOrder();
      getFieldsInRow($newRowUl).each(function () {
        maybeDuplicateUnsavedSettings(originalFieldIdByDuplicatedFieldId[this.getAttribute('data-fid')], jQuery(this).prop('outerHTML'));
      });
    });
    $fields.each(function (index) {
      var cloneOption;
      cloneOption = document.createElement('li');
      cloneOption.classList.add('frm_clone_field');
      cloneOption.setAttribute('frm-target-row-id', newRowId);
      cloneOption.setAttribute('frm-field-order', index);
      this.appendChild(cloneOption);
      cloneOption.click();
      injectedCloneOptions.push(cloneOption);
      syncDetails.push(getSizeOfLayoutClass(getLayoutClassName(this.classList)));
    });
  }
  function clickFieldGroupLayout() {
    var hoverTarget, sizeOfFieldGroup, popupWrapper;
    hoverTarget = document.querySelector('.frm-field-group-hover-target');
    if (null === hoverTarget) {
      return;
    }
    deselectFields();
    sizeOfFieldGroup = getSizeOfFieldGroupFromChildElement(hoverTarget.querySelector('li.form-field'));
    hoverTarget.classList.add('frm-has-open-field-group-popup');
    jQuery(document).on('click', '#frm_builder_page', destroyFieldGroupPopupOnOutsideClick);
    popupWrapper = div();
    popupWrapper.style.position = 'relative';
    popupWrapper.appendChild(getFieldGroupPopup(sizeOfFieldGroup, this));
    this.parentNode.appendChild(popupWrapper);
    var firstLayoutOption = popupWrapper.querySelector('.frm-row-layout-option');
    if (firstLayoutOption) {
      firstLayoutOption.focus();
    }
  }
  function destroyFieldGroupPopupOnOutsideClick(event) {
    if (event.target.classList.contains('frm-custom-field-group-layout') || event.target.classList.contains('frm-cancel-custom-field-group-layout')) {
      return;
    }
    if (!jQuery(event.target).closest('#frm_field_group_controls').length && !jQuery(event.target).closest('#frm_field_group_popup').length) {
      destroyFieldGroupPopup();
    }
  }
  function getSizeOfFieldGroupFromChildElement(element) {
    var $ul = jQuery(element).closest('ul');
    if ($ul.length) {
      return getFieldsInRow($ul).length;
    }
    return getSelectedFieldCount();
  }
  function getFieldGroupPopup(sizeOfFieldGroup, childElement) {
    var popup, wrapper, rowLayoutOptions, ul;
    popup = document.getElementById('frm_field_group_popup');
    if (null === popup) {
      popup = div();
    } else {
      popup.innerHTML = '';
    }
    popup.id = 'frm_field_group_popup';
    wrapper = div();
    wrapper.style.padding = '0 24px 12px';
    wrapper.appendChild(getRowLayoutTitle());
    rowLayoutOptions = getRowLayoutOptions(sizeOfFieldGroup);
    ul = childElement.closest('ul.frm_sorting');
    if (null !== ul) {
      maybeMarkRowLayoutAsActive(ul, rowLayoutOptions);
    }
    wrapper.appendChild(rowLayoutOptions);
    popup.appendChild(wrapper);
    popup.appendChild(separator());
    if (sizeOfFieldGroup <= 6) {
      popup.appendChild(getCustomLayoutOption());
    }
    popup.appendChild(getBreakIntoDifferentRowsOption());
    return popup;
  }
  function maybeMarkRowLayoutAsActive(activeRow, options) {
    var length, index, currentRow;
    length = options.children.length;
    for (index = 0; index < length; ++index) {
      currentRow = options.children[index];
      if (rowLayoutsMatch(currentRow, activeRow)) {
        currentRow.classList.add('frm-active-row-layout');
        return;
      }
    }
  }
  function separator() {
    return document.createElement('hr');
  }
  function getCustomLayoutOption() {
    var option = div();
    option.textContent = __('Custom layout', 'formidable');
    jQuery(option).prepend(getIconClone('frm_gear_svg'));
    option.classList.add('frm-custom-field-group-layout');
    makeTabbable(option);
    return option;
  }
  function makeTabbable(element, ariaLabel) {
    element.setAttribute('tabindex', 0);
    element.setAttribute('role', 'button');
    if ('undefined' !== typeof ariaLabel) {
      element.setAttribute('aria-label', ariaLabel);
    }
  }
  function getIconClone(iconId) {
    var clone = document.getElementById(iconId).cloneNode(true);
    clone.id = '';
    return clone;
  }
  function getBreakIntoDifferentRowsOption() {
    var option = div();
    option.textContent = __('Break into rows', 'formidable');
    jQuery(option).prepend(getIconClone('frm_break_field_group_svg'));
    option.classList.add('frm-break-field-group');
    makeTabbable(option);
    return option;
  }
  function getRowLayoutTitle() {
    var rowLayoutTitle = div();
    rowLayoutTitle.classList.add('frm-row-layout-title');
    rowLayoutTitle.textContent = __('Row Layout', 'formidable');
    return rowLayoutTitle;
  }
  function getRowLayoutOptions(size) {
    var wrapper, padding;
    wrapper = getEmptyGridContainer();
    if (size > 6) {
      wrapper.appendChild(getRowLayoutOption(size, 'even'));
      return wrapper;
    }
    if (5 !== size) {
      wrapper.appendChild(getRowLayoutOption(size, 'even'));
    }
    if (size % 2 === 1) {
      // only include the middle option for odd numbers because even doesn't make a lot of sense.
      wrapper.appendChild(getRowLayoutOption(size, 'middle'));
    }
    if (size < 6) {
      wrapper.appendChild(getRowLayoutOption(size, 'left'));
      wrapper.appendChild(getRowLayoutOption(size, 'right'));
    } else {
      padding = div();
      padding.classList.add('frm_fourth');
      wrapper.prepend(padding);
    }
    return wrapper;
  }
  function getRowLayoutOption(size, type) {
    var option, useClass;
    option = div();
    option.classList.add('frm-row-layout-option');
    makeTabbable(option, type);
    switch (size) {
      case 6:
        useClass = 'frm_half';
        break;
      case 5:
        useClass = 'frm_third';
        break;
      default:
        if (size > 6) {
          // We only show a single option at 6-12, so we use the full width.
          useClass = 'frm_full';
        } else {
          useClass = size % 2 === 1 ? 'frm_fourth' : 'frm_third';
        }
        break;
    }
    option.classList.add(useClass);
    option.setAttribute('layout-type', type);
    option.appendChild(getRowForSizeAndType(size, type));
    return option;
  }
  function rowLayoutsMatch(row1, row2) {
    return getRowLayoutAsKey(row1) === getRowLayoutAsKey(row2);
  }
  function getRowLayoutAsKey(row) {
    var $fields, sizes;
    if (row.classList.contains('frm-row-layout-option')) {
      $fields = jQuery(row).find('.frm_grid_container').children();
    } else {
      $fields = getFieldsInRow(jQuery(row));
    }
    sizes = [];
    $fields.each(function () {
      sizes.push(getSizeOfLayoutClass(getLayoutClassName(this.classList)));
    });
    return sizes.join('-');
  }
  function getRowForSizeAndType(size, type) {
    var row, index, block;
    row = getEmptyGridContainer();
    for (index = 0; index < size; ++index) {
      block = div();
      block.classList.add(getClassForBlock(size, type, index));
      block.style.height = '16px';
      block.style.background = '#9EA9B8';
      block.style.borderRadius = '1px';
      row.appendChild(block);
    }
    return row;
  }

  /**
   * @param {number} size  2-12.
   * @param {string} type  even, middle, left, or right.
   * @param {number} index 0-5.
   * @return {string} The class name.
   */
  function getClassForBlock(size, type, index) {
    if ('even' === type) {
      return getEvenClassForSize(size, index);
    } else if ('middle' === type) {
      if (3 === size) {
        return 1 === index ? 'frm6' : 'frm3';
      }
      if (5 === size) {
        return 2 === index ? 'frm4' : 'frm2';
      }
    } else if ('left' === type) {
      return 0 === index ? getLargeClassForSize(size) : getSmallClassForSize(size);
    } else if ('right' === type) {
      return index === size - 1 ? getLargeClassForSize(size) : getSmallClassForSize(size);
    }
    return 'frm12';
  }

  /**
   * @param {number}           size  2-12.
   * @param {number|undefined} index 0-5.
   * @return {string} The class name.
   */
  function getEvenClassForSize(size, index) {
    if (size > 6) {
      return 'frm1';
    }
    if (-1 !== [2, 3, 4, 6].indexOf(size)) {
      return getLayoutClassForSize(12 / size);
    }
    if (5 === size && 'undefined' !== typeof index) {
      return 0 === index ? 'frm4' : 'frm2';
    }
    return 'frm12';
  }
  function getSmallClassForSize(size) {
    switch (size) {
      case 2:
      case 3:
        return 'frm3';
      case 4:
        return 'frm2';
      case 5:
        return 'frm2';
      case 6:
        return 'frm1';
    }
    return 'frm12';
  }
  function getLargeClassForSize(size) {
    switch (size) {
      case 2:
        return 'frm9';
      case 3:
      case 4:
        return 'frm6';
      case 5:
        return 'frm4';
      case 6:
        return 'frm7';
    }
    return 'frm12';
  }
  function getEmptyGridContainer() {
    var wrapper = div();
    wrapper.classList.add('frm_grid_container');
    return wrapper;
  }

  /**
   * Handle when a field group layout option (that sets grid classes/column sizing) is selected in the "Row Layout" popup.
   *
   * @return {void}
   */
  function handleFieldGroupLayoutOptionClick() {
    var row = document.querySelector('.frm-field-group-hover-target');
    if (!row) {
      // The field group layout options also get clicked when merging multiple rows.
      // The following code isn't required for multiple rows though so just exit early.
      return;
    }
    var type = this.getAttribute('layout-type');
    syncLayoutClasses(getFieldsInRow(jQuery(row)).first(), type);
    destroyFieldGroupPopup();
  }
  function handleFieldGroupLayoutOptionInsideMergeClick() {
    var $ul, type;
    $ul = mergeSelectedFieldGroups();
    type = this.getAttribute('layout-type');
    syncLayoutClasses(getFieldsInRow($ul).first(), type);
    unselectFieldGroups();
  }
  function mergeSelectedFieldGroups() {
    var $selectedFieldGroups = jQuery('.frm-selected-field-group'),
      $firstGroupUl = $selectedFieldGroups.first();
    $selectedFieldGroups.not($firstGroupUl).each(function () {
      getFieldsInRow(jQuery(this)).each(function () {
        var previousParent = this.parentNode;
        getFieldsInRow($firstGroupUl).last().after(this);
        if (!jQuery(previousParent).children('li.form-field').length) {
          // clean up the previous field group if we've removed all of its fields.
          previousParent.closest('li.frm_field_box').remove();
        }
      });
    });
    updateFieldOrder();
    syncLayoutClasses(getFieldsInRow($firstGroupUl).first());
    return $firstGroupUl;
  }
  function customFieldGroupLayoutClick() {
    var $fields;
    if (null !== this.closest('.frm-merge-fields-into-row')) {
      return;
    }
    $fields = getFieldsInRow(jQuery('.frm-field-group-hover-target'));
    setupCustomLayoutOptions($fields);
  }
  function setupCustomLayoutOptions($fields) {
    var size, popup, wrapper, layoutClass, inputRow, paddingElement, inputValueOverride, index, inputField, heading, label, buttonsWrapper, cancelButton, saveButton;
    size = $fields.length;
    popup = document.getElementById('frm_field_group_popup');
    popup.innerHTML = '';
    wrapper = div();
    wrapper.style.padding = '0 24px';
    layoutClass = getEvenClassForSize(5 === size ? 6 : size);
    inputRow = div();
    inputRow.style.padding = '20px 0';
    inputRow.classList.add('frm_grid_container');
    if (5 === size) {
      // add a span to pad the inputs by 1 column, to account for the missing 2 columns.
      paddingElement = document.createElement('span');
      paddingElement.classList.add('frm1');
      inputRow.appendChild(paddingElement);
    }
    inputValueOverride = getSelectedFieldCount() > 0 ? getSizeOfLayoutClass(getEvenClassForSize(size)) : false;
    if (false !== inputValueOverride && inputValueOverride >= 12) {
      inputValueOverride = Math.floor(12 / size);
    }
    for (index = 0; index < size; ++index) {
      inputField = document.createElement('input');
      inputField.type = 'text';
      inputField.classList.add(layoutClass);
      inputField.classList.add('frm-custom-grid-size-input');
      inputField.value = false !== inputValueOverride ? inputValueOverride : getSizeOfLayoutClass(getLayoutClassName($fields.get(index).classList));
      inputRow.appendChild(inputField);
    }
    heading = div();
    heading.classList.add('frm-builder-popup-heading');
    heading.textContent = __('Enter number of columns for each field', 'formidable');
    label = div();
    label.classList.add('frm-builder-popup-subheading');
    label.textContent = __('Layouts are based on a 12-column grid system', 'formidable');
    wrapper.appendChild(heading);
    wrapper.appendChild(label);
    wrapper.appendChild(inputRow);
    buttonsWrapper = div();
    buttonsWrapper.style.textAlign = 'right';
    cancelButton = getSecondaryButton();
    cancelButton.textContent = __('Cancel', 'formidable');
    cancelButton.classList.add('frm-cancel-custom-field-group-layout');
    cancelButton.style.marginRight = '10px';
    saveButton = getPrimaryButton();
    saveButton.textContent = __('Save', 'formidable');
    saveButton.classList.add('frm-save-custom-field-group-layout');
    buttonsWrapper.appendChild(cancelButton);
    buttonsWrapper.appendChild(saveButton);
    wrapper.appendChild(buttonsWrapper);
    popup.appendChild(wrapper);
    setTimeout(function () {
      var firstInput = popup.querySelector('input.frm-custom-grid-size-input').focus();
      if (firstInput) {
        firstInput.focus();
      }
    }, 0);
  }
  function customFieldGroupLayoutInsideMergeClick() {
    $fields = jQuery('.frm-selected-field-group li.form-field');
    setupCustomLayoutOptions($fields);
  }
  function getPrimaryButton() {
    var button = getButton();
    button.classList.add('button-primary', 'frm-button-primary');
    return button;
  }
  function getSecondaryButton() {
    var button = getButton();
    button.classList.add('button-secondary', 'frm-button-secondary');
    return button;
  }
  function getButton() {
    var button = document.createElement('a');
    button.setAttribute('href', '#');
    button.classList.add('button');
    button.style.textDecoration = 'none';
    return button;
  }
  function getSizeOfLayoutClass(className) {
    switch (className) {
      case 'frm_half':
        return 6;
      case 'frm_third':
        return 4;
      case 'frm_two_thirds':
        return 8;
      case 'frm_fourth':
        return 3;
      case 'frm_three_fourths':
        return 9;
      case 'frm_sixth':
        return 2;
    }
    if (0 === className.indexOf('frm')) {
      return parseInt(className.substr(3));
    }

    // Anything missing a layout class should be a full width row.
    return 12;
  }
  function getLayoutClassName(classList) {
    var classes, index, currentClass;
    classes = getLayoutClasses();
    for (index = 0; index < classes.length; ++index) {
      currentClass = classes[index];
      if (classList.contains(currentClass)) {
        return currentClass;
      }
    }
    return '';
  }
  function getLayoutClassForSize(size) {
    return 'frm' + size;
  }
  function breakFieldGroupClick() {
    var row = document.querySelector('.frm-field-group-hover-target');
    breakRow(row);
    destroyFieldGroupPopup();
  }
  function breakRow(row) {
    var $row = jQuery(row);
    getFieldsInRow($row).each(function (index) {
      var field = this;
      if (0 !== index) {
        $row.parent().after(wrapFieldLi(field));
      }
      stripLayoutFromFields(jQuery(field));
    });
  }
  function stripLayoutFromFields(field) {
    syncLayoutClasses(field, 'clear');
  }
  function focusFieldGroupInputOnClick() {
    this.select();
  }
  function cancelCustomFieldGroupClick() {
    revertToFieldGroupPopupFirstPage(this);
  }
  function revertToFieldGroupPopupFirstPage(triggerElement) {
    jQuery(document.getElementById('frm_field_group_popup')).replaceWith(getFieldGroupPopup(getSizeOfFieldGroupFromChildElement(triggerElement), triggerElement));
  }
  function destroyFieldGroupPopup() {
    var popup, wrapper;
    popup = document.getElementById('frm_field_group_popup');
    if (popup === null) {
      return;
    }
    wrapper = document.querySelector('.frm-has-open-field-group-popup');
    if (null !== wrapper) {
      wrapper.classList.remove('frm-has-open-field-group-popup');
      popup.parentNode.remove();
    }
    jQuery(document).off('click', '#frm_builder_page', destroyFieldGroupPopupOnOutsideClick);
  }
  function saveCustomFieldGroupClick() {
    var syncDetails, $controls, $ul;
    syncDetails = [];
    jQuery(document.getElementById('frm_field_group_popup').querySelectorAll('.frm_grid_container input')).each(function () {
      syncDetails.push(parseInt(this.value));
    });
    $controls = jQuery(document.getElementById('frm_field_group_controls'));
    if ($controls.length && 'none' !== $controls.get(0).style.display) {
      syncLayoutClasses(getFieldsInRow(jQuery(document.querySelector('.frm-field-group-hover-target'))).first(), syncDetails);
    } else {
      $ul = mergeSelectedFieldGroups();
      syncLayoutClasses(getFieldsInRow($ul).first(), syncDetails);
      unselectFieldGroups();
    }
    destroyFieldGroupPopup();
  }
  function fieldGroupClick(e) {
    maybeShowFieldGroupMessage();
    if ('ul' !== e.originalEvent.target.nodeName.toLowerCase()) {
      // only continue if the group itself was clicked / ignore when a field is clicked.
      return;
    }
    var hoverTarget = document.querySelector('.frm-field-group-hover-target');
    if (!hoverTarget) {
      return;
    }
    var ctrlOrCmdKeyIsDown = e.ctrlKey || e.metaKey;
    var shiftKeyIsDown = e.shiftKey;
    var groupIsActive = hoverTarget.classList.contains('frm-selected-field-group');
    var $selectedFieldGroups = getSelectedFieldGroups();
    var numberOfSelectedGroups = $selectedFieldGroups.length;
    if (ctrlOrCmdKeyIsDown || shiftKeyIsDown) {
      // multi-selecting

      var selectedField = getSelectedField();
      if (null !== selectedField && !jQuery(selectedField).siblings('li.form-field').length) {
        // count a selected field on its own as a selected field group when multiselecting.
        selectedField.parentNode.classList.add('frm-selected-field-group');
        ++numberOfSelectedGroups;
      }
      if (ctrlOrCmdKeyIsDown) {
        if (groupIsActive) {
          // unselect if holding ctrl or cmd and the group was already active.
          --numberOfSelectedGroups;
          hoverTarget.classList.remove('frm-selected-field-group');
          syncAfterMultiSelect(numberOfSelectedGroups);
          return; // exit early to avoid adding back frm-selected-field-group
        }
        ++numberOfSelectedGroups;
      } else if (shiftKeyIsDown && !groupIsActive) {
        ++numberOfSelectedGroups; // include the one we're selecting right now.
        var $firstGroup = $selectedFieldGroups.first();
        var $range;
        if ($firstGroup.parent().index() < jQuery(hoverTarget.parentNode).index()) {
          $range = $firstGroup.parent().nextUntil(hoverTarget.parentNode);
        } else {
          $range = $firstGroup.parent().prevUntil(hoverTarget.parentNode);
        }
        $range.each(function () {
          var $fieldGroup = jQuery(this).closest('li').find('ul.frm_sorting');
          if (!$fieldGroup.hasClass('frm-selected-field-group')) {
            ++numberOfSelectedGroups;
            $fieldGroup.addClass('frm-selected-field-group');
          }
        });
      }
    } else {
      // not multi-selecting
      unselectFieldGroups();
      numberOfSelectedGroups = 1;
    }
    hoverTarget.classList.add('frm-selected-field-group');
    syncAfterMultiSelect(numberOfSelectedGroups);
    maybeHideFieldGroupMessage();
    jQuery(document).off('click', unselectFieldGroups);
    jQuery(document).on('click', unselectFieldGroups);
  }

  /**
   * Hide the field group message by manipulating classes.
   *
   * @param {Element} fieldGroupMessage The field group message element.
   * @return {void}
   */
  function hideFieldGroupMessage(fieldGroupMessage) {
    if (!fieldGroupMessage) {
      return;
    }
    fieldGroupMessage.classList.add('frm_hidden');
    fieldGroupMessage.classList.remove('frm-fadein-up-back');
  }

  /**
   * Show the field group message by manipulating classes.
   *
   * @param {Element} fieldGroupMessage The field group message element.
   * @return {void}
   */
  function showFieldGroupMessage(fieldGroupMessage) {
    if (!fieldGroupMessage) {
      return;
    }
    fieldGroupMessage.classList.remove('frm_hidden');
    fieldGroupMessage.classList.add('frm-fadein-up-back');
  }

  /**
   * Maybe show a message if there are at least two rows.
   *
   * @return {void}
   */
  function maybeShowFieldGroupMessage() {
    var fieldGroupMessage = document.getElementById('frm-field-group-message');
    var rows = document.querySelectorAll('.edit_form_item:not(.edit_field_type_end_divider)');
    if (rows.length < 2) {
      hideFieldGroupMessage(fieldGroupMessage);
      return;
    }
    if (fieldGroupMessage) {
      showFieldGroupMessage(fieldGroupMessage);
      return;
    }
    fieldGroupMessage = div({
      id: 'frm-field-group-message',
      className: 'frm-flex-center frm-fadein-up-back',
      children: [span({
        id: 'frm-field-group-message-dismiss',
        className: 'frm-flex-center',
        child: svg({
          href: '#frm_close_icon'
        })
      })]
    });

    // Insert the field group into the DOM
    document.getElementById('post-body-content').appendChild(fieldGroupMessage);

    // Get and add the field group message text
    var messageText = getFieldGroupMessageText();
    fieldGroupMessage.prepend(messageText);

    // Set up a click event listener
    document.getElementById('frm-field-group-message-dismiss').addEventListener('click', function () {
      hideFieldGroupMessage(document.getElementById('frm-field-group-message'));
    });
  }

  /**
   * Get a span element with text about selecting multiple fields.
   *
   * @return {HTMLElement} A span element with the message and style classes.
   */
  function getFieldGroupMessageText() {
    var text = document.createElement('span');
    text.classList.add('frm-field-group-message-text', 'frm-flex-center');
    text.innerHTML = sprintf(/* translators: %1$s: Start span HTML, %2$s: end span HTML */
    frm_admin_js.holdShiftMsg,
    // eslint-disable-line camelcase
    '<span class="frm-meta-tag frm-flex-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shift" viewBox="0 0 16 16"><path d="M7.3 2a1 1 0 0 1 1.4 0l6.4 6.8a1 1 0 0 1-.8 1.7h-2.8v3a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1v-3H1.7a1 1 0 0 1-.8-1.7L7.3 2zm7 7.5L8 2.7 1.7 9.5h2.8a1 1 0 0 1 1 1v3h5v-3a1 1 0 0 1 1-1h2.8z"/></svg>', '</span>');
    return text;
  }

  /**
   * Maybe hide the field group message based on the number of selected rows.
   *
   * @return {void}
   */
  function maybeHideFieldGroupMessage() {
    var selectedRowCount = document.querySelectorAll('.frm-selected-field-group').length;
    if (selectedRowCount < 2) {
      return;
    }
    var fieldGroupMessage = document.getElementById('frm-field-group-message');
    hideFieldGroupMessage(fieldGroupMessage);
  }
  function getSelectedField() {
    return document.getElementById('frm-show-fields').querySelector('li.form-field.selected');
  }
  function getSelectedFieldGroups() {
    var $fieldGroups = jQuery('.frm-selected-field-group');
    if ($fieldGroups.length) {
      return $fieldGroups;
    }
    var selectedField = getSelectedField();
    if (selectedField) {
      // If there is only one field in a group and the field is selected, consider the field's group as selected for multi-select.
      var selectedFieldGroup = selectedField.closest('ul');
      if (selectedFieldGroup && 1 === getFieldsInRow(jQuery(selectedFieldGroup)).length) {
        selectedFieldGroup.classList.add('frm-selected-field-group');
        return jQuery(selectedFieldGroup);
      }
    }
    return jQuery();
  }
  function syncAfterMultiSelect(numberOfSelectedGroups) {
    clearSettingsBox(true); // unselect any fields if one is selected.
    if (numberOfSelectedGroups >= 2 || 1 === numberOfSelectedGroups && selectedGroupHasMultipleFields()) {
      addFieldMultiselectPopup();
    } else {
      maybeRemoveMultiselectPopup();
    }
    maybeRemoveGroupHoverTarget();
  }
  function selectedGroupHasMultipleFields() {
    return getFieldsInRow(jQuery(document.querySelector('.frm-selected-field-group'))).length > 1;
  }
  function unselectFieldGroups(event) {
    if ('undefined' !== typeof event) {
      if (null !== event.originalEvent.target.closest('#frm-show-fields')) {
        return;
      }
      if (event.originalEvent.target.classList.contains('frm-merge-fields-into-row')) {
        return;
      }
      if (null !== event.originalEvent.target.closest('.frm-merge-fields-into-row')) {
        return;
      }
      if (event.originalEvent.target.classList.contains('frm-custom-field-group-layout')) {
        return;
      }
      if (event.originalEvent.target.classList.contains('frm-cancel-custom-field-group-layout')) {
        return;
      }
    }
    jQuery('.frm-selected-field-group').removeClass('frm-selected-field-group');
    jQuery(document).off('click', unselectFieldGroups);
    maybeRemoveMultiselectPopup();
  }
  function maybeRemoveMultiselectPopup() {
    var popup = document.getElementById('frm_field_multiselect_popup');
    if (null !== popup) {
      popup.remove();
    }
  }
  function addFieldMultiselectPopup() {
    getFieldMultiselectPopup();
  }
  function getFieldMultiselectPopup() {
    var popup, mergeOption, caret, verticalSeparator, deleteOption;
    popup = document.getElementById('frm_field_multiselect_popup');
    if (null !== popup) {
      popup.classList.toggle('frm-unmergable', !selectedFieldsAreMergeable());
      return popup;
    }
    popup = div();
    popup.id = 'frm_field_multiselect_popup';
    if (!selectedFieldsAreMergeable()) {
      popup.classList.add('frm-unmergable');
    }
    mergeOption = div();
    mergeOption.classList.add('frm-merge-fields-into-row');
    mergeOption.textContent = __('Merge into row', 'formidable');
    caret = document.createElement('a');
    caret.style.marginLeft = '5px';
    caret.classList.add('frm_icon_font', 'frm_arrowdown6_icon');
    caret.setAttribute('href', '#');
    mergeOption.appendChild(caret);
    popup.appendChild(mergeOption);
    verticalSeparator = div();
    verticalSeparator.classList.add('frm-multiselect-popup-separator');
    popup.appendChild(verticalSeparator);
    deleteOption = div();
    deleteOption.classList.add('frm-delete-field-groups');
    deleteOption.appendChild(getIconClone('frm_trash_svg'));
    popup.appendChild(deleteOption);
    document.getElementById('post-body-content').appendChild(popup);
    jQuery(popup).hide().fadeIn();
    return popup;
  }
  function selectedFieldsAreMergeable() {
    var selectedFieldGroups, totalFieldCount, length, index, fieldGroup;
    selectedFieldGroups = document.querySelectorAll('.frm-selected-field-group');
    length = selectedFieldGroups.length;
    if (1 === length) {
      return false;
    }
    totalFieldCount = 0;
    for (index = 0; index < length; ++index) {
      fieldGroup = selectedFieldGroups[index];
      if (null !== fieldGroup.querySelector('.edit_field_type_break, .edit_field_type_hidden')) {
        return false;
      }
      totalFieldCount += getFieldsInRow(jQuery(fieldGroup)).length;
      if (totalFieldCount > MAX_FIELD_GROUP_SIZE) {
        return false;
      }
    }
    return true;
  }
  function mergeFieldsIntoRowClick(event) {
    var size, popup;
    if (null !== event.originalEvent.target.closest('#frm_field_group_popup')) {
      // prevent clicks within the popup from triggering the button again.
      return;
    }
    if (event.originalEvent.target.classList.contains('frm-custom-field-group-layout')) {
      // avoid switching back to the first page when clicking the custom option nested inside of the merge option.
      return;
    }
    size = getSelectedFieldCount();
    popup = getFieldGroupPopup(size, document.querySelector('.frm-selected-field-group').firstChild);
    this.appendChild(popup);
  }
  function getSelectedFieldCount() {
    var count = 0;
    jQuery(document.querySelectorAll('.frm-selected-field-group')).each(function () {
      count += getFieldsInRow(jQuery(this)).length;
    });
    return count;
  }
  function deleteFieldGroupsClick() {
    var fieldIdsToDelete, deleteOnConfirm, multiselectPopup;
    fieldIdsToDelete = getSelectedFieldIds();
    deleteOnConfirm = getDeleteSelectedFieldGroupsOnConfirmFunction(fieldIdsToDelete);
    multiselectPopup = document.getElementById('frm_field_multiselect_popup');
    if (null !== multiselectPopup) {
      multiselectPopup.remove();
    }
    this.setAttribute('data-frmverify', confirmFieldsDeleteMessage(fieldIdsToDelete.length));
    confirmLinkClick(this);
    var confirmedClick = document.getElementById('frm-confirmed-click');

    // Remove any previous delete field data so delete confirmation does not attempt
    // to delete a field that was already deleted or previously attempted and cancelled.
    confirmedClick === null || confirmedClick === void 0 || confirmedClick.removeAttribute('data-deletefield');
    jQuery(confirmedClick).on('click', deleteOnConfirm);
    jQuery('#frm_confirm_modal').one('dialogclose', function () {
      jQuery(confirmedClick).off('click', deleteOnConfirm);
    });
  }
  function getSelectedFieldIds() {
    var deleteFieldIds = [];
    jQuery('.frm-selected-field-group > li.form-field').each(function () {
      deleteFieldIds.push(this.dataset.fid);
    });
    return deleteFieldIds;
  }
  function getDeleteSelectedFieldGroupsOnConfirmFunction(deleteFieldIds) {
    return function (event) {
      event.preventDefault();
      deleteAllSelectedFieldGroups(deleteFieldIds);
    };
  }
  function deleteAllSelectedFieldGroups(deleteFieldIds) {
    deleteFieldIds.forEach(function (fieldId) {
      deleteFields(fieldId);
    });
  }
  function deleteFieldConfirmed() {
    /*jshint validthis:true */
    deleteFields(this.getAttribute('data-deletefield'));
  }
  function deleteFields(fieldId) {
    var field = jQuery('#frm_field_id_' + fieldId);
    deleteField(fieldId);
    if (field.hasClass('edit_field_type_divider')) {
      field.find('li.frm_field_box[data-fid]').each(function () {
        deleteField(this.getAttribute('data-fid'));
      });
    }
    toggleSectionHolder();
  }

  /**
   * Checks if there is only submit field in the form builder.
   *
   * @return {Boolean}
   */
  function hasOnlySubmitField() {
    // If there are at least 2 rows, return false.
    if ($newFields.get(0).childElementCount > 1) {
      return false;
    }
    var childUl = $newFields.get(0).firstElementChild.firstElementChild;

    // Use query instead of children because there might be a div inside this ul.
    var childLi = childUl.querySelectorAll('li.frm_field_box');

    // If there are at least 2 items in the row, return false.
    if (childLi.length > 1) {
      return false;
    }
    return childLi[0].classList.contains('edit_field_type_submit');
  }

  /**
   * Moves open modals out of the field options form.
   *
   * When a modal is open, it is moved in the DOM and appended to the parent element of the modal trigger input. That
   * creates a problem since deleting the field also deletes the modal and this function fixes that problem.
   *
   * @since 6.22
   *
   * @param {Object} settings
   * @return {void}
   */
  function moveOpenModalsOutOfFieldOptions(settings) {
    var openModals = settings[0].querySelectorAll('.frm-inline-modal[data-fills]');
    if (!openModals.length) {
      return;
    }
    openModals.forEach(function (modal) {
      modal.classList.add('frm_hidden');
      modal.removeAttribute('data-fills');
      modal.closest('form').appendChild(modal);
    });
  }
  function deleteField(fieldId) {
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_delete_field',
        field_id: fieldId,
        nonce: frmGlobal.nonce
      },
      success: function success() {
        var $thisField = jQuery(document.getElementById('frm_field_id_' + fieldId)),
          settings = jQuery('#frm-single-settings-' + fieldId);

        // Remove settings from sidebar.
        if (settings.is(':visible')) {
          var _document$querySelect2;
          (_document$querySelect2 = document.querySelector('.frm-settings-panel .frm-tabs-navs ul > li:first-child')) === null || _document$querySelect2 === void 0 || _document$querySelect2.click();
          document.querySelector('#frm-options-panel .frm-single-settings').classList.remove('frm_hidden');
        }
        moveOpenModalsOutOfFieldOptions(settings);
        settings.remove();
        $thisField.fadeOut('slow', function () {
          var $section = $thisField.closest('.start_divider'),
            type = $thisField.data('type'),
            $adjacentFields = $thisField.siblings('li.form-field'),
            $liWrapper;
          if (!$adjacentFields.length) {
            if ($thisField.is('.edit_field_type_end_divider')) {
              $adjacentFields.length = $thisField.closest('li.form-field').siblings();
            } else {
              $liWrapper = $thisField.closest('ul.frm_sorting').parent();
            }
          }
          $thisField.remove();
          if (type === 'break') {
            renumberPageBreaks();
          } else if (type === 'product') {
            maybeHideQuantityProductFieldOption();
            // a product field attached to a quantity field earlier might be the one deleted, so re-populate
            popAllProductFields();
          }
          if ($adjacentFields.length) {
            syncLayoutClasses($adjacentFields.first());
          } else {
            $liWrapper.remove();
          }
          if (jQuery('#frm-show-fields li').length === 0 || hasOnlySubmitField()) {
            var formEditorContainer = document.getElementById('frm_form_editor_container');
            formEditorContainer.classList.remove('frm-has-fields');
            formEditorContainer.classList.add('frm-empty-fields');
          } else if ($section.length) {
            toggleOneSectionHolder($section);
          }

          // prevent "More Options" tooltips from staying around after their target field is deleted.
          deleteTooltips();
        });
        if ($thisField.length) {
          wp.hooks.doAction('frm_after_delete_field', $thisField[0]);
        }
      }
    });
  }
  function addFieldLogicRow() {
    /*jshint validthis:true */
    var id = jQuery(this).closest('.frm-single-settings').data('fid'),
      formId = thisFormId,
      logicRows = document.getElementById('frm_logic_row_' + id).querySelectorAll('.frm_logic_row');
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_add_logic_row',
        form_id: formId,
        field_id: id,
        nonce: frmGlobal.nonce,
        meta_name: getNewRowId(logicRows, 'frm_logic_' + id + '_'),
        fields: getFieldList()
      },
      success: function success(html) {
        jQuery(document.getElementById('logic_' + id)).fadeOut('fast', function () {
          var logicRow = document.getElementById('frm_logic_row_' + id);
          logicRow.insertAdjacentHTML('beforeend', html);
          var logicRowText = logicRow.querySelector('.frm_logic_row:last-child .frm-logic-rule-text');
          if (logicRowText) {
            logicRowText.textContent = logicRow.dataset.ruleText;
          }
          var logicRows = logicRow.closest('.frm_logic_rows');
          logicRows.style.height = 'auto';
          jQuery(logicRows).fadeIn('fast');
        });
      }
    });
    return false;
  }
  function getNewRowId(rows, replace, defaultValue) {
    if (!rows.length) {
      return 'undefined' !== typeof defaultValue ? defaultValue : 0;
    }
    return parseInt(rows[rows.length - 1].id.replace(replace, ''), 10) + 1;
  }
  function addWatchLookupRow() {
    /*jshint validthis:true */
    var lastRowId,
      id = jQuery(this).closest('.frm-single-settings').data('fid'),
      formId = thisFormId,
      lookupBlockRows = document.getElementById('frm_watch_lookup_block_' + id).children;
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_add_watch_lookup_row',
        form_id: formId,
        field_id: id,
        row_key: getNewRowId(lookupBlockRows, 'frm_watch_lookup_' + id + '_'),
        nonce: frmGlobal.nonce
      },
      success: function success(newRow) {
        var _document$getElementB2;
        var watchRowBlock = jQuery(document.getElementById('frm_watch_lookup_block_' + id));
        watchRowBlock.append(newRow);
        watchRowBlock.fadeIn('slow');

        // Show the "Watch Lookup Fields" label if it was hidden
        (_document$getElementB2 = document.getElementById("frm_watch_lookup_label_".concat(id))) === null || _document$getElementB2 === void 0 || _document$getElementB2.classList.remove('frm-force-hidden');
      }
    });
    return false;
  }
  function resetOptionTextDetails() {
    jQuery('.frm-single-settings ul input[type="text"][name^="field_options[options_"]').filter('[data-value-on-load]').removeAttr('data-value-on-load');
    jQuery('input[type="hidden"][name^=optionmap]').remove();
  }
  function optionTextAlreadyExists(input) {
    var fieldId = jQuery(input).closest('.frm-single-settings').attr('data-fid'),
      optionInputs = jQuery(input).closest('ul').get(0).querySelectorAll('.field_' + fieldId + '_option'),
      index,
      optionInput;
    for (index in optionInputs) {
      optionInput = optionInputs[index];
      if (optionInput.id !== input.id && optionInput.value === input.value && optionInput.getAttribute('data-duplicate') !== 'true') {
        return true;
      }
    }
    return false;
  }
  function onOptionTextFocus() {
    var input, fieldId;
    if (this.getAttribute('data-value-on-load') === null) {
      this.setAttribute('data-value-on-load', this.value);
      fieldId = jQuery(this).closest('.frm-single-settings').attr('data-fid');
      input = document.createElement('input');
      input.value = this.value;
      input.setAttribute('type', 'hidden');
      input.setAttribute('name', 'optionmap[' + fieldId + '][' + this.value + ']');
      this.parentNode.appendChild(input);
      if (typeof optionMap[fieldId] === 'undefined') {
        optionMap[fieldId] = {};
      }
      optionMap[fieldId][this.value] = input;
    }
    if (this.getAttribute('data-duplicate') === 'true') {
      this.removeAttribute('data-duplicate');

      // we want to use original value if actually still a duplicate
      if (optionTextAlreadyExists(this)) {
        this.setAttribute('data-value-on-focus', this.getAttribute('data-value-on-load'));
        return;
      }
    }
    if ('' !== this.value || frmAdminJs.new_option !== this.getAttribute('data-value-on-focus')) {
      this.setAttribute('data-value-on-focus', this.value);
    }
  }

  /**
   * Returns an object that has the old and new values and labels, when a field choice is changed.
   *
   * @param {HTMLElement} input
   * @return {Object}
   */
  function getChoiceOldAndNewValues(input) {
    var _getChoiceOldValueAnd = getChoiceOldValueAndLabel(input),
      oldValue = _getChoiceOldValueAnd.oldValue,
      oldLabel = _getChoiceOldValueAnd.oldLabel;
    var _getChoiceNewValueAnd = getChoiceNewValueAndLabel(input),
      newValue = _getChoiceNewValueAnd.newValue,
      newLabel = _getChoiceNewValueAnd.newLabel;
    return {
      oldValue: oldValue,
      oldLabel: oldLabel,
      newValue: newValue,
      newLabel: newLabel
    };
  }

  /**
   * Returns an object that has the new value and label, when a field choice is changed.
   *
   * @param {HTMLElement} choiceElement
   * @return {Object}
   */
  function getChoiceNewValueAndLabel(choiceElement) {
    var singleOptionContainer = choiceElement.closest('.frm_single_option');
    var newValue, newLabel;
    if (choiceElement.parentElement.classList.contains('frm_single_option')) {
      // label changed
      newValue = singleOptionContainer.querySelector('.frm_option_key input[type="text"]').value;
      newLabel = choiceElement.value;
      return {
        newValue: newValue,
        newLabel: newLabel
      };
    }

    // saved value changed
    newLabel = singleOptionContainer.querySelector('input[type="text"]').value;
    newValue = choiceElement.value;
    return {
      newValue: newValue,
      newLabel: newLabel
    };
  }

  /**
   * Returns an object that has the old value and label, when a field choice is changed.
   *
   * @param {HTMLElement} choiceElement
   * @return {Object}
   */
  function getChoiceOldValueAndLabel(choiceElement) {
    var _choiceElement$closes, _choiceElement$closes2;
    var usingSeparateValues = (_choiceElement$closes = (_choiceElement$closes2 = choiceElement.closest('.frm-single-settings').querySelector('.frm_toggle_sep_values')) === null || _choiceElement$closes2 === void 0 ? void 0 : _choiceElement$closes2.checked) !== null && _choiceElement$closes !== void 0 ? _choiceElement$closes : false;
    var singleOptionContainer = choiceElement.closest('.frm_single_option');
    var oldValue, oldLabel;
    if (usingSeparateValues) {
      if (choiceElement.parentElement.classList.contains('frm_single_option')) {
        // label changed
        oldValue = singleOptionContainer.querySelector('.frm_option_key input[type="text"]').getAttribute('data-value-on-focus');
        oldLabel = choiceElement.getAttribute('data-value-on-focus');
        return {
          oldValue: oldValue,
          oldLabel: oldLabel
        };
      }
    }
    oldValue = choiceElement.getAttribute('data-value-on-focus');
    oldLabel = singleOptionContainer.querySelector('input[type="text"]').getAttribute('data-value-on-focus');
    return {
      oldValue: oldValue,
      oldLabel: oldLabel
    };
  }
  function onOptionTextBlur() {
    var originalValue, fieldId, fieldIndex, logicId, row, rowLength, rowIndex, valueSelect, opts, fieldIds, settingId, setting, optionMatches, option;
    var _getChoiceOldAndNewVa = getChoiceOldAndNewValues(this),
      oldValue = _getChoiceOldAndNewVa.oldValue,
      oldLabel = _getChoiceOldAndNewVa.oldLabel,
      newValue = _getChoiceOldAndNewVa.newValue,
      newLabel = _getChoiceOldAndNewVa.newLabel;
    if (oldValue === newValue && oldLabel === newLabel) {
      return;
    }
    var singleSettingsContainer = this.closest('.frm-single-settings');
    fieldId = singleSettingsContainer.getAttribute('data-fid');
    originalValue = this.getAttribute('data-value-on-load');

    // check if the newValue is already mapped to another option
    // if it is, mark as duplicate and return
    if (optionTextAlreadyExists(this)) {
      this.setAttribute('data-duplicate', 'true');
      if (typeof optionMap[fieldId] !== 'undefined' && typeof optionMap[fieldId][originalValue] !== 'undefined') {
        // unmap any other change that may have happened before instead of changing it to something unused
        optionMap[fieldId][originalValue].value = originalValue;
      }
      return;
    }
    if (typeof optionMap[fieldId] !== 'undefined' && typeof optionMap[fieldId][originalValue] !== 'undefined') {
      optionMap[fieldId][originalValue].value = newValue;
    }
    fieldIds = [];
    rows = builderPage.querySelectorAll('.frm_logic_row');
    rowLength = rows.length;
    for (rowIndex = 0; rowIndex < rowLength; rowIndex++) {
      row = rows[rowIndex];
      opts = row.querySelector('.frm_logic_field_opts');
      if (opts.value !== fieldId) {
        continue;
      }
      logicId = row.id.split('_')[2];
      valueSelect = row.querySelector('select[name="field_options[hide_opt_' + logicId + '][]"]');
      if ('' === oldValue) {
        optionMatches = [];
      } else {
        optionMatches = valueSelect.querySelectorAll('option[value="' + oldValue + '"]');
      }
      if (!optionMatches.length) {
        optionMatches = valueSelect.querySelectorAll('option[value="' + newValue + '"]');
        if (!optionMatches.length) {
          var _singleSettingsContai;
          if (!((_singleSettingsContai = singleSettingsContainer.querySelector('.frm_toggle_sep_values')) !== null && _singleSettingsContai !== void 0 && _singleSettingsContai.checked)) {
            option = searchSelectByText(valueSelect, oldValue); // Find conditional logic option with oldValue
          }
          if (!option) {
            option = document.createElement('option');
            valueSelect.appendChild(option);
          }
        }
      }
      if (optionMatches.length) {
        option = optionMatches[optionMatches.length - 1];
      }
      option.setAttribute('value', newValue);
      option.textContent = newLabel;
      if (fieldIds.indexOf(logicId) === -1) {
        fieldIds.push(logicId);
      }
    }
    for (fieldIndex in fieldIds) {
      settingId = fieldIds[fieldIndex];
      setting = document.getElementById('frm-single-settings-' + settingId);
      moveFieldSettings(setting);
    }
  }

  /**
   * Returns an option element that matches a string with its text content.
   *
   * @param {HTMLElement} selectElement
   * @param {string}      searchText
   * @return {HTMLElement|null}
   */
  function searchSelectByText(selectElement, searchText) {
    var options = selectElement.options;
    for (var _i10 = 0; _i10 < options.length; _i10++) {
      var option = options[_i10];
      if (searchText === option.textContent) {
        return option;
      }
    }
    return null;
  }
  function updateGetValueFieldSelection() {
    /*jshint validthis:true */
    var fieldID = this.id.replace('get_values_form_', '');
    var fieldSelect = document.getElementById('get_values_field_' + fieldID);
    var fieldType = this.getAttribute('data-fieldtype');
    if (this.value === '') {
      fieldSelect.options.length = 1;
    } else {
      var formID = this.value;
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'frm_get_options_for_get_values_field',
          form_id: formID,
          field_type: fieldType,
          nonce: frmGlobal.nonce
        },
        success: function success(fields) {
          fieldSelect.innerHTML = fields;
        }
      });
    }
  }

  // Clear the Watch Fields option when Lookup field switches to "Text" option
  function maybeClearWatchFields() {
    /*jshint validthis:true */
    var link,
      lookupBlock,
      fieldID = this.name.replace('field_options[data_type_', '').replace(']', '');
    link = document.getElementById('frm_add_watch_lookup_link_' + fieldID);
    if (!link) {
      return;
    }
    link = link.parentNode;
    if (this.value === 'text') {
      lookupBlock = document.getElementById('frm_watch_lookup_block_' + fieldID);
      if (lookupBlock !== null) {
        // Clear and hide the Watch Fields option
        lookupBlock.innerHTML = '';
        link.classList.add('frm_hidden');

        // Hide the Watch Fields row
        link.previousElementSibling.style.display = 'none';
        link.previousElementSibling.previousElementSibling.style.display = 'none';
        link.previousElementSibling.previousElementSibling.previousElementSibling.style.display = 'none';
      }
    } else {
      // Show the Watch Fields option
      link.classList.remove('frm_hidden');
    }
    toggleMultiSelect(fieldID, this.value);
  }

  // Number the pages and hide/show the first page as needed.
  function renumberPageBreaks() {
    var i,
      containerClass,
      pages = document.getElementsByClassName('frm-page-num');
    if (pages.length > 1) {
      document.getElementById('frm-fake-page').style.display = 'block';
      for (i = 0; i < pages.length; i++) {
        containerClass = pages[i].parentNode.parentNode.parentNode.classList;
        if (i === 1) {
          // Hide previous button on page 1
          containerClass.add('frm-first-page');
        } else {
          containerClass.remove('frm-first-page');
        }
        pages[i].textContent = i + 1;
      }
    } else {
      document.getElementById('frm-fake-page').style.display = 'none';
    }
    wp.hooks.doAction('frm_renumber_page_breaks', pages);
  }

  // The fake field works differently than real fields.
  function maybeCollapsePage() {
    /*jshint validthis:true */
    var field = jQuery(this).closest('.frm_field_box[data-ftype=break]');
    if (field.length) {
      toggleCollapsePage(field);
    } else {
      toggleCollapseFakePage();
    }
  }

  // Find all fields in a page and hide/show them
  function toggleCollapsePage(field) {
    var toCollapse = getAllFieldsForPage(field.get(0).parentNode.closest('li.frm_field_box').nextElementSibling);
    togglePage(field, toCollapse);
  }
  function toggleCollapseFakePage() {
    var topLevel = document.getElementById('frm-fake-page'),
      firstField = document.getElementById('frm-show-fields').firstElementChild,
      toCollapse = getAllFieldsForPage(firstField);
    if (firstField.getAttribute('data-ftype') === 'break') {
      // Don't collapse if the first field is a page break.
      return;
    }
    togglePage(jQuery(topLevel), toCollapse);
  }
  function getAllFieldsForPage(firstWrapper) {
    var $fieldsForPage, currentWrapper;
    $fieldsForPage = jQuery();
    if (null === firstWrapper) {
      return $fieldsForPage;
    }
    currentWrapper = firstWrapper;
    do {
      if (null !== currentWrapper.querySelector('.edit_field_type_break')) {
        break;
      }
      $fieldsForPage = $fieldsForPage.add(jQuery(currentWrapper));
      currentWrapper = currentWrapper.nextElementSibling;
    } while (null !== currentWrapper);
    return $fieldsForPage;
  }
  function togglePage(field, toCollapse) {
    var i,
      fieldCount = toCollapse.length,
      slide = Math.min(fieldCount, 3);
    if (field.hasClass('frm-page-collapsed')) {
      field.removeClass('frm-page-collapsed');
      toCollapse.removeClass('frm-is-collapsed');
      for (i = 0; i < slide; i++) {
        if (i === slide - 1) {
          jQuery(toCollapse[i]).slideDown(150, function () {
            toCollapse.show();
          });
        } else {
          jQuery(toCollapse[i]).slideDown(150);
        }
      }
    } else {
      field.addClass('frm-page-collapsed');
      toCollapse.addClass('frm-is-collapsed');
      for (i = 0; i < slide; i++) {
        if (i === slide - 1) {
          jQuery(toCollapse[i]).slideUp(150, function () {
            toCollapse.css('cssText', 'display:none !important;');
          });
        } else {
          jQuery(toCollapse[i]).slideUp(150);
        }
      }
    }
  }
  function maybeCollapseSection() {
    /*jshint validthis:true */
    var parentCont = this.parentNode.parentNode.parentNode.parentNode;
    parentCont.classList.toggle('frm-section-collapsed');
  }
  function maybeCollapseSettings() {
    /*jshint validthis:true */
    this.classList.toggle('frm-collapsed');

    // Toggles the "aria-expanded" attribute
    var expanded = this.getAttribute('aria-expanded') === 'true' || false;
    this.setAttribute('aria-expanded', !expanded);
    addSlideAnimationCssVars(this.nextElementSibling);
  }

  /**
   * Add slide animation CSS variables to the element
   *
   * @param {HTMLElement} element The element to add CSS variables to
   * @return {void}
   */
  function addSlideAnimationCssVars(element) {
    if (!element) {
      return;
    }
    var height = element.scrollHeight;
    if (height <= 0) {
      return;
    }
    height += 250;
    element.style.setProperty('--slide-height', "".concat(height, "px"));
    element.style.setProperty('--slide-time', "".concat(Math.ceil(height * 0.8), "ms"));
  }
  function clickLabel() {
    if (!this.id) {
      return;
    }

    /*jshint validthis:true */
    var setting = document.querySelectorAll('[data-changeme="' + this.id + '"]')[0],
      fieldId = this.id.replace('field_label_', ''),
      fieldType = document.getElementById('field_options_type_' + fieldId),
      fieldTypeName = fieldType.value;
    if (typeof setting !== 'undefined') {
      if (fieldType.tagName === 'SELECT') {
        fieldTypeName = fieldType.options[fieldType.selectedIndex].text.toLowerCase();
      } else {
        fieldTypeName = fieldTypeName.replace('_', ' ');
      }
      fieldTypeName = normalizeFieldName(fieldTypeName);
      setTimeout(function () {
        if (setting.value.toLowerCase() === fieldTypeName) {
          setting.select();
        } else {
          setting.focus();
        }
      }, 50);
    }
  }
  function clickDescription() {
    /*jshint validthis:true */
    var setting = document.querySelectorAll('[data-changeme="' + this.id + '"]')[0];
    if (typeof setting !== 'undefined') {
      setTimeout(function () {
        setting.focus();
        autoExpandSettings(setting);
      }, 50);
    }
  }
  function autoExpandSettings(setting) {
    var inSection = setting.closest('.frm-collapse-me');
    if (inSection !== null) {
      inSection.previousElementSibling.classList.remove('frm-collapsed');
    }
  }
  function normalizeFieldName(fieldTypeName) {
    if (fieldTypeName === 'divider') {
      fieldTypeName = 'section';
    } else if (fieldTypeName === 'range') {
      fieldTypeName = 'slider';
    } else if (fieldTypeName === 'data') {
      fieldTypeName = 'dynamic';
    } else if (fieldTypeName === 'form') {
      fieldTypeName = 'embed form';
    }
    return fieldTypeName;
  }
  function clickVis(e) {
    /*jshint validthis:true */
    var currentClass, originalList;
    currentClass = e.target.classList;
    if (currentClass.contains('frm-collapse-page') || currentClass.contains('frm-sub-label') || e.target.closest('.dropdown') !== null) {
      return;
    }
    if (this.closest('.start_divider') !== null) {
      e.stopPropagation();
    }
    if (this.classList.contains('edit_field_type_divider')) {
      originalList = e.originalEvent.target.closest('ul.frm_sorting');
      if (null !== originalList) {
        // prevent section click if clicking a field group within a section.
        if (originalList.classList.contains('edit_field_type_divider') || originalList.parentNode.parentNode.classList.contains('start_divider')) {
          return;
        }
      }
    }
    clickAction(this);
  }

  /**
   * Update the format input based on the selected format type.
   *
   * @since 6.9
   *
   * @param {Event} event The event object from the format type selection.
   * @return {void}
   */
  function maybeUpdateFormatInput(event) {
    var formatElement = event.target;
    var type = formatElement.value;
    if ('custom' === type) {
      var fieldId = formatElement.dataset.fieldId;
      var formatInput = document.getElementById("frm-field-format-custom-".concat(fieldId)).querySelector('.frm_format_opt');
      if ('international' === formatInput.value || 'currency' === formatInput.value || 'number' === formatInput.value) {
        formatInput.setAttribute('value', '');
      }
    }
    setTimeout(function () {
      formatElement.querySelectorAll('option').forEach(function (option) {
        if (option.selected && option.classList.contains('frm_show_upgrade')) {
          formatElement.value = 'none';
        }
      });
    }, 0);
  }

  /**
   * Open Advanced settings on double click.
   */
  function openAdvanced() {
    var fieldId = this.getAttribute('data-fid');
    autoExpandSettings(document.getElementById('field_options_field_key_' + fieldId));
  }
  function toggleRepeatButtons() {
    /*jshint validthis:true */
    var $thisField = jQuery(this).closest('.frm_field_box');
    $thisField.find('.repeat_icon_links').removeClass('repeat_format repeat_formatboth repeat_formattext').addClass('repeat_format' + this.value);
    if (this.value === 'text' || this.value === 'both') {
      $thisField.find('.frm_repeat_text').show();
      $thisField.find('.repeat_icon_links a').addClass('frm_button');
    } else {
      $thisField.find('.frm_repeat_text').hide();
      $thisField.find('.repeat_icon_links a').removeClass('frm_button');
    }
  }
  function checkRepeatLimit() {
    /*jshint validthis:true */
    var val = this.value;
    if (val !== '' && (val < 2 || val > 200)) {
      infoModal(frmAdminJs.repeat_limit_min);
      this.value = '';
    }
  }
  function checkCheckboxSelectionsLimit() {
    /*jshint validthis:true */
    var val = this.value;
    if (val !== '' && (val < 1 || val > 200)) {
      infoModal(frmAdminJs.checkbox_limit);
      this.value = '';
    }
  }
  function updateRepeatText(obj, addRemove) {
    var $thisField = jQuery(obj).closest('.frm_field_box');
    $thisField.find('.frm_' + addRemove + '_form_row .frm_repeat_label').text(obj.value);
  }
  function fieldsInSection(id) {
    var children = [];
    jQuery(document.getElementById('frm_field_id_' + id)).find('li.frm_field_box:not(.no_repeat_section .edit_field_type_end_divider)').each(function () {
      children.push(jQuery(this).data('fid'));
    });
    return children;
  }
  function toggleFormTax() {
    /*jshint validthis:true */
    var id = jQuery(this).closest('.frm-single-settings').data('fid');
    var val = this.value;
    var $showFields = document.getElementById('frm_show_selected_fields_' + id);
    var $showForms = document.getElementById('frm_show_selected_forms_' + id);
    jQuery($showForms).find('select').val('');
    if (val === 'form') {
      $showForms.style.display = 'inline';
      empty($showFields);
    } else {
      $showFields.style.display = 'none';
      $showForms.style.display = 'none';
      getTaxOrFieldSelection(val, id);
    }
  }
  function resetOptOnChange() {
    /*jshint validthis:true */
    var field, thisOpt;
    field = getFieldKeyFromOpt(this);
    if (!field) {
      return;
    }
    thisOpt = jQuery(this).closest('.frm_single_option');
    resetSingleOpt(field.fieldId, field.fieldKey, thisOpt);
  }
  function getFieldKeyFromOpt(object) {
    var allOpts, fieldId, fieldKey;
    allOpts = jQuery(object).closest('.frm_sortable_field_opts');
    if (!allOpts.length) {
      return false;
    }
    fieldId = allOpts.attr('id').replace('frm_field_', '').replace('_opts', '');
    fieldKey = allOpts.data('key');
    return {
      fieldId: fieldId,
      fieldKey: fieldKey
    };
  }
  function resetSingleOpt(fieldId, fieldKey, thisOpt) {
    var saved,
      text,
      defaultVal,
      previewInput,
      labelForDisplay,
      optContainer,
      optKey = thisOpt.data('optkey'),
      separateValues = usingSeparateValues(fieldId),
      single = jQuery('label[for="field_' + fieldKey + '-' + optKey + '"]'),
      baseName = 'field_options[options_' + fieldId + '][' + optKey + ']',
      label = jQuery('input[name="' + baseName + '[label]"]');
    if (single.length < 1) {
      resetDisplayedOpts(fieldId);

      // Set the default value.
      defaultVal = thisOpt.find('input[name^="default_value_"]');
      if (defaultVal.is(':checked') && label.length > 0) {
        jQuery('select[name^="item_meta[' + fieldId + ']"]').val(label.val());
      }
      return;
    }
    previewInput = single.children('input');
    if (label.length < 1) {
      // Check for other label.
      label = jQuery('input[name="' + baseName + '"]');
      saved = label.val();
    } else if (separateValues) {
      saved = jQuery('input[name="' + baseName + '[value]"]').val();
    } else {
      saved = label.val();
    }
    if (label.length < 1) {
      return;
    }

    // Set the displayed value.
    text = single[0].childNodes;
    if (imagesAsOptions(fieldId)) {
      labelForDisplay = getImageDisplayValue(thisOpt, fieldId, label);
      optContainer = single.find('.frm_image_option_container');
      if (optContainer.length > 0) {
        optContainer.replaceWith(labelForDisplay);
      } else {
        text[text.length - 1].nodeValue = '';
        single.append(labelForDisplay);
      }
    } else {
      var firstInputIndex = false;
      text.forEach(function (node, index) {
        if (firstInputIndex === false) {
          if (node.tagName === 'INPUT') {
            firstInputIndex = index;
          }
        } else if (index === firstInputIndex + 1) {
          var nodeValue = '';
          if (buttonsAsOptions(fieldId)) {
            nodeValue = div({
              className: 'frm_label_button_container',
              text: ' ' + label.val()
            });
            single[0].replaceChild(nodeValue, node);
          } else {
            node.nodeValue = ' ' + label.val();
          }
        } else {
          single[0].removeChild(node);
        }
      });
    }

    // Set saved value.
    previewInput.val(saved);

    // Set the default value.
    defaultVal = thisOpt.find('input[name^="default_value_"]');
    previewInput.prop('checked', defaultVal.is(':checked') ? true : false);
  }
  function buttonsAsOptions(fieldId) {
    var fields = document.getElementsByName('field_options[image_options_' + fieldId + ']');
    var result = Array.from(fields).find(function (field) {
      return field.checked && 'buttons' === field.value;
    });
    return typeof result !== 'undefined';
  }

  /**
   * Set the displayed value for an image option.
   */
  function getImageDisplayValue(thisOpt, fieldId, label) {
    var image, imageUrl, showLabelWithImage, fieldType;
    image = thisOpt.find('img');
    if (image) {
      imageUrl = image.attr('src');
    }
    showLabelWithImage = showingLabelWithImage(fieldId);
    fieldType = radioOrCheckbox(fieldId);
    return getImageLabel(label.val(), showLabelWithImage, imageUrl, fieldType);
  }
  function getImageOptionSize(fieldId) {
    var val,
      field = document.getElementById('field_options_image_size_' + fieldId),
      size = '';
    if (field !== null) {
      val = field.value;
      if (val !== '') {
        size = val;
      }
    }
    return size;
  }
  function resetDisplayedOpts(fieldId) {
    var i,
      opts,
      type,
      placeholder,
      fieldInfo,
      input = jQuery('[name^="item_meta[' + fieldId + ']"]');
    if (input.length < 1) {
      return;
    }
    if (input.is('select')) {
      placeholder = document.getElementById('frm_placeholder_' + fieldId);
      if (placeholder !== null && placeholder.value === '') {
        fillDropdownOpts(input[0], {
          sourceID: fieldId
        });
      } else {
        fillDropdownOpts(input[0], {
          sourceID: fieldId,
          placeholder: placeholder.value
        });
      }
    } else {
      opts = getMultipleOpts(fieldId);
      jQuery('#field_' + fieldId + '_inner_container > .frm_form_fields').html('');
      fieldInfo = getFieldKeyFromOpt(jQuery('#frm_delete_field_' + fieldId + '-000_container'));
      var container = jQuery('#field_' + fieldId + '_inner_container > .frm_form_fields'),
        hasImageOptions = imagesAsOptions(fieldId),
        imageSize = hasImageOptions ? getImageOptionSize(fieldId) : '',
        imageOptionClass = hasImageOptions ? 'frm_image_option frm_image_' + imageSize + ' ' : '',
        isProduct = isProductField(fieldId);
      type = 'hidden' === input.attr('type') ? input.data('field-type') : input.attr('type');
      for (i = 0; i < opts.length; i++) {
        container.append(addRadioCheckboxOpt(type, opts[i], fieldId, fieldInfo.fieldKey, isProduct, imageOptionClass));
      }
    }
    adjustConditionalLogicOptionOrders(fieldId);
  }

  /**
   * Returns an object that has a value and label for new conditional logic option, for a given option value.
   *
   * @param {Number} fieldId
   * @param {string} expectedOption
   * @return {Object}
   */
  function getNewConditionalLogicOption(fieldId, expectedOption) {
    var optionsContainer = document.getElementById('frm_field_' + fieldId + '_opts');
    var expectedOptionInput = optionsContainer.querySelector('input[value="' + expectedOption + '"]');
    if (expectedOptionInput) {
      return getChoiceNewValueAndLabel(expectedOptionInput);
    }
    return {
      newValue: expectedOption,
      newLabel: expectedOption
    };
  }
  function adjustConditionalLogicOptionOrders(fieldId, type) {
    var row,
      opts,
      logicId,
      valueSelect,
      optionLength,
      optionIndex,
      expectedOption,
      optionMatch,
      fieldOptions,
      rows = builderPage.querySelectorAll('.frm_logic_row'),
      rowLength = rows.length;
    fieldOptions = wp.hooks.applyFilters('frm_conditional_logic_field_options', getFieldOptions(fieldId), {
      type: type,
      fieldId: fieldId
    });
    optionLength = fieldOptions.length;
    for (rowIndex = 0; rowIndex < rowLength; rowIndex++) {
      row = rows[rowIndex];
      opts = row.querySelector('.frm_logic_field_opts');
      if (opts.value != fieldId) {
        continue;
      }
      logicId = row.id.split('_')[2];
      valueSelect = row.querySelector('select[name="field_options[hide_opt_' + logicId + '][]"]');
      for (optionIndex = optionLength - 1; optionIndex >= 0; optionIndex--) {
        var _document$getElementB3;
        expectedOption = fieldOptions[optionIndex];
        var expectedOptionValue = (_document$getElementB3 = document.getElementById('frm_field_' + fieldId + '_opts').querySelector('.frm_option_key input[type="text"]')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.value;
        if (!expectedOptionValue) {
          expectedOptionValue = expectedOption;
        }
        optionMatch = valueSelect.querySelector('option[value="' + expectedOptionValue + '"]');
        var _getNewConditionalLog = getNewConditionalLogicOption(fieldId, expectedOption),
          newValue = _getNewConditionalLog.newValue,
          newLabel = _getNewConditionalLog.newLabel;
        var fieldChoices = document.querySelectorAll('#frm_field_' + fieldId + '_opts input[data-value-on-focus]');
        var expectedChoiceEl = Array.from(fieldChoices).find(function (element) {
          return element.value === expectedOption;
        });
        if (expectedChoiceEl) {
          var oldValue = expectedChoiceEl.dataset.valueOnFocus;
          var hasMatch = oldValue && valueSelect.querySelector('option[value="' + oldValue + '"]');
          if (hasMatch) {
            continue;
          }
        }
        prependValueSelectWithOptionMatch(valueSelect, optionMatch, newValue, newLabel);
      }
      optionMatch = valueSelect.querySelector('option[value=""]');
      if (optionMatch !== null) {
        valueSelect.prepend(optionMatch);
      }
    }
  }
  function prependValueSelectWithOptionMatch(valueSelect, optionMatch, newValue, newLabel) {
    if (optionMatch === null && !valueSelect.querySelector('option[value="' + newValue + '"]')) {
      optionMatch = frmDom.tag('option', {
        text: newLabel
      });
      optionMatch.value = newValue;
    }
    valueSelect.prepend(optionMatch);
  }
  function getFieldOptions(fieldId) {
    var index,
      input,
      li,
      listItems,
      optsContainer,
      length,
      options = [];
    optsContainer = document.getElementById('frm_field_' + fieldId + '_opts');
    if (!optsContainer) {
      return options;
    }
    listItems = optsContainer.querySelectorAll('.frm_single_option');
    length = listItems.length;
    for (index = 0; index < length; index++) {
      li = listItems[index];
      if (li.classList.contains('frm_hidden')) {
        continue;
      }
      input = li.querySelector('.field_' + fieldId + '_option');
      options.push(input.value);
    }
    return options;
  }
  function addRadioCheckboxOpt(type, opt, fieldId, fieldKey, isProduct, classes) {
    var other,
      single = '',
      isOther = opt.key.indexOf('other') !== -1,
      id = 'field_' + fieldKey + '-' + opt.key,
      inputType = type === 'scale' ? 'radio' : type;
    other = '<input type="text" id="field_' + fieldKey + '-' + opt.key + '-otext" class="frm_other_input frm_pos_none" name="item_meta[other][' + fieldId + '][' + opt.key + ']" value="" />';
    this.getSingle = function () {
      /**
       * Get single option template.
       *
       * @param {Object} option  Object containing the option data.
       * @param {string} type    The field type.
       * @param {string} fieldId The field id.
       * @param {string} classes The option clasnames.
       * @param {string} id      The input id attribute.
       */
      single = wp.hooks.applyFilters('frm_admin.build_single_option_template', single, {
        opt: opt,
        type: type,
        fieldId: fieldId,
        classes: classes,
        id: id
      });
      if ('' !== single) {
        return single;
      }
      return '<div class="frm_' + type + ' ' + type + ' ' + classes + '" id="frm_' + type + '_' + fieldId + '-' + opt.key + '"><label for="' + id + '"><input type="' + inputType + '" name="item_meta[' + fieldId + ']' + (type === 'checkbox' ? '[]' : '') + '" value="' + purifyHtml(opt.saved) + '" id="' + id + '"' + (isProduct ? ' data-price="' + opt.price + '"' : '') + (opt.checked ? ' checked="checked"' : '') + '> ' + purifyHtml(opt.label) + '</label>' + (isOther ? other : '') + '</div>';
    };
    return this.getSingle();
  }
  function fillDropdownOpts(field, atts) {
    if (field === null) {
      return;
    }
    var sourceID = atts.sourceID,
      placeholder = atts.placeholder,
      isProduct = isProductField(sourceID),
      showOther = atts.other;
    removeDropdownOpts(field);
    var opts = getMultipleOpts(sourceID, field.id.includes('frm_field_logic_opt'));
    var hasPlaceholder = typeof placeholder !== 'undefined';
    for (var _i11 = 0; _i11 < opts.length; _i11++) {
      var label = opts[_i11].label,
        isOther = opts[_i11].key.indexOf('other') !== -1;
      if (hasPlaceholder && label !== '') {
        addBlankSelectOption(field, placeholder);
      } else if (hasPlaceholder) {
        label = placeholder;
      }
      hasPlaceholder = false;
      if (!isOther || showOther) {
        var opt = document.createElement('option');
        opt.value = opts[_i11].saved;
        opt.innerHTML = purifyHtml(label);
        if (isProduct) {
          opt.setAttribute('data-price', opts[_i11].price);
        }
        field.appendChild(opt);
      }
    }
  }
  function addBlankSelectOption(field, placeholder) {
    var opt = document.createElement('option'),
      firstChild = field.firstChild;
    opt.value = '';
    opt.innerHTML = placeholder;
    if (firstChild !== null) {
      field.insertBefore(opt, firstChild);
      field.selectedIndex = 0;
    } else {
      field.appendChild(opt);
    }
  }

  /**
   * Get multiple options for a field.
   *
   * @param {string}  fieldId          The field id.
   * @param {boolean} showValueAsLabel Whether to show the value as label for empty labels.
   */
  function getMultipleOpts(fieldId) {
    var showValueAsLabel = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var i,
      saved,
      labelName,
      label,
      key,
      optObj,
      fieldType,
      checked = false,
      opts = [],
      imageUrl = '';
    var optVals = jQuery('input[name^="field_options[options_' + fieldId + ']"]').filter('[name$="[label]"], [name*="[other_"]');
    var isProduct = isProductField(fieldId);
    var showLabelWithImage = showingLabelWithImage(fieldId);
    var hasImageOptions = imagesAsOptions(fieldId);
    var separateValues = usingSeparateValues(fieldId);
    for (i = 0; i < optVals.length; i++) {
      if (optVals[i].name.indexOf('[000]') > 0) {
        continue;
      }
      saved = optVals[i].value;
      label = saved;
      key = optVals[i].name.replace('field_options[options_' + fieldId + '][', '').replace('[label]', '').replace(']', '');
      if (separateValues) {
        labelName = optVals[i].name.replace('[label]', '[value]');
        saved = jQuery('input[name="' + labelName + '"]').val();
        if (showValueAsLabel && '' === label) {
          label = '' !== saved ? saved : frm_admin_js.no_label; // eslint-disable-line camelcase
        }
      }
      if (hasImageOptions) {
        imageUrl = getImageUrlFromInput(optVals[i]);
        fieldType = radioOrCheckbox(fieldId);
        label = getImageLabel(label, showLabelWithImage, imageUrl, fieldType);
      }

      /**
       * @since 5.0.04
       */
      label = frmAdminBuild.hooks.applyFilters('frm_choice_field_label', label, fieldId, optVals[i], hasImageOptions);
      checked = getChecked(optVals[i].id);
      optObj = {
        saved: saved,
        label: label,
        checked: checked,
        key: key
      };
      if (isProduct) {
        labelName = optVals[i].name.replace('[label]', '[price]');
        optObj.price = jQuery('input[name="' + labelName + '"]').val();
      }
      opts.push(optObj);
    }
    return opts;
  }
  function radioOrCheckbox(fieldId) {
    var settings = document.getElementById('frm-single-settings-' + fieldId);
    if (settings === null) {
      return 'radio';
    }
    return settings.classList.contains('frm-type-checkbox') ? 'checkbox' : 'radio';
  }
  function getImageUrlFromInput(optVal) {
    var img,
      wrapper = jQuery(optVal).siblings('.frm_image_preview_wrapper');
    if (!wrapper.length) {
      return '';
    }
    img = wrapper.find('img');
    if (!img.length) {
      return '';
    }
    return img.attr('src');
  }
  function purifyHtml(html) {
    if (html instanceof Element || html instanceof Document) {
      html = html.outerHTML;
    }
    var clean = jQuery.parseHTML(html).reduce(function (total, currentNode) {
      var cleanNode = frmDom.cleanNode(currentNode);
      if ('#text' === cleanNode.nodeName) {
        return total += cleanNode.textContent;
      }
      return total + cleanNode.outerHTML;
    }, '');
    if (clean !== html) {
      // Clean it until nothing changes, in case the stripped result is now unsafe.
      return purifyHtml(clean);
    }
    return clean;
  }
  function getImageLabel(label, showLabelWithImage, imageUrl, fieldType) {
    var imageLabelClass,
      originalLabel = label,
      shape = fieldType === 'checkbox' ? 'square' : 'circle',
      labelImage,
      labelNode,
      imageLabel;
    originalLabel = purifyHtml(originalLabel);
    if (imageUrl) {
      labelImage = img({
        src: imageUrl,
        alt: originalLabel
      });
    } else {
      labelImage = div({
        className: 'frm_empty_url'
      });
      labelImage.innerHTML = frmAdminJs.image_placeholder_icon;
    }
    imageLabelClass = showLabelWithImage ? ' frm_label_with_image' : '';
    imageLabel = tag('span', {
      className: 'frm_text_label_for_image_inner'
    });
    imageLabel.innerHTML = originalLabel;
    labelNode = tag('span', {
      className: 'frm_image_option_container' + imageLabelClass,
      children: [labelImage, tag('span', {
        className: 'frm_text_label_for_image',
        child: imageLabel
      })]
    });
    return labelNode;
  }
  function getChecked(id) {
    field = jQuery('#' + id);
    if (field.length === 0) {
      return false;
    }
    checkbox = field.siblings('input[type=checkbox]');
    return checkbox.length && checkbox.prop('checked');
  }
  function removeDropdownOpts(field) {
    var i;
    if (typeof field.options === 'undefined') {
      return;
    }
    for (i = field.options.length - 1; i >= 0; i--) {
      field.remove(i);
    }
  }

  /**
   * Is the box checked to use separate values?
   */
  function usingSeparateValues(fieldId) {
    return isChecked('separate_value_' + fieldId);
  }

  /**
   * Is the box checked to use images as options?
   */
  function imagesAsOptions(fieldId) {
    var checked = false,
      field = document.getElementsByName('field_options[image_options_' + fieldId + ']');
    for (var _i12 = 0; _i12 < field.length; _i12++) {
      if (field[_i12].checked) {
        checked = '0' !== field[_i12].value;
      }
    }

    /**
     * @since 5.0.04
     */
    return frmAdminBuild.hooks.applyFilters('frm_choice_field_images_as_options', checked, fieldId);
  }
  function showingLabelWithImage(fieldId) {
    var isShowing = !isChecked('hide_image_text_' + fieldId);

    /**
     * @since 5.0.04
     */
    return frmAdminBuild.hooks.applyFilters('frm_choice_field_showing_label_with_image', isShowing, fieldId);
  }
  function isChecked(id) {
    var field = document.getElementById(id);
    if (field === null) {
      return false;
    }
    return field.checked;
  }
  function checkUniqueOpt(targetInput) {
    var settingsContainer = targetInput.closest('.frm-single-settings');
    var fieldId = settingsContainer.getAttribute('data-fid');
    var areValuesSeparate = settingsContainer.querySelector('[name="field_options[separate_value_' + fieldId + ']"]').checked;
    if (areValuesSeparate && !targetInput.name.endsWith('[value]')) {
      return;
    }
    var container = document.getElementById('frm_field_' + fieldId + '_opts');
    var conflicts = Array.from(container.querySelectorAll('input[type="text"]')).filter(function (input) {
      return input.id !== targetInput.id && areValuesSeparate === input.name.endsWith('[value]') && input.value === targetInput.value;
    });
    if (conflicts.length) {
      /* translators: %s: The detected option value. */
      infoModal(sprintf(__('Duplicate option value "%s" detected', 'formidable'), purifyHtml(targetInput.value)));
    }
  }
  function getFieldValues() {
    /*jshint validthis:true */
    var isTaxonomy,
      val = this.value;
    if (val) {
      var parentIDs = this.parentNode.id.replace('frm_logic_', '').split('_');
      var fieldID = parentIDs[0];
      var metaKey = parentIDs[1];
      var valueField = document.getElementById('frm_field_id_' + val);
      var valueFieldType = valueField.getAttribute('data-ftype');
      var fill = document.getElementById('frm_show_selected_values_' + fieldID + '_' + metaKey);
      var optionName = 'field_options[hide_opt_' + fieldID + '][]';
      var optionID = 'frm_field_logic_opt_' + fieldID;
      var input = false;
      var showSelect = valueFieldType === 'select' || valueFieldType === 'checkbox' || valueFieldType === 'radio';
      var showText = valueFieldType === 'text' || valueFieldType === 'email' || valueFieldType === 'phone' || valueFieldType === 'url' || valueFieldType === 'number';
      if (showSelect) {
        isTaxonomy = document.getElementById('frm_has_hidden_options_' + val);
        if (isTaxonomy !== null) {
          // get the category options with ajax
          showSelect = false;
        }
      }
      if (showSelect || showText) {
        var comparison = document.querySelector("#frm_logic_".concat(fieldID, "_").concat(metaKey, " [name=\"field_options[hide_field_cond_").concat(fieldID, "][]\"]")).value;
        fill.innerHTML = '';
        var creatingValuesDropdown = showSelect && !['LIKE', 'not LIKE', 'LIKE%', '%LIKE'].includes(comparison);
        if (creatingValuesDropdown) {
          input = document.createElement('select');
        } else {
          input = document.createElement('input');
          input.type = 'text';
        }
        input.name = optionName;
        input.id = optionID + '_' + metaKey;
        fill.appendChild(input);
        if (creatingValuesDropdown) {
          var fillField = document.getElementById(input.id);
          fillDropdownOpts(fillField, {
            sourceID: val,
            placeholder: '',
            other: true
          });
        }
      } else {
        var thisType = this.getAttribute('data-type');
        var callback = function callback() {
          var event = new CustomEvent('frm_logic_options_loaded');
          event.frmData = {
            valueFieldType: valueFieldType,
            fieldID: fieldID,
            metaKey: metaKey
          };
          document.dispatchEvent(event);
        };
        frmGetFieldValues(val, fieldID, metaKey, thisType, undefined, callback);
      }
    }
  }
  function getFieldSelection() {
    /*jshint validthis:true */
    var formId = this.value;
    if (formId) {
      var fieldId = jQuery(this).closest('.frm-single-settings').data('fid');
      getTaxOrFieldSelection(formId, fieldId);
    }
  }
  function getTaxOrFieldSelection(formId, fieldId) {
    if (formId) {
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'frm_get_field_selection',
          field_id: fieldId,
          form_id: formId,
          nonce: frmGlobal.nonce
        },
        success: function success(msg) {
          var $selectedFields = jQuery('#frm_show_selected_fields_' + fieldId);
          $selectedFields.toggleClass('frm6', !msg.includes('frm-inline-message'));
          $selectedFields.html(msg).show();
        }
      });
    }
  }
  function updateFieldOrder() {
    var self = this;
    this.initOnceInAllInstances = function () {
      if ('undefined' !== typeof updateFieldOrder.prototype.orderFieldsObject) {
        return;
      }

      // It will store the order input fields ( input[name="field_options[field_order_{fieldId}]"] ).
      // It will help to reduce the DOM searches based on fieldId.
      // The same object data is used across all "updateFieldOrder" instances.
      updateFieldOrder.prototype.orderFieldsObject = {};

      // Get the Form group that will handle the fields settings.
      // Perform a single DOM search and use it across all "updateFieldOrder" instances.
      updateFieldOrder.prototype.fieldSettingsForm = document.getElementById('frm-end-form-marker').closest('form');
    };
    this.getFieldOrderInputById = function (fieldId, parent) {
      var field;
      var orderFieldsObject = updateFieldOrder.prototype.orderFieldsObject;
      var fieldSettingsForm = updateFieldOrder.prototype.fieldSettingsForm;
      if ('undefined' === typeof orderFieldsObject[fieldId]) {
        field = fieldSettingsForm.querySelector('input[name="field_options[field_order_' + fieldId + ']"]');
        if (null === field) {
          field = parent.querySelector('input[name="field_options[field_order_' + fieldId + ']"]');
        }
        orderFieldsObject[fieldId] = field;
        return field;
      }
      return orderFieldsObject[fieldId];
    };
    this.initOnceInAllInstances();
    renumberPageBreaks();
    return function () {
      var fieldId,
        field,
        currentOrder,
        newOrder,
        moveFieldsClass = new moveFieldSettings(),
        fields = jQuery('li.frm_field_box', jQuery('#frm-show-fields'));
      for (i = 0; i < fields.length; i++) {
        fieldId = fields[i].getAttribute('data-fid');
        field = self.getFieldOrderInputById(fieldId, fields[i]);

        // get current field order, make sure we don't get the "field" reference as the "field" value will get updated later.
        currentOrder = null !== field ? Object.assign({}, field.value)[0] : null;
        newOrder = i + 1;
        if (currentOrder != newOrder && null !== currentOrder) {
          field.value = newOrder;
          singleField = fields[i].querySelector('#frm-single-settings-' + fieldId);

          // add field that needs to be moved to "updateFieldOrder.prototype.fieldSettingsForm"
          moveFieldsClass.append(singleField);
          fieldUpdated();
        }
      }
      // move all appended fields
      moveFieldsClass.moveFields();
    }();
  }
  function toggleSectionHolder() {
    document.querySelectorAll('.start_divider').forEach(function (divider) {
      toggleOneSectionHolder(jQuery(divider));
    });
  }
  function toggleOneSectionHolder($section) {
    var noSectionFields, $rows, length, index, sectionHasFields;
    if (!$section.length) {
      return;
    }
    $rows = $section.find('ul.frm_sorting');
    sectionHasFields = false;
    length = $rows.length;
    for (index = 0; index < length; ++index) {
      if (0 !== getFieldsInRow(jQuery($rows.get(index))).length) {
        sectionHasFields = true;
        break;
      }
    }
    noSectionFields = $section.parent().children('.frm_no_section_fields').get(0);
    noSectionFields.classList.toggle('frm_block', !sectionHasFields);
  }
  function handleShowPasswordLiveUpdate() {
    frmDom.util.documentOn('change', '.frm_show_password_setting_input', function (event) {
      var fieldId = event.target.getAttribute('data-fid');
      var fieldEl = document.getElementById('frm_field_id_' + fieldId);
      if (!fieldEl) {
        return;
      }
      fieldEl.classList.toggle('frm_disabled_show_password', !event.target.checked);
    });
  }
  function slideDown() {
    /*jshint validthis:true */
    var id = jQuery(this).data('slidedown');
    var $thisId = jQuery(document.getElementById(id));
    if ($thisId.is(':hidden')) {
      $thisId.slideDown('fast');
      this.style.display = 'none';
    }
    return false;
  }
  function slideUp() {
    /*jshint validthis:true */
    var id = jQuery(this).data('slideup');
    var $thisId = jQuery(document.getElementById(id));
    $thisId.slideUp('fast');
    $thisId.siblings('a').show();
    return false;
  }
  function adjustVisibilityValuesForEveryoneValues(element, option) {
    if ('' === option.getAttribute('value')) {
      onEveryoneOptionSelected(jQuery(this));
    } else {
      unselectEveryoneOptionIfSelected(jQuery(this));
    }
  }
  function onEveryoneOptionSelected($select) {
    $select.val('');
    $select.next('.btn-group').find('.multiselect-container input[value!=""]').prop('checked', false);
  }
  function unselectEveryoneOptionIfSelected($select) {
    var selectedValues = $select.val(),
      index;
    if (selectedValues === null) {
      $select.next('.btn-group').find('.multiselect-container input[value=""]').prop('checked', true);
      onEveryoneOptionSelected($select);
      return;
    }
    index = selectedValues.indexOf('');
    if (index >= 0) {
      selectedValues.splice(index, 1);
      $select.val(selectedValues);
      $select.next('.btn-group').find('.multiselect-container input[value=""]').prop('checked', false);
    }
  }

  /**
   * Get rid of empty container that inserts extra space.
   */
  function hideEmptyEle() {
    jQuery('.frm-hide-empty').each(function () {
      if (jQuery(this).text().trim().length === 0) {
        jQuery(this).remove();
      }
    });
  }

  /* Change the classes in the builder */
  function changeFieldClass(field, setting) {
    var classes,
      replace,
      alignField,
      replaceWith = ' ' + setting.value,
      fieldId = field.getAttribute('data-fid');

    // Include classes from multiple settings.
    if (typeof fieldId !== 'undefined') {
      if (setting.classList.contains('field_options_align')) {
        replaceWith += ' ' + document.getElementById('frm_classes_' + fieldId).value;
      } else if (setting.classList.contains('frm_classes')) {
        alignField = document.getElementById('field_options_align_' + fieldId);
        if (alignField !== null) {
          replaceWith += ' ' + alignField.value;
        }
      }
    }
    replaceWith += ' ';

    // Allow for the column number dropdown.
    replaceWith = replaceWith.replace(' block ', ' vertical_radio ').replace(' inline ', ' horizontal_radio ');
    classes = field.className.split(' frmstart ')[1];
    classes = 0 === classes.indexOf('frmend ') ? '' : classes.split(' frmend ')[0];
    if (classes.trim() === '') {
      replace = ' frmstart  frmend ';
      if (-1 === field.className.indexOf(replace)) {
        replace = ' frmstart frmend ';
      }
      replaceWith = ' frmstart ' + replaceWith.trim() + ' frmend ';
    } else {
      replace = classes.trim();
      replaceWith = replaceWith.trim();
    }
    field.className = field.className.replace(replace, replaceWith);
  }
  function maybeShowInlineModal(e) {
    /*jshint validthis:true */
    e.preventDefault();
    showInlineModal(this, undefined, e);
  }
  function showInlineModal(icon, input, event) {
    var box = document.getElementById(icon.getAttribute('data-open')),
      container = jQuery(icon).closest('p,ul'),
      inputTrigger = typeof input !== 'undefined';
    if (container.hasClass('frm-open')) {
      container.removeClass('frm-open');
      box.classList.add('frm_hidden');
    } else {
      if (!inputTrigger) {
        input = getInputForIcon(icon);
      }
      if (input !== null) {
        if (!inputTrigger) {
          var key = event.key;
          if (key !== 'Enter' && key !== ' ') {
            input.focus();
          }
        }
        container.after(box);
        box.setAttribute('data-fills', input.id.replace('-proxy-input', ''));
        if (box.id.indexOf('frm-calc-box') === 0) {
          popCalcFields(box, true);
        }
      }
      container.addClass('frm-open');
      box.classList.remove('frm_hidden');

      /**
       * @since 6.4.1
       */
      wp.hooks.doAction('frm_show_inline_modal', box, icon);
    }
  }
  function dismissInlineModal(e) {
    /*jshint validthis:true */
    e.preventDefault();
    this.parentNode.classList.add('frm_hidden');
    jQuery('.frm-open [data-open="' + this.parentNode.id + '"]').closest('.frm-open').removeClass('frm-open');
  }

  /**
   * Close frm-modal-no-dismiss element when clicking outside of it
   *
   * @param {Event} event The click event
   */
  function closeModalOnOutsideClick(_ref3) {
    var target = _ref3.target;
    if (target.closest('.frm-inline-modal.frm-modal-no-dismiss') || target.closest('.frm-show-inline-modal') || target.closest('#frm_adv_info') || target.closest('.frm-token-proxy-input')) {
      return;
    }

    // Close all inline modals (without close button) that are not hidden
    document.querySelectorAll('.frm-inline-modal.frm-modal-no-dismiss:not(.frm_hidden)').forEach(function (modal) {
      modal.classList.add('frm_hidden');
      modal.previousElementSibling.classList.remove('frm-open');
    });
  }
  function changeInputtedValue() {
    /*jshint validthis:true */
    var i,
      action = this.getAttribute('data-frmchange').split(',');
    for (i = 0; i < action.length; i++) {
      if (action[i] === 'updateOption') {
        changeHiddenSeparateValue(this);
      } else if (action[i] === 'updateDefault') {
        changeDefaultRadioValue(this);
      } else if (action[i] === 'checkUniqueOpt') {
        checkUniqueOpt(this);
      } else {
        this.value = this.value[action[i]]();
      }
    }
  }

  /**
   * When the saved value is changed, update the default value radio.
   */
  function changeDefaultRadioValue(input) {
    var parentLi = getOptionParent(input),
      key = parentLi.getAttribute('data-optkey'),
      fieldId = getOptionFieldId(parentLi, key),
      defaultRadio = parentLi.querySelector('input[name="default_value_' + fieldId + '"]');
    if (defaultRadio !== null) {
      defaultRadio.value = input.value;
    }
  }

  /**
   * If separate values are not enabled, change the saved value when
   * the displayed value is changed.
   */
  function changeHiddenSeparateValue(input) {
    var savedVal,
      parentLi = getOptionParent(input),
      key = parentLi.getAttribute('data-optkey'),
      fieldId = getOptionFieldId(parentLi, key),
      sep = document.getElementById('separate_value_' + fieldId);
    if (sep !== null && sep.checked === false) {
      // If separate values are not turned on.
      savedVal = document.getElementById('field_key_' + fieldId + '-' + key);
      savedVal.value = input.value;
      changeDefaultRadioValue(savedVal);
    }
  }
  function getOptionParent(input) {
    var parentLi = input.parentNode;
    if (parentLi.tagName !== 'LI') {
      parentLi = parentLi.parentNode;
    }
    return parentLi;
  }
  function getOptionFieldId(li, key) {
    var liId = li.id;
    return liId.replace('frm_delete_field_', '').replace('-' + key + '_container', '');
  }
  function submitBuild() {
    /*jshint validthis:true */
    var $thisEle = this;
    if (showNameYourFormModal()) {
      return;
    }
    preFormSave(this);
    var $form = jQuery(builderForm);
    var v = JSON.stringify($form.serializeArray());
    jQuery(document.getElementById('frm_compact_fields')).val(v);
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_save_form',
        frm_compact_fields: v,
        nonce: frmGlobal.nonce
      },
      success: function success(msg) {
        afterFormSave($thisEle);
        var $postStuff = document.getElementById('post-body-content');
        var $html = document.createElement('div');
        $html.setAttribute('class', 'frm_updated_message');
        $html.innerHTML = msg;
        $postStuff.insertBefore($html, $postStuff.firstChild);
        reloadIfAddonActivatedAjaxSubmitOnly();
      },
      error: function error() {
        triggerSubmit(document.getElementById('frm_js_build_form'));
      }
    });
  }
  function triggerSubmit(form) {
    var button = form.ownerDocument.createElement('input');
    button.style.display = 'none';
    button.type = 'submit';
    form.appendChild(button).click();
    form.removeChild(button);
  }
  function triggerChange(element) {
    jQuery(element).trigger('change');
  }
  function submitNoAjax() {
    /*jshint validthis:true */
    var form;
    if (showNameYourFormModal()) {
      return;
    }
    preFormSave(this);
    form = jQuery(builderForm);
    jQuery(document.getElementById('frm_compact_fields')).val(JSON.stringify(form.serializeArray()));
    triggerSubmit(document.getElementById('frm_js_build_form'));
  }

  /**
   * Display a modal dialog for naming a new form template, if applicable.
   *
   * @return {boolean} True if the modal is successfully initialized and displayed; false otherwise.
   */
  function showNameYourFormModal() {
    // Exit early if the 'new_template' URL parameter is not set to 'true'
    if (!shouldShowNameYourFormNameModal()) {
      return false;
    }
    var modalWidget = initModal('#frm-form-templates-modal', '440px');
    if (!modalWidget) {
      return false;
    }

    // Set the vertical offset for the modal and open it
    offsetModalY(modalWidget, '72px');
    modalWidget.dialog('open');
    return true;
  }

  /**
   * Returns true if 'Name Your Form' modal should be displayed.
   *
   * @return {Boolean}
   */
  function shouldShowNameYourFormNameModal() {
    var _document$querySelect3;
    var formNameInput = document.getElementById('frm_form_name');
    if (formNameInput && formNameInput.value.trim() !== '') {
      return false;
    }
    return 'true' === urlParams.get('new_template') && ((_document$querySelect3 = document.querySelector('#frm_top_bar #frm_bs_dropdown .frm_bstooltip')) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.textContent.trim()) === frm_admin_js.noTitleText; // eslint-disable-line camelcase
  }

  /**
   * Manages event handling for the 'Name your form' modal.
   *
   * Attaches click and keydown event listeners to the save button and input field.
   *
   * @return {void}
   */
  function addFormNameModalEvents() {
    var saveFormNameButton = document.getElementById('frm-save-form-name-button');
    var newFormNameInput = document.getElementById('frm_new_form_name_input');

    // Attach click event listener
    onClickPreventDefault(saveFormNameButton, onSaveFormNameButton);

    // Attach keydown event listener
    newFormNameInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        onSaveFormNameButton.call(this, event);
      }
    });
  }

  /**
   * Handles the click event on the save form name button.
   *
   * @param {Event} event The click event object.
   * @return {void}
   */
  var onSaveFormNameButton = function onSaveFormNameButton(event) {
    var newFormName = document.getElementById('frm_new_form_name_input').value.trim();

    // Prepare FormData for the POST request
    var formData = new FormData();
    formData.append('form_id', urlParams.get('id'));
    formData.append('form_name', newFormName);

    // Perform the POST request
    doJsonPost('rename_form', formData).then(function (data) {
      // Remove the 'new_template' parameter from the URL and update the browser history
      urlParams.delete('new_template');
      currentURL.search = urlParams.toString();
      history.replaceState({}, '', currentURL.toString());
      if (null !== document.getElementById('frm_notification_settings')) {
        document.getElementById('frm_form_name').value = newFormName;
        document.getElementById('frm_form_key').value = data.form_key;
      }

      // Trigger the 'Save' button click using jQuery
      jQuery('#frm-publishing').find('.frm_button_submit').trigger('click');
    });
  };
  function preFormSave(b) {
    removeWPUnload();
    if (jQuery('form.inplace_form').length) {
      jQuery('.inplace_save, .postbox').trigger('click');
    }
    if (b.classList.contains('frm_button_submit')) {
      b.classList.add('frm_loading_form');
    } else {
      b.classList.add('frm_loading_button');
    }
    b.setAttribute('aria-busy', 'true');
    adjustFormatInputBeforeSave();
  }

  /**
   * Updates the format input based on the selected format type from dropdowns during the form save process.
   *
   * @since 6.9
   *
   * @return {void}
   */
  function adjustFormatInputBeforeSave() {
    var formatTypes = document.querySelectorAll('.frm_format_dropdown, .frm_phone_type_dropdown');
    var valueMap = {
      none: '',
      international: 'international',
      currency: 'currency',
      number: 'number'
    };
    formatTypes.forEach(function (formatType) {
      var value = formatType.value;
      if (value in valueMap) {
        var formatInput = document.getElementById("frm_format_".concat(formatType.dataset.fieldId));
        formatInput.value = valueMap[value];
      }
    });
  }
  function afterFormSave(button) {
    button.classList.remove('frm_loading_form');
    button.classList.remove('frm_loading_button');
    resetOptionTextDetails();
    fieldsUpdated = 0;
    button.setAttribute('aria-busy', 'false');
    setTimeout(function () {
      jQuery('.frm_updated_message').fadeOut('slow', function () {
        this.parentNode.removeChild(this);
      });
    }, 5000);
  }
  function initUpgradeModal() {
    var upgradePopup = __webpack_require__(/*! ./upgrade-popup */ "./js/src/admin/upgrade-popup.js");
    upgradePopup.initUpgradeModal();
  }
  function addOneClick(element, type, upgradeLabel) {
    var upgradePopup = __webpack_require__(/*! ./upgrade-popup */ "./js/src/admin/upgrade-popup.js");
    upgradePopup.addOneClick(element, type, upgradeLabel);
  }

  /**
   * Opens a basic modal with the given title and content.
   *
   * @param {Event} event The event object.
   * @return {void}
   */
  function showBasicModal(event) {
    var _event$target$dataset;
    var button = (_event$target$dataset = event.target.dataset) !== null && _event$target$dataset !== void 0 && _event$target$dataset.modalTitle ? event.target : event.target.closest('[data-modal-title]');
    if (!button) {
      return;
    }
    var _button$dataset = button.dataset,
      modalTitle = _button$dataset.modalTitle,
      modalContent = _button$dataset.modalContent;
    if (!modalTitle || !modalContent) {
      return;
    }
    event.preventDefault();
    frmDom.modal.maybeCreateModal('frmBasicModal', {
      title: modalTitle,
      content: div({
        className: 'inside',
        child: span(modalContent)
      })
    });
  }
  function getRequiredLicenseFromTrigger(element) {
    if (element.dataset.requires) {
      return element.dataset.requires;
    }
    return 'Pro';
  }
  function populateUpgradeTab(element) {
    var title = element.dataset.upgrade;
    var tab = element.getAttribute('href').replace('#', '');
    var container = document.querySelector('.frm_' + tab) || document.querySelector('.' + tab);
    if (!container) {
      return;
    }
    if (container.querySelector('.frm-upgrade-message')) {
      // Tab has already been populated.
      return;
    }
    var h2 = container.querySelector('h2');
    h2.style.borderBottom = 'none';

    /* translators: %s: Form Setting section name (ie Form Permissions, Form Scheduling). */
    h2.textContent = sprintf(__('%s are not installed', 'formidable'), title);
    container.classList.add('frmcenter');
    var upgradeModal = document.getElementById('frm_upgrade_modal');
    appendClonedModalElementToContainer('frm-oneclick');
    appendClonedModalElementToContainer('frm-addon-status');

    // Borrow the call to action from the Upgrade upgradeModal which should exist on the settings page (it is still used for other upgrades including Actions).
    var upgradeModalLink = upgradeModal.querySelector('.frm-upgrade-link');
    if (upgradeModalLink) {
      var upgradeButton;
      var upgradeActions = upgradeModalLink.closest('.frm-upgrade-modal-actions');
      if (upgradeActions) {
        upgradeActions = upgradeActions.cloneNode(true);
        upgradeButton = upgradeActions.querySelector('.frm-upgrade-link');
      } else {
        upgradeButton = upgradeModalLink.cloneNode(true);
      }
      var level = upgradeButton.querySelector('.license-level');
      if (level) {
        level.textContent = getRequiredLicenseFromTrigger(element);
      }
      container.appendChild(upgradeActions || upgradeButton);

      // Maybe append the secondary "Already purchased?" link from the upgradeModal as well.
      if (upgradeModalLink.nextElementSibling && upgradeModalLink.nextElementSibling.querySelector('.frm-link-secondary')) {
        container.appendChild(upgradeModalLink.nextElementSibling.cloneNode(true));
      }
      appendClonedModalElementToContainer('frm-oneclick-button');
    }
    appendClonedModalElementToContainer('frm-upgrade-message');
    var upgradeLabel = element.dataset.message;
    if (upgradeLabel === undefined) {
      upgradeLabel = element.dataset.upgrade;
    }
    addOneClick(element, 'tab', upgradeLabel);
    if (element.dataset.screenshot) {
      container.appendChild(getScreenshotWrapper(element.dataset.screenshot));
    }
    function appendClonedModalElementToContainer(className) {
      container.appendChild(upgradeModal.querySelector('.' + className).cloneNode(true));
    }
  }
  function getScreenshotWrapper(screenshot) {
    var folderUrl = frmGlobal.url + '/images/screenshots/';
    var wrapper = div({
      className: 'frm-settings-screenshot-wrapper',
      children: [getToolbar(), div({
        child: img({
          src: folderUrl + screenshot
        })
      })]
    });
    function getToolbar() {
      var children = getColorIcons();
      children.push(img({
        src: frmGlobal.url + '/images/tab.svg'
      }));
      return div({
        className: 'frm-settings-screenshot-toolbar',
        children: children
      });
    }
    function getColorIcons() {
      return ['#ED8181', '#EDE06A', '#80BE30'].map(function (color) {
        var circle = div({
          className: 'frm-minmax-icon'
        });
        circle.style.backgroundColor = color;
        return circle;
      });
    }
    return wrapper;
  }

  /* Form settings */

  function showInputIcon(parentClass) {
    if (typeof parentClass === 'undefined') {
      parentClass = '';
    }
    maybeAddFieldSelection(parentClass);
    jQuery(parentClass + ' .frm_has_shortcodes:not(.frm-with-right-icon) input,' + parentClass + ' .frm_has_shortcodes:not(.frm-with-right-icon) textarea').wrap('<span class="frm-with-right-icon"></span>').before('<svg class="frmsvg frm-show-box"><use href="#frm_more_horiz_solid_icon"/></svg>');
  }

  /**
   * For reverse compatibility. Check for fields that were
   * using the old sidebar.
   */
  function maybeAddFieldSelection(parentClass) {
    var i,
      missingClass = jQuery(parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_message, ' + parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_to, ' + parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_subject');
    for (i = 0; i < missingClass.length; i++) {
      missingClass[i].parentNode.classList.add('frm_has_shortcodes');
    }
  }
  function showSuccessOpt() {
    /*jshint validthis:true */
    var c = 'success';
    if (this.name === 'options[edit_action]') {
      c = 'edit';
    }
    var v = jQuery(this).val();
    jQuery('.' + c + '_action_box').hide();
    if (v === 'redirect') {
      jQuery('.' + c + '_action_redirect_box.' + c + '_action_box').fadeIn('slow');
    } else if (v === 'page') {
      jQuery('.' + c + '_action_page_box.' + c + '_action_box').fadeIn('slow');
    } else {
      jQuery('.' + c + '_action_message_box.' + c + '_action_box').fadeIn('slow');
    }
  }
  function copyFormAction(event) {
    if (waitForActionToLoadBeforeCopy(event.target)) {
      return;
    }
    var targetSettings = event.target.closest('.frm_form_action_settings');
    var wysiwygs = targetSettings.querySelectorAll('.wp-editor-area');
    if (wysiwygs.length) {
      // Temporary remove TinyMCE before cloning to avoid TinyMCE conflicts.
      wysiwygs.forEach(function (wysiwyg) {
        tinymce.EditorManager.execCommand('mceRemoveEditor', true, wysiwyg.id);
      });
    }
    var $action = jQuery(targetSettings).clone();
    var currentID = $action.attr('id').replace('frm_form_action_', '');
    var newID = newActionId(currentID);
    $action.find('.frm_action_id, .frm-btn-group').remove();
    $action.find('input[name$="[' + currentID + '][ID]"]').val('');
    $action.find('.widget-inside').hide();

    // the .html() gets original values, so they need to be set
    $action.find('input[type=text], textarea, input[type=number]').prop('defaultValue', function () {
      return this.value;
    });
    $action.find('input[type=checkbox], input[type=radio]').prop('defaultChecked', function () {
      return this.checked;
    });
    var rename = new RegExp('\\[' + currentID + '\\]', 'g');
    var reid = new RegExp('_' + currentID + '"', 'g');
    var reclass = new RegExp('-' + currentID + '"', 'g');
    var revalue = new RegExp('"' + currentID + '"', 'g'); // if a field id matches, this could cause trouble

    var html = $action.html().replace(rename, '[' + newID + ']').replace(reid, '_' + newID + '"');
    html = html.replace(reclass, '-' + newID + '"').replace(revalue, '"' + newID + '"');
    var newAction = div({
      id: 'frm_form_action_' + newID,
      className: $action.get(0).className
    });
    newAction.setAttribute('data-actionkey', newID);
    newAction.innerHTML = html;
    newAction.querySelectorAll('.wp-editor-wrap, .wp-editor-wrap *').forEach(function (element) {
      if ('string' === typeof element.className) {
        element.className = element.className.replace(currentID, newID);
      }
      element.id = element.id.replace(currentID, newID);
    });
    newAction.classList.remove('open');
    document.getElementById('frm_notification_settings').appendChild(newAction);
    if (wysiwygs.length) {
      // Re-initialize the original wysiwyg which was removed before cloning.
      wysiwygs.forEach(function (wysiwyg) {
        frmDom.wysiwyg.init(wysiwyg);
      });
      newAction.querySelectorAll('.wp-editor-area').forEach(function (wysiwyg) {
        frmDom.wysiwyg.init(wysiwyg);
      });
    }
    if (newAction.classList.contains('frm_single_on_submit_settings')) {
      var autocompleteInput = newAction.querySelector('input.frm-page-search');
      if (autocompleteInput) {
        initAutocomplete(newAction);
      }
    }
    initiateMultiselect();
    var hookName = 'frm_after_duplicate_action';
    wp.hooks.doAction(hookName, newAction);
  }
  function waitForActionToLoadBeforeCopy(element) {
    var $trigger = jQuery(element),
      $original = $trigger.closest('.frm_form_action_settings'),
      $inside = $original.find('.widget-inside'),
      $top;
    if ($inside.find('p, div, table').length) {
      return false;
    }
    $top = $original.find('.widget-top');
    $top.on('frm-action-loaded', function () {
      $trigger.trigger('click');
      $original.removeClass('open');
      $inside.hide();
    });
    $top.trigger('click');
    return true;
  }
  function newActionId(currentID) {
    var newID = parseInt(currentID, 10) + 11;
    var exists = document.getElementById('frm_form_action_' + newID);
    if (exists !== null) {
      newID++;
      newID = newActionId(newID);
    }
    return newID;
  }
  function addFormAction() {
    /*jshint validthis:true */
    var type = jQuery(this).data('actiontype');
    if (isAtLimitForActionType(type)) {
      return;
    }
    var actionId = getNewActionId();
    var formId = thisFormId;
    var placeholderSetting = document.createElement('div');
    placeholderSetting.classList.add('frm_single_' + type + '_settings');
    var actionsList = document.getElementById('frm_notification_settings');
    actionsList.appendChild(placeholderSetting);
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_add_form_action',
        type: type,
        list_id: actionId,
        form_id: formId,
        nonce: frmGlobal.nonce
      },
      success: handleAddFormActionSuccess
    });
    function handleAddFormActionSuccess(html) {
      fieldUpdated();
      placeholderSetting.remove();
      closeOpenActions();
      var newActionContainer = div();
      newActionContainer.innerHTML = html;
      var widgetTop = newActionContainer.querySelector('.widget-top');
      Array.from(newActionContainer.children).forEach(function (child) {
        return actionsList.appendChild(child);
      });
      jQuery('.frm_form_action_settings').fadeIn('slow');
      var newAction = document.getElementById('frm_form_action_' + actionId);
      newAction.classList.add('open');
      document.getElementById('post-body-content').scroll({
        top: newAction.offsetTop + 10,
        left: 0,
        behavior: 'smooth'
      });

      // Check if icon should be active
      checkActiveAction(type);
      showInputIcon('#frm_form_action_' + actionId);
      initiateMultiselect();
      initAutocomplete(newAction);
      if (widgetTop) {
        jQuery(widgetTop).trigger('frm-action-loaded');
      }

      /**
       * Fires after added a new form action.
       *
       * @since 5.5.4
       *
       * @param {HTMLElement} formAction Form action element.
       */
      frmAdminBuild.hooks.doAction('frm_added_form_action', newAction);
    }
  }
  function closeOpenActions() {
    document.querySelectorAll('.frm_form_action_settings.open').forEach(function (setting) {
      return setting.classList.remove('open');
    });
  }
  function toggleActionGroups() {
    /*jshint validthis:true */
    var actions = document.getElementById('frm_email_addon_menu').classList,
      search = document.getElementById('actions-search-input');
    if (actions.contains('frm-all-actions')) {
      actions.remove('frm-all-actions');
      actions.add('frm-limited-actions');
    } else {
      actions.add('frm-all-actions');
      actions.remove('frm-limited-actions');
    }

    // Reset search.
    search.value = '';
    triggerEvent(search, 'input');
  }
  function getNewActionId() {
    var actionSettings = document.querySelectorAll('.frm_form_action_settings'),
      len = getNewRowId(actionSettings, 'frm_form_action_');
    if (typeof document.getElementById('frm_form_action_' + len) !== 'undefined') {
      len = len + 100;
    }
    if (lastNewActionIdReturned >= len) {
      len = lastNewActionIdReturned + 1;
    }
    lastNewActionIdReturned = len;
    return len;
  }
  function clickAction(obj) {
    var $thisobj = jQuery(obj);
    if (obj.className.indexOf('selected') !== -1) {
      return;
    }
    if (obj.className.indexOf('edit_field_type_end_divider') !== -1 && $thisobj.closest('.edit_field_type_divider').hasClass('no_repeat_section')) {
      return;
    }
    deselectFields();
    $thisobj.addClass('selected');
    showFieldOptions(obj);
  }

  /**
   * When a field is selected, show the field settings in the sidebar.
   */
  function showFieldOptions(obj) {
    var _document$querySelect4;
    var i,
      singleField,
      fieldId = obj.getAttribute('data-fid'),
      fieldType = obj.getAttribute('data-type'),
      allFieldSettings = document.querySelectorAll('.frm-single-settings:not(.frm_hidden)');
    for (i = 0; i < allFieldSettings.length; i++) {
      allFieldSettings[i].classList.add('frm_hidden');
    }
    singleField = document.getElementById('frm-single-settings-' + fieldId);
    moveFieldSettings(singleField);
    if (fieldType && 'quantity' === fieldType) {
      popProductFields(jQuery(singleField).find('.frmjs_prod_field_opt')[0]);
    }

    // Scroll settings panel to top
    (_document$querySelect4 = document.querySelector('.frm-settings-panel.frm-scrollbar-wrapper')) === null || _document$querySelect4 === void 0 || _document$querySelect4.scrollTo({
      top: 0,
      behavior: 'instant'
    });
    singleField.classList.remove('frm_hidden');
    document.getElementById('frm-options-panel-tab').click();
    var editor = singleField.querySelector('.wp-editor-area');
    if (editor) {
      frmDom.wysiwyg.init(editor, {
        setupCallback: setupTinyMceEventHandlers
      });
    }
    wp.hooks.doAction('frmShowedFieldSettings', obj, singleField);
    maybeAddShortcodesModalTriggerIcon(fieldType, fieldId, singleField);
  }
  function maybeAddShortcodesModalTriggerIcon(fieldType, fieldId, singleField) {
    var _singleField$querySel;
    if (!shouldAddShortcodesModalTriggerIcon(fieldType)) {
      return;
    }
    var fieldSettingsSelector = '#frm-single-settings-' + fieldId;
    if (document.querySelector(fieldSettingsSelector + ' .frm-show-box')) {
      return;
    }
    (_singleField$querySel = singleField.querySelector('.wp-editor-container')) === null || _singleField$querySel === void 0 || _singleField$querySel.classList.add('frm_has_shortcodes');
    var wrapTextareaWithIconContainer = function wrapTextareaWithIconContainer() {
      var textareas = document.querySelectorAll(fieldSettingsSelector + ' .frm_has_shortcodes textarea');
      textareas.forEach(function (textarea) {
        var wrapperSpan = span({
          className: 'frm-with-right-icon'
        });
        textarea.parentNode.insertBefore(wrapperSpan, textarea);
        wrapperSpan.appendChild(createModalTriggerIcon());
        wrapperSpan.appendChild(textarea);
      });
    };
    var createModalTriggerIcon = function createModalTriggerIcon() {
      return frmDom.svg({
        href: '#frm_more_horiz_solid_icon',
        classList: ['frm-show-box']
      });
    };
    wrapTextareaWithIconContainer();
  }
  function shouldAddShortcodesModalTriggerIcon(fieldType) {
    var fieldsWithShortcodesBox = wp.hooks.applyFilters('frm_fields_with_shortcode_popup', ['html']);
    return fieldsWithShortcodesBox.includes(fieldType);
  }
  function setupTinyMceEventHandlers(editor) {
    editor.on('Change', function () {
      handleTinyMceChange(editor);
    });
  }
  function handleTinyMceChange(editor) {
    if (!isTinyMceActive() || tinyMCE.activeEditor.isHidden()) {
      return;
    }
    editor.targetElm.value = editor.getContent();
    jQuery(editor.targetElm).trigger('change');
  }
  function isTinyMceActive() {
    var activeSettings, wrapper;
    activeSettings = document.querySelector('.frm-single-settings:not(.frm_hidden)');
    if (!activeSettings) {
      return false;
    }
    wrapper = activeSettings.querySelector('.wp-editor-wrap');
    return null !== wrapper && wrapper.classList.contains('tmce-active');
  }

  /**
   * Move the settings to the sidebar the first time they are changed or selected.
   * Keep the end marker at the end of the form.
   */
  function moveFieldSettings(singleField) {
    var self = this;
    if (singleField === null) {
      // The field may have not been loaded yet via ajax.
      return;
    }
    this.fragment = document.createDocumentFragment();
    this.initOnceInAllInstances = function () {
      if ('undefined' !== typeof moveFieldSettings.prototype.endMarker) {
        return;
      }
      // perform a single search in the DOM and use it across all moveFieldSettings instances
      moveFieldSettings.prototype.endMarker = document.getElementById('frm-end-form-marker');
    };
    this.append = function (field) {
      var classname = null !== field ? field.parentElement.classList : '';
      if (null === field || !classname.contains('frm_field_box') && !classname.contains('divider_section_only')) {
        return;
      }
      self.fragment.appendChild(field);
    };
    this.moveFields = function () {
      builderForm.insertBefore(self.fragment, moveFieldSettings.prototype.endMarker);
    };
    this.initOnceInAllInstances();

    // Move the field if function is called as function with a singleField passed as arg.
    // In this particular case only 1 field is needed to be moved so the field will get instantly moved.
    // "singleField" may be undefined when it's called as a constructor instead of a function. Use the constructor to add multiple fields which are passed through "append" and move these all at once via "moveFields".
    if ('undefined' !== typeof singleField) {
      this.append(singleField);
      this.moveFields();
      return;
    }
    return {
      append: this.append,
      moveFields: this.moveFields
    };
  }
  function showEmailRow() {
    /*jshint validthis:true */
    var actionKey = jQuery(this).closest('.frm_form_action_settings').data('actionkey');
    var rowType = this.getAttribute('data-emailrow');
    jQuery('#frm_form_action_' + actionKey + ' .frm_' + rowType + '_row').fadeIn('slow');
    jQuery(this).fadeOut('slow');
  }
  function hideEmailRow() {
    /*jshint validthis:true */
    var actionBox = jQuery(this).closest('.frm_form_action_settings'),
      rowType = this.getAttribute('data-emailrow'),
      emailRowSelector = '.frm_' + rowType + '_row',
      emailButtonSelector = '.frm_' + rowType + '_button';
    jQuery(actionBox).find(emailButtonSelector).fadeIn('slow');
    jQuery(actionBox).find(emailRowSelector).fadeOut('slow', function () {
      jQuery(actionBox).find(emailRowSelector + ' input').val('');
    });
  }
  function showEmailWarning() {
    /*jshint validthis:true */
    var actionBox = jQuery(this).closest('.frm_form_action_settings'),
      emailRowSelector = '.frm_from_to_match_row',
      fromVal = actionBox.find('input[name$="[post_content][from]"]').val(),
      toVal = actionBox.find('input[name$="[post_content][email_to]"]').val();
    if (fromVal === toVal) {
      jQuery(actionBox).find(emailRowSelector).fadeIn('slow');
    } else {
      jQuery(actionBox).find(emailRowSelector).fadeOut('slow');
    }
  }
  function checkActiveAction(type) {
    var actionTriggers = document.querySelectorAll('.frm_' + type + '_action');
    if (isAtLimitForActionType(type)) {
      var addAlreadyUsedClass = getLimitForActionType(type) > 0;
      markActionTriggersInactive(actionTriggers, addAlreadyUsedClass);
      return;
    }
    markActionTriggersActive(actionTriggers);
  }
  function markActionTriggersActive(triggers) {
    triggers.forEach(function (trigger) {
      if (trigger.querySelector('.frm_show_upgrade')) {
        // Prevent disabled action becoming active.
        return;
      }
      trigger.classList.remove('frm_inactive_action', 'frm_already_used');
      trigger.classList.add('frm_active_action');
    });
  }
  function markActionTriggersInactive(triggers, addAlreadyUsedClass) {
    triggers.forEach(function (trigger) {
      trigger.classList.remove('frm_active_action');
      trigger.classList.add('frm_inactive_action');
      if (addAlreadyUsedClass) {
        trigger.classList.add('frm_already_used');
      }
    });
  }
  function isAtLimitForActionType(type) {
    var atLimit = getNumberOfActionsForType(type) >= getLimitForActionType(type);
    var hookName = 'frm_action_at_limit';
    var hookArgs = {
      type: type
    };
    atLimit = wp.hooks.applyFilters(hookName, atLimit, hookArgs);
    return atLimit;
  }
  function getLimitForActionType(type) {
    return parseInt(jQuery('.frm_' + type + '_action').data('limit'), 10);
  }
  function getNumberOfActionsForType(type) {
    return jQuery('.frm_single_' + type + '_settings').length;
  }
  function actionLimitMessage() {
    var message = frmAdminJs.only_one_action;
    var limit = this.dataset.limit;
    if ('undefined' !== typeof limit) {
      limit = parseInt(limit);
      if (limit > 1) {
        message = message.replace(1, limit).trim();
      } else {
        message += ' ' + frmAdminJs.edit_action_text;
      }
    }
    infoModal(message);
  }
  function addFormLogicRow() {
    /*jshint validthis:true */
    var id = jQuery(this).data('emailkey');
    var type = jQuery(this).closest('.frm_form_action_settings').find('.frm_action_name').val();
    var formId = document.getElementById('form_id').value;
    var logicRowsContainer = document.getElementById('frm_logic_row_' + id);
    var logicRows = logicRowsContainer.querySelectorAll('.frm_logic_row');
    var newRowID = getNewRowId(logicRows, 'frm_logic_' + id + '_');
    var placeholder = div({
      id: 'frm_logic_' + id + '_' + newRowID,
      className: 'frm_logic_row frm_hidden'
    });
    logicRowsContainer.appendChild(placeholder);
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_add_form_logic_row',
        email_id: id,
        form_id: formId,
        meta_name: newRowID,
        type: type,
        nonce: frmGlobal.nonce
      },
      success: function success(html) {
        jQuery(document.getElementById('logic_link_' + id)).fadeOut('slow', function () {
          placeholder.insertAdjacentHTML('beforebegin', html);
          placeholder.remove();

          // Show conditional logic options after "Add Conditional Logic" is clicked.
          jQuery(logicRowsContainer).parent('.frm_logic_rows').fadeIn('slow');
        });
      }
    });
    return false;
  }
  function checkDupPost() {
    /*jshint validthis:true */
    var postField = jQuery('select.frm_single_post_field');
    postField.css('border-color', '');
    var $t = this;
    var v = jQuery($t).val();
    if (v === '' || v === 'checkbox') {
      return false;
    }
    postField.each(function () {
      if (jQuery(this).val() === v && this.name !== $t.name) {
        this.style.borderColor = 'red';
        jQuery($t).val('');
        infoModal(frmAdminJs.field_already_used);
        return false;
      }
    });
  }
  function togglePostContent() {
    /*jshint validthis:true */
    var v = jQuery(this).val();
    if ('' === v) {
      jQuery('.frm_post_content_opt, select.frm_dyncontent_opt').hide().val('');
      jQuery('.frm_dyncontent_opt').hide();
    } else if ('post_content' === v) {
      jQuery('.frm_post_content_opt').show();
      jQuery('.frm_dyncontent_opt').hide();
      jQuery('select.frm_dyncontent_opt').val('');
    } else {
      jQuery('.frm_post_content_opt').hide().val('');
      jQuery('select.frm_dyncontent_opt, .frm_form_field.frm_dyncontent_opt').show();
    }
  }
  function fillDyncontent() {
    /*jshint validthis:true */
    var v = jQuery(this).val();
    var $dyn = jQuery(document.getElementById('frm_dyncontent'));
    if ('' === v || 'new' === v) {
      $dyn.val('');
      jQuery('.frm_dyncontent_opt').show();
    } else {
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'frm_display_get_content',
          id: v,
          nonce: frmGlobal.nonce
        },
        success: function success(val) {
          $dyn.val(val);
          jQuery('.frm_dyncontent_opt').show();
        }
      });
    }
  }
  function switchPostType() {
    /*jshint validthis:true */
    // update all rows of categories/taxonomies
    var curSelect,
      newSelect,
      catRows = document.getElementById('frm_posttax_rows').childNodes,
      postParentField = document.querySelector('.frm_post_parent_field'),
      postMenuOrderField = document.querySelector('.frm_post_menu_order_field'),
      postType = this.value;

    // Get new category/taxonomy options
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_replace_posttax_options',
        post_type: postType,
        nonce: frmGlobal.nonce
      },
      success: function success(html) {
        // Loop through each category row, and replace the first dropdown
        for (i = 0; i < catRows.length; i++) {
          // Check if current element is a div
          if (catRows[i].tagName !== 'DIV') {
            continue;
          }

          // Get current category select
          curSelect = catRows[i].getElementsByTagName('select')[0];

          // Set up new select
          newSelect = document.createElement('select');
          newSelect.innerHTML = html;
          newSelect.className = curSelect.className;
          newSelect.name = curSelect.name;

          // Replace the old select with the new select
          catRows[i].replaceChild(newSelect, curSelect);
        }
      }
    });

    // Get new post parent option.
    if (postParentField) {
      getActionOption(postParentField, postType, 'frm_get_post_parent_option', function (response, optName) {
        // The replaced string is declared in FrmProFormActionController::ajax_get_post_menu_order_option() in the pro version.
        postParentField.querySelector('.frm_post_parent_opt_wrapper').innerHTML = response.replaceAll('REPLACETHISNAME', optName);
        initAutocomplete(postParentField);
      });
    }
    if (postMenuOrderField) {
      getActionOption(postMenuOrderField, postType, 'frm_should_use_post_menu_order_option');
    }
  }
  function getActionOption(field, postType, action, successHandler) {
    var opt = field.querySelector('.frm_autocomplete_value_input') || field.querySelector('select'),
      optName = opt.getAttribute('name');
    jQuery.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: action,
        post_type: postType,
        _wpnonce: frmGlobal.nonce
      },
      success: function success(response) {
        if ('string' !== typeof response) {
          console.error(response);
          return;
        }
        if ('0' === response) {
          // This post type does not support this field.
          field.classList.add('frm_hidden');
          field.value = '';
          return;
        }
        field.classList.remove('frm_hidden');
        if ('function' === typeof successHandler) {
          successHandler(response, optName);
        }
      },
      error: function error(response) {
        return console.error(response);
      }
    });
  }
  function addPosttaxRow() {
    /*jshint validthis:true */
    addPostRow('tax', this);
  }
  function addPostmetaRow() {
    /*jshint validthis:true */
    addPostRow('meta', this);
  }
  function addPostRow(type, button) {
    var name,
      id = jQuery('input[name="id"]').val(),
      settings = jQuery(button).closest('.frm_form_action_settings'),
      key = settings.data('actionkey'),
      postType = settings.find('.frm_post_type').val(),
      metaName = 0,
      postTypeRows = document.querySelectorAll('.frm_post' + type + '_row');
    if (postTypeRows.length) {
      name = postTypeRows[postTypeRows.length - 1].id.replace('frm_post' + type + '_', '');
      if (isNumeric(name)) {
        metaName = 1 + parseInt(name, 10);
      } else {
        metaName = 1;
      }
    }
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_add_post' + type + '_row',
        form_id: id,
        meta_name: metaName,
        tax_key: metaName,
        post_type: postType,
        action_key: key,
        nonce: frmGlobal.nonce
      },
      success: function success(html) {
        var cfOpts, optIndex;
        jQuery(document.getElementById('frm_post' + type + '_rows')).append(html);
        jQuery('.frm_add_post' + type + '_row.button').hide();
        if (type === 'meta') {
          jQuery('.frm_name_value').show();
          cfOpts = document.querySelectorAll('.frm_toggle_cf_opts');
          for (optIndex = 0; optIndex < cfOpts.length - 1; ++optIndex) {
            cfOpts[optIndex].style.display = 'none';
          }
        } else if (type === 'tax') {
          jQuery('.frm_posttax_labels').show();
        }
      }
    });
  }
  function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
  }
  function changePosttaxRow() {
    /*jshint validthis:true */
    if (!jQuery(this).closest('.frm_posttax_row').find('.frm_posttax_opt_list').length) {
      return;
    }
    jQuery(this).closest('.frm_posttax_row').find('.frm_posttax_opt_list').html('<div class="spinner frm_spinner" style="display:block"></div>');
    var postType = jQuery(this).closest('.frm_form_action_settings').find('select[name$="[post_content][post_type]"]').val(),
      actionKey = jQuery(this).closest('.frm_form_action_settings').data('actionkey'),
      taxKey = jQuery(this).closest('.frm_posttax_row').attr('id').replace('frm_posttax_', ''),
      metaName = jQuery(this).val(),
      showExclude = jQuery(document.getElementById(taxKey + '_show_exclude')).is(':checked') ? 1 : 0,
      fieldId = jQuery('select[name$="[post_category][' + taxKey + '][field_id]"]').val(),
      id = jQuery('input[name="id"]').val();
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'frm_add_posttax_row',
        form_id: id,
        post_type: postType,
        tax_key: taxKey,
        action_key: actionKey,
        meta_name: metaName,
        field_id: fieldId,
        show_exclude: showExclude,
        nonce: frmGlobal.nonce
      },
      success: function success(html) {
        var $tax = jQuery(document.getElementById('frm_posttax_' + taxKey));
        $tax.replaceWith(html);
      }
    });
  }
  function toggleCfOpts() {
    /*jshint validthis:true */
    var row = jQuery(this).closest('.frm_postmeta_row');
    var cancel = row.find('.frm_cancelnew');
    var select = row.find('.frm_enternew');
    if (row.find('select.frm_cancelnew').is(':visible')) {
      cancel.hide();
      select.show();
    } else {
      cancel.show();
      select.hide();
    }
    row.find('input.frm_enternew, select.frm_cancelnew').val('');
    return false;
  }
  function toggleFormOpts() {
    /*jshint validthis:true */
    var changedOpt = jQuery(this);
    var val = changedOpt.val();
    if (changedOpt.attr('type') === 'checkbox') {
      if (this.checked === false) {
        val = '';
      }
    }
    var toggleClass = changedOpt.data('toggleclass');
    if (val === '') {
      jQuery('.' + toggleClass).hide();
    } else {
      jQuery('.' + toggleClass).show();
      jQuery('.hide_' + toggleClass + '_' + val).hide();
    }
  }
  function submitSettings() {
    if (showNameYourFormModal()) {
      return;
    }

    /*jshint validthis:true */
    preFormSave(this);
    triggerSubmit(document.querySelector('.frm_form_settings'));
  }

  /* Customization Panel */
  function insertCode(e) {
    /*jshint validthis:true */
    e.preventDefault();
    insertFieldCode(jQuery(this), this.getAttribute('data-code'));
    return false;
  }
  function insertFieldCode(element, variable) {
    var rich = false,
      elementId = element;
    if (_typeof(element) === 'object') {
      if (element.hasClass('frm_noallow')) {
        return;
      }
      elementId = jQuery(element).closest('[data-fills]').attr('data-fills');
      if (typeof elementId === 'undefined') {
        elementId = element.closest('div').attr('class');
        if (typeof elementId !== 'undefined') {
          elementId = elementId.split(' ')[1];
        }
      }
    }
    if (typeof elementId === 'undefined') {
      var active = document.activeElement;
      if (active.type === 'search') {
        // If the search field has focus, find the correct field.
        elementId = active.id.replace('-search-input', '');
        if (elementId.match(/\d/gi) === null) {
          active = jQuery('.frm-single-settings:visible .' + elementId);
          elementId = active.attr('id');
        }
      } else {
        elementId = active.id;
      }
    }
    if (elementId) {
      rich = jQuery('#wp-' + elementId + '-wrap.wp-editor-wrap').length > 0;
    }
    var contentBox = jQuery(document.getElementById(elementId));
    if (typeof element.attr('data-shortcode') === 'undefined' && (!contentBox.length || typeof contentBox.attr('data-shortcode') === 'undefined')) {
      // this helps to exclude those that don't want shortcode-like inserted content e.g. frm-pro's summary field
      var doShortcode = element.parents('ul.frm_code_list').attr('data-shortcode');
      if (doShortcode === 'undefined' || doShortcode !== 'no') {
        variable = '[' + variable + ']';
      }
    }
    if (rich) {
      wpActiveEditor = elementId;
    }
    if (!contentBox.length) {
      return false;
    }
    if (variable === '[default-html]' || variable === '[default-plain]') {
      var p = 0;
      if (variable === '[default-plain]') {
        p = 1;
      }
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'frm_get_default_html',
          form_id: jQuery('input[name="id"]').val(),
          plain_text: p,
          nonce: frmGlobal.nonce
        },
        elementId: elementId,
        success: function success(msg) {
          if (rich) {
            var _p = document.createElement('p');
            _p.innerText = msg;
            send_to_editor(_p.innerHTML);
          } else {
            insertContent(contentBox, msg);
          }
        }
      });
    } else {
      variable = maybeAddSanitizeUrlToShortcodeVariable(variable, element, contentBox);
      if (rich) {
        send_to_editor(variable);
      } else {
        insertContent(contentBox, variable);
      }
    }
    return false;
  }
  function maybeAddSanitizeUrlToShortcodeVariable(variable, element, contentBox) {
    if ('object' !== _typeof(element) || !(element instanceof jQuery) || 0 !== contentBox[0].id.indexOf('success_url_')) {
      return variable;
    }
    element = element[0];
    if (!element.closest('#frm-insert-fields-box')) {
      // Only add sanitize_url=1 to field shortcodes.
      return variable;
    }
    if (!element.parentNode.classList.contains('frm_insert_url')) {
      variable = variable.replace(']', ' sanitize_url=1]');
    }
    return variable;
  }
  function insertContent(contentBox, variable) {
    if (document.selection) {
      contentBox[0].focus();
      document.selection.createRange().text = variable;
    } else {
      obj = contentBox[0];
      var e = obj.selectionEnd;
      variable = maybeFormatInsertedContent(contentBox, variable, obj.selectionStart, e);
      obj.value = obj.value.substr(0, obj.selectionStart) + variable + obj.value.substr(obj.selectionEnd, obj.value.length);
      var _s = e + variable.length;
      maybeRemoveLayoutClasses(obj, variable);
      obj.focus();
      obj.setSelectionRange(_s, _s);
    }
    triggerChange(contentBox);
  }

  /**
   * When a layout class is added, remove any previous layout classes to avoid conflicts.
   * We only expect one layout class to exist for a given field.
   * For example, if a field has frm_half and we set it to frm_third, frm_half will be removed.
   *
   * @since 6.11
   *
   * @param {HTMLElement} obj
   * @param {string}      variable
   * @return {void}
   */
  function maybeRemoveLayoutClasses(obj, variable) {
    if (!obj.classList.contains('frm_classes') || !isALayoutClass(variable)) {
      return;
    }
    var removeClasses = obj.value.split(' ').filter(isALayoutClass);
    if (removeClasses.length) {
      obj.value = maybeRemoveClasses(obj.value, removeClasses, variable.trim());
    }
  }

  /**
   * Check if a given class is a layout class.
   *
   * @since 6.11
   *
   * @param {string} className
   * @return {boolean}
   */
  function isALayoutClass(className) {
    var layoutClasses = ['frm_half', 'frm_third', 'frm_two_thirds', 'frm_fourth', 'frm_three_fourths', 'frm_fifth', 'frm_sixth', 'frm2', 'frm3', 'frm4', 'frm6', 'frm8', 'frm9', 'frm10', 'frm12'];
    return layoutClasses.includes(className.trim());
  }

  /**
   * @since 6.11
   *
   * @param {string} beforeValue
   * @param {Array}  removeClasses
   * @param {string} variable
   * @return {string}
   */
  function maybeRemoveClasses(beforeValue, removeClasses, variable) {
    var currentClasses = beforeValue.split(' ').filter(function (currentClass) {
      currentClass = currentClass.trim();
      return currentClass.length && !removeClasses.includes(currentClass);
    });
    if (!currentClasses.includes(variable)) {
      currentClasses.push(variable);
    }
    return currentClasses.join(' ');
  }
  function maybeFormatInsertedContent(input, textToInsert, selectionStart, selectionEnd) {
    var separator = input.data('sep');
    if (undefined === separator) {
      return textToInsert;
    }
    var value = input.val();
    if (!value.trim().length) {
      return textToInsert;
    }
    var startPattern = new RegExp(separator + '\\s*$');
    var endPattern = new RegExp('^\\s*' + separator);
    if (value.substr(0, selectionStart).trim().length && false === startPattern.test(value.substr(0, selectionStart))) {
      textToInsert = separator + textToInsert;
    }
    if (value.substr(selectionEnd, value.length).trim().length && false === endPattern.test(value.substr(selectionEnd, value.length))) {
      textToInsert += separator;
    }
    return textToInsert;
  }
  function resetLogicBuilder() {
    /*jshint validthis:true */
    var id = document.getElementById('frm-id-condition'),
      key = document.getElementById('frm-key-condition');
    if (this.value === 'id') {
      id.classList.remove('frm_hidden');
      key.classList.add('frm_hidden');
      triggerEvent(key, 'change');
    } else {
      id.classList.add('frm_hidden');
      key.classList.remove('frm_hidden');
      triggerEvent(id, 'change');
    }
  }
  function setLogicExample() {
    var field,
      code,
      idKey = document.getElementById('frm-id-key-condition-id').checked ? 'frm-id-condition' : 'frm-key-condition',
      is = document.getElementById('frm-is-condition').value,
      text = document.getElementById('frm-text-condition').value,
      result = document.getElementById('frm-insert-condition');
    idKey = document.getElementById(idKey);
    field = idKey.options[idKey.selectedIndex].value;
    code = 'if ' + field + ' ' + is + '="' + text + '"]';
    result.setAttribute('data-code', code + frmAdminJs.conditional_text + '[/if ' + field);
    result.innerHTML = '[' + code + '[/if ' + field + ']';
  }

  /**
   * Gets data from href or xlink:href of the given element.
   *
   * @param {HTMLElement} element HTML element.
   *
   * @return {String}
   */
  function getSVGHref(element) {
    return element.getAttribute('href') || element.getAttributeNS('http://www.w3.org/1999/xlink', 'href');
  }
  function maybeShowModal(input) {
    var moreIcon;
    if (input.parentNode.parentNode.classList.contains('frm_has_shortcodes')) {
      hideShortcodes();
      moreIcon = getIconForInput(input);
      if (moreIcon.tagName === 'use') {
        moreIcon = moreIcon.firstElementChild;
        if (getSVGHref(moreIcon).indexOf('frm_close_icon') === -1) {
          showShortcodeBox(moreIcon, 'nofocus');
        }
      } else if (!moreIcon.classList.contains('frm_close_icon')) {
        showShortcodeBox(moreIcon, 'nofocus');
      }
    }
  }
  function showShortcodes(e) {
    /*jshint validthis:true */
    e.preventDefault();
    e.stopPropagation();
    showShortcodeBox(this);
  }

  /**
   * Handles 'change' event on the document.
   *
   * @since 6.16.3
   *
   * @param {Event} event
   * @return {void}
   */
  function handleBuilderChangeEvent(event) {
    maybeShowSaveAndReloadModal(event.target);
  }

  /**
   * Shows 'Save and Reload' modal if the target field's type is changed.
   *
   * @since 6.16.3
   *
   * @param {HTMLElement} target
   * @return {void}
   */
  function maybeShowSaveAndReloadModal(target) {
    var _document$querySelect5;
    if (!target.id.startsWith('field_options_type_')) {
      return;
    }
    var idParts = target.id.split('_');
    var fieldId = idParts.length && idParts[idParts.length - 1];
    if ((_document$querySelect5 = document.querySelector("#frm-single-settings-".concat(fieldId))) !== null && _document$querySelect5 !== void 0 && _document$querySelect5.classList.contains("frm-type-".concat(target.value))) {
      // Do not show modal if the field type is reverted back to the original type when builder is loaded.
      return;
    }
    showSaveAndReloadModal();
  }

  /**
   * Shows 'Save and Reload' modal with the given message.
   *
   * @since 6.16.3
   *
   * @param {string} message
   * @return {void}
   */
  function showSaveAndReloadModal(message) {
    if ('undefined' === typeof message) {
      message = __('You are changing the field type. Not all field settings will appear as expected until you reload the page. Would you like to reload the page now?', 'formidable');
    }
    frmDom.modal.maybeCreateModal('frmSaveAndReloadModal', {
      title: __('Save and Reload?', 'formidable'),
      content: getModalContent(),
      footer: getModalFooter()
    });
    function getModalContent() {
      var modalContent = div(message);
      modalContent.style.padding = 'var(--gap-md)';
      return modalContent;
    }
    function getModalFooter() {
      var continueButton = frmDom.modal.footerButton({
        text: __('Save and Reload', 'formidable'),
        buttonType: 'primary'
      });
      onClickPreventDefault(continueButton, function () {
        saveAndReloadFormBuilder();
      });
      var cancelButton = frmDom.modal.footerButton({
        text: __('Cancel', 'formidable'),
        buttonType: 'cancel'
      });
      cancelButton.classList.add('dismiss');
      return frmDom.div({
        children: [cancelButton, continueButton]
      });
    }
  }
  function updateShortcodesPopupPosition(target) {
    var moreIcon;
    if (target instanceof Event) {
      var useElements = document.querySelectorAll('.frm-single-settings .frm-show-box.frmsvg use');
      var openTrigger = Array.from(useElements).find(function (use) {
        return use.getAttribute('href') === '#frm_close_icon';
      });
      if ('undefined' === typeof openTrigger) {
        return;
      }
      moreIcon = openTrigger.parentElement;
    } else {
      moreIcon = target;
    }
    var moreIconPosition = moreIcon.getBoundingClientRect();
    var shortCodesPopup = document.getElementById('frm_adv_info');
    var parentPos = shortCodesPopup.parentElement.getBoundingClientRect();
    shortCodesPopup.style.top = moreIconPosition.top - parentPos.top + 32 + 'px';
    shortCodesPopup.style.left = moreIconPosition.left - parentPos.left - 280 + 'px';
  }
  function showShortcodeBox(moreIcon, shouldFocus) {
    var input = getInputForIcon(moreIcon),
      box = document.getElementById('frm_adv_info'),
      classes = moreIcon.className;
    if (moreIcon.tagName === 'svg') {
      moreIcon = moreIcon.firstElementChild;
    }
    if (moreIcon.tagName === 'use') {
      classes = getSVGHref(moreIcon);
    }
    if (classes.indexOf('frm_close_icon') !== -1) {
      hideShortcodes(box);
    } else {
      updateShortcodesPopupPosition(moreIcon);
      jQuery('.frm_code_list a').removeClass('frm_noallow');
      if (input.classList.contains('frm_not_email_to')) {
        jQuery('#frm-insert-fields-box .frm_code_list li:not(.show_frm_not_email_to) a').addClass('frm_noallow');
      } else if (input.classList.contains('frm_not_email_subject')) {
        jQuery('.frm_code_list li.hide_frm_not_email_subject a').addClass('frm_noallow');
      }
      box.setAttribute('data-fills', input.id);
      box.style.display = 'block';
      if (moreIcon.tagName === 'use') {
        if (moreIcon.hasAttributeNS('http://www.w3.org/1999/xlink', 'href')) {
          moreIcon.setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#frm_close_icon');
        } else {
          var newMoreIcon = document.createElementNS('http://www.w3.org/2000/svg', 'use');
          newMoreIcon.setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#frm_close_icon');
          moreIcon.parentNode.replaceChild(newMoreIcon, moreIcon);
        }
      } else {
        moreIcon.className = classes.replace('frm_more_horiz_solid_icon', 'frm_close_icon');
      }
      if (shouldFocus !== 'nofocus') {
        if ('none' !== input.style.display) {
          input.focus();
        } else {
          jQuery(tinymce.get(input.id)).trigger('focus');
        }
      }
      showOrHideContextualShortcodes(input);
    }
  }

  /**
   * Returns true if a shortcode could be shown in the search result.
   *
   * @since 6.16.3
   *
   * @param {HTMLElement} item
   * @return {Boolean}
   */
  function checkContextualShortcode(item) {
    if (frmAdminJs.contextualShortcodes.length === 0) {
      return true;
    }
    return !isContextualShortcode(item) || canShowContextualShortcode(item);
  }

  /**
   * Returns true if a shortcode is contextual to fields.
   *
   * @since 6.16.3
   *
   * @param {HTMLElement} item
   * @return {Boolean}
   */
  function isContextualShortcode(item) {
    var anchor = item.querySelector('a');
    if (!anchor) {
      return false;
    }
    var shortcode = anchor.dataset.code;
    return frmAdminJs.contextualShortcodes.address.includes(shortcode) || frmAdminJs.contextualShortcodes.body.includes(shortcode);
  }

  /**
   * @since 6.16.3
   *
   * @param {HTMLElement} item
   * @return {Boolean}
   */
  function canShowContextualShortcode(item) {
    var shortcode = item.querySelector('a').dataset.code;
    var inputId = document.getElementById('frm_adv_info').dataset.fills;
    var input = document.getElementById(inputId);
    var contextualShortcodes = frmAdminJs.contextualShortcodes;
    if (contextualShortcodes.address.includes(shortcode)) {
      return input.matches(contextualShortcodes.addressSelector);
    }
    return input.matches(contextualShortcodes.bodySelector);
  }

  /**
   * @since 6.16.3
   *
   * @param {HTMLElement} input
   * @return {void}
   */
  function showOrHideContextualShortcodes(input) {
    ['address', 'body'].forEach(function (type) {
      toggleContextualShortcodes(input, type);
    });
  }

  /**
   * @since 6.16.3
   *
   * @param {HTMLElement} input
   * @param {string}      type
   *
   * @return {void}
   */
  function toggleContextualShortcodes(input, type) {
    var selector, contextualShortcodes;
    selector = frmAdminJs.contextualShortcodes[type + 'Selector'];
    contextualShortcodes = frmAdminJs.contextualShortcodes[type];
    var shouldShowShortcodes = input.matches(selector);
    var _iterator = _createForOfIteratorHelper(contextualShortcodes),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var _document$querySelect6;
        var shortcode = _step.value;
        var shortcodeLi = (_document$querySelect6 = document.querySelector('#frm-adv-info-tab .frm_code_list [data-code="' + shortcode + '"]')) === null || _document$querySelect6 === void 0 ? void 0 : _document$querySelect6.closest('li');
        shortcodeLi === null || shortcodeLi === void 0 || shortcodeLi.classList.toggle('frm_hidden', !shouldShowShortcodes);
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
  }

  /**
   * Returns shortcodes that are contextual to the current input field.
   *
   * @since 6.16.3
   *
   * @return {Array}
   */
  function getContextualShortcodes() {
    var _document$getElementB4;
    var contextualShortcodes = (_document$getElementB4 = document.getElementById('frm_adv_info')) === null || _document$getElementB4 === void 0 ? void 0 : _document$getElementB4.dataset.contextualShortcodes;
    if (!contextualShortcodes) {
      return [];
    }
    contextualShortcodes = JSON.parse(contextualShortcodes);
    contextualShortcodes.addressSelector = '[id^=email_to], [id^=from_], [id^=cc], [id^=bcc]';
    contextualShortcodes.bodySelector = '[id^=email_message_]';
    return contextualShortcodes;
  }
  function fieldUpdated() {
    if (!fieldsUpdated) {
      fieldsUpdated = 1;
      window.addEventListener('beforeunload', confirmExit);
    }
  }
  function buildSubmittedNoAjax() {
    // set fieldsUpdated to 0 to avoid the unsaved changes pop up
    fieldsUpdated = 0;
  }
  function settingsSubmitted() {
    // set fieldsUpdated to 0 to avoid the unsaved changes pop up
    fieldsUpdated = 0;
  }
  function resetFieldsUpdated() {
    fieldsUpdated = 0;
  }
  function reloadIfAddonActivatedAjaxSubmitOnly() {
    var submitButton = document.getElementById('frm_submit_side_top');
    if (submitButton.hasAttribute('data-new-addon-installed') && 'true' === submitButton.getAttribute('data-new-addon-installed')) {
      submitButton.removeAttribute('data-new-addon-installed');
      window.location.reload();
    }
  }
  function saveAndReloadFormBuilder() {
    var submitButton = document.getElementById('frm_submit_side_top');
    if (submitButton.classList.contains('frm_submit_ajax')) {
      submitButton.setAttribute('data-new-addon-installed', true);
    }
    submitButton.click();
  }
  function confirmExit(event) {
    if (fieldsUpdated) {
      event.preventDefault();
      event.returnValue = '';
    }
  }
  function offsetModalY($modal, amount) {
    var position = {
      my: 'top',
      at: 'top+' + amount,
      of: window
    };
    $modal.dialog('option', 'position', position);
  }

  /**
   * Get the input box for the selected icon or calculation field.
   *
   * @param {Element} moreIcon The icon element
   * @return {Element} The associated input or textarea
   */
  function getInputForIcon(moreIcon) {
    if (moreIcon.classList.contains('frm-input-icon')) {
      return moreIcon.previousElementSibling;
    }

    // For regular fields
    var input = moreIcon.nextElementSibling;
    while (input !== null && (input.tagName !== 'INPUT' && input.tagName !== 'TEXTAREA' || input.classList.contains('frm-token-input-field'))) {
      input = getInputForIcon(input);
    }

    // For calculation fields
    if (!input) {
      var _moreIcon$closest;
      input = (_moreIcon$closest = moreIcon.closest('.frm-field-formula')) === null || _moreIcon$closest === void 0 ? void 0 : _moreIcon$closest.querySelector('.frm-calc-field');
    }
    return input;
  }

  /**
   * Get the ... icon for the selected input box.
   */
  function getIconForInput(input) {
    var _input$nextElementSib;
    if ((_input$nextElementSib = input.nextElementSibling) !== null && _input$nextElementSib !== void 0 && _input$nextElementSib.classList.contains('frm-input-icon')) {
      return input.nextElementSibling;
    }
    var moreIcon = input.previousElementSibling;
    while (moreIcon !== null && moreIcon.tagName !== 'I' && moreIcon.tagName !== 'svg') {
      moreIcon = getIconForInput(moreIcon);
    }
    return moreIcon;
  }
  function hideShortcodes(box) {
    var i, u, closeIcons, closeSvg;
    if (typeof box === 'undefined') {
      box = document.getElementById('frm_adv_info');
      if (box === null) {
        return;
      }
    }
    if (document.getElementById('frm_dyncontent') !== null) {
      // Don't run when in the sidebar.
      return;
    }
    box.style.display = 'none';
    closeIcons = document.querySelectorAll('.frm-show-box.frm_close_icon');
    for (i = 0; i < closeIcons.length; i++) {
      closeIcons[i].classList.remove('frm_close_icon');
      closeIcons[i].classList.add('frm_more_horiz_solid_icon');
    }
    closeSvg = document.querySelectorAll('.frm_has_shortcodes use');
    for (u = 0; u < closeSvg.length; u++) {
      if (getSVGHref(closeSvg[u]) === '#frm_close_icon') {
        if (closeSvg[u].closest('.frm_remove_field')) {
          // Don't change the icon for the email fields remove button.
          continue;
        }
        closeSvg[u].setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#frm_more_horiz_solid_icon');
      }
    }
  }
  function toggleAllowedHTML(input) {
    var b,
      id = input.id;
    if (typeof id === 'undefined' || id.indexOf('-search-input') !== -1) {
      return;
    }
    jQuery('#frm-adv-info-tab').attr('data-fills', id.trim());
    if (input.classList.contains('field_custom_html')) {
      id = 'field_custom_html';
    }
    b = ['after_html', 'before_html', 'submit_html', 'field_custom_html'];
    if (jQuery.inArray(id, b) >= 0) {
      jQuery('.frm_code_list li:not(.show_' + id + ')').addClass('frm_hidden');
      jQuery('.frm_code_list li.show_' + id).removeClass('frm_hidden');
    }
  }
  function toggleKeyID(switchTo, e) {
    e.stopPropagation();
    jQuery('.frm_code_list .frmids, .frm_code_list .frmkeys').addClass('frm_hidden');
    jQuery('.frm_code_list .' + switchTo).removeClass('frm_hidden');
    jQuery('.frmids, .frmkeys').removeClass('current');
    jQuery('.' + switchTo).addClass('current');
  }
  function onActionLoaded(event) {
    var settings = event.target.closest('.frm_form_action_settings');
    if (settings && (settings.classList.contains('frm_single_email_settings') || settings.classList.contains('frm_single_on_submit_settings'))) {
      initWysiwygOnActionLoaded(settings);
    }
  }
  function initWysiwygOnActionLoaded(settings) {
    settings.querySelectorAll('.wp-editor-area').forEach(function (wysiwyg) {
      frmDom.wysiwyg.init(wysiwyg, {
        height: 160,
        addFocusEvents: true
      });
    });
  }

  /* Global settings page */
  function loadSettingsTab(anchor) {
    var holder = anchor.replace('#', '');
    var holderContainer = jQuery('.frm_' + holder + '_ajax');
    if (holderContainer.length) {
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'frm_settings_tab',
          tab: holder.replace('_settings', ''),
          nonce: frmGlobal.nonce
        },
        success: function success(html) {
          holderContainer.replaceWith(html);
        }
      });
    }
  }
  function uninstallNow() {
    /*jshint validthis:true */
    if (confirmLinkClick(this) === true) {
      jQuery('.frm_uninstall .frm-wait').css('visibility', 'visible');
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: 'action=frm_uninstall&nonce=' + frmGlobal.nonce,
        success: function success(msg) {
          jQuery('.frm_uninstall').fadeOut('slow');
          window.location = msg;
        }
      });
    }
    return false;
  }
  function saveAddonLicense() {
    /*jshint validthis:true */
    var button = jQuery(this);
    var buttonName = this.name;
    var pluginSlug = this.getAttribute('data-plugin');
    var action = buttonName.replace('edd_' + pluginSlug + '_license_', '');
    var license = document.getElementById('edd_' + pluginSlug + '_license_key').value;
    button.get(0).disabled = true;
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      dataType: 'json',
      data: {
        action: 'frm_addon_' + action,
        license: license,
        plugin: pluginSlug,
        nonce: frmGlobal.nonce
      },
      success: function success(msg) {
        button.get(0).disabled = false;
        var thisRow = button.closest('.edd_frm_license_row');
        if (action === 'deactivate') {
          license = '';
          document.getElementById('edd_' + pluginSlug + '_license_key').value = '';
        }
        thisRow.find('.edd_frm_license').html(license);
        var eddWrapper = button.get(0).closest('.frm_form_field');
        var actionIsSuccess = msg.success === true;
        eddWrapper.querySelector(".frm_icon_font.frm_action_success").classList.toggle('frm_hidden', !actionIsSuccess || action === 'deactivate');
        eddWrapper.querySelector(".frm_icon_font.frm_action_error").classList.toggle('frm_hidden', actionIsSuccess);
        var messageBox = thisRow.find('.frm_license_msg');
        messageBox.html(msg.message);
        if (msg.message !== '') {
          setTimeout(function () {
            messageBox.html('');
            thisRow.find('.frm_icon_font').addClass('frm_hidden');
            if (actionIsSuccess) {
              var actionIsActivate = action === 'activate';
              thisRow.get(0).querySelector('.edd_frm_unauthorized').classList.toggle('frm_hidden', actionIsActivate);
              thisRow.get(0).querySelector('.edd_frm_authorized').classList.toggle('frm_hidden', !actionIsActivate);
            }
          }, 2000);
        }
      }
    });
  }

  /* Import/Export page */

  function startFormMigration(event) {
    event.preventDefault();
    var checkedBoxes = jQuery(event.target).find('input:checked');
    if (!checkedBoxes.length) {
      return;
    }
    var ids = [];
    checkedBoxes.each(function (i) {
      ids[i] = this.value;
    });

    // Begin the import process.
    importForms(ids, event.target);
  }

  /**
   * Begins the process of importing the forms.
   */
  function importForms(forms, targetForm) {
    // Hide the form select section.
    var $form = jQuery(targetForm),
      $processSettings = $form.next('.frm-importer-process');

    // Display total number of forms we have to import.
    $processSettings.find('.form-total').text(forms.length);
    $processSettings.find('.form-current').text('1');
    $form.hide();

    // Show processing status.
    // '.process-completed' might have been shown earlier during a previous import, so hide now.
    $processSettings.find('.process-completed').hide();
    $processSettings.show();

    // Create global import queue.
    s.importQueue = forms;
    s.imported = 0;

    // Import the first form in the queue.
    importForm($processSettings);
  }

  /**
   * Imports a single form from the import queue.
   */
  function importForm($processSettings) {
    var formID = s.importQueue[0],
      provider = jQuery('#welcome-panel').find('input[name="slug"]').val(),
      data = {
        action: 'frm_import_' + provider,
        form_id: formID,
        nonce: frmGlobal.nonce
      };

    // Trigger AJAX import for this form.
    jQuery.post(ajaxurl, data, function (res) {
      if (res.success) {
        var statusUpdate;
        if (res.data.error) {
          statusUpdate = '<p>' + res.data.name + ': ' + res.data.msg + '</p>';
        } else {
          statusUpdate = '<p>Imported <a href="' + res.data.link + '" target="_blank">' + res.data.name + '</a></p>';
        }
        $processSettings.find('.status').prepend(statusUpdate);
        $processSettings.find('.status').show();

        // Remove this form ID from the queue.
        s.importQueue = jQuery.grep(s.importQueue, function (value) {
          return value != formID;
        });
        s.imported++;
        if (s.importQueue.length === 0) {
          $processSettings.find('.process-count').hide();
          $processSettings.find('.forms-completed').text(s.imported);
          $processSettings.find('.process-completed').show();
        } else {
          // Import next form in the queue.
          $processSettings.find('.form-current').text(s.imported + 1);
          importForm($processSettings);
        }
      }
    });
  }
  function validateExport(e) {
    /*jshint validthis:true */
    e.preventDefault();
    var s = false;
    var $exportForms = jQuery('input[name="frm_export_forms[]"]');
    if (!jQuery('input[name="frm_export_forms[]"]:checked').val()) {
      $exportForms.closest('.frm-table-box').addClass('frm_blank_field');
      s = 'stop';
    }
    var $exportType = jQuery('input[name="type[]"]');
    if (!jQuery('input[name="type[]"]:checked').val() && $exportType.attr('type') === 'checkbox') {
      $exportType.closest('p').addClass('frm_blank_field');
      s = 'stop';
    }
    if (s === 'stop') {
      return false;
    }
    e.stopPropagation();
    this.submit();
  }
  function removeExportError() {
    /*jshint validthis:true */
    var t = jQuery(this).closest('.frm_blank_field');
    if (typeof t === 'undefined') {
      return;
    }
    var $thisName = this.name;
    if ($thisName === 'type[]' && jQuery('input[name="type[]"]:checked').val()) {
      t.removeClass('frm_blank_field');
    } else if ($thisName === 'frm_export_forms[]' && jQuery(this).val()) {
      t.removeClass('frm_blank_field');
    }
  }
  function checkCSVExtension() {
    /*jshint validthis:true */
    var f = jQuery(this).val();
    var re = /\.csv$/i;
    if (f.match(re) !== null) {
      jQuery('.show_csv').fadeIn();
    } else {
      jQuery('.show_csv').fadeOut();
    }
  }
  function getExportOption() {
    var exportFormatSelect = document.querySelector('select[name="format"]');
    if (exportFormatSelect) {
      return exportFormatSelect.value;
    }
    return '';
  }
  function exportTypeChanged(event) {
    var value = event.target.value;
    showOrHideRepeaters(value);
    checkExportTypes.call(event.target);
    checkSelectedAllFormsCheckbox(value);
  }
  function checkSelectedAllFormsCheckbox(exportType) {
    var selectAllCheckbox = document.getElementById('frm-export-select-all');
    if (exportType === 'csv') {
      selectAllCheckbox.checked = false;
      selectAllCheckbox.disabled = true;
    } else {
      selectAllCheckbox.disabled = false;
    }
  }
  function checkExportTypes() {
    /*jshint validthis:true */
    var $dropdown = jQuery(this);
    var $selected = $dropdown.find(':selected');
    var s = $selected.data('support');
    var multiple = s.indexOf('|');
    jQuery('input[name="type[]"]').each(function () {
      this.checked = false;
      if (s.indexOf(this.value) >= 0) {
        this.disabled = false;
        if (multiple === -1) {
          this.checked = true;
        }
      } else {
        this.disabled = true;
      }
    });
    if ($dropdown.val() === 'csv') {
      jQuery('.csv_opts').show();
      jQuery('.xml_opts').hide();
    } else {
      jQuery('.csv_opts').hide();
      jQuery('.xml_opts').show();
    }
    var c = $selected.data('count');
    var exportField = jQuery('input[name="frm_export_forms[]"]');
    if (c === 'single') {
      exportField.prop('multiple', false);
      exportField.prop('checked', false);
    } else {
      exportField.prop('multiple', true);
      exportField.prop('disabled', false);
    }
    $dropdown.trigger('change');
  }
  function showOrHideRepeaters(exportOption) {
    if (exportOption === '') {
      return;
    }
    var repeaters = document.querySelectorAll('.frm-is-repeater');
    if (!repeaters.length) {
      return;
    }
    if (exportOption === 'csv') {
      repeaters.forEach(function (form) {
        form.classList.remove('frm_hidden');
      });
    } else {
      repeaters.forEach(function (form) {
        form.classList.add('frm_hidden');
      });
    }
    searchContent.call(document.querySelector('.frm-auto-search'));
  }
  function preventMultipleExport() {
    var type = jQuery('select[name=format]'),
      selected = type.find(':selected'),
      count = selected.data('count'),
      exportField = jQuery('input[name="frm_export_forms[]"]');
    if (count === 'single') {
      // Disable all other fields to prevent multiple selections.
      if (this.checked) {
        exportField.prop('disabled', true);
        this.removeAttribute('disabled');
      } else {
        exportField.prop('disabled', false);
      }
    } else {
      exportField.prop('disabled', false);
    }
  }
  function initiateMultiselect() {
    jQuery('.frm_multiselect').hide().each(frmDom.bootstrap.multiselect.init);
  }

  /* Addons page */
  function installMultipleAddons(e) {
    e.preventDefault();
    toggleAddonState(this, 'frm_multiple_addons');
  }
  function activateAddon(e) {
    e.preventDefault();
    toggleAddonState(this, 'frm_activate_addon');
  }
  function installAddon(e) {
    e.preventDefault();
    toggleAddonState(this, 'frm_install_addon');
  }
  function toggleAddonState(clicked, action) {
    var addonState = __webpack_require__(/*! ./addon-state */ "./js/src/admin/addon-state.js");
    addonState.toggleAddonState(clicked, action);
  }
  function installAddonWithCreds(e) {
    // Prevent the default action, let the user know we are attempting to install again and go with it.
    e.preventDefault();

    // Now let's make another Ajax request once the user has submitted their credentials.
    var proceed = jQuery(this);
    var el = proceed.parent().parent();
    var plugin = proceed.attr('rel');
    proceed.addClass('frm_loading_button');
    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      async: true,
      cache: false,
      dataType: 'json',
      data: {
        action: 'frm_install_addon',
        nonce: frmAdminJs.nonce,
        plugin: plugin,
        hostname: el.find('#hostname').val(),
        username: el.find('#username').val(),
        password: el.find('#password').val()
      },
      success: function success(response) {
        var _response$data, _response;
        response = (_response$data = (_response = response) === null || _response === void 0 ? void 0 : _response.data) !== null && _response$data !== void 0 ? _response$data : response;
        var error = extractErrorFromAddOnResponse(response);
        if (error) {
          addonError(error, el, proceed);
          return;
        }
        afterAddonInstall(response, proceed, message, el);
      },
      error: function error() {
        proceed.removeClass('frm_loading_button');
      }
    });
  }
  function afterAddonInstall(response, button, message, el, saveAndReload) {
    var action = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 'frm_activate_addon';
    var addonState = __webpack_require__(/*! ./addon-state */ "./js/src/admin/addon-state.js");
    addonState.afterAddonInstall(response, button, message, el, saveAndReload, action);
  }
  function extractErrorFromAddOnResponse(response) {
    var addonState = __webpack_require__(/*! ./addon-state */ "./js/src/admin/addon-state.js");
    return addonState.extractErrorFromAddOnResponse(response);
  }
  function addonError(response, el, button) {
    var addonState = __webpack_require__(/*! ./addon-state */ "./js/src/admin/addon-state.js");
    addonState.addonError(response, el, button);
  }

  /* Templates */
  function showActiveCampaignForm() {
    loadApiEmailForm();
  }
  function handleApiFormError(inputId, errorId, type, message) {
    var $error = jQuery(errorId);
    $error.removeClass('frm_hidden').attr('frm-error', type);
    if (typeof message !== 'undefined') {
      $error.find('span[frm-error="' + type + '"]').text(message);
    }
    jQuery(inputId).one('keyup', function () {
      $error.addClass('frm_hidden');
    });
  }
  function handleEmailAddressError(type) {
    handleApiFormError('#frm_leave_email', '#frm_leave_email_error', type);
  }
  function loadApiEmailForm() {
    var formContainer = document.getElementById('frmapi-email-form');
    jQuery.ajax({
      dataType: 'json',
      url: formContainer.getAttribute('data-url'),
      success: function success(json) {
        var form = json.renderedHtml;
        form = form.replace(/<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '');
        formContainer.innerHTML = form;
      }
    });
  }
  function initAutocomplete(container) {
    frmDom.autocomplete.initSelectionAutocomplete(container);
  }
  function nextInstallStep(thisStep) {
    thisStep.classList.add('frm_grey');
    thisStep.nextElementSibling.classList.remove('frm_grey');
  }
  function installTemplateFieldset(e) {
    /*jshint validthis:true */
    var fieldset = this.parentNode.parentNode,
      action = fieldset.elements.type.value,
      button = this;
    e.preventDefault();
    button.classList.add('frm_loading_button');
    installNewForm(fieldset, action, button);
  }
  function installTemplate(e) {
    /*jshint validthis:true */
    var action = this.elements.type.value,
      button = this.querySelector('button');
    e.preventDefault();
    button.classList.add('frm_loading_button');
    installNewForm(this, action, button);
  }
  function installNewForm(form, action, button) {
    var formData = formToData(form);
    var formName = formData.template_name;
    var formDesc = formData.template_desc;
    var link = form.elements.link.value;
    var data = {
      action: action,
      xml: link,
      name: formName,
      desc: formDesc,
      form: JSON.stringify(formData),
      nonce: frmGlobal.nonce
    };
    var hookName = 'frm_before_install_new_form';
    var filterArgs = {
      formData: formData
    };
    data = wp.hooks.applyFilters(hookName, data, filterArgs);
    postAjax(data, function (response) {
      if (typeof response.redirect !== 'undefined') {
        var redirect = response.redirect;
        if (typeof form.elements.redirect === 'undefined') {
          window.location = redirect;
        } else {
          var href = document.getElementById('frm-redirect-link');
          if (typeof link !== 'undefined' && href !== null) {
            // Show the next installation step.
            href.setAttribute('href', redirect);
            href.classList.remove('frm_grey', 'disabled');
            nextInstallStep(form.parentNode.parentNode);
            button.classList.add('frm_grey', 'disabled');
          }
        }
      } else {
        jQuery('.spinner').css('visibility', 'hidden');

        // Show response.message
        if ('string' === typeof response.message) {
          showInstallFormErrorModal(response.message);
        }
      }
      button.classList.remove('frm_loading_button');
    });
  }
  function showInstallFormErrorModal(message) {
    var modalContent = div(message);
    modalContent.style.padding = '20px 40px';
    var modal = frmDom.modal.maybeCreateModal('frmInstallFormErrorModal', {
      title: __('Unable to install template', 'formidable'),
      content: modalContent
    });
    modal.classList.add('frm_common_modal');
  }
  function handleCaptchaTypeChange(e) {
    var thresholdContainer = document.getElementById('frm_captcha_threshold_container');
    if (thresholdContainer) {
      thresholdContainer.classList.toggle('frm_hidden', 'v3' !== e.target.value);
    }
  }
  function trashTemplate(e) {
    /*jshint validthis:true */
    var id = this.getAttribute('data-id');
    e.preventDefault();
    data = {
      action: 'frm_forms_trash',
      id: id,
      nonce: frmGlobal.nonce
    };
    postAjax(data, function () {
      var card = document.getElementById('frm-template-custom-' + id);
      fadeOut(card, function () {
        card.parentNode.removeChild(card);
      });
    });
  }
  function searchContent() {
    /*jshint validthis:true */
    var i,
      regEx = false,
      searchText = this.value.toLowerCase(),
      toSearch = this.getAttribute('data-tosearch'),
      items = document.getElementsByClassName(toSearch);
    if (this.tagName === 'SELECT') {
      searchText = selectedOptions(this);
      searchText = searchText.join('|').toLowerCase();
      regEx = true;
    }
    if (toSearch === 'frm-action' && searchText !== '') {
      var addons = document.getElementById('frm_email_addon_menu').classList;
      addons.remove('frm-all-actions');
      addons.add('frm-limited-actions');
    }
    for (i = 0; i < items.length; i++) {
      var innerText = items[i].innerText.toLowerCase();
      var itemCanBeShown = !(getExportOption() === 'xml' && items[i].classList.contains('frm-is-repeater'));
      if (searchText === '') {
        if (itemCanBeShown && checkContextualShortcode(items[i])) {
          items[i].classList.remove('frm_hidden');
        }
        items[i].classList.remove('frm-search-result');
      } else if (regEx && new RegExp(searchText).test(innerText) || innerText.indexOf(searchText) >= 0 || textMatchesPlural(innerText, searchText)) {
        if (itemCanBeShown && checkContextualShortcode(items[i])) {
          items[i].classList.remove('frm_hidden');
        }
        items[i].classList.add('frm-search-result');
      } else {
        items[i].classList.add('frm_hidden');
        items[i].classList.remove('frm-search-result');
      }
    }

    // Updates the visibility of category headings based on search results.
    updateCatHeadingVisibility();
    jQuery(this).trigger('frmAfterSearch');
  }

  /**
   * Allow a search for "signatures" to still match "signature" for example when searching fields.
   *
   * @since 6.15
   *
   * @param {string} text       The text in the element we are checking for a match.
   * @param {string} searchText The text value that is being searched.
   * @return {boolean}
   */
  function textMatchesPlural(text, searchText) {
    if (searchText === 's') {
      // Don't match everything when just "s" is searched.
      return false;
    }
    if (text[text.length - 1] === 's') {
      // Do not match something with double s if the text already ends in s.
      return false;
    }
    return (text + 's').indexOf(searchText) >= 0;
  }

  /**
   * Updates the visibility of category headings based on search results.
   * If all associated fields are hidden (indicating no search matches),
   * the heading is hidden.
   *
   * @since 6.4.1
   */
  function updateCatHeadingVisibility() {
    var insertFieldsElement = document.querySelector('#frm-insert-fields');
    if (!insertFieldsElement) {
      return;
    }
    var headingElements = insertFieldsElement.querySelectorAll(':scope > .frm-with-line');
    headingElements.forEach(function (heading) {
      var fieldsListElement = heading.nextElementSibling;
      if (!fieldsListElement) {
        return;
      }
      var listItemElements = fieldsListElement.querySelectorAll(':scope > li.frmbutton');
      var allHidden = Array.from(listItemElements).every(function (li) {
        return li.classList.contains('frm_hidden');
      });

      // Add or remove class based on `allHidden` condition
      heading.classList.toggle('frm_hidden', allHidden);
    });
  }
  function stopPropagation(e) {
    e.stopPropagation();
  }

  /* Helpers */

  function selectedOptions(select) {
    var opt,
      result = [],
      options = select && select.options;
    for (var _i13 = 0, iLen = options.length; _i13 < iLen; _i13++) {
      opt = options[_i13];
      if (opt.selected) {
        result.push(opt.value);
      }
    }
    return result;
  }
  function triggerEvent(element, event) {
    var evt = document.createEvent('HTMLEvents');
    evt.initEvent(event, false, true);
    element.dispatchEvent(evt);
  }
  function postAjax(data, success) {
    var response;
    var xmlHttp = new XMLHttpRequest();
    var params = typeof data === 'string' ? data : Object.keys(data).map(function (k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
    }).join('&');
    xmlHttp.open('post', ajaxurl, true);
    xmlHttp.onreadystatechange = function () {
      if (xmlHttp.readyState > 3 && xmlHttp.status == 200) {
        response = xmlHttp.responseText;
        try {
          response = JSON.parse(response);
        } catch (e) {
          // The response may not be JSON, so just return it.
        }
        success(response);
      }
    };
    xmlHttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xmlHttp.send(params);
    return xmlHttp;
  }
  function fadeOut(element, success) {
    element.classList.add('frm-fade');
    setTimeout(success, 1000);
  }
  function invisible(classes) {
    jQuery(classes).css('visibility', 'hidden');
  }
  function visible(classes) {
    jQuery(classes).css('visibility', 'visible');
  }
  function initModal(id, width) {
    var upgradePopup = __webpack_require__(/*! ./upgrade-popup */ "./js/src/admin/upgrade-popup.js");
    return upgradePopup.initModal(id, width);
  }
  function toggle(cname, id) {
    if (id === '#') {
      var cont = document.getElementById(cname);
      var hidden = cont.style.display;
      if (hidden === 'none') {
        cont.style.display = 'block';
      } else {
        cont.style.display = 'none';
      }
    } else {
      var vis = cname.is(':visible');
      if (vis) {
        cname.hide();
      } else {
        cname.show();
      }
    }
  }
  function removeWPUnload() {
    window.onbeforeunload = null;
    var w = jQuery(window);
    w.off('beforeunload.widgets');
    w.off('beforeunload.edit-post');
  }
  function addMultiselectLabelListener() {
    var clickListener = function clickListener(e) {
      if ('LABEL' !== e.target.nodeName) {
        return;
      }
      var labelFor = e.target.getAttribute('for');
      if (!labelFor) {
        return;
      }
      var input = document.getElementById(labelFor);
      if (!input || !input.nextElementSibling) {
        return;
      }
      var buttonToggle = input.nextElementSibling.querySelector('button.dropdown-toggle.multiselect');
      if (!buttonToggle) {
        return;
      }
      var triggerMultiselectClick = function triggerMultiselectClick() {
        return buttonToggle.click();
      };
      setTimeout(triggerMultiselectClick, 0);
    };
    document.addEventListener('click', clickListener);
  }
  function maybeChangeEmbedFormMsg() {
    var fieldId = jQuery(this).closest('.frm-single-settings').data('fid');
    var fieldItem = document.getElementById('frm_field_id_' + fieldId);
    if (null === fieldItem || 'form' !== fieldItem.dataset.type) {
      return;
    }
    fieldItem = jQuery(fieldItem);
    if (this.options[this.selectedIndex].value) {
      fieldItem.find('.frm-not-set')[0].classList.add('frm_hidden');
      var embedMsg = fieldItem.find('.frm-embed-message');
      embedMsg.html(embedMsg.data('embedmsg') + this.options[this.selectedIndex].text);
      fieldItem.find('.frm-embed-field-placeholder')[0].classList.remove('frm_hidden');
    } else {
      fieldItem.find('.frm-not-set')[0].classList.remove('frm_hidden');
      fieldItem.find('.frm-embed-field-placeholder')[0].classList.add('frm_hidden');
    }
  }
  function toggleProductType() {
    var settings = jQuery(this).closest('.frm-single-settings'),
      container = settings.find('.frmjs_product_choices'),
      heading = settings.find('.frm_prod_options_heading'),
      currentVal = this.options[this.selectedIndex].value;
    container.removeClass('frm_prod_type_single frm_prod_type_user_def');
    heading.removeClass('frm_prod_user_def');
    if ('single' === currentVal) {
      container.addClass('frm_prod_type_single');
    } else if ('user_def' === currentVal) {
      container.addClass('frm_prod_type_user_def');
      heading.addClass('frm_prod_user_def');
    }
  }

  /**
   * @param {Number | string} fieldId
   * @return {boolean} True if the field is a product field.
   */
  function isProductField(fieldId) {
    var field = document.getElementById('frm_field_id_' + fieldId);
    if (field === null) {
      return false;
    }
    return 'product' === field.getAttribute('data-type');
  }

  /**
   * Serialize form data with vanilla JS.
   */
  function formToData(form) {
    var subKey,
      i,
      object = {},
      formData = form.elements;
    for (i = 0; i < formData.length; i++) {
      var input = formData[i],
        key = input.name,
        value = input.value,
        names = key.match(/(.*)\[(.*)\]/);
      if ((input.type === 'radio' || input.type === 'checkbox') && !input.checked) {
        continue;
      }
      if (names !== null) {
        key = names[1];
        subKey = names[2];
        if (!Reflect.has(object, key)) {
          object[key] = {};
        }
        object[key][subKey] = value;
        continue;
      }

      // Reflect.has in favor of: object.hasOwnProperty(key)
      if (!Reflect.has(object, key)) {
        object[key] = value;
        continue;
      }
      if (!Array.isArray(object[key])) {
        object[key] = [object[key]];
      }
      object[key].push(value);
    }
    return object;
  }

  /**
   * Show, hide, and sort subfields of Name field on form builder.
   *
   * @since 4.11
   */
  function handleNameFieldOnFormBuilder() {
    /**
     * Gets subfield element from cache.
     *
     * @param {string} fieldId Field ID.
     * @param {string} key     Cache key.
     * @return {HTMLElement|undefined} Return the element from cache or undefined if not found.
     */
    var getSubFieldElFromCache = function getSubFieldElFromCache(fieldId, key) {
      window.frmCachedSubFields = window.frmCachedSubFields || {};
      window.frmCachedSubFields[fieldId] = window.frmCachedSubFields[fieldId] || {};
      return window.frmCachedSubFields[fieldId][key];
    };

    /**
     * Sets subfield element to cache.
     *
     * @param {string}      fieldId Field ID.
     * @param {string}      key     Cache key.
     * @param {HTMLElement} el      Element.
     */
    var setSubFieldElToCache = function setSubFieldElToCache(fieldId, key, el) {
      window.frmCachedSubFields = window.frmCachedSubFields || {};
      window.frmCachedSubFields[fieldId] = window.frmCachedSubFields[fieldId] || {};
      window.frmCachedSubFields[fieldId][key] = el;
    };

    /**
     * Gets column class from the number of columns.
     *
     * @param {Number} colCount Number of columns.
     * @return {string}
     */
    var getColClass = function getColClass(colCount) {
      return 'frm' + parseInt(12 / colCount);
    };
    var colClasses = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map(function (num) {
      return 'frm' + num;
    });
    var allSubFieldNames = ['first', 'middle', 'last'];

    /**
     * Handles name layout change.
     *
     * @param {Event} event Event object.
     */
    var onChangeLayout = function onChangeLayout(event) {
      var value = event.target.value;
      var subFieldNames = value.split('_');
      var fieldId = event.target.dataset.fieldId;

      /*
       * Live update form on the form builder.
       */
      var container = document.querySelector('#field_' + fieldId + '_inner_container .frm_combo_inputs_container');
      var newColClass = getColClass(subFieldNames.length);

      // Set all sub field elements to cache and hide all of them first.
      allSubFieldNames.forEach(function (name) {
        var subFieldEl = container.querySelector('[data-sub-field-name="' + name + '"]');
        if (subFieldEl) {
          var _subFieldEl$classList;
          subFieldEl.classList.add('frm_hidden');
          (_subFieldEl$classList = subFieldEl.classList).remove.apply(_subFieldEl$classList, _toConsumableArray(colClasses));
          setSubFieldElToCache(fieldId, name, subFieldEl);
        }
      });
      subFieldNames.forEach(function (subFieldName) {
        var subFieldEl = getSubFieldElFromCache(fieldId, subFieldName);
        if (!subFieldEl) {
          return;
        }
        subFieldEl.classList.remove('frm_hidden');
        subFieldEl.classList.add(newColClass);
        container.append(subFieldEl);
      });

      /*
       * Live update subfield options.
       */
      // Hide all subfield options.
      allSubFieldNames.forEach(function (name) {
        var optionsEl = document.querySelector('.frm_sub_field_options-' + name + '[data-field-id="' + fieldId + '"]');
        if (optionsEl) {
          optionsEl.classList.add('frm_hidden');
          setSubFieldElToCache(fieldId, name + '_options', optionsEl);
        }
      });
      subFieldNames.forEach(function (subFieldName) {
        var optionsEl = getSubFieldElFromCache(fieldId, subFieldName + '_options');
        if (!optionsEl) {
          return;
        }
        optionsEl.classList.remove('frm_hidden');
      });
    };
    var dropdownSelector = '.frm_name_layout_dropdown';
    document.addEventListener('change', function (event) {
      if (event.target.matches(dropdownSelector)) {
        onChangeLayout(event);
      }
    }, false);
  }
  function debounce(func) {
    var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 100;
    return frmDom.util.debounce(func, wait);
  }
  function addSaveAndDragIconsToOption(fieldId, liObject) {
    var li, useTag, useTagHref;
    var hasDragIcon = false;
    var hasSaveIcon = false;
    if (liObject.newOption) {
      var parser = new DOMParser();
      li = parser.parseFromString(liObject.newOption, 'text/html').body.childNodes[0];
    } else {
      li = liObject;
    }
    var liIcons = li.querySelectorAll('svg');
    liIcons.forEach(function (svg, key) {
      useTag = svg.getElementsByTagNameNS('http://www.w3.org/2000/svg', 'use')[0];
      if (!useTag) {
        return;
      }
      useTagHref = getSVGHref(useTag);
      if (useTagHref === '#frm_drag_icon') {
        hasDragIcon = true;
      }
      if (useTagHref === '#frm_save_icon') {
        hasSaveIcon = true;
      }
    });
    if (!hasDragIcon) {
      li.prepend(icons.drag.cloneNode(true));
    }
    if (li.querySelector("[id^=field_key_".concat(fieldId, "-]")) && !hasSaveIcon) {
      li.querySelector("[id^=field_key_".concat(fieldId, "-]")).after(icons.save.cloneNode(true));
    }
    if (liObject.newOption) {
      liObject.newOption = li;
    }
  }
  function maybeAddSaveAndDragIcons(fieldId) {
    var fieldOptions = document.querySelectorAll("[id^=frm_delete_field_".concat(fieldId, "-]"));
    // return if there are no options.
    if (fieldOptions.length < 2) {
      return;
    }
    var options = _toConsumableArray(fieldOptions).slice(1);
    options.forEach(function (li, _key) {
      if (li.classList.contains('frm_other_option')) {
        return;
      }
      addSaveAndDragIconsToOption(fieldId, li);
    });
  }

  /**
   * Enforce the maximum number of entries list columns dynamically.
   *
   * @since 6.24
   *
   * @return {void}
   */
  function maybeInitEntriesListPage() {
    if (!document.body.classList.contains('frm-admin-page-entries')) {
      return;
    }
    var screenOptionsWrapper = document.getElementById('screen-options-wrap');
    if (!screenOptionsWrapper) {
      return;
    }
    var maxSelectionsNote = div({
      className: 'frm_warning_style',
      text: __('Only 10 columns can be selected at a time.', 'formidable')
    });
    maxSelectionsNote.style.margin = 0;
    var legend = screenOptionsWrapper.querySelector('legend');
    legend.parentNode.insertBefore(maxSelectionsNote, legend.nextElementSibling);
    var checkboxes = Array.from(screenOptionsWrapper.querySelectorAll('input[type="checkbox"]'));
    var maximumColumns = 10;
    var getSelectedCount = function getSelectedCount() {
      return checkboxes.reduce(function (count, checkbox) {
        return checkbox.checked ? count + 1 : count;
      }, 0);
    };
    var disableCheckboxesIfAtMax = function disableCheckboxesIfAtMax() {
      if (getSelectedCount() >= maximumColumns) {
        maxSelectionsNote.classList.remove('frm_hidden');
        checkboxes.forEach(function (checkbox) {
          if (!checkbox.checked) {
            checkbox.parentNode.classList.add('frm_noallow');
            checkbox.disabled = true;
          }
        });
      } else {
        maxSelectionsNote.classList.add('frm_hidden');
      }
    };
    var addCheckboxListeners = function addCheckboxListeners() {
      checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function (event) {
          if (event.target.checked) {
            disableCheckboxesIfAtMax();
          } else {
            maxSelectionsNote.classList.add('frm_hidden');

            // Enable all checkboxes when a checkbox is unchecked.
            checkboxes.forEach(function (checkbox) {
              checkbox.parentNode.classList.remove('frm_noallow');
              checkbox.disabled = false;
            });
          }
        });
      });
    };
    disableCheckboxesIfAtMax();
    addCheckboxListeners();
  }
  function initOnSubmitAction() {
    var onChangeType = function onChangeType(event) {
      if (!event.target.checked) {
        return;
      }
      var actionEl = event.target.closest('.frm_form_action_settings');
      actionEl.querySelectorAll('.frm_on_submit_dependent_setting:not(.frm_hidden)').forEach(function (el) {
        el.classList.add('frm_hidden');
      });
      var activeEls = actionEl.querySelectorAll('.frm_on_submit_dependent_setting[data-show-if-' + event.target.value + ']');
      activeEls.forEach(function (activeEl) {
        activeEl.classList.remove('frm_hidden');
      });
      actionEl.setAttribute('data-on-submit-type', event.target.value);
    };
    frmDom.util.documentOn('change', '.frm_on_submit_type input[type="radio"]', onChangeType);
  }

  /**
   * Listen for click events for an API-loaded email collection form.
   *
   * This is used for the Active Campaign sign-up form in the inbox page (when there are no messages).
   */
  function initAddMyEmailAddress() {
    jQuery(document).on('click', '#frm-add-my-email-address', function (event) {
      event.preventDefault();
      addMyEmailAddress();
    });
    var emptyInbox = document.getElementById('frm_empty_inbox');
    var leaveEmailInput = document.getElementById('frm_leave_email');
    if (emptyInbox && leaveEmailInput) {
      var leaveEmailModal = document.getElementById('frm-leave-email-modal');
      leaveEmailModal.classList.remove('frm_hidden');
      leaveEmailModal.querySelector('.frm_modal_footer').classList.add('frm_hidden');
      leaveEmailInput.addEventListener('keyup', function (event) {
        if ('Enter' === event.key) {
          var button = document.getElementById('frm-add-my-email-address');
          if (button) {
            button.click();
          }
        }
      });
    }
  }
  function addMyEmailAddress() {
    var email = document.getElementById('frm_leave_email').value.trim();
    if ('' === email) {
      handleEmailAddressError('empty');
      return;
    }
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;
    if (regex.test(email) === false) {
      handleEmailAddressError('invalid');
      return;
    }
    var $hiddenForm = jQuery('#frmapi-email-form').find('form');
    var $hiddenEmailField = $hiddenForm.find('[type="email"]').not('.frm_verify');
    if (!$hiddenEmailField.length) {
      return;
    }
    var emptyInbox = document.getElementById('frm_empty_inbox');
    if (emptyInbox) {
      document.getElementById('frm-add-my-email-address').remove();
      var emailWrapper = document.getElementById('frm_leave_email_wrapper');
      if (emailWrapper) {
        emailWrapper.classList.add('frm_hidden');
        var spinner = span({
          className: 'frm-wait frm_spinner'
        });
        spinner.style.visibility = 'visible';
        spinner.style.float = 'none';
        spinner.style.width = 'unset';
        emailWrapper.parentElement.insertBefore(spinner, emailWrapper.nextElementSibling);
      }
    }
    $hiddenEmailField.val(email);
    jQuery.ajax({
      type: 'POST',
      url: $hiddenForm.attr('action'),
      data: $hiddenForm.serialize() + '&action=frm_forms_preview'
    }).done(function (data) {
      var message = jQuery(data).find('.frm_message').text().trim();
      if (message.indexOf('Thanks!') === -1) {
        handleEmailAddressError('invalid');
        return;
      }
      var apiForm = document.getElementById('frmapi-email-form');
      var spinner = apiForm.parentElement.querySelector('.frm_spinner');
      if (spinner) {
        spinner.remove();
      }
      var showSuccessMessage = wp.hooks.applyFilters('frm_thank_you_on_signup', true);
      if (showSuccessMessage) {
        // Handle successful form submission.
        // handle the Active Campaign form on the inbox page.
        document.getElementById('frm_leave_email_wrapper').replaceWith(span(__('Thank you for signing up!', 'formidable')));
      }
    });
  }

  /**
   * Adds footer links to the admin body content.
   *
   * @return {void}
   */
  function addAdminFooterLinks() {
    var _document$querySelect7;
    var footerLinks = document.querySelector('.frm-admin-footer-links');
    var container = (_document$querySelect7 = document.querySelector('.frm_page_container')) !== null && _document$querySelect7 !== void 0 ? _document$querySelect7 : document.getElementById('wpbody-content');
    if (!footerLinks || !container) {
      return;
    }
    container.appendChild(footerLinks);
    footerLinks.classList.remove('frm_hidden');
  }

  /**
   * Apply zebra striping to a table while ignoring empty rows.
   *
   * @param {string} tableSelector The CSS selector for the table.
   * @param {string} emptyRowClass The class name used to identify empty rows.
   */
  function applyZebraStriping(tableSelector, emptyRowClass) {
    // Get all non-empty table rows within the specified table
    var rows = document.querySelectorAll("".concat(tableSelector, " tr").concat(emptyRowClass ? ":not(.".concat(emptyRowClass, ")") : ''));
    if (rows.length < 1) {
      return;
    }
    var isOdd = true;
    rows.forEach(function (row) {
      // Clean old "frm-odd" or "frm-even" classes and add the appropriate new class
      row.classList.remove('frm-odd', 'frm-even');
      row.classList.add(isOdd ? 'frm-odd' : 'frm-even');
      isOdd = !isOdd;
    });
    var tables = document.querySelectorAll(tableSelector);
    tables.forEach(function (table) {
      return table.classList.add('frm-zebra-striping');
    });
  }
  function maybeHideShortcodes(e) {
    if (!builderPage) {
      e.stopPropagation();
    }
    if (e.target.classList.contains('frm-show-box') || e.target.parentElement && e.target.parentElement.classList.contains('frm-show-box')) {
      return;
    }
    var sidebar = document.getElementById('frm_adv_info');
    if (!sidebar) {
      return;
    }
    if (sidebar.dataset.fills === e.target.id && typeof e.target.id !== 'undefined') {
      return;
    }
    var isChild = e.target.closest('#frm_adv_info');
    if (!isChild && sidebar.style.display !== 'none') {
      hideShortcodes(sidebar);
    }
  }

  /**
   * Initializes and manages the visibility of dependent elements based on the selected options in dropdowns with the 'frm_select_with_dependency' class.
   * It sets up initial visibility at page load and updates it on each dropdown change.
   *
   * @since 6.9
   *
   * @return {void}
   */
  function initSelectDependencies() {
    var selects = document.querySelectorAll('select.frm_select_with_dependency');

    /**
     * Toggles the visibility of dependent elements associated with a select element based on its current selection.
     *
     * @since 6.9
     *
     * @param {HTMLElement} select The select element whose dependencies need to be managed.
     * @return {void}
     */
    function toggleDependencyVisibility(select) {
      var selectedOption = select.options[select.selectedIndex];
      select.querySelectorAll('option[data-dependency]:not([data-dependency-skip])').forEach(function (option) {
        var dependencyElement = document.querySelector(option.dataset.dependency);
        dependencyElement === null || dependencyElement === void 0 || dependencyElement.classList.toggle('frm_hidden', selectedOption !== option);
      });
    }

    // Initial setup: Show dependencies based on the current selection in each dropdown
    selects.forEach(toggleDependencyVisibility);

    // Update dependencies visibility on dropdown change
    frmDom.util.documentOn('change', 'select.frm_select_with_dependency', function (event) {
      return toggleDependencyVisibility(event.target);
    });
  }

  /**
   * Moves the focus to the next single option input field in the list and positions the cursor at the end of the text.
   *
   * @param {HTMLElement} currentInput The currently focused input element.
   */
  function focusNextSingleOptionInput(currentInput) {
    var optionsList = currentInput.closest('.frm_single_option').parentElement;
    var inputs = optionsList.querySelectorAll('.frm_single_option input[name^="field_options[" ], .frm_single_option input[name^="rows_"]');
    var inputsArray = Array.from(inputs);

    // Find the index of the currently focused input
    var currentIndex = inputsArray.indexOf(currentInput);
    if (currentIndex < 0) {
      return;
    }

    // Find the next visible input field
    var nextInput = inputsArray.slice(currentIndex + 1).find(function (input) {
      return input.offsetParent !== null;
    });
    if (nextInput) {
      nextInput.focus();

      // Move the cursor to the end of the text in the next input field
      var textLength = nextInput.value.length;
      nextInput.setSelectionRange(textLength, textLength);
    }
  }
  return {
    init: function init() {
      initAddMyEmailAddress();
      addAdminFooterLinks();
      document.addEventListener('show.bs.dropdown', function () {
        // Fixes issues with tooltips lingering after a dropdown is shown.
        deleteTooltips();
      });
      s = {};

      // Bootstrap dropdown button
      jQuery('.wp-admin').on('click', function (e) {
        var t = jQuery(e.target);
        var $openDrop = jQuery('.dropdown.open');
        if ($openDrop.length && !t.hasClass('dropdown') && !t.closest('.dropdown').length) {
          $openDrop.removeClass('open');
        }
      });
      jQuery('#frm_bs_dropdown:not(.open) a').on('click', focusSearchBox);
      if (typeof thisFormId === 'undefined') {
        thisFormId = jQuery(document.getElementById('form_id')).val();
      }

      // Add event listener for dismissible warning messages.
      document.querySelectorAll('.frm-warning-dismiss').forEach(function (dismissIcon) {
        onClickPreventDefault(dismissIcon, dismissWarningMessage);
      });
      frmAdminBuild.inboxBannerInit();
      if ($newFields.length > 0) {
        // only load this on the form builder page
        frmAdminBuild.buildInit();
      } else if (document.getElementById('frm_notification_settings') !== null) {
        // only load on form settings page
        frmAdminBuild.settingsInit();
      } else if (document.getElementById('frm_styling_form') !== null) {
        // load styling settings js
        frmAdminBuild.styleInit();
      } else if (document.getElementById('form_global_settings') !== null) {
        // global settings page
        frmAdminBuild.globalSettingsInit();
      } else if (document.getElementById('frm_export_xml') !== null) {
        // import/export page
        frmAdminBuild.exportInit();
      } else if (null !== document.querySelector('.frm-inbox-wrapper')) {
        // Dashboard page inbox.
        frmAdminBuild.inboxInit();
      } else if (document.getElementById('frm-welcome') !== null) {
        // Solution install page
        frmAdminBuild.solutionInit();
      } else {
        maybeInitEntriesListPage();
        initAutocomplete();
        jQuery('[data-frmprint]').on('click', function () {
          window.print();
          return false;
        });
      }
      jQuery(document).on('change', 'select[data-toggleclass], input[data-toggleclass]', toggleFormOpts);
      initSelectDependencies();
      var $advInfo = jQuery(document.getElementById('frm_adv_info'));
      if ($advInfo.length > 0 || jQuery('.frm_field_list').length > 0) {
        // only load on the form, form settings, and view settings pages
        frmAdminBuild.panelInit();
      }
      loadTooltips();
      initUpgradeModal();
      frmDom.util.documentOn('click', '[data-modal-title]', showBasicModal);

      // used on build, form settings, and view settings
      var $shortCodeDiv = jQuery(document.getElementById('frm_shortcodediv'));
      if ($shortCodeDiv.length > 0) {
        jQuery('a.edit-frm_shortcode').on('click', function () {
          if ($shortCodeDiv.is(':hidden')) {
            $shortCodeDiv.slideDown('fast');
            this.style.display = 'none';
          }
          return false;
        });
        jQuery('.cancel-frm_shortcode', '#frm_shortcodediv').on('click', function () {
          $shortCodeDiv.slideUp('fast');
          $shortCodeDiv.siblings('a.edit-frm_shortcode').show();
          return false;
        });
      }

      // tabs
      jQuery(document).on('click', '#frm-nav-tabs a', clickNewTab);
      jQuery('.post-type-frm_display .frm-nav-tabs a, .frm-category-tabs a').on('click', function () {
        var showUpgradeTab = this.classList.contains('frm_show_upgrade_tab');
        if (this.classList.contains('frm_noallow') && !showUpgradeTab) {
          return;
        }
        if (showUpgradeTab) {
          populateUpgradeTab(this);
        }
        clickTab(this);
        return false;
      });
      clickTab(jQuery('.starttab a'), 'auto');

      // submit the search form with dropdown
      jQuery(document).on('click', '#frm-fid-search-menu a', function () {
        var val = this.id.replace('fid-', '');
        jQuery('select[name="fid"]').val(val);
        triggerSubmit(document.getElementById('posts-filter'));
        return false;
      });
      jQuery('.frm_select_box').on('click focus', function () {
        this.select();
      });
      jQuery(document).on('input search change', '.frm-auto-search:not(#frm-form-templates-page #template-search-input)', searchContent);
      jQuery(document).on('focusin click', '.frm-auto-search', stopPropagation);
      var autoSearch = jQuery('.frm-auto-search');
      if (autoSearch.val() !== '') {
        autoSearch.trigger('keyup');
      }

      // Initialize Formidable Connection.
      FrmFormsConnect.init();
      jQuery(document).on('click', '.frm-install-addon', installAddon);
      jQuery(document).on('click', '.frm-activate-addon', activateAddon);
      jQuery(document).on('click', '.frm-solution-multiple', installMultipleAddons);

      // prevent annoying confirmation message from WordPress
      jQuery('button, input[type=submit]').on('click', removeWPUnload);
      addMultiselectLabelListener();
      frmAdminBuild.hooks.addFilter('frm_before_embed_modal', function (ids, _ref4) {
        var element = _ref4.element,
          type = _ref4.type;
        if ('form' !== type) {
          return ids;
        }
        var formId, formKey;
        var row = element.closest('tr');
        if (row) {
          // Embed icon on form index.
          formId = parseInt(row.querySelector('.column-id').textContent);
          formKey = row.querySelector('.column-form_key').textContent;
        } else {
          // Embed button in form builder / form settings.
          formId = document.getElementById('form_id').value;
          var formKeyInput = document.getElementById('frm_form_key');
          if (formKeyInput) {
            formKey = formKeyInput.value;
          } else {
            var previewDrop = document.getElementById('frm-previewDrop');
            if (previewDrop) {
              formKey = previewDrop.nextElementSibling.querySelector('.dropdown-item a').getAttribute('href').split('form=')[1];
            }
          }
        }
        return [formId, formKey];
      });
      document.querySelectorAll('#frm-show-fields > li, .frm_grid_container li').forEach(function (el, _key) {
        el.addEventListener('click', function () {
          var _this$querySelector;
          var fieldId = ((_this$querySelector = this.querySelector('li')) === null || _this$querySelector === void 0 ? void 0 : _this$querySelector.dataset.fid) || this.dataset.fid;
          maybeAddSaveAndDragIcons(fieldId);
        });
      });
      var smallScreenProceedButton = document.getElementById('frm_small_screen_proceed_button');
      if (smallScreenProceedButton) {
        onClickPreventDefault(smallScreenProceedButton, function () {
          var _document$getElementB5;
          (_document$getElementB5 = document.getElementById('frm_small_device_message_container')) === null || _document$getElementB5 === void 0 || _document$getElementB5.remove();
          doJsonPost('small_screen_proceed', new FormData());
        });
      }
      var saleBanner = document.getElementById('frm_sale_banner');
      var saleDismiss = saleBanner === null || saleBanner === void 0 ? void 0 : saleBanner.querySelector('.dismiss');
      if (saleBanner) {
        onClickPreventDefault(saleBanner, function (event) {
          var target = event.target;
          if (target.closest('.dismiss')) {
            return;
          }
          window.location.href = saleBanner.getAttribute('data-url');
        });
        if (saleDismiss) {
          onClickPreventDefault(saleDismiss, function () {
            saleBanner.remove();
            var formData = new FormData();
            doJsonPost('sale_banner_dismiss', formData);
          });
        }
      }
    },
    buildInit: function buildInit() {
      jQuery('#frm_builder_page').on('mouseup', '*:not(.frm-show-box)', maybeHideShortcodes);
      var loadFieldId, $builderForm, builderArea;
      debouncedSyncAfterDragAndDrop = debounce(syncAfterDragAndDrop, 10);
      postBodyContent = document.getElementById('post-body-content');
      $postBodyContent = jQuery(postBodyContent);
      if (jQuery('.frm_field_loading').length) {
        loadFieldId = jQuery('.frm_field_loading').first().attr('id');
        loadFields(loadFieldId);
      }
      setupSortable('ul.frm_sorting');
      document.querySelectorAll('.field_type_list > li:not(.frm_show_upgrade)').forEach(makeDraggable);
      jQuery('ul.field_type_list, .field_type_list li, ul.frm_code_list, .frm_code_list li, .frm_code_list li a, #frm_adv_info #category-tabs li, #frm_adv_info #category-tabs li a').disableSelection();
      jQuery('.frm_submit_ajax').on('click', submitBuild);
      jQuery('.frm_submit_no_ajax').on('click', submitNoAjax);
      addFormNameModalEvents();
      jQuery('a.edit-form-status').on('click', slideDown);
      jQuery('.cancel-form-status').on('click', slideUp);
      jQuery('.save-form-status').on('click', function () {
        var newStatus = jQuery(document.getElementById('form_change_status')).val();
        jQuery('input[name="new_status"]').val(newStatus);
        jQuery(document.getElementById('form-status-display')).html(newStatus);
        jQuery('.cancel-form-status').trigger('click');
        return false;
      });
      jQuery('.frm_form_builder form').first().on('submit', function () {
        jQuery('.inplace_field').trigger('blur');
      });
      initiateMultiselect();
      renumberPageBreaks();
      $builderForm = jQuery(builderForm);
      builderArea = document.getElementById('frm_form_editor_container');
      $builderForm.on('click', '.frm_add_logic_row', addFieldLogicRow);
      $builderForm.on('click', '.frm_add_watch_lookup_row', addWatchLookupRow);
      $builderForm.on('change', '.frm_get_values_form', updateGetValueFieldSelection);
      $builderForm.on('change', '.frm_logic_field_opts', getFieldValues);
      $builderForm.on('frm-multiselect-changed', 'select[name^="field_options[admin_only_"]', adjustVisibilityValuesForEveryoneValues);
      jQuery(document.getElementById('frm-insert-fields')).on('click', '.frm_add_field', addFieldClick);
      $newFields.on('click', '.frm_clone_field', duplicateField);
      $builderForm.on('blur', 'input[id^="frm_calc"]', checkCalculationCreatedByUser);
      $builderForm.on('change', 'input.frm_format_opt, input.frm_max_length_opt', toggleInvalidMsg);
      $builderForm.on('change click', '[data-changeme]', liveChanges);
      $builderForm.on('click', 'input.frm_req_field', markRequired);
      $builderForm.on('click', '.frm_mark_unique', markUnique);
      $builderForm.on('change', '.frm_repeat_format', toggleRepeatButtons);
      $builderForm.on('change', '.frm_repeat_limit', checkRepeatLimit);
      $builderForm.on('change', '.frm_js_checkbox_limit', checkCheckboxSelectionsLimit);
      $builderForm.on('input', 'input[name^="field_options[add_label_"]', function () {
        updateRepeatText(this, 'add');
      });
      $builderForm.on('input', 'input[name^="field_options[remove_label_"]', function () {
        updateRepeatText(this, 'remove');
      });
      $builderForm.on('change', 'select[name^="field_options[data_type_"]', maybeClearWatchFields);
      jQuery(builderArea).on('click', '.frm-collapse-page', maybeCollapsePage);
      jQuery(builderArea).on('click', '.frm-collapse-section', maybeCollapseSection);
      $builderForm.on('click', '.frm-single-settings h3, .frm-single-settings h4.frm-collapsible', maybeCollapseSettings);
      $builderForm.on('keydown', '.frm-single-settings h3, .frm-single-settings h4.frm-collapsible', function (event) {
        // If so, only proceed if the key pressed was 'Enter' or 'Space'
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          maybeCollapseSettings.call(this, event);
        }
      });
      jQuery(builderArea).on('show.bs.dropdown hide.bs.dropdown', changeSectionStyle);
      $builderForm.on('click', '.frm_toggle_sep_values', toggleSepValues);
      $builderForm.on('click', '.frm_toggle_image_options', toggleImageOptions);
      $builderForm.on('click', '.frm_remove_image_option', removeImageFromOption);
      $builderForm.on('click', '.frm_choose_image_box', addImageToOption);
      $builderForm.on('change', '.frm_hide_image_text', refreshOptionDisplay);
      $builderForm.on('change', '.frm_field_options_image_size', setImageSize);
      $builderForm.on('click', '.frm_multiselect_opt', toggleMultiselect);
      $newFields.on('mousedown', 'input, textarea, select', stopFieldFocus);
      $newFields.on('click', 'input[type=radio], input[type=checkbox]', stopFieldFocus);
      $newFields.on('click', '.frm_delete_field', clickDeleteField);
      $newFields.on('click', '.frm_select_field', clickSelectField);
      jQuery(document).on('click', '.frm_delete_field_group', clickDeleteFieldGroup);
      jQuery(document).on('click', '.frm_clone_field_group', duplicateFieldGroup);
      jQuery(document).on('click', '#frm_field_group_controls > span:first-child', clickFieldGroupLayout);
      jQuery(document).on('click', '.frm-row-layout-option', handleFieldGroupLayoutOptionClick);
      jQuery(document).on('click', '.frm-merge-fields-into-row .frm-row-layout-option', handleFieldGroupLayoutOptionInsideMergeClick);
      jQuery(document).on('click', '.frm-custom-field-group-layout', customFieldGroupLayoutClick);
      jQuery(document).on('click', '.frm-merge-fields-into-row .frm-custom-field-group-layout', customFieldGroupLayoutInsideMergeClick);
      jQuery(document).on('click', '.frm-break-field-group', breakFieldGroupClick);
      $newFields.on('click', '#frm_field_group_popup .frm_grid_container input', focusFieldGroupInputOnClick);
      jQuery(document).on('click', '.frm-cancel-custom-field-group-layout', cancelCustomFieldGroupClick);
      jQuery(document).on('click', '.frm-save-custom-field-group-layout', saveCustomFieldGroupClick);
      $newFields.on('click', 'ul.frm_sorting', fieldGroupClick);
      jQuery(document).on('click', '.frm-merge-fields-into-row', mergeFieldsIntoRowClick);
      jQuery(document).on('click', '.frm-delete-field-groups', deleteFieldGroupsClick);
      $newFields.on('click', '.frm-field-action-icons [data-toggle="dropdown"]', function () {
        this.closest('li.form-field').classList.add('frm-field-settings-open');
        jQuery(document).on('click', '#frm_builder_page', handleClickOutsideOfFieldSettings);
      });
      $newFields.on('mousemove', 'ul.frm_sorting', checkForMultiselectKeysOnMouseMove);
      $newFields.on('show.bs.dropdown', '.frm-field-action-icons', onFieldActionDropdownShow);
      jQuery(document).on('show.bs.dropdown', '#frm_field_group_controls', onFieldGroupActionDropdownShow);
      $builderForm.on('click', '.frm_single_option a[data-removeid]', deleteFieldOption);
      $builderForm.on('mousedown', '.frm_single_option input[type=radio]', maybeUncheckRadio);
      $builderForm.on('focusin', '.frm_single_option input[type=text]', maybeClearOptText);
      $builderForm.on('click', '.frm_add_opt', addFieldOption);
      $builderForm.on('change', '.frm_single_option input', resetOptOnChange);
      $builderForm.on('change', '.frm_image_id', resetOptOnChange);
      $builderForm.on('change', '.frm_toggle_mult_sel', toggleMultSel);
      $newFields.on('click', '.frm_primary_label', clickLabel);
      $newFields.on('click', '.frm_description', clickDescription);
      $newFields.on('click', 'li.ui-state-default:not(.frm_noallow)', clickVis);
      $newFields.on('dblclick', 'li.ui-state-default', openAdvanced);
      $builderForm.on('change', '.frm_tax_form_select', toggleFormTax);
      $builderForm.on('change', 'select.conf_field', addConf);
      $builderForm.on('change', '.frm_get_field_selection', getFieldSelection);
      $builderForm.on('click', '.frm-show-inline-modal', maybeShowInlineModal);
      $builderForm.on('keydown', '.frm-show-inline-modal', function (event) {
        var key = event.key;
        if (key === 'Enter' || key === ' ') {
          event.preventDefault();
          maybeShowInlineModal.call(this, event);
        }
      });
      $builderForm.on('click', '.frm-inline-modal .dismiss', dismissInlineModal);
      jQuery(document).on('change', '[data-frmchange]', changeInputtedValue);
      document.addEventListener('click', closeModalOnOutsideClick);
      $builderForm.on('change', '.frm_include_extras_field', rePopCalcFieldsForSummary);
      $builderForm.on('change', 'select[name^="field_options[form_select_"]', maybeChangeEmbedFormMsg);
      jQuery(document).on('submit', '#frm_js_build_form', buildSubmittedNoAjax);
      jQuery(document).on('change', '#frm_builder_page input:not(.frm-search-input):not(.frm-custom-grid-size-input), #frm_builder_page select, #frm_builder_page textarea', fieldUpdated);
      popAllProductFields();
      jQuery(document).on('change', '.frmjs_prod_data_type_opt', toggleProductType);
      jQuery(document).on('focus', '.frm-single-settings ul input[type="text"][name^="field_options[options_"]', onOptionTextFocus);
      jQuery(document).on('blur', '.frm-single-settings ul input[type="text"][name^="field_options[options_"]', onOptionTextBlur);
      frmDom.util.documentOn('click', '.frm-show-field-settings', clickVis);
      frmDom.util.documentOn('change', 'select.frm_format_dropdown, select.frm_phone_type_dropdown', maybeUpdateFormatInput);

      // Navigate to the next input field on pressing Enter in a single option field
      $builderForm.on('keydown', '.frm_single_option input[name^="field_options["], .frm_single_option input[name^="rows_"]', function (event) {
        if ('Enter' === event.key) {
          focusNextSingleOptionInput(event.currentTarget);
        }
      });
      initBulkOptionsOverlay();
      hideEmptyEle();
      document.addEventListener('frm_added_field', hideEmptyEle);
      maybeHideQuantityProductFieldOption();
      handleNameFieldOnFormBuilder();
      toggleSectionHolder();
      handleShowPasswordLiveUpdate();
      document.addEventListener('scroll', updateShortcodesPopupPosition, true);
      document.addEventListener('change', handleBuilderChangeEvent);
      document.querySelector('.frm_form_builder').addEventListener('mousedown', function (event) {
        if (event.shiftKey) {
          event.preventDefault();
        }
      });
      wp.hooks.addAction('frmShowedFieldSettings', 'formidableAdmin', function (showBtn, fieldSettingsEl) {
        fieldSettingsEl.querySelectorAll('.frm-collapse-me').forEach(addSlideAnimationCssVars);
      }, 9999);
    },
    settingsInit: function settingsInit() {
      var $formActions = jQuery(document.getElementById('frm_notification_settings'));
      var formSettings, $loggedIn, $cookieExp, $editable;

      // BCC, CC, and Reply To button functionality
      $formActions.on('click', '.frm_email_buttons', showEmailRow);
      $formActions.on('click', '.frm_remove_field', hideEmailRow);
      $formActions.on('change', '.frm_to_row, .frm_from_row', showEmailWarning);
      $formActions.on('change', '.frm_tax_selector', changePosttaxRow);
      $formActions.on('change', 'select.frm_single_post_field', checkDupPost);
      $formActions.on('change', 'select.frm_toggle_post_content', togglePostContent);
      $formActions.on('change', 'select.frm_dyncontent_opt', fillDyncontent);
      $formActions.on('change', '.frm_post_type', switchPostType);
      $formActions.on('click', '.frm_add_postmeta_row', addPostmetaRow);
      $formActions.on('click', '.frm_add_posttax_row', addPosttaxRow);
      $formActions.on('click', '.frm_toggle_cf_opts', toggleCfOpts);
      $formActions.on('click', '.frm_duplicate_form_action', copyFormAction);
      jQuery('.frm_actions_list').on('click', '.frm_active_action', addFormAction);
      jQuery('#frm-show-groups, #frm-hide-groups').on('click', toggleActionGroups);
      initiateMultiselect();

      //set actions icons to inactive
      jQuery('ul.frm_actions_list li').each(function () {
        checkActiveAction(jQuery(this).children('a').data('actiontype'));

        // If the icon is a background image, don't add BG color.
        var icon = jQuery(this).find('i');
        if (icon.css('background-image') !== 'none') {
          icon.addClass('frm-inverse');
        }
      });
      jQuery('.frm_submit_settings_btn').on('click', submitSettings);
      addFormNameModalEvents();
      formSettings = jQuery('.frm_form_settings');
      formSettings.on('click', '.frm_add_form_logic', addFormLogicRow);
      formSettings.on('click', '.frm_already_used', actionLimitMessage);
      document.addEventListener('click', function handleImageUploadClickEvents(event) {
        var target = event.target;
        if (!target.closest('.frm_image_preview_wrapper')) {
          return;
        }
        if (target.closest('.frm_choose_image_box')) {
          addImageToOption.bind(target)(event);
          return;
        }
        if (target.closest('.frm_remove_image_option')) {
          removeImageFromOption.bind(target)(event);
        }
      });

      // Close shortcode modal on click.
      formSettings.on('mouseup', '*:not(.frm-show-box)', maybeHideShortcodes);

      //Warning when user selects "Do not store entries ..."
      jQuery(document.getElementById('no_save')).on('change', function () {
        if (this.checked) {
          if (confirm(frmAdminJs.no_save_warning) !== true) {
            // Uncheck box if user hits "Cancel"
            jQuery(this).attr('checked', false);
          }
        }
      });
      jQuery('select[name="options[edit_action]"]').on('change', showSuccessOpt);
      $loggedIn = document.getElementById('logged_in');
      jQuery($loggedIn).on('change', function () {
        if (this.checked) {
          visible('.hide_logged_in');
        } else {
          invisible('.hide_logged_in');
        }
      });
      $cookieExp = jQuery(document.getElementById('frm_cookie_expiration'));
      jQuery(document.getElementById('frm_single_entry_type')).on('change', function () {
        if (this.value === 'cookie') {
          $cookieExp.fadeIn('slow');
        } else {
          $cookieExp.fadeOut('slow');
        }
      });
      var $singleEntry = document.getElementById('single_entry');
      jQuery($singleEntry).on('change', function () {
        if (this.checked) {
          visible('.hide_single_entry');
        } else {
          invisible('.hide_single_entry');
        }
        if (this.checked && jQuery(document.getElementById('frm_single_entry_type')).val() === 'cookie') {
          $cookieExp.fadeIn('slow');
        } else {
          $cookieExp.fadeOut('slow');
        }
      });
      jQuery('.hide_save_draft').hide();
      var $saveDraft = jQuery(document.getElementById('save_draft'));
      $saveDraft.on('change', function () {
        if (this.checked) {
          jQuery('.hide_save_draft').fadeIn('slow');
        } else {
          jQuery('.hide_save_draft').fadeOut('slow');
        }
      });
      triggerChange($saveDraft);

      //If Allow editing is checked/unchecked
      $editable = document.getElementById('editable');
      jQuery($editable).on('change', function () {
        if (this.checked) {
          jQuery('.hide_editable').fadeIn('slow');
          triggerChange(document.getElementById('edit_action'));
        } else {
          jQuery('.hide_editable').fadeOut('slow');
          jQuery('.edit_action_message_box').fadeOut('slow'); //Hide On Update message box
        }
      });

      //If File Protection is checked/unchecked
      jQuery(document).on('change', '#protect_files', function () {
        if (this.checked) {
          jQuery('.hide_protect_files').fadeIn('slow');
        } else {
          jQuery('.hide_protect_files').fadeOut('slow');
        }
      });
      jQuery(document).on('frm-multiselect-changed', '#protect_files_role', adjustVisibilityValuesForEveryoneValues);
      jQuery(document).on('submit', '.frm_form_settings', settingsSubmitted);
      jQuery(document).on('change', '#form_settings_page input:not(.frm-search-input), #form_settings_page select, #form_settings_page textarea', fieldUpdated);

      // Page Selection Autocomplete
      initAutocomplete();
      jQuery(document).on('frm-action-loaded', onActionLoaded);
      initOnSubmitAction();
      wp.hooks.addAction('frm_reset_fields_updated', 'formidableAdmin', resetFieldsUpdated);
    },
    panelInit: function panelInit() {
      var customPanel, settingsPage, viewPage, insertFieldsTab;
      jQuery('.frm_wrap, #postbox-container-1').on('click', '.frm_insert_code', insertCode);
      jQuery(document).on('change', '.frm_insert_val', function () {
        insertFieldCode(jQuery(this).data('target'), jQuery(this).val());
        jQuery(this).val('');
      });
      jQuery(document).on('click change', '[name="frm-id-key-condition"]', resetLogicBuilder);
      jQuery(document).on('keyup change', '.frm-build-logic', setLogicExample);
      showInputIcon();
      jQuery(document).on('frmElementAdded', function (event, parentEle) {
        /* This is here for add-ons to trigger */
        showInputIcon(parentEle);
      });
      jQuery(document).on('mousedown', '.frm-show-box', showShortcodes);
      settingsPage = document.getElementById('form_settings_page');
      viewPage = document.body.classList.contains('post-type-frm_display');
      insertFieldsTab = document.getElementById('frm_insert_fields_tab');
      if (settingsPage !== null || viewPage || builderPage) {
        jQuery(document).on('focusin', 'form input, form textarea', function (e) {
          var htmlTab;
          e.stopPropagation();
          maybeShowModal(this);
          if (jQuery(this).is(':not(:submit, input[type=button], .frm-search-input, input[type=checkbox])')) {
            if (jQuery(e.target).closest('#frm_adv_info').length) {
              // Don't trigger for fields inside of the modal.
              return;
            }
            if (settingsPage !== null || builderPage) {
              /* form settings page */
              htmlTab = jQuery('#frm_html_tab');
              if (jQuery(this).closest('#html_settings').length > 0) {
                htmlTab.show();
                htmlTab.siblings().hide();
                jQuery('#frm_html_tab a').trigger('click');
                toggleAllowedHTML(this);
              } else {
                showElement(jQuery('.frm-category-tabs li'));
                insertFieldsTab.click();
                htmlTab.hide();
                htmlTab.siblings().show();
              }
            } else if (viewPage) {
              var event = new CustomEvent('frm_legacy_views_handle_field_focus');
              event.frmData = {
                idAttrValue: this.id
              };
              document.dispatchEvent(event);
            }
          }
        });
      }
      jQuery('.frm_wrap, #postbox-container-1').on('mousedown', '#frm_adv_info a, .frm_field_list a', function (e) {
        e.preventDefault();
      });
      customPanel = jQuery('#frm_adv_info');
      customPanel.on('click', '.subsubsub a.frmids', function (e) {
        toggleKeyID('frmids', e);
      });
      customPanel.on('click', '.subsubsub a.frmkeys', function (e) {
        toggleKeyID('frmkeys', e);
      });
    },
    inboxInit: function inboxInit() {
      var _document$getElementB6;
      jQuery('.frm_inbox_dismiss').on('click', function (e) {
        var message = this.parentNode.parentNode;
        var key = message.getAttribute('data-message');
        var href = this.getAttribute('href');
        var dismissedMessage = message.cloneNode(true);
        var dismissedMessagesWrapper = document.querySelector('.frm-dismissed-inbox-messages');
        if ('free_templates' === key && !this.classList.contains('frm_inbox_dismiss')) {
          return;
        }
        e.preventDefault();
        data = {
          action: 'frm_inbox_dismiss',
          key: key,
          nonce: frmGlobal.nonce
        };
        var isInboxSlideIn = 'frm_inbox_slide_in' === message.id;
        if (isInboxSlideIn) {
          message.classList.remove('s11-fadein');
          message.classList.add('s11-fadeout');
          message.addEventListener('animationend', function () {
            return message.remove();
          }, {
            once: true
          });
        }
        postAjax(data, function () {
          if (isInboxSlideIn) {
            return;
          }
          if (href !== '#') {
            window.location = href;
            return true;
          }
          fadeOut(message, function () {
            if (null !== dismissedMessagesWrapper) {
              var _dismissedMessage$que;
              dismissedMessage.classList.remove('frm-fade');
              (_dismissedMessage$que = dismissedMessage.querySelector('.frm-inbox-message-heading')) === null || _dismissedMessage$que === void 0 || _dismissedMessage$que.removeChild(dismissedMessage.querySelector('.frm-inbox-message-heading .frm_inbox_dismiss'));
              dismissedMessagesWrapper.append(dismissedMessage);
            }
            if (1 === message.parentNode.querySelectorAll('.frm-inbox-message-container').length) {
              document.getElementById('frm_empty_inbox').classList.remove('frm_hidden');
              message.parentNode.closest('.frm-active').classList.add('frm-empty-inbox');
              showActiveCampaignForm();
            }
            message.parentNode.removeChild(message);
          });
        });
      });
      if (false === ((_document$getElementB6 = document.getElementById('frm_empty_inbox')) === null || _document$getElementB6 === void 0 ? void 0 : _document$getElementB6.classList.contains('frm_hidden'))) {
        showActiveCampaignForm();
      }
    },
    solutionInit: function solutionInit() {
      jQuery(document).on('submit', '#frm-new-template', installTemplate);
    },
    styleInit: function styleInit() {
      var $previewWrapper = jQuery('.frm_image_preview_wrapper');
      $previewWrapper.on('click', '.frm_choose_image_box', addImageToOption);
      $previewWrapper.on('click', '.frm_remove_image_option', removeImageFromOption);
      wp.hooks.doAction('frm_style_editor_init');
    },
    customCSSInit: function customCSSInit() {
      console.warn('Calling frmAdminBuild.customCSSInit is deprecated.');
    },
    globalSettingsInit: function globalSettingsInit() {
      var licenseTab;
      jQuery(document).on('click', '[data-frmuninstall]', uninstallNow);
      initiateMultiselect();

      // activate addon licenses
      licenseTab = document.getElementById('licenses_settings');
      if (licenseTab !== null) {
        jQuery(licenseTab).on('click', '.edd_frm_save_license', saveAddonLicense);
      }

      // Solution install page
      jQuery(document).on('click', '#frm-new-template button', installTemplateFieldset);
      jQuery('#frm-dismissable-cta .dismiss').on('click', function (event) {
        event.preventDefault();
        jQuery.post(ajaxurl, {
          action: 'frm_lite_settings_upgrade',
          nonce: frmGlobal.nonce
        });
        jQuery('.settings-lite-cta').remove();
      });
      var captchaType = document.getElementById('frm_re_type');
      if (captchaType) {
        captchaType.addEventListener('change', handleCaptchaTypeChange);
      }
      document.querySelector('.frm_captchas').addEventListener('change', function (event) {
        var _document$querySelect8;
        var captchaValueOnLoad = (_document$querySelect8 = document.querySelector('.frm_captchas input[checked="checked"]')) === null || _document$querySelect8 === void 0 ? void 0 : _document$querySelect8.value;
        var showNote = event.target.value !== captchaValueOnLoad;
        document.querySelector('.captcha_settings .frm_note_style').classList.toggle('frm_hidden', !showNote);
      });

      // Set fieldsUpdated to 0 to avoid the unsaved changes pop up.
      frmDom.util.documentOn('submit', '.frm_settings_form', function () {
        return fieldsUpdated = 0;
      });
      var manageStyleSettings = document.getElementById('manage_styles_settings');
      if (manageStyleSettings) {
        manageStyleSettings.addEventListener('change', function (event) {
          var target = event.target;
          if ('SELECT' !== target.nodeName || !target.dataset.name || target.getAttribute('name')) {
            return;
          }
          target.setAttribute('name', target.dataset.name);
        });
      }
      var paymentsSettings = document.getElementById('payments_settings');
      var paymentSettingsTabs = paymentsSettings === null || paymentsSettings === void 0 ? void 0 : paymentsSettings.querySelectorAll('[name="frm_payment_section"]');
      if (paymentSettingsTabs) {
        paymentSettingsTabs.forEach(function (element) {
          element.addEventListener('change', function () {
            if (!element.checked) {
              return;
            }
            var label = paymentsSettings.querySelector("label[for=\"".concat(element.id, "\"]"));
            if (label) {
              label.setAttribute('aria-selected', 'true');
            }
            paymentSettingsTabs.forEach(function (tab) {
              if (tab === element) {
                return;
              }
              var label = paymentsSettings.querySelector("label[for=\"".concat(tab.id, "\"]"));
              if (label) {
                label.setAttribute('aria-selected', 'false');
              }
            });
          });
        });
      }
    },
    exportInit: function exportInit() {
      jQuery('.frm_form_importer').on('submit', startFormMigration);
      jQuery(document.getElementById('frm_export_xml')).on('submit', validateExport);
      jQuery('#frm_export_xml input, #frm_export_xml select').on('change', removeExportError);
      jQuery('input[name="frm_import_file"]').on('change', checkCSVExtension);
      document.querySelector('select[name="format"]').addEventListener('change', exportTypeChanged);
      jQuery('input[name="frm_export_forms[]"]').on('click', preventMultipleExport);
      initiateMultiselect();
      jQuery('.frm-feature-banner .dismiss').on('click', function (event) {
        event.preventDefault();
        jQuery.post(ajaxurl, {
          action: 'frm_dismiss_migrator',
          plugin: this.id,
          nonce: frmGlobal.nonce
        });
        this.parentElement.remove();
      });
      showOrHideRepeaters(getExportOption());
      document.querySelector('#frm-export-select-all').addEventListener('change', function (event) {
        document.querySelectorAll('[name="frm_export_forms[]"]').forEach(function (cb) {
          return cb.checked = event.target.checked;
        });
      });
    },
    inboxBannerInit: function inboxBannerInit() {
      var banner = document.getElementById('frm_banner');
      if (!banner) {
        return;
      }
      var dismissButton = banner.querySelector('.frm-banner-dismiss');
      document.addEventListener('click', function (event) {
        if (event.target !== dismissButton) {
          return;
        }
        var data = {
          action: 'frm_inbox_dismiss',
          key: banner.dataset.key,
          nonce: frmGlobal.nonce
        };
        postAjax(data, function () {
          jQuery(banner).fadeOut(400, function () {
            banner.remove();
          });
        });
      });
    },
    updateOpts: function updateOpts(fieldId, opts, modal) {
      var separate = usingSeparateValues(fieldId),
        action = isProductField(fieldId) ? 'frm_bulk_products' : 'frm_import_options';
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: action,
          field_id: fieldId,
          opts: opts,
          separate: separate,
          nonce: frmGlobal.nonce
        },
        success: function success(html) {
          document.getElementById('frm_field_' + fieldId + '_opts').innerHTML = html;
          wp.hooks.doAction('frm_after_bulk_edit_opts', fieldId);
          resetDisplayedOpts(fieldId);
          if (typeof modal !== 'undefined') {
            modal.dialog('close');
            document.getElementById('frm-update-bulk-opts').classList.remove('frm_loading_button');
          }
        }
      });
    },
    /* remove conditional logic if the field doesn't exist */
    triggerRemoveLogic: function triggerRemoveLogic(fieldID, metaName) {
      jQuery('#frm_logic_' + fieldID + '_' + metaName + ' .frm_remove_tag').trigger('click');
    },
    downloadXML: function downloadXML(controller, ids, isTemplate) {
      var url = ajaxurl + '?action=frm_' + controller + '_xml&ids=' + ids;
      if (isTemplate !== null) {
        url = url + '&is_template=' + isTemplate;
      }
      location.href = url;
    },
    /**
     * @since 5.0.04
     */
    hooks: {
      applyFilters: function applyFilters(hookName) {
        var _wp$hooks;
        for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key2 = 1; _key2 < _len; _key2++) {
          args[_key2 - 1] = arguments[_key2];
        }
        return (_wp$hooks = wp.hooks).applyFilters.apply(_wp$hooks, [hookName].concat(args));
      },
      addFilter: function addFilter(hookName, callback, priority) {
        return wp.hooks.addFilter(hookName, 'formidable', callback, priority);
      },
      doAction: function doAction(hookName) {
        var _wp$hooks2;
        for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key3 = 1; _key3 < _len2; _key3++) {
          args[_key3 - 1] = arguments[_key3];
        }
        return (_wp$hooks2 = wp.hooks).doAction.apply(_wp$hooks2, [hookName].concat(args));
      },
      addAction: function addAction(hookName, callback, priority) {
        return wp.hooks.addAction(hookName, 'formidable', callback, priority);
      }
    },
    applyZebraStriping: applyZebraStriping,
    initModal: initModal,
    infoModal: infoModal,
    offsetModalY: offsetModalY,
    adjustConditionalLogicOptionOrders: adjustConditionalLogicOptionOrders,
    addRadioCheckboxOpt: addRadioCheckboxOpt,
    installNewForm: installNewForm,
    toggleAddonState: toggleAddonState,
    purifyHtml: purifyHtml,
    loadApiEmailForm: loadApiEmailForm,
    addMyEmailAddress: addMyEmailAddress,
    fillDropdownOpts: fillDropdownOpts,
    showSaveAndReloadModal: showSaveAndReloadModal,
    clearSettingsBox: clearSettingsBox,
    deleteField: deleteField,
    insertFormField: insertFormField,
    confirmLinkClick: confirmLinkClick,
    handleInsertFieldByDraggingResponse: handleInsertFieldByDraggingResponse,
    handleAddFieldClickResponse: handleAddFieldClickResponse,
    syncLayoutClasses: syncLayoutClasses,
    moveFieldSettings: moveFieldSettings
  };
};
window.frmAdminBuild = frmAdminBuildJS();
jQuery(document).ready(function () {
  var _document$querySelect9;
  frmAdminBuild.init();
  document.querySelectorAll('.frm-dropdown-menu').forEach(convertOldBootstrapDropdownsToBootstrap5);
  (_document$querySelect9 = document.querySelector('.preview.dropdown .frm-dropdown-toggle')) === null || _document$querySelect9 === void 0 || _document$querySelect9.setAttribute('data-bs-toggle', 'dropdown');

  // Bootstrap 5 uses data-bs-toggle instead of data-toggle.
  document.querySelectorAll('[data-toggle]').forEach(function (toggle) {
    return toggle.setAttribute('data-bs-toggle', toggle.getAttribute('data-toggle'));
  });
  function convertOldBootstrapDropdownsToBootstrap5(frmDropdownMenu) {
    frmDropdownMenu.classList.add('dropdown-menu');
    var toggle = frmDropdownMenu.querySelector('.frm-dropdown-toggle');
    if (toggle) {
      if (!toggle.hasAttribute('role')) {
        toggle.setAttribute('role', 'button');
      }
      if (!toggle.hasAttribute('tabindex')) {
        toggle.setAttribute('tabindex', 0);
      }
    }

    // Convert <li> and <ul> tags.
    if ('UL' === frmDropdownMenu.tagName) {
      convertBootstrapUl(frmDropdownMenu);
    }
  }
  function convertBootstrapUl(ul) {
    var html = ul.outerHTML;
    html = html.replace('<ul ', '<div ');
    html = html.replace('</ul>', '</div>');
    html = html.replaceAll('<li>', '<div class="dropdown-item">');
    html = html.replaceAll('<li class="', '<div class="dropdown-item ');
    html = html.replaceAll('</li>', '</div>');
    ul.outerHTML = html;
  }
});
window.frm_show_div = function (div, value, showIf, classId) {
  // eslint-disable-line camelcase
  if (value == showIf) {
    jQuery(classId + div).fadeIn('slow').css('visibility', 'visible');
  } else {
    jQuery(classId + div).fadeOut('slow');
  }
};
window.frmCheckAll = function (checked, n) {
  jQuery('input[name^="' + n + '"]').prop('checked', !!checked);
};
window.frmCheckAllLevel = function (checked, n, level) {
  var $kids = jQuery('.frm_catlevel_' + level).children('.frm_checkbox').children('label');
  $kids.children('input[name^="' + n + '"]').prop('checked', !!checked);
};
window.frmGetFieldValues = function (fieldId, cur, rowNumber, fieldType, htmlName, callback) {
  if (!fieldId) {
    return;
  }
  jQuery.ajax({
    type: 'POST',
    url: ajaxurl,
    data: 'action=frm_get_field_values&current_field=' + cur + '&field_id=' + fieldId + '&name=' + htmlName + '&t=' + fieldType + '&form_action=' + jQuery('input[name="frm_action"]').val() + '&nonce=' + frmGlobal.nonce,
    success: function success(msg) {
      document.getElementById('frm_show_selected_values_' + cur + '_' + rowNumber).innerHTML = msg;
      if ('function' === typeof callback) {
        callback();
      }
    }
  });
};
window.frmImportCsv = function (formID) {
  var urlVars = '';
  if (typeof __FRMURLVARS !== 'undefined') {
    urlVars = __FRMURLVARS;
  }
  jQuery.ajax({
    type: 'POST',
    url: ajaxurl,
    data: 'action=frm_import_csv&nonce=' + frmGlobal.nonce + '&frm_skip_cookie=1' + urlVars,
    success: function success(count) {
      var max = jQuery('.frm_admin_progress_bar').attr('aria-valuemax');
      var imported = max - count;
      var percent = imported / max * 100;
      jQuery('.frm_admin_progress_bar').css('width', percent + '%').attr('aria-valuenow', imported);
      if (parseInt(count, 10) > 0) {
        jQuery('.frm_csv_remaining').html(count);
        frmImportCsv(formID);
      } else {
        jQuery(document.getElementById('frm_import_message')).html(frm_admin_js.import_complete); // eslint-disable-line camelcase
        setTimeout(function () {
          location.href = '?page=formidable-entries&frm_action=list&form=' + formID + '&import-message=1';
        }, 2000);
      }
    }
  });
};
/******/ })()
;
//# sourceMappingURL=formidable_admin.js.map