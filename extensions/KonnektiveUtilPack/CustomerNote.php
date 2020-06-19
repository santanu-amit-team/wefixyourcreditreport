<?php

namespace Extension\KonnektiveUtilPack;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Helper\Provider;
use Application\Model\Configuration;
use Application\Model\Konnektive;
use Application\Response;
use Application\Session;
use Application\Config;
use Application\Http;
use Exception;

class CustomerNote extends Common
{
    public function __construct()
    {
        parent::__construct();
        try {            
            $this->pageType = Session::get('steps.current.pageType');
            $this->fireOnProspect = Config::extensionsConfig('KonnektiveUtilPack.note_for_prospect');
            $this->skip_note = Config::extensionsConfig('KonnektiveUtilPack.skip_note');
            $this->remove_source_url = Config::extensionsConfig('KonnektiveUtilPack.remove_source_url');
            $this->fireOnOrder = Config::extensionsConfig('KonnektiveUtilPack.note_for_order');
            $configuration = new Configuration($this->previousConfigId);
            $this->crmId   = $configuration->getCrmId();
            $this->crmType = $configuration->getCrmType();
        } catch (Exception $ex) {
            $this->crmType = null;
        }
    }

    public function savePayload()
    {
        if($this->skip_note)
        {
            return;
        }
        if($this->fireOnProspect && $this->pageType == 'leadPage')
        {
            $rawResponse = json_decode(Http::getResponse());
            if(!empty($rawResponse->message->customerId))
            {
                CrmResponse::set('customerId', $rawResponse->message->customerId);
            }
        }
        
        if (
            CrmPayload::get('meta.crmType') === 'konnektive' &&
            CrmResponse::get('success') &&
            CrmResponse::has('customerId')
        ) {

            $message = sprintf(
                '%s | %s', CrmPayload::get('userIsAt'), CrmPayload::get('userAgent')
            );

            if($this->remove_source_url) {

                $message = sprintf(
                    '%s', CrmPayload::get('userAgent')
                );
            }
            Session::set(
                'extensions.konnektiveUtilPack.customerNote.crmPayload',
                array(
                    'customerId' => CrmResponse::get('customerId'),
                    'message'    => $message,
                )
            );
            
            if($this->fireOnProspect && $this->pageType == 'leadPage')
            {
                Session::set(sprintf(
                    'extensions.konnektiveUtilPack.customerNote.%d',
                    $this->previousStepId
                ), true);
            }
            
        }
    }

    public function send()
    {
        if (
            !Session::has(sprintf('steps.%d.orderId', $this->previousStepId)) ||
            Session::get(sprintf(
                'extensions.konnektiveUtilPack.customerNote.%d',
                $this->previousStepId
            )) === true
        ) {
            if($this->fireOnProspect && $this->pageType != 'checkoutPage')
            {
                return;
            } 
        }

        CrmPayload::replace(Session::get(
            'extensions.konnektiveUtilPack.customerNote.crmPayload'
        ));

        CrmPayload::set('meta.bypassCrmHooks', true);

        $crmInstance = new Konnektive($this->crmId);
        $crmInstance->addCustomerNote();

        Session::set(sprintf(
            'extensions.konnektiveUtilPack.customerNote.%d',
            $this->previousStepId
        ), true);

        Session::remove('extensions.konnektiveUtilPack.customerNote.crmPayload');
        
        if($this->fireOnProspect && $this->pageType == 'checkoutPage')
        {
            Session::remove(sprintf(
                'extensions.konnektiveUtilPack.customerNote.%d',
                $this->previousStepId
            ));
            Session::set('extensions.konnektiveUtilPack.customerNote.prospect',true);
        }   

        Response::send(CrmResponse::all());

    }

    public function injectScript()
    {
        if (
            !Session::has('extensions.konnektiveUtilPack.customerNote.crmPayload') ||
            !Session::has(sprintf('steps.%d.orderId', $this->previousStepId)) ||
            Session::has(sprintf(
                'extensions.konnektiveUtilPack.customerNote.%d',
                $this->previousStepId
            )) || $this->crmType !== 'konnektive'
        ) {
            if($this->fireOnProspect && $this->pageType != 'checkoutPage')
            {
                return;
            } 
            if(!$this->fireOnProspect)
            {
                return;
            } 
        }

        if($this->pageType != 'checkoutPage' && !$this->fireOnOrder) {
            return;
        }
        
        Session::set(sprintf(
            'extensions.konnektiveUtilPack.customerNote.%d',
            $this->previousStepId
        ), false);

        echo Provider::asyncScript(
            AJAX_PATH . 'extensions/konnektiveutilpack/send-customer-note'
        );
    }
}
