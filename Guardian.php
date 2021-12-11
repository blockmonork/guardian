<?php

namespace Core;

session_start();

class Guardian
{


    private $Defaults = [];
    private $Definitions = [];
    private $always_refresh = [];

    private $debug = false;
    private $debug_msg = [];


    public function __construct($debug = false, $refreshSession = false)
    {
        $this->debug = $debug;
        if ( $refreshSession && $this->is_session_set('start_time')){
            $this->destroy_session();
        }

        $this->always_refresh = [
            'http_response_code',
            'html_form_start_time',
            'html_form_tokenvalue',
            'logged_in',
            'posts',
            'gets',
        ];

        $this->Defaults = [

            /* * temporizadores * */

            'start_time' => time(),  // 1 per session'
            'html_form_start_time' => time(), // always renew'
            'brute_force_time' => 1,  // html_form_start_time - start_time <= brute_force_time = ban!
            'banishment_expires' => 60, // segundos de banimento

            /* * html form * */

            'html_form_username' => '__AUTO__', // random_string [ 1 per session ]
            'html_form_password' => '__AUTO__', // idem
            'html_form_formname' => '__AUTO__', // idem
            'html_form_tokenname' => '__AUTO__', // idem
            'html_form_tokenvalue' => '__AUTO__', // sha1 always renew

            /* * validadores * */

            'host' => '__AUTO__', // $_SERVER['HTTP_HOST']
            'user' => '', // getenv ou set hardcoded
            'pass' => '', // getenv ou set hardcoded
            // /etc/apache2/envvars must be edited to include the following line:
            // export GUARDIANUSER=your_user_name
            // export GUARDIANPASS=your_password
            'token' => '', // sha1 1 per session
            'credentials_method' => 'getenv', // getenv ou set hardcoded
            'logged_in' => false, // true 1 per session

            /* * controle de tentativa de logins * */
            'max_fail' => 3,
            'count_fail' => 0,

            /* * paginas * */
            'main_page' => 'path/to/file/index.php', // quem instancia e configura a classe
            'login_page' => 'path/to/file/login.php',
            'login_page_title' => 'Guardian Login',
            'logout_page' => 'path/to/file/index.php', // geralmente o mesmo valor de main_page 
            // (mas se logged_page estiver num nivel diferente de main_page, logout_page deve setar o caminho relativo)
            'logout_uri_name' => 'logout',
            'logged_page' => 'path/to/file/hello.php',


            /*  * arquivos * */
            'log' => 'path/to/file/guardian_log.log',
            'ban_file' => 'path/to/file/guardian_ban_file',


            /* * html response code * */
            'http_response_code' => 200,

            /* * POSTS & GETS * */
            'posts' => [],
            'gets' => [],
        ];
    }


