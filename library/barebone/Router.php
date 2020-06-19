<?php

namespace Application;

use Exception;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Router
{

    protected static $routeCollection = null;

    private static $log = array();

    private function __construct()
    {
        return;
    }

    public static function __callStatic($methodName, $arguments)
    {
        if (DEV_MODE) {
            $ex     = new Exception();
            $traces = explode("\n", $ex->getTraceAsString());
            if (!empty($traces[1])) {
                array_push(self::$log, array(
                    'method'    => $methodName,
                    'arguments' => $arguments,
                    'source'    => $traces[1],
                ));
            }
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

    private static function add($path, $callback, $method = 'GET')
    {
        if (!static::$routeCollection instanceof RouteCollection) {
            static::$routeCollection = new RouteCollection();
        }
        static::$routeCollection->add(
            sprintf('%s::%s', $method, $path), new Route(
                $path, array('callback' => $callback),
                array(), array(), '', array(), array($method)
            ));
    }

    private static function remove($path)
    {
        return;
    }

    public static function getRoutes()
    {
        if (!static::$routeCollection instanceof RouteCollection) {
            static::$routeCollection = new RouteCollection();
        }
        return static::$routeCollection;
    }

    public static function getLog()
    {
        return self::$log;
    }

}
