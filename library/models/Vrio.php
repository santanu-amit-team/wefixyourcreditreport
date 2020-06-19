<?php

    namespace Application\Model;

    use Application\Http;
    use Application\CrmPayload;
    use Application\CrmResponse;
    use Application\Registry;
    use Application\Response;
    use Application\Config;
    use Exception;

    /**
     * Class to handle Vrio CRM Request
     * API docs link (https://onlineshopping.sublytics.com/api_docs)
     */
    class Vrio extends BaseCrm {

        /**
         * Method Mapping with Vrio CRM Slugs
         */
        private $methodSlugMapping = array(
            'prospect'             => 'order/doAdd',
            'newOrderWithProspect' => 'order/doProcess',
            'newOrder'             => 'order/doAddProcess',
            'addOffer'             => 'order_offer/doAdd',
            'newOrderCardOnFile' => 'order/doAddProcessOrder',
            'importUpsell' => 'order/doAddProcessOrder',
            'orderView'            => 'order/view/',
            'getMerchant' => 'route/getMerchant/',
            'preAuthorization' => 'order/preauth/',
            'process3ds' => 'transaction/doProcess3ds/'
        );

        /**
         * Validation fields
         */
        protected $requiredPayload = array(
            'prospect' => array(
                'user_id', 'user_password', 'connection_id', 'campaign_id',
                'shipping_profile_id', 'email', 'phone', 'bill_fname', 'bill_lname',
                'bill_country', 'bill_address1', 'bill_city', 'bill_state', 'bill_zipcode',
                'ship_fname', 'ship_lname', 'ship_country', 'ship_address1', 'ship_city',
                'ship_state', 'ship_zipcode', 'tracking1', 'tracking2',
                'tracking3', 'tracking4', 'tracking5', 'tracking6', 
            ),
            'newOrderWithProspect' => array(
                'user_id','user_password','order_id','payment_method_id','card_type_id','card_number','card_cvv','card_exp_month','card_exp_year','bill_fname','bill_lname','bill_address1','bill_address2','bill_city','bill_state','bill_zipcode','bill_country','shipping_same','customers_address_shipping_id','connection_id','payment_method_id','campaign_id','offers','customer_id','shipping_profile_id','shipping_same','ship_fname','ship_lname','ship_address1','ship_address2','ship_city','ship_state','ship_country','ship_zipcode','order_notes','ip_address','session_id','tracking1','tracking2','tracking3','tracking4','tracking5','tracking6','slug','merchant_id','email','phone','items','order_eci','order_xid','order_cavv','3ds_redirect_url','order_cardholder_auth','order_3ds_auth_cycle'
            ),
            'newOrder' => array(
                'user_id', 'user_password', 'connection_id', 'payment_method_id' ,'campaign_id', 'offers', 'customer_id', 'shipping_profile_id', 
                'shipping_same', 'ship_fname', 'ship_lname', 'ship_address1', 'ship_address2', 'ship_city', 'ship_state',
                'ship_country', 'ship_zipcode', 'order_notes', 'ip_address', 'session_id', 'tracking1', 'tracking2',
                'tracking3', 'tracking4', 'tracking5', 'tracking6', 'card_type_id', 'card_number', 'card_cvv',
                'card_exp_month', 'card_exp_year', 'slug', 'merchant_id', 'email', 'phone',
                'bill_fname', 'bill_lname', 'bill_organization', 'bill_country', 'bill_address1', 'bill_address2',
                'bill_city', 'bill_state', 'bill_zipcode', 'items', 'order_eci', 'order_xid', 'order_cavv', '3ds_redirect_url', 'order_cardholder_auth', 'order_3ds_auth_cycle'
            ),
            'newOrderCardOnFile' => array(
                'user_id', 'user_password', 'campaign_id', 'order_id', 'offers',
                'order_id', 'card_cvv', 'items', 'merchant_id', 'payment_method_id', 'order_eci', 'order_xid', 'order_cavv', '3ds_redirect_url', 'order_cardholder_auth', 'order_3ds_auth_cycle', 'shipping_profile_id'
            ),
            'orderView' => array(
                'user_id', 'user_password', 'order_id', 'with'
            ),
            'getMerchant' => array('user_id', 'user_password', 'offer_id', 'customer_id', 'card_type_id', 'campaign_id', 'payment_method_id'),
            'process3ds' => array('user_id', 'user_password', 'transaction_id', 'transaction_3ds_token')
        );

        /**
         * Crm type mapping
         */
        private $cardTypeID = array(
            'visa' => 2,
            'master' => 1,
            'discover' => 3,
            'amex' => 4
        );

        /**
         * Use for Request parameters
         */
        protected $params = array();

        public function __construct($crmId) {
            parent::__construct($crmId);
        }

        /**
         * Implement abstract methods
         */
        protected function beforeAnyCrmClassMethodCall() {

            $this->params = $this->response = array();
            $this->params['user_id'] = $this->username;
            $this->params['user_password'] = $this->password;
        }

        /**
         * Implement abstract methods
         * Creating a prospect in VRIO CRM.
         * @param Null
         * @return [Boolean, Void]
         */
        protected function prospect() {
            $this->beforeAnyCrmClassMethodCall();
            $this->params = array_replace($this->params, CrmPayload::all());
            

            $this->prepareShippingDetails();
            $this->prepareAffiliates();
            $this->injectEssentialParameters();
            

            /**
             * Set the slug for api call
             */
            $this->params['slug'] = $this->methodSlugMapping['prospect'];
            
            unset($this->params['userAgent']);

            if ($this->makeHttpRequest() === false) {
                return false;
            }

            CrmResponse::replace(array(
                'success'    => true,
                'prospectId' => $this->response['data']['order']['id'],
            ));
        }

        /**
         * Implement abstract methods, Creating a order using previously created prospect.
         * @param Null,
         * @return [Boolean, Void]
         */
        protected function newOrderWithProspect() {

            $this->beforeAnyCrmClassMethodCall();
            $this->params = array_replace($this->params, CrmPayload::all());

            /**
             * Add offer to order in crm.
             */
            $addOfferResponse = $this->addOfferToOrder();
            if(!$addOfferResponse) {
                return false;
            }

            /**
             * Set all the parameters
             */
            $this->prepareBillingDetails();
            $this->prepareCardDetails();
            $this->prepareAffiliates();
            $this->injectEssentialParameters();

            $this->params['order_id'] = $this->params['prospectId'];
            $this->params['slug'] = $this->methodSlugMapping['newOrderWithProspect'];
            
            if ($this->makeHttpRequest() === false) {
                return false;
            }

            if((bool) $this->response['success']) {
                CrmResponse::replace(array(
                    'success' => true,
                    'orderId' => $this->response['data']['transaction']['order_id'],
                    'customerId' => $this->response['data']['transaction']['order']['customer_id'],
                    'gatewayId' => $this->response['data']['transaction']['merchant_id'],
                    'response_code' => $this->response['data']['transaction']['response_code'],
                    'transaction_id' => $this->response['data']['transaction']['transaction_id'],
                    '3ds_post_url' => empty($this->response['data']['transaction']['3ds_post_url']) ? null :
                            $this->response['data']['transaction']['3ds_post_url']
                ));
            }
            else {
                return false;
            }
        }

        /**
         * Implement abstract methods, create a new order
         * @param null,
         * @return void
         */
        protected function newOrder() {

            $this->beforeAnyCrmClassMethodCall();
            $this->params = array_replace($this->params, CrmPayload::all());

            $this->prepareShippingDetails();
            $this->prepareBillingDetails();
            $this->prepareProductDetails();
            $this->prepareCardDetails();
            $this->prepareAffiliates();
            $this->injectEssentialParameters();

            $this->params['slug'] = $this->methodSlugMapping['newOrder'];

            unset($this->params['userAgent']);

            if ($this->makeHttpRequest() === false)
            {
                return false;
            }

            if((bool) $this->response['success']) {
                CrmResponse::replace(array(
                    'success' => true,
                    'orderId' => $this->response['data']['transaction']['order_id'],
                    'customerId' => $this->response['data']['transaction']['order']['customer_id'],
                    'gatewayId' => $this->response['data']['transaction']['merchant_id'],
                    'response_code' => $this->response['data']['transaction']['response_code'],
                    'transaction_id' => $this->response['data']['transaction']['transaction_id'],
                    '3ds_post_url' => empty($this->response['data']['transaction']['3ds_post_url']) ? null :
                            $this->response['data']['transaction']['3ds_post_url']
                ));
            }
            else {
                return false;
            }
        }

        /**
         * Implement abstract methods
         */
        protected function newOrderCardOnFile() {
            $this->beforeAnyCrmClassMethodCall();

            $this->params = array_replace($this->params, CrmPayload::all());
            $this->injectEssentialParameters();
            $this->prepareProductDetails();
            $this->params['slug'] = $this->methodSlugMapping['newOrderCardOnFile'];
            $this->params['order_id'] = $this->params['previousOrderId'];
            $this->params['card_cvv'] = $this->params['cvv'];
            $this->params['payment_method_id'] = 1;

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

        /**
         * Validate CRM Credentials
         * @param Array
         * @return Boolean
         */
        public static function isValidCredential($credential) {

            if(is_array($credential) && !empty($credential)) {

                $params = array(
                    'user_id' => $credential['username'],
                    'user_password' => $credential['password']
                );

                /**
                 * Prepare URL
                 */
                $url = sprintf('%s/campaign/index', trim($credential['endpoint']));

                /**
                 * Set Headers 
                 * content-type: application/json
                 */
                $headers = array(
                    "content-type" => "application/json"
                );

                $response = Http::post($url, json_encode($params), $headers);

                if(is_array($response) && array_key_exists("httpCode", $response) && ['httpCode'] == 404) return false;
                $response = json_decode($response, true);
                if($response['success']) return true;
            }

            return false;
        }

        /**
         * Preparing Shipping Details
         * @param null 
         * @return void
         */
        private function prepareShippingDetails() {
            
            $shippingDetails = array(
                'ship_fname' => $this->accessor->getValue($this->params, '[firstName]'),
                'ship_lname' => $this->accessor->getValue($this->params, '[lastName]'),
                'email' => $this->accessor->getValue($this->params, '[email]'),
                'phone' => $this->accessor->getValue($this->params, '[phone]'),
                'ship_address1' => $this->accessor->getValue($this->params, '[shippingAddress1]'),
                'ship_address2' => $this->accessor->getValue($this->params, '[shippingAddress2]'),
                'ship_city' => $this->accessor->getValue($this->params, '[shippingCity]'),
                'ship_state' => $this->accessor->getValue($this->params, '[shippingState]'),
                'ship_zipcode' => $this->accessor->getValue($this->params, '[shippingZip]'),
                'ship_country' => $this->accessor->getValue($this->params, '[shippingCountry]'),
                "bill_fname" => $this->accessor->getValue($this->params, '[firstName]'),
                "bill_lname" => $this->accessor->getValue($this->params, '[lastName]'),
                "bill_country" => $this->accessor->getValue($this->params, '[shippingCountry]'),
                "bill_address1" => $this->accessor->getValue($this->params, '[shippingAddress1]'),
                "bill_address2" => $this->accessor->getValue($this->params, '[shippingAddress2]'),
                "bill_city" => $this->accessor->getValue($this->params, '[shippingCity]'),
                "bill_state" => $this->accessor->getValue($this->params, '[shippingState]'),
                "bill_zipcode" => $this->accessor->getValue($this->params, '[shippingZip]')
            );

            $this->params = array_replace($this->params, array_filter($shippingDetails));
        }

        /**
         * Preparing Affiliate Mapping
         * @param null
         * @return void
         */
        private function prepareAffiliates() {

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

        /**
         * Make a HTTP request
         * @param Null
         * @return Boolean
         */
        private function makeHttpRequest() {
            
            $callingMethod = $this->params['meta.crmMethod'];
            $this->beforeHttpRequest();
            $url = $this->getUrl($this->params['slug']);
            $this->filterPayload($this->requiredPayload[$callingMethod]);

            unset($this->params['slug']);

            $params = array_filter($this->params);

            $headers = array('Content-Type' => 'application/json');

            $response = Http::post($url, json_encode($params), $headers, array(CURLOPT_FAILONERROR => false));

            if (!empty($response['curlError'])) {
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

        /**
         * Prepare Http Response
         * @param Array
         * @return Boolean
         */
        private function prepareResponse($response) {
            
            $this->response = json_decode($response, true);
            if ($this->response['success']) {
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

        /**
         * Before HTTP request Perform the Settings task
         * @param null
         * @return void
         */
        private function beforeHttpRequest() {

            if (!empty($this->params['forceGatewayId'])) {
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

        /**
         * Prepare URL for API call
         * @param String $slug
         * @return String
         */
        private function getUrl($slug) {
            return sprintf('%s/%s', rtrim($this->endpoint, '/'), $slug);
        }

        /**
         * Filter the payload
         * @param Array
         * @return void
         */
        private function filterPayload($requiredPayloadKey = array()) {
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

        /**
         * Inject essential parameter to the $this->params
         * @param null
         * @return void
         */
        private function injectEssentialParameters() {

            $config = new Configuration($this->accessor->getValue($this->params, '[meta.configId]'));
            $campaignids = $config->getCampaignIds();

            if(empty($campaignids)) {
                throw new Exception("Campaign id not found");
            }
            $campaignDetails = $campaign = Config::campaigns(sprintf('%d', current($campaignids)));
            $this->params['connection_id'] = trim($campaignDetails['connection_id']);
            $this->params['campaign_id'] = $this->accessor->getValue($this->params, '[campaignId]');

            $this->params['ip_address'] = $this->accessor->getValue($this->params, '[ipAddress]');
            $this->params['session_id'] = uniqid();
        }

        /**
         * Prepare Billing Details
         * @param null
         * @return void
         */
        private function prepareBillingDetails() {

            $billingDetails = array();
            if (!empty($this->params['meta.mainStepData']['customerAddressId'])) {
                $this->params['customers_address_billing_id'] = $this->params['meta.mainStepData']['customerAddressId'];
            }

            if (strtolower($this->params['billingSameAsShipping']) !== 'yes') {
                $billingDetails = array(
                    'bill_fname' => $this->accessor->getValue($this->params, '[billingFirstName]'),
                    'bill_lname' => $this->accessor->getValue($this->params, '[billingLastName]'),
                    'bill_address1' => $this->accessor->getValue($this->params, '[billingAddress1]'),
                    'bill_address2' => $this->accessor->getValue($this->params, '[billingAddress1]'),
                    'bill_city' => $this->accessor->getValue($this->params, '[billingCity]'),
                    'bill_state' => $this->accessor->getValue($this->params, '[billingState]'),
                    'bill_zipcode' => $this->accessor->getValue($this->params, '[billingZip]'),
                    'bill_country' => $this->accessor->getValue($this->params, '[billingCountry]'),
                    'shipping_same' => false,
                );
            } 
            else {
                if (!empty($this->params['meta.mainStepData']['customerAddressId'])) {
                    $this->params['customers_address_shipping_id'] = $this->params['meta.mainStepData']['customerAddressId'];
                }
                $this->params['shipping_same'] = true;
            }

            $this->params = array_replace($this->params, array_filter($billingDetails));
        }

        /**
         * Prepare card Details
         * @param null
         * @return void
         */
        private function prepareCardDetails() {
            $cardDetails = array(
                'payment_method_id' => 1,
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

        /**
         * Prepare product details
         * @param null,
         * @return void
         */
        private function prepareProductDetails() {

            $products = $this->accessor->getValue($this->params, '[products]');

            if (!is_array($products) || empty($products)) {
                return;
            }

            $offers = array();
            foreach($this->params['products'] as $product) {
                array_push($offers, array(
                    "id" =>  !empty($product['orderOfferId']) ? $product['orderOfferId'] : uniqid(),
                    "offer_id" => $product['vrioOfferId'],
                    "order_offer_quantity" => $product['offerQuantity'],
                    "discount_code" => !empty($this->params['discount_code']) ? $this->params['discount_code'] : '',
                    "order_offer_price" => $product['offerPrice'],
                    "order_offer_shipping" => $product['shippingPrice'],
                    "order_offer_upsell" => !empty($this->params['meta.isUpsellStep']) ? true : false
                ));
            }

            $allOffers['offers'] = $offers;


            if (empty($products[0]['vrioOfferId'])) {

                $this->params['offer_id'] = $this->params['products'][0]['vrioOfferId'];
                if (!empty($products[0]['campaignId']))
                {
                    $this->params['campaign_id'] = $products[0]['campaignId'];
                }
                return;
            }

            $this->params['shipping_profile_id'] = $products[0]['shippingId'];
            $this->params['campaign_id'] = $products[0]['campaignId'];
            
            unset($this->params['products']);
            $this->params = array_replace($this->params, array_filter($allOffers));
        }

        /**
         * Add offer to the order.
         * @param Null
         * @return Boolean
         * 
         */
        public function addOfferToOrder() {

            if( empty($this->params['prospectId']) || empty($this->params['products'])) {
                return;
            }

            $offers = array();
            foreach($this->params['products'] as $product) {
                array_push($offers, array(
                    "id" =>  !empty($product['orderOfferId']) ? $product['orderOfferId'] : uniqid(),
                    "offer_id" => $product['vrioOfferId'],
                    "order_offer_quantity" => $product['offerQuantity'],
                    "discount_code" => !empty($this->params['discount_code']) ? $this->params['discount_code'] : '',
                    "order_offer_price" => $product['offerPrice'],
                    "order_offer_shipping" => $product['shippingPrice'],
                    "order_offer_upsell" => !empty($this->params['meta.isUpsellStep']) ? true : false
                ));
            }

            $postData = array(
                "user_id" =>  trim($this->params['user_id']),
                "user_password" => trim($this->params['user_password']),
                "order_id" => trim($this->params['prospectId']),
                'offers' => $offers,
                "order_notes" => !empty($this->params['notes']) ? $this->params['notes'] : ''
            );

            /** 
             * Prepare the $url 
             */
            $url = $this->getUrl($this->methodSlugMapping['addOffer']);
            $headers = array('Content-Type' => 'application/json');
            $response = Http::post($url, json_encode($postData), $headers, array(CURLOPT_FAILONERROR => false));

            if (!empty($response['curlError'])) {
                CrmResponse::replace(array(
                    'success' => false,
                    'errors' => array(
                        'crmError' => $response['message'],
                    ),
                ));
                return false;
            }

            $response = json_decode($response, true);

            if(!$response['success']) {
                CrmResponse::replace(array(
                    'success' => false,
                    'errors' => array(
                        'crmError' => $response['message'],
                    ),
                ));
                return false;
            }

            return true;
        }
        
        /**
         * Order Views function
         * @param null
         * @return Array
         */
        public function orderView() {
            $this->beforeAnyCrmClassMethodCall();

            $orderIds = CrmPayload::get('orderIds');
            $uniqueOrderIds = array_unique($orderIds);
            $result = $data = array();
            foreach ($uniqueOrderIds as $orderId)
            {
                $this->params['slug'] = $this->methodSlugMapping['orderView'] . $orderId;
                $this->params['order_id'] = $orderId;
                $this->params['meta.crmMethod'] = 'orderView';
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

        /**
         * Make http request alternative;
         * @param null
         * @return Boolean
         */
        private function makeHttpGetRequest() {
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

        /**
         * Get marchant call 
         * @param null
         * @return Boolean
         */
        public function getMerchant() {

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
            $this->params['campaign_id'] = CrmPayload::get('campaign_id');
            $this->params['payment_method_id'] = CrmPayload::get('payment_method_id');
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

        /**
         * Manage PreAuthorization
         */
        public function preAuthorization() {

            CrmResponse::replace(array(
                'success' => true,
                'orderId' => strtoupper(uniqid()),
                'customerId' => strtoupper(uniqid()),
            ));
        }

        /**
         * Process 3ds
         */
        public function process3ds() {
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
    }