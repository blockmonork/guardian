<?php

namespace Core;

/**
 * 
 * @author FAFM & copilot :) (nice help bro)
 * @version 1.0
 * @license GNU GENERAL PUBLIC LICENSE 3
 * @access public
 * 
 * 
 */

// start the session
session_start();

class Guardian
{

    private static $_VERSION = '1.0';

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


    // set the post username and password using in the form fields
    private static $FORM_USERNAME = 'user';
    private static $FORM_PASSWORD = 'pass';
    // hidden field form_token_name
    private static $FORM_TOKEN_NAME = '_ftn_';
    // all alphabetical and numeric characters for created tokens
    private static $ALPHANUMERIC = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    // setting the maxlength for post user and pass
    private static $MAX_LENGTH = 20;

    // if ALLOW_LOGIN_FAIL is false, this session variable will store the number of failed login attempts
    private static $LOGIN_FAIL_TIMES = 0;

    // if ALLOW_LOGIN_FAIL is false, then override the $LOGIN_FAIL_TIMES and ban the user IP after 1st failed login attempt
    private static $ALLOW_LOGIN_FAIL = true;

    // the guardian banishement file stores the banned IPs | banned IPs are stored in the file in the format:
    // IP,BAN_TIME,BAN_EXPIRES
    private static $BAN_FILE = 'guardianBan';

    // the banishment time in seconds that the IP will be banned
    private static $BAN_TIME = 60;

    // get contents of the file guardianBan.txt and stores in format ip,ban_time,ban_expires
    private static $BAN_FILE_ARRAY = array();

    // the login page
    private static $LOGIN_PAGE = 'login.php';

    // the main page of the site (which contains the Guardian's settings)
    private static $MAIN_PAGE = 'index.php';

    // the logged page to redirect to after login
    private static $LOGGED_PAGE = 'hello.php';

    // use anti brute force?
    private static $ANTI_BRUTE_FORCE = true;

    // anti bruce force time in seconds
    private static $ANTI_BRUTE_FORCE_TIME = 1;

    // the title for the login LOGIN_PAGE
    private static $LOGIN_PAGE_TITLE = "Guardian Login";




    // getters and setters


    // get the version
    public static function getVersion()
    {
        return self::$_VERSION;
    }

    // function to set the login page
    /**
     * @param string $page the login page
     * @return void
     */
    public static function setLoginPage(string $page)
    {
        self::$LOGIN_PAGE = $page;
    }

    // setting the login page title
    /**
     * @param string $title the title for the login page
     * @return void
     */
    public static function setLoginPageTitle(string $title)
    {
        self::$LOGIN_PAGE_TITLE = $title;
    }
    public static function getLoginPageTitle()
    {
        return self::$LOGIN_PAGE_TITLE;
    }

    // setting the main page of the site
    /**
     * @param string $mainPage the main page of the site
     * @return void
     */
    public static function setMainPage(string $mainPage)
    {
        self::$MAIN_PAGE = $mainPage;
    }
    public static function getMainPage()
    {
        return self::$MAIN_PAGE;
    }

    // setting the logged page to redirect to after login
    /**
     * @param string $loggedPage the logged page to redirect to after login
     * @return void
     */
    public static function setLoggedPage(string $loggedPage)
    {
        self::$LOGGED_PAGE = $loggedPage;
    }
    public static function getLoggedPage()
    {
        return self::$LOGGED_PAGE;
    }

    // setting the form fields for username and password
    /**
     * @param string $username the username form field
     * @param string $password the password form field
     * @return void
     */
    public static function setFormFields(string $username, string $password)
    {
        self::$FORM_USERNAME = $username;
        self::$FORM_PASSWORD = $password;
    }
    public static function getFormFields()
    {
        return array(self::$FORM_USERNAME, self::$FORM_PASSWORD);
    }

