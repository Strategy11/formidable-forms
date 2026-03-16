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
/* harmony import */ var _babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
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
/* harmony import */ var _babel_runtime_helpers_esm_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");


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
/* harmony import */ var _babel_runtime_helpers_esm_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
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

/***/ "./js/src/admin/components/dependent-updater-component.js":
/*!****************************************************************!*\
  !*** ./js/src/admin/components/dependent-updater-component.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ frmStyleDependentUpdaterComponent)
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
   *
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
   *
   * @param {string[]} inputNames - The names of the inputs to propagate changes to.
   * @return {HTMLElement[]} - The list of inputs to propagate changes to.
   */
  return _createClass(frmStyleDependentUpdaterComponent, [{
    key: "initPropagationList",
    value: function initPropagationList(inputNames) {
      var list = [];
      inputNames.forEach(function (name) {
        var input = document.querySelector("input[name=\"".concat(name, "\"]"));
        if (input) {
          list.push(input);
        }
      });
      return list;
    }

    /**
     * Updates all dependent elements with the given value.
     *
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

/***/ "./js/src/components/class-tabs-navigator.js":
/*!***************************************************!*\
  !*** ./js/src/components/class-tabs-navigator.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmTabsNavigator: () => (/* binding */ frmTabsNavigator)
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
    if (wrapper === undefined) {
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
    this.isRTL = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
    this.resizeObserver = null;
    this.init();
  }
  return _createClass(frmTabsNavigator, [{
    key: "init",
    value: function init() {
      var _this = this;
      if (null === this.wrapper || !this.navs.length || null === this.slideTrackLine || null === this.slideTrack || !this.slides.length) {
        return;
      }
      this.navs.forEach(function (nav, index) {
        nav.addEventListener('click', function (event) {
          return _this.onNavClick(event, index);
        });
        if (nav.classList.contains('frm-active')) {
          _this.initSlideTrackUnderline(nav);
        }
      });
      this.slideTrackLine.style.display = 'block';
      this.setupScrollbarObserver();
      // Cleanup observers when page unloads to prevent memory leaks
      window.addEventListener('beforeunload', this.cleanupObservers);
    }
  }, {
    key: "onNavClick",
    value: function onNavClick(event, index) {
      var navItem = event.currentTarget;
      event.preventDefault();
      this.removeActiveClassnameFromNavs();
      navItem.classList.add('frm-active');
      this.initSlideTrackUnderline(navItem);
      this.changeSlide(index);

      // Handle special case for frm_insert_fields_tab
      var navLink = navItem.querySelector('a');
      if (navLink && navLink.id === 'frm_insert_fields_tab' && !navLink.closest('#frm_adv_info')) {
        var _window$frmAdminBuild, _window$frmAdminBuild2;
        (_window$frmAdminBuild = window.frmAdminBuild) === null || _window$frmAdminBuild === void 0 || (_window$frmAdminBuild2 = _window$frmAdminBuild.clearSettingsBox) === null || _window$frmAdminBuild2 === void 0 || _window$frmAdminBuild2.call(_window$frmAdminBuild);
      }
    }
  }, {
    key: "initSlideTrackUnderline",
    value: function initSlideTrackUnderline(nav) {
      var activeNav = nav !== undefined ? nav : this.navs.filter(function (nav) {
        return nav.classList.contains('frm-active');
      });
      this.positionUnderlineIndicator(activeNav);
    }

    /**
     * Sets up a ResizeObserver to watch for scrollbar changes in the parent container.
     * Automatically repositions the underline indicator when layout changes occur.
     */
  }, {
    key: "setupScrollbarObserver",
    value: function setupScrollbarObserver() {
      var _this2 = this;
      var resizeObserverTarget = document.querySelector('.frm-scrollbar-wrapper, .styling_settings') || document.body;
      if (!resizeObserverTarget || !('ResizeObserver' in window)) {
        return;
      }
      this.resizeObserver = new ResizeObserver(function () {
        var activeNav = _this2.wrapper.querySelector('.frm-tabs-navs ul > li.frm-active');
        if (activeNav) {
          _this2.positionUnderlineIndicator(activeNav);
        }
      });
      this.resizeObserver.observe(resizeObserverTarget);
    }

    /**
     * Cleans up observers to prevent memory leaks.
     */
  }, {
    key: "cleanupObservers",
    value: function cleanupObservers() {
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
        this.resizeObserver = null;
      }
    }

    /**
     * Positions the underline indicator based on the active navigation element.
     *
     * @param {HTMLElement} activeNav The active navigation element to position the underline under
     */
  }, {
    key: "positionUnderlineIndicator",
    value: function positionUnderlineIndicator(activeNav) {
      var _this3 = this;
      requestAnimationFrame(function () {
        var position = _this3.isRTL ? -(activeNav.parentElement.offsetWidth - activeNav.offsetLeft - activeNav.offsetWidth) : activeNav.offsetLeft;
        _this3.slideTrackLine.style.transform = "translateX(".concat(position, "px)");
        _this3.slideTrackLine.style.width = "".concat(activeNav.clientWidth, "px");
      });
    }
  }, {
    key: "changeSlide",
    value: function changeSlide(index) {
      this.removeActiveClassnameFromSlides();
      var translate = index == 0 ? '0px' : "calc( ( ".concat(index * 100, "% + ").concat(parseInt(this.flexboxSlidesGap, 10) * index, "px ) * ").concat(this.isRTL ? 1 : -1, " )");
      if ('0px' !== translate) {
        this.slideTrack.style.transform = "translateX(".concat(translate, ")");
      } else {
        this.slideTrack.style.removeProperty('transform');
      }
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

/***/ "./js/src/settings-components/components/slider-component.js":
/*!*******************************************************************!*\
  !*** ./js/src/settings-components/components/slider-component.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ frmSliderComponent)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var _admin_components_dependent_updater_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../admin/components/dependent-updater-component */ "./js/src/admin/components/dependent-updater-component.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Internal dependencies
 */



/**
 * Represents a slider component.
 *
 * @class frmSliderComponent
 */
var frmSliderComponent = /*#__PURE__*/function () {
  function frmSliderComponent() {
    var _this = this;
    var sliderElements = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    _classCallCheck(this, frmSliderComponent);
    this.loadedByWebComponent = sliderElements.length > 0;
    this.sliderElements = sliderElements.length > 0 ? sliderElements : document.querySelectorAll('.frm-slider-component');
    this.settings = settings;
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
   * Initializes the options for the slider component.
   */
  return _createClass(frmSliderComponent, [{
    key: "initOptions",
    value: function initOptions() {
      var _this2 = this;
      this.options = [];
      this.sliderElements.forEach(function (element, index) {
        var parentWrapper = element.classList.contains('frm-has-multiple-values') ? element.closest('.frm-style-component') : element;
        var steps = _this2.settings.steps || (element.dataset.steps ? JSON.parse(element.dataset.steps) : null);
        _this2.options.push({
          dragging: false,
          startX: 0,
          translateX: 0,
          maxValue: parseInt(element.dataset.maxValue, 10),
          element: element,
          index: index,
          value: 0,
          steps: steps,
          dependentUpdater: parentWrapper.classList.contains('frm-style-dependent-updater-component') ? new _admin_components_dependent_updater_component__WEBPACK_IMPORTED_MODULE_1__["default"](parentWrapper) : null
        });
      });
    }

    /**
     * Initializes the slider component.
     */
  }, {
    key: "init",
    value: function init() {
      this.initDraggable();
      if (this.loadedByWebComponent) {
        this.initSlidersPositionInsideWebComponent();
        return;
      }
      this.initSlidersPosition();
    }

    /**
     * Initializes the draggable functionality for the slider component.
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
        frmSliderComponent.maybeDisableUnitDropdown(element);
        draggableBullet.addEventListener('mousedown', function (event) {
          event.preventDefault();
          event.stopPropagation();
          if (element.classList.contains('frm-disabled')) {
            return;
          }
          _this3.enableDragging(event, index);
          var onMouseMove = function onMouseMove(moveEvent) {
            return _this3.moveTracker(moveEvent, index);
          };
          var _onMouseUp = function onMouseUp() {
            _this3.disableDragging(index);
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', _onMouseUp);
          };
          document.addEventListener('mousemove', onMouseMove);
          document.addEventListener('mouseup', _onMouseUp);
        });
      });
    }
  }, {
    key: "expandSliderGroup",
    value: function expandSliderGroup(element) {
      var svgIcon = element.querySelector('.frmsvg');
      if (element.dataset.displaySliders === undefined || null === svgIcon) {
        return;
      }
      var sliderGroupItems = this.getSliderGroupItems(element);
      svgIcon.addEventListener('click', function () {
        sliderGroupItems.forEach(function (item) {
          item.classList.toggle(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
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
        bubbles: true,
        cancelable: true
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
        var value = frmSliderComponent.calculateValue(sliderWidth, deltaX, _this5.getMaxValue(unit, index), _this5.options[index].steps);
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
     * Disables the unit dropdown if there is only a single unit option.
     *
     * @param {HTMLElement} element - The slider element.
     */
  }, {
    key: "getSliderGroupItems",
    value:
    /**
     * Retrieves an array of slider group items based on the provided element.
     *
     * @param {HTMLElement} element - The element to retrieve slider group items from.
     * @return {NodeList} - An array-like object containing the slider group items.
     */
    function getSliderGroupItems(element) {
      if (element.dataset.displaySliders === undefined) {
        return [];
      }
      var slidersGroup = element.dataset.displaySliders.split(',');
      var query = slidersGroup.map(function (item) {
        return ".frm-slider-component[data-type=\"".concat(item, "\"]");
      }).join(', ');
      return element.closest('.frm-style-component').querySelectorAll(query);
    }
  }, {
    key: "initSlidersPositionInsideWebComponent",
    value: function initSlidersPositionInsideWebComponent() {
      var _this6 = this;
      this.sliderElements.forEach(function (element, index) {
        _this6.initSliderWidth(element, index);
      });
    }

    /**
     * Initializes the position of sliders when a accordion section is opened.
     */
  }, {
    key: "initSlidersPosition",
    value: function initSlidersPosition() {
      var _this7 = this;
      var emailSettings = document.querySelector('.email_settings');
      if (emailSettings) {
        this.initSlidersWidth(emailSettings);
        return;
      }
      var accordionitems = document.querySelectorAll('#frm_style_sidebar .accordion-section h3');
      var quickSettings = document.querySelector('.frm-quick-settings');
      var openedAccordion = document.querySelector('.accordion-section.open');

      // Detect if upload background image upload has triggered and initialize the "Image Opacity" slider width.
      wp.hooks.addAction('frm_pro_on_bg_image_upload', 'formidable', function (event) {
        var imageBackgroundOpacitySlider = event.closest('.accordion-section-content').querySelector('#frm-bg-image-opacity-slider');
        _this7.initSlidersWidth(imageBackgroundOpacitySlider);
      });

      // init the sliders width from "Quick Settings" page.
      if (quickSettings) {
        this.initSlidersWidth(quickSettings);
      }

      // Init the sliders width in opened accordion section from "Advanced Settings" page.
      if (openedAccordion) {
        this.initSlidersWidth(openedAccordion);
      }

      // init the sliders width everytime when an accordion section is opened from "Advanced Settings" page.
      accordionitems.forEach(function (item) {
        item.addEventListener('click', function (event) {
          _this7.initSlidersWidth(event.target.closest('.accordion-section'));
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
      var _this8 = this;
      var fieldShapeType = document.querySelector('.frm-style-component.frm-field-shape');
      if (!fieldShapeType) {
        return;
      }
      var radioButtons = fieldShapeType.querySelectorAll('input[type="radio"]');
      radioButtons.forEach(function (radio) {
        radio.addEventListener('change', function (event) {
          if (event.target.checked && 'rounded-corner' === event.target.value) {
            var slider = document.querySelector('div[data-frm-element="field-shape-corner-radius"] .frm-slider-component');
            _this8.initSliderWidth(slider);
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
      var _this9 = this;
      var sliders = section.querySelectorAll('.frm-slider-component');
      sliders.forEach(function (slider) {
        setTimeout(function () {
          _this9.initSliderWidth(slider);
        }, 100);
      });
    }

    /**
     * Initializes the width of a slider.
     *
     * @param {HTMLElement} slider      - The slider element.
     * @param {number}      sliderIndex - The index of the slider.
     * @return {void}
     */
  }, {
    key: "initSliderWidth",
    value: function initSliderWidth(slider) {
      var sliderIndex = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      if (slider.classList.contains('frm-disabled')) {
        return;
      }
      var index = sliderIndex !== null ? sliderIndex : this.getSliderIndex(slider);
      var sliderWidth = slider.querySelector('.frm-slider').offsetWidth - this.sliderBulletWidth;
      var value = parseInt(slider.querySelector('.frm-slider-value input[type="text"]').value, 10);
      var unit = slider.querySelector('select').value;
      var steps = this.options[index].steps;
      var deltaX = Math.ceil(value / this.options[index].maxValue * sliderWidth);
      if ('%' === unit) {
        deltaX = Math.round(sliderWidth * value / 100);
      } else if (steps && steps.length > 0) {
        deltaX = frmSliderComponent.calculateDeltaXFromSteps(value, steps, sliderWidth);
      }
      slider.querySelector('.frm-slider-active-track').style.width = "".concat(deltaX, "px");
      this.options[index].translateX = deltaX;
      this.options[index].value = value + unit;
    }

    /**
     * Calculates the deltaX position based on a value and steps array.
     *
     * @param {number} value       - The current value.
     * @param {Array}  steps       - Array of step values.
     * @param {number} sliderWidth - The width of the slider.
     * @return {number} - The calculated deltaX position.
     */
  }, {
    key: "initChildSlidersWidth",
    value:
    /**
     * Initializes the width of child sliders.
     *
     * @param {HTMLElement} slider - The parent slider element.
     * @param {number}      width  - The width to set for the child sliders.
     * @param {number}      index  - The starting index for the child sliders.
     * @param {number}      value  - The value to set for the child sliders.
     */
    function initChildSlidersWidth(slider, width, index, value) {
      var _this0 = this;
      if (!slider.classList.contains('frm-has-independent-fields') && !slider.classList.contains('frm-has-multiple-values')) {
        return;
      }
      var childSliders = slider.classList.contains('frm-has-independent-fields') ? slider.querySelectorAll('.frm-independent-slider-field') : this.getSliderGroupItems(slider);
      childSliders.forEach(function (item, childIndex) {
        item.querySelector('.frm-slider-active-track').style.width = "".concat(width, "px");
        _this0.options[index + childIndex + 1].translateX = width;
        _this0.options[index + childIndex + 1].value = value;
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

    /**
     * Handles the movement of the slider tracker.
     *
     * @param {Event}  event - The event object representing the mouse movement.
     * @param {number} index - The index of the slider element.
     * @return {void}
     */
  }, {
    key: "moveTracker",
    value: function moveTracker(event, index) {
      if (!this.options[index].dragging) {
        return;
      }
      var deltaX = event.clientX - this.options[index].startX;
      var element = this.sliderElements[index];
      var sliderWidth = element.querySelector('.frm-slider').offsetWidth - this.sliderBulletWidth;

      // Clamp deltaX within valid range
      deltaX = Math.max(deltaX, 0);
      deltaX = Math.min(deltaX, sliderWidth);
      var unit = element.querySelector('select').value;
      var value = frmSliderComponent.calculateValue(sliderWidth, deltaX, this.getMaxValue(unit, index), this.options[index].steps);
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
     * Enables dragging for the slider component.
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
     */
  }, {
    key: "disableDragging",
    value: function disableDragging(index) {
      if (false === this.options[index].dragging) {
        return;
      }
      var draggableBullet = this.sliderElements[index].querySelector('.frm-slider-bullet');
      draggableBullet.classList.remove('frm-dragging');
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
      var _this1 = this;
      if (null !== this.options[index].dependentUpdater) {
        this.options[index].dependentUpdater.updateAllDependentElements(this.options[index].fullValue);
        return;
      }
      var input = this.sliderElements[index].classList.contains('frm-has-multiple-values') ? this.sliderElements[index].closest('.frm-style-component').querySelector('input[type="hidden"]') : this.sliderElements[index].querySelectorAll('.frm-slider-value input[type="hidden"]');
      if (input instanceof NodeList) {
        input.forEach(function (item) {
          item.dispatchEvent(_this1.eventsChange[index]);
        });
        return;
      }
      input.dispatchEvent(this.eventsChange[index]);
    }

    /**
     * Calculates the value based on the width, deltaX, and maxValue.
     *
     * @param {number}     width    - The width of the slider.
     * @param {number}     deltaX   - The change in x-coordinate.
     * @param {number}     maxValue - The maximum value.
     * @param {Array|null} steps    - Optional array of step values to snap to.
     * @return {number} - The calculated value.
     */
  }, {
    key: "updateValue",
    value:
    /**
     * Updates the value of a slider component.
     *
     * @param {HTMLElement} element - The slider component element.
     * @param {string}      value   - The new value to be set.
     * @return {string} - The updated value.
     */
    function updateValue(element, value) {
      var _this10 = this;
      // When the slider component is used for "Base Font Size", we need to update a hidden input field when change happens to indicate that the "Base Font Size" has been adjusted.
      // Used to avoid conflicts with other possible font sizes adjustemnts in "Advanced Settings" when moving from "Quick Settings" when "Base Font Size" is not changed.
      if (element.classList.contains('frm-base-font-size')) {
        var userBaseFontSizeInput = document.querySelector('input[name="frm_style_setting[post_content][use_base_font_size]"]');
        if (userBaseFontSizeInput) {
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
          var unitMeasure = _this10.getUnitMeasureFromValue(value);
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
  }], [{
    key: "maybeDisableUnitDropdown",
    value: function maybeDisableUnitDropdown(element) {
      var select = element.querySelector('select');
      if (!select) {
        return;
      }
      var options = Array.from(select.options).filter(function (option) {
        return '' !== option.value;
      });
      if (1 >= options.length) {
        select.classList.add('frm-single-unit');
        select.addEventListener('mousedown', function (event) {
          return event.preventDefault();
        });
      }
    }
  }, {
    key: "calculateDeltaXFromSteps",
    value: function calculateDeltaXFromSteps(value, steps, sliderWidth) {
      var stepIndex = steps.indexOf(value);
      if (-1 === stepIndex) {
        // If value not in steps, find closest and use its position
        var closestValue = frmSliderComponent.snapToStep(value, steps);
        var closestIndex = steps.indexOf(closestValue);
        return Math.round(closestIndex / (steps.length - 1) * sliderWidth);
      }
      return Math.round(stepIndex / (steps.length - 1) * sliderWidth);
    }
  }, {
    key: "calculateValue",
    value: function calculateValue(width, deltaX, maxValue) {
      var steps = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
      if (steps && steps.length > 0) {
        // For stepped sliders, map position directly to step index
        var position = deltaX / width;
        var stepIndex = Math.round(position * (steps.length - 1));
        var clampedIndex = Math.max(0, Math.min(stepIndex, steps.length - 1));
        return steps[clampedIndex];
      }

      // Calculate value as a simple proportion of the slider position
      var value = Math.round(deltaX / width * maxValue);
      return Math.min(value, maxValue);
    }

    /**
     * Snaps a value to the nearest step in the steps array.
     *
     * @param {number} value - The value to snap.
     * @param {Array}  steps - Array of step values to snap to.
     * @return {number} - The nearest step value.
     */
  }, {
    key: "snapToStep",
    value: function snapToStep(value, steps) {
      var nearest = steps[0];
      var minDiff = Math.abs(value - nearest);
      for (var i = 1; i < steps.length; i++) {
        var diff = Math.abs(value - steps[i]);
        if (diff < minDiff) {
          minDiff = diff;
          nearest = steps[i];
        }
      }
      return nearest;
    }
  }]);
}();


/***/ }),

/***/ "./js/src/web-components/frm-border-radius-component/frm-border-radius-component.js":
/*!******************************************************************************************!*\
  !*** ./js/src/web-components/frm-border-radius-component/frm-border-radius-component.js ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmBorderRadiusComponent: () => (/* binding */ frmBorderRadiusComponent)
/* harmony export */ });
/* harmony import */ var _frm_web_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../frm-web-component */ "./js/src/web-components/frm-web-component.js");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _frm_border_radius_component_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./frm-border-radius-component.css */ "./js/src/web-components/frm-border-radius-component/frm-border-radius-component.css");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _superPropGet(t, o, e, r) { var p = _get(_getPrototypeOf(1 & r ? t.prototype : t), o, e); return 2 & r && "function" == typeof p ? function (t) { return p.apply(e, t); } : p; }
function _get() { return _get = "undefined" != typeof Reflect && Reflect.get ? Reflect.get.bind() : function (e, t, r) { var p = _superPropBase(e, t); if (p) { var n = Object.getOwnPropertyDescriptor(p, t); return n.get ? n.get.call(arguments.length < 3 ? e : r) : n.value; } }, _get.apply(null, arguments); }
function _superPropBase(t, o) { for (; !{}.hasOwnProperty.call(t, o) && null !== (t = _getPrototypeOf(t));); return t; }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }
function _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object"); }
function _classPrivateFieldSet(s, a, r) { return s.set(_assertClassBrand(s, a), r), r; }
function _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }
function _assertClassBrand(e, t, n) { if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError("Private element is not present on this object"); }



var _onChange = /*#__PURE__*/new WeakMap();
var _unitTypeOptions = /*#__PURE__*/new WeakMap();
var _value = /*#__PURE__*/new WeakMap();
var _defaultValue = /*#__PURE__*/new WeakMap();
var _usesMultipleValues = /*#__PURE__*/new WeakMap();
var frmBorderRadiusComponent = /*#__PURE__*/function (_frmWebComponent) {
  function frmBorderRadiusComponent() {
    var _this;
    _classCallCheck(this, frmBorderRadiusComponent);
    _this = _callSuper(this, frmBorderRadiusComponent);
    _classPrivateFieldInitSpec(_this, _onChange, function () {});
    _classPrivateFieldInitSpec(_this, _unitTypeOptions, ['px', 'em', '%']);
    _classPrivateFieldInitSpec(_this, _value, '0px');
    _classPrivateFieldInitSpec(_this, _defaultValue, '0px');
    _classPrivateFieldInitSpec(_this, _usesMultipleValues, false);
    _this.componentStyle = _frm_border_radius_component_css__WEBPACK_IMPORTED_MODULE_2__["default"];
    return _this;
  }
  _inherits(frmBorderRadiusComponent, _frmWebComponent);
  return _createClass(frmBorderRadiusComponent, [{
    key: "initOptions",
    value: function initOptions() {
      _superPropGet(frmBorderRadiusComponent, "initOptions", this, 3)([]);
      if (null === this.componentId) {
        var _frmBorderRadiusCompo;
        this.componentId = "frm-border-radius-web-component-".concat(_instanceCount._ = (_frmBorderRadiusCompo = _instanceCount._, ++_frmBorderRadiusCompo));
      }
    }
  }, {
    key: "initView",
    value: function initView() {
      this.wrapper = document.createElement('div');
      this.container = document.createElement('div');
      this.wrapper.classList.add('frm-border-radius-component');
      this.container.classList.add('frm-border-radius-container');
      this.container.append(this.getInputWrapper(), this.getButton(), this.getBorderIndividualInputsWrapper());
      this.wrapper.append(this.container);
      return this.wrapper;
    }
  }, {
    key: "parseDefaultValues",
    value: function parseDefaultValues() {
      if (!_classPrivateFieldGet(_defaultValue, this)) {
        return {
          top: {
            value: 0,
            unit: 'px'
          },
          bottom: {
            value: 0,
            unit: 'px'
          },
          left: {
            value: 0,
            unit: 'px'
          },
          right: {
            value: 0,
            unit: 'px'
          }
        };
      }
      var parts = _classPrivateFieldGet(_defaultValue, this).split(' ');
      return {
        top: frmBorderRadiusComponent.parseValueUnit(parts[0] || '0px'),
        bottom: frmBorderRadiusComponent.parseValueUnit(parts[2] || parts[0] || '0px'),
        left: frmBorderRadiusComponent.parseValueUnit(parts[3] || parts[1] || parts[0] || '0px'),
        right: frmBorderRadiusComponent.parseValueUnit(parts[1] || parts[0] || '0px')
      };
    }
  }, {
    key: "getInputWrapper",
    value: function getInputWrapper() {
      this.inputWrapper = document.createElement('div');
      this.inputWrapper.classList.add('frm-input-wrapper');
      this.inputWrapper.append(this.getInputValue(), this.getInputUnit(), this.getHiddenInput());
      return this.inputWrapper;
    }
  }, {
    key: "getHiddenInput",
    value: function getHiddenInput() {
      this.hiddenInput = document.createElement('input');
      this.hiddenInput.type = 'hidden';
      this.hiddenInput.value = _classPrivateFieldGet(_value, this);
      if (this.fieldName) {
        this.hiddenInput.name = this.fieldName;
      }
      return this.hiddenInput;
    }
  }, {
    key: "getInputValue",
    value: function getInputValue() {
      var _this2 = this;
      this.inputValue = document.createElement('input');
      this.inputValue.type = 'text';
      this.inputValue.id = "".concat(this.componentId, "-value");
      this.inputValue.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border radius value', 'formidable'));
      this.inputValue.classList.add('frm-input-value');
      if (!_classPrivateFieldGet(_usesMultipleValues, this)) {
        this.inputValue.value = parseInt(_classPrivateFieldGet(_defaultValue, this)) || 0;
      }
      this.inputValue.addEventListener('change', function () {
        var value = _this2.inputValue.value + _this2.inputUnit.value;
        _this2.hiddenInput.value = value;
        _this2.borderInputBottom.value = _this2.inputValue.value;
        _this2.borderInputTop.value = _this2.inputValue.value;
        _this2.borderInputLeft.value = _this2.inputValue.value;
        _this2.borderInputRight.value = _this2.inputValue.value;
        _this2.updateValue(value);
      });
      return this.inputValue;
    }
  }, {
    key: "getInputUnit",
    value: function getInputUnit() {
      var _this3 = this;
      this.inputUnit = document.createElement('select');
      this.inputUnit.id = "".concat(this.componentId, "-unit");
      this.inputUnit.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border radius unit', 'formidable'));
      this.inputUnit.classList.add('frm-input-unit');
      _classPrivateFieldGet(_unitTypeOptions, this).forEach(function (option) {
        var opt = document.createElement('option');
        opt.value = option;
        opt.textContent = option;
        _this3.inputUnit.append(opt);
      });
      this.inputUnit.addEventListener('change', function () {
        _this3.hiddenInput.value = _this3.inputValue.value + _this3.inputUnit.value;
      });
      return this.inputUnit;
    }
  }, {
    key: "getBorderIndividualInputsWrapper",
    value: function getBorderIndividualInputsWrapper() {
      this.borderIndividualInputsWrapper = document.createElement('div');
      this.borderIndividualInputsWrapper.classList.add('frm-border-individual-inputs-wrapper');
      if (!_classPrivateFieldGet(_usesMultipleValues, this)) {
        this.borderIndividualInputsWrapper.classList.add('frm_hidden');
      }
      this.borderIndividualInputsWrapper.append(this.getBorderInputTop(), this.getBorderInputRight(), this.getBorderInputLeft(), this.getBorderInputBottom());
      return this.borderIndividualInputsWrapper;
    }
  }, {
    key: "getBorderInputTop",
    value: function getBorderInputTop() {
      var _this4 = this;
      var defaultValues = this.parseDefaultValues();
      var span = document.createElement('span');
      span.classList.add('frm-border-input-top');
      this.borderInputTop = document.createElement('input');
      this.borderInputTop.type = 'text';
      this.borderInputTop.id = "".concat(this.componentId, "-top");
      this.borderInputTop.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Top border radius', 'formidable'));
      this.borderInputTop.value = parseInt(defaultValues.top.value);
      span.append(this.borderInputTop);
      this.borderInputTop.addEventListener('change', function () {
        return _this4.buildBorderRadiusIndividualValue();
      });
      return span;
    }
  }, {
    key: "getBorderInputBottom",
    value: function getBorderInputBottom() {
      var _this5 = this;
      var defaultValues = this.parseDefaultValues();
      var span = document.createElement('span');
      span.classList.add('frm-border-input-bottom');
      this.borderInputBottom = document.createElement('input');
      this.borderInputBottom.type = 'text';
      this.borderInputBottom.id = "".concat(this.componentId, "-bottom");
      this.borderInputBottom.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Bottom border radius', 'formidable'));
      this.borderInputBottom.value = parseInt(defaultValues.bottom.value);
      span.append(this.borderInputBottom);
      this.borderInputBottom.addEventListener('change', function () {
        return _this5.buildBorderRadiusIndividualValue();
      });
      return span;
    }
  }, {
    key: "getBorderInputLeft",
    value: function getBorderInputLeft() {
      var _this6 = this;
      var defaultValues = this.parseDefaultValues();
      var span = document.createElement('span');
      span.classList.add('frm-border-input-left');
      this.borderInputLeft = document.createElement('input');
      this.borderInputLeft.type = 'text';
      this.borderInputLeft.id = "".concat(this.componentId, "-left");
      this.borderInputLeft.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Left border radius', 'formidable'));
      this.borderInputLeft.value = parseInt(defaultValues.left.value);
      span.append(this.borderInputLeft);
      this.borderInputLeft.addEventListener('change', function () {
        return _this6.buildBorderRadiusIndividualValue();
      });
      return span;
    }
  }, {
    key: "getBorderInputRight",
    value: function getBorderInputRight() {
      var _this7 = this;
      var defaultValues = this.parseDefaultValues();
      var span = document.createElement('span');
      span.classList.add('frm-border-input-right');
      this.borderInputRight = document.createElement('input');
      this.borderInputRight.type = 'text';
      this.borderInputRight.id = "".concat(this.componentId, "-right");
      this.borderInputRight.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Right border radius', 'formidable'));
      this.borderInputRight.value = parseInt(defaultValues.right.value);
      span.append(this.borderInputRight);
      this.borderInputRight.addEventListener('change', function () {
        return _this7.buildBorderRadiusIndividualValue();
      });
      return span;
    }
  }, {
    key: "buildBorderRadiusIndividualValue",
    value: function buildBorderRadiusIndividualValue() {
      var unit = this.inputUnit.value;
      var value = "".concat(parseInt(this.borderInputTop.value, 10)).concat(unit, " ").concat(parseInt(this.borderInputRight.value, 10)).concat(unit, " ").concat(parseInt(this.borderInputBottom.value, 10)).concat(unit, " ").concat(parseInt(this.borderInputLeft.value, 10)).concat(unit);
      this.updateValue(value);
    }
  }, {
    key: "updateValue",
    value: function updateValue(value) {
      this.hiddenInput.value = value;
      _classPrivateFieldGet(_onChange, this).call(this, value);
    }
  }, {
    key: "getButton",
    value: function getButton() {
      var _this8 = this;
      this.button = document.createElement('button');
      this.button.type = 'button';
      this.button.textContent = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Border Radius', 'formidable');
      if (_classPrivateFieldGet(_usesMultipleValues, this)) {
        this.button.classList.add('frm-active');
      }
      this.button.addEventListener('click', function () {
        _this8.button.classList.toggle('frm-active');
        _this8.borderIndividualInputsWrapper.classList.toggle('frm_hidden');
      });
      return this.button;
    }
  }, {
    key: "onChange",
    set: function set(callback) {
      if ('function' !== typeof callback) {
        throw new TypeError("Expected a function, but received ".concat(_typeof(callback)));
      }
      _classPrivateFieldSet(_onChange, this, callback);
    }
  }, {
    key: "borderRadiusDefaultValue",
    set: function set(value) {
      _classPrivateFieldSet(_defaultValue, this, value);
      _classPrivateFieldSet(_usesMultipleValues, this, !/^(\d+)(px|em|%)?$/.test(value) && '' !== value);
    }
  }], [{
    key: "parseValueUnit",
    value: function parseValueUnit(valueStr) {
      var match = valueStr.match(/^(\d+)(px|em|%)?$/);
      if (!match) {
        return {
          value: 0,
          unit: 'px'
        };
      }
      return {
        value: parseInt(match[1], 10),
        unit: match[2] || 'px'
      };
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_0__.frmWebComponent);
var _instanceCount = {
  _: 0
};

/***/ }),

/***/ "./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.js":
/*!**************************************************************************************!*\
  !*** ./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.js ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmColorpickerComponent: () => (/* binding */ frmColorpickerComponent)
/* harmony export */ });
/* harmony import */ var _frm_web_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../frm-web-component */ "./js/src/web-components/frm-web-component.js");
/* harmony import */ var _frm_colorpicker_component_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./frm-colorpicker-component.css */ "./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.css");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }
function _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object"); }
function _classPrivateFieldSet(s, a, r) { return s.set(_assertClassBrand(s, a), r), r; }
function _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }
function _assertClassBrand(e, t, n) { if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError("Private element is not present on this object"); }


var _onChange = /*#__PURE__*/new WeakMap();
var _defaultValue = /*#__PURE__*/new WeakMap();
var frmColorpickerLiteComponent = /*#__PURE__*/function (_frmWebComponent) {
  function frmColorpickerLiteComponent() {
    var _this;
    _classCallCheck(this, frmColorpickerLiteComponent);
    _this = _callSuper(this, frmColorpickerLiteComponent);
    _classPrivateFieldInitSpec(_this, _onChange, function () {});
    _classPrivateFieldInitSpec(_this, _defaultValue, '#ffffff');
    _this.input = document.createElement('input');
    _this.componentStyle = _frm_colorpicker_component_css__WEBPACK_IMPORTED_MODULE_1__["default"];
    _this.attachInternals();
    return _this;
  }
  _inherits(frmColorpickerLiteComponent, _frmWebComponent);
  return _createClass(frmColorpickerLiteComponent, [{
    key: "initView",
    value: function initView() {
      var wrapper = document.createElement('div');
      wrapper.classList.add('frm-colorpicker-component', 'frm-colorpicker');
      wrapper.append(this.getInput());
      return wrapper;
    }
  }, {
    key: "getInput",
    value: function getInput() {
      var _this2 = this;
      this.input.type = 'text';
      this.input.classList.add('hex');
      if (null !== this.fieldName) {
        this.input.name = this.fieldName;
      }
      if (null !== _classPrivateFieldGet(_defaultValue, this)) {
        this.input.value = _classPrivateFieldGet(_defaultValue, this);
      }
      if (null !== this.componentId) {
        this.input.id = this.componentId;
      }
      this.input.addEventListener('blur', function (event) {
        return _classPrivateFieldGet(_onChange, _this2).call(_this2, event, null);
      });
      return this.input;
    }
  }, {
    key: "useShadowDom",
    value: function useShadowDom() {
      return false;
    }
  }, {
    key: "afterViewInit",
    value: function afterViewInit() {
      var _this3 = this;
      var colorPickerOptions = {
        defaultColor: _classPrivateFieldGet(_defaultValue, this)
      };
      if ('function' === typeof _classPrivateFieldGet(_onChange, this)) {
        // Use the setTimeout when calling the callback for onChange to make sure the latest color is fetched.
        colorPickerOptions.change = function (event, ui) {
          return setTimeout(function () {
            return _classPrivateFieldGet(_onChange, _this3).call(_this3, event, ui);
          }, 20);
        };
      }
      jQuery(this.input).wpColorPicker(colorPickerOptions);
    }

    /**
     * A method to get the color value.
     *
     * @return {string} - The color value.
     */
  }, {
    key: "color",
    get: function get() {
      return jQuery(this.input).wpColorPicker('color');
    }

    /**
     * A method to set the color value.
     *
     * @param {string} value - The value to set the color value for.
     * @return {void}
     */,
    set: function set(value) {
      _classPrivateFieldSet(_defaultValue, this, value);
      this.input.value = value;
    }

    /**
     * A method to set the change event listener for the color picker component.
     *
     * @param {Function} callback - The callback function to call when the color picker component is changed.
     * @return {void}
     */
  }, {
    key: "onChange",
    set: function set(callback) {
      if ('function' !== typeof callback) {
        throw new TypeError("Expected a function, but received ".concat(_typeof(callback)));
      }
      _classPrivateFieldSet(_onChange, this, callback);
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_0__.frmWebComponent); // The color picker component that may be a mixin of the color picker pro component.
_defineProperty(frmColorpickerLiteComponent, "formAssociated", true);
var frmColorpickerComponent = window.frmColorpickerProComponent ? window.frmColorpickerProComponent(frmColorpickerLiteComponent) : frmColorpickerLiteComponent;

/***/ }),

/***/ "./js/src/web-components/frm-dropdown-component/frm-dropdown-component.js":
/*!********************************************************************************!*\
  !*** ./js/src/web-components/frm-dropdown-component/frm-dropdown-component.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmDropdownComponent: () => (/* binding */ frmDropdownComponent)
/* harmony export */ });
/* harmony import */ var _frm_web_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../frm-web-component */ "./js/src/web-components/frm-web-component.js");
/* harmony import */ var _frm_dropdown_component_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./frm-dropdown-component.css */ "./js/src/web-components/frm-dropdown-component/frm-dropdown-component.css");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }
function _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object"); }
function _classPrivateFieldSet(s, a, r) { return s.set(_assertClassBrand(s, a), r), r; }
function _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }
function _assertClassBrand(e, t, n) { if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError("Private element is not present on this object"); }


var _onChange = /*#__PURE__*/new WeakMap();
var _select = /*#__PURE__*/new WeakMap();
var frmDropdownComponent = /*#__PURE__*/function (_frmWebComponent) {
  function frmDropdownComponent() {
    var _this;
    _classCallCheck(this, frmDropdownComponent);
    _this = _callSuper(this, frmDropdownComponent);
    _classPrivateFieldInitSpec(_this, _onChange, function () {});
    _classPrivateFieldInitSpec(_this, _select, document.createElement('select'));
    _this.componentStyle = _frm_dropdown_component_css__WEBPACK_IMPORTED_MODULE_1__["default"];
    _this.attachInternals();
    return _this;
  }

  /**
   * Initializes the view. Called when the component is rendered.
   *
   * @return {Element} - The wrapper element.
   */
  _inherits(frmDropdownComponent, _frmWebComponent);
  return _createClass(frmDropdownComponent, [{
    key: "initView",
    value: function initView() {
      this.wrapper = document.createElement('div');
      this.wrapper.classList.add('frm-dropdown-component');
      this.wrapper.append(this.getSelect());
      return this.wrapper;
    }

    /**
     * Gets the select element.
     *
     * @return {Element} - The select element.
     */
  }, {
    key: "getSelect",
    value: function getSelect() {
      if (this.componentId) {
        _classPrivateFieldGet(_select, this).id = this.componentId;
      }
      if (this.fieldName) {
        _classPrivateFieldGet(_select, this).name = this.fieldName;
      }
      return _classPrivateFieldGet(_select, this);
    }

    /**
     * Determines if the component should use shadow DOM. The dropdown component can utilize shadow DOM as it does not require external functional dependencies.
     *
     * @return {boolean} - True if the component should use shadow DOM, false otherwise.
     */
  }, {
    key: "useShadowDom",
    value: function useShadowDom() {
      return true;
    }

    /**
     * Initializes the select options. It will retrieve the all the options from the component and create new option elements.
     *
     * @return {void}
     */
  }, {
    key: "initSelectOptions",
    value: function initSelectOptions() {
      var _this2 = this;
      var optionsNodes = this.querySelectorAll('option');
      optionsNodes.forEach(function (option) {
        var opt = document.createElement('option');
        opt.value = option.value;
        opt.textContent = option.textContent;
        option.remove();
        _classPrivateFieldGet(_select, _this2).append(opt);
      });
    }

    /**
     * Called when the component is visible in the viewport.
     *
     * @return {void}
     */
  }, {
    key: "afterViewInit",
    value: function afterViewInit() {
      var _this3 = this;
      this.initSelectOptions();
      _classPrivateFieldGet(_select, this).addEventListener('change', function () {
        _classPrivateFieldGet(_onChange, _this3).call(_this3, _classPrivateFieldGet(_select, _this3).value);
      });
    }

    /**
     * A method to add options dynamically to the select element.
     *
     * @param {Array} options - The options to add.
     * @return {void}
     */
  }, {
    key: "addOptions",
    set: function set(options) {
      var _this4 = this;
      options.forEach(function (option) {
        var opt = document.createElement('option');
        opt.value = option.value;
        opt.textContent = option.label;
        opt.selected = option.selected;
        _classPrivateFieldGet(_select, _this4).append(opt);
      });
    }

    /**
     * A method to set the disabled state of the select element.
     *
     * @param {boolean} value - The value to set.
     * @return {void}
     */
  }, {
    key: "disabled",
    set: function set(value) {
      _classPrivateFieldGet(_select, this).disabled = value;
    }

    /**
     * A method to set the change event listener for the select element.
     *
     * @param {Function} callback - The callback function to call when the select element is changed.
     * @return {void}
     */
  }, {
    key: "onChange",
    set: function set(callback) {
      if ('function' !== typeof callback) {
        throw new TypeError("Expected a function, but received ".concat(_typeof(callback)));
      }
      _classPrivateFieldSet(_onChange, this, callback);
    }

    /**
     * A method to set the selected value of the select element.
     *
     * @param {string} value - The value to set.
     * @return {void}
     */
  }, {
    key: "selectedValue",
    set: function set(value) {
      var option = Array.from(_classPrivateFieldGet(_select, this).options).find(function (option) {
        return option.value === value;
      });
      if (option) {
        option.selected = true;
      }
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_0__.frmWebComponent);

// A shorthand function to create a dropdown component.
_defineProperty(frmDropdownComponent, "formAssociated", true);
window.frmDropdownComponent = function (id, options, onChangeCallback) {
  var dropdown = document.createElement('frm-dropdown-component');
  dropdown.id = id;
  dropdown.addOptions = options;
  dropdown.onChange = onChangeCallback;
  return dropdown;
};

/***/ }),

/***/ "./js/src/web-components/frm-range-slider-component/frm-range-slider-component.js":
/*!****************************************************************************************!*\
  !*** ./js/src/web-components/frm-range-slider-component/frm-range-slider-component.js ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmRangeSliderComponent: () => (/* binding */ frmRangeSliderComponent)
/* harmony export */ });
/* harmony import */ var _frm_web_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../frm-web-component */ "./js/src/web-components/frm-web-component.js");
/* harmony import */ var _settings_components_components_slider_component_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../settings-components/components/slider-component.js */ "./js/src/settings-components/components/slider-component.js");
/* harmony import */ var _frm_range_slider_component_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./frm-range-slider-component.css */ "./js/src/web-components/frm-range-slider-component/frm-range-slider-component.css");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _superPropGet(t, o, e, r) { var p = _get(_getPrototypeOf(1 & r ? t.prototype : t), o, e); return 2 & r && "function" == typeof p ? function (t) { return p.apply(e, t); } : p; }
function _get() { return _get = "undefined" != typeof Reflect && Reflect.get ? Reflect.get.bind() : function (e, t, r) { var p = _superPropBase(e, t); if (p) { var n = Object.getOwnPropertyDescriptor(p, t); return n.get ? n.get.call(arguments.length < 3 ? e : r) : n.value; } }, _get.apply(null, arguments); }
function _superPropBase(t, o) { for (; !{}.hasOwnProperty.call(t, o) && null !== (t = _getPrototypeOf(t));); return t; }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }
function _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object"); }
function _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }
function _classPrivateFieldSet(s, a, r) { return s.set(_assertClassBrand(s, a), r), r; }
function _assertClassBrand(e, t, n) { if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError("Private element is not present on this object"); }




