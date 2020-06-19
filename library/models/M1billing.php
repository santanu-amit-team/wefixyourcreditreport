<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;
use Exception;

class M1billing extends BaseCrm
{

    private $methodSlugMapping = array(
        'prospect' => 'customer/doAddCustomerAddress',
        'newOrderWithProspect' => 'order/doAddProcess',
        'newOrder' => 'order/doAddProcess',
        'newOrderCardOnFile' => 'order/doAddProcessOrder',
        'importUpsell' => 'order/doAddProcessOrder',
        'preAuthorization' => 'order/preauth/',
        'orderView' => 'order/view/',
        'getMerchant' => 'route/getMerchant/',
        'process3ds' => 'transaction/doProcess3ds/'
    );
    private $cardTypeID = array(
        'visa' => 2,
        'master' => 1,
        'discover' => 3,
        'amex' => 4
    );
    protected $requiredPayload = array(
        'prospect' => array(
            'user_id', 'user_password', 'first_name',
            'last_name', 'email', 'phone', 'fname',
            'lname', 'country', 'address1', 'address2', 'city',
            'state', 'zipcode', 'slug', 'tracking1', 'tracking2',
            'tracking3', 'tracking4', 'tracking5', 'tracking6', 'campaign_id'
        ),
        'newOrderWithProspect' => array(
            'user_id', 'user_password', 'campaign_id', 'offer_id',
            'customer_id', 'customers_address_billing_id', 'customers_address_shipping_id',
            'shipping_same', 'ship_fname', 'ship_lname', 'ship_address1', 'ship_address2', 'ship_city', 'ship_state',
            'ship_country', 'ship_zipcode', 'order_notes', 'ip_address', 'session_id', 'tracking1', 'tracking2',
            'tracking3', 'tracking4', 'tracking5', 'tracking6', 'card_type_id', 'card_number', 'card_cvv',
            'card_exp_month', 'card_exp_year', 'slug', 'merchant_id', 'items', 'order_eci', 'order_xid', 'order_cavv', '3ds_redirect_url', 'order_cardholder_auth', 'order_3ds_auth_cycle'
        ),
        'newOrder' => array(
            'user_id', 'user_password', 'campaign_id', 'offer_id', 'customer_id',
            'shipping_same', 'ship_fname', 'ship_lname', 'ship_address1', 'ship_address2', 'ship_city', 'ship_state',
            'ship_country', 'ship_zipcode', 'order_notes', 'ip_address', 'session_id', 'tracking1', 'tracking2',
            'tracking3', 'tracking4', 'tracking5', 'tracking6', 'card_type_id', 'card_number', 'card_cvv',
            'card_exp_month', 'card_exp_year', 'slug', 'merchant_id', 'email', 'phone',
            'bill_fname', 'bill_lname', 'bill_organization', 'bill_country', 'bill_address1', 'bill_address2',
            'bill_city', 'bill_state', 'bill_zipcode', 'items', 'order_eci', 'order_xid', 'order_cavv', '3ds_redirect_url', 'order_cardholder_auth', 'order_3ds_auth_cycle'
        ),
        'newOrderCardOnFile' => array(
            'user_id', 'user_password', 'campaign_id', 'offer_id',
            'order_id', 'card_cvv', 'items', 'merchant_id', 'order_eci', 'order_xid', 'order_cavv', '3ds_redirect_url', 'order_cardholder_auth', 'order_3ds_auth_cycle'
        ),
        'orderView' => array(
            'user_id', 'user_password', 'order_id', 'with'
        ),
        'getMerchant' => array('user_id', 'user_password', 'offer_id', 'customer_id', 'card_type_id'),
        'process3ds' => array('user_id', 'user_password', 'transaction_id', 'transaction_3ds_token')
    );

    public function __construct($crmId)
    {
        parent::__construct($crmId);
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();
        $this->params['user_id'] = $this->username;
        $this->params['user_password'] = $this->password;
    }

    private function setNecessaryKey()
    {
        $this->params['campaign_id'] = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['ip_address'] = $this->accessor->getValue($this->params, '[ipAddress]');
        $this->params['session_id'] = uniqid();
    }

