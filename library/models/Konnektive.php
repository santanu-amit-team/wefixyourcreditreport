<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;
use Exception;

class Konnektive extends BaseCrm
{

    private $methodSlugMapping = array(
        'prospect'             => 'leads/import/',
        'newOrderWithProspect' => 'order/import/',
        'newOrder'             => 'order/import/',
        'newOrderCardOnFile'   => 'order/import/',
        'confirmOrder'         => 'order/confirm/',
        'importClick'          => 'landers/clicks/import/',
        'importUpsell'         => 'upsale/import/',
        'preAuthorization'     => 'order/preauth/',
        'addCustomerNote'      => 'customer/addnote/',
        'orderView'            => 'order/query/',
        'campaignQuery'        => 'campaign/query/',
        'transactionQuery'     => 'transactions/query/',
        'confirmPaypal'        => 'transactions/confirmPaypal/',
        'offlinePayment'       => 'order/import/',
        'orderQa'              => 'order/qa/'
    );

    public function __construct($crmId)
    {
        parent::__construct($crmId);
        $this->endpoint = rtrim(
            Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL'), '/'
        );
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();

        $this->params['loginId']  = $this->username;
        $this->params['password'] = $this->password;

        $forceGatewayId = CrmPayload::get('forceGatewayId');

        if (
            !empty($forceGatewayId['evaluate']) && !empty($forceGatewayId['orderId'])
        ) {
            $currentCrmPayload = CrmPayload::all();
            $gatewayId         = $this->getGatewayIdFromOrderId($forceGatewayId['orderId']);
            CrmPayload::replace($currentCrmPayload);
            if ($gatewayId === false) {
                CrmPayload::remove('forceGatewayId');
            } else {
                CrmPayload::set('forceGatewayId', $gatewayId);
            }
        }
    }

