/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/src/components/class-counter.js":
/*!********************************************!*\
  !*** ./js/src/components/class-counter.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmCounter: () => (/* binding */ frmCounter)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var frmCounter = /*#__PURE__*/function () {
  /**
   * Init frmCounter
   *
   * @param {Element} element
   * @param {Object}  options
   * @param {number}  options.timetoFinish Max time in mileseconds for counter to complete the animation.
   */
  function frmCounter(element, options) {
    _classCallCheck(this, frmCounter);
    if (!(element instanceof Element) || !element.dataset.counter) {
      return;
    }
    this.template = element.dataset.type || 'default';
    this.element = element;
    this.value = parseInt(element.dataset.counter, 10);
    this.activeCounter = 0;
    this.locale = element.dataset.locale ? element.dataset.locale.replace('_', '-') : 'en-US';
    this.timeoutInterval = 50;
    this.timeToFinish = 'undefined' !== typeof options && 'undefined' !== typeof options.timetoFinish ? options.timetoFinish : 1400;
    this.valueStep = this.value / Math.ceil(this.timeToFinish / this.timeoutInterval);
    if (0 === this.value) {
      return;
    }
    this.animate();
  }
  return _createClass(frmCounter, [{
    key: "formatNumber",
    value: function formatNumber(number) {
      if ('currency' === this.template) {
        return number.toLocaleString(this.locale, {
          minimumFractionDigits: 2
        });
      }
      return number;
    }
  }, {
    key: "animate",
    value: function animate() {
      if (Math.round(this.activeCounter) < this.value) {
        this.activeCounter += this.valueStep;
        this.element.innerText = this.formatNumber(Math.round(this.activeCounter));
        setTimeout(this.animate.bind(this), this.timeoutInterval);
      } else {
        this.element.innerText = this.formatNumber(this.value);
      }
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
/*!*****************************!*\
  !*** ./js/src/dashboard.js ***!
  \*****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/utils */ "./js/src/core/utils/index.js");
/* harmony import */ var _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/class-tabs-navigator */ "./js/src/components/class-tabs-navigator.js");
/* harmony import */ var _components_class_counter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/class-counter */ "./js/src/components/class-counter.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * External dependencies
 */



var frmDashboard = /*#__PURE__*/function () {
  function frmDashboard() {
    _classCallCheck(this, frmDashboard);
    this.options = {
      ajax: {
        action: 'dashboard_ajax_action',
        dashboardActions: {
          welcomeBanner: 'welcome-banner-has-closed',
          checkEmailIfSubscribed: 'email-has-subscribed',
          saveSubscribedEmail: 'save-subscribed-email'
        }
      }
    };
    this.widgetsAnimate = new core_utils__WEBPACK_IMPORTED_MODULE_0__.frmAnimate(document.querySelectorAll('.frm-dashboard-widget'), 'cascade');
  }
  return _createClass(frmDashboard, [{
    key: "init",
    value: function init() {
      this.initInbox();
      this.initCounters();
      this.initCloseWelcomeBanner();
      this.widgetsAnimate.cascadeFadeIn();
    }
  }, {
    key: "initInbox",
    value: function initInbox() {
      var _this = this;
      new _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_1__.frmTabsNavigator('.frm-inbox-wrapper');
      var userEmailInput = document.querySelector('#frm_leave_email');
      var subscribeButton = document.querySelector('#frm-add-my-email-address');
      subscribeButton.addEventListener('click', function () {
        _this.saveSubscribedEmail(userEmailInput.value).then();
      });
    }
  }, {
    key: "initCounters",
    value: function initCounters() {
      var counters = document.querySelectorAll('.frm-counter');
      counters.forEach(function (counter) {
        return new _components_class_counter__WEBPACK_IMPORTED_MODULE_2__.frmCounter(counter);
      });
    }
  }, {
    key: "initCloseWelcomeBanner",
    value: function initCloseWelcomeBanner() {
      var _this2 = this;
      var closeButton = document.querySelector('.frm-dashboard-banner-close');
      var dashboardBanner = document.querySelector('.frm-dashboard-banner');
      if (!closeButton || !dashboardBanner) {
        return;
      }
      closeButton.addEventListener('click', function () {
        _this2.closeWelcomeBannerSaveCookieRequest().then(function (data) {
          if (true === data.success) {
            dashboardBanner.remove();
          }
        });
      });
    }
  }, {
    key: "saveSubscribedEmail",
    value: function saveSubscribedEmail(email) {
      return fetch(window.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: this.options.ajax.action,
          dashboard_action: this.options.ajax.dashboardActions.saveSubscribedEmail,
          email: email
        })
      }).then(function (result) {
        return result.json();
      });
    }
  }, {
    key: "closeWelcomeBannerSaveCookieRequest",
    value: function closeWelcomeBannerSaveCookieRequest() {
      return fetch(window.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: this.options.ajax.action,
          dashboard_action: this.options.ajax.dashboardActions.welcomeBanner,
          banner_has_closed: 1
        })
      }).then(function (result) {
        return result.json();
      });
    }
  }]);
}();
var frmDashboardClass = new frmDashboard();
document.addEventListener('DOMContentLoaded', function () {
  frmDashboardClass.init();
});
/******/ })()
;
//# sourceMappingURL=formidable_dashboard.js.map