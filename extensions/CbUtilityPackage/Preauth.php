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
use Application\Config;

class Preauth extends Common
{
    public function __construct(){
        parent::__construct();
    }

    public function verify()
    {
        $enablePreauthRegular = Config::extensionsConfig('CbUtilityPackage.enable_preauth_regular');
        $enablePreauthSteps = Config::extensionsConfig('CbUtilityPackage.allow_preauth_steps');
        $allowedSteps = explode(',', $enablePreauthSteps);

        if (
                Request::attributes()->get('action') === 'prospect' ||
                !in_array($this->currentStepId, $allowedSteps) ||
                empty($enablePreauthRegular)
        )
        {
            return;
        }
        $enableRetryPreauthRegular = Config::extensionsConfig('CbUtilityPackage.enable_retry_preauth_regular');
        $configuration = new Configuration(Session::get('steps.current.configId'));
        $crmId = $configuration->getCrmId();
        $crm = $configuration->getCrm();
        $crmType = $crm['crm_type'];
        $crmClass = sprintf(
                '\Application\Model\%s', ucfirst($crmType)
        );
        $crmInstance = new $crmClass($crmId);
        //CrmPayload::set('meta.bypassCrmHooks', true);
        $preauthRegularPrice = Config::extensionsConfig('CbUtilityPackage.preauth_default_amount_regular');
        if (!empty($preauthRegularPrice))
        {
            CrmPayload::set('authorizationAmount', $preauthRegularPrice);
        }
        else
        {
            CrmPayload::set('authorizationAmount', CrmPayload::get('products')['0']['rebillProductPrice']);
        }

        call_user_func_array(array($crmInstance, 'preAuthorization'), array());
        $response = CrmResponse::all();

        Session::set('regular_pre_auth_response_' . $this->currentStepId, $response);
        CrmPayload::set('temp_customer_id', $response['customerId']);
        if (empty($response['success']))
        {
            $customMsg = Config::extensionsConfig('CbUtilityPackage.custom_preauth_message');
            if (!empty($customMsg))
            {
                $response['errors']['crmError'] = Config::extensionsConfig('CbUtilityPackage.custom_preauth_message');
            }


            if (!empty($enableRetryPreauthRegular))
            {

                $retryCamp = Config::extensionsConfig('CbUtilityPackage.preauth_regular_campaign_id');
                $campaignInfo = Campaign::find($retryCamp, true);
                CrmPayload::set('products', $campaignInfo);
                //CrmPayload::set('meta.bypassCrmHooks', true);
                CrmPayload::set('authorizationAmount', CrmPayload::get('products')['0']['rebillProductPrice']);
                call_user_func_array(array($crmInstance, 'preAuthorization'), array());
                $response = CrmResponse::all();
                if (empty($response['success']))
                {
                    CrmPayload::update(array(
                        'meta.bypassCrmHooks' => true,
                        'meta.terminateCrmRequest' => true,
                    ));

                    if (!empty($customMsg))
                    {
                        $response['errors']['crmError'] = Config::extensionsConfig('CbUtilityPackage.custom_preauth_message');
                    }
                    CrmResponse::replace($response);
                }
            }
            else
            {
                CrmPayload::update(array(
                    'meta.bypassCrmHooks' => true,
                    'meta.terminateCrmRequest' => true,
                ));

                CrmResponse::replace($response);
            }
        }
    }

}
