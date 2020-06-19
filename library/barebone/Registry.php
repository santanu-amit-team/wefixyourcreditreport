<?php

namespace Application;

class Registry
{

    private static $systemRegistry    = null;
    private static $extensionRegistry = null;

    private function __construct()
    {
        return;
    }

    public static function system($keyString = null)
    {
        if (self::$systemRegistry === null) {
            self::$systemRegistry = require_once LIB_DIR . DS . 'registry.php';
            if (!is_array(self::$systemRegistry)) {
                self::$systemRegistry = array();
            }
        }
        return self::get(self::$systemRegistry, $keyString);
    }

    public static function extension($keyString = null)
    {
        if (self::$extensionRegistry === null) {
            self::$extensionRegistry = json_decode(
                file_get_contents(STORAGE_DIR . DS . 'registry.json'), true
            );
            if (!is_array(self::$extensionRegistry)) {
                self::$extensionRegistry = array();
            }
        }
        return self::get(self::$extensionRegistry, $keyString);
    }

    private static function get($data, $keyString)
    {
        if ($keyString === null || !is_string($keyString)) {
            return $data;
        }
        $keyParts = explode('.', $keyString);
        foreach ($keyParts as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    public static function reloadExtension()
    {
        self::$extensionRegistry = json_decode(
            file_get_contents(STORAGE_DIR . DS . 'registry.json'), true
        );
        if (!is_array(self::$extensionRegistry)) {
            self::$extensionRegistry = array();
        }
    }

}
