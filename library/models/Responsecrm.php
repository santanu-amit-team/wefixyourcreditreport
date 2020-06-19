<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;
use Application\Request;
use Exception;

class Responsecrm extends BaseCrm
{

    private $methodSlugMapping = array(
        'prospect' => 'customers',
        'newOrderWithProspect' => 'transactions',
        'newOrderCardOnFile' => 'transactions/upsell',
        'upsell' => 'transactions/upsell',
        'newOrder' => 'transactions',
        'binCheck' => 'check-bin',
        'phoneVerification' => 'sepa/phone-verification'
    );
    
    private $additionalPaymentParams = array(
        'sepa_iban' => 'CheckIban',
        'phone_token' => 'SepaPhoneToken',
        'pin_number' => 'SepaPhonePin',
        'formCardNonce' => 'SquareCardNonce'
    );

    public function __construct($crmId)
    {
        parent::__construct($crmId);
        $this->endpoint = rtrim(
                Registry::system('systemConstants.RESPONSE_API_BASE_URL'), '/'
        );
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();
    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareAffiliates();

        $this->params['slug'] = $this->methodSlugMapping['prospect'];

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'prospectId' => $this->response['CustomerID'],
        ));
    }

    protected function newOrderWithProspect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareProductDetails();
        //$this->prepareAffiliates();
        $this->prepareCardDetails();
        $this->prepareBillingDetails();

        $this->params['CustomerID'] = $this->params['prospectId'];

        $this->params['slug'] = $this->methodSlugMapping['newOrderWithProspect'];

        $this->cleanParamsBeforePost();

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['Transaction']['OrderInfo']['TransactionID'],
            'crmOrderId' => $this->response['Transaction']['OrderInfo']['OrderID'],
            'customerId' => $this->response['Transaction']['OrderInfo']['CustomerID'],
        ));
    }

    protected function newOrder()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareProductDetails();
        //$this->prepareAffiliates();
        $this->prepareCardDetails();
        $this->prepareBillingDetails();

        if (
            ($this->params['meta.isDownsellStep'] || 
                ($this->params['meta.isPrepaidFlow'] && !$this->params['meta.isUpsellStep'])
            ) && 
            !$this->params['meta.isSplitOrder'] && 
            !$this->params['meta.isScrapFlow']
        )
        {
            $this->prospect();
            $custResponse = CrmResponse::get('prospectId');
            if (empty($custResponse))
            {
                return false;
            }
            $this->params['CustomerID'] = CrmResponse::get('prospectId');
        }
        else
        {
            $this->params['CustomerID'] = !empty($this->params['customerId']) ? $this->params['customerId'] : $this->params['customer_id'];
        }

        if(
            $this->params['meta.isDownsellStep'] && 
            $this->params['meta.isSplitOrder'] && 
            empty($this->params['CustomerID'])
        ) {
            $this->params['CustomerID'] = $this->params['downsellCustomerId'];
        }

        $this->params['slug'] = $this->methodSlugMapping['newOrder'];
        
        $this->cleanParamsBeforePost();

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }
        
        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['Transaction']['OrderInfo']['TransactionID'],
            'crmOrderId' => $this->response['Transaction']['OrderInfo']['OrderID'],
            'customerId' => $this->response['Transaction']['OrderInfo']['CustomerID'],
        ));
    }

    protected function newOrderCardOnFile()
    {
        return $this->upsell();
    }

    protected function upsell()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->prepareProductDetails();
        
        if(!empty($this->additionalPaymentParams)) {
                foreach ($this->additionalPaymentParams as $key => $value)
            {
                if (!empty($this->params[$key]))
                {
                    $paymentInfo[$value] = $this->params[$key];
                    unset($this->params[$key]);
                }
            }

            $this->params['PaymentInformation'] = $paymentInfo;
            unset($this->params['PaymentInformation']['CheckIban']);
        }

        $this->params['CustomerID'] = $this->params['customerId'];
        $this->params['IpAddress'] = $this->params['ipAddress'];
        $this->params['ProductGroups'] = $this->params['productDetails'];
        $this->params['slug'] = $this->methodSlugMapping['upsell'];

        if (!empty($this->params['OrderID'])) {
            if (!empty($this->params['customerId'])) {
                unset($this->params['customerId']);
            }
            if (!empty($this->params['CustomerID'])) {
                //unset($this->params['CustomerID']);
            }
        }

        unset($this->params['productDetails']);
        $this->cleanParamsBeforePost();

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['Transaction']['OrderInfo']['TransactionID'],
            'customerId' => $this->response['Transaction']['OrderInfo']['CustomerID'],
        ));
    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['forceGatewayId']))
        {
            $this->params['forceMerchantId'] = $this->params['forceGatewayId'];
        }

        unset(
                $this->params['forceGatewayId'], $this->params['preserveGateway'], $this->params['parentOrderId'], $this->params['userIsAt'], $this->params['userAgent']
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
        $url = $this->getUrl($this->params['slug']);

        unset($this->params['slug']);

        $params = array_filter($this->params);
        $this->idempotentKey = $this->genUuid();
        $headers = array('Content-Type' => 'application/json', 'Authorization' => 'ApiGuid ' . $this->username, 'Idempotency-Key' => $this->idempotentKey);
        $response = Http::post($url, json_encode($params), $headers);

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

    private function prepareResponse($response)
    {
        $this->response = json_decode($response, true);
        if (empty($this->response) || $this->response['Status'] != 0)
        {
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'crmError' => !empty($this->response['ErrorMessage']) ? $this->response['ErrorMessage'] : '',
                ),
            ));
            return false;
        }

        if (
                !empty($this->response) &&
                $this->response['Status'] == 0 &&
                !empty($this->response['Transaction']['OrderInfo']) &&
                $this->response['Transaction']['OrderInfo']['Response'] != 1 &&
                $this->response['Transaction']['OrderInfo']['Response'] != 4
        )
        {
            CrmResponse::replace(array(
                'success' => false,
                'errors' => array(
                    'crmError' => @$this->response['Transaction']['OrderInfo']['ResponseText'],
                ),
            ));
            return false;
        }

        return true;
    }

    private function getUrl($slug)
    {
        return sprintf('%s/%s', $this->endpoint, $slug);
    }

    private function prepareShippingDetails()
    {
        $shippingDetails = array(
            'SiteID' => $this->accessor->getValue($this->params, '[campaignId]'),
            'FirstName' => $this->accessor->getValue($this->params, '[firstName]'),
            'LastName' => $this->accessor->getValue($this->params, '[lastName]'),
            'Email' => $this->accessor->getValue($this->params, '[email]'),
            'Phone' => $this->accessor->getValue($this->params, '[phone]'),
            'Address1' => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'Address2' => $this->accessor->getValue($this->params, '[shippingAddress2]'),
            'ZipCode' => $this->accessor->getValue($this->params, '[shippingZip]'),
            'City' => $this->accessor->getValue($this->params, '[shippingCity]'),
            'State' => $this->accessor->getValue($this->params, '[shippingState]'),
            'CountryISO' => $this->accessor->getValue($this->params, '[shippingCountry]'),
            'IpAddress' => Request::getClientIp(),
        );

        unset(
                $this->params['email'], $this->params['phone'], $this->params['shippingAddress1'], $this->params['shippingAddress2'], $this->params['shippingZip'], $this->params['shippingCity'], $this->params['shippingState'], $this->params['shippingCountry']
        );

        $this->params = array_replace($this->params, array_filter($shippingDetails));
    }

    private function prepareBillingDetails()
    {
        if (strtolower($this->params['billingSameAsShipping']) !== 'yes')
        {
            $this->params['BillingAddress'] = array(
                'FirstName' => $this->accessor->getValue($this->params, '[billingFirstName]'),
                'LastName' => $this->accessor->getValue($this->params, '[billingLastName]'),
                'Address1' => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'Address2' => $this->accessor->getValue($this->params, '[billingAddress2]'),
                'ZipCode' => $this->accessor->getValue($this->params, '[billingZip]'),
                'City' => $this->accessor->getValue($this->params, '[billingCity]'),
                'State' => $this->accessor->getValue($this->params, '[billingState]'),
                'CountryISO' => $this->accessor->getValue($this->params, '[billingCountry]')
            );
        }
        else
        {
            $this->params['BillingAddress'] = $this->GetCustomerAddress();
        }

        unset(
                $this->params['billingFirstName'], $this->params['billingLastName'], $this->params['billingAddress1'], $this->params['billingAddress2'], $this->params['billingCity'], $this->params['billingState'], $this->params['billingZip'], $this->params['billingCountry']
        );
    }

    private function prepareProductDetails()
    {
        $productGroup = array();
        $newApi = false;
        foreach ($this->params['products'] as $product)
        {
            if (empty($product['productId']))
            {
                continue;
            }
            if(empty($product['productKey'])) {
                $newApi = true;
                $products = array(
                    'ProductID' => $this->accessor->getValue($product, '[productId]'),
                    'Amount' => $this->accessor->getValue($product, '[productPrice]'),
                    'Quantity' => $this->accessor->getValue($product, '[productQuantity]'),
                );
            } else {
                $products = array(
                    'ProductGroupKey' => $this->accessor->getValue($product, '[productKey]'),
                    'CustomProducts' => array(array(
                            'ProductID' => $this->accessor->getValue($product, '[productId]'),
                            'Amount' => $this->accessor->getValue($product, '[productPrice]'),
                            'Quantity' => $this->accessor->getValue($product, '[productQuantity]'),
                        )
                    )
                );
            }
            
            array_push($productGroup, $products);
        }
        
        unset($this->params['products']);
        if($newApi) {
            $this->params['Products'] = $productGroup;
        } else {
            $this->params['productDetails'] = $productGroup;
        }

    }

    private function prepareCardDetails()
    {

        $paymentInfo = array(
            //'AffiliateID' => $this->accessor->getValue($this->params, '[AffiliateID]'),
            //'SubAffiliateID' => $this->accessor->getValue($this->params, '[SubAffiliateID]'),
            'CCNumber' => $this->accessor->getValue($this->params, '[cardNumber]'),
            'ExpMonth' => $this->accessor->getValue($this->params, '[cardExpiryMonth]'),
            'ExpYear' => !empty($this->params['cardExpiryYear']) ? '20'.$this->accessor->getValue($this->params, '[cardExpiryYear]') : '',
            'CVV' => $this->accessor->getValue($this->params, '[cvv]'),
            'NameOnCard' => $this->params['firstName'] . ' ' . $this->params['lastName'],
            'ProductGroups' => $this->params['productDetails']
        );
        

        foreach ($this->additionalPaymentParams as $key => $value)
        {
            if (!empty($this->params[$key]))
            {
                $paymentInfo[$value] = $this->params[$key];
                unset($this->params[$key]);
            }
        }

        if(!empty($paymentInfo['CheckIban']))
        {
            $paymentInfo['CheckName'] = $this->params['firstName'];
            unset($this->params['sepa_bic'], $paymentInfo['CCNumber'], $paymentInfo['ExpMonth'], $paymentInfo['ExpYear'], $paymentInfo['CVV'], $paymentInfo['NameOnCard']);
        }

        unset(
                $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv'], $this->params['productDetails']
        );

        $this->params['PaymentInformation'] = $paymentInfo;
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        $affiliateParams = array_filter(array(
            'AffiliateID' => $this->accessor->getValue($affiliates, '[affId]'),
            'SubAffiliateID' => $this->accessor->getValue($affiliates, '[c1]'),
            'SubAffiliateID2' => $this->accessor->getValue($affiliates, '[c2]'),
            'SubAffiliateID3' => $this->accessor->getValue($affiliates, '[c3]'),
            'SubAffiliateID4' => $this->accessor->getValue($affiliates, '[c4]'),
            'SubAffiliateID5' => $this->accessor->getValue($affiliates, '[c5]'),
        ));

        unset($this->params['affiliates']);

        $this->params = array_replace($this->params, $affiliateParams);
    }

    private function cleanParamsBeforePost()
    {
        unset($this->params['prospectId'], $this->params['firstName'], $this->params['lastName'], $this->params['userIsAt'], $this->params['billingSameAsShipping'], $this->params['billingFirstName'], $this->params['billingLastName'], $this->params['billingAddress1'], $this->params['billingZip'], $this->params['billingCity'], $this->params['billingState'], $this->params['billingCountry'], $this->params['cardType'], $this->params['cardNumber'], $this->params['SiteID'], $this->params['FirstName'], $this->params['LastName'], $this->params['Email'], $this->params['Phone'], $this->params['Address1'], $this->params['Address2'], $this->params['ZipCode'], $this->params['City'], $this->params['State'], $this->params['CountryISO'], $this->params['campaignId'], $this->params['ipAddress'], $this->params['downsellCustomerId']);
    }

    private function GetCustomerAddress()
    {
        return $shippingDetails = array(
            'FirstName' => $this->accessor->getValue($this->params, '[FirstName]'),
            'LastName' => $this->accessor->getValue($this->params, '[LastName]'),
            'Email' => $this->accessor->getValue($this->params, '[Email]'),
            'Phone' => $this->accessor->getValue($this->params, '[Phone]'),
            'Address1' => $this->accessor->getValue($this->params, '[Address1]'),
            'Address2' => $this->accessor->getValue($this->params, '[Address2]'),
            'ZipCode' => $this->accessor->getValue($this->params, '[ZipCode]'),
            'City' => $this->accessor->getValue($this->params, '[City]'),
            'State' => $this->accessor->getValue($this->params, '[State]'),
            'CountryISO' => $this->accessor->getValue($this->params, '[CountryISO]')
        );
    }

    public function preAuthorization()
    {
        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => strtoupper(uniqid()),
            'customerId' => strtoupper(uniqid()),
        ));
    }

    public function phoneVerification()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['slug'] = $this->methodSlugMapping['phoneVerification'];
        
        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'phone_token'    => $this->response['Token'],
        ));

    }

    public function checkPrepaidBin()
    {
        $bin = CrmPayload::get('bin');
        $url = sprintf('%s/%s/%s', $this->endpoint, $this->methodSlugMapping['binCheck'], $bin);
        $headers = array('Authorization' => 'ApiGuid ' . $this->username);
        $response = Http::get($url, $headers);
        if (!empty($response) && is_array($response) && !empty($response['errorMessage']))
        {
            return false;
        }
        $response = json_decode($response);

        if ($response->Status == 0 && !empty($response->Bin->Category) &&
            ($response->Bin->Category == 'PREPAID' || $response->Bin->Category == 'GIFT')
           )
        {
            return true;
        }
        return false;
    }

    public static function isValidCredential($credential)
    {
        if (is_array($credential) && !empty($credential))
        {
            $endpoint = rtrim(
                    Registry::system('systemConstants.RESPONSE_API_BASE_URL'), '/'
            );
            $url = sprintf('%s/%s', $endpoint, 'test-auth');

            try
            {
                $headers = array('Authorization' => 'ApiGuid ' . $credential['username']);
                $response = Http::get($url, $headers);

                if (!empty($response) && is_array($response) && !empty($response['errorMessage']))
                {
                    return false;
                }

                $response = json_decode($response);

                if (
                        $response->Status == 0
                )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            catch (Exception $ex)
            {
                return false;
            }
        }
    }
    
    protected function genUuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

}
