<?php

namespace Application;

use Exception;
use Application\Helper\Provider;

class Resource
{

    private static $data = array(
        'script' => array(), 'stylesheet' => array()
    );
    private static $logs  = array();

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

    private static function register($type, $key, $path)
    {
        if(!isset(self::$data[$type]) || !is_string($key)){
            return;
        }
        self::$data[$type][$key] = $path;
    }

    private static function unregister($type, $key)
    {
        if(!isset(self::$data[$type][$key])){
            return;
        }
        unset(self::$data[$type][$key]);
    }

    private static function unregisterAll($type)
    {
        if(!isset(self::$data[$type])){
            return;
        }
        return self::$data[$type] = array();
    }

    public static function getAll($type){
        if(!isset(self::$data[$type])){
            return;
        }
        return self::$data[$type];
    }

    public static function getAllAsHtml($type){
        if(!isset(self::$data[$type])){
            return;
        }
        $resourceBasePath = $html = $htmlTag = '';
        switch ($type) {
            case 'script':
                $resourceBasePath = Provider::getResourceBasePath('js'); 
                $htmlTag          = '<script src="%s" type="text/javascript"></script>';
                break;
            case 'stylesheet':
                $resourceBasePath = Provider::getResourceBasePath('css');
                $htmlTag          = '<link href="%s" type="text/stylesheet"></link>';
                break;
            default: break;
        }
        foreach (self::$data[$type] as $key => $path) {
            $html .= "\n" . sprintf(
                $htmlTag, sprintf('%s/%s', $resourceBasePath, $path)
            );
        }
        return $html;
    }

    public static function getLogs()
    {
        return self::$logs;
    }

}
