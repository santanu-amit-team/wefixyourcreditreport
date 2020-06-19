<?php

use Application\Config;
use Application\Model\Settings;

$settings = new Settings();
$allowedCountries = Config::settings('allowed_country_codes');
$countries = $settings->getCountriesAndStates(
        $allowedCountries
);
$countryLangMappingMetas = explode("\n", Config::settings('country_lang_mapping'));
        $countryLangMapping      = array();
        foreach ($countryLangMappingMetas as $countryLangMappingMeta) {
            $parts = explode('|', $countryLangMappingMeta);
            if (!empty($parts[0]) && !empty($parts[1]) && !empty($parts[2])) {
                $countryLangMapping[$parts[0]] = array(
                    'state' => $parts[1],
                    'zip'   => $parts[2],
                );
            }
        }
$vars['country_lang_mapping'] = $countryLangMapping;
$vars['allowed_country_codes'] = $allowedCountries;
$vars['countries'] = $countries;
