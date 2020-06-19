<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\Response;
use Application\Session;
use Application\CrmResponse;
use Application\Request;

class DeclineRedirection
{
    public function performRedirection()
    {
        if (
            CrmResponse::get('isPrepaidDecline') == true ||
            CrmResponse::get('success') != '' //||
            //Session::get('steps.current.id') !== 1 
        ) {
            return;
        }
        
        $redirectionUrl = '';
        $currentStep = Session::get('steps.current.id');
        $appVersion = Session::get('appVersion');
        $declineRedirectionEnabled = Config::extensionsConfig('CbUtilityPackage.decline_redirection_enabled');
        $declineRedirection = Config::extensionsConfig('CbUtilityPackage.decline_redirection');
        
        if (
            Request::attributes()->get('action') == 'prospect' ||
            empty($declineRedirectionEnabled)
        ) {
            return;
        }
        
        foreach ($declineRedirection as $value)
        {
            if($currentStep == $value['redirection_step'])
            { 
                $redirectionUrl = $value['desktop_redirection'];

                if($appVersion == 'mobile')
                {
                    $redirectionUrl = $value['mobile_redirection'];
                }
                
                break;
            }
        }
        
        if (empty($redirectionUrl)) {
            return;
        }

        $queryParams = Session::get('queryParams', array());
        if (!empty($queryParams)) {
            $redirectionUrl = sprintf(
                '%s?%s', $redirectionUrl, http_build_query($queryParams)
            );
        }

        Response::send(array(
            'success'          => false,
            'errors'           => array('crmError' => 'Declined!'),
            'Redirect'         => $redirectionUrl,
        ));

    }

}
