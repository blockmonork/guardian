<?php

use Core\Guardian;

/**
 * redirect to $redirectTo if user is not logged in
 * @param string $redirectTo the file with relative path to Guardian.php redirects to
 * @param string $guardianPath relative path to Guardian.php file
 * @return void
 */
function guardianMonitor(string $redirectTo = 'index.php', string $guardianPath = '')
{
    if (!file_exists($guardianPath . 'Guardian.php')) {
        die('Guardian.php not found');
    }
    require($guardianPath . 'Guardian.php');
    Guardian::printHeader();
    if (!Guardian::is_logged_in()) {
        Guardian::redirect($redirectTo);
    }
}
