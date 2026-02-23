/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@wordpress/dom-ready/build-module/index.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@wordpress/dom-ready/build-module/index.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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

/***/ "./js/src/core/constants.js":
/*!**********************************!*\
  !*** ./js/src/core/constants.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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

/***/ "./js/src/core/events/index.js":
/*!*************************************!*\
  !*** ./js/src/core/events/index.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addOptionBoxEvents: () => (/* reexport safe */ _optionBoxListener__WEBPACK_IMPORTED_MODULE_0__.addOptionBoxEvents)
/* harmony export */ });
/* harmony import */ var _optionBoxListener__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./optionBoxListener */ "./js/src/core/events/optionBoxListener.js");


/***/ }),

/***/ "./js/src/core/events/optionBoxListener.js":
/*!*************************************************!*\
  !*** ./js/src/core/events/optionBoxListener.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addOptionBoxEvents: () => (/* binding */ addOptionBoxEvents)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/**
 * Internal dependencies
 */

var OPTION_BOX_CLASS = '.frm-option-box';

/**
 * Manages event handling for an option-box.
 *
 * @return {void}
 */
function addOptionBoxEvents() {
  var optionBoxes = document.querySelectorAll(OPTION_BOX_CLASS);
  optionBoxes.forEach(function (optionBox) {
    optionBox.addEventListener('click', onOptionBoxClick);
  });
}

/**
 * Handles the click event on a option box item.
 *
 * @private
 * @param {Event} event The click event object.
 */
function onOptionBoxClick(event) {
  if (event.target.tagName.toLowerCase() !== 'input') {
    return;
  }
  var optionBox = event.currentTarget.closest(OPTION_BOX_CLASS);
  optionBox.classList.toggle(core_constants__WEBPACK_IMPORTED_MODULE_0__.CHECKED_CLASS);
}

/***/ }),

/***/ "./js/src/core/factory/createPageElements.js":
/*!***************************************************!*\
  !*** ./js/src/core/factory/createPageElements.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageElements: () => (/* reexport safe */ _createPageElements__WEBPACK_IMPORTED_MODULE_0__.createPageElements),
/* harmony export */   createPageState: () => (/* reexport safe */ _createPageState__WEBPACK_IMPORTED_MODULE_1__.createPageState)
/* harmony export */ });
/* harmony import */ var _createPageElements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./createPageElements */ "./js/src/core/factory/createPageElements.js");
/* harmony import */ var _createPageState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./createPageState */ "./js/src/core/factory/createPageState.js");



/***/ }),

/***/ "./js/src/core/utils/animation.js":
/*!****************************************!*\
  !*** ./js/src/core/utils/animation.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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

/***/ "./js/src/onboarding-wizard/dataUtils/index.js":
/*!*****************************************************!*\
  !*** ./js/src/onboarding-wizard/dataUtils/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupUsageData: () => (/* reexport safe */ _setupUsageData__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _setupUsageData__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setupUsageData */ "./js/src/onboarding-wizard/dataUtils/setupUsageData.js");


/***/ }),

/***/ "./js/src/onboarding-wizard/dataUtils/setupUsageData.js":
/*!**************************************************************!*\
  !*** ./js/src/onboarding-wizard/dataUtils/setupUsageData.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/**
 * Internal Dependencies
 */


/**
 * Processes and submits usage data for the specified onboarding step.
 *
 * @param {string} processedStep The name of the step that has just been processed.
 * @param {string} nextStepName  The name of the next step in the onboarding process.
 * @return {void}
 */
function setupUsageData(processedStep, nextStepName) {
  var formData = processDataForStep(processedStep, nextStepName);
  if (!formData) {
    return;
  }

  // Send the POST request
  var doJsonPost = frmDom.ajax.doJsonPost;
  doJsonPost('onboarding_setup_usage_data', formData);
}

/**
 * Processes onboarding step data and returns the corresponding FormData.
 *
 * @private
 * @param {string} processedStep The name of the step that has just been processed.
 * @param {string} nextStepName  The name of the next step in the onboarding process.
 * @return {FormData|null} The FormData to be submitted for the step, or null if there's no data.
 */
