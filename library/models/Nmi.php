<?php

namespace Application\Model;

use Application\Gateway\Nmi\NmiDirectPost;
use Application\Gateway\Nmi\NmiCustomerVault;
use Application\CrmPayload;
use Application\CrmResponse;
use Application\Http;
use Application\Registry;
use Application\Response;
use Exception;
use Application\Request;

class Nmi extends BaseCrm
{

    protected $transaction, $customer;

    public function __construct($crmId)
    {
        parent::__construct($crmId);

        $options = array(
            "nmi_url" => $this->endpoint,
            "nmi_user" => $this->username,
            "nmi_password" => $this->password
        );

        $this->transaction = new NmiDirectPost($options);
        $this->customer = new NMICustomerVault($options);
    }

    protected function beforeAnyCrmClassMethodCall()
    {
        $this->params = $this->response = array();

        $this->params['nmi_user'] = $this->username;
        $this->params['nmi_password'] = $this->password;
    }

    protected function prospect()
    {
        CrmResponse::replace(array(
            'success' => true,
            'prospectId' => rand(),
        ));
    }

    protected function newOrderWithProspect()
    {
        if ($this->sale() == false)
        {
            return false;
        }

        if ($this->addCustomer() == false)
        {
            CrmResponse::set('success', false);
            CrmResponse::set('customerId', null);
            return false;
        }
        $customerVaultId = isset($this->response['customer_vault_id']) ? $this->response['customer_vault_id'] : null;
        CrmResponse::set('customerId', $customerVaultId);
    }

    protected function newOrder()
    {
        if ($this->sale() == false)
        {
            return false;
        }

        if ($this->addCustomer() == false)
        {
            CrmResponse::set('success', false);
            CrmResponse::set('customerId', null);
            return false;
        }
        $customerVaultId = isset($this->response['customer_vault_id']) ? $this->response['customer_vault_id'] : null;
        CrmResponse::set('customerId', $customerVaultId);
    }

    protected function newOrderCardOnFile()
    {
        return $this->chargeCustomer();
    }

    public static function isValidCredential($credential)
    {
        $options = array(
            "nmi_url" => $credential['endpoint'],
            "nmi_user" => $credential['username'],
            "nmi_password" => $credential['password']
        );
        $vault = new NMICustomerVault($options);
        $vault->add();
        $result = $vault->execute();
        if (empty($result))
        {
            return false;
        }
        else if (!empty($result['responsetext']) && preg_match("/Authentication Failed/i", $result['responsetext']))
        {
            return false;
        }

        return true;
    }

    public function sale()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $this->prepareProductDetails();
        $formParam = Request::form()->all();
        $Tax = (!empty($formParam['nmiTax'])) ? $formParam['nmiTax'] : '0.00';
        $Amount = (float) ($this->params['productTotal'] + $this->params['shippingTotal'] + $Tax);
        $Shipping = $this->params['shippingTotal']; 
        $CcNumber = $this->params['cardNumber']; 
        $CcExp = $this->params['cardExpiryMonth'] . $this->params['cardExpiryYear'];
        $Cvv = $this->params['cvv'];
        $FirstName = $this->params['firstName'];
        $LastName = $this->params['lastName'];
        $Address1 = $this->params['shippingAddress1'];
        $City = $this->params['shippingCity'];
        $State = $this->params['shippingState'];
        $Zip = $this->params['lastName'];
        $Country = $this->params['shippingCountry'];
        $Phone = $this->params['phone'];
        $Email = $this->params['email'];
        $Orderid = uniqid();
        $IpAddress = $this->params['ipAddress'];
        $currency = (!empty($formParam['nmiCurrency'])) ?
                strtoupper($formParam['nmiCurrency']) : 'USD';
        $this->transaction->setCurrency($currency);
        $this->transaction->setAmount($Amount);
        $this->transaction->setTax($Tax);
        $this->transaction->setShipping($Shipping);

        $this->transaction->setCcNumber($CcNumber);
        $this->transaction->setCcExp($CcExp);
        $this->transaction->setCvv($Cvv);
        $this->transaction->setFirstName($FirstName);
        $this->transaction->setLastName($LastName);
        $this->transaction->setAddress1($Address1);
        $this->transaction->setCity($City);
        $this->transaction->setState($State);
        $this->transaction->setZip($Zip);
        $this->transaction->setCountry($Country);
        $this->transaction->setPhone($Phone);
        $this->transaction->setEmail($Email);
        $this->transaction->setOrderId($Orderid);
        $this->transaction->setIpAddress($IpAddress);

        if (!empty($this->params['nmiPlanId']))
        {
            $this->transaction->setPlanId($this->params['nmiPlanId']);
            $this->transaction->setRecurring('add_subscription');
        }

        $this->transaction->sale();

