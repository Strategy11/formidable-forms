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

/***/ "./js/src/common/constants.js":
/*!************************************!*\
  !*** ./js/src/common/constants.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CHECKED_CLASS: () => (/* binding */ CHECKED_CLASS),
/* harmony export */   HIDDEN_CLASS: () => (/* binding */ HIDDEN_CLASS)
/* harmony export */ });
var HIDDEN_CLASS = 'frm_hidden';
var CHECKED_CLASS = 'frm-checked';

/***/ }),

/***/ "./js/src/common/events/index.js":
/*!***************************************!*\
  !*** ./js/src/common/events/index.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addOptionBoxEvents: () => (/* reexport safe */ _optionBoxListener__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _optionBoxListener__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./optionBoxListener */ "./js/src/common/events/optionBoxListener.js");


/***/ }),

/***/ "./js/src/common/events/optionBoxListener.js":
/*!***************************************************!*\
  !*** ./js/src/common/events/optionBoxListener.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../constants */ "./js/src/common/constants.js");
/**
 * Internal dependencies
 */


/**
 * Manages event handling for an option-box.
 *
 * @return {void}
 */
function addOptionBoxEvents() {
  var optionBoxes = document.querySelectorAll('.frm-option-box');

  // Attach click event listeners to each option-boxes.
  optionBoxes.forEach(function (optionBox) {
    optionBox.addEventListener('click', onOptionBoxClick);
  });
}
function onOptionBoxClick(event) {
  if (event.target.tagName.toLowerCase() !== 'input') {
    return;
  }
  var optionBox = event.currentTarget.closest('.frm-option-box');
  optionBox.classList.toggle(_constants__WEBPACK_IMPORTED_MODULE_0__.CHECKED_CLASS);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addOptionBoxEvents);

/***/ }),

/***/ "./js/src/common/utilities/animation.js":
/*!**********************************************!*\
  !*** ./js/src/common/utilities/animation.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmAnimate: () => (/* binding */ frmAnimate)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : String(i); }
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
    this.cssFilePath = 'admin/animations.css';
    this.type = type;
    this.initOnceInAllInstances();
    this.prepareElements();
  }

  /**
   * Init animation - fadeIn.
   * Requires this.type = 'default';
   * ex: new frmAnimate( elements ).fadeIn();
   */
  _createClass(frmAnimate, [{
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
    key: "initOnceInAllInstances",
    value: function initOnceInAllInstances() {
      if (true === frmAnimate.init) {
        return;
      }
      frmAnimate.init = true;
      this.loadCssFile();
    }
  }, {
    key: "getCssFileUrl",
    value: function getCssFileUrl() {
      if (!window.frmGlobal) {
        return '';
      }
      return window.frmGlobal.url + '/css/' + this.cssFilePath;
    }
  }, {
    key: "loadCssFile",
    value: function loadCssFile() {
      var style = document.createElement('link');
      style.href = this.getCssFileUrl();
      style.rel = 'stylesheet';
      document.getElementsByTagName('head')[0].appendChild(style);
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
  return frmAnimate;
}();

/***/ }),

/***/ "./js/src/common/utilities/index.js":
/*!******************************************!*\
  !*** ./js/src/common/utilities/index.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: () => (/* reexport safe */ _requestQueue__WEBPACK_IMPORTED_MODULE_2__.addToRequestQueue),
/* harmony export */   frmAnimate: () => (/* reexport safe */ _animation__WEBPACK_IMPORTED_MODULE_1__.frmAnimate),
/* harmony export */   getQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_5__.getQueryParam),
/* harmony export */   hasQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_5__.hasQueryParam),
/* harmony export */   hide: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.hide),
/* harmony export */   hideElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.hideElements),
/* harmony export */   isHTMLElement: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_3__.isHTMLElement),
/* harmony export */   isValidEmail: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_3__.isValidEmail),
/* harmony export */   isVisible: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.isVisible),
/* harmony export */   onClickPreventDefault: () => (/* binding */ onClickPreventDefault),
/* harmony export */   removeQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_5__.removeQueryParam),
/* harmony export */   setQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_5__.setQueryParam),
/* harmony export */   show: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.show),
/* harmony export */   showElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_0__.showElements),
/* harmony export */   showFormError: () => (/* reexport safe */ _uiUtils__WEBPACK_IMPORTED_MODULE_4__.showFormError)
/* harmony export */ });
/* harmony import */ var _visibility__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./visibility */ "./js/src/common/utilities/visibility.js");
/* harmony import */ var _animation__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./animation */ "./js/src/common/utilities/animation.js");
/* harmony import */ var _requestQueue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./requestQueue */ "./js/src/common/utilities/requestQueue.js");
/* harmony import */ var _validation__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./validation */ "./js/src/common/utilities/validation.js");
/* harmony import */ var _uiUtils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./uiUtils */ "./js/src/common/utilities/uiUtils.js");
/* harmony import */ var _url__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./url */ "./js/src/common/utilities/url.js");
var onClickPreventDefault = window.frmDom.util.onClickPreventDefault;








