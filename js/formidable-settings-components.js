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

/***/ "./js/src/admin/components/dependent-updater-component.js":
/*!****************************************************************!*\
  !*** ./js/src/admin/components/dependent-updater-component.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
        if (null !== input) {
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
      this.initDefaultSlideTrackerWidth();
      this.navs.forEach(function (nav, index) {
        nav.addEventListener('click', function (event) {
          return _this.onNavClick(event, index);
        });
      });
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
      this.initSlideTrackUnderline(navItem, index);
      this.changeSlide(index);

      // Handle special case for frm_insert_fields_tab
      var navLink = navItem.querySelector('a');
      if (navLink && navLink.id === 'frm_insert_fields_tab' && !navLink.closest('#frm_adv_info')) {
        var _window$frmAdminBuild, _window$frmAdminBuild2;
        (_window$frmAdminBuild = window.frmAdminBuild) === null || _window$frmAdminBuild === void 0 || (_window$frmAdminBuild2 = _window$frmAdminBuild.clearSettingsBox) === null || _window$frmAdminBuild2 === void 0 || _window$frmAdminBuild2.call(_window$frmAdminBuild);
      }
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
      var scrollbarWrapper = this.wrapper.closest('.frm-scrollbar-wrapper');
      if (!scrollbarWrapper || !('ResizeObserver' in window)) {
        return;
      }
      this.resizeObserver = new ResizeObserver(function () {
        var activeNav = _this2.wrapper.querySelector('.frm-tabs-navs ul > li.frm-active');
        if (activeNav) {
          _this2.positionUnderlineIndicator(activeNav);
        }
      });
      this.resizeObserver.observe(scrollbarWrapper);
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
        _this3.slideTrackLine.style.width = activeNav.clientWidth + 'px';
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

/***/ "./js/src/core/utils/event.js":
/*!************************************!*\
  !*** ./js/src/core/utils/event.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   documentOn: () => (/* binding */ documentOn),
/* harmony export */   onClickPreventDefault: () => (/* binding */ onClickPreventDefault)
/* harmony export */ });
var _window$frmDom$util = window.frmDom.util,
  onClickPreventDefault = _window$frmDom$util.onClickPreventDefault,
  documentOn = _window$frmDom$util.documentOn;


/***/ }),

/***/ "./js/src/core/utils/index.js":
/*!************************************!*\
  !*** ./js/src/core/utils/index.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToRequestQueue: () => (/* reexport safe */ _async__WEBPACK_IMPORTED_MODULE_1__.addToRequestQueue),
/* harmony export */   documentOn: () => (/* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_3__.documentOn),
/* harmony export */   frmAnimate: () => (/* reexport safe */ _animation__WEBPACK_IMPORTED_MODULE_0__.frmAnimate),
/* harmony export */   getQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.getQueryParam),
/* harmony export */   hasQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.hasQueryParam),
/* harmony export */   hide: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.hide),
/* harmony export */   hideElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.hideElements),
/* harmony export */   isEmptyObject: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isEmptyObject),
/* harmony export */   isHTMLElement: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isHTMLElement),
/* harmony export */   isValidEmail: () => (/* reexport safe */ _validation__WEBPACK_IMPORTED_MODULE_5__.isValidEmail),
/* harmony export */   isVisible: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.isVisible),
/* harmony export */   onClickPreventDefault: () => (/* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_3__.onClickPreventDefault),
/* harmony export */   removeParamFromHistory: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.removeParamFromHistory),
/* harmony export */   removeQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.removeQueryParam),
/* harmony export */   setQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_4__.setQueryParam),
/* harmony export */   show: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.show),
/* harmony export */   showElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_6__.showElements),
/* harmony export */   showFormError: () => (/* reexport safe */ _error__WEBPACK_IMPORTED_MODULE_2__.showFormError)
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

/***/ "./js/src/settings-components/components/index.js":
/*!********************************************************!*\
  !*** ./js/src/settings-components/components/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmRadioComponent: () => (/* reexport safe */ _radio_component__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   frmSliderComponent: () => (/* reexport safe */ _slider_component__WEBPACK_IMPORTED_MODULE_1__["default"]),
/* harmony export */   frmTabsComponent: () => (/* reexport safe */ _tabs_component__WEBPACK_IMPORTED_MODULE_2__["default"]),
/* harmony export */   initToggleGroupComponents: () => (/* reexport safe */ _toggle_group__WEBPACK_IMPORTED_MODULE_4__.initToggleGroupComponents),
/* harmony export */   initTokenInputFields: () => (/* reexport safe */ _token_input__WEBPACK_IMPORTED_MODULE_3__.initTokenInputFields),
/* harmony export */   setupUnitInputHandlers: () => (/* reexport safe */ _unit_input__WEBPACK_IMPORTED_MODULE_5__.setupUnitInputHandlers)
/* harmony export */ });
/* harmony import */ var _radio_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./radio-component */ "./js/src/settings-components/components/radio-component.js");
/* harmony import */ var _slider_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./slider-component */ "./js/src/settings-components/components/slider-component.js");
/* harmony import */ var _tabs_component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./tabs-component */ "./js/src/settings-components/components/tabs-component.js");
/* harmony import */ var _token_input__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./token-input */ "./js/src/settings-components/components/token-input/index.js");
/* harmony import */ var _toggle_group__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./toggle-group */ "./js/src/settings-components/components/toggle-group/index.js");
/* harmony import */ var _unit_input__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./unit-input */ "./js/src/settings-components/components/unit-input.js");







