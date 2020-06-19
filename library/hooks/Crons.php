<?php

namespace Application\Hook;

use Admin\Controller\SettingsController;
use Application\Helper\Security;
use Application\Config;
use Application\Request;
use Admin\Controller\ExtensionsController as Extensions;
use Admin\Controller\CampaignsController;

class Crons
{

    public function disableDevelopmentMode()
    {
        $maxTimeLimit = 3 * 60 * 60; // In seconds
        $fileName     = STORAGE_DIR . DS . '.development_mode';

        if (!file_exists($fileName)) {
            touch($fileName);
            return;
        }

        $currentTime    = time();
        $fileModifiedAt = filemtime($fileName);

        if (($currentTime - $fileModifiedAt) > $maxTimeLimit) {
            $settiongController = new SettingsController();
            $settiongController->updateDevMode(1, 0);
        }

    }
    
    public function disableExtensionsIfLicenseKeyExpired(){
        $validLicense = Security::isValidLicenseKey(Config::settings('domain'), Config::settings('license_key'), Config::settings('unify_authentication_key'));
        if (!$validLicense) {
            // Disbaled the all extensions
            $extension = new Extensions('');
            $allExtension = $extension->installedExtensions();

            if($allExtension['success']) {
                $allExtension = $allExtension['data'];
                foreach ($allExtension as $eachExtension) {
                    
                    foreach($eachExtension as $key => $value) {
                        Request::form()->set($key, $value);
                        if(!strcmp($key, 'active'))
                            Request::form()->set($key, false);
                    }
                    $extension->edit($eachExtension['id'], true);
                }
                echo "All Extension has been deactivated due to license key expired.";
            }
            
        }        
    }
    
    public function storeCrmProductShippingDetails() {
        try {
            $autoSyncCampaigns = array();

            $crmDetails = array_values(Config::crms())[0];
            $campaignDetails = Config::campaigns();
            if (empty($campaignDetails)) {
                return;
            }
            $i = 0;
            foreach ($campaignDetails as $key => $value) {
                if (empty($value['enable_product_shipping_auto_sync']) ||
                        $value['crm_id'] != $crmDetails['id']) {
                    continue;
                }
                $autoSyncCampaigns[$i] = $value;
                $i++;
            }

            if (empty($autoSyncCampaigns))
                return;
            $campaignObj = new CampaignsController();
            foreach ($autoSyncCampaigns as $key => $value) {
                $crmData = $this->getCrmData($crmDetails, $value);
                $this->setFormPArams($value, $crmData);
                $campaignObj->edit($value['id']);
            }
        } catch (Exception $ex) {
            
        }
    }

    private function setFormPArams($existingCampaignDetails, $crmData) {
        if (!$crmData['success'])
            return;
        foreach ($existingCampaignDetails as $key => $val) {
            Request::form()->set($key, $val);
        }
        $this->updateProductParams($crmData, $existingCampaignDetails);
        $this->updateShippingParams($crmData, $existingCampaignDetails);
        $this->updateShippingProfile($crmData, $existingCampaignDetails);

    }

    private function updateProductParams($crmCampaignData, $campaignData) {
        if (empty($crmCampaignData['data']['productDetails']))
            return;
        $campaignProductData = array_values(json_decode($campaignData['product_array'], true));
        foreach ($campaignProductData as $key => $value) {
            foreach ($crmCampaignData['data']['productDetails'] as $crmCampaignKey => $crmCampaignValue) {
                if ($value['product_id'] == $crmCampaignValue['product_id'])
                    $campaignProductData[$key]['product_price'] = $crmCampaignValue['product_price'];
            }
        }

        Request::form()->set('product_array', json_encode($campaignProductData) 
        );
    }

    private function updateShippingParams($crmCampaignData, $campaignData) {
        if (empty($crmCampaignData['data']['shippingDetails']))
            return;
        foreach ($crmCampaignData['data']['shippingDetails'] as $key => $value) {

            if ($value['shipping_id'] == $campaignData['shipping_id'])
                Request::form()->set('shipping_price', $value['shipping_price']);
        }
    }

    private function updateShippingProfile($crmCampaignData, $campaignData) {
        $shippingProfiles = array_values(json_decode($campaignData['shipping_profiles'], true));
        if (empty($shippingProfiles) || empty($crmCampaignData['data']['shippingDetails']))
            return;
        foreach ($shippingProfiles as $shippingProfileKey => $shippingProfileValue) {
            foreach ($crmCampaignData['data']['shippingDetails'] as $key => $value) {
                if ($shippingProfileValue['shipping_id'] == $value['shipping_id']) {
                    $shippingProfiles[$shippingProfileKey]['shipping_price'] = $value['shipping_price'];
                }
            }
        }
        Request::form()->set('shipping_profiles', json_encode($shippingProfiles));
    }

    private function getCrmData($crmDetails, $param) {
        $crmDetails['campaign_id'] = $param['campaign_id'];
        $crmClass = sprintf('Application\Model\%s', ucfirst($crmDetails['crm_type']));
        return call_user_func_array(
                array($crmClass, 'getCrmCampaignData'), array($crmDetails)
        );
    }
}