/***/ }),

/***/ "./js/src/common/utilities/requestQueue.js":
/*!*************************************************!*\
  !*** ./js/src/common/utilities/requestQueue.js ***!
  \*************************************************/
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

/***/ "./js/src/common/utilities/uiUtils.js":
/*!********************************************!*\
  !*** ./js/src/common/utilities/uiUtils.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showFormError: () => (/* binding */ showFormError)
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getQueryParam: () => (/* binding */ getQueryParam),
/* harmony export */   hasQueryParam: () => (/* binding */ hasQueryParam),
/* harmony export */   removeQueryParam: () => (/* binding */ removeQueryParam),
/* harmony export */   setQueryParam: () => (/* binding */ setQueryParam)
/* harmony export */ });
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
 * Sets the value of a query parameter in the current URL and returns the updated URL string.
 *
 * @param {string} paramName The name of the query parameter to set.
 * @param {string} paramValue The value to set for the query parameter.
 * @return {string} The updated URL string.
 */
var setQueryParam = function setQueryParam(paramName, paramValue) {
  urlParams.set(paramName, paramValue);
  url.search = urlParams.toString();
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
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
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
 * @private
 * @param {any} element Element to be checked.
 * @return {boolean} True if it's an HTMLElement, otherwise false.
 */
var isHTMLElement = function isHTMLElement(element) {
  return element instanceof HTMLElement || console.warn('Invalid argument: Element must be an instance of HTMLElement') || false;
};

/***/ }),

/***/ "./js/src/common/utilities/visibility.js":
/*!***********************************************!*\
  !*** ./js/src/common/utilities/visibility.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hide: () => (/* binding */ hide),
/* harmony export */   hideElements: () => (/* binding */ hideElements),
/* harmony export */   isVisible: () => (/* binding */ isVisible),
/* harmony export */   show: () => (/* binding */ show),
/* harmony export */   showElements: () => (/* binding */ showElements)
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

/***/ "./js/src/onboarding-wizard/elements/elements.js":
/*!*******************************************************!*\
  !*** ./js/src/onboarding-wizard/elements/elements.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* binding */ addElements),
/* harmony export */   getElements: () => (/* binding */ getElements),
/* harmony export */   initializeElements: () => (/* binding */ initializeElements)
/* harmony export */ });
/* harmony import */ var _getDOMElements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./getDOMElements */ "./js/src/onboarding-wizard/elements/getDOMElements.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : String(i); }
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
  elements = (0,_getDOMElements__WEBPACK_IMPORTED_MODULE_0__["default"])();
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

/***/ }),

/***/ "./js/src/onboarding-wizard/elements/getDOMElements.js":
/*!*************************************************************!*\
  !*** ./js/src/onboarding-wizard/elements/getDOMElements.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : String(i); }
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
  var bodyElements = {
    onboardingWizardPage: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-wizard-page")),
    pageBackground: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-bg")),
    container: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-container")),
    skipStepButtons: document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-skip-step"))
  };

  // Welcome Step Elements
  var welcomeStep = {
    welcomeStep: document.getElementById(_shared__WEBPACK_IMPORTED_MODULE_0__.WELCOME_STEP_ID),
    proceedWithoutAccountButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-proceed-without-account"))
  };

  // Install Formidable Pro Step Elements
  var installFormidableProStep = {
    installFormidableProStep: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-install-formidable-pro-step")),
    checkProInstallationButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-check-pro-installation-button")),
    checkProInstallationError: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-check-pro-installation-error"))
  };

  // License Management Step Elements
  var licenseManagementStep = {
    licenseManagementStep: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-license-management-step"))
  };

  // Default Email Address Step Elements
  var emailStep = {
    setupEmailStepButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-setup-email-step-button")),
    defaultEmailField: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-default-email-field")),
    subscribeCheckbox: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-subscribe")),
    allowTrackingCheckbox: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-allow-tracking"))
  };

  // Install Formidable Add-ons Step Elements
  var installAddonsStep = {
    installAddonsButton: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-install-addons-button"))
  };

  // Success Step Elements
  var successStep = {
    successStep: document.getElementById("".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-success-step"))
  };
  return _objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread({}, bodyElements), installFormidableProStep), licenseManagementStep), welcomeStep), emailStep), installAddonsStep), successStep);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (getDOMElements);

/***/ }),

