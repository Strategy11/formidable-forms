/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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
      var position = this.isRTL ? -(activeNav.parentElement.offsetWidth - activeNav.offsetLeft - activeNav.offsetWidth) : activeNav.offsetLeft;
      this.slideTrackLine.style.transform = "translateX(".concat(position, "px)");
      this.slideTrackLine.style.width = activeNav.clientWidth + 'px';
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

/***/ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.js":
/*!******************************************************************************************!*\
  !*** ./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.js ***!
  \******************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmTabNavigatorComponent: function() { return /* binding */ frmTabNavigatorComponent; }
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
      wrapper.innerHTML = this.getTabDelimiter() + this.getTabs() + this.getTabContainer();
      new _components_class_tabs_navigator__WEBPACK_IMPORTED_MODULE_0__.frmTabsNavigator(wrapper);
      return wrapper;
    }
  }, {
    key: "afterViewInit",
    value: function afterViewInit(wrapper) {
      this.setInitialUnderlineWidth(wrapper);
    }

    /**
     * Sets the initial underline width of active tab nav item.
     * @param {Element} wrapper - The wrapper element.
     */
  }, {
    key: "setInitialUnderlineWidth",
    value: function setInitialUnderlineWidth(wrapper) {
      var li = wrapper.querySelector('li.frm-active');
      var tabActiveUnderline = wrapper.querySelector('.frm-tabs-delimiter .frm-tabs-active-underline');
      if (!li || !tabActiveUnderline) {
        return;
      }
      tabActiveUnderline.style.width = "".concat(li.clientWidth, "px");
    }

    /**
     * Gets the tab delimiter.
     * @return {string} - The tab delimiter.
     */
  }, {
    key: "getTabDelimiter",
    value: function getTabDelimiter() {
      return "<div class=\"frm-tabs-delimiter\">\n\t\t\t<span data-initial-width=\"123\" class=\"frm-tabs-active-underline frm-first\"></span>\n\t\t</div>";
    }

    /**
     * Gets the tab headings.
     * @return {string} - The tab headings.
     */
  }, {
    key: "getTabs",
    value: function getTabs() {
      var _this2 = this;
      var tabHeadings = Array.from(this.tabs).map(function (tab, index) {
        return _this2.createTabHeading(tab, index);
      });
      return "<div class=\"frm-tabs-navs\">\n\t\t\t<ul>\n\t\t\t\t".concat(tabHeadings.join(''), "\n\t\t\t</ul>\n\t\t</div>");
    }

    /**
     * Gets the tab container.
     * @return {string} - The tab container.
     */
  }, {
    key: "getTabContainer",
    value: function getTabContainer() {
      var _this3 = this;
      var tabContainer = Array.from(this.tabs).map(function (tab, index) {
        return _this3.createTabContainer(tab, index);
      });
      return "<div class=\"frm-tabs-container\">\n\t\t\t<div class=\"frm-tabs-slide-track frm-flex-box\">\n\t\t\t\t".concat(tabContainer.join(''), "\n\t\t\t</div>\n\t\t</div>");
    }

    /**
     * Creates a tab heading.
     * @param {Element} tab   - The tab element.
     * @param {number}  index - The index of the tab.
     * @return {string} - The tab heading.
     */
  }, {
    key: "createTabHeading",
    value: function createTabHeading(tab, index) {
      var className = index === 0 ? 'frm-active' : '';
      return "<li class=\"".concat(className, "\">").concat(tab.getAttribute('data-tab-title'), "</li>");
    }

    /**
     * Creates a tab container.
     * @param {Element} tab   - The tab element.
     * @param {number}  index - The index of the tab.
     * @return {string} - The tab container.
     */
  }, {
    key: "createTabContainer",
    value: function createTabContainer(tab, index) {
      var className = index === 0 ? 'frm-active' : '';
      return "<div class=\"frm-tab-container ".concat(className, "\">\n\t\t\t").concat(tab.innerHTML, "\n\t\t</div>");
    }

    /**
     * Gets the tab underline.
     * @return {Element} - The tab underline.
     */
  }, {
    key: "getTabUnderline",
    value: function getTabUnderline() {
      return this.shadowRoot.querySelector('.frm-tabs-active-underline');
    }
  }]);
}(_frm_web_component__WEBPACK_IMPORTED_MODULE_1__.frmWebComponent);

/***/ }),

