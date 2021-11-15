<?php

namespace Core;

/**
 * 
 * @author FAFM & copilot :) (nice help bro)
 * @version 1.0
 * @license GNU GENERAL PUBLIC LICENSE 3
 * @access public
 * 
 * see at the end of this file for more info
 * 
 */

// start the session
session_start();

class Guardian
{

    // credentials mode ( 1.hardcoded | 2.env )
    private static $CREDENTIALS_MODE = 2;
    // if $CREDENTIALS_MODE == 2 ('env'), so
    // /etc/apache2/envvars must be edited to include the following line:
    // export GUARDIANUSER=your_user_name
    // export GUARDIANPASS=your_password

    // the user and pass hardcoded credentials
    private static $HARD_USER = 'your_user_name';
    private static $HARD_PASS = 'your_password';

    // the user and pass variables
    private static $USER = '';
    private static $PASS = '';
    private static $SALT = '';

    // set the post username and password using in the form fields
    private static $FORM_USERNAME = 'user';
    private static $FORM_PASSWORD = 'pass';

    // setting the maxlength for post user and pass
    private static $MAX_LENGTH = 20;

    // if ALLOW_LOGIN_FAIL is false, this session variable will store the number of failed login attempts
    private static $LOGIN_FAIL_TIMES = 0;

    // if ALLOW_LOGIN_FAIL is false, then override the $LOGIN_FAIL_TIMES and ban the user IP after 1st failed login attempt
    private static $ALLOW_LOGIN_FAIL = true;

    // the guardian banishement file (guardianBan.txt) - stores the banned IPs | banned IPs are stored in the file in the format:
    // IP,BAN_TIME,BAN_EXPIRES
    private static $BAN_FILE = 'guardianBan.txt';

    // the time in seconds that the IP will be banned
    private static $BAN_DURATION = 60;

    // get contents of the file guardianBan.txt and stores in format ip,ban_time,ban_expires
    private static $BAN_FILE_ARRAY = array();

    // the login page
    private static $LOGIN_PAGE = 'login.php';

    // use anti brute force?
    private static $ANTI_BRUTE_FORCE = true;

    // anti bruce force time in seconds
    private static $ANTI_BRUTE_FORCE_TIME = 1;


    // getters and setters

    // setting the form fields for username and password
    public static function setFormFields($username, $password)
    {
        self::$FORM_USERNAME = $username;
        self::$FORM_PASSWORD = $password;
    }

    // set use of anti bruce force
    public static function setAntiBruteForce($val)
    {
        self::$ANTI_BRUTE_FORCE = $val;
    }

    // set anti bruce force time
    public static function setAntiBruteForceTime($time)
    {
        self::$ANTI_BRUTE_FORCE_TIME = $time;
    }

    // settings for authentication mode to gathering credentials from the user
    public static function setCredentialsMode($mode)
    {
        self::getAntiBruteForceTime();
        self::$CREDENTIALS_MODE = $mode;
        self::init();
    }

    // set hardcoded credentials (not recommended)
    public static function setHardCredentials($user, $pass)
    {
        self::$HARD_USER = $user;
        self::$HARD_PASS = $pass;
    }

    // init gathering credentials from the user and other checks 
    public static function init()
    {
        self::ini();
    }

    // function to set the login page
    public static function setLoginPage($page)
    {
        self::getAntiBruteForceTime();
        self::$LOGIN_PAGE = $page;
    }

    // function to set the login failure times before die
    public static function setLoginFailTimes($times)
    {
        self::getAntiBruteForceTime();
        self::$LOGIN_FAIL_TIMES = intval($times);
    }

    // function to set allowLoginFail
    public static function setAllowLoginFail($allow)
    {
        self::getAntiBruteForceTime();
        self::$ALLOW_LOGIN_FAIL = (bool)$allow;
    }

    // function to return session LOGIN_FAIL_TIMES
    public static function getLoginFailTimes()
    {
        return (isset($_SESSION['LFT'])) ? intval($_SESSION['LFT']) : 0;
    }


    // checks login, is_logged_id, logout

    // function that checks if the user is logged in
    public static function is_logged_in()
    {
        self::init();
        if (isset($_SESSION['ses_user']) && isset($_SESSION['ses_pass']) && isset($_SESSION['ses_token'])) {
            if ($_SESSION['ses_user'] == self::$USER && $_SESSION['ses_pass'] == self::$PASS) {
                return true;
            }
        }
        return false;
    }