/***/ "./js/src/onboarding-wizard/elements/index.js":
/*!****************************************************!*\
  !*** ./js/src/onboarding-wizard/elements/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.addElements),
/* harmony export */   getElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.getElements),
/* harmony export */   initializeElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_0__.initializeElements)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./elements */ "./js/src/onboarding-wizard/elements/elements.js");


/***/ }),

/***/ "./js/src/onboarding-wizard/events/checkProInstallationListener.js":
/*!*************************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/checkProInstallationListener.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/onboarding-wizard/events/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw new Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw new Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
/**
 * Internal dependencies
 */





/**
 * Manages event handling for the "Continue" button in the "Install Formidable Pro" step.
 *
 * @return {void}
 */
function addCheckProInstallationButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    checkProInstallationButton = _getElements.checkProInstallationButton;

  // Attach click event listener
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault)(checkProInstallationButton, onCheckProInstallationButtonClick);
}

/**
 * Handles the click event on the "Continue" button in the "Install Formidable Pro" setup.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onCheckProInstallationButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var formData, response, _yield$response$json, success, _getElements2, checkProInstallationError;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          // Prepare FormData for the POST request
          formData = new FormData();
          formData.append('action', 'frm_check_plugin_activation');
          formData.append('nonce', _shared__WEBPACK_IMPORTED_MODULE_2__.nonce);
          formData.append('plugin_path', 'formidable-pro/formidable-pro.php');
          _context.prev = 4;
          _context.next = 7;
          return fetch(ajaxurl, {
            method: 'POST',
            body: formData
          });
        case 7:
          response = _context.sent;
          _context.next = 10;
          return response.json();
        case 10:
          _yield$response$json = _context.sent;
          success = _yield$response$json.success;
          if (success) {
            (0,___WEBPACK_IMPORTED_MODULE_0__.navigateToNextStep)();
          } else {
            _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), checkProInstallationError = _getElements2.checkProInstallationError;
            (0,_utils__WEBPACK_IMPORTED_MODULE_3__.show)(checkProInstallationError);
          }
          _context.next = 18;
          break;
        case 15:
          _context.prev = 15;
          _context.t0 = _context["catch"](4);
          console.error('An error occurred:', _context.t0);
        case 18:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[4, 15]]);
  }));
  return function onCheckProInstallationButtonClick() {
    return _ref.apply(this, arguments);
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addCheckProInstallationButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/events/index.js":
/*!**************************************************!*\
  !*** ./js/src/onboarding-wizard/events/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addEventListeners: () => (/* binding */ addEventListeners),
/* harmony export */   navigateToNextStep: () => (/* binding */ navigateToNextStep)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
/* harmony import */ var _common_events__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../common/events */ "./js/src/common/events/index.js");
/* harmony import */ var _proceedWithoutAccountButtonListener__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./proceedWithoutAccountButtonListener */ "./js/src/onboarding-wizard/events/proceedWithoutAccountButtonListener.js");
/* harmony import */ var _skipStepButtonListener__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./skipStepButtonListener */ "./js/src/onboarding-wizard/events/skipStepButtonListener.js");
/* harmony import */ var _setupEmailStepButtonListener__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./setupEmailStepButtonListener */ "./js/src/onboarding-wizard/events/setupEmailStepButtonListener.js");
/* harmony import */ var _installAddonsButtonListener__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./installAddonsButtonListener */ "./js/src/onboarding-wizard/events/installAddonsButtonListener.js");
/* harmony import */ var _checkProInstallationListener__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./checkProInstallationListener */ "./js/src/onboarding-wizard/events/checkProInstallationListener.js");
/**
 * Internal dependencies
 */










/**
 * Navigates to the next step in a sequence.
 *
 * Hiding the current step and displaying the next one.
 *
 * @param {Event} event The click event object.
 * @return {void}
 */
