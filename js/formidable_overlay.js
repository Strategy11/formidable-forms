/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/src/components/class-overlay.js":
/*!********************************************!*\
  !*** ./js/src/components/class-overlay.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FrmOverlay: () => (/* binding */ FrmOverlay)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
var FrmOverlay = /*#__PURE__*/function () {
  function FrmOverlay() {
    _classCallCheck(this, FrmOverlay);
    this.body = document.body;
  }

  /**
   * Open overlay
   *
   * @param {Object} overlayData - An object containing data for the overlay.
   * @param {string} overlayData.hero_image - URL of the hero image.
   * @param {string} overlayData.heading - Heading of the overlay.
   * @param {string} overlayData.copy - Copy/content of the overlay.
   * @param {Array}  overlayData.buttons - Array of button objects.
   * @param {string} overlayData.buttons[].url - URL for the button.
   * @param {string} overlayData.buttons[].target - Target attribute for the button link.
   * @param {string} overlayData.buttons[].label - Label/text of the button.
   */
  _createClass(FrmOverlay, [{
    key: "open",
    value: function open(overlayData) {
      this.overlayData = {
        hero_image: null,
        heading: null,
        copy: null,
        buttons: []
      };
      this.overlayData = _objectSpread(_objectSpread({}, this.overlayData), overlayData);
      this.bodyAddOverflowHidden();
      this.body.insertBefore(this.buildOverlay(), this.body.firstChild);
      this.initCloseButton();
      this.initOverlayIntroAnimation(200);
    }
  }, {
    key: "bodyAddOverflowHidden",
    value: function bodyAddOverflowHidden() {
      this.body.classList.add('frm-hidden-overflow');
      setTimeout(function () {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      }, 80);
    }
  }, {
    key: "close",
    value: function close() {
      var overlayWrapper = document.querySelector('.frm-overlay--wrapper');
      if (overlayWrapper) {
        document.body.classList.remove('frm-hidden-overflow');
        overlayWrapper.remove();
      }
    }
  }, {
    key: "initCloseButton",
    value: function initCloseButton() {
      var overlayWrapper = document.querySelector('.frm-overlay--wrapper');
      if (overlayWrapper) {
        var closeButton = document.createElement('span');
        closeButton.classList.add('frm-overlay--close');
        closeButton.addEventListener('click', this.close);
        overlayWrapper.prepend(closeButton);
      }
    }
  }, {
    key: "getHeroImage",
    value: function getHeroImage() {
      if (this.overlayData.hero_image) {
        return frmDom.img({
          src: this.overlayData.hero_image
        });
      }
      return '';
    }
  }, {
    key: "getButtons",
    value: function getButtons() {
      var buttons = this.overlayData.buttons.map(function (button, index) {
        if (!button.url || '' === button.url) {
          return '';
        }
        ;
        var buttonTypeClassname = 1 === index ? 'frm-button-primary' : 'frm-button-secondary';
        var options = {
          href: button.url,
          text: button.label,
          className: 'button frm_animate_bg ' + buttonTypeClassname
        };
        if (button.target) {
          options.target = button.target;
        }
        return frmDom.a(options);
      });
      if (buttons) {
        var buttonsWrapperElementOptions = {
          className: 'frm-overlay--cta frm-flex-box',
          children: buttons
        };
        return frmDom.div(buttonsWrapperElementOptions);
      }
      return '';
    }
  }, {
    key: "getHeading",
    value: function getHeading() {
      if (this.overlayData.heading) {
        return frmDom.tag('h2', {
          className: 'frm-overlay--heading frm-text-xl',
          text: this.overlayData.heading
        });
      }
      return '';
    }
  }, {
    key: "getCopy",
    value: function getCopy() {
      if (this.overlayData.copy) {
        var copy = frmDom.tag('div');
        copy.innerHTML = this.overlayData.copy;
        return frmDom.div({
          className: 'frm-overlay--copy',
          child: copy
        });
      }
      return '';
    }
  }, {
    key: "initOverlayIntroAnimation",
    value: function initOverlayIntroAnimation(delay) {
      var overlayWrapper = document.querySelector('.frm-overlay--wrapper');
      if (overlayWrapper) {
        setTimeout(function () {
          overlayWrapper.classList.add('frm-active');
        }, delay);
      }
    }
  }, {
    key: "buildOverlay",
    value: function buildOverlay() {
      var container = frmDom.div({
        className: 'frm-overlay--container',
        children: [frmDom.div({
          className: 'frm-overlay--hero-image frm-mb-md',
          children: [this.getHeroImage()]
        }), this.getHeading(), this.getCopy(), this.getButtons()]
      });
      return frmDom.div({
        className: 'frm-overlay--wrapper frm_wrap',
        children: [container]
      });
    }
  }]);
  return FrmOverlay;
}();

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
/*!***************************!*\
  !*** ./js/src/overlay.js ***!
  \***************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_class_overlay__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/class-overlay */ "./js/src/components/class-overlay.js");

window.frmOverlay = new _components_class_overlay__WEBPACK_IMPORTED_MODULE_0__.FrmOverlay();
})();

/******/ })()
;
//# sourceMappingURL=formidable_overlay.js.map