function processDataForStep(processedStep, nextStepName) {
  var formData;

  // Append completed steps if moving to the success step
  if (_shared__WEBPACK_IMPORTED_MODULE_0__.STEPS.SUCCESS === nextStepName || _shared__WEBPACK_IMPORTED_MODULE_0__.STEPS.UNSUCCESSFUL === nextStepName) {
    var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.getState)(),
      processedSteps = _getState.processedSteps;
    if (processedSteps.length > 1) {
      if (!processedSteps.includes(nextStepName)) {
        processedSteps.push(nextStepName);
      }
      formData = new FormData();
      formData.append('processed_steps', processedSteps.join(','));
      formData.append('completed_steps', true);
    }
  }

  // Append installed addons for the addon installation step
  if (_shared__WEBPACK_IMPORTED_MODULE_0__.STEPS.INSTALL_ADDONS === processedStep) {
    var _getState2 = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.getState)(),
      installedAddons = _getState2.installedAddons;
    if (installedAddons.length > 0) {
      formData = formData !== null && formData !== void 0 ? formData : new FormData();
      formData.append('installed_addons', installedAddons.join(','));
    }
  }
  return formData;
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (setupUsageData);

/***/ }),

/***/ "./js/src/onboarding-wizard/elements/elements.js":
/*!*******************************************************!*\
  !*** ./js/src/onboarding-wizard/elements/elements.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* binding */ addElements),
/* harmony export */   getElements: () => (/* binding */ getElements)
/* harmony export */ });
/* harmony import */ var core_factory__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/factory */ "./js/src/core/factory/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */

var _createPageElements = (0,core_factory__WEBPACK_IMPORTED_MODULE_0__.createPageElements)({
    onboardingWizardPage: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-wizard-page")),
    container: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-container")),
    rootline: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-rootline")),
    steps: document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-step")),
    skipStepButtons: document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-skip-step")),
    backButtons: document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-back-button")),
    consentTrackingButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-consent-tracking")),
    installAddonsButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-install-addons-button")),
    hiddenLicenseKeyInput: document.getElementById('frm-license-key')
  }),
  getElements = _createPageElements.getElements,
  addElements = _createPageElements.addElements;


/***/ }),

/***/ "./js/src/onboarding-wizard/elements/index.js":
/*!****************************************************!*\
  !*** ./js/src/onboarding-wizard/elements/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.addElements),
/* harmony export */   getElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.getElements)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./elements */ "./js/src/onboarding-wizard/elements/elements.js");


/***/ }),

/***/ "./js/src/onboarding-wizard/events/backButtonListener.js":
/*!***************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/backButtonListener.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Manages event handling for the "Back" button.
 *
 * @return {void}
 */
function addBackButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    backButtons = _getElements.backButtons;

  // Attach click event listeners to each back buttons
  backButtons.forEach(function (backButton) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(backButton, onBackButtonClick);
  });
}

/**
 * Handles the click event on a "Back" button.
 *
 * @private
 * @return {void}
 */
var onBackButtonClick = function onBackButtonClick() {
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.navigateToPrevStep)();
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addBackButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/events/consentTrackingButtonListener.js":
/*!**************************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/consentTrackingButtonListener.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Manages event handling for the "Allow & Continue" button in the "Never miss an important update" step.
 *
 * @return {void}
 */
function addConsentTrackingButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    consentTrackingButton = _getElements.consentTrackingButton;

  // Attach click event listener
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(consentTrackingButton, onConsentTrackingButtonClick);
}

/**
 * Handles the click event on the "Allow & Continue" button in the "Never miss an important update" step.
 *
 * @private
 * @return {void}
 */
var onConsentTrackingButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var doJsonPost;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          doJsonPost = frmDom.ajax.doJsonPost;
          doJsonPost('onboarding_consent_tracking', new FormData()).then(_utils__WEBPACK_IMPORTED_MODULE_2__.navigateToNextStep);
        case 1:
          return _context.a(2);
      }
    }, _callee);
  }));
  return function onConsentTrackingButtonClick() {
    return _ref.apply(this, arguments);
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addConsentTrackingButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/events/index.js":
/*!**************************************************!*\
  !*** ./js/src/onboarding-wizard/events/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addEventListeners: () => (/* binding */ addEventListeners)
