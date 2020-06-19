<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';

use Extension\Membership\Membership;
use Application\Session;
use Application\Request;
$obj = new Membership();

$res = $obj->memberLogout();

$result = json_decode($res, true);

if($result['response_code'] == 100) {
    Session::remove('memberSessionData');
    header('Location: '.Request::getOfferUrl().'member/login.php');
}else{
    header('Location: '.Request::getOfferUrl().'member/dashboard.php');
}