/***/ }),

/***/ "./js/src/settings-components/components/radio-component.js":
/*!******************************************************************!*\
  !*** ./js/src/settings-components/components/radio-component.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ frmRadioComponent)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
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
 * Represents a radio component.
 *
 * @class
 */
var frmRadioComponent = /*#__PURE__*/function () {
  function frmRadioComponent() {
    var _this = this;
    _classCallCheck(this, frmRadioComponent);
    this.radioElements = document.querySelectorAll('.frm-style-component.frm-radio-component');
    this.observers = new Map();
    if (0 < this.radioElements.length) {
      this.init();
    }

    /**
     * Handles the addition of new fields.
     *
     * @param {Event}       event          The frm_added_field event.
     * @param {HTMLElement} event.frmField The added field object being destructured from the event.
     */
    document.addEventListener('frm_added_field', function (_ref) {
      var frmField = _ref.frmField;
      return _this.discoverAndInitFieldRadios(frmField.dataset.fid);
    });

    /**
     * Handles the addition of new fields via AJAX.
     *
     * @param {Event}       event           The frm_ajax_loaded_field event.
     * @param {HTMLElement} event.frmFields The added field objects being destructured from the event.
     */
    document.addEventListener('frm_ajax_loaded_field', function (_ref2) {
      var frmFields = _ref2.frmFields;
      return frmFields.forEach(function (field) {
        return _this.discoverAndInitFieldRadios(field.id);
      });
    });

    // Cleanup observers when page unloads to prevent memory leaks
    window.addEventListener('beforeunload', function () {
      return _this.cleanupObservers();
    });
  }

  /**
   * Initializes the radio component.
   */
  return _createClass(frmRadioComponent, [{
    key: "init",
    value: function init() {
      this.initRadio();
      this.initTrackerOnAccordionClick();
    }

    /**
     * Discovers and initializes radio components for a specific field.
     *
     * @param {string|number} fieldId The unique identifier of the field whose radio components should be discovered and initialized
     * @throws {Error} Throws an error if the field container is not found in the DOM
     */
  }, {
    key: "discoverAndInitFieldRadios",
    value: function discoverAndInitFieldRadios(fieldId) {
      var fieldContainer = document.getElementById("frm-single-settings-".concat(fieldId));
      if (!fieldContainer) {
        throw new Error("Field container not found for field ID: ".concat(fieldId));
      }
      this.radioElements = fieldContainer.querySelectorAll('.frm-style-component.frm-radio-component');
      this.initRadio();
    }

    /**
     * Initializes the radio component.
     */
  }, {
    key: "initRadio",
    value: function initRadio() {
      var _this2 = this;
      this.radioElements.forEach(function (element) {
        _this2.initOnRadioChange(element);
        _this2.initVisibilityObserver(element);
      });
    }
  }, {
    key: "initTrackerOnAccordionClick",
    value: function initTrackerOnAccordionClick() {
      var _this3 = this;
      var accordionitems = document.querySelectorAll('#frm_style_sidebar .accordion-section h3');
      accordionitems.forEach(function (accordionitem) {
        accordionitem.addEventListener('click', function (event) {
          var wrapper = event.target.closest('.accordion-section');
          var radioButtons = wrapper.querySelectorAll('.frm-style-component.frm-radio-component input[type="radio"]:checked');
          radioButtons.forEach(function (radio) {
            setTimeout(function () {
              return _this3.onRadioChange(radio);
            }, 200);
          });
        });
      });
    }

    /**
     * Initializes the onRadioChange event for the given wrapper.
     *
     * @param {HTMLElement} radioElement - The radio element.
     */
  }, {
    key: "initOnRadioChange",
    value: function initOnRadioChange(radioElement) {
      var _this4 = this;
      radioElement.querySelectorAll('input[type="radio"]').forEach(function (radio) {
        if (radio.checked) {
          _this4.onRadioChange(radio);
        }
        radio.addEventListener('change', function (event) {
          _this4.onRadioChange(event.target);
        });
      });
    }

    /**
     * Handles the onRadioChange event for the given wrapper.
     *
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
     *
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
        (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.show)(element);
        element.classList.add('frm-element-is-visible');
      });
    }

    /**
     * Initializes visibility observer for the radio component. This handles cases when components are conditionally shown.
     *
     * @param {HTMLElement} element The radio component element
     * @return {void}
     */
  }, {
    key: "initVisibilityObserver",
    value: function initVisibilityObserver(element) {
      var _this5 = this;
      if (this.observers.has(element)) {
        this.observers.get(element).disconnect();
      }
      var observer = new MutationObserver(function () {
        // Check if element is now visible
        if ((0,core_utils__WEBPACK_IMPORTED_MODULE_1__.isVisible)(element)) {
          var radio = element.querySelector('input[type="radio"]:checked');
          if (radio) {
            _this5.onRadioChange(radio);
          }
        }
      });
      this.observers.set(element, observer);

      // Observe for attribute changes on the component and its ancestors
      observer.observe(element, {
        attributes: true,
        attributeFilter: ['class', 'style']
      });

      // Also observe parent elements up to a reasonable depth
      var parent = element.parentElement;
      for (var i = 0; i < 7 && parent; i++) {
        observer.observe(parent, {
          attributes: true,
          attributeFilter: ['class', 'style']
        });
        parent = parent.parentElement;
      }
    }

    /**
     * Cleanup all observers to prevent memory leaks.
     */
  }, {
    key: "cleanupObservers",
    value: function cleanupObservers() {
      this.observers.forEach(function (observer) {
        observer.disconnect();
      });
      this.observers.clear();
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
        element.classList.add(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
        (0,core_utils__WEBPACK_IMPORTED_MODULE_1__.hide)(element);
      });
    }

    /**
     * Moves the tracker to the active item.
     *
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

/***/ "./js/src/settings-components/components/slider-component.js":
/*!*******************************************************************!*\
  !*** ./js/src/settings-components/components/slider-component.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
    _classCallCheck(this, frmSliderComponent);
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
   * Initializes the options for the slider component.
   */
  return _createClass(frmSliderComponent, [{
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
      this.initSlidersPosition();
      this.initDraggable();
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
     * Initializes the position of sliders when a accordion section is opened.
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

/***/ "./js/src/settings-components/components/tabs-component.js":
/*!*****************************************************************!*\
  !*** ./js/src/settings-components/components/tabs-component.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ frmTabsComponent)
/* harmony export */ });
/* harmony import */ var _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../components/class-tabs-navigator */ "./js/src/components/class-tabs-navigator.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

/**
 * Represents a Tabs Component.
 *
 * @class
 */
var frmTabsComponent = /*#__PURE__*/function () {
  function frmTabsComponent() {
    _classCallCheck(this, frmTabsComponent);
    this.elements = document.querySelectorAll('.frm-style-tabs-wrapper');
    if (0 < this.elements.length) {
      this.init();
    }
  }

  /**
   * Initializes the Tabs Component.
   */
  return _createClass(frmTabsComponent, [{
    key: "init",
    value: function init() {
      this.elements.forEach(function (element) {
        new _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__.frmTabsNavigator(element);
      });
    }

    /**
     * Initializes the component on tab click.
     *
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

/***/ "./js/src/settings-components/components/toggle-group/index.js":
/*!*********************************************************************!*\
  !*** ./js/src/settings-components/components/toggle-group/index.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initToggleGroupComponents: () => (/* reexport safe */ _toggle_group_js__WEBPACK_IMPORTED_MODULE_0__.initToggleGroupComponents)
/* harmony export */ });
/* harmony import */ var _toggle_group_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toggle-group.js */ "./js/src/settings-components/components/toggle-group/toggle-group.js");


/***/ }),

/***/ "./js/src/settings-components/components/toggle-group/toggle-group.js":
/*!****************************************************************************!*\
  !*** ./js/src/settings-components/components/toggle-group/toggle-group.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initToggleGroupComponents: () => (/* binding */ initToggleGroupComponents)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/**
 * Group Toggle Component
 *
 * Handles toggling visibility and enabled state of related form elements
 */

/**
 * Internal dependencies
 */



/**
 * Class names for group toggle component
 *
 * @private
 */
var CLASS_NAMES = {
  GROUP_TOGGLE: 'frm-toggle-group',
  TOGGLE_BLOCK: 'frm_toggle_block'
};

/**
 * Data attributes for group toggle component
 *
 * @private
 */
var DATA_ATTRIBUTES = {
  GROUP_NAME: 'data-group-name',
  SHOW: 'data-show',
  DISABLE: 'data-disable',
  ENABLE: 'data-enable'
};

/**
 * Initialize all group toggle components on the page
 *
 * @return {void}
 */
function initToggleGroupComponents() {
  applyInitialState();
  addEventListeners();
}

/**
 * Apply the initial state for all toggle buttons on the page
 *
 * @private
 * @return {void}
 */
function applyInitialState() {
  var toggleGroups = document.querySelectorAll(".".concat(CLASS_NAMES.GROUP_TOGGLE));
  if (!toggleGroups.length) {
    return;
  }
  toggleGroups.forEach(function (toggleGroup) {
    var toggleButton = toggleGroup.querySelector("[".concat(DATA_ATTRIBUTES.GROUP_NAME, "]:checked"));
    if (!toggleButton) {
      return;
    }
    applyToggleState(toggleButton, toggleGroup);
  });
}

/**
 * Add event listeners to toggle buttons in a group toggle component
 *
 * @private
 * @return {void}
 */
function addEventListeners() {
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.documentOn)('change', ".".concat(CLASS_NAMES.GROUP_TOGGLE, " [").concat(DATA_ATTRIBUTES.GROUP_NAME, "]"), handleToggleClick);
}