/* harmony export */ });
/* harmony import */ var core_events__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/events */ "./js/src/core/events/index.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _skipStepButtonListener__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./skipStepButtonListener */ "./js/src/onboarding-wizard/events/skipStepButtonListener.js");
/* harmony import */ var _backButtonListener__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./backButtonListener */ "./js/src/onboarding-wizard/events/backButtonListener.js");
/* harmony import */ var _consentTrackingButtonListener__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./consentTrackingButtonListener */ "./js/src/onboarding-wizard/events/consentTrackingButtonListener.js");
/* harmony import */ var _installAddonsButtonListener__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./installAddonsButtonListener */ "./js/src/onboarding-wizard/events/installAddonsButtonListener.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
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
  // Add event handling for the "Skip" and "Back" buttons
  (0,_skipStepButtonListener__WEBPACK_IMPORTED_MODULE_2__["default"])();
  (0,_backButtonListener__WEBPACK_IMPORTED_MODULE_3__["default"])();
  (0,_consentTrackingButtonListener__WEBPACK_IMPORTED_MODULE_4__["default"])();

  // Add event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step
  (0,_installAddonsButtonListener__WEBPACK_IMPORTED_MODULE_5__["default"])();
  // Add event handling for an option-box
  (0,core_events__WEBPACK_IMPORTED_MODULE_0__.addOptionBoxEvents)();
}

/**
 * Responds to browser navigation events (back/forward) by updating the UI to match the step indicated in the URL or history state.
 *
 * @param {PopStateEvent} event The event object associated with the navigation action.
 * @return {void}
 */
window.addEventListener('popstate', function (event) {
  var _event$state;
  var stepName = ((_event$state = event.state) === null || _event$state === void 0 ? void 0 : _event$state.step) || (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.getQueryParam)('step');
  // Navigate to the specified step without adding to browser history
  (0,_utils__WEBPACK_IMPORTED_MODULE_6__.navigateToStep)(stepName, 'replaceState');
});

/***/ }),

/***/ "./js/src/onboarding-wizard/events/installAddonsButtonListener.js":
/*!************************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/installAddonsButtonListener.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * External dependencies
 */



/**
 * Internal dependencies
 */




/**
 * Manages event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step.
 *
 * @return {void}
 */
function addInstallAddonsButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_2__.getElements)(),
    installAddonsButton = _getElements.installAddonsButton;

  // Attach click event listener
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.onClickPreventDefault)(installAddonsButton, onInstallAddonsButtonClick);
}

