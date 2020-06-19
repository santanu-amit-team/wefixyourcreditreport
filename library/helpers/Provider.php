<?php

namespace Application\Helper;

use Application\Config;
use Application\CrmPayload;
use Application\CrmResponse;
use Application\HookHandler;
use Application\Model\Configuration;
use Application\Registry;
use Application\Request;
use Application\Resource;
use Application\Session;

final class Provider
{

    private static $hostName       = null;
    private static $customHeaders  = null;
    private static $serverProtocol = null;
    private static $statusCode     = null;
    private static $information    = null;

    private function __construct()
    {
        return;
    }

    private function __clone()
    {
        return;
    }

    public static function removeSubDomain($domain)
    {
        $fragments = explode('.', $domain);

        if (count($fragments) == 1) {
            $domain = preg_replace('/:.+$/i', '', $domain);
            return $domain;
        }

        $domain = preg_replace('/(^https?:\/\/)/i', '', $domain);
        $domain = preg_replace('/:.+$/i', '', $domain);
        if (
            preg_match(
                '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,8})$/i',
                $domain, $regs
            )
        ) {
            return $regs['domain'];
        }
    }

    public static function getDeviceSpecificOfferPath()
    {
        $deviceSpecificOfferPath = Request::getOfferPath();
        if (Session::get('appVersion') !== 'desktop') {
            $deviceSpecificOfferPath = sprintf(
                '%s%s/',
                $deviceSpecificOfferPath, Config::settings('mobile_path')
            );
        }
        return $deviceSpecificOfferPath;
    }

    public static function asyncScript($url)
    {
        $gtmetrixIps = Registry::system('gtmetrixIps');
        $clientIp    = Request::getClientIp();

        if (
            in_array($clientIp, $gtmetrixIps['ipv4']) ||
            in_array($clientIp, $gtmetrixIps['ipv6'])
        ) {
            return;
        }

        $script = sprintf(
            '$.get("%s/%s");',
            rtrim(Request::getOfferPath(), '/'), trim($url, '/')
        );

        $ajaxDelay = 1000 * Session::get('steps.current.ajaxDelay');

        if ($ajaxDelay > 0) {
            $script = sprintf(
                'setTimeout(function(){%s}, %d);', $script, $ajaxDelay
            );
        }

        return sprintf('<script>$(function(){%s});</script>', $script);
    }

    public static function getAlternativeCdnPath()
    {
        if (!Config::settings('enable_alternate_cdn_path')) {
            return sprintf(
                '%s/assets', rtrim(Request::getOfferPath(), '/')
            );
        }
        $alternativeCdnPath = trim(Config::settings('cdn_basepath'));
        if (empty($alternativeCdnPath)) {
            return sprintf(
                '%s/assets', rtrim(Request::getOfferPath(), '/')
            );
        }
        return rtrim($alternativeCdnPath, '/');
    }

    public static function getResourceBasePath($type)
    {
        $resourceTypes = array(
            'images' => 'img_cdn_path',
            'css'    => 'css_cdn_path',
            'js'     => 'js_cdn_path',
        );
        $resourceBasePath = rtrim(Request::getOfferPath(), '/');

        if (
            !in_array($type, array_keys($resourceTypes)) ||
            !Config::settings('enable_cdn')
        ) {
            return $resourceBasePath;
        }
        $cdnBasePath = trim(Config::settings($resourceTypes[$type]));
        return !empty($cdnBasePath) ? rtrim($cdnBasePath, '/') : $resourceBasePath;
    }

    public static function renderScripts()
    {
        $hookHandler = HookHandler::getInstance()
            ->performAction('beforeRenderScripts');
        $scripts = Resource::getAll();
        print_r($scripts);exit;
    }

    public static function orderView($orderIds = array())
    {
        if (!is_array($orderIds)) {
            return array();
        }

        $crmType = Session::get('crmType', 'unknown');

        if ($crmType === 'unknown') {
            return array();
        }
        
        if($crmType == 'sixcrm')
        {            
            if(Session::has('orderViewResponse'))
            {
                return self::getOrderByID($orderIds);
            }
        }

        CrmPayload::replace(array(
            'orderIds'            => $orderIds,
            'meta.bypassCrmHooks' => true,
            'token' => Session::has('token')?Session::get('token'):'',
            'sessionId' => Session::has('sessionId')?Session::get('sessionId'):'',
        ));
        
        $configuration = new Configuration(Session::get('steps.1.configId'));

        $crmId = (int) $configuration->getCrmId();

        $crmClass = sprintf('Application\Model\%s', ucfirst($crmType));

        call_user_func(array(new $crmClass($crmId), 'orderView'));

        if (CrmResponse::get('success') !== true) {
            return array();
        }
        
        if($crmType == 'sixcrm')
        {
            Session::set('orderViewResponse', CrmResponse::get('result'));
            return self::getOrderByID($orderIds);
        }

        return CrmResponse::get('result');

    }
    
    
    public static function getOrderByID($orderIds = array())
    {        
        return $orders = Session::get('orderViewResponse');
//        $data = $result = array();
//        foreach ($orders['orders'] as $key => $orderInfo) {
//            $data[$orderInfo['id']] = $orderInfo;
//        }
//        
//        foreach ($orderIds as $key => $orderId) {
//            if (!empty($data[$orderId]) && is_array($data[$orderId])) {
//                $result[$key] = $data[$orderId];
//            } else {
//                $result[$key] = 'Not found!';
//            }
//        }
//        
//        return $result;
        
    }

    public static function pr($param = array(), $continue = true, $label = null)
    {
        if (!empty($label)) {
            echo '<p>-- ' . $label . ' --</p>';
        }

        echo '<pre>';
        print_r($param);
        echo '</pre><br />';

        if (!$continue) {
            die('-- code execution discontinued --');
        }
    }

    public static function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge(
                $files, self::rglob($dir . '/' . basename($pattern), $flags)
            );
        }
        return $files;
    }


    public static function checkExtensions($extentionName = '')
    {
       
        $extentionName = strlen($extentionName) ? $extentionName : Request::get('extention');
       
        $result = false;
        $extensions = Config::extensions();

        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== $extentionName)
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result = true;
            }
            break;
        }


        return $result;
    }
    
    public static function cleanString($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace('-', '_', $string)); 
    }

}