/**
 * Handle click events on toggle buttons
 *
 * @private
 * @param {Event} event The click event
 * @return {void}
 */
function handleToggleClick(event) {
  var toggleButton = event.target;
  var toggleGroup = toggleButton.closest(".".concat(CLASS_NAMES.GROUP_TOGGLE));
  if (!toggleGroup) {
    return;
  }
  applyToggleState(toggleButton, toggleGroup);
}

/**
 * Apply toggle state based on toggle button settings
 * Shared functionality used by both click handler and initial state
 *
 * @private
 * @param {HTMLElement} toggleButton The toggle button element
 * @param {HTMLElement} toggleGroup  The toggle group container element
 * @return {void}
 */
function applyToggleState(toggleButton, toggleGroup) {
  var _toggleGroup$closest;
  var fieldId = ((_toggleGroup$closest = toggleGroup.closest(".".concat(core_constants__WEBPACK_IMPORTED_MODULE_1__.SINGLE_SETTINGS_CLASS))) === null || _toggleGroup$closest === void 0 ? void 0 : _toggleGroup$closest.dataset.fid) || toggleGroup.dataset.fid;
  var isChecked = toggleButton.checked;

  // Handle show/hide elements
  var showSelectors = toggleButton.getAttribute(DATA_ATTRIBUTES.SHOW);
  if (showSelectors) {
    document.querySelectorAll(normalizeSelector(showSelectors, fieldId)).forEach(function (element) {
      return element.classList.toggle(core_constants__WEBPACK_IMPORTED_MODULE_1__.HIDDEN_CLASS, !isChecked);
    });
  }

  // Handle disable elements
  var disableSelectors = toggleButton.getAttribute(DATA_ATTRIBUTES.DISABLE);
  if (disableSelectors) {
    document.querySelectorAll(normalizeSelector(disableSelectors, fieldId)).forEach(function (element) {
      element.classList.toggle(core_constants__WEBPACK_IMPORTED_MODULE_1__.DISABLED_CLASS, isChecked);
      element.querySelectorAll('input, select, textarea').forEach(function (formElement) {
        return formElement.disabled = isChecked;
      });
      element.querySelectorAll('.frm-show-inline-modal[tabindex]').forEach(function (inlineModal) {
        return inlineModal.tabIndex = isChecked ? -1 : 0;
      });
    });
  }

  // Handle enable elements
  var enableSelectors = toggleButton.getAttribute(DATA_ATTRIBUTES.ENABLE);
  if (enableSelectors) {
    document.querySelectorAll(normalizeSelector(enableSelectors, fieldId)).forEach(function (element) {
      return element.classList.toggle(core_constants__WEBPACK_IMPORTED_MODULE_1__.DISABLED_CLASS, !isChecked);
    });
  }

  // Toggle disabled state for all other toggle blocks within the group
  var currentToggleBlock = toggleButton.closest(".".concat(CLASS_NAMES.TOGGLE_BLOCK));
  Array.from(toggleGroup.querySelectorAll(".".concat(CLASS_NAMES.TOGGLE_BLOCK))).filter(function (toggleBlock) {
    return toggleBlock !== currentToggleBlock;
  }).forEach(function (toggleBlock) {
    toggleBlock.classList.toggle(core_constants__WEBPACK_IMPORTED_MODULE_1__.DISABLED_CLASS, isChecked);

    // Disable toggle switch
    var toggle = toggleBlock.querySelector('.frm_toggle');
    toggle.tabIndex = isChecked ? -1 : 0;
    toggle.setAttribute('aria-disabled', isChecked);
  });
}