/**
 * Handles the click event on the "Install & Finish Setup" button in the "Install Formidable Add-ons" step.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onInstallAddonsButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(event) {
    var addons, _getState, installedAddons, installAddonsButton, _iterator, _step, _loop, _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          addons = document.querySelectorAll('.frm-option-box.frm-checked:not(.frm-disabled)');
          _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_3__.getState)(), installedAddons = _getState.installedAddons;
          installAddonsButton = event.currentTarget;
          installAddonsButton.classList.add('frm_loading_button');
          _iterator = _createForOfIteratorHelper(addons);
          _context2.p = 1;
          _loop = /*#__PURE__*/_regenerator().m(function _loop() {
            var addon, addonTitle, _t;
            return _regenerator().w(function (_context) {
              while (1) switch (_context.p = _context.n) {
                case 0:
                  addon = _step.value;
                  _context.p = 1;
                  _context.n = 2;
                  return (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.addToRequestQueue)(function () {
                    return installAddon(addon.getAttribute('rel'), addon.dataset);
                  });
                case 2:
                  // Capture addon title
                  addonTitle = addon.dataset.title;
                  if (!installedAddons.includes(addonTitle)) {
                    installedAddons.push(addonTitle);
                  }
                  _context.n = 4;
                  break;
                case 3:
                  _context.p = 3;
                  _t = _context.v;
                  console.error('An error occurred:', _t);
                case 4:
                  return _context.a(2);
              }
            }, _loop, null, [[1, 3]]);
          });
          _iterator.s();
        case 2:
          if ((_step = _iterator.n()).done) {
            _context2.n = 4;
            break;
          }
          return _context2.d(_regeneratorValues(_loop()), 3);
        case 3:
          _context2.n = 2;
          break;
        case 4:
          _context2.n = 6;
          break;
        case 5:
          _context2.p = 5;
          _t2 = _context2.v;
          _iterator.e(_t2);
        case 6:
          _context2.p = 6;
          _iterator.f();
          return _context2.f(6);
        case 7:
          installAddonsButton.classList.remove('frm_loading_button');
          (0,_shared__WEBPACK_IMPORTED_MODULE_3__.setSingleState)('installedAddons', installedAddons);
          (0,_utils__WEBPACK_IMPORTED_MODULE_4__.navigateToNextStep)();
        case 8:
          return _context2.a(2);
      }
    }, _callee, null, [[1, 5, 6, 7]]);
  }));
  return function onInstallAddonsButtonClick(_x) {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Installs an add-on or plugin based on the provided plugin name and vendor status.
 *
 * @private
 * @param {string}  plugin              The unique identifier or name of the plugin or add-on to be installed.
 * @param {Object}  options             An object containing additional options for the installation.
 * @param {boolean} options.isInstalled Indicates whether the plugin is already installed.
 * @param {boolean} options.isVendor    Indicates whether the plugin is a vendor plugin (true) or a regular add-on (false).
 * @return {Promise<any>} A promise that resolves with the JSON response from the server after the installation request is completed.
 */
function installAddon(_x2, _x3) {
  return _installAddon.apply(this, arguments);
}
function _installAddon() {
  _installAddon = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(plugin, _ref2) {
    var isVendor, isInstalled, formData, addonAction, response, _t3;
    return _regenerator().w(function (_context3) {
      while (1) switch (_context3.p = _context3.n) {
        case 0:
          isVendor = _ref2.isVendor, isInstalled = _ref2.isInstalled;
          // Prepare FormData for the POST request
          formData = new FormData();
          formData.append('nonce', core_constants__WEBPACK_IMPORTED_MODULE_0__.nonce);
          formData.append('plugin', plugin);
          addonAction = isInstalled ? 'frm_activate_addon' : 'frm_install_addon';
          formData.append('action', isVendor ? 'frm_install_plugin' : addonAction);
          _context3.p = 1;
          _context3.n = 2;
          return fetch(ajaxurl, {
            method: 'POST',
            body: formData
          });
        case 2:
          response = _context3.v;
          if (response.ok) {
            _context3.n = 3;
            break;
          }
          throw new Error("Server responded with status ".concat(response.status));
        case 3:
          _context3.n = 4;
          return response.json();
        case 4:
          return _context3.a(2, _context3.v);
        case 5:
          _context3.p = 5;
          _t3 = _context3.v;
          console.error('An error occurred:', _t3);
        case 6:
          return _context3.a(2);
      }
    }, _callee2, null, [[1, 5]]);
  }));
  return _installAddon.apply(this, arguments);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addInstallAddonsButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/events/skipStepButtonListener.js":
/*!*******************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/skipStepButtonListener.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Manages event handling for the "Skip" step button.
 *
 * @return {void}
 */
function addSkipStepButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    skipStepButtons = _getElements.skipStepButtons;

  // Attach click event listeners to each skip buttons
  skipStepButtons.forEach(function (skipButton) {
    (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault)(skipButton, onSkipStepButtonClick);
  });
}

/**
 * Handles the click event on a "Skip" step button.
 *
 * @private
 * @return {void}
 */
var onSkipStepButtonClick = function onSkipStepButtonClick() {
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.navigateToNextStep)();
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addSkipStepButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/initializeOnboardingWizard.js":
/*!****************************************************************!*\
  !*** ./js/src/onboarding-wizard/initializeOnboardingWizard.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ui */ "./js/src/onboarding-wizard/ui/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./events */ "./js/src/onboarding-wizard/events/index.js");
/**
 * Internal dependencies
 */