    // ******* USER METHODS *******
    public function setup($array_user_definition = [])
    {
        foreach ($this->Defaults as $key => $value) {
            if ($key != 'posts' && $key != 'gets') {
                if (!isset($array_user_definition[$key])) {
                    $this->Definitions[$key] = $value;
                } else {
                    $this->Definitions[$key] = $array_user_definition[$key];
                }
                $m = "$key = " . $this->Definitions[$key];
                $this->set_debug($m, 'setup');
            }
        }
        return $this;
    }
    public function print_header()
    {
        $this->printHeader();
        return $this;
    }
    public function start()
    {
        // inits begin...
        // /*
        $this->init_load();
        $this->init_temporizadores();
        $this->init_html_form_validators();
        $this->set_session('max_fail', $this->Definitions['max_fail']);
        $this->set_session('count_fail', 0);
        $this->init_pages();
        $this->set_session('log', $this->format_path($this->Definitions['log']));
        $this->set_session('ban_file', $this->format_path($this->Definitions['ban_file']));
        $this->set_session('http_response_code', $this->Definitions['http_response_code']);
        $this->init_posts();
        $this->init_gets();
        // */

        if ($this->debug) {
            $this->get_debug();
            echo "<br>";
            foreach ($this->Defaults as $key => $value) {
                echo $key . ' = ' .  $this->get_session($key) . '<br>';
            }
        }

        return $this;
    }
    public function monitore()
    {
        $this->init_load();
        $g = $this->get_session('logout_uri_name');
        if (isset($_GET[$g])) {
            $this->logout();
        }
        // check ban
        if ($this->is_ban()) {
            $this->error('Banned');
        }
        if ((bool) $this->get_session('logged_in') === false) {
            // check post
            $post_vals = $this->__get_post();
            /*
            return codes
            0 - no valid method
            1 - !method_id_post || !isset_post
            2 - !username
            3 - !pass
            4 - !token
            5 - !start_time
            6 - not found
            array - success
            string - ban_user | login_fail
            */
            if (is_array($post_vals)) {
                $success = $this->is_success_login($post_vals);
                /*
                return codes
                "1" - success
                "0" - fail
                "ban_user" - max fail
                */
                switch ($success) {
                    case '1':
                        $this->redirect($this->get_session('logged_page'));
                        break;
                    case '0':
                        $this->set_flash("fail login " . $this->get_session('count_fail'));
                        $this->require_page($this->get_session('login_page'));
                        break;
                    case 'ban_user':
                        $this->ban_user();
                        $this->error('banned.xTimes');
                        break;
                }
            } else {
                switch ($post_vals) {
                    case 'ban_user':
                        $this->ban_user();
                        $this->error('banned.bf'); // brute force
                        break;
                    case 'login_fail':
                        $this->set_flash('login failed');
                        $this->require_page($this->get_session('login_page'));
                        break;
                    default:
                        if ($post_vals != "0") {
                            $this->set_flash("error #$post_vals");
                        }
                        if ($this->pwd_page_is('logged_page')) { 
                            $this->redirect($this->get_session('logout_page'));
                        } else { 
                            $this->require_page($this->get_session('login_page'));
                        }
                        break;
                }
            }
        } else {
            if (!$this->pwd_page_is('logged_page')) { 
                $this->redirect($this->get_session('logged_page'));
            }
        }

        return $this;
    }
    public function get($key)
    {
        return $this->get_session($key);
    }
    public function get_post($clear = false)
    {
        $posts = $this->get_session('posts');
        // clear vals
        if ($clear) {
            $this->set_session('posts', [], true);
        }
        return $posts;
    }
    public function get_get($clear = false)
    {
        $gets = $this->get_session('gets');
        // clear vals
        if ($clear) {
            $this->set_session('gets', [], true);
        }
        return $gets;
    }
    public function get_html_form_infos()
    {
        /* will return this
        'html_form_username' => '__AUTO__', // random_string [ 1 per session ]
        'html_form_password' => '__AUTO__', // idem
        'html_form_formname' => '__AUTO__', // idem
        'html_form_tokenname' => '__AUTO__', // idem
        'html_form_tokenvalue' => '__AUTO__', // sha1 always renew
        'html_form_start_time'  => time(), // always renew
        */
        $html_form_start_time_name = 'html_form_start_time';
        return [
            'login_page_title' => $this->get_session('login_page_title'),
            'username' => $this->get_session('html_form_username'),
            'password' => $this->get_session('html_form_password'),
            'formname' => $this->get_session('html_form_formname'),
            'token' => '<input type="hidden" name="' . $this->get_session('html_form_tokenname') . '" value="' . $this->get_session('html_form_tokenvalue') . '">',
            'start_time' => '<input type="hidden" name="' . $html_form_start_time_name . '" value="' . time() . '">',
        ];
    }
    public function logout()
    {
        $pg = $this->get_session('logout_page');
        if (!file_exists($pg)) {
            $pg = $this->get_session('main_page');
            if (!file_exists($pg)) {
                $pg = $this->get_session('login_page');
                if (!file_exists($pg)) {
                    $this->destroy_session();
                    $this->set_session('http_response_code', 404);
                    $this->error('logout pages not found');
                }
            }
        }
        $this->destroy_session();
        $this->redirect($pg);
    }