        if ($this->makeHttpRequest($this->transaction) === false)
        {
            CrmResponse::replace(array(
                'success' => false,
                'orderId' => null
            ));
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->response['transactionid']
        ));
        return true;
    }

    private function makeHttpRequest($instance)
    {
        $result = $instance->execute();
        if (empty($result))
        {
            return false;
        }
        if ($result['response'] == 1)
        {
            $this->response = $result;
            return true;
        }

        return false;
    }

    public function auth($detailsArr)
    {
        $OrderDescription = $detailsArr['orderDescription'];
        $Amount = $detailsArr['payable_amount']; //1.00
        $Tax = $detailsArr['tax']; //0.00
        $Shipping = $detailsArr['shipping']; //0.00
        $CcNumber = $detailsArr['card_number']; //4111111111111111
        $CcExp = $detailsArr['card_expiry_month'] . $detailsArr['card_expiry_year']; //1115
        $Cvv = $detailsArr['card_cvv']; //999
        $Company = $detailsArr['company'];
        $FirstName = $detailsArr['firstName'];
        $LastName = $detailsArr['lastName'];
        $Address1 = $detailsArr['address1'];
        $City = $detailsArr['city'];
        $State = trim($detailsArr['state']);
        $Zip = $detailsArr['zip'];
        $Country = $detailsArr['country'];
        $Phone = $detailsArr['phone'];
        $Email = $detailsArr['email'];

        //$transaction = new nmiDirectPost;
        $this->transaction->setOrderDescription($OrderDescription);

        $this->transaction->setAmount($Amount);
        $this->transaction->setTax($Tax);
        $this->transaction->setShipping($Shipping);

        $this->transaction->setCcNumber($CcNumber);
        $this->transaction->setCcExp($CcExp);
        $this->transaction->setCvv($Cvv);

        $this->transaction->setCompany($Company);
        $this->transaction->setFirstName($FirstName);
        $this->transaction->setLastName($LastName);
        $this->transaction->setAddress1($Address1);
        $this->transaction->setCity($City);
        $this->transaction->setState($State);
        $this->transaction->setZip($Zip);
        $this->transaction->setCountry($Country);
        $this->transaction->setPhone($Phone);
        $this->transaction->setEmail($Email);

        $this->transaction->auth();

        $result = $this->transaction->execute();

        switch ($result['response'])
        {
            case 1:
                $result['transaction_state'] = 'Approved';
                break;
            case 2:
                $result['transaction_state'] = 'Declined';
                break;
            default;
                $result['transaction_state'] = 'System Error';
        }
        return $result;
    }

    public function refund($transaction_id, $amount)
    {
        $this->transaction->setTransactionId($transaction_id);
        $this->transaction->setAmount($amount);  //optional. Only needed if you are making a partial refund
        //$transaction->refund();

        $this->transaction->refund($transaction_id, $amount);

        $result = $this->transaction->execute();

        /*
          Result is returned as an array in the format of...
          $result = Array
          (
          [response] => 1
          [responsetext] => SUCCESS
          [authcode] => 123456
          [transactionid] => 1087714082
          [avsresponse] => Y
          [cvvresponse] => M
          [orderid] =>
          [type] => sale
          [response_code] => 100
          )
         *
         */

        switch ($result['response'])
        {
            case 1:
                $result['transaction_state'] = 'Approved';
                break;
            case 2:
                $result['transaction_state'] = 'Declined';
                break;
            default;
                $result['transaction_state'] = 'System Error';
        }
        return $result;
    }

    public function nmi_void($transaction_id)
    {
        $this->transaction->setTransactionId($transaction_id);
        $this->transaction->void($transaction_id);
        $result = $this->transaction->execute();

        /*
          Result is returned as an array in the format of...
          $result = Array
          (
          [response] => 1
          [responsetext] => SUCCESS
          [authcode] => 123456
          [transactionid] => 1087714082
          [avsresponse] => Y
          [cvvresponse] => M
          [orderid] =>
          [type] => sale
          [response_code] => 100
          )
         *
         */

        switch ($result['response'])
        {
            case 1:
                $result['transaction_state'] = 'Approved';
                break;
            case 2:
                $result['transaction_state'] = 'Declined';
                break;
            default;
                $result['transaction_state'] = 'System Error';
        }
        return $result;
    }

    public function nmi_update()
    {
        //Transaction ID from previous Auth
        //$transaction_id = '123456';
        $transaction_id = '2677245415';

        //Tracking Number
        $tracking_number = '1Z8905';

        //Custom Order Id
        $order_id = '432543';
        $shipping_carrier = 'ups'; //Acceptable values are ups, fedex, dhl, or usps
        //$transaction = new nmiDirectPost;

        $this->transaction->setTransactionId($transaction_id);
        $this->transaction->setOrderId($order_id);
        $this->transaction->setTrackingNumber($tracking_number);
        $this->transaction->setShippingCarrier($shipping_carrier);
        ////Acceptable values are ups, fedex, dhl, or usps
        //$transaction->update();
        $this->transaction->update($transaction_id, array());

        $result = $this->transaction->execute();

        /*
          Result is returned as an array in the format of...
          $result = Array
          (
          [response] => 1
          [responsetext] => SUCCESS
          [authcode] => 123456
          [transactionid] => 1087714082
          [avsresponse] => Y
          [cvvresponse] => M
          [orderid] =>
          [type] => sale
          [response_code] => 100
          )
         *
         */

        switch ($result['response'])
        {
            case 1:
                $result['transaction_state'] = 'Approved';
                break;
            case 2:
                $result['transaction_state'] = 'Declined';
                break;
            default;
                $result['transaction_state'] = 'System Error';
        }
        return $result;
    }

    public function capture($detailsArr)
    {
        //Transaction ID from previous Auth
        $transaction_id = $detailsArr['transaction_id'];
        //$transaction = new nmiDirectPost;
        $amount = $detailsArr['amount'];

        $this->transaction->capture($transaction_id, $amount);
        Helper::debugPrint($this->transaction, 'capture transaction');
        $result = $this->transaction->execute();
        Helper::debugPrint($result, 'capture response');

        /*
          Result is returned as an array in the format of...
          $result = Array
          (
          [response] => 1
          [responsetext] => SUCCESS
          [authcode] => 123456
          [transactionid] => 1087714082
          [avsresponse] => Y
          [cvvresponse] => M
          [orderid] =>
          [type] => sale
          [response_code] => 100
          )
         *
         */

        switch ($result['response'])
        {
            case 1:
                $result['transaction_state'] = 'Approved';
                break;
            case 2:
                $result['transaction_state'] = 'Declined';
                break;
            default;
                $result['transaction_state'] = 'System Error';
        }
        return $result;
    }

    public function addCustomer()
    {
        $CcNumber = $this->params['cardNumber']; 
        $CcExp = $this->params['cardExpiryMonth'] . $this->params['cardExpiryYear']; 
        $Cvv = $this->params['cvv']; 
        $FirstName = $this->params['firstName'];
        $LastName = $this->params['lastName'];
        $Address1 = $this->params['shippingAddress1'];
        $City = $this->params['shippingCity'];
        $State = $this->params['shippingState'];
        $Zip = $this->params['lastName'];
        $Country = $this->params['shippingCountry'];
        $Phone = $this->params['phone'];
        $Email = $this->params['email'];

        $vault = $this->customer;
        $vault->setCcNumber($CcNumber);
        $vault->setCcExp($CcExp);
        $vault->setCvv($Cvv);
        $vault->setFirstName($FirstName);
        $vault->setLastName($LastName);
        $vault->setAddress1($Address1);
        $vault->setCity($City);
        $vault->setState($State);
        $vault->setZip($Zip);
        $vault->setCountry($Country);
        $vault->setPhone($Phone);
        $vault->setEmail($Email);
        $vault->add();
        if ($this->makeHttpRequest($this->customer) === false)
        {
            return false;
        }
        return true;
    }

    public function deleteCustomer($customer_vault_id)
    {
        $vault = $this->customer;
        $vault->setCustomerVaultId($customer_vault_id);
        $vault->delete();
        $result = $vault->execute();
        return $result;
    }

    public function chargeCustomer()
    {
        $this->params = array_replace($this->params, CrmPayload::all());
        $vault = $this->customer;
        $customer_vault_id = $this->params['customerId'];
        $this->prepareProductDetails();
        $amount = $this->params['productTotal'] + $this->params['shippingTotal'];
        $vault->setCustomerVaultId($customer_vault_id);
        $vault->charge($amount);
        if ($this->makeHttpRequest($this->customer) === false)
        {
            CrmResponse::replace(array(
                'success' => false,
                'orderId' => null,
                'rawPayload' => json_encode(Http::getPayload()),
                'rawResponse' => json_encode(Http::getResponse())
            ));
            return false;
        }

        CrmResponse::replace(array(
            'success' => true,
            'orderId' => $this->params['previousOrderId'],
            'customerId' => $this->response['customer_vault_id'],
            'rawPayload' => json_encode(Http::getPayload()),
            'rawResponse' => json_encode(Http::getResponse())
        ));
        return true;
    }

    private function prepareProductDetails()
    {
        $productTotal = 0.00;
        $shippingTotal = 0.00;
        foreach ($this->params['products'] as $i => $product)
        {
            $productTotal = $productTotal + ($product['productPrice'] * $product['productQuantity']);
            $shippingTotal = $shippingTotal + $product['shippingPrice'];
            if (!empty($product['nmi_plan_id']))
            {
                $this->params['nmiPlanId'] = $product['nmi_plan_id'];
            }
        }
        $this->params['productTotal'] = $productTotal;
        $this->params['shippingTotal'] = $shippingTotal;
        return;
    }

}
