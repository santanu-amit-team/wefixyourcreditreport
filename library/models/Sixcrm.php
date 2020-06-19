<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;

class Sixcrm extends BaseCrm
{

    private $methodSlugMapping = array(
        'prospect' => 'lead/create/',
        'newOrderWithProspect' => 'order/create/',
        'newOrder' => 'order/create/',
        'newOrderCardOnFile' => 'order/create/',
        'orderConfirm' => 'order/confirm/',
    );
    protected $token;
    protected $isdownsell = false;
    protected $currentSlug = '';

    public function __construct($crmId)
    {
        parent::__construct($crmId);
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();
        $this->params['loginId'] = $this->username;
        $this->params['password'] = $this->password;
        $this->params['account'] = $this->account;
    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $customerDetails = array(
            'firstname' => $this->accessor->getValue($this->params, '[firstName]'),
            'lastname' => $this->accessor->getValue($this->params, '[lastName]'),
            'email' => $this->accessor->getValue($this->params, '[email]'),
            'phone' => $this->accessor->getValue($this->params, '[phone]'),
            'billing' => $this->prepareBillingDetails(),
            'address' => $this->prepareShippingDetails(),
        );

        $this->params['customer'] = $customerDetails;
        $this->params['campaign'] = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['slug'] = $this->methodSlugMapping['prospect'];

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        $response = array(
            'success' => true,
            'prospectId' => $this->response['response']['alias'],
            'sessionId' => $this->response['response']['id'],
        );

        if ($this->isdownsell)
        {
            return $response;
        }

        CrmResponse::replace($response);
    }

