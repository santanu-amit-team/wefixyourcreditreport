<?php

namespace Application;

class Logger
{
    private static $logs          = array();
    private static $curlOptions   = array();
    private static $curlResponses = array();

    private static $restrictedKeys = array(
        'password', 'token', 'secret', 'key', 'ApiGuid'
    );

    private function __construct()
    {
        return;
    }

    public static function write($key, $content)
    {
        if (!array_key_exists($key, self::$logs)) {
            self::$logs[$key] = array();
        }
        array_push(self::$logs[$key], $content);
    }

    public static function setCurlOption($option)
    {
        array_push(self::$curlOptions, $option);
    }

    public static function setCurlResponse($response)
    {
        array_push(self::$curlResponses, $response);
    }

    public static function getCurlOptions()
    {
        return self::$curlOptions;
    }

    public static function getCurlResponses()
    {
        return self::$curlResponses;
    }

    public static function getLogs()
    {
        $pattern = sprintf('/(%s)/', implode('|', self::$restrictedKeys));

        $allLogs = array(
            'custom'         => self::$logs,
            'session'        => Session::getLogs(),
            'crmPayload'     => CrmPayload::getLogs(),
            'crmResponse'    => CrmResponse::getLogs(),
            'http'           => Http::getLogs(),
            'curlOptions'    => self::$curlOptions,
            'curlResponses'  => self::$curlResponses,
        );
        array_walk_recursive($allLogs, function (&$value, $key) use($pattern) {
            if (preg_match($pattern, $key)) {
                $value = '********';
            }
        });
        
        $allLogs = self::maskedPayload($allLogs, $pattern, 'curlOptions');
        $allLogs = self::maskedPayload($allLogs, $pattern, 'http');
        
        return $allLogs;
    }
    
    
    private static function maskedPayload($allLogs, $pattern, $type)
    {
        foreach($allLogs[$type] as $k => $cOption)
        {
            if($type == 'curlOptions' && !empty($cOption['10015']))
            {
                $v = $cOption['10015'];
                $updatedKey = $allLogs['curlOptions'][$k];
            }
            elseif($type == 'http' && !empty($cOption['arguments']['1']))
            {
                $v = $cOption['arguments']['1'];
                $updatedKey = $allLogs['http'][$k]['arguments'][1];
            }
            
            if(empty($v) || is_array($v))
            {
                continue;
            }
            $arr = json_decode($v, true);
            if(is_array($arr)) 
            {
                array_walk_recursive($arr, function (&$value, $key) use($pattern) {
                    if (preg_match($pattern, $key)) {
                        $value = '********';
                    }
                });
                $updatedKey =  json_encode($arr);
                if($type == 'curlOptions')
                {
                    $allLogs['curlOptions'][$k] = json_encode($arr);
                }
                elseif($type == 'http')
                {
                    $allLogs['http'][$k]['arguments'][1] = json_encode($arr);
                }
                continue;
            }
            parse_str($v, $arr);
            array_walk_recursive($arr, function (&$value, $key) use($pattern) {
                if (preg_match($pattern, $key)) {
                    $value = '********';
                }
            });
            
            if($type == 'curlOptions')
            {
                $allLogs['curlOptions'][$k] = http_build_query($arr);
            }
            elseif($type == 'http')
            {
                $allLogs['http'][$k]['arguments'][1] = http_build_query($arr);
            }
            
        }
        return $allLogs;
    }
    

}
