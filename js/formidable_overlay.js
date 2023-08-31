/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/src/components/class-overlay.js":
/*!********************************************!*\
  !*** ./js/src/components/class-overlay.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, exports) => {

eval("\n\nObject.defineProperty(exports, \"__esModule\", ({\n\tvalue: true\n}));\n\nvar _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };\n\nvar _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();\n\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nvar FrmOverlay = exports.FrmOverlay = function () {\n\tfunction FrmOverlay() {\n\t\t_classCallCheck(this, FrmOverlay);\n\n\t\tthis.body = document.querySelector('body');\n\t}\n\n\t/**\n  * Open overlay\n  *\n  * @param {Object} overlayData - An object containing data for the overlay.\n  * @param {string} overlayData.hero_image - URL of the hero image.\n  * @param {string} overlayData.heading - Heading of the overlay.\n  * @param {string} overlayData.copy - Copy/content of the overlay.\n  * @param {Array}  overlayData.buttons - Array of button objects.\n  * @param {string} overlayData.buttons[].url - URL for the button.\n  * @param {string} overlayData.buttons[].target - Target attribute for the button link.\n  * @param {string} overlayData.buttons[].label - Label/text of the button.\n  */\n\n\n\t_createClass(FrmOverlay, [{\n\t\tkey: 'open',\n\t\tvalue: function open(overlayData) {\n\t\t\tthis.overlayData = {\n\t\t\t\thero_image: null,\n\t\t\t\theading: null,\n\t\t\t\tcopy: null,\n\t\t\t\tbuttons: []\n\t\t\t};\n\n\t\t\tthis.overlayData = _extends({}, this.overlayData, overlayData);\n\t\t\tthis.body.insertBefore(this.buildOverlay(), this.body.firstChild);\n\t\t\tthis.initCloseButton();\n\t\t\tthis.initOverlayIntroAnimation(200);\n\t\t}\n\t}, {\n\t\tkey: 'close',\n\t\tvalue: function close() {\n\t\t\tvar overlayWrapper = document.querySelector('.frm-overlay--wrapper');\n\t\t\tif (overlayWrapper) {\n\t\t\t\toverlayWrapper.remove();\n\t\t\t}\n\t\t}\n\t}, {\n\t\tkey: 'initCloseButton',\n\t\tvalue: function initCloseButton() {\n\t\t\tvar overlayWrapper = document.querySelector('.frm-overlay--wrapper');\n\n\t\t\tif (overlayWrapper) {\n\t\t\t\tvar closeButton = document.createElement('span');\n\t\t\t\tcloseButton.classList.add('frm-overlay--close');\n\t\t\t\tcloseButton.addEventListener('click', this.close);\n\t\t\t\toverlayWrapper.prepend(closeButton);\n\t\t\t}\n\t\t}\n\t}, {\n\t\tkey: 'getHeroImage',\n\t\tvalue: function getHeroImage() {\n\t\t\tif (this.overlayData.hero_image) {\n\t\t\t\treturn frmDom.img({ src: this.overlayData.hero_image });\n\t\t\t}\n\t\t\treturn '';\n\t\t}\n\t}, {\n\t\tkey: 'getButtons',\n\t\tvalue: function getButtons() {\n\t\t\tvar buttons = this.overlayData.buttons.map(function (button) {\n\t\t\t\tif (!button.url || '' === button.url) {\n\t\t\t\t\treturn '';\n\t\t\t\t};\n\t\t\t\tvar options = {\n\t\t\t\t\thref: button.url,\n\t\t\t\t\ttext: button.label\n\t\t\t\t};\n\t\t\t\tif (button.target) {\n\t\t\t\t\toptions.target = button.target;\n\t\t\t\t}\n\t\t\t\treturn frmDom.a(options);\n\t\t\t});\n\n\t\t\tif (buttons) {\n\t\t\t\tvar buttonsWrapperElementOptions = { className: 'frm-overlay--cta', children: [] };\n\t\t\t\tbuttons.map(function (item) {\n\t\t\t\t\treturn buttonsWrapperElementOptions.children.push(item);\n\t\t\t\t});\n\t\t\t\treturn frmDom.div(buttonsWrapperElementOptions);\n\t\t\t}\n\n\t\t\treturn '';\n\t\t}\n\t}, {\n\t\tkey: 'getHeading',\n\t\tvalue: function getHeading() {\n\t\t\tif (this.overlayData.heading) {\n\t\t\t\treturn frmDom.tag('h2', { className: 'frm-overlay--heading', text: this.overlayData.heading });\n\t\t\t}\n\t\t\treturn '';\n\t\t}\n\t}, {\n\t\tkey: 'getCopy',\n\t\tvalue: function getCopy() {\n\t\t\tif (this.overlayData.copy) {\n\t\t\t\tvar copy = frmDom.tag('div');\n\t\t\t\tcopy.innerHTML = this.overlayData.copy;\n\t\t\t\treturn frmDom.div({ className: 'frm-overlay--copy', child: copy });\n\t\t\t}\n\t\t\treturn '';\n\t\t}\n\t}, {\n\t\tkey: 'initOverlayIntroAnimation',\n\t\tvalue: function initOverlayIntroAnimation(delay) {\n\t\t\tvar overlayWrapper = document.querySelector('.frm-overlay--wrapper');\n\t\t\tif (overlayWrapper) {\n\t\t\t\tsetTimeout(function () {\n\t\t\t\t\toverlayWrapper.classList.add('frm-active');\n\t\t\t\t}, delay);\n\t\t\t}\n\t\t}\n\t}, {\n\t\tkey: 'escapeHtml',\n\t\tvalue: function escapeHtml(html) {\n\t\t\treturn '' + html;\n\t\t}\n\t}, {\n\t\tkey: 'buildOverlay',\n\t\tvalue: function buildOverlay() {\n\t\t\tvar container = frmDom.div({\n\t\t\t\tclassName: 'frm-overlay--container',\n\t\t\t\tchildren: [frmDom.div({ className: 'frm-overlay--hero-image', children: [this.getHeroImage()] }), this.getHeading(), this.getCopy(), this.getButtons()]\n\t\t\t});\n\n\t\t\treturn frmDom.div({ className: 'frm-overlay--wrapper frm_wrap', children: [container] });\n\t\t}\n\t}]);\n\n\treturn FrmOverlay;\n}();\n\n//# sourceURL=webpack://formidable/./js/src/components/class-overlay.js?");

/***/ }),

/***/ "./js/src/overlay.js":
/*!***************************!*\
  !*** ./js/src/overlay.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("\n\nvar _classOverlay = __webpack_require__(/*! ./components/class-overlay */ \"./js/src/components/class-overlay.js\");\n\nwindow.frmOverlay = new _classOverlay.FrmOverlay();\n\n//# sourceURL=webpack://formidable/./js/src/overlay.js?");

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
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./js/src/overlay.js");
/******/ 	
/******/ })()
;