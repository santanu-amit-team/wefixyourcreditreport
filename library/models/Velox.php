<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;

class Velox extends BaseCrm
{

    const GENERAL_ERROR = "Your request couldn't be processed, Please try again.";

    private $countryStateCodes       = array();
    private $headers                 = array();
    private $paymentMethodIdMappings = array(
        'visa' => 1, 'master' => 2, 'amex' => 3, 'discover' => 4,
    );

    private $methodSlugMapping = array(
        'prospect'             => 'Prospects/Add/',
        'newOrderWithProspect' => 'Orders/NewSale/',
        'newOrder'             => 'Orders/FullSale/',
        'importUpsell'         => 'Orders/UpSale/',
        'newOrderCardOnFile'   => 'Orders/FullSale/',
        'orderView'            => 'Order/Query/',
    );

    public function __construct($crmId)
    {
        parent::__construct($crmId);
        $this->endpoint = rtrim(
            Registry::system('systemConstants.VELOX_API_BASE_URL'), '/'
        );
        $this->headers = array(
            'Authorization' => sprintf('Basic %s', base64_encode(
                sprintf('%s:%s', $this->username, $this->password)
            )),
            'Content-Type'  => 'application/json',
        );
        $this->countryStateCodes = json_decode(
            file_get_contents(
                STORAGE_DIR . DS . 'country-state-codes.json'
            ), true
        );
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();

        $this->setTermsAndConditionsParams();

        if (CrmPayload::has('campaignId')) {
            CrmPayload::set('OfferId', CrmPayload::get('campaignId'));
            CrmPayload::remove('campaignId');
        }
        if (CrmPayload::has('userIsAt')) {
            CrmPayload::set('PageUrl', CrmPayload::get('userIsAt'));
            CrmPayload::remove('userIsAt');
        }
        if (CrmPayload::has('ipAddress')) {
            CrmPayload::set('IpAddress', CrmPayload::get('ipAddress'));
            CrmPayload::remove('ipAddress');
        }

        if (
            !in_array(CrmPayload::get('meta.crmMethod'), array(
                'prospect', 'newOrderWithProspect',
                'newOrder', 'importUpsell', 'newOrderCardOnFile',
            ))
        ) {
            $this->endpoint = str_replace('/crm.', '/api.', str_replace(
                '/api', '', $this->endpoint
            ));
        }

    }

    private function setTermsAndConditionsParams()
    {
        if (
            in_array(CrmPayload::get('meta.crmMethod'), array(
                'newOrderWithProspect', 'newOrder', 'importUpsell', 'newOrderCardOnFile',
            ))
        ) {
            if (!CrmPayload::has('AgreeToTelemarketing')) {
                CrmPayload::set('AgreeToTelemarketing', 0);
            }

            if (!CrmPayload::has('AgreeToTerms')) {
                CrmPayload::set('AgreeToTerms', 0);
            }
            return;
        }

        if (CrmPayload::get('meta.crmMethod') === 'prospect') {
            if (!CrmPayload::has('AgreeToTelemarketing')) {
                CrmPayload::set('AgreeToTelemarketing', 2);
            }
            if (!CrmPayload::has('AgreeToTerms')) {
                CrmPayload::set('AgreeToTerms', 2);
            }
        }
    }

    protected function prospect()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareAffiliates();

        $this->params['slug'] = $this->methodSlugMapping['prospect'];

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        if (empty($this->response['ProspectId'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('responseError' => self::GENERAL_ERROR),
            ));
            return;
        }

