<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\Response;
use Application\Session;
use Application\CrmResponse;

class PrepaidRedirection
{
    public function performRedirection()
    {
        if (
            CrmResponse::get('isPrepaidDecline') !== true ||
            Session::get('steps.current.id') !== 1 ||
            !Config::extensionsConfig('CbUtilityPackage.enable_prepaid_flow')
        ) {
            return;
        }
        
        $redirectionUrl = Config::extensionsConfig(
            'CbUtilityPackage.prepaid_redirection_url'
        );

        $appVersion = Session::get('appVersion');

        if($appVersion == 'mobile'){
            $redirectionUrl = Config::extensionsConfig(
                'CbUtilityPackage.prepaid_redirection_url_mobile'
            );
        }

        if (empty($redirectionUrl)) {
            return;
        }

        $enablePrepaidFlow = Config::extensionsConfig('CbUtilityPackage.enable_prepaid_flow');

        if(
            $enablePrepaidFlow && 
            !Session::has('extensions.konnektiveUtilPack.isPrepaidFlow')
        ) {
            Session::set('extensions.konnektiveUtilPack.isPrepaidFlow', true);
        }

        $queryParams = Session::get('queryParams', array());
        if (!empty($queryParams)) {
            $redirectionUrl = sprintf(
                '%s?%s', $redirectionUrl, http_build_query($queryParams)
            );
        }

        Response::send(array(
            'success'          => false,
            'errors'           => array('crmError' => 'Prepaid declined!'),
            'prepaidRedirect' => $redirectionUrl,
        ));

    }

    public function setPrepaidSession()
    {
        $enablePrepaidFlow = Config::extensionsConfig('CbUtilityPackage.enable_prepaid_flow');
        $pageType = Session::get('steps.current.pageType');
        
        if(
            $enablePrepaidFlow && 
            Session::has('extensions.konnektiveUtilPack.isPrepaidFlow') &&
            $pageType == 'checkoutPage'
        ) {
            Session::set('steps.meta.isPrepaidFlow', true);
        }
    }
}
