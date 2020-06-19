<?php

namespace Application\Gateway\Nmi;

/**
 * nmiCustomerVault Class
 *
 * This class implements the Network Merchants Customer Vault API
 * This API allows for adding, updating, and deleting customers from
 * the Network Merchants Customer Vault
 *
 * @author 	Jamie Estep
 * @copyright	October 2009
 * @version	1.0
 * @link		http://www.saynotoflash.com/scripts/network-merchants-api-php-class-script/
 */
use Application\Gateway\Nmi\Nmi;

class NmiCustomerVault extends Nmi
{
    /*
     * The data property is an array that holds all of our values until we create a query string
     */

    public $data = array();

    /*
     * The $query_string property is a properly formatted name-value-pair string
     */
    public $query_string;

    /*
     * @param array $options
     * - nmi_url
     * - nmi_user
     * - nmi_password
     */

    public function __construct($options = array())
    {
        parent::__construct($options);
    }

    /*
     * @return string parent::nmi_url parameter
     *  Network Merchants Post Url
     */

    protected function getNmiUrl()
    {
        return parent::getNmiUrl();
    }

    /*
     * @return string parent::nmi_user parameter
     *  Network Merchants API Username
     */

    protected function getNmiUser()
    {
        return parent::getNmiUser();
    }

    /*
     * @return string parent::nmi_password parameter
     *  Network Merchants API Password
     */

    protected function getNmiPassword()
    {
        return parent::getNmiPassword();
    }

    /* TRANSACTION METHODS
     *
     */

    /*
     * @param string $customer_vault
     *  Posible actions are add_customer, update_customer, delete_customer
     */

    protected function setNmiCustomerVault($customer_vault)
    {
        $this->customer_vault = $customer_vault;
    }

    /*
     * @return string $this->customer_vault
     */

    function getNmiCustomerVault()
    {
        return $this->customer_vault;
    }

    /*
     * @param string $customer_vault_id
     *  $customer_vault_id is used to identify an existing or
     *  specify a specific id when using the API
     */

    public function setCustomerVaultId($customer_vault_id)
    {
        $this->data['transaction']['customer_vault_id'] = $customer_vault_id;
    }

    /*
     * @param decimal $amount
     *  Enter the amount to charge the transaction
     */

    public function setAmount($amount)
    {
        $this->data['transaction']['amount'] = $amount;
    }

    /*
     * @param string $currency
     *  Set the currenct if the account supports multi-currency
     */

    public function setCurrency($currency)
    {
        $this->data['transaction']['currency'] = $currency;
    }

    /*
     * $param string $order_id
     *  Set a unique order identifier
     */

    public function setOrderId($order_id)
    {
        $this->data['transaction']['order_id'] = $order_id;
    }

    /*
     * @param string $order_description
     *  Set the description for the customer
     */

    public function setOrderDescription($order_description)
    {
        $this->data['transaction']['order_description'] = $order_description;
    }

    /*
     * @param int $po_number
     *  Set a purchase order number for the customer
     */

    public function setPoNumber($po_number)
    {
        $this->data['transaction']['po_number'] = $po_number;
    }

    /*
     * @param decimal $tax
     */

    public function setTax($tax)
    {
        $this->data['transaction']['tax'] = $tax;
    }

    public function setTaxExempt($tax_exempt)
    {
        $this->data['transaction']['tax_exempt'] = $tax_exempt;
    }

    /*
     * @param decimal $shipping
     */

    public function setShipping($shipping)
    {
        $this->data['transaction']['shipping'] = $shipping;
    }

    /*
     * @param string $method
     *  Denotes whether we're adding a credit card or ACH
     *  Acceptable values are creditcard or check
     */

    public function setMethod($method)
    {
        $this->data['transaction']['method'] = $method;
    }

    /* CREDIT CARD METHODS
     *
     */

    /*
     * @param int $ccnumber
     *  Valid Credit card number to add
     */

    public function setCcNumber($ccnumber)
    {
        $this->data['credit_card']['ccnumber'] = $ccnumber;
    }

    /*
     * @param string $cc_exp
     *  Expiration date in the format of MMYY
     */

    public function setCcExp($cc_exp)
    {
        $this->data['credit_card']['cc_exp'] = $cc_exp;
    }