/**
 * Normalize a selector by replacing {id} placeholders with the actual field ID
 *
 * @private
 * @param {string} selector The selector string with potential {id} placeholders
 * @param {string} fieldId  The field ID to replace placeholders with
 * @return {string} The normalized selector
 */
function normalizeSelector(selector, fieldId) {
  return selector.replace(/{id}/g, fieldId);
}


/***/ }),

/***/ "./js/src/settings-components/components/token-input/constants.js":
/*!************************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/constants.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CLASS_NAMES: () => (/* binding */ CLASS_NAMES),
/* harmony export */   KEYS: () => (/* binding */ KEYS),
/* harmony export */   PROXY_INPUT_HEIGHT: () => (/* binding */ PROXY_INPUT_HEIGHT),
/* harmony export */   TOKEN_GAP: () => (/* binding */ TOKEN_GAP)
/* harmony export */ });
/**
 * Constants for token input component
 *
 * Reusable constants for class names and other static values
 */

var CLASS_NAMES = {
  CONTAINER: 'frm-token-container',
  TOKENS_WRAPPER: 'frm-tokens',
  TOKEN: 'frm-token',
  TOKEN_VALUE: 'frm-token-value',
  TOKEN_REMOVE: 'frm-token-remove',
  TOKEN_INPUT_FIELD: 'frm-token-input-field',
  TOKEN_PROXY_INPUT: 'frm-token-proxy-input',
  WITH_RIGHT_ICON: 'frm-with-right-icon'
};
var KEYS = {
  SPACE: ' ',
  ENTER: 'Enter',
  COMMA: ',',
  TAB: 'Tab',
  BACKSPACE: 'Backspace'
};
var PROXY_INPUT_HEIGHT = 36;
var TOKEN_GAP = 4;

/***/ }),

/***/ "./js/src/settings-components/components/token-input/event-handlers.js":
/*!*****************************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/event-handlers.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addEventListeners: () => (/* binding */ addEventListeners)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/settings-components/components/token-input/constants.js");
/* harmony import */ var _token_actions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./token-actions */ "./js/src/settings-components/components/token-input/token-actions.js");
/* harmony import */ var _proxy_input_style__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./proxy-input-style */ "./js/src/settings-components/components/token-input/proxy-input-style.js");
/**
 * Event handlers
 *
 * Functions for handling token input events
 */

/**
 * Internal dependencies
 */