var navigateToNextStep = function navigateToNextStep() {
  // Find and hide current step
  var currentStep = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-step.").concat(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS));
  currentStep.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(currentStep);

  // Display next step
  var nextStep = currentStep.nextElementSibling;
  nextStep.classList.add(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.show)(nextStep);
  new _utils__WEBPACK_IMPORTED_MODULE_2__.frmAnimate(nextStep).fadeIn();

  // Update onboarding wizard's current step
  var stepName = nextStep.dataset.stepName;
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    onboardingWizardPage = _getElements.onboardingWizardPage;
  onboardingWizardPage.setAttribute('data-current-step', stepName);
};

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
function addEventListeners() {
  // Add event handling for the "Proceed without Account" button
  (0,_proceedWithoutAccountButtonListener__WEBPACK_IMPORTED_MODULE_4__["default"])();

  // Add event handling for the "Skip" step button
  (0,_skipStepButtonListener__WEBPACK_IMPORTED_MODULE_5__["default"])();

  // Add event handling for the "Next Step" button in the "Default Email Address" step
  (0,_setupEmailStepButtonListener__WEBPACK_IMPORTED_MODULE_6__["default"])();

  // Add event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step
  (0,_installAddonsButtonListener__WEBPACK_IMPORTED_MODULE_7__["default"])();
  // Add event handling for an option-box
  (0,_common_events__WEBPACK_IMPORTED_MODULE_3__.addOptionBoxEvents)();

  // Add event handling for the "Continue" button in the "Install Formidable Pro" step
  (0,_checkProInstallationListener__WEBPACK_IMPORTED_MODULE_8__["default"])();
}

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
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/onboarding-wizard/events/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw new Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw new Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
/**
 * Internal dependencies
 */





/**
 * Manages event handling for the "Install & Finish Setup" button.
 *
 * @return {void}
 */
function addInstallAddonsButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    installAddonsButton = _getElements.installAddonsButton;

  // Attach click event listener
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault)(installAddonsButton, onInstallAddonsButtonClick);
}

/**
 * Handles the click event on the "Install & Finish Setup" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onInstallAddonsButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(event) {
    var addons, installAddonsButton, _iterator, _step, _loop;
    return _regeneratorRuntime().wrap(function _callee$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          addons = document.querySelectorAll('.frm-option-box.frm-checked:not(.frm-disabled)');
          installAddonsButton = event.currentTarget;
          installAddonsButton.classList.add('frm_loading_button');
          _iterator = _createForOfIteratorHelper(addons);
          _context2.prev = 4;
          _loop = /*#__PURE__*/_regeneratorRuntime().mark(function _loop() {
            var addon;
            return _regeneratorRuntime().wrap(function _loop$(_context) {
              while (1) switch (_context.prev = _context.next) {
                case 0:
                  addon = _step.value;
                  _context.prev = 1;
                  _context.next = 4;
                  return (0,_utils__WEBPACK_IMPORTED_MODULE_3__.addToRequestQueue)(function () {
                    return installAddon(addon.getAttribute('rel'), addon.dataset);
                  });
                case 4:
                  _context.next = 9;
                  break;
                case 6:
                  _context.prev = 6;
                  _context.t0 = _context["catch"](1);
                  console.error('An error occurred:', _context.t0);
                case 9:
                case "end":
                  return _context.stop();
              }
            }, _loop, null, [[1, 6]]);
          });
          _iterator.s();
        case 7:
          if ((_step = _iterator.n()).done) {
            _context2.next = 11;
            break;
          }
          return _context2.delegateYield(_loop(), "t0", 9);
        case 9:
          _context2.next = 7;
          break;
        case 11:
          _context2.next = 16;
          break;
        case 13:
          _context2.prev = 13;
          _context2.t1 = _context2["catch"](4);
          _iterator.e(_context2.t1);
        case 16:
          _context2.prev = 16;
          _iterator.f();
          return _context2.finish(16);
        case 19:
          installAddonsButton.classList.remove('frm_loading_button');
          (0,___WEBPACK_IMPORTED_MODULE_0__.navigateToNextStep)();
        case 21:
        case "end":
          return _context2.stop();
      }
    }, _callee, null, [[4, 13, 16, 19]]);
  }));
  return function onInstallAddonsButtonClick(_x) {
    return _ref.apply(this, arguments);
  };
}();

