<?php

use Core\Guardian;

if (!isset($guardian)) exit;

$debugando = 0; // usado para setar fn.js(0) || login_funcs.js(1) e login_css em files

$f = $guardian->get_html_form_infos();
$inputUser = $f['username'];
$inputPass = $f['password'];
$formName = $f['formname'];
$required_hidden_fields = $f['required_hidden_fields'];
$loginPageTitle = $f['login_page_title'];
// base_dir to include files
$d = $guardian->get('base_dir') . 'assets/';

$login_funcs = [
    'fn.js',
    'login_funcs.js'
];
$login_css = [
    'c.css',
    'login_css.css',
];

$files = [
    'jquery' => 'jq.js',
    'google_fonts' => 'gf.css',
    'materialize_css' => 'mt.css',
    'materialize_js' => 'mt.js', 
    'crypto' => 'cr.js', 
    'c_css' => $login_css[$debugando], // login styles
    'fn' => $login_funcs[$debugando],
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="<?php echo $d ?>favicon.png">
    <title><?php echo $loginPageTitle; ?></title>

    <link href="<?php echo $d . $files['google_fonts']; ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $d . $files['materialize_css']; ?>" media="screen,projection" type="text/css">
    <link rel="stylesheet" type="text/css" href="<?php echo $d . $files['c_css']; ?>">
</head>

<body>
    <div class="containerLg container">
        <h3 class="valign-wrapper">
            <i class="material-icons left">lock</i>
            <?php echo $loginPageTitle; ?>
            <span>
                <?php echo $guardian->vs; ?>  
            </span>
        </h3>
        <div class="row">
            <form name="<?php echo $formName; ?>" class="col s12" action="index.php" method="post">
                <?php
                echo $required_hidden_fields;
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
                        <span onclick="sb()" id="btn-submit" class="btn btn-large waves-effect waves-light col s12">
                            login
                            <i class="material-icons left">lock_open</i>
                        </span>
                    </div>
                </div>
                <?php
                $flash_msg = $guardian->get_flash();
                if ($flash_msg != '') :
                ?>
                    <div class="row">
                        <div class="col s12">
                            <div class="badge red">
                                <span class="white-text">
                                    <h5 class="center-align"><?php echo $flash_msg ?></h5>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
        <?php
        /*
        sequencia de const _v_:
        0 - min char length
        1 - max char length
        2 - fake inputname | real input hidden = fake_input.substr(1, fake_input.length)
        3 - fake inputpass | real idem a 2
        4 - form name
        5 - msg bogus - soh pra confundir :)
        6 - mensagem padrao de strlen
        7 - msg_especial_char
        8 - msg_number_required
        9 - msg_lowerCase
        10 - msg_upperCase
        */
        $min_len = 4;
        $minLen = '"' . base64_encode($min_len) . '"';
        $max_len = 50;
        $maxLen = '"' . base64_encode($max_len) . '"';
        $user = '"'.$inputUser.'"';
        $pass = '"' . $inputPass . '"';
        $form = '"' . $formName . '"';
        $bogus = '"' . md5('a1.'.time()) . '"';
        $msgStrLen = '"' . base64_encode("must be between $min_len and $max_len characters") . '"';
        $msgEspChar = '"' . base64_encode("pass must contain at least one special character") . '"';
        $msgNum = '"' . base64_encode("pass must contain at least one number") . '"';
        $msgLow = '"' . base64_encode("pass must contain at least one lowercase letter") . '"';
        $msgUp = '"' . base64_encode("pass must contain at least one uppercase letter") . '"';


        $so = '<script>';
        $sc = '</script>';
        echo $so . 'const _v_ = ['.$minLen.', '.$maxLen.', '.$user.', '.$pass.', '.$form.', '.$bogus.', '.$msgStrLen.', '.$msgEspChar.', '.$msgNum.', '.$msgLow.', '.$msgUp.']; ' . $sc;
        ?>
    <script type="text/JavaScript" src="<?php echo $d . $files['jquery']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['materialize_js']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['crypto']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['fn']; ?>"></script>
</body>

</html>