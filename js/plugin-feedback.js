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
  if (document.readyState === 'complete' ||
  // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.
  document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.
  ) {
    return void callback();
  }

  // DOMContentLoaded has not fired yet, delay callback until then.
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

/***/ "./js/src/plugin-feedback/submitFeedbackEvents.js":
/*!********************************************************!*\
  !*** ./js/src/plugin-feedback/submitFeedbackEvents.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils */ "./js/src/plugin-feedback/utils.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * Internal dependencies
 */

var doJsonPost = frmDom.ajax.doJsonPost;
var HIDDEN_CLASS = 'frm_hidden';
var CLASS_PREFIX = 'frm-plugin-feedback';
var LOADING_CLASS = 'frm_loading_button';
var pluginFeedback = document.getElementById(CLASS_PREFIX);
var form = document.getElementById("".concat(CLASS_PREFIX, "-form"));
var submitButton = form === null || form === void 0 ? void 0 : form.querySelector('button[type="submit"]');
var npsStep = document.getElementById("".concat(CLASS_PREFIX, "-nps-step"));
var reasonsStep = document.getElementById("".concat(CLASS_PREFIX, "-reasons-step"));
var thankYouStep = document.getElementById("".concat(CLASS_PREFIX, "-thank-you-step"));
var submitAction = pluginFeedback === null || pluginFeedback === void 0 ? void 0 : pluginFeedback.dataset.submitAction;
var dismissAction = pluginFeedback === null || pluginFeedback === void 0 ? void 0 : pluginFeedback.dataset.dismissAction;

/**
 * Adds event listeners for submitting plugin feedback.
 *
 * @private
 * @return {void}
 */
function addSubmitFeedbackEventListeners() {
  var _pluginFeedback$query;
  if (!pluginFeedback || !form) {
    return;
  }
  form.addEventListener('submit', onSubmitFeedback);
  (_pluginFeedback$query = pluginFeedback.querySelector('.dismiss')) === null || _pluginFeedback$query === void 0 || _pluginFeedback$query.addEventListener('click', onDismissFeedback);
}

/**
 * Handles form submission for plugin feedback.
 *
 * @private
 * @param {Event} event The form submit event.
 * @return {void}
 */
function onSubmitFeedback(_x) {
  return _onSubmitFeedback.apply(this, arguments);
}
/**
 * Updates the feedback step and shows/hides appropriate step elements.
 *
 * @private
 * @param {string} step The current step ('nps' or 'reasons').
 * @return {void}
 */
function _onSubmitFeedback() {
  _onSubmitFeedback = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(event) {
    var step, formData, npsScore, _form$querySelector, reasons, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          event.preventDefault();
          submitButton.classList.add(LOADING_CLASS);
          step = pluginFeedback.dataset.step;
          formData = new FormData();
          if ('nps' === step) {
            npsScore = form.querySelector('input[name="plugin-feedback-nps-score"]:checked');
            formData.append('nps-score', npsScore === null || npsScore === void 0 ? void 0 : npsScore.value);
          } else {
            reasons = form.querySelectorAll('input[name="plugin-feedback-reasons"]:checked');
            formData.append('reasons', JSON.stringify(Array.from(reasons).map(function (reason) {
              return reason.value;
            })));
            formData.append('details', (_form$querySelector = form.querySelector('textarea[name="plugin-feedback-details"]')) === null || _form$querySelector === void 0 ? void 0 : _form$querySelector.value);
          }
          _context.p = 1;
          _context.n = 2;
          return doJsonPost(submitAction, formData);
        case 2:
          _context.n = 4;
          break;
        case 3:
          _context.p = 3;
          _t = _context.v;
          (0,_utils__WEBPACK_IMPORTED_MODULE_0__.showError)(_t.type);
          if (_t.message) {
            console.error('Feedback submission error:', _t.message);
          }
          return _context.a(2);
        case 4:
          _context.p = 4;
          submitButton.classList.remove(LOADING_CLASS);
          return _context.f(4);
        case 5:
          (0,_utils__WEBPACK_IMPORTED_MODULE_0__.hideError)();
          updateFeedbackStep(step);
        case 6:
          return _context.a(2);
      }
    }, _callee, null, [[1, 3, 4, 5]]);
  }));
  return _onSubmitFeedback.apply(this, arguments);
}
function updateFeedbackStep(step) {
  if ('nps' === step) {
    pluginFeedback.dataset.step = 'reasons';
    npsStep.classList.add(HIDDEN_CLASS);
    reasonsStep.classList.remove(HIDDEN_CLASS);
  } else {
    pluginFeedback.dataset.step = 'thank-you';
    reasonsStep.classList.add(HIDDEN_CLASS);
    form.classList.add(HIDDEN_CLASS);
    thankYouStep.classList.remove(HIDDEN_CLASS);
  }
}

/**
 * Handles dismiss button click.
 *
 * @private
 * @param {Event} event The click event.
 * @return {void}
 */
function onDismissFeedback(_x2) {
  return _onDismissFeedback.apply(this, arguments);
}
function _onDismissFeedback() {
  _onDismissFeedback = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(event) {
    var formData, _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          event.preventDefault();
          pluginFeedback.remove();
          if (!('thank-you' === pluginFeedback.dataset.step)) {
            _context2.n = 1;
            break;
          }
          return _context2.a(2);
        case 1:
          formData = new FormData();
          formData.append('dismissed', true);
          _context2.p = 2;
          _context2.n = 3;
          return doJsonPost(dismissAction, formData);
        case 3:
          _context2.n = 5;
          break;
        case 4:
          _context2.p = 4;
          _t2 = _context2.v;
          if (_t2.message) {
            console.error('Feedback submission error:', _t2.message);
          }
        case 5:
          return _context2.a(2);
      }
    }, _callee2, null, [[2, 4]]);
  }));
  return _onDismissFeedback.apply(this, arguments);
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addSubmitFeedbackEventListeners);

/***/ }),

/***/ "./js/src/plugin-feedback/utils.js":
/*!*****************************************!*\
  !*** ./js/src/plugin-feedback/utils.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hideError: () => (/* binding */ hideError),
/* harmony export */   showError: () => (/* binding */ showError)
/* harmony export */ });
var error = document.getElementById('frm-plugin-feedback-error');

/**
 * Shows an error message for a form field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
function showError(type) {
  error.setAttribute('frm-error', type);
  error.classList.remove('frm_hidden');
}

/**
 * Hides the error message.
 *
 * @return {void}
 */
function hideError() {
  error.classList.add('frm_hidden');
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
/*!*****************************************!*\
  !*** ./js/src/plugin-feedback/index.js ***!
  \*****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var core_events__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core/events */ "./js/src/core/events/index.js");
/* harmony import */ var _submitFeedbackEvents__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./submitFeedbackEvents */ "./js/src/plugin-feedback/submitFeedbackEvents.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_2__["default"])(function () {
  (0,_submitFeedbackEvents__WEBPACK_IMPORTED_MODULE_1__["default"])();
  (0,core_events__WEBPACK_IMPORTED_MODULE_0__.addOptionBoxEvents)();
});
})();

/******/ })()
;
//# sourceMappingURL=plugin-feedback.js.map