    // ******* HTML FORM METHODS *******
    private function reset_form_values()
    {
        $this->Definitions['html_form_tokenvalue'] = $this->Definitions['token'] = $this->encode_as($this->get_random_string(32));
        $this->Definitions['html_form_start_time'] = time();
        $resets = [
            'html_form_username',
            'html_form_password',
            'html_form_formname',
            'html_form_tokenname',
        ];
        foreach ($resets as $reset) {
            $this->Definitions[$reset] = $this->get_random_string();
            usleep(10);
        }
        $this->set_session('html_form_tokenvalue', $this->Definitions['html_form_tokenvalue'], true)
            ->set_session('html_form_username', $this->Definitions['html_form_username'], true)
            ->set_session('html_form_password', $this->Definitions['html_form_password'], true)
            ->set_session('html_form_formname', $this->Definitions['html_form_formname'], true)
            ->set_session('html_form_tokenname', $this->Definitions['html_form_tokenname'], true)
            ->set_session('html_form_start_time', $this->Definitions['html_form_start_time'], true)
            ->set_session('token', $this->Definitions['token'], true)
            ->set_session('logged_in', false, true);
    }
    private function __get_post()
    {
        /*
        return codes
        0 - no valid method
        1 - !method_id_post || !isset_post
        2 - !username
        3 - !pass
        4 - !token
        5 - !start_time
        6 - not found
        string ban_user
        */
        if (!$this->is_valid_method('POST')) return '0';
        if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST)) {
            $post_names = [
                'html_form_username' => $this->get_session('html_form_username'),
                'html_form_password' => $this->get_session('html_form_password'),
                'html_form_tokenvalue' => $this->get_session('html_form_tokenname'),
                'html_form_start_time' => 'html_form_start_time',
                //'html_form_formname' => $this->get_session('html_form_formname'),
            ];
            $found = false;
            $i = 1;
            foreach ($post_names as $session_key => $post_key) {
                $i++;
                $this->set_debug("$session_key = $post_key", 'get_post');
                if (!isset($_POST[$post_key])) {
                    return "$i";
                } else {
                    $post_names[$session_key] = preg_replace('/[^a-zA-Z0-9]/', '', $_POST[$post_key]);
                    $this->set_debug($post_names[$session_key] . ' = ' . $_POST[$post_key], 'get_post');
                    $found = true;
                }
            }
            // check brute force
            $request_time = (isset($_SERVER['REQUEST_TIME']))
                ? $_SERVER['REQUEST_TIME']
                : $post_names['html_form_start_time'];

            $start_time = $this->get_session('start_time');
            $brute_force_time = $this->get_session('brute_force_time');
            if (intval($request_time - $start_time) <= intval($brute_force_time)) {
                $this->set_debug("$request_time - $start_time <= $brute_force_time", 'get_post');
                return 'ban_user';
            }
            return ($found) ? $post_names : '6';
        }
        return '1';
    }
    private function is_success_login($post_vals)
    {
        /*
        return codes
        "1" - success
        "0" - fail
        "ban_user" - max fail
        */
        if (is_array($post_vals)) {
            $username = $post_vals['html_form_username'];
            $password = $post_vals['html_form_password'];
            $tokenvalue = $post_vals['html_form_tokenvalue'];
            $token = $this->get_session('token');
            $user = $this->get_session('user');
            $pass = $this->get_session('pass');
            if ($token == $tokenvalue && $user == $username && $pass == $password) {

                $this->set_debug("sessToken($token) x postToken($tokenvalue)", 'is_success_login');
                $this->set_debug("sessUser($user) x postUser($username)", 'is_success_login');
                $this->set_debug("sessPass($pass) x postPass($password)", 'is_success_login');

                $this->set_session('logged_in', true);
                return "1";
            } else {
                $this->reset_form_values();
                $this->set_session('logged_in', false);

                $max_fail = intval($this->get_session('max_fail'));
                $count_fail = intval($this->get_session('count_fail'));
                $count_fail++;
                if ($count_fail >= $max_fail) {
                    $this->set_session('count_fail', 0, true);
                    return 'ban_user';
                } else {
                    $this->set_session('count_fail', $count_fail, true);
                    return "0";
                }
            }
        }
        $this->set_session('logged_in', false);
        return "0";
    }

    // ******* INIT METHODS
    protected function init_load()
    {
        if ($this->is_session_set('start_time')) {
            foreach ($this->Defaults as $key => $value) {
                $this->Definitions[$key] = $this->get_session($key);
            }
        }
    }
    protected function init_temporizadores()
    {
        $keys = [
            'start_time',
            'html_form_start_time',
            'brute_force_time',
            'banishment_expires',
        ];
        foreach ($keys as $key) {
            $this->set_debug("$key = " . $this->Definitions[$key], 'init_temporizadores');
            $this->set_session($key, $this->Definitions[$key]);
        }
    }
    protected function init_html_form_validators()
    {
        $Token = $this->encode_as($this->get_random_string(32));
        $keys = [
            'html_form_username',
            'html_form_password',
            'html_form_formname',
            'html_form_tokenname',
            'html_form_tokenvalue',
        ];
        /*
        'html_form_username' => '__AUTO__', // random_string [ 1 per session ]
        'html_form_password' => '__AUTO__', // idem
        'html_form_formname' => '__AUTO__', // idem
        'html_form_tokenname' => '__AUTO__', // idem
        'html_form_tokenvalue' => '__AUTO__', // sha1 always renew
        'user' => '', // getenv ou set hardcoded
        'pass' => '', // getenv ou set hardcoded
        'token' => '', // sha1 1 per session
        'credentials_method' => 'getenv', // getenv ou set hardcoded
        */
        foreach ($keys as $key) {
            $x = $this->Definitions[$key];
            if ($x == '__AUTO__') {
                $x = $this->get_random_string();
            }
            if ($key == 'html_form_tokenvalue') {
                $x = $Token;
            }
            $this->set_debug("$key = " . $x, 'init_html_form_validators');
            $this->set_session($key, $x);
        }
        $CM = strtoupper($this->Definitions['credentials_method']);
        if ($CM == 'G' || $CM == 'GETENV') {
            $U = getenv('GUARDIANUSER');
            $P = getenv('GUARDIANPASS');
        } else {
            $U = $this->Definitions['user'];
            $P = $this->Definitions['pass'];
        }
        if (empty($U) || empty($P)) {
            $this->error('user or pass empty');
        } else {
            $U = $this->encode_as($U);
            $P = $this->encode_as($P);
        }
        $h = ($this->Definitions['host'] == '__AUTO__') ? $_SERVER['HTTP_HOST'] : $this->Definitions['host'];
        $this->Definitions['host'] = $h;
        $this->set_debug("host = $h", 'init_html_form_validators');
        $this->set_debug("user($U)", 'init_html_form_validators');
        $this->set_debug("pass($P)", 'init_html_form_validators');
        $this->set_debug("token($Token)", 'init_html_form_validators');
        $this->set_debug("credentials_method($CM)", 'init_html_form_validators');

        $this->set_session('host', $h)
            ->set_session('user', $U)
            ->set_session('pass', $P)
            ->set_session('token', $Token)
            ->set_session('credentials_method', $CM);
    }
    protected function init_pages()
    {
        $keys = [
            'main_page',
            'login_page',
            'login_page_title',
            'logout_page',
            'logout_uri_name',
            'logged_page',
        ];
        foreach ($keys as $key) {
            if ($key != 'login_page_title' && $key != 'logout_uri_name') {
                $page = $this->format_path($this->Definitions[$key]);
                $this->set_debug("$key [$page]", 'init_pages');
                if (file_exists($page)) {
                    $this->set_session($key, $page);
                } else {
                    $this->error("Page $key [$page] not found");
                }
            } else {
                $this->set_session($key, $this->Definitions[$key]);
            }
        }
    }
    protected function init_posts()
    {
        if (!$this->is_valid_method('POST')) return;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST)) {
            $x = [
                $this->get_session('html_form_username'),
                $this->get_session('html_form_password'),
                $this->get_session('html_form_formname'),
                $this->get_session('html_form_tokenname'),
                $this->get_session('html_form_tokenvalue'),
            ];
            foreach ($_POST as $key => $value) {
                if (!preg_match('/html_form/', $key) && !in_array($key, $x)) {
                    $this->Definitions['posts'][$key] = $value;
                }
            }
        } else {
            $this->Definitions['posts'] = [];
        }
        $this->set_session('posts', $this->Definitions['posts'], true);
    }
    protected function init_gets()
    {
        if (!$this->is_valid_method('GET')) return;
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET)) {
            foreach ($_GET as $key => $value) {
                $this->Definitions['gets'][$key] = $value;
            }
        } else {
            $this->Definitions['gets'] = [];
        }
        $this->set_session('gets', $this->Definitions['gets'], true);
    }
    protected function is_valid_method($method = 'POST')
    {
        $host = $_SERVER['HTTP_HOST'];
        $cdr = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
        $sn = $_SERVER['SCRIPT_NAME'];
        $sfn = $_SERVER['SCRIPT_FILENAME'];
        if ($host != $this->get_session('host')) return false;
        $x = str_replace($cdr, '', $sfn);
        if ($x != $sn) return false;
        if (strtoupper($method) != $_SERVER['REQUEST_METHOD']) return false;
        return true;
    }




    // ******* STRING METHODS *******
    protected function get_random_string($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            //string must start with a letter
            $c = $characters[rand(0, $charactersLength - 1)];
            if ($randomString == '') {
                if (is_numeric($c)) {
                    $i--;
                } else {
                    $randomString = $c;
                }
            } else {
                $randomString .= $c;
            }
        }
        return $randomString;
    }
    protected function encode_as($value, $alg = 'sha256')
    {
        return hash($alg, $value);
    }
    protected function printHeader()
    {
        if ($this->debug) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            ini_set('html_errors', 1);
            ini_set('error_log', 'guardian_vacilos_php.log');
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
            ini_set('html_errors', 0);
            ini_set('error_log', '');
        }
        /*
        script-src allowed domains:
        *.googleapis.com
        *.cloudflare.com
        https://developer.mozilla.org/pt-BR/docs/Web/HTTP/CSP

        "Content-Security-Policy: 
        default-src 'self' 'unsafe-inline'; 
        img-src 'self'; 
        child-src 'self' blob:; 
        script-src 'self' 'unsafe-inline' 'unsafe-eval';"
        
        debuging:
        Content-Security-Policy-Report-Only: policy
        TO-DO:
        read this
        https://developers.google.com/web/fundamentals/security/csp#policy_applies_to_a_wide_variety_of_resources

        */

        $allowedDomains = [
            '*.googleapis.com',
            '*.gstatic.com',
            '*.cloudflare.com',
            '*.jquery.com',
            '*.jsdelivr.net',
            '*.bootstrapcdn.com',
        ];
        $CSP = [
            "default-src 'self' 'unsafe-inline' 'unsafe-eval' %s;",
            "img-src 'self' 'unsafe-eval';",
            "child-src 'self';",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval';",
            "script-src-elem 'self' 'unsafe-inline' 'unsafe-eval' %s;"
        ];
        $CSP_string = '';
        $allowed = '';
        foreach ($allowedDomains as $domain) {
            $allowed .= $domain . ' ';
        }
        foreach ($CSP as $_csp) {
            if (preg_match('/\%s/', $_csp)) {
                $CSP_string .= sprintf($_csp, $allowed) . ' ';
            } else {
                $CSP_string .= $_csp . ' ';
            }
        }
        $infos = [
            "Content-Type: text/html; charset=UTF-8",
            "X-Frame-Options: SAMEORIGIN",
            "X-Content-Type-Options: nosniff",
            "Set-Cookie: xyz=abc; SameSite=Strict; path=/",
            "Content-Security-Policy: $CSP_string",
            "X-XSS-Protection: 1; mode=block",
            "Cache-Control: no-store, no-cache, must-revalidate",
            /*"Content-Security-Policy-Report-Only: policy",*/
            "Strict-Transport-Security: max-age=31536000; includeSubDomains; preload",
        ];

        // http_response_codes
        // https://www.php.net/manual/en/function.http-response-code.php
        //https://www.php.net/http-response-code
        $codes = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];
        $http = (!is_null($this->get_session('http_response_code'))) ? $this->get_session('http_response_code') : 200;
        foreach ($infos as $info) {
            header('HTTP / 1.1 ' . $http . ' ' . $codes[$http]);
            header($info);
        }
        return $this;
    }


    // ******* FILE METHODS *******
    protected function pwd_page()
    {
        $b1 = $_SERVER['REQUEST_URI'];
        $b2 = $_SERVER['PHP_SELF'];
        if ($b1 || $b2) {
            $b = ($b2) ? $b2 : $b1;
        } else {
            $b = '';
        }
        return ($b == '' ) ? '' : str_replace($this->get_session('host'), '', $b);
    }
    protected function pwd_page_is($name)
    { 
        $pg = $this->get_session($name);
        $pwd = $this->pwd_page();
        return strstr($pwd, $pg);
        //$c = strstr($pwd, $pg);
        //$c = ( $c ) ? 'sim' : 'nao';
        //exit('<b>pwd_page_is - test: check('.$pg.') x this ('.$pwd.') x '.$c.'</b>');
        // desabilitado o bloco abaixo pois caso logged_page tenha mesmo nome de outra pagina, vai gerar erro
        /*
        if (preg_match('/\//', $pg)) {
            $E = explode('/', $pg);
            $pg = end($E);
        }
        */
        //return ($this->pwd_page() == $pg);
    }
    protected function require_page($page)
    {
        if (file_exists($page)) {
            $guardian = $this;
            require_once($page);
        } else {
            $this->error('require_page: file "' . $page . '" not found');
        }
    }
    protected function redirect($page)
    {
        $this->set_debug($page, 'redirect');
        if (file_exists($page)) {
            echo "<script>window.location.href = '$page';</script>";
        } else {
            $this->error('redirect: file "' . $page . '" not found');
        }
        exit;
    }
    protected function format_path($path)
    {
        $example = 'path/to/file/';
        if ($path != '' && preg_match('/'.str_replace('/', '\/', $example).'/', $path)) {
            $path = str_replace($example, '', $path);
        }
        $this->set_debug($path, 'format_path');
        return $path;
    }
    protected function get_ban()
    {
        $file = $this->Definitions['ban_file'];
        $bans = [];
        $txt = $this->f_get($file);
        $E = explode("\n", $txt);
        foreach ($E as $line) {
            $line = trim($line);
            if ($line != '') {
                $E2 = explode('|', $line);
                if (isset($E2[0]) && isset($E2[1])) {
                    $bans[$E2[0]] = $E2[1];
                }
            }
        }
        return $bans;
    }
    protected function set_ban($array_content)
    {
        $file = $this->Definitions['ban_file'];
        $txt = '';
        foreach ($array_content as $ip => $expires) {
            $txt .= $ip . '|' . $expires . "\n";
        }
        $this->f_set($file, $txt);
    }
    protected function get_log()
    {
        $file = $this->Definitions['log'];
        return $this->f_get($file);
    }
    protected function set_log($msg)
    {
        $file = $this->Definitions['log'];
        $this->f_set($file, $msg);
    }
    protected function f_get($file)
    {
        $file = $this->format_path($file);
        if (file_exists($file)) {
            $fp = fopen($file, 'r');
            $content = fread($fp, filesize($file));
            fclose($fp);
            return $content;
        }
        return false;
    }
    protected function f_set($file, $content)
    {
        $file = $this->format_path($file);
        $fp = fopen($file, 'w');
        fwrite($fp, $content);
        fclose($fp);
        return true;
    }


    // ******* BAN METHODS *******
    protected function get_ban_info_user()
    {
        $ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '00';
        $expired = intval($this->get_session('banishment_expires')) + time();
        return [
            'ip' => $ip,
            'expires' => $expired,
        ];
    }
    protected function is_ban()
    {
        $ban_info = $this->get_ban_info_user();
        $banneds = $this->get_ban();
        if (array_key_exists($ban_info['ip'], $banneds)) {
            $expires = $banneds[$ban_info['ip']];
            if ($expires > time()) {
                return true;
            } else {
                unset($banneds[$ban_info['ip']]);
                if (count($banneds) == 0) {
                    unlink($this->Definitions['ban_file']);
                } else {
                    $this->set_ban($banneds);
                }
                return false;
            }
        }
        return false;
    }
    protected function ban_user()
    {
        if (!$this->is_ban()) {
            $ban_info = $this->get_ban_info_user();
            $banneds = $this->get_ban();
            $banneds[$ban_info['ip']] = $ban_info['expires'];
            $this->set_ban($banneds);
        }
    }



    // ******* SESSION METHODS *******
    protected function is_session_set($key)
    {
        return isset($_SESSION[$key]);
    }
    protected function get_session($key)
    {
        if ($this->is_session_set($key)) {
            return $_SESSION[$key];
        }
        return null;
    }
    protected function set_session($key, $value, $force = false)
    {
        if (in_array($key, $this->always_refresh)) {
            $force = true;
        }
        if (!$force) {
            if (!$this->is_session_set($key)) {
                $_SESSION[$key] = $value;
            }
        } else {
            $_SESSION[$key] = $value;
        }
        return $this;
    }
    protected function unset_session($key)
    {
        if ($this->is_session_set($key)) {
            unset($_SESSION[$key]);
        }
        return $this;
    }
    protected function destroy_session()
    {
        session_destroy();
        return $this;
    }

    // ******* DEBUG METHODS *******
    private function set_debug($msg, $from_function = '')
    {
        $linha = "Function: %s | Message: $msg";
        //$lg = $this->get_log();
        //$lg .= sprintf($linha, $from_function) . "\n";
        //$this->set_log($lg);
        array_push($this->debug_msg, sprintf($linha, $from_function));
    }
    private function get_debug()
    {
        $msg = implode('<br>', $this->debug_msg);
        $msg_log = implode("\n", $this->debug_msg);
        $this->set_log($msg_log);
        echo "<pre>$msg</pre>";
    }

    // ******* MESSAGE METHODS *******
    private function error($msg)
    {
        echo ($msg);
        exit;
    }
    private function set_flash($msg)
    {
        $this->set_session('flash', $msg, true);
    }
    private function get_flash()
    {
        if ($this->is_session_set('flash')) {
            $msg = $this->get_session('flash');
            $this->unset_session('flash');
            return $msg;
        }
        return "";
    }
}//END CLASS