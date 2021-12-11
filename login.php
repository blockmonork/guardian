<?php

use Core\Guardian;

$f = $guardian->get_html_form_infos();
$inputUser = $f['username'];
$inputPass = $f['password'];
$formName = $f['formname'];
$hiddenTokenInput = $f['token'];
$startTime = $f['start_time'];
$loginPageTitle = $f['login_page_title'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $loginPageTitle; ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" media="screen,projection" type="text/css">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .containerLg {
            margin-top: 50px;
            margin-right: auto;
            margin-bottom: auto;
            margin-left: auto;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            background-color: #efefef;
        }

        h3 {
            text-shadow: 1px 2px 0 grey;
            margin-top: -5px
        }

        pre {
            background-color: #efefef;
            padding: 10px;
            border-radius: 10px;
            width: 500px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="containerLg container">
        <h3 class="valign-wrapper">
            <i class="material-icons left">lock</i>
            <?php echo $loginPageTitle; ?>
        </h3>
        <div class="row">
            <form name="<?php echo $formName; ?>" class="col s12" action="index.php" method="post">
                <?php 
                echo $hiddenTokenInput; 
                echo $startTime;
                ?>
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">account_circle</i>
                        <input id="<?php echo $inputUser; ?>" name="<?php echo $inputUser; ?>" type="text" class="validate" autocomplete="username" required maxlength="50" data-length="50">
                        <label for="<?php echo $inputUser; ?>">Login</label>
                        <span id="<?php echo $inputUser; ?>_helper" class="helper-text" data-error="wrong" data-success="right"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">verified_user</i>
                        <input id="<?php echo $inputPass; ?>" name="<?php echo $inputPass; ?>" type="password" class="validate" autocomplete="new-password" required maxlength="50" data-length="50">
                        <label for="<?php echo $inputPass; ?>">Password</label>
                        <span id="<?php echo $inputPass; ?>_helper" class="helper-text" data-error="wrong" data-success="right"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <span onclick="sbmt()" id="btn-submit" class="btn btn-large waves-effect waves-light col s12">
                            login
                            <i class="material-icons left">lock_open</i>
                        </span>
                    </div>
                </div>
                <?php
                $flash_msg = $guardian->get_flash();
                if ( $flash_msg != '' ) :
                ?>
                <div class="row">
                    <div class="col s12">
                        <div class="badge red">
                            <span class="white-text">
                                <h5 class="center-align"><?php echo $flash_msg?></h5>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        var MiL = 4;
        var MxL = 50;
        var U = "<?php echo $inputUser; ?>";
        var P = "<?php echo $inputPass; ?>"; 
        var msgLen = `must be between ${MiL} and ${MxL} characters`;
        function sbmt() {
            if (checkLogin()) {
                document.getElementById(P).value = CryptoJS.SHA256(document.getElementById(P).value);
                document.getElementById(U).value = CryptoJS.SHA256(document.getElementById(U).value);
                document.forms["<?php echo $formName ?>"].submit()
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
    </script>
</body>

</html>