<?php

use Core\Guardian;

$f = $guardian->get_html_form_infos();
$inputUser = $f['username'];
$inputPass = $f['password'];
$formName = $f['formname'];
$hiddenTokenInput = $f['token'];
$startTime = $f['start_time'];
$loginPageTitle = $f['login_page_title'];
// base_dir to include files
$d = $guardian->get('base_dir') . 'assets/';
$files = [
    'jquery' => 'jq.js', 
    'google_fonts' => 'gf.css', 
    'materialize_css' => 'mt.css', 
    'materialize_js' => 'mt.js', // materialize
    'crypto' => 'cr.js', // crypto
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="<?php echo $d?>favicon.png">
    <title><?php echo $loginPageTitle; ?></title>

    <link href="<?php echo $d . $files['google_fonts']; ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $d . $files['materialize_css']; ?>" media="screen,projection" type="text/css">

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
            width: 50%;
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
        const _v_ = [4, 50, "<?php echo $inputUser?>", "<?php echo $inputPass?>", "<?php echo $formName?>"];
    </script>
    <script type="text/JavaScript" src="<?php echo $d . $files['jquery']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['materialize_js']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['crypto']; ?>"></script>

    <script type="text/JavaScript" src="<?php echo $d; ?>fn.js"></script>
</body>

</html>