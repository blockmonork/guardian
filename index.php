<?php
// @session_destroy(); exit("destroyed");
// foreach ( $_SERVER as $key => $val ){ echo "$key: $val<br>"; } 

use Core\Guardian;

require_once('Guardian.php');

$debug = false;
$refreshSession = false;

$guardian = new Guardian($debug, $refreshSession);


$guardian->setup([
    'logged_page' => ['hello.php', 'logged_page2.php'],
    ])
    ->start()
    ->monitore(true)
    ->print_header()
    ->end();

// and its done!
