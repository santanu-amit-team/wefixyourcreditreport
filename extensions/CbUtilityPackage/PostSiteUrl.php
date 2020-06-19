<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\CrmPayload;
use Application\Request;

class PostSiteUrl
{
    public function __construct(){
        $this->enableSiteUrl = Config::extensionsConfig('CbUtilityPackage.post_site_url');
        $this->siteUrlType = Config::extensionsConfig('CbUtilityPackage.type');
        $this->siteUrl = Config::extensionsConfig('CbUtilityPackage.site_url');
    }
    
    public function postSiteUrl()
    {
        $formAction = Request::attributes()->get('action');
        if($formAction == 'prospect' || empty($this->enableSiteUrl)) {
            return;
        }
        
        switch ($this->siteUrlType)
        {
            case 'static':
                $website = preg_replace('#^https?://#', '', $this->siteUrl);
                break;
            
            case 'siteurl':
                $offerUrl = Request::getOfferUrl();
                $website = preg_replace('#^https?://#', '', $offerUrl);
                break;

            default:
                break;
        }
        
        $crmType = CrmPayload::get('meta.crmType');
        if(!empty($website)) {
            switch ($crmType)
            {
                case 'limelight':
                    CrmPayload::set('website', $website);
                    break;

                case 'konnektive':
                    CrmPayload::set('salesUrl', $website);
                    break;

                default:
                    break;
            }
            
        }
        
    }

}