var _onChange = /*#__PURE__*/new WeakMap();
var _sliderDefaultValue = /*#__PURE__*/new WeakMap();
var _sliderDefaultMultipleValues = /*#__PURE__*/new WeakMap();
var _hasMultipleValues = /*#__PURE__*/new WeakMap();
var _sliderAvailableUnits = /*#__PURE__*/new WeakMap();
var _sliderMaxValue = /*#__PURE__*/new WeakMap();
var _sliderSteps = /*#__PURE__*/new WeakMap();
var frmRangeSliderComponent = /*#__PURE__*/function (_frmWebComponent) {
  function frmRangeSliderComponent() {
    var _this;
    _classCallCheck(this, frmRangeSliderComponent);
    _this = _callSuper(this, frmRangeSliderComponent);
    _classPrivateFieldInitSpec(_this, _onChange, function () {});
    _classPrivateFieldInitSpec(_this, _sliderDefaultValue, '0px');
    _classPrivateFieldInitSpec(_this, _sliderDefaultMultipleValues, {
      vertical: {
        value: 0,
        unit: 'px'
      },
      top: {
        value: 0,
        unit: 'px'
      },
      bottom: {
        value: 0,
        unit: 'px'
      },
      horizontal: {
        value: 0,
        unit: 'px'
      },
      left: {
        value: 0,
        unit: 'px'
      },
      right: {
        value: 0,
        unit: 'px'
      }
    });
    _classPrivateFieldInitSpec(_this, _hasMultipleValues, false);
    _classPrivateFieldInitSpec(_this, _sliderAvailableUnits, ['px', 'em', '%']);
    _classPrivateFieldInitSpec(_this, _sliderMaxValue, 100);
    _classPrivateFieldInitSpec(_this, _sliderSteps, null);
    _this.componentStyle = _frm_range_slider_component_css__WEBPACK_IMPORTED_MODULE_2__["default"];
    return _this;
  }
  _inherits(frmRangeSliderComponent, _frmWebComponent);
  return _createClass(frmRangeSliderComponent, [{
    key: "initOptions",
    value: function initOptions() {
      _superPropGet(frmRangeSliderComponent, "initOptions", this, 3)([]);
      if (null === this.componentId) {
        var _frmRangeSliderCompon;
        this.componentId = "frm-range-slider-web-component-".concat(_instanceCount._ = (_frmRangeSliderCompon = _instanceCount._, ++_frmRangeSliderCompon));
      }
    }

    /**
     * A method to set the change event listener for the slider component.
     *
     * @param {Function} callback - The callback function to call when the slider component is changed.
     * @return {void}
     */
  }, {
    key: "onChange",
    set: function set(callback) {
      if ('function' !== typeof callback) {
        throw new TypeError("Expected a function, but received ".concat(_typeof(callback)));
      }
      _classPrivateFieldSet(_onChange, this, callback);
    }

    /**
     * A method to set the has multiple values flag. This flag is used to determine if the slider component should display multiple values.
     *
     * @param {boolean} value - The value to set.
     * @return {void}
     */
  }, {
    key: "hasMultipleValues",
    set: function set(value) {
      _classPrivateFieldSet(_hasMultipleValues, this, value);
    }

    /**
     * A method to set the default multiple values. This values are used to determine the default values for the slider component.
     *
     * @param {Object} value - The value to set.
     * @return {void}
     */
  }, {
    key: "sliderDefaultMultipleValues",
    set: function set(value) {
      _classPrivateFieldSet(_sliderDefaultMultipleValues, this, value);
    }

    /**
     * A method to set the default value for the single slider component. This value is used to determine the default value for the single slider component.
     *
     * @param {string|number} value - The value to set.
     * @return {void}
     */
  }, {
    key: "sliderDefaultValue",
    set: function set(value) {
      _classPrivateFieldSet(_sliderDefaultValue, this, String(value));
    }

    /**
     * A method to set the available units for the slider component. This units are used to determine the available units for the slider component.
     *
     * @param {Array} value - The value to set.
     * @return {void}
     */
  }, {
    key: "sliderAvailableUnits",
    set: function set(value) {
      _classPrivateFieldSet(_sliderAvailableUnits, this, value);
    }

    /**
     * A method to set the max value that the slider can take.
     *
     * @param {string|number} value - The value to set.
     * @return {void}
     */
  }, {
    key: "sliderMaxValue",
    set: function set(value) {
      _classPrivateFieldSet(_sliderMaxValue, this, value.toString());
    }
  }, {
    key: "steps",
    set: function set(value) {
      _classPrivateFieldSet(_sliderSteps, this, value);
    }
  }, {
    key: "useShadowDom",
    value: function useShadowDom() {
      return false;
    }
  }, {
    key: "initView",
    value: function initView() {
      this.wrapper = document.createElement('div');
      this.slidersContainer = document.createElement('div');
      this.slidersContainer.classList.add('frm-sliders-container');
      this.wrapper.classList.add('frm-style-component');
      var config = {
        maxValue: parseInt(this.getAttribute('data-max-value') || _classPrivateFieldGet(_sliderMaxValue, this), 10),
        units: this.getAvailableUnits(),
        componentClass: this.getAttribute('data-component-class') || '',
        componentId: this.componentId,
        fieldName: this.fieldName ? "name=\"".concat(this.fieldName, "\"") : '',
        fieldValue: this.defaultValue || _classPrivateFieldGet(_sliderDefaultValue, this)
      };
      if (this.hasMultipleSliderValues()) {
        this.createMultipleValuesSlider(this.slidersContainer, _objectSpread(_objectSpread({}, config), {}, {
          defaultValues: this.parseDefaultMultipleValues()
        }));
        this.wrapper.append(this.slidersContainer);
        return this.wrapper;
      }
      this.slidersContainer.append(this.createSlider({
        maxValue: config.maxValue,
        units: config.units,
        value: frmRangeSliderComponent.parseValueUnit(config.fieldValue),
        addHiddenInputValue: true
      }));
      this.wrapper.append(this.slidersContainer);
      return this.wrapper;
    }

    /**
     * A method to get the available units for the slider component. It will checke the data-units attribute first and if it is not set, it will return the default available units.
     *
     * @return {Array} - The available units.
     */
  }, {
    key: "getAvailableUnits",
    value: function getAvailableUnits() {
      var attr = this.getAttribute('data-units');
      if (!attr) {
        return _classPrivateFieldGet(_sliderAvailableUnits, this);
      }
      return attr.split(',').map(function (u) {
        return u.trim();
      });
    }

    /**
     * A method to parse the default values for the multiple values slider component. It will check the data-values attribute first and if it is not set, it will return the default values.
     * If the values haven't been set via data-values attribute or dynamically via this._sliderDefaultMultipleValues it will return the default values.
     *
     * @return {Object} - The default values.
     */
  }, {
    key: "parseDefaultMultipleValues",
    value: function parseDefaultMultipleValues() {
      var valuesAttr = this.getAttribute('data-values') || _classPrivateFieldGet(_sliderDefaultMultipleValues, this);
      var parts = valuesAttr.split(' ');
      var getPart = function getPart(id, fallbackId) {
        return parts[id] || parts[fallbackId] || '0px';
      };
      return {
        vertical: frmRangeSliderComponent.parseValueUnit(getPart(0)),
        top: frmRangeSliderComponent.parseValueUnit(getPart(0)),
        bottom: frmRangeSliderComponent.parseValueUnit(getPart(2, 0)),
        horizontal: frmRangeSliderComponent.parseValueUnit(getPart(1, 0)),
        left: frmRangeSliderComponent.parseValueUnit(getPart(3, 1)),
        right: frmRangeSliderComponent.parseValueUnit(getPart(1, 0))
      };
    }

    /**
     * A method to parse the value and unit for the slider component.
     *
     * @param {string} valueStr - The value string to parse.
     * @return {Object} - The value and unit object.
     */
  }, {
    key: "hasMultipleSliderValues",
    value:
    /**
     * A method to check if the slider component has multiple values. It will check the data-has-multiple-values attribute first and if it is not set, it will return the default value.
     *
     * @return {boolean} - The has multiple values flag.
     */
    function hasMultipleSliderValues() {
      return this.getAttribute('data-has-multiple-values') === 'true' || _classPrivateFieldGet(_hasMultipleValues, this);
    }

    /**
     * A method to create the multiple values slider.
     *
     * @param {Element} wrapper - The wrapper element.
     * @param {Object}  options - The options for the slider.
     * @return {void}
     */
  }, {
    key: "createMultipleValuesSlider",
    value: function createMultipleValuesSlider(wrapper, options) {
      var _this2 = this;
      var maxValue = options.maxValue,
        units = options.units,
        componentClass = options.componentClass,
        fieldValue = options.fieldValue,
        defaultValues = options.defaultValues;
      var groups = [{
        type: 'vertical',
        displaySliders: 'top,bottom',
        iconSvgId: 'frm-margin-top-bottom',
        ariaLabel: 'Vertical value',
        defaultValues: defaultValues.vertical
      }, {
        type: 'horizontal',
        displaySliders: 'left,right',
        iconSvgId: 'frm-margin-left-right',
        ariaLabel: 'Horizontal value',
        defaultValues: defaultValues.horizontal
      }];
      if (componentClass) {
        wrapper.className = componentClass;
      }
      groups.forEach(function (group) {
        wrapper.append(_this2.createSliderGroup({
          type: group.type,
          displaySliders: group.displaySliders,
          maxValue: maxValue,
          units: units,
          value: group.defaultValues,
          iconSvgId: group.iconSvgId,
          ariaLabel: group.ariaLabel,
          defaultValues: defaultValues,
          addHiddenInputValue: false
        }));
      });
      if (fieldValue) {
        wrapper.append(this.createSliderHiddenInputValue(fieldValue));
      }
    }

    /**
     * A method to create the hidden input value for the slider component. This hidden input value is used to store the value of the slider component.
     *
     * @param {string} fieldValue - The field value to set.
     * @return {Element} - The hidden input value element.
     */
  }, {
    key: "createSliderHiddenInputValue",
    value: function createSliderHiddenInputValue(fieldValue) {
      var _this3 = this;
      if (!fieldValue) {
        return null;
      }
      var input = document.createElement('input');
      Object.assign(input, {
        type: 'hidden',
        value: fieldValue
      });
      if (this.fieldName) {
        input.setAttribute('name', this.fieldName);
      }
      if (this.componentId) {
        input.id = this.componentId;
      }
      if (_classPrivateFieldGet(_onChange, this)) {
        input.addEventListener('change', function () {
          _classPrivateFieldGet(_onChange, _this3).call(_this3, input.value);
        });
      }
      return input;
    }

    /**
     * A method to create the slider group. This method is used to create the slider group.
     *
     * @param {Object} options - The options for the slider.
     * @return {Element} - The slider group element.
     */
  }, {
    key: "createSliderGroup",
    value: function createSliderGroup(options) {
      var _this4 = this;
      var slider = this.createSlider(options);
      slider.classList.add('frm-group-sliders', 'frm-has-multiple-values');
      slider.setAttribute('data-display-sliders', options.displaySliders);
      var slidersGroupItems = options.displaySliders.split(',');
      slidersGroupItems.forEach(function (item) {
        slider.append(_this4.createSlider({
          type: item,
          maxValue: options.maxValue,
          units: options.units,
          value: options.defaultValues[item],
          iconSvgId: "frm-margin-".concat(item),
          ariaLabel: "".concat(item, " value"),
          hidden: true,
          addHiddenInputValue: false
        }));
      });
      return slider;
    }

    /**
     * A method to create the slider track. This method is used to create the slider track.
     *
     * @param {Object} value - The value of the slider.
     * @return {Element} - The slider track element.
     */
  }, {
    key: "createSlider",
    value:
    /**
     * A method to create the slider. This method is used to create the slider.
     *
     * @param {Object} options - The options for the slider.
     * @return {Element} - The slider element.
     */
    function createSlider(options) {
      var type = options.type,
        maxValue = options.maxValue,
        units = options.units,
        value = options.value,
        iconSvgId = options.iconSvgId,
        ariaLabel = options.ariaLabel,
        hidden = options.hidden,
        addHiddenInputValue = options.addHiddenInputValue;
      var sliderWrapper = document.createElement('div');
      sliderWrapper.classList.add('frm-slider-component');
      sliderWrapper.setAttribute('data-max-value', maxValue.toString());
      if (hidden) {
        sliderWrapper.classList.add('frm_hidden');
      }
      if (type) {
        sliderWrapper.setAttribute('data-type', type);
      }
      var flexContainer = document.createElement('div');
      flexContainer.classList.add('frm-flex-justify');

      // Slider container
      var sliderContainer = document.createElement('div');
      sliderContainer.classList.add('frm-slider-container');

      // Icon
      if (iconSvgId) {
        sliderContainer.append(frmRangeSliderComponent.createSvgIcon(iconSvgId));
      }

      // Slider track
      sliderContainer.append(frmRangeSliderComponent.createSliderTrack(value));
      flexContainer.append(sliderContainer);

      // Value input and unit select
      var baseId = "".concat(this.componentId).concat(type ? "-".concat(type) : '');
      var valueContainer = frmRangeSliderComponent.createSliderValueAndUnitSelection(value, ariaLabel, units, baseId);
      if (addHiddenInputValue) {
        valueContainer.append(this.createSliderHiddenInputValue(options));
      }
      flexContainer.append(valueContainer);
      sliderWrapper.append(flexContainer);
      return sliderWrapper;
    }
  }, {
    key: "afterViewInit",
    value: function afterViewInit() {
      var defaultValues = this.hasMultipleSliderValues() ? this.parseDefaultMultipleValues() : frmRangeSliderComponent.parseValueUnit(this.defaultValue);
      var options = Object.assign({
        defaultValues: defaultValues
      }, {
        steps: _classPrivateFieldGet(_sliderSteps, this)
      });
      new _settings_components_components_slider_component_js__WEBPACK_IMPORTED_MODULE_1__["default"](this.wrapper.querySelectorAll('.frm-slider-component'), options);
    }
  }], [{
    key: "parseValueUnit",
    value: function parseValueUnit(valueStr) {
      var defaultValue = {
        value: 0,
        unit: 'px'
      };
      if (!valueStr) {
        return defaultValue;
      }
      var match = valueStr.match(/^(\d+)(px|em|%|\s)?$/);
      if (!match) {
        return defaultValue;
      }
      return {
        value: parseInt(match[1], 10),
        unit: match[2] || 'px'
      };
    }
  }, {
    key: "createSliderTrack",
    value: function createSliderTrack(value) {
      var slider = document.createElement('span');
      slider.classList.add('frm-slider');
      slider.setAttribute('tabindex', '0');
      var activeTrack = document.createElement('span');
      activeTrack.classList.add('frm-slider-active-track');
      var bullet = document.createElement('span');
      bullet.classList.add('frm-slider-bullet');
      var valueLabel = document.createElement('span');
      valueLabel.classList.add('frm-slider-value-label');
      valueLabel.textContent = value.value.toString();
      bullet.append(valueLabel);
      activeTrack.append(bullet);
      slider.append(activeTrack);
      return slider;
    }

    /**
     * A method to create the value and unit selection. This method is used to create the value and unit selection.
     *
     * @param {Object} value     - The value of the slider.
     * @param {string} ariaLabel - The aria label of the slider.
     * @param {Array}  units     - The units of the slider.
     * @param {string} baseId    - The base ID for the form elements.
     * @return {Element} - The value and unit selection element.
     */
  }, {
    key: "createSliderValueAndUnitSelection",
    value: function createSliderValueAndUnitSelection(value, ariaLabel, units, baseId) {
      var valueContainer = document.createElement('div');
      valueContainer.classList.add('frm-slider-value');
      var valueInput = document.createElement('input');
      valueInput.type = 'text';
      valueInput.value = value.value.toString();
      if (ariaLabel) {
        valueInput.setAttribute('aria-label', ariaLabel);
      }
      var unitSelect = document.createElement('select');
      unitSelect.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Value unit', 'formidable'));
      if (baseId) {
        valueInput.id = "".concat(baseId, "-value");
        unitSelect.id = "".concat(baseId, "-unit");
      }
      units.forEach(function (unit) {
        return unitSelect.append(frmRangeSliderComponent.createDropdownOption(unit, unit, value.unit === unit));
      });
      valueContainer.append(valueInput, unitSelect);
      return valueContainer;
    }

    /**
     * A method to create the dropdown option. This method is used to create the dropdown option.
     *
     * @param {string}  value    - The value of the option.
     * @param {string}  label    - The label of the option.
     * @param {boolean} selected - Whether the option is selected.
     * @return {Element} - The dropdown option element.
     */
  }, {
    key: "createDropdownOption",
    value: function createDropdownOption(value, label) {
      var selected = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      var option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      option.selected = selected;
      return option;
    }

    /**
     * A method to create the SVG icon. This method is used to create the SVG icon.
     *
     * @param {string} iconSvgId - The ID of the SVG icon.
     * @return {Element} - The SVG icon element.
     */
  }, {
    key: "createSvgIcon",
    value: function createSvgIcon(iconSvgId) {
      var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.classList.add('frmsvg');
      var use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
      use.setAttribute('href', "#".concat(iconSvgId));
      svg.append(use);
      return svg;
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_0__.frmWebComponent);
var _instanceCount = {
  _: 0
};

/***/ }),

/***/ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.js":
/*!******************************************************************************************!*\
  !*** ./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.js ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmTabNavigatorComponent: () => (/* binding */ frmTabNavigatorComponent)
/* harmony export */ });
/* harmony import */ var _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../components/class-tabs-navigator */ "./js/src/components/class-tabs-navigator.js");
/* harmony import */ var _frm_web_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../frm-web-component */ "./js/src/web-components/frm-web-component.js");
/* harmony import */ var _frm_tab_navigator_component_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./frm-tab-navigator-component.css */ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.css");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }



var frmTabNavigatorComponent = /*#__PURE__*/function (_frmWebComponent) {
  function frmTabNavigatorComponent() {
    var _this;
    _classCallCheck(this, frmTabNavigatorComponent);
    _this = _callSuper(this, frmTabNavigatorComponent);
    _this.componentStyle = _frm_tab_navigator_component_css__WEBPACK_IMPORTED_MODULE_2__["default"];
    return _this;
  }

  /**
   * Initializes the view.
   *
   * @return {Element} - The wrapper element.
   */
  _inherits(frmTabNavigatorComponent, _frmWebComponent);
  return _createClass(frmTabNavigatorComponent, [{
    key: "initView",
    value: function initView() {
      this.tabs = this.querySelectorAll('.frm-tab');
      if (0 === this.tabs.length) {
        return null;
      }
      var wrapper = document.createElement('div');
      wrapper.classList.add('frm-tabs-wrapper');
      wrapper.append(this.getTabDelimiter());
      wrapper.append(this.getTabs());
      wrapper.append(this.getTabContainer());
      return wrapper;
    }
  }, {
    key: "afterViewInit",
    value: function afterViewInit(wrapper) {
      this.tabsNavigator = new _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__.frmTabsNavigator(wrapper);
    }

    /**
     * Gets the tab delimiter.
     *
     * @return {string} - The tab delimiter.
     */
  }, {
    key: "getTabDelimiter",
    value: function getTabDelimiter() {
      var delimiter = document.createElement('div');
      var underline = document.createElement('span');
      underline.classList.add('frm-tabs-active-underline');
      delimiter.className = 'frm-tabs-delimiter';
      delimiter.append(underline);
      return delimiter;
    }

    /**
     * Gets the tab headings.
     *
     * @return {string} - The tab headings.
     */
  }, {
    key: "getTabs",
    value: function getTabs() {
      var _this2 = this;
      var tabHeadings = document.createElement('div');
      var ul = document.createElement('ul');
      tabHeadings.className = 'frm-tabs-navs';
      tabHeadings.append(ul);
      Array.from(this.tabs).forEach(function (tab, index) {
        ul.append(_this2.createTabHeading(tab, index));
      });
      return tabHeadings;
    }

    /**
     * Gets the tab container.
     *
     * @return {string} - The tab container.
     */
  }, {
    key: "getTabContainer",
    value: function getTabContainer() {
      var _this3 = this;
      var tabContainer = document.createElement('div');
      var slideTrack = document.createElement('div');
      tabContainer.className = 'frm-tabs-container';
      slideTrack.className = 'frm-tabs-slide-track frm-flex-box';
      tabContainer.append(slideTrack);
      Array.from(this.tabs).forEach(function (tab, index) {
        slideTrack.append(_this3.createTabContainer(tab, index));
      });
      return tabContainer;
    }

    /**
     * Creates a tab heading.
     *
     * @param {Element} tab   - The tab element.
     * @param {number}  index - The index of the tab.
     * @return {string} - The tab heading.
     */
  }, {
    key: "createTabHeading",
    value: function createTabHeading(tab, index) {
      var className = index === 0 ? 'frm-active' : '';
      var li = document.createElement('li');
      li.className = className;
      li.innerText = tab.getAttribute('data-tab-title');
      return li;
    }

    /**
     * Creates a tab container.
     *
     * @param {Element} tab   - The tab element.
     * @param {number}  index - The index of the tab.
     * @return {string} - The tab container.
     */
  }, {
    key: "createTabContainer",
    value: function createTabContainer(tab, index) {
      var className = index === 0 ? 'frm-active' : '';
      var container = document.createElement('div');
      container.className = "frm-tab-container ".concat(className);
      Array.from(tab.children).forEach(function (child) {
        container.append(child);
      });
      return container;
    }

    /**
     * Gets the tab underline.
     *
     * @return {Element} - The tab underline.
     */
  }, {
    key: "getTabUnderline",
    value: function getTabUnderline() {
      return this.shadowRoot.querySelector('.frm-tabs-active-underline');
    }
  }, {
    key: "getLabelText",
    value: function getLabelText() {
      return null;
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_1__.frmWebComponent);

/***/ }),

/***/ "./js/src/web-components/frm-typography-component/frm-typography-component.js":
/*!************************************************************************************!*\
  !*** ./js/src/web-components/frm-typography-component/frm-typography-component.js ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmTypographyComponent: () => (/* binding */ frmTypographyComponent)
/* harmony export */ });
/* harmony import */ var _frm_web_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../frm-web-component */ "./js/src/web-components/frm-web-component.js");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "./node_modules/@wordpress/i18n/build-module/index.js");
/* harmony import */ var _frm_typography_component_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./frm-typography-component.css */ "./js/src/web-components/frm-typography-component/frm-typography-component.css");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _superPropGet(t, o, e, r) { var p = _get(_getPrototypeOf(1 & r ? t.prototype : t), o, e); return 2 & r && "function" == typeof p ? function (t) { return p.apply(e, t); } : p; }
function _get() { return _get = "undefined" != typeof Reflect && Reflect.get ? Reflect.get.bind() : function (e, t, r) { var p = _superPropBase(e, t); if (p) { var n = Object.getOwnPropertyDescriptor(p, t); return n.get ? n.get.call(arguments.length < 3 ? e : r) : n.value; } }, _get.apply(null, arguments); }
function _superPropBase(t, o) { for (; !{}.hasOwnProperty.call(t, o) && null !== (t = _getPrototypeOf(t));); return t; }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }
function _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object"); }
function _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }
function _classPrivateFieldSet(s, a, r) { return s.set(_assertClassBrand(s, a), r), r; }
function _assertClassBrand(e, t, n) { if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError("Private element is not present on this object"); }