    /*
     * @param string $cvv
     *  3 or 4 Digit numerical string
     *  Amex = 4 digits, Visa/MC/Discover = 3 Digits
     */

    public function setCvv($cvv)
    {
        $this->data['credit_card']['cvv'] = $cvv;
    }

    /* ACH METHODS
     *
     */

    /*
     * @param string $account_name
     *  Name on bank account
     */

    public function setAccountName($account_name)
    {
        $this->data['ach']['account_name'] = $account_name;
    }

    /*
     * @param string $account
     *  Numerical string for the bank account number
     */

    public function setAccount($account)
    {
        $this->data['ach']['account'] = $account;
    }

    /*
     * @param string $routing
     *  9 digit numerical routing number
     */

    public function setRouting($routing)
    {
        $this->data['ach']['routing'] = $routing;
    }

    /*
     * @param string $account_type
     *  Denotes checking or savings account
     *  Acceptable values are checking or savings
     */

    public function setAccountType($account_type)
    {
        $this->data['ach']['account_type'] = $account_type;
    }

    /*
     * @param string $account_holder_type
     *  Denotes personal or business account
     *  Acceptable values are personal or business
     */

    public function setAccountHolderType($account_holder_type)
    {
        $this->data['ach']['account_holder_type'] = $account_holder_type;
    }

    /*
     * @param string $sec_code
     *  ACH Standard Entry Class codes
     *  Allowed PPD/WEB/TEL/CCD
     */

    public function setSecCode($sec_code)
    {
        $this->data['ach']['sec_code'] = $sec_code;
    }

    /* BILLING ADDRESS
     *
     * The following are self-explanatory
     */

    public function setCompany($company)
    {
        $this->data['customer']['company'] = $company;
    }

    public function setFirstName($first_name)
    {
        $this->data['customer']['first_name'] = $first_name;
    }

    public function setLastName($last_name)
    {
        $this->data['customer']['last_name'] = $last_name;
    }

    public function setAddress1($address_1)
    {
        $this->data['customer']['address_1'] = $address_1;
    }

    public function setAddress2($address_2)
    {
        $this->data['customer']['address_2'] = $address_2;
    }

    public function setCity($city)
    {
        $this->data['customer']['city'] = $city;
    }

    public function setState($state)
    {
        $this->data['customer']['state'] = $state;
    }

    public function setZip($zip)
    {
        $this->data['customer']['zip'] = $zip;
    }

    public function setCountry($country)
    {
        $this->data['customer']['country'] = $country;
    }

    public function setPhone($phone)
    {
        $this->data['customer']['phone'] = $phone;
    }

    public function setFax($fax)
    {
        $this->data['customer']['fax'] = $fax;
    }

    public function setEmail($email)
    {
        $this->data['customer']['email'] = $email;
    }

    /* SHIPPING ADDRESS
     *
     * The following are self-explanatory
     */

    public function setShippingCompany($shipping_company)
    {
        $this->data['customer']['shipping_company'] = $shipping_company;
    }

    public function setShippingFirstName($shipping_first_name)
    {
        $this->data['customer']['shipping_first_name'] = $shipping_first_name;
    }

    public function setShippingLastName($shipping_last_name)
    {
        $this->data['customer']['shipping_last_name'] = $shipping_last_name;
    }

    public function setShippingAddress1($shipping_address_1)
    {
        $this->data['customer']['shipping_address_1'] = $shipping_address_1;
    }

    public function setShippingAddress2($shipping_address_2)
    {
        $this->data['customer']['shipping_address_2'] = $shipping_address_2;
    }

    public function setShippingCity($shipping_city)
    {
        $this->data['customer']['shipping_city'] = $shipping_city;
    }

    public function setShippingState($shipping_state)
    {
        $this->data['customer']['shipping_state'] = $shipping_state;
    }

    public function setShippingZip($shipping_zip)
    {
        $this->data['customer']['shipping_zip'] = $shipping_zip;
    }

    public function setShippingCountry($shipping_country)
    {
        $this->data['customer']['shipping_country'] = $shipping_country;
    }

    public function setShippingPhone($shipping_phone)
    {
        $this->data['customer']['shipping_phone'] = $shipping_phone;
    }

    public function setShippingFax($shipping_fax)
    {
        $this->data['customer']['shipping_fax'] = $shipping_fax;
    }

