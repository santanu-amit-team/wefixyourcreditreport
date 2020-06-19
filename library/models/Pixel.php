<?php

namespace Application\Model;

use Application\Config;
use Application\Http;
use Application\Logger;
use Application\Request;
use Application\Session;
use Detection\MobileDetect;
use Application\Helper\Provider;

class Pixel
{

    private $previousStepId, $currentStepId, $previousConfigId, $currentConfigId;
    private $currentStepPixels;

    public function __construct()
    {
        $this->currentStepPixels = array();
        $this->currentStepId     = (int) Session::get('steps.current.id');
        $this->currentConfigId   = (int) Session::get('steps.current.configId');
        if (Session::has('steps.previous.configId') !== true) {
            $this->previousConfigId = (int) Session::get('steps.current.configId');
        }
        else
        {
            $this->previousConfigId = (int) Session::get('steps.previous.configId');
        }
        $this->previousStepId   = (int) Session::get('steps.previous.id');
        
        $this->loadPixelsByConfigId($this->previousConfigId);
    }

    public function getHeadPixelsAsHtml()
    {
        $headPixelsHtml = '';
        foreach ($this->currentStepPixels as $pixel) {
            if ($pixel['pixel_placement'] !== 'head') {
                continue;
            }
            $headPixelsHtml .= $this->parsePixel($pixel);
        }
        return $headPixelsHtml;
    }

    public function getTopPixelsAsHtml()
    {
        $topPixelsHtml = '';
        foreach ($this->currentStepPixels as $pixel) {
            if ($pixel['pixel_placement'] !== 'top') {
                continue;
            }
            $topPixelsHtml .= $this->parsePixel($pixel);
        }
        return $topPixelsHtml;
    }

    public function getBottomPixelsAsHtml()
    {
        $bottomPixelsHtml = '';
        foreach ($this->currentStepPixels as $pixel) {
            if ($pixel['pixel_placement'] !== 'bottom') {
                continue;
            }
            $bottomPixelsHtml .= $this->parsePixel($pixel);
        }
        return $bottomPixelsHtml;
    }
    
    public function setClickID()
    {

        if (Session::get('pixels.clickIdGenerated') === true) {
            return;
        }

        $this->loadPixelsByConfigId($this->currentConfigId);

        foreach ($this->currentStepPixels as $pixel) {
            if ($this->isValidDevice($pixel) === false) {
                continue;
            }

            if ($this->isValidAffiliates($pixel) === false) {
                continue;
            }

            if (!empty($pixel['click_pixel'])) {
                $clickId = $this->getClickID($pixel['click_pixel']);
                if (!empty($clickId)) {
                    Session::set('affiliates.clickId', $clickId);
                    Session::set('queryParams.click_id', $clickId);
                    Session::set('pixels.clickIdGenerated', true);
                }
            }

        }
    }

    public function hasClickPixels()
    {
        $this->loadPixelsByConfigId($this->currentConfigId);
        foreach ($this->currentStepPixels as $pixel) {
            if (!empty($pixel['click_pixel'])) {
                return true;
            }
        }
    }

    private function loadPixelsByConfigId($configId)
    {
        $this->currentStepPixels = array();
        $pixels                  = Config::pixels();
        foreach ($pixels as $pixel) {
            if (in_array($configId, explode(',',$pixel['configuration_id']))) {
                array_push($this->currentStepPixels, $pixel);
            }
        }
        $this->updatePixelsPriority();
    }
    
    public function updatePixelsPriority()
    {
        $this->currentStepPixels = $this->multid_sort($this->currentStepPixels, 'pixel_firing_priority');;
    }
    
    public function multid_sort($arr, $index) {
        $temparr = array();
        foreach ($arr as $key => $value) {
            $temparr[$key] = !empty($value[$index]) ? $value[$index] : 1;
        }
        array_multisort($temparr, SORT_DESC, $arr);
        usort($arr, $this->sortByPriority);
        return $arr;
    }
    
