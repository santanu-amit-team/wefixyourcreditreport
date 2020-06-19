<?php

namespace Extension\CbUtilityPackage;

class Crmstructure
{

    protected $structure;

    public function __construct()
    {
        $this->structure = array(
            'responsecrm' => array(
                'prospect' => array(
                    'ApiGuid' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'SiteID' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'FirstName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'LastName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Email' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Phone' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'City' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'State' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CountryISO' => array(
                        'type' => 'string',
                        'length' => 2
                    ),
                    'ZipCode' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'IpAddress' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'SubAffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'MDFs' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                ),
                'newOrderWithProspect' => array(
                    'ApiGuid' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CustomerID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'IpAddress' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingAddress' => array(
                        'FirstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'LastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'Address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'Address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'City' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'State' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CountryISO' => array(
                            'type' => 'string',
                            'length' => 2
                        ),
                        'ZipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'PaymentInformation' => array(
                        'AffiliateID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'SubAffiliateID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ExpMonth' => array(
                            'type' => 'string',
                            'length' => 2
                        ),
                        'ExpYear' => array(
                            'type' => 'string',
                            'length' => 4
                        ),
                        'CCNumber' => array(
                            'type' => 'string',
                            'length' => 16
                        ),
                        'NameOnCard' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CVV' => array(
                            'type' => 'string',
                            'length' => 3
                        ),
                        'XID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CAVV' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ECI' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CardholderAuth' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ProductGroups' => array(
                            'ProductGroupKey' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'CustomProducts' => array(
                                'ProductID' => array(
                                    'type' => 'integer',
                                    'length' => ''
                                ),
                                'Amount' => array(
                                    'type' => 'string',
                                    'length' => ''
                                ),
                                'Quantity' => array(
                                    'type' => 'integer',
                                    'length' => ''
                                ),
                            ),
                        ),
                    ),
                ),
                'newOrder' => array(
                    'ApiGuid' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CustomerID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'IpAddress' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingAddress' => array(
                        'FirstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'LastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'Address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'Address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'City' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'State' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CountryISO' => array(
                            'type' => 'string',
                            'length' => 2
                        ),
                        'ZipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'PaymentInformation' => array(
                        'AffiliateID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'SubAffiliateID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ExpMonth' => array(
                            'type' => 'string',
                            'length' => 2
                        ),
                        'ExpYear' => array(
                            'type' => 'string',
                            'length' => 4
                        ),
                        'CCNumber' => array(
                            'type' => 'string',
                            'length' => 16
                        ),
                        'NameOnCard' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CVV' => array(
                            'type' => 'string',
                            'length' => 3
                        ),
                        'XID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CAVV' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ECI' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CardholderAuth' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ProductGroups' => array(
                            'ProductGroupKey' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'CustomProducts' => array(
                                'ProductID' => array(
                                    'type' => 'integer',
                                    'length' => ''
                                ),
                                'Amount' => array(
                                    'type' => 'string',
                                    'length' => ''
                                ),
                                'Quantity' => array(
                                    'type' => 'integer',
                                    'length' => ''
                                ),
                            ),
                        ),
                    ),
                ),
                'upsell' => array(
                    'ApiGuid' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CustomerID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'IpAddress' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'ProductGroups' => array(
                        'ProductGroupKey' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'PaymentInformation' => array(
                        'XID' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CAVV' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ECI' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'CardholderAuth' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                ),
            ),
            'emanage' => array(
                'prospect' => array(
                    'email' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'phoneNumber' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'customerIdentificationTypeId' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'customerIdentificationValue' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'useShippingAddressForBilling' => array(
                        'type' => 'bool',
                        'length' => ''
                    ),
                    'shippingAddress' => array(
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'billingAddress' => array(
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'analytics' => array(
                        'referringUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'landingUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'browser' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'os' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'screenResolution' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'device' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                ),
                'newOrderWithProspect' => array(
                    'couponCode' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'shippingMethodId' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'comment' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'useShippingAddressForBilling' => array(
                        'type' => 'bool',
                        'length' => ''
                    ),
                    'productId' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'campaignUpsell' => array(
                        'webKey' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'relatedOrderNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'miniUpsell' => array(
                        'productId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'shippingMethodId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                    ),
                    'customer' => array(
                        'id' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'customerIdentificationTypeId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'customerIdentificationValue' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'email' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ip' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'payment' => array(
                        'paymentProcessorId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'cardgatePaymentMethodId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'cardgatePaymentIssuerId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'name' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'creditCard' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'expiration' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'cvv' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'creditCardBrand' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'cardId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'shippingAddress' => array(
                        'id' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'billingAddress' => array(
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'analyticsV2' => array(
                        'referringUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'landingUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'userStringData64' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'antiFraud' => array(
                        'sessionId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'callCenter' => array(
                        'callCenterId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'representativeName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                ),
                'newOrder' => array(
                    'couponCode' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'shippingMethodId' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'comment' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'useShippingAddressForBilling' => array(
                        'type' => 'bool',
                        'length' => ''
                    ),
                    'productId' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'campaignUpsell' => array(
                        'webKey' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'relatedOrderNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'miniUpsell' => array(
                        'productId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'shippingMethodId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                    ),
                    'customer' => array(
                        'id' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'customerIdentificationTypeId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'customerIdentificationValue' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'email' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ip' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'payment' => array(
                        'paymentProcessorId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'cardgatePaymentMethodId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'cardgatePaymentIssuerId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'name' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'creditCard' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'expiration' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'cvv' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'creditCardBrand' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'cardId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'shippingAddress' => array(
                        'id' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'billingAddress' => array(
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'analyticsV2' => array(
                        'referringUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'landingUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'userStringData64' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'antiFraud' => array(
                        'sessionId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'callCenter' => array(
                        'callCenterId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'representativeName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                ),
                'upsell' => array(
                    'couponCode' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'shippingMethodId' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'comment' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'useShippingAddressForBilling' => array(
                        'type' => 'bool',
                        'length' => ''
                    ),
                    'productId' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'campaignUpsell' => array(
                        'webKey' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'relatedOrderNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'miniUpsell' => array(
                        'productId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'shippingMethodId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                    ),
                    'customer' => array(
                        'id' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'customerIdentificationTypeId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'customerIdentificationValue' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'email' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'ip' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'payment' => array(
                        'paymentProcessorId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'cardgatePaymentMethodId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'cardgatePaymentIssuerId' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'name' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'creditCard' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'expiration' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'cvv' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'creditCardBrand' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'cardId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'shippingAddress' => array(
                        'id' => array(
                            'type' => 'integer',
                            'length' => ''
                        ),
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'billingAddress' => array(
                        'firstName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'middleName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address1' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address2' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'city' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'zipCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'state' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'countryCode' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phoneNumber' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'analyticsV2' => array(
                        'referringUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'landingUrl' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'userStringData64' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'antiFraud' => array(
                        'sessionId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                    'callCenter' => array(
                        'callCenterId' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'representativeName' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                    ),
                ),
            ),
            'velox' => array(
                'prospect' => array(
                    'FirstName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'LastName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Email' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Cell' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Phone' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'City' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'StateID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Zip' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CountryID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'BillingAddress1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingAddress2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCity' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingStateID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'BillingZip' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCountryID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Custom1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AgreeToTelemarketing' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AgreeToTerms' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'OS' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Browser' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'IP' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'OfferID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'SubAffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCycleProfileID' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'PageUrl' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
                'newOrder' => array(
                    'FirstName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'LastName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Email' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Cell' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Phone' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'City' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'StateID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Zip' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CountryID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'BillingFirstName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingLastName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingAddress1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingAddress2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCity' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingStateID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'BillingZip' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCountryID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Custom1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'PaymentMethodID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'CardNumber' => array(
                        'type' => 'string',
                        'length' => '16'
                    ),
                    'ExpiryMonth' => array(
                        'type' => 'integer',
                        'length' => '2'
                    ),
                    'ExpiryYear' => array(
                        'type' => 'integer',
                        'length' => '4'
                    ),
                    'Cvv' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AgreeToTelemarketing' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AgreeToTerms' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'OS' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Browser' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'IP' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCycleProfileID' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'PropspectID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'PageUrl' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'ThreeDSecureEnrolledStatus' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
                'newOrderWithProspect' => array(
                    'FirstName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'LastName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Email' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Cell' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Phone' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'City' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'StateID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Zip' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CountryID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Custom1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'OfferID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'SubAffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCycleProfileID' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'C1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'PaymentMethodID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'CardNumber' => array(
                        'type' => 'string',
                        'length' => '16'
                    ),
                    'ExpiryMonth' => array(
                        'type' => 'integer',
                        'length' => '2'
                    ),
                    'ExpiryYear' => array(
                        'type' => 'integer',
                        'length' => '4'
                    ),
                    'Cvv' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AgreeToTelemarketing' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AgreeToTerms' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'OS' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Browser' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'IpAddress' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'PageUrl' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'ThreeDSecureEnrolledStatus' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
                'newOrderCardOnFile' => array(
                    'FirstName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'LastName' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Email' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Cell' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Phone' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Address2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'City' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'StateID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Zip' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'CountryID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Custom1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Custom5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'OfferID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'SubAffiliateID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'BillingCycleProfileID' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'C1' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C2' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C3' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C4' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'C5' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'PaymentMethodID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'CardNumber' => array(
                        'type' => 'string',
                        'length' => '16'
                    ),
                    'ExpiryMonth' => array(
                        'type' => 'integer',
                        'length' => '2'
                    ),
                    'ExpiryYear' => array(
                        'type' => 'integer',
                        'length' => '4'
                    ),
                    'Cvv' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AgreeToTelemarketing' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AgreeToTerms' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'OS' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'Browser' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'IpAddress' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'PageUrl' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'ThreeDSecureEnrolledStatus' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
                'importUpsell' => array(
                    'PaymentMethodID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'CardNumber' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'ExpiryMonth' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'ExpiryYear' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'Cvv' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'AgreeToTelemarketing' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'AgreeToTerms' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'OfferID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'BillingCycleProfileID' => array(
                        'type' => 'ctype_digit',
                        'length' => ''
                    ),
                    'OrderID' => array(
                        'type' => 'integer',
                        'length' => ''
                    ),
                    'PageUrl' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'ThreeDSecureEnrolledStatus' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'InitialXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillECI' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillCAVV' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'RebillXID' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                )
            ),
            'sixcrm' => array(
                'prospect' => array(
                    'campaign' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'customer' => array(
                        'firstname' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'lastname' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'email' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'phone' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'billing' => array(
                            'line1' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'city' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'state' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'zip' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'country' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                        ),
                        'address' => array(
                            'line1' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'city' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'state' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'zip' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'country' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                        ),
                    ),
                ),
                'newOrderWithProspect' => array(
                    'session' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'product_schedules' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                    'products' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                    'creditcard' => array(
                        'number' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'expiration' => array(
                            'type' => 'string',
                            'length' => '4'
                        ),
                        'cvv' => array(
                            'type' => 'string',
                            'length' => '3'
                        ),
                        'name' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address' => array(
                            'line1' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'city' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'state' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'zip' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'country' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                        ),
                    ),
                    'transaction_subtype' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
                'newOrder' => array(
                    'session' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'product_schedules' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                    'products' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                    'creditcard' => array(
                        'number' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'expiration' => array(
                            'type' => 'string',
                            'length' => '4'
                        ),
                        'cvv' => array(
                            'type' => 'string',
                            'length' => '3'
                        ),
                        'name' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address' => array(
                            'line1' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'city' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'state' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'zip' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'country' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                        ),
                    ),
                    'transaction_subtype' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
                'newOrderCardOnFile' => array(
                    'session' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                    'product_schedules' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                    'products' => array(
                        'type' => 'array',
                        'length' => ''
                    ),
                    'creditcard' => array(
                        'number' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'expiration' => array(
                            'type' => 'string',
                            'length' => '4'
                        ),
                        'cvv' => array(
                            'type' => 'string',
                            'length' => '3'
                        ),
                        'name' => array(
                            'type' => 'string',
                            'length' => ''
                        ),
                        'address' => array(
                            'line1' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'city' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'state' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'zip' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                            'country' => array(
                                'type' => 'string',
                                'length' => ''
                            ),
                        ),
                    ),
                    'transaction_subtype' => array(
                        'type' => 'string',
                        'length' => ''
                    ),
                ),
            ),
        );
    }

    public function getStructure($crm, $method)
    {
        if (!empty($this->structure[$crm][$method]))
        {
            return $this->structure[$crm][$method];
        }
    }

}
