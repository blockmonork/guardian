<?php
header('Content-Type:text/html; charset=UTF-8');

use Core\Guardian;

// require class
require('Guardian.php');
// setAntiBruteForce && setAntiBruceForceTime must be set first of all
// set Guardian::setCredentialsMode (1:hardcoded, 2:env [default]) must be set before Guardian::init()
// must be the 1st call if you want to use hardcoded credentials
// Guardian::init();


//Guardian::logout(); exit("ok"); // manual stops all

// allows login fail? if false, exit after 1st fail
// @override MAX_LOGIN_FAIL
$ALLOWS_LOGIN_FAIL = false;

Guardian::setAllowLoginFail($ALLOWS_LOGIN_FAIL);

// if MAX_LOGIN_FAIL > 0, then the login will be blocked if the user fails to login MAX_LOGIN_FAIL times
$MAX_LOGIN_FAIL = 3;

if ($MAX_LOGIN_FAIL > 0) {
    Guardian::setLoginFailTimes($MAX_LOGIN_FAIL);
}

// setting the page to logged user in
$LOGGED_PAGE = 'hello.php';

// setting the login page (root/login.php) default.
$LOGIN_PAGE = 'login.php';
Guardian::setLoginPage($LOGIN_PAGE);

// the default names for form fields for username and password are user, pass
// you can change them by setting the following
Guardian::setFormFields('user', 'pass');


// logout
if (isset($_GET['logout'])) {
    Guardian::logout();
    // quick reload page
    header("Location: index.php");
}


// checks whether the form submitted the post user and post pass
if (Guardian::has_form_submitted(['user', 'pass'])) {
    // if the user and pass are correct, log the user in
    if (Guardian::login()) {
        //Guardian::require_page($LOGGED_PAGE);
        Guardian::redirect($LOGGED_PAGE);
    } else {
        // if login fails, die!
        if (!$ALLOWS_LOGIN_FAIL || ($MAX_LOGIN_FAIL > 0 && Guardian::getLoginFailTimes() >= $MAX_LOGIN_FAIL)) {
            Guardian::die_if_not_logged_in();
        } else {
            Guardian::redirect_if_not_valid_token();
        }
    }
} else {
    if (!Guardian::is_logged_in()) {
        Guardian::redirect_if_not_valid_token();
    } else {
        //Guardian::require_page($LOGGED_PAGE);
        Guardian::redirect($LOGGED_PAGE);
    }
}