/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
  (0,_ui__WEBPACK_IMPORTED_MODULE_0__.setupInitialView)();
  (0,_events__WEBPACK_IMPORTED_MODULE_1__.addEventListeners)();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (initializeOnboardingWizard);

/***/ }),

/***/ "./js/src/onboarding-wizard/shared/constants.js":
/*!******************************************************!*\
  !*** ./js/src/onboarding-wizard/shared/constants.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   INITIAL_STEP: () => (/* binding */ INITIAL_STEP),
/* harmony export */   PREFIX: () => (/* binding */ PREFIX),
/* harmony export */   STEPS: () => (/* binding */ STEPS)
/* harmony export */ });
var INITIAL_STEP = window.frmOnboardingWizardVars.INITIAL_STEP;

var PREFIX = 'frm-onboarding';
var STEPS = {
  INITIAL: INITIAL_STEP,
  INSTALL_ADDONS: 'install-addons',
  SUCCESS: 'success',
  UNSUCCESSFUL: 'unsuccessful'
};

/***/ }),

/***/ "./js/src/onboarding-wizard/shared/index.js":
/*!**************************************************!*\
  !*** ./js/src/onboarding-wizard/shared/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   INITIAL_STEP: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.INITIAL_STEP),
/* harmony export */   PREFIX: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PREFIX),
/* harmony export */   STEPS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.STEPS),
/* harmony export */   getSingleState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.getSingleState),
/* harmony export */   getState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.getState),
/* harmony export */   setSingleState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.setSingleState),
/* harmony export */   setState: () => (/* reexport safe */ _pageState__WEBPACK_IMPORTED_MODULE_1__.setState)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/onboarding-wizard/shared/constants.js");
/* harmony import */ var _pageState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pageState */ "./js/src/onboarding-wizard/shared/pageState.js");



/***/ }),

/***/ "./js/src/onboarding-wizard/shared/pageState.js":
/*!******************************************************!*\
  !*** ./js/src/onboarding-wizard/shared/pageState.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getSingleState: () => (/* binding */ getSingleState),
/* harmony export */   getState: () => (/* binding */ getState),
/* harmony export */   setSingleState: () => (/* binding */ setSingleState),
/* harmony export */   setState: () => (/* binding */ setState)
/* harmony export */ });
/* harmony import */ var core_factory__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/factory */ "./js/src/core/factory/index.js");
/**
 * External dependencies
 */

var _createPageState = (0,core_factory__WEBPACK_IMPORTED_MODULE_0__.createPageState)({
    processedSteps: [],
    installedAddons: []
  }),
  getState = _createPageState.getState,
  getSingleState = _createPageState.getSingleState,
  setState = _createPageState.setState,
  setSingleState = _createPageState.setSingleState;


/***/ }),

/***/ "./js/src/onboarding-wizard/ui/index.js":
/*!**********************************************!*\
  !*** ./js/src/onboarding-wizard/ui/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupInitialView: () => (/* reexport safe */ _setupInitialView__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   updateRootline: () => (/* reexport safe */ _rootline__WEBPACK_IMPORTED_MODULE_1__.updateRootline)
/* harmony export */ });
/* harmony import */ var _setupInitialView__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setupInitialView */ "./js/src/onboarding-wizard/ui/setupInitialView.js");
/* harmony import */ var _rootline__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./rootline */ "./js/src/onboarding-wizard/ui/rootline.js");



/***/ }),

/***/ "./js/src/onboarding-wizard/ui/rootline.js":
/*!*************************************************!*\
  !*** ./js/src/onboarding-wizard/ui/rootline.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   updateRootline: () => (/* binding */ updateRootline)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


var COMPLETED_STEP_CLASS = 'frm-completed-step';

/**
 * Updates the rootline to reflect the current and completed steps.
 *
 * - Applies COMPLETED_STEP_CLASS to steps before the current one.
 * - Applies CURRENT_CLASS to the current step, unless it is the success step.
 *
 * @param {string} currentStep The current step in the process.
 * @return {void}
 */