var _onChange = /*#__PURE__*/new WeakMap();
var _defaultOptions = /*#__PURE__*/new WeakMap();
var _unitTypeOptions = /*#__PURE__*/new WeakMap();
var _defaultValue = /*#__PURE__*/new WeakMap();
var frmTypographyComponent = /*#__PURE__*/function (_frmWebComponent) {
  function frmTypographyComponent() {
    var _this;
    _classCallCheck(this, frmTypographyComponent);
    _this = _callSuper(this, frmTypographyComponent);
    _classPrivateFieldInitSpec(_this, _onChange, function () {});
    _classPrivateFieldInitSpec(_this, _defaultOptions, [{
      value: '21px',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Default', 'formidable')
    }]);
    _classPrivateFieldInitSpec(_this, _unitTypeOptions, ['px', 'em', '%']);
    _classPrivateFieldInitSpec(_this, _defaultValue, '21px');
    _this.componentStyle = _frm_typography_component_css__WEBPACK_IMPORTED_MODULE_2__["default"];
    _classPrivateFieldSet(_defaultOptions, _this, [{
      value: '',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Default', 'formidable')
    }, {
      value: '18px',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Small', 'formidable')
    }, {
      value: '21px',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Regular', 'formidable')
    }, {
      value: '26px',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Large', 'formidable')
    }, {
      value: '32px',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Larger', 'formidable')
    }, {
      value: 'custom',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Custom', 'formidable')
    }]);
    return _this;
  }
  _inherits(frmTypographyComponent, _frmWebComponent);
  return _createClass(frmTypographyComponent, [{
    key: "initOptions",
    value: function initOptions() {
      _superPropGet(frmTypographyComponent, "initOptions", this, 3)([]);
      if (null === this.componentId) {
        var _frmTypographyCompone;
        this.componentId = "frm-typography-web-component-".concat(_instanceCount._ = (_frmTypographyCompone = _instanceCount._, ++_frmTypographyCompone));
      }
    }
  }, {
    key: "initView",
    value: function initView() {
      this.wrapper = document.createElement('div');
      this.container = document.createElement('div');
      this.wrapper.classList.add('frm-typography-component', 'frm-typography');
      this.container.classList.add('frm-typography-container');
      this.container.append(this.getSelect(), this.getUnitValueWrapper(), this.getHiddenInput());
      this.wrapper.append(this.container);
      return this.wrapper;
    }

    /**
     * A method to get the select element.
     *
     * @return {Element} - The select element.
     */
  }, {
    key: "getSelect",
    value: function getSelect() {
      this.select = document.createElement('select');
      this.select.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Font size', 'formidable'));
      if (null !== this.componentId) {
        this.select.id = this.componentId;
      }
      if (null !== this.fieldName) {
        this.select.name = "".concat(this.fieldName, "[size]");
      }
      this.getDefaultOptions(this.select);
      return this.select;
    }

    /**
     * A method to get the default options for the select element.
     *
     * @param {Element} select - The select element.
     * @return {void}
     */
  }, {
    key: "getDefaultOptions",
    value: function getDefaultOptions(select) {
      var _this2 = this;
      _classPrivateFieldGet(_defaultOptions, this).forEach(function (option) {
        var opt = document.createElement('option');
        opt.value = option.value;
        opt.textContent = option.label;
        opt.selected = option.value === 'custom' && frmTypographyComponent.isCustomFonSize(_classPrivateFieldGet(_defaultValue, _this2)) || option.value === _classPrivateFieldGet(_defaultValue, _this2);
        select.append(opt);
      });
    }

    /**
     * A method to check if the value is a custom font size.
     *
     * @param {string} value - The value to check if it is a custom font size.
     * @return {boolean} - True if the value is a custom font size, false otherwise.
     */
  }, {
    key: "changeSelectValue",
    value:
    /**
     * A method to change the select value.
     *
     * @param {string} value - The value to change the select value for.
     * @return {void}
     */
    function changeSelectValue(value) {
      var isCustomFonSize = frmTypographyComponent.isCustomFonSize(value);
      Array.from(this.select.options).forEach(function (option) {
        option.selected = option.value === 'custom' && isCustomFonSize || option.value === value;
      });
    }

    /**
     * A method to get the unit value wrapper element.
     *
     * @return {Element} - The unit value wrapper element.
     */
  }, {
    key: "getUnitValueWrapper",
    value: function getUnitValueWrapper() {
      this.unitValueWrapper = document.createElement('div');
      this.unitValueWrapper.classList.add('frm-unit-value');
      this.unitValueWrapper.append(this.getUnitValueInput());
      this.unitValueWrapper.append(this.getUnitTypeSelect());
      return this.unitValueWrapper;
    }

    /**
     * A method to get the unit value input element.
     *
     * @return {Element} - The unit value input element.
     */
  }, {
    key: "getUnitValueInput",
    value: function getUnitValueInput() {
      var _this3 = this;
      this.unitValueInput = document.createElement('input');
      this.unitValueInput.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Font size value', 'formidable'));
      if (null !== this.componentId) {
        this.unitValueInput.id = "".concat(this.componentId, "-unit");
      }
      if (null !== this.fieldName) {
        this.unitValueInput.name = "".concat(this.fieldName, "[unit]");
      }
      this.unitValueInput.type = 'text';
      this.unitValueInput.value = '' !== _classPrivateFieldGet(_defaultValue, this) ? "".concat(parseInt(_classPrivateFieldGet(_defaultValue, this), 10)) : '';
      this.unitValueInput.addEventListener('change', function (event) {
        var value = '' !== event.target.value ? event.target.value + _this3.unitTypeSelect.value : '';
        _this3.changeSelectValue(value);
        _classPrivateFieldGet(_onChange, _this3).call(_this3, value);
      });
      return this.unitValueInput;
    }

    /**
     * A method to get the unit type select element.
     *
     * @return {Element} - The unit type select element.
     */
  }, {
    key: "getUnitTypeSelect",
    value: function getUnitTypeSelect() {
      var _this4 = this;
      this.unitTypeSelect = document.createElement('select');
      this.unitTypeSelect.setAttribute('aria-label', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Font size unit', 'formidable'));
      if (null !== this.componentId) {
        this.unitTypeSelect.id = "".concat(this.componentId, "-unit-type");
      }
      if (null !== this.fieldName) {
        this.unitTypeSelect.name = "".concat(this.fieldName, "[unit-type]");
      }
      _classPrivateFieldGet(_unitTypeOptions, this).forEach(function (option) {
        var opt = document.createElement('option');
        opt.value = option;
        opt.textContent = option;
        _this4.unitTypeSelect.append(opt);
      });
      return this.unitTypeSelect;
    }

    /**
     * A method to get the hidden input element.
     *
     * @return {Element} - The hidden input element.
     */
  }, {
    key: "getHiddenInput",
    value: function getHiddenInput() {
      this.hiddenInput = document.createElement('input');
      this.hiddenInput.type = 'hidden';
      if (null !== this.fieldName) {
        this.hiddenInput.name = "".concat(this.fieldName, "[value]");
      }
      this.hiddenInput.value = _classPrivateFieldGet(_defaultValue, this);
      return this.hiddenInput;
    }

    /**
     * A method to set the change event listener for the select element.
     *
     * @return {void} - The unit value.
     */
  }, {
    key: "afterViewInit",
    value: function afterViewInit() {
      var _this5 = this;
      this.select.addEventListener('change', function () {
        var value = frmTypographyComponent.getUnitValue(_this5.select.value);
        _this5.unitValueInput.value = value.value;
        _this5.hiddenInput.value = value.value + value.unit;
        _classPrivateFieldGet(_onChange, _this5).call(_this5, _this5.hiddenInput.value);
        _this5.unitTypeSelect.value = value.unit;
      });
    }

    /**
     * A method to get the unit value.
     *
     * @param {string} value - The value to get the unit value for.
     * @return {Object} - The unit value.
     */
  }, {
    key: "onChange",
    set:
    /**
     * A method to set the change event listener for the select element.
     *
     * @param {Function} callback - The callback function to call when the select element is changed.
     * @return {void}
     */
    function set(callback) {
      if ('function' !== typeof callback) {
        throw new TypeError("Expected a function, but received ".concat(_typeof(callback)));
      }
      _classPrivateFieldSet(_onChange, this, callback);
    }

    /**
     * A method to set dynamically the default value for the typography component.
     *
     * @param {string} value - The value to set dynamically the default value for.
     * @return {void}
     */
  }, {
    key: "typographyDefaultValue",
    set: function set(value) {
      _classPrivateFieldSet(_defaultValue, this, value);
    }
  }], [{
    key: "isCustomFonSize",
    value: function isCustomFonSize(value) {
      return !['', '18px', '21px', '26px', '32px'].includes(value);
    }
  }, {
    key: "getUnitValue",
    value: function getUnitValue(value) {
      var defaultValue = {
        value: '',
        unit: ''
      };
      if (!value) {
        return defaultValue;
      }
      var match = value.match(/^([\d.]+)(px|em|%)?$/);
      if (!match) {
        return defaultValue;
      }
      return {
        value: parseInt(value, 10),
        unit: match[2] || 'px'
      };
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_0__.frmWebComponent);
var _instanceCount = {
  _: 0
};

/***/ }),

/***/ "./js/src/web-components/frm-web-component.js":
/*!****************************************************!*\
  !*** ./js/src/web-components/frm-web-component.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmWebComponent: () => (/* binding */ frmWebComponent)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _wrapNativeSuper(t) { var r = "function" == typeof Map ? new Map() : void 0; return _wrapNativeSuper = function _wrapNativeSuper(t) { if (null === t || !_isNativeFunction(t)) return t; if ("function" != typeof t) throw new TypeError("Super expression must either be null or a function"); if (void 0 !== r) { if (r.has(t)) return r.get(t); r.set(t, Wrapper); } function Wrapper() { return _construct(t, arguments, _getPrototypeOf(this).constructor); } return Wrapper.prototype = Object.create(t.prototype, { constructor: { value: Wrapper, enumerable: !1, writable: !0, configurable: !0 } }), _setPrototypeOf(Wrapper, t); }, _wrapNativeSuper(t); }
function _construct(t, e, r) { if (_isNativeReflectConstruct()) return Reflect.construct.apply(null, arguments); var o = [null]; o.push.apply(o, e); var p = new (t.bind.apply(t, o))(); return r && _setPrototypeOf(p, r.prototype), p; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _isNativeFunction(t) { try { return -1 !== Function.toString.call(t).indexOf("[native code]"); } catch (n) { return "function" == typeof t; } }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
var frmWebComponent = /*#__PURE__*/function (_HTMLElement) {
  function frmWebComponent() {
    var _this;
    _classCallCheck(this, frmWebComponent);
    if ((this instanceof frmWebComponent ? this.constructor : void 0) === frmWebComponent) {
      throw new Error('frmWebComponent is an abstract class and cannot be instantiated directly');
    }
    _this = _callSuper(this, frmWebComponent);
    if (_this.useShadowDom()) {
      _this.attachShadow({
        mode: 'open'
      });
    }
    return _this;
  }
  _inherits(frmWebComponent, _HTMLElement);
  return _createClass(frmWebComponent, [{
    key: "initOptions",
    value: function initOptions() {
      this.fieldName = this.getAttribute('name') || null;
      this.defaultValue = this.getAttribute('value') || null;
      this.componentId = this.getAttribute('id') || null;
    }
  }, {
    key: "getLabelText",
    value: function getLabelText() {
      if (this._labelText) {
        return this._labelText;
      }
      var label = this.querySelector('span.frm-component-label');
      if (null === label) {
        return null;
      }
      return label.innerText;
    }
  }, {
    key: "useShadowDom",
    value: function useShadowDom() {
      return 'false' !== this.getAttribute('data-shadow-dom');
    }

    /*
    * Load the component style.
    * @return string
    */
  }, {
    key: "loadStyle",
    value: function loadStyle() {
      var _frmGlobal;
      var style = document.createElement('style');
      this.componentStyle = this.componentStyle.replace('--frm-plugin-url', ((_frmGlobal = frmGlobal) === null || _frmGlobal === void 0 ? void 0 : _frmGlobal.url) || '');
      style.textContent += this.componentStyle;
      return style;
    }
  }, {
    key: "getWrapper",
    value: function getWrapper() {
      return this.useShadowDom() ? this.shadowRoot : this;
    }

    /*
    * Render the component inside the shadow root.
    * @return void
    */
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;
      var view = this.initView();
      if (!view) {
        return;
      }
      var wrapper = this.getWrapper();
      wrapper.innerHTML = '';
      view.classList.add('frm-component');
      wrapper.append.apply(wrapper, _toConsumableArray(this.getViewItems(view)));
      this.addLabelToView(view);
      this.whenElementBecomesVisible().then(function () {
        return _this2.afterViewInit(_this2);
      });
    }
  }, {
    key: "addLabelToView",
    value: function addLabelToView(view) {
      var labelText = this.getLabelText();
      if (null === labelText || null === view) {
        return;
      }
      var label = document.createElement('span');
      label.classList.add('frm-component-label');
      label.textContent = labelText;
      view.prepend(label);
    }
  }, {
    key: "getViewItems",
    value: function getViewItems(view) {
      return [this.loadStyle(), view].filter(function (item) {
        return item !== null;
      });
    }

    /**
     * Waits for the element to become visible in the viewport.
     *
     * @return {Promise} - A promise that resolves when the element is visible.
     */
  }, {
    key: "whenElementBecomesVisible",
    value: function whenElementBecomesVisible() {
      var _this3 = this;
      return new Promise(function (resolve) {
        if (window.IntersectionObserver === undefined) {
          requestAnimationFrame(function () {
            return resolve();
          });
          return;
        }
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            // The element is in viewport and its visibility is greater than 0.
            if (entry.isIntersecting && entry.intersectionRatio > 0) {
              observer.disconnect();
              requestAnimationFrame(function () {
                return resolve();
              });
            }
          });
        }, {
          threshold: 0.1
        });
        var element = _this3.useShadowDom() ? _this3.shadowRoot.host : _this3;
        if (element) {
          observer.observe(_this3);
        }
      });
    }
  }, {
    key: "frmLabel",
    set: function set(text) {
      this._labelText = text;
    }

    /**
     * After the view is initialized and the element/wrapper is visible in the viewport.
     *
     * @param {Element} wrapper - The wrapper element.
     */
  }, {
    key: "afterViewInit",
    value: function afterViewInit(wrapper) {
      // Override in child class.
    }

    /**
     * Constructs the view in the DOM.
     * return {Element} - The wrapper element.
     */
  }, {
    key: "initView",
    value: function initView() {
      // Override in child class.
    }

    /*
    * Called by browser when the component is rendered to the DOM.
    * @return void
    */
  }, {
    key: "connectedCallback",
    value: function connectedCallback() {
      this.initOptions();
      this.render();
    }

    /*
    * Called by browser when the component is removed from the DOM.
    * @return void
    */
  }, {
    key: "disconnectedCallback",
    value: function disconnectedCallback() {}
  }]);
}(/*#__PURE__*/_wrapNativeSuper(HTMLElement));

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

