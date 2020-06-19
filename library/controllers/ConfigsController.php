<?php

namespace Application\Controller;

use Application\Config;
use Application\Lang;
use Application\Model\Configuration;
use Application\Model\Settings;
use Application\Request;
use Application\Response;
use Application\Session;
use Detection\MobileDetect;
use Exception;

class ConfigsController
{

    public function __construct()
    {

    }

    public function appConfig()
    {
        $allowedCountries = Config::settings('allowed_country_codes');
        if (empty($allowedCountries)) {
            $allowedCountries = array('US');
        }
        $stepConfig = new StepConfig();
        Response::send(array(
            'basePath'       => Request::getBasePath(),
            'countries'      => $stepConfig->getCountriesAndStates(
                $allowedCountries
            ),
            'defaultCountry' => $allowedCountries[0],
            'lang'           => Lang::get(),
            'cardTypes'      => $this->getCardTypes(),
        ));
    }

    private function getCardTypes()
    {
        $cardTypes = Config::settings('allowed_card_types');
        $result    = array();
        foreach ($cardTypes as $cardType) {
            $result[$cardType] = ucfirst($cardType);
        }
        return $result;
    }

    public static function oldConfig()
    {
        $vars = array(
            'valid_class'   => 'no-error', 'error_class' => 'has-error',
            'loading_class' => 'loading',
        );

        try {
            $configuration                 = new Configuration();
            $vars['crm_type']              = $configuration->getCrmType();
            $vars['exit_popup_enabled']    = $configuration->exitPopupEnabled();
            $vars['exit_popup_element_id'] = $configuration->exitPopupElementId();
            $vars['exit_popup_page']       = $configuration->exitPopupPage();
        } catch (Exception $ex) {

        }

        $vars['offer_path']   = sprintf('%s/', rtrim(Request::getOfferPath(), '/'));
        $vars['current_step'] = Session::get('steps.current.id', 1);
        $vars['cbtoken']      = '';

        $vars['dev_mode']               = Config::settings('development_mode') ? 'Y' : 'N';
        $vars['show_validation_errors'] = Config::settings('show_validation_errors');

        $vars['allowed_tc'] = Config::settings('allowed_test_cards');
        if (empty($vars['allowed_tc']) || !is_array($vars['allowed_tc'])) {
            $vars['allowed_tc'] = array();
        }

        $vars['allowed_country_codes'] = Config::settings('allowed_country_codes');
        $vars['countries']             = Settings::getCountriesAndStates(
            $vars['allowed_country_codes']
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

        $detect                       = new MobileDetect;
        $vars['device_is_mobile']     = $detect->isMobile() ? true : false;
        $vars['pageType']             = Session::get('steps.current.pageType');
        $vars['enable_browser_back_button'] = Config::settings('enable_browser_back_button');
        $vars['disable_trialoffer_cardexp'] = Config::settings('disable_trialoffer_cardexp');

        if (!empty($extra)) {
            $extra = explode('&', $extra . '&');

            foreach ($extra as $key => $val) {
                if (!empty($val)) {
                    $item = explode('=', $val . '=');

                    if (!empty($item[0]) && !empty($item[1])) {
                        $vars[$item[0]] = $item[1];
                    }
                }
            }
        }

        return $vars;

    }

}
