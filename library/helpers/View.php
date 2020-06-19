<?php

namespace Application\Helper;

use Application\Registry;
use ThreeDS\Integrator\Exception\Exception;

class View
{
    private static $instance = null;

    private $viewLocation;

    private function __construct($relativeDir)
    {
        if (empty($relativeDir)) {
            $relativeDir = Registry::system('systemConstants.VIEW_DIR');
        }
        
        $this->viewLocation = sprintf(
        	'%s%s%s', BASE_DIR, DS, trim($relativeDir, '/')
        );

        set_include_path(
            get_include_path() . PS . $this->viewLocation
        );
    }

    public static function getInstance($relativeDir = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($relativeDir);
        }
        return self::$instance;
    }

    public function load($viewName, $viewData)
    {
        if (stripos(strrev($viewName), 'lpt.') !== 0) {
            $viewName .= '.tpl';
        }
        require_once LIB_DIR . DS . 'functions.php';
        extract($viewData, EXTR_SKIP);
        if(!file_exists(sprintf('%s%s%s', $this->viewLocation , DS , $viewName))) {
            throw new \Exception('Template path has not found.');
        }
        require_once $viewName;
        exit(0);
    }
}
