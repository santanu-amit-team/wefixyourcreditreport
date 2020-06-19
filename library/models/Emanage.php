<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;
use Application\Session;
use Application\Request;

class Emanage extends BaseCrm
{

    private $methodSlugMapping = array(
        'prospect'             => 'customers/',
        'newOrderWithProspect' => 'orders/',
        'newOrder'             => 'orders/',
        'newOrderCardOnFile'   => 'orders/',
        'confirmPaypal'   => 'orders/',
    );

    public function __construct($crmId)
    {
        parent::__construct($crmId);
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();
    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['email'] = $this->accessor->getValue($this->params, '[email]');
        $this->params['phoneNumber'] = $this->accessor->getValue($this->params, '[phone]');
        $this->params['useShippingAddressForBilling'] = true;
        $this->prepareShippingDetails();
        //$this->prepareAffiliates();
        $this->prepareAnalytics();
        
        
        $this->params['slug'] = $this->methodSlugMapping['prospect'];
        
        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'prospectId' => $this->response['id'],
        ));
    }

    protected function newOrderWithProspect()
    {

        $this->params = array_replace($this->params, CrmPayload::all());
        
        $this->prepareShippingDetails();
        $this->prepareBillingDetails();        
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        //$this->prepareAffiliates();
        //$this->prepareAnalytics();
        $this->prepareAnalyticsV2();
        $this->params['customer']['email'] = $this->params['email'];

        unset($this->params['prospectId']);
        $this->params['slug'] = $this->methodSlugMapping['newOrderWithProspect'];

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderNumber'],
            'customerId' => $this->response['customerResult']['customerId'],
            'cardId'     => $this->response['cardId'],
            'funnelBoxOrderId' => !empty($this->response['funnelBox']['orderNumber']) ? $this->response['funnelBox']['orderNumber'] : '',
        ));
    }

    protected function newOrder()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        
        $this->prepareShippingDetails();
        $this->prepareBillingDetails();        
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        //$this->prepareAffiliates();
        //$this->prepareAnalytics();
        $this->prepareAnalyticsV2();
        $this->params['customer']['email'] = $this->params['email'];

        unset($this->params['prospectId']);
        $this->params['slug'] = $this->methodSlugMapping['newOrder'];

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderNumber'],
            'customerId' => $this->response['customerResult']['customerId'],
            'cardId'     => $this->response['cardId'],
            'funnelBoxOrderId' => !empty($this->response['funnelBox']['orderNumber']) ? $this->response['funnelBox']['orderNumber'] : '',
        ));
    }

    protected function newOrderCardOnFile()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        
        $this->prepareShippingDetails();
        $this->prepareBillingDetails();        
        $this->prepareProductDetails();
        $this->prepareUpsell();
        $this->prepareUpsellCardDetails();
        //$this->prepareAffiliates();
        //$this->prepareAnalytics();
        $this->prepareAnalyticsV2();
        $this->params['customer']['email'] = $this->params['email'];

        unset($this->params['prospectId']);
        $this->params['slug'] = $this->methodSlugMapping['newOrder'];

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderNumber'],
            'customerId' => $this->response['customerResult']['customerId'],
            'cardId'     => $this->response['cardId'],
            'funnelBoxOrderId' => !empty($this->response['funnelBox']['orderNumber']) ? $this->response['funnelBox']['orderNumber'] : '',
        ));
    }
    
    public function preAuthorization()
    {
        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => strtoupper(uniqid()),
            'customerId' => strtoupper(uniqid()),
        ));
    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['forceGatewayId'])) {
            $this->params['forceMerchantId'] = $this->params['forceGatewayId'];
        }
        
        if (!empty($this->params['cpf'])) {
            $this->params['customer']['customerIdentificationValue'] = $this->params['cpf'];
            $this->params['customer']['customerIdentificationTypeId'] = 1;
            unset($this->params['cpf']);
        }
        
        if (!empty($this->params['instalments'])) {
            $this->params['payment']['instalments'] = $this->params['instalments'];
            unset($this->params['instalments']);
        }
        
        if(!empty($this->params['ipAddress'])) {
            $this->params['customer']['ip'] = $this->params['ipAddress'];
            unset($this->params['ipAddress']);
        }
        
        unset(
            $this->params['forceGatewayId'], $this->params['preserveGateway'],
            $this->params['parentOrderId'], $this->params['userIsAt'],
            $this->params['userAgent'],$this->params['browser'],
            $this->params['os'],$this->params['screenResolution'],
            $this->params['device'], $this->params['userStringData64']
        );

        $metaKeys = preg_grep('/^meta\..+$/', array_keys($this->params));
        foreach ($metaKeys as $metaKey) {
            unset($this->params[$metaKey]);
        }

        $this->response = array();
    }
    
    public function confirmPaypal()
    {
        $this->params = $this->response = array();
        $testSlug = '';
        $this->params           = array_replace($this->params, CrmPayload::all());
        $this->params['slug']   = $this->methodSlugMapping['confirmPaypal'];
        
        $url = $this->getUrl($this->params['slug']). $this->params['campaignId'];
        
        if(!empty($this->params['isSandbox']))
        {
            $testSlug = '&isTest=true';
        }
        
        unset($this->params['slug']);
        $headers = array('Content-Type' => 'application/json', 'X_CID' => $this->username);
        
        $response = Http::customRequest($url.'?trackingNumber='.$this->params['trackingNumber'].$testSlug, '{}', $headers,'PUT');
  
        if (!empty($response['curlError'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return;
        }

        $responseStatus = $this->prepareResponse($response);
        
        if(!$responseStatus){
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderNumber'],
            'customerId' => $this->response['customerResult']['customerId'],
            'cardId'     => $this->response['cardId'],
            'funnelBoxOrderId' => !empty($this->response['funnelBox']['orderNumber']) ? $this->response['funnelBox']['orderNumber'] : '',
        ));

    }

    private function makeHttpRequest()
    {

        $this->beforeHttpRequest();

        $url = $this->getUrl($this->params['slug']). $this->params['campaignId'];
        if(!empty($this->params['isSandbox']))
        {
            $url = $url.'?behaviorId=2';
        }        

        unset($this->params['slug'], $this->params['campaignId'], $this->params['loginId']);

        $userAgent = Request::headers()->get('HTTP_USER_AGENT');
        $ua = !empty($userAgent) ? $userAgent : 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

        $params = array_filter($this->params);
        $headers = array('Content-Type' => 'application/json', 'X_CID' => $this->username);
        $curlOptions = array(CURLOPT_FAILONERROR => false, CURLOPT_USERAGENT => $ua);
        $response = Http::post($url, json_encode($params, JSON_UNESCAPED_SLASHES), $headers, $curlOptions);

        if (!empty($response['curlError'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return false;
        }

        return $this->prepareResponse($response);
    }

    private function prepareResponse($response)
    {
        $this->response = json_decode($response, true);
        
        if(!empty($this->response) && !empty($this->response['success']) && !empty($this->response['callBackUrl'])){
            CrmResponse::update(array(
                'success'  => true,
                'redirect' => $this->response['callBackUrl'],
                'orderNumber' => $this->response['orderNumber'],
                'customerId' => $this->response['customerResult']['customerId'],
                'cardId'     => $this->response['cardId'],
                'funnelBoxOrderId' => !empty($this->response['funnelBox']['orderNumber']) ? $this->response['funnelBox']['orderNumber'] : '',
            ));
            return false;
        }

        if (!empty($this->response) && (!empty($this->response['success']) || !empty($this->response['id'])))
        {
            if(CrmPayload::has('parentOrderData') && CrmPayload::has('isPayPalFlow'))
            {
                $parentOrderData = CrmPayload::get('parentOrderData');
                $this->response['orderNumber'] = $parentOrderData['orderNumber'];
                $this->response['customerResult']['customerId'] = $parentOrderData['customerId'];
                $this->response['funnelBox']['orderNumber'] = $parentOrderData['funnelBoxOrderId'];
                $this->response['cardId'] = $parentOrderData['cardId'];
            }
            return true;
        }else{
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'crmError' => $this->response['message'],
                ),
            ));
            return false;
        }
    }

    private function getUrl($slug)
    {
        return sprintf('%s/%s', $this->endpoint, $slug);
    }

    private function prepareShippingDetails()
    {
        $shippingDetails = array(
            'firstName'    => $this->accessor->getValue($this->params, '[firstName]'),
            'lastName'     => $this->accessor->getValue($this->params, '[lastName]'),
            'phoneNumber'  => $this->accessor->getValue($this->params, '[phone]'),
            'address1'     => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'zipCode'      => $this->accessor->getValue($this->params, '[shippingZip]'),
            'city'         => $this->accessor->getValue($this->params, '[shippingCity]'),
            'state'        => $this->accessor->getValue($this->params, '[shippingState]'),
            'countryCode'  => $this->accessor->getValue($this->params, '[shippingCountry]'),
        );
        
        if (!empty($this->params['shippingAddress2']))
        {
            $shippingDetails['address2'] = $this->accessor->getValue($this->params, '[shippingAddress2]');
        }

        unset(
            $this->params['phone'],
            $this->params['shippingAddress1'], $this->params['shippingAddress2'],
            $this->params['shippingZip'], $this->params['shippingCity'],
            $this->params['shippingState'], $this->params['shippingCountry']
        );

        $this->params['shippingAddress'] = $shippingDetails;
    }

    private function prepareBillingDetails()
    {
        $this->params['useShippingAddressForBilling'] = false;
        if (strtolower($this->params['billingSameAsShipping']) !== 'yes') {
            $billingDetails = array(
                'firstName'      => $this->accessor->getValue($this->params, '[billingFirstName]'),
                'lastName'       => $this->accessor->getValue($this->params, '[billingLastName]'),
                'phoneNumber'    => $this->accessor->getValue($this->params, '[phone]'),
                'address1'       => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'zipCode'        => $this->accessor->getValue($this->params, '[billingZip]'),
                'city'           => $this->accessor->getValue($this->params, '[billingCity]'),
                'state'          => $this->accessor->getValue($this->params, '[billingState]'),
                'countryCode'    => $this->accessor->getValue($this->params, '[billingCountry]'),
            );
            
            if (!empty($this->params['billingAddress2']))
            {
                $billingDetails['address2'] = $this->accessor->getValue($this->params, '[billingAddress2]');
            }
            
        }else{
            $billingDetails = array(
                'firstName'    => $this->accessor->getValue($this->params['shippingAddress'], '[firstName]'),
                'lastName'     => $this->accessor->getValue($this->params['shippingAddress'], '[lastName]'),
                'phoneNumber'  => $this->accessor->getValue($this->params['shippingAddress'], '[phoneNumber]'),
                'address1'     => $this->accessor->getValue($this->params['shippingAddress'], '[address1]'),
                'zipCode'      => $this->accessor->getValue($this->params['shippingAddress'], '[zipCode]'),
                'city'         => $this->accessor->getValue($this->params['shippingAddress'], '[city]'),
                'state'        => $this->accessor->getValue($this->params['shippingAddress'], '[state]'),
                'countryCode'  => $this->accessor->getValue($this->params['shippingAddress'], '[countryCode]'),
            );
            
            if (!empty($this->params['shippingAddress']['address2']))
            {
                $billingDetails['address2'] = $this->accessor->getValue($this->params['shippingAddress'], '[address2]');
            }
            
            $this->params['useShippingAddressForBilling'] = true;
        }

        unset(
            $this->params['billingFirstName'], $this->params['billingLastName'],
            $this->params['billingAddress1'], $this->params['billingAddress2'],
            $this->params['billingCity'], $this->params['billingState'],
            $this->params['billingZip'], $this->params['billingCountry'],
            $this->params['billingSameAsShipping']
        );

        $this->params['billingAddress'] = $billingDetails;
    }

    private function prepareProductDetails()
    {
        $result = array();
        foreach ($this->params['products'] as $i => $product) {
            if (empty($product['productId'])) {
                continue;
            }
            $result['productId'] = $this->accessor->getValue($product, '[productId]');
            $result['shippingMethodId'] = $this->accessor->getValue($product, '[shippingId]');
        }

        unset($this->params['products']);
        $this->params = array_replace($this->params, array_filter($result));
    }

    private function prepareCardDetails()
    {
        $cardDetails = array(
            'name'             => $this->accessor->getValue($this->params, '[firstName]').' '.$this->accessor->getValue($this->params, '[lastName]'),
            'creditCard'       => $this->accessor->getValue($this->params, '[cardNumber]'),
            'creditCardBrand'  => $this->accessor->getValue($this->params, '[cardType]'),
            'expiration'       => $this->accessor->getValue($this->params, '[cardExpiryMonth]').'/20'.$this->accessor->getValue($this->params, '[cardExpiryYear]'),
            'cvv'              => $this->accessor->getValue($this->params, '[cvv]'),
        );
        
        if(!empty($this->params['cardHolderName']))
        {
            $cardDetails['name'] = $this->params['cardHolderName'];
            unset($this->params['cardHolderName']);
        }
        
        if(!empty($this->params['paymentProcessorId']))
        {
            $cardDetails = array(
                'paymentProcessorId'  => $this->accessor->getValue($this->params, '[paymentProcessorId]'),                
            );
        }
        
        unset(
            $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv'], $this->params['paymentProcessorId']
        );

        $this->params['payment'] = $cardDetails;
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        $affiliateParams = array_filter(array(
            'affId'        => $this->accessor->getValue($affiliates, '[affId]'),
            'sourceValue1' => $this->accessor->getValue($affiliates, '[c1]'),
            'sourceValue2' => $this->accessor->getValue($affiliates, '[c2]'),
            'sourceValue3' => $this->accessor->getValue($affiliates, '[c3]'),
            'sourceValue4' => $this->accessor->getValue($affiliates, '[c4]'),
            'sourceValue5' => $this->accessor->getValue($affiliates, '[c5]'),
        ));

        unset($this->params['affiliates']);

        $this->params = array_replace($this->params, $affiliateParams);
    }
    
    private function prepareUpsell()
    {
        $campaignUpsell = array();
        $campaignUpsell['webKey'] = $this->params['campaignId'];
        $campaignUpsell['relatedOrderNumber'] = $this->params['meta.mainStepData']['orderId'];
        $this->params['campaignUpsell'] = $campaignUpsell;
    }
    
    private function prepareUpsellCardDetails()
    {
        $cardDetails = array(
            'cardId'             => $this->params['meta.mainStepData']['cardId'],
        );
        
        if(!empty($this->params['paymentProcessorId']))
        {
            $cardDetails = array(
                'paymentProcessorId'  => $this->accessor->getValue($this->params, '[paymentProcessorId]'),                
            );
        }
        
        unset(
            $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv']
        );

        $this->params['payment'] = $cardDetails;
    }
    
    public static function isValidCredential($credential)
    {
        return true;
    }
    
    public function orderView()
    {
        $orderIds       = CrmPayload::get('orderIds');
        $result         = $data         = array();
        $sessionData = Session::all();
        
        foreach ($orderIds as $orderKey => $orderId) {
            foreach($sessionData['steps'] as $key => $value) {
                if(is_numeric($key)) {
                    $checkOrderId = Session::get('steps.'.$key.'.orderId');
                    if($orderId == $checkOrderId) {
                        $result[$orderKey] = Session::get('steps.'.$key);
                        break;
                    }
                }
            }
        }
        
        if(empty($result)) {
            $result = 'Not Found!';
        }
         
        CrmResponse::replace(array(
            'success' => true,
            'result'  => $result,
        ));
    }
    
    private function prepareAnalytics()
    {
        if(!CrmPayload::has('analyticsDisabled'))
        {
            $analytics = array();
            $analytics['referringUrl'] = Request::headers()->get('HTTP_REFERER');
            $analytics['landingUrl'] = Request::getOfferUrl();
            $analytics['browser'] = CrmPayload::get('browser');
            $analytics['os'] = CrmPayload::get('os');
            $analytics['screenResolution'] = CrmPayload::get('screenResolution');
            $analytics['device'] = CrmPayload::get('device');
            $this->params['analytics'] = $analytics; 
        }
        
    }
    
    private function prepareAnalyticsV2()
    {
        if(!CrmPayload::has('analyticsDisabled'))
        {
            $analytics = array();
            $analytics['referringUrl'] = Request::headers()->get('HTTP_REFERER');
            $analytics['landingUrl'] = Request::getOfferUrl();
            $analytics['userStringData64'] = CrmPayload::get('userStringData64');
            $this->params['analyticsV2'] = $analytics;
        }
    }

}