        CrmResponse::replace(array(
            'success' => true, 'prospectId' => $this->response['ProspectId'],
        ));
    }

    protected function newOrderWithProspect()
    {

        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        $billingCycleProfileIds = $this->getBillingCycleProfileIds();

        $this->params['BillingCycleProfileID'] = array_shift($billingCycleProfileIds);

        $this->params['slug']       = $this->methodSlugMapping['newOrderWithProspect'];
        $this->params['ProspectID'] = $this->params['prospectId'];
        unset($this->params['prospectId']);

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        $this->performAfterOrderPlacementTasks($billingCycleProfileIds);

    }

    protected function newOrder()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->prepareShippingDetails();
        $this->prepareBillingDetails();
        $this->prepareCardDetails();
        $this->prepareAffiliates();

        $billingCycleProfileIds = $this->getBillingCycleProfileIds();

        $this->params['BillingCycleProfileID'] = array_shift($billingCycleProfileIds);

        unset($this->params['orderId'], $this->params['parentOrderId']);

        $this->params['slug'] = $this->methodSlugMapping['newOrderCardOnFile'];

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        $this->performAfterOrderPlacementTasks($billingCycleProfileIds);
    }

    protected function newOrderCardOnFile()
    {
        $this->newOrder();
    }

    protected function importUpsell()
    {
        $this->params = array_replace($this->params, CrmPayload::all());

        $this->params['OrderID'] = $this->params['previousOrderId'];
        unset($this->params['previousOrderId']);

        $billingCycleProfileIds = $this->getBillingCycleProfileIds();

        $this->params['BillingCycleProfileID'] = array_shift($billingCycleProfileIds);

        $this->unsetImportUpsellUnwantedFields();
        $this->params['slug'] = $this->methodSlugMapping['importUpsell'];

        if ($this->makeHttpRequest() === false) {
            return false;
        }

        $this->performAfterOrderPlacementTasks($billingCycleProfileIds);
    }

    private function performAfterOrderPlacementTasks(&$billingCycleProfileIds)
    {
        if (empty($this->response['OrderID'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('responseError' => self::GENERAL_ERROR),
            ));
            return;
        }

        $mainStepMetaData = CrmPayload::get('meta.mainStepData');

        CrmResponse::replace(array(
            'success'    => true, 'orderId' => $this->response['OrderID'],
            'customerId' => $mainStepMetaData['prospectId'],
        ));

        if (empty($billingCycleProfileIds)) {
            return;
        }

        $this->processExtraBillingCycleProfileIds($billingCycleProfileIds);
    }

    private function processExtraBillingCycleProfileIds(&$billingCycleProfileIds)
    {
        $crmResponse = CrmResponse::all();

        $this->unsetImportUpsellUnwantedFields();
        $this->params['slug']    = $this->methodSlugMapping['importUpsell'];
        $this->params['OrderID'] = $this->response['OrderID'];

        $otherOrderMeta = array();
        foreach ($billingCycleProfileIds as $billingCycleProfileId) {
            $this->params['BillingCycleProfileID'] = $billingCycleProfileId;

            if ($this->makeHttpRequest() === false) {
                $otherOrderIds[$billingCycleProfileId] = array(
                    'errors' => CrmResponse::get('errors'),
                );
                continue;
            }

            if (empty($this->response['OrderID'])) {
                $otherOrderIds[$billingCycleProfileId] = array(
                    'errors' => array('responseError' => self::GENERAL_ERROR),
                );
                continue;
            }

            $otherOrderIds[$billingCycleProfileId] = array(
                'orderId' => $this->response['OrderID'],
            );
        }

        $crmResponse['otherOrderIds'] = $otherOrderIds;

        CrmResponse::replace($crmResponse);
    }

    public function orderView()
    {

        $this->beforeAnyCrmClassMethodCall();

        $orderIds       = CrmPayload::get('orderIds');
        $uniqueOrderIds = array_unique($orderIds);
        $result         = $data         = array();

        foreach ($uniqueOrderIds as $orderId) {
            $this->params['slug']    = $this->methodSlugMapping['orderView'];
            $this->params['OrderId'] = $orderId;
            if ($this->makeHttpRequest() === false) {
                $data[$orderId] = 'Not Found';
                continue;
            }
            if (!empty($this->response['Orders']) && is_array($this->response['Orders'])) {
                foreach ($this->response['Orders'] as $orderInfo) {
                    if ($orderInfo['OrderID'] == $orderId) {
                        $data[$orderId] = $orderInfo;
                        break;
                    }
                }
            }
            if (empty($data[$orderId])) {
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

    private function beforeHttpRequest()
    {

        unset(
            $this->params['forceGatewayId'], $this->params['preserveGateway'],
            $this->params['parentOrderId'], $this->params['userIsAt'],
            $this->params['userAgent']
        );

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

        // print_r($params); exit;

        $response = Http::post(
            $url, json_encode($params), $this->headers, array(
                CURLOPT_FAILONERROR => false,
            )
        );

        // print_r($response); exit;

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
        $isResponseError = empty($response) ? true : false;

        $this->response = json_decode($response, true);

        if (!empty($this->response['Status'])) {
            return true;
        }

        if ($isResponseError || empty($this->response) || !is_array($this->response)) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('responseError' => self::GENERAL_ERROR),
            ));
            return false;
        }

        $modelKeys = array_values(
            preg_grep('/^model\..+$/', array_keys($this->response))
        );
        if (empty($modelKeys) && !empty($this->response['StatusMessage'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'crmError' => preg_replace(
                        '/^\\d+\\s*-\\s*/', '', $this->response['StatusMessage']
                    ),
                ),
            ));
            return false;
        } elseif (empty($modelKeys)) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => self::GENERAL_ERROR),
            ));
            return false;
        }

        if (!empty($this->response[$modelKeys[0]]['Errors'][0]['ErrorMessage'])) {
            $messgae = $this->response[$modelKeys[0]]['Errors'][0]['ErrorMessage'];
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    str_replace('model.', '', $modelKeys[0]) => $messgae,
                ),
            ));
            return false;
        }

        CrmResponse::replace(array(
            'success' => false,
            'errors'  => array('crmError' => self::GENERAL_ERROR),
        ));
        return false;

    }

    private function getUrl($slug)
    {
        return sprintf('%s/%s', $this->endpoint, $slug);
    }

    private function prepareShippingDetails()
    {
        $countryCode = $this->accessor->getValue($this->params, '[shippingCountry]');
        $stateCode   = $this->accessor->getValue($this->params, '[shippingState]');

        $shippingDetails = array(
            'FirstName' => $this->accessor->getValue($this->params, '[firstName]'),
            'LastName'  => $this->accessor->getValue($this->params, '[lastName]'),
            'Email'     => $this->accessor->getValue($this->params, '[email]'),
            'Phone'     => $this->accessor->getValue($this->params, '[phone]'),
            'Address1'  => $this->accessor->getValue($this->params, '[shippingAddress1]'),
            'Address2'  => $this->accessor->getValue($this->params, '[shippingAddress2]'),
            'Zip'       => $this->accessor->getValue($this->params, '[shippingZip]'),
            'City'      => $this->accessor->getValue($this->params, '[shippingCity]'),
            'StateID'   => $this->getStateId($countryCode, $stateCode),
            'CountryID' => $this->getCountryId($countryCode),
        );

        unset(
            $this->params['firstName'], $this->params['lastName'],
            $this->params['email'], $this->params['phone'],
            $this->params['shippingAddress1'], $this->params['shippingAddress2'],
            $this->params['shippingZip'], $this->params['shippingCity'],
            $this->params['shippingState'], $this->params['shippingCountry']
        );

        $this->params = array_replace($this->params, array_filter($shippingDetails));
    }

    private function prepareBillingDetails()
    {
        $billingDetails = array();

        if (strtolower($this->params['billingSameAsShipping']) !== 'yes') {
            $countryCode    = $this->accessor->getValue($this->params, '[billingCountry]');
            $stateCode      = $this->accessor->getValue($this->params, '[billingState]');
            $billingDetails = array(
                'BillingFirstName' => $this->accessor->getValue($this->params, '[billingFirstName]'),
                'BillingLastName'  => $this->accessor->getValue($this->params, '[billingLastName]'),
                'BillingAddress1'  => $this->accessor->getValue($this->params, '[billingAddress1]'),
                'BillingAddress2'  => $this->accessor->getValue($this->params, '[billingAddress2]'),
                'BillingZip'       => $this->accessor->getValue($this->params, '[billingZip]'),
                'BillingCity'      => $this->accessor->getValue($this->params, '[billingCity]'),
                'BillingStateID'   => $this->getStateId($countryCode, $stateCode),
                'BillingCountryID' => $this->getCountryId($countryCode),
            );
        }

        unset(
            $this->params['billingSameAsShipping'],
            $this->params['billingFirstName'], $this->params['billingLastName'],
            $this->params['billingAddress1'], $this->params['billingAddress2'],
            $this->params['billingCity'], $this->params['billingState'],
            $this->params['billingZip'], $this->params['billingCountry']
        );

        $this->params = array_replace($this->params, array_filter($billingDetails));
    }

    private function getCountryId($countryCode)
    {
        if (!array_key_exists($countryCode, $this->countryStateCodes)) {
            return 0;
        }
        return $this->countryStateCodes[$countryCode]['code'];

    }

    private function getStateId($countryCode, $stateCode)
    {
        if (!array_key_exists($countryCode, $this->countryStateCodes)) {
            return 0;
        }
        if (!array_key_exists(
            $stateCode, $this->countryStateCodes[$countryCode]['stateCodes']
        )) {
            return 0;
        }
        return $this->countryStateCodes[$countryCode]['stateCodes'][$stateCode];
    }

    private function getBillingCycleProfileIds()
    {
        $billingCycleProfileIds = array();

        foreach ($this->params['products'] as $product) {
            array_push($billingCycleProfileIds, $product['productId']);
        }

        unset($this->params['products']);

        return $billingCycleProfileIds;
    }

    private function prepareCardDetails()
    {
        $cardDetails = array(
            'PaymentMethodID' => $this->paymentMethodIdMappings[
                $this->accessor->getValue($this->params, '[cardType]')
            ],
            'CardNumber'      => $this->accessor->getValue($this->params, '[cardNumber]'),
            'ExpiryMonth'     => $this->accessor->getValue($this->params, '[cardExpiryMonth]'),
            'ExpiryYear'      => $this->accessor->getValue($this->params, '[cardExpiryYear]'),
            'Cvv'             => $this->accessor->getValue($this->params, '[cvv]'),
        );

        $cardDetails['ExpiryMonth'] = (int) $cardDetails['ExpiryMonth'];
        $cardDetails['ExpiryYear']  = (int) sprintf('20%s', $cardDetails['ExpiryYear']);

        unset(
            $this->params['cardNumber'], $this->params['cardType'], $this->params['cvv'],
            $this->params['cardExpiryMonth'], $this->params['cardExpiryYear']
        );

        $this->params = array_replace($this->params, array_filter($cardDetails));
    }

    private function prepareAffiliates()
    {
        $affiliates = $this->params['affiliates'];

        $affiliateParams = array_filter(array(
            'AffiliateID'    => $this->accessor->getValue($affiliates, '[affId]'),
            'SubAffiliateID' => $this->accessor->getValue($affiliates, '[sId]'),
            'C1'             => $this->accessor->getValue($affiliates, '[c1]'),
            'C2'             => $this->accessor->getValue($affiliates, '[c2]'),
            'C3'             => $this->accessor->getValue($affiliates, '[c3]'),
            'C3'             => $this->accessor->getValue($affiliates, '[c4]'),
            'C4'             => $this->accessor->getValue($affiliates, '[c5]'),
        ));

        unset($this->params['affiliates']);

        $this->params = array_replace($this->params, $affiliateParams);
    }

    private function unsetImportUpsellUnwantedFields()
    {
        $fields = array(
            'firstName', 'lastName', 'email', 'phone', 'address1', 'zip',
            'city', 'state', 'country', 'prospect', 'cvv', 'cardNumber',
            'expiryMonth', 'expiryYear', 'customerId',
        );

        $paramsKeys = array_keys($this->params);

        foreach ($fields as $field) {
            $unwantedKeys = preg_grep('/' . $field . '/i', $paramsKeys);
            if (empty($unwantedKeys)) {
                continue;
            }
            foreach ($unwantedKeys as $unwantedKey) {
                unset($paramsKeys[$unwantedKey], $this->params[$unwantedKey]);
            }
        }
    }

    public static function isValidCredential($credential)
    {
        $endpoint = rtrim(
            Registry::system('systemConstants.VELOX_API_BASE_URL'), '/'
        );

        $endpoint = sprintf(
            '%s/Lead/Query/', str_replace(
                '/crm.', '/api.', str_replace('/api', '', $endpoint)
            )
        );

        $headers = array(
            'Authorization' => sprintf('Basic %s', base64_encode(
                sprintf(
                    '%s:%s', $credential['username'], $credential['password']
                )
            )),
            'Content-Type'  => 'application/json',
        );

        $params = array('ProspectID' => -9999);

        $response = Http::post(
            $endpoint, json_encode($params), $headers, array(
                CURLOPT_FAILONERROR => false,
            )
        );

        if (empty($response)) {
            return false;
        }

        $response = json_decode($response, true);

        if (!empty($response['$id']) && !empty($response['StatusCode'])) {
            return true;
        }

        return false;

    }

}
