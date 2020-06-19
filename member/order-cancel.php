<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'check-login.php';

use Application\Request;
use Extension\Membership\Membership;
use Application\Session;

$orderID = Request::get('orderID');


App::run(array(
    'version' => 'desktop',
    'tpl' => 'member/order-cancel.tpl',
    'go_to' => '',
    'tpl_vars' => array(
        'orderID' => $orderID,
        'configID' => 1,
        'settings' => json_encode($vars)
    ),
    'pageType' => 'Member',
));