/**
 * Installs an add-on or plugin based on the provided plugin name and vendor status.
 *
 * @private
 * @param {string} plugin The unique identifier or name of the plugin or add-on to be installed.
 * @param {Object} options An object containing additional options for the installation.
 * @param {boolean} options.isVendor Indicates whether the plugin is a vendor plugin (true) or a regular add-on (false).
 * @returns {Promise<any>} A promise that resolves with the JSON response from the server after the installation request is completed.
 */
function installAddon(_x2, _x3) {
  return _installAddon.apply(this, arguments);
}
function _installAddon() {
  _installAddon = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2(plugin, _ref2) {
    var isVendor, formData, response;
    return _regeneratorRuntime().wrap(function _callee2$(_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          isVendor = _ref2.isVendor;
          // Prepare FormData for the POST request
          formData = new FormData();
          formData.append('action', isVendor ? 'frm_install_plugin' : 'frm_install_addon');
          formData.append('nonce', _shared__WEBPACK_IMPORTED_MODULE_2__.nonce);
          formData.append('plugin', plugin);
          _context3.prev = 5;
          _context3.next = 8;
          return fetch(ajaxurl, {
            method: 'POST',
            body: formData
          });
        case 8:
          response = _context3.sent;
          _context3.next = 11;
          return response.json();
        case 11:
          return _context3.abrupt("return", _context3.sent);
        case 14:
          _context3.prev = 14;
          _context3.t0 = _context3["catch"](5);
          console.error('An error occurred:', _context3.t0);
        case 17:
        case "end":
          return _context3.stop();
      }
    }, _callee2, null, [[5, 14]]);
  }));
  return _installAddon.apply(this, arguments);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addInstallAddonsButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/events/proceedWithoutAccountButtonListener.js":
/*!********************************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/proceedWithoutAccountButtonListener.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/onboarding-wizard/events/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
/**
 * Internal dependencies
 */





/**
 * Manages event handling for the "Proceed without Account" button.
 *
 * @return {void}
 */
function addProceedWithoutAccountButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    proceedWithoutAccountButton = _getElements.proceedWithoutAccountButton;

  // Attach click event listeners to each skip buttons
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault)(proceedWithoutAccountButton, onProceedWithoutAccountButtonClick);
}

/**
 * Handles the click event on the "Proceed without Account" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onProceedWithoutAccountButtonClick = function onProceedWithoutAccountButtonClick() {
  // Remove the "License Management" step by clicking on the "Proceed without Account" button
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    licenseManagementStep = _getElements2.licenseManagementStep,
    installFormidableProStep = _getElements2.installFormidableProStep;
  licenseManagementStep.remove();
  installFormidableProStep.remove();

  // Calculate and set the width for each step's progress bar
  var steps = Array.from(document.querySelectorAll(".".concat(_shared__WEBPACK_IMPORTED_MODULE_2__.PREFIX, "-step"))).filter(function (step) {
    return step.id !== _shared__WEBPACK_IMPORTED_MODULE_2__.WELCOME_STEP_ID;
  });
  steps.forEach(function (step, index) {
    // Calculate width percentage based on the current step index (add 1 since index is 0-based) and total steps length
    var widthPercentage = (index + 1) / steps.length * 100;

    // Find the progress bar within the current step and set its width
    var progressBar = step.querySelector('.frm-card-box-progress-bar > span');
    if (progressBar) {
      progressBar.style.width = "".concat(widthPercentage, "%");
    }
  });
  (0,___WEBPACK_IMPORTED_MODULE_0__.navigateToNextStep)();
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addProceedWithoutAccountButtonEvents);

/***/ }),

/***/ "./js/src/onboarding-wizard/events/setupEmailStepButtonListener.js":
/*!*************************************************************************!*\
  !*** ./js/src/onboarding-wizard/events/setupEmailStepButtonListener.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/onboarding-wizard/events/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui */ "./js/src/onboarding-wizard/ui/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw new Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw new Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
/**
 * Internal dependencies
 */





/**
 * Manages event handling for the "Next Step" button in the "Default Email Address" step.
 *
 * @return {void}
 */
function addSetupEmailStepButtonEvents() {
  var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(),
    setupEmailStepButton = _getElements.setupEmailStepButton;

  // Attach click event listener
  (0,_utils__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault)(setupEmailStepButton, onSetupEmailStepButtonClick);
}