    public function sortByPriority($a, $b) {
        return $a['pixel_firing_priority'] - $b['pixel_firing_priority'];
    }
    
    
    private function parsePixel($pixel)
    {
        if ($this->pixelTriggers($pixel) === false) {
            return '';
        }    

        switch ($pixel['pixel_type']) {
            
            case 'HTML':
                return $this->parseHtmlPixel($pixel);
            
            case 'Postback':
                return $this->parsePostbackPixel($pixel);
                
            case 'Postback + 3rd Party Pixels':
                return $this->parsePostback3rdPartyPixel($pixel);

            case 'Conversion Pixel (HTML)':
                return $this->parseHtmlPixel($pixel);

            case 'Conversion Pixel (Server to Server)':
                return $this->parsePostbackPixel($pixel);

            case 'Conversion Pixel (HTML+Postback)':
                return $this->parsePostback3rdPartyPixel($pixel);

            case 'Cake Postback':
                return $this->parseCakePostbackPixel($pixel);

            case 'Cake Postback + 3rd Party Pixels':
                return $this->parseCakePostback3rdPartyPixel($pixel);

            case 'HasOffers Postback':
                return $this->parseHasOffersPostbackPixel($pixel);

            case 'HasOffers Postback + 3rd Party Pixels':
                return $this->parseHasOffersPostback3rdPartyPixel($pixel);

            case 'HitPath Postback':
                return $this->parseHitPathPostbackPixel($pixel);

            case 'HitPath Postback + 3rd Party Pixels':
                return $this->parseHasOffersPostback3rdPartyPixel($pixel);
                
            case 'General':
                $isExtensionEnable = Provider::checkExtensions('TrafficLoadBalancer');
                if(!$pixel['ignore_pixel_on_order_filter'] && $isExtensionEnable) {
                    $genPixelToFire = array();
                    if( Session::has("GeneralPixelToFire")) {
                        $genPixelToFire = Session::get("GeneralPixelToFire");
                    }

                    array_push($genPixelToFire, array(
                        'content' => $this->parseHtmlPixel($pixel),
                        'type' => $pixel['pixel_placement']
                    ));

                    Session::set("GeneralPixelToFire", $genPixelToFire);
                    return "";
                }

                return $this->parseHtmlPixel($pixel);
                
            case 'Decline':
                return $this->parseHtmlPixel($pixel);
                
            case 'Submission':
                return $this->parseHtmlPixel($pixel);    
                
            case 'On Decline':
                return $this->parseHtmlPixel($pixel);
                
            case 'On Form Submission':
                return $this->parseHtmlPixel($pixel);
        }
    }

    protected function parseHitPathPostback3rdPartyPixels($pixel)
    {
        $url = $this->parseTokens($pixel['convert_pixel']);
        $this->doPostBack($url);

        if (!empty($pixel['third_party_postback_url'])) {
            $url = $this->parseTokens(trim($pixel['third_party_postback_url']));
            $this->doPostBack($url);
        }

        if (!empty($pixel['third_party_html'])) {
            return $this->parseTokens(trim($pixel['third_party_html']));
        }
    }

    protected function parseHitPathPostbackPixel($pixel)
    {
        $url = $this->parseTokens($pixel['convert_pixel']);
        $this->doPostBack($url);
        return sprintf(
            '<!-- HitPath Postback Triggered%s%s%s/HitPath Postback Triggered -->',
            PHP_EOL, $url, PHP_EOL
        );
    }

    protected function parseHasOffersPostback3rdPartyPixel($pixel)
    {
        $url = $this->parseTokens($pixel['convert_pixel']);
        $this->doPostBack($url);

        if (!empty($pixel['third_party_postback_url'])) {
            $url = $this->parseTokens($trim($pixel['third_party_postback_url']));
            $this->doPostBack($url);
        }

        if (!empty($pixel['third_party_html'])) {
            return $this->parseTokens(trim($pixel['third_party_html']));
        }
    }

    protected function parseHasOffersPostbackPixel($pixel)
    {
        $url = $this->parseTokens($pixel['convert_pixel']);
        $this->doPostBack($url);
        return sprintf(
            '<!-- HasOffers Postback Triggered%s%s%s/HasOffers Postback Triggered -->',
            PHP_EOL, $url, PHP_EOL
        );
    }

