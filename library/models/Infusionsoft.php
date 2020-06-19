<?php

namespace Application\Model;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use DOMDocument;

class Infusionsoft extends BaseCrm
{
    const CREATE_CONTACT_ERROR     = "Prospect couldn't be created. Please try again!";
    const CREATE_BLANK_ORDER_ERROR = "Order couldn't be created. Please try again!";
    const ADD_CARD_ERROR           = "Invalid Credit Card. Please try again!";
    const ADD_ITEMS_ERROR          = "Product couldn't be added. Please try again!";
    const CHARGE_INVOICE_ERROR     = "Order has been declined. Please try again!";
    const MERCHANT_ID_ERROR        = "Merchant Id not found. Please try again!";

    private $privateKeyParam    = null;
    private $methodName         = null;
    private $methodNameMappings = array(
        'createContact'         => 'ContactService.addWithDupCheck',
        'validateNewCreditCard' => 'InvoiceService.validateCreditCard',
        'locateExistingCard'    => 'InvoiceService.locateExistingCard',
        'createBlankOrder'      => 'InvoiceService.createBlankOrder',
        'createBlankOrder'      => 'InvoiceService.createBlankOrder',
        'addDataToTable'        => 'DataService.add',
        'addItemsToInvoice'     => 'InvoiceService.addOrderItem',
        'chargeInvoice'         => 'InvoiceService.chargeInvoice',
    );
    private $countryMapping = array();

    public function __construct($crmId)
    {
        parent::__construct($crmId);
        $countryStates = json_decode(
            file_get_contents(STORAGE_DIR . DS . 'countries-states.json'), true
        );
        foreach ($countryStates as $countryCode => $data) {
            if (empty($data['name'])) {
                continue;
            }
            $this->countryMapping[$countryCode] = $data['name'];
        }
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->privateKeyParam = array(
            array('tag' => 'string', 'value' => $this->password),
        );
        $shippingCountry = CrmPayload::get('shippingCountry');
        $billingCountry  = CrmPayload::get('shippingCountry');
        if (
            !empty($shippingCountry) &&
            !empty($this->countryMapping[$shippingCountry])
        ) {
            CrmPayload::set(
                'shippingCountry', $this->countryMapping[$shippingCountry]
            );
        }
        if (
            !empty($billingCountry) &&
            !empty($this->countryMapping[$billingCountry])
        ) {
            CrmPayload::set(
                'billingCountry', $this->countryMapping[$billingCountry]
            );
        }
    }

