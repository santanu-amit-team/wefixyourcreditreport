<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\CrmPayload;
use Application\Session;
use Application\Response;
use Application\Request;
use Application\CrmResponse;

class CustomDeclineMsg
{

    public function __construct()
    {
        $this->currentStepId = (int) Session::get('steps.current.id');
        $this->currentPageType = Session::get('steps.current.pageType');
        $this->customerId = Session::get('steps.1.customerId');
        $this->enable = Config::extensionsConfig('CbUtilityPackage.enable_custom_decline_msg');
        $this->declineMsg = Config::extensionsConfig('CbUtilityPackage.custom_decline_msg');
    }

    public function updateDeclineReason()
    {
        if (!$this->enable)
        {
            return;
        }


        $declineMsg = $this->getMessage();
    }

    private function getMessage()
    {
        $response = CrmResponse::all();
        
        $mappedDeclineMsg = '';
        
        $declineMessages = $this->declineMsg;

        if (!empty($declineMessages) && !empty($response['errors']['crmError']) && !$response['success'])
        {
            $errorMsg = $response['errors']['crmError'];
            foreach ($declineMessages as $value)
            {
                $crmDeclineMsg = $value['crm_msg'];
                if (preg_match("/$errorMsg/i", $crmDeclineMsg) ||
                        preg_match("/$crmDeclineMsg/i", $errorMsg))
                {
                    $mappedDeclineMsg = $value['custom_msg'];
                    break;
                }
            }
            if (!empty($mappedDeclineMsg))
            {
                Response::send(array(
                    'success' => false,
                    'errors' => array(
                        'crmError' => $mappedDeclineMsg
                    )
                ));
            }
        }
    }

}