    protected function parseCakePostback3rdPartyPixel($pixel)
    {
        if (!empty($pixel['convert_pixel'])) {
            $url = $this->parseTokens(trim($pixel['convert_pixel']));
            $this->doPostBack($url);
        }
        if (!empty($pixel['third_party_postback_url'])) {
            $url = $this->parseTokens(trim($pixel['third_party_postback_url']));
            $this->doPostBack($url);
        }
        if (!empty($pixel['third_party_html'])) {
            return $this->parseTokens(trim($pixel['third_party_html']));
        }
    }

    protected function parseCakePostbackPixel($pixel)
    {
        $url = $this->parseTokens($pixel['convert_pixel']);
        $this->doPostBack($url);
        return sprintf(
            '<!-- Cake Postback Triggered%s%s%s/Cake Postback Triggered -->',
            PHP_EOL, $url, PHP_EOL
        );
    }

    protected function parsePostback3rdPartyPixel($pixel)
    {
        if (!empty($pixel['postback_url'])) {
            $url = $this->parseTokens(trim($pixel['postback_url']));
            $this->doPostBack($url);
        }

        if (!empty($pixel['third_party_postback_url'])) {
            $url = $this->parseTokens(trim($pixel['third_party_postback_url']));
            $this->doPostBack($url);
        }

        if (!empty($pixel['third_party_html'])) {
            return $this->parseTokens(trim($pixel['third_party_html']));
        }

        return '';
    }

    protected function parsePostbackPixel($pixel)
    {
        $url = $this->parseTokens($pixel['postback_url']);
        $this->doPostBack($url);
        return '';
    }

    protected function parseHtmlPixel($pixel)
    {
        return $this->parseTokens($pixel['html_pixel']);
    }

    protected function htmlPixel($pixelHtml)
    {
        return sprintf('<img src="%s" style="display: none">', $pixelHtml);
    }

    private function parseTokens($stringWithTokens)
    {
        return preg_replace_callback(
            "/\{([a-z0-9_]+)\}/i", function ($data) {

                if ($data[1] === 'order_id' || $data[1] === 'orderId') {
                    return Session::get(
                        sprintf('steps.%d.orderId', $this->previousStepId)
                    );
                }
                
                if ($data[1] === 'split_order_id' || $data[1] === 'splitOrderId') {
                    return Session::get(
                        sprintf('steps.%d.splitOrder.orderId', $this->previousStepId)
                    );
                }
                
                if ($data[1] === 'order_total' || $data[1] === 'orderTotal') {
                    $orderTotal = $this->getOrderTotal();
                    return $orderTotal;
                }
                
                if ($data[1] === 'campaign_id' || $data[1] === 'campaignId') {
                	$products = Session::get(
			                        sprintf('steps.%d.products', $this->previousStepId)
			                    );
                    return $products[0]['campaignId'];
                }

                if ($data[1] === 'card_type' || $data[1] === 'cardType') {
                	return Session::get('customer.cardType');
                }
                
                if (preg_match('/custom_pixel/i', $data[1])) {
                    return Session::get('custom_pixel_tokens.'.$data[1]);
                }
                
                $formData = array(
                    'firstName',
                    'lastName',
                    'email',
                    'phone',
                    'shippingCity',
                    'shippingState',
                    'shippingCountry'
                );
                
                foreach($formData as $formTokens) {
                    if ($data[1] === $formTokens) {
                        return Session::get(
                            sprintf('customer.%s', $formTokens)
                        );
                    }
                }

                if ($data[1] === 'campaign_id' || $data[1] === 'campaignId') {
                	$products = Session::get(
			                        sprintf('steps.%d.products', $this->previousStepId)
			                    );
                    return $products[0]['campaignId'];
                }

                if ($data[1] === 'card_type' || $data[1] === 'cardType') {
                	return Session::get('customer.cardType');
                }

                $param = strtolower(str_replace('_', '', $data[1]));

                $affiliates = array_change_key_case(Session::get('affiliates'));

                foreach ($affiliates as $key => $value) {
                    if ($param === $key) {
                        return rawurlencode($value);
                    }
                }

            }, $stringWithTokens
        );
    }
    
