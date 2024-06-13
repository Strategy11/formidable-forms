/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/src/components/class-counter.js":
/*!********************************************!*\
  !*** ./js/src/components/class-counter.js ***!
  \********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmCounter: function() { return /* binding */ frmCounter; }
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
   * @param {object} options
   * @param {integer} options.timetoFinish - Max time in mileseconds for counter to complete the animation.
   */
  function frmCounter(element, options) {
    _classCallCheck(this, frmCounter);
    if (!element instanceof Element || !element.dataset.counter) {
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
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

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
    this.wrapper = document.querySelector(wrapper);
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
      this.initSlideTrackUnterline(event.target);
      this.changeSlide(index);
    }
  }, {
    key: "initSlideTrackUnterline",
    value: function initSlideTrackUnterline(nav) {
      var activeNav = 'undefined' !== typeof nav ? nav : this.navs.filter(function (nav) {
        return nav.classList.contains('frm-active');
      });
      this.slideTrackLine.style.transform = "translateX(".concat(activeNav.offsetLeft, "px)");
      this.slideTrackLine.style.width = activeNav.offsetWidth + 'px';
    }
  }, {
    key: "changeSlide",
    value: function changeSlide(index) {
      this.removeActiveClassnameFromSlides();
      var translate = index == 0 ? '0px' : "calc( ( ".concat(index * 100, "% + ").concat(this.flexboxSlidesGap, " ) * -1 )");
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

/***/ "./js/src/core/utils/animation.js":
/*!****************************************!*\
  !*** ./js/src/core/utils/animation.js ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

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

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dispatchCustomEvent: function() { return /* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_0__.dispatchCustomEvent; },
/* harmony export */   frmAnimate: function() { return /* reexport safe */ _animation__WEBPACK_IMPORTED_MODULE_1__.frmAnimate; },
/* harmony export */   onClickPreventDefault: function() { return /* reexport safe */ _event__WEBPACK_IMPORTED_MODULE_0__.onClickPreventDefault; }
/* harmony export */ });
/* harmony import */ var _event__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./event */ "./js/src/core/utils/event.js");
/* harmony import */ var _animation__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./animation */ "./js/src/core/utils/animation.js");



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
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
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
}();
/******/ })()
;
//# sourceMappingURL=formidable_dashboard.js.map