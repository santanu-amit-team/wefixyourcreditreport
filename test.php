<?php

require_once ('library' . DIRECTORY_SEPARATOR . 'bootstrap.php');
Bootstrap::initialize('static');

use Application\Session;
$sessionData = Session::all();
if (
    !empty($_COOKIE['CB_DEBUG_MODE']) &&
    $_COOKIE['CB_DEBUG_MODE'] === 'ENABLE_DEBUGGER'
) {
    echo '<pre>';
}
    print_r($sessionData);