<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;

class Limelightv2 extends BaseCrm
{

    private $transactionMethods = array(
        'prospect' => '/api/v1/new_prospect',
        'newOrderWithProspect' => '/api/v1/new_order_with_prospect',
        'newOrder' => '/api/v1/new_order',
        'newOrderCardOnFile' => '/api/v1/new_order_card_on_file',
        'preAuthorization' => '/api/v1/authorize_payment',
        'offlinePayment' => '/api/v1/new_order',
        'getVariantSpecificProductPrice' => '/api/v1/product_attribute_index'
    );
    private $memberShipMethods = array(
        'orderUpdate' => '/api/v1/order_update',
        'updateOrderRecurring' => '/api/v1/order_update_recurring',
        'getAlternateProvider' => '/api/v1/get_alternative_provider',
        'orderUpdateRecurring' => '/api/v1/order_update_recurring',
        'validateCoupon' => '/api/v1/coupon_validate',
        'orderView' => '/api/v1/order_view',
        'orderFind' => '/api/v1/order_find',
    );
    private $errorMessages = array(
        '342' => 'Invalid Email Address',
        '901' => 'Invalid return URL',
        '902' => 'Invalid cancel URL',
        '903' => 'Error retrieving alternative provider data',
        '904' => 'Campaign does not support an alternative payment provider',
    );
    private $isPriceFromVariant;

    public function __construct($crmId)
    {
        parent::__construct($crmId);
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();

        $this->params['username'] = $this->username;
        $this->params['password'] = $this->password;
        $this->isPriceFromVariant = false;
    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $shippingKeys = preg_grep('/^shipping/', array_keys($this->params));
        foreach ($shippingKeys as $shippingKey)
        {
            $this->params[
                    lcfirst(str_replace('shipping', '', $shippingKey))
                    ] = $this->params[$shippingKey];
            unset($this->params[$shippingKey]);
        }

        $this->prepareAffiliates();

        $this->params['method'] = $this->transactionMethods['prospect'];

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'prospectId' => $this->response['prospectId'],
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

        $this->params['method'] = $this->transactionMethods['newOrderWithProspect'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['order_id'],
            'customerId' => $this->response['customerId'],
            'gatewayId' => $this->response['gatewayId'],
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

        $this->params['method'] = $this->transactionMethods['newOrder'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['order_id'],
            'customerId' => $this->response['customerId'],
            'gatewayId' => $this->response['gatewayId'],
        ));
    }

    protected function newOrderCardOnFile()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['CVV'] = $this->params['cvv'];

        $this->prepareProductDetails();
        $this->removeCardDetails();
        $this->prepareAffiliates();

