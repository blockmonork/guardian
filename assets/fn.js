var msgLen=`must be between ${_v_[0]} and ${_v_[1]} characters`;const _d=document;function sbmt(){if(checkLogin()){_g(_v_[2]).value=_s(_v_[2]);_g(_v_[3]).value=_s(_v_[3]);document.forms[_v_[4]].submit()}}
function _g(i){return _d.getElementById(i)}
function setHelper(e,t){_g(e+"_helper").setAttribute("data-error",t),_g(e+"_helper").setAttribute("data-success",t)}
function _s(i){return CryptoJS.SHA256(_g(i).value)}
function checkLogin(){var e=_g(_v_[2]).value,t=_g(_v_[3]).value,s=!0;return e.length<_v_[0]||e.length>_v_[1]?(setHelper(_v_[2],`user ${msgLen}`),s=!1):setHelper(_v_[2],"right"),t.length<_v_[0]||t.length>_v_[1]?(setHelper(_v_[3],`pass ${msgLen}`),s=!1):setHelper(_v_[3],"right"),!!s&&checkPass()}
function checkPass(){var e=_g(_v_[3]).value;return/[A-Z]/.test(e)?/[a-z]/.test(e)?/[0-9]/.test(e)?/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(e) ? (setHelper(_v_[3], "right"), !0) : (setHelper(_v_[3], "pass must contain at least one special character"), !1) : (setHelper(_v_[3], "pass must contain at least one number"), !1) : (setHelper(_v_[3], "pass must contain at least one lowercase letter"), !1) : (setHelper(_v_[3], "pass must contain at least one uppercase letter"), !1)
 }
 //
 document.querySelector("#" + _v_[2]).focus(), document.querySelector("#" + _v_[2]).addEventListener("change", function (e) {
     checkLogin()
 }), document.querySelector("#" + _v_[3]).addEventListener("change", function (e) {
     checkLogin()
 });
 $(document).ready(function () {
     const inames = 'input#' + _v_[2] + ',input#'+_v_[3];$(inames).characterCounter()})