/***/ "./js/src/web-components/frm-border-radius-component/frm-border-radius-component.css":
/*!*******************************************************************************************!*\
  !*** ./js/src/web-components/frm-border-radius-component/frm-border-radius-component.css ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("/*!********************************************************************************************************************************************************************************************************************************************************************************!*\\\n  !*** css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[0].use[1]!./node_modules/css-unicode-loader/index.js!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[0].use[3]!./js/src/web-components/frm-border-radius-component/frm-border-radius-component.scss ***!\n  \\********************************************************************************************************************************************************************************************************************************************************************************/\n/**\n * Base - Variables\n */\n:root,\n.frm-white-body,\n.frm_wrap {\n  --grey-900: #101828;\n  --grey-800: #1d2939;\n  --grey-700: #344054;\n  --grey-600: #475467;\n  --grey-500: #667085; /* Roughly 65% opacity */\n  --grey-400: #98a2b3;\n  --grey-300: #d0d5dd;\n  --grey-200: #eaecf0;\n  --grey-100: #f2f4f7;\n  --grey-50: #f9fafb;\n  --grey-25: #fcfcfd;\n  --dark-grey: var(--grey-700); /* Deprecated */\n  --medium-grey: rgba(40, 47, 54, 0.65);\n  --grey: var(--grey-500); /* Deprecated */\n  --grey-border: var(--grey-300); /* Deprecated */\n  --lightest-grey: rgb(250, 250, 250);\n  --sidebar-color: var(--grey-50);\n  --sidebar-hover: var(--grey-200);\n  --primary-700: #2b66a9;\n  --primary-500: #4199fd;\n  --primary-300: #80bbfe;\n  --primary-200: #c0ddfe;\n  --primary-50: #ecf5ff;\n  --primary-25: #f5faff;\n  --primary-color: var(--primary-500); /* Deprecated */\n  --primary-hover: var(--primary-700); /* Deprecated */\n  --light-blue: var(--primary-25); /* Deprecated */\n  --blue-border: rgb(188, 224, 253);\n  --error-700: #b42318;\n  --error-500: #f04438;\n  --error-300: #fecdca;\n  --error-100: #fee4e2;\n  --error-25: #fff5f4;\n  --green: rgb(63, 172, 37);\n  --orange: #f15a24;\n  --warning-500: #f79009;\n  --pink: rgb(226, 42, 110);\n  --purple: rgb(141, 53, 245);\n  --success-900: #054f31;\n  --success-800: #065f46;\n  --success-500: #12b76a;\n  --success-100: #d1fae5;\n  --success-200: #a6f4c5;\n  --success-50: #ecfdf3;\n  --success-25: #f6fef9;\n  --border-radius: 35px;\n  --small-radius: 8px;\n  --medium-radius: 16px;\n  --small-sidebar: 275px;\n  --medium-sidebar: 350px;\n  --big-sidebar: 390px;\n  --biggest-sidebar: 450px;\n  --text-xs: 12px;\n  --text-sm: 14px;\n  --text-md: 16px;\n  --text-lg: 18px;\n  --text-xl: 20px;\n  --h-xs: 24px;\n  --h-sm: 30px;\n  --h-md: 36px;\n  --leading: 1.5;\n  --gap-2xs: 4px;\n  --gap-xs: 8px;\n  --gap-sm: 16px;\n  --gap-md: 24px;\n  --gap-lg: 32px;\n  --gap-xl: 40px;\n  --gap-2xl: 48px;\n  --box-shadow-xs: 0 0.47074466943740845px 0.9414893388748169px 0 rgba(16, 24, 40, 0.05);\n  --box-shadow-sm: 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-md: 0 1.88298px 3.76596px -0.941489px rgba(16, 24, 40, 0.1), 0 0.941489px 1.88298px -0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-lg: 0 6px 8px -2px rgba(16, 24, 40, 0.08), 0 1.88298px 4px -1px rgba(16, 24, 40, 0.03), 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-xl: 0 9.41489px 11.2979px -1.88298px rgba(16, 24, 40, 0.08), 0 3.76596px 3.76596px -1.88298px rgba(16, 24, 40, 0.03);\n  --box-shadow-xxl: 0 11px 22px -5px rgba(16, 24, 40, 0.18);\n  --button-shadow: 0 0.47px 0.94px 0 rgba(16, 24, 40, 0.06), 0 0.47px 1.47px 0 rgba(16, 24, 40, 0.1);\n  /* Override front-end CSS */\n  --check-label-color: var(--grey-700);\n}\n\n.frm_hidden {\n  display: none;\n}\n\n.frm-component:not(.frm-tabs-wrapper) {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.frm-component:not(.frm-tabs-wrapper) > span.frm-component-label {\n  font-weight: 500;\n  font-size: var(--text-sm) !important;\n  color: var(--grey-900) !important;\n  width: 40% !important;\n  display: block !important;\n  margin-right: 12px !important;\n}\n\n.frm-border-radius-component .frm-border-radius-container {\n  width: 100%;\n  display: flex;\n  justify-content: space-between;\n  flex-wrap: wrap;\n}\n.frm-border-radius-component .frm-border-radius-container button {\n  overflow: hidden;\n  text-indent: -9999px;\n  cursor: pointer;\n  width: 36px;\n  height: 36px;\n  border-radius: var(--small-radius);\n  border: 1px solid var(--grey-300);\n  background: white;\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  box-sizing: border-box;\n  background: url('data:image/svg+xml,<svg width=\"20\" height=\"20\" viewBox=\"0 0 20 20\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M3.95837 12.2915V14.3748C3.95837 15.2953 4.70457 16.0415 5.62504 16.0415H7.70837\" stroke=\"%23667085\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/><path d=\"M16.0416 12.2915V14.3748C16.0416 15.2953 15.2955 16.0415 14.375 16.0415H12.2916\" stroke=\"%23667085\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/><path d=\"M16.0416 7.7085V5.62516C16.0416 4.70469 15.2955 3.9585 14.375 3.9585H12.2916\" stroke=\"%23667085\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/><path d=\"M3.95837 7.7085V5.62516C3.95837 4.70469 4.70457 3.9585 5.62504 3.9585H7.70837\" stroke=\"%23667085\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>') no-repeat center center;\n  background-size: 20px;\n}\n.frm-border-radius-component .frm-border-radius-container button.frm-active, .frm-border-radius-component .frm-border-radius-container button:hover {\n  background-color: rgba(0, 0, 0, 0.05);\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper {\n  width: 100%;\n  justify-content: space-between;\n  flex-wrap: wrap;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper:not(.frm_hidden) {\n  display: flex;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span {\n  position: relative;\n  display: block;\n  overflow: hidden;\n  width: calc(50% - 6px);\n  height: 36px;\n  border-radius: var(--small-radius);\n  border: 1px solid var(--grey-300);\n  margin-top: 12px;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span input {\n  width: 100%;\n  height: 100%;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(16, 24, 40);\n  padding: 0 12px 0px 20px;\n  box-sizing: border-box;\n  border: none;\n  text-align: right;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span input:focus {\n  outline: none;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span:before {\n  content: \"\";\n  position: absolute;\n  display: block;\n  width: 12px;\n  height: 12px;\n  left: 12px;\n  top: 0;\n  bottom: 0;\n  right: auto;\n  margin: auto;\n  background: url('data:image/svg+xml,<svg width=\"12\" height=\"12\" viewBox=\"0 0 12 12\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10.75 3.25V3.25833M0.75 5.75V5.75833M5.75 5.75V5.75833M0.75 8.25V8.25833M0.75 3.25833V3.25M3.25 0.758334V0.75M0.75 0.758334V0.75M3.25 10.75V10.7583M8.25 0.75V0.758334M0.75 10.75V10.7583M10.75 0.75V0.758334M5.75 0.758334V0.75M6.375 10.9583H7.625C8.50905 10.9583 9.3569 10.6071 9.98202 9.98202C10.6071 9.3569 10.9583 8.50905 10.9583 7.625V6.375\" stroke=\"%23667085\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>') center center no-repeat;\n  background-size: 12px;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span.frm-border-input-top:before {\n  transform: rotate(180deg);\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span.frm-border-input-bottom:before {\n  transform: rotate(0deg);\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span.frm-border-input-left:before {\n  transform: rotate(90deg);\n}\n.frm-border-radius-component .frm-border-radius-container .frm-border-individual-inputs-wrapper span.frm-border-input-right:before {\n  transform: rotate(-90deg);\n}\n.frm-border-radius-component .frm-border-radius-container .frm-input-wrapper {\n  width: calc(100% - 36px - 12px);\n  height: 36px;\n  display: flex;\n  justify-content: center;\n  box-sizing: border-box;\n  background: white;\n  border-radius: var(--small-radius);\n  border: 1px solid var(--grey-300);\n  overflow: hidden;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-input-wrapper > * {\n  border: none;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-input-wrapper input {\n  width: calc(100% - 44px);\n  height: 100%;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(16, 24, 40);\n  padding-left: 12px;\n  box-sizing: border-box;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-input-wrapper input:focus {\n  outline: none;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-input-wrapper select {\n  text-align: right;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(102, 112, 133);\n  width: 44px;\n  background: url(\"../../images/style/small-arrow.svg\") no-repeat;\n  background-position: center right 12px;\n}\n.frm-border-radius-component .frm-border-radius-container .frm-input-wrapper select:focus {\n  outline: none;\n}\n\n/*# sourceMappingURL=frm-border-radius-component.css.map*/");

/***/ }),