    protected function prospect()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->prepareShippingDetails();
        $this->prepareAffiliates();
        $this->params['campaign_id'] = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['slug'] = $this->methodSlugMapping['prospect'];
        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'prospectId' => $this->response['data']['customer'],
            'customerAddressId' => $this->response['data']['customer_address_id'],
        ));
    }

    protected function newOrderWithProspect()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->prepareBillingDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();
        $this->setNecessaryKey();
        $this->prepareProductDetails();

        $this->params['customer_id'] = $this->params['prospectId'];
        $this->params['slug'] = $this->methodSlugMapping['newOrderWithProspect'];

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['data']['transaction']['order_id'],
            'customerId' => $this->response['data']['transaction']['customer_id'],
            'gatewayId' => $this->response['data']['transaction']['merchant_id'],
            'response_code' => $this->response['data']['transaction']['response_code'],
            'transaction_id' => $this->response['data']['transaction']['transaction_id'],
            '3ds_post_url' => empty($this->response['data']['transaction']['3ds_post_url']) ? null :
                    $this->response['data']['transaction']['3ds_post_url']
        ));
    }

    protected function newOrder()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();
        $this->setNecessaryKey();
        $this->prepareProductDetails();

        $this->params['slug'] = $this->methodSlugMapping['newOrder'];
        if (!empty($this->params['prospectId']))
        {
            $this->params['customer_id'] = $this->params['prospectId'];
        }

        unset($this->params['userAgent']);

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['data']['transaction']['order_id'],
            'customerId' => $this->response['data']['transaction']['customer_id'],
            'gatewayId' => $this->response['data']['transaction']['merchant_id'],
            'response_code' => $this->response['data']['transaction']['response_code'],
            'transaction_id' => $this->response['data']['transaction']['transaction_id'],
            '3ds_post_url' => empty($this->response['data']['transaction']['3ds_post_url']) ? null :
                    $this->response['data']['transaction']['3ds_post_url']
        ));
    }

    protected function newOrderCardOnFile()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->setNecessaryKey();
        $this->prepareProductDetails();
        $this->params['slug'] = $this->methodSlugMapping['newOrderCardOnFile'];
        $this->params['order_id'] = $this->params['previousOrderId'];
        $this->params['card_cvv'] = $this->params['cvv'];
        if ($this->makeHttpRequest() === false)
        {
            return false;
        }


        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['data']['transaction']['order_id'],
            'customerId' => $this->response['data']['transaction']['customer_id'],
            'gatewayId' => $this->response['data']['transaction']['merchant_id'],
            'response_code' => $this->response['data']['transaction']['response_code'],
            'transaction_id' => $this->response['data']['transaction']['transaction_id'],
            '3ds_post_url' => empty($this->response['data']['transaction']['3ds_post_url']) ? null :
                    $this->response['data']['transaction']['3ds_post_url']
        ));
    }

    private function filterPayload($requiredPayloadKey = array())
    {
        if (!empty($requiredPayloadKey))
        {
            foreach (array_keys($this->params) as $key => $value)
            {
                if (!in_array($value, $requiredPayloadKey))
                {
                    unset($this->params[$value]);
                }
            }

            $this->params = array_filter($this->params);
        }
    }

    public function preAuthorization()
    {

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => strtoupper(uniqid()),
            'customerId' => strtoupper(uniqid()),
        ));
    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['forceGatewayId']))
        {
            $this->params['merchant_id'] = trim($this->params['forceGatewayId']);
        }

        unset(
                $this->params['forceGatewayId'], $this->params['preserveGateway'], $this->params['parentOrderId'], $this->params['userIsAt'], $this->params['billingSameAsShipping']
        );

        $metaKeys = preg_grep('/^meta\..+$/', array_keys($this->params));
        foreach ($metaKeys as $metaKey)
        {
            unset($this->params[$metaKey]);
        }

        $this->response = array();
    }

    public function process3ds()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->params['slug'] = $this->methodSlugMapping['process3ds'];

        if ($this->makeHttpRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'data' => $this->response
        ));
    }

    private function makeHttpRequest()
    {
        $callingMethod = $this->params['meta.crmMethod'];
        $this->beforeHttpRequest();
        $url = $this->getUrl($this->params['slug']);
        $this->filterPayload($this->requiredPayload[$callingMethod]);

        unset($this->params['slug']);

        $params = array_filter($this->params);
        $headers = array('Content-Type' => 'application/json');
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

    private function prepareProductDetails()
    {
        $products = $this->accessor->getValue($this->params, '[products]');

        if (!is_array($products) || empty($products))
        {
            return;
        }
        if (empty($products[0]['m1billing_offer_id']))
        {
            $this->params['offer_id'] = $this->params['products'][0]['productId'];
            if (!empty($products[0]['campaignId']))
            {
                $this->params['campaign_id'] = $products[0]['campaignId'];
            }
            return;
        }
        $this->params['offer_id'] = $products[0]['m1billing_offer_id'];
        $this->params['campaign_id'] = $products[0]['campaignId'];
        $items = $result = array();
        foreach ($products as $key => $product)
        {
            $productId = (int) $this->accessor->getValue($product, '[productId]');
            if (empty($productId))
            {
                continue;
            }
            $product = array_shift($products);
            $result[$key]['item_id'] = $this->accessor->getValue($product, '[productId]');
            $result[$key]['quantity'] = $this->accessor->getValue($product, '[productQuantity]');
            $result[$key]['price'] = $this->accessor->getValue($product, '[productPrice]');
        }
        $items['items'] = $result;
        unset($this->params['products']);
        $this->params = array_replace($this->params, array_filter($items));
    }

    private function prepareResponse($response)
    {
        $this->response = json_decode($response, true);
        if ($this->response['success'])
        {
            return true;
        }
        CrmResponse::replace(array(
            'success' => false,
            'errors' => array(
                'crmError' => $this->response['message'],
            ),
        ));
        if (
                !empty($this->response['message']) &&
                preg_match("/Prepaid cards not allowed/i", $this->response['message'])
        )
        {
            CrmResponse::set('isPrepaidDecline', true);
        }

        return false;
    }

    private function getUrl($slug)
    {
        return sprintf('%s/%s', $this->endpoint, $slug);
    }

    private function prepareShippingDetails()
    {
        $shippingDetails = array(
            'email' => $this->accessor->getValue($this->params, '[email]'),
            'phone' => $this->accessor->getValue($this->params, '[phone]'),
            'first_name' => $this->accessor->getValue($this->params, '[firstName]'),
            'last_name' => $this->accessor->getValue($this->params, '[lastName]'),
            'fname' => $this->accessor->getValue($this->params, '[firstName]'),
            'lname' => $this->accessor->getValue($this->params, '[lastName]'),
            'country' => $this->accessor->getValue($this->params, '[shippingCountry]'),
            'address1' => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'city' => $this->accessor->getValue($this->params, '[shippingCity]'),
            'state' => $this->accessor->getValue($this->params, '[shippingState]'),
            'zipcode' => $this->accessor->getValue($this->params, '[shippingZip]'),
            'bill_address1' => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'bill_address2' => $this->accessor->getValue($this->params, '[shippingAddress2]'),
            'bill_zipcode' => $this->accessor->getValue($this->params, '[shippingZip]'),
            'bill_city' => $this->accessor->getValue($this->params, '[shippingCity]'),
            'bill_state' => $this->accessor->getValue($this->params, '[shippingState]'),
            'bill_country' => $this->accessor->getValue($this->params, '[shippingCountry]'),
            'bill_fname' => $this->accessor->getValue($this->params, '[firstName]'),
            'bill_lname' => $this->accessor->getValue($this->params, '[lastName]'),
            'shipping_same' => true,
            'session_id' => uniqid()
        );

        $this->params = array_replace($this->params, array_filter($shippingDetails));
    }

    private function prepareBillingDetails()
    {
        $billingDetails = array();
        if (!empty($this->params['meta.mainStepData']['customerAddressId']))
        {
            $this->params['customers_address_billing_id'] = $this->params['meta.mainStepData']['customerAddressId'];
        }
        if (strtolower($this->params['billingSameAsShipping']) !== 'yes')
        {
            $billingDetails = array(
                'ship_fname' => $this->accessor->getValue($this->params, '[billingFirstName]'),
                'ship_lname' => $this->accessor->getValue($this->params, '[billingLastName]'),
                'ship_address1' => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'ship_address2' => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'ship_city' => $this->accessor->getValue($this->params, '[billingCity]'),
                'ship_state' => $this->accessor->getValue($this->params, '[billingState]'),
                'ship_zipcode' => $this->accessor->getValue($this->params, '[billingZip]'),
                'ship_country' => $this->accessor->getValue($this->params, '[billingCountry]'),
                'shipping_same' => false,
            );
        }
        else
        {
            if (!empty($this->params['meta.mainStepData']['customerAddressId']))
            {
                $this->params['customers_address_shipping_id'] = $this->params['meta.mainStepData']['customerAddressId'];
            }
            $this->params['shipping_same'] = true;
        }

        $this->params = array_replace($this->params, array_filter($billingDetails));
    }

    private function prepareCardDetails()
    {
        $cardDetails = array(
            'card_number' => $this->accessor->getValue($this->params, '[cardNumber]'),
            'card_type_id' => $this->cardTypeID[$this->accessor->getValue($this->params, '[cardType]')],
            'card_exp_month' => $this->accessor->getValue($this->params, '[cardExpiryMonth]'),
            'card_exp_year' => !empty($this->params['cardExpiryYear']) ? '20' . $this->accessor->getValue($this->params, '[cardExpiryYear]') : '',
            'card_cvv' => $this->accessor->getValue($this->params, '[cvv]')
        );


        unset(
                $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv'], $this->params['cardType'], $this->params['cardNumber']
        );

        $this->params = array_replace($this->params, array_filter($cardDetails));
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        $affiliateParams = array_filter(array(
            'tracking1' => $this->accessor->getValue($affiliates, '[affId]'),
            'tracking2' => $this->accessor->getValue($affiliates, '[c1]'),
            'tracking3' => $this->accessor->getValue($affiliates, '[c2]'),
            'tracking4' => $this->accessor->getValue($affiliates, '[c3]'),
            'tracking5' => $this->accessor->getValue($affiliates, '[c4]'),
            'tracking6' => $this->accessor->getValue($affiliates, '[c5]'),
        ));

        unset($this->params['affiliates']);

        $this->params = array_replace($this->params, $affiliateParams);
    }

    public static function isValidCredential($credential)
    {
        if (is_array($credential) && !empty($credential))
        {
            $params = array(
                'user_id' => $credential['username'],
                'user_password' => $credential['password']
            );
            $endpoint = rtrim(
                    $credential['endpoint'], '/'
            );
            $url = sprintf('%s/campaign/index', $endpoint);
            $response = Http::post($url, $params);
            $response = json_decode($response);
            if ($response->success
            )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function orderView()
    {

        $this->beforeAnyCrmClassMethodCall();

        $orderIds = CrmPayload::get('orderIds');
        $uniqueOrderIds = array_unique($orderIds);
        $result = $data = array();
        //print_r($uniqueOrderIds);die;
        foreach ($uniqueOrderIds as $orderId)
        {
            $this->params['slug'] = $this->methodSlugMapping['orderView'] . $orderId;
            $this->params['order_id'] = $orderId;
            if ($this->makeHttpGetRequest() === false)
            {
                $data[$orderId] = 'Not Found';
                continue;
            }

            if (!empty($this->response['data']['order']))
            {
                $data[$orderId] = $this->response['data']['order'];
            }
            else
            {
                $data[$orderId] = 'Unexpected error';
            }
        }

        foreach ($orderIds as $key => $orderId)
        {
            $result[$key] = $data[$orderId];
        }

        CrmResponse::replace(array(
            'success' => true,
            'result' => $result,
        ));
    }

    public function getMerchant()
    {
        $this->beforeAnyCrmClassMethodCall();
        $card_type_id = CrmPayload::get('card_type_id');
        $offer_id = CrmPayload::get('offer_id');
        if (empty($offer_id) || empty($card_type_id))
        {
            CrmResponse::replace(array(
                'success' => false,
                'msg' => 'No offer id or card type id found'
            ));
        }
        $this->params['offer_id'] = CrmPayload::get('offer_id');
        $this->params['card_type_id'] = CrmPayload::get('card_type_id');
        $this->params['slug'] = $this->methodSlugMapping['getMerchant'];
        if (CrmPayload::get('customer_id'))
        {
            $this->params['customer_id'] = CrmPayload::get('customer_id');
        }
        if ($this->makeHttpGetRequest() === false)
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'result' => $this->response['data']
        ));
    }

    private function makeHttpGetRequest()
    {
        $callingMethod = $this->params['meta.crmMethod'];
        $this->beforeHttpRequest();

        $url = $this->getUrl($this->params['slug']);
        $this->filterPayload($this->requiredPayload[$callingMethod]);
        unset($this->params['slug']);
        $params = array_filter($this->params);
        $response = Http::get($url . '?' . http_build_query($params), array(), array(CURLOPT_FAILONERROR => false));
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

}