/**
 * Handles the click event on the "Next Step" button during email setup.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onSetupEmailStepButtonClick = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
    var _getElements2, defaultEmailField, email, _getElements3, subscribeCheckbox, allowTrackingCheckbox, emailInput, formData, doJsonPost;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), defaultEmailField = _getElements2.defaultEmailField;
          email = defaultEmailField.value.trim(); // Check if the email is valid
          if ((0,_utils__WEBPACK_IMPORTED_MODULE_3__.isValidEmail)(email)) {
            _context.next = 5;
            break;
          }
          (0,_ui__WEBPACK_IMPORTED_MODULE_2__.showEmailAddressError)('invalid');
          return _context.abrupt("return");
        case 5:
          _getElements3 = (0,_elements__WEBPACK_IMPORTED_MODULE_1__.getElements)(), subscribeCheckbox = _getElements3.subscribeCheckbox, allowTrackingCheckbox = _getElements3.allowTrackingCheckbox; // Check if the 'subscribe' checkbox is selected. If so, proceed to add the user's email to the active campaign
          if (subscribeCheckbox.checked) {
            // Assign default email to 'leave email' input if provided; otherwise, use administrator's email
            if (email) {
              emailInput = document.getElementById('frm_leave_email');
              emailInput.value = email;
            }
            frmAdminBuild.addMyEmailAddress();
          }

          // Prepare FormData for the POST request
          formData = new FormData();
          formData.append('default_email', email);
          formData.append('is_tracking_allowed', allowTrackingCheckbox.checked);

          // Send the POST request
          doJsonPost = frmDom.ajax.doJsonPost;
          doJsonPost('onboarding_setup_email_step', formData).then(function () {
            (0,___WEBPACK_IMPORTED_MODULE_0__.navigateToNextStep)();
          });
        case 12:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function onSetupEmailStepButtonClick() {
    return _ref.apply(this, arguments);
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addSetupEmailStepButtonEvents);

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
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./js/src/onboarding-wizard/events/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
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
    (0,_utils__WEBPACK_IMPORTED_MODULE_2__.onClickPreventDefault)(skipButton, onSkipStepButtonClick);
  });
}

/**
 * Handles the click event on a "Skip" step button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
var onSkipStepButtonClick = function onSkipStepButtonClick() {
  (0,___WEBPACK_IMPORTED_MODULE_0__.navigateToNextStep)();
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
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./events */ "./js/src/onboarding-wizard/events/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ui */ "./js/src/onboarding-wizard/ui/index.js");
/**
 * Internal dependencies
 */





/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
  // Initializes essential DOM elements
  (0,_elements__WEBPACK_IMPORTED_MODULE_1__.initializeElements)();
  (0,_shared__WEBPACK_IMPORTED_MODULE_0__.initializeAppState)();

  // Set up the initial view, including any required DOM manipulations for proper presentation
  (0,_ui__WEBPACK_IMPORTED_MODULE_3__.setupInitialView)();
  (0,_events__WEBPACK_IMPORTED_MODULE_2__.addEventListeners)();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (initializeOnboardingWizard);

/***/ }),

/***/ "./js/src/onboarding-wizard/shared/appState.js":
/*!*****************************************************!*\
  !*** ./js/src/onboarding-wizard/shared/appState.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getAppState: () => (/* binding */ getAppState),
/* harmony export */   getAppStateProperty: () => (/* binding */ getAppStateProperty),
/* harmony export */   initializeAppState: () => (/* binding */ initializeAppState),
/* harmony export */   setAppState: () => (/* binding */ setAppState),
/* harmony export */   setAppStateProperty: () => (/* binding */ setAppStateProperty)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : String(i); }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var appState = null;

/**
 * Initialize the application state.
 *
 * @return {void}
*/
var initializeAppState = function initializeAppState() {
  appState = {};
};

/**
 * Returns the current application state.
 *
 * @return {Object} The current state of the application.
 */
var getAppState = function getAppState() {
  return appState;
};

/**
 * Updates the application state with new values.
 *
 * @param {Object} newState The new values to update the state.
 * @return {void}
 */
var setAppState = function setAppState(newState) {
  appState = _objectSpread(_objectSpread({}, appState), newState);
};

/**
 * Returns a specific property from the current application state.
 *
 * @param {string} propertyName The property name to retrieve from the state.
 * @return {*} The value of the specified property, or null if it doesn't exist.
 */
var getAppStateProperty = function getAppStateProperty(propertyName) {
  var _Reflect$get;
  return (_Reflect$get = Reflect.get(appState, propertyName)) !== null && _Reflect$get !== void 0 ? _Reflect$get : null;
};