function updateRootline(currentStep) {
  if (currentStep === _shared__WEBPACK_IMPORTED_MODULE_2__.STEPS.UNSUCCESSFUL) {
    currentStep = _shared__WEBPACK_IMPORTED_MODULE_2__.STEPS.SUCCESS;
  }
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    rootline = _getElements.rootline;
  var currentItem = rootline.querySelector(".frm-rootline-item[data-step=\"".concat(currentStep, "\"]"));
  rootline.querySelectorAll('.frm-rootline-item').forEach(function (item) {
    item.classList.remove(COMPLETED_STEP_CLASS);
    item.classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS);
  });
  var prevItem = currentItem.previousElementSibling;
  if (prevItem) {
    while (prevItem) {
      prevItem.classList.add(COMPLETED_STEP_CLASS);
      prevItem = prevItem.previousElementSibling; // move to the previous sibling
    }
  }
  if (currentStep === _shared__WEBPACK_IMPORTED_MODULE_2__.STEPS.SUCCESS) {
    currentItem.classList.add(COMPLETED_STEP_CLASS);
  } else {
    currentItem.classList.add(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS);
  }
}

/***/ }),

/***/ "./js/src/onboarding-wizard/ui/setupInitialView.js":
/*!*********************************************************!*\
  !*** ./js/src/onboarding-wizard/ui/setupInitialView.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ setupInitialView)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */




/**
 * Initializes the onboarding wizard's UI, sets up the initial step based on certain conditions,
 * and applies necessary UI enhancements for a smoother user experience.
 *
 * @return {void}
 */
function setupInitialView() {
  navigateToInitialStep();
  fadeInPageElements();
}

/**
 * Determines the initial step in the onboarding process and navigates to it, considering the installation
 * status of Formidable Pro and specific query parameters.
 *
 * @private
 * @return {void}
 */
function navigateToInitialStep() {
  var initialStepName = determineInitialStep();
  clearOnboardingQueryParams();
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.navigateToStep)(initialStepName, 'replaceState');
}

/**
 * Determines the initial step based on the current state, such as whether Formidable Pro is installed
 * and the presence of specific query parameters. Also handles the removal of unnecessary steps.
 *
 * @private
 * @return {string} The name of the initial step to navigate to.
 */
function determineInitialStep() {
  var isConnectedAccount = (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.getQueryParam)('success');
  if (isConnectedAccount === '0') {
    return _shared__WEBPACK_IMPORTED_MODULE_2__.STEPS.UNSUCCESSFUL;
  }
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    hiddenLicenseKeyInput = _getElements.hiddenLicenseKeyInput;
  if (hiddenLicenseKeyInput || isConnectedAccount) {
    return _shared__WEBPACK_IMPORTED_MODULE_2__.STEPS.INSTALL_ADDONS;
  }
  return (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.getQueryParam)('step') || _shared__WEBPACK_IMPORTED_MODULE_2__.STEPS.INITIAL;
}

/**
 * Clears specific query parameters related to the onboarding process.
 *
 * @private
 * @return {void}
 */
function clearOnboardingQueryParams() {
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.removeQueryParam)('key');
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.removeQueryParam)('success');
}

/**
 * Smoothly fades in the background and container elements of the page for a more pleasant user experience.
 *
 * @private
 * @return {void}
 */
function fadeInPageElements() {
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    container = _getElements2.container;
  new core_utils__WEBPACK_IMPORTED_MODULE_0__.frmAnimate(container).fadeIn();
}

/***/ }),

/***/ "./js/src/onboarding-wizard/utils/index.js":
/*!*************************************************!*\
  !*** ./js/src/onboarding-wizard/utils/index.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   navigateToNextStep: () => (/* reexport safe */ _navigateToStep__WEBPACK_IMPORTED_MODULE_0__.navigateToNextStep),
/* harmony export */   navigateToPrevStep: () => (/* reexport safe */ _navigateToStep__WEBPACK_IMPORTED_MODULE_0__.navigateToPrevStep),
/* harmony export */   navigateToStep: () => (/* reexport safe */ _navigateToStep__WEBPACK_IMPORTED_MODULE_0__.navigateToStep)
/* harmony export */ });
/* harmony import */ var _navigateToStep__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./navigateToStep */ "./js/src/onboarding-wizard/utils/navigateToStep.js");


