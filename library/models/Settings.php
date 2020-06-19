<?php

namespace Application\Model;

use Application\Config;
use Application\Http;
use Application\Logger;
use Application\Session;
use Detection\MobileDetect;

class Settings
{
	public static function getCountriesAndStates($countries = array()){
        $fileName = STORAGE_DIR . DS . 'countries-states.json';
        if (file_exists($fileName)) {
            $contents = json_decode(file_get_contents($fileName), TRUE);
            if (empty($countries)) {
                return $contents;
            } else {
                foreach ($countries as $country) {
                    if (!empty($contents[$country])) {
                        $output[$country] = $contents[$country];
                    }
                }
                return $output;
            }
        }
        return array();
	}
}