    // make the form token name with 20 random characters from ALPHANUMERIC and set it to the form token input hidden field
    /**
     * @return string html hidden input for the form token
     */
    public static function getFormToken()
    {
        $token = '';
        for ($i = 0; $i < 20; $i++) {
            $token .= self::$ALPHANUMERIC[rand(0, strlen(self::$ALPHANUMERIC) - 1)];
        }
        // before return, save the token to the session variable strtoupper form_token_name if its not already set
        $key = strtoupper(self::$FORM_TOKEN_NAME);
        if ( !self::getSessVar($key)) {
            self::setSessVar($key, $token);
        }

        return '<input type="hidden" name="' . self::$FORM_TOKEN_NAME . '" value="' . $token . '">' . "\n";
    }

    // setting the maxlength for username and password fields
    /**
     * @param int $maxlength the maxlength for username and password fields
     * @return void
     */
    public static function setFormMaxLength(int $maxlength)
    {
        self::$MAX_LENGTH = $maxlength;
    }
    public static function getFormMaxLength()
    {
        return self::$MAX_LENGTH;
    }

    // seting banishement time in seconds
    /**
     * @param int $time the banishement time in seconds
     * @return void
     */
    public static function setBanishmentTime(int $time)
    {
        self::$BAN_TIME = $time;
    }
    public static function getBanishmentTime()
    {
        return self::$BAN_TIME;
    }

    // set use of anti bruce force
    /**
     * @param bool $use true to use anti bruce force, false to not use
     * @return void
     */
    public static function setAntiBruteForce(bool $val)
    {
        self::$ANTI_BRUTE_FORCE = $val;
    }

    // set anti bruce force time
    /**
     * @param int $time the anti bruce force time in seconds
     * @return void
     */
    public static function setAntiBruteForceTime(int $time)
    {
        self::$ANTI_BRUTE_FORCE_TIME = $time;
    }

    // settings for authentication mode to gathering credentials from the user
    /**
     * @param int $mode the authentication mode (1.hardcoded | 2.env)
     * @return void
     */
    public static function setCredentialsMode(int $mode)
    {
        self::getAntiBruteForceTime();
        self::$CREDENTIALS_MODE = $mode;
        // store credentials in session credentials mode sesison var 
        self::setSessVar('credentials_mode', $mode);
    }

    // set hardcoded credentials (not recommended)
    /**
     * @param string $user the hardcoded username
     * @param string $pass the hardcoded password
     * @return void
     */
    public static function setHardCredentials(string $user, string $pass)
    {
        self::$HARD_USER = $user;
        self::$HARD_PASS = $pass;
        self::setHardCredentialsSession();
    }