        $this->params['method'] = $this->transactionMethods['newOrderCardOnFile'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['order_id'],
            'customerId' => $this->response['customerId'],
            'gatewayId' => $this->response['gatewayId'],
        ));
    }

    public function preAuthorization()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        if (empty($this->params['productId']))
        {
            $this->params['productId'] = $this->params['products'][0]['productId'];
        }

        $this->params['method'] = $this->transactionMethods['preAuthorization'];
        $this->params['auth_amount'] = $this->params['authorizationAmount'];
        if (empty($this->params['auth_amount']))
        {
            $this->params['auth_amount'] = '0.00';
        }
        $this->params['tranType'] = 'Sale';
        $this->params['save_customer'] = 1;
        $this->params['void_flag'] = 1;
        
        unset($this->params['sessionId'], $this->params['authorizationAmount'], $this->params['products']);

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['tempCustomerId'],
            'customerId' => $this->response['tempCustomerId'],
            'gatewayId' => $this->response['gatewayId'],
        ));
    }

    public function orderUpdate()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['orderUpdate'];

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
        ));
    }

    public function orderUpdateRecurring()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['orderUpdateRecurring'];

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
        ));
    }

    public function validateCoupon()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['validateCoupon'];

        $this->params['campaign_id'] = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['promo_code'] = $this->accessor->getValue($this->params, '[couponCode]');
        $this->params['shipping_id'] = $this->accessor->getValue(
                $this->params, '[products][0][shippingId]'
        );

        $this->params['product_ids'] = array();
        foreach ($this->params['products'] as $product)
        {
            $productId = $this->accessor->getValue($product, '[productId]');
            array_push($this->params['product_ids'], $productId);
            $this->params[
                    sprintf('product_qty_%s', $productId)
                    ] = $this->accessor->getValue($product, '[productQuantity]');
        }
        $this->params['product_ids'] = implode(',', $this->params['product_ids']);

        unset(
                $this->params['campaignId'], $this->params['couponCode'], $this->params['products'], $this->params['preserveGateway']
        );

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'couponAmount' => $this->accessor->getValue($this->response, '[coupon_amount]'),
        ));
    }

    public function getAlternateProvider()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['getAlternateProvider'];

        $products = $this->accessor->getValue($this->params, '[products]');
        $productIds = $productPrices = $productQuantities = array();
        $orderTotal = 0.00;
        foreach ($products as $product)
        {
            array_push(
                    $productIds, $this->accessor->getValue($product, '[productId]')
            );
            array_push(
                    $productPrices, $this->accessor->getValue($product, '[productPrice]')
            );
            array_push(
                    $productQuantities, $this->accessor->getValue($product, '[productQuantity]')
            );
            $orderTotal += (
                    (float) $this->accessor->getValue($product, '[productQuantity]') * (float) $this->accessor->getValue($product, '[productPrice]') + (float) $this->accessor->getValue($product, '[shippingPrice]')
                    );
        }

        $this->calculateProductDiscount($orderTotal, $productPrices, $productIds);

        $this->params['shipping_id'] = $this->accessor->getValue($products, '[0][shippingId]');
        $this->params['campaign_id'] = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['amount'] = $orderTotal;
        $this->params['products'] = implode(',', $productIds);
        $this->params['product_price'] = implode(',', $productPrices);
        $this->params['product_qty'] = implode(',', $productQuantities);
        $this->params['alt_pay_type'] = $this->accessor->getValue($this->params, '[cardType]');
        $this->params['return_url'] = $this->accessor->getValue($this->params, '[returnUrl]');
        $this->params['cancel_url'] = $this->accessor->getValue($this->params, '[cancelUrl]');

        unset(
                $this->params['cardType'], $this->params['cancelUrl'], $this->params['returnUrl']
        );

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'redirect' => $this->response['redirect_url'],
        ));
    }

    private function calculateProductDiscount(&$orderTotal, &$productPrices, $productIds)
    {
        if (!empty($this->params['discount_amount']))
        {
            $orderTotal = $orderTotal - $this->params['discount_amount_total'];
            if ($this->params['discount_type'] == 'each')
            {
                foreach ($productPrices as $key => $value)
                {
                    $productPrices[$key] = $productPrices[$key] - $this->params['discount_amount'][$productIds[$key]];
                }
            }
            else
            {
                $productTotal = array_sum($productPrices);
                $discountProductTotal = $productTotal - $this->params['discount_amount'];
                foreach ($productPrices as $key => $value)
                {
                    $prodPer = round(($productPrices[$key] / $productTotal), 2);
                    $productPrices[$key] = ($discountProductTotal * $prodPer);
                }
            }
        }
    }

    public function orderView()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params['method'] = $this->memberShipMethods['orderView'];

        $orderIds = CrmPayload::get('orderIds');
        $uniqueOrderIds = array_values(array_unique($orderIds));
        $result = $data = array();

        $this->params['order_id'] = implode(',', $uniqueOrderIds);

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        if (empty($this->response['total_orders']))
        {
            $data[$uniqueOrderIds[0]] = $this->response;
        }
        else
        {
            $data = json_decode($this->response['data'], true);
        }

        foreach ($orderIds as $key => $orderId)
        {
            if (!empty($data[$orderId]) && is_array($data[$orderId]))
            {
                $result[$key] = $data[$orderId];
            }
            else
            {
                $result[$key] = 'Not found!';
            }
        }

        CrmResponse::replace(array(
            'success' => true,
            'result' => $result,
        ));
    }

    public function orderFind()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params['method'] = $this->memberShipMethods['orderFind'];
        $this->params['criteria'] = CrmPayload::get('criteria');
        $this->params['start_date'] = CrmPayload::get('start_date');
        $this->params['end_date'] = CrmPayload::get('end_date');
        $this->params['campaign_id'] = CrmPayload::get('campaign_id');
        $this->params['return_type'] = CrmPayload::get('return_type');
        $result = $data = array();

        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        if (!empty($this->response['total_orders']))
        {
            $data = $this->response['data'];
        }

        CrmResponse::replace(array(
            'success' => true,
            'result' => $data,
        ));
    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['userIsAt']) || !empty($this->params['userAgent']))
        {
            $this->params['notes'] = sprintf(
                    '%s | %s', $this->params['userIsAt'], $this->params['userAgent']
            );
        }

        if (!empty($this->params['customNotes']))
        {
            $this->params['notes'] .= '| ' . $this->params['customNotes'];
        }

        if (!empty($this->params['preserveGateway']))
        {
            $this->params['preserve_force_gateway'] = 1;
        }

        if (!empty($this->params['parentOrderId']))
        {
            $this->params['master_order_id'] = $this->accessor->getValue(
                    $this->params, '[parentOrderId]'
            );
        }

        if (!CrmPayload::has('three_d_redirect_url') &&
                CrmPayload::has('meta.crmMethod') &&
                CrmPayload::get('meta.crmMethod') != 'prospect')
        {

            $this->params['three_d_redirect_url'] = true;
        }

        if (!empty($this->params['couponCode']))
        {
            $this->params['promoCode'] = $this->params['couponCode'];
        }

        unset(
                $this->params['preserveGateway'], $this->params['parentOrderId'], $this->params['userIsAt'], $this->params['userAgent'], $this->params['couponCode']
        );
        
        $evaluateForceGatewayArray = $this->params['forceGatewayId'];
        if(!empty($evaluateForceGatewayArray) && is_array($evaluateForceGatewayArray))
        {
            unset($this->params['forceGatewayId']);
        }

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

        $url = $this->getUrl($this->params['method']);
        $params = array_filter($this->params);
        unset($params['method'], $params['username'], $params['password']);
        $headers = array(
            'Content-Type' => 'application/json',
            'authorization' => 'Basic ' . base64_encode($this->params['username'] . ':' . $this->params['password'])
        );

        $response = Http::post($url, json_encode($params, JSON_UNESCAPED_SLASHES), $headers, array(CURLOPT_HTTP_VERSION  => CURL_HTTP_VERSION_1_1));
        
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

        $this->response = json_decode($response, true);

        $this->filterResponse();

        if (!empty($this->response['errorFound']) || (int) $this->response['responseCode'] !== 100)
        {
            CrmResponse::replace(array(
                'success' => false,
                'declineOrderId' => !empty($this->response['order_id']) ? $this->response['order_id'] : '',
                'errors' => array(
                    'crmError' => $this->response['errorMessage'],
                ),
            ));
            if (
                    !empty($this->response['responseCode']) &&
                    preg_match("/Prepaid.+Not Accepted/i", $this->response['errorMessage'])
            )
            {
                CrmResponse::set('isPrepaidDecline', true);
            }
            return false;
        }
        return true;
    }

    private function getUrl($limelightMethod)
    {
        return trim($this->endpoint, '/') . $limelightMethod;
    }

    private function filterResponse()
    {
        $keyMaps = array(
            'error_message' => 'errorMessage',
            'declineReason' => 'errorMessage',
            'decline_reason' => 'errorMessage',
            'error_code' => 'errorCode',
            'response_code' => 'responseCode',
            'gateway_id' => 'gatewayId',
            'temp_customer_id' => 'tempCustomerId',
            'responseCode' => 'responseCode',
        );
        foreach ($keyMaps as $alias => $key)
        {
            if (!isset($this->response[$key]) && isset($this->response[$alias]))
            {
                $this->response[$key] = $this->response[$alias];
                unset($this->response[$alias]);
            }
        }
        if (
                empty($this->response['errorMessage']) &&
                array_key_exists($this->response['responseCode'], $this->errorMessages)
        )
        {
            $this->response['errorMessage'] = $this->errorMessages[$this->response['responseCode']];
        }
        elseif (empty($this->response['errorMessage']))
        {
            $this->response['errorMessage'] = 'Something went wrong.';
        }
    }

    private function prepareShippingDetails()
    {
        $shippingCountry = $this->accessor->getValue($this->params, '[shippingCountry]');
        if (!in_array($shippingCountry, array('US', 'CA')))
        {
            $this->params['shippingState'] = sprintf(
                    '%s-%s', $shippingCountry, $this->accessor->getValue($this->params, '[shippingState]')
            );
        }
    }

    private function prepareBillingDetails()
    {
        $billingDetails = array('billingSameAsShipping' => 'NO');
        if (strtolower($this->params['billingSameAsShipping']) === 'yes')
        {
            $billingDetails = array(
                'billingSameAsShipping' => 'YES',
                'billingFirstName' => $this->accessor->getValue($this->params, '[firstName]'),
                'billingLastName' => $this->accessor->getValue($this->params, '[lastName]'),
                'billingAddress1' => $this->accessor->getValue($this->params, '[shippingAddress1]'),
                'billingAddress2' => $this->accessor->getValue($this->params, '[shippingAddress2]'),
                'billingZip' => $this->accessor->getValue($this->params, '[shippingZip]'),
                'billingCity' => $this->accessor->getValue($this->params, '[shippingCity]'),
                'billingState' => $this->accessor->getValue($this->params, '[shippingState]'),
                'billingCountry' => $this->accessor->getValue($this->params, '[shippingCountry]'),
            );
        }

        $this->params = array_replace($this->params, array_filter($billingDetails));

        $billingCountry = $this->accessor->getValue($this->params, '[billingCountry]');
        $billingState = $this->accessor->getValue($this->params, '[billingState]');
        if (
                !in_array($billingCountry, array('US', 'CA')) &&
                strpos($billingState, $billingCountry) === false
        )
        {
            $this->params['billingState'] = sprintf('%s-%s', $billingCountry, $billingState);
        }
    }

    private function prepareProductDetails()
    {
        $products = $this->accessor->getValue($this->params, '[products]');

        if (!is_array($products) || empty($products))
        {
            return;
        }

        $product = array_shift($products);
        $result['productId'] = $this->accessor->getValue($product, '[productId]');
        //$result['shippingId'] = $this->accessor->getValue($product, '[shippingId]');
        $result['shippingId'] = empty($this->params['shippingId']) ? 
                $this->accessor->getValue($product, '[shippingId]') : $this->params['shippingId'];

        if (!empty($this->params['products'][0]['enableBillingModule']))
        {
            $billingModuleArray = $this->createBillingModule($result);
        }

        unset($this->params['products']);

        if (!empty($billingModuleArray))
        {
            $this->params['offers'] = $billingModuleArray;
            $this->params['shippingId'] = $result['shippingId'];
        }
    }

    private function prepareProduuctVariants($productId)
    {
        $variant = array();
        if (empty($this->params['product_attribute'][$productId]))
            return false;

        foreach (array_keys($this->params['product_attribute'][$productId]) as $key => $value)
        {
            if ($value == "price" &&
                    $this->params['product_attribute'][$productId][$value] == 0)
            {
                $this->isPriceFromVariant = true;
            }
            else if ($value != "price")
            {
                $variant[$key]['attribute_name'] = $value;
                $variant[$key]['attribute_value'] = $this->params['product_attribute'][$productId][$value];
            }
        }
        return $variant;
    }

    private function createBillingModule($result)
    {
        $billingModuleArray = array();
        foreach ($this->params['products'] as $key => $value)
        {
            $mainProductListKey = $key;
            $billingModuleArray[$key] = array(
                'offer_id' => $this->params['products'][$key]['offerId'],
                'billing_model_id' => $this->params['products'][$key]['billingModelId'],
                'product_id' => $this->params['products'][$key]['productId'],
                'step_num' => $key + 1
            );
            if(
                empty($this->params['meta.bypassPrice'])
            ) {
                $billingModuleArray[$key]['price'] = $value['productPrice'];
                $billingModuleArray[$key]['quantity'] = $value['productQuantity'];
            }
            $variant = $this->prepareProduuctVariants($value['productId']);
            if (!empty($variant))
            {
                $billingModuleArray[$key]['variant'] = $variant;
                if ($this->isPriceFromVariant)
                {
                    unset($billingModuleArray[$key]['price']);
                }
            }

            if (!empty($this->params['products'][$key]['childrenSettings']))
            {
                $childrenSettings = $this->params['products'][$key]['childrenSettings'];
                $configs = preg_split("/\\r\,|\\r|\,/", $childrenSettings);
                $childProduct = array();
                foreach ($configs as $key => $val)
                {
                    $data = explode('|', $val);
                    $childProduct[$key] = array(
                        'product_id' => $data[0],
                        'quantity' => $data[1]
                    );
                }
                $billingModuleArray[$mainProductListKey]['children'] = $childProduct;
            }

            if (!empty($this->params['products'][$key]['trialProductId']))
            {
                $billingModuleArray[$mainProductListKey]['trial'] = array(
                    'product_id' => $this->params['products'][$key]['trialProductId'],
                    'price' => $this->params['products'][$key]['trialProductPrice'],
                    'quantity' => $this->params['products'][$key]['trialProductQuantity'],
                );
                unset($billingModuleArray[$this->params['products'][$key]['productId']]['price']);
            }

            if (!empty($this->params['products'][$key]['trialChildrenSettings']))
            {
                $trialChildrenSettings = $this->params['products'][$key]['trialChildrenSettings'];
                $configs = preg_split("/\\r\,|\\r|\,/", $trialChildrenSettings);
                foreach ($configs as $key => $val)
                {
                    $data = explode('|', $val);
                    $billingModuleArray[$this->params['products'][$key]['productId']]['trial']['children'][$key] = array(
                        'product_id' => $data[0],
                        'quantity' => $data[1]
                    );
                }
            }
        }
        return $billingModuleArray;
    }

    private function prepareCardDetails()
    {

        $cardDetails = array(
            'creditCardType' => $this->accessor->getValue($this->params, '[cardType]'),
            'creditCardNumber' => $this->accessor->getValue($this->params, '[cardNumber]'),
            'CVV' => $this->accessor->getValue($this->params, '[cvv]'),
        );

        $cardExpiryMonth = $this->accessor->getValue($this->params, '[cardExpiryMonth]');
        $cardExpiryYear = $this->accessor->getValue($this->params, '[cardExpiryYear]');

        $cardDetails['expirationDate'] = sprintf('%s%s', $cardExpiryMonth, $cardExpiryYear);

        $this->removeCardDetails();

        $this->params = array_replace($this->params, array_filter($cardDetails));
    }

    private function removeCardDetails()
    {
        unset(
                $this->params['cardType'], $this->params['cardNumber'], $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'], $this->params['cvv']
        );
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        $affiliateParams = array_filter(array(
            'AFID' => $this->accessor->getValue($affiliates, '[afId]'),
            'AFFID' => $this->accessor->getValue($affiliates, '[affId]'),
            'SID' => $this->accessor->getValue($affiliates, '[sId]'),
            'C1' => $this->accessor->getValue($affiliates, '[c1]'),
            'C2' => $this->accessor->getValue($affiliates, '[c2]'),
            'C3' => $this->accessor->getValue($affiliates, '[c3]'),
            'C4' => $this->accessor->getValue($affiliates, '[c4]'),
            'C5' => $this->accessor->getValue($affiliates, '[c5]'),
            'AID' => $this->accessor->getValue($affiliates, '[aId]'),
            'OPT' => $this->accessor->getValue($affiliates, '[opt]'),
            'click_id' => $this->accessor->getValue($affiliates, '[clickId]'),
        ));

        unset($this->params['affiliates']);
        $this->params = array_replace($this->params, $affiliateParams);
    }

    public static function isValidCredential($credential)
    {
        if (is_array($credential) && !empty($credential))
        {

            $url = rtrim($credential['endpoint'], '/') . '/api/v1/validate_credentials';
            $headers = array(
                'Content-Type' => 'application/json',
                'authorization' => 'Basic ' . base64_encode($credential['username'] . ':' . $credential['password'])
            );

            $response = Http::post($url, array(), $headers, array(CURLOPT_HTTP_VERSION  => CURL_HTTP_VERSION_1_1));
            $response = json_decode($response, true);

            if (!empty($response['response_code']) && $response['response_code'] == 100)
            {
                return true;
            }
            else if ($response == 200)
            {
                return false;
            }
        }
    }

    public function updatePaypalData()
    {
        $this->params['shippingAddress1'] = $this->accessor->getValue($this->params['currentOrderData'], '[line1]');
        $this->params['shippingCity'] = $this->accessor->getValue($this->params['currentOrderData'], '[city]');
        $this->params['shippingZip'] = $this->accessor->getValue($this->params['currentOrderData'], '[postal_code]');
        $this->params['shippingState'] = $this->accessor->getValue($this->params['currentOrderData'], '[state]');
        $this->params['shippingCountry'] = $this->accessor->getValue($this->params['currentOrderData'], '[country_code]');
        $this->params['creditCardType'] = 'offline';
    }

    public function offlinePayment()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareProductDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        $this->params['creditCardType'] = 'offline';
        $prevCrmMethod = !empty($this->params['prevCrmMethod']) ? $this->params['prevCrmMethod'] : 'offlinePayment';

        $this->params['method'] = $this->transactionMethods[$prevCrmMethod];
        $this->params['tranType'] = 'Sale';

        if (!empty($this->params['currentOrderData']))
        {
            $this->params['customNotes'] = 'Paypal Reference ID: ' . $this->params['currentOrderData']['id'];
            $this->updatePaypalData();
        }

        unset($this->params['creditCardNumber'], $this->params['CVV'], $this->params['expirationDate']);

        if ($this->makeHttpRequest() === false)
        {
            return;
        }
        CrmPayload::set('order_id', array(
            $this->response['order_id'] => array(
                'payment_received' => 1
            )
        ));

        $orderData = array(
            'success' => true,
            'orderId' => $this->response['order_id'],
            'customerId' => $this->response['customerId'],
            'gatewayId' => $this->response['gateway_id'],
        );
        $this->orderUpdate();
        if (!CrmResponse::get('success'))
        {
            return;
        }
        CrmResponse::replace($orderData);
    }

    public function threeDredirect()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->transactionMethods['threeDredirect'];

        $url = $this->getUrl($this->params['method']);
        $params = array_filter($this->params);
        $response = Http::post($url, http_build_query($params));

        if (empty($response))
        {
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'html' => $response
        ));
    }

    public function getVariantSpecificProductPrice($param)
    {
        if(empty($param || !is_array($param))){
            return false;
        }
        $this->beforeAnyCrmClassMethodCall();
        $this->params['product_id'] = $param;
        $this->params['method'] = $this->transactionMethods['getVariantSpecificProductPrice'];
        if ($this->makeHttpRequest() === false)
        {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
            'response' => $this->response,
        ));
    }

    public function consentReceive($param)
    {
        $responseStatus = array();
        try
        {
            if (empty($param))
            {
                return;
            }
            $basicAuth = array(
                CURLOPT_USERPWD => $this->username . ":" . $this->password,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_FAILONERROR => false,
                CURLOPT_HTTP_VERSION  => CURL_HTTP_VERSION_1_1
            );
            $url = rtrim($this->endpoint, '/') . '/api/v2/orders/' . $param . '/consent';
            $response = Http::post($url, array(), array(), $basicAuth);
            if (!empty($response['curlError']))
            {
                $responseStatus = array(
                    'success' => false,
                    'errors' => array(
                        'curlError' => $response['errorMessage'],
                    ),
                );
            }
            $response = json_decode($response, true);

            if (!empty($response['status']) && strtolower($response['status']) == "success")
            {
                $responseStatus = array(
                    'success' => true,
                );
            }
            else
            {
                $responseStatus = array(
                    'success' => false,
                    'errors' => array(
                        'msg' => $response['message'],
                    ),
                );
            }
        }
        catch (\Exception $ex)
        {
            $responseStatus = array(
                'success' => false,
                'errors' => array(
                    'msg' => $ex->getMessage(),
                ),
            );
        }

        CrmResponse::replace($responseStatus);
    }

}
