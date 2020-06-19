<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;

class Limelight extends BaseCrm
{

    private $transactionMethods = array(
        'prospect'             => 'NewProspect',
        'newOrderWithProspect' => 'NewOrderWithProspect',
        'newOrder'             => 'NewOrder',
        'newOrderCardOnFile'   => 'NewOrderCardOnFile',
        'preAuthorization'     => 'authorize_payment',
        'offlinePayment'       => 'NewOrder',
        'threeDredirect'       => 'three_d_redirect',
    );

    private $memberShipMethods = array(
        'orderUpdate'          => 'order_update',
        'updateOrderRecurring' => 'order_update_recurring',
        'getAlternateProvider' => 'get_alternative_provider',
        'orderUpdateRecurring' => 'order_update_recurring',
        'validateCoupon'       => 'coupon_validate',
        'orderView'            => 'order_view',
    );

    private $errorMessages = array(
        '342' => 'Invalid Email Address',
        '901' => 'Invalid return URL',
        '902' => 'Invalid cancel URL',
        '903' => 'Error retrieving alternative provider data',
        '904' => 'Campaign does not support an alternative payment provider',
    );
    
    private $isThreeDSecureFlow = false;

    public function __construct($crmId)
    {
        parent::__construct($crmId);
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();

        $this->params['username'] = $this->username;
        $this->params['password'] = $this->password;
    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $shippingKeys = preg_grep('/^shipping/', array_keys($this->params));
        foreach ($shippingKeys as $shippingKey) {
            $this->params[
                lcfirst(str_replace('shipping', '', $shippingKey))
            ] = $this->params[$shippingKey];
            unset($this->params[$shippingKey]);
        }

        $this->prepareAffiliates();

        $this->params['method'] = $this->transactionMethods['prospect'];

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
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

        $this->params['method']   = $this->transactionMethods['newOrderWithProspect'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderId'],
            'customerId' => $this->response['customerId'],
            'gatewayId'  => $this->response['gatewayId'],
            'isThreeDSecureFlow' => $this->isThreeDSecureFlow
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

        $this->params['method']   = $this->transactionMethods['newOrder'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderId'],
            'customerId' => $this->response['customerId'],
            'gatewayId'  => $this->response['gatewayId'],
            'isThreeDSecureFlow' => $this->isThreeDSecureFlow
        ));
    }
    
    protected function newOrderCardOnFileWithCvv()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['CVV'] = $this->params['cvv'];
        
        $this->prepareProductDetails();
        $this->removeCardDetails();
        $this->prepareAffiliates();

