<?php

return array(
    'routes'          => array(
        array(
            'slug'     => 'prospect',
            'callback' => 'CrmsController@prospect',
            'method'   => 'POST',
        ),
        array(
            'slug'     => 'checkout',
            'callback' => 'CrmsController@checkout',
            'method'   => 'POST',
        ),
        array(
            'slug'     => 'upsell',
            'callback' => 'CrmsController@upsell',
            'method'   => 'POST',
        ),
        array(
            'slug'     => 'downsell',
            'callback' => 'CrmsController@downsell',
            'method'   => 'POST',
        ),
        array(
            'slug'     => 'validate-coupon',
            'callback' => 'CrmsController@validateCoupon',
            'method'   => 'POST',
        ),
        array(
            'slug'     => 'app-config',
            'callback' => 'ConfigsController@appConfig',
        ),
        array(
            'slug'     => 'set-click-id',
            'callback' => 'Application\Hook\Routes@setClickId',
        ),
        array(
            'slug'     => 'disable-devmode',
            'callback' => 'Application\Hook\Crons@disableDevelopmentMode',
        ),
        array(
            'slug'     => 'file-hash',
            'callback' => 'Application\Diff@getLocalFileHash',
        ),
        array(
            'slug'     => 'set-token',
            'callback' => 'Application\Hook\Events@setInitialToken',
        ),
        array(
            'slug'     => 'fire-sixcrm-confirm-order',
            'callback' => 'Application\Hook\Events@fireConfirmOrder',
        ),
        array(
            'slug'     => 'process-additional-order',
            'callback' => 'Application\Hook\Events@processAdditionalOrder',
        ),
        array(
            'slug'     => 'fulfilled-order',
            'callback' => 'Application\Hook\Events@fulfilledOrder',
        ),
        array(
            'slug'     => 'cancelled-order',
            'callback' => 'Application\Hook\Events@cancelledOrder',
        ),
        array(
            'slug'     => 'refunded-order',
            'callback' => 'Application\Hook\Events@refundedOrder',
        ),
//        array(
//            'slug'     => 'check-cron-status',
//            'callback' => 'Application\Hook\Events@checkCronStatus',
//        ),
        array(
            'slug'     => 'recurring-order',
            'callback' => 'Application\Hook\Events@recurringOrder',
        ),
    ),
    'hooks'           => array(
        array(
            'event'    => 'afterBasicFormValidation',
            'callback' => 'Application\Hook\Events@performTestCardActions',
            'priority' => 1000,

        ),
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => 'Application\Hook\Events@injectClickIdGeneratorScript',
            'priority' => 1000,
        ),
//        array(
//            'event'    => 'beforeBodyTagClose',
//            'callback' => 'Application\Hook\Events@fireCron',
//            'priority' => 1000,
//        ),
//        array(
//            'event'    => 'beforeBodyTagClose',
//            'callback' => 'Application\Hook\Events@injectKountPixelIframe',
//            'priority' => 1000,
//        ),
//        array(
//            'event'    => 'beforeAnyCrmRequest',
//            'callback' => 'Application\Hook\Events@injectKountSessionIdIntoCrmPayload',
//            'priority' => 1000,
//        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => 'Application\Hook\Events@assertToken',
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => "Application\Hook\Events@injectToken",
            'priority' => 1000,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => "Application\Hook\Events@setSessionID",
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => "Application\Hook\Events@setUpsellData",
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => "Application\Hook\Events@confirmOrder",
            'priority' => 1000,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => "Application\Hook\Events@captureAdditionalOrder",
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => "Application\Hook\Events@injectAdditionalOrderScript",
            'priority' => 1000,
        ),
        array(
            'event'    => 'afterBasicFormValidation',
            'callback' => "Application\Hook\Events@checkPrepaid",
            'priority' => 1001,
        ),
        array(
            'event'    => 'afterCrmPayloadReady',
            'callback' => "Application\Hook\Events@updatePrepaidMethod",
            'priority' => 1001,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => "Application\Hook\Events@addAdditionalPixels",
            'priority' => 1001,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => 'Application\Hook\Events@nmiDataStore',
            'priority' => 1001,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => 'Application\Hook\Events@approveOfflineOrders',
            'priority' => 1000,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => 'Application\Hook\Events@reprocessOrders',
            'priority' => 1000,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => 'Application\Hook\Events@switchCampaign',
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => 'Application\Hook\Events@switchUpsellCampaign',
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => 'Application\Hook\Events@regularPreAuth',
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeSplitOrderCrmRequest',
            'callback' => 'Application\Hook\Events@regularSplitPreAuth',
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => 'Application\Hook\Events@postSiteUrl',
            'priority' => 900,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => "Application\Hook\Events@passWebsiteID",
            'priority' => 900,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => 'Application\Hook\Events@increaseDeclineCount',
            'priority' => 900,
        ),
        array(
            'event'    => 'afterBasicFormValidation',
            'callback' => 'Application\Hook\Events@checkDecline',
            'priority' => 900,
        ),
        array(
            'event'    => 'beforeAnyDelayCrmRequest',
            'callback' => 'Application\Hook\Events@pushReprocessDataForDelay',
            'priority' => 1000,
        ),
        array(
            'event'    => 'afterAnyDelayCrmRequest',
            'callback' => 'Application\Hook\Events@reprocessOrdersForDelay',
            'priority' => 1000,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => 'Application\Hook\Events@reprocessOrdersForUpsell',
            'priority' => 1000,
        ),
        array(
            'event' => 'beforeAnyCrmRequest',
            'callback' => "Application\\Hook\\Events@detectIsPrepaidRouteEligible",
            'priority' => 900,
        ),
        array(
            'event' => 'beforeAnyCrmRequest',
            'callback' => "Application\\Hook\\Events@updateCampaign",
            'priority' => 800,
        ),
        array(
            'event' => 'afterAnyCrmRequest',
            'callback' => "Application\\Hook\\Events@updatePrepaidRoutelog",
            'priority' => 900,
        )
    ),
    'crons'           => array(
        array(
            'every'   => '*/15 * * * *',
            'handler' => 'Application\Hook\Crons@disableDevelopmentMode',
            'overlap' => false,
        ),
//        array(
//            'every'   => '*/60 * * * *',
//            'handler' => 'Application\Hook\Crons@disableExtensionsIfLicenseKeyExpired',
//            'overlap' => false,
//        ),
        array(
            'every'   => '00 23 * * *',
            'handler' => 'Application\Hook\Crons@storeCrmProductShippingDetails',
            'overlap' => false,
        )
    ),
    'scripts'         => array(
        'promise.min'       => 'assets/js/promise.min.js',
        'jquery.min'        => 'assets/js/jquery.min.js',
        'jquery.mask.min'   => 'assets/js/jquery.mask.min.js',
        'core_validator'    => 'assets/js/validator.js',
        'core_codebase'     => 'assets/js/codebase.js',
        'core_form_handler' => 'assets/js/form_handler.js',
        'core_app'          => 'assets/js/app.js',
        'core_outro'        => 'assets/js/outro.js',
    ),
    'gtmetrixIps'     => array(
        'ipv4' => array(
            '208.70.247.157',
            '204.187.14.70',
            '204.187.14.71',
            '204.187.14.72',
            '204.187.14.73',
            '204.187.14.74',
            '204.187.14.75',
            '204.187.14.76',
            '204.187.14.77',
            '204.187.14.78',
            '13.85.80.124',
            '13.84.146.132',
            '13.84.146.226',
            '40.74.254.217',
            '172.255.61.34',
            '172.255.61.35',
            '172.255.61.36',
            '172.255.61.37',
            '172.255.61.38',
            '172.255.61.39',
            '52.62.235.19',
            '191.235.85.154',
            '191.235.86.0',
            '52.66.75.147',
            '52.175.28.116',
        ),
        'ipv6' => array(
            '2607:fcc0:6::110',
            '2607:fcc0:6::111',
            '2607:fcc0:6::112',
            '2607:fcc0:6::113',
            '2607:fcc0:6::114',
            '2607:fcc0:6::115',
            '2607:fcc0:6::116',
            '2607:fcc0:6::117',
            '2607:fcc0:6::118',
            '2607:fcc0:4000:5::101',
            '2607:fcc0:4000:5::102',
            '2607:fcc0:4000:5::103',
            '2607:fcc0:4000:5::104',
            '2607:fcc0:4000:5::105',
            '2607:fcc0:4000:5::106',
        ),
    ),
    'systemConstants' => array(
        'KONNEKTIVE_API_BASE_URL' => 'https://api.konnektive.com',
        'VELOX_API_BASE_URL'      => 'https://crm.veloxcrm.com/api',
        'RESPONSE_API_BASE_URL'   => 'https://openapi.responsecrm.com/api/v2/open',
        'VIEW_DIR'                => 'app',
        'REMOTE_URL'              => 'https://framework.unify.to/',
        'REMOTE_DOWNLOAD_URL'     => 'https://framework.unify.to/download.php',
        'REMOTE_FRAMEWORK_UPDATE_URL'  => 'https://framework.unify.to/',
        'REMOTE_LIST_URL'         => 'https://framework.unify.to/extension-lists/extension.list.json',
        'REMOTE_LIST_CHANGELOG_URL' => 'https://framework.unify.to/extension-lists/changelog',
        '201CLICKS_URL'           => 'https://api.almost20.com',
        '201CLICKS_AUTH_KEY'      => '7FCV8Sb0Qbmw6JM7l7EM2dcw3906D2T8',
        'LOCAL_ENCRYPTION_KEY'    => 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282',
        'UNIFY_LIVE_URL'          => 'https://platform.unify.to',
        'version'                 => '4.5.1',
        
    ),
);
