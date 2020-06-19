<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\CrmPayload;
use Application\CrmResponse;
use Application\Session;
use Application\Model\Configuration;
use Application\Request;

class PreserveGateway extends Common
{
    public function __construct()
    {
        parent::__construct();
        $this->preserveGatewayUpsells = Config::extensionsConfig('CbUtilityPackage.preserve_gateway_upsells');
        $this->preserve_gateway       = Config::extensionsConfig('CbUtilityPackage.preserve_gateway');
        try{
            $this->configuration = new Configuration();
        }
        catch (Exception $ex){
            
        }
    }

    public function preserveUpsellGateways()
    {
        if (empty($this->preserveGatewayUpsells)) {
            return;
        }
        
        $gatewayId = '';
        
        foreach ($this->preserve_gateway as $value)
        {
            $preserveSteps = explode(',', $value['destination_step']);
            $gatewayId = Session::get('steps.'.$value['source_step'].'.gatewayId');
            if(!empty($gatewayId) && in_array($this->currentStepId, $preserveSteps))
            {
                break;
            }
        }
        
        if (!empty($gatewayId)) 
        {
            CrmPayload::set('forceGatewayId', $gatewayId);
            CrmPayload::set('preserveGateway', 1);
        }
        
    }
    
    public function getGatewayId() {
        $isPaypalFlow = Session::get('customer.cardType');

        if(
                Request::attributes()->get('action') == 'prospect' ||
                $this->configuration->getCrmType() != 'konnektive' ||
                $isPaypalFlow == 'paypal'
        ) {
            return;
        }
        
        $response = CrmResponse::all();
        
        if(empty($response['success']))
        {
            return;
        }
        
        
        CrmPayload::set('orderId', $response['orderId']);
        $crmClass = sprintf(
                '\Application\Model\%s', ucfirst($this->configuration->getCrmType())
        );
        $crmInstance = new $crmClass($this->configuration->getCrm()['id']);

        call_user_func(array($crmInstance, 'transactionQuery'));

        Session::set('gateway_info_payload', CrmPayload::all());
        Session::set('gateway_info_response', CrmResponse::all());

        if (CrmResponse::has('success') && CrmResponse::get('transactionInfo')) {
            $crmResponse = CrmResponse::all();
            foreach ($crmResponse as $key => $value) {
                if ($key === 'success') {
                    continue;
                }
                $gID = trim($crmResponse['transactionInfo']['data'][0]['merchantId']);
                foreach($crmResponse['transactionInfo']['data'] as $key => $value) {
                    if($value['responseType'] == 'SUCCESS') {
                        $gID = $value['merchantId'];
                        break;
                    }
                }
                Session::set('steps.'.$this->currentStepId.'.gatewayId', $gID);
                CrmResponse::replace($response);
            }
        }
    }

}