        $this->params['method']   = $this->transactionMethods['newOrderCardOnFile'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderId'],
            'customerId' => $this->response['customerId'],
            'gatewayId'  => $this->response['gatewayId'],
        ));
    }

    protected function newOrderCardOnFile()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        
        $this->prepareProductDetails();
        $this->removeCardDetails();
        $this->prepareAffiliates();

        $this->params['method']   = $this->transactionMethods['newOrderCardOnFile'];
        $this->params['tranType'] = 'Sale';

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['orderId'],
            'customerId' => $this->response['customerId'],
            'gatewayId'  => $this->response['gatewayId'],
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
        
        if(empty($this->params['productId'])) {
            $productkey = array_keys($this->params['products']);
            $this->params['productId'] = $productkey[0];
        }

        $this->params['method'] = $this->transactionMethods['preAuthorization'];
        $this->params['auth_amount'] = $this->params['authorizationAmount'];
        if(empty($this->params['auth_amount'])) {
            $this->params['auth_amount'] = '0.00';
        }
        $this->params['tranType']      = 'Sale';
        $this->params['save_customer'] = 1;
        $this->params['void_flag'] = 1;
        unset($this->params['sessionId'], $this->params['authorizationAmount']);
        
        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'orderId'    => $this->response['tempCustomerId'],
            'customerId' => $this->response['tempCustomerId'],
            'gatewayId'  => $this->response['gatewayId'],
        ));

    }

    public function orderUpdate()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params           = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['orderUpdate'];

        if ($this->makeHttpRequest() === false) {
            return;
        }
        
        CrmResponse::replace(array(
            'success' => true,
        ));

    }

    public function orderUpdateRecurring()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params           = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['orderUpdateRecurring'];

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success' => true,
        ));

    }

    public function validateCoupon()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params           = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['validateCoupon'];

        $this->params['campaign_id'] = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['promo_code']  = $this->accessor->getValue($this->params, '[couponCode]');
        $this->params['shipping_id'] = $this->accessor->getValue(
            $this->params, '[products][0][shippingId]'
        );

        $this->params['product_ids'] = array();
        foreach ($this->params['products'] as $product) {
            $productId = $this->accessor->getValue($product, '[productId]');
            array_push($this->params['product_ids'], $productId);
            $this->params[
                sprintf('product_qty_%s', $productId)
            ] = $this->accessor->getValue($product, '[productQuantity]');
        }
        $this->params['product_ids'] = implode(',', $this->params['product_ids']);

        unset(
            $this->params['campaignId'], $this->params['couponCode'],
            $this->params['products'], $this->params['preserveGateway']
        );

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'      => true,
            'couponAmount' => $this->accessor->getValue($this->response, '[coupon_amount]'),
        ));

    }

    public function getAlternateProvider()
    {

        $this->beforeAnyCrmClassMethodCall();

        $this->params           = array_replace($this->params, CrmPayload::all());
        $this->params['method'] = $this->memberShipMethods['getAlternateProvider'];

        $products   = $this->accessor->getValue($this->params, '[products]');
        $productIds = $productPrices = $productQuantities = array();
        $orderTotal = 0.00;
        foreach ($products as $product) {
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
                (float) $this->accessor->getValue($product, '[productQuantity]')
                 * (float) $this->accessor->getValue($product, '[productPrice]')
                 + (float) $this->accessor->getValue($product, '[shippingPrice]')
            );
        }

        $this->calculateProductDiscount($orderTotal, $productPrices, $productIds);

        $this->params['shipping_id']    = $this->accessor->getValue($products, '[0][shippingId]');
        $this->params['campaign_id']   = $this->accessor->getValue($this->params, '[campaignId]');
        $this->params['amount']        = $orderTotal;
        $this->params['products']      = implode(',', $productIds);
        $this->params['product_price'] = implode(',', $productPrices);
        $this->params['product_qty']   = implode(',', $productQuantities);
        $this->params['alt_pay_type']  = $this->accessor->getValue($this->params, '[cardType]');
        $this->params['return_url']    = $this->accessor->getValue($this->params, '[returnUrl]');
        $this->params['cancel_url']    = $this->accessor->getValue($this->params, '[cancelUrl]');
        
        unset(
            $this->params['cardType'],
            $this->params['cancelUrl'], $this->params['returnUrl']
        );

        if ($this->makeHttpRequest() === false) {
            return;
        }

        CrmResponse::replace(array(
            'success'  => true,
            'redirect' => $this->response['redirect_url'],
        ));

    }

    private function calculateProductDiscount(&$orderTotal, &$productPrices, $productIds)
    {
    	if(!empty($this->params['discount_amount']))
        {
            $orderTotal = $orderTotal - $this->params['discount_amount_total'];
            if($this->params['discount_type'] == 'each')
            {          	
            	foreach ($productPrices as $key => $value) {
	                $productPrices[$key] = $productPrices[$key] - $this->params['discount_amount'][$productIds[$key]];
	            }
            }
            else
            {
            	$productTotal = array_sum($productPrices);
	            $discountProductTotal = $productTotal - $this->params['discount_amount'];
	            foreach ($productPrices as $key => $value) {
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

        $orderIds       = CrmPayload::get('orderIds');
        $uniqueOrderIds = array_values(array_unique($orderIds));
        $result         = $data         = array();

        $this->params['order_id'] = implode(',', $uniqueOrderIds);

        if ($this->makeHttpRequest() === false) {
            return;
        }

        if (empty($this->response['total_orders'])) {
            $data[$uniqueOrderIds[0]] = $this->response;
        } else {
            $data = json_decode($this->response['data'], true);
        }

        foreach ($orderIds as $key => $orderId) {
            if (!empty($data[$orderId]) && is_array($data[$orderId])) {
                $result[$key] = $data[$orderId];
            } else {
                $result[$key] = 'Not found!';
            }
        }

        CrmResponse::replace(array(
            'success' => true,
            'result'  => $result,
        ));
    }

    private function beforeHttpRequest()
    {

        if (!empty($this->params['userIsAt']) || !empty($this->params['userAgent'])) {
            $this->params['notes'] = sprintf(
                '%s | %s', $this->params['userIsAt'], $this->params['userAgent']
            );
        }
        
        if(!empty($this->params['customNotes']))
        {
            $this->params['notes'] .= '| '. $this->params['customNotes'];
        }

        if (!empty($this->params['preserveGateway'])) {
            $this->params['preserve_force_gateway'] = 1;
        }

        if (!empty($this->params['parentOrderId'])) {
            $this->params['master_order_id'] = $this->accessor->getValue(
                $this->params, '[parentOrderId]'
            );
        }

        if (!CrmPayload::has('three_d_redirect_url')
            &&
            CrmPayload::has('meta.crmMethod')
            &&
            CrmPayload::get('meta.crmMethod') != 'prospect') {

            $this->params['three_d_redirect_url'] = true;
        }

        if (!empty($this->params['couponCode'])) {
            $this->params['promoCode'] = $this->params['couponCode'];
        }

        unset(
            $this->params['preserveGateway'], $this->params['parentOrderId'],
            $this->params['userIsAt'], $this->params['userAgent'],
            $this->params['couponCode']
        );
        
        $evaluateForceGatewayArray = $this->params['forceGatewayId'];
        if(!empty($evaluateForceGatewayArray) && is_array($evaluateForceGatewayArray))
        {
            unset($this->params['forceGatewayId']);
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

        $url    = $this->getUrl($this->params['method']);
        $params = array_filter($this->params);

        $response = Http::post($url, http_build_query($params));
        
        if (!empty($response['curlError'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'curlError' => $response['errorMessage'],
                ),
            ));
            return false;
        }

        parse_str($response, $this->response);

        $this->filterResponse();

        if (!empty($this->response['errorFound']) || (int) $this->response['responseCode'] !== 100) {
            
            if((int) $this->response['responseCode'] == 101)
            {
                $this->isThreeDSecureFlow = true;
                return true;
            }
            CrmResponse::replace(array(
                'success' => false,
                'declineOrderId' => !empty($this->response['orderId']) ? $this->response['orderId'] : '',
                'errors'  => array(
                    'crmError' => $this->response['errorMessage'],
                ),
            ));
            if (
                !empty($this->response['responseCode']) &&
                preg_match("/Prepaid.+Not Accepted/i", $this->response['errorMessage'])
            ) {
                CrmResponse::set('isPrepaidDecline', true);
            }
            return false;
        }
        return true;
    }

    private function getUrl($limelightMethod)
    {
        if (in_array($limelightMethod, $this->memberShipMethods)) {
            return $this->endpoint . '/admin/membership.php';
        } else {
            return $this->endpoint . '/admin/transact.php';
        }

    }

    private function filterResponse()
    {
        $keyMaps = array(
            'error_message'    => 'errorMessage',
            'declineReason'    => 'errorMessage',
            'decline_reason'   => 'errorMessage',
            'error_code'       => 'errorCode',
            'response_code'    => 'responseCode',
            'gateway_id'       => 'gatewayId',
            'temp_customer_id' => 'tempCustomerId',
            'responseCode'     => 'responseCode',
            'response_message' => 'errorMessage',
        );
        foreach ($keyMaps as $alias => $key) {
            if (!isset($this->response[$key]) && isset($this->response[$alias])) {
                $this->response[$key] = $this->response[$alias];
                unset($this->response[$alias]);
            }
        }
        if (
            empty($this->response['errorMessage']) &&
            array_key_exists($this->response['responseCode'], $this->errorMessages)
        ) {
            $this->response['errorMessage'] = $this->errorMessages[$this->response['responseCode']];
        } elseif (empty($this->response['errorMessage'])) {
            $this->response['errorMessage'] = 'Something went wrong.';
        }

    }

    private function prepareShippingDetails()
    {
        $shippingCountry = $this->accessor->getValue($this->params, '[shippingCountry]');
        if (!in_array($shippingCountry, array('US', 'CA'))) {
            $this->params['shippingState'] = sprintf(
                '%s-%s', $shippingCountry,
                $this->accessor->getValue($this->params, '[shippingState]')
            );
        }
    }

    private function prepareBillingDetails()
    {
        $billingDetails = array('billingSameAsShipping' => 'NO');
        if (strtolower($this->params['billingSameAsShipping']) === 'yes') {
            $billingDetails = array(
                'billingSameAsShipping' => 'YES',
                'billingFirstName'      => $this->accessor->getValue($this->params, '[firstName]'),
                'billingLastName'       => $this->accessor->getValue($this->params, '[lastName]'),
                'billingAddress1'       => $this->accessor->getValue($this->params, '[shippingAddress1]'),
                'billingAddress2'       => $this->accessor->getValue($this->params, '[shippingAddress2]'),
                'billingZip'            => $this->accessor->getValue($this->params, '[shippingZip]'),
                'billingCity'           => $this->accessor->getValue($this->params, '[shippingCity]'),
                'billingState'          => $this->accessor->getValue($this->params, '[shippingState]'),
                'billingCountry'        => $this->accessor->getValue($this->params, '[shippingCountry]'),
            );
        }

        $this->params = array_replace($this->params, array_filter($billingDetails));

        $billingCountry = $this->accessor->getValue($this->params, '[billingCountry]');
        $billingState   = $this->accessor->getValue($this->params, '[billingState]');
        if (
            !in_array($billingCountry, array('US', 'CA')) &&
            strpos($billingState, $billingCountry) === false
        ) {
            $this->params['billingState'] = sprintf('%s-%s', $billingCountry, $billingState);
        }
    }

    private function prepareProductDetails()
    {
        $products = $this->accessor->getValue($this->params, '[products]');

        if (!is_array($products) || empty($products)) {
            return;
        }

        $upsellProductIds = $result = array();
        foreach ($products as $key => $product) {
            $productId = (int) $this->accessor->getValue($product, '[productId]');
            if (empty($productId)) {
                continue;
            }

            array_push($upsellProductIds, $productId);
            $result[
                sprintf('dynamic_product_price_%s', $productId)
            ] = $this->accessor->getValue($product, '[productPrice]');

            $result[
                sprintf('product_qty_%s', $productId)
            ] = $this->accessor->getValue($product, '[productQuantity]');
            
            $result['product_step'][$productId] = $key + 1;//$this->params['meta.stepId'];
        }

        $product              = array_shift($products);
        $result['productId']  = $this->accessor->getValue($product, '[productId]');
        //$result['shippingId'] = $this->accessor->getValue($product, '[shippingId]');
        $result['shippingId'] = empty($this->params['shippingId']) ? 
                $this->accessor->getValue($product, '[shippingId]') : $this->params['shippingId'];
        
        if (count($upsellProductIds) > 1) {
            array_shift($upsellProductIds);
            $result['upsellProductIds'] = implode(',', $upsellProductIds);
            $result['upsellCount']      = 1;
        }
        
        if(!empty($this->params['products'][0]['enableBillingModule'])) {
            $billingModuleArray = $this->createBillingModule($result);
        }
        
        unset($this->params['products']);
        
        if(!empty($billingModuleArray)) {
            $this->params['products'] = $billingModuleArray;
            $this->params['shippingId'] = $result['shippingId'];
        }
        else{
            $this->params = array_replace($this->params, array_filter($result));
        }
    }
    
    private function createBillingModule($result) 
    {
        $billingModuleArray = array();
        foreach ($this->params['products'] as $key => $value)
        {
            $billingModuleArray[$this->params['products'][$key]['productId']] = array(
                'offer_id' => $this->params['products'][$key]['offerId'],
                'billing_model_id' => $this->params['products'][$key]['billingModelId'],
                'quantity' => $result[sprintf('product_qty_%s', $this->params['products'][$key]['productId'])],
                'price' => $result[sprintf('dynamic_product_price_%s', $this->params['products'][$key]['productId'])],
                'step_num' => $key + 1,
            );
        
            if(!empty($this->params['products'][$key]['childrenSettings'])) {
                $childrenSettings = $this->params['products'][$key]['childrenSettings'];
                $configs = preg_split("/\\r\,|\\r|\,/", $childrenSettings);
                foreach($configs as $key => $val) {
                    $data = explode('|', $val);
                    $billingModuleArray[$this->params['products'][$key]['productId']]['children'][$key] = array(
                        'product_id' => $data[0],
                        'quantity' => $data[1]
                    );
                }
            }
            
            if(!empty($this->params['products'][$key]['trialProductId'])) {
                $billingModuleArray[$this->params['products'][$key]['productId']]['trial'] = array(
                    'product_id' => $this->params['products'][$key]['trialProductId'],
                    'price' => $this->params['products'][$key]['trialProductPrice'],
                    'quantity' => $this->params['products'][$key]['trialProductQuantity'],
                );
                if(empty($billingModuleArray[$this->params['products'][$key]['productId']]['trial']['quantity'])) {
                	unset($billingModuleArray[$this->params['products'][$key]['productId']]['trial']['quantity']);
                }
                unset($billingModuleArray[$this->params['products'][$key]['productId']]['price']);
            }
                        
            if(!empty($this->params['products'][$key]['trialChildrenSettings'])) {
                $trialChildrenSettings = $this->params['products'][$key]['trialChildrenSettings'];
                $configs = preg_split("/\\r\,|\\r|\,/", $trialChildrenSettings);
                foreach($configs as $key => $val) {
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
            'creditCardType'   => $this->accessor->getValue($this->params, '[cardType]'),
            'creditCardNumber' => $this->accessor->getValue($this->params, '[cardNumber]'),
            'CVV'              => $this->accessor->getValue($this->params, '[cvv]'),
        );

        $cardExpiryMonth = $this->accessor->getValue($this->params, '[cardExpiryMonth]');
        $cardExpiryYear  = $this->accessor->getValue($this->params, '[cardExpiryYear]');

        $cardDetails['expirationDate'] = sprintf('%s%s', $cardExpiryMonth, $cardExpiryYear);
        
        if(!empty($this->params['cardType']) && $this->params['cardType'] == 'COD')
        {
            $cardDetails['creditCardType'] = 'offline';
            unset($cardDetails['creditCardNumber'], $cardDetails['CVV'], $cardDetails['expirationDate']);
        }

        $this->removeCardDetails();

        $this->params = array_replace($this->params, array_filter($cardDetails));

    }

    private function removeCardDetails()
    {
        unset(
            $this->params['cardType'], $this->params['cardNumber'],
            $this->params['cardExpiryMonth'], $this->params['cardExpiryYear'],
            $this->params['cvv']
        );
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        $affiliateParams = array_filter(array(
            'AFID'     => $this->accessor->getValue($affiliates, '[afId]'),
            'AFFID'    => $this->accessor->getValue($affiliates, '[affId]'),
            'SID'      => $this->accessor->getValue($affiliates, '[sId]'),
            'C1'       => $this->accessor->getValue($affiliates, '[c1]'),
            'C2'       => $this->accessor->getValue($affiliates, '[c2]'),
            'C3'       => $this->accessor->getValue($affiliates, '[c3]'),
            'C4'       => $this->accessor->getValue($affiliates, '[c4]'),
            'C5'       => $this->accessor->getValue($affiliates, '[c5]'),
            'AID'      => $this->accessor->getValue($affiliates, '[aId]'),
            'OPT'      => $this->accessor->getValue($affiliates, '[opt]'),
            'click_id' => $this->accessor->getValue($affiliates, '[clickId]'),
        ));

        unset($this->params['affiliates']);
        $this->params = array_replace($this->params, $affiliateParams);
    }

    public static function isValidCredential($credential)
    {
        $methodName = 'validate_credentials';
        if (is_array($credential) && !empty($credential)) {
            $params = array(
                'username' => $credential['username'],
                'password' => $credential['password'],
                'method'   => $methodName,
            );
            $url      = rtrim($credential['endpoint'], '/') . '/admin/membership.php';
            $response = Http::post($url, $params);
            if ($response == 100) {
                return true;
            } else if ($response == 200) {
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
        $prevCrmMethod = !empty($this->params['prevCrmMethod'])? $this->params['prevCrmMethod'] : 'offlinePayment';
        
        $this->params['method']   = $this->transactionMethods[$prevCrmMethod];
        $this->params['tranType'] = 'Sale';
        
        if(!empty($this->params['currentOrderData']))
        {
            $this->params['customNotes'] = 'Paypal Reference ID: '.$this->params['currentOrderData']['id'];
            $this->updatePaypalData();
        }     
        
        unset($this->params['creditCardNumber'], $this->params['CVV'], $this->params['expirationDate']);

        if ($this->makeHttpRequest() === false) {
            return;
        }
        
        CrmPayload::set('actions', 'payment_received');
        CrmPayload::set('values', "1");
        CrmPayload::set('order_ids', $this->response['orderId']);
        $orderData = array(
            'success'    => true,
            'orderId'    => $this->response['orderId'],
            'customerId' => $this->response['customerId'],
            'gatewayId'  => $this->response['gatewayId'],
        );
        $this->orderUpdate();
        if(!CrmResponse::get('success')){
                return;
        }
        CrmResponse::replace($orderData);

    }
    
    public function threeDredirect()
    {
        $this->beforeAnyCrmClassMethodCall();
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->params['method']   = $this->transactionMethods['threeDredirect'];
       
        $url    = $this->getUrl($this->params['method']);
        $params = array_filter($this->params);
        $response = Http::post($url, http_build_query($params));

        if(empty($response))
        {
            return false;
        }
        
        CrmResponse::replace(array(
            'success'    => true,
            'html'    => $response
        ));
    }
    
    public function consentReceive($param)
    {
        $responseStatus = array();
        try
        {
            if (empty($param)) {
                return;
            }
            $basicAuth = array(
                CURLOPT_USERPWD     => $this->username . ":" . $this->password,
                CURLOPT_HTTPAUTH    => CURLAUTH_BASIC,
                CURLOPT_FAILONERROR => false,
            );
            $url      = rtrim($this->endpoint, '/') . '/api/v2/orders/' . $param . '/consent';
            $response = Http::post($url, array(), array(), $basicAuth);
            if (!empty($response['curlError'])) {
                $responseStatus = array(
                    'success' => false,
                    'errors'  => array(
                        'curlError' => $response['errorMessage'],
                    ),
                );
            }
            $response = json_decode($response, true);

            if (!empty($response['status']) && strtolower($response['status']) == "success") {
                $responseStatus = array(
                    'success' => true,
                );
            } else {
                $responseStatus = array(
                    'success' => false,
                    'errors'  => array(
                        'msg' => $response['message'],
                    ),
                );
            }
        } catch (\Exception $ex) {
            $responseStatus = array(
                'success' => false,
                'errors'  => array(
                    'msg' => $ex->getMessage(),
                ),
            );
        }

        CrmResponse::replace($responseStatus);
    }
    
    public static function getCrmCampaignData($param) 
    {
        $methodName = 'campaign_view';  
        if (is_array($param) && !empty($param)) {
            $params = array(
                'username' => $param['username'],
                'password' => $param['password'],
                'campaign_id' => empty($param['campaign_id']) ? 
                \Application\Request::form()->get('campaign_id') : $param['campaign_id'],
                'method' => $methodName,
            );
            $url = rtrim($param['endpoint'], '/') . '/admin/membership.php';
            $response = Http::post($url, $params);
            parse_str($response, $data);
          
            if ($data['response_code'] != 100)
                return array(
                    'success' => false,
                    'error_message' => 'Unable to import.'
                );
            
            $productDetails = array(
                    'product_id' => explode(",", $data['product_id']),
                    'product_name' => explode(",", $data['product_name']),
                    'shipping_id' => explode(",", $data['shipping_id']),
                    'shipping_initial_price' => explode(",", $data['shipping_initial_price']),
                    'shipping_recurring_price' => explode(",", $data['shipping_recurring_price'])
                );
           return self::getCRMProductDetails(array_merge($data,$param));
        }
    }
    
    private static function getCRMProductDetails($param) 
    {
        try{
            $params = array(
                'username' => $param['username'],
                'password' => $param['password'],
                'campaign_id' => \Application\Request::form()->get('campaign_id'),
                'method' => "product_index",
                'product_id' => $param['product_id']
            );
            
            $url = rtrim($param['endpoint'], '/') . '/admin/membership.php';
            $response = Http::post($url, $params);
            parse_str($response, $details);
            
            if(empty($details['response_code'])){
               throw new \Exception("Unable to import.");
            }
            if(strchr($details['response_code'],",")){
                    $details['response_code'] = explode(",", $details['response_code']);
                    if($details['response_code'][0] != 100)
                        throw new \Exception("Unable to import.");
                }
                $products = array();$shippingDetails = array();
                $productIDs = explode(",", $details['product_id']);
                $productNames = explode(",", $details['product_name']);
                $productPrices = explode(",", $details['product_price']);
                $shippingIds = explode(",", $param['shipping_id']);
                $shippingInitialPrices = explode(",", $param['shipping_initial_price']);
            for($i=0;$i<count($productIDs);$i++){
                $products[$i]['product_id'] = $productIDs[$i];
                $products[$i]['product_name'] = $productNames[$i];
                $products[$i]['product_price'] = $productPrices[$i];
                $products[$i]['retail_price'] = 0.00;
                $products[$i]['product_quantity'] = 1;
                $products[$i]['rebill_product_price'] = 0.00;
            }
            for($i=0;$i<count($shippingIds);$i++){
                $shippingDetails[$i]['shipping_id'] = $shippingIds[$i];
                $shippingDetails[$i]['shipping_price'] = $shippingInitialPrices[$i];
            }
            
            return array(
                'success' => true,
                'data' => array('productDetails' => $products,
                        'shippingDetails' => $shippingDetails
                    )
            );
                
        }catch(\Exception $ex){
            return array(
                    'success' => false,
                    'error_message' => $ex->getMessage()
                );
        }
            
    }

}
