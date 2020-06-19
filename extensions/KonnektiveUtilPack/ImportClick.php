<?php

namespace Extension\KonnektiveUtilPack;

use Application\CrmPayload;
use Application\CrmResponse;
use Application\Helper\Provider;
use Application\Model\Campaign;
use Application\Model\Configuration;
use Application\Model\Konnektive;
use Application\Request;
use Application\Response;
use Application\Session;
use Exception;
use Application\Config;

class ImportClick extends Common
{

    public function __construct()
    {
        parent::__construct();
        try {
            $configuration = new Configuration($this->currentConfigId);
            $this->crmId   = $configuration->getCrmId();
            $this->crmType = $configuration->getCrmType();
        } catch (Exception $ex) {
            $this->crmType = null;
        }
    }

    public function fire()
    {
        if (
            Session::get(sprintf(
                'extensions.konnektiveUtilPack.importClick.%s',
                Session::get('steps.current.pageType')
            )) === true
        ) {
            return;
        }

        $requestUri = sprintf(
            '%s%s', Request::getOfferUrl(), Session::get('steps.current.link')
        );
        $queryParams = Session::get('queryParams', array());
        if (is_array($queryParams) && !empty($queryParams)) {
            $requestUri = sprintf(
                '%s?%s', $requestUri, http_build_query($queryParams)
            );
        }
        
        $enable_default_param = Config::extensionsConfig('KonnektiveUtilPack.enable_default_param');
        $defaultKeyValues = Config::extensionsConfig('KonnektiveUtilPack.default_params');
        if(!empty($enable_default_param) && !empty($defaultKeyValues)){
            $selectedDefaultParams = array();
            foreach ($defaultKeyValues as $key => $value) {
                if(!in_array(strtolower($value['default_param_key']), 
                        array_map('strtolower', array_keys($queryParams)))
                    ){
                    $selectedDefaultParams[$value['default_param_key']] = $value['default_param_value'];
                }
            }
            $requestUri = sprintf(
                '%s%s', Request::getOfferUrl(), Session::get('steps.current.link')
                );
            if(!empty($selectedDefaultParams)){
                $requestUri = sprintf(
                    '%s?%s', $requestUri, http_build_query($selectedDefaultParams)
                );
                if(!empty($queryParams)){
                    $requestUri = $requestUri.'&'.http_build_query($queryParams);
                }
            }else if(!empty($queryParams)){
                $requestUri = $requestUri.'?'.http_build_query($queryParams);
            }
        }
        
        
        $clientIp = Request::getClientIp();
        if(filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $this->convertIp) {
            $clientIp = $this->convertIp($clientIp);
        }
        
        CrmPayload::replace(array(
            'pageType'            => Session::get('steps.current.pageType'),
            'requestUri'          => $requestUri,
            'ipAddress'           => !empty($clientIp) ? $clientIp : Request::getClientIp(),
            'userAgent'           => Request::headers()->get('HTTP_USER_AGENT'),
            'httpReferer'         => Request::headers()->get('HTTP_REFERER'),
            'acceptHeader'        => Request::headers()->get('HTTP_ACCEPT'),
            'meta.bypassCrmHooks' => true,
        ));
        
        if (Session::has('extensions.konnektiveUtilPack.importClick.sessionId')) {
            CrmPayload::set('sessionId', Session::get(
                'extensions.konnektiveUtilPack.importClick.sessionId')
            );
        } else {
            try {
                $configuration = new Configuration();
                $campaignIds   = $configuration->getCampaignIds();
                $campaign      = Campaign::find($campaignIds[0], true);
                if (empty($campaign[0]['campaignId'])) {
                    throw new Exception('Campaign Id not found!');
                }
                CrmPayload::set('campaignId', $campaign[0]['campaignId']);
            } catch (Exception $ex) {
                CrmPayload::set('campaignId', 0);
            }
        }
        
        if(empty($this->crmId))
        {
            return;
        }

        $crmInstance = new Konnektive($this->crmId);
        $crmInstance->importClick();

        if (CrmResponse::get('success') !== true) {
            Response::send(CrmResponse::all());
        }

        Session::set(
            'extensions.konnektiveUtilPack.importClick.sessionId',
            CrmResponse::get('sessionId')
        );

        Session::set(sprintf(
            'extensions.konnektiveUtilPack.importClick.%s',
            Session::get('steps.current.pageType')
        ), true);

        Response::send(CrmResponse::all());

    }

    public function injectScript()
    {

        if (
            Session::has(sprintf(
                'extensions.konnektiveUtilPack.importClick.%s',
                Session::get('steps.current.pageType')
            )) || $this->crmType !== 'konnektive'
        ) {
            return;
        }

        Session::set(sprintf(
            'extensions.konnektiveUtilPack.importClick.%s',
            Session::get('steps.current.pageType')
        ), false);

        echo Provider::asyncScript(
            AJAX_PATH . 'extensions/konnektiveutilpack/fire-import-click'
        );
    }

    public function addSessionId()
    {
        if (
            Session::get('steps.meta.isScrapFlow') === true ||
            (
                (CrmPayload::get('meta.isSplitOrder') || CrmPayload::get('meta.isUpsellStep')) && 
                CrmPayload::get('meta.crmMethod') == 'newOrder'
            )
            || Session::get('isSessionSkip.'.Session::get('steps.current.id')) 
        ) {
            CrmPayload::remove('sessionId');
            return;
        }

        if(
                Session::get('steps.meta.isPrepaidFlow') === true &&
                !CrmPayload::get('meta.isUpsellStep'))
        {
            CrmPayload::remove('sessionId');
            return;
        }

        
        if (Session::has(
            'extensions.konnektiveUtilPack.importClick.sessionId'
        )) {
            CrmPayload::set('sessionId', Session::get(
                'extensions.konnektiveUtilPack.importClick.sessionId'
            ));
        }
    }

    public function switchMethod()
    {
        
        if(
                Session::get('steps.meta.isPrepaidFlow') === true &&
                !CrmPayload::get('meta.isSplitOrder') &&
                !CrmPayload::get('meta.isUpsellStep'))
        {
            CrmPayload::set('meta.crmMethod', 'newOrder');
        }
    }

}