    private function getGatewayIdFromOrderId($orderId = null)
    {
        if (empty($orderId)) {
            return false;
        }

        CrmPayload::replace(array('orderId' => $orderId));

        $this->transactionQuery();

        $transactionInfo = CrmResponse::get('transactionInfo');
        if (empty($transactionInfo['data']) || !is_array($transactionInfo['data'])) {
            return false;
        }

        $lastIndex = $lastTransactionId = 0;
        foreach ($transactionInfo['data'] as $index => $transaction) {
            if (
                !empty($transaction['transactionId']) &&
                $transaction['transactionId'] > $lastTransactionId
            ) {
                $lastTransactionId = $transaction['transactionId'];
                $lastIndex         = $index;
            }
        }

        if (!empty($transactionInfo['data'][$lastIndex])) {
            return $transactionInfo['data'][$lastIndex]['merchantId'];
        }

        return false;

    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareAffiliates();

        $this->params['slug'] = $this->methodSlugMapping['prospect'];
        
        unset($this->params['userAgent']);

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'prospectId' => $this->response['message']['orderId'],
        ));
    }

    protected function newOrderWithProspect()
    {

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        $this->params['orderId']   = $this->params['prospectId'];        
        $this->params['slug'] = $this->methodSlugMapping['newOrderWithProspect'];
        
        unset($this->params['prospectId'], $this->params['userAgent']);

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['message']['orderId'],
            'customerId' => $this->response['message']['customerId'],
        ));
    }

    protected function newOrder()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        unset($this->params['orderId'], $this->params['parentOrderId']);
        $this->params['slug']      = $this->methodSlugMapping['newOrder'];
        
        unset($this->params['userAgent']);

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['message']['orderId'],
            'customerId' => $this->response['message']['customerId'],
        ));
    }

    protected function newOrderCardOnFile()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareProductDetails();
        $this->prepareAffiliates();

        $this->params['paySource'] = 'ACCTONFILE';
        $this->params['slug']      = $this->methodSlugMapping['newOrderCardOnFile'];
        unset(
            $this->params['cardNumber'], $this->params['cardType'],
            $this->params['cardExpiryYear'], $this->params['cardExpiryMonth'],
            $this->params['cvv'], $this->params['userAgent']
        );

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['message']['orderId'],
            'customerId' => $this->response['message']['customerId'],
        ));
    }

    protected function importUpsell()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareAffiliates();
        $this->prepareUpsellProduct();
        
        $this->params['slug'] = $this->methodSlugMapping['importUpsell'];   
        
        unset($this->params['userAgent']);

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['message']['orderId'],
            'customerId' => $this->response['message']['customerId'],
        ));
    }

    public function preAuthorization()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        $this->params['slug']      = $this->methodSlugMapping['preAuthorization'];
        unset($this->params['customerId'], $this->params['userAgent']);

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => strtoupper(uniqid()),
            'customerId' => strtoupper(uniqid()),
            'gatewayId'  => CrmPayload::get('forceGatewayId'),
        ));
    }

    public function importClick()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->params['slug'] = $this->methodSlugMapping['importClick'];

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'   => true,
            'sessionId' => $this->accessor->getValue($this->response, '[message][sessionId]'),
        ));
    }

    public function confirmOrder()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->params['slug'] = $this->methodSlugMapping['confirmOrder'];

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array('success' => true));
    }

    public function addCustomerNote()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->params['slug'] = $this->methodSlugMapping['addCustomerNote'];

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array('success' => true));
    }

    public function orderView()
    {

        $this->beforeAnyCrmClassMethodCall();

        $orderIds       = CrmPayload::get('orderIds');
        $uniqueOrderIds = array_unique($orderIds);
        $result         = $data         = array();
        foreach ($uniqueOrderIds as $orderId) {
            $this->params['slug']    = $this->methodSlugMapping['orderView'];
            $this->params['orderId'] = $orderId;
            if ($this->makeHttpRequest() === false) {
                $data[$orderId] = 'Not Found';
                continue;
            }
            if (!empty($this->response['message'])) {
                $data[$orderId] = $this->response['message'];
            } else {
                $data[$orderId] = 'Unexpected error';
            }
        }

        foreach ($orderIds as $key => $orderId) {
            $result[$key] = $data[$orderId];
        }

        CrmResponse::replace(array(
            'success' => true,
            'result'  => $result,
        ));
    }

    public function campaignQuery()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params['campaignId'] = CrmPayload::get('campaignId');
        $this->params['slug']       = $this->methodSlugMapping['campaignQuery'];

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success' => true, 'campaignInfo' => $this->response['message'],
        ));

    }

    public function validateCoupon()
    {

        $this->beforeAnyCrmClassMethodCall();

        $campaignId = CrmPayload::get('campaignId');
        $couponCode = CrmPayload::get('couponCode');

        $this->campaignQuery();

        if (CrmResponse::get('success') !== true) {
            return;
        }

        $campaignInfo = CrmResponse::get('campaignInfo');

        if (
            empty($campaignInfo['data'][$campaignId]['coupons']) ||
            !is_array($campaignInfo['data'][$campaignId]['coupons'])
        ) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'crmError' => 'No coupons available!',
                ),
            ));
            return;
        }

        $couponInfo = array();

        foreach ($campaignInfo['data'][$campaignId]['coupons'] as $coupon) {
            if ($coupon['couponCode'] === $couponCode) {
                $couponInfo = $coupon;
            }
        }

        if (empty($couponInfo)) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'couponCode' => 'Invalid coupon!',
                ),
            ));
            return;
        }

        CrmResponse::replace(array(
            'success' => true, 'couponInfo' => $couponInfo,
        ));

    }

    public function transactionQuery()
    {
        $this->params['orderId'] = CrmPayload::get('orderId');
        $this->params['slug']    = $this->methodSlugMapping['transactionQuery'];
        $this->params['loginId']  = $this->username;
        $this->params['password'] = $this->password;

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success' => true, 'transactionInfo' => $this->response['message'],
        ));

    }
    
    public function confirmPaypal()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params           = array_replace($this->params, CrmPayload::all());
        $this->params['slug']   = $this->methodSlugMapping['confirmPaypal'];
        if(!empty($this->params['prospectId'])){
            $this->params['orderId']   = $this->params['prospectId'];
        }
        $this->prepareProductDetails();
        $this->beforeHttpRequest();

        $url = $this->getUrl($this->params['slug']);
        unset($this->params['slug']);
        $params = array_filter($this->params);
        $queryParams = http_build_query($params);
        $paypalUrl = $url.'?'.$queryParams;       
        $response = Http::get($paypalUrl);

        if (!empty($response['curlError'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return;
        }

        $this->prepareResponse($response);
        
        if($this->response['result'] == 'ERROR'){
            return;
        }
        
        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['message']['orderId'],
            'customerId' => $this->response['message']['customerId'],
        ));

    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['forceGatewayId'])) {
            $this->params['forceMerchantId'] = trim($this->params['forceGatewayId']);
        }
        
        $unusedExtraParams = array('limelight_charset', 'forceGatewayId', 'preserveGateway', 'parentOrderId', 'userIsAt', 'billingSameAsShipping', 'prepaidConfig', 'scrapDetail', 'scrapRuleId', 'enableLocalCapture', 'enableDeclineCapture');
        
        foreach ($unusedExtraParams as $value)
        {
            if(isset($this->params[$value]))
            {
                unset($this->params[$value]);
            }
        }
        
        $metaKeys = preg_grep('/^meta\..+$/', array_keys($this->params));
        foreach ($metaKeys as $metaKey) {
            unset($this->params[$metaKey]);
        }

        $this->response = array();
    }

    private function makeHttpRequest()
    {

        $this->beforeHttpRequest();

        $url = $this->getUrl($this->params['slug']);

        unset($this->params['slug']);
        $params = array_filter($this->params);

        if(!empty($this->params['isOrderVaultAPICall']))
        {
            unset($params['isOrderVaultAPICall']);
            $response = Http::get($url.'?'.http_build_query($params));
        }
        else{
            $response = Http::post($url, http_build_query($params));
        }
        
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
        
        if(!empty($this->response['result']) && !empty($this->response['message']['paypalUrl'])){
            CrmResponse::update(array(
                'success'  => true,
                'redirect' => $this->response['message']['paypalUrl'],
            ));
            return false;
        }

        if (!empty($this->response['result']) && $this->response['result'] === 'MERC_REDIRECT') {
            CrmResponse::replace(array(
                'success'               => false,
                'isMerchantRedirection' => true,
                'script'                => $this->response['message']['script'],
            ));
            return false;
        }

        if (empty($this->response['result']) || $this->response['result'] !== 'SUCCESS') {
            if (is_array($this->response['message'])) {
                $this->response['message'] = sprintf(
                    '%s: %s', key($this->response['message']),
                    $this->response['message'][key($this->response['message'])]
                );
            }
            $isPrepaidDecline = false;
            if (
                !empty($this->response['message']) &&
                preg_match("/Prepaid.+Not Accepted/i", $this->response['message'])
            ) {
                $isPrepaidDecline = true;
            }
            CrmResponse::replace(array(
                'success'          => false,
                'isPrepaidDecline' => $isPrepaidDecline,
                'errors'           => array(
                    'crmError' => $this->response['message'],
                ),
            ));
            return false;
        }

        return true;
    }

    private function getUrl($slug)
    {
        if(!empty($this->params['isOrderVaultAPICall']))
        {
            if($slug == 'order/import/' || $slug == 'upsale/import/')
            {
                $this->endpoint = 'https://api.ordervault.com';
            }
        }
        return sprintf('%s/%s', $this->endpoint, $slug);
    }

    private function prepareShippingDetails()
    {
        $shippingDetails = array(
            'emailAddress' => $this->accessor->getValue($this->params, '[email]'),
            'phoneNumber'  => $this->accessor->getValue($this->params, '[phone]'),
            'address1'     => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'address2'     => $this->accessor->getValue($this->params, '[shippingAddress2]'),
            'postalCode'   => $this->accessor->getValue($this->params, '[shippingZip]'),
            'city'         => $this->accessor->getValue($this->params, '[shippingCity]'),
            'state'        => $this->accessor->getValue($this->params, '[shippingState]'),
            'country'      => $this->accessor->getValue($this->params, '[shippingCountry]'),
        );

        unset(
            $this->params['email'], $this->params['phone'],
            $this->params['shippingAddress1'], $this->params['shippingAddress2'],
            $this->params['shippingZip'], $this->params['shippingCity'],
            $this->params['shippingState'], $this->params['shippingCountry']
        );

        $this->params = array_replace($this->params, array_filter($shippingDetails));
    }

    private function prepareBillingDetails()
    {
        $billingDetails = array('billShipSame' => '1');

        if (strtolower($this->params['billingSameAsShipping']) !== 'yes') {
            $billingDetails = array(
                'billShipSame'   => '0',
                'firstName'      => $this->accessor->getValue($this->params, '[billingFirstName]'),
                'lastName'       => $this->accessor->getValue($this->params, '[billingLastName]'),
                'address1'       => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'address2'       => $this->accessor->getValue($this->params, '[billingAddress2]'),
                'postalCode'     => $this->accessor->getValue($this->params, '[billingZip]'),
                'city'           => $this->accessor->getValue($this->params, '[billingCity]'),
                'state'          => $this->accessor->getValue($this->params, '[billingState]'),
                'country'        => $this->accessor->getValue($this->params, '[billingCountry]'),
                'shipFirstName'  => $this->accessor->getValue($this->params, '[firstName]'),
                'shipLastName'   => $this->accessor->getValue($this->params, '[lastName]'),
                'shipAddress1'   => $this->accessor->getValue($this->params, '[address1]'),
                'shipAddress2'   => $this->accessor->getValue($this->params, '[address2]'),
                'shipPostalCode' => $this->accessor->getValue($this->params, '[postalCode]'),
                'shipCity'       => $this->accessor->getValue($this->params, '[city]'),
                'shipState'      => $this->accessor->getValue($this->params, '[state]'),
                'shipCountry'    => $this->accessor->getValue($this->params, '[country]'),
            );
        }

        unset(
            $this->params['billingFirstName'], $this->params['billingLastName'],
            $this->params['billingAddress1'], $this->params['billingAddress2'],
            $this->params['billingCity'], $this->params['billingState'],
            $this->params['billingZip'], $this->params['billingCountry']
        );

        $this->params = array_replace($this->params, array_filter($billingDetails));
    }

    private function prepareProductDetails()
    {
        $result = array();
        foreach ($this->params['products'] as $i => $product) {
            if (empty($product['productId'])) {
                continue;
            }
            $result[
                sprintf('product%s_id', $i + 1)
            ] = $this->accessor->getValue($product, '[productId]');
            $result[
                sprintf('product%s_qty', $i + 1)
            ] = $this->accessor->getValue($product, '[productQuantity]');
            $result[
                sprintf('product%s_price', $i + 1)
            ] = $this->accessor->getValue($product, '[productPrice]');
            $result[
                sprintf('product%s_shipPrice', $i + 1)
            ] = $this->accessor->getValue($product, '[shippingPrice]');
            
            if(!empty($this->params['isOrderVaultAPICall']))
            {
                $result[
                    sprintf('rebill%s_id', $i + 1)
                ] = $this->accessor->getValue($product, '[rebillId]');
            }
        }
        
        unset($this->params['products']);

        $this->params = array_replace($this->params, array_filter($result));
    }

    private function prepareCardDetails()
    {
        $cardDetails = array(
            'cardType'         => $this->accessor->getValue($this->params, '[cardType]'),
            'cardMonth'        => $this->accessor->getValue($this->params, '[cardExpiryMonth]'),
            'cardYear'         => !empty($this->params['cardExpiryYear']) ? '20'.$this->accessor->getValue($this->params, '[cardExpiryYear]') : '',
            'cardSecurityCode' => $this->accessor->getValue($this->params, '[cvv]'),
            'paySource'        => $this->accessor->getValue($this->params, '[paySource]'),
        );
        
        if(empty($cardDetails['paySource']))
        {
            $cardDetails['paySource'] = 'CREDITCARD';
        }

        $cardDetails['cardType'] = strtoupper($cardDetails['cardType']);
        if ($cardDetails['cardType'] === 'MASTER') {
            $cardDetails['cardType'] .= 'CARD';
        }
        
        if(!empty($this->params['cardType']) && $this->params['cardType'] == 'COD')
        {
            $cardDetails['paySource'] = 'COD';
            unset($cardDetails['cardType']);
            unset($this->params['cardType']);
        }
        
        if(!empty($this->params['cardType']) && $this->params['cardType'] == 'DIRECTDEBIT')
        {
            unset($cardDetails['cardType']);
            unset($this->params['cardType']);
        }
        
        if(!empty($this->params['cardType']) && $this->params['cardType'] == 'PREPAID')
        {
            $cardDetails['paySource'] = 'PREPAID';
            unset($cardDetails['cardType'], $cardDetails['cardMonth'], $cardDetails['cardYear'], $cardDetails['cardSecurityCode']);
            unset($this->params['cardType']);
        }

        unset(
            $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv']
        );

        $this->params = array_replace($this->params, array_filter($cardDetails));
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

    public static function isValidCredential($credential)
    {
        if (is_array($credential) && !empty($credential)) {
            $params = array(
                'loginId'        => $credential['username'],
                'password'       => $credential['password'],
                'resultsPerPage' => 1,
            );
            $endpoint = rtrim(
                Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL'), '/'
            );
            $url      = sprintf('%s/campaign/query/', $endpoint);
            $response = Http::post($url, $params);
            $response = json_decode($response);
            if (
                $response->result == 'ERROR' &&
                preg_match(
                    "/Could not authenticate credentials/i", $response->message
                )
            ) {
                return false;
            } else {
                return true;
            }
        }
    }
    
    public function updatePaypalData()
    {
        $this->params['address1'] = $this->accessor->getValue($this->params['currentOrderData'], '[line1]');
        $this->params['city'] = $this->accessor->getValue($this->params['currentOrderData'], '[city]');
        $this->params['postalCode'] = $this->accessor->getValue($this->params['currentOrderData'], '[postal_code]');
        $this->params['state'] = $this->accessor->getValue($this->params['currentOrderData'], '[state]');
        $this->params['country'] = $this->accessor->getValue($this->params['currentOrderData'], '[country_code]');
        $this->params['creditCardType'] = 'offline';        
    }
    
    public function offlinePayment()
    {
        $this->beforeAnyCrmClassMethodCall();
        
        $this->params = array_replace($this->params, CrmPayload::all());

        $prevCrmMethod = !empty($this->params['prevCrmMethod'])? $this->params['prevCrmMethod'] : 'offlinePayment';

        if($prevCrmMethod == 'newOrder')
        {
            $this->params['is_upsell'] = false;
        }
        
        if(empty($this->params['is_upsell']))
        {
            $this->prepareShippingDetails();
            $this->prepareBillingDetails();
            $this->prepareProductDetails();
            $this->prepareCardDetails();
            $this->prepareAffiliates();
            $this->updatePaypalData();
            $this->params['paySource'] = 'PREPAID';            
            $this->params['slug']      = $this->methodSlugMapping[$prevCrmMethod];   
            $this->params['skipQA'] = true;
            if(!empty($this->params['prospectId']))
            {
                $this->params['orderId']   = $this->params['prospectId'];
                unset($this->params['prospectId']);
            }
            
        }
        else
        {
            $this->prepareAffiliates();
            $this->prepareUpsellProduct();
            $prevCrmMethod = !empty($this->params['prevCrmMethod'])? $this->params['prevCrmMethod'] : 'importUpsell';
            $this->params['slug']      = $this->methodSlugMapping[$prevCrmMethod]; 
            $this->params['skipQA'] = true;
        }

        unset($this->params['cardType'],  $this->params['cardNumber'], $this->params['prevCrmMethod']);

        if ($this->makeHttpRequest() === false) {
            return false;
        }
        
        if(!empty($this->params['is_upsell']))
        {
            $this->orderQa($this->params['orderId']);
        }
        
        $prevPayload = CrmPayload::all();
        $prevResponse = $this->response;
        $this->addOfflineNote();

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $prevResponse['message']['orderId'],
            'customerId' => $prevResponse['message']['customerId'],
        ));        
    }
    
    public function orderQa($orderId)
    {
        try
        {            
            $params['slug'] = $this->methodSlugMapping['orderQa'];
            $url = $this->getUrl($params['slug']);
            $params['loginId']  = $this->username;
            $params['password'] = $this->password;
            $params['orderId']  = $orderId;
            $params['action']   = 'APPROVE';
            
            return Http::post($url, $params);

        } catch (Exception $ex) {
            
        }
    }
    
    public function addOfflineNote()
    {
        try
        {            
            CrmPayload::update(array(
                'customerId' => $this->response['message']['customerId'],
                'message' => 'Order proceesed via Offline method. Reference ID: '.$this->params['currentOrderData']['id']
            ));
            $this->addCustomerNote();
            
        } catch (Exception $ex) {
            
        }
    }
    
    public function prepareUpsellProduct()
    { 
        if(!empty($this->params['replaceProductId']))
        {
            $this->params['productId'] = $this->accessor->getValue(
               $this->params, '[products][0][productId]'
           );
           $this->params['productQty'] = $this->accessor->getValue(
               $this->params, '[products][0][productQuantity]'
           );
           $this->params['productPrice'] = $this->accessor->getValue(
               $this->params, '[products][0][productPrice]'
           );
           $this->params['productShipPrice'] = $this->accessor->getValue(
               $this->params, '[products][0][shippingPrice]'
           );

           $this->params['orderId'] = $this->accessor->getValue(
               $this->params, '[previousOrderId]'
           );
        }
        else{

            $result = array();
            foreach ($this->params['products'] as $i => $product) {
                if (empty($product['productId'])) {
                    continue;
                }
                $result[
                    sprintf('product%s_id', $i + 1)
                ] = $this->accessor->getValue($product, '[productId]');
                $result[
                    sprintf('product%s_qty', $i + 1)
                ] = $this->accessor->getValue($product, '[productQuantity]');
                $result[
                    sprintf('product%s_price', $i + 1)
                ] = $this->accessor->getValue($product, '[productPrice]');
                $result[
                    sprintf('product%s_shipPrice', $i + 1)
                ] = $this->accessor->getValue($product, '[shippingPrice]');
                
                if(!empty($this->params['isOrderVaultAPICall']))
                {
                    $result[
                        sprintf('rebill%s_id', $i + 1)
                    ] = $this->accessor->getValue($product, '[rebillId]');
                }
            }

            $this->params['orderId'] = $this->accessor->getValue(
               $this->params, '[previousOrderId]'
            );
            
            $this->params = array_replace($this->params, array_filter($result));

        }

        unset($this->params['products'], $this->params['previousOrderId']);
    }
    
    public static function getCrmCampaignData($param) 
    {
       try{
            $url = "https://api.konnektive.com/campaign/query/?";
            $params['loginId'] = $param['username'];
            $params['password'] = $param['password'];
            $params['campaignId'] = empty($param['campaign_id']) ? 
                    \Application\Request::form()->get('campaign_id') : $param['campaign_id'];


            $response = Http::post($url, http_build_query($params));
            $response = json_decode($response,true);
            if (
                    !empty($response['curlError']) || 
                    empty($response['result']) ||
                    $response['result'] !== 'SUCCESS'
                ) {
                    return array(
                        'success' => false,
                        'error_message' => 'Unable to import.'
                    );
                }
            return self::getCrmProductShippingDetails($response);
         
        }catch(Exception $ex){
           return  array(
                    'success' => false,
                    'error_message' => 'Unable to import.'
                );
       }
        
    }
    /**
     * Konnektive does not return shipping id so we are placing
     * static shipping id
     * @param type $param
     * @return type
     */
    private static function getCrmProductShippingDetails($param) 
    {
        $products = array();$shippingDetails = array();
        $productShippingDetails = array();
        // echo "<pre>"; print_r($param['message']['data'][1]['products']);die;
        foreach ($param['message']['data'][1]['products'] as $key => $val){
            $products[$key]['product_id'] = $val['campaignProductId'];
            $products[$key]['product_name'] = $val['productName'];
            $products[$key]['product_price'] = $val['price'];
            $products[$key]['retail_price'] = 0.00;
            $products[$key]['product_quantity'] = empty($val['productQty']) ? 1 : $val['productQty'];
            $products[$key]['rebill_product_price'] = 0.00;
            if(!empty($val['shippingPrice'])){
                $shippingDetails[$key]['shipping_id'] = 1;
                $shippingDetails[$key]['shipping_price'] = $val['shippingPrice'];
            }
        }
          return array(
                'success' => true,
                'data' => array('productDetails' => $products,
                        'shippingDetails' => $shippingDetails
                    )
            );
    }

}
