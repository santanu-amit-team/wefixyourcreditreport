<?php

namespace Extension\CbUtilityPackage;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Form\ProspectForm;
use Application\Model\Campaign;
use Application\Model\Configuration;
use Application\Model\LimeLight;
use Application\Request;
use Application\Response;
use Application\Session;

class PartialProspect extends Common
{
    public function __construct(){
        parent::__construct();
    }

    public function createProspect()
    {

        $prospectForm = new ProspectForm();
        $formPayload   = Request::form()->all();
        $configuration = new Configuration();
        if(!empty($formPayload['campaignId'])) {
            $campaign      = Campaign::find($formPayload['campaignId'], true);
        } else {
            $campaignIds   = $configuration->getCampaignIds();
            $campaign      = Campaign::find($campaignIds[0], true);
        }
        
        CrmPayload::replace(Session::get('customer', array()));
        
        $clientIp = Request::getClientIp();
        if(filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $this->convertIp) {
            $clientIp = $this->convertIp($clientIp);
        }

        CrmPayload::update(array(
            'campaignId'          => $campaign[0]['campaignId'],
            'affiliates'          => Session::get('affiliates'),
            'ipAddress'           => !empty($clientIp) ? $clientIp : Request::getClientIp(),
            'userAgent'           => Request::headers()->get('HTTP_USER_AGENT'),
            'affiliates'          => Session::get('affiliates', array()),
            'meta.bypassCrmHooks' => true,
        ));        
        
        $customParams = array('custom1','custom2','custom3','custom4','custom5');
        if(!empty($formPayload))
        {
            foreach ($customParams as $value)
            {
                if(isset($formPayload[$value]))
                {
                    CrmPayload::set($value, $formPayload[$value]);
                }
            }
            
            foreach ($formPayload as $key => $fvalue)
            {
                if (preg_match('/^custom_/', $key) && isset($formPayload[$key]))
                {
                    CrmPayload::set($key, $formPayload[$key]);
                }
            }
        }

        $crmClass  = sprintf(
            'Application\Model\%s', ucfirst($configuration->getCrmType())
        );

        $crmInstance = new $crmClass($configuration->getCrmId());
        $crmInstance->prospect();

        if (CrmResponse::has('success') && CrmResponse::get('success')) {
            $crmResponse = CrmResponse::all();
            foreach ($crmResponse as $key => $value) {
                if ($key === 'success') {
                    continue;
                }
                Session::set(
                    sprintf(
                        'steps.%d.%s', $this->currentStepId, $key
                    ), $value
                );
            }
        }

        CrmResponse::set('data', Request::form()->all());
        CrmResponse::set('redirect', Session::get('steps.next.link'));

        Response::send(CrmResponse::all());
    }
}