    private function getOrderTotal()
    {
        $products = Session::get(
                sprintf('steps.%d.products', $this->previousStepId)
            );
        $orderTotal = 0.00;
        
        if(!empty($products)) 
        {
            foreach ($products as $value)
            {
                $orderTotal += ($value['productPrice'] * $value['productQuantity']) + $value['shippingPrice'];
            }
        }
        
        return $orderTotal;
    }

    protected function doPostBack($postbackUrl)
    {
        $response = Http::get(
            $postbackUrl, array(), array(CURLOPT_CONNECTTIMEOUT => 5)
        );
        if (DEV_MODE) {
            Logger::write('postBack', $response);
            Session::set('postBack_request.'.$this->previousStepId, $postbackUrl);
            Session::set('postBack_response.'.$this->previousStepId, $response);
        }
        if (!empty($response['curlError'])) {
            return '';
        }
        return $response;
    }
    
    protected function getClickID($url)
    {
        $response = $this->doPostBack($this->parseTokens($url));
        if (!empty($response['curlError'])) {
            return '';
        }

        $data = explode('=', $response);
        if (!empty($data) && is_array($data) && !empty($data[1])) {
            return $data[1];
        }

        return '';
    }
    
    protected function getScheduletime($pixel)
    {   
        date_default_timezone_set($pixel['time_zone']);
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i');
        date_default_timezone_set(Config::settings('app_timezone'));
        
        return array(
            'startDate'     => $pixel['start_date'],
            'endDate'       => $pixel['end_date'],
            'startTime'     => $pixel['start_time'],
            'endTime'       => $pixel['end_time'],
            'currentDate'   => $currentDate,
            'currentTime'   => $currentTime
        );
    }

