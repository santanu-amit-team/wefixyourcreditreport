<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\CrmPayload;
use Application\Session;

class AffiliateOverwrite
{

    public function performOverwrite()
    {   
        $isPrepaidFlow = Session::get('steps.meta.isPrepaidFlow');
        $isScrapFlow = Session::get('steps.meta.isScrapFlow');
        $overwriteAffiliates = array();
        $affiliates = array();
        $extensionAffiliates = array();
        

        $enableOverwriteForPrepaid = Config::extensionsConfig(
                        'CbUtilityPackage.enable_overwrite_prepaid'
        );
        $enableOverwriteForOfferFilter = Config::extensionsConfig(
                        'CbUtilityPackage.enable_overwrite_order_filter'
        );

        if ($enableOverwriteForPrepaid == false && $enableOverwriteForOfferFilter == false)
        {
            return;
        }

        if ($enableOverwriteForPrepaid == true)
        {
            if ($isPrepaidFlow === true)
            {
                $extensionAffiliates = $this->getMappedAffiliates("prepaid");
                CrmPayload::update(array('affiliates' => $extensionAffiliates));
            }
        }

        if ($enableOverwriteForOfferFilter == true)
        {
            if ($isScrapFlow === true)
            {
                $extensionAffiliates = $this->getMappedAffiliates("order");
                CrmPayload::update(array('affiliates' => $extensionAffiliates));
            }
        }
    }
    
    
    private function getMappedAffiliates($affSlug)
    {
        $affiliates = CrmPayload::get('affiliates');
        $affiliateProfileID = Config::extensionsConfig('CbUtilityPackage.' . $affSlug . '_affiliate');
        $affData = $this->getAffiliatesValueByID($affiliateProfileID);
        $extensionAffiliates = array();
        foreach ($affiliates as $key => $value)
        {
            foreach ($affData as $key1 => $value1)
            {
                if(strtolower($key) == strtolower($key1))
                {
                    $extensionAffiliates[$key] = !empty($affData[$key1]) ? $affData[$key1] : $affiliates[$key1];
                }

            }
        }
        return $extensionAffiliates;
    }
    
    private function getAffiliatesValueByID($id)
    {
        $affData = Config::affiliates();
        $affDetails = array();
        if(!empty($affData))
        {
            foreach ($affData as $value)
            {
                if($value['id'] == $id)
                {
                    $affDetails = $value;
                    break;
                }
            }
        }
        return $affDetails;
    }

    public function performSplitOverwrite()
    {
        if (CrmPayload::get('meta.isSplitOrder') !== true)
        {
            return;
        }

        $enableOverwriteForSplitOfferFilter = Config::extensionsConfig(
                        'CbUtilityPackage.enable_overwrite_split_order'
        );

        if ($enableOverwriteForSplitOfferFilter == false)
        {
            return;
        }

        $extensionAffiliates = $this->getMappedAffiliates("split_order");
        CrmPayload::update(
                array(
                    'affiliates' => array_filter($extensionAffiliates),
                )
        );
    }

}
