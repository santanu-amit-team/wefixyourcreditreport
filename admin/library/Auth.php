<?php

namespace Admin\Library;

use Application\Session;
use Application\Request;

class Auth
{
    public static function isLoginAuthorized(){
        if(
               (Session::get('loginFlag') && 
                Session::get('username') &&
                Session::get('loginFlag') === 'logged_in')
            || (Session::get('access_token') && 
                Session::get('googleEmail'))
        ){
            //check if session has the token;
            if(!Session::has('DocAccessToken')) {
                //Request::getClientIp();
                $userAgent = Request::headers()->get('HTTP_USER_AGENT');
                Session::set('DocAccessToken', base64_encode($userAgent));
            }

            return true;
        }
        return false;
    }

    public static function backToLogin(){
        header('location: ../');
        exit;
    }
}