    protected function prospect()
    {
        $crmPayload     = CrmPayload::all();
        $contactDetails = $this->createContact($crmPayload);

        if ($contactDetails['success'] === false) {
            if (empty($contactDetails['errorMessage'])) {
                $contactDetails['errorMessage'] = self::PROSPECT_ERROR;
            }
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $contactDetails['errorMessage']),
            ));
            return;
        }

        CrmResponse::replace(array(
            'success'    => true,
            'prospectId' => $contactDetails['contactId'],
        ));
    }

    protected function newOrderWithProspect()
    {
        $crmPayload = CrmPayload::all();

        $orderDetails = $this->createBlankOrder($crmPayload);
        if (empty($orderDetails['success'])) {
            if (empty($orderDetails['errorMessage'])) {
                $orderDetails['errorMessage'] = self::CREATE_BLANK_ORDER_ERROR;
            }
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $orderDetails['errorMessage']),
            ));
            return;
        }

        $response = $this->addItemsToInvoice(
            $orderDetails['invoiceId'], $crmPayload
        );

        if (empty($response['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $response['errorMessage']),
            ));
            return;
        }

        $cardDetails = $this->addCreditCard($crmPayload);
        if (empty($cardDetails['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $cardDetails['errorMessage']),
            ));
            return;
        }

        $chargeInvoiceDetails = $this->chargeInvoice(
            $orderDetails['invoiceId'], $cardDetails['cardId'], $crmPayload
        );
        if (empty($chargeInvoiceDetails['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'crmError' => $chargeInvoiceDetails['errorMessage'],
                ),
            ));
            return;
        }

        CrmResponse::replace(array(
            'success'         => true,
            'orderId'         => $orderDetails['invoiceId'],
            'cardId'          => $cardDetails['cardId'],
            'referenceNumber' => $chargeInvoiceDetails['referenceNumber'],
        ));

        //$cardDetails = $this->validateNewCreditCard($crmPayload);
        //$cardId = $cardDetails['cardId'];
    }

    protected function newOrder()
    {
        $crmPayload = CrmPayload::all();

        $mainStepMetaData = $crmPayload['meta.mainStepData'];
        unset($crmPayload['meta.mainStepData']);
        $crmPayload['prospectId'] = $mainStepMetaData['prospectId'];

        $orderDetails = $this->createBlankOrder($crmPayload);
        if (empty($orderDetails['success'])) {
            if (empty($orderDetails['errorMessage'])) {
                $orderDetails['errorMessage'] = self::CREATE_BLANK_ORDER_ERROR;
            }
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $orderDetails['errorMessage']),
            ));
            return;
        }

        $response = $this->addItemsToInvoice(
            $orderDetails['invoiceId'], $crmPayload
        );

        if (empty($response['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $response['errorMessage']),
            ));
            return;
        }

        $cardDetails = $this->addCreditCard($crmPayload);
        if (empty($cardDetails['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $cardDetails['errorMessage']),
            ));
            return;
        }

        $chargeInvoiceDetails = $this->chargeInvoice(
            $orderDetails['invoiceId'], $cardDetails['cardId'], $crmPayload
        );
        if (empty($chargeInvoiceDetails['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'crmError' => $chargeInvoiceDetails['errorMessage'],
                ),
            ));
            return;
        }

        CrmResponse::replace(array(
            'success'         => true,
            'orderId'         => $orderDetails['invoiceId'],
            'cardId'          => $cardDetails['cardId'],
            'referenceNumber' => $chargeInvoiceDetails['referenceNumber'],
        ));
    }

    protected function newOrderCardOnFile()
    {
        $crmPayload = CrmPayload::all();

        $mainStepMetaData = $crmPayload['meta.mainStepData'];
        unset($crmPayload['meta.mainStepData']);
        $crmPayload['prospectId'] = $mainStepMetaData['prospectId'];

        $orderDetails = $this->createBlankOrder($crmPayload);
        if (empty($orderDetails['success'])) {
            if (empty($orderDetails['errorMessage'])) {
                $orderDetails['errorMessage'] = self::CREATE_BLANK_ORDER_ERROR;
            }
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $orderDetails['errorMessage']),
            ));
            return;
        }

        $response = $this->addItemsToInvoice($orderDetails['invoiceId'], $crmPayload);

        if (empty($response['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array('crmError' => $response['errorMessage']),
            ));
            return;
        }

        $chargeInvoiceDetails = $this->chargeInvoice(
            $orderDetails['invoiceId'], $mainStepMetaData['cardId'], $crmPayload
        );
        if (empty($chargeInvoiceDetails['success'])) {
            CrmResponse::replace(array(
                'success' => false,
                'errors'  => array(
                    'crmError' => $chargeInvoiceDetails['errorMessage'],
                ),
            ));
            return;
        }

        CrmResponse::replace(array(
            'success'         => true,
            'orderId'         => $orderDetails['invoiceId'],
            'cardId'          => $mainStepMetaData['cardId'],
            'referenceNumber' => $chargeInvoiceDetails['referenceNumber'],
        ));
    }

    private function createContact(&$params)
    {
        $contactDetails   = $this->getContactDetails($params);
        $this->methodName = $this->methodNameMappings['createContact'];
        $this->params     = array($this->privateKeyParam);

        array_push($this->params,
            array(
                array(
                    'tag'   => 'struct',
                    'value' => $this->getStructFormat($contactDetails),
                ),
            ), array(
                array('tag' => 'string', 'value' => 'Email'),
            )
        );

        $response = $this->makeRequest();

        if (empty($response['params']['param']['value']['i4'])) {
            if (empty($response['errorMessage'])) {
                $response['errorMessage'] = self::CREATE_CONTACT_ERROR;
            }
            return $response;
        }

        return array(
            'success'   => true,
            'contactId' => $response['params']['param']['value']['i4'],
        );
    }

    private function createBlankOrder(&$params)
    {
        $contactId        = $params['prospectId'];
        $this->methodName = $this->methodNameMappings['createBlankOrder'];
        $this->params     = array($this->privateKeyParam, array(
            array('tag' => 'string', 'value' => $params['prospectId']),
        ));

        $fullName = sprintf(
            '%s %s',
            ucfirst($this->accessor->getValue($params, '[billingFirstName]')),
            ucfirst($this->accessor->getValue($params, '[billingFLastName]'))
        );

        if (trim($fullName) === '') {
            $fullName = sprintf(
                '%s %s',
                ucfirst($params['firstName']), ucfirst($params['lastName'])
            );
        }

        $affiliates = $this->getAffiliatesDetails($params);

        array_push(
            $this->params, array(array('tag' => 'string', 'value' => $fullName)),
            array(array('tag' => 'dateTime.iso8601', 'value' => date('Ymd\TH:i:s'))),
            array(array('tag' => 'int', 'value' => $affiliates['leadAffiliateID'])),
            array(array('tag' => 'int', 'value' => $affiliates['saleAffiliateID']))
        );

        $response = $this->makeRequest();

        if (empty($response['params']['param']['value']['i4'])) {
            if (empty($response['errorMessage'])) {
                $response['errorMessage'] = self::CREATE_BLANK_ORDER_ERROR;
            }
            return $response;
        }

        $this->updateOrderData(
            $response['params']['param']['value']['i4'], $params
        );

        return array(
            'success'   => true,
            'invoiceId' => $response['params']['param']['value']['i4'],
        );

    }

    private function updateOrderData($invoiceId, &$params)
    {

        $shippingDetails                = $this->getShippingDetails($params);
        $shippingDetails['ShipStreet1'] = $shippingDetails['ShipAddress1'];
        $shippingDetails['ShipStreet2'] = $shippingDetails['ShipAddress2'];
        unset(
            $shippingDetails['ShipAddress1'], $shippingDetails['ShipAddress2']
        );

        $this->addDataToTable('Job', $shippingDetails);
    }

    private function addItemsToInvoice($invoiceId, &$params)
    {
        $this->methodName = $this->methodNameMappings['addItemsToInvoice'];

        $extraParams = $this->getAddItemsToInvoicesExtraParams($params);

        $productAdded = false;
        foreach ($params['products'] as $product) {
            $this->params = array($this->privateKeyParam);
            $productId    = $this->accessor->getValue($product, '[productId]');
            $productPrice = number_format((float) $this->accessor->getValue(
                $product, '[productPrice]'
            ), 2);
            $productQuantity = (int) $this->accessor->getValue(
                $product, '[productQuantity]'
            );

            $productType = 4;
//            if (!empty($product['shippingPrice'])) {
//                $productType  = 1;
//                $productPrice = number_format((float) $this->accessor->getValue(
//                    $product, '[shippingPrice]'
//                ), 2);
//            }

            array_push($this->params,
                array(array('tag' => 'int', 'value' => $invoiceId)),
                array(array('tag' => 'int', 'value' => $productId)),
                array(array('tag' => 'int', 'value' => $productType)),
                array(array('tag' => 'double', 'value' => $productPrice)),
                array(array('tag' => 'int', 'value' => $productQuantity)),
                array(array(
                    'tag' => 'string', 'value' => $extraParams['description'],
                )),
                array(array('tag' => 'string', 'value' => $extraParams['note']))
            );

            $response = $this->makeRequest();

            if (!empty($response['params']['param']['value']['boolean'])) {
                $productAdded = true;
            }

        }

        if ($productAdded) {
            return array('success' => true);
        }

        return array(
            'success' => false, 'errorMessage' => self::ADD_ITEMS_ERROR,
        );
    }

    private function chargeInvoice($invoiceId, $cardId, &$params)
    {
        $this->methodName = $this->methodNameMappings['chargeInvoice'];
        $this->params     = array($this->privateKeyParam);
        $notes            = sprintf(
            '%s | %s',
            $this->accessor->getValue($params, '[userAgent]'),
            $this->accessor->getValue($params, '[userIsAt]')
        );

        $merchantId = $this->accessor->getValue($params, '[forceGatewayId]');
        if (empty($merchantId)) {
            return array(
                'success' => false, 'errorMessage' => self::MERCHANT_ID_ERROR,
            );
        }

        array_push(
            $this->params,
            array(array('tag' => 'int', 'value' => $invoiceId)),
            array(array('tag' => 'string', 'value' => $notes)),
            array(array('tag' => 'int', 'value' => $cardId)),
            array(array('tag' => 'int', 'value' => $merchantId)),
            array(array('tag' => 'boolean', 'value' => 0))
        );

        $response = $this->makeRequest();

        if (
            empty($response['params']['param']['value']['struct']['member']) ||
            !is_array($response['params']['param']['value']['struct']['member'])
        ) {
            return array(
                'success' => false, 'errorMessage' => self::CHARGE_INVOICE_ERROR,
            );
        }

        $memberData = $response['params']['param']['value']['struct']['member'];

        $isSuccess       = false;
        $errorMessage    = self::CHARGE_INVOICE_ERROR;
        $referenceNumber = 0;
        foreach ($memberData as $key => $data) {
            if (!empty($data['name']) && $data['name'] === 'Successful') {
                if (!empty($data['value']['boolean'])) {
                    $isSuccess = true;
                }
            }
            if (!empty($data['name']) && $data['name'] === 'Message') {
                if (!empty($data['value'])) {
                    $errorMessage = $data['value'];
                }
            }
        }

        if (!$isSuccess) {
            return array('success' => false, 'errorMessage' => $errorMessage);
        }

        return array(
            'success' => true, 'referenceNumber' => $referenceNumber,
        );
    }

    private function addCreditCard(&$params)
    {
        $cardDetails              = $this->getCardDetails($params);
        $cardDetails['ContactId'] = array(
            'dtype' => 'int', 'value' => $params['prospectId'],
        );

        $response = $this->addDataToTable('CreditCard', $cardDetails);

        if (!empty($response['success']) && !empty($response['id'])) {
            $response['cardId'] = $response['id'];
            unset($response['id']);
            return $response;
        }

        if (empty($response['errorMessage'])) {
            $response['errorMessage'] = self::ADD_CARD_ERROR;
        }

        return $response;

    }

    private function validateNewCreditCard(&$params)
    {
        $cardDetails      = $this->getCardDetails($params);
        $this->methodName = $this->methodNameMappings['validateNewCreditCard'];
        $this->params     = array($this->privateKeyParam);

        array_push($this->params, array(
            array(
                'tag'   => 'struct',
                'value' => $this->getStructFormat($cardDetails),
            ),
        ));

        $response = $this->makeRequest();
        print_r($response);exit;

        return array('cardId' => 100);
    }

    private function locateExistingCard(&$params)
    {
        $lastFourDigit    = substr($params['cardNumber'], -4);
        $this->methodName = $this->methodNameMappings['locateExistingCard'];
        $this->params     = array($this->privateKeyParam);
        array_push($this->params, array(
            array(
                'tag'   => 'struct',
                'value' => $this->getStructFormat($cardDetails),
            ),
        ));
        array_push($this->params, array(
            array(
                'tag'   => 'struct',
                'value' => $this->getStructFormat($cardDetails),
            ),
        ));
        $this->makeRequest();

        return array('cardId' => 100);
    }

    private function addDataToTable($table, $data)
    {
        $this->methodName = $this->methodNameMappings['addDataToTable'];
        $this->params     = array($this->privateKeyParam);

        array_push($this->params,
            array(array('tag' => 'string', 'value' => $table)),
            array(array(
                'tag'   => 'struct',
                'value' => $this->getStructFormat($data),
            ))
        );

        $response = $this->makeRequest();

        if (empty($response['params']['param']['value']['i4'])) {
            $response['success'] = false;
            return $response;
        }

        return array(
            'success' => true,
            'id'      => $response['params']['param']['value']['i4'],
        );

    }

    private function makeRequest($debug = false)
    {
        $xmlDoc   = new DOMDocument('1.0', 'UTF-8');
        $rootNode = $xmlDoc->createElement('methodCall');
        $params   = array();

        foreach ($this->params as $param) {
            array_push($params, array(
                'tag' => 'param', 'value' => array(
                    array('tag' => 'value', 'value' => $param),
                ),
            ));
        }

        $this->arrayToXml($xmlDoc, $rootNode, array(
            array('tag' => 'methodName', 'value' => $this->methodName),
            array('tag' => 'params', 'value' => $params),
        ));
        $xmlDoc->appendChild($rootNode);

        $url         = sprintf('%s/api/xmlrpc', trim($this->endpoint, '/'));
        $postContent = $xmlDoc->saveXML();
        //print_r($postContent);
        if ($debug) {
            header('Content-Type: application/xml');
            print_r($postContent);exit;
        }

        $response = Http::post($url, $postContent, array(
            'Content-Type'   => 'application/xml',
            'Content-Length' => strlen($postContent),
        ));

        return $this->parseResponse($response);
    }

    private function parseResponse(&$xmlString)
    {
        $errorRessponse = array();
        $hasFaultCode   = preg_match(
            '/<fault>(.*)<\/fault>/', $xmlString, $errorRessponse
        );

        if ($hasFaultCode) {
            $response = array('success' => false);

            if (!empty($errorRessponse[1])) {
                $matches     = array();
                $errorString = $errorRessponse[1];
                preg_match(
                    '/faultCode.*<i4>(.*)<\/i4>/', $errorString, $matches
                );
                if (isset($matches[1])) {
                    $response['responseCode'] = $matches[1];
                }
                preg_match(
                    '/faultString.*<value>(.*)<\/value>/',
                    $errorString, $matches
                );
                if (isset($matches[1])) {
                    $response['errorMessage'] = preg_replace(
                        '/<(.*)>/', '', $matches[1]
                    );
                }
            }
            return $response;
        }

        $simpleXmlObj = @simplexml_load_string($xmlString);
        if ($simpleXmlObj === false) {
            return array('success' => false);
        }

        $response = @json_decode(@json_encode($simpleXmlObj), true);

        if (empty($response) && !is_array($response)) {
            return array('success' => false);
        }

        $response['success'] = true;

        return $response;
    }

    private function getAddItemsToInvoicesExtraParams(&$params)
    {
        $orderType = 'Main';
        if (!empty($params['meta.isSplitOrder'])) {
            $orderType = 'Split';
        }

        return array(
            'description' => sprintf(
                'Step: %d, Config Id: %d, %s Order',
                $this->accessor->getValue($params, '[meta.stepId]'),
                $this->accessor->getValue($params, '[meta.configId]'),
                $orderType
            ),
            'note'        => sprintf(
                '%s Product',
                empty($params['isUpsellStep']) ? 'Main' : 'Upsell'
            ),
        );
    }

    private function getCardDetails(&$params, $isAddCard = true)
    {
        $cardDetails = array();
        if ($isAddCard === true) {
            $cardDetails             = $this->getBillingDetails($params);
            $cardDetails['BillName'] = array(
                'dtype' => 'string',
                'value' => sprintf(
                    '%s %s',
                    $cardDetails['FirstName']['value'], $cardDetails['LastName']['value']
                ),
            );
        }

        $cardDetails = array_replace_recursive($cardDetails, array(
            'CardType'        => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, '[cardType]'),
            ),
            'ContactId'       => array(
                'dtype' => 'int',
                'value' => $this->accessor->getValue($params, '[billingContactId]'),
            ),
            'CardNumber'      => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, '[cardNumber]'),
            ),
            'ExpirationMonth' => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, '[cardExpiryMonth]'),
            ),
            'ExpirationYear'  => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, '[cardExpiryYear]'),
            ),
            'CVV2'            => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, '[cvv]'),
            ),
        ));

        $cardDetails = array_replace_recursive(
            $cardDetails, $this->getShippingDetails($params)
        );

        return array_filter($cardDetails, function ($data) {
            return !is_null($data['value']);
        });
    }

    private function getContactDetails(&$params, $isBilling = false)
    {
        $keys = array(
            'firstName', 'lastName', 'email', 'phone', 'address1',
            'address2', 'zip', 'city', 'state', 'country',
        );

        $contactDetails = array(
            'FirstName' => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[firstName]"),
            ),
            'LastName'  => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[lastName]"),
            ),
            'Email'     => array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[email]"),
            ),
        );

        // foreach ($keys as $key) {

        //     if ($isBilling && in_array($key, array('email', 'phone'))) {
        //         continue;
        //     }

        //     if (in_array($key, array('firstName', 'lastName'))) {
        //         if ($isBilling) {
        //             $key = sprintf('billing%s', ucfirst($key));
        //         }
        //     } else if(!in_array($key, array('email', 'phone'))) {
        //         $key = sprintf(
        //             '%s%s', $isBilling ? 'billing' : 'shipping', ucfirst($key)
        //         );
        //     }

        //     $contactDetails[ucfirst($key)] = array(
        //         'dtype' => 'string',
        //         'value' => $this->accessor->getValue($params, "[$key]"),
        //     );

        // }

        return array_filter($contactDetails, function ($data) {
            return !is_null($data['value']);
        });
    }

    private function getShippingDetails(&$params)
    {
        $requiredKeys = ['Address1', 'Address2', 'Zip', 'City', 'State', 'Country'];

        $shippingDetails['ShipFirstName'] = array(
            'dtype' => 'string',
            'value' => $this->accessor->getValue($params, "[firstName]"),
        );
        $shippingDetails['ShipLastName'] = array(
            'dtype' => 'string',
            'value' => $this->accessor->getValue($params, "[lastName]"),
        );

        foreach ($requiredKeys as $requiredKey) {
            $key = sprintf('shipping%s', $requiredKey);

            $shippingDetails[sprintf('Ship%s', $requiredKey)] = array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[$key]"),
            );
        }

        return $shippingDetails;

    }

    private function getBillingDetails(&$params)
    {
        $requiredKeys   = ['Address1', 'Address2', 'Zip', 'City', 'State', 'Country'];
        $billingDetails = array();
        $prefix         = 'billing';
        if (strtolower($params['billingSameAsShipping']) === 'yes') {
            $prefix                      = 'shipping';
            $billingDetails['FirstName'] = array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[firstName]"),
            );
            $billingDetails['LastName'] = array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[lastName]"),
            );
        } else {
            $billingDetails['FirstName'] = array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[billingFirstName]"),
            );
            $billingDetails['LastName'] = array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[billingLastName]"),
            );
        }

        foreach ($requiredKeys as $requiredKey) {
            $key = sprintf('%s%s', $prefix, $requiredKey);

            $billingDetails[sprintf('Bill%s', $requiredKey)] = array(
                'dtype' => 'string',
                'value' => $this->accessor->getValue($params, "[$key]"),
            );
        }

        return $billingDetails;

    }

    private function getAffiliatesDetails()
    {
        return array(
            'leadAffiliateID' => 0,
            'saleAffiliateID' => 0,
        );
    }

    private function getStructFormat(&$params)
    {
        $struct = array();

        foreach ($params as $key => $data) {
            array_push($struct, array(
                'tag' => 'member', 'value' => array(
                    array('tag' => 'name', 'value' => $key),
                    array('tag' => 'value', 'value' => array(
                        array(
                            'tag' => $data['dtype'], 'value' => $data['value'],
                        ),
                    )),
                ),
            ));
        }

        return $struct;
    }

    private function arrayToXml($xmlDoc, $parentElement, $params)
    {
        foreach ($params as $param) {
            $childElement = $xmlDoc->createElement($param['tag']);
            if (is_array($param['value'])) {
                $this->arrayToXml($xmlDoc, $childElement, $param['value']);
            } else {
                $childElement->appendChild(
                    $xmlDoc->createTextNode($param['value'])
                );
            }
            $parentElement->appendChild($childElement);
        }
    }

}
