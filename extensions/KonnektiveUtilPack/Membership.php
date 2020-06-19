<?php

namespace Extension\KonnektiveUtilPack;

use Application\Config;
use Application\Session;
use Application\Model\Configuration;
use Application\Http;
use Application\Registry;

class Membership extends Common
{

    protected $curlResponse,$apiEndPoint, $curlPostData = array();

    public function __construct()
    {
        $this->pageType = Session::get('steps.current.pageType');
        $this->crmType = Session::get('crmType');
        $this->activate = Config::extensionsConfig('KonnektiveUtilPack.membership_service');
    }

    public function orderUpdateRecurring($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive' ||
                (empty($params['orderId'])))
        {
            return;
        }

        $purchaseInfo = $this->queryPurchase($params);
        $purchaseId = $this->getPurchaseID($purchaseInfo,$params['productId']);
        
        if(empty($purchaseId))
        {
            return;
        }
        $this->curlPostData['purchaseId'] = $purchaseId;
        $this->curlPostData['nextBillDate'] = $params['nextDate'];
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/purchase/update/";        

        return $this->callAPI();
    }
    
    public function queryPurchase($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive' ||
                (empty($params['orderId'])))
        {
            return;
        }

        $this->curlPostData['orderId'] = $params['orderId'];
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/purchase/query/";

        return $this->callAPI();
    }
    
    public function queryOrder($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (empty($params['orderId']))
        {
            return;
        }

        $this->curlPostData['orderId'] = $params['orderId'];
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/order/query/";
        $configID = !empty($params['configID'])?$params['configID']:'';
        return $this->callAPI($configID);
    }

    private function callAPI($configID = null)
    {
        if(empty($configID))
        {
            $prevConfigId = Session::get('steps.previous.configId');

            if (!empty($prevConfigId))
            {
                $configId = $prevConfigId;
            }
            else
            {
                $configId = Session::get('steps.current.configId');
            }
        }
        else
        {
            $configId = $configID;
        }
        
        $this->configuration = new Configuration($configId);

        $crmInfo = $this->configuration->getCrm();

        $this->curlPostData['loginId'] = $crmInfo['username'];
        $this->curlPostData['password'] = $crmInfo['password'];
        $this->curlResponse = Http::post($this->apiEndPoint, http_build_query($this->curlPostData));
        return json_decode($this->curlResponse,true);
    }
    
    private function getPurchaseID($purchaseData,$productID)
    {
        $purchaseId = false;
        
        if (!empty($purchaseData['message']['data']))
        {
            foreach ($purchaseData['message']['data'] as $purchase)
            {
                if ($purchase['productId'] == $productID)
                {
                    $purchaseId = $purchase['purchaseId'];
                    break;
                }
            }
        }
        return $purchaseId;
    }

    public function queryTransaction($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive' ||
                (empty($params['customerId'])))
        {
            return;
        }

        $this->curlPostData['orderId'] = $params['orderId'];
        $this->curlPostData['customerId'] = $params['customerId'];
        $configID = !empty($params['configID'])?$params['configID']:'';
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/transactions/query/";

        return $this->callAPI($configID);
    }
    
    public function refundPurchase($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (empty($params['purchaseId']))
        {
            return;
        }

        $this->curlPostData['purchaseId'] = $params['purchaseId'];
        if(!empty($params['refundAmount']))
        {
            $this->curlPostData['refundAmount'] = $params['refundAmount'];
        }
        else
        {
            $this->curlPostData['fullRefund'] = true;
        }
        
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/purchase/refund/";
        $configID = !empty($params['configID'])?$params['configID']:'';
        return $this->callAPI($configID);
    }
    
    
    
    public function refundTransaction($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (empty($params['transactionId']))
        {
            return;
        }

        $this->curlPostData['transactionId'] = $params['transactionId'];
        if(!empty($params['refundAmount']))
        {
            $this->curlPostData['refundAmount'] = $params['refundAmount'];
        }
        else
        {
            $this->curlPostData['fullRefund'] = true;
        }
        
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/transactions/refund/";
        $configID = !empty($params['configID'])?$params['configID']:'';
        return $this->callAPI($configID);
    }
    
    public function customerUpdate($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }
        
        if (empty($params['customerId']))
        {
            return;
        }

        $this->curlPostData = $params;
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/customer/update/";
        $configID = !empty($params['configID'])?$params['configID']:'';
        return $this->callAPI($configID);
    }
    
    public function addCustomerNote($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }
        
        if (empty($params['customerId']))
        {
            return;
        }

        $this->curlPostData = $params;
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/customer/addnote/";
        $configID = !empty($params['configID'])?$params['configID']:'';
        return $this->callAPI($configID);
    }
    
    public function queryCampaign($params = array())
    {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive' ||
                (empty($params['campaignId'])))
        {
            return;
        }
        
        $configID = !empty($params['configID'])?$params['configID']:'';
        $this->curlPostData['campaignId'] = $params['campaignId'];        
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/campaign/query/";

        return $this->callAPI($configID);
    }

}
