<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'check-login.php';

App::run(array(
    //'step'     => 0,
    'version'  => 'desktop',
    'tpl'      => 'member/dashboard.tpl',
    'go_to'    => '',
    'tpl_vars' => array(
        'settings' => json_encode($vars)
    ),
    'pageType' => 'Member',
));
