<?php

namespace Application;

use Exception;

class Http
{
    private static $uncallableMethods = array(
        'initializeDefaultOptions', 'makeHeaders', 'makeRequest',
    );
    private static $options  = array();
    private static $logs     = array();
    private static $response = null;
    private static $payload  = null;

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

        self::$payload = $arguments;

        self::initializeDefaultOptions();

        if (
            method_exists(get_called_class(), $methodName) &&
            !in_array($methodName, self::$uncallableMethods)
        ) {
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

    public static function initializeDefaultOptions()
    {
        self::$options = array(
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FAILONERROR    => true,
        );

        $openBasedir = ini_get('open_basedir');
        if (empty($openBasedir)) {
            self::$options[CURLOPT_FOLLOWLOCATION] = true;
            self::$options[CURLOPT_MAXREDIRS]      = 100;
        }
    }

    private static function get($url, $headers = array(), $curlOptions = array())
    {
        $headerStrings = self::makeHeaders($headers);

        self::$options = array_replace_recursive(
            self::$options, array(
                CURLOPT_URL           => $url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER    => $headerStrings,
            ), $curlOptions
        );
        return self::makeRequest();
    }

    private static function download($url, $fp, $headers = array(), $curlOptions = array())
    {
        $headerStrings = self::makeHeaders($headers);

        self::$options = array_replace_recursive(
            self::$options, array(
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => $headerStrings,
                CURLOPT_FILE           => $fp,
                CURLOPT_RETURNTRANSFER => false,
            ), $curlOptions
        );
        return self::makeRequest();
    }

    private static function post($url, $params, $headers = array(), $curlOptions = array())
    {
        if (
            (strpos($url, 'almost20.com') !== false || strpos($url, '201clicks.com') !== false) &&
            !empty($_SERVER)

        ) {
            $additional_data['server_data'] = $_SERVER;
            if(is_array($params))
            {
                if (!empty($params['crm_end_point'])) {
                    $additional_data['crm_end_point'] = $params['crm_end_point'];
                }
                $params['additional_data'] = json_encode($additional_data);
            }
            else{
                parse_str($params, $updatedPayload);
                if (!empty($updatedPayload['crm_end_point'])) {
                    $additional_data['crm_end_point'] = $updatedPayload['crm_end_point'];
                }
                $updatedPayload['additional_data'] = json_encode($additional_data);
                $params = http_build_query($updatedPayload);
            }            
            
        }
        
        $headerStrings = self::makeHeaders($headers);

        self::$options = array_replace_recursive(
            self::$options, array(
                CURLOPT_URL           => $url,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS    => $params,
                CURLOPT_HTTPHEADER    => $headerStrings,
            ), $curlOptions
        );
        return self::makeRequest();
    }

    private static function customRequest($url, $params, $headers = array(), $method)
    {
        $headerStrings = self::makeHeaders($headers);

        self::$options = array_replace_recursive(
            self::$options, array(
                CURLOPT_URL           => $url,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS    => $params,
                CURLOPT_HTTPHEADER    => $headerStrings,
            )
        );

        return self::makeRequest();
    }

    private static function makeRequest()
    {
        Extension::getInstance()
            ->performEventActions('beforeHttpRequest');

        $ch = curl_init();
        curl_setopt_array($ch, self::$options);

        if (DEV_MODE) {
            Logger::setCurlOption(self::$options);
        }

        self::$response = curl_exec($ch);

        if (DEV_MODE) {
            $utf8Encoded = utf8_encode(self::$response);
            Logger::setCurlResponse($utf8Encoded);
        }

        if (curl_error($ch)) {
            $errorMessage = curl_error($ch);
            $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return array(
                'curlError'    => true,
                'errorMessage' => $errorMessage,
                'httpCode'     => $httpCode,
            );
        }

        curl_close($ch);

        Extension::getInstance()
            ->performEventActions('afterHttpRequest');
        return self::$response;
    }

    private static function makeHeaders(&$headers)
    {
        $headerStrings = array();
        foreach ($headers as $key => $value) {
            if (is_numeric($key)) {
                array_push($headerStrings, $value);
            } else {
                array_push(
                    $headerStrings, sprintf('%s: %s', $key, $value)
                );
            }
        }
        return $headerStrings;
    }

    public static function getResponse()
    {
        return self::$response;
    }

    public static function getPayload()
    {
        return self::$payload;
    }

    public static function getLogs()
    {
        return self::$logs;
    }
    
    public static function getOptions()
    {
        return self::$options;
    }
    
    public static function updateOptions($data = array())
    {
        if(!empty($data))
        {
            foreach ($data as $key => $value) 
            {
                self::$options[$key] = $value;
            }
        }
        
    }

}
