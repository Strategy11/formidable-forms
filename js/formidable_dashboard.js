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

/***/ "./js/src/components/class-counter.js":
/*!********************************************!*\
  !*** ./js/src/components/class-counter.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, exports) => {

eval("\n\nObject.defineProperty(exports, \"__esModule\", ({\n\tvalue: true\n}));\n\nvar _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();\n\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nvar FrmCounter = exports.FrmCounter = function () {\n\tfunction FrmCounter(element, options) {\n\t\t_classCallCheck(this, FrmCounter);\n\n\t\tif (!element instanceof Element || null === element.getAttribute('data-counter')) {\n\t\t\treturn;\n\t\t}\n\n\t\tthis.template = 'default';\n\t\tif (null !== element.getAttribute('data-type')) {\n\t\t\tthis.template = element.getAttribute('data-type');\n\t\t}\n\n\t\tthis.element = element;\n\t\tthis.value = parseInt(element.getAttribute('data-counter'), 10);\n\t\tthis.activeCounter = 0;\n\t\tthis.speed = 'undefined' !== typeof options && 'undefined' !== typeof options.speed ? options.speed : 270;\n\t\tthis.valueStep = Math.ceil(this.value / this.speed);\n\n\t\tthis.animate();\n\t}\n\n\t_createClass(FrmCounter, [{\n\t\tkey: 'formatNumber',\n\t\tvalue: function formatNumber(number) {\n\t\t\tif ('currency' === this.template) {\n\t\t\t\treturn number.toLocaleString(undefined, { minimumFractionDigits: 2 });\n\t\t\t}\n\t\t\treturn number;\n\t\t}\n\t}, {\n\t\tkey: 'animate',\n\t\tvalue: function animate() {\n\t\t\tvar _this = this;\n\n\t\t\tif (this.activeCounter < this.value) {\n\t\t\t\tthis.activeCounter += this.valueStep;\n\t\t\t\tthis.element.innerText = this.formatNumber(this.activeCounter);\n\t\t\t\tsetTimeout(function () {\n\t\t\t\t\t_this.animate();\n\t\t\t\t}, 4);\n\t\t\t} else {\n\t\t\t\tthis.element.innerText = this.formatNumber(this.value);\n\t\t\t}\n\t\t}\n\t}]);\n\n\treturn FrmCounter;\n}();\n\n//# sourceURL=webpack://formidable/./js/src/components/class-counter.js?");

/***/ }),

/***/ "./js/src/components/class-tabs-navigator.js":
/*!***************************************************!*\
  !*** ./js/src/components/class-tabs-navigator.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, exports) => {

eval("\n\nObject.defineProperty(exports, \"__esModule\", ({\n\tvalue: true\n}));\n\nvar _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();\n\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nvar FrmTabsNavigator = exports.FrmTabsNavigator = function () {\n\tfunction FrmTabsNavigator(wrapper) {\n\t\t_classCallCheck(this, FrmTabsNavigator);\n\n\t\tif ('undefined' === typeof wrapper) {\n\t\t\treturn;\n\t\t}\n\t\tthis.flexboxSlidesGap = '16px';\n\t\tthis.wrapper = document.querySelector(wrapper);\n\t\tthis.navs = this.wrapper.querySelectorAll('.frm-tabs-navs ul > li');\n\t\tthis.slideTrackLine = this.wrapper.querySelector('.frm-tabs-active-underline');\n\t\tthis.slideTrack = this.wrapper.querySelector('.frm-tabs-slide-track');\n\t\tthis.slides = this.wrapper.querySelectorAll('.frm-tabs-slide-track > div');\n\n\t\tthis.init();\n\t}\n\n\t_createClass(FrmTabsNavigator, [{\n\t\tkey: 'init',\n\t\tvalue: function init() {\n\t\t\tvar _this = this;\n\n\t\t\tif (null === this.wrapper || null === this.navs || null === this.trackLine || null === this.slideTrack || null === this.slides) {\n\t\t\t\treturn;\n\t\t\t}\n\t\t\tthis.navs.forEach(function (nav, index) {\n\t\t\t\tnav.addEventListener('click', function (event) {\n\t\t\t\t\treturn _this.onNavClick(event, index);\n\t\t\t\t});\n\t\t\t});\n\t\t}\n\t}, {\n\t\tkey: 'onNavClick',\n\t\tvalue: function onNavClick(event, index) {\n\t\t\tthis.removeActiveClassnameFromNavs();\n\t\t\tevent.target.classList.add('frm-active');\n\t\t\tthis.initSlideTrackUnterline(event.target);\n\t\t\tthis.changeSlide(index);\n\t\t}\n\t}, {\n\t\tkey: 'initSlideTrackUnterline',\n\t\tvalue: function initSlideTrackUnterline(nav) {\n\t\t\tvar activeNav = 'undefined' !== typeof nav ? nav : this.navs.filter(function (nav) {\n\t\t\t\treturn nav.classList.contains('frm-active');\n\t\t\t});\n\t\t\tthis.slideTrackLine.style.transform = 'translateX(' + activeNav.offsetLeft + 'px)';\n\t\t\tthis.slideTrackLine.style.width = activeNav.offsetWidth + 'px';\n\t\t}\n\t}, {\n\t\tkey: 'changeSlide',\n\t\tvalue: function changeSlide(index) {\n\t\t\tthis.removeActiveClassnameFromSlides();\n\t\t\tvar translate = index == 0 ? '0px' : 'calc( ( ' + index * 100 + '% + ' + this.flexboxSlidesGap + ' ) * -1 )';\n\t\t\tthis.slideTrack.style.transform = 'translateX(' + translate + ')';\n\t\t\tif ('undefined' !== typeof this.slides[index]) {\n\t\t\t\tthis.slides[index].classList.add('frm-active');\n\t\t\t}\n\t\t}\n\t}, {\n\t\tkey: 'removeActiveClassnameFromSlides',\n\t\tvalue: function removeActiveClassnameFromSlides() {\n\t\t\tthis.slides.forEach(function (slide) {\n\t\t\t\treturn slide.classList.remove('frm-active');\n\t\t\t});\n\t\t}\n\t}, {\n\t\tkey: 'removeActiveClassnameFromNavs',\n\t\tvalue: function removeActiveClassnameFromNavs() {\n\t\t\tthis.navs.forEach(function (nav) {\n\t\t\t\treturn nav.classList.remove('frm-active');\n\t\t\t});\n\t\t}\n\t}]);\n\n\treturn FrmTabsNavigator;\n}();\n\n//# sourceURL=webpack://formidable/./js/src/components/class-tabs-navigator.js?");

/***/ }),

