<?php

namespace Core;

ob_start();
session_start();

class Guardian
{


    private $Defaults = [];
    private $Definitions = [];
    private $always_refresh = [];

    private $debug = false;
    private $debug_msg = [];
    private $http_header = 200;

    public $vs = '2021.12.26';



    public function __construct($debug = false, $refreshSession = false)
    {
        $this->debug = $debug;
        if ($refreshSession && $this->is_session_set('start_time')) {
            $this->destroy_session();
        }

        $this->always_refresh = [
            'http_header_response_code',
            'html_form_start_time',
            'html_form_tokenvalue',
            '_get_html_form_infos_count_',
            'logado',
            'posts',
            'gets',
        ];

        $this->http_header = 200;


        $this->Defaults = [

            /* * propriedades dentro de _ (underline) sao privadas * */

            'guardian_base_file' => 'http://localhost/Git_projects/guardian/guardian/index.php', // caminho absoluto para guardian/index.php
            // ALTERAR SOMENTE SE SAIR DO AMBIENTE DE DESENVOLVIMENTO    


            /* * base_dir* */

            'base_dir' => '', // caminho relativo para este script (usado para login "puxar" css, js, etc)

            /* * temporizadores * */

            'start_time' => time(),  // 1 per session'
            'html_form_start_time' => time(), // always renew'
            'brute_force_time' => 1,  // html_form_start_time - start_time <= brute_force_time = ban!
            'banishment_expires' => 90, // segundos de banimento

            /* * html form * */

            'html_form_username' => '__AUTO__', // random_string [ 1 per session ]
            'html_form_password' => '__AUTO__', // idem
            'html_form_formname' => '__AUTO__', // idem
            'html_form_tokenname' => '__AUTO__', // idem
            'html_form_tokenvalue' => '__AUTO__', // sha1 always renew
            '_get_html_form_infos_count_' => 0, // count always renew

            /* * validadores * */

            'host' => '__AUTO__', // $_SERVER['HTTP_HOST']
            '_user_' => '', // getenv ou set hardcoded
            '_pass_' => '', // getenv ou set hardcoded
            // /etc/apache2/envvars must be edited to include the following line:
            // export GUARDIANUSER=your_user_name
            // export GUARDIANPASS=your_password
            '_token_' => '', // sha1 1 per session
            '_credentials_method_' => 'getenv', // getenv ou set hardcoded
            'logado' => false, // true 1 per session

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

            // se logged_page is array, paginas serao setadas em _logged_pages_
            'logged_page' => 'path/to/file/hello.php', // ou array('path/to/file/hello.php', 'path/to/file/page2.php')  


            /* * internos * */
            // interno. pega paginas de logged_page se este eh array
            // usara methodo is_authenticated_page
            '_logged_pages_' => [],
            '_setup_' => 0, // 0 = nao inicializado, 1 = inicializado
            '_auto_redirect_times' => 0, // vezes que o redirect automatico foi executando ao tentar acessar uma pagina nao autorizada
            '_auto_redirect_limit_' => 3, // limite de vezes que o redirect automatico pode ser executado antes de encerrar o script com $this->error('access denied')  


            /*  * arquivos * */
            'log' => 'path/to/file/guardian_log.log',
            'ban_file' => 'path/to/file/guardian_ban_file',
            '_error_log_' => 'path/to/file/guardian_vacilos',


            /* * http headers * */
            // 
            'http_header_response_code' => 200,
            'http_header_unsafe_inline' =>  1,
            'http_header_unsafe_eval' => 1,
            'http_header_allow_subdomains' => 0,
            'http_header_cache_control' => 'no-store, no-cache, must-revalidate', // public | private ou string configurando (se omitido, usa padrao sistema)
            'http_header_x_content_type_options' => 'nosniff', // 0 disable  or custom string // se omitido, usa o padrao sistema
            //"X-Content-Type-Options: nosniff",
            'http_header_x_frame_options' => 'SAMEORIGIN', // 0 disable or custom string // se omitido, usa o padrao sistema
            //"X-Frame-Options: SAMEORIGIN",
            'http_header_set_cookie' => 0, // 0 disable 1 string padrao sistema or custom string (string padrao define o path como /)
            //string padrao sistema => "Set-Cookie: xyz=a1b2c3; SameSite=Strict; path=/",
            //desabilitar caso chamar a classe fora do diretorio base do script. ou entao tera erro #2.1
            'http_header_content_security_policy' => 1, // 0 disable
            //"Content-Security-Policy: $CSP_string",        
            'http_header_x_xss_protection' => 1, // 0 disable, 1 enable(sanitize), 1; mode=block[otherOption]


            /* * POSTS & GETS * */
            'posts' => [],
            'gets' => [],


        ];
    }


