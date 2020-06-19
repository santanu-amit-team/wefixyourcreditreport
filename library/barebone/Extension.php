<?php

namespace Application;

use Application\Helper\MaxHeap;
use Application\Registry;
use Application\Router;

class Extension
{

    private $allowedEvents = array(
        'beforeResolveRoute',
        'beforeControllerAction',
        'pageLoad',
        'afterBasicFormValidation',
        'afterCrmPayloadReady',
        'beforeAnyCrmRequest',
        'afterAnyCrmRequest',
        'beforePrepaidOrderCrmRequest',
        'afterPrepaidOrderCrmRequest',
        'beforeSplitOrderCrmRequest',
        'afterSplitOrderCrmRequest',
        'beforeHttpRequest',
        'afterHttpRequest',
        'beforeHeadTagClose',
        'afterBodyTagOpen',
        'beforeBodyTagClose',
        'beforeRenderScripts',
        'beforeBodyTagClose',
        'beforeViewRender',
        'beforeAnyDelayCrmRequest',
        'afterAnyDelayCrmRequest',
        'beforeDelayDBRequest'
    );

    private static $instance = null;

    private $activeHooks = array();

    private function __construct()
    {
        $trace = debug_backtrace();
        $matched = false;
        if(!empty($trace))
        {
            $documentRootDir = dirname(dirname(__FILE__));
            foreach ($trace as $value)
            {
                if(
                    preg_match("/library\/cron\.php/", $value['file']) && 
                    preg_match('/'.preg_quote($documentRootDir, '/').'/i', $value['file'])
                )
                {
                    $matched = true;
                }
            }
        }
        
        if (!$matched && (php_sapi_name() === 'cli' || defined('STDIN'))) {
            return;
        }

        if (is_array(Registry::system('routes'))) {
            $this->defineSystemRoutes(Registry::system('routes'));
        }

        if (is_array(Registry::system('hooks'))) {
            $this->defineSystemHooks(Registry::system('hooks'));
        }

        if (is_array(Registry::extension('routes'))) {
            $this->defineExtensionRoutes(Registry::extension('routes'));
        }

        if (is_array(Registry::extension('hooks'))) {
            $this->defineExtensionHooks(Registry::extension('hooks'));
        }
    }

    private function __clone()
    {
        return;
    }

    public static function initializeEngine()
    {
        if (self::$instance !== null) {
            return;
        }
        self::$instance = new self();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::initializeEngine();
        }
        return self::$instance;
    }

    private function defineSystemRoutes($routes)
    {
        foreach ($routes as $route) {
            if (empty($route['slug']) || empty($route['callback'])) {
                continue;
            }
            $path   = sprintf('%s', $route['slug']);
            $method = empty($route['method']) ? 'GET' : $route['method'];
            Router::add($path, $route['callback'], $method);
        }
    }

    private function defineSystemHooks($hooks)
    {
        foreach ($hooks as $hook) {
            $this->addHook($hook);
        }
    }

    private function defineExtensionRoutes($routeRegistry)
    {
        foreach ($routeRegistry as $extension => $routes) {
            $urlPrefix = sprintf('/extensions/%s', strtolower($extension));
            foreach ($routes as $route) {
                if (empty($route['slug']) || empty($route['callback'])) {
                    continue;
                }
                $path   = sprintf('%s/%s', $urlPrefix, $route['slug']);
                $method = empty($route['method']) ? 'GET' : $route['method'];
                Router::add($path, $route['callback'], $method);
            }
        }
    }

    private function defineExtensionHooks($hookRegistry)
    {
        foreach ($hookRegistry as $extension => $hooks) {
            foreach ($hooks as $hook) {
                $this->addHook($hook);
            }
        }
    }

    public function addHook($hook = array())
    {
        if (empty($hook['event']) || empty($hook['callback'])) {
            return;
        }
        if (!in_array($hook['event'], $this->allowedEvents)) {
            return;
        }
        if (!array_key_exists($hook['event'], $this->activeHooks)) {
            $this->activeHooks[$hook['event']] = new MaxHeap();
        }
        if (
            empty($hook['priority']) || !is_integer($hook['priority'])
        ) {
            $hook['priority'] = 500;
        }
        $this->activeHooks[$hook['event']]->insert(
            array(
                $hook['callback'], $hook['priority'],
            )
        );
    }

    public function performEventActions($eventName)
    {
        if (!array_key_exists($eventName, $this->activeHooks)) {
            return;
        }
        $extractedNodes = array();
        while ($this->activeHooks[$eventName]->valid()) {
            $node = $this->activeHooks[$eventName]->extract();
            $meta = explode('@', $node[0]);
            call_user_func(array((new $meta[0]()), $meta[1]));
            array_push($extractedNodes, $node);
        }
        foreach ($extractedNodes as $node) {
            $this->activeHooks[$eventName]->insert($node);
        }
        $this->activeHooks[$eventName]->rewind();
    }

}