/**
 * Add event listeners to token input components
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} proxyInput    The proxy input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function addEventListeners(field, proxyInput, tokensWrapper) {
  // The jQuery change event is required to catch programmatic updates, as "Add Layout Classes" modifies the field value via jQuery
  jQuery(field).on('change', function () {
    return (0,_token_actions__WEBPACK_IMPORTED_MODULE_1__.synchronizeTokensDisplay)(field.value, proxyInput, tokensWrapper);
  });
  proxyInput.addEventListener('keydown', function (event) {
    return onProxyInputKeydown(event, field, proxyInput, tokensWrapper);
  });
  proxyInput.addEventListener('blur', function () {
    return (0,_token_actions__WEBPACK_IMPORTED_MODULE_1__.addToken)(proxyInput.value.trim(), field, proxyInput);
  });
  tokensWrapper.addEventListener('click', function (event) {
    return handleTokenRemoval(event, field, proxyInput);
  });
}

/**
 * Handle keydown events on the proxy input field
 *
 * @private
 *
 * @param {Event}       event         Keydown event
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} proxyInput    The proxy input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function onProxyInputKeydown(event, field, proxyInput, tokensWrapper) {
  var key = event.key;
  var value = proxyInput.value.trim();
  switch (key) {
    // Remove the last token when backspace is pressed and input field is empty (no text being typed)
    case _constants__WEBPACK_IMPORTED_MODULE_0__.KEYS.BACKSPACE:
      if (!value) {
        event.preventDefault();
        var lastToken = tokensWrapper.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKEN, ":last-child"));
        (0,_token_actions__WEBPACK_IMPORTED_MODULE_1__.removeToken)(lastToken, field, proxyInput);
      }
      break;

    // Create a token from current input when delimiter keys are pressed
    case _constants__WEBPACK_IMPORTED_MODULE_0__.KEYS.SPACE:
    case _constants__WEBPACK_IMPORTED_MODULE_0__.KEYS.COMMA:
    case _constants__WEBPACK_IMPORTED_MODULE_0__.KEYS.ENTER:
      event.preventDefault();
      (0,_token_actions__WEBPACK_IMPORTED_MODULE_1__.addToken)(value, field, proxyInput);
      break;
  }
  (0,_proxy_input_style__WEBPACK_IMPORTED_MODULE_2__.adjustProxyInputStyle)(proxyInput, tokensWrapper);
}

/**
 * Handle token removal when clicking the remove button
 *
 * @private
 *
 * @param {Event}       event      Click event
 * @param {HTMLElement} field      The original hidden input field
 * @param {HTMLElement} proxyInput The proxy input field for interaction
 * @return {void}
 */
function handleTokenRemoval(event, field, proxyInput) {
  var removeButton = event.target.closest(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKEN_REMOVE));
  if (!removeButton) {
    return;
  }
  var token = removeButton.closest(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKEN));
  if (!token) {
    return;
  }
  var tokensWrapper = token.parentElement;
  (0,_token_actions__WEBPACK_IMPORTED_MODULE_1__.removeToken)(token, field, proxyInput);
  (0,_proxy_input_style__WEBPACK_IMPORTED_MODULE_2__.adjustProxyInputStyle)(proxyInput, tokensWrapper);
}

/***/ }),

/***/ "./js/src/settings-components/components/token-input/index.js":
/*!********************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/index.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initTokenInputFields: () => (/* reexport safe */ _token_input__WEBPACK_IMPORTED_MODULE_0__.initTokenInputFields)
/* harmony export */ });
/* harmony import */ var _token_input__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./token-input */ "./js/src/settings-components/components/token-input/token-input.js");


/***/ }),

/***/ "./js/src/settings-components/components/token-input/proxy-input-style.js":
/*!********************************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/proxy-input-style.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   adjustAllProxyInputStyles: () => (/* binding */ adjustAllProxyInputStyles),
/* harmony export */   adjustProxyInputStyle: () => (/* binding */ adjustProxyInputStyle)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/settings-components/components/token-input/constants.js");
/**
 * Proxy input style
 *
 * Functions for adjusting proxy input styling
 */



/**
 * Adjust styling for all proxy inputs on the current settings
 *
 * @return {void}
 */
function adjustAllProxyInputStyles() {
  document.querySelectorAll(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.CONTAINER)).forEach(function (container) {
    return adjustProxyInputStyle(container.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKEN_PROXY_INPUT)), container.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKENS_WRAPPER)));
  });
}

/**
 * Adjust the styling of the proxy input based on tokens wrapper dimensions
 *
 * @param {HTMLElement} proxyInput    The proxy input field
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function adjustProxyInputStyle(proxyInput, tokensWrapper) {
  if (!proxyInput || !tokensWrapper) {
    return;
  }
  var tokens = tokensWrapper.querySelectorAll(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKEN));
  var hasTokens = tokens.length > 0;

  // Reset all styles if no tokens
  if (!hasTokens) {
    proxyInput.style.paddingLeft = '';
    proxyInput.style.paddingTop = '';
    proxyInput.style.height = '';
    return;
  }
  var tokensWrapperHeight = tokensWrapper.offsetHeight;

  // Calculate number of rows based on wrapper height
  var numRows = Math.max(1, Math.ceil(tokensWrapperHeight / _constants__WEBPACK_IMPORTED_MODULE_0__.PROXY_INPUT_HEIGHT));
  if (numRows > 1) {
    // For multiple rows, calculate the width of tokens in the last row
    var lastRowWidth = calculateLastRowWidth(getLastRowTokens(tokens));
    proxyInput.style.height = "".concat(tokensWrapperHeight, "px");
    proxyInput.style.paddingTop = "".concat(tokensWrapperHeight - _constants__WEBPACK_IMPORTED_MODULE_0__.PROXY_INPUT_HEIGHT + _constants__WEBPACK_IMPORTED_MODULE_0__.TOKEN_GAP, "px");
    proxyInput.style.paddingLeft = lastRowWidth ? "".concat(lastRowWidth + _constants__WEBPACK_IMPORTED_MODULE_0__.TOKEN_GAP * 2, "px") : '';
  } else {
    // For single row, use the full width of tokens
    proxyInput.style.height = '';
    proxyInput.style.paddingTop = '';
    proxyInput.style.paddingLeft = "".concat(tokensWrapper.offsetWidth - _constants__WEBPACK_IMPORTED_MODULE_0__.TOKEN_GAP, "px");
  }
}

/**
 * Identify tokens in the last row of a multi-row token layout
 *
 * @param {NodeList} tokens All token elements
 * @return {Array} Array of tokens in the last row
 */