/***/ }),

/***/ "./js/src/onboarding-wizard/utils/navigateToStep.js":
/*!**********************************************************!*\
  !*** ./js/src/onboarding-wizard/utils/navigateToStep.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   navigateToNextStep: () => (/* binding */ navigateToNextStep),
/* harmony export */   navigateToPrevStep: () => (/* binding */ navigateToPrevStep),
/* harmony export */   navigateToStep: () => (/* binding */ navigateToStep)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _dataUtils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../dataUtils */ "./js/src/onboarding-wizard/dataUtils/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../ui */ "./js/src/onboarding-wizard/ui/index.js");
/**
 * External dependencies
 */



/**
 * Internal dependencies
 */





/**
 * Navigates to the given step in the onboarding sequence.
 * Optionally updates the browser's history state to include the current step.
 *
 * @param {string} stepName                   The name of the step to navigate to.
 * @param {string} [updateMethod='pushState'] Specifies the method to update the browser's history and URL. Accepts 'pushState' or 'replaceState'. If omitted, defaults to 'pushState'.
 * @return {void}
 */
var navigateToStep = function navigateToStep(stepName) {
  var updateMethod = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'pushState';
  // Find the target step element
  var targetStep = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_4__.PREFIX, "-step[data-step-name=\"").concat(stepName, "\"]"));
  if (!targetStep) {
    return;
  }

  // Find and hide the current step element
  var currentStep = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_4__.PREFIX, "-step.").concat(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS));
  if (currentStep) {
    currentStep.classList.remove(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS);
    (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hide)(currentStep);
  }

  // Display the target step element
  targetStep.classList.add(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(targetStep);
  new core_utils__WEBPACK_IMPORTED_MODULE_1__.frmAnimate(targetStep).fadeIn();

  // Update the onboarding wizard's current step attribute
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_3__.getElements)(),
    onboardingWizardPage = _getElements.onboardingWizardPage;
  onboardingWizardPage.setAttribute('data-current-step', stepName);

  // Update the URL query parameter, with control over history update method
  (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.setQueryParam)('step', stepName, updateMethod);
  (0,_ui__WEBPACK_IMPORTED_MODULE_5__.updateRootline)(stepName);
};

/**
 * Navigates to the next step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
var navigateToNextStep = function navigateToNextStep() {
  var currentStep = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_4__.PREFIX, "-step.").concat(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS));
  var nextStep = currentStep === null || currentStep === void 0 ? void 0 : currentStep.nextElementSibling;
  if (!nextStep) {
    return;
  }
  var processedStep = currentStep.dataset.stepName;
  var nextStepName = nextStep.dataset.stepName;

  // Save processed steps
  var _getState = (0,_shared__WEBPACK_IMPORTED_MODULE_4__.getState)(),
    processedSteps = _getState.processedSteps;
  if (!processedSteps.includes(processedStep)) {
    processedSteps.push(processedStep);
    (0,_shared__WEBPACK_IMPORTED_MODULE_4__.setSingleState)('processedSteps', processedSteps);
  }
  (0,_dataUtils__WEBPACK_IMPORTED_MODULE_2__.setupUsageData)(processedStep, nextStepName);
  navigateToStep(nextStepName);
};

/**
 * Navigates to the previous step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
var navigateToPrevStep = function navigateToPrevStep() {
  var currentStep = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_4__.PREFIX, "-step.").concat(core_constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS));
  var prevStep = currentStep === null || currentStep === void 0 ? void 0 : currentStep.previousElementSibling;
  if (!prevStep) {
    return;
  }
  navigateToStep(prevStep.dataset.stepName);
};

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
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*******************************************!*\
  !*** ./js/src/onboarding-wizard/index.js ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var _initializeOnboardingWizard__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./initializeOnboardingWizard */ "./js/src/onboarding-wizard/initializeOnboardingWizard.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */

(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__["default"])(function () {
  (0,_initializeOnboardingWizard__WEBPACK_IMPORTED_MODULE_0__["default"])();
});
})();

/******/ })()
;
//# sourceMappingURL=onboarding-wizard.js.map