/***/ "./js/src/dashboard.js":
/*!*****************************!*\
  !*** ./js/src/dashboard.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("\n\nvar _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();\n\nvar _classTabsNavigator = __webpack_require__(/*! ./components/class-tabs-navigator */ \"./js/src/components/class-tabs-navigator.js\");\n\nvar _classCounter = __webpack_require__(/*! ./components/class-counter */ \"./js/src/components/class-counter.js\");\n\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nvar FrmDashboard = function () {\n\tfunction FrmDashboard() {\n\t\t_classCallCheck(this, FrmDashboard);\n\n\t\tthis.options = {\n\t\t\tajax: {\n\t\t\t\taction: 'dashboard_ajax_action',\n\t\t\t\tdashboardActions: {\n\t\t\t\t\twelcomeBanner: 'welcome-banner-cookie'\n\t\t\t\t}\n\t\t\t}\n\t\t};\n\t}\n\n\t_createClass(FrmDashboard, [{\n\t\tkey: 'initInbox',\n\t\tvalue: function initInbox() {\n\t\t\tnew _classTabsNavigator.FrmTabsNavigator('.frm-inbox-wrapper');\n\t\t}\n\t}, {\n\t\tkey: 'initIntroWidgetAnimation',\n\t\tvalue: function initIntroWidgetAnimation() {\n\t\t\tvar widgets = document.querySelectorAll('.frm-dashboard-widget.frm-animate');\n\t\t\twidgets.forEach(function (widget, index) {\n\t\t\t\twidget.classList.remove('frm-animate');\n\t\t\t\twidget.style.transitionDelay = (index + 1) * 0.025 + 's';\n\t\t\t});\n\t\t}\n\t}, {\n\t\tkey: 'initCounter',\n\t\tvalue: function initCounter() {\n\t\t\tvar counters = document.querySelectorAll('.frm-counter');\n\t\t\tcounters.forEach(function (counter) {\n\t\t\t\treturn new _classCounter.FrmCounter(counter);\n\t\t\t});\n\t\t}\n\t}, {\n\t\tkey: 'initCloseWelcomeBanner',\n\t\tvalue: function initCloseWelcomeBanner() {\n\t\t\tvar _this = this;\n\n\t\t\tvar closeButton = document.querySelector('.frm-dashboard-banner-close');\n\t\t\tvar dashboardBanner = document.querySelector('.frm-dashboard-banner');\n\n\t\t\tif (null === closeButton || null === dashboardBanner) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tcloseButton.addEventListener('click', function () {\n\t\t\t\t_this.closeWelcomeBannerSaveCookieRequest().then(function (data) {\n\t\t\t\t\tif (true === data.success) {\n\t\t\t\t\t\tdashboardBanner.remove();\n\t\t\t\t\t}\n\t\t\t\t});\n\t\t\t});\n\t\t}\n\t}, {\n\t\tkey: 'closeWelcomeBannerSaveCookieRequest',\n\t\tvalue: function closeWelcomeBannerSaveCookieRequest() {\n\t\t\tvar _this2 = this;\n\n\t\t\treturn new Promise(function (resolve, reject) {\n\t\t\t\tfetch(window.ajaxurl, {\n\t\t\t\t\tmethod: 'POST',\n\t\t\t\t\theaders: {\n\t\t\t\t\t\t'Content-Type': 'application/x-www-form-urlencoded'\n\t\t\t\t\t},\n\t\t\t\t\tbody: new URLSearchParams({\n\t\t\t\t\t\taction: _this2.options.ajax.action,\n\t\t\t\t\t\tdashboard_action: _this2.options.ajax.dashboardActions.welcomeBanner,\n\t\t\t\t\t\tbanner_has_closed: 1\n\t\t\t\t\t})\n\t\t\t\t}).then(function (result) {\n\t\t\t\t\tresult.json().then(function (data) {\n\t\t\t\t\t\tresolve(data);\n\t\t\t\t\t});\n\t\t\t\t});\n\t\t\t});\n\t\t}\n\t}]);\n\n\treturn FrmDashboard;\n}();\n\nvar dashboard = new FrmDashboard();\ndocument.addEventListener('DOMContentLoaded', function () {\n\tdashboard.initInbox();\n\tdashboard.initIntroWidgetAnimation();\n\tdashboard.initCounter();\n\tdashboard.initCloseWelcomeBanner();\n});\n\n//# sourceURL=webpack://formidable/./js/src/dashboard.js?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./js/src/dashboard.js");
/******/ 	
/******/ })()
;