    // function that logs the user in
    public static function login()
    {
        /* //debug values for post user and pass and self user and pass characters
        echo 'post user('.$_POST['user'].'), post pass('.$_POST['pass'].'), self user('.self::$USER.'), self pass('.self::$PASS.')';
        die; */
        // check brute force
        if (self::$ANTI_BRUTE_FORCE) {
            if (self::isBruteForce()) {
                die('Brute force detected. Please try again later.');
            }
        }
        self::init();
        if (!self::isPostOrigin()) {
            die('Invalid origin.');
        }
        $post_user = self::getPostUser();
        $post_pass = self::getPostPass();
        if ($post_user && $post_pass) {
            if ($post_user == self::$USER && $post_pass == self::$PASS) {
                // if the user and pass are correct, set the session variables
                $_SESSION['ses_token'] = self::gen_token();
                $_SESSION['ses_user'] = self::$USER;
                $_SESSION['ses_pass'] = self::$PASS;
                return true;
            } else {
                if (self::$LOGIN_FAIL_TIMES > 0 && self::$ALLOW_LOGIN_FAIL) {
                    self::checkLoginFailTimes();
                } else if (!self::$ALLOW_LOGIN_FAIL) {
                    self::logout();
                    self::die_if_not_logged_in();
                }
            }
        }
        return false;
    }

    // function that logs the user out
    public static function logout()
    {
        if (self::is_logged_in()) {
            unset($_SESSION['ses_user']);
            unset($_SESSION['ses_pass']);
            unset($_SESSION['ses_token']);
            // unset LOGIN_FAIL_TIMES
            unset($_SESSION['LFT']);
            // unset ABFT
            unset($_SESSION['ABFT']);
            return true;
        }
        return false;
    }

    // function that checks if the user is logged in and if the token is valid
    public static function is_valid_token()
    {
        if (self::is_logged_in()) {
            if (isset($_SESSION['ses_token'])) {
                if ($_SESSION['ses_token'] == self::$SALT) {
                    return true;
                }
            }
        }
        return false;
    }

    // if the user is not logged in or if the token is invalid, redirect to the login page
    public static function redirect_if_not_valid_token()
    {
        if (!self::is_valid_token()) {
            // include page instead of redirect because of
            // the possibility of a redirect loop            
            self::require_page(self::$LOGIN_PAGE);
        }
    }

    // if login fails, die!
    public static function die_if_not_logged_in()
    {
        self::ban_user();
        die("You are not logged in!");
    }