/**
 * Updates a specific property in the application state with a new value.
 *
 * @param {string} propertyName The property name to update.
 * @param {*} value The new value to set.
 * @return {void}
 */
var setAppStateProperty = function setAppStateProperty(propertyName, value) {
  if (Reflect.has(appState, propertyName)) {
    Reflect.set(appState, propertyName, value);
  }
};

/***/ }),

/***/ "./js/src/onboarding-wizard/shared/constants.js":
/*!******************************************************!*\
  !*** ./js/src/onboarding-wizard/shared/constants.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CURRENT_CLASS: () => (/* binding */ CURRENT_CLASS),
/* harmony export */   HIDDEN_CLASS: () => (/* binding */ HIDDEN_CLASS),
/* harmony export */   HIDE_JS_CLASS: () => (/* binding */ HIDE_JS_CLASS),
/* harmony export */   PREFIX: () => (/* binding */ PREFIX),
/* harmony export */   WELCOME_STEP_ID: () => (/* binding */ WELCOME_STEP_ID),
/* harmony export */   nonce: () => (/* binding */ nonce)
/* harmony export */ });
var nonce = window.frmGlobal.nonce;

var PREFIX = 'frm-onboarding';
var HIDDEN_CLASS = 'frm_hidden';
var HIDE_JS_CLASS = 'frm-hide-js';
var CURRENT_CLASS = 'frm-current';
var WELCOME_STEP_ID = "".concat(PREFIX, "-welcome-step");

/***/ }),

/***/ "./js/src/onboarding-wizard/shared/index.js":
/*!**************************************************!*\
  !*** ./js/src/onboarding-wizard/shared/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CURRENT_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS),
/* harmony export */   HIDDEN_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS),
/* harmony export */   HIDE_JS_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.HIDE_JS_CLASS),
/* harmony export */   PREFIX: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PREFIX),
/* harmony export */   WELCOME_STEP_ID: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.WELCOME_STEP_ID),
/* harmony export */   getAppState: () => (/* reexport safe */ _appState__WEBPACK_IMPORTED_MODULE_1__.getAppState),
/* harmony export */   getAppStateProperty: () => (/* reexport safe */ _appState__WEBPACK_IMPORTED_MODULE_1__.getAppStateProperty),
/* harmony export */   initializeAppState: () => (/* reexport safe */ _appState__WEBPACK_IMPORTED_MODULE_1__.initializeAppState),
/* harmony export */   nonce: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.nonce),
/* harmony export */   setAppState: () => (/* reexport safe */ _appState__WEBPACK_IMPORTED_MODULE_1__.setAppState),
/* harmony export */   setAppStateProperty: () => (/* reexport safe */ _appState__WEBPACK_IMPORTED_MODULE_1__.setAppStateProperty)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/onboarding-wizard/shared/constants.js");
/* harmony import */ var _appState__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./appState */ "./js/src/onboarding-wizard/shared/appState.js");



/***/ }),

/***/ "./js/src/onboarding-wizard/ui/index.js":
/*!**********************************************!*\
  !*** ./js/src/onboarding-wizard/ui/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupInitialView: () => (/* reexport safe */ _setupInitialView__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   showEmailAddressError: () => (/* reexport safe */ _showError__WEBPACK_IMPORTED_MODULE_1__.showEmailAddressError)
/* harmony export */ });
/* harmony import */ var _setupInitialView__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setupInitialView */ "./js/src/onboarding-wizard/ui/setupInitialView.js");
/* harmony import */ var _showError__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./showError */ "./js/src/onboarding-wizard/ui/showError.js");



/***/ }),

