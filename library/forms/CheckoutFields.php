<?php

namespace Application\Form;

class CheckoutFields
{

    public static $data = array(
        'billingSameAsShipping' => array(
            'label'    => "Billing Same As Shipping",
            'aliases'  => array(),
            'required' => true,
        ),
        'billingFirstName'      => array(
            'label'    => "Billing First Name",
            'aliases'  => array(),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'billingLastName'       => array(
            'label'    => "Billing Last Name",
            'aliases'  => array(),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'billingAddress1'       => array(
            'label'    => "Billing Address",
            'aliases'  => array(),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'billingAddress2'       => array(
            'label'   => "Billing Address",
            'aliases' => array(),
        ),
        'billingZip'            => array(
            'label'    => "Billing Zip",
            'aliases'  => array('billingZip'),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'billingCity'           => array(
            'label'    => "Billing City",
            'aliases'  => array('billingCity'),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'billingState'          => array(
            'label'    => "Billing State",
            'aliases'  => array('billingState'),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'billingCountry'        => array(
            'label'    => "Billing Country",
            'aliases'  => array('billingCountry'),
            'required' => array(
                'billingSameAsShipping', 'no',
            ),
        ),
        'cardType'              => array(
            'label'    => "Card Type",
            'aliases'  => array('creditCardType'),
            'required' => true,
        ),
        'cardNumber'            => array(
            'label'    => "Card Number",
            'aliases'  => array('creditCardNumber'),
            'required' => true,
        ),
        'cardExpiryMonth'       => array(
            'label'    => "Card Expiry Month",
            'aliases'  => array('expmonth'),
            'required' => true,
        ),
        'cardExpiryYear'        => array(
            'label'    => "Expiry Year",
            'aliases'  => array('expyear'),
            'required' => true,
        ),
        'cvv'                   => array(
            'label'    => "CVV",
            'aliases'  => array('CVV'),
            'required' => true,
        ),
        'userIsAt'         => array(
            'label'    => "User Is At",
            'aliases'  => array('user_is_at'),
            'required' => false,
        ),
    );
}
