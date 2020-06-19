<?php

namespace Application\Gateway\Nmi;

/**
 * nmiDirectPost Class
 *
 * This class implements the Network Merchants Direct Post API
 * This API allows for processing credit and online ACH transactions
 * Methods supported are sale, auth, capture, void, refund, credit and update
 *
 * @author 	Jamie Estep
 * @copyright	October 2009
 * @version	1.0
 * @link		http://www.saynotoflash.com/scripts/network-merchants-api-php-class-script/
 */
use Application\Gateway\Nmi\Nmi;

class NmiDirectPost extends Nmi
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
     * @param string $type
     *  The type of transaction
     *  This is normally set automatically
     *  Acceptable values are: sale, auth, capture, void, refund, credit, update
     */

    protected function setType($type)
    {
        $this->data['transaction']['type'] = $type;
    }

    /*
     * @param string $transaction_id
     *  Required for capture, void, credit, and update types
     */

    public function setTransactionId($transaction_id)
    {
        $this->data['transaction']['transaction_id'] = $transaction_id;
    }

    /*
     * @param decimal $account
     *  The amount of the transaction
     */

    public function setAmount($amount)
    {
        $this->data['transaction']['amount'] = $amount;
    }

    /*
     * @param string $processor_id
     *  Only required for load balancing
     *  processor_id is obtained under Options->Load Balancing in the Control Panel
     */

    public function setProcessorId($processor_id)
    {
        $this->data['transaction']['processor_id'] = $processor_id;
    }

    /*
     * @param int $dup_seconds
     *  Used to disable dup checking (if supported)
     */

    public function setDupSeconds($dup_seconds)
    {
        $this->data['transaction']['dup_seconds'] = $dup_seconds;
    }

    /*
     * @param string $descriptor
     *  Used to set a custom descriptor for receipts / bank statements
     *  Not supported with most processors
     */

    public function setDescriptor($descriptor)
    {
        $this->data['transaction']['descriptor'] = $descriptor;
    }

    /*
     * @param string $descriptor_phone
     *  Used to set a custom descriptor phone number for receipts / bank statements
     *  Not supported with most processors
     */

    public function setDescriptorPhone($descriptor_phone)
    {
        $this->data['transaction']['descriptor_phone'] = $descriptor_phone;
    }

    /*
     * @param string $sku_num
     *  Used to associate transaction with recurring SKU
     */

    public function setProductSku($sku_num)
    {
        $this->data['transaction']['product_sku_' . $sku_num] = $sku_num;
    }

    /*
     * @param string $order_description
     *  Set the description for the customer
     */

    public function setOrderDescription($orderdescription)
    {
        $this->data['transaction']['orderdescription'] = $orderdescription;
    }

    /*
     * $param string $order_id
     *  Set a unique order identifier
     */

    public function setOrderId($orderid)
    {
        $this->data['transaction']['orderid'] = $orderid;
    }

    /*
     * @param string $ipaddress
     *  Store the customer's IP address
     *  Must be in standard 255.255.255.255 format
     */

    public function setIpAddress($ipaddress)
    {
        $this->data['transaction']['ipaddress'] = $ipaddress;
    }

    /*
     * @param decimal $tax
     */

    public function setTax($tax)
    {
        $this->data['transaction']['tax'] = $tax;
    }

    /*
     * @param decimal $shipping
     */

    public function setShipping($shipping)
    {
        $this->data['transaction']['shipping'] = $shipping;
    }

    /*
     * @param int $ponumber
     *  Set a purchase order number for the transaction
     */

    public function setPoNumber($ponumber)
    {
        $this->data['transaction']['ponumber'] = $ponumber;
    }

    /*
     * @param string $tracking_number
     *  The tracking number for the package being sent
     */

    public function setTrackingNumber($tracking_number)
    {
        $this->data['transaction']['tracking_number'] = $tracking_number;
    }

    /*
     * @param string $shipping_carrier
     *  The carrier whom the package is being shipped through
     *  Acceptable values are ups, fedex, dhl, or usps
     */

    public function setShippingCarrier($shipping_carrier)
    {
        $this->data['transaction']['shipping_carrier'] = $shipping_carrier;
    }

    /*
     * @param string $payment
     *  Denotes whether we're using a credit card or ACH
     *  Acceptable values are creditcard or check
     */

    public function setPayment($payment = 'creditcard')
    {
        $this->data['transaction']['payment'] = $payment;
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

    public function setCcExp($ccexp)
    {
        $this->data['credit_card']['ccexp'] = $ccexp;
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
     * @param string $checkname
     *  Name on bank account
     */

    public function setAccountName($checkname)
    {
        $this->data['ach']['checkname'] = $checkname;
    }

    /*
     * @param string $checkaccount
     *  Numerical string for the bank account number
     */

    public function setAccount($checkaccount)
    {
        $this->data['ach']['checkaccount'] = $checkaccount;
    }

    /*
     * @param string $routing
     *  9 digit numerical routing number
     */

    public function setRouting($checkaba)
    {
        $this->data['ach']['checkaba'] = $checkaba;
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

    public function setFirstName($firstname)
    {
        $this->data['customer']['firstname'] = $firstname;
    }

    public function setLastName($lastname)
    {
        $this->data['customer']['lastname'] = $lastname;
    }

    public function setAddress1($address1)
    {
        $this->data['customer']['address1'] = $address1;
    }

    public function setAddress2($address2)
    {
        $this->data['customer']['address2'] = $address2;
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

    public function setValidation($validation)
    {
        $this->data['customer']['validation'] = $validation;
    }

    /* SHIPPING ADDRESS
     *
     * The following are self-explanatory
     */

    public function setShippingCompany($shipping_company)
    {
        $this->data['customer']['shipping_company'] = $shipping_company;
    }

    public function setShippingFirstName($shipping_firstname)
    {
        $this->data['customer']['shipping_firstname'] = $shipping_firstname;
    }

    public function setShippingLastName($shippinglastname)
    {
        $this->data['customer']['shipping_lastname'] = $shipping_lastname;
    }

    public function setShippingAddress1($shipping_address1)
    {
        $this->data['customer']['shipping_address1'] = $shipping_address1;
    }

    public function setShippingAddress2($shipping_address2)
    {
        $this->data['customer']['shipping_address2'] = $shipping_address2;
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

    public function setShippingEmail($shipping_email)
    {
        $this->data['customer']['shipping_email'] = $shipping_email;
    }

    /*     * ****** added by sibnath start ******* */
    /*
     * @param string $currency
     *  Set the currenct if the account supports multi-currency
     */

    public function setCurrency($currency)
    {
        $this->data['transaction']['currency'] = $currency;
    }    

    /**
     *  Recurring specific fields 
     */
    public function setRecurring($param)
    {
        $this->data['transaction']['recurring'] = $param;
    }
    
    public function setPlanPayment($param)
    {
        $this->data['transaction']['plan_payments'] = $param;
    }

    public function setPlanAmount($param)
    {
        $this->data['transaction']['plan_amount'] = $param;
    }

    public function setDayFrequency($param)
    {
        $this->data['transaction']['day_frequency'] = $param;
    }

    public function setMonthFrequency($param)
    {
        $this->data['transaction']['month_frequency'] = $param;
    }

    public function setDayOfMonth($param)
    {
        $this->data['transaction']['day_of_month'] = $param;
    }

    /**
     * 
     * @param type $param YYYYMMDD
     */
    public function setStartDate($param)
    {
        $this->data['transaction']['start_date'] = $param;
    }

    /**
     * Set plan id for subscription for future orders
     * Create a subscription tied to a Plan ID if the
     *  sale/auth transaction is
      successful.

     * */
    public function setPlanId($id)
    {
        $this->data['transaction']['plan_id'] = $id;
    }

    /*
     * @param string $field_id and $field_value
     *  Custom Merchant Definied Data Field
     *  This can be set a number of times
     *  Additional data will be added in the form of
     *  merchant_defined_field_1, merchant_defined_field_2, etc...
     */

    public function setMerchantDefinedField($field_id, $field_value)
    {
        $this->data['merchant_defined_fields']['merchant_defined_field_' . $field_id] = $field_value;
    }

    /*
     * @param string $post_product_description
     */

    public function setPostProductDescription($post_product_description)
    {
        $this->data['product']['description'] = $post_product_description;
    }

    /*     * ****** added by sibnath end ******* */

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

        //Log their IP address as well
        if (!isset($this->data['transaction']['ipaddress']) || empty($this->data['transaction']['ipaddress']))
            $this->setIpAddress($_SERVER['REMOTE_ADDR']);

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
     * Set the transaction type to sale
     */

    public function sale()
    {
        $this->setType('sale');
    }

    /*
     * Set the transaction type to auth
     */

    public function auth()
    {
        $this->setType('sale');
    }

    /*
     * Set the transaction type to credit
     */

    public function credit()
    {
        $this->setType('credit');
    }

    /*
     *
     * Set the transaction type to capture
     * @param int $transactionid
     * @param array[string] string $options
     */

    public function capture($transactionid, $amount, $options = array())
    {
        $this->setType('capture');
        $this->setTransactionId($transactionid);
        $this->setAmount($amount);

        if (isset($options['tracking_number']))
            $this->setTrackingNumber($options['tracking_number']);
        if (isset($options['shipping_carrier']))
            $this->setShippingCarrier($options['shipping_carrier']);
        if (isset($options['orderid']))
            $this->setOrderId($options['orderid']);
    }

    /*
     *
     * Set the transaction type to refund
     * @param int $transactionid
     * @param decimal $amount
     */

    public function refund($transactionid, $amount)
    {
        $this->setType('refund');
        $this->setTransactionId($transactionid);
        $this->setAmount($amount);
    }

    /*
     *
     * Set the transaction type to void
     * @param int $transactionid
     */

    public function void($transactionid)
    {
        $this->setTransactionId($transactionid);
        $this->setType('void');
    }

    /*
     *
     * Set the transaction type to update
     * @param int $transactionid
     * @param array[string] string $options
     */

    public function update($transactionid, $options)
    {
        $this->setTransactionId($transactionid);
        $this->setType('update');

        if (isset($options['tracking_number']))
            $this->setTrackingNumber($options['tracking_number']);
        if (isset($options['shipping_carrier']))
            $this->setShippingCarrier($options['shipping_carrier']);
        if (isset($options['orderid']))
            $this->setOrderId($options['orderid']);
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