    // init gathering credentials from the user and other checks 
    /**
     * iniciate the authentication process and other checks
     * @return void
     */
    public static function initCredentials()
    {
        self::getAntiBruteForceTime();
        // before, check if the user IP is banned
        if (self::is_banned()) {
            exit('You are not allowed to access this Page.');
        }
        self::$CREDENTIALS_MODE = self::getSessVar('credentials_mode');
        if (self::$CREDENTIALS_MODE == 1) {
            self::getHardCredentialsSession();
            self::$USER = self::$HARD_USER;
            self::$PASS = self::$HARD_PASS;
        } else {
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
    }

    // function to set allowLoginFail
    /**
     * @param bool $val true to allow login failure, false to not allow
     * @return void
     */
    public static function setAllowLoginFail(bool $allow)
    {
        self::getAntiBruteForceTime();
        self::$ALLOW_LOGIN_FAIL = $allow;
    }

    // function to set the login failure times before die
    /**
     * @param int $times the login failure times before die
     * @return void
     */
    public static function setLoginFailTimes(int $times)
    {
        self::getAntiBruteForceTime();
        self::$LOGIN_FAIL_TIMES = $times;
    }

    // function to return session LOGIN_FAIL_TIMES
    /**
     * @return int the session LOGIN_FAIL_TIMES
     */
    public static function getLoginFailTimes()
    {
        $x = self::getSessVar('LFT');
        return ($x) ? intval($x) : 0;
    }


    // function that checks if the user is logged in
    /**
     * @return bool true if the user is logged in, false if not
     */
    public static function is_logged_in()
    {
        self::initCredentials();
        $su = self::getSessVar('ses_user');
        $sp = self::getSessVar('ses_pass');
        $st = self::getSessVar('ses_token');
        return ($su == self::$USER && $sp == self::$PASS && $st)  ? true : false;
    }

    // function that logs the user in
    /**
     * function that logs the user in
     * @return bool true if the user is logged in, false if not
     */
    public static function login()
    {
        // check brute force
        if (self::$ANTI_BRUTE_FORCE) {
            if (self::isBruteForce()) {
                die('Brute force detected. Please try again later.');
            }
        }
        $post_user = self::getPostUser();
        $post_pass = self::getPostPass();
        $post_token = self::getPostToken();
        self::initCredentials();
        if ($post_user && $post_pass && $post_token) {
            if ($post_user == self::$USER && $post_pass == self::$PASS && $post_token == self::getSessionToken()) {
                // if the user and pass are correct, set the session variables
                self::setSessVar('ses_user', $post_user);
                self::setSessVar('ses_pass', $post_pass);
                self::setSessVar('ses_token', self::gen_token());
                return true;
            }
        }
        return false;
    }

    // function that logs the user out
    /**
     * function that logs the user out
     * @return void
     */
    public static function logout()
    {
        $unset_sessions = [
            'ses_user', 
            'ses_pass', 
            'ses_token', 
            'LFT', 
            'ABFT', 
            'credentials_mode', 
            strtoupper(self::$FORM_TOKEN_NAME)
        ];
        foreach ($unset_sessions as $session) {
            self::unsetSessVar($session);
        }
    }

    // if login fails, die!
    /**
     * if login fails, die!
     * @return void
     */
    public static function die_if_not_logged_in()
    {
        self::ban_user();
        die("You are not logged in!");
    }

    // check if the form has submitted the fields specified in array
    /**
     * check if the form has submitted the fields specified in array
     * @param array $fields the fields to check
     * @return bool true if the form has submitted the fields specified in array, false if not
     */
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
     * function that includes a file through the require function
     * @param string $file the file to include
     * @return void
     */
    public static function require_page(string $page)
    {
        $page = self::remove_http($page);
        if (file_exists($page)) {
            require_once($page);
        } else {
            die("The page you are looking for does not exist!");
        }
    }

    // redirects to the specified page
    /**
     * redirects to the specified page
     * @param string $page the page to redirect to
     * @return void
     */
    public static function redirect(string $page)
    {
        $page = self::remove_http($page);
        if (!file_exists($page)) {
            die("The page you are looking for does not exist!");
        }
        header("Location: $page");
        exit;
    }

    // start monitoring the requests
    /**
     * start monitoring the requests
     * @return void
     */
    public static function start()
    {
        // check if user is banned first of all
        if (self::is_banned()) {
            die("You are not allowed to access this page.");
        }
        // and then, check for logout request
        if (isset($_GET['logout'])) {
            self::logout();
            self::redirect(self::$MAIN_PAGE);      
        }
        // checks whether the form submitted the post user and post pass
        if (self::has_form_submitted([self::$FORM_USERNAME, self::$FORM_PASSWORD])) {
            // if the form submitted the post user and post pass, check if the user and pass are correct
            if (self::login()) {
                // if the user and pass are correct, redirect to the main page
                self::redirect(self::$LOGGED_PAGE);
            } else {
                // reset session token before redirect
                self::resetSessionToken();
                if (self::$LOGIN_FAIL_TIMES > 0 && self::$ALLOW_LOGIN_FAIL) {
                    if ( self::checkLoginFailTimes() ){
                        self::redirect(self::$MAIN_PAGE);
                    }
                } else if (!self::$ALLOW_LOGIN_FAIL) {
                    self::logout();
                    self::die_if_not_logged_in();
                }
            }
        } else {
            if (!self::is_logged_in()) {
                self::require_page(self::$LOGIN_PAGE);
            } else {
                self::redirect(self::$LOGGED_PAGE);
            }
        }
    }


    /////////////////////////////////////// PRIVATE METHODS //////////////////////////////////


    // set hardcoded credentials in session variables
    /**
     * @return void
     */
    private static function setHardCredentialsSession()
    {
        self::setSessVar('HU', self::$HARD_USER);
        self::setSessVar('HP', self::$HARD_PASS);
    }
    // get hardcoded credentials from session variables
    /**
     * @return array the hardcoded credentials
     */
    private static function getHardCredentialsSession()
    {
        list(self::$HARD_USER, self::$HARD_PASS) = array(self::getSessVar('HU'), self::getSessVar('HP'));
    }


    // get variable value from session
    /**
     * @param string $key session variable name
     * @return array|bool the hardcoded credentials session variable if is set or false otherwise
     */
    private static function getSessVar($k)  
    {
        return (isset($_SESSION[$k]) ) ? $_SESSION[$k]: false;
    }
    // set variable value in session
    /**
     * @param string $key session variable name
     * @param string $value session variable value
     * @return void
     */
    private static function setSessVar($k, $v)
    {
        $_SESSION[$k] = $v;
    }
    // unset variable value in session
    /**
     * @param string $key session variable name
     * @return void
     */
    private static function unsetSessVar($k)
    {
       if(self::getSessVar($k)) unset($_SESSION[$k]);
    }

    // get the $_SERVER key
    /**
     * get the $_SERVER key
     * @param string $key the key to get
     * @return string the value of the key
     */
    private static function getServer(string $key)
    {
        $key = strtoupper($key);
        return (isset($_SERVER[$key])) ? $_SERVER[$key] : '';
    }

    // generate function thar removes http:// or https:// or www. from url
    /**
     * generate function thar removes http:// or https:// or www. from url
     * @param string $url the url to remove the http:// or https:// or www. from
     * @return string the url without http:// or https:// or www.
     */
    private static function remove_http(string $_file)
    {
        $disallowed = array('http://', 'https://', 'www.');
        $file = trim($_file);
        foreach ($disallowed as $d) {
            $file = str_replace($d, '', $file);
        }
        return $file;
    }

    // check post origin
    /**
     * check post origin
     * @return bool true if the post origin is valid, false if not
     */
    private static function isPostOrigin()
    {
        $keys = ['http_host', 'http_origin', 'http_referer', 'request_method'];
        $vals = [];
        foreach ($keys as $key) {
            $temp = self::getServer($key);
            if ($temp == '') {
                return false;
            }
            array_push($vals, $temp);
        }
        list($h, $o, $r, $m) = $vals;
        $h = basename($h);
        $o = basename($o);
        $r = explode('/', self::remove_http($r))[0];
        if ($h == $o && $o == $r && $m == 'POST') {
            return true;
        }
        return false;
    }

    // get the post username from form
    /**
     * get the post username from form
     * @return string if the post username is valid, false if not
     */
    private static function getPostUser()
    {
        return (isset($_POST[self::$FORM_USERNAME]) && !empty($_POST[self::$FORM_USERNAME])) ? self::sanitize_post($_POST[self::$FORM_USERNAME]) : false;
    }

    // get the password from form
    /**
     * get the password from form
     * @return string if the password is valid, false if not
     */
    private static function getPostPass()
    {
        return (isset($_POST[self::$FORM_PASSWORD]) && !empty($_POST[self::$FORM_PASSWORD])) ? self::sanitize_post($_POST[self::$FORM_PASSWORD]) : false;
    }

    // get the post token from form
    /**
     * get the post token from form
     * @return string if the post token is valid, false if not
     */
    private static function getPostToken()
    {
        return (isset($_POST[self::$FORM_TOKEN_NAME]) && !empty($_POST[self::$FORM_TOKEN_NAME])) ? self::sanitize_post($_POST[self::$FORM_TOKEN_NAME]) : false;
    }
    // get session token from strtoupper form_token_name
    /**
     * get session token from strtoupper form_token_name
     * @return string if the session token is valid, false if not
     */
    private static function getSessionToken()
    {
        $key = strtoupper(self::$FORM_TOKEN_NAME);
        $token = self::getSessVar($key);
        return ($token) ? self::sanitize_post($token) : false;
    }
    // reset session token from strtoupper form_token_name
    /**
     * reset session token from strtoupper form_token_name
     * @return void
     */
    private static function resetSessionToken()
    {
        unset($_SESSION[strtoupper(self::$FORM_TOKEN_NAME)]);
    }

    // ignore post user and pass characters beyond the max length
    /**
     * ignore post user and pass characters beyond the max length and remove invalid characters
     * @param string $post the post to sanitize
     * @return string the sanitized post
     */
    private static function sanitize_post(string $val)
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
    /**
     * start the anti brute force
     * @return void
     */
    private static function startAntiBruteForceTimer()
    {
        // store the time in session variable
        self::setSessVar('ABFT', time() + self::$ANTI_BRUTE_FORCE_TIME);
    }

    // get brute force time from session variable
    /**
     * get brute force time from session variable
     * @return int the time in seconds
     */
    private static function getAntiBruteForceTime()
    {
        $abft = self::getSessVar('ABFT');
        if (!$abft) {
            self::startAntiBruteForceTimer();
            return $_SESSION['ABFT'];
        } else {
            return $abft;
        }
    }

    // check if the user is brute force
    /**
     * check if the user is brute force
     * @return bool true if the user is brute force, false if not
     */
    private static function isBruteForce()
    {
        return (time() < self::getAntiBruteForceTime()) ? true : false;
    }

    // function that checks the LFT session variable and increments the login failure times
    /**
     * function that checks the LFT session variable and increments the login failure times
     * @return void
     */
    private static function checkLoginFailTimes()
    {
        // get the current session LFT session variable
        $times = self::getLoginFailTimes();
        // increments the login failure times
        $times++;
        // sets the LFT session variable to the new value
        self::setSessVar('LFT', $times);
        // checks if the login failure times is greater than the max login failure times
        if ($times >= self::$LOGIN_FAIL_TIMES) {
            // ban the user
            self::ban_user();
            // if the login failure times is greater than the max login failure times, die
            die('You have exceeded the maximum number of login attempts. Please try again later.');
            //die("debug fail times " . self::$LOGIN_FAIL_TIMES);
        }
        return true;
    }

    // function that generates a random string and returns it
    /**
     * function that generates a random string and returns it
     * @param int $length the length of the random string
     * @return string the random string
     */
    private static function gen_token()
    {
        return md5(uniqid(rand(), true));
    }

    // get ban_file and store it in an array(ip => [ban_time, ban_expires])
    /**
     * get ban_file and store it in an array(ip => [ban_time, ban_expires])
     * @return array the ban_file
     */
    private static function get_ban_file()
    {
        if (count(self::$BAN_FILE_ARRAY) == 0) {
            $ban_file = self::$BAN_FILE;
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
    }

    // check if the user is banned
    /**
     * check if the user is banned
     * @return bool true if the user is banned, false if not
     */
    private static function is_banned()
    {
        self::get_ban_file();
        $ip = self::getServer('remote_addr');
        if ( empty($ip) ){
            die("dont you have an IP?! ");
        }
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
    /**
     * write the ban file
     * @return void
     */
    private static function write_ban_file()
    {
        $ban_file = self::$BAN_FILE;
        // if count BAN_FILE_ARRAY is 0, then delete the ban_file
        if (count(self::$BAN_FILE_ARRAY) == 0) {
            if (file_exists($ban_file)) {
                unlink($ban_file);
            }
        } else {
            $file_handle = fopen($ban_file, 'w');
            foreach (self::$BAN_FILE_ARRAY as $ip => $ban_array) {
                fwrite($file_handle, $ip . ',' . $ban_array[0] . ',' . $ban_array[1] . "\n");
            }
            fclose($file_handle);
        }
    }

    // ban the user
    /**
     * ban the user
     * @return void
     */
    private static function ban_user()
    {
        if (!self::is_banned()) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ban_file = self::$BAN_FILE;
            self::$BAN_FILE_ARRAY[$ip] = array(time(), time() + self::$BAN_TIME);
            $file_handle = fopen($ban_file, 'w');
            foreach (self::$BAN_FILE_ARRAY as $ip => $ban_array) {
                fwrite($file_handle, $ip . ',' . $ban_array[0] . ',' . $ban_array[1] . "\n");
            }
            fclose($file_handle);
        }
    }
}
