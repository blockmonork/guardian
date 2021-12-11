/**
 * 
 * 
 * funcoes originais do login
 * editar aqui e colar no login.php
 * 
 * 
 */

var MiL = 4;
var MxL = 50;
var U = ''; /*   AQUI EH PHP "<?php echo $inputUser; ?>"; */
var P = ''; /*   AQUI EH PHP  "<?php echo $inputPass; ?>"; */
 var msgLen = `must be between ${MiL} and ${MxL} characters`;

 function sbmt() {
     if (checkLogin()) {
         document.getElementById(P).value = CryptoJS.SHA256(document.getElementById(P).value);
         document.getElementById(U).value = CryptoJS.SHA256(document.getElementById(U).value);
         document.forms["<?php echo $formName?>"].submit()
     }
 }

 function setHelper(e, t) {
     document.getElementById(e + "_helper").setAttribute("data-error", t), document.getElementById(e + "_helper").setAttribute("data-success", t)
 }

 function checkLogin() {
     var e = document.getElementById(U).value,
         t = document.getElementById(P).value,
         s = !0;
     return e.length < MiL || e.length > MxL ? (setHelper(U, `user ${msgLen}`), s = !1) : setHelper(U, "right"), t.length < MiL || t.length > MxL ? (setHelper(P, `pass ${msgLen}`), s = !1) : setHelper(P, "right"), !!s && checkPass()
 }

 function checkPass() {
     var e = document.getElementById(P).value;
     return /[A-Z]/.test(e) ? /[a-z]/.test(e) ? /[0-9]/.test(e) ? /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(e) ? (setHelper(P, "right"), !0) : (setHelper(P, "pass must contain at least one special character"), !1) : (setHelper(P, "pass must contain at least one number"), !1) : (setHelper(P, "pass must contain at least one lowercase letter"), !1) : (setHelper(P, "pass must contain at least one uppercase letter"), !1)
 }
 //
 document.querySelector("#" + U).focus(), document.querySelector("#" + U).addEventListener("change", function(e) {
     checkLogin()
 }), document.querySelector("#" + P).addEventListener("change", function(e) {
     checkLogin()
 });
 $(document).ready(function() {
     const inames = 'input#' + U + ', input#' + P;
     $(inames).characterCounter();
 })
