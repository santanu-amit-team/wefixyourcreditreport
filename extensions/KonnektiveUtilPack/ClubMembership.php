<?php

namespace Extension\KonnektiveUtilPack;

use Application\Config;
use Application\Session;
use Application\Model\Configuration;
use Application\Http;
use Application\Registry;

class ClubMembership extends Common
{

    protected $curlResponse,$apiEndPoint, $curlPostData = array();

    public function __construct()
    {
        $this->pageType = Session::get('steps.current.pageType');
        $this->crmType = Session::get('crmType');
        $this->activate = Config::extensionsConfig('KonnektiveUtilPack.club_membership_service');
    }

    public function queryClubMembers($params = array())
    {
        if(!is_array($params)) {
            return 'Arguments must be an array, other type provide inside queryClubMembers().';
        }
        if(
            !(array_key_exists('customerId', $params) && isset($params['customerId']))
            && 
            !(array_key_exists('clubId', $params) && isset($params['clubId']))
        ) {
            return 'Required field is missing. Please provide customerId and clubId';
        }

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive')
        {
            return;
        }

        foreach($params as $key => $value) {
            $this->curlPostData[$key] = $value;
        }
        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "members/query/";        

        return $this->callAPI();
    }

    public function cancelClubMember($params = array())
    {
        if(!is_array($params)) {
            return 'Arguments must be an array, other type provide inside cancelClubMember().';
        }

        if(!(array_key_exists('clubId', $params) && isset($params['clubId'])) && !(array_key_exists('memberId', $params) && isset($params['memberId']))) {
            return 'Required field is missing. Please provide clubId and memberId';
        }


        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive')
        {
            return;
        }

        foreach($params as $key => $value) {
            $this->curlPostData[$key] = $value;
        }

        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "members/cancel/";        

        return $this->callAPI();
    }

    public function reactivateClubMembership($params = array())
    {
        if(!is_array($params)) {
            return 'Arguments must be an array, other type provide inside cancelClubMember().';
        }

        if(!(array_key_exists('clubId', $params) && isset($params['clubId'])) && !(array_key_exists('memberId', $params) && isset($params['memberId']))) {
            return 'Required field is missing. Please provide clubId and memberId';
        }


        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive')
        {
            return;
        }

        foreach($params as $key => $value) {
            $this->curlPostData[$key] = $value;
        }

        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "members/reactivate/";        

        return $this->callAPI();
    }

    public function updateClubMembers($params = array())
    {
        if(!is_array($params)) {
            return 'Arguments must be an array, other type provide inside cancelClubMember().';
        }

        if(!(array_key_exists('clubId', $params) && isset($params['clubId'])) && !(array_key_exists('memberId', $params) && isset($params['memberId']))) {
            return 'Required field is missing. Please provide clubId and memberId';
        }

        if (!$this->checkExtensionStatus('KonnektiveUtilPack') || !$this->activate)
        {
            return;
        }

        if (Session::get('crmType', 'unknown') !== 'konnektive')
        {
            return;
        }

        foreach($params as $key => $value) {
            $this->curlPostData[$key] = $value;
        }

        $this->apiEndPoint = Registry::system('systemConstants.KONNEKTIVE_API_BASE_URL') . "members/update/";        

        return $this->callAPI();
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
}
