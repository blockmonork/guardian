/**
 * 
 * funcoes originais do login
 * editar aqui e colar no fn.js e depois minificar
 * 
  */
const _d = document;
var _a = (n) => atob(_v_[n]);
var _g = (i) => _d.getElementById(i);
var _s = (i) => CryptoJS.SHA256(_g(i).value);
var _u = (n) => n.substring(1, n.length);


//(function teste() { alert(_a(6)); })();

function sb() {
    if (cl1()) {
        _g(_u(_v_[2])).value = _s(_v_[2]);
        _g(_v_[2]).value = '';
        _g(_u(_v_[3])).value = _s(_v_[3]);
        _g(_v_[3]).value = '';
        document.forms[_v_[4]].submit();
    }
}

function shp(e, t) {
    _g(e + "_helper").setAttribute("data-error", t), _g(e + "_helper").setAttribute("data-success", t)
}


function cl1() {
    var e = _g(_v_[2]).value,
        t = _g(_v_[3]).value,
        s = !0;
    return e.length < _v_[0] || e.length > _v_[1]
        ? (shp(_v_[2], `user ${_a(6)}`), s = !1)
        : shp(_v_[2], "right"), t.length < _v_[0] || t.length > _v_[1]
            ? (shp(_v_[3], `pass ${_a(6)}`), s = !1)
            : shp(_v_[3], "right"), !!s && cp1()
}

function cp1() {
    var e = _g(_v_[3]).value;
    return /[A-Z]/.test(e)
        ? /[a-z]/.test(e)
            ? /[0-9]/.test(e)
                ? /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(e)
                    ? (shp(_v_[3], "right"), !0)
                    : (shp(_v_[3], _a(7)), !1)
                : (shp(_v_[3], _a(8)), !1)
            : (shp(_v_[3], _a(9)), !1)
        : (shp(_v_[3], _a(10)), !1)
}
//
document.querySelector("#" + _v_[2]).focus(), document.querySelector("#" + _v_[2]).addEventListener("change", function (e) {
    cl1()
}), document.querySelector("#" + _v_[3]).addEventListener("change", function (e) {
    cl1()
});
$(document).ready(function () {
    const inames = 'input#' + _v_[2] + ', input#' + _v_[3];
    $(inames).characterCounter();
})

// generate a javascript function to send an ajax post request to index.php


