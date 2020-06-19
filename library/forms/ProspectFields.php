<?php

namespace Application\Form;

class ProspectFields
{

    public static $data = array(
        'firstName'        => array(
            'label'    => "First Name",
            'aliases'  => array('firstName'),
            'required' => true,
        ),
        'lastName'         => array(
            'label'    => "Last Name",
            'aliases'  => array('lastName'),
            'required' => true,
        ),
        'email'            => array(
            'label'    => "Email",
            'aliases'  => array(),
            'required' => true,
        ),
        'phone'            => array(
            'label'    => "Phone Number",
            'aliases'  => array(),
            'required' => true,
        ),
        'shippingAddress1' => array(
            'label'    => "Address",
            'aliases'  => array(),
            'required' => true,
        ),
        'shippingAddress2' => array(
            'label'    => "Address",
            'aliases'  => array(),
            'required' => false,
        ),
        'shippingZip'      => array(
            'label'    => "Zip",
            'aliases'  => array(),
            'required' => true,
        ),
        'shippingCity'     => array(
            'label'    => "City",
            'aliases'  => array(),
            'required' => true,
        ),
        'shippingState'    => array(
            'label'    => "State",
            'aliases'  => array(),
            'required' => true,
        ),
        'shippingCountry'  => array(
            'label'    => "Country",
            'aliases'  => array(),
            'required' => true,
        ),
        'userIsAt'         => array(
            'label'    => "User Is At",
            'aliases'  => array('user_is_at'),
            'required' => false,
        ),
    );

}