    // ******* USER METHODS *******
    public function setup($array_user_definitions = [])
    {
        foreach ($this->Defaults as $key => $value) {
            switch ($key) {
                    // setados somente em monitore:
                case '_auto_redirect_times_':
                case '_auto_redirect_limit_':
                    // setado somente no login:
                case 'logado':
                    // uso interno:
                case '_error_log_':
                    continue;
                case '_get_html_form_infos_count_':
                    $this->Defaults[$key] = 0;
                case '_setup_':
                    $this->Defaults[$key] = 1;
                case '_logged_pages_':
                    continue; // setado abaixo
                case 'logged_page':
                    $vals = (isset($array_user_definitions['logged_page']))
                        ? $array_user_definitions['logged_page']
                        : $value;
                    $first = '';
                    $others = [];
                    if (is_array($vals)) {
                        foreach ($vals as $val) {
                            if ($first == '') {
                                $first = $val;
                            }
                            array_push($others, $val);
                        }
                    }
                    $this->Definitions['logged_page'] =  ($first == '') ? $vals : $first;
                    $this->Definitions['_logged_pages_'] = $others;
                    break;
                case 'posts':
                case 'gets':
                    $this->Definitions[$key] = [];
                    break;
                default:
                    if (!isset($array_user_definitions[$key])) {
                        $this->Definitions[$key] = $value;
                    } else {
                        $this->Definitions[$key] = $array_user_definitions[$key];
                    }
                    $m = "$key = " . $this->Definitions[$key];
                    $this->set_debug($m, 'setup');
                    break;
            }
        }
        $this->set_session('_setup_', 1);
        if ($this->debug) {
            echo "<br><b>after setup:</b><pre>";
            foreach ($this->Definitions as $key => $value) {
                $v = (is_array($value)) ? 'array: ' . implode(',', $value) : $value;
                echo $key . ' = ' .  $v . '<br>';
            }
            echo '</pre>';
        }
        return $this;
    }
    public function print_header()
    {
        if (!headers_sent()) {
            $this->printHeader();
        }
        return $this;
    }
    public function end()
    {
        //session_write_close();
        ob_end_flush();
    }
    public function start()
    {
        // inits begin...
        $this->check_env();
        $base_dir = (isset($this->Definitions['base_dir'])) ? $this->Definitions['base_dir'] : '';
        if ($base_dir != '') {
            // if the last character is not a slash add (para login.php dar include nos assets)
            if ($base_dir[strlen($base_dir) - 1] != '/') $base_dir .= '/';
        }
        $this->Definitions['base_dir'] = $base_dir;
        $this->set_session('base_dir', $base_dir);
        $this->init_headers();
        $this->init_temporizadores();
        $this->init_html_form_validators();
        $this->set_session('max_fail', ((isset($this->Definitions['max_fail']) ? $this->Definitions['max_fail'] : $this->Defaults['max_fail'])));
        $this->set_session('count_fail', 0);
        $this->init_pages();
        $this->init_files();
        $this->init_posts();
        $this->init_gets();
        $this->break_point('start');
        return $this;
    }
    public function monitore($isMainPage = false)
    {
        $this->init_load();
        $is_logged = ($this->is_session_set('logado')) ? (bool) $this->get_session('logado') : false;
        $uri_name = (isset($this->Definitions['logout_uri_name']))
            ? $this->Definitions['logout_uri_name']
            : $this->Defaults['logout_uri_name'];
        if (isset($_GET[$uri_name])) {
            $this->logout();
        }
        // check ban
        if ($this->is_ban()) {
            $this->http_header = 403;
            $this->error('Banned');
        }
        if (!$isMainPage) {
            if (!$is_logged) {
                LazzyCode:
                $page = ($this->is_session_set('logout_page'))
                    ? $this->get_session('logout_page')
                    : ($this->is_session_set('main_page') ? $this->get_session('main_page') : false);
                if ($page && file_exists($page)) {
                    $this->redirect($page);
                } else {
                    if (!$this->auto_redirect_exceeds_limit()) {
                        $this_page = $this->get_pwd_file();
                        if ($this_page && (file_exists($this_page) || is_dir($this_page))) {
                            $this->redirect($this_page);
                        }
                        $this->http_header = 401;
                        $this->error("access denied");
                    } else {
                        $this->http_header = 401;
                        $this->error("access denied");
                    }
                }
            }
            // usando paginas autenticadas?
            $auth = ((is_array($this->Definitions['_logged_pages_'])) && count($this->Definitions['_logged_pages_']) > 0);
            if ($auth && !$this->is_authenticated_page()) { //&& $this->auto_redirect_exceeds_limit()) {
                // vai funfar? vamos testar...
                // goto LazzyCode; perigo...
                $this->http_header = 401;
                $this->error('access denied.');
            }
            return $this;
        }
        if (!$is_logged) {
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
                        $this->http_header = 200;
                        $this->redirect($this->Definitions['logged_page']);
                        break;
                    case '0':
                        $this->http_header = 407;
                        $this->set_flash("fail login " . $this->get_session('count_fail'))
                            ->require_page($this->Definitions['login_page']);
                        break;
                    case 'ban_user':
                        $this->http_header = 401;
                        $this->ban_user()
                            ->error('banned.xTimes');
                        break;
                }
            } else {
                switch ($post_vals) {
                    case 'ban_user':
                        $this->http_header = 401;
                        $this->ban_user()
                            ->error('banned.bf'); // brute force
                        break;
                    case 'login_fail':
                        $this->http_header = 407;
                        $this->set_flash('login failed')
                            ->require_page($this->Definitions['login_page']);
                        break;
                    default:
                        if ($post_vals != "0") {
                            /*
                            BUG: 
                            as vezes a sessao "trava" com nomes "perdidos" nos forms. analisando:
                            */
                            $ei = 0;
                            if (!$this->is_session_set('_erro_interno_')) {
                                $ei = 1;
                                $this->set_session('_erro_interno_', $ei, true);
                            } else {
                                $ei = $this->get_session('_erro_interno_');
                                $ei++;
                                $this->set_session('_erro_interno_', $ei, true);
                                if ($ei >= 2) $this->logout();
                            }
                            $this->http_header = 407;
                            $this->set_flash("error #$post_vals . $ei");
                        }
                        if ($this->pwd_page_is('logged_page')) {
                            $this->http_header = 307;
                            $this->redirect($this->Definitions['logout_page']);
                        } else {
                            $this->http_header = 200;
                            $this->require_page($this->Definitions['login_page']);
                        }
                        break;
                }
            }
        } else {
            if (!$this->pwd_page_is('logged_page')) {
                $this->http_header = 200;
                $this->redirect($this->Definitions['logged_page']);
            }
        }

        return $this;
    }
    public function get($key)
    {
        if ($key != '' && $key[0] != '_') {
            return $this->get_session($key);
        } else {
            return null;
        }
    }
    public function get_post($varName, $clear = false)
    {
        $posts = $this->get_session('posts');
        // clear vals
        if ($clear) {
            $this->unset_session('posts');
        }
        if (is_array($varName)) {
            $r = [];
            foreach ($varName as $k) {
                $r[$k] = (isset($posts[$k])) ? $posts[$k] : false;
            }
            return $r;
        }
        return (isset($posts[$varName])) ? $posts[$varName] : false;
    }
    public function get_get($varName, $clear = false)
    {
        $gets = $this->get_session('gets');
        // clear vals
        if ($clear) {
            $this->unset_session('gets');
        }
        if (is_array($varName)) {
            $r = [];
            foreach ($varName as $k) {
                $r[$k] = (isset($gets[$k])) ? $gets[$k] : false;
            }
            return $r;
        }
        return (isset($gets[$varName])) ? $gets[$varName] : false;
    }
    public function sanitize_as($value, $as = 'text')
    {
        if (empty($value) || strlen($value) == 0) return $value;
        //https://theasciicode.com.ar/ascii-printable-characters/backslash-reverse-slash-ascii-code-92.html
        $Map = [
            '<' => '&lt;',
            '>' => '&gt;',
            '\\' => '&bsol;',
            '"' => '&quot;',
            "'" => '&#039;',
        ];
        $as = strtoupper(substr($as, 0, 3));
        switch ($as) {
            case 'TEX':
            case 'STR':
                $txt = trim($value);
                foreach ($Map as $f => $r) {
                    $txt = str_replace($f, $r, $txt);
                }
                return $txt;
            case 'INT':
            case 'NUM':
                return preg_replace('/[^0-9]/', '', $value);
            case 'DAT':
                return preg_replace('/[^0-9\/\.\-]/', '', $value);
            default:
                return $value;
        }
    }
    public function get_html_form_infos()
    {
        $count = $this->get_session('_get_html_form_infos_count_');
        $count++;
        $this->set_session('_get_html_form_infos_count_', $count, true);
        if ( $count > 1 ){
            $token_form = $this->encode_as($this->get_random_string(32));
            $this->set_session('html_form_tokenvalue', $token_form, true);
        }else{
            $token_form = $this->get_session('html_form_tokenvalue');
        }
        /* will return this
        'html_form_username' => '__AUTO__', // random_string [ 1 per session ]
        'html_form_password' => '__AUTO__', // idem
        'html_form_formname' => '__AUTO__', // idem
        'html_form_tokenname' => '__AUTO__', // idem
        'html_form_tokenvalue' => '__AUTO__', // sha1 always renew
        'html_form_start_time'  => time(), // always renew
        */
        $html_form_start_time_name = 'html_form_start_time';
        /*
        campos html_form_username e html_form_password sao hidden, campos input sao apenas mascara para pegar os valores digitados
        esses vals serao transportados para o hidden e o post eh via ajax.post
        javascript deve retirar o primeiro caracter das strings mascara. esse valor representa o real nome dos respectivos hidden
        */
        $tag = '<input type="hidden" name="%s" id="%s" value="%s" />';
        $username = $this->get_session('html_form_username');
        $password = $this->get_session('html_form_password');
        $fake_input_username = $this->get_random_string(1) . $username;
        $fake_input_password = $this->get_random_string(1) . $password;
        $required_hidden_fields =
            /*sprintf(
                $tag,
                'debug_token',
                'debug_token',
                $this->get_session('_token_')
            ) .*/
            sprintf(
                $tag,
                $this->get_session('html_form_tokenname'),
                $this->get_session('html_form_tokenname'),
                $token_form
            ) . sprintf(
                $tag,
                $html_form_start_time_name,
                $html_form_start_time_name,
                time()
            ) . sprintf(
                $tag,
                $username,
                $username,
                ''
            ) . sprintf($tag, $password, $password, '');

        return [
            'login_page_title' => $this->get_session('login_page_title'),
            'username' => $fake_input_username,
            'password' => $fake_input_password,
            'formname' => $this->get_session('html_form_formname') . '_' . $count,
            'required_hidden_fields' => $required_hidden_fields,
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
                    $this->http_header = 404;
                    //$this->error('logout pages not found');
                    // simplesmente redireciona pra guardian_base_file
                    $this->redirect($this->Defaults['guardian_base_file']);
                }
            }
        }
        $this->destroy_session();
        $this->http_header = 307;
        $this->set_session('http_header_set_cookie', 1, true);
        $this->redirect($pg);
    }



    // ******* HTML FORM METHODS *******
    private function reset_form_values()
    {
        $this->Definitions['html_form_tokenvalue'] = $this->Definitions['_token_'] = $this->encode_as($this->get_random_string(32));
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
            ->set_session('_token_', $this->Definitions['_token_'], true)
            ->set_session('_get_html_form_infos_count_', 0, true)
            ->set_session('logado', false, true);
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
        if ($this->server('REQUEST_METHOD') === 'POST'  && isset($_POST)) {
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
            $request_time = (!is_null($this->server('REQUEST_TIME', false)))
                ? $this->server('REQUEST_TIME')
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
            $form_reloaded_times = $this->get_session('_get_html_form_infos_count_');
            $username = $post_vals['html_form_username'];
            $password = $post_vals['html_form_password'];
            $tokenvalue = ($form_reloaded_times > 1 ) ? '0' : $post_vals['html_form_tokenvalue'];
            $token = $this->get_session('_token_');
            $user = $this->get_session('_user_');
            $pass = $this->get_session('_pass_');

            //echo "<p>debug username($username), user($user)<br>password($password), pass($pass)<br>tokenvalue($tokenvalue), token($token)</p>";
            //exit;

            if (($token === $tokenvalue) && ($user === $username) && ($pass === $password)) {

                $this->set_session('logado', true);
                // unset posts
                foreach ($_POST as $key => $val) {
                    unset($_POST[$key]);
                }
                // block session
                session_write_close();
                return "1";

            } else {
                $this->reset_form_values();
                $this->set_session('logado', false);

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
        $this->set_session('logado', false);
        return "0";
    }

    // ******* INIT METHODS
    protected function init_load()
    {
        if ($this->is_session_set('start_time')) {
            foreach ($this->Defaults as $key => $value) {
                $this->Definitions[$key] = $this->get_session($key);
            }
        } else {
            // minimal setup
            $this->Definitions['logout_uri_name'] = $this->Defaults['logout_uri_name'];
            $this->Definitions['ban_file'] = $this->get_pwd_file();
            $this->set_session('ban_file', $this->Definitions['ban_file'], true);
        }
        return $this;
    }
    protected function init_headers()
    {
        $keys = [
            'http_header_response_code',
            'http_header_unsafe_inline',
            'http_header_unsafe_eval',
            'http_header_allow_subdomains',
            'http_header_cache_control',
            'http_header_x_content_type_options',
            'http_header_x_frame_options',
            'http_header_set_cookie',
            'http_header_content_security_policy',
            'http_header_x_xss_protection',
        ];
        foreach ($keys as $key) {
            $this->set_session($key, $this->Definitions[$key], true);
        }
        return $this;
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
        return $this;
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
        '_user_' => '', // getenv ou set hardcoded
        '_pass_' => '', // getenv ou set hardcoded
        '_token_' => '', // sha1 1 per session
        '_credentials_method_' => 'getenv', // getenv ou set hardcoded
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
        $CM = strtoupper($this->Definitions['_credentials_method_']);
        if ($CM == 'G' || $CM == 'GETENV') {
            // caso: getenv == '' depois de upgrade php-8.1 em vb.
            $U = (!empty(getenv('GUARDIANUSER'))) ? getenv('GUARDIANUSER') : '';
            $P = (!empty(getenv('GUARDIANPASS'))) ? getenv('GUARDIANPASS') : '';
        } else {
            $U = $this->Definitions['_user_'];
            $P = $this->Definitions['_pass_'];
        }
        if (empty($U) || empty($P)) {
            $this->error('user or pass empty');
        } else {
            $U = $this->encode_as($U);
            $P = $this->encode_as($P);
        }
        $h = ($this->Definitions['host'] == '__AUTO__') ? $this->server('HTTP_HOST') : $this->Definitions['host'];
        $this->Definitions['host'] = $h;
        $this->set_debug("host = $h", 'init_html_form_validators');
        $this->set_debug("user($U)", 'init_html_form_validators');
        $this->set_debug("pass($P)", 'init_html_form_validators');
        $this->set_debug("token($Token)", 'init_html_form_validators');
        $this->set_debug("credentials_method($CM)", 'init_html_form_validators');

        $this->set_session('host', $h)
            ->set_session('_user_', $U)
            ->set_session('_pass_', $P)
            ->set_session('_token_', $Token)
            ->set_session('_credentials_method_', $CM);
        return $this;
    }
    protected function init_pages()
    {
        // teste: caso guardian seja usado por mais de um sistema, ao sair de um e ir pro outro, vai dar page not found.
        // para evitar que o codigo "morra", pego essas dead pages e comparo com o diretorio da chamada.
        // ver no final do código
        $change_pages = [];
        $keys = [
            'main_page',
            'login_page',
            'login_page_title',
            'logout_page',
            'logout_uri_name',
            'logged_page',
        ];
        foreach ($keys as $key) {
            switch ($key) {
                case 'login_page_title':
                case 'logout_uri_name':
                    $this->set_session($key, $this->Definitions[$key]);
                    break;
                default:
                    $page = $this->format_path($this->Definitions[$key]);
                    $this->set_debug("$key [$page]", 'init_pages');
                    if (file_exists($page)) {
                        // verificando se houve mudança entre session e definitions. se sim, possivel alteração de diretorio/sistema.
                        if (!$this->is_session_set($key)) {
                            $this->set_session($key, $page);
                        } else {
                            $session_page = $this->get_session($key);
                            if ($session_page != $page) {
                                $change_pages[$key] = $page;
                            }
                        }
                    } else {
                        $this->error("Page $key [$page] not found.");
                    }
                    break;
            }
        }
        if (count($change_pages) > 0) {
            if (!$this->is_session_set('_auto_redirect_times_')) {
                $main_page = $this->get_session('main_page');
                if ($main_page && file_exists($main_page)) {
                    $this->destroy_session();
                    // respira
                    usleep(100);
                    $this->set_session('_auto_redirect_times_', 1);
                    $this->redirect($main_page);
                } else {
                    $this->error("Page change detected and new main_page not found. Please, close you browser and open again.");
                }
            } else {
                $this->error("Page change detected. Configurations could not be loaded. 
                    Please, close you browser and check your setup options.");
            }
        }
        if (count($this->Definitions['_logged_pages_']) > 0) {
            $this->set_session('_logged_pages_', $this->Definitions['_logged_pages_']);
            $this->set_debug('_logged_pages_ set array', 'init_pages');
        } else {
            $this->set_session('_logged_pages_', []);
            $this->set_debug('_logged_pages_ is empty', 'init_pages');
        }
        return $this;
    }
    protected function init_files()
    {
        $keys = [
            'log',
            'ban_file',
        ];
        foreach ($keys as $key) {
            $this->set_session($key, $this->format_path($this->Definitions[$key]));
        }
        return $this;
    }
    protected function init_posts()
    {
        if (!$this->is_valid_method('POST')) return;
        if ($this->server('REQUEST_METHOD') === 'POST' && isset($_POST)) {
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
        return $this;
    }
    protected function init_gets()
    {
        if (!$this->is_valid_method('GET')) return;
        if ($this->server('REQUEST_METHOD') === 'GET' && isset($_GET)) {
            foreach ($_GET as $key => $value) {
                $this->Definitions['gets'][$key] = $value;
            }
        } else {
            $this->Definitions['gets'] = [];
        }
        $this->set_session('gets', $this->Definitions['gets'], true);
        return $this;
    }
    protected function is_valid_method($method = 'POST')
    {
        $host = $this->server('HTTP_HOST');
        $cdr = $this->server('CONTEXT_DOCUMENT_ROOT');
        $sn = $this->server('SCRIPT_NAME');
        $sfn = $this->server('SCRIPT_FILENAME');
        if ($host != $this->get_session('host')) return false;
        $x = str_replace($cdr, '', $sfn);
        if ($x != $sn) return false;
        if (strtoupper($method) != $this->server('REQUEST_METHOD')) return false;
        return true;
    }




    // ******* STRING METHODS *******
    public function get_all_printable_keys()
    {
        $keys = [];
        $chars = 'abcdefghijklomnoprstuvwxyzABCDEFGHIJKMNOPQRSTUVWXYZ0123456789!@#$%*()_+-=[{]},<.>;:/?"|';
        for ($i = 0; $i < strlen($chars); $i++) {
            array_push($keys, $chars[$i]);
        }
        return $keys;
    }
    public function get_public_key()
    {
        $the_key = $this->f_get('_pk');
        if (!$the_key) {
            $keys = $this->get_all_printable_keys();
            $public_key = $keys;
            shuffle($public_key);
            if (count($public_key) == 0) {
                $this->error('Public key not found.');
            }
            // save public_key in _pk file
            $the_key = implode('', $public_key);
            $this->f_set('_pk', $the_key);
        }
        return $the_key;
    }
    private function get_private_key()
    {
        $pk = $this->get_public_key();
        $pub_keys = [];
        for ($i = 0; $i < strlen($pk); $i++) {
            array_push($pub_keys, $pk[$i]);
        }
        $keys = $this->get_all_printable_keys();
        $private_key = [];
        for ($i = 0; $i < count($keys); $i++) {
            $private_key[$keys[$i]] = $pub_keys[$i];
        }
        return $private_key;
    }
    public function encode_printable_string($string)
    {
        $encoded = '';
        $priv_key = $this->get_private_key();
        for ($i = 0; $i < strlen($string); $i++) {
            $chr = $string[$i];
            $encoded .= isset($priv_key[$chr]) ? $priv_key[$chr] : $chr;
        }
        return $encoded;
    }
    public function decode_printable_string($string)
    {
        $decoded = '';
        $priv_key = array_flip($this->get_private_key());
        for ($i = 0; $i < strlen($string); $i++) {
            $chr = $string[$i];
            $decoded .= isset($priv_key[$chr]) ? $priv_key[$chr] : $chr;
        }
        return $decoded;
    }

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
            ini_set('error_log', $this->Defaults['_error_log_']);
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

        /*
        FIREFOX ISSUES  
        Content Security Policy: Não foi possível processar a diretiva desconhecida “script-src-elem”
        https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/worker-src
        */

        $host = $this->get_session('host');
        $b_unsafe_inline = (bool) $this->get_session('http_header_unsafe_inline');
        $b_unsafe_eval = (bool) $this->get_session('http_header_unsafe_eval');
        $b_allow_sub_domains = (bool) $this->get_session('http_header_allow_subdomains');


        $unsafe_inline = "'unsafe-inline'";
        $unsafe_eval = "'unsafe-eval'";
        $sub_domains = '%s';

        if (!$b_unsafe_inline) {
            $unsafe_inline = '';
        }
        if (!$b_unsafe_eval) {
            $unsafe_eval = '';
        }
        if (!$b_allow_sub_domains) {
            $sub_domains = $host;
        }



        $is_FF = (strstr($this->server('HTTP_USER_AGENT'), 'Firefox'));

        $default_src = "default-src 'self' $unsafe_inline $unsafe_eval $sub_domains;";

        $img_src = "img-src 'self';"; //"img-src 'self' 'unsafe-eval';"; is this unsafe?

        $child_src = "child-src 'self';";

        $script_src = "script-src 'self' $unsafe_inline $unsafe_eval;";

        $script_src_elem = "script-src-elem 'self' $unsafe_inline $unsafe_eval $sub_domains;";

        if ($is_FF) {
            $default_src = "default-src 'self' $unsafe_inline $unsafe_eval $sub_domains;";
            $child_src = "child-src 'self' $unsafe_inline $unsafe_eval $sub_domains;";
            $script_src_elem = "worker-src 'self' $unsafe_inline $unsafe_eval $sub_domains;";
            $script_src = "script-src 'self' $unsafe_inline $unsafe_eval $sub_domains;";
        }



        if ($b_allow_sub_domains) {
            $allowedDomains = [
                $host,
                '*.googleapis.com',
                '*.gstatic.com',
                '*.cloudflare.com',
                '*.jquery.com',
                '*.jsdelivr.net',
                '*.bootstrapcdn.com',
            ];
        } else {
            $allowedDomains = [$host];
        }

        $CSP = [
            $default_src,
            $img_src,
            $child_src,
            $script_src,
            $script_src_elem,
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

        //https://stackoverflow.com/questions/18008135/is-serverrequest-scheme-reliable
        $sts = '';
        if (!is_null($this->server('REQUEST_SCHEME', false))) {
            if (strtoupper($this->server('REQUEST_SCHEME')) == 'HTTPS') {
                $sts = "Strict-Transport-Security: max-age=31536000; includeSubDomains; preload";
            }
        }



        $cache = $this->get_session('http_header_cache_control');
        $cache_control = "Cache-Control: no-store, no-cache, must-revalidate"; //public, private, no-store, no-cache, must-revalidate
        if (!is_null($cache)) {
            $cache_control = "Cache-Control: $cache";
        }

        //https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Headers/X-XSS-Protection

        $x_pr = "X-XSS-Protection: %s"; // 1 or "X-XSS-Protection: 1; mode=block";
        $x_cto = "X-Content-Type-Options: nosniff";
        $x_fo = "X-Frame-Options: SAMEORIGIN";
        $x_sc = "Set-Cookie: xyz=a1b2c3; SameSite=Strict; path=/";
        $x_sp = "Content-Security-Policy: $CSP_string";

        $b_co = $this->get_session('http_header_x_content_type_options');
        $b_fo = $this->get_session('http_header_x_frame_options');
        $b_sc = $this->get_session('http_header_set_cookie');
        $b_sp = (bool) $this->get_session('http_header_content_security_policy');
        $b_pr = $this->get_session('http_header_x_xss_protection');


        $content_type_options = ($b_co == '0')
            ? ''
            : $x_cto;

        $x_frame_options = ($b_fo == '0')
            ? ''
            : $x_fo;

        $set_cookie = ($b_sc == '0')
            ? ''
            : $x_sc;

        $content_security_policy = ($b_sp) ? $x_sp : '';

        $xss_protection = ($b_pr == '0') ? '' : sprintf($x_pr, $b_pr);


        /*
        'http_header_x_content_type_options' => 'nosniff', // 0 disable or string
        //"X-Content-Type-Options: nosniff",

        'http_header_x_frame_options' => 'SAMEORIGIN', // 0 disable or string
        //"X-Frame-Options: SAMEORIGIN",

        'http_header_set_cookie' => 0, // 0 disable 1 cookie string padrao sistema or custom string
        //"Set-Cookie: xyz=a1b2c3; SameSite=Strict; path=/",

        'http_header_content_security_policy' => 1, // 0 disable
        
        'http_header_x_xss_protection' => 1 // 0 disable, 1 enable(sanitize), 1; mode=block[otherOption]
        */


        //"X-Content-Type-Options: nosniff",
        //"X-Frame-Options: SAMEORIGIN",
        //"Set-Cookie: xyz=a1b2c3; SameSite=Strict; path=/",
        //"Content-Security-Policy: $CSP_string",        
        $infos = [
            "Content-Type: text/html; charset=UTF-8",
            $cache_control,
            $xss_protection,
            $sts,
            $content_type_options,
            $x_frame_options,
            $set_cookie,
            $content_security_policy,

            /*"Content-Security-Policy-Report-Only: policy",*/
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
        $http = ($this->http_header != '')
            ? $this->http_header
            : ((!is_null($this->get_session('http_header_response_code'))) ? $this->get_session('http_header_response_code') : 501);

        header('HTTP / 1.1 ' . $http . ' ' . $codes[$http]);
        foreach ($infos as $info) {
            header($info);
        }
        /*
        //https://stackoverflow.com/questions/1773386/how-to-suppress-remove-php-session-cookie
        header_remove('Set-Cookie');
        // no cookies at all!
        //https://stackoverflow.com/questions/686155/remove-a-cookie
        foreach ( $_COOKIE as $key => $val ){
            if ( isset($_COOKIE[$key])){
                unset($_COOKIE[$key]);
                setcookie($key, null, -1, '/'); 
                
            }
        }
        */
        return $this;
    }


    // ******* FILE METHODS *******
    protected function is_authenticated_page()
    {
        $pg = $this->pwd_page();
        $pages = $this->get_session('_logged_pages_');
        if (!is_array($pages) || count($pages) == 0) {
            return false;
        }
        foreach ($pages as $page) {
            if (strstr($pg, $page)) {
                return true;
            }
        }
        return false;
    }
    protected function get_pwd_file()
    {
        return dirname($this->server('PHP_SELF'));
    }
    protected function pwd_page()
    {
        $b1 = $this->server('REQUEST_URI', false);
        $b2 = $this->server('PHP_SELF');
        if ($b1 || $b2) {
            $b = ($b2) ? $b2 : $b1;
        } else {
            $b = '';
        }
        return ($b == '') ? '' : str_replace($this->get_session('host'), '', $b);
    }
    protected function pwd_page_is($name)
    {
        $pwd = $this->pwd_page();
        $pg = $this->get_session($name);
        return strstr($pwd, $pg);
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
        if (file_exists($page) || is_dir($page)) {
            echo "<script>window.location='$page';</script>";
        } else {
            $this->error('redirect: file "' . $page . '" not found');
        }
        exit;
    }
    protected function format_path($path)
    {
        $example = 'path/to/file/';
        if ($path != '' && preg_match('/' . str_replace('/', '\/', $example) . '/', $path)) {
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
    protected function f_set($file, $content, $mode = 'w')
    {
        $file = $this->format_path($file);
        $fp = fopen($file, $mode) or die("Guardian->f_set error: can't open file $file");
        fwrite($fp, $content);
        fclose($fp);
        return true;
    }


    // ******* BAN METHODS *******
    protected function get_ban_info_user()
    {
        $ip = (!is_null($this->server('REMOTE_ADDR', false))) ? $this->server('REMOTE_ADDR') : '00';
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
        return $this;
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
        foreach ($_SESSION as $k => $v) {
            unset($_SESSION[$k]);
        }
        session_destroy();
        return $this;
    }
    protected function server($key, $die = true)
    {
        $key = strtoupper(trim($key));
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        if ($die) {
            //$this->http_response_code = 500;
            $this->printHeader();
            echo "<h1>Internal Server Error</h1>";
            exit("<!-- Error $key! -->");
        }
        return null;
    }
    protected function auto_redirect_exceeds_limit()
    {
        $times = ($this->is_session_set('_auto_redirect_times_')) ? intval($this->get_session('_auto_redirect_times_')) + 1 : 1;
        $this->set_session('_auto_redirect_times_', $times);
        if ($times >= $this->Defaults['_auto_redirect_limit_']) {
            return true;
        }
        return false;
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
        echo "<b>get_debug()::debug_msg is:</b><pre>$msg</pre>";
    }
    private function break_point($where, $dumpSomething = [], $thenDie = true)
    {
        if ($this->debug) {
            $this->get_debug();
            echo "<br>BREAK POINT - DEBUG AFTER <b>$where :</b><pre>";
            foreach ($this->Defaults as $key => $value) {
                $val = $this->get_session($key);
                $v = (is_array($val)) ? 'array: ' . implode(',', $val) : $val;
                echo $key . ' = ' .  $v . '<br>';
            }
            echo '</pre>';
            if (count($dumpSomething) > 0) {
                echo '<b>DUMP:</b><pre>';
                var_dump($dumpSomething);
                echo '</pre>';
            }
            if ($thenDie) {
                exit();
            }
        }
    }
    private function check_env()
    {
        $functions = [
            'hash',
            'md5',
            'getenv',
            'headers_sent'
        ];
        foreach ($functions as $fn) {
            if (!function_exists($fn)) {
                echo "<hr><p align='center'><b>check_env::FUNCTION $fn !EXISTS...ABORT</b></p><hr>";
                exit;
            }
        }
    }

    // ******* MESSAGE METHODS *******
    private function error($msg)
    {
        if (!headers_sent()) {
            $this->printHeader();
        }
        exit($msg);
    }
    private function set_flash($msg)
    {
        $this->set_session('flash', $msg, true);
        return $this;
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