<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'check-login.php';

use Application\Request;
use Extension\Membership\Membership;

$trackingID = Request::get('trackingID');
if (!empty($trackingID))
{
    $obj = new Membership();
    $trackDetails = $obj->orderTrack(
            array(
                'tracking_id' => $trackingID
            )
    );
}

//echo '<pre>';
//print_r($trackDetails);


App::run(array(
    //'step'     => 0,
    'version' => 'desktop',
    'tpl' => 'member/order-tracking.tpl',
    'go_to' => '',
    'tpl_vars' => array(
        'settings' => json_encode($vars),
        'trackDetails' => !empty($trackDetails) ? $trackDetails : ''
    ),
    'pageType' => 'Member',
));