/***/ "./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.css":
/*!***************************************************************************************!*\
  !*** ./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.css ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("/*!****************************************************************************************************************************************************************************************************************************************************************************!*\\\n  !*** css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[0].use[1]!./node_modules/css-unicode-loader/index.js!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[0].use[3]!./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.scss ***!\n  \\****************************************************************************************************************************************************************************************************************************************************************************/\n/**\n * Base - Variables\n */\n:root,\n.frm-white-body,\n.frm_wrap {\n  --grey-900: #101828;\n  --grey-800: #1d2939;\n  --grey-700: #344054;\n  --grey-600: #475467;\n  --grey-500: #667085; /* Roughly 65% opacity */\n  --grey-400: #98a2b3;\n  --grey-300: #d0d5dd;\n  --grey-200: #eaecf0;\n  --grey-100: #f2f4f7;\n  --grey-50: #f9fafb;\n  --grey-25: #fcfcfd;\n  --dark-grey: var(--grey-700); /* Deprecated */\n  --medium-grey: rgba(40, 47, 54, 0.65);\n  --grey: var(--grey-500); /* Deprecated */\n  --grey-border: var(--grey-300); /* Deprecated */\n  --lightest-grey: rgb(250, 250, 250);\n  --sidebar-color: var(--grey-50);\n  --sidebar-hover: var(--grey-200);\n  --primary-700: #2b66a9;\n  --primary-500: #4199fd;\n  --primary-300: #80bbfe;\n  --primary-200: #c0ddfe;\n  --primary-50: #ecf5ff;\n  --primary-25: #f5faff;\n  --primary-color: var(--primary-500); /* Deprecated */\n  --primary-hover: var(--primary-700); /* Deprecated */\n  --light-blue: var(--primary-25); /* Deprecated */\n  --blue-border: rgb(188, 224, 253);\n  --error-700: #b42318;\n  --error-500: #f04438;\n  --error-300: #fecdca;\n  --error-100: #fee4e2;\n  --error-25: #fff5f4;\n  --green: rgb(63, 172, 37);\n  --orange: #f15a24;\n  --warning-500: #f79009;\n  --pink: rgb(226, 42, 110);\n  --purple: rgb(141, 53, 245);\n  --success-900: #054f31;\n  --success-800: #065f46;\n  --success-500: #12b76a;\n  --success-100: #d1fae5;\n  --success-200: #a6f4c5;\n  --success-50: #ecfdf3;\n  --success-25: #f6fef9;\n  --border-radius: 35px;\n  --small-radius: 8px;\n  --medium-radius: 16px;\n  --small-sidebar: 275px;\n  --medium-sidebar: 350px;\n  --big-sidebar: 390px;\n  --biggest-sidebar: 450px;\n  --text-xs: 12px;\n  --text-sm: 14px;\n  --text-md: 16px;\n  --text-lg: 18px;\n  --text-xl: 20px;\n  --h-xs: 24px;\n  --h-sm: 30px;\n  --h-md: 36px;\n  --leading: 1.5;\n  --gap-2xs: 4px;\n  --gap-xs: 8px;\n  --gap-sm: 16px;\n  --gap-md: 24px;\n  --gap-lg: 32px;\n  --gap-xl: 40px;\n  --gap-2xl: 48px;\n  --box-shadow-xs: 0 0.47074466943740845px 0.9414893388748169px 0 rgba(16, 24, 40, 0.05);\n  --box-shadow-sm: 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-md: 0 1.88298px 3.76596px -0.941489px rgba(16, 24, 40, 0.1), 0 0.941489px 1.88298px -0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-lg: 0 6px 8px -2px rgba(16, 24, 40, 0.08), 0 1.88298px 4px -1px rgba(16, 24, 40, 0.03), 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-xl: 0 9.41489px 11.2979px -1.88298px rgba(16, 24, 40, 0.08), 0 3.76596px 3.76596px -1.88298px rgba(16, 24, 40, 0.03);\n  --box-shadow-xxl: 0 11px 22px -5px rgba(16, 24, 40, 0.18);\n  --button-shadow: 0 0.47px 0.94px 0 rgba(16, 24, 40, 0.06), 0 0.47px 1.47px 0 rgba(16, 24, 40, 0.1);\n  /* Override front-end CSS */\n  --check-label-color: var(--grey-700);\n}\n\n.frm_hidden {\n  display: none;\n}\n\n.frm-component:not(.frm-tabs-wrapper) {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.frm-component:not(.frm-tabs-wrapper) > span.frm-component-label {\n  font-weight: 500;\n  font-size: var(--text-sm) !important;\n  color: var(--grey-900) !important;\n  width: 40% !important;\n  display: block !important;\n  margin-right: 12px !important;\n}\n\n.frm-colorpicker-component .wp-picker-container button[type=button] {\n  position: relative;\n  height: 36px !important;\n  background-image: none !important;\n  background-color: #fff !important;\n  overflow: hidden;\n}\n.frm-colorpicker-component .wp-picker-container button[type=button]:after {\n  content: \"\";\n  width: 20px;\n  height: 20px;\n  display: block;\n  position: absolute;\n  top: 0;\n  right: 8px;\n  bottom: 0;\n  margin: auto;\n  background: url(\"--frm-plugin-url/images/style/small-arrow.svg\") no-repeat;\n  background-position: center;\n  z-index: 10;\n}\n.frm-colorpicker-component .wp-picker-container button[type=button]:focus {\n  border-color: var(--primary-500) !important;\n}\n.frm-colorpicker-component .wp-color-result-text {\n  line-height: 36px !important;\n  padding: 0 12px;\n  border: 0;\n}\n.frm-colorpicker-component .color-alpha {\n  width: 20px !important;\n  height: 20px !important;\n  border-radius: 50% !important;\n  border: 1px solid rgb(208, 213, 221);\n  top: 0;\n  left: 0;\n  bottom: 0;\n  margin: auto;\n  margin-left: 12px;\n}\n.frm-colorpicker-component .wp-picker-input-wrap input {\n  width: calc(100% - 10px) !important;\n  margin: 1px 5px;\n  height: 32px;\n  line-height: 32px;\n}\n\n/*# sourceMappingURL=frm-colorpicker-component.css.map*/");

/***/ }),