function getLastRowTokens(tokens) {
  if (!tokens.length) {
    return [];
  }
  var tokensArray = Array.from(tokens);
  var lastRowY = -1;
  tokensArray.forEach(function (token) {
    var tokenRect = token.getBoundingClientRect();
    var tokenBottom = tokenRect.bottom;
    if (tokenBottom > lastRowY) {
      lastRowY = tokenBottom;
    }
  });
  var threshold = _constants__WEBPACK_IMPORTED_MODULE_0__.TOKEN_GAP / 2;
  return tokensArray.filter(function (token) {
    var tokenRect = token.getBoundingClientRect();
    return Math.abs(tokenRect.bottom - lastRowY) <= threshold;
  });
}

/**
 * Calculate the total width of tokens in the last row
 *
 * @param {Array} lastRowTokens Array of token elements in the last row
 * @return {number} Total width of tokens in the last row
 */
function calculateLastRowWidth(lastRowTokens) {
  if (!lastRowTokens.length) {
    return 0;
  }
  var totalWidth = 0;
  lastRowTokens.forEach(function (token) {
    totalWidth += token.offsetWidth;
  });
  totalWidth += (lastRowTokens.length - 1) * _constants__WEBPACK_IMPORTED_MODULE_0__.TOKEN_GAP;
  return totalWidth + _constants__WEBPACK_IMPORTED_MODULE_0__.TOKEN_GAP;
}

/***/ }),

/***/ "./js/src/settings-components/components/token-input/token-actions.js":
/*!****************************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/token-actions.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToken: () => (/* binding */ addToken),
/* harmony export */   clearProxyInput: () => (/* binding */ clearProxyInput),
/* harmony export */   parseTokens: () => (/* binding */ parseTokens),
/* harmony export */   removeToken: () => (/* binding */ removeToken),
/* harmony export */   synchronizeTokensDisplay: () => (/* binding */ synchronizeTokensDisplay),
/* harmony export */   updateFieldValue: () => (/* binding */ updateFieldValue)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./js/src/settings-components/components/token-input/constants.js");
/* harmony import */ var _proxy_input_style__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./proxy-input-style */ "./js/src/settings-components/components/token-input/proxy-input-style.js");
/* harmony import */ var _token_elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./token-elements */ "./js/src/settings-components/components/token-input/token-elements.js");
/**
 * Token actions
 *
 * Core functions for token operations and management
 */

/**
 * Internal dependencies
 */




/**
 * Synchronize token display with the field value
 *
 * @param {string}      value         The field value
 * @param {HTMLElement} proxyInput    The proxy input field
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
function synchronizeTokensDisplay(value, proxyInput, tokensWrapper) {
  if (!value || !tokensWrapper || !proxyInput) {
    return;
  }

  // Clear existing tokens display and render new tokens
  tokensWrapper.innerHTML = '';
  parseTokens(value).forEach(function (token) {
    return (0,_token_elements__WEBPACK_IMPORTED_MODULE_2__.createTokenElement)(token, tokensWrapper);
  });
  (0,_proxy_input_style__WEBPACK_IMPORTED_MODULE_1__.adjustProxyInputStyle)(proxyInput, tokensWrapper);
  proxyInput.focus();
}

/**
 * Add a new token to the field
 *
 * @param {string}      tokenValue The token value to add
 * @param {HTMLElement} field      The original field
 * @param {HTMLElement} proxyInput The proxy input
 * @return {boolean} Whether a token was added
 */
function addToken(tokenValue, field, proxyInput) {
  if (!tokenValue || !field || !proxyInput) {
    return false;
  }

  // Get current tokens from field value
  var tokens = parseTokens(field.value);

  // Skip duplicate tokens
  if (tokens.includes(tokenValue)) {
    clearProxyInput(proxyInput);
    return false;
  }

  // Add new token
  tokens.push(tokenValue);
  updateFieldValue(field, tokens);
  clearProxyInput(proxyInput);
  return true;
}

/**
 * Remove a specific token from the field
 *
 * @param {HTMLElement} token      The token element to remove
 * @param {HTMLElement} field      The original field
 * @param {HTMLElement} proxyInput The proxy input
 * @return {void}
 */
function removeToken(token, field, proxyInput) {
  if (!token || !field || !proxyInput) {
    return;
  }
  var value = token.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_0__.CLASS_NAMES.TOKEN_VALUE)).textContent;

  // Filter out the token to remove
  var tokens = parseTokens(field.value).filter(function (tokenValue) {
    return tokenValue !== value;
  });
  updateFieldValue(field, tokens);

  // Remove the token element from DOM
  token.remove();
  proxyInput.focus();
}