    protected function newOrderWithProspect()
    {

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareProductDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        unset($this->params['prospectId']);
        $this->params['slug'] = $this->methodSlugMapping['newOrderWithProspect'];
        $this->params['transaction_subtype'] = 'main';

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,            
            'orderId' => CrmPayload::get('prospectId'),
            'orderId_encoded' => $this->response['response']['order']['id'],
        ));
    }

    protected function newOrder()
    {
        $this->isdownsell = true;
        $leadData = $this->prospect();
        if (!$leadData)
        {
            return false;
        }
        
        $this->beforeAnyCrmClassMethodCall();
        CrmPayload::set('session', $leadData['sessionId']);
        $this->params = array_replace($this->params, CrmPayload::all());
        
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        unset($this->params['prospectId']);
        $this->params['slug'] = $this->methodSlugMapping['newOrder'];
        $this->params['transaction_subtype'] = 'main';

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }
        
        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $leadData['prospectId'],
            'orderId_encoded' => $this->response['response']['order']['id'],
            'sessionId' => $leadData['sessionId']
        ));
    }

    protected function newOrderCardOnFile()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareProductDetails();
        $this->prepareAffiliates();
        $this->prepareUpsellCardDetails();

        $this->params['slug'] = $this->methodSlugMapping['newOrderCardOnFile'];
        $this->params['transaction_subtype'] = 'upsell'. CrmPayload::get('upsellCount');
        
        unset($this->params['upsellCount']);
        
        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => CrmPayload::get('previousOrderId'),
            'orderId_encoded' => $this->response['response']['order']['id'],
        ));
    }

    public function preAuthorization()
    {
        CrmResponse::replace(array(
            'success' => true,
            'orderId' => strtoupper(uniqid()),
            'customerId' => strtoupper(uniqid()),
        ));
    }

    public function acquireToken()
    {
        $payload = CrmPayload::all();
        $signature = $this->generateAcquireTokenSignature();
        $account = $this->account;
        $this->params['campaignId'] = $payload['campaignId'];
        $this->params['affiliates'] = $payload['affiliates'];
        $acquire_token = $this->getAcquireBody();
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => $signature,
            'Content-Length' => strlen(json_encode($acquire_token))
        );

        $response = Http::post($this->endpoint . '/token/acquire/' . $account, json_encode($acquire_token), $headers, array(CURLOPT_FAILONERROR => false));
        
        if (!empty($response['curlError']))
        {
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return false;
        }
        return json_decode($response);
    }

    public function generateAcquireTokenSignature()
    {
        $access_key = $this->username;
        $request_time = time() * 1000;
        $signature = $this->generateSignature($request_time);
        return implode(':', array($access_key, $request_time, $signature));
    }

    public function generateSignature($request_time)
    {
        $secret = $this->password;
        $prehash = $secret . $request_time;
        return sha1($prehash);
    }

    public function getAcquireBody()
    {
        $getAcquireBody = array(
            "campaign" => $this->params['campaignId'],
            "affiliates" => $this->prepareAffiliates()
        );
        return $getAcquireBody;
    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['forceGatewayId']))
        {
            $this->params['forceMerchantId'] = $this->params['forceGatewayId'];
        }

        if (!empty($this->params['token']))
        {
            $this->token = $this->params['token'];
        }

        unset(
                $this->params['forceGatewayId'], $this->params['preserveGateway'], $this->params['parentOrderId'], $this->params['userIsAt'], $this->params['userAgent'], $this->params['token']
        );

        $metaKeys = preg_grep('/^meta\..+$/', array_keys($this->params));
        foreach ($metaKeys as $metaKey)
        {
            unset($this->params[$metaKey]);
        }

        $this->response = array();
    }

    private function makeHttpRequest()
    {

        $this->beforeHttpRequest();

        $url = $this->getUrl($this->params['slug']) . $this->params['account'];

        $this->currentSlug = $this->params['slug'];

        $this->cleanUpPayload();

        $params = array_filter($this->params);
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => $this->token,
            'Content-Length' => strlen(json_encode($params))
        );

        $response = Http::post($url, json_encode($params), $headers, array(CURLOPT_FAILONERROR => false));
        
        if (!empty($response['curlError']))
        {
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return false;
        }

        return $this->prepareResponse($response);
    }

    private function cleanUpPayload()
    {
        unset($this->params['billingSameAsShipping'], $this->params['loginId'], $this->params['password'], $this->params['account'], $this->params['firstName'], $this->params['lastName'], $this->params['email'], $this->params['ipAddress'], $this->params['campaignId'], $this->params['slug']);
    }

    private function prepareResponse($response)
    {
        $this->response = json_decode($response, true);
        if (
                !empty($this->response) && 
                !empty($this->response['success']) && 
                ($this->response['code'] == 200 && 
                ($this->currentSlug == 'lead/create/' || $this->currentSlug == 'order/confirm/')))
            
        {
            return true;
        }
        elseif(($this->response['code'] == 200 && !empty($this->response['response']['result']) && $this->response['response']['result'] == 'success'))
        {
            return true;
        }
        else
        {
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'crmError' => !empty($this->response['message'])?$this->response['message']: 'The order has been declined.',
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
            'line1' => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'zip' => $this->accessor->getValue($this->params, '[shippingZip]'),
            'city' => $this->accessor->getValue($this->params, '[shippingCity]'),
            'state' => $this->accessor->getValue($this->params, '[shippingState]'),
            'country' => $this->accessor->getValue($this->params, '[shippingCountry]'),
        );

        if (!empty($this->params['shippingAddress2']))
        {
            $shippingDetails['line2'] = $this->params['shippingAddress2'];
        }

        unset(
                $this->params['phone'], $this->params['shippingAddress1'], $this->params['shippingAddress2'], $this->params['shippingZip'], $this->params['shippingCity'], $this->params['shippingState'], $this->params['shippingCountry']
        );

        return $shippingDetails;
    }

    private function prepareBillingDetails()
    {

        if (!empty($this->params['billingSameAsShipping']) && strtolower($this->params['billingSameAsShipping']) !== 'yes')
        {
            $billingDetails = array(
                'line1' => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'zip' => $this->accessor->getValue($this->params, '[billingZip]'),
                'city' => $this->accessor->getValue($this->params, '[billingCity]'),
                'state' => $this->accessor->getValue($this->params, '[billingState]'),
                'country' => $this->accessor->getValue($this->params, '[billingCountry]'),
            );
        }
        else
        {
            $billingDetails = array(
                'line1' => $this->accessor->getValue($this->params, '[shippingAddress1]') . ', ' . $this->accessor->getValue($this->params, '[shippingAddress2]'),
                'zip' => $this->accessor->getValue($this->params, '[shippingZip]'),
                'city' => $this->accessor->getValue($this->params, '[shippingCity]'),
                'state' => $this->accessor->getValue($this->params, '[shippingState]'),
                'country' => $this->accessor->getValue($this->params, '[shippingCountry]'),
            );
        }

        unset(
                $this->params['billingFirstName'], $this->params['billingLastName'], $this->params['billingAddress1'], $this->params['billingAddress2'], $this->params['billingCity'], $this->params['billingState'], $this->params['billingZip'], $this->params['billingCountry'], $this->params['billingSameAsShipping']
        );

        return $billingDetails;
    }

    private function prepareProductDetails()
    {
        $proArr = $proScheduleArr  = array();
        
        foreach ($this->params['products'] as $i => $product)
        {
            if (!empty($product['productId']))
            {               
                $obj{$i + 1} = new \stdClass();
                $obj{$i + 1}->product = $this->accessor->getValue($product, '[productId]');
                $obj{$i + 1}->quantity = $this->accessor->getValue($product, '[productQuantity]');
                $obj{$i + 1}->price = $this->accessor->getValue($product, '[productPrice]');
                array_push($proArr, $obj{$i + 1});
            }
            if (!empty($product['productSchedule']))
            {               
                $sObj{$i + 1} = new \stdClass();
                $sObj{$i + 1}->product_schedule = $this->accessor->getValue($product, '[productSchedule]');
                $sObj{$i + 1}->quantity = $this->accessor->getValue($product, '[productScheduleQuantity]');
                array_push($proScheduleArr, $sObj{$i + 1});
            }
        }
        unset($this->params['products']);
        if(!empty($proArr))
        {
            $this->params['products'] = $proArr;
        }
        if(!empty($proScheduleArr))
        {
            $this->params['product_schedules'] = $proScheduleArr;
        }
    }

    private function prepareCardDetails()
    {
        $cardDetails = array(
            'name' => $this->accessor->getValue($this->params, '[firstName]') . ' ' . $this->accessor->getValue($this->params, '[lastName]'),
            'number' => $this->accessor->getValue($this->params, '[cardNumber]'),
            'expiration' => $this->accessor->getValue($this->params, '[cardExpiryMonth]') . $this->accessor->getValue($this->params, '[cardExpiryYear]'),
            'cvv' => $this->accessor->getValue($this->params, '[cvv]'),
            'address' => $this->prepareShippingDetails()
        );

        unset(
                $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv']
        );

        $this->params['creditcard'] = $cardDetails;
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        if (empty($affiliates))
        {
            $this->params['affiliates'] = new \stdClass();
        }
        else
        {
            $this->params['affiliates'] = array_filter(array(
                'affiliate' => $this->accessor->getValue($affiliates, '[affId]'),
                'subaffiliate_1' => $this->accessor->getValue($affiliates, '[c1]'),
                'subaffiliate_2' => $this->accessor->getValue($affiliates, '[c2]'),
                'subaffiliate_3' => $this->accessor->getValue($affiliates, '[c3]'),
                'subaffiliate_4' => $this->accessor->getValue($affiliates, '[c4]'),
                'subaffiliate_5' => $this->accessor->getValue($affiliates, '[c5]'),
                'cid' => $this->accessor->getValue($affiliates, '[c6]'),
            ));
        }

        return $this->params['affiliates'];
    }

    private function prepareUpsellCardDetails()
    {
        unset(
                $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv']
        );
    }

    public function orderView()
    {
        if (!CrmPayload::has('token') || !CrmPayload::has('sessionId'))
        {
            return;
        }
        
        $this->currentSlug = $this->methodSlugMapping['orderConfirm'];

        $url = $this->getUrl($this->methodSlugMapping['orderConfirm']) . $this->account . '?session=' . CrmPayload::get('sessionId');

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => CrmPayload::get('token')
        );

        $response = Http::get($url, $headers, array(CURLOPT_FAILONERROR => false));

        if (!empty($response['curlError']))
        {
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return false;
        }

        if (!$this->prepareResponse($response))
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'result' => $this->response['response']
        ));
    }

    public static function isValidCredential($credential)
    {
        return true;
    }

}