/***/ "./js/src/web-components/frm-dropdown-component/frm-dropdown-component.css":
/*!*********************************************************************************!*\
  !*** ./js/src/web-components/frm-dropdown-component/frm-dropdown-component.css ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("/*!**********************************************************************************************************************************************************************************************************************************************************************!*\\\n  !*** css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[0].use[1]!./node_modules/css-unicode-loader/index.js!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[0].use[3]!./js/src/web-components/frm-dropdown-component/frm-dropdown-component.scss ***!\n  \\**********************************************************************************************************************************************************************************************************************************************************************/\n/**\n * Base - Variables\n */\n:root,\n.frm-white-body,\n.frm_wrap {\n  --grey-900: #101828;\n  --grey-800: #1d2939;\n  --grey-700: #344054;\n  --grey-600: #475467;\n  --grey-500: #667085; /* Roughly 65% opacity */\n  --grey-400: #98a2b3;\n  --grey-300: #d0d5dd;\n  --grey-200: #eaecf0;\n  --grey-100: #f2f4f7;\n  --grey-50: #f9fafb;\n  --grey-25: #fcfcfd;\n  --dark-grey: var(--grey-700); /* Deprecated */\n  --medium-grey: rgba(40, 47, 54, 0.65);\n  --grey: var(--grey-500); /* Deprecated */\n  --grey-border: var(--grey-300); /* Deprecated */\n  --lightest-grey: rgb(250, 250, 250);\n  --sidebar-color: var(--grey-50);\n  --sidebar-hover: var(--grey-200);\n  --primary-700: #2b66a9;\n  --primary-500: #4199fd;\n  --primary-300: #80bbfe;\n  --primary-200: #c0ddfe;\n  --primary-50: #ecf5ff;\n  --primary-25: #f5faff;\n  --primary-color: var(--primary-500); /* Deprecated */\n  --primary-hover: var(--primary-700); /* Deprecated */\n  --light-blue: var(--primary-25); /* Deprecated */\n  --blue-border: rgb(188, 224, 253);\n  --error-700: #b42318;\n  --error-500: #f04438;\n  --error-300: #fecdca;\n  --error-100: #fee4e2;\n  --error-25: #fff5f4;\n  --green: rgb(63, 172, 37);\n  --orange: #f15a24;\n  --warning-500: #f79009;\n  --pink: rgb(226, 42, 110);\n  --purple: rgb(141, 53, 245);\n  --success-900: #054f31;\n  --success-800: #065f46;\n  --success-500: #12b76a;\n  --success-100: #d1fae5;\n  --success-200: #a6f4c5;\n  --success-50: #ecfdf3;\n  --success-25: #f6fef9;\n  --border-radius: 35px;\n  --small-radius: 8px;\n  --medium-radius: 16px;\n  --small-sidebar: 275px;\n  --medium-sidebar: 350px;\n  --big-sidebar: 390px;\n  --biggest-sidebar: 450px;\n  --text-xs: 12px;\n  --text-sm: 14px;\n  --text-md: 16px;\n  --text-lg: 18px;\n  --text-xl: 20px;\n  --h-xs: 24px;\n  --h-sm: 30px;\n  --h-md: 36px;\n  --leading: 1.5;\n  --gap-2xs: 4px;\n  --gap-xs: 8px;\n  --gap-sm: 16px;\n  --gap-md: 24px;\n  --gap-lg: 32px;\n  --gap-xl: 40px;\n  --gap-2xl: 48px;\n  --box-shadow-xs: 0 0.47074466943740845px 0.9414893388748169px 0 rgba(16, 24, 40, 0.05);\n  --box-shadow-sm: 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-md: 0 1.88298px 3.76596px -0.941489px rgba(16, 24, 40, 0.1), 0 0.941489px 1.88298px -0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-lg: 0 6px 8px -2px rgba(16, 24, 40, 0.08), 0 1.88298px 4px -1px rgba(16, 24, 40, 0.03), 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-xl: 0 9.41489px 11.2979px -1.88298px rgba(16, 24, 40, 0.08), 0 3.76596px 3.76596px -1.88298px rgba(16, 24, 40, 0.03);\n  --box-shadow-xxl: 0 11px 22px -5px rgba(16, 24, 40, 0.18);\n  --button-shadow: 0 0.47px 0.94px 0 rgba(16, 24, 40, 0.06), 0 0.47px 1.47px 0 rgba(16, 24, 40, 0.1);\n  /* Override front-end CSS */\n  --check-label-color: var(--grey-700);\n}\n\n.frm_hidden {\n  display: none;\n}\n\n.frm-component:not(.frm-tabs-wrapper) {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.frm-component:not(.frm-tabs-wrapper) > span.frm-component-label {\n  font-weight: 500;\n  font-size: var(--text-sm) !important;\n  color: var(--grey-900) !important;\n  width: 40% !important;\n  display: block !important;\n  margin-right: 12px !important;\n}\n\n.frm-dropdown-component select {\n  width: 100%;\n  outline: 0;\n  box-shadow: var(--box-shadow-xs);\n  border-radius: var(--small-radius);\n  padding: 5px var(--gap-lg) 5px 14px;\n  border-color: var(--grey-300);\n  color: var(--grey-800);\n  font-size: var(--text-md);\n  margin: 0;\n  line-height: var(--leading);\n  border-width: 1px;\n  border-style: solid;\n  background-position: center 5px;\n  background: transparent url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none'%3E%3Cpath stroke='%2398A2B3' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M12.708 8.959 10 11.875 7.292 8.96'/%3E%3C/svg%3E\") no-repeat right 8px top 50%;\n  background-color: #fff;\n  appearance: none;\n  -webkit-appearance: none;\n  -moz-appearance: none;\n}\n.frm-dropdown-component select:focus {\n  border-color: var(--primary-500);\n  box-shadow: none;\n}\n\n/*# sourceMappingURL=frm-dropdown-component.css.map*/");

/***/ }),