/**
 * Parse string input into an array of tokens
 *
 * @param {string} value Space-separated string
 * @return {string[]} Array of tokens
 */
function parseTokens() {
  var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  value = value.trim();
  if (!value) {
    return [];
  }
  return value.split(/\s+/).filter(Boolean);
}

/**
 * Update field value with tokens and trigger change event
 *
 * @param {HTMLElement} field  The field to update
 * @param {string[]}    tokens Array of token values
 * @return {void}
 */
function updateFieldValue(field) {
  var tokens = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
  if (!field) {
    return;
  }
  field.value = tokens.join(' ');
  jQuery(field).trigger('change');
}

/**
 * Clear proxy input and maintain focus
 *
 * @param {HTMLElement} proxyInput The proxy input field
 * @return {void}
 */
function clearProxyInput(proxyInput) {
  if (!proxyInput) {
    return;
  }
  proxyInput.value = '';
  proxyInput.focus();
}

/***/ }),

/***/ "./js/src/settings-components/components/token-input/token-elements.js":
/*!*****************************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/token-elements.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createTokenContainerElement: () => (/* binding */ createTokenContainerElement),
/* harmony export */   createTokenElement: () => (/* binding */ createTokenElement)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants */ "./js/src/settings-components/components/token-input/constants.js");
/**
 * Token elements
 *
 * Functions for creating token DOM elements
 */

/**
 * Internal dependencies
 */


var _window$frmDom = window.frmDom,
  span = _window$frmDom.span,
  svg = _window$frmDom.svg,
  tag = _window$frmDom.tag;

/**
 * Create token container and input elements
 *
 * @param {HTMLElement} field Input field for tokenization
 * @return {HTMLElement|null} The container element or null if already initialized
 */
function createTokenContainerElement(field) {
  // Get the main container (.frm-with-right-icon) to work with Formidable's modal system
  var container = field.closest(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.WITH_RIGHT_ICON));
  if (container.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKENS_WRAPPER))) {
    return null;
  }
  container.classList.add(_constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.CONTAINER);
  var tokensWrapper = span({
    className: _constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKENS_WRAPPER
  });
  container.insertBefore(tokensWrapper, container.firstChild);
  var proxyInput = tag('input', {
    className: _constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKEN_PROXY_INPUT,
    id: "".concat(field.id, "-proxy-input")
  });
  proxyInput.type = 'text';

  // Inserting proxyInput after the field is important to maintain compatibility with Formidable's modal system
  field.parentNode.insertBefore(proxyInput, field.nextSibling);
  field.classList.add(core_constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);
  return container;
}

/**
 * Create a single token element
 *
 * @param {string}      value         Token value
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
function createTokenElement(value, tokensWrapper) {
  var tokenElement = span({
    className: _constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKEN,
    children: [span({
      text: value,
      className: _constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKEN_VALUE
    }), span({
      className: _constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKEN_REMOVE,
      child: svg({
        href: '#frm_close_icon'
      })
    })]
  });
  tokensWrapper.appendChild(tokenElement);
}

/***/ }),

/***/ "./js/src/settings-components/components/token-input/token-input.js":
/*!**************************************************************************!*\
  !*** ./js/src/settings-components/components/token-input/token-input.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initTokenInputFields: () => (/* binding */ initTokenInputFields)
/* harmony export */ });
/* harmony import */ var core_constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/constants */ "./js/src/core/constants.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants */ "./js/src/settings-components/components/token-input/constants.js");
/* harmony import */ var _token_elements__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./token-elements */ "./js/src/settings-components/components/token-input/token-elements.js");
/* harmony import */ var _token_actions__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./token-actions */ "./js/src/settings-components/components/token-input/token-actions.js");
/* harmony import */ var _proxy_input_style__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./proxy-input-style */ "./js/src/settings-components/components/token-input/proxy-input-style.js");
/* harmony import */ var _event_handlers__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./event-handlers */ "./js/src/settings-components/components/token-input/event-handlers.js");
/**
 * Token Input Component
 *
 * Transforms space-separated values in a text input into selectable tokens
 */

/**
 * Internal dependencies
 */







/**
 * Initialize all token input fields on the page
 *
 * @return {void}
 */
function initTokenInputFields() {
  findAndInitializeTokenFields();

  /**
   * Initialize for newly added fields
   *
   * @param {Event}       event          The frm_added_field event.
   * @param {HTMLElement} event.frmField The added field object being destructured from the event.
   */
  document.addEventListener('frm_added_field', function (_ref) {
    var frmField = _ref.frmField;
    return findAndInitializeTokenFields(frmField.dataset.fid);
  });

  /**
   * Initialize for newly added fields via AJAX
   *
   * @param {Event}       event           The frm_ajax_loaded_field event.
   * @param {HTMLElement} event.frmFields The added field objects being destructured from the event.
   */
  document.addEventListener('frm_ajax_loaded_field', function (_ref2) {
    var frmFields = _ref2.frmFields;
    return frmFields.forEach(function (field) {
      return findAndInitializeTokenFields(field.id);
    });
  });

  // Adjust styling for all token inputs when field settings are shown
  wp.hooks.addAction(core_constants__WEBPACK_IMPORTED_MODULE_0__.HOOKS.SHOW_FIELD_SETTINGS, 'formidable-token-input', _proxy_input_style__WEBPACK_IMPORTED_MODULE_4__.adjustAllProxyInputStyles);
}

