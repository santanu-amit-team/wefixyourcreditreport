<?php

namespace Application;

class Lang
{
    private static $langPaths   = null;
    private static $langArray   = null;
    private static $currentLang = 'en';

    public static function get($keyString = null)
    {
        if (self::$langArray === null || self::$langPaths === null) {
            $langFiles       = glob(LANG_DIR . DS . '*.php');
            self::$langPaths = array();
            foreach ($langFiles as $langFile) {
                $key = str_replace(
                    array(LANG_DIR . DS, '.php'), '', $langFile
                );
                self::$langPaths[$key] = $langFile;
                self::$langArray[$key] = array();
            }
        }
        $langFile = self::$langPaths[self::$currentLang];
        $data     = self::$langArray[self::$currentLang];
        if (empty($data) && file_exists($langFile)) {

            $data = require_once $langFile;

            self::$langArray[self::$currentLang] = $data;
        }
        if (empty($keyString)) {
            return $data;
        }
        $keys = explode('.', $keyString);
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

}
