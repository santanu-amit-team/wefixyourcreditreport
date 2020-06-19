<?php

use Application\Session;
use Application\Request;

if(!Session::has('memberSessionData.member_token')) {
    header('Location: '.Request::getOfferUrl().'member/login.php');
}