/**
 * Find all token input fields and initialize them
 *
 * @private
 * @param {string|number} fieldId The ID of the field to initialize
 * @return {void}
 */
function findAndInitializeTokenFields(fieldId) {
  var container = fieldId ? document.getElementById("frm-single-settings-".concat(fieldId)) : document.body;
  var tokenInputFields = container.querySelectorAll(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKEN_INPUT_FIELD));
  if (!tokenInputFields.length) {
    return;
  }

  // Track processed fields to prevent duplicate initialization
  var processedFields = new Set();
  tokenInputFields.forEach(function (field) {
    if (!processedFields.has(field.id)) {
      setupTokenInput(field);
      processedFields.add(field.id);
    }
  });
}

/**
 * Set up a token input field with token container
 *
 * @private
 *
 * @param {HTMLElement} field Input field for tokenization
 */
function setupTokenInput(field) {
  var container = (0,_token_elements__WEBPACK_IMPORTED_MODULE_2__.createTokenContainerElement)(field);
  if (!container) {
    return;
  }
  var proxyInput = container.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKEN_PROXY_INPUT));
  var tokensWrapper = container.querySelector(".".concat(_constants__WEBPACK_IMPORTED_MODULE_1__.CLASS_NAMES.TOKENS_WRAPPER));
  (0,_token_actions__WEBPACK_IMPORTED_MODULE_3__.synchronizeTokensDisplay)(field.value, proxyInput, tokensWrapper);
  (0,_event_handlers__WEBPACK_IMPORTED_MODULE_5__.addEventListeners)(field, proxyInput, tokensWrapper);
}


/***/ }),

/***/ "./js/src/settings-components/components/unit-input.js":
/*!*************************************************************!*\
  !*** ./js/src/settings-components/components/unit-input.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupUnitInputHandlers: () => (/* binding */ setupUnitInputHandlers)
/* harmony export */ });
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/**
 * Internal dependencies
 */


/**
 * Setup unit input handlers
 *
 * @return {void}
 */
function setupUnitInputHandlers() {
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.documentOn)('change', '.frm-unit-input .frm-unit-input-control', onUnitInputChange);
  (0,core_utils__WEBPACK_IMPORTED_MODULE_0__.documentOn)('change', '.frm-unit-input select', onUnitInputChange);
}

/**
 * Handle the change event for the unit input
 *
 * @private
 * @param {Event} event The event object.
 * @return {void}
 */
function onUnitInputChange(event) {
  var unitInput = event.target.closest('.frm-unit-input');
  var control = unitInput.querySelector('.frm-unit-input-control');
  var unit = unitInput.querySelector('select').value;

  // Update input type when unit changes
  if (event.target.matches('select')) {
    control.type = '' === unit ? 'text' : 'number';
  }

  // Update the actual field value
  var inputValue = control.value.trim();
  unitInput.querySelector('input[type="hidden"]').value = '' !== inputValue ? inputValue + unit : '';
}

/***/ }),

/***/ "./js/src/settings-components/events/add-fields-button-handler.js":
/*!************************************************************************!*\
  !*** ./js/src/settings-components/events/add-fields-button-handler.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * Redirects to "Add Fields" tab when the "Add Fields" button is clicked.
 *
 * When users view the Field Options tab with no fields, they see an "Add Fields" button.
 * Clicking this button should take them to the "Add Fields" tab for field selection.
 */

/**
 * Initializes the Add Fields button click handler.
 */
var initAddFieldsButtonHandler = function initAddFieldsButtonHandler() {
  var _document$getElementB;
  (_document$getElementB = document.getElementById('frm-form-add-field')) === null || _document$getElementB === void 0 || _document$getElementB.addEventListener('click', function (event) {
    var _document$querySelect;
    event.preventDefault();
    (_document$querySelect = document.querySelector('.frm-settings-panel .frm-tabs-navs ul > li:first-child')) === null || _document$querySelect === void 0 || _document$querySelect.click();
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (initAddFieldsButtonHandler);

/***/ }),

/***/ "./js/src/settings-components/events/index.js":
/*!****************************************************!*\
  !*** ./js/src/settings-components/events/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initAddFieldsButtonHandler: () => (/* reexport safe */ _add_fields_button_handler__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _add_fields_button_handler__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./add-fields-button-handler */ "./js/src/settings-components/events/add-fields-button-handler.js");


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
/*!*********************************************!*\
  !*** ./js/src/settings-components/index.js ***!
  \*********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./events */ "./js/src/settings-components/events/index.js");
/* harmony import */ var _components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components */ "./js/src/settings-components/components/index.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__["default"])(function () {
  new _components__WEBPACK_IMPORTED_MODULE_1__.frmRadioComponent();
  new _components__WEBPACK_IMPORTED_MODULE_1__.frmSliderComponent();
  new _components__WEBPACK_IMPORTED_MODULE_1__.frmTabsComponent();
  (0,_events__WEBPACK_IMPORTED_MODULE_0__.initAddFieldsButtonHandler)();
  (0,_components__WEBPACK_IMPORTED_MODULE_1__.initTokenInputFields)();
  (0,_components__WEBPACK_IMPORTED_MODULE_1__.initToggleGroupComponents)();
  (0,_components__WEBPACK_IMPORTED_MODULE_1__.setupUnitInputHandlers)();
});
/******/ })()
;
//# sourceMappingURL=formidable-settings-components.js.map