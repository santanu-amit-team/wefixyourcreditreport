<?php

namespace Application;

use Exception;

class Config
{
    private static $configArray = null;
    private static $configPaths = null;

    private function __construct()
    {
        return;
    }

    public static function __callStatic($methodName, $arguments)
    {
        if (self::$configArray === null || self::$configPaths === null) {
            self::$configPaths = self::$configArray = array();
            $configFiles       = glob(LAZER_DATA_PATH . '*.data.json');
            foreach ($configFiles as $configFile) {
                $key = str_replace(
                    array(LAZER_DATA_PATH, '.data.json'), '', $configFile
                );
                self::$configPaths[$key] = $configFile;
                self::$configArray[$key] = array();
            }
        }
        $keyString = isset($arguments[0]) ? $arguments[0] : '';
        return call_user_func_array(
            array(get_called_class(), 'get'),
            array(
                $methodName,
                $keyString = isset($arguments[0]) ? $arguments[0] : null,
            )
        );
    }

    private static function get($configName, $keyString = null)
    {
        if (
            !array_key_exists($configName, self::$configPaths) ||
            !array_key_exists($configName, self::$configArray)

        ) {
            throw new Exception(
                sprintf('Config file %s missing!', $configName), 1002
            );
        }
        $configFile = self::$configPaths[$configName];
        $config     = self::$configArray[$configName];
        if (empty($config) && file_exists($configFile)) {
            $config = self::filterData(
                $configName, json_decode(
                    file_get_contents($configFile), true
                )
            );
            self::$configArray[$configName] = $config;
        }
        if ($keyString === null || !is_string($keyString)) {
            return $config;
        }
        $keyParts = explode('.', $keyString);
        foreach ($keyParts as $key) {
            if (!is_array($config) || !array_key_exists($key, $config)) {
                return null;
            }
            $config = $config[$key];
        }
        return $config;
    }

    private static function filterData($configName, $data)
    {
        $multipleData = array(
            'configurations', 'crms', 'campaigns', 'routings', 'upsellmanager'
        );
        if (in_array($configName, $multipleData)) {
            $result = array();
            foreach ($data as $value) {
                $result[$value['id']] = $value;
            }
            $data = $result;
            if ($configName === 'configurations') {
                self::prepareConfigurations($data);
            }
            if ($configName === 'crms') {
                self::prepareCrms($data);
            }
        } else if (is_array($data) && array_key_exists(0, $data)) {
            if ($configName === 'settings') {
                $data = $data[0];
                self::prepareSettings($data);
            }
            if ($configName === 'advanced') {
                $data = $data[0];
                self::prepareAdvanced($data);
            }
        }
        return $data;
    }

    private static function prepareCrms(&$data)
    {
        $isHttp = false;
        foreach (array_keys($data) as $crmId) {
            if (strchr($data[$crmId]['endpoint'], "http://")) {
                $isHttp = true;
            }
            $urlParts = parse_url($data[$crmId]['endpoint']);
            if (!empty($urlParts['scheme'])) {
                $data[$crmId]['endpoint'] = str_replace(
                        sprintf(
                                '%s://', $urlParts['scheme']
                        ), '', $data[$crmId]['endpoint']
                );
            }
            if (!$isHttp) {

                $data[$crmId]['endpoint'] = sprintf(
                        'https://%s', $data[$crmId]['endpoint']
                );
            } else {
                $data[$crmId]['endpoint'] = sprintf(
                        'http://%s', $data[$crmId]['endpoint']
                );
            }
            $isHttp = false;
        }
        
    }

    private static function prepareAdvanced(&$data)
    {
        $data['scrapper'] = json_decode($data['scrapper'], true);
        if (!is_array($data['scrapper'])) {
            $data['scrapper'] = array('enable' => false);
        }
    }

    private static function prepareSettings(&$data)
    {
        $data['allowed_country_codes'] = json_decode(
            $data['allowed_country_codes'], true
        );
        if (!is_array($data['allowed_country_codes'])) {
            $data['allowed_country_codes'] = array();
        }

        $data['allowed_card_types'] = json_decode(
            $data['allowed_card_types'], true
        );
        if (!is_array($data['allowed_card_types'])) {
            $data['allowed_card_types'] = array();
        }

        $data['allowed_test_cards'] = array_filter(explode(
            "\n", $data['allowed_test_cards']
        ));
    }

    private static function prepareConfigurations(&$data)
    {
        foreach (array_keys($data) as $key) {
            $data[$key]['campaign_ids'] = json_decode(
                $data[$key]['campaign_ids'], true
            );
            if (!is_array($data[$key]['campaign_ids'])) {
                $data[$key]['campaign_ids'] = array();
            }

            $data[$key]['campaign_ids'] = array_map(function ($value) {
                return (int) $value;
            }, $data[$key]['campaign_ids']);

            $data[$key]['split_campaign_ids'] = json_decode(
                $data[$key]['split_campaign_ids'], true
            );

            if (!is_array($data[$key]['split_campaign_ids'])) {
                $data[$key]['split_campaign_ids'] = array();
            }

            $data[$key]['split_campaign_ids'] = array_map(function ($value) {
                return (int) $value;
            }, $data[$key]['split_campaign_ids']);
        }
    }

}
