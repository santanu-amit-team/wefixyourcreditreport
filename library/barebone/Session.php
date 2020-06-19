<?php

namespace Application;

use Exception;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session as SessionStorage;

class Session
{
    private static $updateMethods = array(
        'set', 'update', 'replace', 'remove', 'clear',
    );
    private static $sessionStorage = null;
    private static $logs           = array();

    private function __construct()
    {
        return;
    }

    public static function __callStatic($methodName, $arguments)
    {
        if (DEV_MODE && in_array($methodName, self::$updateMethods)) {
            $ex     = new Exception();
            $traces = explode("\n", $ex->getTraceAsString());
            if (!empty($traces[1])) {
                array_push(self::$logs, array(
                    'method'    => $methodName,
                    'arguments' => $arguments,
                    'source'    => $traces[1],
                ));
            }
        }

        if (self::$sessionStorage === null) {
            $basePath       = sprintf('/%s', trim(Request::getBasePath(), '/'));
            $sessionRootKey = 'unify-v4';
            if (strpos($basePath, '/admin') === false) {
                $isDomainSpecific = Config::settings('allow_global_session');
                if(!empty($isDomainSpecific))
                {
                    session_set_cookie_params(0, '/', Request::getHttpHost());
                }
                else{
                    $offerPath = rtrim(Request::getOfferPath(), '/');
                    session_set_cookie_params(0, sprintf('%s/', $offerPath));
                }
                
            } else {
                $adminPath = str_replace('/app', '', $basePath);
                session_set_cookie_params(0, sprintf('%s/', $adminPath));
                $sessionRootKey = sprintf('%s-admin', $sessionRootKey);
            }
            
            self::$sessionStorage = new SessionStorage();

            self::$sessionStorage->registerBag(
                new NamespacedAttributeBag($sessionRootKey, '.')
            );

            ini_set('session.save_handler', 'files');
        }

        if (method_exists(get_called_class(), $methodName)) {
            return call_user_func_array(
                array(get_called_class(), $methodName), $arguments
            );
        }

        throw new Exception(
            sprintf(
                '%s::%s method not found!', get_called_class(), $methodName
            )
        );
    }

    private static function set($key, $value)
    {
        return self::$sessionStorage->set($key, $value);
    }

    private static function get($key, $default = null)
    {
        return self::$sessionStorage->get($key, $default);
    }

    private static function all()
    {
        return self::$sessionStorage->all();
    }

    private static function has($key)
    {
        return self::$sessionStorage->has($key);
    }

    private static function update($data = array())
    {
        foreach ($data as $key => $value) {
            self::$sessionStorage->set($key, $value);
        }
    }

    private static function replace($data)
    {
        return self::$sessionStorage->replace($data);
    }

    private static function remove($key)
    {
        return self::$sessionStorage->remove($key);
    }

    private static function clear()
    {
        return self::$sessionStorage->clear();
    }

    public static function getLogs()
    {
        return self::$logs;
    }

    private static function getSessionId() {
        self::$sessionStorage->start();
        return self::$sessionStorage->getId();
    }

    private static function setSessionId($id){
        self::$sessionStorage->setId($id);
    }

}
