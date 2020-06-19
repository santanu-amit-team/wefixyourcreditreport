<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';

use Application\Config;
use Application\Extension;
use Application\Helper\Provider;
use Application\Helper\View;
use Application\Registry;
use Application\Request;
use Application\Resource;
use Application\Session;
use Detection\MobileDetect;

class App
{
    private static $options = array();

    public static function initialize()
    {
        self::$options = array(
            'config_id' => 0, 'version'  => 'desktop', 'resetSession'  => false,
            'step'      => 0, 'pageType' => 'landingPage', 'ajaxDelay' => 0,
        );
        Bootstrap::initialize('static');
    }

    private function __construct()
    {
        return;
    }

    public static function run($options)
    {
        try {
            self::$options = array_replace_recursive(
                self::$options, $options
            );
            self::updateRequiredSessionDataForCurrentStep();
            
            Extension::getInstance()->performEventActions('beforeViewRender');
            $appVersion = Session::get('extensions.TemplateSwitcher.version');
            if(!empty($appVersion)) {
                self::$options['version'] = $appVersion;
            }
            
            self::decideRedirectionBasedOnConfigRules();
            Extension::getInstance()->performEventActions('pageLoad');
            self::registerScripts();

            if (
                empty(self::$options['tpl_vars']) ||
                !is_array(self::$options['tpl_vars'])
            ) {
                self::$options['tpl_vars'] = array();
            }
            Session::set('extensions.bypass', self::$options['tpl_vars']);
            self::evaluateCustomPixelTokens();
            $systemTokens      = self::getSystemTokens();
            $configTokens      = self::getConfigTokens();
            $transactionTokens = self::getTransactionTokens();
            $orderDataTokens   = self::getOrderDataTokens();
            
            $mobile_custom_path = Config::settings('mobile_template_path');
            $detector = new MobileDetect();
            $device   = $detector->isMobile() ? 'mobile' : 'desktop';
            
            if ($detector->isTablet() && Config::settings('redirect_tablet_screen')) {
                $device = 'mobile';
            } else if ($detector->isTablet()) {
                $device = 'desktop';
            }
            
            if(!empty($mobile_custom_path) && $device == 'mobile')
            {
                self::$options['version'] = $mobile_custom_path;
            }
            
            View::getInstance()->load(
                sprintf(
                    '%s%s%s', self::$options['version'], DS, self::$options['tpl']
                ),
                array_replace_recursive(
                    $systemTokens, $configTokens, $transactionTokens,
                    $orderDataTokens, self::$options['tpl_vars']
                )
            );
            exit(0);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
        }
    }
    
    private static function evaluateCustomPixelTokens()
    {
        if(!empty(self::$options['tpl_vars']))
        {
            $custom_pixel_token = array();
            foreach (self::$options['tpl_vars'] as $key => $value)
            {
                if (preg_match('/custom_pixel/i', $key, $m)) {

                    $custom_pixel_token[$key] = $value;
                    Session::remove('extensions.bypass.'.$key);
                }
            }

            if(!empty($custom_pixel_token)) {
                Session::set('custom_pixel_tokens', $custom_pixel_token);
            }

        }
    }

    private static function getConfigTokens()
    {
        $cardTypes        = Config::settings('allowed_card_types');
        $allowedCardTypes = array();
        foreach ($cardTypes as $cardType) {
            $allowedCardTypes[$cardType] = $cardType;
            if ($cardType === 'master') {
                $allowedCardTypes[$cardType] = 'Master Card';
            }
        }
        $config = array(
            'offer_path'         => sprintf('%s/', rtrim(Request::getOfferPath(), '/')),
            'allowed_card_types' => $allowedCardTypes,
        );
        return array('config' => $config);
    }
    
    
    public static function getDelayPixels()
    {
        $fired = Session::get('extensions.delayedTransactions.delaypixels.fired');        
        $pixels = Session::get('extensions.delayedTransactions.pixels');
        $isEnabled = Config::extensionsConfig('DelayedTransactions.allow_pixel_fire_for_delay');
        $pString = '';
        if(!empty($pixels) && empty($fired) && !empty($isEnabled)){
            foreach ($pixels  as  $value)
            {
               $pString .= $value."\n";
            }
            Session::set('extensions.delayedTransactions.delaypixels.fired',true);
        }
        return $pString;
    }

    private static function getSystemTokens()
    {
        return array(
            'path' => self::getResourcePaths(),
        );
    }
    
    private static function getTransactionTokens()
    {
        $tokens = array();
        if (Session::has('customer')) {
            $tokens['customer'] = Session::get('customer');
        }
        if (Session::has('steps')) {
            $tokens['steps'] = Session::get('steps');
        }
        return $tokens;
    }

