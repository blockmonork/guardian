<?php
header('Content-type:text/html; charset=UTF-8');

use Core\Guardian;


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Guardian::getLoginPageTitle(); ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" media="screen,projection" type="text/css">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
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
    </style>
</head>

<body>
    <div class="containerLg container">
        <h3 class="valign-wrapper">
            <i class="material-icons left">lock</i>
            <?php echo Guardian::getLoginPageTitle(); ?>
        </h3>
        <div class="row">
            <form class="col s12" action="index.php" method="post" onsubmit="return checkLogin();">
                <?php echo Guardian::getFormToken(); ?>
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">account_circle</i>
                        <input id="<?php echo Guardian::getFormFields()[0]; ?>" name="<?php echo Guardian::getFormFields()[0]; ?>" type="text" class="validate" autocomplete="username" required maxlength="<?php echo Guardian::getFormMaxLength(); ?>" data-length="<?php echo Guardian::getFormMaxLength(); ?>">
                        <label for="user">First Name</label>
                        <span id="user_helper" class="helper-text" data-error="wrong" data-success="right"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">verified_user</i>
                        <input id="<?php echo Guardian::getFormFields()[1]; ?>" name="<?php echo Guardian::getFormFields()[1]; ?>" type="password" class="validate" autocomplete="new-password" required maxlength="<?php echo Guardian::getFormMaxLength(); ?>" data-length="<?php echo Guardian::getFormMaxLength(); ?>">
                        <label for="pass">Password</label>
                        <span id="pass_helper" class="helper-text" data-error="wrong" data-success="right"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <button type="submit" onclick="sdmt()" id="btn-submit" class="btn btn-large waves-effect waves-light col s12">
                            login
                            <i class="material-icons left">lock_open</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        var MinLen = 4;
        var MaxLen = <?php echo Guardian::getFormMaxLength(); ?>;
        var U = "<?php echo Guardian::getFormFields()[0]; ?>";
        var P = "<?php echo Guardian::getFormFields()[1]; ?>";
        var msgLen = `must be between ${MinLen} and ${MaxLen} characters`;

        function sbmt() {
            checkLogin() && document.forms[0].submit()
        }

        function setHelper(e, t) {
            document.getElementById(e + "_helper").setAttribute("data-error", t), document.getElementById(e + "_helper").setAttribute("data-success", t)
        }

        function checkLogin() {
            var e = document.getElementById(U).value,
                t = document.getElementById(P).value,
                s = !0;
            return e.length < MinLen || e.length > MaxLen ? (setHelper("user", `user ${msgLen}`), s = !1) : setHelper("user", "right"), t.length < MinLen || t.length > MaxLen ? (setHelper("pass", `pass ${msgLen}`), s = !1) : setHelper("pass", "right"), !!s && checkPass()
        }

        function checkPass() {
            var e = document.getElementById(P).value;
            return /[A-Z]/.test(e) ? /[a-z]/.test(e) ? /[0-9]/.test(e) ? /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(e) ? (setHelper("pass", "right"), !0) : (setHelper("pass", "pass must contain at least one special character"), !1) : (setHelper("pass", "pass must contain at least one number"), !1) : (setHelper("pass", "pass must contain at least one lowercase letter"), !1) : (setHelper("pass", "pass must contain at least one uppercase letter"), !1)
        }
        document.querySelector("#user").focus(), document.querySelector("#" + U).addEventListener("change", function(e) {
            checkLogin()
        }), document.querySelector("#" + P).addEventListener("change", function(e) {
            checkLogin()
        });
        $(document).ready(function(){
            $('input#user, input#pass').characterCounter();
        })
    </script>
</body>

</html>