    // check if the form has submitted the fields specified in array
    public static function has_form_submitted(array $fields)
    {
        self::getAntiBruteForceTime();
        if (!self::isPostOrigin()) {
            return false;
        }
        $total = count($fields);
        $submitted = 0;
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && !empty($_POST[$field])) {
                $submitted++;
            }
        }
        return ($submitted == $total) ? true : false;
    }

    // function that includes a file through the require function
    /**
     * @param string $file path/and/file to the file to be included in the page
     */
    public static function require_page($page)
    {

        if (file_exists($page)) {
            require($page);
        } else {
            die("The page you are looking for does not exist!");
        }
    }

    // redirects to the specified page
    public static function redirect($page)
    {
        $page = self::remove_http($page);
        if (!file_exists($page)) {
            die("The page you are looking for does not exist!");
        }
        header("Location: $page");
        exit;
    }

    /////////////////////////////////////// PRIVATE METHODS //////////////////////////////////

    // generate function thar removes http:// or https:// or www. from url
    private static function remove_http($_file)
    {
        $disallowed = array('http://', 'https://', 'www.');
        $file = trim($_file);
        foreach ($disallowed as $d) {
            $file = str_replace($d, '', $file);
        }
        return $file;
    }

    // check post origin
    private static function isPostOrigin()
    {
        $h = basename($_SERVER['HTTP_HOST']);
        $o = basename($_SERVER['HTTP_ORIGIN']);
        $r = explode('/', self::remove_http($_SERVER['HTTP_REFERER']))[0];
        $m = $_SERVER['REQUEST_METHOD'];
        if ($h == $o && $o == $r && $m == 'POST') {
            return true;
        }
        return false;
    }

    // get the post username from form
    private static function getPostUser()
    {
        return (isset($_POST[self::$FORM_USERNAME]) && !empty($_POST[self::$FORM_USERNAME])) ? self::sanitize_post($_POST[self::$FORM_USERNAME]) : false;
    }

    // get the password from form
    private static function getPostPass()
    {
        return (isset($_POST[self::$FORM_PASSWORD]) && !empty($_POST[self::$FORM_PASSWORD])) ? self::sanitize_post($_POST[self::$FORM_PASSWORD]) : false;
    }

    // ignore post user and pass characters beyond the max length
    private static function sanitize_post($val)
    {
        $val = substr($val, 0, self::$MAX_LENGTH);
        $unwanted_chars = [
            '"',
            '<',
            '>',
            '&',
            '\'',
            '\\',
            '\r',
            '\n',
            '\t',
            ';',
        ];
        foreach ($unwanted_chars as $k) {
            $val = str_replace($k, '', $val);
        }
        return $val;
    }

    // start the anti brute force
    private static function startAntiBruteForceTimer()
    {
        // store the time in session variable
        $_SESSION['ABFT'] = time() + self::$ANTI_BRUTE_FORCE_TIME;
    }

    // get brute force time from session variable
    private static function getAntiBruteForceTime()
    {
        $abft = (isset($_SESSION['ABFT'])) ? $_SESSION['ABFT'] : false;
        if (!$abft) {
            self::startAntiBruteForceTimer();
            return $_SESSION['ABFT'];
        } else {
            return $abft;
        }
    }

    // check if the user is brute force
    private static function isBruteForce()
    {
        return (time() < self::getAntiBruteForceTime()) ? true : false;
    }

    // init gathering credentials from the user
    private static function ini()
    {
        self::getAntiBruteForceTime();
        // before, check if the user IP is banned
        if (self::is_banned()) {
            exit('You are not allowed to access this Page.');
        }
        if (self::$CREDENTIALS_MODE == 1) {
            self::$USER = self::$HARD_USER;
            self::$PASS = self::$HARD_PASS;
        } elseif (self::$CREDENTIALS_MODE == 2) {
            self::$USER = getenv('GUARDIANUSER');
            self::$PASS = getenv('GUARDIANPASS');
        }
        // cleanning empty spaces in values
        self::$USER = trim(self::$USER);
        self::$PASS = trim(self::$PASS);
        // if credentials are not set, then exit
        if (self::$USER == '' || self::$PASS == '') {
            exit('Credentials not set');
        }
        //echo 'user('.self::$USER.'), pass('.self::$PASS.')'; exit;
    }

    // function that checks the LFT session variable and increments the login failure times
    private static function checkLoginFailTimes()
    {
        // get the current session LFT session variable
        $times = self::getLoginFailTimes();
        // increments the login failure times
        $times++;
        // sets the LFT session variable to the new value
        $_SESSION['LFT'] = $times;
        // checks if the login failure times is greater than the max login failure times
        if ($times > self::$LOGIN_FAIL_TIMES) {
            // ban the user
            self::ban_user();
            // if the login failure times is greater than the max login failure times, die
            die('You have exceeded the maximum number of login attempts. Please try again later.');
            //die("debug fail times " . self::$LOGIN_FAIL_TIMES);
        }
    }

    // function that generates a random string and returns it
    private static function gen_token()
    {
        $token = md5(uniqid(rand(), true));
        self::$SALT = $token;
        return $token;
    }

    // get ban_file and store it in an array(ip => [ban_time, ban_expires])
    private static function get_ban_file()
    {
        $ban_file = self::$BAN_FILE;
        self::$BAN_FILE_ARRAY = array();
        if (file_exists($ban_file)) {
            $file_handle = fopen($ban_file, 'r');
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $line_array = explode(',', $line);
                if (count($line_array) == 3) {
                    self::$BAN_FILE_ARRAY[$line_array[0]] = array($line_array[1], $line_array[2]);
                }
            }
            fclose($file_handle);
        }
    }

    // check if the user is banned
    private static function is_banned()
    {
        self::get_ban_file();
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset(self::$BAN_FILE_ARRAY[$ip])) {
            if (self::$BAN_FILE_ARRAY[$ip][1] > time()) {
                return true;
            } else {
                // ban time is over. remove the user from the file and return false
                unset(self::$BAN_FILE_ARRAY[$ip]);
                self::write_ban_file();
                return false;
            }
        }
        return false;
    }

    // write the ban file
    private static function write_ban_file()
    {
        $ban_file = self::$BAN_FILE;
        $file_handle = fopen($ban_file, 'w');
        foreach (self::$BAN_FILE_ARRAY as $ip => $ban_array) {
            fwrite($file_handle, $ip . ',' . $ban_array[0] . ',' . $ban_array[1] . "\n");
        }
        fclose($file_handle);
    }

    // ban the user
    private static function ban_user()
    {
        if (!self::is_banned()) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ban_file = self::$BAN_FILE;
            self::$BAN_FILE_ARRAY[$ip] = array(time(), time() + self::$BAN_DURATION);
            $file_handle = fopen($ban_file, 'w');
            foreach (self::$BAN_FILE_ARRAY as $ip => $ban_array) {
                fwrite($file_handle, $ip . ',' . $ban_array[0] . ',' . $ban_array[1] . "\n");
            }
            fclose($file_handle);
        }
    }
}

/**
 * how to use the class
 * example: file 1 - index.php (will handle the requests)
 * example: file 2 - login.php (login form action to index.php)
 * example: file 3 - hello.php (visible only for logged users)
 * 
 * 

header('Content-Type:text/html; charset=UTF-8');

use Core\Guardian;

// require class
require('Guardian.php');
// setAntiBruteForce && setAntiBruceForceTime must be set first of all if you want to use the anti brute force feature
// set Guardian::setCredentialsMode (1:hardcoded, 2:env [default]) must be set before Guardian::init()
// if credentials mode is set to 1, Guardian::setHardCredentials must be set before Guardian::init()
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

 * 
 * 
 */