    public function setShippingEmail($shipping_email)
    {
        $this->data['customer']['shipping_email'] = $shipping_email;
    }

    /*
     * @param string $field_value
     *  Custom Merchant Definied Data Field
     *  This can be set a number of times
     *  Additional data will be added in the form of
     *  merchant_defined_field_1, merchant_defined_field_2, etc...
     */

    public function setMerchantDefinedField($field_value)
    {
        if (isset($this->data['merchant_defined_fields']))
        {
            $total = count($this->data['merchant_defined_fields']);

            $this->data['merchant_defined_fields']['merchant_defined_field_' . ($total++)] = $field_value;
        }
        else
        {
            $this->data['merchant_defined_fields']['merchant_defined_field_1'] = $field_value;
        }
    }

    /*
     * @return array
     *  Returns the $this->data holder array
     */

    public function getData()
    {
        return $this->data;
    }

    /*
     * @param string $parameter
     * @param string $value
     *  Add a query parameter to the query string
     */

    private function addQueryParameter($parameter, $value)
    {
        if (isset($value) AND ! empty($value))
            $this->setQueryString($this->getQueryString() . $parameter . '=' . trim($value) . '&');
    }

    /*
     * @return $this->query_string
     *  Return the query string
     */

    private function getQueryString()
    {
        return $this->query_string;
    }

    /*
     * @param $string
     *  Replace $this->query_string with $string
     */

    private function setQueryString($string)
    {
        return $this->query_string = $string;
    }

    /*
     * Make the query string safe for posting
     */

    private function cleanQueryString($string)
    {
        $string = str_replace(' ', '+', $string);

        return $string;
    }

    /*
     * Prepare the query string for posting
     */

    public function prepareQueryString()
    {
        $this->addQueryParameter('username', $this->getNmiUser());
        $this->addQueryParameter('password', $this->getNmiPassword());
        $this->addQueryParameter('customer_vault', $this->getNmiCustomerVault());

        // Build Query String
        if ($data = $this->getData())
        {
            foreach ($data as $group)
            {
                foreach ($group as $param => $value)
                {
                    $this->addQueryParameter($param, $value);
                }
            }
        }

        return $this->cleanQueryString(substr($this->getQueryString(), 0, -1));
    }

    /*
     * @param string $customer_id
     * $customer_id is not mandatory
     * The gateway will return a customer_id if you do not specify one
     * Add a customer to the customer vault
     */

    public function add()
    {
        $this->setNmiCustomerVault('add_customer');
    }

    /*
     * @param decimal $amount
     *  Enter the amount we need to charge right now
     */

    public function addAndCharge($amount)
    {
        $this->data['transaction']['type'] = 'sale';

        $this->setAmount($amount);
        $this->setNmiCustomerVault('add_customer');
    }

    /*
     * @param decimal $amount
     *  Enter the amount we need to charge the customer
     */

    public function charge($amount)
    {

        if (!isset($this->data['transaction']['customer_vault_id']) || empty($this->data['transaction']['customer_vault_id']))
        {
            throw new Exception('Customer ID must be set in order to charge the customer');
        }

        //Added this below line manually to recognize the type of operation
        $this->setNmiCustomerVault('update_customer');

        $this->setAmount($amount);
    }

    /*
     * @param string $customer_id
     *  $customer_id is a numerical string that references the customer in th vault
     *  Customer Id is required for updating a customer from the vault
     */

    public function update()
    {

        if (!isset($this->data['transaction']['customer_vault_id']) || empty($this->data['transaction']['customer_vault_id']))
        {
            throw new Exception('Customer ID must be set in order to update the customer');
        }

        $this->setNmiCustomerVault('update_customer');
    }

    /*
     * @param string $customer_id
     *  $customer_id is a numerical string that references the customer in th vault
     *  Customer Id is required for deleting a customer from the vault
     */

    public function delete()
    {

        if (!isset($this->data['transaction']['customer_vault_id']) || empty($this->data['transaction']['customer_vault_id']))
        {
            throw new Exception('Customer ID must be set in order to delete the customer');
        }

        $this->setNmiCustomerVault('delete_customer');
    }

    /*
     *
     * @return array
     *  We execute through the parent class to reuse code
     */

    public function execute($string = false)
    {
        // Prepare the query
        $query_string = $this->prepareQueryString();

        return parent::execute($query_string);
    }

}
