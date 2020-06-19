<?php

namespace Extension\KonnektiveUtilPack;

use Application\Config;
use Application\CrmPayload;
use Application\Request;
use Application\Session;

class DirectDebit
{

    protected $curlPostData = array();

    public function __construct()
    {
        $this->pageType      = Session::get('steps.current.pageType');
        $this->currentStepId = (int) Session::get('steps.current.id');
        $this->directDebit = Config::extensionsConfig('KonnektiveUtilPack.direct_debit');
    }

    public function captureCrmPayload()
    {
        if(!$this->directDebit) {
            return;
        }

        $formData       = Request::form()->all();
        $creditCardType = !empty($formData['creditCardType']) ? $formData['creditCardType'] : Session::get('customer.cardType');

        if ($creditCardType != 'DIRECTDEBIT') {
            return;
        }

        if ($this->currentStepId == 1) {
            Session::set('extensions.KonnektiveUtilPack.iban', $formData['iban']);
            Session::set('extensions.KonnektiveUtilPack.ddbic', $formData['ddbic']);
            if (!empty($formData['accountHolder'])) {
                Session::set('extensions.KonnektiveUtilPack.accountHolder', $formData['accountHolder']);
            }
        }
        $accHolderData = Session::get('extensions.KonnektiveUtilPack.accountHolder');
        $accountHolder = !empty($accHolderData) ? $accHolderData : Session::get('customer.firstName') . ' ' . Session::get('customer.lastName');

        if (empty($accountHolder)) {
            $accountHolder = $formData['firstName'] . ' ' . $formData['lastName'];
        }

        CrmPayload::update(array(
            'iban'          => Session::get('extensions.KonnektiveUtilPack.iban'),
            'ddbic'         => Session::get('extensions.KonnektiveUtilPack.ddbic'),
            'accountHolder' => $accountHolder,
            'paySource'     => 'DIRECTDEBIT',
        ));
    }

}
