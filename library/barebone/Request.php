<?php

namespace Application;

use Exception;
use Symfony\Component\HttpFoundation\Request as RequestProvider;
use Application\Config;

class Request
{
    private static $applicationRootPath = null;
    private static $requestProvider     = null;

    private function __construct()
    {
        return;
    }

    public static function __callStatic($methodName, $arguments)
    {
        if (self::$requestProvider === null) {
            self::$requestProvider = self::getInstance();
        }
        if (!in_array(
            $methodName, array(
                'form', 'query', 'cookies', 'server', 'headers', 'attributes',
            ))
        ) {
            return call_user_func_array(
                array(self::$requestProvider, $methodName), $arguments
            );
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

    private static function form()
    {
        return self::$requestProvider->request;
    }

    private static function query()
    {
        return self::$requestProvider->query;
    }

    private static function cookies()
    {
        return self::$requestProvider->cookies;
    }

    private static function server()
    {
        return self::$requestProvider->server;
    }

    private static function headers()
    {
        return self::$requestProvider->server;
    }

    private static function attributes()
    {
        return self::$requestProvider->attributes;
    }

    private static function getContentType()
    {
        return self::$requestProvider->getContentType();
    }

    public static function getInstance()
    {
        if (self::$requestProvider === null) {
            self::$requestProvider = RequestProvider::createFromGlobals();
            if (self::$requestProvider->getContentType() === 'json') {
                $contents = json_decode(
                    self::$requestProvider->getContent(), true
                );
                foreach ($contents as $key => $value) {
                    self::$requestProvider->request->set($key, $value);
                }
            }
        }
        return self::$requestProvider;
    }

    public static function getClientIp()
    {

        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (
            strtolower($_SERVER['HTTP_HOST']) == 'localhost' ||
            strtolower($_SERVER['SERVER_NAME']) == 'localhost' ||
            $_SERVER['REMOTE_ADDR'] == '::1'
        ) {
            return sprintf(
                '%s.%s.%s.%s',
                rand(1, 255), rand(1, 255), rand(1, 255), rand(1, 255)
            );
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    public static function isSecure()
    {
        $serverProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);

        if (strpos($serverProtocol, 'https') === 0) {
            return true;
        }

        if (
            (
                !empty($_SERVER['HTTPS']) &&
                strtolower($_SERVER['HTTPS']) !== 'off'
            ) ||
            (
                !empty($_SERVER['SERVER_PORT']) &&
                (int) $_SERVER['SERVER_PORT'] === 443
            )
        ) {
            return true;
        }

        if (
            !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
        ) {
            return true;
        }

        if (
            !empty($_SERVER['REQUEST_SCHEME']) &&
            strtolower($_SERVER['REQUEST_SCHEME']) === 'https'
        ) {
            return true;
        }
        
        if (
            !empty($_SERVER['HTTP_CF_VISITOR'])
        ) {
            $schm = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
            if(strtolower($schm['scheme']) === 'https')
            {
                return true;
            }
        }

        return false;
    }

    public static function getScheme()
    {

        if (self::isSecure() === true) {
            return 'https';
        }

        return 'http';
    }

    public static function getBaseUrl()
    {
        if (self::$requestProvider === null) {
            self::$requestProvider = self::getInstance();
        }
        $hostName = trim(self::$requestProvider->getHttpHost(), '/');
        $basePath = trim(self::$requestProvider->getBasePath(), '/');
        $scheme   = self::getScheme();
        return rtrim(
            sprintf('%s://%s/%s', $scheme, $hostName, $basePath), '/'
        );
    }

    public static function oldGetOfferPath()
    {
        if (self::$requestProvider === null) {
            self::$requestProvider = self::getInstance();
        }
        $hostName = trim(self::$requestProvider->getHttpHost(), '/');
        $basePath = trim(self::$requestProvider->getBasePath(), '/');
        if ($basePath === '') {
            return '/';
        }
        $basePathParts = explode('/', $basePath);
        $offerDir      = trim(
            dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR
        );
        $offerDirParts = explode(DIRECTORY_SEPARATOR, $offerDir);
        if (end($offerDirParts) !== end($basePathParts)) {
            unset($basePathParts[count($basePathParts) - 1]);
            $basePath = implode('/', $basePathParts);
            if ($basePath === '') {
                return '/';
            }
        }
        $rootDir = str_replace(
            sprintf(
                '%s%s', DIRECTORY_SEPARATOR, str_replace(
                    '/', DIRECTORY_SEPARATOR, $basePath
                )
            ), '', $offerDir
        );
        self::$offerPath = trim(str_replace(
            DIRECTORY_SEPARATOR, '/', str_replace(
                $rootDir, '', $offerDir
            )
        ), '/');

        if (!empty(self::$offerPath)) {
            self::$offerPath = sprintf('%s/', self::$offerPath);
        }
        self::$offerPath = sprintf('/%s', self::$offerPath);
        return self::$offerPath;
    }

    /**
     *
     * Get Offer's Root Path
     * @return Offer's Root Path with trailing slashes
     *
     **/
    public static function getOfferPath()
    {
        if (self::$applicationRootPath !== null) {
            return self::$applicationRootPath;
        }

        $documentRoot    = filter_var($_SERVER['DOCUMENT_ROOT']);
        $documentRootDir = trim(
            str_replace(DIRECTORY_SEPARATOR, '/', $documentRoot), '/'
        );
        
        $applicationRootDir = trim(
            str_replace(
                DIRECTORY_SEPARATOR, '/', dirname(dirname(__DIR__))
            ), '/'
        );
        
        if(!preg_match('/'.preg_quote($documentRootDir, '/').'/i', $applicationRootDir))
        {
            $settingsOfferPath = Config::settings('offer_path');
            return self::$applicationRootPath = '/'.trim($settingsOfferPath,'/').'/';
        }

        $matchPosition = strpos($applicationRootDir, $documentRoot);

        if(!empty($matchPosition))
        {
            $extraStr = substr($applicationRootDir, 0, $matchPosition+1);
            $applicationRootDir = str_replace($extraStr, '', $applicationRootDir);
        }

        $applicationRootPath = sprintf(
            '/%s/',
            str_replace($documentRootDir, '', $applicationRootDir)
        );

        self::$applicationRootPath = str_replace(
            '//', '/', $applicationRootPath
        );
        
        return self::$applicationRootPath;
    }

    /**
     *
     * Get Offer's Root URL
     * @return Offer's Root URL with trailing slashes
     *
     **/
    public static function getOfferUrl()
    {
        if (self::$requestProvider === null) {
            self::$requestProvider = self::getInstance();
        }
        $hostName  = trim(self::$requestProvider->getHttpHost(), '/');
        $offerPath = ltrim(self::getOfferPath(), '/');
        $scheme    = self::getScheme();
        return sprintf('%s://%s/%s', $scheme, $hostName, $offerPath);
    }

    /**
     *
     * Get Device Specific Path (Path based on Desktop and Mobile Version)
     * @return Device Specific Path with trailing slashes
     *
     **/
    public static function getInstancePath()
    {
        $deviceSpecificOfferPath = Request::getOfferPath();
        if (Session::get('appVersion', 'desktop') !== 'desktop') {
            $deviceSpecificOfferPath = sprintf(
                '%s%s/',
                $deviceSpecificOfferPath, Config::settings('mobile_path')
            );
        }
        return $deviceSpecificOfferPath;
    }

}
