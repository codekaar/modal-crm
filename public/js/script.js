(function () {
    'use strict';

    /**
     * Source: https://github.com/janl/mustache.js/blob/master/mustache.js
     */
    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };
    window.escapeHtml = function (string) {
        return String(string).replace(/[&<>"'`=\/]/g, function fromEntityMap (s) {
            return entityMap[s];
        });
    };

})();

// https://github.com/hustcc/timeago.js/blob/master/gh-pages/timeago.min.js
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?e(exports):"function"==typeof define&&define.amd?define(["exports"],e):e(t.timeago={})}(this,function(t){"use strict";var f=[60,60,24,7,365/7/12,12],o=function(t){return parseInt(t)},n=function(t){return t instanceof Date?t:!isNaN(t)||/^\d+$/.test(t)?new Date(o(t)):(t=(t||"").trim().replace(/\.\d+/,"").replace(/-/,"/").replace(/-/,"/").replace(/(\d)T(\d)/,"$1 $2").replace(/Z/," UTC").replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2"),new Date(t))},s=function(t,e){for(var n=0,r=t<0?1:0,a=t=Math.abs(t);f[n]<=t&&n<f.length;n++)t/=f[n];return(0===(n*=2)?9:1)<(t=o(t))&&(n+=1),e(t,n,a)[r].replace("%s",t)},d=function(t,e){return((e=e?n(e):new Date)-n(t))/1e3},r="second_minute_hour_day_week_month_year".split("_"),a="秒_分钟_小时_天_周_个月_年".split("_"),e=function(t,e){if(0===e)return["just now","right now"];var n=r[parseInt(e/2)];return 1<t&&(n+="s"),["".concat(t," ").concat(n," ago"),"in ".concat(t," ").concat(n)]},i={en_US:e,zh_CN:function(t,e){if(0===e)return["刚刚","片刻后"];var n=a[parseInt(e/2)];return["".concat(t," ").concat(n,"前"),"".concat(t," ").concat(n,"后")]}},c=function(t){return i[t]||e},l="timeago-tid",u=function(t,e){return t.getAttribute?t.getAttribute(e):t.attr?t.attr(e):void 0},p=function(t){return u(t,l)},_={},v=function(t){clearTimeout(t),delete _[t]},h=function t(e,n,r,a){v(p(e));var o=d(n,a);e.innerHTML=s(o,r);var i,c,u=setTimeout(function(){t(e,n,r,a)},1e3*function(t){for(var e=1,n=0,r=Math.abs(t);f[n]<=t&&n<f.length;n++)t/=f[n],e*=f[n];return r=(r%=e)?e-r:e,Math.ceil(r)}(o),2147483647);_[u]=0,c=u,(i=e).setAttribute?i.setAttribute(l,c):i.attr&&i.attr(l,c)};t.version="4.0.0-beta.2",t.format=function(t,e,n){var r=d(t,n);return s(r,c(e))},t.render=function(t,e,n){var r;void 0===t.length&&(t=[t]);for(var a=0;a<t.length;a++){r=t[a];var o=u(r,"datetime"),i=c(e);h(r,o,i,n)}return t},t.cancel=function(t){if(t)v(p(t));else for(var e in _)v(e)},t.register=function(t,e){i[t]=e},Object.defineProperty(t,"__esModule",{value:!0})});