/***/ "./js/src/web-components/frm-web-component.js":
/*!****************************************************!*\
  !*** ./js/src/web-components/frm-web-component.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   frmWebComponent: function() { return /* binding */ frmWebComponent; }
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
    _this.initOptions();
    if (_this.useShadowDom) {
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
      this.useShadowDom = 'false' === this.getAttribute('data-shadow-dom') ? false : true;
    }

    /*
    * Load the component style.
    * @return string
    */
  }, {
    key: "loadStyle",
    value: function loadStyle() {
      var style = document.createElement('style');
      style.textContent = this.componentStyle;
      return style;
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
      var wrapper = this.useShadowDom ? this.shadowRoot : this;
      wrapper.innerHTML = '';
      wrapper.appendChild(this.loadStyle());
      wrapper.appendChild(view);
      this.whenElementBecomesVisible().then(function () {
        _this2.afterViewInit(_this2);
      });
    }

    /**
     * Waits for the element to become visible in the viewport.
     * @return {Promise} - A promise that resolves when the element is visible.
     */
  }, {
    key: "whenElementBecomesVisible",
    value: function whenElementBecomesVisible() {
      var _this3 = this;
      // eslint-disable-next-line compat/compat
      return new Promise(function (resolve) {
        // eslint-disable-next-line compat/compat
        if ('undefined' === typeof window.IntersectionObserver) {
          requestAnimationFrame(function () {
            return resolve();
          });
          return;
        }

        // eslint-disable-next-line compat/compat
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
        var element = _this3.useShadowDom ? _this3.shadowRoot : _this3;
        observer.observe(element);
      });
    }

    /**
     * After the view is initialized and the element/wrapper is visible in the viewport.
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

/***/ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.css":
/*!*******************************************************************************************!*\
  !*** ./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.css ***!
  \*******************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = (".frm-tabs-wrapper {\n\tposition: relative;\n\toverflow: hidden;\n}\n.frm-tabs-wrapper .frm-tabs-navigator {\n\tmargin: 0;\n\tpadding: 0;\n\tdisplay: flex;\n\tgap: var(--gap-xs);\n\tjustify-content: space-between;\n\talign-items: center;\n\tbackground: rgb(242, 244, 247);\n\tborder-radius: var(--small-radius);\n\tbox-sizing: border-box;\n\theight: 44px;\n\tposition: relative;\n\tz-index: 2;\n}\n.frm-tabs-wrapper .frm-tabs-navigator .frm-tab-item {\n\tflex: 1;\n\ttext-align: center;\n\tcursor: pointer;\n}\n.frm-tabs-wrapper .frm-tabs-navigator .frm-active-background {\n\tdisplay: block;\n\theight: 100%;\n\tbackground: white;\n\tposition: absolute;\n\ttop: 0;\n\tleft: 0;\n\tz-index: 1;\n}\n.frm-tabs-navs {\n\tpadding: 0;\n\tmin-height: 44px;\n}\n.frm-tabs-navs ul {\n\tmargin: 0;\n\theight: var(--h-md);\n\tposition: relative;\n\tdisplay: flex;\n\tjustify-content: space-between;\n\tlist-style-type: none;\n\tpadding: 0px;\n}\n.frm-tabs-navs ul li,\n.frm-tabs-navs ul li a {\n\tcolor: var(--grey-500);\n\tfont-weight: 500;\n\tfont-size: var(--text-sm);\n\tline-height: 28px;\n}\n.frm-tabs-navs ul li {\n\tflex: 1;\n\theight: 28px;\n\ttext-align: center;\n\tmargin-top: var(--gap-xs);\n\tmargin-bottom: 0;\n\tcursor: pointer;\n}\n.frm-tabs-navs ul li.frm-active, .frm-style-tabs-wrapper .frm-tabs-navs ul li.frm-active a {\n\tcolor: var(--grey-900);\n}\n.frm-tabs-navs ul li:first-child {\n\tmargin-left: var(--gap-xs);\n}\n.frm-tabs-navs ul li:last-child {\n\tmargin-right: var(--gap-xs);\n}\n.frm-tabs-delimiter {\n\tposition: absolute;\n\ttop: 0;\n\tleft: 0;\n\twidth: 100%;\n\tbackground: rgb(242, 244, 247);\n\theight: 44px;\n\tmargin: 0;\n\tborder-radius: var(--small-radius);\n}\n.frm-tabs-delimiter .frm-tabs-active-underline {\n\theight: 28px;\n\tbackground: white;\n\tposition: absolute;\n\tleft: 0;\n\tbottom: 8px;\n\twidth: 45px;\n\ttransition: 0.4s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n\tborder-radius: var(--small-radius);\n\tbox-shadow: var(--button-shadow);\n}\n.frm-tabs-delimiter .frm-tabs-active-underline.frm-first {\n\tleft: var(--gap-xs);\n}\n.frm-tabs-delimiter .frm-tabs-active-underline.frm-last {\n\tleft: calc(-1 * var(--gap-xs));\n}\n.frm-tabs-container {\n\tposition: relative;\n\toverflow: hidden;\n\tmargin-top: var(--gap-md);\n\theight: 100%;\n}\n.frm-tabs-container .frm-tabs-slide-track {\n\tdisplay: flex;\n\ttransition: 0.32s transform cubic-bezier(0.25, 0.46, 0.45, 0.94);\n}\n.frm-tabs-slide-track > div {\n\tflex: 0 0 100%;\n\topacity: 0;\n\ttransition: 0.25s opacity linear;\n\tposition: relative;\n\theight: auto;\n\tmax-height: unset;\n\toverflow: hidden;\n\tbox-sizing: border-box;\n}\n.frm-tabs-slide-track > div > div {\n\toverflow: auto;\n\tposition: relative;\n\twidth: 100%;\n\tpadding: 0;\n\tbox-sizing: border-box;\n}\n.frm-tabs-slide-track > div > div:first-child {\n\theight: 100%;\n}\n.frm-tabs-slide-track > div.frm-active {\n\topacity: 1;\n\ttransition: 0.35s opacity linear;\n}");

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
/*!****************************************!*\
  !*** ./js/src/web-components/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _frm_tab_navigator_component_frm_tab_navigator_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./frm-tab-navigator-component/frm-tab-navigator-component */ "./js/src/web-components/frm-tab-navigator-component/frm-tab-navigator-component.js");

customElements.define('frm-tab-navigator-component', _frm_tab_navigator_component_frm_tab_navigator_component__WEBPACK_IMPORTED_MODULE_0__.frmTabNavigatorComponent);
/******/ })()
;
//# sourceMappingURL=formidable-web-components.js.map