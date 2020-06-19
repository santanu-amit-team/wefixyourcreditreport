<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';

use Application\Request;
use Extension\Membership\Membership;
use Application\Session;

$isApproved = Request::get('isApproved');
$isRefundSuccess = false;
$obj = new Membership;
if($obj->orderRefund())
{
    $isRefundSuccess = true;
}

App::run(array(
    'version' => 'desktop',
    'tpl' => 'member/refund.tpl',
    'go_to' => '',
    'tpl_vars' => array(
        'refundApproved' => $isApproved,
        'isRefundSuccess' => $isRefundSuccess
    ),
    'pageType' => 'Member',
));