/***/ "./js/src/onboarding-wizard/ui/setupInitialView.js":
/*!*********************************************************!*\
  !*** ./js/src/onboarding-wizard/ui/setupInitialView.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./js/src/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
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
  // Display a specific step based on the 'step' query parameter, if it exists
  if ((0,_utils__WEBPACK_IMPORTED_MODULE_2__.hasQueryParam)('step')) {
    var stepElement = document.querySelector(".".concat(_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX, "-step[data-step-name=\"").concat((0,_utils__WEBPACK_IMPORTED_MODULE_2__.getQueryParam)('step'), "\"]"));
    // Proceed only if the step element is found
    if (stepElement) {
      var _getElements = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
        welcomeStep = _getElements.welcomeStep,
        onboardingWizardPage = _getElements.onboardingWizardPage;

      // Transition from the "Welcome" step to the targeted step
      welcomeStep.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
      (0,_utils__WEBPACK_IMPORTED_MODULE_2__.hide)(welcomeStep);
      stepElement.classList.add(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
      (0,_utils__WEBPACK_IMPORTED_MODULE_2__.show)(stepElement);

      // Update the onboarding wizard's current step attribute
      onboardingWizardPage.setAttribute('data-current-step', (0,_utils__WEBPACK_IMPORTED_MODULE_2__.getQueryParam)('step'));
    }
  }

  // Smoothly display the page
  var _getElements2 = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)(),
    pageBackground = _getElements2.pageBackground,
    container = _getElements2.container;
  new _utils__WEBPACK_IMPORTED_MODULE_2__.frmAnimate(pageBackground).fadeIn();
  new _utils__WEBPACK_IMPORTED_MODULE_2__.frmAnimate(container).fadeIn();

  /**
   * Initializes the "Default Email Address" step in the Onboarding Wizard.
   * This function injects the API email form into the 'frmapi-email-form' element within 'leave-email-modal.php'.
   * It utilizes 'FrmAppController::api_email_form' from 'default-email-step.php' to facilitate this injection.
   */
  frmAdminBuild.showActiveCampaignForm();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (setupInitialView);

/***/ }),

/***/ "./js/src/onboarding-wizard/ui/showError.js":
/*!**************************************************!*\
  !*** ./js/src/onboarding-wizard/ui/showError.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showEmailAddressError: () => (/* binding */ showEmailAddressError)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils */ "./js/src/onboarding-wizard/utils/index.js");
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
  (0,_utils__WEBPACK_IMPORTED_MODULE_1__.showFormError)("#".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-default-email-field"), "#".concat(_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX, "-email-step-error"), type);
};

/***/ }),

/***/ "./js/src/onboarding-wizard/utils/index.js":
/*!*************************************************!*\
  !*** ./js/src/onboarding-wizard/utils/index.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.addToRequestQueue),
/* harmony export */   frmAnimate: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.frmAnimate),
/* harmony export */   getQueryParam: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.getQueryParam),
/* harmony export */   hasQueryParam: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.hasQueryParam),
/* harmony export */   hide: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.hide),
/* harmony export */   hideElements: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.hideElements),
/* harmony export */   isHTMLElement: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isHTMLElement),
/* harmony export */   isValidEmail: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isValidEmail),
/* harmony export */   isVisible: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.isVisible),
/* harmony export */   onClickPreventDefault: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault),
/* harmony export */   removeQueryParam: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.removeQueryParam),
/* harmony export */   setQueryParam: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.setQueryParam),
/* harmony export */   show: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.show),
/* harmony export */   showElements: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.showElements),
/* harmony export */   showFormError: () => (/* reexport safe */ _common_utilities__WEBPACK_IMPORTED_MODULE_0__.showFormError)
/* harmony export */ });
/* harmony import */ var _common_utilities__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../common/utilities */ "./js/src/common/utilities/index.js");


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
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*******************************************!*\
  !*** ./js/src/onboarding-wizard/index.js ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shared */ "./js/src/onboarding-wizard/shared/index.js");
/* harmony import */ var _initializeOnboardingWizard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./initializeOnboardingWizard */ "./js/src/onboarding-wizard/initializeOnboardingWizard.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__["default"])(function () {
  /**
   * Entry point for pre-initialization adjustments to the application state.
   *
   * @param {Object} appState Current state of the application.
   */
  wp.hooks.doAction('frmOnboardingWizard.beforeInitialize', {
    getAppState: _shared__WEBPACK_IMPORTED_MODULE_0__.getAppState,
    setAppState: _shared__WEBPACK_IMPORTED_MODULE_0__.setAppState
  });

  // Initialize the Onboarding Wizard
  (0,_initializeOnboardingWizard__WEBPACK_IMPORTED_MODULE_1__["default"])();

  /**
   * Entry point for post-initialization custom logic or adjustments to the application state.
   *
   * @param {Object} appState Current state of the application.
   */
  wp.hooks.doAction('frmOnboardingWizard.afterInitialize', {
    getAppState: _shared__WEBPACK_IMPORTED_MODULE_0__.getAppState,
    setAppState: _shared__WEBPACK_IMPORTED_MODULE_0__.setAppState
  });
});
})();

/******/ })()
;
//# sourceMappingURL=onboarding-wizard.js.map