<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\Request;
use Application\Model\Campaign;
use Application\CrmPayload;
use Application\Model\Configuration;
use Application\Session;
use Application\CrmResponse;

class ReprocessOrders extends Common
{
    public function __construct()
    {
        parent::__construct();
        $this->crmType = Session::get('crmType');
        $this->configId = Session::get('steps.current.configId');
        try
        {
            $this->configuration = new Configuration($this->configId);
            $this->crm = $this->configuration->getCrm();
        }
        catch (Exception $ex)
        {
            
        }
    }
    
    public function reprocessOrders()
    {
        if (
            !Config::extensionsConfig('CbUtilityPackage.reprocess_decline_orders') ||
            CrmPayload::get('meta.isSplitOrder') === true ||
            Request::attributes()->get('action') === 'prospect'
        ) {
            return;
        }
        
        $response = CrmResponse::all();
        
        if(!empty($response['success'])) {
            return;
        }
        
        if(
        	preg_match("/Prepaid.+Not Accepted/i", $response['errors']['crmError']) &&
        	!empty($response['errors']['crmError'])
    	) {
        	return;
    	}
        
        $configurations = Config::extensionsConfig(
            'CbUtilityPackage.reprocessing_configuration'
        );

        $configurationsArray = preg_split("/\\r\\n|\\r|\\n/", $configurations);
        
        foreach ($configurationsArray as $key => $value)
        {
            $configurationsDetails = explode('|', $value);
            if($this->currentStepId == $configurationsDetails[1]) {
                $cbCampaignId = $configurationsDetails[0];
                $campaignInfo = Campaign::find($cbCampaignId, true);
                CrmPayload::set('products', $campaignInfo);
                CrmPayload::set('campaignId', $campaignInfo[0]['campaignId']);
                break;
            }
        }
        
        $crmType = $this->crm['crm_type'];
        $crmClass = sprintf(
                '\Application\Model\%s', $crmType
        );

        $crmInstance = new $crmClass($this->configuration->getCrmId());
        call_user_func_array(array($crmInstance, CrmPayload::get('meta.crmMethod')), array());
        
    }

}
