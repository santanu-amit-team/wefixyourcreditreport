<?php

namespace Application;

use Application\Session;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseProvider;

class Response
{
    public static $flushResponse     = false;
    public static $rawResponse       = null;
    private static $responseProvider = null;
    public static $disableLog = false;

    private function __construct()
    {
        return;
    }

    public static function __callStatic($methodName, $arguments)
    {
        if (self::$responseProvider === null) {
            self::$responseProvider = new ResponseProvider();
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

    private static function send($content, $options = array())
    {
        $defaultOptions = array(
            'statusCode' => 200,
            'headers'    => array(
                'Content-Type' => 'application/json',
            ),
        );

        if (!empty($options)) {
            $defaultOptions = array_replace_recursive(
                $defaultOptions, $options
            );
            if (!is_array($defaultOptions['headers'])) {
                $defaultOptions['headers'] = array(
                    'Content-Type' => 'application/json',
                );
            }
        }

        self::$responseProvider->setStatusCode(
            $defaultOptions['statusCode']
        );

        foreach ($defaultOptions['headers'] as $key => $value) {
            self::$responseProvider->headers->set($key, $value);
        }

        if (is_array($content)) {
            
            self::prepareResponse($content);

            if ( self::$flushResponse === true ) {
                self::$rawResponse = $content;
                return;
            }

            self::$responseProvider->headers->set(
                'Content-Type', 'application/json'
            );
            
            if(self::$disableLog && array_key_exists('logs', $content)) unset ($content['logs']);
            
            $content = json_encode($content);
        }
        
        self::$responseProvider->setContent($content);
        self::$responseProvider->send();
        exit(0);
    }

    private static function prepareResponse(&$data)
    {
        $requestPageType = Request::attributes()->get('requestPageType');
        if ($requestPageType === 'admin') {
            return;
        }
        if (DEV_MODE) {
            $data['logs'] = Logger::getLogs();
        }
        $controller = Request::attributes()->get('controller');
        if ($controller !== "Application\\Controller\\CrmsController") {
            return;
        }

        if (Session::get('steps.current.id') === 1) {
            if (Session::get('steps.1.prospectId') !== null) {
                Session::set(
                    'queryParams.prospect_id', Session::get('steps.1.prospectId')
                );
            }
            if (Session::get('steps.1.orderId') !== null) {
                Session::set(
                    'queryParams.order_id', Session::get('steps.1.orderId')
                );
            }
            if (Session::get('steps.1.customerId') !== null) {
                Session::set(
                    'queryParams.customer_id', Session::get('steps.1.customerId')
                );
            }
        }
        
        if(!empty($data['skipQueryParams']))
        {
            return;
        }

        $queryParams = array_filter(Session::get('queryParams', array()));
        if (!empty($data['redirect'])) {
            if (stripos(strrev($data['redirect']), 'php.') !== 0) {
                $data['redirect'] .= '.php';
            }
        }
        if (!empty($data['redirect']) && !empty($queryParams)) {
            $data['redirect'] = sprintf(
                '%s?%s', $data['redirect'], http_build_query(
                    Session::get('queryParams')
                )
            );
        }
    }

}
