<?php

namespace Extension\KonnektiveUtilPack;

use Application\Config;
use Application\CrmPayload;
use Application\Request;
use Application\Session;

class ExtraSources extends Common
{

    public function addParams()
    {
        $extraSourceKeysString = Config::extensionsConfig(
            'KonnektiveUtilPack.extra_source_keys'
        );

        if (empty($extraSourceKeysString)) {
            return;
        }

        $extraSourceKeys = array_filter(array_map(function ($value) {
            return trim($value);
        }, explode(',', $extraSourceKeysString)));

        if (empty($extraSourceKeys) || !is_array($extraSourceKeys)) {
            return;
        }

        $extraAffiliateParams = array();
        foreach ($extraSourceKeys as $extraSourceKey) {
            $extraAffiliateParams[$extraSourceKey] = Request::query()
                ->get($extraSourceKey);
        }
        $extraAffiliateParams = array_filter($extraAffiliateParams);

        foreach ($extraAffiliateParams as $key => $value) {
            Session::set(sprintf('affiliates.%s', $key), $value);
        }
    }

    public function addDefineParams() {
        
        $extraSourceValue = Config::extensionsConfig('KonnektiveUtilPack.extra_source_value');
        $customSourceValue = Config::extensionsConfig('KonnektiveUtilPack.custom_source_value');

        $final = array_combine(['AFFID', 'AFID', 'SID', 'C1', 'C2', 'C3', 'C4', 'C5'], array_merge($extraSourceValue, $customSourceValue));
        
        foreach (array_filter($final) as $key => $value) {
            Session::set(sprintf('affiliates.%s', $key), $value);
        }
    }

    public function pushParams()
    {
        $affiliates = CrmPayload::get('affiliates');

        if (empty($affiliates) || !is_array($affiliates)) {
            $affiliates = array();
        }

        unset(
            $affiliates['affId'], $affiliates['c1'], $affiliates['c2'],
            $affiliates['c3'], $affiliates['c4'], $affiliates['c5']
        );

        $affiliates = array_filter($affiliates);

        foreach ($affiliates as $key => $value) {
            CrmPayload::set($key, $value);
        }
        
        $insureshipService = Config::extensionsConfig('KonnektiveUtilPack.insureship_service');
        $postData   = Request::form()->all();
        
        if($insureshipService && !empty($postData['insureShipment']) && CrmPayload::get('meta.isSplitOrder') === false)
        {
            CrmPayload::set('insureShipment', 1);
            Session::set('insureShipment', 1);
        }

        $shipProfileId = Config::extensionsConfig('KonnektiveUtilPack.activate_ship_profileid');

        if($shipProfileId && !empty($postData['shipProfileId']) && CrmPayload::get('meta.isSplitOrder') === false)
        {
            CrmPayload::set('shipProfileId', $postData['shipProfileId']);
            Session::set('shipProfileId', $postData['shipProfileId']);
            
            if($postData['bypassShipPrice']) {
                $payload = CrmPayload::all();        
                foreach ($payload['products'] as $key => $value)
                {
                    if($value['shippingPrice'] == '0.00')
                    {
                        unset($payload['products'][$key]['shippingPrice']);
                    }
                }
                CrmPayload::replace($payload);
            }
        }
    }
    
    public function bypassZeroValue()
    {
        $isBypassEnabled = Config::extensionsConfig(
            'KonnektiveUtilPack.bypass_zero_value'
        );
        if (
                !$this->checkExtensionStatus('KonnektiveUtilPack') ||
                !$isBypassEnabled
            )
        {
            return;
        }
        
        $payload = CrmPayload::all();        
        
        foreach ($payload['products'] as $key => $value)
        {
            if(
                    $value['productPrice'] == '0.00' && 
                    $value['shippingPrice'] == '0.00'
               )
            {
                unset(
                        $payload['products'][$key]['shippingPrice'], 
                        $payload['products'][$key]['productPrice']
                     );
            }
        }
        CrmPayload::replace($payload);
        
    }
    
    public function addAdditionalParams()
    {
        if (
                !$this->checkExtensionStatus('KonnektiveUtilPack')
            )
        {
            return;
        }
        $formPayload = Request::form()->all();
        $customParams = array('custom1','custom2','custom3','custom4','custom5', 'salesTax','skipQA', 'productSalesTax', 'partialOrderID', 'billDelayOverride', 'replaceProductId', 'forceGatewayId', 'ipAddress');
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
    }
    
    public function addProductVariation() {

        if (!$this->checkExtensionStatus('KonnektiveUtilPack')) {
            return;
        }

        $formPayload = Request::form()->all();

        foreach ($formPayload as $key => $fvalue) {

            if (preg_match('/^variant[0-9]_id$/i', $key) && isset($fvalue))
            {
                CrmPayload::set($key, $fvalue);
            }
        }
    }

}
