<?php

use Core\Guardian;

// require class
require('Guardian.php');

// first of all, output header Information
Guardian::printHeader();

// setAntiBruteForce must be set first of all if you don't want to use the anti brute force feature (default is true)
# Guardian::setAntiBruteForce(true);

// if you want to use the anti brute force feature, you must set setAntiBruceForceTime to the time in seconds (default is 1)
# Guardian::setAntiBruteForceTime(1);

// set Guardian::setCredentialsMode (1:hardcoded, 2:env [default]) 
# Guardian::setCredentialsMode(1);

// if credentials mode is set to 1, Guardian::setHardCredentials must be set 
# Guardian::setHardCredentials('your_user', 'your_pass'); //('your_user', 'your_pass');

// seting banishement time in seconds (how long the banishment takes) (default: 60)
# Guardian::setBanishmentTime(60);

// setting the maxlength of the form fields (default: 20 characters)
# Guardian::setFormMaxLength(20);

// setting the field names for the login form page.
// the default names are: user, pass
// you can change them by setting the following
# Guardian::setFormFields('user', 'pass');


// allows login fail? if false, exit after 1st fail try (default: true)
// @override LOGIN_FAIL_TIMES
$ALLOW_LOGIN_FAIL = true;
Guardian::setAllowLoginFail($ALLOW_LOGIN_FAIL);


// setting how many tries before banishment (default: 3)
// if MAX_LOGIN_FAIL > 0, then the login will be blocked if the user fails to login MAX_LOGIN_FAIL times
$LOGIN_FAIL_TIMES = 3;

if ($LOGIN_FAIL_TIMES > 0) {
    Guardian::setLoginFailTimes($LOGIN_FAIL_TIMES);
}

// setting the page to logged user in (relative path to Guardian.php) (default ./hello.php)
// look at the hello.php file for more information about the use of the guardianMonitor.php file
$LOGGED_PAGE = 'hello.php';
Guardian::setLoggedPage($LOGGED_PAGE);

// setting the login page (relative path to Guardian.php) (default: ./login.php)
// if you choose different page, look at the code of the login page to see how to use Guardian
$LOGIN_PAGE = 'login.php';
Guardian::setLoginPage($LOGIN_PAGE);

// set login page title
$LOGIN_PAGE_TITLE = 'Guardian ' . Guardian::getVersion() . ' Login';
Guardian::setLoginPageTitle($LOGIN_PAGE_TITLE);

// setting the main page (this one) (relative path to Guardian.php) (default ./index.php)
$MAIN_PAGE = 'index.php';
Guardian::setMainPage($MAIN_PAGE);

// start the Guardian
Guardian::start();