    private static function getResourcePaths()
    {
        $paths      = array();
        $appVersion = self::$options['version'];

        $localDirs = glob(APP_DIR . DS . $appVersion . DS . '*', GLOB_ONLYDIR);
        if (empty($localDirs) && !is_array($localDirs)) {
            $localDirs = array();
        }

        foreach ($localDirs as $dirPath) {
            $dirName = str_replace(
                APP_DIR . DS . $appVersion . DS, '', $dirPath
            );
            $paths[$dirName] = sprintf(
                '%s/app/%s/%s',
                Provider::getResourceBasePath($dirName), $appVersion, $dirName
            );
        }

        $assetsDirs = glob(BASE_DIR . DS . 'assets' . DS . '*', GLOB_ONLYDIR);
        if (empty($assetsDirs) && !is_array($assetsDirs)) {
            $assetsDirs = array();
        }

        foreach ($assetsDirs as $dirPath) {
            $dirName = str_replace(
                BASE_DIR . DS . 'assets' . DS, '', $dirPath
            );
            $paths[sprintf('assets_%s', $dirName)] = sprintf(
                '%s/assets/%s',
                Provider::getResourceBasePath($dirName), $dirName
            );
        }
        $paths['alternative_cdn_path'] = Provider::getAlternativeCdnPath();
        return $paths;
    }

    private static function getOrderDataTokens()
    {
        $shippingData = array(
            'shipping_first_name'     => Session::get('customer.firstName', ''),
            'shipping_last_name'      => Session::get('customer.lastName', ''),
            'email_address'           => Session::get('customer.email', ''),
            'customers_telephone'     => Session::get('customer.phone', ''),
            'shipping_street_address' => Session::get('customer.shippingAddress1', ''),
            'shipping_postcode'       => Session::get('customer.shippingZip', ''),
            'shipping_city'           => Session::get('customer.shippingCity', ''),
            'shipping_state'          => Session::get('customer.shippingState', ''),
            'shipping_country'        => Session::get('customer.shippingCountry', ''),
        );

        $billingData = array_filter(array(
            'billing_first_name'     => Session::get('customer.billingFirstName', ''),
            'billing_last_name'      => Session::get('customer.billingLastName', ''),
            'billing_street_address' => Session::get('customer.billingAddress1', ''),
            'billing_postcode'       => Session::get('customer.billingZip', ''),
            'billing_city'           => Session::get('customer.billingCity', ''),
            'billing_state'          => Session::get('customer.billingState', ''),
            'billing_country'        => Session::get('customer.billingCountry', ''),
        ));

        if (empty($billingData)) {
            foreach ($shippingData as $key => $value) {
                if (strpos($key, 'shipping_') === 0) {
                    $billingKey               = str_replace('shipping_', 'billing_', $key);
                    $billingData[$billingKey] = $value;
                }
            }
        }

        return array(
            'order_data' => array_replace($shippingData, $billingData),
        );
    }

    private static function updateRequiredSessionDataForCurrentStep()
    {
        self::checkAndResetSessionIfRequired();

        if (!Session::has('appVersion')) {
            Session::set('appVersion', self::$options['version']);
        }

        $currentStep = self::getCurrentStepInformation();

        $crm = Config::crms(sprintf('%d', $currentStep['crmId']));
        if (!Session::has('crmType') && !empty($crm['crm_type'])) {
            Session::set('crmType', $crm['crm_type']);
        }

        $currentStepId = (int) Session::get('steps.current.id', 1);

        if (
            (strpos($currentStep['pageType'], 'upsellPage') === 0 ||
                $currentStep['pageType'] === 'thankyouPage') &&
            $currentStepId !== $currentStep['id']
        ) {
            Session::set(
                'steps.previous', Session::get('steps.current', array())
            );
        }

        unset($currentStep['crmId']);
        Session::set('steps.current', $currentStep);

        if ($currentStep['pageType'] !== 'thankyouPage') {
            Session::set('steps.next.link', $currentStep['goto']);
        } else {
            Session::remove('steps.next');
        }

        $previousData = Session::get(sprintf('steps.%d', $currentStep['id']));
        if (is_array($previousData)) {
            $currentStep = array_replace_recursive($previousData, $currentStep);
        }
        Session::set(sprintf('steps.%d', $currentStep['id']), $currentStep);

    }

    private static function checkAndResetSessionIfRequired()
    {
        $clearAndResetSession = false;

        if (self::$options['resetSession'] === true) {
            $clearAndResetSession = true;
        } else {
            $currentUrl = preg_replace('/^https?:\/\//', '', trim(str_replace(
                                    '/index.php', '', strtok(Request::getUri(), '?')
                            ), '/'));

            $baseUrl = preg_replace(
                    '/^https?:\/\//', '', trim(Request::getBaseUrl(), '/')
            );

            if ($currentUrl === $baseUrl) {
                $clearAndResetSession = true;
            }
        }
        
        $sesCartData = '';
        $cartData = Session::get('extensions.cart');
        if (!empty($cartData)) {
            $sesCartData = $cartData;
        }
        if ($clearAndResetSession === true) {
            Session::clear();
            Session::set('queryParams', Request::query()->all());
            self::updateRequiredSessionDataForAffiliates();
        }
        if (!empty($sesCartData)) {
            Session::set('extensions.cart', $sesCartData);
        }
    }

