<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\Session;
use Application\CrmResponse;
use Application\Request;
use Application\Response;
use Application\Registry;
use Application\Http;
use Application\Model\Configuration;
use Exception;

class OrderDecline
{
    protected $declineText = 'Your order has been declined! Please try again later.';
    protected $ip;
    
    public function __construct()
    {
        $this->ip = Request::getClientIp();
        $this->currentStepId = (int) Session::get('steps.current.id');
        $this->configId      = (int) Session::get('steps.current.configId');
        $this->currentPageType = Session::get('steps.current.pageType');
        try{
            $this->configuration = new Configuration($this->configId);
        } catch (Exception $ex) {

        }
    }
    public function checkDecline()
    {
        if (
            Request::attributes()->get('action') === 'prospect' || $this->currentStepId > 1
        ) {
            return;
        }
        $orderDeclineLimit = Config::extensionsConfig('CbUtilityPackage.order_decline_limit');
        $orderDeclineMessage = Config::extensionsConfig('CbUtilityPackage.order_decline_message');
        $currentDeclineCount = Session::get('declined_order_count');
        if(!empty($currentDeclineCount) && !empty($orderDeclineLimit) && $currentDeclineCount>=$orderDeclineLimit)
        {
            Response::send(array(
                'success' => false,
                'errors' => array(
                    'binerror' => !empty($orderDeclineMessage)?$orderDeclineMessage:$this->declineText
                )
            ));
        }
    }
    public function increaseDeclineCount()
    {
        if (
            Request::attributes()->get('action') === 'prospect' || $this->currentStepId > 1
        ) {
            return;
        }
        $response = CrmResponse::all();
        $orderDeclineLimit = Config::extensionsConfig('CbUtilityPackage.order_decline_limit');
        if(!$response['success'] && !empty($response['errors']) && !empty($orderDeclineLimit))
        {
            $currentDeclineCount = Session::has('declined_order_count')?Session::get('declined_order_count'):0;          
            Session::set('declined_order_count',$currentDeclineCount+1);
            Response::send(array(
                'success' => false,
                'errors' => array(
                    'crmError' => $response['errors']['crmError']
                )
            ));
        } 
        return;
        
    }
    
    public function checkTimeBasedDecline()
    {
        $enableTimeBasedBlock = Config::extensionsConfig('CbUtilityPackage.enable_time_based_user_block');
        $blockType = Config::extensionsConfig('CbUtilityPackage.time_based_block_type');
        if (
            (Request::attributes()->get('action') != 'prospect' && 
                Request::attributes()->get('action') != 'downsell') 
                || 
                !$enableTimeBasedBlock) {
            return;
        }
        
        $blockMsg = Config::extensionsConfig('CbUtilityPackage.time_based_block_msg');
        
        $params = Request::form()->all();
        $params['blockTime'] = Config::extensionsConfig('CbUtilityPackage.block_time');
        
        $crmClass = '\Extension\CbUtilityPackage\OrderDecline';
        $crmInstance = new $crmClass; 
        $data = call_user_func_array(array($crmInstance, 'getOrdersFor'.ucfirst(Session::get('crmType'))), array($params, $blockType));
        
        if($data)
        {
            Response::send(array(
                'success' => false,
                'errors' => array(
                    'binerror' => !empty($blockMsg) ? $blockMsg : $this->declineText
                )
            ));
        }
    }    
    
    public function checkTimeBasedDeclineAdvanced()
    {
        $enableTimeBasedBlock = Config::extensionsConfig('CbUtilityPackage.enable_time_based_user_block');
        $blockType = Config::extensionsConfig('CbUtilityPackage.time_based_block_type');
        
        if (
            ($this->currentPageType != 'checkoutPage' && 
                Request::attributes()->get('action') != 'downsell') 
                || 
                !$enableTimeBasedBlock
                ||
                !in_array('CREDITCARD', $blockType)
                || 
                Session::get('crmType') != 'limelight'
            ) {
            return;
        }
        
        $blockMsg = Config::extensionsConfig('CbUtilityPackage.time_based_block_msg');
        
        $params = Request::form()->all();
        $params['blockTime'] = Config::extensionsConfig('CbUtilityPackage.block_time');
        
        $crmClass = '\Extension\CbUtilityPackage\OrderDecline';
        $crmInstance = new $crmClass; 
        $data = call_user_func_array(array($crmInstance, 'getOrdersFor'.ucfirst(Session::get('crmType'))), array($params, 'CREDITCARD'));
        
        if($data)
        {
            Response::send(array(
                'success' => false,
                'errors' => array(
                    'binerror' => !empty($blockMsg) ? $blockMsg : $this->declineText
                )
            ));
        }
    }    
    
