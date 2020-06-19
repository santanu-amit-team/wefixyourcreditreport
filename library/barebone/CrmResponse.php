<?php

namespace Application;

use Exception;

class CrmResponse
{

    private static $data    = array();
    private static $logs    = array();
    private static $rawData = null;

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
                array_push(self::$logs, array(
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

    private static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return self::has($key) ? self::$data[$key] : $default;
    }

    public static function all()
    {
        return self::$data;
    }

    public static function has($key)
    {
        return array_key_exists($key, self::$data);
    }

    private static function update($data = array())
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    private static function replace($data = array())
    {
        self::$data = array();
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    private static function remove($key)
    {
        if (self::has($key)) {
            unset(self::$data[$key]);
        }
    }

    private static function clear()
    {
        self::$data = array();
    }

    public static function getLogs()
    {
        return self::$logs;
    }

}