    private static function getCurrentStepInformation()
    {
        $currentStep = array(
            'id'        => (int) self::$options['step'],
            'configId'  => (int) self::$options['config_id'],
            'goto'      => (string) self::$options['go_to'],
            'pageType'  => (string) self::$options['pageType'],
            'ajaxDelay' => (int) self::$options['ajaxDelay'],
        );     

        if (strpos(strrev($currentStep['goto']), 'php.') !== 0) {
            $currentStep['goto'] = sprintf('%s.php', $currentStep['goto']);
        }

        $config = Config::configurations(sprintf('%d', $currentStep['configId']));

        $currentStep['crmId'] = empty($config['crm_id']) ? 0 : (int) $config['crm_id'];
        $currentStep['link']  = basename(Request::server()->get('SCRIPT_NAME'));

        return $currentStep;
    }

    private static function updateRequiredSessionDataForAffiliates()
    {
        $affiliatesMapping = array(
            'nid'    => array('NID', 'aid', 'nid', 'n_id'),
            'afId'    => array('AFID', 'afid', 'af_id'),
            'affId'   => array('AFFID', 'affid', 'aff_id'),
            'sId'     => array('SID', 'sid'),
            'c1'      => array('C1', 'sourceValue1','subid', 's1'),
            'c2'      => array('C2', 'sourceValue2', 's2'),
            'c3'      => array('C3', 'sourceValue3', 's3'),
            'c4'      => array('C4', 'sourceValue4', 's4'),
            'c5'      => array('C5', 'sourceValue5', 's5'),
            'c6'      => array('C6', 'sourceValue6', 's6'),
            'aId'     => array('AID', 'aid'),
            'opt'     => array('OPT', 'opt'),
            'clickId' => array('click_id'),
        );
        $queryKeys  = array_keys(Request::query()->all());
        $affiliates = array();
        foreach (array_keys($affiliatesMapping) as $key) {
            if (in_array($key, $queryKeys)) {
                $affiliates[$key] = Request::query()->get($key);
                continue;
            }
            foreach ($affiliatesMapping[$key] as $alias) {
                if (in_array($alias, $queryKeys)) {
                    $affiliates[$key] = Request::query()->get($alias);
                    break;
                }
            }
        }
        Session::set('affiliates', $affiliates);
    }

    private static function decideRedirectionBasedOnConfigRules()
    {
        if (
            !Config::settings('enable_mobile_version') &&
            self::$options['version'] === 'desktop'
        ) {
            return;
        } else if (!Config::settings('enable_mobile_version')) {
            self::redirectToDevice('desktop');
        }

        if (Config::settings('allow_direct_access')) {
            return;
        }

        if (
            Config::settings('mobile_version_only') &&
            !preg_match("/mobile/", self::$options['version'])
        ) {
            self::redirectToDevice('mobile');
        } else if (Config::settings('mobile_version_only')) {
            return;
        }

        $detector = new MobileDetect();
        $device   = $detector->isMobile() ? 'mobile' : 'desktop';

        if ($detector->isTablet() && Config::settings('redirect_tablet_screen')) {
            $device = 'mobile';
        } else if ($detector->isTablet()) {
            $device = 'desktop';
        }
        
        if (!preg_match("/".$device."/", self::$options['version'])) {
            self::redirectToDevice($device);
        }

    }

    private static function redirectToDevice($device = 'desktop')
    {
        $getOfferPath = sprintf('%s/', rtrim(Request::getOfferPath(), '/'));
        if ($device === 'desktop') {
            $url = $getOfferPath;
        } else {
            $url = sprintf(
                '%s%s', $getOfferPath, Config::settings('mobile_path')
            );
        }
        $queryParams = http_build_query(Request::query()->all());
        $queryString = empty($queryParams) ? '/' : '/?' . $queryParams;
        header(sprintf('Location: %s', rtrim($url, '/') . $queryString));
        exit();
    }

    private static function registerScripts()
    {
        if (is_array(Registry::system('scripts'))) {
            foreach (Registry::system('scripts') as $key => $path) {
                Resource::register('script', $key, $path);
            }
        }

        if (is_array(Registry::extension('scripts'))) {
            foreach (
                Registry::extension('scripts') as $extensionName => $scripts
            ) {
                foreach ($scripts as $key => $relativePath) {
                    $path = sprintf(
                        'extensions/%s/%s', $extensionName, $relativePath
                    );
                    Resource::register('script', $key, $path);
                }
            }
        }
    }

}

App::initialize();
