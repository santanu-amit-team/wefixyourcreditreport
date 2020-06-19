<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'common.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'member' . DIRECTORY_SEPARATOR . 'check-login.php';

use Application\Request;
use Extension\Membership\Membership;
use Application\Session;

$orderID = Request::get('orderID');
if (!empty($orderID))
{
    $obj = new Membership();
    $orderDetails = $obj->getCustomerOrders(
            array(
        'order_ids' => $orderID,
        'criteria' => 'all',
            ), true
    );
    
    $orderDetails = $orderDetails[$orderID];
}
else
{
    $obj = new Membership();
    $orderDetails = $obj->getCustomerOrders(array(), true, true);
    $ordersKeys = array_keys($orderDetails);
    $orderDetails = end($orderDetails);
    $orderID = end($ordersKeys);
}
//
//echo '<pre>';
//print_r($orderDetails);
//die;

App::run(array(
    'version' => 'desktop',
    'tpl' => 'member/order-details.tpl',
    'go_to' => '',
    'tpl_vars' => array(
        'orderDetails' => $orderDetails,
        'orderID' => $orderID,
        'configID' => 1,
        'settings' => json_encode($vars)
    ),
    'pageType' => 'Member',
));
