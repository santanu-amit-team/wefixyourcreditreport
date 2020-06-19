<?php

/*
 * To authenticate Basic login credential
 */
require_once '..' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bootstrap.php';
Bootstrap::initialize('static');
use Application\Session;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;

header("Content-Type: application/json");

$postdata = file_get_contents("php://input");
$request  = json_decode($postdata, true);

$loggedInUser = null;
if (!empty($request)) {

    $usersArray = getTheUserDetails($request['username']);

    if(empty($usersArray)){
        echo json_encode(array(
            'success' => false,
            'field'   => 'username',
            'message' => 'Username or Password is incorrect.',
        ));
        return;
    }
       

    $loggedInUser['username'] = $usersArray[0]['username'];
    $loggedInUser['userType'] = $usersArray[0]['user_type'];

    if (!empty($loggedInUser)) {

        if ($request['password'] === $usersArray[0]['password']) {

            Session::update(array(
                'username'  => $loggedInUser['username'],
                'userType'  => $loggedInUser['userType'],
                'access_urls' => array_slice($usersArray[0], 4),
                'loginFlag' => 'logged_in',
            ));
            echo json_encode(array(
                'success' => true,
                'message' => 'Correct Credential.',
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'field'   => 'password',
                'message' => 'Incorrect Password',
            ));
        }
    } else {
        echo json_encode(array(
            'success' => false,
            'field'   => 'username',
            'message' => 'Incorrect Username',
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Incorrect Credential',
    ));
}


function getTheUserDetails($username)
{
    $table = array(
        'name' => 'users',
        'attr' => array(
            'id' => 'integer',
            'username' => 'string',
            'password'    => 'string',
            'email'    => 'string',
            'user_type' => 'integer',
            'change_access_permissions' => 'boolean',
            'ecommerce' => 'boolean',
            'campaigns' => 'boolean',
            'funnel_configurations' => 'boolean',
            'coupons' => 'boolean',
            'cms' => 'boolean',
            'extensions' => 'boolean',
            'logs' => 'boolean',
            'systems_log' => 'boolean',
            'user_activity' => 'boolean',
            'change_log' => 'boolean',
            'system' => 'boolean',
            'crm' => 'boolean',
            'users' => 'boolean',
            'settings' => 'boolean',
            'advance_settings' => 'boolean',
            'tools' => 'boolean',
            'affiliate_manager' => 'boolean',
            'pixel_manager' => 'boolean',
            'rotators' => 'boolean',
            'mid_routing' => 'boolean',
            'traffic_monitor' => 'boolean',
            'auto_responder' => 'boolean',
            'auto_filters' => 'boolean',
            'scheduler' => 'boolean',
            'troubleshooting' => 'boolean'
        ),
    );

    try {
        $user = Database::table('users')->where('username', '=', trim($username))->find()->asArray();
        return $user;
    }
    catch(Exception $e){
        return false;
    }
}
