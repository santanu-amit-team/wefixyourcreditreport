<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\CrmPayload;
use Application\Session;
use Application\Response;
use Application\Request;

class UserBlock
{

    public function __construct()
    {
        $this->currentStepId = (int) Session::get('steps.current.id');
        $this->currentPageType = Session::get('steps.current.pageType');
        $this->customerId = Session::get('steps.1.customerId');
        $this->blockLimit = Config::extensionsConfig('CbUtilityPackage.block_limit');
        $this->isUserBlockEnable = Config::extensionsConfig('CbUtilityPackage.enable_session_based_uder_block');
        $this->blockMsg = Config::extensionsConfig('CbUtilityPackage.block_msg');
    }

    public function captureSessionCount()
    {
        if ($this->currentPageType == 'leadPage')
        {
            return;
        }

        $allowedMethod = array('newOrderWithProspect', 'newOrder', 'newOrderCardOnFile', 'importUpsell');
        $method = CrmPayload::get('meta.crmMethod');

        if ($this->currentStepId == 1)
        {
            $creditCardNumber = Request::form()->get('creditCardNumber');
        }

        if ($this->isUserBlockEnable && in_array($method, $allowedMethod))
        {

            if (Session::get('extensions.UserBlock.count_' . $this->currentStepId) >= $this->blockLimit)
            {
                Response::send(array(
                    'success' => false,
                    'errors' => array(
                        'usererror' => $this->blockMsg
                    )
                ));
            }

            $userBlockCount = Session::get('extensions.UserBlock.count_' . $this->currentStepId);
            $userPreviousCard = Session::get('extensions.UserBlock.previous_card');

            if (
                    (
                    empty($userBlockCount) &&
                    empty($userPreviousCard) ||
                    Session::get('extensions.UserBlock.previous_card') != $creditCardNumber
                    )
            )
            {
                Session::set('extensions.UserBlock.count_' . $this->currentStepId, 1);
                Session::set('extensions.UserBlock.previous_card', $creditCardNumber);
            }
            elseif (
                    Session::get('extensions.UserBlock.previous_card') == $creditCardNumber
            )
            {
                Session::set('extensions.UserBlock.count_' . $this->currentStepId, $userBlockCount + 1);
            }
            elseif (
                    $this->currentStepId > 1
            )
            {
                Session::set('extensions.UserBlock.count_' . $this->currentStepId, $userBlockCount + 1);
            }
        }
    }

}