/***/ "./js/src/web-components/frm-range-slider-component/frm-range-slider-component.css":
/*!*****************************************************************************************!*\
  !*** ./js/src/web-components/frm-range-slider-component/frm-range-slider-component.css ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("/*!******************************************************************************************************************************************************************************************************************************************************************************!*\\\n  !*** css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[0].use[1]!./node_modules/css-unicode-loader/index.js!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[0].use[3]!./js/src/web-components/frm-range-slider-component/frm-range-slider-component.scss ***!\n  \\******************************************************************************************************************************************************************************************************************************************************************************/\n/**\n * Sliders component styles\n */\n.frm-style-component .frm-slider-container {\n  width: calc(100% - 91px);\n  display: flex;\n  align-items: center;\n  color: rgb(29, 41, 57);\n}\n.frm-style-component .frm-slider-container svg.frmsvg {\n  color: currentColor;\n  margin-right: 8px;\n  margin-left: -5px;\n  position: relative;\n  z-index: 15;\n}\n\n.frm-style-component .frm-group-sliders .frm-slider-container svg.frmsvg:hover {\n  color: rgb(65, 153, 253);\n  cursor: pointer;\n}\n\n.frm-style-component .frm-slider-container .frm-slider-active-track {\n  display: block;\n  height: 100%;\n  width: 0;\n  position: relative;\n  border-radius: 200px;\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track,\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet {\n  background: rgb(65, 153, 253);\n  box-shadow: 0 1.88px 4px -1px rgba(16, 24, 40, 0.03), 0 6px 8px -2px rgba(16, 24, 40, 0.08);\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet {\n  position: absolute;\n  display: block;\n  width: 16px;\n  height: 16px;\n  border-radius: 50%;\n  transform: translateX(15px);\n  cursor: grab;\n  right: 0;\n  top: 0;\n  bottom: 0;\n  margin: auto;\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet .frm-slider-value-label {\n  position: absolute;\n  display: block;\n  width: 48px;\n  height: 36px;\n  background: rgb(16, 24, 40);\n  color: white;\n  font-weight: 400;\n  font-size: var(--text-sm);\n  line-height: 36px;\n  border-radius: var(--small-radius);\n  transform: translate(-18px, -42px) scale3d(0.7, 1, 1);\n  opacity: 0;\n  z-index: -2;\n  pointer-events: none;\n  text-align: center;\n  user-select: none;\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet.frm-dragging .frm-slider-value-label {\n  z-index: 12;\n  transform: translate(-18px, -42px) scale3d(1, 1, 1);\n  opacity: 1;\n  transition: 0.3s opacity, 0.35s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet::before {\n  content: \"\";\n  display: block;\n  position: absolute;\n  width: 18px;\n  height: 18px;\n  border-radius: 50%;\n  left: 0;\n  top: 0;\n  transform: translate(-5px, -5px) scale3d(0.7, 0.7, 1);\n  border: 4px solid rgb(65, 153, 253);\n  opacity: 0;\n  transition: 0.3s opacity, 0.35s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet.frm-dragging::before {\n  opacity: 0.5;\n  transform: translate(-5px, -5px) scale3d(1, 1, 1);\n  transition: 0.3s opacity, 0.35s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n}\n.frm-style-component .frm-slider-container .frm-slider-active-track .frm-slider-bullet::after {\n  content: \"\";\n  position: absolute;\n  display: block;\n  width: 180%;\n  height: 200%;\n  border-radius: 50%;\n  left: 0;\n  top: 0;\n  transform: translate(-24%, -24%);\n}\n\n.frm-style-component .frm-slider-value {\n  width: 86px;\n  height: 36px;\n  display: flex;\n  justify-content: center;\n  box-sizing: border-box;\n  background: white;\n  border-radius: var(--small-radius);\n  border: 1px solid var(--grey-300);\n}\n.frm-style-component .frm-slider-value > * {\n  border: none;\n}\n.frm-style-component .frm-slider-value input {\n  width: 40px;\n  height: 100%;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(16, 24, 40);\n  padding-left: 12px;\n  box-sizing: border-box;\n}\n.frm-style-component .frm-slider-value select {\n  text-align: right;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(102, 112, 133);\n  width: 44px;\n  background: url(\"../../images/style/small-arrow.svg\") no-repeat;\n  background-position: center right 12px;\n  padding-right: 24px !important;\n}\n.frm-style-component .frm-slider-value select.frm-single-unit {\n  pointer-events: none;\n  background-image: none;\n  padding-right: 12px !important;\n}\n.frm-style-component .frm-slider {\n  display: block;\n  width: calc(100% - 5px);\n  height: 4px;\n  background: rgb(234, 236, 240);\n  border-radius: 200px;\n  cursor: pointer;\n}\n\n.frm-slider-component .frm-independent-slider-field {\n  margin-top: 10px;\n}\n.frm-slider-component.frm-disabled .frm-slider-container {\n  pointer-events: none;\n  opacity: 0.5;\n}\n.frm-slider-component.frm-disabled .frm-slider-value input[type=text] {\n  width: 28px;\n}\n.frm-slider-component.frm-disabled .frm-slider-value select {\n  width: 56px;\n}\n.frm-slider-component.frm-disabled.frm-empty .frm-slider-value input[type=text] {\n  width: 56px;\n}\n.frm-slider-component.frm-disabled.frm-empty .frm-slider-value select {\n  width: 28px;\n}\n\n.frm-style-component .frm-slider-component.frm-has-multiple-values {\n  margin-bottom: 10px;\n}\n\n/**\n * Base - Variables\n */\n:root,\n.frm-white-body,\n.frm_wrap {\n  --grey-900: #101828;\n  --grey-800: #1d2939;\n  --grey-700: #344054;\n  --grey-600: #475467;\n  --grey-500: #667085; /* Roughly 65% opacity */\n  --grey-400: #98a2b3;\n  --grey-300: #d0d5dd;\n  --grey-200: #eaecf0;\n  --grey-100: #f2f4f7;\n  --grey-50: #f9fafb;\n  --grey-25: #fcfcfd;\n  --dark-grey: var(--grey-700); /* Deprecated */\n  --medium-grey: rgba(40, 47, 54, 0.65);\n  --grey: var(--grey-500); /* Deprecated */\n  --grey-border: var(--grey-300); /* Deprecated */\n  --lightest-grey: rgb(250, 250, 250);\n  --sidebar-color: var(--grey-50);\n  --sidebar-hover: var(--grey-200);\n  --primary-700: #2b66a9;\n  --primary-500: #4199fd;\n  --primary-300: #80bbfe;\n  --primary-200: #c0ddfe;\n  --primary-50: #ecf5ff;\n  --primary-25: #f5faff;\n  --primary-color: var(--primary-500); /* Deprecated */\n  --primary-hover: var(--primary-700); /* Deprecated */\n  --light-blue: var(--primary-25); /* Deprecated */\n  --blue-border: rgb(188, 224, 253);\n  --error-700: #b42318;\n  --error-500: #f04438;\n  --error-300: #fecdca;\n  --error-100: #fee4e2;\n  --error-25: #fff5f4;\n  --green: rgb(63, 172, 37);\n  --orange: #f15a24;\n  --warning-500: #f79009;\n  --pink: rgb(226, 42, 110);\n  --purple: rgb(141, 53, 245);\n  --success-900: #054f31;\n  --success-800: #065f46;\n  --success-500: #12b76a;\n  --success-100: #d1fae5;\n  --success-200: #a6f4c5;\n  --success-50: #ecfdf3;\n  --success-25: #f6fef9;\n  --border-radius: 35px;\n  --small-radius: 8px;\n  --medium-radius: 16px;\n  --small-sidebar: 275px;\n  --medium-sidebar: 350px;\n  --big-sidebar: 390px;\n  --biggest-sidebar: 450px;\n  --text-xs: 12px;\n  --text-sm: 14px;\n  --text-md: 16px;\n  --text-lg: 18px;\n  --text-xl: 20px;\n  --h-xs: 24px;\n  --h-sm: 30px;\n  --h-md: 36px;\n  --leading: 1.5;\n  --gap-2xs: 4px;\n  --gap-xs: 8px;\n  --gap-sm: 16px;\n  --gap-md: 24px;\n  --gap-lg: 32px;\n  --gap-xl: 40px;\n  --gap-2xl: 48px;\n  --box-shadow-xs: 0 0.47074466943740845px 0.9414893388748169px 0 rgba(16, 24, 40, 0.05);\n  --box-shadow-sm: 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-md: 0 1.88298px 3.76596px -0.941489px rgba(16, 24, 40, 0.1), 0 0.941489px 1.88298px -0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-lg: 0 6px 8px -2px rgba(16, 24, 40, 0.08), 0 1.88298px 4px -1px rgba(16, 24, 40, 0.03), 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-xl: 0 9.41489px 11.2979px -1.88298px rgba(16, 24, 40, 0.08), 0 3.76596px 3.76596px -1.88298px rgba(16, 24, 40, 0.03);\n  --box-shadow-xxl: 0 11px 22px -5px rgba(16, 24, 40, 0.18);\n  --button-shadow: 0 0.47px 0.94px 0 rgba(16, 24, 40, 0.06), 0 0.47px 1.47px 0 rgba(16, 24, 40, 0.1);\n  /* Override front-end CSS */\n  --check-label-color: var(--grey-700);\n}\n\n.frm_hidden {\n  display: none;\n}\n\n.frm-component:not(.frm-tabs-wrapper) {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.frm-component:not(.frm-tabs-wrapper) > span.frm-component-label {\n  font-weight: 500;\n  font-size: var(--text-sm) !important;\n  color: var(--grey-900) !important;\n  width: 40% !important;\n  display: block !important;\n  margin-right: 12px !important;\n}\n\n.frm-component .frmsvg {\n  width: 18px;\n  height: 18px;\n}\n.frm-component .frm-sliders-container {\n  width: 100%;\n}\n.frm-component .frm-group-sliders .frm-slider-component {\n  margin-top: 5px;\n}\n\n/*# sourceMappingURL=frm-range-slider-component.css.map*/");

/***/ }),

/***/ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.css":
/*!*******************************************************************************************!*\
  !*** ./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.css ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("/*!********************************************************************************************************************************************************************************************************************************************************************************!*\\\n  !*** css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[0].use[1]!./node_modules/css-unicode-loader/index.js!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[0].use[3]!./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.scss ***!\n  \\********************************************************************************************************************************************************************************************************************************************************************************/\n/**\n * Base - Variables\n */\n:root,\n.frm-white-body,\n.frm_wrap {\n  --grey-900: #101828;\n  --grey-800: #1d2939;\n  --grey-700: #344054;\n  --grey-600: #475467;\n  --grey-500: #667085; /* Roughly 65% opacity */\n  --grey-400: #98a2b3;\n  --grey-300: #d0d5dd;\n  --grey-200: #eaecf0;\n  --grey-100: #f2f4f7;\n  --grey-50: #f9fafb;\n  --grey-25: #fcfcfd;\n  --dark-grey: var(--grey-700); /* Deprecated */\n  --medium-grey: rgba(40, 47, 54, 0.65);\n  --grey: var(--grey-500); /* Deprecated */\n  --grey-border: var(--grey-300); /* Deprecated */\n  --lightest-grey: rgb(250, 250, 250);\n  --sidebar-color: var(--grey-50);\n  --sidebar-hover: var(--grey-200);\n  --primary-700: #2b66a9;\n  --primary-500: #4199fd;\n  --primary-300: #80bbfe;\n  --primary-200: #c0ddfe;\n  --primary-50: #ecf5ff;\n  --primary-25: #f5faff;\n  --primary-color: var(--primary-500); /* Deprecated */\n  --primary-hover: var(--primary-700); /* Deprecated */\n  --light-blue: var(--primary-25); /* Deprecated */\n  --blue-border: rgb(188, 224, 253);\n  --error-700: #b42318;\n  --error-500: #f04438;\n  --error-300: #fecdca;\n  --error-100: #fee4e2;\n  --error-25: #fff5f4;\n  --green: rgb(63, 172, 37);\n  --orange: #f15a24;\n  --warning-500: #f79009;\n  --pink: rgb(226, 42, 110);\n  --purple: rgb(141, 53, 245);\n  --success-900: #054f31;\n  --success-800: #065f46;\n  --success-500: #12b76a;\n  --success-100: #d1fae5;\n  --success-200: #a6f4c5;\n  --success-50: #ecfdf3;\n  --success-25: #f6fef9;\n  --border-radius: 35px;\n  --small-radius: 8px;\n  --medium-radius: 16px;\n  --small-sidebar: 275px;\n  --medium-sidebar: 350px;\n  --big-sidebar: 390px;\n  --biggest-sidebar: 450px;\n  --text-xs: 12px;\n  --text-sm: 14px;\n  --text-md: 16px;\n  --text-lg: 18px;\n  --text-xl: 20px;\n  --h-xs: 24px;\n  --h-sm: 30px;\n  --h-md: 36px;\n  --leading: 1.5;\n  --gap-2xs: 4px;\n  --gap-xs: 8px;\n  --gap-sm: 16px;\n  --gap-md: 24px;\n  --gap-lg: 32px;\n  --gap-xl: 40px;\n  --gap-2xl: 48px;\n  --box-shadow-xs: 0 0.47074466943740845px 0.9414893388748169px 0 rgba(16, 24, 40, 0.05);\n  --box-shadow-sm: 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-md: 0 1.88298px 3.76596px -0.941489px rgba(16, 24, 40, 0.1), 0 0.941489px 1.88298px -0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-lg: 0 6px 8px -2px rgba(16, 24, 40, 0.08), 0 1.88298px 4px -1px rgba(16, 24, 40, 0.03), 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-xl: 0 9.41489px 11.2979px -1.88298px rgba(16, 24, 40, 0.08), 0 3.76596px 3.76596px -1.88298px rgba(16, 24, 40, 0.03);\n  --box-shadow-xxl: 0 11px 22px -5px rgba(16, 24, 40, 0.18);\n  --button-shadow: 0 0.47px 0.94px 0 rgba(16, 24, 40, 0.06), 0 0.47px 1.47px 0 rgba(16, 24, 40, 0.1);\n  /* Override front-end CSS */\n  --check-label-color: var(--grey-700);\n}\n\n.frm_hidden {\n  display: none;\n}\n\n.frm-component:not(.frm-tabs-wrapper) {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.frm-component:not(.frm-tabs-wrapper) > span.frm-component-label {\n  font-weight: 500;\n  font-size: var(--text-sm) !important;\n  color: var(--grey-900) !important;\n  width: 40% !important;\n  display: block !important;\n  margin-right: 12px !important;\n}\n\n.frm-tabs-wrapper {\n  position: relative;\n  overflow: hidden;\n}\n.frm-tabs-wrapper .frm-tabs-navigator {\n  margin: 0;\n  padding: 0;\n  display: flex;\n  gap: var(--gap-xs);\n  justify-content: space-between;\n  align-items: center;\n  background: rgb(242, 244, 247);\n  border-radius: var(--small-radius);\n  box-sizing: border-box;\n  height: 44px;\n  position: relative;\n  z-index: 2;\n}\n.frm-tabs-wrapper .frm-tabs-navigator .frm-tab-item {\n  flex: 1;\n  text-align: center;\n  cursor: pointer;\n}\n.frm-tabs-wrapper .frm-tabs-navigator .frm-active-background {\n  display: block;\n  height: 100%;\n  background: white;\n  position: absolute;\n  top: 0;\n  left: 0;\n  z-index: 1;\n}\n\n.frm-tabs-navs {\n  padding: 0;\n  min-height: 44px;\n}\n.frm-tabs-navs ul {\n  margin: 0;\n  height: var(--h-md);\n  position: relative;\n  display: flex;\n  justify-content: space-between;\n  list-style-type: none;\n  padding: 0px;\n}\n.frm-tabs-navs ul li,\n.frm-tabs-navs ul li a {\n  color: var(--grey-500);\n  font-weight: 500;\n  font-size: var(--text-sm);\n  line-height: 28px;\n}\n.frm-tabs-navs ul li {\n  flex: 1;\n  height: 28px;\n  text-align: center;\n  margin-top: var(--gap-xs);\n  margin-bottom: 0;\n  cursor: pointer;\n}\n\n.frm-tabs-navs ul li.frm-active, .frm-style-tabs-wrapper .frm-tabs-navs ul li.frm-active a {\n  color: var(--grey-900);\n}\n\n.frm-tabs-navs ul li:first-child {\n  margin-left: var(--gap-xs);\n}\n\n.frm-tabs-navs ul li:last-child {\n  margin-right: var(--gap-xs);\n}\n\n.frm-tabs-delimiter {\n  position: absolute;\n  top: 0;\n  left: 0;\n  width: 100%;\n  background: rgb(242, 244, 247);\n  height: 44px;\n  margin: 0;\n  border-radius: var(--small-radius);\n}\n.frm-tabs-delimiter .frm-tabs-active-underline {\n  height: 28px;\n  background: white;\n  position: absolute;\n  left: 0;\n  bottom: 8px;\n  transition: 0.4s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n  border-radius: var(--small-radius);\n  box-shadow: var(--button-shadow);\n  display: none;\n}\n\n.frm-tabs-container {\n  position: relative;\n  overflow: hidden;\n  margin-top: var(--gap-md);\n  height: 100%;\n}\n\n.frm-tabs-container .frm-tabs-slide-track {\n  display: flex;\n  transition: 0.32s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n}\n\n.frm-tabs-slide-track > div {\n  flex: 0 0 100%;\n  opacity: 0;\n  transition: 0.25s opacity linear;\n  position: relative;\n  height: auto;\n  max-height: unset;\n  overflow: hidden;\n  box-sizing: border-box;\n}\n\n.frm-tabs-slide-track > div > div {\n  overflow: auto;\n  position: relative;\n  width: 100%;\n  padding: 0;\n  box-sizing: border-box;\n}\n\n.frm-tabs-slide-track > div > div:first-child {\n  height: 100%;\n}\n\n.frm-tabs-slide-track > div.frm-active {\n  opacity: 1;\n  transition: 0.35s opacity linear;\n}\n\n/*# sourceMappingURL=frm-tab-navigator-component.css.map*/");

/***/ }),

/***/ "./js/src/web-components/frm-typography-component/frm-typography-component.css":
/*!*************************************************************************************!*\
  !*** ./js/src/web-components/frm-typography-component/frm-typography-component.css ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ("/*!**************************************************************************************************************************************************************************************************************************************************************************!*\\\n  !*** css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[0].use[1]!./node_modules/css-unicode-loader/index.js!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[0].use[3]!./js/src/web-components/frm-typography-component/frm-typography-component.scss ***!\n  \\**************************************************************************************************************************************************************************************************************************************************************************/\n/**\n * Base - Variables\n */\n:root,\n.frm-white-body,\n.frm_wrap {\n  --grey-900: #101828;\n  --grey-800: #1d2939;\n  --grey-700: #344054;\n  --grey-600: #475467;\n  --grey-500: #667085; /* Roughly 65% opacity */\n  --grey-400: #98a2b3;\n  --grey-300: #d0d5dd;\n  --grey-200: #eaecf0;\n  --grey-100: #f2f4f7;\n  --grey-50: #f9fafb;\n  --grey-25: #fcfcfd;\n  --dark-grey: var(--grey-700); /* Deprecated */\n  --medium-grey: rgba(40, 47, 54, 0.65);\n  --grey: var(--grey-500); /* Deprecated */\n  --grey-border: var(--grey-300); /* Deprecated */\n  --lightest-grey: rgb(250, 250, 250);\n  --sidebar-color: var(--grey-50);\n  --sidebar-hover: var(--grey-200);\n  --primary-700: #2b66a9;\n  --primary-500: #4199fd;\n  --primary-300: #80bbfe;\n  --primary-200: #c0ddfe;\n  --primary-50: #ecf5ff;\n  --primary-25: #f5faff;\n  --primary-color: var(--primary-500); /* Deprecated */\n  --primary-hover: var(--primary-700); /* Deprecated */\n  --light-blue: var(--primary-25); /* Deprecated */\n  --blue-border: rgb(188, 224, 253);\n  --error-700: #b42318;\n  --error-500: #f04438;\n  --error-300: #fecdca;\n  --error-100: #fee4e2;\n  --error-25: #fff5f4;\n  --green: rgb(63, 172, 37);\n  --orange: #f15a24;\n  --warning-500: #f79009;\n  --pink: rgb(226, 42, 110);\n  --purple: rgb(141, 53, 245);\n  --success-900: #054f31;\n  --success-800: #065f46;\n  --success-500: #12b76a;\n  --success-100: #d1fae5;\n  --success-200: #a6f4c5;\n  --success-50: #ecfdf3;\n  --success-25: #f6fef9;\n  --border-radius: 35px;\n  --small-radius: 8px;\n  --medium-radius: 16px;\n  --small-sidebar: 275px;\n  --medium-sidebar: 350px;\n  --big-sidebar: 390px;\n  --biggest-sidebar: 450px;\n  --text-xs: 12px;\n  --text-sm: 14px;\n  --text-md: 16px;\n  --text-lg: 18px;\n  --text-xl: 20px;\n  --h-xs: 24px;\n  --h-sm: 30px;\n  --h-md: 36px;\n  --leading: 1.5;\n  --gap-2xs: 4px;\n  --gap-xs: 8px;\n  --gap-sm: 16px;\n  --gap-md: 24px;\n  --gap-lg: 32px;\n  --gap-xl: 40px;\n  --gap-2xl: 48px;\n  --box-shadow-xs: 0 0.47074466943740845px 0.9414893388748169px 0 rgba(16, 24, 40, 0.05);\n  --box-shadow-sm: 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-md: 0 1.88298px 3.76596px -0.941489px rgba(16, 24, 40, 0.1), 0 0.941489px 1.88298px -0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-lg: 0 6px 8px -2px rgba(16, 24, 40, 0.08), 0 1.88298px 4px -1px rgba(16, 24, 40, 0.03), 0 0.470745px 1.41223px rgba(16, 24, 40, 0.1), 0 0.470745px 0.941489px rgba(16, 24, 40, 0.06);\n  --box-shadow-xl: 0 9.41489px 11.2979px -1.88298px rgba(16, 24, 40, 0.08), 0 3.76596px 3.76596px -1.88298px rgba(16, 24, 40, 0.03);\n  --box-shadow-xxl: 0 11px 22px -5px rgba(16, 24, 40, 0.18);\n  --button-shadow: 0 0.47px 0.94px 0 rgba(16, 24, 40, 0.06), 0 0.47px 1.47px 0 rgba(16, 24, 40, 0.1);\n  /* Override front-end CSS */\n  --check-label-color: var(--grey-700);\n}\n\n.frm_hidden {\n  display: none;\n}\n\n.frm-component:not(.frm-tabs-wrapper) {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.frm-component:not(.frm-tabs-wrapper) > span.frm-component-label {\n  font-weight: 500;\n  font-size: var(--text-sm) !important;\n  color: var(--grey-900) !important;\n  width: 40% !important;\n  display: block !important;\n  margin-right: 12px !important;\n}\n\n.frm-typography-component .frm-typography-container {\n  display: flex;\n  justify-content: space-between;\n  width: 100%;\n}\n.frm-typography-component .frm-typography-container select {\n  width: calc(70% - 6px);\n  outline: 0;\n  box-shadow: var(--box-shadow-xs);\n  border-radius: var(--small-radius);\n  padding: 5px 14px;\n  border-color: var(--grey-300);\n  color: var(--grey-800);\n  font-size: var(--text-md);\n  margin: 0;\n  background-color: #fff;\n  line-height: var(--leading);\n  border-width: 1px;\n  border-style: solid;\n}\n.frm-typography-component .frm-typography-container .frm-unit-value {\n  width: 28%;\n  height: 36px;\n  display: flex;\n  justify-content: center;\n  box-sizing: border-box;\n  background: white;\n  border-radius: var(--small-radius);\n  border: 1px solid var(--grey-300);\n  overflow: hidden;\n}\n.frm-typography-component .frm-typography-container .frm-unit-value > * {\n  border: none;\n}\n.frm-typography-component .frm-typography-container .frm-unit-value input {\n  width: 32px;\n  height: 100%;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(16, 24, 40);\n  padding-left: 8px;\n  box-sizing: border-box;\n}\n.frm-typography-component .frm-typography-container .frm-unit-value input:focus {\n  outline: none;\n}\n.frm-typography-component .frm-typography-container .frm-unit-value select {\n  text-align: left;\n  padding: 0;\n  font-size: var(--text-sm);\n  color: rgb(102, 112, 133);\n  width: 36px;\n  background: url(\"../../images/style/small-arrow.svg\") no-repeat;\n  background-position: center right 12px;\n}\n.frm-typography-component .frm-typography-container .frm-unit-value select:focus {\n  outline: none;\n}\n\n/*# sourceMappingURL=frm-typography-component.css.map*/");

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

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js ***!
  \*********************************************************************/
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

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _arrayWithoutHoles)
/* harmony export */ });
/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

function _arrayWithoutHoles(r) {
  if (Array.isArray(r)) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r);
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js ***!
  \*******************************************************************/
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

/***/ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/defineProperty.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _defineProperty)
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperty(e, r, t) {
  return (r = (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r)) in e ? Object.defineProperty(e, r, {
    value: t,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[r] = t, e;
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/iterableToArray.js":
/*!********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/iterableToArray.js ***!
  \********************************************************************/
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

/***/ "./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js ***!
  \**********************************************************************/
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

/***/ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _toConsumableArray)
/* harmony export */ });
/* harmony import */ var _arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayWithoutHoles.js */ "./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js");
/* harmony import */ var _iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./iterableToArray.js */ "./node_modules/@babel/runtime/helpers/esm/iterableToArray.js");
/* harmony import */ var _unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./unsupportedIterableToArray.js */ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js");
/* harmony import */ var _nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./nonIterableSpread.js */ "./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js");




function _toConsumableArray(r) {
  return (0,_arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r) || (0,_iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__["default"])(r) || (0,_unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__["default"])(r) || (0,_nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__["default"])();
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPrimitive)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");

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

/***/ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPropertyKey)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./toPrimitive.js */ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js");


function toPropertyKey(t) {
  var i = (0,_toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__["default"])(t, "string");
  return "symbol" == (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i) ? i : i + "";
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!***********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \***********************************************************/
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


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js":
/*!*******************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _unsupportedIterableToArray)
/* harmony export */ });
/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ("string" == typeof r) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r, a) : void 0;
  }
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
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!****************************************!*\
  !*** ./js/src/web-components/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _frm_tab_navigator_component_frm_tab_navigator_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./frm-tab-navigator-component/frm-tab-navigator-component */ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.js");
/* harmony import */ var _frm_colorpicker_component_frm_colorpicker_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./frm-colorpicker-component/frm-colorpicker-component */ "./js/src/web-components/frm-colorpicker-component/frm-colorpicker-component.js");
/* harmony import */ var _frm_range_slider_component_frm_range_slider_component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./frm-range-slider-component/frm-range-slider-component */ "./js/src/web-components/frm-range-slider-component/frm-range-slider-component.js");
/* harmony import */ var _frm_dropdown_component_frm_dropdown_component__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./frm-dropdown-component/frm-dropdown-component */ "./js/src/web-components/frm-dropdown-component/frm-dropdown-component.js");
/* harmony import */ var _frm_typography_component_frm_typography_component__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./frm-typography-component/frm-typography-component */ "./js/src/web-components/frm-typography-component/frm-typography-component.js");
/* harmony import */ var _frm_border_radius_component_frm_border_radius_component__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./frm-border-radius-component/frm-border-radius-component */ "./js/src/web-components/frm-border-radius-component/frm-border-radius-component.js");






customElements.define('frm-tab-navigator-component', _frm_tab_navigator_component_frm_tab_navigator_component__WEBPACK_IMPORTED_MODULE_0__.frmTabNavigatorComponent);
customElements.define('frm-colorpicker-component', _frm_colorpicker_component_frm_colorpicker_component__WEBPACK_IMPORTED_MODULE_1__.frmColorpickerComponent);
customElements.define('frm-range-slider-component', _frm_range_slider_component_frm_range_slider_component__WEBPACK_IMPORTED_MODULE_2__.frmRangeSliderComponent);
customElements.define('frm-dropdown-component', _frm_dropdown_component_frm_dropdown_component__WEBPACK_IMPORTED_MODULE_3__.frmDropdownComponent);
customElements.define('frm-typography-component', _frm_typography_component_frm_typography_component__WEBPACK_IMPORTED_MODULE_4__.frmTypographyComponent);
customElements.define('frm-border-radius-component', _frm_border_radius_component_frm_border_radius_component__WEBPACK_IMPORTED_MODULE_5__.frmBorderRadiusComponent);
})();

/******/ })()
;
//# sourceMappingURL=formidable-web-components.js.map