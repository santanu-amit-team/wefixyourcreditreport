<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';

use Application\Session;
use Application\Request;


//echo $config['offer_path'];die;
if(Session::has('memberSessionData.member_token')) {
    header('Location: '.Request::getOfferUrl().'member/dashboard.php');
}

App::run(array(
    //'step'     => 0,
    'version'  => 'desktop',
    'tpl'      => 'member/login.tpl',
    'go_to'    => '',
    'tpl_vars' => array(
        'settings' => json_encode($vars)
    ),
    'pageType' => 'Member',
));
