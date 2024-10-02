!function(){var t={616:function(t){t.exports=function(t,e){var n,r,o=0;function i(){var i,a,c=n,l=arguments.length;t:for(;c;){if(c.args.length===arguments.length){for(a=0;a<l;a++)if(c.args[a]!==arguments[a]){c=c.next;continue t}return c!==n&&(c===r&&(r=c.prev),c.prev.next=c.next,c.next&&(c.next.prev=c.prev),c.next=n,c.prev=null,n.prev=c,n=c),c.val}c=c.next}for(i=new Array(l),a=0;a<l;a++)i[a]=arguments[a];return c={args:i,val:t.apply(null,i)},n?(n.prev=c,c.next=n):r=c,o===e.maxSize?(r=r.prev).next=null:o++,n=c,c.val}return e=e||{},i.clear=function(){n=null,r=null,o=0},i}},604:function(t,e,n){var r;!function(){"use strict";var o={not_string:/[^s]/,not_bool:/[^t]/,not_type:/[^T]/,not_primitive:/[^v]/,number:/[diefg]/,numeric_arg:/[bcdiefguxX]/,json:/[j]/,not_json:/[^j]/,text:/^[^\x25]+/,modulo:/^\x25{2}/,placeholder:/^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/,key:/^([a-z_][a-z_\d]*)/i,key_access:/^\.([a-z_][a-z_\d]*)/i,index_access:/^\[(\d+)\]/,sign:/^[+-]/};function i(t){return function(t,e){var n,r,a,c,l,s,u,f,d,p=1,y=t.length,m="";for(r=0;r<y;r++)if("string"==typeof t[r])m+=t[r];else if("object"==typeof t[r]){if((c=t[r]).keys)for(n=e[p],a=0;a<c.keys.length;a++){if(null==n)throw new Error(i('[sprintf] Cannot access property "%s" of undefined value "%s"',c.keys[a],c.keys[a-1]));n=n[c.keys[a]]}else n=c.param_no?e[c.param_no]:e[p++];if(o.not_type.test(c.type)&&o.not_primitive.test(c.type)&&n instanceof Function&&(n=n()),o.numeric_arg.test(c.type)&&"number"!=typeof n&&isNaN(n))throw new TypeError(i("[sprintf] expecting number but found %T",n));switch(o.number.test(c.type)&&(f=n>=0),c.type){case"b":n=parseInt(n,10).toString(2);break;case"c":n=String.fromCharCode(parseInt(n,10));break;case"d":case"i":n=parseInt(n,10);break;case"j":n=JSON.stringify(n,null,c.width?parseInt(c.width):0);break;case"e":n=c.precision?parseFloat(n).toExponential(c.precision):parseFloat(n).toExponential();break;case"f":n=c.precision?parseFloat(n).toFixed(c.precision):parseFloat(n);break;case"g":n=c.precision?String(Number(n.toPrecision(c.precision))):parseFloat(n);break;case"o":n=(parseInt(n,10)>>>0).toString(8);break;case"s":n=String(n),n=c.precision?n.substring(0,c.precision):n;break;case"t":n=String(!!n),n=c.precision?n.substring(0,c.precision):n;break;case"T":n=Object.prototype.toString.call(n).slice(8,-1).toLowerCase(),n=c.precision?n.substring(0,c.precision):n;break;case"u":n=parseInt(n,10)>>>0;break;case"v":n=n.valueOf(),n=c.precision?n.substring(0,c.precision):n;break;case"x":n=(parseInt(n,10)>>>0).toString(16);break;case"X":n=(parseInt(n,10)>>>0).toString(16).toUpperCase()}o.json.test(c.type)?m+=n:(!o.number.test(c.type)||f&&!c.sign?d="":(d=f?"+":"-",n=n.toString().replace(o.sign,"")),s=c.pad_char?"0"===c.pad_char?"0":c.pad_char.charAt(1):" ",u=c.width-(d+n).length,l=c.width&&u>0?s.repeat(u):"",m+=c.align?d+n+l:"0"===s?d+l+n:l+d+n)}return m}(function(t){if(c[t])return c[t];for(var e,n=t,r=[],i=0;n;){if(null!==(e=o.text.exec(n)))r.push(e[0]);else if(null!==(e=o.modulo.exec(n)))r.push("%");else{if(null===(e=o.placeholder.exec(n)))throw new SyntaxError("[sprintf] unexpected placeholder");if(e[2]){i|=1;var a=[],l=e[2],s=[];if(null===(s=o.key.exec(l)))throw new SyntaxError("[sprintf] failed to parse named argument key");for(a.push(s[1]);""!==(l=l.substring(s[0].length));)if(null!==(s=o.key_access.exec(l)))a.push(s[1]);else{if(null===(s=o.index_access.exec(l)))throw new SyntaxError("[sprintf] failed to parse named argument key");a.push(s[1])}e[2]=a}else i|=2;if(3===i)throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported");r.push({placeholder:e[0],param_no:e[1],keys:e[2],sign:e[3],pad_char:e[4],align:e[5],width:e[6],precision:e[7],type:e[8]})}n=n.substring(e[0].length)}return c[t]=r}(t),arguments)}function a(t,e){return i.apply(null,[t].concat(e||[]))}var c=Object.create(null);"undefined"!=typeof window&&(window.sprintf=i,window.vsprintf=a,void 0===(r=function(){return{sprintf:i,vsprintf:a}}.call(e,n,e,t))||(t.exports=r))}()}},e={};function n(r){var o=e[r];if(void 0!==o)return o.exports;var i=e[r]={exports:{}};return t[r](i,i.exports,n),i.exports}n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,{a:e}),e},n.d=function(t,e){for(var r in e)n.o(e,r)&&!n.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:e[r]})},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},function(){"use strict";var t=window.frmGlobal,e=t.url,r=(t.nonce,"frm_hidden"),o="frm-hide-js",i="frm-current";function a(t){return a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},a(t)}function c(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,l(r.key),r)}}function l(t){var e=function(t,e){if("object"!=a(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var r=n.call(t,"string");if("object"!=a(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"==a(e)?e:String(e)}var s=function(){function t(e){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"default";!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.elements=e,this.type=n,this.prepareElements()}var e,n;return e=t,n=[{key:"fadeIn",value:function(){var t=this;this.applyStyleToElements((function(e){e.classList.add("frm-fadein-up"),e.addEventListener("animationend",(function(){t.resetOpacity(),e.classList.remove("frm-fadein-up")}),{once:!0})}))}},{key:"cascadeFadeIn",value:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:.03;setTimeout((function(){t.applyStyleToElements((function(t,n){t.classList.remove("frm-animate"),t.style.transitionDelay=(n+1)*e+"s"}))}),200)}},{key:"prepareElements",value:function(){var t=this;this.applyStyleToElements((function(e){"default"===t.type&&(e.style.opacity="0.0"),"cascade"===t.type&&e.classList.add("frm-init-cascade-animation"),"cascade-3d"===t.type&&e.classList.add("frm-init-fadein-3d"),e.classList.add("frm-animate")}))}},{key:"resetOpacity",value:function(){this.applyStyleToElements((function(t){return t.style.opacity="1.0"}))}},{key:"applyStyleToElements",value:function(t){this.elements instanceof Element?t(this.elements,0):0<this.elements.length&&this.elements.forEach((function(e,n){return t(e,n)}))}}],n&&c(e.prototype,n),Object.defineProperty(e,"prototype",{writable:!1}),t}(),u=(Promise.resolve(),window.frmDom.util.onClickPreventDefault),f=(new URL(window.location.href).searchParams,function(t){var e;return null===(e=Array.from(t))||void 0===e?void 0:e.forEach((function(t){return p(t)}))}),d=function(t){var e;return null===(e=Array.from(t))||void 0===e?void 0:e.forEach((function(t){return y(t)}))},p=function(t){return null==t?void 0:t.classList.remove(r)},y=function(t){return null==t?void 0:t.classList.add(r)},m="frm-page-skeleton",b={ALL_ITEMS:"all-items"};function v(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function h(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?v(Object(n),!0).forEach((function(e){var r,o,i,a;r=t,o=e,i=n[e],a=function(t,e){if("object"!=g(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var r=n.call(t,"string");if("object"!=g(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(o),(o="symbol"==g(a)?a:String(a))in r?Object.defineProperty(r,o,{value:i,enumerable:!0,configurable:!0,writable:!0}):r[o]=i})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):v(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function g(t){return g="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},g(t)}var S,w,x,_,O=n(616),E=n.n(O);function j(t){return j="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},j(t)}function A(t,e,n){return r=function(t,e){if("object"!=j(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var r=n.call(t,"string");if("object"!=j(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(e),(e="symbol"==j(r)?r:String(r))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t;var r}n(604),E()(console.error),S={"(":9,"!":8,"*":7,"/":7,"%":7,"+":6,"-":6,"<":5,"<=":5,">":5,">=":5,"==":4,"!=":4,"&&":3,"||":2,"?":1,"?:":1},w=["(","?"],x={")":["("],":":["?","?:"]},_=/<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/;var P={"!":function(t){return!t},"*":function(t,e){return t*e},"/":function(t,e){return t/e},"%":function(t,e){return t%e},"+":function(t,e){return t+e},"-":function(t,e){return t-e},"<":function(t,e){return t<e},"<=":function(t,e){return t<=e},">":function(t,e){return t>e},">=":function(t,e){return t>=e},"==":function(t,e){return t===e},"!=":function(t,e){return t!==e},"&&":function(t,e){return t&&e},"||":function(t,e){return t||e},"?:":function(t,e,n){if(t)throw e;return n}};var k={contextDelimiter:"",onMissingKey:null};function C(t,e){var n;for(n in this.data=t,this.pluralForms={},this.options={},k)this.options[n]=void 0!==e&&n in e?e[n]:k[n]}function I(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function T(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?I(Object(n),!0).forEach((function(e){A(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):I(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}C.prototype.getPluralForm=function(t,e){var n,r,o,i,a=this.pluralForms[t];return a||("function"!=typeof(o=(n=this.data[t][""])["Plural-Forms"]||n["plural-forms"]||n.plural_forms)&&(r=function(t){var e,n,r;for(e=t.split(";"),n=0;n<e.length;n++)if(0===(r=e[n].trim()).indexOf("plural="))return r.substr(7)}(n["Plural-Forms"]||n["plural-forms"]||n.plural_forms),i=function(t){var e=function(t){for(var e,n,r,o,i=[],a=[];e=t.match(_);){for(n=e[0],(r=t.substr(0,e.index).trim())&&i.push(r);o=a.pop();){if(x[n]){if(x[n][0]===o){n=x[n][1]||n;break}}else if(w.indexOf(o)>=0||S[o]<S[n]){a.push(o);break}i.push(o)}x[n]||a.push(n),t=t.substr(e.index+n.length)}return(t=t.trim())&&i.push(t),i.concat(a.reverse())}(t);return function(t){return function(t,e){var n,r,o,i,a,c,l=[];for(n=0;n<t.length;n++){if(a=t[n],i=P[a]){for(r=i.length,o=Array(r);r--;)o[r]=l.pop();try{c=i.apply(null,o)}catch(t){return t}}else c=e.hasOwnProperty(a)?e[a]:+a;l.push(c)}return l[0]}(e,t)}}(r),o=function(t){return+i({n:t})}),a=this.pluralForms[t]=o),a(e)},C.prototype.dcnpgettext=function(t,e,n,r,o){var i,a,c;return i=void 0===o?0:this.getPluralForm(t,o),a=n,e&&(a=e+this.options.contextDelimiter+n),(c=this.data[t][a])&&c[i]?c[i]:(this.options.onMissingKey&&this.options.onMissingKey(n,t),0===i?n:r)};var L={"":{plural_forms:function(t){return 1===t?0:1}}},F=/^i18n\.(n?gettext|has_translation)(_|$)/,D=function(t){return"string"!=typeof t||""===t?(console.error("The namespace must be a non-empty string."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(t)||(console.error("The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."),!1)},q=function(t){return"string"!=typeof t||""===t?(console.error("The hook name must be a non-empty string."),!1):/^__/.test(t)?(console.error("The hook name cannot begin with `__`."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(t)||(console.error("The hook name can only contain numbers, letters, dashes, periods and underscores."),!1)},R=function(t,e){return function(n,r,o){var i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:10,a=t[e];if(q(n)&&D(r))if("function"==typeof o)if("number"==typeof i){var c={callback:o,priority:i,namespace:r};if(a[n]){var l,s=a[n].handlers;for(l=s.length;l>0&&!(i>=s[l-1].priority);l--);l===s.length?s[l]=c:s.splice(l,0,c),a.__current.forEach((function(t){t.name===n&&t.currentIndex>=l&&t.currentIndex++}))}else a[n]={handlers:[c],runs:0};"hookAdded"!==n&&t.doAction("hookAdded",n,r,o,i)}else console.error("If specified, the hook priority must be a number.");else console.error("The hook callback must be a function.")}},B=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return function(r,o){var i=t[e];if(q(r)&&(n||D(o))){if(!i[r])return 0;var a=0;if(n)a=i[r].handlers.length,i[r]={runs:i[r].runs,handlers:[]};else for(var c=i[r].handlers,l=function(t){c[t].namespace===o&&(c.splice(t,1),a++,i.__current.forEach((function(e){e.name===r&&e.currentIndex>=t&&e.currentIndex--})))},s=c.length-1;s>=0;s--)l(s);return"hookRemoved"!==r&&t.doAction("hookRemoved",r,o),a}}},M=function(t,e){return function(n,r){var o=t[e];return void 0!==r?n in o&&o[n].handlers.some((function(t){return t.namespace===r})):n in o}},N=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return function(r){var o=t[e];o[r]||(o[r]={handlers:[],runs:0}),o[r].runs++;for(var i=o[r].handlers,a=arguments.length,c=new Array(a>1?a-1:0),l=1;l<a;l++)c[l-1]=arguments[l];if(!i||!i.length)return n?c[0]:void 0;var s={name:r,currentIndex:0};for(o.__current.push(s);s.currentIndex<i.length;){var u=i[s.currentIndex].callback.apply(null,c);n&&(c[0]=u),s.currentIndex++}return o.__current.pop(),n?c[0]:void 0}},V=function(t,e){return function(){var n,r,o=t[e];return null!==(n=null===(r=o.__current[o.__current.length-1])||void 0===r?void 0:r.name)&&void 0!==n?n:null}},z=function(t,e){return function(n){var r=t[e];return void 0===n?void 0!==r.__current[0]:!!r.__current[0]&&n===r.__current[0].name}},$=function(t,e){return function(n){var r=t[e];if(q(n))return r[n]&&r[n].runs?r[n].runs:0}},U=new function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.actions=Object.create(null),this.actions.__current=[],this.filters=Object.create(null),this.filters.__current=[],this.addAction=R(this,"actions"),this.addFilter=R(this,"filters"),this.removeAction=B(this,"actions"),this.removeFilter=B(this,"filters"),this.hasAction=M(this,"actions"),this.hasFilter=M(this,"filters"),this.removeAllActions=B(this,"actions",!0),this.removeAllFilters=B(this,"filters",!0),this.doAction=N(this,"actions"),this.applyFilters=N(this,"filters",!0),this.currentAction=V(this,"actions"),this.currentFilter=V(this,"filters"),this.doingAction=z(this,"actions"),this.doingFilter=z(this,"filters"),this.didAction=$(this,"actions"),this.didFilter=$(this,"filters")},Z=(U.addAction,U.addFilter,U.removeAction,U.removeFilter,U.hasAction,U.hasFilter,U.removeAllActions,U.removeAllFilters,U.doAction,U.applyFilters,U.currentAction,U.currentFilter,U.doingAction,U.doingFilter,U.didAction,U.didFilter,U.actions,U.filters,function(t,e,n){var r=new C({}),o=new Set,i=function(){o.forEach((function(t){return t()}))},a=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"default";r.data[e]=T(T(T({},L),r.data[e]),t),r.data[e][""]=T(T({},L[""]),r.data[e][""])},c=function(t,e){a(t,e),i()},l=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default",e=arguments.length>1?arguments[1]:void 0,n=arguments.length>2?arguments[2]:void 0,o=arguments.length>3?arguments[3]:void 0,i=arguments.length>4?arguments[4]:void 0;return r.data[t]||a(void 0,t),r.dcnpgettext(t,e,n,o,i)},s=function(){return arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default"},u=function(t,e,r){var o=l(r,e,t);return n?(o=n.applyFilters("i18n.gettext_with_context",o,t,e,r),n.applyFilters("i18n.gettext_with_context_"+s(r),o,t,e,r)):o};if(n){var f=function(t){F.test(t)&&i()};n.addAction("hookAdded","core/i18n",f),n.addAction("hookRemoved","core/i18n",f)}return{getLocaleData:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"default";return r.data[t]},setLocaleData:c,resetLocaleData:function(t,e){r.data={},r.pluralForms={},c(t,e)},subscribe:function(t){return o.add(t),function(){return o.delete(t)}},__:function(t,e){var r=l(e,void 0,t);return n?(r=n.applyFilters("i18n.gettext",r,t,e),n.applyFilters("i18n.gettext_"+s(e),r,t,e)):r},_x:u,_n:function(t,e,r,o){var i=l(o,void 0,t,e,r);return n?(i=n.applyFilters("i18n.ngettext",i,t,e,r,o),n.applyFilters("i18n.ngettext_"+s(o),i,t,e,r,o)):i},_nx:function(t,e,r,o,i){var a=l(i,o,t,e,r);return n?(a=n.applyFilters("i18n.ngettext_with_context",a,t,e,r,o,i),n.applyFilters("i18n.ngettext_with_context_"+s(i),a,t,e,r,o,i)):a},isRTL:function(){return"rtl"===u("ltr","text direction")},hasTranslation:function(t,e,o){var i,a,c=e?e+""+t:t,l=!(null===(i=r.data)||void 0===i||null===(a=i[null!=o?o:"default"])||void 0===a||!a[c]);return n&&(l=n.applyFilters("i18n.has_translation",l,t,e,o),l=n.applyFilters("i18n.has_translation_"+s(o),l,t,e,o)),l}}}(0,0,U)),K=(Z.getLocaleData.bind(Z),Z.setLocaleData.bind(Z),Z.resetLocaleData.bind(Z),Z.subscribe.bind(Z),Z.__.bind(Z)),X=(Z._x.bind(Z),Z._n.bind(Z),Z._nx.bind(Z),Z.isRTL.bind(Z),Z.hasTranslation.bind(Z),window.frmDom),G=X.tag,J=X.div,H=X.a,Q=X.img;function W(t){return W="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},W(t)}function Y(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}var tt,et=document.getElementById("post-body-content"),nt=document.getElementById("".concat(m,"-sidebar")),rt=((tt=H({className:"button button-primary frm-button-primary"})).setAttribute("role","button"),J({id:"".concat(m,"-empty-state"),className:"frm-flex-col frm-flex-center frm-gap-md ".concat(r),children:[Q({src:"".concat(e,"/images/page-skeleton/empty-state.svg"),alt:K("Empty State","formidable")}),J({className:"frmcenter",children:[G("h2",{className:"".concat(m,"-title frm-mb-0")}),G("p",{className:"".concat(m,"-text frm-mb-0")})]}),tt]}));null==et||et.appendChild(rt);var ot=function(){var t=document.querySelector("#".concat(m,"-empty-state"));return{emptyState:t,emptyStateTitle:null==t?void 0:t.querySelector(".".concat(m,"-title")),emptyStateText:null==t?void 0:t.querySelector(".".concat(m,"-text")),emptyStateButton:null==t?void 0:t.querySelector(".button")}}(),it=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if("object"!==g(t)||null===t)throw new Error("createPageElements: initialElements must be a non-null object");var e=t;return{getElements:function(){return e},addElements:function(t){if("object"!==g(t)||null===t)throw new Error("addElements: newElements must be a non-null object");e=h(h({},e),t)}}}(function(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?Y(Object(n),!0).forEach((function(e){var r,o,i,a;r=t,o=e,i=n[e],a=function(t,e){if("object"!=W(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var r=n.call(t,"string");if("object"!=W(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(o),(o="symbol"==W(a)?a:String(a))in r?Object.defineProperty(r,o,{value:i,enumerable:!0,configurable:!0,writable:!0}):r[o]=i})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):Y(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}({bodyContent:et,sidebar:nt,searchInput:nt.querySelector(".frm-search-input"),categoryItems:nt.querySelectorAll(".".concat(m,"-cat")),allItemsCategory:nt.querySelector(".".concat(m,'-cat[data-category="').concat(b.ALL_ITEMS,'"]'))},ot)),at=it.getElements,ct=it.addElements;function lt(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function st(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?lt(Object(n),!0).forEach((function(e){var r,o,i,a;r=t,o=e,i=n[e],a=function(t,e){if("object"!=ut(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var r=n.call(t,"string");if("object"!=ut(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(o),(o="symbol"==ut(a)?a:String(a))in r?Object.defineProperty(r,o,{value:i,enumerable:!0,configurable:!0,writable:!0}):r[o]=i})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):lt(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function ut(t){return ut="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},ut(t)}var ft=at().allItemsCategory,dt=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if("object"!==ut(t)||null===t)throw new Error("createPageState: initialState must be a non-null object");var e=t;return{getState:function(){return e},getSingleState:function(t){var n=Reflect.get(e,t);return void 0===n?null:n},setState:function(t){if("object"!==ut(t)||null===t)throw new Error("setState: newState must be a non-null object");e=st(st({},e),t)},setSingleState:function(t,n){Reflect.has(e,t)&&Reflect.set(e,t,n)}}}({notEmptySearchText:!1,selectedCategory:b.ALL_ITEMS,selectedCategoryEl:ft}),pt=dt.getState,yt=dt.getSingleState,mt=dt.setState,bt=dt.setSingleState,vt=function(t){var e,n=t.currentTarget,r=n.getAttribute("data-category"),o=pt(),a=o.selectedCategory,c=o.selectedCategoryEl,l=o.notEmptySearchText;if(a!==r){a=wp.hooks.applyFilters("frmPage.selectedCategory",r),c.classList.remove(i),(c=n).classList.add(i),mt({selectedCategory:a,selectedCategoryEl:c}),l&&((e=at().searchInput).value="",e.dispatchEvent(new Event("input",{bubbles:!0}))),wp.hooks.doAction("frmPage.onCategoryClick",a);var u=at().bodyContent;new s(u).fadeIn()}};function ht(t){"Enter"!==t.key&&" "!==t.key||(t.preventDefault(),vt(t))}var gt=window.frmAddonsVars.addonRequestURL,St="frm-addons",wt={AVAILABLE:"available-addons",ACTIVE:"active-addons"},xt="basic",_t="plus",Ot="business",Et="elite",jt=at(),At=jt.bodyContent,Pt=jt.sidebar;At.querySelectorAll(".frm-card-item:not(.plugin-card-formidable-pro)").forEach((function(t){var e=t.dataset.categories;switch(!0){case e.includes(Ot):t.setAttribute("data-categories","".concat(e,",").concat(Et));break;case e.includes(_t):t.setAttribute("data-categories","".concat(e,",").concat(Ot,",").concat(Et));break;case e.includes(xt):t.setAttribute("data-categories","".concat(e,",").concat(_t,",").concat(Ot,",").concat(Et))}})),ct({upgradeBanner:document.getElementById("frm-upgrade-banner"),availableCategory:Pt.querySelector(".".concat(m,'-cat[data-category="').concat(wt.AVAILABLE,'"]')),activeCategory:Pt.querySelector(".".concat(m,'-cat[data-category="').concat(wt.ACTIVE,'"]')),categoriesTopDivider:Pt.querySelector(".".concat(m,"-divider")),basicPlanCategory:Pt.querySelector(".".concat(m,'-cat[data-category="').concat(xt,'"]')),plusPlanCategory:Pt.querySelector(".".concat(m,'-cat[data-category="').concat(_t,'"]')),businessPlanCategory:Pt.querySelector(".".concat(m,'-cat[data-category="').concat(Ot,'"]')),elitePlanCategory:Pt.querySelector(".".concat(m,'-cat[data-category="').concat(Et,'"]')),addonsList:document.getElementById("".concat(St,"-list")),addons:At.querySelectorAll(".frm-card-item"),availableAddons:At.querySelectorAll(".frm-card-item:not(.frm-locked-item)"),addonsToggle:At.querySelectorAll(".frm_toggle_block"),bodyContentChildren:null==At?void 0:At.children});var kt={};kt[wt.AVAILABLE]=[],kt[wt.ACTIVE]=[];var Ct,It=".".concat(m,"-cat-count");function Tt(){var t=at(),e=t.activeCategory,n=t.availableCategory,r=t.categoriesTopDivider,o=document.querySelectorAll(".frm-addon-active:not(.frm-locked-item)");0!==o.length?(kt[wt.ACTIVE]=o,e.querySelector(It).textContent=o.length):d([e,n,r])}function Lt(){var t=pt().selectedCategory,e=at(),n=e.emptyState,r=e.emptyStateButton,o=e.emptyStateTitle,i=e.emptyStateText;n.setAttribute("data-view",t),o.textContent=K("No add-ons found","formidable"),i.textContent=K("Sorry, we didn't find any add-ons that match your criteria.","formidable"),r.textContent=K("Request Add-On","formidable"),r.href=gt,r.setAttribute("target","_blank"),f([n,r])}function Ft(t,e){if(t){if("string"==typeof t)return Dt(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Dt(t,e):void 0}}function Dt(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}var qt=function(t){var e,n,r;if(null===(e=Ct)||void 0===e||!e.classList.contains("frm_loading_button")){var o,i=t.currentTarget.closest(".frm-card-item"),a=function(t,e){var n="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!n){if(Array.isArray(t)||(n=Ft(t))){n&&(t=n);var r=0,o=function(){};return{s:o,n:function(){return r>=t.length?{done:!0}:{done:!1,value:t[r++]}},e:function(t){throw t},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var i,a=!0,c=!1;return{s:function(){n=n.call(t)},n:function(){var t=n.next();return a=t.done,t},e:function(t){c=!0,i=t},f:function(){try{a||null==n.return||n.return()}finally{if(c)throw i}}}}(new Map([["frm-addon-not-installed",".frm-install-addon"],["frm-addon-installed",".frm-activate-addon"],["frm-addon-active",".frm-deactivate-addon"]]).entries());try{for(a.s();!(o=a.n()).done;){var c=(n=o.value,r=2,function(t){if(Array.isArray(t))return t}(n)||function(t,e){var n=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=n){var r,o,i,a,c=[],l=!0,s=!1;try{if(i=(n=n.call(t)).next,0===e){if(Object(n)!==n)return;l=!1}else for(;!(l=(r=i.call(n)).done)&&(c.push(r.value),c.length!==e);l=!0);}catch(t){s=!0,o=t}finally{try{if(!l&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(s)throw o}}return c}}(n,r)||Ft(n,r)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),l=c[0],s=c[1];if(i.classList.contains(l)){(Ct=i.querySelector(s)).click();break}}}catch(t){a.e(t)}finally{a.f()}}},Rt=window.frmDom.search.init;function Bt(t,e){var n=t.foundSomething,r=t.notEmptySearchText;if(!e||"search"!==e.type||""!==e.target.value){var o=pt(),a=at().allItemsCategory;bt("notEmptySearchText",r),o.notEmptySearchText||o.selectedCategory?(o.selectedCategory&&(function(t){var e=at(),n=e.bodyContent,r=e.bodyContentChildren,o=e.addonsList;t&&yt("selectedCategoryEl").classList.remove(i),d(r),p(o),new s(n).fadeIn()}(r),r&&bt("selectedCategory","")),function(t){if(t){var e=at().emptyState;n=e,"none"!==window.getComputedStyle(n).getPropertyValue("display")&&y(e)}else Lt();var n}(n)):a.dispatchEvent(new Event("click",{bubbles:!0}))}}var Mt;function Nt(){var t;at().categoryItems.forEach((function(t){u(t,vt),t.addEventListener("keydown",ht)})),wp.hooks.addAction("frmPage.onCategoryClick","frmAddonsPage",(function(t){!function(t){var e=at(),n=e.bodyContent,r=e.addons,o=e.emptyState,i=e.upgradeBanner;y(o),p(i),n.dataset.category=t,b.ALL_ITEMS!==t?(d(r),0===kt[t].length?(Lt(),y(i)):f(kt[t])):f(r)}(t)})),at().addonsToggle.forEach((function(t){return t.addEventListener("click",qt)})),wp.hooks.addAction("frm_update_addon_state","frmAddonPage",(function(){Tt()})),t=at().searchInput,Rt(t,"frm-card-item",{handleSearchResult:Bt})}Mt=function(){(function(){var t,e,n,r,i,a,c,l,u,f,d=at(),y=d.sidebar,m=d.searchInput,b=d.bodyContent,v=new s(b);m.value="",Tt(),function(){var t=at(),e=t.bodyContent,n=t.availableCategory,r=t.availableAddons;if(kt[wt.AVAILABLE]=r,n){n.querySelector(It).textContent=r.length;var o=e.querySelector(".".concat(St,"-available-count"));o&&(o.textContent=r.length)}}(),function(){var t=at(),e=t.allItemsCategory,n=t.addons;e.querySelector(It).textContent=n.length}(),e=(t=at()).basicPlanCategory,n=t.plusPlanCategory,r=t.businessPlanCategory,i=t.elitePlanCategory,c=(a=function(t){return parseInt(t.querySelector(It).textContent,10)||0})(e),l=a(n)-1,u=a(r)-1,f=a(i)-1,n.querySelector(It).textContent=c+l,r.querySelector(It).textContent=c+l+u,i.querySelector(It).textContent=c+l+u+f,b.classList.remove(o),y.classList.remove(o),v.fadeIn(),p(y)})(),at().addons.forEach((function(t){var e=t.getAttribute("data-categories");e&&e.split(",").forEach((function(e){kt[e]||(kt[e]=[]),kt[e].push(t)}))})),Nt()},"undefined"!=typeof document&&("complete"!==document.readyState&&"interactive"!==document.readyState?document.addEventListener("DOMContentLoaded",Mt):Mt())}()}();