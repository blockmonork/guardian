<?php
// @session_destroy(); exit("destroyed");

//foreach ( $_SERVER as $key => $val ){ echo "$key: $val<br>"; }

use Core\Guardian;

require ('Guardian.php');

$debug = false;
$refreshSession = false;

$guardian = new Guardian($debug, $refreshSession);

$guardian->setup(
    [
        'brute_force_time' => 60,
        'banishment_expires' => 90,
    ]
)->print_header()->start()->monitore();

// and its done!