    public function getOrdersForKonnektive($params, $blockType = false)
    {
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "/order/query/";
        $configBlockTypes = Config::extensionsConfig('CbUtilityPackage');
        $this->curlPostData['startDate'] = date('m/d/Y', strtotime('-' . $params['blockTime'] . ' Hours'));
        $this->curlPostData['endDate'] = date('m/d/Y');
        $this->curlPostData['orderStatus'] = 'COMPLETE';

        $crmInfo = $this->configuration->getCrm();
        $this->curlPostData['loginId'] = $crmInfo['username'];
        $this->curlPostData['password'] = $crmInfo['password'];

        if (!empty($configBlockTypes['time_based_block_type']))
        {
            if (in_array(strtoupper('ipAddress'), $configBlockTypes['time_based_block_type']) &&
                    !$this->getInWhitelistDetails(
                        array('whitelist_ip' => $this->ip
                        )
                ))
            {
                $this->curlPostData['ipAddress'] = Request::getClientIp();
                $this->curlResponse = Http::post($this->apiEndPoint, http_build_query($this->curlPostData), array(), array('13' => 10));
                $data = json_decode($this->curlResponse, true);
                if (!empty($data['result']) && $data['result'] == 'SUCCESS')
                {
                    return true;
                }else{
                     unset($this->curlPostData['ipAddress']);
                }
            }
            if (in_array(strtoupper('email'), $configBlockTypes['time_based_block_type']) && 
                !$this->getInWhitelistDetails(
                        array('whitelist_email' => $params['email']
                        )
                ))
            {
                $this->curlPostData['emailAddress'] = $params['email'];
                $this->curlResponse = Http::post($this->apiEndPoint, http_build_query($this->curlPostData), array(), array('13' => 10));
                $data = json_decode($this->curlResponse, true);
                if (!empty($data['result']) && $data['result'] == 'SUCCESS')
                {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function getOrdersForLimelight($params, $blockType)
    {
        $this->curlPostData['method'] = 'order_find';
        $this->curlPostData['campaign_id'] = 'all';
        $this->curlPostData['start_date'] = date('m/d/Y', strtotime('-' . $params['blockTime'] . ' Hours'));
        $this->curlPostData['end_date'] = date('m/d/Y');
        $this->curlPostData['search_type'] = 'all';
        $this->curlPostData['return_type'] = 'order_view';
        $crmInfo = $this->configuration->getCrm();
        $this->curlPostData['username'] = $crmInfo['username'];
        $this->curlPostData['password'] = $crmInfo['password'];
        $url = $crmInfo['endpoint'] . "/admin/membership.php";

        if ($blockType == 'CREDITCARD' && !$this->getInWhitelistDetails(
                    array('whitelist_card' => $params['creditCardNumber']
                    )
            ))
        {
            $creditCardNumber = $params['creditCardNumber'];
            $first_6_cc = substr($creditCardNumber, 0, 6);
            $last_4_cc = substr($creditCardNumber, -4);
            $criteria = "'first_6_cc=" . $first_6_cc . ",last_4_cc=" . $last_4_cc . ",approved,shipped'";
            $this->curlPostData['criteria'] = $criteria;
            $this->curlResponse = Http::post($url, http_build_query($this->curlPostData), array(), array('13' => 10));
            parse_str($this->curlResponse, $result);
            if (!empty($result['response_code']) && $result['response_code'] == '100')
            {
                return true;
            }
        }
        else if (is_array($blockType))
        {
            if (in_array(strtoupper('ipAddress'), $blockType) && !$this->getInWhitelistDetails(
                    array('whitelist_ip' => $this->ip
                    )
            ))
            {
                $this->curlPostData['criteria'] = "'ip_address=" . Request::getClientIp() . "'";
                $this->curlResponse = Http::post($url, http_build_query($this->curlPostData), array(), array('13' => 10));
                parse_str($this->curlResponse, $result);
                if (!empty($result['response_code']) && $result['response_code'] == '100')
                {
                    return true;
                }
            }
            if (in_array('EMAIL', $blockType) && $this->currentPageType == 'leadPage' && !$this->getInWhitelistDetails(
                    array('whitelist_email' => $params['email']
                    )
            ))
            {
                $criteria = "'email=" . $params['email'] . ",approved,shipped'";
                $this->curlPostData['criteria'] = $criteria;
                $this->curlResponse = Http::post($url, http_build_query($this->curlPostData), array(), array('13' => 10));
                parse_str($this->curlResponse, $result);
                if (!empty($result['response_code']) && $result['response_code'] == '100')
                {
                    return true;
                }
            }
            else if (in_array('EMAIL', $blockType) && Request::attributes()->get('action') != 'downsell' &&
                !$this->getInWhitelistDetails(
                        array('whitelist_email' => $params['email']
                        )
                ))
            {
                $criteria = "'email=" . $params['email'] . ",approved,shipped'";
                $this->curlPostData['criteria'] = $criteria;
                $this->curlResponse = Http::post($url, http_build_query($this->curlPostData), array(), array('13' => 10));
                parse_str($this->curlResponse, $result);
                if (!empty($result['response_code']) && $result['response_code'] == '100')
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
        else if (
                    !$this->getInWhitelistDetails(
                        array('whitelist_email' => $params['email']
                    )
                )
            )
        {
            $criteria = "'email=" . $params['email'] . ",approved,shipped'";
            $this->curlPostData['criteria'] = $criteria;
            $this->curlResponse = Http::post($url, http_build_query($this->curlPostData), array(), array('13' => 10));
            parse_str($this->curlResponse, $result);
            if (!empty($result['response_code']) && $result['response_code'] == '100')
            {
                return true;
            }
        }
        return false;
    }
    
    protected function getInWhitelistDetails($filterKeys = array())
    {
        if (empty($filterKeys))
        {
                return false;
        }
        $whiteList = array();
        $whiteList['whitelist_ip'] = preg_split("/\\r\\n|\\r|\\n/", Config::
                extensionsConfig('CbUtilityPackage.whitelist_ip'));
        $whiteList['whitelist_email'] = preg_split("/\\r\\n|\\r|\\n/", Config::
                extensionsConfig('CbUtilityPackage.whitelist_email'));
        $whiteList['whitelist_card'] = preg_split("/\\r\\n|\\r|\\n/", Config::
                extensionsConfig('CbUtilityPackage.whitelist_card'));

        foreach (array_keys($whiteList) as $key => $value)
        {
                if (empty($whiteList[$value][0]))
                {
                        $whiteList[$value] = false;
                }
        }
        foreach (array_keys($filterKeys) as $value)
        {
                if (!empty($whiteList[$value]) && in_array($filterKeys[$value], $whiteList[$value]))
                {
                        return true;
                }
        }

        return false;
    }
}
