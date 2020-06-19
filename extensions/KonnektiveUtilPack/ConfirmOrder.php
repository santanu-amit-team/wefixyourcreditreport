<?php

namespace Extension\KonnektiveUtilPack;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Helper\Provider;
use Application\Model\Configuration;
use Application\Model\Konnektive;
use Application\Response;
use Application\Session;
use Exception;

class ConfirmOrder extends Common
{

    public function __construct()
    {
        parent::__construct();

        try {
            $configuration             = new Configuration();
            $this->currentUpsellMethod = $configuration->getUpsellPreferredMethod();
        } catch (Exception $ex) {
            $this->currentUpsellMethod = null;
        }

        try {
            $configuration = new Configuration($this->previousConfigId);
            $this->crmId   = $configuration->getCrmId();
            $this->crmType = $configuration->getCrmType();
        } catch (Exception $ex) {
            $this->crmType = null;
        }

    }

    public function fire()
    {
        if (
            !Session::has(sprintf('steps.%d.orderId', $this->previousStepId)) ||
            Session::get(sprintf(
                'extensions.konnektiveUtilPack.confirmOrder.%d',
                $this->previousStepId
            )) === true ||
            $this->currentUpsellMethod === 'importUpsell'
        ) {
            return;
        }

        CrmPayload::replace(array(
            'orderId'             => Session::get(
                sprintf('steps.%d.orderId', $this->previousStepId)
            ),
            'meta.bypassCrmHooks' => true,
        ));

        $crmInstance = new Konnektive($this->crmId);
        $crmInstance->confirmOrder();

        Session::set(sprintf(
            'extensions.konnektiveUtilPack.confirmOrder.%d',
            $this->previousStepId
        ), true);

        Response::send(CrmResponse::all());

    }

    public function injectScript()
    {
        if (
            !Session::has(sprintf('steps.%d.orderId', $this->previousStepId)) ||
            Session::has(sprintf(
                'extensions.konnektiveUtilPack.confirmOrder.%d',
                $this->previousStepId
            )) || $this->crmType !== 'konnektive'
        ) {
            return;
        }

        Session::set(sprintf(
            'extensions.konnektiveUtilPack.confirmOrder.%d',
            $this->previousStepId
        ), false);

        echo Provider::asyncScript(
            AJAX_PATH . 'extensions/konnektiveutilpack/fire-confirm-order'
        );
    }
}