    protected function pixelTriggers($pixel)
    {
        if(Session::has(sprintf('extensions.delayedTransactions.steps.%d.main',$this->previousStepId)) && $pixel['pixel_type'] != 'General')
        {
            return false;
        }
        
        $isTestOrder = Session::get('steps.meta.isTestCard');
        if(!empty($isTestOrder) && !empty($pixel['fire_live_transactions']))
        {
            return false;
        }
        
        if(
            !empty($pixel['enable_custom_firing_schedule']))
        {
            $dateInfo = $this->getScheduletime($pixel);

            if(
                !empty($dateInfo['startDate']) && 
                !empty($dateInfo['endDate']) && 
                (
                    $dateInfo['startDate'] > $dateInfo['currentDate'] ||
                    $dateInfo['endDate'] < $dateInfo['currentDate']
                )
            ){
                return false;
            }
            
            if(
                !empty($dateInfo['startTime']) && 
                !empty($dateInfo['endTime']) && 
                (
                    $dateInfo['startTime'] > $dateInfo['currentTime'] ||
                    $dateInfo['endTime'] < $dateInfo['currentTime']
                )
            ){
                return false;
            }
        }
        
        if (Session::has(
                sprintf('steps.%d.pending', $this->previousStepId)
            )) 
        {
            return false;
        }
        
        if (Session::get(sprintf('steps.meta.skipPixelFire')) === true && $pixel['pixel_type'] != 'General') {
            return false;
        }
        
        $isValidDevice = $this->isValidDevice($pixel);
        
        if(
            $pixel['pixel_type'] == 'Submission' && 
            $isValidDevice && 
            !Session::get('steps.meta.isPrepaidFlow') && 
            !Session::get('steps.meta.isScrapFlow') 
            )
        {
            Session::set(
                sprintf(
                    'submissionPixels.pixel.%d.%s',
                    $pixel['id'], $pixel['pixel_placement']
                ), $pixel['html_pixel']
            );
        }

        if (
            !Session::has(
                sprintf('steps.%d.orderId', $this->previousStepId)
            )
                && 
                $pixel['pixel_type'] != 'General'
                && 
                $pixel['pixel_type'] != 'Decline'
        ) {
            return false;
        }

        if (
            Session::get(
                sprintf(
                    'pixels.fireStatus.%d.%d',
                    $this->previousConfigId, $pixel['id']
                )
            ) === true
                && 
                $pixel['pixel_type'] != 'General'
                && 
                $pixel['pixel_type'] != 'Decline'
        ) {
            return false;
        }

        if (
            $pixel['prepaid'] === false &&
            Session::get('steps.meta.isPrepaidFlow') === true  && 
            $pixel['pixel_type'] != 'General'
        ) {
            return false;
        }

        if (
            Session::get('steps.meta.isScrapFlow') === true  && 
            $pixel['pixel_type'] != 'General'
        ) {
            return false;
        }

        if (false === $this->isValidAffiliates($pixel)) {
            return false;
        }

        if (!empty($pixel['page']) && !empty($pixel['enable_page'])) {
            $pages = explode(',', $pixel['page']);
            $pages = array_map(function($v){
                return basename($v);
            }, $pages);
            
            $currentPageName = basename(
                Request::server()->get('SCRIPT_NAME')
            );
            if(!in_array($currentPageName, $pages))
            {
                return false;
            }
        }
        
        if (!empty($pixel['exceptions'])) {
            $excludepages = explode(',', $pixel['exceptions']);
            $excludepages = array_map(function($v){
                return basename($v);
            }, $excludepages);
            
            $currentPageName = basename(
                Request::server()->get('SCRIPT_NAME')
            );
            if(in_array($currentPageName, $excludepages))
            {
                return false;
            }
        }

        if ($isValidDevice && $pixel['pixel_type'] != 'General' && $pixel['pixel_type'] != 'Decline') {
            Session::set(
                sprintf(
                    'pixels.fireStatus.%d.%d',
                    $this->previousConfigId, $pixel['id']
                ), true
            );
            return true;
        }
        
        if (
            Session::get(
                sprintf(
                    'generalPixels.fireStatus.%d',
                    $pixel['id']
                )
            ) === true
                && $pixel['pixel_type'] == 'General'
        ) {
            return false;
        }
        
        if($pixel['pixel_type'] == 'General' && $isValidDevice)
        {
            $pixel['multi_fire'] = ($pixel['pixel_firing_option'] == "always") ? true : false;
            if(!empty($pixel['multi_fire'])){
                return true;
            }else{
                Session::set(
                    sprintf(
                        'generalPixels.fireStatus.%d',
                        $pixel['id']
                    ), true
                );
                return true;
            }
            
        }
        
        if($pixel['pixel_type'] == 'Decline' && $isValidDevice)
        {
            Session::set(
                sprintf(
                    'declinePixels.pixel.%d.%s',
                    $pixel['id'], $pixel['pixel_placement']
                ), $pixel['html_pixel']
            );
           
            return false;
        }
        
        return false;
    }

    private function isValidAffiliates($pixel)
    {
        if(!empty($pixel['enable_affiliate_parameters']))
        {
            if (!empty($pixel['affiliate_id_value'])) {
                $affiliates = explode(',', $pixel['affiliate_id_value']);
                $affVal = Session::get(
                        sprintf('affiliates.%s', $pixel['affiliate_id_key'])
                    );
                if (
                    !in_array($affVal, $affiliates)
                ) {
                    return false;
                }
            }
            
            if (!empty($pixel['sub_id_value'])) {
                $subaffiliates = explode(',', $pixel['sub_id_value']);
                $affVal = Session::get(
                        sprintf('affiliates.%s', $pixel['sub_id_key'])
                    );
                if (
                    !in_array($affVal, $subaffiliates)
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    private function isValidDevice($pixel)
    {

        if (!empty($pixel['device'])) {
            $devices = explode(',', $pixel['device']);
            if (!in_array('all', $devices)) {

                $detect = new MobileDetect();
                $ua  = '';

                if ($detect->isTablet()) {
                    $ua = 'tablet';
                    if($detect->version('iPad'))
                    {
                        $ua = 'ipad';
                    }
                }

                if (!$detect->isMobile() && !$detect->isTablet()) {
                    $ua = 'desktop';
                }

                if ($detect->isAndroidOS()) {
                    $ua = 'mobile_android';
                }

                if ($detect->isIphone()) {
                    $ua = 'iphone';
                }
                
                if (!in_array($ua, $devices)) {
                    return false;
                }
            }
        }

        return true;
    }

}
